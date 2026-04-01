<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<title>MiniShiksha — OMR Room v3.0.0</title>
<link rel="icon" href="https://minishiksha.in/wp-content/uploads/2025/06/icons8-class-pulsar-gradient-16.png" sizes="any">
<link rel="apple-touch-icon" href="apple-touch-icon.png">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=JetBrains+Mono:wght@400;500;700&family=Instrument+Serif:ital@0;1&display=swap" rel="stylesheet">
<!-- Firebase Auth Guard -->
<script src="https://www.gstatic.com/firebasejs/10.12.0/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/10.12.0/firebase-auth-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/10.12.0/firebase-firestore-compat.js"></script>
<script>(function(){try{var u=localStorage.getItem('omr_user');if(!u||u==='null'){var base=location.pathname.substring(0,location.pathname.lastIndexOf('/')+1);window.location.replace(base+'login.php?return='+encodeURIComponent(location.href));}}catch(e){}})();</script>
<script src="firebase-config.js"></script>
<script src="db-api.js"></script>
<!-- GetStream SDK CDN preconnect (speeds up ESM import in stream-codec.view.php) -->
<link rel="preconnect" href="https://esm.sh">
<link rel="preconnect" href="https://cdn.jsdelivr.net">
<!-- Native browser PDF viewer is used (no pdf.js needed) -->
<style>
:root {
  --bg: #07080f;
  --surface: #0f1018;
  --surface2: #171825;
  --border: #252638;
  --p0: #5b7fff; --p0-dim: rgba(91,127,255,0.12); --p0-glow: rgba(91,127,255,0.3);
  --p1: #ff5f7e; --p1-dim: rgba(255,95,126,0.12); --p1-glow: rgba(255,95,126,0.3);
  --p2: #ffe156; --p2-dim: rgba(255,225,86,0.1);
  --p3: #4fffb0; --p3-dim: rgba(79,255,176,0.1);
  --text: #eaeaf5; --muted: #5a5a7a; --muted2: #3a3a55;
  --danger: #ff4757; --warn: #ffa502; --ok: #4fffb0;
  --radius: 12px; --tr: 0.18s cubic-bezier(.4,0,.2,1);
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
html,body{height:100%;overflow:hidden;}
body{font-family:'Syne',sans-serif;background:var(--bg);color:var(--text);font-size:14px;}

/* ── DARK SCROLLBARS ── */
/* WebKit (Chrome, Safari, Edge) */
::-webkit-scrollbar{width:4px;height:4px;}
::-webkit-scrollbar-track{background:transparent;}
::-webkit-scrollbar-thumb{background:rgba(255,255,255,.08);border-radius:4px;transition:background .3s;}
::-webkit-scrollbar-thumb:hover{background:rgba(255,255,255,.18);}
::-webkit-scrollbar-corner{background:transparent;}
/* Firefox */
*{scrollbar-width:thin;scrollbar-color:rgba(255,255,255,.08) transparent;}

/* Scroll hint — subtle gradient overlay at bottom of scrollable areas */
.chat-messages,.q-grid,.main-content,.sidebar{
  position:relative;
}
.chat-messages::after,.q-grid::after{
  content:'';position:sticky;bottom:0;left:0;right:0;height:24px;
  background:linear-gradient(transparent,var(--surface));
  pointer-events:none;display:block;flex-shrink:0;
  opacity:.6;transition:opacity .3s;
}

/* ── SCREENS ── */
.screen{display:none;position:fixed;inset:0;z-index:10;}
.screen.active{display:flex;}

/* ══════════════════════════════════════
   LOBBY / CODE ENTRY SCREEN
══════════════════════════════════════ */
#scr-lobby{
  background:var(--bg);flex-direction:column;
  align-items:center;justify-content:center;
  overflow-y:auto;padding:2rem 1rem;
}
.lobby-card{
  background:var(--surface);border:1.5px solid var(--border);
  border-radius:16px;padding:2.5rem 2rem;
  width:100%;max-width:420px;text-align:center;
}
.lobby-logo{font-size:2.5rem;margin-bottom:1rem;}
.lobby-title{font-size:1.6rem;font-weight:800;letter-spacing:-1px;margin-bottom:.4rem;}
.lobby-sub{color:var(--muted);font-size:.85rem;margin-bottom:2rem;line-height:1.5;}
.code-input{
  width:100%;background:var(--bg);border:2px solid var(--border);border-radius:12px;
  color:var(--text);padding:1rem;font-family:'JetBrains Mono',monospace;
  font-size:1.8rem;font-weight:700;letter-spacing:.5rem;text-align:center;
  outline:none;transition:var(--tr);text-transform:uppercase;
}
.code-input:focus{border-color:var(--p0);}
.btn-join{
  width:100%;padding:1rem;margin-top:1rem;
  background:linear-gradient(135deg,#5b7fff,#7b5fff);
  border:none;border-radius:var(--radius);
  color:#fff;font-family:'Syne',sans-serif;font-size:1rem;font-weight:700;
  cursor:pointer;transition:var(--tr);
}
.btn-join:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(91,127,255,0.4);}
.lobby-msg{margin-top:.75rem;font-size:.85rem;padding:8px 12px;border-radius:8px;display:none;}
.lobby-msg.error{background:rgba(255,71,87,.1);border:1px solid rgba(255,71,87,.4);color:#ff6b7a;display:block;}
.lobby-msg.ok{background:rgba(79,255,176,.1);border:1px solid rgba(79,255,176,.4);color:var(--ok);display:block;}

.back-link{display:block;margin-top:1.25rem;color:var(--muted);font-size:.8rem;text-decoration:none;}
.back-link:hover{color:var(--text);}

/* GLOBAL LOADING SCREEN */
.global-loading{position:fixed;inset:0;z-index:9999;background:var(--bg);display:flex;align-items:center;justify-content:center;flex-direction:column;text-align:center;transition:opacity .4s ease;}
.global-loading.hidden{opacity:0;pointer-events:none;}
.gl-content{background:var(--surface);border:1.5px solid var(--border);border-radius:16px;padding:2.5rem;max-width:400px;width:90%;}
.gl-title{font-size:1.4rem;font-weight:800;margin-bottom:.5rem;}
.gl-sub{color:var(--muted);font-size:.85rem;margin-bottom:1.5rem;line-height:1.4;}
.gl-steps{display:flex;flex-direction:column;gap:.8rem;text-align:left;}
.gl-step{font-family:'JetBrains Mono',monospace;font-size:.8rem;color:var(--muted);display:flex;align-items:center;gap:.6rem;}
.gl-step.active{color:var(--p0);font-weight:600;}
.gl-step.done{color:var(--ok);}
.gl-spinner{width:36px;height:36px;border:3px solid var(--surface2);border-top-color:var(--p0);border-radius:50%;animation:irv-spin .8s linear infinite;margin:0 auto;}

/* ══════════════════════════════════════
   WAITING ROOM SCREEN
══════════════════════════════════════ */
#scr-waiting{
  background:var(--bg);flex-direction:column;
  align-items:center;justify-content:center;
  overflow-y:auto;padding:2rem 1rem;
}
.waiting-card{
  background:var(--surface);border:1.5px solid var(--border);
  border-radius:16px;padding:2rem;width:100%;max-width:560px;
}
.waiting-header{
  display:flex;align-items:center;gap:1rem;
  padding-bottom:1.25rem;border-bottom:1px solid var(--border);margin-bottom:1.5rem;
}
.waiting-title{font-size:1.3rem;font-weight:800;}
.waiting-test-name{color:var(--p0);font-size:.85rem;font-family:'JetBrains Mono',monospace;margin-top:.2rem;}
.status-badge{
  margin-left:auto;padding:5px 12px;border-radius:50px;
  font-size:.7rem;font-family:'JetBrains Mono',monospace;font-weight:700;letter-spacing:1px;
}
.status-badge.waiting{border:1px solid var(--warn);color:var(--warn);background:rgba(255,165,2,.1);}
.status-badge.active{border:1px solid var(--ok);color:var(--ok);background:rgba(79,255,176,.1);}

.players-waiting{display:flex;flex-direction:column;gap:.75rem;margin-bottom:1.5rem;}
.pw-item{
  display:flex;align-items:center;gap:.75rem;
  background:var(--surface2);border:1px solid var(--border);border-radius:10px;
  padding:.85rem 1rem;transition:var(--tr);
}
.pw-item.joined{border-color:var(--ok);}
.pw-avatar{
  width:36px;height:36px;border-radius:50%;
  display:flex;align-items:center;justify-content:center;
  font-size:1rem;font-weight:700;flex-shrink:0;
}
.pw-name{font-size:.95rem;font-weight:600;flex:1;}
.pw-code{font-family:'JetBrains Mono',monospace;font-size:.75rem;color:var(--muted);}
.pw-status{font-size:.75rem;padding:3px 10px;border-radius:50px;font-family:'JetBrains Mono',monospace;}
.pw-status.joined{color:var(--ok);border:1px solid var(--ok);background:rgba(79,255,176,.08);}
.pw-status.waiting{color:var(--muted);border:1px solid var(--border);}

.share-section{margin-bottom:1.5rem;}
.share-label{font-size:.65rem;letter-spacing:2px;text-transform:uppercase;color:var(--muted);margin-bottom:.75rem;font-family:'JetBrains Mono',monospace;}
.share-grid{display:grid;grid-template-columns:1fr auto;gap:.75rem;align-items:center;}
.share-code-big{
  background:var(--bg);border:1.5px solid var(--border);border-radius:10px;
  padding:.85rem 1rem;display:flex;flex-direction:column;gap:2px;
}
.share-code-num{font-family:'JetBrains Mono',monospace;font-size:1.6rem;font-weight:700;letter-spacing:.3rem;}
.share-code-sub{font-size:.7rem;color:var(--muted);}
.btn-copy{
  padding:.85rem 1.1rem;background:var(--surface2);
  border:1.5px solid var(--border);border-radius:10px;
  color:var(--text);font-family:'JetBrains Mono',monospace;font-size:.75rem;
  cursor:pointer;transition:var(--tr);white-space:nowrap;
}
.btn-copy:hover{border-color:var(--p0);color:var(--p0);}
.btn-copy.copied{border-color:var(--ok);color:var(--ok);}

.qr-wrap{text-align:center;margin-top:.75rem;}
.qr-wrap img{border-radius:8px;border:3px solid var(--border);background:#fff;}

.btn-start-test{
  width:100%;padding:1rem;
  background:linear-gradient(135deg,var(--ok),#00c896);
  border:none;border-radius:var(--radius);
  color:#000;font-family:'Syne',sans-serif;font-size:1rem;font-weight:700;
  cursor:pointer;transition:var(--tr);
}
.btn-start-test:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(79,255,176,.35);}
.btn-start-test:disabled{opacity:.4;cursor:not-allowed;transform:none;}
.start-hint{text-align:center;font-size:.78rem;color:var(--muted);margin-top:.5rem;}

/* ══════════════════════════════════════
   MAIN EXAM SCREEN
══════════════════════════════════════ */
#scr-exam{flex-direction:column;overflow:hidden;}

/* TOP BAR */
.topbar{
  display:flex;align-items:center;gap:.75rem;
  padding:.6rem 1rem;
  background:var(--surface);border-bottom:1px solid var(--border);
  z-index:100;flex-shrink:0;position:relative;
  box-shadow:0 2px 12px rgba(0,0,0,.15);
  transition:margin-top .4s ease, max-height .4s ease, padding .4s ease, opacity .3s ease;
  max-height:60px;overflow:hidden;
}
.topbar.auto-hide{
  max-height:0;padding-top:0;padding-bottom:0;
  opacity:0;pointer-events:none;overflow:hidden;
  border-bottom:none;
}
.topbar.auto-hide:hover,
.topbar.auto-hide.peek{
  max-height:60px;padding:.6rem 1rem;
  opacity:1;pointer-events:auto;overflow:visible;
  border-bottom:1px solid var(--border);
}
.topbar-hover-zone{
  position:fixed;top:0;left:0;right:0;height:12px;z-index:99;
  display:none;
}
.topbar.auto-hide ~ .topbar-hover-zone{display:block;}
/* Floating timer always visible */
.floating-timer{
  position:fixed;top:8px;left:50%;transform:translateX(-50%);
  z-index:98;display:none;
}
.topbar.auto-hide ~ .floating-timer{display:flex;}

/* REFRESH & FULLSCREEN BUTTONS */
.tb-icon-btn{
  background:var(--surface2);border:1px solid var(--border);border-radius:8px;
  color:var(--muted);width:32px;height:32px;display:flex;align-items:center;justify-content:center;
  cursor:pointer;font-size:.9rem;transition:all .2s cubic-bezier(.4,0,.2,1);flex-shrink:0;
}
.tb-icon-btn svg{display:block;flex-shrink:0;}
.tb-icon-btn:hover{color:var(--text);border-color:rgba(255,255,255,.2);background:rgba(255,255,255,.05);box-shadow:0 0 12px rgba(91,127,255,.1);}
.tb-icon-btn:active{transform:scale(.9);}
.tb-icon-btn.spinning svg{animation:btn-spin .6s ease;}
@keyframes btn-spin{from{transform:rotate(0deg);}to{transform:rotate(360deg);}}
.tb-brand{
  font-size:.9rem;font-weight:800;letter-spacing:-0.5px;
  background:linear-gradient(135deg,var(--p0),var(--p1));
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
  flex-shrink:0;display:none;
}
@media(min-width:768px){.tb-brand{display:block;}}
.tb-test-name{
  font-size:.75rem;font-family:'JetBrains Mono',monospace;color:var(--muted);
  display:none;
}
@media(min-width:600px){.tb-test-name{display:block;}}
.tb-center{flex:1;display:flex;justify-content:center;}
.timer{
  display:flex;align-items:center;gap:.4rem;
  background:var(--surface2);border:1px solid var(--border);
  border-radius:50px;padding:5px 14px;
  font-family:'JetBrains Mono',monospace;font-size:.95rem;font-weight:500;
  transition:var(--tr);
}
.timer.warn{border-color:var(--warn);color:var(--warn);}
.timer.danger{border-color:var(--danger);color:var(--danger);animation:pulse 1s infinite;}
@keyframes pulse{0%,100%{opacity:1;}50%{opacity:.5;}}
.tb-right{display:flex;align-items:center;gap:.5rem;flex-shrink:0;}
.online-dots{display:flex;gap:3px;align-items:center;}
.online-dot{width:8px;height:8px;border-radius:50%;transition:var(--tr);}
.online-dot.on{animation:pulse2 2s infinite;}
@keyframes pulse2{0%,100%{opacity:1;}50%{opacity:.5;}}
.tb-menu-btn{
  background:var(--surface2);border:1px solid var(--border);border-radius:8px;
  color:var(--muted);width:32px;height:32px;display:flex;align-items:center;justify-content:center;
  cursor:pointer;font-size:1.1rem;transition:all .2s cubic-bezier(.4,0,.2,1);
}
.tb-menu-btn svg{display:block;flex-shrink:0;}
.tb-menu-btn:hover{color:var(--text);border-color:rgba(255,255,255,.2);background:rgba(255,255,255,.05);box-shadow:0 0 12px rgba(91,127,255,.1);}
.tb-menu-btn:active{transform:scale(.92);}

/* SCORE CHIPS */
.score-chips{display:flex;gap:.4rem;}
.sc-chip{
  display:flex;align-items:center;gap:4px;
  padding:4px 10px;border-radius:50px;
  font-size:.72rem;font-family:'JetBrains Mono',monospace;border:1px solid;
  white-space:nowrap;max-width:100px;overflow:hidden;text-overflow:ellipsis;
}
.sc-chip.p0{border-color:var(--p0);color:var(--p0);background:var(--p0-dim);}
.sc-chip.p1{border-color:var(--p1);color:var(--p1);background:var(--p1-dim);}
.sc-chip.p2{border-color:var(--p2);color:var(--p2);background:var(--p2-dim);}
.sc-chip.p3{border-color:var(--p3);color:var(--p3);background:var(--p3-dim);}

/* BODY LAYOUT */
.exam-body{
  display:flex;flex:1;min-height:0;overflow:hidden;
  max-width:100vw;
}

/* SIDEBAR */
.sidebar{
  width:240px;background:var(--surface);border-right:1px solid var(--border);
  display:flex;flex-direction:column;overflow:hidden;flex-shrink:0;
  transition:transform var(--tr);
}
.sidebar.hidden{display:none;}
@media(max-width:767px){
  .sidebar{
    position:fixed;left:0;top:0;bottom:0;z-index:200;
    width:280px;transform:translateX(-100%);
  }
  .sidebar.open{transform:translateX(0);box-shadow:8px 0 32px rgba(0,0,0,.5);}
}
.sidebar-overlay{
  display:none;position:fixed;inset:0;z-index:150;background:rgba(0,0,0,.6);
}
.sidebar-overlay.show{display:block;}
.sidebar-top{
  padding:1rem;border-bottom:1px solid var(--border);flex-shrink:0;
}
.sb-section{padding:.75rem 1rem;flex-shrink:0;}
.sb-label{
  font-size:.58rem;letter-spacing:2.5px;text-transform:uppercase;color:var(--muted);
  margin-bottom:.6rem;font-family:'JetBrains Mono',monospace;
}

/* PLAYER TABS */
.player-tabs{display:flex;flex-direction:column;gap:4px;}
.ptab{
  padding:.5rem .75rem;border-radius:8px;border:none;
  background:transparent;color:var(--muted);
  font-family:'Syne',sans-serif;font-size:.82rem;font-weight:600;
  cursor:pointer;transition:var(--tr);text-align:left;
  display:flex;align-items:center;gap:.5rem;
}
.ptab.active{color:#000;font-weight:700;}
.ptab.active.p0{background:var(--p0);}
.ptab.active.p1{background:var(--p1);}
.ptab.active.p2{background:var(--p2);}
.ptab.active.p3{background:var(--p3);}
.ptab-dot{width:6px;height:6px;border-radius:50%;flex-shrink:0;}
.p0 .ptab-dot,.ptab.p0-dot .ptab-dot{background:var(--p0);}
.p1 .ptab-dot,.ptab.p1-dot .ptab-dot{background:var(--p1);}
.p2 .ptab-dot,.ptab.p2-dot .ptab-dot{background:var(--p2);}
.p3 .ptab-dot,.ptab.p3-dot .ptab-dot{background:var(--p3);}

.ptab-current-q{
  margin-left:auto;
  font-family:'JetBrains Mono',monospace;
  font-size:.65rem;font-weight:700;
  padding:3px 7px;
  border-radius:6px;
  background:rgba(255,255,255,.05);
  border:1px solid rgba(255,255,255,.1);
  color:var(--text);
  display:flex;align-items:center;gap:4px;
}
.ptab-current-q::before{
  content:'';display:inline-block;width:4px;height:4px;
  border-radius:50%;background:var(--p0);
}
.ptab.active .ptab-current-q{
  background:rgba(0,0,0,.15);
  border-color:rgba(0,0,0,.15);
  color:#000;
}
.ptab.active .ptab-current-q::before{
  background:#000;
}

/* ══ Q-GRID ══════════════════════════════════════════════════ */
.q-grid{
  display:grid;grid-template-columns:repeat(5,1fr);gap:4px;
  padding:.75rem .75rem .5rem;overflow-y:auto;flex:1;
  align-content:start;
}
.q-btn{
  aspect-ratio:1;border-radius:7px;border:1.5px solid var(--border);
  background:var(--surface2);color:var(--muted);
  font-family:'JetBrains Mono',monospace;font-size:.62rem;font-weight:600;
  cursor:pointer;transition:var(--tr);
  display:flex;align-items:center;justify-content:center;
  position:relative;overflow:visible;
}
.q-btn:hover{border-color:rgba(255,255,255,.3);color:var(--text);transform:scale(1.05);}

/* ── state: NOT-VISITED (default) — grey ─────────────────── */
/* (no extra class — use base style) */

/* ── state: VIEWED (visited, no answer) — amber ─────────── */
.q-btn.viewed{border-color:rgba(255,165,2,.55);color:var(--warn);background:rgba(255,165,2,.06);}

/* ── state: ANSWERED — player colour, filled ─────────────── */
.q-btn.answered-p0{background:var(--p0);border-color:var(--p0);color:#fff;font-weight:700;}
.q-btn.answered-p1{background:var(--p1);border-color:var(--p1);color:#fff;font-weight:700;}
.q-btn.answered-p2{background:var(--p2);border-color:var(--p2);color:#000;font-weight:700;}
.q-btn.answered-p3{background:var(--p3);border-color:var(--p3);color:#000;font-weight:700;}

/* ── state: MARKED FOR REVIEW (no answer) — purple ───────── */
.q-btn.review-only{
  border-color:#a855f7;color:#a855f7;
  background:rgba(168,85,247,.1);
}
/* ── state: ANSWERED + MARKED FOR REVIEW ─────────────────── */
.q-btn.answered-review::after{
  content:'';position:absolute;top:0;right:0;
  width:0;height:0;
  border-style:solid;
  border-width:0 8px 8px 0;
  border-color:transparent #a855f7 transparent transparent;
  border-radius:0 7px 0 0;
}

/* ── CURRENT QUESTION — blue ring (stacks on any state) ──── */
.q-btn.current{
  box-shadow:0 0 0 2.5px var(--p0);
  z-index:1;
  transform:scale(1.08);
}

/* ── REVEALED ─────────────────────────────────────────────── */
.q-btn.revealed-q{background:#a855f7;border-color:#a855f7;color:#fff;font-weight:700;}

/* ── REVIEW FLAG on answered buttons ─────────────────────── */
.q-btn.review-flag::before{
  content:'🔖';position:absolute;top:-5px;left:-4px;
  font-size:.55rem;line-height:1;
}

/* ── NAVIGATOR LEGEND ─────────────────────────────────────── */
.nav-legend{
  display:grid;grid-template-columns:1fr 1fr;gap:4px;
  padding:.5rem .75rem .75rem;font-size:.63rem;
}
.nav-legend-item{
  display:flex;align-items:center;gap:6px;line-height:1.3;
  color:var(--text);opacity:.7;
}
.nav-legend-dot{
  width:13px;height:13px;border-radius:3px;flex-shrink:0;
  border:1.5px solid var(--border);background:var(--surface2);
}
.nav-legend-dot.s-viewed{
  border-color:#f59e0b;background:rgba(245,158,11,.18);
}
.nav-legend-dot.s-answered{
  background:var(--p0);border-color:var(--p0);box-shadow:0 0 4px var(--p0-glow);
}
.nav-legend-dot.s-review{
  border-color:#a855f7;background:rgba(168,85,247,.22);
  box-shadow:0 0 4px rgba(168,85,247,.3);
}
.nav-legend-dot.s-ans-rev{
  background:var(--p0);border-color:#a855f7;position:relative;overflow:hidden;
}
.nav-legend-dot.s-ans-rev::after{
  content:'';position:absolute;top:0;right:0;
  width:0;height:0;border-style:solid;
  border-width:0 7px 7px 0;
  border-color:transparent #a855f7 transparent transparent;
}
.nav-legend-dot.s-current{outline:2px solid var(--p0);outline-offset:1px;}

/* STATS */
.stats-grid{display:grid;grid-template-columns:1fr 1fr;gap:6px;padding:.75rem;}
.stat-item{
  background:var(--surface2);border:1px solid var(--border);border-radius:8px;
  padding:.5rem .6rem;display:flex;flex-direction:column;gap:2px;
}
.stat-val{font-size:1.1rem;font-weight:700;font-family:'JetBrains Mono',monospace;}
.stat-lbl{font-size:.6rem;color:var(--muted);text-transform:uppercase;letter-spacing:1px;}

/* MAIN CONTENT */
.main-content{flex:1;overflow-y:auto;display:flex;flex-direction:column;min-width:0;position:relative;}
.q-area{padding:1rem 1rem 0;max-width:760px;width:100%;margin:0 auto;flex-shrink:0;}

/* QUESTION CARD */
.q-header{
  display:flex;align-items:center;gap:.75rem;margin-bottom:1rem;
}
.q-number{
  font-family:'JetBrains Mono',monospace;font-size:.75rem;
  background:var(--surface2);border:1px solid var(--border);
  border-radius:8px;padding:5px 12px;color:var(--muted);
}
.q-subject{
  font-size:.7rem;color:var(--muted);font-family:'JetBrains Mono',monospace;
  background:var(--surface2);border:1px solid var(--border);
  border-radius:8px;padding:4px 10px;
}
.q-mark-btn{
  margin-left:auto;padding:5px 12px;border-radius:8px;
  border:1px solid var(--border);background:transparent;color:var(--muted);
  font-size:.75rem;cursor:pointer;transition:var(--tr);font-family:'JetBrains Mono',monospace;
}
.q-mark-btn:hover{border-color:#a855f7;color:#a855f7;}
.q-mark-btn.marked{border-color:#a855f7;color:#a855f7;background:rgba(168,85,247,.1);}
.q-mark-btn svg{display:inline-block;vertical-align:middle;margin-right:3px;}

.q-text{
  font-size:1rem;line-height:1.7;margin-bottom:1.25rem;
  background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);
  padding:1.25rem;
}

/* OPTIONS */
.options-grid{display:flex;flex-direction:column;gap:.6rem;margin-bottom:1.25rem;}
.opt{
  display:flex;align-items:flex-start;gap:.75rem;
  padding:.85rem 1rem;border-radius:var(--radius);
  border:1.5px solid var(--border);background:var(--surface);
  cursor:pointer;transition:var(--tr);
  position:relative;overflow:hidden;
}
.opt:hover{border-color:var(--muted);background:var(--surface2);}
.opt.selected-p0{border-color:var(--p0);background:var(--p0-dim);}
.opt.selected-p1{border-color:var(--p1);background:var(--p1-dim);}
.opt.selected-p2{border-color:var(--p2);background:var(--p2-dim);}
.opt.selected-p3{border-color:var(--p3);background:var(--p3-dim);}
.opt.correct{border-color:var(--ok)!important;background:rgba(79,255,176,.08)!important;}
.opt.wrong-p0,.opt.wrong-p1,.opt.wrong-p2,.opt.wrong-p3{border-color:var(--danger)!important;background:rgba(255,71,87,.06)!important;}
.opt-letter{
  width:28px;height:28px;border-radius:50%;
  display:flex;align-items:center;justify-content:center;
  font-family:'JetBrains Mono',monospace;font-size:.8rem;font-weight:700;
  background:var(--surface2);border:1.5px solid var(--border);flex-shrink:0;
  transition:var(--tr);
}
.opt.selected-p0 .opt-letter{background:var(--p0);border-color:var(--p0);color:#000;}
.opt.selected-p1 .opt-letter{background:var(--p1);border-color:var(--p1);color:#fff;}
.opt.selected-p2 .opt-letter{background:var(--p2);border-color:var(--p2);color:#000;}
.opt.selected-p3 .opt-letter{background:var(--p3);border-color:var(--p3);color:#000;}
.opt.correct .opt-letter{background:var(--ok);border-color:var(--ok);color:#000;}
.opt-text{font-size:.9rem;line-height:1.5;flex:1;padding-top:2px;}

/* MULTI-PLAYER ANSWER INDICATOR on option */
.opt-badges{
  display:flex;gap:3px;align-items:center;flex-wrap:wrap;position:absolute;right:10px;top:50%;transform:translateY(-50%);
}
.opt-badge{
  width:16px;height:16px;border-radius:50%;
  display:flex;align-items:center;justify-content:center;
  font-size:.55rem;font-weight:700;
}

/* ACTION ROW */
.action-row{
  display:flex;gap:.6rem;margin-bottom:1.25rem;flex-wrap:wrap;
}
.btn-action{
  padding:.65rem 1.1rem;border-radius:10px;
  font-family:'Syne',sans-serif;font-size:.8rem;font-weight:600;
  cursor:pointer;transition:var(--tr);border:1.5px solid var(--border);
  background:var(--surface2);color:var(--muted);
}
.btn-action:hover{border-color:var(--text);color:var(--text);}
.btn-action.primary{
  background:linear-gradient(135deg,var(--p0),#7b5fff);
  border-color:transparent;color:#fff;
}
.btn-action.danger{border-color:var(--danger);color:var(--danger);}
.btn-action.warn{border-color:var(--warn);color:var(--warn);}
.btn-action.reveal{border-color:var(--ok);color:var(--ok);}
.btn-action:disabled{opacity:.4;cursor:not-allowed;}

/* NAV ROW */
.nav-row{display:flex;gap:.6rem;align-items:center;margin-bottom:1.5rem;}
.btn-nav{
  padding:.65rem 1.2rem;border-radius:10px;
  border:1.5px solid var(--border);background:var(--surface2);
  color:var(--muted);font-family:'Syne',sans-serif;font-size:.85rem;font-weight:600;
  cursor:pointer;transition:var(--tr);
}
.btn-nav:hover{border-color:var(--text);color:var(--text);}
.btn-nav.next{
  margin-left:auto;
  background:linear-gradient(135deg,var(--p0),#7b5fff);
  border-color:transparent;color:#fff;
}
.q-counter{
  flex:1;text-align:center;font-family:'JetBrains Mono',monospace;
  font-size:.78rem;color:var(--muted);
}

/* REVEAL SECTION */
.reveal-section{
  background:var(--surface);border:1px solid var(--border);
  border-radius:var(--radius);padding:1.25rem;margin-bottom:1rem;
}
.reveal-header{
  font-size:.8rem;font-weight:700;letter-spacing:1px;text-transform:uppercase;
  color:var(--ok);margin-bottom:.75rem;font-family:'JetBrains Mono',monospace;
}
.answer-compare{display:flex;flex-direction:column;gap:.5rem;}

/* INLINE REVEAL VOTE */
.inline-reveal-vote{
  width:100%;max-width:480px;margin:.75rem 0;
}
.irv-status{
  padding:.75rem 1rem;border-radius:12px;font-size:.82rem;
  font-family:'JetBrains Mono',monospace;display:flex;align-items:center;gap:.5rem;
}
.irv-status.requesting{
  background:rgba(91,127,255,.08);border:1.5px dashed var(--accent);color:var(--accent);
}
.irv-status.waiting{
  background:rgba(255,225,86,.08);border:1.5px solid var(--warn);color:var(--warn);
}
.irv-status.voted{
  background:rgba(0,217,126,.08);border:1.5px solid var(--ok);color:var(--ok);
}
.irv-status.voting{
  background:var(--surface);border:2px solid var(--accent);color:var(--text);
  flex-direction:column;align-items:stretch;gap:.5rem;
}
.irv-title{font-weight:700;font-family:'Syne',sans-serif;font-size:.85rem;}
.irv-actions{display:flex;gap:.5rem;align-items:center;}
.irv-btn{
  padding:.5rem 1rem;border-radius:8px;font-family:'Syne',sans-serif;
  font-size:.78rem;font-weight:700;cursor:pointer;transition:var(--tr);border:1.5px solid;
  flex:1;
}
.irv-btn.accept{background:rgba(0,217,126,.15);border-color:var(--ok);color:var(--ok);}
.irv-btn.accept:hover{background:rgba(0,217,126,.3);}
.irv-btn.reject{background:rgba(255,95,126,.1);border-color:var(--danger);color:var(--danger);}
.irv-btn.reject:hover{background:rgba(255,95,126,.25);}
.irv-time{
  font-size:.78rem;font-weight:800;color:var(--accent);
  min-width:30px;text-align:center;
}
.irv-spinner{
  width:14px;height:14px;border:2px solid var(--accent);border-top-color:transparent;
  border-radius:50%;animation:irv-spin .8s linear infinite;flex-shrink:0;
}
@keyframes irv-spin{to{transform:rotate(360deg)}}

/* ══════════════════════════════════════
   TOOLS PANEL (Video + Calculator) — in main content
══════════════════════════════════════ */
.tools-fab{
  position:absolute;bottom:1.5rem;right:1.5rem;z-index:90;
  width:50px;height:50px;border-radius:25px;
  background:linear-gradient(135deg,var(--p0),#7b5fff);color:#fff;
  border:none;display:flex;align-items:center;justify-content:center;
  cursor:pointer;box-shadow:0 6px 20px rgba(91,127,255,.3);transition:var(--tr);
}
.tools-fab:hover{transform:scale(1.08);}
.tools-fab.active{
  background:var(--surface2);border:2px solid var(--p0);color:var(--p0);
  box-shadow:none;
}
.tools-fab .tool-badge{
  position:absolute;top:4px;right:4px;width:10px;height:10px;border-radius:50%;
  background:var(--ok);display:none;border:2px solid var(--bg);
}
.tools-fab .tool-badge.show{display:block;}

.tools-panel{
  max-width:760px;width:100%;margin:0 auto;padding:1rem 1rem 1.5rem;
  display:none;flex-direction:column;flex:1;min-height:0;
}
.tools-panel.open{display:flex;}
.tools-tabs{
  display:flex;gap:0;border:1.5px solid var(--border);border-radius:var(--radius) var(--radius) 0 0;
  overflow:hidden;flex-shrink:0;
}
.tools-tab{
  flex:1;padding:.6rem .5rem;border:none;background:var(--surface);
  color:var(--muted);font-family:'Syne',sans-serif;font-size:.75rem;font-weight:600;
  cursor:pointer;transition:var(--tr);display:flex;align-items:center;justify-content:center;gap:6px;
  position:relative;letter-spacing:.3px;
}
.tools-tab svg{width:15px;height:15px;flex-shrink:0;}
.tools-tab:hover{color:var(--text);background:var(--surface2);}
.tools-tab.active{
  color:var(--p0);background:var(--surface2);
}
.tools-tab.active::after{
  content:'';position:absolute;bottom:0;left:10%;right:10%;height:2px;
  background:var(--p0);border-radius:2px;
}
.tools-tab .tool-badge{
  width:6px;height:6px;border-radius:50%;background:var(--ok);
  display:none;flex-shrink:0;
}
.tools-tab .tool-badge.live{display:inline-block;animation:pulse2 2s infinite;}
.tools-content{
  border:1.5px solid var(--border);border-top:none;
  border-radius:0 0 var(--radius) var(--radius);
  background:var(--surface);overflow:hidden;
  display:flex;flex-direction:column;flex:1;min-height:0;
}
.tools-pane{
  display:none;flex-direction:column;flex:1;min-height:0;
}
.tools-pane.active{display:flex;}

/* CALCULATOR (inside tools panel) */
.calc-wrap{
  width:100%;background:var(--surface);
  padding:.6rem;display:flex;flex-direction:column;
  flex:1;justify-content:center;
}
.calc-display{
  background:#1a1a2e;border-radius:10px;padding:.5rem .7rem;margin-bottom:.5rem;
  text-align:right;min-height:48px;display:flex;flex-direction:column;justify-content:flex-end;
}
.calc-history{
  font-size:.55rem;color:var(--muted);font-family:'JetBrains Mono',monospace;
  min-height:12px;word-break:break-all;
}
.calc-result{
  font-size:1.2rem;font-weight:700;color:#fff;font-family:'JetBrains Mono',monospace;
  word-break:break-all;line-height:1.2;
}
.calc-grid{
  display:grid;grid-template-columns:repeat(5,1fr);gap:3px;
}
.calc-btn{
  padding:.45rem .15rem;border-radius:7px;border:1px solid var(--border);
  background:var(--surface2);color:var(--text);
  font-family:'JetBrains Mono',monospace;font-size:.68rem;font-weight:600;
  cursor:pointer;transition:var(--tr);text-align:center;
}
.calc-btn:hover{background:var(--border);transform:scale(1.04);}
.calc-btn:active{transform:scale(.96);}
.calc-btn.op{background:rgba(91,127,255,.12);color:var(--accent);border-color:rgba(91,127,255,.25);}
.calc-btn.fn{background:rgba(168,85,247,.1);color:#a855f7;border-color:rgba(168,85,247,.25);font-size:.6rem;}
.calc-btn.eq{background:rgba(0,217,126,.15);color:var(--ok);border-color:rgba(0,217,126,.3);font-weight:800;}
.calc-btn.clr{background:rgba(255,95,126,.08);color:var(--danger);border-color:rgba(255,95,126,.2);}
.calc-btn.wide{grid-column:span 2;}
.pac{
  display:flex;align-items:center;gap:.75rem;
  background:var(--surface2);border:1px solid var(--border);border-radius:8px;padding:.65rem .85rem;
}
.pac-name{font-size:.78rem;font-weight:700;width:100px;flex-shrink:0;}
.pac-answer{font-size:.85rem;color:var(--muted);flex:1;}
.pac-answer .correct{color:var(--ok);}
.pac-answer .wrong{color:var(--danger);}
.pac-answer .notdone{color:var(--muted);font-style:italic;}

/* ══ VOICE PANEL ══════════════════════════════════════════════ */
.voice-panel{display:flex;flex-direction:column;gap:.6rem;padding:.75rem;}
.voice-status-bar{
  display:flex;align-items:center;gap:.5rem;
  font-size:.75rem;color:var(--muted);font-family:'JetBrains Mono',monospace;
  background:var(--surface2);border-radius:8px;padding:.5rem .75rem;
}
.voice-dot{
  width:8px;height:8px;border-radius:50%;background:var(--muted);flex-shrink:0;
}
.voice-dot.live{background:var(--ok);animation:pulse2 1.5s infinite;}
.voice-participants{display:flex;flex-direction:column;gap:4px;}
.voice-participant{
  display:flex;align-items:center;gap:.5rem;padding:.4rem .6rem;
  background:var(--surface2);border-radius:7px;border:1px solid var(--border);
  font-size:.75rem;
}
.voice-participant .vp-name{flex:1;font-weight:600;}
.voice-participant .vp-mic{font-size:.8rem;}
.voice-controls{display:flex;gap:.4rem;flex-wrap:wrap;}
.voice-ctrl-btn{
  flex:1;min-width:0;display:flex;align-items:center;justify-content:center;gap:5px;
  padding:.55rem .5rem;border-radius:8px;border:1.5px solid var(--border);
  background:var(--surface2);color:var(--text);font-family:'Syne',sans-serif;
  font-size:.72rem;font-weight:700;cursor:pointer;transition:var(--tr);
}
.voice-ctrl-btn svg{width:14px;height:14px;flex-shrink:0;}
.voice-ctrl-btn:hover{border-color:var(--p0);color:var(--p0);}
.voice-ctrl-btn.muted{border-color:var(--warn);color:var(--warn);}
.voice-ctrl-btn.unmuted{border-color:var(--ok);color:var(--ok);}
.voice-ctrl-btn.danger{border-color:var(--danger);color:var(--danger);}
.voice-ctrl-btn.danger:hover{background:rgba(255,71,87,.12);}

/* ── Meet section ─────────────────────────────────────────── */
.meet-section{
  background:var(--surface2);border:1px solid var(--border);border-radius:10px;
  padding:.7rem .8rem;margin-top:.25rem;
}
.meet-label{font-size:.78rem;font-weight:700;margin-bottom:3px;}
.meet-desc{font-size:.68rem;color:var(--muted);margin-bottom:.6rem;line-height:1.4;}
.meet-actions{display:flex;gap:.4rem;}
.meet-btn{
  flex:1;padding:.45rem .3rem;border-radius:7px;border:1.5px solid var(--border);
  background:var(--surface);color:var(--muted);font-family:'Syne',sans-serif;
  font-size:.7rem;font-weight:700;cursor:pointer;transition:var(--tr);
  display:flex;align-items:center;justify-content:center;gap:4px;
}
.meet-btn:hover{border-color:var(--p0);color:var(--p0);}

/* ══ STATUS PANEL ══════════════════════════════════════════════ */
.status-panel{padding:.75rem;display:flex;flex-direction:column;gap:.5rem;}
.status-panel-label{
  font-size:.65rem;text-transform:uppercase;letter-spacing:1px;
  color:var(--muted);font-family:'JetBrains Mono',monospace;
}
.status-btn-grid{display:grid;grid-template-columns:1fr 1fr;gap:.4rem;}
.status-chip{
  display:flex;align-items:center;gap:.4rem;padding:.55rem .65rem;
  border-radius:8px;border:1.5px solid var(--border);background:var(--surface2);
  color:var(--muted);font-family:'Syne',sans-serif;font-size:.78rem;font-weight:700;
  cursor:pointer;transition:var(--tr);
}
.status-chip span{font-size:1rem;line-height:1;}
.status-chip:hover{border-color:rgba(255,255,255,.2);color:var(--text);}
.status-chip.sel-active{border-color:var(--ok);color:var(--ok);background:rgba(79,255,176,.08);}
.status-chip.sel-brb{border-color:var(--warn);color:var(--warn);background:rgba(255,165,2,.08);}
.status-chip.sel-break{border-color:#a78bfa;color:#a78bfa;background:rgba(167,139,250,.08);}
.status-chip.sel-help{border-color:var(--danger);color:var(--danger);background:rgba(255,71,87,.1);}
.team-status-row{
  display:flex;align-items:center;gap:.6rem;padding:.4rem .5rem;
  background:var(--surface2);border-radius:7px;font-size:.78rem;
}
.team-status-badge{
  font-size:.65rem;padding:.15rem .45rem;border-radius:5px;font-weight:700;
  flex-shrink:0;
}
.tsb-active{background:rgba(79,255,176,.15);color:var(--ok);}
.tsb-brb{background:rgba(255,165,2,.15);color:var(--warn);}
.tsb-break{background:rgba(167,139,250,.15);color:#a78bfa;}
.tsb-help{background:rgba(255,71,87,.15);color:var(--danger);}

/* ── Player status indicator on topbar/player tabs ─────────── */
.player-status-pill{
  display:inline-block;font-size:.58rem;padding:1px 5px;border-radius:4px;
  font-weight:700;margin-left:4px;vertical-align:middle;
}
.psp-brb{background:rgba(255,165,2,.2);color:var(--warn);}
.psp-break{background:rgba(167,139,250,.2);color:#a78bfa;}
.psp-help{background:rgba(255,71,87,.15);color:var(--danger);animation:pulse2 1s infinite;}

/* CHAT PANEL */
.chat-panel{
  position:fixed;right:0;top:0;bottom:0;z-index:200;
  width:280px;background:var(--surface);border-left:1px solid var(--border);
  display:flex;flex-direction:column;transform:translateX(100%);transition:transform var(--tr);
}
.chat-panel.open{transform:translateX(0);}
.chat-header{
  padding:.85rem 1rem;border-bottom:1px solid var(--border);
  display:flex;align-items:center;gap:.5rem;font-weight:700;
}
.chat-close{
  margin-left:auto;background:none;border:none;color:var(--muted);font-size:1.2rem;cursor:pointer;
}
.chat-messages{flex:1;overflow-y:auto;padding:.75rem;display:flex;flex-direction:column;gap:.5rem;}
.chat-msg{background:var(--surface2);border-radius:8px;padding:.5rem .75rem;}
.chat-from{font-size:.65rem;font-family:'JetBrains Mono',monospace;color:var(--p0);margin-bottom:2px;}
.chat-text{font-size:.83rem;line-height:1.4;}
.chat-time{font-size:.6rem;color:var(--muted);margin-top:2px;}
.chat-input-row{
  display:flex;gap:.5rem;padding:.75rem;border-top:1px solid var(--border);
}
.chat-input{
  flex:1;background:var(--bg);border:1px solid var(--border);border-radius:8px;
  color:var(--text);padding:.55rem .75rem;font-family:'Syne',sans-serif;font-size:.83rem;outline:none;
}
.chat-input:focus{border-color:var(--p0);}
.chat-send{
  background:var(--p0);border:none;border-radius:8px;
  color:#fff;padding:.55rem .85rem;cursor:pointer;font-size:.85rem;
}

/* ══════════════════════════════════════
   RESULT SCREEN
══════════════════════════════════════ */
#scr-result{
  background:var(--bg);flex-direction:column;
  align-items:stretch;overflow-y:auto;padding:0;
}
.result-topbar{
  display:flex;align-items:center;gap:.75rem;
  padding:.65rem 1.25rem;
  background:var(--surface);border-bottom:1px solid var(--border);
  position:sticky;top:0;z-index:10;flex-shrink:0;
}
.result-topbar-title{font-weight:700;font-size:.88rem;flex:1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.result-topbar-meta{font-size:.72rem;color:var(--muted);font-family:'JetBrains Mono',monospace;white-space:nowrap;}
.result-wrap{width:100%;max-width:860px;margin:0 auto;padding:1.5rem 1.25rem;}
.result-headline{
  text-align:center;font-size:clamp(1.6rem,5vw,2.8rem);font-weight:800;
  letter-spacing:-1.5px;margin-bottom:.4rem;
}
.result-sub{text-align:center;color:var(--muted);margin-bottom:1.5rem;font-size:.88rem;}

/* Result PDF viewers */
.result-pdf-bar{
  display:flex;gap:.5rem;justify-content:center;margin-bottom:1.5rem;
}
.result-pdf-btn{
  padding:.6rem 1.2rem;border-radius:var(--radius);font-family:'JetBrains Mono',monospace;
  font-size:.78rem;font-weight:600;cursor:pointer;transition:var(--tr);
  background:var(--surface);border:1.5px solid var(--border);color:var(--muted);
}
.result-pdf-btn:hover{border-color:var(--text);color:var(--text);}
.result-pdf-btn.active{border-color:var(--accent);color:var(--accent);background:var(--accent-dim);}
.result-pdf-btn.sol-active{border-color:var(--accent3);color:var(--accent3);background:rgba(255,225,86,.08);}
.result-pdf-container{
  display:flex;gap:1rem;margin-bottom:2rem;flex-wrap:wrap;
}
.result-pdf-panel{
  flex:1;min-width:320px;height:75vh;
  background:var(--surface2);border-radius:12px;
  display:flex;flex-direction:column;overflow:hidden;
  border:1px solid var(--border);
}
.result-pdf-panel iframe{
  width:100%;flex:1;border:none;border-radius:0 0 12px 12px;
}
.result-pdf-panel-label{
  width:100%;text-align:center;font-size:.72rem;font-family:'JetBrains Mono',monospace;
  color:var(--muted);padding:.5rem 0;background:var(--surface2);border-radius:12px 12px 0 0;
  flex-shrink:0;border-bottom:1px solid var(--border);
}

/* Scorecards — compact horizontal strip */
.scorecard-row{display:flex;gap:.75rem;flex-wrap:wrap;margin-bottom:1.5rem;}
.scorecard{
  flex:1;min-width:150px;
  background:var(--surface);border:2px solid var(--border);
  border-radius:var(--radius);padding:1rem 1.25rem;text-align:center;
  position:relative;transition:var(--tr);
}
.scorecard.winner{
  border-color:var(--p0);
  background:linear-gradient(135deg,var(--surface),rgba(91,127,255,.06));
}
.crown{position:absolute;top:-16px;left:50%;transform:translateX(-50%);font-size:1.4rem;display:none;}
.scorecard.winner .crown{display:block;}
.sc-name{font-size:.82rem;font-weight:700;margin-bottom:.35rem;}
.sc-score{font-size:2.5rem;font-weight:800;font-family:'JetBrains Mono',monospace;line-height:1;}
.sc-pct{font-size:.72rem;color:var(--muted);margin-top:.2rem;font-family:'JetBrains Mono',monospace;}
.sc-stats{display:flex;gap:.4rem;justify-content:center;margin-top:.65rem;flex-wrap:wrap;}
.sc-stat{
  display:flex;align-items:center;gap:4px;
  padding:2px 7px;border-radius:20px;
  font-size:.68rem;font-family:'JetBrains Mono',monospace;font-weight:600;
  background:var(--surface2);border:1px solid var(--border);
}

/* Result action bar */
.result-actions{
  display:flex;gap:.6rem;flex-wrap:wrap;align-items:center;
  margin-bottom:1.5rem;
  padding:.75rem 1rem;
  background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);
}
.btn-result{
  padding:.6rem 1.2rem;border-radius:8px;
  font-family:'Syne',sans-serif;font-size:.82rem;font-weight:700;
  cursor:pointer;transition:var(--tr);white-space:nowrap;
}
.btn-result.primary{
  background:linear-gradient(135deg,var(--p0),#7b5fff);border:none;color:#fff;
  padding:.65rem 1.4rem;
}
.btn-result.primary:hover{transform:translateY(-1px);box-shadow:0 6px 20px rgba(91,127,255,.4);}
.btn-result.secondary{background:transparent;border:1.5px solid var(--border);color:var(--muted);}
.btn-result.secondary:hover{border-color:var(--text);color:var(--text);}
.btn-result.icon-btn{
  background:var(--surface2);border:1.5px solid var(--border);color:var(--text);
  padding:.5rem .85rem;
}
.btn-result.icon-btn:hover{border-color:var(--p0);color:var(--p0);}
.result-actions-spacer{flex:1;}

/* Review table */
.review-section{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;margin-bottom:1.5rem;}
.review-head{
  padding:.75rem 1.1rem;border-bottom:1px solid var(--border);
  font-weight:700;font-size:.82rem;display:flex;align-items:center;gap:.75rem;
  background:var(--surface2);
}
.review-head-label{flex:1;font-family:'JetBrains Mono',monospace;font-size:.65rem;text-transform:uppercase;letter-spacing:1px;color:var(--muted);}
.review-table-wrap{overflow-x:auto;max-height:60vh;overflow-y:auto;}
table{width:100%;border-collapse:collapse;}
th,td{padding:.45rem .75rem;text-align:left;border-bottom:1px solid var(--border);font-size:.8rem;}
th{
  background:var(--surface2);font-size:.62rem;text-transform:uppercase;
  letter-spacing:1.5px;color:var(--muted);font-family:'JetBrains Mono',monospace;
  position:sticky;top:0;z-index:2;
}
tbody tr:hover{background:var(--surface2);}
.cell-ok{color:var(--ok);font-weight:600;}
.cell-bad{color:var(--danger);}
.cell-skip{color:var(--muted);font-style:italic;}

/* UPSC MARKS PANEL */
.upsc-panel-inner{
  background:var(--surface);border:1.5px solid var(--border);border-radius:var(--radius);
  padding:1.25rem 1.5rem;max-width:640px;margin:0 auto;
}
.upsc-panel-title{
  font-size:.72rem;text-transform:uppercase;letter-spacing:1.5px;
  color:var(--muted);font-family:'JetBrains Mono',monospace;margin-bottom:.85rem;
  display:flex;align-items:center;gap:.5rem;
}
.upsc-rows{display:flex;flex-direction:column;gap:.5rem;margin-bottom:.85rem;}
.upsc-row{
  display:flex;align-items:center;gap:.5rem;
  background:var(--surface2);border-radius:8px;padding:.55rem .85rem;
}
.upsc-row-label{flex:1;font-size:.82rem;font-weight:600;}
.upsc-row-val{font-size:.9rem;font-weight:800;font-family:'JetBrains Mono',monospace;}
.upsc-net{
  display:flex;align-items:center;justify-content:space-between;
  border-top:1px solid var(--border);padding-top:.75rem;
}
.upsc-net-label{font-size:.85rem;font-weight:700;}
.upsc-net-val{font-size:1.3rem;font-weight:800;font-family:'JetBrains Mono',monospace;}
.upsc-pattern-select{
  font-size:.72rem;color:var(--muted);display:flex;align-items:center;gap:.4rem;
  margin-bottom:.75rem;
}
.upsc-pattern-select select{
  background:var(--surface2);border:1px solid var(--border);border-radius:6px;
  color:var(--text);font-family:'Syne',sans-serif;font-size:.72rem;
  padding:2px 6px;cursor:pointer;
}

/* ── ANALYSIS MODE — 4-column layout ────────────────────── */
.analysis-overlay{
  position:fixed;inset:0;z-index:800;
  background:var(--bg);display:flex;flex-direction:column;
  animation:fadeIn .2s ease;
}
.analysis-topbar{
  display:flex;align-items:center;gap:.75rem;padding:.5rem 1rem;
  background:var(--surface);border-bottom:1px solid var(--border);flex-shrink:0;
  min-height:44px;
}
.analysis-title{font-weight:700;font-size:.85rem;flex:1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.analysis-topbar-stats{display:flex;gap:.5rem;align-items:center;flex-shrink:0;}
.analysis-stat-pill{
  padding:2px 8px;border-radius:12px;font-size:.68rem;font-weight:700;
  font-family:'JetBrains Mono',monospace;
}
.analysis-stat-pill.ok{background:rgba(79,255,176,.15);color:var(--ok);}
.analysis-stat-pill.bad{background:rgba(255,71,87,.15);color:var(--danger);}
.analysis-stat-pill.skip{background:var(--surface2);color:var(--muted);}
.analysis-close{
  background:none;border:1.5px solid var(--border);border-radius:8px;
  color:var(--muted);padding:.3rem .65rem;cursor:pointer;font-size:.78rem;
  transition:var(--tr);font-family:'Syne',sans-serif;font-weight:700;flex-shrink:0;
}
.analysis-close:hover{border-color:var(--danger);color:var(--danger);}

.analysis-body{display:flex;flex:1;min-height:0;overflow:hidden;}

/* COL 1 — Q navigator */
.analysis-left{
  width:165px;flex-shrink:0;display:flex;flex-direction:column;
  border-right:1px solid var(--border);background:var(--surface);overflow-y:auto;
}
.analysis-nav-header{
  padding:.45rem .65rem;font-size:.58rem;text-transform:uppercase;
  letter-spacing:1px;color:var(--muted);font-family:'JetBrains Mono',monospace;
  border-bottom:1px solid var(--border);flex-shrink:0;
}
.analysis-q-grid{
  display:grid;grid-template-columns:repeat(5,1fr);gap:3px;padding:.5rem;
  align-content:start;
}
.analysis-q-btn{
  aspect-ratio:1;border-radius:6px;border:none;font-size:.68rem;font-weight:700;
  font-family:'JetBrains Mono',monospace;cursor:pointer;transition:var(--tr);
  display:flex;align-items:center;justify-content:center;
}
.analysis-q-btn.aq-correct{background:rgba(79,255,176,.22);color:var(--ok);}
.analysis-q-btn.aq-wrong{background:rgba(255,71,87,.18);color:var(--danger);}
.analysis-q-btn.aq-skip{background:var(--surface2);color:var(--muted);}
.analysis-q-btn.aq-current{outline:2px solid var(--p0);outline-offset:1px;}

/* COL 2 — Response card */
.analysis-response{
  width:210px;flex-shrink:0;display:flex;flex-direction:column;
  border-right:1px solid var(--border);background:var(--surface);overflow-y:auto;
}
.analysis-col-label{
  padding:.45rem .65rem;font-size:.58rem;text-transform:uppercase;
  letter-spacing:1px;color:var(--muted);font-family:'JetBrains Mono',monospace;
  border-bottom:1px solid var(--border);flex-shrink:0;
}
.analysis-response-body{padding:.65rem;display:flex;flex-direction:column;gap:.55rem;}
.analysis-q-header{
  display:flex;align-items:center;gap:.4rem;flex-wrap:wrap;
  background:var(--surface2);border:1px solid var(--border);border-radius:8px;
  padding:.45rem .6rem;
}
.analysis-q-num{font-size:.95rem;font-weight:800;font-family:'JetBrains Mono',monospace;}
.analysis-verdict{font-size:.68rem;font-weight:700;padding:.18rem .5rem;border-radius:5px;}
.analysis-verdict.correct{background:rgba(79,255,176,.15);color:var(--ok);}
.analysis-verdict.wrong{background:rgba(255,71,87,.15);color:var(--danger);}
.analysis-verdict.skip{background:var(--surface2);color:var(--muted);}
.analysis-marks{font-size:.68rem;color:var(--muted);margin-left:auto;font-family:'JetBrains Mono',monospace;}
.analysis-ans-row{
  display:flex;gap:.35rem;font-size:.72rem;font-family:'JetBrains Mono',monospace;
  align-items:center;flex-wrap:wrap;
}
.analysis-ans-pill{
  padding:2px 7px;border-radius:4px;font-weight:700;border:1px solid var(--border);
  background:var(--surface2);
}
.analysis-ans-pill.correct-key{
  background:rgba(79,255,176,.12);color:var(--ok);border-color:rgba(79,255,176,.3);
}
.analysis-ans-pill.user-wrong{
  background:rgba(255,71,87,.12);color:var(--danger);border-color:rgba(255,71,87,.3);
}
.analysis-options{display:flex;flex-direction:column;gap:.3rem;}
.analysis-opt{
  display:flex;align-items:flex-start;gap:.45rem;padding:.4rem .55rem;
  background:var(--surface);border:1.5px solid var(--border);border-radius:7px;
  font-size:.76rem;transition:var(--tr);
}
.analysis-opt.opt-correct{border-color:var(--ok);background:rgba(79,255,176,.07);}
.analysis-opt.opt-user-wrong{border-color:var(--danger);background:rgba(255,71,87,.08);}
.analysis-opt-letter{
  width:20px;height:20px;border-radius:4px;display:flex;align-items:center;justify-content:center;
  font-family:'JetBrains Mono',monospace;font-size:.68rem;font-weight:700;
  background:var(--surface2);flex-shrink:0;margin-top:1px;
}

/* COL 3+4 — PDF columns */
.analysis-pdfs{display:flex;flex:1;min-width:0;overflow:hidden;}
.analysis-pdf-col{
  flex:1;min-width:0;display:flex;flex-direction:column;
  border-left:1px solid var(--border);overflow:hidden;
}
.analysis-pdf-col-label{
  padding:.4rem .75rem;font-size:.6rem;text-transform:uppercase;
  letter-spacing:1px;color:var(--muted);font-family:'JetBrains Mono',monospace;
  background:var(--surface2);border-bottom:1px solid var(--border);flex-shrink:0;
  text-align:center;display:flex;align-items:center;justify-content:center;gap:.4rem;
}
.analysis-pdf-col iframe{
  flex:1;border:none;width:100%;min-height:0;
  display:block;transition:opacity .25s ease;
}
.analysis-nav-bar{
  display:flex;align-items:center;justify-content:space-between;
  padding:.5rem 1rem;border-top:1px solid var(--border);flex-shrink:0;
  background:var(--surface);
}
.analysis-nav-btn{
  display:flex;align-items:center;gap:.4rem;padding:.4rem .85rem;
  border-radius:8px;border:1.5px solid var(--border);background:transparent;
  color:var(--muted);font-family:'Syne',sans-serif;font-size:.78rem;font-weight:700;
  cursor:pointer;transition:var(--tr);
}
.analysis-nav-btn:hover{border-color:var(--p0);color:var(--p0);}
.analysis-nav-center{
  display:flex;align-items:center;gap:.75rem;
  font-size:.72rem;color:var(--muted);font-family:'JetBrains Mono',monospace;
}
@media(max-width:1100px){.analysis-pdf-col:last-child{display:none;}}
@media(max-width:900px){.analysis-response{display:none;}}
@media(max-width:600px){.analysis-left{display:none;}}

/* MODAL */
.modal-overlay{
  display:none;position:fixed;inset:0;z-index:400;
  background:rgba(0,0,0,.75);backdrop-filter:blur(8px);
  align-items:center;justify-content:center;padding:1rem;
}
.modal-overlay.open{display:flex;}
.modal{
  background:var(--surface);border:1.5px solid var(--border);border-radius:16px;
  padding:2rem;width:100%;max-width:440px;position:relative;
  animation:modal-in .2s ease;
}
@keyframes modal-in{from{opacity:0;transform:scale(.95);}to{opacity:1;transform:scale(1);}}
.modal-title{font-size:1.2rem;font-weight:800;margin-bottom:.4rem;}
.modal-sub{color:var(--muted);font-size:.85rem;margin-bottom:1.5rem;line-height:1.5;}
.modal-actions{display:flex;gap:.75rem;flex-wrap:wrap;}
.btn-modal{
  flex:1;padding:.85rem;border-radius:10px;
  font-family:'Syne',sans-serif;font-size:.9rem;font-weight:700;cursor:pointer;transition:var(--tr);
  min-width:120px;
}
.btn-modal.confirm{background:linear-gradient(135deg,var(--danger),#ff6b47);border:none;color:#fff;}
.btn-modal.cancel{background:transparent;border:1.5px solid var(--border);color:var(--muted);}
.btn-modal.cancel:hover{border-color:var(--text);color:var(--text);}

/* TOAST */
.toast{
  position:fixed;bottom:1.5rem;left:50%;transform:translateX(-50%) translateY(80px);
  background:var(--surface);border:1px solid var(--border);border-radius:50px;
  padding:.65rem 1.25rem;font-size:.82rem;font-weight:600;
  transition:.3s ease;z-index:500;white-space:nowrap;pointer-events:none;
}
.toast.show{transform:translateX(-50%) translateY(0);}
.toast.ok{border-color:var(--ok);color:var(--ok);}
.toast.info{border-color:var(--p0);color:var(--p0);}
.toast.error{border-color:var(--danger);color:var(--danger);}

/* CONFETTI */
.confetti-piece{position:fixed;top:-10px;z-index:600;animation:confetti-fall linear forwards;}
@keyframes confetti-fall{to{top:110vh;transform:rotate(720deg);}}

/* MISC */
.spinner{width:16px;height:16px;border:2px solid rgba(255,255,255,.3);border-top-color:#fff;border-radius:50%;animation:spin .6s linear infinite;display:inline-block;}
@keyframes spin{to{transform:rotate(360deg);}}
.sep{height:1px;background:var(--border);margin:.75rem 0;}

/* SUBMITTED BANNER */
.submitted-banner{
  background:rgba(79,255,176,.08);border:1px solid var(--ok);border-radius:8px;
  padding:.6rem 1rem;font-size:.8rem;color:var(--ok);margin-bottom:.75rem;
  display:flex;align-items:center;gap:.5rem;
}

/* ── READING PHASE BANNER ── */
#reading-overlay{
  position:fixed;top:0;left:0;right:0;z-index:800;
  background:rgba(10,11,22,.97);backdrop-filter:blur(8px);
  border-bottom:2px solid var(--p2);
  box-shadow:0 4px 24px rgba(0,0,0,.5);
  transition:opacity .5s ease;
}
#reading-overlay.hidden{opacity:0;pointer-events:none;}
.reading-banner-row{
  display:flex;align-items:center;gap:.75rem;
  padding:.55rem 1.25rem;
}
.reading-icon-sm{font-size:1.5rem;flex-shrink:0;}
.reading-banner-text{flex:1;min-width:0;}
.reading-title{font-size:.92rem;font-weight:800;display:block;}
.reading-sub{font-size:.72rem;color:var(--muted);display:block;margin-top:1px;}
.reading-countdown{
  font-family:'JetBrains Mono',monospace;font-size:1.55rem;font-weight:700;
  color:var(--p2);letter-spacing:2px;white-space:nowrap;flex-shrink:0;
}
.reading-bar-wrap{height:3px;background:var(--surface2);overflow:hidden;}
.reading-bar{height:100%;background:var(--p2);transition:width 1s linear;}

/* ── EXAM MILESTONE ALERT ── */
#exam-alert{
  position:fixed;top:4rem;left:50%;transform:translateX(-50%);z-index:950;
  min-width:280px;max-width:90vw;padding:1rem 1.5rem;border-radius:12px;
  text-align:center;font-weight:700;font-size:.95rem;
  box-shadow:0 8px 32px rgba(0,0,0,.4);
  transition:opacity .4s ease,transform .4s ease;
  opacity:0;transform:translateX(-50%) translateY(-10px);pointer-events:none;
}
#exam-alert.show{opacity:1;transform:translateX(-50%) translateY(0);}
#exam-alert.type-warn{background:rgba(255,165,2,.15);border:1.5px solid var(--warn);color:var(--warn);}
#exam-alert.type-error{background:rgba(255,71,87,.15);border:1.5px solid var(--danger);color:var(--danger);}
#exam-alert.type-ok{background:rgba(79,255,176,.1);border:1.5px solid var(--ok);color:var(--ok);}

/* ── RESULT DOWNLOAD BTN ── */
.result-pdf-dl{
  padding:.45rem .8rem;border-radius:8px;font-family:'JetBrains Mono',monospace;
  font-size:.7rem;font-weight:600;cursor:pointer;transition:var(--tr);
  background:var(--p0-dim);border:1px solid var(--p0-glow);color:var(--p0);
  text-decoration:none;display:inline-flex;align-items:center;gap:.3rem;
}
.result-pdf-dl:hover{background:rgba(91,127,255,0.2);border-color:var(--p0);}

/* ══════════════════════════════════════
   CHAT NOTIFICATION POPUP
══════════════════════════════════════ */
.chat-notif-container{
  position:fixed;top:.75rem;right:.75rem;z-index:900;
  display:flex;flex-direction:column;gap:.5rem;
  pointer-events:none;
}
.chat-notif{
  pointer-events:auto;
  background:var(--surface);border:1.5px solid var(--p0);border-radius:12px;
  padding:.75rem 1rem;min-width:220px;max-width:320px;
  box-shadow:0 8px 32px rgba(91,127,255,.25), 0 0 0 1px rgba(91,127,255,.1);
  cursor:pointer;transition:all .35s cubic-bezier(.4,0,.2,1);
  animation:notif-in .4s cubic-bezier(.34,1.56,.64,1);
  backdrop-filter:blur(12px);
}
.chat-notif:hover{transform:translateX(-4px);border-color:var(--text);}
.chat-notif.out{opacity:0;transform:translateX(100%);}
@keyframes notif-in{from{opacity:0;transform:translateX(100%);}to{opacity:1;transform:translateX(0);}}
.chat-notif-from{
  font-size:.65rem;font-family:'JetBrains Mono',monospace;
  color:var(--p0);letter-spacing:1px;text-transform:uppercase;margin-bottom:.25rem;
  display:flex;align-items:center;gap:.4rem;
}
.chat-notif-text{
  font-size:.83rem;line-height:1.4;color:var(--text);
  overflow:hidden;text-overflow:ellipsis;white-space:nowrap;
}

/* UNREAD CHAT BADGE */
.chat-badge{
  position:absolute;top:-4px;right:-4px;
  background:var(--danger);color:#fff;
  font-size:.55rem;font-weight:800;font-family:'JetBrains Mono',monospace;
  min-width:16px;height:16px;border-radius:50%;
  display:none;align-items:center;justify-content:center;
  padding:0 3px;line-height:1;
  animation:badge-pop .3s cubic-bezier(.34,1.56,.64,1);
}
.chat-badge.show{display:flex;}
@keyframes badge-pop{from{transform:scale(0);}to{transform:scale(1);}}

/* OPPONENT CURRENT QUESTION TAG */
.ptab-current-q{
  margin-left:auto;
  font-size:.6rem;font-family:'JetBrains Mono',monospace;
  padding:2px 6px;border-radius:4px;
  background:rgba(91,127,255,.1);color:var(--p0);
  white-space:nowrap;letter-spacing:.5px;
  animation:q-pulse 2s ease-in-out infinite;
}
@keyframes q-pulse{0%,100%{opacity:1;}50%{opacity:.6;}}

/* ══════════════════════════════════════
   OMR COMPACT MODE (Answer-Key Only)
══════════════════════════════════════ */
.omr-compact-wrap{
  display:flex;flex-direction:column;align-items:center;
  padding:1rem .75rem;
}
.omr-compact-qnum{
  font-family:'JetBrains Mono',monospace;font-size:.9rem;font-weight:700;
  color:var(--muted);margin-bottom:.75rem;letter-spacing:1px;
  display:flex;align-items:center;gap:.5rem;
}
.omr-compact-qnum .omr-q-idx{
  font-size:.65rem;font-weight:500;color:var(--muted);
  background:var(--surface2);border:1px solid var(--border);border-radius:6px;
  padding:2px 6px;
}
.omr-compact-grid{
  display:grid;grid-template-columns:repeat(4,1fr);gap:.5rem;
  width:100%;max-width:320px;margin-bottom:.75rem;
}
.omr-bubble{
  border-radius:12px;
  border:2px solid var(--border);
  background:var(--surface);
  display:flex;flex-direction:column;align-items:center;justify-content:center;
  cursor:pointer;transition:all .2s cubic-bezier(.4,0,.2,1);
  position:relative;overflow:hidden;
  min-height:56px;padding:.5rem 0;
}
.omr-bubble::before{
  content:'';position:absolute;inset:0;border-radius:14px;
  background:radial-gradient(circle at 50% 40%, rgba(255,255,255,.04) 0%, transparent 70%);
  pointer-events:none;
}
.omr-bubble:hover{
  border-color:var(--muted);transform:translateY(-2px);
  box-shadow:0 6px 20px rgba(0,0,0,.25);
}
.omr-bubble:active{transform:scale(.95);}
.omr-bubble-letter{
  font-family:'JetBrains Mono',monospace;font-size:1.3rem;font-weight:800;
  color:var(--muted);transition:all .2s ease;line-height:1;
}
.omr-bubble-sub{
  font-size:.55rem;font-family:'JetBrains Mono',monospace;
  color:var(--muted2);letter-spacing:2px;text-transform:uppercase;
  margin-top:4px;transition:all .2s ease;
}

/* Selected states */
.omr-bubble.sel-p0{
  border-color:var(--p0);background:var(--p0-dim);
  box-shadow:0 0 24px rgba(91,127,255,.2), inset 0 0 20px rgba(91,127,255,.06);
}
.omr-bubble.sel-p0 .omr-bubble-letter{color:var(--p0);}
.omr-bubble.sel-p0 .omr-bubble-sub{color:var(--p0);opacity:.7;}

.omr-bubble.sel-p1{
  border-color:var(--p1);background:var(--p1-dim);
  box-shadow:0 0 24px rgba(255,95,126,.2), inset 0 0 20px rgba(255,95,126,.06);
}
.omr-bubble.sel-p1 .omr-bubble-letter{color:var(--p1);}
.omr-bubble.sel-p1 .omr-bubble-sub{color:var(--p1);opacity:.7;}

.omr-bubble.sel-p2{
  border-color:var(--p2);background:var(--p2-dim);
  box-shadow:0 0 24px rgba(255,225,86,.2), inset 0 0 20px rgba(255,225,86,.06);
}
.omr-bubble.sel-p2 .omr-bubble-letter{color:var(--p2);}
.omr-bubble.sel-p2 .omr-bubble-sub{color:var(--p2);opacity:.7;}

.omr-bubble.sel-p3{
  border-color:var(--p3);background:var(--p3-dim);
  box-shadow:0 0 24px rgba(79,255,176,.2), inset 0 0 20px rgba(79,255,176,.06);
}
.omr-bubble.sel-p3 .omr-bubble-letter{color:var(--p3);}
.omr-bubble.sel-p3 .omr-bubble-sub{color:var(--p3);opacity:.7;}

/* Correct/Wrong */
.omr-bubble.correct{
  border-color:var(--ok)!important;background:rgba(79,255,176,.1)!important;
  box-shadow:0 0 24px rgba(79,255,176,.25)!important;
}
.omr-bubble.correct .omr-bubble-letter{color:var(--ok)!important;}
.omr-bubble.correct .omr-bubble-sub{color:var(--ok)!important;}

.omr-bubble.wrong{
  border-color:var(--danger)!important;background:rgba(255,71,87,.08)!important;
  box-shadow:0 0 24px rgba(255,71,87,.15)!important;
}
.omr-bubble.wrong .omr-bubble-letter{color:var(--danger)!important;}
.omr-bubble.wrong .omr-bubble-sub{color:var(--danger)!important;}

/* Other player badges on bubble */
.omr-bubble-badges{
  position:absolute;bottom:6px;left:50%;transform:translateX(-50%);
  display:flex;gap:3px;
}
.omr-bubble-badge{
  width:14px;height:14px;border-radius:50%;
  display:flex;align-items:center;justify-content:center;
  font-size:.5rem;font-weight:800;
}

/* Compact action row */
.omr-compact-actions{
  display:flex;gap:.4rem;flex-wrap:wrap;justify-content:center;
  margin-bottom:.75rem;max-width:320px;width:100%;
}

/* Responsive - smaller bubbles on very small screens */
@media(max-width:400px){
  .omr-compact-grid{gap:.4rem;max-width:260px;}
  .omr-bubble{min-height:48px;border-radius:10px;}
  .omr-bubble-letter{font-size:1.1rem;}
}

/* ══════════════════════════════════════
   PDF PANEL & SPLIT VIEW
══════════════════════════════════════ */
.exam-body.has-pdf { }
.pdf-panel{
  display:none;flex-direction:column;overflow:hidden;
  background:var(--surface);border-left:1px solid var(--border);
  min-width:200px;max-width:100%;position:relative;
}
.pdf-panel.open{display:flex;flex:0 0 50%;min-width:0;}
.pdf-iframe{
  width:100%;flex:1;border:none;background:#fff;border-radius:0;
  min-height:0;transition:opacity 0.25s ease;
}
.analysis-pdf-frame{transition:opacity 0.25s ease;}
.pdf-panel-header{
  display:flex;align-items:center;gap:.5rem;
  padding:.5rem .75rem;
  background:var(--surface2);border-bottom:1px solid var(--border);
  flex-shrink:0;
}
.pdf-panel-title{
  flex:1;font-size:.72rem;font-family:'JetBrains Mono',monospace;
  color:var(--muted);letter-spacing:1px;text-transform:uppercase;
  white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
}
.pdf-panel-close{
  background:var(--surface);border:1px solid var(--border);border-radius:6px;
  color:var(--muted);width:24px;height:24px;
  display:flex;align-items:center;justify-content:center;
  cursor:pointer;font-size:.8rem;transition:var(--tr);
}
.pdf-panel-close:hover{color:var(--text);border-color:var(--text);}

/* SPLITTER */
.splitter{
  display:none;width:6px;cursor:col-resize;position:relative;
  background:var(--border);flex-shrink:0;z-index:10;
  transition:background var(--tr);
}
.splitter.open{display:block;}
.splitter:hover,.splitter.dragging{background:var(--p0);}
.splitter::after{
  content:'';position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);
  width:2px;height:40px;border-radius:2px;
  background:var(--muted);transition:background var(--tr);
}
.splitter:hover::after,.splitter.dragging::after{background:var(--p0);}

/* PDF TOGGLE BUTTON in topbar */
.tb-pdf-btn{
  background:var(--surface2);border:1px solid var(--border);border-radius:8px;
  color:var(--muted);height:32px;padding:0 10px;display:none;align-items:center;gap:4px;
  cursor:pointer;font-size:.72rem;transition:var(--tr);
  font-family:'JetBrains Mono',monospace;white-space:nowrap;
}
.tb-pdf-btn.has-pdf{display:flex;}
.tb-pdf-btn:hover{color:var(--text);border-color:var(--text);}
.tb-pdf-btn.active{color:var(--p0);border-color:var(--p0);background:var(--p0-dim);}

/* Solution PDF button */
.tb-sol-btn{
  background:var(--surface2);border:1px solid var(--border);border-radius:8px;
  color:var(--muted);height:32px;padding:0 10px;display:none;align-items:center;gap:4px;
  cursor:pointer;font-size:.72rem;transition:var(--tr);
  font-family:'JetBrains Mono',monospace;white-space:nowrap;
}
.tb-sol-btn.has-sol{display:flex;}
.tb-sol-btn:hover{border-color:var(--accent3);color:var(--accent3);}
.tb-sol-btn.active{border-color:var(--accent3);color:var(--accent3);background:rgba(255,225,86,.08);}

@media(max-width:767px){
  .pdf-panel.open{
    position:fixed;right:0;top:0;bottom:0;z-index:200;
    width:85vw;flex:none;
    box-shadow:-8px 0 32px rgba(0,0,0,.5);
  }
  .splitter{display:none!important;}
}

/* KEYBOARD SHORTCUTS OVERLAY */
.shortcuts-overlay{
  position:fixed;inset:0;z-index:600;
  background:rgba(0,0,0,.65);backdrop-filter:blur(8px);
  display:flex;align-items:center;justify-content:center;
  opacity:0;pointer-events:none;transition:all .25s ease;
}
.shortcuts-overlay.open{opacity:1;pointer-events:auto;}
.shortcuts-card{
  background:var(--surface);border:1.5px solid var(--border);border-radius:16px;
  padding:1.75rem 2rem;width:100%;max-width:360px;
  animation:modal-in .2s ease;
}
.shortcuts-title{
  font-size:1rem;font-weight:800;margin-bottom:1.25rem;
  display:flex;align-items:center;gap:.5rem;
}
.shortcuts-grid{
  display:grid;grid-template-columns:auto 1fr;gap:.5rem .85rem;
  align-items:center;
}
.shortcuts-grid kbd{
  display:inline-flex;align-items:center;justify-content:center;
  min-width:32px;height:28px;padding:0 8px;
  background:var(--bg);border:1.5px solid var(--border);border-radius:6px;
  font-family:'JetBrains Mono',monospace;font-size:.75rem;font-weight:700;
  color:var(--text);box-shadow:0 2px 0 var(--border);
}
.shortcuts-grid span{
  font-size:.82rem;color:var(--muted);
}
.shortcuts-close{
  margin-top:1.25rem;text-align:center;font-size:.75rem;color:var(--muted);cursor:pointer;
}
.shortcuts-close kbd{
  display:inline-flex;align-items:center;justify-content:center;
  min-width:20px;height:18px;padding:0 5px;
  background:var(--bg);border:1px solid var(--border);border-radius:4px;
  font-family:'JetBrains Mono',monospace;font-size:.6rem;font-weight:700;
  color:var(--muted);
}

/* ── MOBILE TAB BAR ── */
.exam-tab-bar{
  display:none;background:var(--surface);border-bottom:1px solid var(--border);flex-shrink:0;
}
.exam-tab-btn{
  flex:1;padding:.6rem .5rem;border:none;background:none;
  color:var(--muted);font-family:'Syne',sans-serif;font-size:.7rem;font-weight:600;
  cursor:pointer;transition:var(--tr);display:flex;flex-direction:column;align-items:center;gap:3px;
  border-bottom:2px solid transparent;
}
.exam-tab-btn.active{color:var(--p0);border-bottom-color:var(--p0);}
.exam-tab-btn.tab-pdf.active{color:var(--p2);border-bottom-color:var(--p2);}
@media(max-width:767px){
  .exam-tab-bar{display:flex;}
  /* exam-body becomes a column so tabs fill below the bar */
  #exam-body{flex-direction:column;}
  /* Hide sidebar overlay — not needed in tab mode */
  .sidebar-overlay{display:none!important;}
  /* Splitter never shown on mobile */
  #exam-body .splitter{display:none!important;}

  /* ── OPTIONS tab (default) ── */
  #scr-exam[data-tab="options"] #main-content{display:flex;flex:1;}
  #scr-exam[data-tab="options"] .sidebar{display:none!important;}
  #scr-exam[data-tab="options"] .pdf-panel{display:none!important;}

  /* ── NAVIGATOR tab ── */
  #scr-exam[data-tab="navigator"] .sidebar{
    display:flex!important;position:relative!important;
    width:100%!important;flex:1;min-height:0;overflow-y:auto;
    z-index:auto;box-shadow:none;border-right:none;
  }
  #scr-exam[data-tab="navigator"] #main-content{display:none!important;}
  #scr-exam[data-tab="navigator"] .pdf-panel{display:none!important;}

  /* ── PDF tab ── */
  #scr-exam[data-tab="pdf"] .pdf-panel{
    display:flex!important;position:relative!important;
    width:100%!important;flex:1;border-left:none;z-index:auto;box-shadow:none;
  }
  #scr-exam[data-tab="pdf"] #main-content{display:none!important;}
  #scr-exam[data-tab="pdf"] .sidebar{display:none!important;}

  /* Default (no data-tab set yet) = options mode */
  #scr-exam:not([data-tab]) #main-content{display:flex;flex:1;}
  #scr-exam:not([data-tab]) .sidebar{display:none!important;}
  #scr-exam:not([data-tab]) .pdf-panel{display:none!important;}
}

/* ── USER AVATAR in topbar ── */
.tb-user-avatar{
  width:28px;height:28px;border-radius:50%;object-fit:cover;
  border:1.5px solid var(--border);cursor:pointer;transition:var(--tr);flex-shrink:0;
}
.tb-user-avatar:hover{border-color:var(--p0);}

/* PRINT STYLES */
@media print{
  body{background:#fff!important;color:#000!important;}
  .topbar,.sidebar,.chat-panel,.modal-overlay,.toast,
  .topbar-hover-zone,.floating-timer,.sidebar-overlay,
  .confetti-piece,.chat-notif-container,.shortcuts-overlay{
    display:none!important;
  }
}
</style>
</head>
<body>

<!-- ══ GLOBAL LOADING SCREEN ══ -->
<div class="global-loading hidden" id="global-loading">
  <div class="gl-content">
    <div class="gl-spinner" style="margin-bottom:1.5rem;"></div>
    <div class="gl-title">Establishing Connection</div>
    <div class="gl-sub">Please hold on while we prepare your exam environment.</div>
    <div class="gl-steps">
      <div class="gl-step" id="gl-step-api"><span>⏳</span> Connecting to Server...</div>
      <div class="gl-step" id="gl-step-data"><span>⏳</span> Fetching Exam Data...</div>
      <div class="gl-step" id="gl-step-pdf"><span>⏳</span> Preparing Question Paper...</div>
      <div class="gl-step" id="gl-step-ui"><span>⏳</span> Launching Environment...</div>
    </div>
  </div>
</div>

<!-- ══ LOBBY / CODE ENTRY ══ -->
<div class="screen" id="scr-lobby">
  <div class="lobby-card">
    <div class="lobby-logo">📝</div>
    <div class="lobby-title">Join Test Room</div>
    <div class="lobby-sub">Enter the 3-character room code shared by your host to join the test.</div>
    <input type="text" class="code-input" id="lobby-code" maxlength="3"
      placeholder="ABC" autocomplete="off" oninput="this.value=this.value.toUpperCase()">
    <button class="btn-join" onclick="lobbyJoin()">Join Room →</button>
    <div class="lobby-msg" id="lobby-msg"></div>
    <a href="test.php" class="back-link">← Back to MiniShiksha</a>
  </div>
</div>

<!-- ══ WAITING ROOM ══ -->
<div class="screen" id="scr-waiting">
  <div class="waiting-card">
    <div class="waiting-header">
      <div>
        <div class="waiting-title">🏠 Waiting Room</div>
        <div class="waiting-test-name" id="w-test-name">Loading...</div>
      </div>
      <div class="status-badge waiting" id="w-status-badge">WAITING</div>
    </div>

    <!-- Share section (other players' codes) -->
    <div class="share-section" id="w-share-section">
      <div class="share-label">Share Codes With Other Players</div>
      <div id="w-share-codes-list"></div>
    </div>

    <!-- Players list -->
    <div class="sb-label" style="padding:.25rem 0 .6rem;">Players</div>
    <div class="players-waiting" id="w-players-list"></div>

    <!-- Start button (host only) -->
    <div id="w-host-controls" style="display:none;">
      <button class="btn-start-test" id="btn-start-test" onclick="hostStartTest()">
        ▶ Start Test for Everyone
      </button>
      <div class="start-hint" id="start-hint"></div>
    </div>
    <div id="w-guest-waiting" style="display:none;text-align:center;color:var(--muted);font-size:.85rem;padding:.5rem;">
      ⏳ Waiting for host to start the test...
    </div>
  </div>
</div>

<!-- ══ MAIN EXAM SCREEN ══ -->
<div class="screen" id="scr-exam">

  <!-- Top Bar -->
  <div class="topbar">
    <span class="tb-brand">MiniShiksha</span>
    <span class="tb-test-name" id="tb-test-name">—</span>
    <button class="tb-menu-btn" onclick="toggleSidebar()" title="Question Navigator"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg></button>
    <div class="tb-center">
      <div class="timer" id="timer-display"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg> 00:00</div>
    </div>
    <div class="tb-right">
      <img id="tb-user-avatar" class="tb-user-avatar" src="" alt="User" onclick="window.location.href='test.php'" title="Back to Dashboard" style="display:none;">
      <div class="score-chips" id="score-chips"></div>
      <div class="online-dots" id="online-dots"></div>
      <button class="tb-pdf-btn" id="tb-pdf-btn" onclick="togglePdfPanel()" title="Toggle PDF Viewer">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg> PDF
      </button>
      <button class="tb-menu-btn" onclick="toggleChat()" title="Chat" id="chat-btn" style="position:relative;"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg><span class="chat-badge" id="chat-badge">0</span></button>
      <button class="tb-icon-btn" onclick="refreshPage()" title="Refresh" id="btn-refresh"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg></button>
      <button class="tb-icon-btn" onclick="toggleFullscreen()" title="Fullscreen" id="btn-fullscreen"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 3 21 3 21 9"/><polyline points="9 21 3 21 3 15"/><line x1="21" y1="3" x2="14" y2="10"/><line x1="3" y1="21" x2="10" y2="14"/></svg></button>
      <button class="tb-icon-btn" onclick="toggleMute()" title="Mute/Unmute" id="btn-mute"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"/></svg></button>
      <button class="tb-icon-btn" onclick="toggleShortcutsOverlay()" title="Keyboard Shortcuts (?)"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg></button>
      <button class="tb-menu-btn" onclick="openSubmitModal()" title="Submit"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg></button>
    </div>
  </div>
  <div class="topbar-hover-zone" id="topbar-hover-zone"></div>
  <div class="floating-timer timer" id="floating-timer">00:00</div>

  <!-- Mobile Tab Bar -->
  <div class="exam-tab-bar" id="exam-tab-bar">
    <button class="exam-tab-btn active" data-tab="options" onclick="switchExamTab('options')">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
      Options
    </button>
    <button class="exam-tab-btn" data-tab="navigator" onclick="switchExamTab('navigator')">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
      Navigator
    </button>
    <button class="exam-tab-btn tab-pdf" data-tab="pdf" onclick="switchExamTab('pdf')" id="exam-tab-pdf-btn" style="display:none;">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
      PDF
    </button>
  </div>

  <div class="exam-body" id="exam-body">
    <!-- Sidebar -->
    <div class="sidebar hidden" id="sidebar">
      <div class="sidebar-top">
        <div class="sb-label">Viewing Responses Of</div>
        <div class="player-tabs" id="player-tabs"></div>
      </div>

      <div class="sb-section">
        <div class="sb-label">Questions</div>
      </div>
      <div class="q-grid" id="q-grid"></div>

      <!-- Navigator Legend -->
      <div class="nav-legend">
        <div class="nav-legend-item"><div class="nav-legend-dot"></div>Not Visited</div>
        <div class="nav-legend-item"><div class="nav-legend-dot s-viewed"></div>Viewed</div>
        <div class="nav-legend-item"><div class="nav-legend-dot s-answered"></div>Answered</div>
        <div class="nav-legend-item"><div class="nav-legend-dot s-review"></div>For Review</div>
        <div class="nav-legend-item"><div class="nav-legend-dot s-ans-rev"></div>Ans + Review</div>
        <div class="nav-legend-item"><div class="nav-legend-dot s-current"></div>Current</div>
      </div>

      <div class="sep" style="margin:.25rem 1rem .5rem;"></div>
      <div class="stats-grid" id="stats-grid"></div>
    </div>
    <div class="sidebar-overlay" id="sidebar-overlay" onclick="closeSidebar()"></div>

    <div class="main-content" id="main-content">
      <div class="q-area" id="q-area">
        <div style="text-align:center;color:var(--muted);padding:3rem 0;">Loading test...</div>
      </div>

      <!-- Tools Toggle FAB -->
      <button class="tools-fab" id="tools-fab" onclick="toggleToolsPanel()" title="Tools">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"/></svg>
        <span class="tool-badge" id="tools-fab-badge"></span>
      </button>

      <!-- ══ Tools Panel (Voice + Status + Calculator) ══ -->
      <div class="tools-panel" id="tools-panel">
        <div class="tools-tabs">
          <button class="tools-tab active" id="stab-voice" onclick="switchSidebarTool('voice')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3z"/><path d="M3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/></svg>
            Voice
            <span class="tool-badge" id="voice-live-badge"></span>
          </button>
          <button class="tools-tab" id="stab-status" onclick="switchSidebarTool('status')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            Status
          </button>
          <button class="tools-tab" id="stab-calc" onclick="switchSidebarTool('calc')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="2" width="16" height="20" rx="2"/><line x1="8" y1="6" x2="16" y2="6"/><line x1="8" y1="10" x2="8" y2="10.01"/><line x1="12" y1="10" x2="12" y2="10.01"/><line x1="16" y1="10" x2="16" y2="10.01"/><line x1="8" y1="14" x2="8" y2="14.01"/><line x1="12" y1="14" x2="12" y2="14.01"/><line x1="16" y1="14" x2="16" y2="14.01"/><line x1="8" y1="18" x2="8" y2="18.01"/><line x1="12" y1="18" x2="16" y2="18"/></svg>
            Calc
          </button>
        </div>
        <div class="tools-content">
          <!-- ══ VOICE PANEL ══ -->
          <div class="tools-pane active" id="pane-voice">
            <div class="voice-panel">
              <div class="voice-status-bar" id="voice-status-bar">
                <span class="voice-dot" id="voice-dot"></span>
                <span id="voice-status-text">Not in voice</span>
              </div>
              <div class="voice-participants" id="voice-participants"></div>
              <div class="voice-controls">
                <button class="voice-ctrl-btn" id="vc-join" onclick="voiceJoin()" title="Join Voice">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M15.05 5A5 5 0 0 1 19 8.95M15.05 1A9 9 0 0 1 23 8.94m-1 7.98v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.12 4.11 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91"/></svg>
                  Join Voice
                </button>
                <button class="voice-ctrl-btn muted" id="vc-mic" onclick="voiceToggleMic()" title="Mute / Unmute" style="display:none;">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><line x1="12" y1="19" x2="12" y2="23"/><line x1="8" y1="23" x2="16" y2="23"/></svg>
                  Muted
                </button>
                <button class="voice-ctrl-btn danger" id="vc-leave" onclick="voiceLeave()" title="Leave Voice" style="display:none;">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.68 13.31a16 16 0 0 0 3.41 2.6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7 2 2 0 0 1 1.72 2v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.34 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91"/><line x1="23" y1="1" x2="1" y2="23"/></svg>
                  Leave
                </button>
              </div>
              <!-- Meet Link -->
              <div class="meet-section">
                <div class="meet-label">📹 Video Meeting</div>
                <div class="meet-desc">Open a free Jitsi Meet room for this exam (no account needed)</div>
                <div class="meet-actions">
                  <button class="meet-btn" onclick="openMeetLink()" title="Open in new tab">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                    Open
                  </button>
                  <button class="meet-btn" onclick="copyMeetLink()" title="Copy link">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                    Copy
                  </button>
                  <button class="meet-btn" id="meet-qr-btn" onclick="toggleMeetQR()" title="Show QR">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><path d="M21 21h-3v-3M21 15v-1h-3"/></svg>
                    QR
                  </button>
                </div>
                <div id="meet-qr-wrap" style="display:none;margin-top:.5rem;text-align:center;">
                  <img id="meet-qr-img" width="120" height="120" style="border-radius:8px;display:block;margin:0 auto;" alt="QR Code">
                </div>
              </div>
            </div>
          </div>
          <!-- ══ STATUS PANEL ══ -->
          <div class="tools-pane" id="pane-status">
            <div class="status-panel">
              <div class="status-panel-label">Your Status</div>
              <div class="status-btn-grid">
                <button class="status-chip active" id="sc-active" onclick="setMyStatus('active')">
                  <span>🟢</span> Active
                </button>
                <button class="status-chip" id="sc-brb" onclick="setMyStatus('brb')">
                  <span>🕐</span> BRB
                </button>
                <button class="status-chip" id="sc-break" onclick="setMyStatus('break')">
                  <span>☕</span> Break
                </button>
                <button class="status-chip" id="sc-help" onclick="setMyStatus('help')">
                  <span>🆘</span> Need Help
                </button>
              </div>
              <div class="status-panel-label" style="margin-top:.75rem;">Team Statuses</div>
              <div id="team-status-list"></div>
            </div>
          </div>
          <!-- Calculator Pane -->
          <div class="tools-pane" id="pane-calc">
            <div class="calc-wrap" id="calc-wrap">
              <div class="calc-display">
                <div class="calc-history" id="calc-history"></div>
                <div class="calc-result" id="calc-result">0</div>
              </div>
              <div class="calc-grid">
                <button class="calc-btn fn" onclick="calcFn('sqrt')">√x</button>
                <button class="calc-btn fn" onclick="calcFn('cbrt')">∛x</button>
                <button class="calc-btn fn" onclick="calcFn('sq')">x²</button>
                <button class="calc-btn fn" onclick="calcFn('cube')">x³</button>
                <button class="calc-btn fn" onclick="calcFn('pct')">%</button>
                <button class="calc-btn fn" onclick="calcFn('inv')">1/x</button>
                <button class="calc-btn fn" onclick="calcFn('neg')">±</button>
                <button class="calc-btn op" onclick="calcInput('(')">(</button>
                <button class="calc-btn op" onclick="calcInput(')')">)</button>
                <button class="calc-btn clr" onclick="calcClear()">AC</button>
                <button class="calc-btn" onclick="calcInput('7')">7</button>
                <button class="calc-btn" onclick="calcInput('8')">8</button>
                <button class="calc-btn" onclick="calcInput('9')">9</button>
                <button class="calc-btn op" onclick="calcInput('/')">÷</button>
                <button class="calc-btn clr" onclick="calcBackspace()">⌫</button>
                <button class="calc-btn" onclick="calcInput('4')">4</button>
                <button class="calc-btn" onclick="calcInput('5')">5</button>
                <button class="calc-btn" onclick="calcInput('6')">6</button>
                <button class="calc-btn op" onclick="calcInput('*')">×</button>
                <button class="calc-btn fn" onclick="calcFn('pow')">xⁿ</button>
                <button class="calc-btn" onclick="calcInput('1')">1</button>
                <button class="calc-btn" onclick="calcInput('2')">2</button>
                <button class="calc-btn" onclick="calcInput('3')">3</button>
                <button class="calc-btn op" onclick="calcInput('-')">−</button>
                <button class="calc-btn fn" onclick="calcFn('fact')">n!</button>
                <button class="calc-btn wide" onclick="calcInput('0')">0</button>
                <button class="calc-btn" onclick="calcInput('.')">.</button>
                <button class="calc-btn op" onclick="calcInput('+')">+</button>
                <button class="calc-btn eq" onclick="calcEval()">=</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Splitter -->
    <div class="splitter" id="pdf-splitter"></div>

    <!-- PDF Panel -->
    <div class="pdf-panel" id="pdf-panel">
      <div class="pdf-panel-header">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;color:var(--p0);"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
        <span class="pdf-panel-title" id="pdf-panel-title">Question Paper</span>
        <button class="pdf-panel-close" onclick="togglePdfPanel()" title="Close PDF"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
      </div>
      <iframe class="pdf-iframe" id="pdf-iframe-main" src="about:blank" title="Question Paper"></iframe>
    </div>

    <!-- Solution PDF Panel -->
    <div class="pdf-panel" id="sol-panel">
      <div class="pdf-panel-header" style="border-color:rgba(255,225,86,.2);">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;color:var(--p2);"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
        <span class="pdf-panel-title" id="sol-panel-title">Solution</span>
        <button class="pdf-panel-close" onclick="toggleSolPanel()" title="Close Solution"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
      </div>
      <iframe class="pdf-iframe" id="pdf-iframe-sol" src="about:blank" title="Solution PDF"></iframe>
    </div>
  </div>
</div>

<!-- ══ RESULT SCREEN ══ -->
<div class="screen" id="scr-result">
  <!-- Sticky topbar -->
  <div class="result-topbar">
    <span class="result-topbar-title" id="result-topbar-title">Results</span>
    <span class="result-topbar-meta" id="result-topbar-meta"></span>
    <button class="btn-result secondary" style="padding:.4rem .85rem;font-size:.78rem;" onclick="window.location.href='test.php'">← New Room</button>
  </div>

  <div class="result-wrap">
    <div class="result-headline" id="result-headline">Results</div>
    <div class="result-sub" id="result-sub"></div>

    <div class="scorecard-row" id="scorecard-row"></div>

    <!-- Action bar -->
    <div class="result-actions">
      <button class="btn-result primary" onclick="openAnalysisMode()">🔍 Analysis Mode</button>
      <button class="btn-result icon-btn" onclick="exportResultPdf()">⬇ Report</button>
      <button class="btn-result icon-btn" id="btn-toggle-upsc" onclick="toggleUpscPanel()">&#x1F4CA; UPSC Marks</button>
      <div class="result-actions-spacer"></div>
      <div class="result-pdf-bar" id="result-pdf-bar" style="display:contents;"></div>
    </div>

    <!-- UPSC Marks Panel (collapsible) -->
    <div id="upsc-panel" style="display:none;margin-bottom:1.25rem;"></div>

    <div class="result-pdf-container" id="result-pdf-container" style="display:none;"></div>

    <!-- Answer Review — always visible -->
    <div class="review-section" id="review-section">
      <div class="review-head">
        <span class="review-head-label">Answer Review</span>
        <button class="btn-result icon-btn" style="padding:.25rem .65rem;font-size:.72rem;" onclick="exportResultPdf()">⬇ Export</button>
      </div>
      <div class="review-table-wrap">
        <table>
          <thead id="review-thead"></thead>
          <tbody id="review-tbody"></tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- ══ CHAT PANEL ══ -->
<div class="chat-panel" id="chat-panel">
  <div class="chat-header">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg> Room Chat
    <button class="chat-close" onclick="toggleChat()"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
  </div>
  <div class="chat-messages" id="chat-messages"></div>
  <div class="chat-input-row">
    <input class="chat-input" id="chat-input" placeholder="Type a message..." maxlength="200"
      onkeydown="if(event.key==='Enter')sendChat()">
    <button class="chat-send" onclick="sendChat()"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg></button>
  </div>
</div>

<!-- ══ SUBMIT MODAL ══ -->
<div class="modal-overlay" id="modal-submit">
  <div class="modal">
    <div class="modal-title">🏁 Submit Test?</div>
    <div class="modal-sub" id="submit-modal-msg">Are you sure you want to submit?</div>
    <div id="submit-status-list" style="margin-bottom:1.25rem;display:flex;flex-direction:column;gap:.5rem;"></div>
    <div class="modal-actions">
      <button class="btn-modal confirm" onclick="confirmSubmit()">Submit My Answers</button>
      <button class="btn-modal cancel" onclick="closeAllModals()">Cancel</button>
    </div>
  </div>
</div>

<!-- ══ START CONFIRM MODAL ══ -->
<div class="modal-overlay" id="modal-start-confirm">
  <div class="modal">
    <div class="modal-title">▶ Start Test Now?</div>
    <div class="modal-sub" id="start-confirm-msg">Not all players have joined yet. Start anyway?</div>
    <div class="modal-actions">
      <button class="btn-modal confirm" onclick="confirmStartTest()">Start Immediately</button>
      <button class="btn-modal cancel" onclick="closeAllModals()">Wait for Players</button>
    </div>
  </div>
</div>

<!-- TOAST -->
<div class="toast" id="toast"></div>

<!-- CHAT NOTIFICATION CONTAINER -->
<div class="chat-notif-container" id="chat-notifs"></div>

<!-- ══ READING PHASE BANNER ══ -->
<div id="reading-overlay" class="hidden">
  <div class="reading-banner-row">
    <span class="reading-icon-sm">📖</span>
    <div class="reading-banner-text">
      <span class="reading-title">Paper Reading Time</span>
      <span class="reading-sub">Read carefully — answering disabled · use ← → to browse questions</span>
    </div>
    <div class="reading-countdown" id="reading-countdown">05:00</div>
  </div>
  <div class="reading-bar-wrap"><div class="reading-bar" id="reading-bar" style="width:100%"></div></div>
</div>

<!-- ══ EXAM MILESTONE ALERT ══ -->
<div id="exam-alert"></div>

<script>
// ══════════════════════════════════════════════════════════════
//  CONFIG
// ══════════════════════════════════════════════════════════════
const SYNC_INTERVAL = 2500; // ms
const APP_START_TIME = Date.now();

function getOrCreateSessionId() {
  let id = sessionStorage.getItem('omr_tab_id');
  if (!id) {
    id = Math.random().toString(36).substring(2, 15);
    sessionStorage.setItem('omr_tab_id', id);
  }
  return id;
}
const SESSION_ID = getOrCreateSessionId();

function setGlStep(id, status, text) {
  const el = document.getElementById(id);
  if (!el) return;
  if (status === 'active') {
    el.className = 'gl-step active';
    el.innerHTML = `<span style="display:inline-block;width:14px;height:14px;border:2px solid var(--p0);border-top-color:transparent;border-radius:50%;animation:irv-spin .8s linear infinite;"></span> ` + text;
  } else if (status === 'done') {
    el.className = 'gl-step done';
    el.innerHTML = `<span style="color:var(--ok);font-weight:800">✓</span> ` + text;
  } else {
    el.className = 'gl-step';
    el.innerHTML = `<span>⏳</span> ` + text;
  }
}
function hideGlobalLoading() {
  const gl = document.getElementById('global-loading');
  if (gl) gl.classList.add('hidden');
}
function showGlobalLoading() {
  const gl = document.getElementById('global-loading');
  if (gl) gl.classList.remove('hidden');
  setGlStep('gl-step-api', 'pending', 'Connecting to Server...');
  setGlStep('gl-step-data', 'pending', 'Fetching Exam Data...');
  setGlStep('gl-step-pdf', 'pending', 'Preparing Question Paper...');
  setGlStep('gl-step-ui', 'pending', 'Launching Environment...');
}
const PLAYER_COLORS = ['p0','p1','p2','p3'];
const PLAYER_COLOR_VARS = ['var(--p0)','var(--p1)','var(--p2)','var(--p3)'];

// From PHP
let MY_CODE   = <?= json_encode($player_id) ?>;
let ROOM_ID   = <?= json_encode($room_id) ?>;
let IS_HOST   = <?= $is_host ? 'true' : 'false' ?>;

// ══════════════════════════════════════════════════════════════
//  STATE
// ══════════════════════════════════════════════════════════════
let state = {
  room:        null,
  myIdx:       -1,
  viewingIdx:  0,      // which player's answers we're viewing in sidebar
  currentQ:    null,   // current question id string
  currentQIdx: 0,
  syncTimer:   null,
  timerTick:   null,
  lastChat:    0,
  lastChatCount: 0,   // track chat count for new message detection
  chatUnread:  0,      // unread message counter
  chatOpen:    false,
  sidebarOpen: false,
  submitted:   false,
  autoSubmitTriggered: false,  // guard against double auto-submit
  examNotified: {},            // tracks which milestone alerts have fired
  localAnswers: {},    // local cache: qid -> answer (for instant feedback)
  localMarked:  {},
  localSkipped: {},
  pendingUpdates: {},  // queued updates not yet sent
  viewedQ:      new Set(), // questions the user has visited this session
  myStatus:     'active',  // 'active' | 'brb' | 'break' | 'help'
};

// (init is defined at bottom of file with reconnection support)

// ══════════════════════════════════════════════════════════════
//  LOBBY
// ══════════════════════════════════════════════════════════════
async function lobbyJoin() {
  const code = document.getElementById('lobby-code').value.trim().toUpperCase();
  if (!code || code.length < 3) {
    lobbyError('Please enter a 3-character code'); return;
  }
  lobbyError('');
  document.getElementById('lobby-msg').className = 'lobby-msg ok';
  document.getElementById('lobby-msg').textContent = 'Joining...';
  document.getElementById('lobby-msg').style.display = 'block';
  try {
    const d = await api({action:'validate_code', code, session_id: SESSION_ID});
    if (!d.valid) { lobbyError(d.error || 'Invalid code'); return; }
    MY_CODE = code;
    ROOM_ID = d.room_id;
    history.replaceState({}, '', 'room.php?player_id=' + code + '&room_id=' + ROOM_ID);
    await joinRoom();
  } catch(e) { lobbyError('Network error. Please retry.'); }
}
function lobbyError(msg) {
  const el = document.getElementById('lobby-msg');
  if (!msg) { el.style.display = 'none'; return; }
  el.className = 'lobby-msg error';
  el.textContent = msg;
}
document.getElementById('lobby-code').addEventListener('keydown', e => {
  if (e.key === 'Enter') lobbyJoin();
});

// ══════════════════════════════════════════════════════════════
//  JOIN ROOM
// ══════════════════════════════════════════════════════════════
async function joinRoom() {
  showGlobalLoading();
  // 1. Establish connection
  setGlStep('gl-step-api', 'active', 'Connecting to Server...');
  try {
    const d = await api({
      action: 'player_join', 
      player_id: MY_CODE, 
      room_id: ROOM_ID,
      session_id: SESSION_ID
    });
    if (d.error) { hideGlobalLoading(); lobbyError(d.error); showScreen('scr-lobby'); return; }
    setGlStep('gl-step-api', 'done', 'Connected to Server');

    // 2. Fetch room state
    setGlStep('gl-step-data', 'active', 'Fetching Exam Data...');
    state.myIdx = d.player_idx;
    state.viewingIdx = d.player_idx;
    await syncRoom();
    if (!state.room) {
      hideGlobalLoading();
      lobbyError('Room not found or has expired. Ask your host for a new code.');
      showScreen('scr-lobby');
      return;
    }
    setGlStep('gl-step-data', 'done', 'Exam Data Loaded');

    // 3. Verify PDF is accessible (quick HEAD check — native viewer handles rendering)
    if (state.room && state.room.pdf_url) {
      setGlStep('gl-step-pdf', 'active', 'Verifying Question Paper...');
      try {
        const r = await fetch(state.room.pdf_url, {method:'HEAD'});
        setGlStep('gl-step-pdf', 'done', r.ok ? 'Question Paper Ready' : 'Paper Not Found');
      } catch(e) {
        setGlStep('gl-step-pdf', 'done', 'Paper Check Skipped');
      }
    } else {
      document.getElementById('gl-step-pdf').style.display = 'none';
    }

    // 4. UI Setup
    setGlStep('gl-step-ui', 'active', 'Launching Environment...');
    await new Promise(r => setTimeout(r, 400));
    setGlStep('gl-step-ui', 'done', 'Environment Ready');
    hideGlobalLoading();

    try {
      if (state.room.status === 'active') {
        enterExam();
      } else if (state.room.status === 'finished') {
        showResultScreen();
      } else {
        enterWaiting();
      }
    } catch(uiErr) {
      console.error('[joinRoom] UI init error:', uiErr);
    }

    // Save to recent rooms for Rejoin feature
    try {
      let recent = JSON.parse(localStorage.getItem('omr_recent_rooms') || '[]');
      // remove existing entry with same code if any
      recent = recent.filter(r => r.code !== MY_CODE);
      recent.push({
        code: MY_CODE,
        room_id: ROOM_ID,
        test_name: state.room.test_name,
        timestamp: Date.now()
      });
      // keep last 10
      if (recent.length > 10) recent = recent.slice(recent.length - 10);
      localStorage.setItem('omr_recent_rooms', JSON.stringify(recent));
    } catch(e) {}

    // Start polling
    state.syncTimer = setInterval(syncAndUpdate, SYNC_INTERVAL);
  } catch(e) {
    console.error('[joinRoom] fatal:', e);
    hideGlobalLoading();
    lobbyError(e.message || 'Failed to join room. Check your code.');
    showScreen('scr-lobby');
    // Pre-fill code field so user can retry without retyping
    if (MY_CODE) document.getElementById('lobby-code').value = MY_CODE;
  }
}

// ══════════════════════════════════════════════════════════════
//  WAITING ROOM
// ══════════════════════════════════════════════════════════════
function enterWaiting() {
  showScreen('scr-waiting');
  renderWaiting();
}

function renderWaiting() {
  const room = state.room;
  document.getElementById('w-test-name').textContent = room.test_name;

  // Player list
  const list = document.getElementById('w-players-list');
  list.innerHTML = room.players.map((p, i) => {
    const color = PLAYER_COLORS[i];
    const isMe = i === state.myIdx;
    return `<div class="pw-item ${p.joined?'joined':''}">
      <div class="pw-avatar" style="background:var(--${color}-dim);border:1.5px solid var(--${color});color:var(--${color})">
        ${p.name[0].toUpperCase()}
      </div>
      <div style="flex:1;">
        <div class="pw-name">${escHtml(p.name)} ${isMe?'<span style="color:var(--muted);font-size:.7rem">(You)</span>':''}</div>
        <div class="pw-code" style="font-size:.68rem;color:var(--muted);">Code: ${p.code}</div>
      </div>
      <div class="pw-status ${p.joined?'joined':'waiting'}">${p.joined?'✓ JOINED':'WAITING'}</div>
    </div>`;
  }).join('');

  // Share section — show OTHER players' codes so host can share them
  const otherPlayers = room.players.filter((p, i) => i !== state.myIdx);
  const shareList = document.getElementById('w-share-codes-list');
  if (otherPlayers.length === 0) {
    document.getElementById('w-share-section').style.display = 'none';
  } else {
    document.getElementById('w-share-section').style.display = '';
    shareList.innerHTML = otherPlayers.map((p, idx) => {
      const color = PLAYER_COLORS[room.players.indexOf(p)];
      return `<div style="background:var(--surface2);border:1.5px solid var(--border);border-radius:10px;padding:.85rem 1rem;margin-bottom:.6rem;display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;">
        <div style="flex:1;min-width:140px;">
          <div style="font-size:.68rem;letter-spacing:2px;text-transform:uppercase;color:var(--${color});font-family:'JetBrains Mono',monospace;margin-bottom:.25rem;">${escHtml(p.name)}</div>
          <div style="font-family:'JetBrains Mono',monospace;font-size:1.4rem;font-weight:700;letter-spacing:.3rem;">${p.code}</div>
        </div>
        <div style="display:flex;gap:.4rem;">
          <button class="btn-copy" onclick="copyCode('${p.code}', this)">📋 Code</button>
          <button class="btn-copy" onclick="copyLink('${p.code}', this)">🔗 Link</button>
        </div>
      </div>`;
    }).join('');
  }

  // Host controls
  if (IS_HOST || state.myIdx === 0) {
    document.getElementById('w-host-controls').style.display = '';
    document.getElementById('w-guest-waiting').style.display = 'none';
    const allJoined = room.players.every(p => p.joined);
    document.getElementById('btn-start-test').disabled = false;
    document.getElementById('start-hint').textContent = allJoined
      ? '✓ All players have joined!'
      : `${room.players.filter(p=>p.joined).length}/${room.player_count} player(s) joined`;
  } else {
    document.getElementById('w-host-controls').style.display = 'none';
    document.getElementById('w-guest-waiting').style.display = '';
  }
}

function copyCode(code, btn) {
  navigator.clipboard?.writeText(code).then(() => {
    const orig = btn.textContent;
    btn.textContent = '✓ Copied!';
    btn.classList.add('copied');
    setTimeout(() => { btn.textContent = orig; btn.classList.remove('copied'); }, 2000);
  });
  showToast('Code ' + code + ' copied!', 'ok');
}
function copyLink(code, btn) {
  const url = `https://minishiksha.in/room.php?player_id=${code}&room_id=${ROOM_ID}`;
  navigator.clipboard?.writeText(url).then(() => {
    const orig = btn.textContent;
    btn.textContent = '✓ Copied!';
    btn.classList.add('copied');
    setTimeout(() => { btn.textContent = orig; btn.classList.remove('copied'); }, 2000);
  });
  showToast('Join link copied!', 'ok');
}

function hostStartTest() {
  const allJoined = state.room?.players.every(p => p.joined);
  if (!allJoined) {
    document.getElementById('start-confirm-msg').textContent =
      `${state.room.players.filter(p=>p.joined).length} of ${state.room.player_count} player(s) have joined. Start anyway?`;
    document.getElementById('modal-start-confirm').classList.add('open');
  } else {
    confirmStartTest();
  }
}
async function confirmStartTest() {
  closeAllModals();
  try {
    const d = await api({action:'start_test', room_id: ROOM_ID, player_id: MY_CODE});
    if (d.success) {
      await syncRoom();
      enterExam();
    }
  } catch(e) { showToast('Failed to start. Retry.', 'error'); }
}

// ══════════════════════════════════════════════════════════════
//  EXAM
// ══════════════════════════════════════════════════════════════
function enterExam() {
  const room = state.room;
  const tbName = document.getElementById('tb-test-name');
  if (tbName) tbName.textContent = room.test_name;

  // Init local state from server — merge, don't overwrite restored session data
  const myPlayer = room.players[state.myIdx];
  if (myPlayer) {
    const serverAns  = Object.assign({}, myPlayer.answers);
    const serverMark = Object.assign({}, myPlayer.marked);
    const serverSkip = Object.assign({}, myPlayer.skipped);
    // Only take server value for keys we don't already have locally (session restore)
    Object.keys(serverAns).forEach(k => { if (!(k in state.localAnswers) || state.localAnswers[k] === undefined) state.localAnswers[k] = serverAns[k]; });
    Object.keys(serverMark).forEach(k => { if (!(k in state.localMarked))  state.localMarked[k]  = serverMark[k]; });
    Object.keys(serverSkip).forEach(k => { if (!(k in state.localSkipped)) state.localSkipped[k] = serverSkip[k]; });
    state.submitted    = myPlayer.submitted;
  }

  // Set first question
  if (!state.currentQ && room.questions.length > 0) {
    state.currentQ    = room.questions[0];
    state.currentQIdx = 0;
  }

  showScreen('scr-exam');

  // Reset exam notification state (guards milestone alarms from firing twice on reconnect)
  state.autoSubmitTriggered = false;
  state.examNotified = {};

  // ── EXAM MODE: show reading overlay + pre-populate past milestones ──
  const room2 = state.room;
  if (room2 && room2.exam_mode && room2.started_at) {
    const now2 = Math.floor(Date.now() / 1000);
    const elapsed2 = Math.max(0, now2 - room2.started_at - (room2.total_paused_sec || 0));
    const answerElapsed2 = Math.max(0, elapsed2 - 300);
    const rawRem2 = (room2.duration_sec || 0) - elapsed2;

    if (elapsed2 >= 300) state.examNotified.readingEnd = true;
    if (answerElapsed2 >= 3600) state.examNotified.hour1 = true;
    if (rawRem2 <= 1800) state.examNotified.min30 = true;
    if (rawRem2 <= 300) state.examNotified.min5 = true;

    if (elapsed2 < 300) {
      const overlay = document.getElementById('reading-overlay');
      if (overlay) {
        overlay.style.display = '';
        overlay.classList.remove('hidden');
      }
    }
  }

  // Initialise mobile tabs
  initExamTabs(room);

  // Apply Google display name if player name is still default
  applyGoogleNameToRoom();

  // Build sidebar
  buildSidebar();
  renderQuestion();
  updateScoreChips();
  updateOnlineDots();

  // Show sidebar on desktop
  if (window.innerWidth >= 768) {
    const sb = document.getElementById('sidebar');
    if (sb) sb.classList.remove('hidden');
  }

  // PDF panel setup
  setupPdfPanel();
  setupSolPanel();

  // Start timer
  startTimer();

  // Initialize chat count (so we don't notify for old messages)
  state.lastChatCount = (room.chat || []).length;

  // Push initial current question to server
  if (state.currentQ) pushCurrentQuestion(state.currentQ);

  // Initialize voice channel status display
  renderTeamStatuses();

  // Init StreamCodec video/voice (stream-codec.view.php)
  // Module script may not have loaded yet — retry with delay
  const myP2 = room.players[state.myIdx];
  const _tryInitStream = () => {
    if (window.StreamCodec) {
      StreamCodec.init(ROOM_ID, MY_CODE, myP2?.name || MY_CODE).catch(err => {
        console.warn('[Voice] StreamCodec init failed:', err?.message || err);
        showToast('Voice SDK failed to load — check network/adblocker', 'warn');
      });
    }
  };
  if (window.StreamCodec) _tryInitStream();
  else setTimeout(_tryInitStream, 1500);

  // Restore currentQ from Firestore if we joined via direct URL (no localStorage)
  if (!getSavedSession()) {
    loadStateFromFirestore().then(fsData => {
      if (!fsData) return;
      if (fsData.localAnswers) Object.assign(state.localAnswers, fsData.localAnswers);
      if (fsData.localMarked)  Object.assign(state.localMarked,  fsData.localMarked);
      if (fsData.localSkipped) Object.assign(state.localSkipped, fsData.localSkipped);
      if (fsData.current_q && state.room?.questions?.includes(fsData.current_q)) {
        goToQuestion(fsData.current_q);
        showToast('✓ Progress restored from cloud', 'ok');
      }
    }).catch(() => {});
  }
}

// ══════════════════════════════════════════════════════════════
//  TIMER
// ══════════════════════════════════════════════════════════════
function startTimer() {
  if (state.timerTick) clearInterval(state.timerTick);
  state.timerTick = setInterval(tickTimer, 1000);
  tickTimer();
  setupTopbarAutoHide();
}
// SVG icon templates for timer states
const TIMER_ICONS = {
  clock: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>',
  pause: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>',
  hourglass: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 22h14"/><path d="M5 2h14"/><path d="M17 22v-4.172a2 2 0 0 0-.586-1.414L12 12l-4.414 4.414A2 2 0 0 0 7 17.828V22"/><path d="M7 2v4.172a2 2 0 0 0 .586 1.414L12 12l4.414-4.414A2 2 0 0 0 17 6.172V2"/></svg>',
  infinity: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18.178 8c5.096 0 5.096 8 0 8-5.095 0-7.133-8-12.739-8-4.585 0-4.585 8 0 8 5.606 0 7.644-8 12.74-8z"/></svg>',
  alarm: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="13" r="8"/><path d="M12 9v4l2 2"/><path d="M5 3L2 6"/><path d="M22 6l-3-3"/><path d="M6.38 18.7L4 21"/><path d="M17.64 18.67L20 21"/></svg>'
};
function tickTimer() {
  const room = state.room;
  if (!room || room.status !== 'active') { clearInterval(state.timerTick); return; }
  const el = document.getElementById('timer-display');
  const now = Math.floor(Date.now() / 1000);
  const pausedUntil = room.paused_until || 0;
  const totalPaused = room.total_paused_sec || 0;

  // Currently paused?
  if (pausedUntil > now) {
    const pauseRem = pausedUntil - now;
    el.innerHTML = TIMER_ICONS.pause + ' PAUSED ' + formatTime(pauseRem);
    el.className = 'timer warn';
    syncFloatingTimer(TIMER_ICONS.pause + ' ' + formatTime(pauseRem), ' warn');
    return;
  }

  if (room.timer_mode === 'none') {
    el.innerHTML = TIMER_ICONS.infinity + ' Free Mode';
    syncFloatingTimer(TIMER_ICONS.infinity + ' Free', '');
    return;
  }

  const elapsed = Math.floor(now - room.started_at) - totalPaused;

  // ── EXAM MODE: reading phase (first 300s) ──
  if (room.exam_mode && elapsed < 300) {
    const readRem = 300 - elapsed;
    el.innerHTML = '📖 Reading: ' + formatTime(readRem);
    el.className = 'timer warn';
    syncFloatingTimer('📖 ' + formatTime(readRem), ' warn');
    // Update overlay countdown and progress bar
    const cdEl = document.getElementById('reading-countdown');
    const barEl = document.getElementById('reading-bar');
    if (cdEl) cdEl.textContent = formatTime(readRem);
    if (barEl) barEl.style.width = ((readRem / 300) * 100).toFixed(1) + '%';
    // Show overlay if not already visible
    const overlay = document.getElementById('reading-overlay');
    if (overlay && overlay.classList.contains('hidden')) { overlay.style.display = ''; overlay.classList.remove('hidden'); }
    return;
  }

  // ── EXAM MODE: reading just ended ──
  if (room.exam_mode && elapsed >= 300 && !state.examNotified.readingEnd) {
    state.examNotified.readingEnd = true;
    const overlay = document.getElementById('reading-overlay');
    if (overlay) {
      overlay.classList.add('hidden');
      setTimeout(() => { overlay.style.display = 'none'; }, 600);
    }
    showExamAlert('📝 Reading time over — Start answering now!', 'ok');
    sfxRingingAlert();
    renderQuestion();
  }

  if (room.timer_mode === 'stopwatch') {
    const t = Math.max(0, elapsed);
    el.innerHTML = TIMER_ICONS.clock + ' ' + formatTime(t);
    el.className = 'timer';
    syncFloatingTimer(formatTime(t), '');
    return;
  }

  // ── COUNTDOWN ──
  const rawRem = room.duration_sec - elapsed;
  const rem = Math.max(0, rawRem);
  el.innerHTML = TIMER_ICONS.hourglass + ' ' + formatTime(rem);
  el.className = 'timer' + (rem < 300 && rem > 60 ? ' warn' : rem <= 60 ? ' danger' : '');
  syncFloatingTimer(formatTime(rem), rem < 300 && rem > 60 ? ' warn' : rem <= 60 ? ' danger' : '');

  // ── EXAM MODE milestone alarms ──
  if (room.exam_mode) {
    const answerElapsed = Math.max(0, elapsed - 300); // time spent answering
    // 1-hour mark
    if (!state.examNotified.hour1 && answerElapsed >= 3600) {
      state.examNotified.hour1 = true;
      sfxRingingAlert();
      showExamAlert('⏰ 1 Hour Complete! Keep going strong!', 'ok');
    }
    // 30 min remaining
    if (!state.examNotified.min30 && rem > 0 && rem <= 1800) {
      state.examNotified.min30 = true;
      sfxRingingAlert();
      showExamAlert('⚠️ Last 30 Minutes! Hurry up!', 'warn');
    }
    // 5 min remaining
    if (!state.examNotified.min5 && rem > 0 && rem <= 300) {
      state.examNotified.min5 = true;
      sfxRingingAlert();
      showExamAlert('🚨 Last 5 Minutes!', 'error');
    }
  }

  // ── AUTO-SUBMIT on time up (guarded against double-trigger) ──
  if (rawRem <= 0 && !state.autoSubmitTriggered) {
    state.autoSubmitTriggered = true;
    clearInterval(state.timerTick);
    showToast(TIMER_ICONS.alarm + ' Time is up! Auto-submitting...', 'error');
    setTimeout(() => { if (!state.submitted) confirmSubmit(); }, 2000);
  }
}
function formatTime(secs) {
  const h = Math.floor(secs / 3600);
  const m = Math.floor((secs % 3600) / 60);
  const s = secs % 60;
  if (h > 0) return pad(h)+':'+pad(m)+':'+pad(s);
  return pad(m)+':'+pad(s);
}
function pad(n) { return n.toString().padStart(2,'0'); }

// Topbar auto-hide after 5 seconds
let topbarHideTimer = null, topbarPeekTimer = null;
function setupTopbarAutoHide() {
  const topbar = document.querySelector('.topbar');
  const hoverZone = document.getElementById('topbar-hover-zone');
  if (!topbar || !hoverZone) return;
  topbarHideTimer = setTimeout(() => { topbar.classList.add('auto-hide'); }, 5000);
  hoverZone.addEventListener('mouseenter', () => {
    topbar.classList.add('peek'); clearTimeout(topbarPeekTimer);
  });
  hoverZone.addEventListener('mouseleave', () => {
    topbarPeekTimer = setTimeout(() => topbar.classList.remove('peek'), 2000);
  });
  topbar.addEventListener('mouseenter', () => {
    clearTimeout(topbarPeekTimer); topbar.classList.add('peek');
  });
  topbar.addEventListener('mouseleave', () => {
    topbarPeekTimer = setTimeout(() => topbar.classList.remove('peek'), 2000);
  });
  document.addEventListener('touchstart', (e) => {
    if (e.touches[0].clientY < 50 && topbar.classList.contains('auto-hide')) {
      topbar.classList.toggle('peek');
    }
  }, { passive: true });
}
// Sync floating timer with main timer
function syncFloatingTimer(text, className) {
  const f = document.getElementById('floating-timer');
  if (f) { f.innerHTML = text; f.className = 'floating-timer timer' + (className || ''); }
}
// ══════════════════════════════════════════════════════════════
//  SIDEBAR
// ══════════════════════════════════════════════════════════════
function buildSidebar() {
  buildPlayerTabs();
  buildQGrid();
  buildStats();
}

function buildPlayerTabs() {
  const room = state.room;
  const tabs = document.getElementById('player-tabs');
  tabs.innerHTML = room.players.map((p, i) => {
    const color = PLAYER_COLORS[i];
    const active = i === state.viewingIdx ? 'active ' + color : '';
    const isMe = i === state.myIdx;
    // Show opponent's current question
    let curQTag = '';
    if (!isMe && p.current_question) {
      const qNum = p.current_question.replace(/[^0-9]/g, '');
      curQTag = `<span class="ptab-current-q" title="Currently on Q${qNum}">Q${qNum}</span>`;
    }
    return `<button class="ptab ${active} ${color}-dot" onclick="switchViewPlayer(${i})">
      <span class="ptab-dot"></span>
      ${escHtml(p.name)}${isMe?' (You)':''}
      ${curQTag}
    </button>`;
  }).join('');
}

function buildQGrid() {
  const room = state.room;
  const grid = document.getElementById('q-grid');
  const isMyView = (state.viewingIdx === state.myIdx);
  const viewPlayer = room.players[state.viewingIdx];
  if (!viewPlayer) return;
  const color = PLAYER_COLORS[state.viewingIdx];

  grid.innerHTML = room.questions.map((qid, idx) => {
    // For my own view use local state; for others use server state
    const ans     = isMyView ? state.localAnswers[qid] : viewPlayer.answers?.[qid];
    const marked  = isMyView ? state.localMarked[qid]  : viewPlayer.marked?.[qid];
    const viewed  = isMyView ? state.viewedQ.has(qid)  : false;
    const revealed = room.revealed?.[qid] >= 1;
    const isCur   = qid === state.currentQ;

    let cls = 'q-btn';

    // Highest-priority: revealed
    if (revealed) {
      cls += ' revealed-q';
    } else if (ans && marked) {
      // Answered + Marked for review
      cls += ' answered-' + color + ' answered-review';
    } else if (ans) {
      // Answered only
      cls += ' answered-' + color;
    } else if (marked) {
      // Marked for review, not answered
      cls += ' review-only review-flag';
    } else if (viewed) {
      // Visited but unanswered, not marked
      cls += ' viewed';
    }
    // else: unseen (no extra class)

    if (isCur) cls += ' current';

    const num = qid.replace(/[^0-9]/g, '');
    const stateTitle = revealed ? 'Revealed' : ans && marked ? 'Answered + Review' : ans ? 'Answered' : marked ? 'Marked for Review' : viewed ? 'Viewed — Not Answered' : 'Not Visited';
    return `<button class="${cls}" onclick="goToQuestion(${idx})" title="Q${num} · ${stateTitle}">${num}</button>`;
  }).join('');
}

function buildStats() {
  const room = state.room;
  const isMyView = (state.viewingIdx === state.myIdx);
  const p = room.players[state.viewingIdx];
  if (!p) return;

  let answered = 0, reviewOnly = 0, ansReview = 0, viewed = 0, unseen = 0;
  const total = room.questions.length;

  room.questions.forEach(qid => {
    const ans    = isMyView ? state.localAnswers[qid] : p.answers?.[qid];
    const marked = isMyView ? state.localMarked[qid]  : p.marked?.[qid];
    const vis    = isMyView ? state.viewedQ.has(qid)  : false;

    if (ans && marked)      { ansReview++; answered++; }
    else if (ans)           { answered++; }
    else if (marked)        { reviewOnly++; }
    else if (vis)           { viewed++; }
    else                    { unseen++; }
  });

  document.getElementById('stats-grid').innerHTML = `
    <div class="stat-item"><div class="stat-val" style="color:var(--ok)">${answered}</div><div class="stat-lbl">Answered</div></div>
    <div class="stat-item"><div class="stat-val" style="color:var(--danger)">${total - answered - reviewOnly}</div><div class="stat-lbl">Not Answered</div></div>
    <div class="stat-item"><div class="stat-val" style="color:#a855f7">${reviewOnly}</div><div class="stat-lbl">For Review</div></div>
    <div class="stat-item"><div class="stat-val" style="color:var(--muted)">${unseen}</div><div class="stat-lbl">Not Visited</div></div>
  `;
}

function switchViewPlayer(idx) {
  state.viewingIdx = idx;
  buildSidebar();
  renderQuestion();
}

// ══════════════════════════════════════════════════════════════
//  QUESTION RENDERING
// ══════════════════════════════════════════════════════════════
const LETTERS = ['a','b','c','d'];
const LETTER_LABELS = ['A','B','C','D'];

function renderQuestion() {
  const room = state.room;
  if (!room || !state.currentQ) return;
  const qid  = state.currentQ;
  const qIdx = state.currentQIdx;

  // Track viewed questions for the navigator colour states
  if (!state.viewedQ.has(qid)) {
    state.viewedQ.add(qid);
    buildQGrid(); // refresh immediately so the dot changes colour
  }
  const area = document.getElementById('q-area');

  const myPlayer  = room.players[state.myIdx];
  const myAns     = state.localAnswers[qid] ?? null;
  const myMarked  = state.localMarked[qid]  ?? false;
  const mySkipped = state.localSkipped[qid] ?? false;
  const revealed  = room.revealed?.[qid] ?? 0;
  const correctAns = (revealed >= 1 || room.status === 'finished')
    ? (room.answer_key?.[qid] || room.answer_key_partial?.[qid] || null)
    : null;

  // Detect answer-key-only mode: no question texts AND no options for any question
  const hasQuestionTexts = room.question_texts && Object.keys(room.question_texts).length > 0;
  const hasOptions = room.options && Object.keys(room.options).length > 0;
  const isCompactMode = !hasQuestionTexts && !hasOptions;

  // Exam mode reading phase: block all answer input
  const nowSec = Math.floor(Date.now() / 1000);
  const elapsedSec = room.started_at ? (nowSec - room.started_at - (room.total_paused_sec || 0)) : 9999;
  const isReading = !!(room.exam_mode && room.started_at && elapsedSec < 300);

  let html = '';

  // Submitted banner
  if (state.submitted) {
    let endTestBtn = '';
    if (room.status === 'active') {
       endTestBtn = `<button onclick="forceEndTest()" style="margin-left:auto; background:var(--surface2); color:var(--text); border:1px solid var(--border); padding:4px 10px; border-radius:6px; font-size:0.75rem; cursor:pointer; font-weight:600; display:flex; align-items:center; gap:4px; transition:0.2s;" onmouseover="this.style.borderColor='var(--accent)'" onmouseout="this.style.borderColor='var(--border)'">🛑 Exit to Dashboard</button>`;
    }
    html += `<div class="submitted-banner" style="display:flex; align-items:center; flex-wrap:wrap; gap:10px;">
      <div>✓ You have submitted your answers. You can still view responses.</div>
      ${endTestBtn}
    </div>`;
  }

  if (isCompactMode) {
    // ══ COMPACT OMR BUBBLE MODE ══
    html += `<div class="omr-compact-wrap">`;

    // Question number header
    html += `<div class="omr-compact-qnum">
      Q${qid.replace(/[^0-9]/g,'')}
      <span class="omr-q-idx">${qIdx+1} / ${room.questions.length}</span>
      <button class="q-mark-btn ${myMarked?'marked':''}" onclick="toggleMark()" style="font-size:.7rem;" title="Mark for Review (R)">
        ${myMarked ? '🔖 Unmark' : '🔖 Review'}
      </button>
      ${!isReading && myAns ? `<button class="q-mark-btn" onclick="clearAnswer()" style="font-size:.7rem;margin-left:4px;" title="Clear Answer (X)">✕ Clear</button>` : ''}
    </div>`;

    // ── Reading phase: hide bubbles entirely ──
    if (isReading) {
      html += `<div style="padding:1.5rem 1rem;text-align:center;color:var(--muted);font-size:.85rem;line-height:1.8;">
        <div style="font-size:2rem;margin-bottom:.5rem;">📖</div>
        <strong style="color:var(--p2);font-size:.95rem;">Paper Reading Phase</strong><br>
        Answer selection is disabled during reading time.<br>
        Use ← → to browse questions.
      </div>`;
      html += `</div>`; // close omr-compact-wrap
      area.innerHTML = html; area.scrollTop = 0; return;
    }

    // 4 OMR bubbles A B C D
    html += `<div class="omr-compact-grid">`;
    LETTERS.forEach((letter, li) => {
      let cls = 'omr-bubble';
      // My selection
      if (myAns === letter) cls += ' sel-' + PLAYER_COLORS[state.myIdx];
      // Correct/wrong
      if (correctAns) {
        if (letter === correctAns) cls += ' correct';
        else if (myAns === letter && myAns !== correctAns) cls += ' wrong';
      }

      // Other players' selections (badges)
      let badges = '';
      room.players.forEach((p, pi) => {
        const pAns = p.answers?.[qid];
        if (pAns === letter && pi !== state.myIdx) {
          badges += `<div class="omr-bubble-badge" style="background:var(--${PLAYER_COLORS[pi]});color:#000" title="${escHtml(p.name)}">${p.name[0]}</div>`;
        }
      });

      const isLocked = myPlayer.locked_answers && myPlayer.locked_answers[qid];
      let subLabel = myAns === letter ? 'SELECTED' : 'Option';
      if (isLocked) subLabel = 'LOCKED';
      if (isLocked) cls += ' locked-bubble';

      html += `<div class="${cls}" onclick="selectAnswer('${letter}')" ${isLocked ? 'style="opacity:0.75;"' : ''}>
        <div class="omr-bubble-letter">${LETTER_LABELS[li]}</div>
        <div class="omr-bubble-sub">${subLabel}</div>
        ${badges ? `<div class="omr-bubble-badges">${badges}</div>` : ''}
      </div>`;
    });
    html += `</div>`;

    // Compact actions (skip / clear / reveal)
    const isLocked = myPlayer.locked_answers && myPlayer.locked_answers[qid];
    if (room.status === 'active') {
      html += `<div class="omr-compact-actions">
        <button class="btn-action warn" onclick="skipQuestion()" ${state.submitted?'disabled':''}>⏭ Skip</button>
        <button class="btn-action" onclick="clearAnswer()" ${(state.submitted || isLocked)?'disabled':''}>${myAns?'✕ Clear':'Clear'}</button>
        ${revealed < 1 ? `<button class="btn-action reveal" onclick="revealAnswer(1)">👁 Reveal</button>` : ''}
      </div>`;
    }

    // Inline reveal vote (compact)
    html += buildInlineRevealVoteHtml(qid);

    // Reveal section (compact)
    if (revealed >= 1 || room.status === 'finished') {
      html += `<div class="reveal-section" style="width:100%;max-width:320px;">
        <div class="reveal-header">✅ Correct: ${correctAns ? LETTER_LABELS[LETTERS.indexOf(correctAns)] : '?'}</div>
        <div class="answer-compare">`;
      room.players.forEach((p, pi) => {
        const pAns = (pi === state.myIdx) ? myAns : p.answers?.[qid];
        const isCorrect = pAns === correctAns;
        const isMe = pi === state.myIdx;
        html += `<div class="pac">
          <div class="pac-name" style="color:var(--${PLAYER_COLORS[pi]})">${escHtml(p.name)}${isMe?' (You)':''}</div>
          <div class="pac-answer">
            ${pAns
              ? `<span class="${isCorrect?'correct':'wrong'}">${LETTER_LABELS[LETTERS.indexOf(pAns)]} ${isCorrect?'✅':'❌'}</span>`
              : `<span class="notdone">— Not Answered</span>`}
          </div>
        </div>`;
      });
      html += `</div></div>`;
    }

    // Nav row
    html += `<div class="nav-row" style="width:100%;max-width:480px;">
      <button class="btn-nav" onclick="prevQuestion()" ${qIdx===0?'disabled':''}>← Prev</button>
      <span class="q-counter">${qIdx+1} of ${room.questions.length}</span>
      <button class="btn-nav next" onclick="nextQuestion()" ${qIdx===room.questions.length-1?'disabled':''}>Next →</button>
    </div>`;

    html += `</div>`; // close .omr-compact-wrap

  } else {
    // ══ FULL QUESTION CARD MODE (original) ══

    // Header
    html += `<div class="q-header">
      <div class="q-number">Q${qid.replace(/[^0-9]/g,'')}</div>
      <div class="q-subject">${qIdx+1} / ${room.questions.length}</div>
      <button class="q-mark-btn ${myMarked?'marked':''}" onclick="toggleMark()" title="Mark for Review (R / F)">
        ${myMarked ? '🔖 Unmark' : '🔖 Review'}
      </button>
      ${myAns ? `<button class="q-mark-btn" onclick="clearAnswer()" title="Clear Answer (X)" style="margin-left:4px;">✕ Clear</button>` : ''}
    </div>`;

    // Question text
    const questionText = room.question_texts?.[qid] || null;
    if (questionText) {
      html += `<div class="q-text">
        <strong style="font-family:'JetBrains Mono',monospace;color:var(--muted);font-size:.75rem;">Question ${qid.replace(/[^0-9]/g,'')}</strong>
        <div style="margin-top:.5rem;">${escHtml(questionText)}</div>
      </div>`;
    } else {
      html += `<div class="q-text">
        <strong style="font-family:'JetBrains Mono',monospace;color:var(--muted);font-size:.75rem;">Question ${qid.replace(/[^0-9]/g,'')}</strong>
        <div style="margin-top:.5rem;color:var(--muted);font-size:.85rem;font-style:italic;">
          (Question text not included in answer-key JSON — answer the OMR sheet based on your question paper.)
        </div>
      </div>`;
    }

    // ── Reading phase: hide options entirely ──
    if (isReading) {
      html += `<div style="padding:2rem 1rem;text-align:center;color:var(--muted);font-size:.85rem;line-height:1.8;background:var(--surface);border-radius:var(--radius);margin:.5rem 0;">
        <div style="font-size:2rem;margin-bottom:.5rem;">📖</div>
        <strong style="color:var(--p2);font-size:.95rem;">Paper Reading Phase</strong><br>
        Answer selection is disabled during reading time.<br>
        Use ← → to browse questions freely.
      </div>`;
      html += `<div class="nav-row">
        <button class="btn-nav" onclick="prevQuestion()" ${qIdx===0?'disabled':''}>← Prev</button>
        <span class="q-counter">${qIdx+1} of ${room.questions.length}</span>
        <button class="btn-nav next" onclick="nextQuestion()" ${qIdx===room.questions.length-1?'disabled':''}>Next →</button>
      </div>`;
      area.innerHTML = html; area.scrollTop = 0; return;
    }

    // Options A B C D
    const qOptions = room.options?.[qid] || null;
    html += `<div class="options-grid">`;
    LETTERS.forEach((letter, li) => {
      let cls = 'opt';
      // My selection
      if (myAns === letter) cls += ' selected-' + PLAYER_COLORS[state.myIdx];
      // Correct/wrong highlight
      if (correctAns) {
        if (letter === correctAns) cls += ' correct';
        else if (myAns === letter && myAns !== correctAns) cls += ' wrong-' + PLAYER_COLORS[state.myIdx];
      }

      // Other players' selections
      let badges = '';
      room.players.forEach((p, pi) => {
        const pAns = p.answers?.[qid];
        if (pAns === letter && pi !== state.myIdx) {
          badges += `<div class="opt-badge" style="background:var(--${PLAYER_COLORS[pi]});color:#000" title="${escHtml(p.name)}">${p.name[0]}</div>`;
        }
      });

      // Option text: use actual text from JSON if available, otherwise show placeholder
      let optText = qOptions?.[li] || null;
      const isLocked = myPlayer.locked_answers && myPlayer.locked_answers[qid];
      if (isLocked) optText = optText ? optText + '  🔒' : '(Option ' + LETTER_LABELS[li] + ')  🔒';
      
      const optDisplay = optText
        ? `<div class="opt-text">${escHtml(optText)}</div>`
        : `<div class="opt-text" style="color:var(--muted);font-size:.85rem;font-style:italic;">(Option ${LETTER_LABELS[li]})</div>`;

      html += `<div class="${cls}" onclick="selectAnswer('${letter}')">
        <div class="opt-letter">${LETTER_LABELS[li]}</div>
        ${optDisplay}
        ${badges ? `<div class="opt-badges">${badges}</div>` : ''}
      </div>`;
    });
    html += `</div>`;

    // Action row
    const isLocked = myPlayer.locked_answers && myPlayer.locked_answers[qid];
    if (room.status === 'active') {
      html += `<div class="action-row">
        <button class="btn-action warn" onclick="skipQuestion()" ${state.submitted?'disabled':''}>⏭ Skip</button>
        <button class="btn-action" onclick="clearAnswer()" ${(state.submitted || isLocked)?'disabled':''}>${myAns?'✕ Clear':'Clear'}</button>
        ${revealed < 1 ? `<button class="btn-action reveal" onclick="revealAnswer(1)">👁 Reveal Answer</button>` : ''}
      </div>`;
    }

    // Inline reveal vote (full mode)
    html += buildInlineRevealVoteHtml(qid);

    // Reveal section
    if (revealed >= 1 || room.status === 'finished') {
      html += `<div class="reveal-section">
        <div class="reveal-header">✅ Correct Answer: ${correctAns ? LETTER_LABELS[LETTERS.indexOf(correctAns)] : '?'}</div>
        <div class="sb-label" style="margin-bottom:.6rem;">Player Responses</div>
        <div class="answer-compare">`;
      room.players.forEach((p, pi) => {
        const pAns = (pi === state.myIdx) ? myAns : p.answers?.[qid];
        const isCorrect = pAns === correctAns;
        const isMe = pi === state.myIdx;
        html += `<div class="pac">
          <div class="pac-name" style="color:var(--${PLAYER_COLORS[pi]})">${escHtml(p.name)}${isMe?' (You)':''}</div>
          <div class="pac-answer">
            ${pAns
              ? `<span class="${isCorrect?'correct':'wrong'}">${LETTER_LABELS[LETTERS.indexOf(pAns)]} ${isCorrect?'✅':'❌'}</span>`
              : `<span class="notdone">— Not Answered</span>`}
          </div>
        </div>`;
      });
      html += `</div></div>`;
    }

    // Nav row
    html += `<div class="nav-row">
      <button class="btn-nav" onclick="prevQuestion()" ${qIdx===0?'disabled':''}>← Prev</button>
      <span class="q-counter">${qIdx+1} of ${room.questions.length}</span>
      <button class="btn-nav next" onclick="nextQuestion()" ${qIdx===room.questions.length-1?'disabled':''}>Next →</button>
    </div>`;
  }

  area.innerHTML = html;
  area.scrollTop = 0;
}

// ══════════════════════════════════════════════════════════════
//  ANSWER ACTIONS
// ══════════════════════════════════════════════════════════════
function selectAnswer(letter) {
  if (state.submitted) { showToast('Already submitted', 'info'); return; }
  if (state.room.status !== 'active') return;
  // Block answers during reading phase
  const _now = Math.floor(Date.now() / 1000);
  const _el = Math.max(0, _now - (state.room.started_at || _now) - (state.room.total_paused_sec || 0));
  if (state.room.exam_mode && _el < 300) { showToast('📖 Reading phase — answering starts soon!', 'info'); return; }
  const qid = state.currentQ;
  // Lock answers on revealed questions
  if (state.room.revealed?.[qid] >= 1) { showToast('Answer is locked — already revealed', 'info'); return; }
  
  const myPlayer = state.room.players[state.myIdx];
  if (myPlayer && myPlayer.locked_answers && myPlayer.locked_answers[qid]) {
    showToast('This question was answered in a previous session and is locked.', 'warn');
    return;
  }

  // Toggle
  if (state.localAnswers[qid] === letter) {
    state.localAnswers[qid] = null;
    pushAnswerUpdate(qid, '', false, false);
  } else {
    state.localAnswers[qid] = letter;
    state.localSkipped[qid] = false;
    pushAnswerUpdate(qid, letter, false, false);
  }
  buildQGrid();
  renderQuestion();
  updateScoreChips();
}

function clearAnswer() {
  if (state.submitted) return;
  const qid = state.currentQ;
  if (state.room.revealed?.[qid] >= 1) { showToast('Answer is locked — already revealed', 'info'); return; }
  
  const myPlayer = state.room.players[state.myIdx];
  if (myPlayer && myPlayer.locked_answers && myPlayer.locked_answers[qid]) {
    showToast('This question is locked.', 'warn');
    return;
  }
  
  state.localAnswers[qid] = null;
  pushAnswerUpdate(qid, '', false, false);
  buildQGrid();
  renderQuestion();
}

function toggleMark() {
  const qid = state.currentQ;
  state.localMarked[qid] = !state.localMarked[qid];
  pushAnswerUpdate(qid, state.localAnswers[qid], state.localMarked[qid], state.localSkipped[qid]);
  buildQGrid();
  renderQuestion();
}

function skipQuestion() {
  if (state.submitted) return;
  const qid = state.currentQ;
  state.localSkipped[qid] = true;
  state.localAnswers[qid] = null;
  pushAnswerUpdate(qid, '', false, true);
  buildQGrid();
  nextQuestion();
}

// Reveal vote system — inline (no popup)
let revealVoteState = { active: false, qid: null, savedQIdx: -1, autoRejectTimer: null };

function buildInlineRevealVoteHtml(qid) {
  const room = state.room;
  if (!room || !room.pending_reveal) return '';
  const pending = room.pending_reveal;
  if (pending.qid !== qid) return '';

  const age = Math.floor(Date.now()/1000) - pending.requested_at;
  if (age > 15) return '';

  const myIdx = state.myIdx;
  const isRequester = pending.requester_idx === myIdx;
  const hasVoted = pending.votes && pending.votes[myIdx] !== undefined;
  const votePhaseStarted = age >= 2; // 2s delay before voting
  const timeLeft = Math.max(0, 12 - age); // 10s vote + 2s delay

  let html = '<div class="inline-reveal-vote">';

  if (isRequester) {
    if (!votePhaseStarted) {
      html += `<div class="irv-status requesting">
        <span class="irv-spinner"></span> Requesting reveal for Q${qid.replace(/\D/g, '')}...
      </div>`;
    } else {
      html += `<div class="irv-status waiting">
        <span class="irv-icon">🕒</span> Waiting for votes... <span class="irv-time">${timeLeft}s</span>
      </div>`;
    }
  } else if (hasVoted) {
    html += `<div class="irv-status voted">
      <span class="irv-icon">✅</span> You voted — waiting for others... <span class="irv-time">${timeLeft}s</span>
    </div>`;
  } else if (votePhaseStarted) {
    html += `<div class="irv-status voting">
      <div class="irv-title">👀 ${escHtml(pending.requester_name)} wants to reveal Q${qid.replace(/\D/g, '')}</div>
      <div class="irv-actions">
        <button class="irv-btn accept" onclick="acceptReveal()">✅ Accept</button>
        <button class="irv-btn reject" onclick="rejectReveal()">❌ Reject</button>
        <span class="irv-time">${timeLeft}s</span>
      </div>
    </div>`;
  } else {
    html += `<div class="irv-status requesting">
      <span class="irv-spinner"></span> ${escHtml(pending.requester_name)} is requesting reveal...
    </div>`;
  }

  html += '</div>';
  return html;
}

function checkPendingReveal() {
  const room = state.room;
  if (!room || !room.pending_reveal) {
    if (revealVoteState.active) {
      clearInterval(revealVoteState.autoRejectTimer);
      const wasQid = revealVoteState.qid;
      const savedQIdx = revealVoteState.savedQIdx;
      
      revealVoteState.active = false;
      revealVoteState.qid = null;
      revealVoteState.savedQIdx = -1;

      // Show result toast
      if (room && room.revealed && room.revealed[wasQid] >= 1) {
        showToast('Answer revealed by vote! ✅', 'ok');
      } else {
        showToast('Reveal request was rejected ❌', 'error');
      }

      // Navigate back to the question user was working on with a 7s delay
      if (savedQIdx >= 0 && savedQIdx !== state.currentQIdx) {
        // Show result temporarily, then show countdown
        setTimeout(() => {
          showCountdownToast('Returning to your question in {s}s...', 7, () => {
            if (state.currentQIdx !== savedQIdx) {
              goToQuestion(savedQIdx);
            }
          });
        }, 3000);
      }
      renderQuestion();
    }
    return;
  }

  const pending = room.pending_reveal;
  const myIdx = state.myIdx;
  const age = Math.floor(Date.now()/1000) - pending.requested_at;
  if (age > 15) return;

  // First time seeing this pending reveal — auto-navigate
  if (!revealVoteState.active || revealVoteState.qid !== pending.qid) {
    revealVoteState.savedQIdx = state.currentQIdx;
    revealVoteState.active = true;
    revealVoteState.qid = pending.qid;

    // Play ringing alert for incoming requests
    if (pending.requester_idx !== myIdx) {
      sfxRingingAlert();
    }

    // Navigate to the requested question
    const qIdx = room.questions.indexOf(pending.qid);
    if (qIdx >= 0 && qIdx !== state.currentQIdx) {
      goToQuestion(qIdx);
    }
    // Close calculator if open
    if (calcOpen) toggleCalc();

    // Start auto-reject timer for non-requesters who haven't voted
    clearInterval(revealVoteState.autoRejectTimer);
    if (pending.requester_idx !== myIdx) {
      const hasVoted = pending.votes && pending.votes[myIdx] !== undefined;
      if (!hasVoted) {
        revealVoteState.autoRejectTimer = setTimeout(() => {
          if (revealVoteState.active) rejectReveal();
        }, Math.max(1000, (12 - age) * 1000));
      }
    }
  }

  // The inline UI is already rendered by renderQuestion() via buildInlineRevealVoteHtml
}

async function acceptReveal() {
  clearInterval(revealVoteState.autoRejectTimer);
  try {
    await api({action:'respond_reveal', room_id:ROOM_ID, player_id:MY_CODE, accept: true});
  } catch(e) {}
  await syncRoom();
  renderQuestion();
}

async function rejectReveal() {
  clearInterval(revealVoteState.autoRejectTimer);
  try {
    await api({action:'respond_reveal', room_id:ROOM_ID, player_id:MY_CODE, accept: false});
  } catch(e) {}
  await syncRoom();
  renderQuestion();
}

// Debounced server push
let pushTimer = {};
function pushAnswerUpdate(qid, answer, marked, skipped) {
  clearTimeout(pushTimer[qid]);
  pushTimer[qid] = setTimeout(async () => {
    try {
      await api({
        action: 'update_answer',
        room_id: ROOM_ID,
        player_id: MY_CODE,
        q_id: qid,
        answer: answer,
        marked: marked,
        skipped: skipped,
      });
    } catch(e) { /* silent — will resync */ }
  }, 400);
}

async function revealAnswer(level) {
  try {
    const res = await api({action:'reveal_answer', room_id:ROOM_ID, player_id:MY_CODE, q_id:state.currentQ, level});
    if (res.mode === 'instant') {
      await syncRoom();
      renderQuestion();
      showToast('Answer revealed!', 'ok');
    } else {
      showToast('Reveal requested — waiting for all players to vote...', 'ok');
    }
  } catch(e) { showToast('Failed to request reveal', 'error'); }
}


// ══════════════════════════════════════════════════════════════
//  CALCULATOR
// ══════════════════════════════════════════════════════════════
let calcExpr = '';
let calcOpen = false;
let currentSidebarTool = 'voice';

function switchSidebarTool(tool) {
  currentSidebarTool = tool;
  ['voice','status','calc'].forEach(t => {
    const tab  = document.getElementById('stab-' + t);
    const pane = document.getElementById('pane-' + t);
    if (tab)  tab.classList.toggle('active',  t === tool);
    if (pane) pane.classList.toggle('active', t === tool);
  });
  calcOpen = (tool === 'calc');
  if (tool === 'status') renderTeamStatuses();
}

function toggleToolsPanel() {
  const panel = document.getElementById('tools-panel');
  const fab = document.getElementById('tools-fab');
  if (!panel) return;
  const isOpen = panel.classList.toggle('open');
  if (fab) fab.classList.toggle('active', isOpen);
  if (isOpen) {
    // Hide the unread badge once opened
    const badge = document.getElementById('tools-fab-badge');
    if (badge) badge.classList.remove('show');
    // Scroll tools into view if needed
    panel.scrollIntoView({behavior:'smooth', block:'start'});
  }
}

function calcUpdateDisplay() {
  const display = calcExpr || '0';
  document.getElementById('calc-result').textContent = display;
}

function calcInput(ch) {
  calcExpr += ch;
  calcUpdateDisplay();
}

function calcClear() {
  calcExpr = '';
  document.getElementById('calc-history').textContent = '';
  document.getElementById('calc-result').textContent = '0';
}

function calcBackspace() {
  calcExpr = calcExpr.slice(0, -1);
  calcUpdateDisplay();
}

function factorial(n) {
  n = Math.round(n);
  if (n < 0) return NaN;
  if (n <= 1) return 1;
  if (n > 170) return Infinity;
  let r = 1;
  for (let i = 2; i <= n; i++) r *= i;
  return r;
}

// ── Safe Math Expression Evaluator ──
// Recursive-descent parser that only allows numbers, operators, parentheses.
// Blocks arbitrary code execution (unlike new Function()).
function safeCalcEval(expr) {
  let pos = 0;
  const str = expr.replace(/\s+/g, '');

  function peek() { return str[pos]; }
  function consume(ch) {
    if (str[pos] === ch) { pos++; return true; }
    return false;
  }

  // expr = term (('+' | '-') term)*
  function parseExpr() {
    let val = parseTerm();
    while (true) {
      if (consume('+'))      val += parseTerm();
      else if (consume('-')) val -= parseTerm();
      else break;
    }
    return val;
  }

  // term = power (('*' | '/') power)*
  function parseTerm() {
    let val = parsePower();
    while (true) {
      if (consume('*') && consume('*')) { val = Math.pow(val, parsePower()); }
      else if (str[pos - 1] === '*') { val *= parsePower(); }
      else if (consume('/')) { val /= parsePower(); }
      else break;
    }
    return val;
  }

  // power = unary
  function parsePower() {
    return parseUnary();
  }

  // unary = ('+' | '-')? atom
  function parseUnary() {
    if (consume('-')) return -parseAtom();
    if (consume('+')) return parseAtom();
    return parseAtom();
  }

  // atom = number | '(' expr ')'
  function parseAtom() {
    // Parenthesized expression
    if (consume('(')) {
      const val = parseExpr();
      consume(')'); // tolerant of missing closing paren
      return val;
    }

    // Number (including decimals)
    const start = pos;
    while (pos < str.length && (/[0-9.]/).test(str[pos])) pos++;
    if (pos === start) return NaN;
    return parseFloat(str.substring(start, pos));
  }

  const result = parseExpr();
  if (pos < str.length) return NaN; // Unparsed trailing characters = invalid
  return result;
}

function calcFn(fn) {
  let val;
  try {
    val = safeCalcEval(calcExpr);
  } catch(e) {
    val = NaN;
  }
  if (isNaN(val) && fn !== 'neg' && fn !== 'pow') {
    document.getElementById('calc-result').textContent = 'Error';
    return;
  }
  let result;
  let label = '';
  switch(fn) {
    case 'sqrt':  result = Math.sqrt(val);    label = '√(' + calcExpr + ')'; break;
    case 'cbrt':  result = Math.cbrt(val);    label = '∛(' + calcExpr + ')'; break;
    case 'sq':    result = val * val;          label = '(' + calcExpr + ')²'; break;
    case 'cube':  result = val * val * val;    label = '(' + calcExpr + ')³'; break;
    case 'pct':   result = val / 100;          label = '(' + calcExpr + ')%'; break;
    case 'inv':   result = 1 / val;            label = '1/(' + calcExpr + ')'; break;
    case 'neg':
      if (calcExpr.startsWith('-')) calcExpr = calcExpr.slice(1);
      else calcExpr = '-' + calcExpr;
      calcUpdateDisplay();
      return;
    case 'pow':
      calcExpr += '**';
      calcUpdateDisplay();
      return;
    case 'fact':  result = factorial(val);     label = '(' + calcExpr + ')!'; break;
    default: return;
  }
  document.getElementById('calc-history').textContent = label;
  calcExpr = String(result);
  document.getElementById('calc-result').textContent = calcExpr;
}

function calcEval() {
  if (!calcExpr) return;
  try {
    const expr = calcExpr;
    const result = safeCalcEval(expr);
    if (isNaN(result)) throw new Error('Invalid expression');
    document.getElementById('calc-history').textContent = expr + ' =';
    calcExpr = String(result);
    document.getElementById('calc-result').textContent = calcExpr;
  } catch(e) {
    document.getElementById('calc-result').textContent = 'Error';
    calcExpr = '';
  }
}

// Keyboard handler for calculator
document.addEventListener('keydown', (e) => {
  if (!calcOpen) return;
  // Don't capture if user is typing in an input/textarea
  const tag = document.activeElement?.tagName;
  if (tag === 'INPUT' || tag === 'TEXTAREA') return;

  const key = e.key;
  if (/^[0-9]$/.test(key)) { calcInput(key); e.preventDefault(); }
  else if (key === '+' || key === '-') { calcInput(key); e.preventDefault(); }
  else if (key === '*') { calcInput('*'); e.preventDefault(); }
  else if (key === '/') { calcInput('/'); e.preventDefault(); }
  else if (key === '.') { calcInput('.'); e.preventDefault(); }
  else if (key === '(' || key === ')') { calcInput(key); e.preventDefault(); }
  else if (key === '%') { calcFn('pct'); e.preventDefault(); }
  else if (key === '^') { calcExpr += '**'; calcUpdateDisplay(); e.preventDefault(); }
  else if (key === 'Enter' || key === '=') { calcEval(); e.preventDefault(); }
  else if (key === 'Backspace') { calcBackspace(); e.preventDefault(); }
  else if (key === 'Escape') { calcClear(); e.preventDefault(); }
  else if (key === 'Delete') { calcClear(); e.preventDefault(); }
});


// ══════════════════════════════════════════════════════════════
function goToQuestion(idx) {
  const room = state.room;
  if (!room || idx < 0 || idx >= room.questions.length) return;
  state.currentQIdx = idx;
  state.currentQ    = room.questions[idx];
  buildQGrid();
  renderQuestion();
  if (window.innerWidth < 768) closeSidebar();
  // Tell server which question we're on
  pushCurrentQuestion(state.currentQ);
  // Auto-scroll PDF to the page containing this question
  scrollPdfToQuestion(state.currentQ);
}

function nextQuestion() {
  if (state.currentQIdx < state.room.questions.length - 1) goToQuestion(state.currentQIdx + 1);
}
function prevQuestion() {
  if (state.currentQIdx > 0) goToQuestion(state.currentQIdx - 1);
}

// Keyboard shortcuts (comprehensive)
document.addEventListener('keydown', e => {
  // Skip if typing in an input
  if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
  // Skip if not on exam screen
  const onExam = document.getElementById('scr-exam')?.classList.contains('active');

  // ? — toggle shortcut help overlay (works on exam screen)
  if (e.key === '?' && onExam) { toggleShortcutsOverlay(); return; }

  if (!onExam) return;

  switch(e.key) {
    case 'ArrowRight': case 'ArrowDown': e.preventDefault(); nextQuestion(); sfxNav(); break;
    case 'ArrowLeft':  case 'ArrowUp':   e.preventDefault(); prevQuestion(); sfxNav(); break;
    case 'a': case 'A': selectAnswer('a'); sfxClick(); break;
    case 'b': case 'B': selectAnswer('b'); sfxClick(); break;
    case 'c': case 'C': selectAnswer('c'); sfxClick(); break;
    case 'd': case 'D': selectAnswer('d'); sfxClick(); break;
    case 'f': case 'F': toggleMark(); sfxFlag(); break;
    case 'r': case 'R': toggleMark(); sfxFlag(); break;  // R = mark for review (alias)
    case 's': case 'S': skipQuestion(); break;
    case 'x': case 'X': clearAnswer(); break;  // X = clear answer
  }
});

// ══════════════════════════════════════════════════════════════
//  SUBMIT
// ══════════════════════════════════════════════════════════════
function openSubmitModal() {
  const room = state.room;
  if (!room) return;
  // Block submission during reading phase
  if (room.exam_mode && room.started_at) {
    const _t = Math.floor(Date.now() / 1000);
    const _e = Math.max(0, _t - room.started_at - (room.total_paused_sec || 0));
    if (_e < 300) { showToast('📖 Reading phase — cannot submit yet!', 'info'); return; }
  }
  const myP = room.players[state.myIdx];
  let unanswered = 0;
  room.questions.forEach(qid => {
    if (!state.localAnswers[qid] && !state.localSkipped[qid]) unanswered++;
  });
  document.getElementById('submit-modal-msg').textContent =
    `You have ${unanswered} unanswered question(s). Submitting will lock your answers.`;

  // Player status
  const list = document.getElementById('submit-status-list');
  list.innerHTML = room.players.map((p, i) =>
    `<div style="display:flex;align-items:center;gap:.5rem;padding:.4rem .5rem;background:var(--surface2);border-radius:8px;">
      <div style="width:8px;height:8px;border-radius:50%;background:var(--${PLAYER_COLORS[i]})"></div>
      <span style="flex:1;font-size:.83rem;">${escHtml(p.name)}</span>
      <span style="font-size:.75rem;font-family:'JetBrains Mono',monospace;color:${p.submitted?'var(--ok)':'var(--muted)'}">
        ${p.submitted ? '✓ Submitted' : 'Pending'}
      </span>
    </div>`).join('');

  document.getElementById('modal-submit').classList.add('open');
}

async function confirmSubmit() {
  closeAllModals();
  state.submitted = true;
  try {
    const d = await api({action:'submit_player', room_id:ROOM_ID, player_id:MY_CODE});
    if (d.all_done) {
      await syncRoom();
      showResultScreen();
    } else {
      showToast('✓ Your answers locked. Waiting for others...', 'ok');
      buildQGrid();
      renderQuestion();
    }
  } catch(e) { showToast('Submit failed. Please retry.', 'error'); state.submitted = false; }
}

function forceEndTest() {
  if (!confirm("Are you sure you want to exit the test room now?\nYou have already submitted your answers. You can view the results later from the dashboard once everyone has finished.")) return;
  window.location.href = 'test.php';
}

// ══════════════════════════════════════════════════════════════
//  SYNC
// ══════════════════════════════════════════════════════════════
async function syncRoom() {
  try {
    const d = await api({action:'sync', room_id:ROOM_ID, player_id:MY_CODE, session_id:SESSION_ID});
    if (d.error) return;
    // Merge server state but keep local answers optimistically
    if (state.room) {
      // Preserve local changes
      const myServerPlayer = d.players?.[state.myIdx];
      if (myServerPlayer) {
        // Only take server answers if we haven't changed locally
        // For other players, always take server
      }
    }
    state.room = d;
    // Sync my own submitted state
    if (d.players?.[state.myIdx]?.submitted) state.submitted = true;
    // Sync local answers from server for other players (already in room.players)
    // Keep our own local for responsiveness
    const serverMyAnswers = d.players?.[state.myIdx]?.answers || {};
    // Only update local if server has newer data we don't have
    Object.keys(serverMyAnswers).forEach(qid => {
      if (!(qid in state.localAnswers) || state.localAnswers[qid] === undefined) {
        state.localAnswers[qid] = serverMyAnswers[qid];
      }
    });
  } catch(e) { /* ignore sync errors */ }
}

async function syncAndUpdate() {
  await syncRoom();
  const room = state.room;
  if (!room) return;

  if (room.status === 'active') {
    buildQGrid();
    buildStats();
    buildPlayerTabs();
    updateScoreChips();
    updateOnlineDots();
    renderQuestion();
    // Sync chat + detect new messages
    detectNewChatMessages();
    renderChat();
    // Check pending reveal votes
    checkPendingReveal();
    // Check if all submitted
    if (room.players.every(p => p.submitted) && !document.getElementById('scr-result')?.classList.contains('active')) {
      clearInterval(state.syncTimer);
      clearInterval(state.timerTick);
      showResultScreen();
    }
  } else if (room.status === 'waiting') {
    renderWaiting();
    // If someone else started, enter exam
  } else if (room.status === 'finished') {
    if (document.getElementById('scr-result')?.classList.contains('active')) return;
    clearInterval(state.syncTimer);
    clearInterval(state.timerTick);
    showResultScreen();
  }

  // Transition from waiting to active
  if (room.status === 'active' && document.getElementById('scr-waiting')?.classList.contains('active')) {
    enterExam();
  }

  // Update team status panel if open
  if (currentSidebarTool === 'status') renderTeamStatuses();

  // Alert when a teammate asks for help
  if (room.status === 'active') {
    room.players.forEach((p, i) => {
      if (i === state.myIdx) return;
      const prevStatus = state._lastPlayerStatus?.[i];
      if (p.status === 'help' && prevStatus !== 'help') {
        showExamAlert('🆘 ' + escHtml(p.name) + ' needs help!', 'warn');
        typeof sfxRingingAlert === 'function' && sfxRingingAlert();
      }
    });
    if (!state._lastPlayerStatus) state._lastPlayerStatus = {};
    room.players.forEach((p, i) => { state._lastPlayerStatus[i] = p.status || 'active'; });
  }
}

// ══════════════════════════════════════════════════════════════
//  SCORE CHIPS & ONLINE DOTS
// ══════════════════════════════════════════════════════════════
function updateScoreChips() {
  const room = state.room;
  if (!room || room.status === 'finished') return;
  const chips = document.getElementById('score-chips');
  if (!chips) return;
  // Count answered (not score — we don't have answer key during test)
  chips.innerHTML = room.players.map((p, i) => {
    const ans = (i === state.myIdx) ? state.localAnswers : p.answers;
    const count = Object.values(ans || {}).filter(v => v).length;
    return `<div class="sc-chip p${i}" title="${escHtml(p.name)}: ${count} answered">
      ${escHtml(p.name.substring(0,6))}: ${count}
    </div>`;
  }).join('');
}

function updateOnlineDots() {
  const room = state.room;
  if (!room) return;
  const dots = document.getElementById('online-dots');
  if (!dots) return;
  dots.innerHTML = room.players.map((p, i) => {
    const online = room.online?.[i];
    return `<div class="online-dot ${online?'on':''}" style="background:var(--${PLAYER_COLORS[i]})" 
      title="${escHtml(p.name)} ${online?'online':'offline'}"></div>`;
  }).join('');
}

// ══════════════════════════════════════════════════════════════
//  CHAT
// ══════════════════════════════════════════════════════════════
function toggleChat() {
  const panel = document.getElementById('chat-panel');
  state.chatOpen = !state.chatOpen;
  panel.classList.toggle('open', state.chatOpen);
  if (state.chatOpen) {
    // Clear unread badge
    state.chatUnread = 0;
    const badge = document.getElementById('chat-badge');
    badge.classList.remove('show');
    badge.textContent = '0';
    renderChat();
    document.getElementById('chat-input').focus();
    // Show BRB hint on first chat open
    if (!state.chatHintShown) {
      state.chatHintShown = true;
      showToast('Tip: Type "BRB" to pause the test for 5 min (1 per player)', 'info');
    }
  }
}

function renderChat() {
  const room = state.room;
  if (!room) return;
  const msgs = room.chat || [];
  const el = document.getElementById('chat-messages');
  const wasAtBottom = el.scrollHeight - el.scrollTop <= el.clientHeight + 50;
  el.innerHTML = msgs.map(m => {
    const t = new Date(m.ts * 1000);
    const tStr = t.getHours()+':'+pad(t.getMinutes());
    return `<div class="chat-msg">
      <div class="chat-from">${escHtml(m.from)}</div>
      <div class="chat-text">${escHtml(m.msg)}</div>
      <div class="chat-time">${tStr}</div>
    </div>`;
  }).join('') || '<div style="color:var(--muted);font-size:.8rem;text-align:center;padding:1rem;">No messages yet</div>';
  if (wasAtBottom) {
    el.scrollTo({ top: el.scrollHeight, behavior: 'smooth' });
  }
}

async function sendChat() {
  const input = document.getElementById('chat-input');
  const msg = input.value.trim();
  if (!msg) return;
  input.value = '';

  // BRB detection
  if (msg.toUpperCase() === 'BRB') {
    try {
      const res = await api({action:'brb', room_id:ROOM_ID, player_id:MY_CODE});
      if (res.success) {
        showToast('⏸ BRB activated! Test paused for 5 minutes.', 'ok');
        await syncRoom();
        state.lastChatCount = (state.room?.chat || []).length;
        renderChat();
        tickTimer();
      }
    } catch(e) {
      showToast(e?.message || 'BRB failed — you may have already used yours', 'error');
    }
    return;
  }

  try {
    await api({action:'send_message', room_id:ROOM_ID, player_id:MY_CODE, message:msg});
    await syncRoom();
    // Update lastChatCount so our own message doesn't trigger a notification
    state.lastChatCount = (state.room?.chat || []).length;
    renderChat();
  } catch(e) {}
}

// ══════════════════════════════════════════════════════════════
//  CHAT NOTIFICATIONS
// ══════════════════════════════════════════════════════════════
function detectNewChatMessages() {
  const room = state.room;
  if (!room) return;
  const msgs = room.chat || [];
  const newCount = msgs.length;
  const prevCount = state.lastChatCount;

  if (newCount > prevCount && prevCount > 0) {
    // Find messages that are new
    const newMsgs = msgs.slice(prevCount);
    // Filter out messages from ourselves  
    const myPlayer = room.players[state.myIdx];
    const myName = myPlayer ? myPlayer.name : '';
    const otherMsgs = newMsgs.filter(m => m.from !== myName);

    if (otherMsgs.length > 0) {
      // Play notification sound
      playNotifSound();

      if (!state.chatOpen) {
        // Auto-open chat panel on new message
        toggleChat();
        showToast('💬 New message from ' + otherMsgs[otherMsgs.length-1].from, 'info');
      }
    }
  }
  state.lastChatCount = newCount;
}

function showChatNotification(from, msg) {
  const container = document.getElementById('chat-notifs');
  const el = document.createElement('div');
  el.className = 'chat-notif';
  el.innerHTML = `
    <div class="chat-notif-from">💬 ${escHtml(from)}</div>
    <div class="chat-notif-text">${escHtml(msg)}</div>
  `;
  el.addEventListener('click', () => {
    el.classList.add('out');
    setTimeout(() => el.remove(), 350);
    if (!state.chatOpen) toggleChat();
  });
  container.appendChild(el);

  // Auto-dismiss after 4s
  setTimeout(() => {
    el.classList.add('out');
    setTimeout(() => el.remove(), 350);
  }, 4000);
}

// Synthesized notification ping using Web Audio API (no external files)
let _audioCtx = null;
function playNotifSound() {
  try {
    if (!_audioCtx) _audioCtx = new (window.AudioContext || window.webkitAudioContext)();
    const ctx = _audioCtx;
    const t = ctx.currentTime;

    // Two-tone ping: pleasant notification sound
    const osc1 = ctx.createOscillator();
    const osc2 = ctx.createOscillator();
    const gain = ctx.createGain();

    osc1.type = 'sine';
    osc1.frequency.setValueAtTime(880, t);      // A5
    osc1.frequency.setValueAtTime(1100, t + 0.08); // ~C#6

    osc2.type = 'sine';
    osc2.frequency.setValueAtTime(660, t);      // E5
    osc2.frequency.setValueAtTime(880, t + 0.08);  // A5

    gain.gain.setValueAtTime(0.15, t);
    gain.gain.exponentialRampToValueAtTime(0.01, t + 0.3);

    osc1.connect(gain);
    osc2.connect(gain);
    gain.connect(ctx.destination);

    osc1.start(t);
    osc2.start(t);
    osc1.stop(t + 0.3);
    osc2.stop(t + 0.3);
  } catch(e) { /* Audio not available */ }
}

// ══════════════════════════════════════════════════════════════
//  PUSH CURRENT QUESTION TO SERVER
// ══════════════════════════════════════════════════════════════
let _pushQTimer = null;
function pushCurrentQuestion(qid) {
  clearTimeout(_pushQTimer);
  _pushQTimer = setTimeout(async () => {
    try {
      await api({
        action: 'update_current_q',
        room_id: ROOM_ID,
        player_id: MY_CODE,
        current_q: qid,
      });
    } catch(e) { /* silent */ }
  }, 500);
}

// ══════════════════════════════════════════════════════════════
//  RESULTS
// ══════════════════════════════════════════════════════════════
function showResultScreen() {
  clearInterval(state.syncTimer);
  clearInterval(state.timerTick);
  showScreen('scr-result');

  const room = state.room;
  if (!room) return;
  const ansKey = room.answer_key || {};
  const questions = room.questions || [];

  // Compute scores
  const scores = room.players.map(p => {
    let correct = 0, wrong = 0, skip = 0;
    questions.forEach(qid => {
      const ans = (p.idx === state.myIdx) ? (state.localAnswers[qid] || null) : (p.answers?.[qid] || null);
      const key = ansKey[qid] || null;
      if (!ans) skip++;
      else if (ans === key) correct++;
      else wrong++;
    });
    return { correct, wrong, skip, total: correct };
  });

  const maxScore = Math.max(...scores.map(s => s.total));
  const winnerIdx = scores.findIndex(s => s.total === maxScore);
  const isDraw = scores.filter(s => s.total === maxScore).length > 1;

  // Topbar
  const tTitle = document.getElementById('result-topbar-title');
  const tMeta  = document.getElementById('result-topbar-meta');
  if (tTitle) tTitle.textContent = room.test_name || 'Results';
  if (tMeta)  tMeta.textContent  = `Room ${ROOM_ID} · ${questions.length} Q`;

  if (room.players.length === 1) {
    document.getElementById('result-headline').textContent = 'Test Complete! 🎉';
    document.getElementById('result-sub').textContent = `${scores[0].correct} / ${questions.length} correct`;
  } else if (isDraw) {
    document.getElementById('result-headline').textContent = "It's a Draw! 🤝";
    document.getElementById('result-sub').textContent = `All tied at ${maxScore} / ${questions.length}`;
  } else {
    document.getElementById('result-headline').textContent = room.players[winnerIdx].name + ' Wins! 🏆';
    document.getElementById('result-sub').textContent = `${maxScore} / ${questions.length} correct`;
    launchConfetti();
  }

  // Compact horizontal scorecards
  const row = document.getElementById('scorecard-row');
  row.innerHTML = room.players.map((p, i) => {
    const s = scores[i];
    const isWin = !isDraw && i === winnerIdx;
    const color = PLAYER_COLORS[i];
    const pct = questions.length ? Math.round(s.total / questions.length * 100) : 0;
    return `<div class="scorecard ${isWin?'winner':''}" style="${isWin?'border-color:var(--'+color+')':''}">\n      <div class="crown">👑</div>\n      <div class="sc-name" style="color:var(--${color})">${escHtml(p.name)}</div>\n      <div class="sc-score" style="color:var(--${color})">${s.total}</div>\n      <div class="sc-pct">${pct}%</div>\n      <div class="sc-stats">\n        <span class="sc-stat" style="color:var(--ok)">✓ ${s.correct}</span>\n        <span class="sc-stat" style="color:var(--danger)">✗ ${s.wrong}</span>\n        <span class="sc-stat" style="color:var(--muted)">— ${s.skip}</span>\n      </div>\n    </div>`;
  }).join('');

  // Build review table header
  const thead = document.getElementById('review-thead');
  thead.innerHTML = `<tr>
    <th>#</th><th>Correct</th>
    ${room.players.map(p=>`<th>${escHtml(p.name)}</th>`).join('')}
  </tr>`;

  // Build review table body
  const tbody = document.getElementById('review-tbody');
  tbody.innerHTML = questions.map((qid, idx) => {
    const key = ansKey[qid] || '?';
    const keyLabel = key !== '?' ? LETTER_LABELS[LETTERS.indexOf(key)] : '?';
    const cells = room.players.map((p, pi) => {
      const ans = (pi === state.myIdx) ? (state.localAnswers[qid] || null) : (p.answers?.[qid] || null);
      if (!ans) return `<td class="cell-skip">—</td>`;
      const isOk = ans === key;
      return `<td class="${isOk?'cell-ok':'cell-bad'}">${LETTER_LABELS[LETTERS.indexOf(ans)]} ${isOk?'✅':'❌'}</td>`;
    }).join('');
    return `<tr>
      <td style="color:var(--muted);font-family:'JetBrains Mono',monospace">${qid.replace(/[^0-9]/g,'')}</td>
      <td style="color:var(--ok);font-weight:700">${keyLabel}</td>
      ${cells}
    </tr>`;
  }).join('');

  // Persist result to Firestore for history
  saveResultToFirestore(room, scores, winnerIdx, isDraw);

  // Result PDF/SOL buttons
  const pdfBar = document.getElementById('result-pdf-bar');
  let barHtml = '';
  if (room.pdf_url) {
    const safeName = encodeURIComponent(room.test_name || 'paper');
    barHtml += `<button class="result-pdf-btn" id="res-pdf-btn" onclick="toggleResultPdf()">📄 Question Paper</button>`;
    barHtml += `<a class="result-pdf-dl" href="${escHtml(room.pdf_url)}" download="${safeName}_paper.pdf" target="_blank">⬇ Download Paper</a>`;
  }
  if (room.solution_pdf_url) {
    const safeName = encodeURIComponent(room.test_name || 'solution');
    barHtml += `<button class="result-pdf-btn" id="res-sol-btn" onclick="toggleResultSol()">📖 Solution</button>`;
    barHtml += `<a class="result-pdf-dl" href="${escHtml(room.solution_pdf_url)}" download="${safeName}_solution.pdf" target="_blank">⬇ Download Solution</a>`;
  }
  if (barHtml) {
    pdfBar.innerHTML = barHtml;
    pdfBar.style.display = 'flex';
    pdfBar.style.flexWrap = 'wrap';
    pdfBar.style.gap = '.5rem';
  }

  // UPSC marks — auto-show if tag matches, otherwise show with default GS pattern
  const autoPattern = detectUpscPattern(room) || 'gs';
  renderUpscPanel(autoPattern);
}

// ── Save result to Firestore for history ──────────────────────
async function saveResultToFirestore(room, scores, winnerIdx, isDraw) {
  try {
    if (typeof db === 'undefined') return; // Firebase not loaded
    const user = omrGetStoredUser();
    if (!user) return;
    const myUid = user.uid;

    const players = (room.players || []).map((p, i) => {
      const s = scores[i] || {};
      return {
        name:        p.name || ('Player ' + (i + 1)),
        uid:         (i === state.myIdx) ? myUid : (p.uid || null),
        player_code: p.code || '',
        correct:     s.correct || 0,
        wrong:       s.wrong   || 0,
        skip:        s.skip    || 0,
      };
    });

    const winnerUid = (!isDraw && winnerIdx === state.myIdx) ? myUid : null;

    await db.collection('results').doc(ROOM_ID + '_' + myUid).set({
      room_id:         ROOM_ID,
      test_name:       room.test_name || '',
      played_at:       firebase.firestore.FieldValue.serverTimestamp(),
      all_player_uids: [myUid],
      players:         players,
      winner_uid:      winnerUid,
      player_count:    (room.players || []).length,
      player_code:     MY_CODE,
    }, { merge: true });
  } catch(e) {
    console.warn('Firestore result write failed:', e);
  }
}

function showReviewSection() {
  const el = document.getElementById('review-section');
  if (el) el.scrollIntoView({behavior:'smooth'});
}
function hideReviewSection() {} // review always visible now

function toggleUpscPanel() {
  const panel = document.getElementById('upsc-panel');
  const btn   = document.getElementById('btn-toggle-upsc');
  if (!panel) return;
  const showing = panel.style.display !== 'none';
  panel.style.display = showing ? 'none' : '';
  if (btn) btn.style.borderColor = showing ? '' : 'var(--p0)';
  if (!showing) panel.scrollIntoView({behavior:'smooth'});
}

// Result section PDF/SOL toggles
let resultPdfShown = false, resultSolShown = false;
let resultPdfRendered = false, resultSolRendered = false;

function rebuildResultPdfContainer() {
  const container = document.getElementById('result-pdf-container');
  const room = state.room;
  if (!resultPdfShown && !resultSolShown) {
    container.style.display = 'none';
    return;
  }
  container.style.display = 'flex';
  let html = '';
  if (resultPdfShown && room.pdf_url) {
    html += `<div class="result-pdf-panel" id="res-pdf-wrap">
      <div class="result-pdf-panel-label">📄 Question Paper</div>
      <iframe src="${escHtml(room.pdf_url)}" title="Question Paper" allow="fullscreen"></iframe>
    </div>`;
  }
  if (resultSolShown && room.solution_pdf_url) {
    html += `<div class="result-pdf-panel" id="res-sol-wrap">
      <div class="result-pdf-panel-label">📖 Solution</div>
      <iframe src="${escHtml(room.solution_pdf_url)}" title="Solution" allow="fullscreen"></iframe>
    </div>`;
  }
  container.innerHTML = html;
}

function toggleResultPdf() {
  resultPdfShown = !resultPdfShown;
  const btn = document.getElementById('res-pdf-btn');
  if (btn) btn.classList.toggle('active', resultPdfShown);
  rebuildResultPdfContainer();
}

function toggleResultSol() {
  resultSolShown = !resultSolShown;
  const btn = document.getElementById('res-sol-btn');
  if (btn) btn.classList.toggle('sol-active', resultSolShown);
  rebuildResultPdfContainer();
}

// ══════════════════════════════════════════════════════════════
//  SIDEBAR TOGGLE
// ══════════════════════════════════════════════════════════════
// ══════════════════════════════════════════════════════════════
//  MOBILE EXAM TABS
// ══════════════════════════════════════════════════════════════
let _mobileTab = 'options';
function switchExamTab(tab) {
  _mobileTab = tab;
  const scr = document.getElementById('scr-exam');
  if (scr) scr.dataset.tab = tab;
  document.querySelectorAll('.exam-tab-btn').forEach(b => {
    b.classList.toggle('active', b.dataset.tab === tab);
  });
  // When switching to navigator, ensure sidebar is not hidden
  if (tab === 'navigator') {
    const sb = document.getElementById('sidebar');
    if (sb) sb.classList.remove('hidden');
  }
  // Load PDF lazily on first visit to PDF tab
  if (tab === 'pdf') {
    if (!pdfLoaded && state.room && state.room.pdf_url) {
      pdfLoaded = true;
      renderPdfToContainer(state.room.pdf_url, 'pdf-pages-wrap');
      scrollPdfToQuestion(state.currentQ);
    }
    if (!solLoaded && state.room && state.room.solution_pdf_url) {
      solLoaded = true;
      renderPdfToContainer(state.room.solution_pdf_url, 'sol-pages-wrap');
    }
  }
}

function initExamTabs(room) {
  // Set default tab
  const scrExam = document.getElementById('scr-exam');
  if (scrExam) scrExam.dataset.tab = 'options';
  _mobileTab = 'options';
  document.querySelectorAll('.exam-tab-btn').forEach(b => {
    b.classList.toggle('active', b.dataset.tab === 'options');
  });
  // Show PDF tab button only if room has a PDF
  const pdfTabBtn = document.getElementById('exam-tab-pdf-btn');
  if (pdfTabBtn && (room.pdf_url || room.solution_pdf_url)) {
    pdfTabBtn.style.display = '';
  }
}

// ══════════════════════════════════════════════════════════════
//  FIREBASE AUTH STATE (room.view)
// ══════════════════════════════════════════════════════════════
document.addEventListener('omr:authChanged', function(e) {
  const user = e.detail;
  // Show user avatar in topbar
  const av = document.getElementById('tb-user-avatar');
  if (av && user && user.photoURL) {
    av.src = user.photoURL;
    av.style.display = '';
  } else if (av) {
    av.style.display = 'none';
  }
});

// Try to apply Google name to this player's slot on first join
function applyGoogleNameToRoom() {
  const user = omrGetStoredUser();
  if (!user || !user.displayName) return;
  if (state.myIdx < 0 || !state.room) return;
  const myP = state.room.players[state.myIdx];
  if (!myP) return;
  // Auto-set player name to Google display name if it's still the default "Player N"
  const defaultPat = /^Player \d+$/;
  if (defaultPat.test(myP.name || '')) {
    api({ action: 'rename_player', room_id: ROOM_ID, player_id: MY_CODE, name: user.displayName })
      .catch(() => {});
  }
}

function toggleSidebar() {
  const sb = document.getElementById('sidebar');
  const ov = document.getElementById('sidebar-overlay');
  if (window.innerWidth >= 768) {
    sb.classList.toggle('hidden');
  } else {
    state.sidebarOpen = !state.sidebarOpen;
    sb.classList.toggle('open', state.sidebarOpen);
    ov.classList.toggle('show', state.sidebarOpen);
  }
}
function closeSidebar() {
  document.getElementById('sidebar').classList.remove('open');
  document.getElementById('sidebar-overlay').classList.remove('show');
  state.sidebarOpen = false;
}
window.addEventListener('resize', () => {
  if (window.innerWidth >= 768) {
    document.getElementById('sidebar-overlay').classList.remove('show');
    document.getElementById('sidebar').classList.remove('open');
  }
});

// ══════════════════════════════════════════════════════════════
//  PDF PANEL & SPLITTER
// ══════════════════════════════════════════════════════════════
let pdfPanelOpen = false;
let pdfLoaded = false;

function setupPdfPanel() {
  const room = state.room;
  const btn = document.getElementById('tb-pdf-btn');
  if (room && room.pdf_url) {
    btn.classList.add('has-pdf');
    document.getElementById('pdf-panel-title').textContent = room.test_name + ' — Question Paper';
    // Auto-open on desktop
    if (window.innerWidth >= 768) {
      togglePdfPanel();
    }
  } else {
    btn.classList.remove('has-pdf');
  }
}

// ── Native browser PDF viewer helpers ────────────────────────
// Map logical container IDs → iframe element IDs
const PDF_IFRAME_MAP = {
  'pdf-pages-wrap': 'pdf-iframe-main',
  'sol-pages-wrap': 'pdf-iframe-sol',
  // result page iframes are created dynamically in rebuildResultPdfContainer
};

function renderPdfToContainer(url, containerId) {
  // Resolve to an iframe either via the map or by looking for a child iframe
  let iframeId = PDF_IFRAME_MAP[containerId];
  let iframe = iframeId ? document.getElementById(iframeId) : null;
  if (!iframe) {
    // Result-page panels: look for iframe inside the container div
    const wrap = document.getElementById(containerId);
    if (wrap) iframe = wrap.querySelector('iframe');
  }
  if (!iframe || !url) return;
  iframe._pdfBaseUrl = url;
  iframe.src = url;
}

// Jump a PDF iframe to a specific page via #page=N.
// Chrome's built-in PDF viewer ignores fragment changes on an already-loaded
// document — it needs a true reload. We blank the iframe first, wait one tick,
// then set the target URL. CSS opacity transition hides the flash.
function jumpPdfIframeToPage(iframe, url, page) {
  if (!iframe || !url || !page) return;
  if (iframe._lastPage === page && iframe._pdfBaseUrl === url) return;
  iframe._lastPage   = page;
  iframe._pdfBaseUrl = url;
  iframe.style.opacity = '0.08';
  iframe.src = 'about:blank';
  const target = url + '#page=' + page;
  setTimeout(() => {
    iframe.src = target;
    const onLoad = () => {
      iframe.style.opacity = '';
      iframe.removeEventListener('load', onLoad);
    };
    iframe.addEventListener('load', onLoad);
    setTimeout(() => { iframe.style.opacity = ''; }, 2500); // fallback
  }, 35);
}

function togglePdfPanel() {
  const panel = document.getElementById('pdf-panel');
  const splitter = document.getElementById('pdf-splitter');
  const btn = document.getElementById('tb-pdf-btn');
  pdfPanelOpen = !pdfPanelOpen;

  panel.classList.toggle('open', pdfPanelOpen);
  splitter.classList.toggle('open', pdfPanelOpen && window.innerWidth >= 768);
  btn.classList.toggle('active', pdfPanelOpen);

  if (pdfPanelOpen && !pdfLoaded && state.room && state.room.pdf_url) {
    pdfLoaded = true;
    renderPdfToContainer(state.room.pdf_url, 'pdf-pages-wrap');
  }
}

// Solution PDF panel
let solPanelOpen = false;
let solLoaded = false;

function setupSolPanel() {
  const room = state.room;
  const btn = document.getElementById('tb-sol-btn');
  if (room && room.solution_pdf_url) {
    btn.classList.add('has-sol');
    document.getElementById('sol-panel-title').textContent = room.test_name + ' — Solution';
  } else {
    btn.classList.remove('has-sol');
  }
}

function toggleSolPanel() {
  const panel = document.getElementById('sol-panel');
  const btn = document.getElementById('tb-sol-btn');
  solPanelOpen = !solPanelOpen;

  panel.classList.toggle('open', solPanelOpen);
  btn.classList.toggle('active', solPanelOpen);

  if (solPanelOpen && !solLoaded && state.room && state.room.solution_pdf_url) {
    solLoaded = true;
    renderPdfToContainer(state.room.solution_pdf_url, 'sol-pages-wrap');
  }
}

// Auto-jump PDFs to the page containing the current question
function scrollPdfToQuestion(qid) {
  const room = state.room;
  if (!room || !room.page_map) return;

  const qNum = parseInt(String(qid).replace(/\D/g, ''));
  if (isNaN(qNum)) return;

  let targetPage = null, maxStartQ = -1;
  for (const [pageStr, startQ] of Object.entries(room.page_map)) {
    const pNum = parseInt(pageStr);
    const sq   = parseInt(startQ);
    if (!isNaN(pNum) && !isNaN(sq) && qNum >= sq && sq > maxStartQ) {
      maxStartQ = sq;
      targetPage = pNum;
    }
  }
  if (!targetPage) return;

  if (pdfPanelOpen && room.pdf_url)
    jumpPdfIframeToPage(document.getElementById('pdf-iframe-main'), room.pdf_url, targetPage);

  if (solPanelOpen && room.solution_pdf_url)
    jumpPdfIframeToPage(document.getElementById('pdf-iframe-sol'), room.solution_pdf_url, targetPage);
}

// Draggable splitter — with drag overlay to prevent iframe stealing events
(function initSplitter() {
  const splitter = document.getElementById('pdf-splitter');
  const examBody = document.querySelector('.exam-body');
  const mainContent = document.getElementById('main-content');
  const pdfPanel = document.getElementById('pdf-panel');
  let isDragging = false;

  // Create an invisible overlay to cover iframes during drag
  const dragOverlay = document.createElement('div');
  dragOverlay.style.cssText = 'position:fixed;inset:0;z-index:9999;cursor:col-resize;display:none;';
  document.body.appendChild(dragOverlay);

  function startDrag(e) {
    if (!pdfPanelOpen) return;
    isDragging = true;
    splitter.classList.add('dragging');
    document.body.style.cursor = 'col-resize';
    document.body.style.userSelect = 'none';
    // Show overlay to intercept all mouse events (prevents iframe interference)
    dragOverlay.style.display = 'block';
    e.preventDefault();
  }

  function doDrag(e) {
    if (!isDragging) return;
    const clientX = e.touches ? e.touches[0].clientX : e.clientX;
    const rect = examBody.getBoundingClientRect();
    // Account for the sidebar width
    const sidebar = document.getElementById('sidebar');
    const sidebarWidth = (sidebar && !sidebar.classList.contains('hidden') && window.innerWidth >= 768)
      ? sidebar.offsetWidth : 0;
    const splitterW = 6;
    const availableWidth = rect.width - sidebarWidth - splitterW;
    const offsetX = clientX - rect.left - sidebarWidth;

    // Clamp: each panel gets at least 20% of available space
    const minPx = availableWidth * 0.2;
    const leftPx = Math.max(minPx, Math.min(availableWidth - minPx, offsetX));
    const rightPx = availableWidth - leftPx;

    // Use pixel widths instead of percentages to avoid sidebar interference
    mainContent.style.flex = '0 0 ' + leftPx + 'px';
    pdfPanel.style.flex = '0 0 ' + rightPx + 'px';
  }

  function endDrag() {
    if (!isDragging) return;
    isDragging = false;
    splitter.classList.remove('dragging');
    document.body.style.cursor = '';
    document.body.style.userSelect = '';
    // Hide drag overlay
    dragOverlay.style.display = 'none';
  }

  splitter.addEventListener('mousedown', startDrag);
  splitter.addEventListener('touchstart', startDrag, {passive: false});
  document.addEventListener('mousemove', doDrag);
  document.addEventListener('touchmove', doDrag, {passive: false});
  document.addEventListener('mouseup', endDrag);
  document.addEventListener('touchend', endDrag);

  // Also listen on the overlay for safety
  dragOverlay.addEventListener('mousemove', doDrag);
  dragOverlay.addEventListener('mouseup', endDrag);
})();

// ══════════════════════════════════════════════════════════════
//  FULLSCREEN & REFRESH
// ══════════════════════════════════════════════════════════════
function requestFullscreen() {
  try {
    const el = document.documentElement;
    const rfs = el.requestFullscreen || el.webkitRequestFullscreen || el.mozRequestFullScreen || el.msRequestFullscreen;
    if (rfs) rfs.call(el).catch(() => {});
  } catch(e) { /* Fullscreen not supported or user gesture required */ }
}

function toggleFullscreen() {
  const btn = document.getElementById('btn-fullscreen');
  if (document.fullscreenElement || document.webkitFullscreenElement) {
    (document.exitFullscreen || document.webkitExitFullscreen || document.mozCancelFullScreen || document.msExitFullscreen).call(document);
    btn.textContent = '⛶';
  } else {
    requestFullscreen();
    btn.textContent = '⊡';
  }
}

// Listen for fullscreen change to update button icon
document.addEventListener('fullscreenchange', () => {
  const btn = document.getElementById('btn-fullscreen');
  if (btn) btn.textContent = document.fullscreenElement ? '⊡' : '⛶';
});
document.addEventListener('webkitfullscreenchange', () => {
  const btn = document.getElementById('btn-fullscreen');
  if (btn) btn.textContent = document.webkitFullscreenElement ? '⊡' : '⛶';
});

async function refreshPage() {
  const btn = document.getElementById('btn-refresh');
  btn.classList.add('spinning');
  try {
    await syncRoom();
    const room = state.room;
    if (room) {
      buildSidebar();
      renderQuestion();
      updateScoreChips();
      updateOnlineDots();
      renderChat();
    }
    showToast('✓ Refreshed', 'ok');
  } catch(e) {
    showToast('Refresh failed', 'error');
  }
  setTimeout(() => btn.classList.remove('spinning'), 600);
}

// ══════════════════════════════════════════════════════════════
//  HELPERS
// ══════════════════════════════════════════════════════════════
function showScreen(id) {
  document.querySelectorAll('.screen').forEach(s => s.classList.remove('active'));
  document.getElementById(id).classList.add('active');
  if (id !== 'scr-exam') {
    const ro = document.getElementById('reading-overlay');
    if (ro) { ro.classList.add('hidden'); ro.style.display = 'none'; }
  }
}

function closeAllModals() {
  document.querySelectorAll('.modal-overlay.open').forEach(m => m.classList.remove('open'));
}

let toastTimeout = null;
let countdownToastInterval = null;
function showToast(msg, type='info') {
  const t = document.getElementById('toast');
  clearInterval(countdownToastInterval);
  clearTimeout(toastTimeout);
  t.textContent = msg;
  t.className = 'toast show ' + type;
  toastTimeout = setTimeout(() => t.classList.remove('show'), 3000);
}

function showCountdownToast(templateMsg, seconds, onComplete) {
  const t = document.getElementById('toast');
  clearInterval(countdownToastInterval);
  clearTimeout(toastTimeout);
  
  let rem = seconds;
  t.textContent = templateMsg.replace('{s}', rem);
  t.className = 'toast show info';
  
  countdownToastInterval = setInterval(() => {
    rem--;
    if (rem <= 0) {
      clearInterval(countdownToastInterval);
      t.classList.remove('show');
      if (onComplete) onComplete();
    } else {
      t.textContent = templateMsg.replace('{s}', rem);
    }
  }, 1000);
}

// ── EXAM MILESTONE ALERT (large modal-style notification) ──
let _examAlertTimer = null;
function showExamAlert(msg, type) {
  const el = document.getElementById('exam-alert');
  if (!el) return;
  clearTimeout(_examAlertTimer);
  el.textContent = msg;
  el.className = 'show type-' + (type || 'warn');
  _examAlertTimer = setTimeout(() => { el.className = ''; }, 6000);
}

function escHtml(s) {
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// api() function is now provided by db-api.js (Firestore-backed)

function launchConfetti() {
  const colors = ['#5b7fff','#ff5f7e','#ffe156','#4fffb0','#ff8c42'];
  for (let i = 0; i < 60; i++) {
    setTimeout(() => {
      const el = document.createElement('div');
      el.className = 'confetti-piece';
      el.style.cssText = `left:${Math.random()*100}vw;background:${colors[Math.floor(Math.random()*colors.length)]};width:${4+Math.random()*8}px;height:${4+Math.random()*8}px;animation-duration:${2+Math.random()*3}s;border-radius:${Math.random()>.5?'50%':'2px'};`;
      document.body.appendChild(el);
      setTimeout(() => el.remove(), 5000);
    }, i * 50);
  }
}

// ESC to close modals/chat/overlays
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') {
    closeAllModals();
    if (state.chatOpen) toggleChat();
    const so = document.getElementById('shortcuts-overlay');
    if (so && so.classList.contains('open')) so.classList.remove('open');
  }
});

// ══════════════════════════════════════════════════════════════
//  SOUND EFFECTS (Web Audio API — no external files)
// ══════════════════════════════════════════════════════════════
let audioCtx = null;
let sfxMuted = localStorage.getItem('omr_muted') === '1';

function getAudioCtx() {
  if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
  return audioCtx;
}

function sfxClick() {
  if (sfxMuted) return;
  try {
    const ctx = getAudioCtx();
    const osc = ctx.createOscillator();
    const gain = ctx.createGain();
    osc.type = 'sine';
    osc.frequency.setValueAtTime(880, ctx.currentTime);
    osc.frequency.exponentialRampToValueAtTime(1200, ctx.currentTime + 0.04);
    gain.gain.setValueAtTime(0.08, ctx.currentTime);
    gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.06);
    osc.connect(gain).connect(ctx.destination);
    osc.start(); osc.stop(ctx.currentTime + 0.06);
  } catch(e) {}
}

function sfxNav() {
  if (sfxMuted) return;
  try {
    const ctx = getAudioCtx();
    const osc = ctx.createOscillator();
    const gain = ctx.createGain();
    osc.type = 'sine';
    osc.frequency.setValueAtTime(400, ctx.currentTime);
    osc.frequency.exponentialRampToValueAtTime(700, ctx.currentTime + 0.07);
    gain.gain.setValueAtTime(0.06, ctx.currentTime);
    gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.08);
    osc.connect(gain).connect(ctx.destination);
    osc.start(); osc.stop(ctx.currentTime + 0.08);
  } catch(e) {}
}

function sfxFlag() {
  if (sfxMuted) return;
  try {
    const ctx = getAudioCtx();
    // Two-tone chirp
    [600, 900].forEach((freq, i) => {
      const osc = ctx.createOscillator();
      const gain = ctx.createGain();
      osc.type = 'triangle';
      osc.frequency.value = freq;
      gain.gain.setValueAtTime(0.07, ctx.currentTime + i * 0.06);
      gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + i * 0.06 + 0.06);
      osc.connect(gain).connect(ctx.destination);
      osc.start(ctx.currentTime + i * 0.06);
      osc.stop(ctx.currentTime + i * 0.06 + 0.07);
    });
  } catch(e) {}
}

function sfxWarning() {
  if (sfxMuted) return;
  try {
    const ctx = getAudioCtx();
    const osc = ctx.createOscillator();
    const gain = ctx.createGain();
    osc.type = 'square';
    osc.frequency.value = 440;
    gain.gain.setValueAtTime(0.05, ctx.currentTime);
    gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.15);
    osc.connect(gain).connect(ctx.destination);
    osc.start(); osc.stop(ctx.currentTime + 0.15);
  } catch(e) {}
}

function sfxComplete() {
  if (sfxMuted) return;
  try {
    const ctx = getAudioCtx();
    // Rising arpeggio: C5, E5, G5, C6
    [523, 659, 784, 1047].forEach((freq, i) => {
      const osc = ctx.createOscillator();
      const gain = ctx.createGain();
      osc.type = 'sine';
      osc.frequency.value = freq;
      gain.gain.setValueAtTime(0.08, ctx.currentTime + i * 0.1);
      gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + i * 0.1 + 0.25);
      osc.connect(gain).connect(ctx.destination);
      osc.start(ctx.currentTime + i * 0.1);
      osc.stop(ctx.currentTime + i * 0.1 + 0.3);
    });
  } catch(e) {}
}

function sfxRingingAlert() {
  if (sfxMuted) return;
  try {
    const ctx = getAudioCtx();
    const t = ctx.currentTime;
    
    // Telephone-like electronic ring (two closely spaced frequencies beating)
    const osc1 = ctx.createOscillator();
    const osc2 = ctx.createOscillator();
    const gain = ctx.createGain();

    osc1.type = 'sine';
    osc1.frequency.setValueAtTime(440, t); // A4
    osc2.type = 'sine';
    osc2.frequency.setValueAtTime(480, t); // slightly detuned for the "brrrring" effect

    // Sharp attack, sustained ring, quick decay
    gain.gain.setValueAtTime(0, t);
    gain.gain.linearRampToValueAtTime(0.15, t + 0.05);
    gain.gain.setValueAtTime(0.15, t + 1.2);
    gain.gain.exponentialRampToValueAtTime(0.001, t + 1.3);

    osc1.connect(gain);
    osc2.connect(gain);
    gain.connect(ctx.destination);

    osc1.start(t);
    osc2.start(t);
    osc1.stop(t + 1.4);
    osc2.stop(t + 1.4);
  } catch(e) {}
}

function toggleMute() {
  sfxMuted = !sfxMuted;
  localStorage.setItem('omr_muted', sfxMuted ? '1' : '0');
  const btn = document.getElementById('btn-mute');
  if (btn) {
    btn.innerHTML = sfxMuted
      ? '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><line x1="23" y1="9" x2="17" y2="15"/><line x1="17" y1="9" x2="23" y2="15"/></svg>'
      : '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"/></svg>';
    btn.title = sfxMuted ? 'Unmute' : 'Mute';
  }
  showToast(sfxMuted ? 'Sound muted' : 'Sound enabled', 'info');
}

// Hook sound into selectAnswer
const _origSelectAnswer = selectAnswer;
selectAnswer = function(letter) {
  _origSelectAnswer(letter);
  sfxClick();
};

// Timer warning sound (every 10s when < 60s left)
let lastWarnSec = -1;
const _origTickTimer = tickTimer;
tickTimer = function() {
  _origTickTimer();
  const room = state.room;
  if (!room || room.status !== 'active') return;
  if (room.timer_mode === 'countdown') {
    const now = Math.floor(Date.now() / 1000);
    const totalPaused = room.total_paused_sec || 0;
    const elapsed = Math.floor(now - room.started_at) - totalPaused;
    const rem = Math.max(0, (room.duration_sec || 0) - elapsed);
    if (rem <= 60 && rem > 0 && rem % 10 === 0 && rem !== lastWarnSec) {
      lastWarnSec = rem;
      sfxWarning();
    }
  }
};

// ══════════════════════════════════════════════════════════════
//  KEYBOARD SHORTCUTS OVERLAY
// ══════════════════════════════════════════════════════════════
function toggleShortcutsOverlay() {
  let el = document.getElementById('shortcuts-overlay');
  if (!el) {
    el = document.createElement('div');
    el.id = 'shortcuts-overlay';
    el.className = 'shortcuts-overlay';
    el.innerHTML = `
      <div class="shortcuts-card">
        <div class="shortcuts-title"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M6 8h.01M10 8h.01M14 8h.01M18 8h.01M8 12h.01M12 12h.01M16 12h.01M6 16h8"/></svg> Keyboard Shortcuts</div>
        <div class="shortcuts-grid">
          <kbd>A</kbd><span>Select Option A</span>
          <kbd>B</kbd><span>Select Option B</span>
          <kbd>C</kbd><span>Select Option C</span>
          <kbd>D</kbd><span>Select Option D</span>
          <kbd>←</kbd><span>Previous Question</span>
          <kbd>→</kbd><span>Next Question</span>
          <kbd>F</kbd> / <kbd>R</kbd><span>Mark / Unmark for Review</span>
          <kbd>S</kbd><span>Skip Question</span>
          <kbd>X</kbd><span>Clear Answer</span>
          <kbd>?</kbd><span>Toggle This Help</span>
          <kbd>Esc</kbd><span>Close Panels</span>
        </div>
        <div class="shortcuts-close" onclick="toggleShortcutsOverlay()">Press <kbd>?</kbd> or <kbd>Esc</kbd> to close</div>
      </div>`;
    document.body.appendChild(el);
    requestAnimationFrame(() => el.classList.add('open'));
  } else {
    el.classList.toggle('open');
  }
}

// ══════════════════════════════════════════════════════════════
//  RECONNECTION HANDLING (localStorage + Firestore backup)
// ══════════════════════════════════════════════════════════════
function saveSession() {
  if (!ROOM_ID || !MY_CODE) return;
  localStorage.setItem('omr_session', JSON.stringify({
    room_id: ROOM_ID,
    player_id: MY_CODE,
    timestamp: Date.now(),
    currentQIdx: state.currentQIdx,
    localAnswers: state.localAnswers,
    localMarked: state.localMarked,
    localSkipped: state.localSkipped,
    viewedQ: Array.from(state.viewedQ),
  }));
  // Also mirror to Firestore (non-blocking)
  saveStateToFirestore();
}

// Write current exam state to Firestore (cross-device persistence)
function saveStateToFirestore() {
  if (!ROOM_ID || !MY_CODE || typeof db === 'undefined') return;
  const user = (typeof omrGetStoredUser === 'function') ? omrGetStoredUser() : null;
  if (!user) return;
  db.collection('live_sessions')
    .doc(ROOM_ID + '_' + MY_CODE)
    .set({
      room_id:      ROOM_ID,
      player_id:    MY_CODE,
      uid:          user.uid,
      current_q:    state.currentQ    || null,
      currentQIdx:  state.currentQIdx || 0,
      localAnswers: state.localAnswers || {},
      localMarked:  state.localMarked  || {},
      localSkipped: state.localSkipped || {},
      updated_at:   firebase.firestore.FieldValue.serverTimestamp(),
    }, { merge: true })
    .catch(() => {}); // silent — localStorage is primary
}

// Read back exam state from Firestore (used when localStorage is gone)
async function loadStateFromFirestore() {
  if (!ROOM_ID || !MY_CODE || typeof db === 'undefined') return null;
  const user = (typeof omrGetStoredUser === 'function') ? omrGetStoredUser() : null;
  if (!user) return null;
  try {
    const doc = await db.collection('live_sessions').doc(ROOM_ID + '_' + MY_CODE).get();
    if (doc.exists) return doc.data();
  } catch(e) {}
  return null;
}

function clearSession() {
  localStorage.removeItem('omr_session');
}

function getSavedSession() {
  try {
    const raw = localStorage.getItem('omr_session');
    if (!raw) return null;
    const sess = JSON.parse(raw);
    // Expire after 6 hours
    if (Date.now() - sess.timestamp > 6 * 3600 * 1000) {
      clearSession();
      return null;
    }
    return sess;
  } catch(e) { return null; }
}

// Hook into pushAnswerUpdate to save session on every answer change
const _origPushAnswerUpdate = pushAnswerUpdate;
pushAnswerUpdate = function(qid, answer, marked, skipped) {
  _origPushAnswerUpdate(qid, answer, marked, skipped);
  saveSession(); // writes to both localStorage + Firestore
};

// Hook into enterExam to save session when entering exam
const _origEnterExam = enterExam;
enterExam = function() {
  _origEnterExam();
  saveSession();
};

// Hook into showResultScreen to play completion sound and clear session
const _origShowResult = showResultScreen;
showResultScreen = function() {
  _origShowResult();
  sfxComplete();
  clearSession();
};

// ══════════════════════════════════════════════════════════════
//  EXPORT RESULTS AS PDF (Browser Print)
// ══════════════════════════════════════════════════════════════
function exportResultPdf() {
  const room = state.room;
  if (!room) return;
  const ansKey = room.answer_key || {};
  const questions = room.questions || [];

  // Compute scores
  const scores = room.players.map((p, pi) => {
    let correct = 0, wrong = 0, skip = 0;
    questions.forEach(qid => {
      const ans = (pi === state.myIdx) ? (state.localAnswers[qid] || null) : (p.answers?.[qid] || null);
      const key = ansKey[qid] || null;
      if (!ans) skip++;
      else if (ans === key) correct++;
      else wrong++;
    });
    return { name: p.name, correct, wrong, skip };
  });

  const maxScore = Math.max(...scores.map(s => s.correct));
  const winnerIdx = scores.findIndex(s => s.correct === maxScore);
  const isDraw = scores.filter(s => s.correct === maxScore).length > 1;

  // Build review rows
  let reviewRows = questions.map((qid, idx) => {
    const key = ansKey[qid] || '?';
    const keyLabel = key !== '?' ? (LETTER_LABELS[LETTERS.indexOf(key)] || key) : '?';
    const cells = room.players.map((p, pi) => {
      const ans = (pi === state.myIdx) ? (state.localAnswers[qid] || null) : (p.answers?.[qid] || null);
      if (!ans) return '<td style="color:#999;font-style:italic">—</td>';
      const isOk = ans === key;
      return `<td style="color:${isOk ? '#22c55e' : '#ef4444'};font-weight:${isOk ? '700' : '400'}">${LETTER_LABELS[LETTERS.indexOf(ans)] || ans} ${isOk ? '✓' : '✗'}</td>`;
    }).join('');
    return `<tr><td style="color:#888">${qid.replace(/[^0-9]/g,'')}</td><td style="color:#22c55e;font-weight:700">${keyLabel}</td>${cells}</tr>`;
  }).join('');

  const playerHeaders = room.players.map(p => `<th>${escHtml(p.name)}</th>`).join('');
  const now = new Date();
  const dateStr = now.toLocaleDateString('en-IN', {day:'numeric', month:'short', year:'numeric'});
  const timeStr = now.toLocaleTimeString('en-IN', {hour:'2-digit', minute:'2-digit'});

  // Score cards
  const scoreCards = scores.map((s, i) => {
    const isWin = !isDraw && i === winnerIdx;
    return `<div style="flex:1;min-width:140px;background:${isWin ? '#f0fdf4' : '#f8f9fa'};border:2px solid ${isWin ? '#22c55e' : '#e5e7eb'};border-radius:12px;padding:1.25rem;text-align:center;">
      ${isWin ? '<div style="font-size:1.5rem;margin-bottom:.25rem">🏆</div>' : ''}
      <div style="font-weight:700;font-size:1rem;margin-bottom:.25rem">${escHtml(s.name)}</div>
      <div style="font-size:2rem;font-weight:800;color:${isWin ? '#22c55e' : '#374151'}">${s.correct}</div>
      <div style="font-size:.75rem;color:#888;margin-top:.25rem">✓ ${s.correct} &nbsp; ✗ ${s.wrong} &nbsp; — ${s.skip}</div>
    </div>`;
  }).join('');

  const html = `<!DOCTYPE html><html><head><meta charset="UTF-8">
    <title>OMR Results — ${escHtml(room.test_name)}</title>
    <style>
      *{box-sizing:border-box;margin:0;padding:0;}
      body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;font-size:13px;color:#1a1a1a;padding:2rem;max-width:900px;margin:0 auto;}
      .header{text-align:center;margin-bottom:2rem;padding-bottom:1.5rem;border-bottom:2px solid #e5e7eb;}
      .header h1{font-size:1.6rem;font-weight:800;margin-bottom:.25rem;}
      .header .sub{font-size:.85rem;color:#888;}
      .scores{display:flex;gap:1rem;margin-bottom:2rem;flex-wrap:wrap;}
      table{width:100%;border-collapse:collapse;font-size:.82rem;}
      th,td{padding:.5rem .75rem;text-align:left;border-bottom:1px solid #e5e7eb;}
      th{background:#f8f9fa;font-size:.7rem;text-transform:uppercase;letter-spacing:1px;color:#888;font-weight:600;}
      .footer{margin-top:2rem;text-align:center;font-size:.75rem;color:#aaa;padding-top:1rem;border-top:1px solid #e5e7eb;}
      @media print{body{padding:.5rem;}}
    </style>
  </head><body>
    <div class="header">
      <h1>OMR Battle — ${escHtml(room.test_name)}</h1>
      <div class="sub">${dateStr} at ${timeStr} · Room ${ROOM_ID} · ${questions.length} Questions</div>
    </div>
    <div class="scores">${scoreCards}</div>
    <table>
      <thead><tr><th>#</th><th>Answer</th>${playerHeaders}</tr></thead>
      <tbody>${reviewRows}</tbody>
    </table>
    <div class="footer">Generated by MiniShiksha OMR Battle System v2.0</div>
    <script>window.onload=function(){window.print();}<\/script>
  </body></html>`;

  const w = window.open('', '_blank');
  if (w) {
    w.document.write(html);
    w.document.close();
  } else {
    showToast('Please allow popups to export PDF', 'error');
  }
}

// ══════════════════════════════════════════════════════════════
//  START (with reconnection check)
// ══════════════════════════════════════════════════════════════
async function init() {
  // Case 1: Direct URL with player_id + room_id
  if (MY_CODE && ROOM_ID) {
    // Restore session data (answers, viewed questions) from localStorage if available
    const saved1 = getSavedSession();
    if (saved1 && saved1.room_id === ROOM_ID && saved1.player_id === MY_CODE) {
      if (saved1.localAnswers) state.localAnswers = saved1.localAnswers;
      if (saved1.localMarked) state.localMarked = saved1.localMarked;
      if (saved1.localSkipped) state.localSkipped = saved1.localSkipped;
      if (Array.isArray(saved1.viewedQ)) state.viewedQ = new Set(saved1.viewedQ);
      if (typeof saved1.currentQIdx === 'number') state.currentQIdx = saved1.currentQIdx;
    }
    await joinRoom();
    return;
  }
  // Case 2: Only room_id (host visiting their own room link)
  if (ROOM_ID && !MY_CODE) {
    showScreen('scr-lobby');
    document.getElementById('lobby-msg').textContent = 'Enter your player code to join room ' + ROOM_ID;
    document.getElementById('lobby-msg').className = 'lobby-msg ok';
    return;
  }
  // Case 3: Check for saved session — localStorage first, Firestore fallback
  let saved = getSavedSession();
  if (!saved) {
    // No localStorage — try Firestore if URL has both IDs
    // (MY_CODE/ROOM_ID are empty here; skip Firestore for anonymous case)
  }
  if (saved && saved.room_id && saved.player_id) {
    MY_CODE = saved.player_id;
    ROOM_ID = saved.room_id;
    if (saved.localAnswers) state.localAnswers = saved.localAnswers;
    if (saved.localMarked) state.localMarked = saved.localMarked;
    if (saved.localSkipped) state.localSkipped = saved.localSkipped;
    if (Array.isArray(saved.viewedQ)) state.viewedQ = new Set(saved.viewedQ);
    if (typeof saved.currentQIdx === 'number') state.currentQIdx = saved.currentQIdx;
    history.replaceState({}, '', 'room.php?player_id=' + MY_CODE + '&room_id=' + ROOM_ID);
    try {
      await joinRoom();
      showToast('✓ Reconnected to your session!', 'ok');
    } catch(e) {
      clearSession();
      showScreen('scr-lobby');
    }
    return;
  }
  // Case 4: Just the page — show code entry
  showScreen('scr-lobby');
}

// ══════════════════════════════════════════════════════════════
//  UPSC MARKS CALCULATION
// ══════════════════════════════════════════════════════════════
const UPSC_PATTERNS = {
  gs:   { label: 'GS (Paper 1)',  perQ: 2,   neg: 2/3  },
  csat: { label: 'CSAT (Paper 2)', perQ: 2.5, neg: 2.5/3 },
  gs2:  { label: 'GS (2-mark)',   perQ: 2,   neg: 2/3  },
  custom:{ label: 'Custom',       perQ: 1,   neg: 1/3  },
};

function detectUpscPattern(room) {
  const tag = (room.tag || room.subject || '').toLowerCase();
  if (/csat|paper.?2|csat/.test(tag)) return 'csat';
  if (/gs|general.?stud|prelim|paper.?1/.test(tag)) return 'gs';
  return null; // not UPSC
}

function calcUpscMarks(room, myAnswers, patternKey) {
  const pat    = UPSC_PATTERNS[patternKey] || UPSC_PATTERNS.gs;
  const ansKey = room.answer_key || {};
  let correct = 0, wrong = 0, skip = 0;
  (room.questions || []).forEach(qid => {
    const given = myAnswers[qid] || null;
    const key   = ansKey[qid]   || null;
    if (!given) skip++;
    else if (given === key) correct++;
    else wrong++;
  });
  const gross  = correct * pat.perQ;
  const deduct = wrong   * pat.neg;
  const net    = gross   - deduct;
  return { correct, wrong, skip, gross, deduct, net, pat };
}

function renderUpscPanel(patternKey) {
  const room = state.room;
  if (!room) return;
  const myAnswers = state.localAnswers || {};
  const { correct, wrong, skip, gross, deduct, net, pat } = calcUpscMarks(room, myAnswers, patternKey);

  const col = net >= 0 ? 'var(--ok)' : 'var(--danger)';
  document.getElementById('upsc-panel').innerHTML = `
    <div class="upsc-panel-inner">
      <div class="upsc-panel-title">
        🇮🇳 UPSC CSE Marks — ${escHtml(pat.label)}
        <span style="margin-left:auto;font-weight:400;">
          <span class="upsc-pattern-select">
            Pattern:
            <select onchange="renderUpscPanel(this.value)">
              ${Object.entries(UPSC_PATTERNS).map(([k,p]) =>
                `<option value="${k}" ${k===patternKey?'selected':''}>${escHtml(p.label)}</option>`
              ).join('')}
            </select>
          </span>
        </span>
      </div>
      <div class="upsc-rows">
        <div class="upsc-row">
          <span class="upsc-row-label">✅ Correct × ${pat.perQ}</span>
          <span class="upsc-row-val" style="color:var(--ok)">+${gross.toFixed(2)}</span>
        </div>
        <div class="upsc-row">
          <span class="upsc-row-label">❌ Wrong × ${pat.neg.toFixed(3)}</span>
          <span class="upsc-row-val" style="color:var(--danger)">−${deduct.toFixed(2)}</span>
        </div>
        <div class="upsc-row">
          <span class="upsc-row-label">⏭ Not Attempted</span>
          <span class="upsc-row-val" style="color:var(--muted)">${skip}</span>
        </div>
      </div>
      <div class="upsc-net">
        <span class="upsc-net-label">Net Score</span>
        <span class="upsc-net-val" style="color:${col}">${net.toFixed(2)} / ${((room.questions||[]).length*pat.perQ).toFixed(0)}</span>
      </div>
    </div>`;
  document.getElementById('upsc-panel').style.display = '';
}

// ══════════════════════════════════════════════════════════════
//  ANALYSIS MODE
// ══════════════════════════════════════════════════════════════
let _analysisIdx  = 0;
let _analysisPat  = 'gs';
let _analysisShowSol = false;

function openAnalysisMode() {
  if (!state.room) return;
  _analysisIdx = 0;
  _analysisPat = detectUpscPattern(state.room) || 'gs';

  const room = state.room;
  const ansKey = room.answer_key || {};
  const qs = room.questions || [];
  let correct = 0, wrong = 0, skip = 0;
  qs.forEach(qid => {
    const g = _getMyAnswer(qid), k = ansKey[qid] || null;
    if (!g) skip++; else if (g === k) correct++; else wrong++;
  });

  const ol = document.createElement('div');
  ol.className = 'analysis-overlay';
  ol.id = 'analysis-overlay';
  ol.innerHTML = `
    <div class="analysis-topbar">
      <span class="analysis-title">🔍 ${escHtml(room.test_name || 'Analysis Mode')}</span>
      <div class="analysis-topbar-stats">
        <span class="analysis-stat-pill ok">✓ ${correct}</span>
        <span class="analysis-stat-pill bad">✗ ${wrong}</span>
        <span class="analysis-stat-pill skip">— ${skip}</span>
        <span class="analysis-stat-pill" style="background:var(--surface2);color:var(--text)">${correct}/${qs.length}</span>
      </div>
      <button class="analysis-close" onclick="closeAnalysisMode()">✕ Close</button>
    </div>
    <div class="analysis-body">
      <div class="analysis-left">
        <div class="analysis-nav-header">Questions</div>
        <div class="analysis-q-grid" id="am-q-grid"></div>
      </div>
      <div class="analysis-response">
        <div class="analysis-col-label">Your Response</div>
        <div class="analysis-response-body" id="am-center"></div>
      </div>
      <div class="analysis-pdfs">
        <div class="analysis-pdf-col">
          <div class="analysis-pdf-col-label">📄 Question Paper</div>
          <iframe id="am-pdf-q" src="about:blank" title="Question PDF"></iframe>
        </div>
        <div class="analysis-pdf-col">
          <div class="analysis-pdf-col-label">📖 Solution</div>
          <iframe id="am-pdf-s" src="about:blank" title="Solution PDF"></iframe>
        </div>
      </div>
    </div>
    <div class="analysis-nav-bar">
      <button class="analysis-nav-btn" onclick="analysisNav(-1)">← Prev</button>
      <div class="analysis-nav-center" id="am-nav-label"></div>
      <button class="analysis-nav-btn" onclick="analysisNav(1)">Next →</button>
    </div>`;
  document.body.appendChild(ol);
  renderAnalysisGrid();
  renderAnalysisQuestion(0);
}

function closeAnalysisMode() {
  const ol = document.getElementById('analysis-overlay');
  if (ol) ol.remove();
}

function _getMyAnswer(qid) {
  return state.localAnswers[qid] || state.room?.players?.[state.myIdx]?.answers?.[qid] || null;
}

function renderAnalysisGrid() {
  const room = state.room;
  const ansKey = room.answer_key || {};
  const grid = document.getElementById('am-q-grid');
  if (!grid) return;
  grid.innerHTML = (room.questions || []).map((qid, idx) => {
    const given = _getMyAnswer(qid);
    const key   = ansKey[qid] || null;
    let cls = 'analysis-q-btn ';
    if (!given)           cls += 'aq-skip';
    else if (given===key) cls += 'aq-correct';
    else                  cls += 'aq-wrong';
    if (idx === _analysisIdx) cls += ' aq-current';
    const num = qid.replace(/\D/g,'');
    return `<button class="${cls}" onclick="renderAnalysisQuestion(${idx})" title="Q${num}">${num}</button>`;
  }).join('');
}

function renderAnalysisQuestion(idx) {
  const room = state.room;
  const questions = room.questions || [];
  if (idx < 0 || idx >= questions.length) return;
  _analysisIdx = idx;

  const qid    = questions[idx];
  const ansKey = room.answer_key || {};
  const given  = _getMyAnswer(qid);
  const key    = ansKey[qid] || null;
  const opts   = room.options?.[qid] || {};
  const qText  = room.question_texts?.[qid] || null;
  const pat    = UPSC_PATTERNS[_analysisPat] || UPSC_PATTERNS.gs;

  let verdict, verdictCls, marksStr;
  if (!given)          { verdict='Not Attempted'; verdictCls='skip';    marksStr='0'; }
  else if (given===key){ verdict='Correct';       verdictCls='correct'; marksStr='+'+pat.perQ; }
  else                 { verdict='Wrong';         verdictCls='wrong';   marksStr='−'+pat.neg.toFixed(2); }

  const letters = ['a','b','c','d'];
  const optRows = letters.map((l, li) => {
    const text = opts[li] || '';
    if (!text) return '';
    const isCorrect   = l === key;
    const isUserWrong = l === given && l !== key;
    const isUserRight = l === given && l === key;
    let cls = 'analysis-opt';
    if (isCorrect)   cls += ' opt-correct';
    if (isUserWrong) cls += ' opt-user-wrong';
    const icon = isCorrect ? ' ✓' : (isUserWrong ? ' ✗' : '');
    return `<div class="${cls}">
      <span class="analysis-opt-letter">${l.toUpperCase()}</span>
      <span style="flex:1;line-height:1.35">${escHtml(text)}${icon}</span>
    </div>`;
  }).join('');

  const marked = state.localMarked[qid] ? '<span style="color:#a855f7;font-size:.65rem;">🔖</span>' : '';

  // Your answer pill
  let yourPill, keyPill;
  if (!given) {
    yourPill = `<span class="analysis-ans-pill" style="color:var(--muted)">—</span>`;
  } else if (given === key) {
    yourPill = `<span class="analysis-ans-pill correct-key">${given.toUpperCase()} ✓</span>`;
  } else {
    yourPill = `<span class="analysis-ans-pill user-wrong">${given.toUpperCase()} ✗</span>`;
  }
  keyPill = key ? `<span class="analysis-ans-pill correct-key">${key.toUpperCase()}</span>` : `<span class="analysis-ans-pill">?</span>`;

  document.getElementById('am-center').innerHTML = `
    <div class="analysis-q-header">
      <span class="analysis-q-num">Q${qid.replace(/\D/g,'')}</span>
      ${marked}
      <span class="analysis-verdict ${verdictCls}">${verdict}</span>
      <span class="analysis-marks">${marksStr}</span>
    </div>
    <div class="analysis-ans-row">
      <span style="color:var(--muted)">You</span>${yourPill}
      <span style="color:var(--muted)">Key</span>${keyPill}
    </div>
    ${qText ? `<div style="background:var(--surface2);border:1px solid var(--border);border-radius:7px;padding:.55rem .65rem;font-size:.78rem;line-height:1.55;">${escHtml(qText)}</div>` : ''}
    ${optRows ? `<div class="analysis-options">${optRows}</div>` : ''}`;

  const navLabel = document.getElementById('am-nav-label');
  if (navLabel) navLabel.innerHTML = `<span>${idx+1} / ${questions.length}</span>`;

  renderAnalysisGrid();
  _analysisJumpToPage(qid);
}

function _analysisJumpToPage(qid) {
  const room = state.room;
  if (!room?.page_map) return;
  const qNum = parseInt(String(qid).replace(/\D/g,''));
  let targetPage = null, maxSQ = -1;
  for (const [ps, sq] of Object.entries(room.page_map)) {
    const p = parseInt(ps), s = parseInt(sq);
    if (!isNaN(p) && !isNaN(s) && qNum >= s && s > maxSQ) { maxSQ = s; targetPage = p; }
  }
  if (!targetPage) return;
  // Jump BOTH PDF iframes simultaneously
  const iQ = document.getElementById('am-pdf-q');
  const iS = document.getElementById('am-pdf-s');
  if (iQ && room.pdf_url)          jumpPdfIframeToPage(iQ, room.pdf_url,          targetPage);
  if (iS && room.solution_pdf_url) jumpPdfIframeToPage(iS, room.solution_pdf_url, targetPage);
}

function analysisNav(dir) {
  const total = (state.room?.questions || []).length;
  renderAnalysisQuestion(Math.max(0, Math.min(total-1, _analysisIdx + dir)));
}

// Keyboard navigation inside analysis mode
document.addEventListener('keydown', e => {
  if (!document.getElementById('analysis-overlay')) return;
  if (e.key === 'ArrowRight' || e.key === 'ArrowDown') { e.preventDefault(); analysisNav(1); }
  if (e.key === 'ArrowLeft'  || e.key === 'ArrowUp')   { e.preventDefault(); analysisNav(-1); }
  if (e.key === 'Escape') closeAnalysisMode();
});

init();

// ══════════════════════════════════════════════════════════════
//  VOICE CHANNEL  — delegates to StreamCodec (stream-codec.view.php)
// ══════════════════════════════════════════════════════════════
const VoiceChannel = (() => {
  let _call = null;
  let _client = null;
  let _muted  = true;
  let _inCall = false;

  function _setUI(inCall) {
    _inCall = inCall;
    const joinBtn  = document.getElementById('vc-join');
    const micBtn   = document.getElementById('vc-mic');
    const leaveBtn = document.getElementById('vc-leave');
    const dot      = document.getElementById('voice-dot');
    const txt      = document.getElementById('voice-status-text');
    const badge    = document.getElementById('voice-live-badge');

    if (joinBtn)  joinBtn.style.display  = inCall ? 'none' : '';
    if (micBtn)   micBtn.style.display   = inCall ? ''     : 'none';
    if (leaveBtn) leaveBtn.style.display = inCall ? ''     : 'none';
    if (dot)      dot.classList.toggle('live', inCall);
    if (txt)      txt.textContent = inCall ? 'In voice channel' : 'Not in voice';
    if (badge)    badge.classList.toggle('live', inCall);
  }

  function _renderParticipants() {
    const wrap = document.getElementById('voice-participants');
    if (!wrap || !_call) return;
    const parts = Object.values(_call.state?.participants ?? {});
    if (!parts.length) { wrap.innerHTML = ''; return; }
    wrap.innerHTML = parts.map(p => {
      const isMuted = !(p.publishedTracks?.includes('audio'));
      return `<div class="voice-participant">
        <span class="vp-name">${escHtml(p.name || p.userId || 'Player')}</span>
        <span class="vp-mic">${isMuted ? '🔇' : '🎙️'}</span>
      </div>`;
    }).join('');
  }

  async function join() {
    try {
      const myName = state.room?.players?.[state.myIdx]?.name || MY_CODE;
      // Request token from stream.php (POST JSON required)
      const res  = await fetch('stream.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'get_token', room_id: ROOM_ID, player_id: MY_CODE, player_name: myName }),
      });
      const data = await res.json();
      if (!data.token) throw new Error(data.error || 'No token');

      // Prefer StreamCodec (loaded via module script) for voice
      if (window.StreamCodec) {
        StreamCodec.startCall();
        return;
      }

      // Fallback: use GetStream SDK if loaded globally
      const SVC = window.StreamVideoClient;
      if (!SVC) {
        showToast('Voice SDK still loading — please try again in a few seconds', 'warn');
        return;
      }

      _client = new SVC({
        apiKey: <?php echo json_encode(STREAM_API_KEY); ?>,
        user: { id: MY_CODE, name: myName },
        token: data.token,
      });

      // Audio-only call using room ID as stable call ID
      _call = _client.call('audio_room', 'omr-' + ROOM_ID);
      await _call.join({ create: true, data: { members: [] } });

      // Unmute mic on join
      _muted = false;
      await _call.microphone.enable();

      _call.on('participantJoined', _renderParticipants);
      _call.on('participantLeft',   _renderParticipants);
      _call.on('trackPublished',    _renderParticipants);
      _call.on('trackUnpublished',  _renderParticipants);

      _setUI(true);
      _renderParticipants();
      _updateMicBtn();
      showToast('🎙️ Joined voice channel', 'ok');
    } catch(e) {
      console.warn('Voice join error:', e);
      showToast('Could not join voice: ' + e.message, 'error');
    }
  }

  async function leave() {
    try {
      if (_call) await _call.leave();
      if (_client) await _client.disconnectUser();
    } catch(e) {}
    _call = null; _client = null; _muted = true; _inCall = false;
    _setUI(false);
    document.getElementById('voice-participants').innerHTML = '';
    showToast('Left voice channel', 'ok');
  }

  function _updateMicBtn() {
    const btn = document.getElementById('vc-mic');
    if (!btn) return;
    btn.className = 'voice-ctrl-btn ' + (_muted ? 'muted' : 'unmuted');
    btn.innerHTML = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      ${_muted
        ? '<line x1="1" y1="1" x2="23" y2="23"/><path d="M9 9v3a3 3 0 0 0 5.12 2.12M15 9.34V4a3 3 0 0 0-5.94-.6"/><path d="M17 16.95A7 7 0 0 1 5 12v-2m14 0v2a7 7 0 0 1-.11 1.23"/><line x1="12" y1="19" x2="12" y2="23"/><line x1="8" y1="23" x2="16" y2="23"/>'
        : '<path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><line x1="12" y1="19" x2="12" y2="23"/><line x1="8" y1="23" x2="16" y2="23"/>'}
    </svg> ${_muted ? 'Unmute' : 'Mute'}`;
  }

  async function toggleMic() {
    if (!_call) return;
    _muted = !_muted;
    if (_muted) await _call.microphone.disable();
    else        await _call.microphone.enable();
    _updateMicBtn();
  }

  return { join, leave, toggleMic };
})();

function voiceJoin() {
  if (window.StreamCodec) { StreamCodec.startCall(); return; }
  VoiceChannel.join();
}
function voiceLeave() {
  if (window.StreamCodec) { StreamCodec.leaveCall(true); return; }
  VoiceChannel.leave();
}
function voiceToggleMic() {
  if (window.StreamCodec) { StreamCodec.toggleMic(); return; }
  VoiceChannel.toggleMic();
}

// ══════════════════════════════════════════════════════════════
//  JITSI MEET  (free, no account, per-exam room)
// ══════════════════════════════════════════════════════════════
function getMeetUrl() {
  const slug = 'minishiksha-' + ROOM_ID.replace(/[^a-z0-9]/gi, '-');
  // config params: skip prejoin + lobby, mute mic on entry
  return 'https://meet.jit.si/' + slug +
    '#config.prejoinPageEnabled=false' +
    '&config.lobby.enabled=false' +
    '&config.disableModeratorIndicator=true' +
    '&config.startWithAudioMuted=true' +
    '&config.requireDisplayName=false';
}

function openMeetLink() {
  window.open(getMeetUrl(), '_blank', 'noopener,noreferrer');
}

function copyMeetLink() {
  const url = getMeetUrl();
  navigator.clipboard.writeText(url).then(() => {
    showToast('📋 Meet link copied!', 'ok');
  }).catch(() => {
    prompt('Copy this link:', url);
  });
}

let _meetQrShown = false;
function toggleMeetQR() {
  const wrap = document.getElementById('meet-qr-wrap');
  if (!wrap) return;
  _meetQrShown = !_meetQrShown;
  wrap.style.display = _meetQrShown ? '' : 'none';
  if (_meetQrShown) {
    const img = document.getElementById('meet-qr-img');
    if (img && !img.src.includes('qrserver')) {
      const url = encodeURIComponent(getMeetUrl());
      img.src = `https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=${url}&color=ffffff&bgcolor=171825&margin=4`;
    }
  }
}

// ══════════════════════════════════════════════════════════════
//  PLAYER STATUS  (BRB / Break / Help / Active)
// ══════════════════════════════════════════════════════════════
const STATUS_META = {
  active: { label: 'Active',  emoji: '🟢', cls: 'tsb-active', pill: '' },
  brb:    { label: 'BRB',     emoji: '🕐', cls: 'tsb-brb',    pill: 'psp-brb'   },
  break:  { label: 'Break',   emoji: '☕', cls: 'tsb-break',  pill: 'psp-break' },
  help:   { label: '🆘 Help', emoji: '🆘', cls: 'tsb-help',   pill: 'psp-help'  },
};

async function setMyStatus(status) {
  state.myStatus = status;
  // Highlight selected chip
  ['active','brb','break','help'].forEach(s => {
    const el = document.getElementById('sc-' + s);
    if (el) el.className = 'status-chip' + (s === status ? ' sel-' + s : '');
  });
  const meta = STATUS_META[status] || STATUS_META.active;
  const name  = state.room?.players?.[state.myIdx]?.name || 'Player';

  // Push to server so teammates see it on next sync
  try {
    await api({ action: 'update_player_status', room_id: ROOM_ID, player_id: MY_CODE, status });
  } catch(e) {}

  // Post a chat notification for non-active statuses
  if (status !== 'active') {
    try {
      await api({ action: 'send_message', room_id: ROOM_ID, player_id: MY_CODE,
        message: meta.emoji + ' ' + name + ' is now ' + meta.label });
    } catch(e) {}
    if (status === 'help') {
      showExamAlert('🆘 ' + name + ' needs help!', 'warn');
      typeof sfxRingingAlert === 'function' && sfxRingingAlert();
    }
  }
  renderTeamStatuses();
}

function renderTeamStatuses() {
  const wrap = document.getElementById('team-status-list');
  if (!wrap || !state.room) return;
  const html = state.room.players.map((p, i) => {
    const isMe   = i === state.myIdx;
    // My own status comes from local state (instant); others from server room data
    const status = isMe ? state.myStatus : (p.status || 'active');
    const meta   = STATUS_META[status] || STATUS_META.active;
    return `<div class="team-status-row">
      <span>${meta.emoji}</span>
      <span style="flex:1;font-size:.78rem;font-weight:${isMe?'700':'500'}">${escHtml(p.name)}${isMe?' (You)':''}</span>
      <span class="team-status-badge ${meta.cls}">${meta.label}</span>
    </div>`;
  }).join('');
  wrap.innerHTML = html || '<div style="color:var(--muted);font-size:.75rem;padding:.5rem 0;">No players yet</div>';
}

</script>
<?php if (file_exists(__DIR__ . '/stream-codec.view.php')) include __DIR__ . '/stream-codec.view.php'; ?>
</body>
</html>



