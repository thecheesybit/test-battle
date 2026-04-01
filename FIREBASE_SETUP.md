# Firebase Setup Guide — MiniShiksha OMR

Complete guide to configure Firebase Auth + Firestore for the OMR system.

---

## 1 · Firebase Project (already created)

Your project is **`minishiksha-test`**.  
Console: https://console.firebase.google.com/project/minishiksha-test

---

## 2 · Fix the `.env` File

The `.env` at project root currently contains JavaScript code — that is incorrect.  
Replace its contents with the proper environment variable format:

```ini
# .env — server environment variables (never commit to git)

# Firebase (public config — same as what's in firebase-config.js)
FIREBASE_PROJECT_ID=minishiksha-test
FIREBASE_API_KEY=AIzaSyBGhIVYnx_kBAF3TsB0vJOTnTE8oGZcTto

# GetStream video credentials
STREAM_API_KEY=vwe5xw2ju9ja
STREAM_API_SECRET=h57msa7c3act73mxrk6p2av97astvbmy2nc93snbq734jf4uzvjejejb8nvyfe4z
```

> The Firebase **client API key** (`AIzaSy...`) is intentionally public — it only identifies
> your project to Google. Real security comes from Auth + Firestore rules (step 5).

---

## 3 · Enable Google Sign-In

1. Firebase Console → **Authentication** → **Sign-in method**
2. Enable **Google**
3. Set **Project support email** to your email
4. Save

---

## 4 · Enable Firestore

1. Firebase Console → **Firestore Database** → **Create database**
2. Start in **production mode**
3. Choose region: **asia-south1** (Mumbai) — closest to your users

---

## 5 · Firestore Security Rules

Go to **Firestore → Rules** and paste:

```javascript
rules_version = '2';
service cloud.firestore {
  match /databases/{database}/documents {

    // Users can only read/write their own profile
    match /users/{uid} {
      allow read, write: if request.auth != null && request.auth.uid == uid;
    }

    // Tests: any authenticated user can read; only you (admin) should write
    // For now, allow any authenticated user to write (tighten after go-live)
    match /tests/{testId} {
      allow read:  if request.auth != null;
      allow write: if request.auth != null;
    }

    // Rooms: any authenticated user can read/write (host creates, players update answers)
    match /rooms/{roomId} {
      allow read, write: if request.auth != null;
    }

    // Results: readable by anyone whose uid is in all_player_uids
    match /results/{resultId} {
      allow read:  if request.auth != null &&
                      request.auth.uid in resource.data.all_player_uids;
      allow create: if request.auth != null;
      allow update: if request.auth != null &&
                       request.auth.uid in resource.data.all_player_uids;
    }
  }
}
```

Click **Publish**.

---

## 6 · Authorised Domains

1. Firebase Console → **Authentication** → **Settings** → **Authorised domains**
2. Add your WordPress domain, e.g. `minishiksha.in`
3. `localhost` is already added by default

---

## 7 · Firebase Admin SDK (for PHP server — optional but recommended)

The client-side SDK handles auth for the browser. For the PHP API to verify tokens server-side:

### 7a · Generate a Service Account Key

1. Firebase Console → **Project Settings** → **Service accounts**
2. Click **Generate new private key** → download JSON
3. Rename it `firebase-service-account.json`
4. **Never commit this file** — add to `.gitignore`

### 7b · Add to .gitignore

```
firebase-service-account.json
.env
wp-content/omr-data/
```

### 7c · PHP Token Validation (no Composer needed)

The `api.php` router now accepts an `Authorization: Bearer {id_token}` header.  
Token validation is done via Google's public key endpoint — no SDK needed.

---

## 8 · Deploy to WordPress Server

### Files to upload (FTP/SSH):

```
/test.php
/room.php
/login.php
/migrate.php
/mobile.php
/stream.php
/api.php
/firebase-config.js        ← NEW
/api/
/includes/
/views/
  login.view.php            ← NEW
  auth-guard.php            ← NEW
  migrate.view.php          ← NEW
  test.view.php
  room.view.php
  stream-codec.view.php
```

> PDFs are served from `wp-content/omr-data/` — do NOT upload these to Firebase Storage
> unless you explicitly enable a paid Blaze plan. The system keeps PDFs on your server.

### Environment on server:

The `firebase-config.js` uses hardcoded values (public config — this is fine).  
For the service account key: upload `firebase-service-account.json` to a directory
**outside the web root** if possible, e.g. `/home/yourdomain/firebase-service-account.json`,
then update the path in `includes/config.php`.

---

## 9 · Run the Migration Tool

1. Go to `https://minishiksha.in/migrate.php`
2. Sign in with your Google account
3. Click **Start Migration**
4. Wait for "Complete" — each test is written to Firestore once
5. Re-running is safe: existing entries are skipped (or use Force Re-migrate)

---

## 10 · Verify Everything Works

| Check | URL |
|---|---|
| Sign-in page | `/login.php` |
| Dashboard (protected) | `/test.php` |
| Exam room (protected) | `/room.php` |
| Migration tool | `/migrate.php` |
| Firebase Console | https://console.firebase.google.com |

---

## 11 · Firestore Data Structure

```
/users/{uid}
  displayName, email, photoURL, last_login

/tests/{testName}
  name, title, subject, tag, question_count
  answer_key: { Q1: "a", ... }
  question_texts: { Q1: "...", ... }
  options: { Q1: [...], ... }
  pdf_url, solution_pdf_url    ← server paths, NOT Firebase Storage
  page_map: { "1": 1, ... }

/rooms/{roomId}
  test_name, status, timer_mode, duration_sec, exam_mode
  started_at, ended_at, created_at
  players: [{ name, uid, code, answers, submitted, ... }]

/results/{resultId}
  room_id, test_name, played_at
  all_player_uids: [uid1, uid2, ...]   ← used for history queries
  players: [{ name, uid, correct, wrong, skip }]
  winner_uid
```

---

## Troubleshooting

| Problem | Solution |
|---|---|
| Pop-up blocked on sign-in | Allow pop-ups for your domain in browser settings |
| `auth/unauthorized-domain` | Add your domain in Firebase Auth → Authorised domains |
| Firestore permission denied | Check security rules (step 5) |
| Migration shows 0 tests | Ensure `wp-content/omr-data/*.json` files exist on server |
| PDF not loading | PDFs stay on PHP server — check `pdf_url` path in test JSON |
