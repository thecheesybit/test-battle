<?php
/**
 * omr-mobile.view.php
 * View file for the dedicated mobile video call interface.
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title>OMR Call — Mobile</title>
<link rel="icon" href="https://minishiksha.in/wp-content/uploads/2025/06/icons8-class-pulsar-gradient-16.png" sizes="any">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=JetBrains+Mono:wght@400;700;800&display=swap" rel="stylesheet">
<style>
/* Reset and core */
:root {
  --bg: #07080f;
  --surface: #0f1018;
  --surface2: #171825;
  --border: #252638;
  --p0: #5b7fff; --p0-dim: rgba(91,127,255,0.12);
  --text: #eaeaf5; --muted: #5a5a7a;
  --danger: #ff4757; --warn: #ffa502; --ok: #4fffb0;
}
* { box-sizing: border-box; }
body { margin: 0; padding: 0; background: var(--bg); color: var(--text); font-family: 'Syne', sans-serif; height: 100vh; display: flex; flex-direction: column; overflow: hidden; }

/* Topbar */
.mobile-topbar { height: 60px; background: var(--surface2); border-bottom: 1px solid var(--border); display: flex; align-items: center; padding: 0 15px; flex-shrink: 0; justify-content: space-between; }
.mobile-brand { font-weight: 800; font-size: 1.1rem; color: var(--p0); }
.mobile-status { font-family: 'JetBrains Mono', monospace; font-size: 0.75rem; color: var(--ok); }

/* Main area (split between video and chat) */
.mobile-main { flex: 1; display: flex; flex-direction: column; min-height: 0; position: relative; }

/* Video area */
.mobile-video-area { flex: 1; display: flex; flex-direction: column; min-height: 0; position: relative; background: #000; border-radius: 0 0 24px 24px; overflow: hidden; box-shadow: 0px 4px 24px rgba(0,0,0,0.5); z-index: 10; }
#stream-video-grid { flex: 1; min-height: 0; padding: 4px; gap: 4px; display: flex; flex-direction: column; }
#stream-video-grid .stream-tile { border-radius: 16px; flex: 1; border: 1px solid rgba(255,255,255,0.05); }

/* Premium Floating Controls */
.stream-controls { 
    position: absolute !important; 
    bottom: 15px !important; 
    left: 0; right: 0; 
    display: flex !important; 
    justify-content: center !important; 
    gap: 15px !important; 
    z-index: 50 !important; 
    background: transparent !important; 
    border: none !important; 
    margin: 0 !important; 
    padding: 10px 0 !important;
    height: auto !important;
}
.stream-controls::before {
    content: ''; position: absolute; bottom: -15px; left: 0; right: 0; height: 120px;
    background: linear-gradient(to top, rgba(0,0,0,0.85) 0%, transparent 100%);
    z-index: -1; pointer-events: none;
}
.stream-ctrl-btn {
    width: 52px !important; height: 52px !important;
    background: rgba(30, 30, 45, 0.6) !important;
    backdrop-filter: blur(12px) !important; -webkit-backdrop-filter: blur(12px) !important;
    border: 1px solid rgba(255,255,255,0.1) !important;
    border-radius: 50% !important; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
}
.stream-ctrl-btn:active { transform: scale(0.92) !important; }
.stream-ctrl-btn.active {
    background: rgba(79, 255, 176, 0.15) !important;
    color: var(--ok) !important; border-color: rgba(79, 255, 176, 0.4) !important;
}
.stream-ctrl-btn.danger { background: rgba(255, 71, 87, 0.9) !important; border-color: transparent !important; }

/* Chat area */
.mobile-chat-area { height: 32vh; max-height: 300px; background: transparent; display: flex; flex-direction: column; flex-shrink: 0; padding-bottom: Env(safe-area-inset-bottom); }
.chat-header { padding: 12px 16px; font-weight: 800; font-size: 0.9rem; color: var(--muted); text-transform: uppercase; letter-spacing: 0.05em; display: flex; justify-content: space-between; align-items: center; }
.chat-messages { flex: 1; overflow-y: auto; padding: 0 12px 10px; display: flex; flex-direction: column; gap: 10px; font-family: sans-serif; font-size: 0.9rem; }
.chat-input-row { display: flex; padding: 10px 12px; background: transparent; }
.chat-input { flex: 1; padding: 12px 16px; border-radius: 24px; border: 1px solid var(--border); background: var(--surface2); color: var(--text); font-size: 0.95rem; outline: none; transition: border-color 0.2s; }
.chat-input:focus { border-color: var(--p0); }
.chat-send { width: 44px; height: 44px; border-radius: 50%; background: var(--p0); border: none; color: #fff; margin-left: 8px; display: flex; align-items: center; justify-content: center; cursor: pointer; flex-shrink: 0; box-shadow: 0 4px 12px var(--p0-dim); }

/* Global loading */
.global-loading { position: fixed; inset: 0; background: var(--bg); display: flex; align-items: center; justify-content: center; z-index: 1000; flex-direction: column; }
.global-loading.hidden { opacity: 0; pointer-events: none; transition: opacity 0.3s ease; }
.spinner { width: 44px; height: 44px; border: 3px solid rgba(255,255,255,0.05); border-top-color: var(--p0); border-radius: 50%; animation: spin 0.8s infinite linear; margin-bottom: 20px; }
@keyframes spin { to { transform: rotate(360deg); } }

/* Helpers from main */
.chat-msg { background: var(--surface2); padding: 10px 14px; border-radius: 18px; border-bottom-left-radius: 4px; align-self: flex-start; max-width: 85%; word-break: break-word; border: 1px solid rgba(255,255,255,0.02); box-shadow: 0 2px 8px rgba(0,0,0,0.2); }
.chat-msg.me { align-self: flex-end; background: var(--p0); border-bottom-left-radius: 18px; border-bottom-right-radius: 4px; border: none; }
.chat-msg.me .chat-from { color: rgba(255,255,255,0.8); }
.chat-msg.me .chat-time { color: rgba(255,255,255,0.6); }
.chat-from { font-size: 0.70rem; color: var(--p0); font-family: 'JetBrains Mono', monospace; text-transform: uppercase; font-weight: 700; margin-bottom: 4px; }
.chat-time { font-size: 0.65rem; color: var(--muted); text-align: right; margin-top: 6px; font-weight: 600; }
</style>
</head>
<body>

<div class="global-loading" id="global-loading">
  <div class="spinner"></div>
  <div style="font-weight:700;">Connecting...</div>
</div>

<div class="mobile-topbar">
  <div class="mobile-brand"><?= htmlspecialchars($myName ?? 'Mobile Player') ?>'s Lobby</div>
  <div class="mobile-status" id="stream-status">Init...</div>
</div>

<div class="mobile-main">
  <!-- Video Panel -->
  <div class="mobile-video-area">
    <div class="stream-call-banner hidden" id="stream-call-banner" style="position:absolute;top:0;left:0;right:0;z-index:100;background:var(--surface2);padding:10px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;">
      <div style="font-size:0.85rem;font-weight:700;"><span id="stream-banner-name">Someone</span> is calling...</div>
      <div>
        <button onclick="StreamCodec.acceptCall()" style="background:var(--ok);border:none;padding:5px 10px;border-radius:4px;font-weight:700;cursor:pointer;">Accept</button>
      </div>
    </div>
    <div class="stream-video-grid" id="stream-video-grid" style="display:flex;flex-wrap:wrap;gap:4px;padding:4px;flex:1;"></div>
    
    <div class="stream-controls">
      <button class="stream-ctrl-btn" id="sc-mic" onclick="StreamCodec.toggleMic()"></button>
      <button class="stream-ctrl-btn" id="sc-cam" onclick="StreamCodec.toggleCam()"></button>
      <button class="stream-ctrl-btn" id="sc-speaker" onclick="StreamCodec.toggleSpk()"></button>
      <button class="stream-ctrl-btn" id="sc-call" onclick="StreamCodec.startCall()"></button>
      <button class="stream-ctrl-btn danger stream-leave hidden" id="sc-leave" onclick="StreamCodec.leaveCall(true)"></button>
    </div>
  </div>

  <!-- Chat Panel -->
  <div class="mobile-chat-area">
    <div class="chat-header">
      Room Chat
    </div>
    <div class="chat-messages" id="chat-messages"></div>
    <div class="chat-input-row">
      <input type="text" class="chat-input" id="chat-input" placeholder="Message..." onkeydown="if(event.key==='Enter')sendChat()">
      <button class="chat-send" onclick="sendChat()">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
      </button>
    </div>
  </div>
</div>

<!-- Included StreamCodec handles GetStream initialization -->
<?php include __DIR__ . '/stream-codec.view.php'; ?>

<script>
const MY_CODE = <?= json_encode($player_id) ?>;
const ROOM_ID = <?= json_encode($room_id) ?>;
const MSID = <?= json_encode($msid) ?>;
let state = {
  room: null,
  myIdx: -1,
  lastChatCount: 0
};
let syncTimer = null;
let _firstSync = true;
let _firstSyncCallCheck = true;

const $ = id => document.getElementById(id);
const escHtml = s => {
  const el = document.createElement('div'); el.innerText = s; return el.innerHTML;
};

// Fallback functions required by stream-codec
function switchSidebarTool() {}
function _updateLiveBadge() {}
function showToast(msg, type) { console.log('[Toast:', type, ']', msg); }

// API wrapper — uses JSON body to match api.php requirements
async function api(payload) {
  const res = await fetch('api.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload),
  });
  if (!res.ok) {
    const errData = await res.json().catch(() => ({}));
    throw new Error(errData.error || `HTTP ${res.status}`);
  }
  return res.json();
}

// ── SYNC ──
async function syncRoom() {
  try {
    const payload = {action:'sync', room_id:ROOM_ID, player_id:MY_CODE, msid:MSID};
    if (_firstSync) payload.claim_msid = 1;
    _firstSync = false;
    const d = await api(payload);
    if (d.error) {
        if (d.error === 'superseded') {
            alert("Transferred to a newer device. This session will now disconnect.");
            if (StreamCodec.destroy) StreamCodec.destroy();
            clearInterval(syncTimer);
            return;
        }
        return;
    }
    state.room = d;
    
    // Find myIdx
    state.myIdx = d.players.findIndex(p => p.code === MY_CODE);

    // Initial connection logic
    if ($('global-loading').classList.contains('hidden') === false) {
      $('global-loading').classList.add('hidden');
      const myName = state.myIdx >= 0 ? d.players[state.myIdx].name : 'Mobile';
      
      // Init video client - securely wait for the module to load
      const initVideoClient = () => {
          if (typeof StreamCodec === 'undefined' || !StreamCodec.init) {
              setTimeout(initVideoClient, 200);
              return;
          }
          StreamCodec.init(ROOM_ID, MY_CODE, myName);
          // Auto-join is fundamentally unsupported on iOS due to MediaStream constraints needing a strict user tap event.
          // We will instead let the user click the active green call button to start their camera unhindered.
      };
      initVideoClient();
    }

    // Render Chat
    renderChat();
    
    // Incoming call polling
    if (StreamCodec && StreamCodec._checkCallState) {
      if (_firstSyncCallCheck) {
          if (d.call_active && d.call_active.active && d.call_active.caller_code !== MY_CODE) {
              StreamCodec._lastCallActiveId = d.call_active.caller_code + '_' + d.call_active.started_at;
          }
          _firstSyncCallCheck = false;
      }
      StreamCodec._checkCallState(d.call_active);
    }
  } catch(e) { }
}

// ── CHAT ──
function renderChat() {
  const msgs = state.room?.chat || [];
  const el = $('chat-messages');
  if (msgs.length === state.lastChatCount && el.innerHTML !== '') return;
  state.lastChatCount = msgs.length;
  
  const wasAtBottom = el.scrollHeight - el.scrollTop <= el.clientHeight + 50;
  el.innerHTML = msgs.map(m => {
    const t = new Date(m.ts * 1000);
    const h = t.getHours().toString().padStart(2,'0');
    const min = t.getMinutes().toString().padStart(2,'0');
    const isMe = (state.myIdx >= 0 && m.from === state.room.players[state.myIdx].name);
    return `<div class="chat-msg ${isMe ? 'me' : ''}">
      <div class="chat-from">${isMe ? 'You' : escHtml(m.from)}</div>
      <div>${escHtml(m.msg)}</div>
      <div class="chat-time">${h}:${min}</div>
    </div>`;
  }).join('') || '<div style="color:#888;text-align:center;padding:10px;">No messages</div>';
  
  if (wasAtBottom) el.scrollTo(0, el.scrollHeight);
}

async function sendChat() {
  const input = $('chat-input');
  const msg = input.value.trim();
  if (!msg) return;
  input.value = '';
  try {
    await api({action:'send_message', room_id:ROOM_ID, player_id:MY_CODE, message:msg});
    await syncRoom();
  } catch(e) {}
}

// Start — sync every 2.5s to act as keepalive for msid
syncRoom();
syncTimer = setInterval(syncRoom, 2500);

</script>
</body>
</html>

