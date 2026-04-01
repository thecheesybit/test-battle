<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Firestore Migration — MiniShiksha</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
<style>
:root{--bg:#07080f;--surface:#0f1018;--surface2:#171825;--border:#252638;--p0:#5b7fff;--p0-dim:rgba(91,127,255,.12);--p0-glow:rgba(91,127,255,.3);--text:#eaeaf5;--muted:#5a5a7a;--ok:#4fffb0;--warn:#ffa502;--danger:#ff4757;--radius:12px;--tr:.18s cubic-bezier(.4,0,.2,1);}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
body{font-family:'Syne',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;padding:2rem 1rem;}
h1{font-size:1.6rem;font-weight:800;letter-spacing:-1px;margin-bottom:.25rem;}
.sub{color:var(--muted);font-size:.85rem;margin-bottom:2rem;}
.card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:1.5rem;margin-bottom:1.5rem;max-width:760px;}
.card h2{font-size:1rem;font-weight:700;margin-bottom:.75rem;display:flex;align-items:center;gap:.5rem;}
.btn{padding:.65rem 1.25rem;border-radius:8px;border:none;font-family:'Syne',sans-serif;font-size:.85rem;font-weight:700;cursor:pointer;transition:var(--tr);}
.btn-primary{background:var(--p0);color:#fff;}
.btn-primary:hover{background:#7090ff;}
.btn-primary:disabled{opacity:.4;pointer-events:none;}
.btn-danger{background:var(--danger);color:#fff;}
.btn-danger:hover{opacity:.85;}
.progress-wrap{background:var(--surface2);border-radius:100px;height:8px;overflow:hidden;margin:.75rem 0;}
.progress-bar{height:100%;background:var(--p0);border-radius:100px;transition:width .3s ease;width:0;}
.log{font-family:'JetBrains Mono',monospace;font-size:.74rem;color:var(--muted);background:var(--surface2);border-radius:8px;padding:.75rem;max-height:260px;overflow-y:auto;line-height:1.7;}
.log .ok{color:var(--ok);}
.log .skip{color:var(--warn);}
.log .err{color:var(--danger);}
.stat-row{display:grid;grid-template-columns:repeat(3,1fr);gap:.75rem;margin-bottom:1rem;}
.stat{background:var(--surface2);border-radius:10px;padding:.85rem;text-align:center;}
.stat-val{font-size:1.6rem;font-weight:800;font-family:'JetBrains Mono',monospace;}
.stat-lbl{font-size:.72rem;color:var(--muted);margin-top:.25rem;}
.status-badge{display:inline-flex;align-items:center;gap:.4rem;font-size:.75rem;font-family:'JetBrains Mono',monospace;padding:.3rem .65rem;border-radius:6px;font-weight:600;}
.status-badge.ok{background:rgba(79,255,176,.1);border:1px solid rgba(79,255,176,.3);color:var(--ok);}
.status-badge.warn{background:rgba(255,165,2,.1);border:1px solid rgba(255,165,2,.3);color:var(--warn);}
.user-bar{display:flex;align-items:center;gap:.75rem;background:var(--surface2);border:1px solid var(--border);border-radius:10px;padding:.6rem 1rem;margin-bottom:1.5rem;max-width:760px;}
.user-bar img{width:32px;height:32px;border-radius:50%;object-fit:cover;}
.user-bar .name{font-weight:700;font-size:.88rem;}
.user-bar .email{font-size:.75rem;color:var(--muted);}
</style>
</head>
<body>
<!-- Firebase SDKs -->
<script src="https://www.gstatic.com/firebasejs/10.12.0/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/10.12.0/firebase-auth-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/10.12.0/firebase-firestore-compat.js"></script>
<script src="/firebase-config.js"></script>
<script>
// Fast auth guard
(function(){var u=localStorage.getItem('omr_user');if(!u||u==='null'){window.location.replace('/login.php?return='+encodeURIComponent(location.href));}})();
</script>

<!-- Access Denied state -->
<div id="access-denied" style="display:none;max-width:520px;margin:6rem auto;text-align:center;">
  <div style="font-size:3rem;margin-bottom:1rem;">🚫</div>
  <h2 style="font-size:1.3rem;font-weight:800;margin-bottom:.5rem;">Access Restricted</h2>
  <p style="color:var(--muted);font-size:.88rem;line-height:1.6;margin-bottom:1.5rem;">The Migration Tool is only available to the super admin account.<br>Your account is not authorised to access this page.</p>
  <a href="/test.php" style="display:inline-block;padding:.65rem 1.5rem;background:var(--p0);color:#fff;border-radius:8px;font-weight:700;font-size:.85rem;text-decoration:none;">← Back to Dashboard</a>
</div>

<div id="migrate-content" style="display:none;">
<div id="user-bar" class="user-bar" style="margin-bottom:1.5rem;"></div>

<h1>🔄 Firestore Migration Tool</h1>
<p class="sub">Migrates existing JSON test data to Firestore. Skips duplicates automatically. Run once — safe to re-run.</p>

<div class="card">
  <h2>📊 Summary</h2>
  <div class="stat-row">
    <div class="stat"><div class="stat-val" id="stat-total">—</div><div class="stat-lbl">Tests Found</div></div>
    <div class="stat"><div class="stat-val ok" id="stat-migrated" style="color:var(--ok)">—</div><div class="stat-lbl">Migrated</div></div>
    <div class="stat"><div class="stat-val" id="stat-skipped" style="color:var(--warn)">—</div><div class="stat-lbl">Skipped (already exist)</div></div>
  </div>
  <div id="status-badge" class="status-badge warn">⏳ Not started</div>
</div>

<div class="card">
  <h2>▶ Run Migration</h2>
  <p style="font-size:.83rem;color:var(--muted);margin-bottom:1rem;line-height:1.6;">
    This will read all test JSON files from the server and write them to Firestore.<br>
    PDFs remain on the server — only metadata, answer keys, questions and options are migrated.<br>
    <strong style="color:var(--text);">Existing Firestore entries are <em>not</em> overwritten.</strong>
  </p>
  <button class="btn btn-primary" id="btn-migrate" onclick="startMigration()">🚀 Start Migration</button>
  <button class="btn btn-danger" id="btn-force" onclick="startMigration(true)" style="margin-left:.5rem;display:none;">⚡ Force Re-migrate All</button>
  <div class="progress-wrap" id="progress-wrap" style="display:none;">
    <div class="progress-bar" id="progress-bar"></div>
  </div>
  <div id="progress-label" style="font-size:.78rem;color:var(--muted);margin-bottom:.5rem;display:none;font-family:'JetBrains Mono',monospace;"></div>
  <div class="log" id="log" style="display:none;"></div>
</div>

<div style="max-width:760px;margin-top:.5rem;">
  <a href="/test.php" style="color:var(--muted);font-size:.8rem;text-decoration:underline;text-underline-offset:3px;">← Back to Dashboard</a>
</div>

<script>
let migrated = 0, skipped = 0, errors = 0;
let migrationRunning = false;

const SUPER_ADMIN_EMAIL = 'ak818ace@gmail.com';

// ── Auth ─────────────────────────────────────────────────────
auth.onAuthStateChanged(user => {
  if (!user) { window.location.replace('/login.php?return=' + encodeURIComponent(location.href)); return; }

  if (user.email !== SUPER_ADMIN_EMAIL) {
    document.getElementById('access-denied').style.display = '';
    return;
  }

  document.getElementById('migrate-content').style.display = '';
  const bar = document.getElementById('user-bar');
  bar.innerHTML = `
    <img src="${user.photoURL || ''}" onerror="this.style.display='none'">
    <div><div class="name">${user.displayName || 'User'}</div><div class="email">${user.email || ''}</div></div>
    <span style="margin-left:auto;font-size:.75rem;padding:.25rem .6rem;background:rgba(79,255,176,.1);border:1px solid rgba(79,255,176,.3);border-radius:6px;color:var(--ok);">Super Admin</span>`;
});

// ── Log helper ────────────────────────────────────────────────
function log(msg, type = '') {
  const el = document.getElementById('log');
  const div = document.createElement('div');
  div.className = type;
  div.textContent = msg;
  el.appendChild(div);
  el.scrollTop = el.scrollHeight;
}

// ── Migration ─────────────────────────────────────────────────
async function startMigration(force = false) {
  if (migrationRunning) return;
  migrationRunning = true;
  migrated = 0; skipped = 0; errors = 0;

  const btn    = document.getElementById('btn-migrate');
  const logEl  = document.getElementById('log');
  const pwrap  = document.getElementById('progress-wrap');
  const pbar   = document.getElementById('progress-bar');
  const plabel = document.getElementById('progress-label');
  const badge  = document.getElementById('status-badge');

  btn.disabled = true;
  btn.textContent = '⏳ Running…';
  logEl.innerHTML = '';
  logEl.style.display = '';
  pwrap.style.display = '';
  plabel.style.display = '';
  badge.className = 'status-badge warn';
  badge.textContent = '⏳ Migrating…';

  try {
    // 1. Fetch test list from PHP
    log('Fetching test list from server…');
    const res  = await fetch('/migrate.php?api=list_tests');
    const data = await res.json();
    const tests = data.tests || [];
    document.getElementById('stat-total').textContent = tests.length;
    log(`Found ${tests.length} test(s) on server.`);

    if (tests.length === 0) {
      log('No tests found to migrate.', 'warn');
      badge.className = 'status-badge ok';
      badge.textContent = '✓ Nothing to migrate';
      migrationRunning = false;
      return;
    }

    // 2. Migrate each test
    for (let i = 0; i < tests.length; i++) {
      const test = tests[i];
      pbar.style.width = (((i + 1) / tests.length) * 100).toFixed(1) + '%';
      plabel.textContent = `[${i+1}/${tests.length}] ${test.name}`;

      try {
        const ref = db.collection('tests').doc(test.name);

        if (!force) {
          const snap = await ref.get();
          if (snap.exists) {
            log(`  ↷  SKIP  ${test.name}  (already in Firestore)`, 'skip');
            skipped++;
            updateStats();
            continue;
          }
        }

        await ref.set({
          name:             test.name,
          title:            test.title,
          subject:          test.subject,
          tag:              test.tag,
          answer_key:       test.answer_key,
          question_texts:   test.question_texts,
          options:          test.options,
          pdf_url:          test.pdf_url     || null,
          solution_pdf_url: test.has_solution ? test.pdf_url : null,
          page_map:         test.page_map    || {},
          question_count:   test.question_count,
          migrated_at:      firebase.firestore.FieldValue.serverTimestamp(),
        }, { merge: force });

        log(`  ✓  OK    ${test.name}  (${test.question_count} questions)`, 'ok');
        migrated++;
        updateStats();

      } catch (e) {
        log(`  ✗  ERR   ${test.name}: ${e.message}`, 'err');
        errors++;
        updateStats();
      }
    }

    // Done
    badge.className = 'status-badge ok';
    badge.textContent = `✓ Complete — ${migrated} migrated, ${skipped} skipped, ${errors} errors`;
    log('');
    log(`Migration complete. ${migrated} migrated · ${skipped} skipped · ${errors} errors.`, errors > 0 ? 'err' : 'ok');
    document.getElementById('btn-force').style.display = '';

  } catch (e) {
    log('Fatal error: ' + e.message, 'err');
    badge.className = 'status-badge warn';
    badge.textContent = '✗ Failed';
  }

  btn.disabled = false;
  btn.textContent = '🔄 Run Again';
  migrationRunning = false;
}

function updateStats() {
  document.getElementById('stat-migrated').textContent = migrated;
  document.getElementById('stat-skipped').textContent  = skipped;
}
</script>
</div><!-- /migrate-content -->
</body>
</html>
