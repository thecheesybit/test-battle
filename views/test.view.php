<?php
// test.php — Main landing page for MiniShiksha OMR System
// Place at: https://minishiksha.in/test.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MiniShiksha — OMR Battle Room v3.0.0</title>
<link rel="icon" href="https://minishiksha.in/wp-content/uploads/2025/06/icons8-class-pulsar-gradient-16.png" sizes="any">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=JetBrains+Mono:wght@400;500;700&family=Instrument+Serif:ital@0;1&display=swap" rel="stylesheet">
<style>
:root {
  --bg: #07080f;
  --surface: #0f1018;
  --surface2: #171825;
  --border: #252638;
  --accent: #5b7fff;
  --accent2: #ff5f7e;
  --accent3: #ffe156;
  --accent-dim: rgba(91,127,255,0.12);
  --text: #eaeaf5;
  --muted: #5a5a7a;
  --muted2: #3a3a55;
  --radius: 14px;
  --tr: 0.2s cubic-bezier(.4,0,.2,1);
  --glow: rgba(91,127,255,0.3);
  --ok: #4fffb0;
  --warn: #ffa502;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
html{scroll-behavior:smooth;}
body{
  font-family:'Syne',sans-serif;
  background:var(--bg);
  color:var(--text);
  min-height:100vh;
  overflow-x:hidden;
}

/* ── BG ── */
.bg-wrap{position:fixed;inset:0;z-index:0;pointer-events:none;overflow:hidden;}
.bg-grid{
  position:absolute;inset:0;
  background-image:
    linear-gradient(rgba(91,127,255,0.04) 1px,transparent 1px),
    linear-gradient(90deg,rgba(91,127,255,0.04) 1px,transparent 1px);
  background-size:48px 48px;
}
.bg-orb{position:absolute;border-radius:50%;filter:blur(100px);animation:orb-float 10s ease-in-out infinite;}
.bg-orb.o1{width:500px;height:500px;background:rgba(91,127,255,0.18);top:-150px;left:-100px;}
.bg-orb.o2{width:400px;height:400px;background:rgba(255,95,126,0.14);bottom:-100px;right:-100px;animation-delay:4s;}
.bg-orb.o3{width:250px;height:250px;background:rgba(255,225,86,0.1);top:40%;left:60%;animation-delay:7s;}
@keyframes orb-float{0%,100%{transform:translateY(0) scale(1);}50%{transform:translateY(-25px) scale(1.06);}}

/* ── LAYOUT ── */
.page{position:relative;z-index:1;min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:flex-start;padding:2rem 1rem;}

/* ── HEADER ── */
.header{text-align:center;margin-bottom:3.5rem;padding-top:2rem;}
.logo-badge{
  display:inline-flex;align-items:center;gap:8px;
  background:var(--accent-dim);border:1px solid var(--accent);
  border-radius:50px;padding:6px 18px;margin-bottom:1.5rem;
  font-family:'JetBrains Mono',monospace;font-size:.7rem;letter-spacing:3px;text-transform:uppercase;color:var(--accent);
}
.logo-dot{width:6px;height:6px;border-radius:50%;background:var(--accent);animation:blink 1.5s infinite;}
@keyframes blink{0%,100%{opacity:1;}50%{opacity:.3;}}
h1{font-size:clamp(2.5rem,7vw,5rem);font-weight:800;letter-spacing:-2px;line-height:1;}
.h1-line1{display:block;color:var(--text);}
.h1-line2{display:block;font-family:'Instrument Serif',serif;font-style:italic;color:var(--accent);}
.tagline{color:var(--muted);margin-top:1rem;font-size:.95rem;line-height:1.6;}

/* ── CARD GRID ── */
.card-grid{display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;width:100%;max-width:800px;}
@media(max-width:600px){.card-grid{grid-template-columns:1fr;}}

.action-card{
  background:var(--surface);border:2px solid var(--border);border-radius:var(--radius);
  padding:2rem;cursor:pointer;transition:var(--tr);
  display:flex;flex-direction:column;gap:1rem;
  position:relative;overflow:hidden;
}
.action-card::before{
  content:'';position:absolute;inset:0;
  background:linear-gradient(135deg,var(--card-accent-dim,transparent),transparent);
  opacity:0;transition:var(--tr);
}
.action-card:hover{border-color:var(--card-accent,var(--accent));transform:translateY(-3px);}
.action-card:hover::before{opacity:1;}
.action-card.create{--card-accent:var(--accent);--card-accent-dim:rgba(91,127,255,0.1);}
.action-card.load{--card-accent:var(--accent2);--card-accent-dim:rgba(255,95,126,0.1);}

.card-icon{font-size:2.5rem;}
.card-title{font-size:1.3rem;font-weight:700;}
.card-sub{color:var(--muted);font-size:.85rem;line-height:1.5;}
.card-arrow{
  margin-top:auto;align-self:flex-start;
  padding:8px 20px;border-radius:50px;font-size:.8rem;font-weight:700;letter-spacing:1px;
  border:1.5px solid var(--card-accent,var(--accent));
  color:var(--card-accent,var(--accent));
  transition:var(--tr);font-family:'JetBrains Mono',monospace;
}
.action-card:hover .card-arrow{background:var(--card-accent,var(--accent));color:#000;}

/* ── MODAL ── */
.modal-overlay{
  display:none;position:fixed;inset:0;z-index:200;
  background:rgba(0,0,0,.75);backdrop-filter:blur(8px);
  align-items:center;justify-content:center;padding:1rem;
}
.modal-overlay.open{display:flex;}
.modal{
  background:var(--surface);border:1.5px solid var(--border);border-radius:var(--radius);
  padding:2rem;width:100%;max-width:560px;
  max-height:90vh;overflow-y:auto;
  position:relative;animation:modal-in .25s ease;
}
@keyframes modal-in{from{opacity:0;transform:translateY(20px);}to{opacity:1;transform:translateY(0);}}
.modal-close{
  position:absolute;top:1rem;right:1rem;
  background:var(--surface2);border:1px solid var(--border);border-radius:8px;
  color:var(--muted);width:32px;height:32px;
  display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:1.1rem;
  transition:var(--tr);
}
.modal-close:hover{color:var(--text);border-color:var(--text);}
.modal-title{font-size:1.4rem;font-weight:800;margin-bottom:.4rem;}
.modal-sub{color:var(--muted);font-size:.85rem;margin-bottom:1.75rem;}

/* ── FORM ELEMENTS ── */
.form-group{margin-bottom:1.25rem;}
label{display:block;font-size:.7rem;letter-spacing:2px;text-transform:uppercase;color:var(--muted);margin-bottom:.5rem;font-family:'JetBrains Mono',monospace;}
input[type=text],input[type=number],select,textarea{
  width:100%;background:var(--bg);
  border:1.5px solid var(--border);border-radius:10px;
  color:var(--text);padding:11px 15px;
  font-family:'Syne',sans-serif;font-size:.95rem;outline:none;transition:var(--tr);
}
input:focus,select:focus,textarea:focus{border-color:var(--accent);}
input::placeholder,textarea::placeholder{color:var(--muted);}
textarea{resize:vertical;min-height:100px;font-family:'JetBrains Mono',monospace;font-size:.8rem;}
select{appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%235a5a7a' stroke-width='1.5' fill='none'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 14px center;}

.row-2{display:grid;grid-template-columns:1fr 1fr;gap:1rem;}
@media(max-width:480px){.row-2{grid-template-columns:1fr;}}

/* ── PLAYER NAMES SECTION ── */
.players-section{margin-bottom:1.25rem;}
.players-grid{display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-top:.5rem;}
@media(max-width:480px){.players-grid{grid-template-columns:1fr;}}
.player-input-wrap{
  background:var(--surface2);border:1.5px solid var(--border);border-radius:10px;
  padding:.75rem 1rem;
}
.player-input-label{
  font-size:.6rem;letter-spacing:2px;text-transform:uppercase;
  margin-bottom:.4rem;font-family:'JetBrains Mono',monospace;
}
.player-input-label.p1{color:#5b7fff;}
.player-input-label.p2{color:#ff5f7e;}
.player-input-label.p3{color:#ffe156;}
.player-input-label.p4{color:#4fffb0;}
.player-input-wrap input{
  background:transparent;border:none;padding:0;font-size:.9rem;
  border-bottom:1px solid var(--border);border-radius:0;padding-bottom:4px;
}
.player-input-wrap input:focus{border-bottom-color:var(--accent);}

/* ── TIMER TOGGLE ── */
.toggle-group{display:flex;gap:.5rem;flex-wrap:wrap;}
.toggle-btn{
  flex:1;padding:10px;border-radius:10px;min-width:80px;
  border:1.5px solid var(--border);background:transparent;
  color:var(--muted);font-family:'Syne',sans-serif;font-size:.85rem;
  font-weight:600;cursor:pointer;transition:var(--tr);text-align:center;
}
.toggle-btn.active{border-color:var(--accent);color:var(--accent);background:var(--accent-dim);}

/* ── SUBMIT BTN ── */
.btn-primary{
  width:100%;padding:1rem;margin-top:1.5rem;
  background:linear-gradient(135deg,var(--accent),#7b5fff);
  border:none;border-radius:var(--radius);
  color:#fff;font-family:'Syne',sans-serif;font-size:1rem;font-weight:700;
  letter-spacing:.5px;cursor:pointer;transition:var(--tr);
  display:flex;align-items:center;justify-content:center;gap:.5rem;
}
.btn-primary:hover{transform:translateY(-2px);box-shadow:0 12px 40px var(--glow);}
.btn-primary:active{transform:translateY(0);}
.btn-primary:disabled{opacity:.5;cursor:not-allowed;transform:none;}

.btn-secondary{
  width:100%;padding:.85rem;margin-top:.75rem;
  background:transparent;
  border:1.5px solid var(--border);border-radius:var(--radius);
  color:var(--muted);font-family:'Syne',sans-serif;font-size:.9rem;font-weight:600;
  cursor:pointer;transition:var(--tr);
}
.btn-secondary:hover{border-color:var(--text);color:var(--text);}

/* ── ERROR / INFO ── */
.msg{
  padding:10px 14px;border-radius:8px;
  font-size:.85rem;margin-top:.75rem;display:none;
}
.msg.error{background:rgba(255,71,87,0.12);border:1px solid rgba(255,71,87,0.4);color:#ff6b7a;}
.msg.success{background:rgba(79,255,176,0.1);border:1px solid rgba(79,255,176,0.4);color:#4fffb0;}
.msg.show{display:block;}

/* ── UPLOAD PROGRESS BAR ── */
.upload-progress{
  margin-top:.75rem;padding:10px 14px;border-radius:8px;
  background:var(--surface2);border:1px solid var(--border);
}
.upload-progress-label{
  font-size:.75rem;color:var(--muted);margin-bottom:6px;
  font-family:'JetBrains Mono',monospace;display:flex;justify-content:space-between;
}
.upload-progress-track{
  width:100%;height:6px;border-radius:3px;background:var(--bg);overflow:hidden;
}
.upload-progress-fill{
  height:100%;border-radius:3px;transition:width .2s ease;
  background:linear-gradient(90deg,var(--accent),#7b5fff,var(--accent));
  background-size:200% 100%;
  animation:progress-shimmer 1.5s linear infinite;
}
@keyframes progress-shimmer{0%{background-position:200% 0}100%{background-position:-200% 0}}

/* ── SPINNER ── */
.spinner{
  width:18px;height:18px;border:2px solid rgba(255,255,255,.3);
  border-top-color:#fff;border-radius:50%;animation:spin .6s linear infinite;display:none;
}
.spinner.show{display:inline-block;}
@keyframes spin{to{transform:rotate(360deg);}}

/* ── RECENT ROOMS ── */
.recent-section{margin-top:2.5rem;width:100%;max-width:800px;}
.section-label{
  font-size:.65rem;letter-spacing:3px;text-transform:uppercase;color:var(--muted);
  margin-bottom:1rem;font-family:'JetBrains Mono',monospace;display:flex;align-items:center;gap:.5rem;
}
.section-label::after{content:'';flex:1;height:1px;background:var(--border);}
.recent-list{display:flex;flex-direction:column;gap:.5rem;}
.recent-item{
  background:var(--surface);border:1px solid var(--border);border-radius:10px;
  padding:.85rem 1.1rem;display:flex;align-items:center;gap:1rem;cursor:pointer;transition:var(--tr);
}
.recent-item:hover{border-color:var(--accent);background:var(--accent-dim);}
.recent-icon{font-size:1.2rem;}
.recent-info{flex:1;}
.recent-name{font-size:.9rem;font-weight:600;}
.recent-meta{font-size:.75rem;color:var(--muted);font-family:'JetBrains Mono',monospace;}
.recent-badge{
  font-size:.65rem;padding:3px 8px;border-radius:50px;
  border:1px solid var(--border);color:var(--muted);
  font-family:'JetBrains Mono',monospace;
}

/* ── DIVIDER ── */
.divider{
  display:flex;align-items:center;gap:.75rem;margin:1.5rem 0;color:var(--muted);font-size:.75rem;
  font-family:'JetBrains Mono',monospace;
}
.divider::before,.divider::after{content:'';flex:1;height:1px;background:var(--border);}

/* ── FOOTER ── */
.footer{margin-top:4rem;text-align:center;color:var(--muted);font-size:.78rem;padding-bottom:2rem;}
.footer a{color:var(--accent);text-decoration:none;}

/* ── LOADING OVERLAY ── */
.loading-overlay{
  display:none;position:fixed;inset:0;z-index:300;
  background:rgba(7,8,15,.85);backdrop-filter:blur(10px);
  align-items:center;justify-content:center;flex-direction:column;gap:1rem;
}
.loading-overlay.show{display:flex;}
.loading-text{font-size:1.1rem;font-weight:700;color:var(--accent);}
.loading-sub{font-size:.85rem;color:var(--muted);}
.big-spinner{
  width:48px;height:48px;border:3px solid var(--border);
  border-top-color:var(--accent);border-radius:50%;animation:spin .8s linear infinite;
}

/* ── TEST LIST (Load Test Modal) ── */
.test-list{display:flex;flex-direction:column;gap:.6rem;margin-bottom:1rem;}
.test-list-item{
  background:var(--surface2);border:1.5px solid var(--border);border-radius:12px;
  padding:.9rem 1.1rem;cursor:pointer;transition:var(--tr);
  display:flex;align-items:center;gap:1rem;
}
.test-list-item:hover{border-color:var(--accent2);background:rgba(255,95,126,0.06);}
.test-list-item.selected{border-color:var(--accent2);background:rgba(255,95,126,0.1);box-shadow:0 0 0 3px rgba(255,95,126,0.15);}
.test-list-icon{
  width:42px;height:42px;border-radius:10px;
  background:rgba(255,95,126,0.1);border:1px solid rgba(255,95,126,0.25);
  display:flex;align-items:center;justify-content:center;font-size:1.25rem;flex-shrink:0;
}
.test-list-info{flex:1;min-width:0;}
.test-list-name{font-size:.95rem;font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.test-list-meta{font-size:.72rem;color:var(--muted);font-family:'JetBrains Mono',monospace;margin-top:2px;}
.test-list-badge{
  font-size:.65rem;padding:4px 10px;border-radius:50px;
  border:1px solid var(--accent2);color:var(--accent2);
  font-family:'JetBrains Mono',monospace;white-space:nowrap;
}
.test-list-empty{
  text-align:center;padding:2.5rem 1rem;
  background:var(--surface2);border:1.5px dashed var(--border);border-radius:12px;
  margin-bottom:1rem;
}
.test-list-empty-icon{font-size:2.5rem;margin-bottom:.75rem;}
.test-list-empty-title{font-size:1rem;font-weight:700;margin-bottom:.4rem;color:var(--muted);}
.test-list-empty-sub{font-size:.82rem;color:var(--muted2);line-height:1.5;}

.test-list-loading{
  display:flex;align-items:center;justify-content:center;gap:.75rem;
  padding:2rem;color:var(--muted);font-size:.9rem;
}
.test-list-loading .mini-spinner{
  width:20px;height:20px;border:2px solid var(--border);
  border-top-color:var(--accent2);border-radius:50%;animation:spin .6s linear infinite;
}

/* ── CREATE TEST INLINE FORM ── */
.create-test-form{
  background:var(--surface2);border:1.5px solid var(--border);border-radius:12px;
  padding:1.25rem;margin-bottom:1rem;animation:modal-in .2s ease;
}
.create-test-header{
  display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;
}
.create-test-title{font-size:.9rem;font-weight:700;color:var(--accent3);}
.create-test-close{
  background:none;border:none;color:var(--muted);font-size:1rem;cursor:pointer;
  padding:4px 8px;border-radius:6px;transition:var(--tr);
}
.create-test-close:hover{color:var(--text);}

/* ── TEST SELECTOR (Create Room Modal) ── */
.test-selector{margin-bottom:1.25rem;}
.test-selector-selected{
  background:var(--surface2);border:1.5px solid var(--ok);border-radius:10px;
  padding:.85rem 1rem;display:flex;align-items:center;gap:.75rem;
  animation:modal-in .2s ease;
}
.test-selector-info{flex:1;}
.test-selector-name{font-size:.95rem;font-weight:700;color:var(--ok);}
.test-selector-meta{font-size:.72rem;color:var(--muted);font-family:'JetBrains Mono',monospace;margin-top:2px;}
.test-selector-change{
  padding:6px 14px;border-radius:8px;border:1px solid var(--border);
  background:transparent;color:var(--muted);font-size:.75rem;font-family:'JetBrains Mono',monospace;
  cursor:pointer;transition:var(--tr);
}
.test-selector-change:hover{border-color:var(--text);color:var(--text);}
.test-selector-empty{
  background:var(--surface2);border:1.5px dashed var(--border);border-radius:10px;
  padding:1.25rem;text-align:center;
}
.test-selector-empty-text{font-size:.85rem;color:var(--muted);margin-bottom:.75rem;}
.test-selector-actions{display:flex;gap:.5rem;justify-content:center;flex-wrap:wrap;}
.test-selector-btn{
  padding:8px 16px;border-radius:8px;
  border:1.5px solid var(--border);background:transparent;
  color:var(--muted);font-family:'Syne',sans-serif;font-size:.82rem;font-weight:600;
  cursor:pointer;transition:var(--tr);
}
.test-selector-btn:hover{border-color:var(--accent);color:var(--accent);}
.test-selector-btn.primary{
  background:var(--accent-dim);border-color:var(--accent);color:var(--accent);
}

/* ── PAGE MAPPING MODAL ── */
.map-body{
  display:flex;gap:1rem;margin-bottom:1rem;
}
@media(max-width:600px){.map-body{flex-direction:column;}}
.map-pdf-side{
  flex:1;min-width:0;display:flex;flex-direction:column;
  background:var(--bg);border:1.5px solid var(--border);border-radius:12px;overflow:hidden;
}
.map-pdf-side iframe{flex:1;border:none;min-height:350px;}
.map-pdf-nav{
  display:flex;align-items:center;gap:.5rem;padding:.5rem .75rem;
  background:var(--surface2);border-top:1px solid var(--border);
  font-family:'JetBrains Mono',monospace;font-size:.8rem;color:var(--muted);
}
.map-pdf-nav button{
  background:var(--surface);border:1px solid var(--border);border-radius:6px;
  color:var(--muted);width:28px;height:28px;cursor:pointer;font-size:.9rem;transition:var(--tr);
}
.map-pdf-nav button:hover{color:var(--text);border-color:var(--text);}
.map-input-side{
  width:220px;flex-shrink:0;display:flex;flex-direction:column;gap:.5rem;
  max-height:420px;overflow-y:auto;
}
@media(max-width:600px){.map-input-side{width:100%;max-height:200px;}}
.map-page-row{
  display:flex;align-items:center;gap:.4rem;
  background:var(--surface2);border:1px solid var(--border);border-radius:8px;
  padding:.5rem .6rem;font-size:.75rem;font-family:'JetBrains Mono',monospace;
  cursor:pointer;transition:var(--tr);
}
.map-page-row:hover,.map-page-row.active{border-color:var(--accent);background:var(--accent-dim);}
.map-page-row .pg-label{color:var(--muted);white-space:nowrap;min-width:36px;}
.map-page-row input{
  width:40px;background:var(--bg);border:1px solid var(--border);border-radius:4px;
  color:var(--text);padding:3px 4px;font-family:'JetBrains Mono',monospace;
  font-size:.75rem;text-align:center;
}
.map-page-row input:focus{border-color:var(--accent);}
.map-page-row .pg-dash{color:var(--muted2);}
.map-status{
  font-size:.72rem;color:var(--ok);font-family:'JetBrains Mono',monospace;
  padding:.25rem 0;
}
/* ── UI REVAMP TEST TILES ── */
.test-category-title {
  font-size: 1.05rem;
  font-weight: 800;
  color: var(--text);
  margin-top: 1.5rem;
  margin-bottom: 1rem;
  display: flex;
  align-items: center;
  gap: 12px;
}
.test-category-title::after {
  content: '';
  flex: 1;
  height: 1.5px;
  background: var(--border);
  border-radius: 2px;
}

.test-tile-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 1rem;
}

.test-tile {
  background: var(--surface2);
  border: 1.5px solid var(--border);
  border-radius: 14px;
  padding: 1.25rem;
  cursor: pointer;
  transition: all 0.25s cubic-bezier(0.2,0.8,0.2,1);
  display: flex;
  flex-direction: column;
  position: relative;
  overflow: hidden;
}
.test-tile::before {
  content:''; position:absolute; inset:0;
  background: linear-gradient(135deg, rgba(255,95,126,0.1), transparent);
  opacity: 0; transition: var(--tr);
}
.test-tile:hover {
  border-color: var(--accent2);
  transform: translateY(-4px);
  box-shadow: 0 12px 24px rgba(255, 95, 126, 0.12);
}
.test-tile:hover::before { opacity: 1; }

.test-tile.selected {
  border-color: var(--accent2);
  background: rgba(255, 95, 126, 0.08);
  box-shadow: 0 0 0 2px rgba(255, 95, 126, 0.15);
}

.test-tile-header {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  margin-bottom: 1rem;
  position: relative; z-index: 1;
}
.test-tile-icon {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  background: var(--accent-dim);
  border: 1px solid var(--accent);
  color: var(--accent);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.3rem;
  flex-shrink: 0;
}
.test-tile-info {
  flex: 1;
  min-width: 0;
}
.test-tile-name {
  font-size: .95rem;
  font-weight: 800;
  color: var(--text);
  line-height: 1.4;
  margin-bottom: .25rem;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.test-tile-meta {
  font-size: .75rem;
  color: var(--muted);
  font-family: 'JetBrains Mono', monospace;
}

.test-tile-badges {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 6px;
  margin-top: auto;
  padding-top: 1rem;
  border-top: 1px solid rgba(255,255,255,0.05);
  position: relative; z-index: 1;
}
.tile-badge {
  font-size: .65rem;
  padding: 4px 10px;
  border-radius: 50px;
  border: 1px solid var(--border);
  font-family: 'JetBrains Mono', monospace;
  background: var(--surface);
  white-space: nowrap;
}
.tile-badge.tag-gs { border-color: #5b7fff; color: #5b7fff; background: rgba(91,127,255,0.08); }
.tile-badge.tag-csat { border-color: #ffe156; color: #ffe156; background: rgba(255,225,86,0.08); }

.tile-badge.action { cursor: pointer; transition: var(--tr); color: var(--muted); }
.tile-badge.action:hover { background: var(--border); color: var(--text); }
.tile-badge.action.pdf-add { border-color: var(--ok); color: var(--ok); }
.tile-badge.action.pdf-edit { border-color: var(--warn); color: var(--warn); }
.tile-badge.action.sol-add { border-color: var(--accent3); color: var(--accent3); }
.tile-badge.action.sol-edit { border-color: var(--warn); color: var(--warn); }
.tile-badge.action.map-add { border-color: var(--accent); color: var(--accent); }
.tile-badge.action.map-edit { border-color: var(--muted); color: var(--muted); }
</style>
</head>
<body>

<div class="bg-wrap">
  <div class="bg-grid"></div>
  <div class="bg-orb o1"></div>
  <div class="bg-orb o2"></div>
  <div class="bg-orb o3"></div>
</div>

<div class="page">
  <!-- Header -->
  <div class="header">
    <div class="logo-badge"><span class="logo-dot"></span> MiniShiksha OMR</div>
    <h1>
      <span class="h1-line1">Battle</span>
      <span class="h1-line2">Prep Room</span>
    </h1>
    <div class="tagline">Create multiplayer OMR test rooms instantly. Upload your answer key<br>and let the battle begin.</div>
  </div>

  <!-- ── ACTIVE TESTS (REJOIN) ── -->
  <div id="active-tests-section" style="display:none; width:100%; max-width:800px; margin-bottom:2.5rem;">
    <div class="section-label" style="justify-content:center; text-align:center; margin-bottom:1.5rem;">
      <span style="background:var(--bg); padding:0 10px; color:var(--accent);">Ongoing Tests</span>
    </div>
    <div id="active-tests-list" style="display:flex; flex-direction:column; gap:.75rem;"></div>
  </div>

  <!-- ── CARD GRID ── -->
  <div class="card-grid">
    <div class="action-card create" onclick="openCreateModal()">
      <div class="card-icon">🚀</div>
      <div>
        <div class="card-title">Create Room</div>
        <div class="card-sub">Start a new test session. Pick a test, set timer, invite players.</div>
      </div>
      <div class="card-arrow">CREATE →</div>
    </div>
    <div class="action-card load" onclick="openLoadModal()">
      <div class="card-icon">📂</div>
      <div>
        <div class="card-title">Load Test</div>
        <div class="card-sub">Browse available tests or create a new one from JSON.</div>
      </div>
      <div class="card-arrow">LOAD →</div>
    </div>
  </div>

  <!-- Join by Code -->
  <div class="recent-section" style="max-width:500px;margin-top:2rem;">
    <div class="divider">or join existing</div>
    <div style="display:flex;gap:.75rem;">
      <input type="text" id="join-code-input" placeholder="Enter 3-digit room code" maxlength="6"
        style="flex:1;text-align:center;font-size:1.1rem;font-family:'JetBrains Mono',monospace;letter-spacing:4px;"
        oninput="this.value=this.value.toUpperCase()">
      <button class="btn-primary" style="width:auto;padding:.75rem 1.5rem;margin-top:0;" onclick="joinByCode()">JOIN</button>
    </div>
    <div class="msg error" id="join-error"></div>
  </div>



  <div class="footer">
    <a href="https://minishiksha.in">← Back to MiniShiksha</a> &nbsp;·&nbsp; OMR Battle System v3.0.0
  </div>
</div>

<!-- ══ MODAL: CREATE ROOM ══ -->
<div class="modal-overlay" id="modal-create">
  <div class="modal">
    <button class="modal-close" onclick="closeModal('modal-create')">✕</button>
    <div class="modal-title">🚀 Create New Room</div>
    <div class="modal-sub">Select a test and configure your session.</div>

    <!-- Test Selector -->
    <div class="test-selector" id="create-test-selector">
      <label>Selected Test</label>
      <div id="create-test-display">
        <!-- Filled dynamically -->
      </div>
    </div>

    <div class="form-group">
      <label>Timer Mode</label>
      <div class="toggle-group" id="timer-mode-toggle">
        <button class="toggle-btn active" data-val="countdown" onclick="setToggle('timer-mode-toggle',this)">⏳ Countdown</button>
        <button class="toggle-btn" data-val="stopwatch" onclick="setToggle('timer-mode-toggle',this)">⏱ Stopwatch</button>
        <button class="toggle-btn" data-val="none" onclick="setToggle('timer-mode-toggle',this)">∞ No Timer</button>
      </div>
    </div>

    <div class="form-group" id="duration-group">
      <label>Duration (minutes)</label>
      <input type="number" id="c-duration" value="120" min="1" max="360">
    </div>

    <div class="form-group">
      <label>Number of Players</label>
      <div class="toggle-group" id="player-count-toggle">
        <button class="toggle-btn active" data-val="1" onclick="setToggle('player-count-toggle',this);updatePlayerInputs()">Solo</button>
        <button class="toggle-btn" data-val="2" onclick="setToggle('player-count-toggle',this);updatePlayerInputs()">2 Players</button>
        <button class="toggle-btn" data-val="3" onclick="setToggle('player-count-toggle',this);updatePlayerInputs()">3 Players</button>
        <button class="toggle-btn" data-val="4" onclick="setToggle('player-count-toggle',this);updatePlayerInputs()">4 Players</button>
      </div>
    </div>

    <div class="players-section" id="players-section">
      <label>Player Names</label>
      <div class="players-grid" id="players-grid">
        <div class="player-input-wrap">
          <div class="player-input-label p1">Player 1</div>
          <input type="text" class="player-name-input" placeholder="Enter name..." data-idx="0">
        </div>
      </div>
    </div>

    <button class="btn-primary" onclick="createRoom()" id="btn-create">
      <span class="spinner" id="create-spinner"></span>
      <span id="btn-create-text">Create Room &amp; Get Code</span>
    </button>
    <div class="msg" id="create-msg"></div>
  </div>
</div>

<!-- ══ MODAL: LOAD TEST ══ -->
<div class="modal-overlay" id="modal-load">
  <div class="modal">
    <button class="modal-close" onclick="closeModal('modal-load')">✕</button>
    <div class="modal-title">📂 Load Existing Test</div>
    <div class="modal-sub">Select an existing test or create a new one.</div>

    <!-- Test List -->
    <div id="load-test-list-container">
      <div class="test-list-loading" id="load-test-loading">
        <div class="mini-spinner"></div>
        Loading tests...
      </div>
    </div>

    <!-- Create Test Inline Form (hidden by default) -->
    <div id="load-create-form-wrap" style="display:none;">
      <div class="create-test-form">
        <div class="create-test-header">
          <div class="create-test-title">✨ Create New Test</div>
          <button class="create-test-close" onclick="hideCreateTestForm()">✕</button>
        </div>
        <div class="form-group" style="margin-bottom:.75rem;">
          <label>Test Name</label>
          <input type="text" id="new-test-name" placeholder="e.g. CSAT_Mock_9979">
        </div>
        <div class="form-group" style="margin-bottom:.75rem;">
          <label>Test Tag / Category</label>
          <select id="new-test-tag">
            <option value="General">General / Uncategorized</option>
            <option value="GS">General Studies (GS)</option>
            <option value="CSAT">CSAT</option>
          </select>
        </div>
        <div class="form-group" style="margin-bottom:.75rem;">
          <label>Answer Key JSON</label>
          <textarea id="new-test-json" placeholder='Paste the full JSON content here...&#10;&#10;{"test_info":{...},"responses":{"Q1":"a","Q2":"b",...}}'></textarea>
        </div>
        <div class="form-group" style="margin-bottom:.75rem;">
          <label>Question Paper PDF <span style="opacity:.5">(Optional)</span></label>
          <div style="position:relative;">
            <input type="file" id="new-test-pdf" accept=".pdf" style="display:none" onchange="onPdfSelected(this)">
            <div id="pdf-upload-area" onclick="document.getElementById('new-test-pdf').click()"
              style="border:1.5px dashed var(--border);border-radius:10px;padding:1rem;text-align:center;cursor:pointer;transition:var(--tr);background:var(--bg);">
              <div style="font-size:1.5rem;margin-bottom:.3rem;">📄</div>
              <div style="font-size:.82rem;color:var(--muted);" id="pdf-upload-label">Click to select PDF file</div>
            </div>
          </div>
        </div>
        <div class="form-group" style="margin-bottom:.75rem;">
          <label>Solution PDF <span style="opacity:.5">(Optional)</span></label>
          <div style="position:relative;">
            <input type="file" id="new-test-sol-pdf" accept=".pdf" style="display:none" onchange="onSolPdfSelected(this)">
            <div id="sol-pdf-upload-area" onclick="document.getElementById('new-test-sol-pdf').click()"
              style="border:1.5px dashed var(--border);border-radius:10px;padding:.75rem;text-align:center;cursor:pointer;transition:var(--tr);background:var(--bg);">
              <div style="font-size:1.2rem;margin-bottom:.2rem;">📖</div>
              <div style="font-size:.78rem;color:var(--muted);" id="sol-pdf-upload-label">Click to select solution PDF</div>
            </div>
          </div>
        </div>
        <div class="msg error" id="new-test-error"></div>
        <button class="btn-primary" style="margin-top:.75rem;background:linear-gradient(135deg,var(--accent3),#ff8c42);" onclick="saveNewTest()">
          <span class="spinner" id="save-test-spinner"></span>
          <span id="save-test-btn-text">Save Test</span>
        </button>
      </div>
    </div>

    <button class="btn-secondary" id="load-create-test-btn" onclick="showCreateTestForm()" style="margin-top:0;">
      ✨ Create New Test
    </button>

    <div class="msg" id="load-msg"></div>
  </div>
</div>

<!-- ══ MODAL: PAGE MAPPING ══ -->
<div class="modal-overlay" id="modal-map">
  <div class="modal" style="max-width:780px;">
    <button class="modal-close" onclick="closeModal('modal-map')">✕</button>
    <div class="modal-title">📐 Map Questions to Pages</div>
    <div class="modal-sub" id="map-modal-sub">Set which questions are on each page of the PDF.</div>

    <div class="map-body">
      <div class="map-pdf-side">
        <iframe id="map-pdf-iframe" src="about:blank"></iframe>
        <div class="map-pdf-nav">
          <button onclick="mapPageNav(-1)">←</button>
          <span>Page <strong id="map-current-page">1</strong> / <span id="map-total-pages">?</span></span>
          <button onclick="mapPageNav(1)">→</button>
          <span style="margin-left:auto;font-size:.65rem;color:var(--muted2)">Use scroll to browse</span>
        </div>
      </div>
      <div class="map-input-side" id="map-inputs"></div>
    </div>

    <div class="map-status" id="map-status"></div>
    <button class="btn-primary" style="background:linear-gradient(135deg,var(--ok),#2ecc71);" onclick="savePageMap()">
      <span class="spinner" id="map-save-spinner"></span>
      <span id="map-save-text">💾 Save Mapping</span>
    </button>
  </div>
</div>

<!-- Loading overlay -->
<div class="loading-overlay" id="loading-overlay">
  <div class="big-spinner"></div>
  <div class="loading-text">Creating your room...</div>
  <div class="loading-sub">Setting up session files</div>
</div>

<script>
// ── STATE ──
let selectedTest = null; // {name, q_count, test_info}
let cachedTests = [];     // cached list from server

// ── TOGGLE HELPERS ──
function setToggle(groupId, btn) {
  document.querySelectorAll('#' + groupId + ' .toggle-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  if (groupId === 'timer-mode-toggle') {
    document.getElementById('duration-group').style.display = btn.dataset.val === 'countdown' ? '' : 'none';
  }
}

function getActiveVal(groupId) {
  const btn = document.querySelector('#' + groupId + ' .toggle-btn.active');
  return btn ? btn.dataset.val : null;
}

// ── PLAYER INPUTS ──
const playerColors = ['p1','p2','p3','p4'];
function updatePlayerInputs() {
  const count = parseInt(getActiveVal('player-count-toggle'));
  const grid = document.getElementById('players-grid');
  grid.innerHTML = '';
  for (let i = 0; i < count; i++) {
    grid.innerHTML += `
      <div class="player-input-wrap">
        <div class="player-input-label ${playerColors[i]}">Player ${i+1}</div>
        <input type="text" class="player-name-input" placeholder="Enter name..." data-idx="${i}">
      </div>`;
  }
  grid.style.gridTemplateColumns = count <= 2 ? '1fr 1fr' : '1fr 1fr';
}

// ── MODAL ──
function openCreateModal() {
  document.getElementById('modal-create').classList.add('open');
  renderCreateTestSelector();
}
function openLoadModal() {
  document.getElementById('modal-load').classList.add('open');
  hideCreateTestForm();
  fetchAndRenderTests();
}
function closeModal(id) {
  document.getElementById(id).classList.remove('open');
}
document.querySelectorAll('.modal-overlay').forEach(m => {
  m.addEventListener('click', e => { if (e.target === m) m.classList.remove('open'); });
});

// ── FETCH AVAILABLE TESTS ──
async function fetchTests() {
  try {
    const res = await fetch('api.php', {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({action: 'list_tests'})
    });
    const data = await res.json();
    cachedTests = data.tests || [];
    return cachedTests;
  } catch(e) {
    cachedTests = [];
    return [];
  }
}

async function fetchAndRenderTests() {
  const container = document.getElementById('load-test-list-container');
  container.innerHTML = `<div class="test-list-loading"><div class="mini-spinner"></div>Loading tests...</div>`;
  const tests = await fetchTests();
  renderTestList(tests);
}

function renderTestList(tests) {
  const container = document.getElementById('load-test-list-container');

  if (tests.length === 0) {
    container.innerHTML = `
      <div class="test-list-empty">
        <div class="test-list-empty-icon">📭</div>
        <div class="test-list-empty-title">No Tests Available</div>
        <div class="test-list-empty-sub">
          No test files found on the server. Create your first test below!
        </div>
      </div>`;
    return;
  }

  // Segregate by tags
  const groups = {
    'GS': [],
    'CSAT': [],
    'General': []
  };

  tests.forEach((t, i) => {
    t._idx = i; // Keep original index for selection mapping
    let tag = (t.test_info && t.test_info.tag) ? t.test_info.tag : 'General';
    if (!groups[tag]) groups[tag] = [];
    groups[tag].push(t);
  });

  let html = '';
  const renderGroup = (title, arr) => {
    if (arr.length === 0) return;
    html += `<div class="test-category-title">${title} <span style="font-size:.8rem;color:var(--muted);font-weight:600;margin-left:4px;">(${arr.length})</span></div>`;
    html += `<div class="test-tile-grid">`;
    arr.forEach(t => {
      const idx = t._idx;
      const date = new Date(t.created_at * 1000);
      const dateStr = date.toLocaleDateString('en-IN', {day:'numeric',month:'short',year:'numeric'});
      const isSelected = selectedTest && selectedTest.name === t.name;
      
      const tagClass = (t.test_info && t.test_info.tag === 'GS') ? 'tag-gs' : 
                       ((t.test_info && t.test_info.tag === 'CSAT') ? 'tag-csat' : '');
      const tagLabel = (t.test_info && t.test_info.tag) ? t.test_info.tag : 'General';
      const tagBadge = `<span class="tile-badge ${tagClass}">${escHtml(tagLabel)}</span>`;
      
      let actionBtns = '';
      if (!t.has_pdf) {
        actionBtns += `<span class="tile-badge action pdf-add" onclick="event.stopPropagation();uploadPdfForTest('${escHtml(t.name)}')" title="Attach Question PDF">+ PDF</span>`;
      } else {
        actionBtns += `<span class="tile-badge action pdf-edit" onclick="event.stopPropagation();uploadPdfForTest('${escHtml(t.name)}')" title="Replace Question PDF">✎ PDF</span>`;
      }
      if (!t.has_solution_pdf) {
        actionBtns += `<span class="tile-badge action sol-add" onclick="event.stopPropagation();uploadSolutionPdfForTest('${escHtml(t.name)}')" title="Attach Solution PDF">+ SOL</span>`;
      } else {
        actionBtns += `<span class="tile-badge action sol-edit" onclick="event.stopPropagation();uploadSolutionPdfForTest('${escHtml(t.name)}')" title="Replace Solution PDF">✎ SOL</span>`;
      }
      if (t.has_pdf && !t.has_page_map) {
        actionBtns += `<span class="tile-badge action map-add" onclick="event.stopPropagation();openMapModal('${escHtml(t.name)}',${t.q_count})" title="Map Questions to Pages">📐 MAP</span>`;
      } else if (t.has_page_map) {
        actionBtns += `<span class="tile-badge action map-edit" onclick="event.stopPropagation();openMapModal('${escHtml(t.name)}',${t.q_count})" title="Edit Mapping">✏ MAP</span>`;
      }

      html += `
        <div class="test-tile ${isSelected?'selected':''}" onclick="selectTestFromList(${idx})" id="test-item-${idx}">
          <div class="test-tile-header">
            <div class="test-tile-icon">${t.has_pdf ? '📄' : '📝'}</div>
            <div class="test-tile-info">
              <div class="test-tile-name">${escHtml(t.name)}</div>
              <div class="test-tile-meta">${t.q_count} Qs · ${dateStr}</div>
            </div>
          </div>
          <div class="test-tile-badges">
            ${tagBadge}
            <div style="flex:1"></div>
            ${actionBtns}
          </div>
        </div>
      `;
    });
    html += `</div>`;
  };

  renderGroup('🌟 General Studies (GS)', groups['GS']);
  renderGroup('📊 CSAT', groups['CSAT']);
  renderGroup('📁 General / Uncategorized', groups['General']);

  container.innerHTML = html;
}

function selectTestFromList(idx) {
  const test = cachedTests[idx];
  if (!test) return;
  selectedTest = {name: test.name, q_count: test.q_count, test_info: test.test_info};
  // Update UI in Load modal
  document.querySelectorAll('.test-list-item').forEach(el => el.classList.remove('selected'));
  const item = document.getElementById('test-item-' + idx);
  if (item) item.classList.add('selected');
  // Show success & offer to create room
  showMsg('load-msg', '✓ Selected "' + test.name + '" — ' + test.q_count + ' questions. You can now create a room!', 'success');
  // After a short delay, close load modal and open create modal
  setTimeout(() => {
    hideMsg('load-msg');
    closeModal('modal-load');
    renderCreateTestSelector();
    openCreateModal();
  }, 800);
}

// ── CREATE TEST SELECTOR (inside Create Room modal) ──
function renderCreateTestSelector() {
  const display = document.getElementById('create-test-display');
  if (selectedTest) {
    display.innerHTML = `
      <div class="test-selector-selected">
        <div style="font-size:1.4rem;">📝</div>
        <div class="test-selector-info">
          <div class="test-selector-name">${escHtml(selectedTest.name)}</div>
          <div class="test-selector-meta">${selectedTest.q_count} questions</div>
        </div>
        <button class="test-selector-change" onclick="openLoadModal()">Change</button>
      </div>`;
  } else {
    display.innerHTML = `
      <div class="test-selector-empty">
        <div class="test-selector-empty-text">No test selected yet</div>
        <div class="test-selector-actions">
          <button class="test-selector-btn primary" onclick="closeModal('modal-create');openLoadModal()">📂 Browse Tests</button>
          <button class="test-selector-btn" onclick="closeModal('modal-create');openLoadModal();setTimeout(showCreateTestForm,400)">✨ Create New</button>
        </div>
      </div>`;
  }
}

// ── CREATE TEST FORM (inside Load modal) ──
function showCreateTestForm() {
  document.getElementById('load-create-form-wrap').style.display = '';
  document.getElementById('load-create-test-btn').style.display = 'none';
  const nameInput = document.getElementById('new-test-name');
  if (nameInput) nameInput.focus();
}
function hideCreateTestForm() {
  document.getElementById('load-create-form-wrap').style.display = 'none';
  document.getElementById('load-create-test-btn').style.display = '';
}

async function saveNewTest() {
  const name = document.getElementById('new-test-name').value.trim();
  const raw  = document.getElementById('new-test-json').value.trim();
  const pdfInput = document.getElementById('new-test-pdf');
  const tagStr = document.getElementById('new-test-tag').value;

  if (!name) { showMsg('new-test-error', 'Please enter a test name.', 'error'); return; }
  const v = validateJSON(raw);
  if (!v.ok) { showMsg('new-test-error', 'Invalid JSON: ' + v.error, 'error'); return; }
  hideMsg('new-test-error');

  if (!v.data.test_info) v.data.test_info = {};
  v.data.test_info.tag = tagStr;

  document.getElementById('save-test-spinner').classList.add('show');
  document.getElementById('save-test-btn-text').textContent = 'Saving...';

  try {
    const res = await fetch('api.php', {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({action:'save_test', test_name: name, json_data: v.data})
    });
    const data = await res.json();
    if (data.success) {
      // Upload PDF if selected
      if (pdfInput.files.length > 0) {
        document.getElementById('save-test-btn-text').textContent = 'Uploading PDF...';
        const fd = new FormData();
        fd.append('action', 'upload_pdf');
        fd.append('test_name', name);
        fd.append('pdf_file', pdfInput.files[0]);
        try {
          await fetch('api.php', { method: 'POST', body: fd });
        } catch(e) { /* PDF upload failed, but test saved */ }
      }
      // Upload Solution PDF if selected
      const solPdfInput = document.getElementById('new-test-sol-pdf');
      if (solPdfInput && solPdfInput.files.length > 0) {
        document.getElementById('save-test-btn-text').textContent = 'Uploading Solution PDF...';
        const fd2 = new FormData();
        fd2.append('action', 'upload_solution_pdf');
        fd2.append('test_name', name);
        fd2.append('pdf_file', solPdfInput.files[0]);
        try {
          await fetch('api.php', { method: 'POST', body: fd2 });
        } catch(e) { /* Solution PDF upload failed */ }
      }
      // Auto-select the newly created test
      selectedTest = {name: data.name, q_count: data.q_count, test_info: v.data.test_info || {}};
      // Reset form
      document.getElementById('new-test-name').value = '';
      document.getElementById('new-test-json').value = '';
      pdfInput.value = '';
      document.getElementById('pdf-upload-label').textContent = 'Click to select PDF file';
      const solPdfClear = document.getElementById('new-test-sol-pdf');
      if (solPdfClear) { solPdfClear.value = ''; }
      document.getElementById('sol-pdf-upload-label').textContent = 'Click to select solution PDF';
      hideCreateTestForm();
      // Refresh test list
      await fetchAndRenderTests();
      showMsg('load-msg', '✓ Test "' + name + '" saved! ' + data.q_count + ' questions.', 'success');
    } else {
      showMsg('new-test-error', '❌ ' + (data.error || 'Failed to save test'), 'error');
    }
  } catch(e) {
    showMsg('new-test-error', '❌ Network error. Please try again.', 'error');
  } finally {
    document.getElementById('save-test-spinner').classList.remove('show');
    document.getElementById('save-test-btn-text').textContent = 'Save Test';
  }
}

// PDF selected handler
function onPdfSelected(input) {
  const label = document.getElementById('pdf-upload-label');
  if (input.files.length > 0) {
    label.textContent = '✓ ' + input.files[0].name;
    label.style.color = 'var(--ok)';
  } else {
    label.textContent = 'Click to select PDF file';
    label.style.color = '';
  }
}

function onSolPdfSelected(input) {
  const label = document.getElementById('sol-pdf-upload-label');
  if (input.files.length > 0) {
    label.textContent = '✓ ' + input.files[0].name;
    label.style.color = 'var(--accent3)';
  } else {
    label.textContent = 'Click to select solution PDF';
    label.style.color = '';
  }
}

// ── Upload with progress bar ──
function uploadFileWithProgress(action, testName, file, label) {
  return new Promise((resolve, reject) => {
    const msgEl = document.getElementById('load-msg');
    // Build progress bar
    const sizeMB = (file.size / (1024*1024)).toFixed(1);
    msgEl.className = 'msg show';
    msgEl.style.display = 'block';
    msgEl.innerHTML = `<div class="upload-progress">
      <div class="upload-progress-label">
        <span>${label}: ${file.name}</span>
        <span id="upload-pct">0%</span>
      </div>
      <div class="upload-progress-track">
        <div class="upload-progress-fill" id="upload-fill" style="width:0%"></div>
      </div>
    </div>`;

    const fd = new FormData();
    fd.append('action', action);
    fd.append('test_name', testName);
    fd.append('pdf_file', file);

    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'api.php');

    xhr.upload.onprogress = (e) => {
      if (e.lengthComputable) {
        const pct = Math.round((e.loaded / e.total) * 100);
        const pctEl = document.getElementById('upload-pct');
        const fillEl = document.getElementById('upload-fill');
        if (pctEl) pctEl.textContent = pct + '%';
        if (fillEl) fillEl.style.width = pct + '%';
      }
    };

    xhr.onload = () => {
      try {
        const data = JSON.parse(xhr.responseText);
        if (data.success) {
          resolve(data);
        } else {
          reject(data.error || 'Upload failed');
        }
      } catch(e) { reject('Invalid response'); }
    };
    xhr.onerror = () => reject('Network error');
    xhr.send(fd);
  });
}

// Upload PDF for an existing test
async function uploadPdfForTest(testName) {
  const input = document.createElement('input');
  input.type = 'file';
  input.accept = '.pdf';
  input.onchange = async () => {
    if (!input.files.length) return;
    try {
      await uploadFileWithProgress('upload_pdf', testName, input.files[0], '📄 Uploading PDF');
      showMsg('load-msg', '✓ PDF attached to "' + testName + '"', 'success');
      await fetchAndRenderTests();
    } catch(e) {
      showMsg('load-msg', '❌ ' + (e || 'Failed to upload PDF'), 'error');
    }
  };
  input.click();
}

// Upload Solution PDF for an existing test
async function uploadSolutionPdfForTest(testName) {
  const input = document.createElement('input');
  input.type = 'file';
  input.accept = '.pdf';
  input.onchange = async () => {
    if (!input.files.length) return;
    try {
      await uploadFileWithProgress('upload_solution_pdf', testName, input.files[0], '📖 Uploading Solution');
      showMsg('load-msg', '✓ Solution PDF attached to "' + testName + '"', 'success');
      await fetchAndRenderTests();
    } catch(e) {
      showMsg('load-msg', '❌ ' + (e || 'Failed to upload solution PDF'), 'error');
    }
  };
  input.click();
}

// ── PAGE MAPPING TOOL ──
let mapState = { testName: '', qCount: 0, currentPage: 1, totalPages: 20, hasExisting: false };

async function openMapModal(testName, qCount) {
  mapState.testName = testName;
  mapState.qCount = qCount;
  mapState.currentPage = 1;
  mapState.hasExisting = false;

  // Load PDF in iframe
  const safeName = testName.replace(/[^a-zA-Z0-9_\-]/g, '_');
  const pdfUrl = 'wp-content/omr-data/' + safeName + '.pdf?t=' + Date.now();
  document.getElementById('map-pdf-iframe').src = pdfUrl;
  document.getElementById('map-modal-sub').textContent = `"${testName}" — ${qCount} questions. Set starting question per page.`;
  document.getElementById('map-status').textContent = '';

  // Fetch existing map data from test JSON
  let existingMap = null;
  try {
    const res = await fetch('api.php', {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({ action: 'list_tests' })
    });
    const data = await res.json();
    const test = (data.tests || []).find(t => t.name === testName);
    if (test && test.has_page_map) {
      // Need to fetch full test data to get the page_map
      const res2 = await fetch('api.php', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({ action: 'check_test', test_name: testName })
      });
      const d2 = await res2.json();
      if (d2.page_map) existingMap = d2.page_map;
    }
  } catch(e) { /* ignore */ }

  const inputSide = document.getElementById('map-inputs');
  let html = '';

  if (existingMap && Object.keys(existingMap).length > 0) {
    // Load existing mapping
    mapState.hasExisting = true;
    const pages = Object.keys(existingMap).map(Number).sort((a,b) => a - b);
    mapState.totalPages = Math.max(...pages);
    document.getElementById('map-total-pages').textContent = mapState.totalPages;
    document.getElementById('map-current-page').textContent = '1';

    html += `<div style="background:rgba(255,225,86,.08);border:1px solid rgba(255,225,86,.3);border-radius:8px;padding:.5rem .6rem;margin-bottom:.5rem;font-size:.72rem;color:var(--accent3);font-family:'JetBrains Mono',monospace;">
      ⚠️ Existing mapping loaded. <button onclick="enableMapOverride()" style="background:var(--surface);border:1px solid var(--accent3);color:var(--accent3);border-radius:4px;padding:2px 8px;cursor:pointer;font-size:.65rem;font-family:'JetBrains Mono',monospace;">Override</button>
    </div>`;

    for (let p = 1; p <= mapState.totalPages; p++) {
      const val = existingMap[String(p)] !== undefined ? existingMap[String(p)] : '';
      html += `<div class="map-page-row ${p===1?'active':''}" onclick="mapGoToPage(${p})">
        <span class="pg-label">Page ${p} starts at Q:</span>
        <input type="number" id="map-start-${p}" min="0" max="${qCount}" value="${val}" disabled onclick="event.stopPropagation()">
      </div>`;
    }
  } else {
    // Generate fresh defaults
    const estPages = Math.max(5, Math.ceil(qCount / 4));
    mapState.totalPages = estPages;
    document.getElementById('map-total-pages').textContent = estPages;
    document.getElementById('map-current-page').textContent = '1';

    const qPerPage = Math.ceil(qCount / estPages);
    let nextQ = 1;
    for (let p = 1; p <= estPages; p++) {
      const from = Math.min(nextQ, qCount);
      const to = Math.min(nextQ + qPerPage - 1, qCount);
      nextQ = to + 1;
      html += `<div class="map-page-row ${p===1?'active':''}" onclick="mapGoToPage(${p})">
        <span class="pg-label">Page ${p} starts at Q:</span>
        <input type="number" id="map-start-${p}" min="0" max="${qCount}" value="${from}" onclick="event.stopPropagation()">
      </div>`;
      if (nextQ > qCount && p < estPages) {
        for (let ep = p+1; ep <= estPages; ep++) {
          html += `<div class="map-page-row" onclick="mapGoToPage(${ep})">
            <span class="pg-label">Page ${ep} starts at Q:</span>
            <input type="number" id="map-start-${ep}" min="0" max="${qCount}" value="" placeholder="-" onclick="event.stopPropagation()">
          </div>`;
        }
        break;
      }
    }
  }

  // Add row button
  html += `<button style="width:100%;padding:.4rem;border:1.5px dashed var(--border);border-radius:8px;background:transparent;color:var(--muted);font-size:.72rem;cursor:pointer;font-family:'JetBrains Mono',monospace;" onclick="addMapPageRow()">+ Add Page</button>`;
  inputSide.innerHTML = html;

  document.getElementById('modal-map').classList.add('open');
}

function enableMapOverride() {
  mapState.hasExisting = false;
  document.querySelectorAll('#map-inputs input[type=number]').forEach(inp => inp.disabled = false);
  document.getElementById('map-status').textContent = '✏️ Editing enabled — make changes and save.';
  document.getElementById('map-status').style.color = 'var(--accent3)';
}

function addMapPageRow() {
  mapState.totalPages++;
  const p = mapState.totalPages;
  document.getElementById('map-total-pages').textContent = p;
  const btn = document.getElementById('map-inputs').querySelector('button');
  const row = document.createElement('div');
  row.className = 'map-page-row';
  row.onclick = () => mapGoToPage(p);
  row.innerHTML = `<span class="pg-label">Page ${p} starts at Q:</span>
    <input type="number" id="map-start-${p}" min="0" max="${mapState.qCount}" value="" placeholder="-" onclick="event.stopPropagation()">`;
  btn.parentNode.insertBefore(row, btn);
}

function mapGoToPage(p) {
  mapState.currentPage = p;
  document.getElementById('map-current-page').textContent = p;
  // Navigate PDF iframe to page
  const iframe = document.getElementById('map-pdf-iframe');
  const baseUrl = iframe.src.split('#')[0];
  iframe.src = baseUrl + '#page=' + p;
  // Highlight active row
  document.querySelectorAll('.map-page-row').forEach((r, i) => r.classList.toggle('active', i === p-1));
}

function mapPageNav(delta) {
  const next = mapState.currentPage + delta;
  if (next >= 1 && next <= mapState.totalPages) {
    mapGoToPage(next);
  }
}

async function savePageMap() {
  if (mapState.hasExisting) {
    document.getElementById('map-status').textContent = '⚠️ Click "Override" first to enable editing.';
    document.getElementById('map-status').style.color = 'var(--accent3)';
    return;
  }

  const pageMap = {};
  for (let p = 1; p <= mapState.totalPages; p++) {
    const startEl = document.getElementById('map-start-' + p);
    if (!startEl) continue;
    const startQ = parseInt(startEl.value);
    if (!isNaN(startQ) && startQ >= 0) {
      pageMap[String(p)] = startQ;
    }
  }

  if (Object.keys(pageMap).length === 0) {
    document.getElementById('map-status').textContent = '❌ No valid page mappings entered.';
    document.getElementById('map-status').style.color = 'var(--accent2)';
    return;
  }

  document.getElementById('map-save-spinner').classList.add('show');
  document.getElementById('map-save-text').textContent = 'Saving...';

  try {
    const res = await fetch('api.php', {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({ action: 'save_page_map', test_name: mapState.testName, page_map: pageMap })
    });
    const data = await res.json();
    if (data.success) {
      document.getElementById('map-status').textContent = '✓ Mapping saved! ' + data.pages_mapped + ' pages mapped.';
      document.getElementById('map-status').style.color = 'var(--ok)';
      await fetchAndRenderTests();
      setTimeout(() => closeModal('modal-map'), 1200);
    } else {
      document.getElementById('map-status').textContent = '❌ ' + (data.error || 'Failed to save');
      document.getElementById('map-status').style.color = 'var(--accent2)';
    }
  } catch(e) {
    document.getElementById('map-status').textContent = '❌ Network error.';
    document.getElementById('map-status').style.color = 'var(--accent2)';
  } finally {
    document.getElementById('map-save-spinner').classList.remove('show');
    document.getElementById('map-save-text').textContent = '💾 Save Mapping';
  }
}

// ── JSON VALIDATION ──
function validateJSON(raw) {
  try {
    const d = JSON.parse(raw);
    if (!d.responses || typeof d.responses !== 'object') throw new Error('Missing "responses" key');
    const keys = Object.keys(d.responses);
    if (keys.length === 0) throw new Error('responses object is empty');
    return { ok: true, data: d, count: keys.length };
  } catch(e) {
    return { ok: false, error: e.message };
  }
}

function showMsg(id, text, type) {
  const el = document.getElementById(id);
  el.textContent = text;
  el.className = 'msg ' + type + ' show';
}
function hideMsg(id) {
  const el = document.getElementById(id);
  if (el) el.classList.remove('show');
}

function escHtml(s) {
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// ── CREATE ROOM ──
async function createRoom() {
  if (!selectedTest) {
    showMsg('create-msg', 'Please select a test first.', 'error');
    return;
  }

  const testName    = selectedTest.name;
  const timerMode   = getActiveVal('timer-mode-toggle');
  const duration    = parseInt(document.getElementById('c-duration')?.value || 120);
  const playerCount = parseInt(getActiveVal('player-count-toggle'));

  // Collect player names
  const playerNames = [];
  document.querySelectorAll('.player-name-input').forEach(inp => {
    playerNames.push(inp.value.trim() || 'Player ' + (playerNames.length + 1));
  });

  // Show spinner
  document.getElementById('create-spinner').classList.add('show');
  document.getElementById('btn-create-text').textContent = 'Creating...';
  document.getElementById('btn-create').disabled = true;

  try {
    const res = await fetch('api.php', {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({
        action: 'create_room',
        test_name: testName,
        timer_mode: timerMode,
        duration_minutes: duration,
        player_count: playerCount,
        player_names: playerNames
      })
    });
    const data = await res.json();
    if (data.success) {

      closeModal('modal-create');
      // Redirect host to room
      window.location.href = 'room.php?player_id=' + data.player_codes[0] + '&room_id=' + data.room_id + '&host=1';
    } else {
      showMsg('create-msg', '❌ ' + (data.error || 'Failed to create room'), 'error');
    }
  } catch(e) {
    showMsg('create-msg', '❌ Network error. Please try again.', 'error');
  } finally {
    document.getElementById('create-spinner').classList.remove('show');
    document.getElementById('btn-create-text').textContent = 'Create Room & Get Code';
    document.getElementById('btn-create').disabled = false;
  }
}

// ── JOIN BY CODE ──
async function joinByCode() {
  const code = document.getElementById('join-code-input').value.trim().toUpperCase();
  if (!code || code.length < 3) {
    showMsg('join-error','Please enter a valid room code.','error'); return;
  }
  hideMsg('join-error');

  let sessionId = sessionStorage.getItem('omr_tab_id');
  if (!sessionId) {
    sessionId = Math.random().toString(36).substring(2, 15);
    sessionStorage.setItem('omr_tab_id', sessionId);
  }

  try {
    const res = await fetch('api.php', {
      method:'POST',
      headers:{'Content-Type':'application/json'},
      body: JSON.stringify({action:'validate_code', code, session_id: sessionId})
    });
    const data = await res.json();
    if (data.valid) {
      window.location.href = 'room.php?player_id=' + data.player_id + '&room_id=' + data.room_id;
    } else {
      showMsg('join-error', '❌ ' + (data.error || 'Invalid code. Check and try again.'), 'error');
    }
  } catch(e) {
    showMsg('join-error','❌ Network error.','error');
  }
}
document.getElementById('join-code-input').addEventListener('keydown', e => {
  if (e.key === 'Enter') joinByCode();
});



// Keyboard shortcut: ESC closes modals
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') document.querySelectorAll('.modal-overlay.open').forEach(m=>m.classList.remove('open'));
});

// ── REJOIN ACTIVE EXAMS ──
async function fetchActiveTests() {
  try {
    const recentStr = localStorage.getItem('omr_recent_rooms');
    if (!recentStr) return;
    const recent = JSON.parse(recentStr);
    if (!recent || !recent.length) return;
    
    // Sort recent so latest is first
    recent.sort((a,b) => (b.timestamp || 0) - (a.timestamp || 0));
    const codes = recent.map(r => r.code);
    
    const res = await fetch('api.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({ action: 'check_recent_rooms', codes })
    });
    const data = await res.json();
    
    if (data.success && data.rooms && data.rooms.length > 0) {
      const sec = document.getElementById('active-tests-section');
      const list = document.getElementById('active-tests-list');
      sec.style.display = 'block';
      let html = '';
      data.rooms.forEach(r => {
        let meta = `Playing as ${escHtml(r.player_name)} • Code: ${r.code}`;
        let statusColor = r.status === 'active' ? 'var(--ok)' : (r.status === 'finished' ? 'var(--accent)' : 'var(--warn)');
        let badgeText = r.status === 'finished' ? 'Reattempt Test' : 'Resume Test';
        
        let onClickAction = `window.location.href='room.php?player_id=${r.code}&room_id=${r.room_id}'`;
        
        if (r.status === 'finished' && r.can_reattempt) {
            onClickAction = `startReattempt('${r.room_id}', '${r.code}')`;
        }

        let actionsHtml = `<div class="recent-badge" style="color:${statusColor};border-color:${statusColor};background:rgba(255,255,255,0.05);">${badgeText}</div>`;
        if (r.status === 'finished') {
           actionsHtml += `<button class="recent-badge" onclick="event.stopPropagation(); window.location.href='room.php?player_id=${r.code}&room_id=${r.room_id}'" style="background:var(--surface2); color:var(--text); border-color:var(--border); cursor:pointer; margin-left:6px; transition:var(--tr);" onmouseover="this.style.borderColor='var(--accent)'" onmouseout="this.style.borderColor='var(--border)'" title="View Result">📊 View Result</button>`;
        }

        html += `<div class="recent-item" onclick="${onClickAction}" style="border-color:${statusColor}; background:rgba(255,255,255,0.03);">
          <div class="recent-icon">${r.status === 'finished' ? '♻️' : '⏳'}</div>
          <div class="recent-info" style="flex:1;">
            <div class="recent-name" style="color:var(--text);">${escHtml(r.test_name)}</div>
            <div class="recent-meta">${meta}</div>
          </div>
          <div style="display:flex; align-items:center;">
             ${actionsHtml}
          </div>
        </div>`;
      });
      list.innerHTML = html;
    }
  } catch(e) { console.warn("Failed to fetch active tests", e); }
}

fetchActiveTests();

async function startReattempt(roomId, code) {
  if (!confirm("Are you sure you want to reattempt? All your existing answers will be permanently locked.")) return;
  
  // Show global loading state if we want, or rely on alert for errors
  document.getElementById('loading-overlay').classList.add('show');
  document.querySelector('.loading-text').textContent = "Starting Reattempt...";
  document.querySelector('.loading-sub').textContent = "Rebuilding test session";
  
  try {
    const res = await fetch('api.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({ action: 'start_reattempt', room_id: roomId, player_id: code })
    });
    const data = await res.json();
    if (data.success) {
      window.location.href = `room.php?player_id=${code}&room_id=${roomId}`;
    } else {
      document.getElementById('loading-overlay').classList.remove('show');
      alert("Failed to start reattempt: " + (data.error || "Unknown error"));
    }
  } catch(e) {
    document.getElementById('loading-overlay').classList.remove('show');
    alert("Network error. Please try again.");
  }
}

</script>
</body>
</html>
