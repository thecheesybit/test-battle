<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sign In — MiniShiksha OMR</title>
<link rel="icon" href="https://minishiksha.in/wp-content/uploads/2025/06/icons8-class-pulsar-gradient-16.png">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
<style>
:root{
  --bg:#07080f;--surface:#0f1018;--surface2:#171825;--border:#252638;
  --p0:#5b7fff;--p0-dim:rgba(91,127,255,.12);--p0-glow:rgba(91,127,255,.3);
  --text:#eaeaf5;--muted:#5a5a7a;--ok:#4fffb0;--danger:#ff4757;
  --radius:14px;--tr:.18s cubic-bezier(.4,0,.2,1);
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
html,body{height:100%;background:var(--bg);color:var(--text);font-family:'Syne',sans-serif;display:flex;align-items:center;justify-content:center;overflow:hidden;}

/* animated gradient background */
.bg-gradient{position:fixed;inset:0;z-index:0;
  background:radial-gradient(ellipse 80% 60% at 50% -10%, rgba(91,127,255,.18) 0%, transparent 65%),
             radial-gradient(ellipse 60% 50% at 80% 100%, rgba(79,255,176,.10) 0%, transparent 60%);
  animation:bg-pulse 8s ease-in-out infinite alternate;}
@keyframes bg-pulse{0%{opacity:.7}100%{opacity:1}}

.card{
  position:relative;z-index:1;
  background:var(--surface);border:1.5px solid var(--border);border-radius:24px;
  padding:3rem 2.5rem;width:100%;max-width:420px;margin:1rem;
  box-shadow:0 24px 80px rgba(0,0,0,.5);
  animation:card-in .5s cubic-bezier(.16,1,.3,1);
}
@keyframes card-in{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}

.logo{text-align:center;margin-bottom:2rem;}
.logo-icon{font-size:2.8rem;display:block;margin-bottom:.5rem;animation:logo-bob 3s ease-in-out infinite;}
@keyframes logo-bob{0%,100%{transform:translateY(0)}50%{transform:translateY(-6px)}}
.logo-brand{font-size:1.4rem;font-weight:800;letter-spacing:-1px;}
.logo-sub{font-size:.8rem;color:var(--muted);margin-top:.25rem;font-family:'JetBrains Mono',monospace;}

.card-title{font-size:1.6rem;font-weight:800;letter-spacing:-1px;text-align:center;margin-bottom:.5rem;}
.card-sub{text-align:center;color:var(--muted);font-size:.85rem;line-height:1.6;margin-bottom:2rem;}

.btn-google{
  display:flex;align-items:center;justify-content:center;gap:.75rem;
  width:100%;padding:1rem 1.5rem;border-radius:12px;border:1.5px solid var(--border);
  background:var(--surface2);color:var(--text);font-family:'Syne',sans-serif;
  font-size:.95rem;font-weight:700;cursor:pointer;transition:var(--tr);
  position:relative;overflow:hidden;
}
.btn-google::before{
  content:'';position:absolute;inset:0;
  background:linear-gradient(135deg, var(--p0-dim), transparent);
  opacity:0;transition:var(--tr);
}
.btn-google:hover{border-color:var(--p0);color:var(--p0);}
.btn-google:hover::before{opacity:1;}
.btn-google:active{transform:scale(.98);}
.btn-google.loading{pointer-events:none;opacity:.6;}
.btn-google svg{flex-shrink:0;}

.divider{display:flex;align-items:center;gap:1rem;margin:1.5rem 0;color:var(--muted);font-size:.75rem;}
.divider::before,.divider::after{content:'';flex:1;height:1px;background:var(--border);}

.features{display:flex;flex-direction:column;gap:.6rem;}
.feature{display:flex;align-items:center;gap:.6rem;font-size:.82rem;color:var(--muted);}
.feature-dot{width:6px;height:6px;border-radius:50%;background:var(--p0);flex-shrink:0;}

.error-msg{
  background:rgba(255,71,87,.08);border:1px solid rgba(255,71,87,.3);border-radius:10px;
  padding:.75rem 1rem;font-size:.82rem;color:var(--danger);margin-top:1rem;
  display:none;text-align:center;
}
.error-msg.show{display:block;}

.footer{text-align:center;margin-top:1.5rem;font-size:.72rem;color:var(--muted);line-height:1.5;}
.footer a{color:var(--muted);text-decoration:underline;text-underline-offset:3px;}
</style>
</head>
<body>
<div class="bg-gradient"></div>
<div class="card">
  <div class="logo">
    <span class="logo-icon">📝</span>
    <div class="logo-brand">MiniShiksha</div>
    <div class="logo-sub">OMR Battle System</div>
  </div>

  <div class="card-title">Welcome Back</div>
  <div class="card-sub">Sign in with your Google account to access your tests, rooms and results.</div>

  <button class="btn-google" id="btn-google" onclick="handleGoogleSignIn()">
    <svg width="20" height="20" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
    <span id="btn-google-text">Continue with Google</span>
  </button>

  <div class="error-msg" id="error-msg"></div>

  <div class="divider">included features</div>

  <div class="features">
    <div class="feature"><div class="feature-dot"></div>Real-time OMR battles with friends</div>
    <div class="feature"><div class="feature-dot" style="background:var(--ok)"></div>Your name auto-fills from Google</div>
    <div class="feature"><div class="feature-dot" style="background:#ffe156"></div>Full exam history & score tracking</div>
    <div class="feature"><div class="feature-dot" style="background:#ff5f7e"></div>PDF question papers & solutions</div>
  </div>

  <div class="footer">
    By signing in you agree to our <a href="#">Terms of Service</a> &amp; <a href="#">Privacy Policy</a>.<br>
    Your data is stored securely via Google Firebase.
  </div>
</div>

<!-- Firebase SDKs (compat) -->
<script src="https://www.gstatic.com/firebasejs/10.12.0/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/10.12.0/firebase-auth-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/10.12.0/firebase-firestore-compat.js"></script>
<script src="/firebase-config.js"></script>
<script>
const RETURN_URL = <?php echo json_encode($return_url ?? '/test.php'); ?>;

// If already signed in, redirect straight away
auth.onAuthStateChanged(user => {
  if (user) {
    window.location.replace(RETURN_URL);
  }
});

async function handleGoogleSignIn() {
  const btn  = document.getElementById('btn-google');
  const text = document.getElementById('btn-google-text');
  const err  = document.getElementById('error-msg');
  btn.classList.add('loading');
  text.textContent = 'Signing in…';
  err.classList.remove('show');
  try {
    await omrSignInGoogle();
    // onAuthStateChanged will redirect
  } catch (e) {
    let msg = 'Sign-in failed. Please try again.';
    if (e.code === 'auth/popup-closed-by-user')  msg = 'Sign-in cancelled.';
    if (e.code === 'auth/popup-blocked')          msg = 'Pop-up blocked — please allow pop-ups for this site.';
    err.textContent = msg;
    err.classList.add('show');
    btn.classList.remove('loading');
    text.textContent = 'Continue with Google';
  }
}
</script>
</body>
</html>
