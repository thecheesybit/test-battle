<?php
// views/auth-guard.php
// Include at the TOP of any protected PHP page (before any HTML output).
// Outputs a fast-redirect script that runs before Firebase SDK loads,
// then a full Firebase auth check once the SDK is available.
?>
<!-- Firebase SDKs -->
<script src="https://www.gstatic.com/firebasejs/10.12.0/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/10.12.0/firebase-auth-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/10.12.0/firebase-firestore-compat.js"></script>
<!-- Fast localStorage pre-check (no SDK needed) -->
<script>
(function(){
  try {
    var u = localStorage.getItem('omr_user');
    if (!u || u === 'null') {
      var ret = encodeURIComponent(window.location.href);
      var base=location.pathname.substring(0,location.pathname.lastIndexOf('/')+1);window.location.replace(base+'login.php?return=' + ret);
    }
  } catch(e) {}
})();
</script>
<!-- Full Firebase auth validation (loaded async) -->
<script src="firebase-config.js"></script>
<script src="db-api.js"></script>
<script>
// Proper auth check once SDK is ready
auth.onAuthStateChanged(function(user) {
  if (!user) {
    var ret = encodeURIComponent(window.location.href);
    var base=location.pathname.substring(0,location.pathname.lastIndexOf('/')+1);
    window.location.replace(base+'login.php?return=' + ret);
  }
});
</script>
