// db-api.js — Firestore Data Layer for MiniShiksha OMR
// Replaces all PHP API (api.php) operations with direct Firestore calls.
// Requires: firebase-config.js loaded first (provides db, auth, fsRoom, etc.)

const ROOMS_COL  = 'omr_rooms';
const CODES_COL  = 'omr_codes';
const TESTS_COL  = 'omr_tests';

// ══════════════════════════════════════════════════════════════
//  HELPERS
// ══════════════════════════════════════════════════════════════

function _generateCode(existingSet) {
  const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
  let code;
  do {
    code = '';
    for (let i = 0; i < 3; i++) code += chars[Math.floor(Math.random() * chars.length)];
  } while (existingSet.has(code));
  return code;
}

function _generateRoomId() {
  const hex = '0123456789ABCDEF';
  let id = '';
  for (let i = 0; i < 8; i++) id += hex[Math.floor(Math.random() * 16)];
  return id;
}

function _nowSec() { return Math.floor(Date.now() / 1000); }

function _buildOnlineStatus(players) {
  const now = _nowSec();
  const online = {};
  (players || []).forEach((p, i) => { online[i] = (now - (p.online_at || 0)) < 15; });
  return online;
}

function _buildPartialAnswerKey(room) {
  const partial = {};
  const revealed = room.revealed || {};
  const answerKey = room.answer_key || {};
  Object.keys(revealed).forEach(qid => {
    if (revealed[qid] >= 1) partial[qid] = answerKey[qid] || null;
  });
  return partial;
}

function _buildSyncResponse(room) {
  const now = _nowSec();
  const elapsed = room.started_at ? (now - room.started_at - (room.total_paused_sec || 0)) : 0;
  let timeRemaining = null;
  if (room.timer_mode === 'countdown' && room.started_at) {
    timeRemaining = Math.max(0, room.duration_sec - elapsed);
  }

  const summary = {
    room_id:          room.room_id,
    test_name:        room.test_name,
    test_info:        room.test_info || {},
    questions:        room.questions || [],
    question_texts:   room.question_texts || {},
    options:          room.options || {},
    status:           room.status,
    timer_mode:       room.timer_mode,
    duration_sec:     room.duration_sec,
    started_at:       room.started_at,
    elapsed_sec:      elapsed,
    time_remaining:   timeRemaining,
    player_count:     room.player_count,
    revealed:         room.revealed || {},
    online:           _buildOnlineStatus(room.players),
    chat:             (room.chat || []).slice(-20),
    last_updated:     room.last_updated,
    pdf_url:          room.pdf_url || null,
    solution_pdf_url: room.solution_pdf_url || null,
    page_map:         room.page_map || null,
    exam_mode:        room.exam_mode || false,
    pending_reveal:   room.pending_reveal || null,
    paused_until:     room.paused_until || 0,
    total_paused_sec: room.total_paused_sec || 0,
    brb_used:         room.brb_used || {},
    call_active:      room.call_active || null,
    answer_key_partial: _buildPartialAnswerKey(room),
    players:          [],
  };

  if (room.status === 'finished') {
    summary.answer_key = room.answer_key;
  }

  (room.players || []).forEach((p, i) => {
    summary.players[i] = {
      idx:              p.idx,
      name:             p.name,
      code:             p.code,
      joined:           p.joined,
      submitted:        p.submitted,
      answers:          p.answers || {},
      marked:           p.marked || {},
      skipped:          p.skipped || {},
      current_question: p.current_question || null,
      locked_answers:   p.locked_answers || {},
      status:           p.status || 'active',
    };
  });

  return summary;
}

// ══════════════════════════════════════════════════════════════
//  ROOM OPERATIONS
// ══════════════════════════════════════════════════════════════

async function _fsCreateRoom(payload) {
  const testName     = (payload.test_name || '').trim();
  const jsonData     = payload.json_data || null;
  const timerMode    = payload.timer_mode || 'countdown';
  const examMode     = !!payload.exam_mode;
  const defaultDur   = examMode ? 130 : 120;
  const durationMin  = parseInt(payload.duration_minutes || defaultDur, 10);
  const playerCount  = Math.max(1, Math.min(4, parseInt(payload.player_count || 1, 10)));
  let   playerNames  = payload.player_names || [];

  if (!testName) throw new Error('Test name required');

  for (let i = 0; i < playerCount; i++) {
    if (!playerNames[i]) playerNames[i] = 'Player ' + (i + 1);
  }
  playerNames = playerNames.slice(0, playerCount);

  // Resolve answer key: from payload or saved test
  let answerKey = null;
  let testData  = jsonData;
  if (jsonData && jsonData.responses) {
    answerKey = jsonData.responses;
    // Save/update test template in Firestore
    await db.collection(TESTS_COL).doc(testName).set(jsonData, { merge: true });
  } else {
    const testSnap = await db.collection(TESTS_COL).doc(testName).get();
    if (testSnap.exists) {
      testData  = testSnap.data();
      answerKey = testData.responses || null;
    }
  }
  if (!answerKey) throw new Error('No answer key found. Please paste the JSON data.');

  // Normalise answer key
  const normalised = {};
  Object.keys(answerKey).forEach(q => { normalised[q] = (answerKey[q] + '').toLowerCase().trim(); });

  // Sort questions numerically
  const questions = Object.keys(normalised);
  questions.sort((a, b) => {
    const na = parseInt(a.replace(/\D/g, ''), 10) || 0;
    const nb = parseInt(b.replace(/\D/g, ''), 10) || 0;
    return na - nb;
  });

  const roomId = _generateRoomId();

  // Generate unique player codes (check Firestore for collisions)
  const usedCodes = new Set();
  const playerCodes = [];
  for (let i = 0; i < playerCount; i++) {
    const code = _generateCode(usedCodes);
    usedCodes.add(code);
    playerCodes.push(code);
  }

  // Build players array
  const emptyAnswers = {};
  const emptyMarked  = {};
  const emptySkipped = {};
  questions.forEach(q => { emptyAnswers[q] = null; emptyMarked[q] = false; emptySkipped[q] = false; });

  const players = [];
  for (let i = 0; i < playerCount; i++) {
    players.push({
      idx:       i,
      name:      playerNames[i],
      code:      playerCodes[i],
      joined:    (i === 0),
      submitted: false,
      answers:   { ...emptyAnswers },
      marked:    { ...emptyMarked },
      skipped:   { ...emptySkipped },
      online_at: _nowSec(),
    });
  }

  const room = {
    room_id:          roomId,
    test_name:        testName,
    test_info:        (testData && testData.test_info) || {},
    questions:        questions,
    question_texts:   (testData && testData.questions) || {},
    options:          (testData && testData.options) || {},
    answer_key:       normalised,
    revealed:         {},
    timer_mode:       timerMode,
    duration_sec:     durationMin * 60,
    started_at:       null,
    ended_at:         null,
    status:           'waiting',
    player_count:     playerCount,
    players:          players,
    chat:             [],
    pdf_url:          (testData && testData.pdf_url) || null,
    solution_pdf_url: (testData && testData.solution_pdf_url) || null,
    page_map:         (testData && testData.page_map) || null,
    exam_mode:        examMode,
    paused_until:     0,
    total_paused_sec: 0,
    brb_used:         {},
    call_active:      null,
    pending_reveal:   null,
    created_at:       _nowSec(),
    last_updated:     _nowSec(),
    host_uid:         (auth.currentUser && auth.currentUser.uid) || null,
  };

  // Batch write: room document + code mappings
  const batch = db.batch();
  batch.set(db.collection(ROOMS_COL).doc(roomId), room);
  for (let i = 0; i < playerCount; i++) {
    batch.set(db.collection(CODES_COL).doc(playerCodes[i]), {
      room_id:    roomId,
      player_idx: i,
      created_at: _nowSec(),
    });
  }
  await batch.commit();

  return {
    success:      true,
    room_id:      roomId,
    player_codes: playerCodes,
    player_count: playerCount,
    q_count:      questions.length,
  };
}

async function _fsGetRoom(payload) {
  const roomId = (payload.room_id || '').trim();
  if (!roomId) throw new Error('Missing room_id');
  const snap = await db.collection(ROOMS_COL).doc(roomId).get();
  if (!snap.exists) throw new Error('Room not found');
  const room = snap.data();
  if (room.status !== 'finished') {
    const copy = { ...room };
    delete copy.answer_key;
    return copy;
  }
  return room;
}

async function _fsCheckRecentRooms(payload) {
  const codes = payload.codes || [];
  if (!Array.isArray(codes) || codes.length === 0) return { success: true, rooms: [] };

  const activeRooms = [];
  for (const rawCode of codes) {
    const code = (rawCode + '').toUpperCase().trim();
    const codeSnap = await db.collection(CODES_COL).doc(code).get();
    if (!codeSnap.exists) continue;
    const codeData = codeSnap.data();
    const roomSnap = await db.collection(ROOMS_COL).doc(codeData.room_id).get();
    if (!roomSnap.exists) continue;
    const room = roomSnap.data();
    const pidx = codeData.player_idx;
    const player = (room.players || [])[pidx];
    if (!player) continue;

    let canReattempt = false;
    if (room.status === 'finished') {
      const unattempted = (room.questions || []).filter(q => (player.answers || {})[q] == null).length;
      if (unattempted > 0) canReattempt = true;
    }
    if (!player.submitted || canReattempt) {
      activeRooms.push({
        code, room_id: codeData.room_id, test_name: room.test_name,
        status: room.status, player_name: player.name, can_reattempt: canReattempt,
      });
    }
  }
  return { success: true, rooms: activeRooms };
}

async function _fsUpdateCallState(payload) {
  const roomId = (payload.room_id || '').trim();
  const code   = (payload.player_id || '').toUpperCase().trim();
  const active = !!payload.active;
  if (!roomId || !code) throw new Error('Missing params');

  const roomRef = db.collection(ROOMS_COL).doc(roomId);
  const snap = await roomRef.get();
  if (!snap.exists) throw new Error('Room not found');
  const room = snap.data();

  const callerIdx = (room.players || []).findIndex(p => p.code === code);
  if (callerIdx < 0) throw new Error('Player does not belong to this room');
  const callerName = room.players[callerIdx].name || 'Player';

  const callActive = active ? {
    active: true, caller_idx: callerIdx, caller_name: callerName,
    caller_code: code, call_id: 'omr-' + roomId, started_at: _nowSec(),
  } : null;

  await roomRef.update({ call_active: callActive, last_updated: _nowSec() });
  return { success: true, call_active: callActive };
}

// ══════════════════════════════════════════════════════════════
//  PLAYER OPERATIONS
// ══════════════════════════════════════════════════════════════

async function _fsValidateCode(payload) {
  const code = (payload.code || '').toUpperCase().trim();
  const sessionId = payload.session_id || '';
  if (!code) return { valid: false, error: 'No code provided' };

  const codeSnap = await db.collection(CODES_COL).doc(code).get();
  if (!codeSnap.exists) return { valid: false, error: 'Code not found. Check and try again.' };
  const codeData = codeSnap.data();

  const roomSnap = await db.collection(ROOMS_COL).doc(codeData.room_id).get();
  if (!roomSnap.exists) return { valid: false, error: 'Room not found or expired.' };
  const room = roomSnap.data();
  if (room.status === 'finished') return { valid: false, error: 'This test has already ended.' };

  const pidx = codeData.player_idx;
  const player = (room.players || [])[pidx] || {};
  const isActive = player.joined || false;
  const timeSinceOnline = _nowSec() - (player.online_at || 0);
  const activeSession = player.active_session || '';

  if (isActive && timeSinceOnline < 15 && activeSession && sessionId && activeSession !== sessionId) {
    return { valid: false, error: 'This player code is currently active in another browser window.' };
  }

  return {
    valid: true, room_id: codeData.room_id, player_id: code,
    player_idx: pidx, player_name: player.name || 'Player',
  };
}

async function _fsPlayerJoin(payload) {
  const code    = (payload.player_id || '').toUpperCase().trim();
  const roomId  = (payload.room_id || '').trim();
  const sessionId = payload.session_id || '';
  if (!code || !roomId) throw new Error('Missing player_id or room_id');

  const codeSnap = await db.collection(CODES_COL).doc(code).get();
  if (!codeSnap.exists) throw new Error('Invalid player code');
  const pidx = codeSnap.data().player_idx;

  const roomRef = db.collection(ROOMS_COL).doc(roomId);
  return db.runTransaction(async tx => {
    const snap = await tx.get(roomRef);
    if (!snap.exists) throw new Error('Room not found');
    const room = snap.data();
    const player = (room.players || [])[pidx];
    if (!player) throw new Error('Player not found');

    const now = _nowSec();
    const isActive = player.joined || false;
    const timeSinceOnline = now - (player.online_at || 0);
    const activeSession = player.active_session || '';

    if (isActive && timeSinceOnline < 15 && activeSession && sessionId && activeSession !== sessionId) {
      throw new Error('Player slot is currently active in another window.');
    }

    room.players[pidx].joined = true;
    room.players[pidx].online_at = now;
    if (sessionId) room.players[pidx].active_session = sessionId;
    room.last_updated = now;

    tx.update(roomRef, { players: room.players, last_updated: now });

    const allJoined = room.players.every(p => p.joined);
    return {
      success: true, all_joined: allJoined, player_idx: pidx,
      player_name: room.players[pidx].name, room_status: room.status,
    };
  });
}

async function _fsSubmitPlayer(payload) {
  const roomId = (payload.room_id || '').trim();
  const code   = (payload.player_id || '').toUpperCase().trim();
  if (!roomId || !code) throw new Error('Missing params');

  const codeSnap = await db.collection(CODES_COL).doc(code).get();
  if (!codeSnap.exists) throw new Error('Invalid code');
  const pidx = codeSnap.data().player_idx;

  const roomRef = db.collection(ROOMS_COL).doc(roomId);
  return db.runTransaction(async tx => {
    const snap = await tx.get(roomRef);
    if (!snap.exists) throw new Error('Room not found');
    const room = snap.data();

    room.players[pidx].submitted = true;
    const allDone = room.players.every(p => p.submitted);
    if (allDone) { room.status = 'finished'; room.ended_at = _nowSec(); }
    room.last_updated = _nowSec();

    tx.update(roomRef, { players: room.players, status: room.status, ended_at: room.ended_at || null, last_updated: room.last_updated });
    return { success: true, all_done: allDone, submitted: room.players.map(p => p.submitted) };
  });
}

async function _fsRenamePlayer(payload) {
  const roomId = (payload.room_id || '').trim();
  const code   = (payload.player_id || '').toUpperCase().trim();
  const name   = (payload.name || '').trim().substring(0, 50);
  if (!roomId || !code || !name) throw new Error('Missing params');

  const codeSnap = await db.collection(CODES_COL).doc(code).get();
  if (!codeSnap.exists) throw new Error('Invalid code');
  const pidx = codeSnap.data().player_idx;

  const roomRef = db.collection(ROOMS_COL).doc(roomId);
  const snap = await roomRef.get();
  if (!snap.exists) throw new Error('Room not found');
  const room = snap.data();
  room.players[pidx].name = name;
  await roomRef.update({ players: room.players, last_updated: _nowSec() });
  return { success: true };
}

async function _fsStartReattempt(payload) {
  const roomId = (payload.room_id || '').trim();
  const code   = (payload.player_id || '').toUpperCase().trim();
  if (!roomId || !code) throw new Error('Missing params');

  const codeSnap = await db.collection(CODES_COL).doc(code).get();
  if (!codeSnap.exists) throw new Error('Invalid code');
  const pidx = codeSnap.data().player_idx;

  const roomRef = db.collection(ROOMS_COL).doc(roomId);
  return db.runTransaction(async tx => {
    const snap = await tx.get(roomRef);
    if (!snap.exists) throw new Error('Room not found');
    const room = snap.data();

    if (room.status === 'finished') {
      room.status = 'waiting';
      room.started_at = null;
      room.ended_at = null;
      room.reattempt_active = true;
      room.reattempt_expiry = _nowSec() + (7 * 24 * 3600);
      room.pending_reveal = null;
      room.revealed = {};

      room.players.forEach((player, i) => {
        room.players[i].joined = false;
        room.players[i].submitted = false;
        const locked = player.locked_answers || {};
        (room.questions || []).forEach(q => {
          if ((player.answers || {})[q] != null) locked[q] = player.answers[q];
        });
        room.players[i].locked_answers = locked;
      });
    }
    room.players[pidx].joined = true;
    room.players[pidx].online_at = _nowSec();
    room.last_updated = _nowSec();

    tx.update(roomRef, room);
    return { success: true };
  });
}

// ══════════════════════════════════════════════════════════════
//  EXAM OPERATIONS
// ══════════════════════════════════════════════════════════════

async function _fsSync(payload) {
  const roomId = (payload.room_id || '').trim();
  const code   = (payload.player_id || '').toUpperCase().trim();
  const sessionId = payload.session_id || '';
  if (!roomId) throw new Error('Missing room_id');

  const roomRef = db.collection(ROOMS_COL).doc(roomId);
  const snap = await roomRef.get();
  if (!snap.exists) throw new Error('Room not found');
  const room = snap.data();

  const pidx = (room.players || []).findIndex(p => p.code === code);
  const now = _nowSec();
  let needsWrite = false;

  if (pidx >= 0) {
    const lastOnline = room.players[pidx].online_at || 0;
    if (now - lastOnline > 10) needsWrite = true;
    if (sessionId && (room.players[pidx].active_session || '') !== sessionId) needsWrite = true;
  }

  // Check countdown expiry
  if (room.timer_mode === 'countdown' && room.started_at && room.status === 'active') {
    const elapsed = now - room.started_at - (room.total_paused_sec || 0);
    if (elapsed >= room.duration_sec) needsWrite = true;
  }

  // Timeout pending_reveal (15s)
  if (room.pending_reveal && (now - (room.pending_reveal.requested_at || 0)) > 15) {
    needsWrite = true;
  }

  if (needsWrite) {
    try {
      await db.runTransaction(async tx => {
        const freshSnap = await tx.get(roomRef);
        if (!freshSnap.exists) return;
        const r = freshSnap.data();

        // Timeout pending_reveal
        if (r.pending_reveal && (now - (r.pending_reveal.requested_at || 0)) > 15) {
          r.pending_reveal = null;
        }

        if (pidx >= 0 && r.players[pidx]) {
          r.players[pidx].online_at = now;
          if (sessionId) r.players[pidx].active_session = sessionId;
        }

        // Countdown expiry → auto-finish
        if (r.timer_mode === 'countdown' && r.started_at && r.status === 'active') {
          const elapsed = now - r.started_at - (r.total_paused_sec || 0);
          if (elapsed >= r.duration_sec) {
            r.status = 'finished';
            r.ended_at = now;
            r.players.forEach((p, i) => { r.players[i].submitted = true; });
          }
        }

        r.last_updated = now;
        tx.update(roomRef, r);
      });
      // Re-read for fresh data
      const freshSnap = await roomRef.get();
      return _buildSyncResponse(freshSnap.data());
    } catch (e) {
      // If transaction fails, return stale data
      console.warn('[db-api] sync write failed:', e.message);
    }
  }

  return _buildSyncResponse(room);
}

async function _fsUpdateAnswer(payload) {
  const roomId  = (payload.room_id || '').trim();
  const code    = (payload.player_id || '').toUpperCase().trim();
  const qid     = (payload.q_id || '').trim();
  const answer  = payload.answer != null ? (payload.answer + '').toLowerCase().trim() : null;
  const marked  = payload.marked != null ? !!payload.marked : null;
  const skipped = payload.skipped != null ? !!payload.skipped : null;
  if (!roomId || !code || !qid) throw new Error('Missing params');

  const codeSnap = await db.collection(CODES_COL).doc(code).get();
  if (!codeSnap.exists) throw new Error('Invalid code');
  const pidx = codeSnap.data().player_idx;

  const roomRef = db.collection(ROOMS_COL).doc(roomId);
  return db.runTransaction(async tx => {
    const snap = await tx.get(roomRef);
    if (!snap.exists) throw new Error('Room not found');
    const room = snap.data();
    if (room.status !== 'active') throw new Error('Test not active');
    if (!room.players[pidx]) throw new Error('Player not found');

    // Check locked answers (reattempt)
    if (room.players[pidx].locked_answers && room.players[pidx].locked_answers[qid]) {
      return { success: true, ts: _nowSec(), locked: true };
    }

    if (answer !== null && answer !== '') {
      if (!['a', 'b', 'c', 'd'].includes(answer)) throw new Error('Invalid answer');
    }

    if (answer !== null) {
      room.players[pidx].answers[qid] = (answer === '') ? null : answer;
      if (answer !== '') room.players[pidx].skipped[qid] = false;
    }
    if (marked !== null) room.players[pidx].marked[qid] = marked;
    if (skipped !== null) {
      room.players[pidx].skipped[qid] = skipped;
      if (skipped) room.players[pidx].answers[qid] = null;
    }
    room.players[pidx].online_at = _nowSec();
    if (payload.current_q) room.players[pidx].current_question = payload.current_q;
    room.last_updated = _nowSec();

    tx.update(roomRef, { players: room.players, last_updated: room.last_updated });
    return { success: true, ts: _nowSec() };
  });
}

async function _fsUpdateCurrentQ(payload) {
  const roomId = (payload.room_id || '').trim();
  const code   = (payload.player_id || '').toUpperCase().trim();
  const currentQ = (payload.current_q || '').trim();
  if (!roomId || !code || !currentQ) throw new Error('Missing params');

  const codeSnap = await db.collection(CODES_COL).doc(code).get();
  if (!codeSnap.exists) throw new Error('Invalid code');
  const pidx = codeSnap.data().player_idx;

  const roomRef = db.collection(ROOMS_COL).doc(roomId);
  const snap = await roomRef.get();
  if (!snap.exists) throw new Error('Room not found');
  const room = snap.data();
  if (!room.players[pidx]) throw new Error('Player not found');

  room.players[pidx].current_question = currentQ;
  room.players[pidx].online_at = _nowSec();
  room.last_updated = _nowSec();
  await roomRef.update({ players: room.players, last_updated: room.last_updated });
  return { success: true };
}

async function _fsStartTest(payload) {
  const roomId = (payload.room_id || '').trim();
  const code   = (payload.player_id || '').toUpperCase().trim();
  if (!roomId) throw new Error('Missing room_id');

  const roomRef = db.collection(ROOMS_COL).doc(roomId);
  return db.runTransaction(async tx => {
    const snap = await tx.get(roomRef);
    if (!snap.exists) throw new Error('Room not found');
    const room = snap.data();
    if (room.status !== 'waiting') throw new Error('Test already started or ended');

    // Verify host (player 0)
    const isHost = room.players.some((p, i) => p.code === code && i === 0);
    if (!isHost) throw new Error('Only the host can start the test');

    const startedAt = _nowSec();
    tx.update(roomRef, { status: 'active', started_at: startedAt, last_updated: _nowSec() });
    return { success: true, started_at: startedAt };
  });
}

async function _fsSendMessage(payload) {
  const roomId = (payload.room_id || '').trim();
  const code   = (payload.player_id || '').toUpperCase().trim();
  const message = (payload.message || '').trim();
  if (!roomId || !message) throw new Error('Missing params');

  const roomRef = db.collection(ROOMS_COL).doc(roomId);
  const snap = await roomRef.get();
  if (!snap.exists) throw new Error('Room not found');
  const room = snap.data();

  let name = 'Player';
  const pidx = (room.players || []).findIndex(p => p.code === code);
  if (pidx >= 0) name = room.players[pidx].name || 'Player';

  const chat = room.chat || [];
  chat.push({ from: name, msg: message.substring(0, 200), ts: _nowSec() });
  // Keep last 50
  const trimmedChat = chat.length > 50 ? chat.slice(-50) : chat;

  await roomRef.update({ chat: trimmedChat, last_updated: _nowSec() });
  return { success: true };
}

async function _fsUpdatePlayerStatus(payload) {
  const roomId = (payload.room_id || '').trim();
  const code   = (payload.player_id || '').toUpperCase().trim();
  const status = (payload.status || 'active').trim();
  if (!roomId || !code) throw new Error('Missing params');

  const allowed = ['active', 'brb', 'break', 'help'];
  if (!allowed.includes(status)) throw new Error('Invalid status');

  const codeSnap = await db.collection(CODES_COL).doc(code).get();
  if (!codeSnap.exists) throw new Error('Invalid code');
  const pidx = codeSnap.data().player_idx;

  const roomRef = db.collection(ROOMS_COL).doc(roomId);
  const snap = await roomRef.get();
  if (!snap.exists) throw new Error('Room not found');
  const room = snap.data();
  if (!room.players[pidx]) throw new Error('Player not found');

  room.players[pidx].status = status;
  room.players[pidx].online_at = _nowSec();
  room.last_updated = _nowSec();
  await roomRef.update({ players: room.players, last_updated: room.last_updated });
  return { success: true };
}

async function _fsBrb(payload) {
  const roomId = (payload.room_id || '').trim();
  const code   = (payload.player_id || '').toUpperCase().trim();
  if (!roomId || !code) throw new Error('Missing params');

  const roomRef = db.collection(ROOMS_COL).doc(roomId);
  return db.runTransaction(async tx => {
    const snap = await tx.get(roomRef);
    if (!snap.exists) throw new Error('Room not found');
    const room = snap.data();

    const pidx = (room.players || []).findIndex(p => p.code === code);
    if (pidx < 0) throw new Error('Player not found');
    const name = room.players[pidx].name || 'Player';

    const brbUsed = room.brb_used || {};
    if (brbUsed[pidx]) throw new Error('You already used your BRB break');

    const now = _nowSec();
    if ((room.paused_until || 0) > now) throw new Error('Test is already paused');
    if (room.status !== 'active') throw new Error('Test is not active');

    room.paused_until = now + 300;
    room.total_paused_sec = (room.total_paused_sec || 0) + 300;
    brbUsed[pidx] = true;
    room.brb_used = brbUsed;

    const chat = room.chat || [];
    chat.push({ from: '⏸ System', msg: name + ' used BRB — test paused for 5 minutes', ts: now });
    room.chat = chat;
    room.last_updated = now;

    tx.update(roomRef, room);
    return { success: true, paused_until: room.paused_until };
  });
}

async function _fsRevealAnswer(payload) {
  const roomId = (payload.room_id || '').trim();
  const code   = (payload.player_id || '').toUpperCase().trim();
  const qid    = (payload.q_id || '').trim();
  const level  = parseInt(payload.level || 1, 10);
  if (!roomId || !qid) throw new Error('Missing params');

  const roomRef = db.collection(ROOMS_COL).doc(roomId);
  return db.runTransaction(async tx => {
    const snap = await tx.get(roomRef);
    if (!snap.exists) throw new Error('Room not found');
    const room = snap.data();

    const requesterIdx = (room.players || []).findIndex(p => p.code === code);
    const playerCount = (room.players || []).length;

    if (playerCount <= 1) {
      // Instant reveal for solo
      const current = (room.revealed || {})[qid] || 0;
      if (level > current) {
        room.revealed = room.revealed || {};
        room.revealed[qid] = level;
      }
      room.last_updated = _nowSec();
      tx.update(roomRef, { revealed: room.revealed, last_updated: room.last_updated });
      return { success: true, revealed_level: room.revealed[qid], mode: 'instant' };
    } else {
      // Voting for multi-player
      room.pending_reveal = {
        qid, level, requester_idx: requesterIdx,
        requester_name: (room.players[requesterIdx] || {}).name || 'Player',
        requested_at: _nowSec(), votes: {},
      };
      room.last_updated = _nowSec();
      tx.update(roomRef, { pending_reveal: room.pending_reveal, last_updated: room.last_updated });
      return { success: true, mode: 'voting', qid };
    }
  });
}

async function _fsRespondReveal(payload) {
  const roomId = (payload.room_id || '').trim();
  const code   = (payload.player_id || '').toUpperCase().trim();
  const accept = !!payload.accept;
  if (!roomId || !code) throw new Error('Missing params');

  const codeSnap = await db.collection(CODES_COL).doc(code).get();
  if (!codeSnap.exists) throw new Error('Invalid player');
  const voterIdx = codeSnap.data().player_idx;

  const roomRef = db.collection(ROOMS_COL).doc(roomId);
  return db.runTransaction(async tx => {
    const snap = await tx.get(roomRef);
    if (!snap.exists) throw new Error('Room not found');
    const room = snap.data();

    if (!room.pending_reveal) throw new Error('No pending reveal request');

    room.pending_reveal.votes[voterIdx] = accept;

    if (!accept) {
      room.pending_reveal = null;
      room.last_updated = _nowSec();
      tx.update(roomRef, { pending_reveal: null, last_updated: room.last_updated });
      return { success: true, result: 'rejected' };
    }

    // Check if all non-requester players voted
    const requesterIdx = room.pending_reveal.requester_idx;
    let allVoted = true, allAccepted = true;
    room.players.forEach((p, i) => {
      if (i === requesterIdx) return;
      if (room.pending_reveal.votes[i] == null) allVoted = false;
      else if (!room.pending_reveal.votes[i]) allAccepted = false;
    });

    let result = 'waiting';
    if (allVoted && allAccepted) {
      const qid = room.pending_reveal.qid;
      const lvl = room.pending_reveal.level;
      room.revealed = room.revealed || {};
      const current = room.revealed[qid] || 0;
      if (lvl > current) room.revealed[qid] = lvl;
      room.pending_reveal = null;
      result = 'revealed';
    }
    room.last_updated = _nowSec();
    tx.update(roomRef, room);
    return { success: true, result };
  });
}

// ══════════════════════════════════════════════════════════════
//  TEST TEMPLATE OPERATIONS (for test.view.php dashboard)
// ══════════════════════════════════════════════════════════════

async function _fsCheckTest(payload) {
  const testName = (payload.test_name || '').trim();
  if (!testName) return { exists: false };
  const snap = await db.collection(TESTS_COL).doc(testName).get();
  if (!snap.exists) return { exists: false };
  const data = snap.data();
  return {
    exists: true,
    q_count: Object.keys(data.responses || {}).length,
    page_map: data.page_map || null,
  };
}

async function _fsSaveTest(payload) {
  const testName = (payload.test_name || '').trim();
  const jsonData = payload.json_data || null;
  if (!testName) throw new Error('Test name required');
  if (!jsonData || !jsonData.responses || Object.keys(jsonData.responses).length === 0) {
    throw new Error('Valid JSON with "responses" object required');
  }
  // Add metadata
  jsonData.saved_at = _nowSec();
  jsonData.saved_by = (auth.currentUser && auth.currentUser.uid) || null;
  await db.collection(TESTS_COL).doc(testName).set(jsonData, { merge: true });
  return { success: true, name: testName, q_count: Object.keys(jsonData.responses).length };
}

async function _fsListTests(payload) {
  const uid = (auth.currentUser && auth.currentUser.uid) || null;
  const snap = await db.collection(TESTS_COL).get();
  const tests = [];
  snap.forEach(doc => {
    const data = doc.data();
    if (!data.responses) return;
    tests.push({
      name: doc.id,
      q_count: Object.keys(data.responses || {}).length,
      test_info: data.test_info || {},
      has_pdf: !!data.pdf_url,
      has_solution_pdf: !!data.solution_pdf_url,
      has_page_map: !!data.page_map,
      created_at: data.saved_at || 0,
    });
  });
  tests.sort((a, b) => b.created_at - a.created_at);
  return { tests };
}

async function _fsUpdateTestTag(payload) {
  const testName = (payload.test_name || '').trim();
  const newTag   = (payload.new_tag || '').trim();
  if (!testName || !newTag) throw new Error('Test name and new tag required');

  const ref = db.collection(TESTS_COL).doc(testName);
  const snap = await ref.get();
  if (!snap.exists) throw new Error('Test not found');

  await ref.update({ 'test_info.tag': newTag });
  return { success: true, test_name: testName, new_tag: newTag };
}

async function _fsSavePageMap(payload) {
  const testName = (payload.test_name || '').trim();
  const pageMap  = payload.page_map || null;
  if (!testName) throw new Error('Test name required');
  if (!pageMap || typeof pageMap !== 'object') throw new Error('page_map must be an object');

  const ref = db.collection(TESTS_COL).doc(testName);
  const snap = await ref.get();
  if (!snap.exists) throw new Error('Test not found');

  await ref.update({ page_map: pageMap });
  return { success: true, test_name: testName, pages_mapped: Object.keys(pageMap).length };
}

// ══════════════════════════════════════════════════════════════
//  MASTER API ROUTER — drop-in replacement for fetch('api.php')
// ══════════════════════════════════════════════════════════════

async function api(payload) {
  const action = (payload.action || '').trim();
  switch (action) {
    // Room
    case 'create_room':         return _fsCreateRoom(payload);
    case 'get_room':            return _fsGetRoom(payload);
    case 'check_recent_rooms':  return _fsCheckRecentRooms(payload);
    case 'update_call_state':   return _fsUpdateCallState(payload);
    // Player
    case 'validate_code':       return _fsValidateCode(payload);
    case 'player_join':         return _fsPlayerJoin(payload);
    case 'submit_player':       return _fsSubmitPlayer(payload);
    case 'start_reattempt':     return _fsStartReattempt(payload);
    case 'rename_player':       return _fsRenamePlayer(payload);
    // Exam
    case 'sync':                return _fsSync(payload);
    case 'update_answer':       return _fsUpdateAnswer(payload);
    case 'update_current_q':    return _fsUpdateCurrentQ(payload);
    case 'start_test':          return _fsStartTest(payload);
    case 'send_message':        return _fsSendMessage(payload);
    case 'update_player_status':return _fsUpdatePlayerStatus(payload);
    case 'brb':                 return _fsBrb(payload);
    case 'reveal_answer':       return _fsRevealAnswer(payload);
    case 'respond_reveal':      return _fsRespondReveal(payload);
    // Test management
    case 'check_test':          return _fsCheckTest(payload);
    case 'save_test':           return _fsSaveTest(payload);
    case 'list_tests':          return _fsListTests(payload);
    case 'update_test_tag':     return _fsUpdateTestTag(payload);
    case 'save_page_map':       return _fsSavePageMap(payload);
    // Cleanup is now handled by Firestore TTL or manual — no-op
    case 'cleanup':             return { cleaned: 0 };
    default:
      throw new Error('Unknown action: ' + action);
  }
}
