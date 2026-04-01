// firebase-config.js — Shared Firebase config & auth helpers
// ⚠️  Firebase JS API keys are safe to expose in client code.
//     Real security is enforced via Firebase Auth + Firestore Security Rules.

const FIREBASE_CONFIG = {
  apiKey:            'AIzaSyBGhIVYnx_kBAF3TsB0vJOTnTE8oGZcTto',
  authDomain:        'minishiksha-test.firebaseapp.com',
  databaseURL:       'https://minishiksha-test-default-rtdb.asia-southeast1.firebasedatabase.app',
  projectId:         'minishiksha-test',
  storageBucket:     'minishiksha-test.firebasestorage.app',
  messagingSenderId: '607452088491',
  appId:             '1:607452088491:web:c66a7dae53ae07ae138fee',
};

// Singleton initialisation — safe to include multiple times
if (!firebase.apps.length) {
  firebase.initializeApp(FIREBASE_CONFIG);
}

const db   = firebase.firestore();
const auth = firebase.auth();

// ── Persistence ───────────────────────────────────────────────
auth.setPersistence(firebase.auth.Auth.Persistence.LOCAL);

// ── Stored user helpers ───────────────────────────────────────
function omrGetStoredUser() {
  try { return JSON.parse(localStorage.getItem('omr_user') || 'null'); } catch { return null; }
}
function omrStoreUser(user) {
  if (!user) { localStorage.removeItem('omr_user'); return; }
  localStorage.setItem('omr_user', JSON.stringify({
    uid:         user.uid,
    displayName: user.displayName || 'User',
    email:       user.email       || '',
    photoURL:    user.photoURL    || '',
  }));
}

// ── Sign-in / out ─────────────────────────────────────────────
async function omrSignInGoogle() {
  const provider = new firebase.auth.GoogleAuthProvider();
  provider.addScope('profile');
  provider.addScope('email');
  const result = await auth.signInWithPopup(provider);
  // Upsert user doc in Firestore
  if (result.user) {
    await db.collection('users').doc(result.user.uid).set({
      displayName: result.user.displayName || 'User',
      email:       result.user.email       || '',
      photoURL:    result.user.photoURL    || '',
      last_login:  firebase.firestore.FieldValue.serverTimestamp(),
    }, { merge: true });
  }
  return result;
}
async function omrSignOut() {
  omrStoreUser(null);
  return auth.signOut();
}

// ── Auth state listener ───────────────────────────────────────
auth.onAuthStateChanged(user => {
  omrStoreUser(user || null);
  document.dispatchEvent(new CustomEvent('omr:authChanged', { detail: user || null }));
});

// ── Firestore helpers ─────────────────────────────────────────
function fsRoom(roomId)       { return db.collection('rooms').doc(roomId); }
function fsTest(testName)     { return db.collection('tests').doc(testName); }
function fsUser(uid)          { return db.collection('users').doc(uid); }
function fsResults(uid)       { return db.collection('results').where('all_player_uids', 'array-contains', uid).orderBy('played_at', 'desc'); }

// ── Token helper (for PHP API calls) ─────────────────────────
async function omrGetIdToken() {
  const user = auth.currentUser;
  if (!user) return null;
  return user.getIdToken(false);
}
