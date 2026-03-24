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
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=JetBrains+Mono:wght@400;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
<style>
/* Reset and core */
* { box-sizing: border-box; }
body { margin: 0; padding: 0; background: var(--bg); color: var(--text); font-family: 'Syne', sans-serif; height: 100vh; display: flex; flex-direction: column; overflow: hidden; }

/* Topbar */
.mobile-topbar { height: 60px; background: var(--surface2); border-bottom: 1px solid var(--border); display: flex; align-items: center; padding: 0 15px; flex-shrink: 0; justify-content: space-between; }
.mobile-brand { font-weight: 800; font-size: 1.1rem; color: var(--p0); }
.mobile-status { font-family: 'JetBrains Mono', monospace; font-size: 0.75rem; color: var(--ok); }

/* Main area (split between video and chat) */
.mobile-main { flex: 1; display: flex; flex-direction: column; min-height: 0; }

/* Video area */
.mobile-video-area { flex: 1; display: flex; flex-direction: column; min-height: 0; position: relative; }
#stream-video-grid { flex: 1; min-height: 0; }
.stream-controls { background: var(--surface); border-top: 1px solid var(--border); }

/* Chat area (toggleable or bottom sheet) */
.mobile-chat-area { height: 45%; background: var(--surface); border-top: 2px solid var(--border); display: flex; flex-direction: column; flex-shrink: 0; }
.chat-header { padding: 10px; font-weight: 700; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
.chat-messages { flex: 1; overflow-y: auto; padding: 10px; display: flex; flex-direction: column; gap: 8px; font-family: sans-serif; font-size: 0.85rem; }
.chat-input-row { display: flex; padding: 10px; border-top: 1px solid var(--border); background: var(--surface2); }
.chat-input { flex: 1; padding: 8px 12px; border-radius: 20px; border: 1px solid var(--border); background: var(--surface); color: var(--text); }
.chat-send { width: 40px; height: 40px; border-radius: 50%; background: var(--p0); border: none; color: #fff; margin-left: 8px; display: flex; align-items: center; justify-content: center; cursor: pointer; }

/* Global loading */
.global-loading { position: fixed; inset: 0; background: var(--bg); display: flex; align-items: center; justify-content: center; z-index: 1000; flex-direction: column; }
.global-loading.hidden { display: none; }
.spinner { width: 40px; height: 40px; border: 3px solid rgba(255,255,255,0.1); border-top-color: var(--p0); border-radius: 50%; animation: spin 1s infinite linear; margin-bottom: 20px; }
@keyframes spin { to { transform: rotate(360deg); } }

/* Helpers from main */
.chat-msg { background: var(--surface2); padding: 8px 12px; border-radius: 12px; align-self: flex-start; max-width: 90%; word-break: break-word; }
.chat-from { font-size: 0.65rem; color: var(--p0); font-family: 'JetBrains Mono', monospace; text-transform: uppercase; margin-bottom: 2px; }
.chat-time { font-size: 0.6rem; color: var(--muted); text-align: right; margin-top: 4px; }
</style>
</head>
<body>

<div class="global-loading" id="global-loading">
  <div class="spinner"></div>
  <div style="font-weight:700;">Connecting...</div>
</div>

<div class="mobile-topbar">
  <div class="mobile-brand">Mobile Call</div>
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
      <button class="stream-ctrl-btn danger stream-leave hidden" id="sc-leave" onclick="StreamCodec.leaveCall()"></button>
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

const $ = id => document.getElementById(id);
const escHtml = s => {
  const el = document.createElement('div'); el.innerText = s; return el.innerHTML;
};

// Fallback functions required by stream-codec
function switchSidebarTool() {}
function _updateLiveBadge() {}
function showToast(msg, type) { console.log('[Toast:', type, ']', msg); }

// Simple API wrapper
async function api(payload) {
  const fd = new FormData();
  for (const k in payload) fd.append(k, payload[k]);
  const res = await fetch('api.php', {method: 'POST', body: fd});
  const text = await res.text();
  if (text.startsWith('{')) return JSON.parse(text);
  throw new Error(text);
}

// ── SYNC ──
async function syncRoom() {
  try {
    const d = await api({action:'sync', room_id:ROOM_ID, player_id:MY_CODE, msid:MSID});
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
      
      // Init video client
      StreamCodec.init(ROOM_ID, MY_CODE, myName);
      
      // Auto join if there's already an active call
      if (d.call_active && d.call_active.active) {
          setTimeout(() => {
              if (StreamCodec.acceptCall) StreamCodec.acceptCall();
          }, 1000);
      }
    }

    // Render Chat
    renderChat();
    
    // Incoming call polling
    if (StreamCodec && StreamCodec._checkCallState) {
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
    return \`<div class="chat-msg">
      <div class="chat-from">\${escHtml(m.from)}</div>
      <div>\${escHtml(m.msg)}</div>
      <div class="chat-time">\${h}:\${min}</div>
    </div>\`;
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

// Start
syncRoom();
syncTimer = setInterval(syncRoom, 2000);

</script>
</body>
</html>

