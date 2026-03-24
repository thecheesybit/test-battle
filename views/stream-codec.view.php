<!-- stream-codec.php — GetStream Video/Voice Panel for OMR Battle Room -->
<!-- Included by room.php. Outputs: CSS + JS. The HTML scaffold is placed directly in room.php sidebar. -->

<style>
/* ══════════════════════════════════════
   STREAM VIDEO PANEL
══════════════════════════════════════ */

.stream-panel {
  background: var(--surface);
  display: flex;
  flex-direction: column;
  flex: 1;
  min-height: 0;
  overflow: hidden;
}

/* Video grid */
.stream-video-grid {
  display: flex;
  flex-direction: column;
  gap: 6px;
  padding: 8px;
  flex: 1;
  min-height: 0;
  overflow: hidden;
}
.stream-tile {
  position: relative;
  background: #000;
  border-radius: 8px;
  overflow: hidden;
  flex: 1;
  min-height: 0;
  display: flex;
  align-items: center;
  justify-content: center;
}
.stream-tile video {
  width: 100%; height: 100%;
  object-fit: contain;
  display: block;
}
.stream-tile audio {
  display: none;
}
.stream-tile.speaking {
  box-shadow: 0 0 0 2px var(--p0);
}
.stream-tile-name {
  position: absolute; bottom: 4px; left: 6px;
  font-size: 0.65rem; font-weight: 600;
  color: #fff;
  background: rgba(0,0,0,0.55);
  padding: 2px 6px; border-radius: 4px;
  pointer-events: none;
}
.stream-tile-quality {
  position: absolute; top: 5px; right: 5px;
  width: 7px; height: 7px; border-radius: 50%;
}
.stream-tile-quality.good   { background: var(--ok); }
.stream-tile-quality.medium { background: var(--warn); }
.stream-tile-quality.poor   { background: var(--danger); }

/* Tile placeholder when camera is off */
.stream-tile-avatar {
  position: absolute; inset: 0;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.5rem; font-weight: 800; color: var(--muted);
  background: var(--surface2);
  z-index: 1;
}
.stream-tile-avatar.hidden { display: none; }

/* Mute Indicator overlay */
.stream-tile-mute {
  position: absolute; top: 6px; left: 6px;
  width: 22px; height: 22px; border-radius: 50%;
  background: rgba(239, 68, 68, 0.9);
  color: #fff;
  display: flex; align-items: center; justify-content: center;
  z-index: 5;
}
.stream-tile-mute svg { width: 13px; height: 13px; }
.stream-tile-mute.hidden { display: none; }

/* Controls bar */
.stream-controls {
  padding: 8px;
  display: flex; gap: 8px;
  background: var(--surface2);
  align-items: center;
  justify-content: center;
  padding-right: 70px; /* Leave space for tools-fab */
}
.stream-ctrl-btn {
  width: 32px; height: 32px;
  border-radius: 8px;
  border: 1px solid var(--border);
  background: var(--surface2);
  color: var(--muted);
  cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  font-size: 0.75rem; font-family: "Syne", sans-serif;
  transition: all 0.15s ease;
  flex-shrink: 0;
}
.stream-ctrl-btn svg { width: 16px; height: 16px; }
.stream-ctrl-btn:hover { color: var(--text); border-color: rgba(255,255,255,0.2); }
.stream-ctrl-btn.active { background: var(--p0); border-color: var(--p0); color: #000; }
.stream-ctrl-btn.danger { background: var(--danger); border-color: var(--danger); color: #fff; }
.stream-ctrl-btn.stream-leave { margin-left: auto; }
.stream-ctrl-btn.hidden { display: none; }

/* Call banner */
.stream-call-banner {
  background: var(--surface2);
  border-bottom: 1px solid var(--border);
  padding: 10px 12px;
  display: flex; align-items: center; justify-content: space-between; gap: 8px;
  animation: sc-slideDown 0.25s ease;
}
@keyframes sc-slideDown { from { transform: translateY(-100%); opacity: 0; } to { transform: none; opacity: 1; } }
.stream-call-banner.hidden { display: none; }
.stream-banner-info { display: flex; align-items: center; gap: 8px; }
.stream-banner-avatar {
  width: 30px; height: 30px; border-radius: 50%;
  background: var(--p1-dim); color: var(--p1);
  display: flex; align-items: center; justify-content: center;
  font-weight: 700; font-size: 0.85rem;
}
.stream-banner-name { font-size: 0.82rem; font-weight: 700; }
.stream-banner-sub  { font-size: 0.7rem; color: var(--muted); }
.stream-banner-actions { display: flex; gap: 6px; }
.stream-btn-accept {
  padding: 5px 12px; border-radius: 7px; border: none;
  background: var(--ok); color: #000; font-weight: 700;
  font-size: 0.75rem; cursor: pointer; transition: all 0.15s;
}
.stream-btn-accept:hover { filter: brightness(1.15); }
.stream-btn-decline {
  padding: 5px 12px; border-radius: 7px;
  border: 1px solid var(--danger); background: transparent;
  color: var(--danger); font-size: 0.75rem; cursor: pointer;
  transition: all 0.15s;
}
.stream-btn-decline:hover { background: rgba(255,71,87,0.1); }

/* Ringing pulse animation */
@keyframes sc-ring-pulse {
  0%, 100% { box-shadow: 0 0 0 0 rgba(79,255,176,0.4); }
  50% { box-shadow: 0 0 0 6px rgba(79,255,176,0); }
}
.stream-ctrl-btn.ringing {
  animation: sc-ring-pulse 1.5s ease infinite;
  border-color: var(--ok);
  color: var(--ok);
}

/* Status indicator */
.stream-status {
  font-size: 0.6rem; color: var(--muted);
  font-family: 'JetBrains Mono', monospace;
  padding: 3px 8px; text-align: center;
  letter-spacing: 0.5px;
}
.stream-status.connected { color: var(--ok); }
.stream-status.connecting { color: var(--warn); }


</style>

<script type="module">
// ══════════════════════════════════════════════════════════════
//  StreamCodec — GetStream Video/Voice Controller
//  Uses polling-based call notification via api.php sync
// ══════════════════════════════════════════════════════════════

const StreamCodec = (() => {
  // ── State ──
  let _client = null;
  let _call = null;
  let _roomId = null;
  let _playerCode = null;
  let _playerName = null;
  let _userId = null;
  let _callId = null;
  let _apiKey = null;
  let _inCall = false;
  let _micOn = true;
  let _camOn = true;
  let _spkOn = true;
  let _hdMode = false;
  let _sdkLoaded = false;
  let _StreamVideoClient = null;
  let _bannerTimeout = null;
  let _lastCallActiveId = null; // track which call we've already shown banner for

  // Binding caches for cleanup
  const _videoBindings = new Map();
  const _videoTracking = new Map();
  const _audioBindings = new Map();

  // ── SVG Icons ──
  const ICONS = {
    mic:     '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><line x1="12" y1="19" x2="12" y2="22"/></svg>',
    micOff:  '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="1" y1="1" x2="23" y2="23"/><path d="M9 9v3a3 3 0 0 0 5.12 2.12M15 9.34V5a3 3 0 0 0-5.94-.6"/><path d="M17 16.95A7 7 0 0 1 5 12v-2m14 0v2c0 .76-.13 1.48-.35 2.17"/><line x1="12" y1="19" x2="12" y2="22"/></svg>',
    cam:     '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>',
    camOff:  '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="1" y1="1" x2="23" y2="23"/><path d="M21 21H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h3m3-3h6l2 3h4a2 2 0 0 1 2 2v9.34m-7.72-2.06a4 4 0 1 1-5.56-5.56"/></svg>',
    speaker: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"/></svg>',
    speakerOff: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><line x1="23" y1="9" x2="17" y2="15"/><line x1="17" y1="9" x2="23" y2="15"/></svg>',
    call: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.12 4.11 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91"/></svg>',
    leave: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.68 13.31a16 16 0 0 0 3.41 2.6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.12 4.11 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91"/><line x1="23" y1="1" x2="1" y2="23"/></svg>',
    chevUp:  '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"/></svg>',
    chevDn:  '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>',
  };

  // ── DOM helpers ──
  const $ = id => document.getElementById(id);
  const show = el => { if (el) el.classList.remove('hidden'); };
  const hide = el => { if (el) el.classList.add('hidden'); };

  // ── Load SDK from CDN (with fallbacks) ──
  const CDN_URLS = [
    'https://esm.sh/@stream-io/video-client',
    'https://cdn.skypack.dev/@stream-io/video-client',
    'https://cdn.jsdelivr.net/npm/@stream-io/video-client/+esm',
  ];

  async function _loadSDK() {
    if (_sdkLoaded) return;
    let lastErr = null;
    for (const url of CDN_URLS) {
      try {
        console.log('[StreamCodec] Trying SDK from:', url);
        const mod = await import(url);
        _StreamVideoClient = mod.StreamVideoClient || mod.default?.StreamVideoClient;
        if (_StreamVideoClient) {
          _sdkLoaded = true;
          console.log('[StreamCodec] SDK loaded from:', url);
          return;
        }
        console.warn('[StreamCodec] Module loaded but StreamVideoClient not found. Exports:', Object.keys(mod));
        lastErr = new Error('StreamVideoClient export not found');
      } catch (err) {
        console.warn('[StreamCodec] Failed from', url, ':', err.message || err);
        lastErr = err;
      }
    }
    console.error('[StreamCodec] All CDN sources failed:', lastErr);
    if (typeof showToast === 'function') showToast('Failed to load video SDK', 'error');
    throw lastErr;
  }

  // ── Fetch token from server ──
  async function _fetchToken() {
    const resp = await fetch('stream.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        action: 'get_token',
        room_id: _roomId,
        player_id: _playerCode,
        player_name: _playerName,
      })
    });
    const data = await resp.json();
    if (data.error) throw new Error(data.error);
    return data;
  }

  // ── Notify other players that a call is active ──
  async function _setCallActive(active) {
    try {
      await fetch('api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          action: 'update_call_state',
          room_id: _roomId,
          player_id: _playerCode,
          active: active,
        })
      });
    } catch(e) { console.warn('[StreamCodec] setCallActive error:', e); }
  }

  // ── Update live badge on sidebar tab ──
  function _updateLiveBadge(live) {
    const badge = $('video-live-badge');
    if (badge) badge.className = 'tool-badge' + (live ? ' live' : '');
  }

  // ── Update button UI ──
  function _updateControls() {
    const micBtn = $('sc-mic');
    const camBtn = $('sc-cam');
    const spkBtn = $('sc-speaker');
    const hdBtn = $('sc-hd');
    const callBtn = $('sc-call');
    const leaveBtn = $('sc-leave');

    if (micBtn) {
      micBtn.innerHTML = _micOn ? ICONS.mic : ICONS.micOff;
      micBtn.className = 'stream-ctrl-btn' + (_micOn ? ' active' : '');
    }
    if (camBtn) {
      camBtn.innerHTML = _camOn ? ICONS.cam : ICONS.camOff;
      camBtn.className = 'stream-ctrl-btn' + (_camOn ? ' active' : '');
    }
    if (spkBtn) {
      spkBtn.innerHTML = _spkOn ? ICONS.speaker : ICONS.speakerOff;
      spkBtn.className = 'stream-ctrl-btn' + (_spkOn ? ' active' : '');
    }
    if (hdBtn) {
      hdBtn.textContent = _hdMode ? 'HD' : 'SD';
      hdBtn.className = 'stream-ctrl-btn' + (_hdMode ? ' active' : '');
    }
    if (callBtn) {
      if (_inCall) {
        hide(callBtn);
      } else {
        show(callBtn);
        callBtn.innerHTML = ICONS.call;
        callBtn.className = 'stream-ctrl-btn active'; // Highlight it slightly to make it obvious
        callBtn.style.color = 'var(--ok)'; // Green icon for calling
        callBtn.style.borderColor = 'var(--ok)';
      }
    }
    if (leaveBtn) {
      if (_inCall) {
        show(leaveBtn);
        leaveBtn.innerHTML = ICONS.leave;
      } else {
        hide(leaveBtn);
      }
    }
    // Update live badge
    _updateLiveBadge(_inCall);
  }

  // ── Render participants ──
  function _setupParticipantWatcher() {
    if (!_call) return;
    const grid = $('stream-video-grid');
    if (!grid) return;

    // Set viewport for visibility tracking
    try { _call.setViewport(grid); } catch(e) {}

    _call.state.participants$.subscribe((rawParticipants) => {
      // Deduplicate participants by userId to prevent "ghost" clones (e.g., from unclosed mobile transfers)
      const uniqueParticipants = [];
      const seenUserIds = new Set();
      
      // Iterate backwards to prioritize newly joined sessions over stale ghosts
      for (let i = rawParticipants.length - 1; i >= 0; i--) {
        const p = rawParticipants[i];
        if (!seenUserIds.has(p.userId)) {
          seenUserIds.add(p.userId);
          uniqueParticipants.push(p);
        }
      }

      // Render / update each unique participant
      uniqueParticipants.forEach(p => {
        _renderParticipant(p, grid);
      });

      // Remove stale tiles
      grid.querySelectorAll('.stream-tile').forEach(tileEl => {
        const sid = tileEl.dataset.sessionId;
        if (sid && !uniqueParticipants.find(p => p.sessionId === sid)) {
          _cleanupParticipant(sid);
          tileEl.remove();
        }
      });
    });

    // Dominant speaker
    if (_call.state.dominantSpeaker$) {
      _call.state.dominantSpeaker$.subscribe((speaker) => {
        grid.querySelectorAll('.stream-tile').forEach(t => t.classList.remove('speaking'));
        if (speaker) {
          const tile = grid.querySelector(`[data-session-id="${speaker.sessionId}"]`);
          if (tile) tile.classList.add('speaking');
        }
      });
    }
  }

  function _renderParticipant(participant, container) {
    const sid = participant.sessionId;
    const tileId = `stream-tile-${sid}`;
    let tile = $(tileId);

    if (!tile) {
      tile = document.createElement('div');
      tile.className = 'stream-tile';
      tile.id = tileId;
      tile.dataset.sessionId = sid;

      // Avatar placeholder
      const avatar = document.createElement('div');
      avatar.className = 'stream-tile-avatar';
      avatar.textContent = (participant.name || participant.userId || '?').charAt(0).toUpperCase();
      avatar.id = `stream-avatar-${sid}`;
      tile.appendChild(avatar);

      // Video element
      const video = document.createElement('video');
      video.id = `stream-vid-${sid}`;
      video.autoplay = true;
      video.playsInline = true;
      video.dataset.sessionId = sid;
      if (participant.isLocalParticipant) video.muted = true;
      tile.appendChild(video);

      // Audio element (remote only)
      if (!participant.isLocalParticipant) {
        const audio = document.createElement('audio');
        audio.id = `stream-aud-${sid}`;
        audio.autoplay = true;
        audio.playsInline = true;
        audio.dataset.sessionId = sid;
        tile.appendChild(audio);
      }

      // Name badge
      const nameEl = document.createElement('div');
      nameEl.className = 'stream-tile-name';
      nameEl.textContent = participant.name || participant.userId || 'User';
      tile.appendChild(nameEl);

      // Mute indicator icon
      const muteIcon = document.createElement('div');
      muteIcon.className = 'stream-tile-mute hidden';
      muteIcon.id = `stream-mute-${sid}`;
      muteIcon.innerHTML = ICONS.micOff;
      tile.appendChild(muteIcon);

      // Quality dot
      const qDot = document.createElement('div');
      qDot.className = 'stream-tile-quality good';
      qDot.id = `stream-q-${sid}`;
      tile.appendChild(qDot);

      container.appendChild(tile);

      // Bind video
      try {
        const untrack = _call.trackElementVisibility(video, sid, 'videoTrack');
        _videoTracking.set(sid, untrack);
        const unbind = _call.bindVideoElement(video, sid, 'videoTrack');
        _videoBindings.set(sid, unbind);
      } catch (e) { console.warn('[StreamCodec] Video bind error:', e); }

      // Bind audio (remote only)
      if (!participant.isLocalParticipant) {
        try {
          const audioEl = $(`stream-aud-${sid}`);
          if (audioEl) {
            const unbind = _call.bindAudioElement(audioEl, sid);
            _audioBindings.set(sid, unbind);
          }
        } catch (e) { console.warn('[StreamCodec] Audio bind error:', e); }
      }
    }

    // Update avatar visibility based on video track (2)
    const avatar = $(`stream-avatar-${sid}`);
    if (avatar) {
      const hasVideo = participant.publishedTracks?.includes(2);
      avatar.classList.toggle('hidden', !!hasVideo);
    }

    // Update mute icon visibility based on audio track (1)
    const muteIcon = $(`stream-mute-${sid}`);
    if (muteIcon) {
      const hasAudio = participant.publishedTracks?.includes(1);
      muteIcon.classList.toggle('hidden', !!hasAudio);
    }

    // Apply speaker mute
    if (!participant.isLocalParticipant) {
      const audioEl = $(`stream-aud-${sid}`);
      if (audioEl) audioEl.muted = !_spkOn;
    }
  }

  function _cleanupParticipant(sessionId) {
    const unbindVideo = _videoBindings.get(sessionId);
    if (unbindVideo) { unbindVideo(); _videoBindings.delete(sessionId); }
    const untrackVideo = _videoTracking.get(sessionId);
    if (untrackVideo) { untrackVideo(); _videoTracking.delete(sessionId); }
    const unbindAudio = _audioBindings.get(sessionId);
    if (unbindAudio) { unbindAudio(); _audioBindings.delete(sessionId); }
  }

  function _clearAllTiles() {
    const grid = $('stream-video-grid');
    if (grid) {
      grid.querySelectorAll('.stream-tile').forEach(tile => {
        const sid = tile.dataset.sessionId;
        if (sid) _cleanupParticipant(sid);
        tile.remove();
      });
    }
  }

  function _setStatus(text, cls) {
    const el = $('stream-status');
    if (el) {
      el.textContent = text;
      el.className = 'stream-status' + (cls ? ' ' + cls : '');
    }
  }

  // ══════════════════════════════════════════════════════════════
  //  INTERNAL: Join a call (shared by startCall + acceptCall)
  // ══════════════════════════════════════════════════════════════
  async function _joinCall(isInitiator, isViewerOnly = false) {
    _setStatus(isInitiator ? 'Starting call…' : 'Joining call…', 'connecting');

    try {
      // Ensure SDK + client ready
      if (!_client) {
        await _loadSDK();
        const tokenData = await _fetchToken();
        _apiKey = tokenData.api_key;
        _client = new _StreamVideoClient({
          apiKey: _apiKey,
          token: tokenData.token,
          user: { id: _userId, name: _playerName },
        });
      }

      _call = _client.call('default', _callId);

      // Join (create if doesn't exist) — NO ring, just join
      await _call.join({ create: true });
      _inCall = true;

      // Enable camera and mic unless joining as viewer only (i.e. declined to participate actively)
      if (!isViewerOnly) {
        try { 
          await _call.camera.enable(); 
          if (!_hdMode) await _call.camera.setPreferredResolution({ width: 640, height: 480 });
          _camOn = true; 
        } catch(e) { 
          _camOn = false; console.warn('[StreamCodec] Camera unavailable:', e.message); 
        }

        try { await _call.microphone.enable(); _micOn = true; }
        catch(e) { _micOn = false; console.warn('[StreamCodec] Mic unavailable:', e.message); }
      } else {
        _camOn = false;
        _micOn = false;
      }

      // Switch sidebar to video pane
      if (typeof switchSidebarTool === 'function') switchSidebarTool('video');

      _setupParticipantWatcher();
      _updateControls();
      _setStatus('In call', 'connected');

      // If initiator, notify other players via API
      if (isInitiator) {
        await _setCallActive(true);
      }

      if (typeof showToast === 'function') {
        if (isViewerOnly) showToast('📞 Viewing call', 'info');
        else showToast('📞 ' + (isInitiator ? 'Call started' : 'Joined call'), 'ok');
      }
    } catch (err) {
      console.error('[StreamCodec] joinCall error:', err);
      _setStatus('Call failed — ' + (err.message || ''), '');
      if (typeof showToast === 'function') showToast('Call failed: ' + (err.message || err), 'error');
    }
  }

  // ══════════════════════════════════════════════════════════════
  //  PUBLIC API
  // ══════════════════════════════════════════════════════════════
  const api = {};

  /**
   * init(roomId, playerCode, playerName)
   * Called from enterExam(). Loads SDK & shows the call button.
   */
  api.init = async function(roomId, playerCode, playerName) {
    _roomId = roomId;
    _playerCode = playerCode;
    _playerName = playerName;
    _callId = 'omr-' + roomId;
    _userId = 'omr-' + playerCode;

    console.log('[StreamCodec] Initializing for room', roomId, 'player', playerCode);

    _setStatus('Loading…', 'connecting');

    try {
      await _loadSDK();

      // Pre-fetch token and create client so we're ready
      const tokenData = await _fetchToken();
      _apiKey = tokenData.api_key;
      _client = new _StreamVideoClient({
        apiKey: _apiKey,
        token: tokenData.token,
        user: { id: _userId, name: _playerName },
      });

      _setStatus('Ready', 'connected');
      _updateControls();
      console.log('[StreamCodec] Ready');
    } catch (err) {
      console.error('[StreamCodec] Init failed:', err);
      _setStatus('Video unavailable', '');
    }
  };

  /**
   * startCall() — Initiate a call (from the 📹 Video Call button)
   */
  api.startCall = async function() {
    if (_inCall) return;
    await _joinCall(true);
  };

  /**
   * acceptCall() — Accept incoming call (from banner)
   */
  api.acceptCall = async function() {
    hide($('stream-call-banner'));
    if (_bannerTimeout) { clearTimeout(_bannerTimeout); _bannerTimeout = null; }
    if (_inCall) return;
    await _joinCall(false);
  };

  /**
   * declineCall() — Dismiss incoming call banner and join as Viewer instead of participant
   */
  api.declineCall = async function() {
    hide($('stream-call-banner'));
    if (_bannerTimeout) { clearTimeout(_bannerTimeout); _bannerTimeout = null; }
    if (_inCall) return;
    await _joinCall(false, true); // Join as viewer only
  };

  /**
   * leaveCall() — Leave the current call
   */
  api.leaveCall = async function() {
    if (!_call) return;
    try { await _call.leave(); } catch(e) { console.warn('[StreamCodec] leave error:', e); }

    _clearAllTiles();
    _call = null;
    _inCall = false;
    _micOn = true;
    _camOn = true;

    // Clear call active state on server
    await _setCallActive(false);

    _setStatus('Ready', 'connected');
    _updateControls();
    if (typeof showToast === 'function') showToast('📞 Left call', 'info');
  };

  /** toggleMic() */
  api.toggleMic = async function() {
    if (!_call) return;
    try {
      if (_micOn) await _call.microphone.disable();
      else await _call.microphone.enable();
      _micOn = !_micOn;
      _updateControls();
    } catch (e) {
      console.warn('[StreamCodec] mic toggle error:', e);
      if (typeof showToast === 'function') showToast('Mic error: ' + e.message, 'error');
    }
  };

  /** toggleCam() */
  api.toggleCam = async function() {
    if (!_call) return;
    try {
      if (_camOn) await _call.camera.disable();
      else await _call.camera.enable();
      _camOn = !_camOn;
      _updateControls();
    } catch (e) {
      console.warn('[StreamCodec] cam toggle error:', e);
      if (typeof showToast === 'function') showToast('Camera error: ' + e.message, 'error');
    }
  };

  /** toggleSpk() — Mute/unmute all incoming audio */
  api.toggleSpk = function() {
    _spkOn = !_spkOn;
    document.querySelectorAll('#stream-video-grid audio').forEach(el => { el.muted = !_spkOn; });
    _updateControls();
  };

  /** toggleHD() — Switch SD/HD */
  api.toggleHD = async function() {
    if (!_call) return;
    _hdMode = !_hdMode;
    try {
      const res = _hdMode ? { width: 1280, height: 720 } : { width: 854, height: 480 };
      await _call.camera.setPreferredResolution(res);
    } catch (e) { console.warn('[StreamCodec] resolution error:', e); }
    _updateControls();
  };


  /**
   * showIncomingBanner(callerName)
   */
  api.showIncomingBanner = function(callerName) {
    const banner = $('stream-call-banner');
    const nameEl = $('stream-banner-name');
    const avatarEl = $('stream-banner-avatar');

    if (nameEl) nameEl.textContent = callerName;
    if (avatarEl) avatarEl.textContent = (callerName || '?').charAt(0).toUpperCase();
    show(banner);

    // Ring sound
    if (typeof sfxRingingAlert === 'function') {
      sfxRingingAlert();
    } else {
      // Fallback for mobile view where sfx might not be loaded yet
      try {
        const ctx = new (window.AudioContext || window.webkitAudioContext)();
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.connect(gain); gain.connect(ctx.destination);
        osc.frequency.value = 440; gain.gain.value = 0.15;
        osc.start(); osc.stop(ctx.currentTime + 0.2);
        setTimeout(() => {
          try {
            const osc2 = ctx.createOscillator();
            const gain2 = ctx.createGain();
            osc2.connect(gain2); gain2.connect(ctx.destination);
            osc2.frequency.value = 554; gain2.gain.value = 0.15;
            osc2.start(); osc2.stop(ctx.currentTime + 0.25);
          } catch(e) {}
        }, 250);
      } catch(e) {}
    }

    // Auto-switch to video pane to show the banner
    if (typeof switchSidebarTool === 'function') switchSidebarTool('video');

    // Auto-dismiss after 30s
    if (_bannerTimeout) clearTimeout(_bannerTimeout);
    _bannerTimeout = setTimeout(() => {
      hide(banner);
    }, 30000);
  };

  /**
   * showTransferModal()
   */
  api.showTransferModal = function() {
    let modal = document.getElementById('sc-transfer-modal');
    if (!modal) {
      modal = document.createElement('div');
      modal.id = 'sc-transfer-modal';
      modal.className = 'modal-overlay';
      modal.innerHTML = `
        <div class="modal" style="max-width:320px;text-align:center;">
          <div class="modal-title">Transfer to Mobile</div>
          <div class="modal-sub">Scan to switch your camera and mic to your phone</div>
          <div style="background:#fff;padding:10px;border-radius:12px;display:inline-block;margin-bottom:1.5rem;">
            <img id="sc-qr-img" src="" style="width:200px;height:200px;display:block;">
          </div>
          <div class="modal-actions" style="flex-direction:column;gap:.5rem;">
            <button class="btn-modal cancel" onclick="document.getElementById('sc-transfer-modal').classList.remove('open')">Close</button>
          </div>
        </div>
      `;
      document.body.appendChild(modal);
    }
    
    // Construct the mobile URL
    let baseUrl = window.location.href.split('?')[0];
    baseUrl = baseUrl.replace('room.php', 'mobile.php');
    const msid = Math.random().toString(36).substr(2, 9);
    const transferUrl = baseUrl + '?room_id=' + encodeURIComponent(_roomId) + '&player_id=' + encodeURIComponent(_playerCode) + '&msid=' + msid;
    
    document.getElementById('sc-qr-img').src = 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=' + encodeURIComponent(transferUrl);
    modal.classList.add('open');
  };

  /**
   * _checkCallState(callActive) — Called from syncAndUpdate polling
   * Detects when another player started a call and shows incoming banner
   */
  api._checkCallState = function(callActive) {
    if (!callActive || !callActive.active) {
      _lastCallActiveId = null;
      return;
    }

    // If this call state has a different started_at than what we've seen, it's new
    const callKey = callActive.caller_code + '_' + callActive.started_at;
    if (callKey === _lastCallActiveId) return; // Already shown

    // Don't show banner for our own call
    if (callActive.caller_code === _playerCode) {
      _lastCallActiveId = callKey;
      return;
    }

    // Don't show if already in a call
    if (_inCall) {
      _lastCallActiveId = callKey;
      return;
    }

    _lastCallActiveId = callKey;
    _callId = callActive.call_id || ('omr-' + _roomId);

    console.log('[StreamCodec] Incoming call from:', callActive.caller_name);
    api.showIncomingBanner(callActive.caller_name);
  };

  /**
   * destroy() — Cleanup
   */
  api.destroy = async function() {
    if (_call) { try { await _call.leave(); } catch(e) {} }
    if (_client) { try { _client.disconnectUser(); } catch(e) {} }
    _clearAllTiles();
    _call = null;
    _client = null;
    _inCall = false;
  };

  /**
   * handleMobileTransfer()
   */
  api.handleMobileTransfer = async function() {
      if (_call) { try { await _call.leave(); } catch(e) {} }
      if (_client) { try { await _client.disconnectUser(); } catch(e) {} }
      _clearAllTiles();
      _call = null;
      _client = null;
      _inCall = false;
      document.getElementById('stream-video-grid').innerHTML = 
          '<div style="flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;color:var(--muted);text-align:center;">' +
          '<div style="font-size:2rem;margin-bottom:1rem;">📱</div>' +
          '<div style="font-weight:700;">Transferred to Mobile</div><div style="font-size:.75rem;margin-top:.4rem;line-height:1.4;">Your camera and microphone are now<br>operating on your phone.</div></div>';
      
      const modal = document.getElementById('sc-transfer-modal');
      if (modal) modal.classList.remove('open');
      
      const st = document.getElementById('stream-status');
      if (st) {
          st.textContent = 'Mobile Active';
          st.className = 'stream-status connected';
      }
      hide(document.getElementById('stream-call-banner'));
      
      // Hide buttons gracefully except settings maybe, but mobile has them
      hide($('sc-mic')); hide($('sc-cam')); hide($('sc-speaker')); hide($('sc-call')); hide($('sc-leave'));
  };

  // Expose globally
  window.StreamCodec = api;
  return api;
})();
</script>

