# MiniShiksha OMR — Setup & Architecture

## Architecture Overview

All dynamic data (rooms, players, answers, chat, tests) is stored in **Firebase Firestore**.
Only **PDF files** remain on the PHP server filesystem.
The PHP backend is minimal: `stream.php` (GetStream JWT tokens) + `upload.php` (PDF uploads).

### Data Flow
```
Browser → db-api.js → Firebase Firestore (rooms, tests, codes, results)
Browser → upload.php → Server filesystem (PDFs only)
Browser → stream.php → GetStream JWT token (voice calls)
```

### Firestore Collections
| Collection | Purpose |
|---|---|
| `omr_rooms` | Active exam rooms, players, answers, chat, BRB |
| `omr_codes` | Player code → room_id lookup |
| `omr_tests` | Test templates (answer keys, questions, metadata) |
| `results` | Completed exam results for history |
| `live_sessions` | Cross-device session persistence |
| `users` | Google auth user profiles |

---

## 1 · GetStream — Enable `audio_room` call type

1. Go to [GetStream Dashboard](https://dashboard.getstream.io) → your app
2. Navigate to **Video & Audio → Call Types**
3. Click **Add call type** → name it exactly `audio_room`
4. Settings: disable video, enable audio, max participants = 50
5. Save.

---

## 2 · Firestore Security Rules

In Firebase Console → Firestore → Rules, paste:

```
rules_version = '2';
service cloud.firestore {
  match /databases/{database}/documents {

    // Rooms: any authenticated user can read/write (multiplayer rooms)
    match /omr_rooms/{roomId} {
      allow read, write: if request.auth != null;
    }

    // Codes: any authenticated user can read/write
    match /omr_codes/{code} {
      allow read, write: if request.auth != null;
    }

    // Tests: any authenticated user can read; admin can write
    match /omr_tests/{testId} {
      allow read: if request.auth != null;
      allow write: if request.auth != null;
    }

    // Results: authenticated users can read their own
    match /results/{docId} {
      allow read, write: if request.auth != null;
    }

    // Live sessions: authenticated users can read/write their own
    match /live_sessions/{sessionId} {
      allow read, write: if request.auth != null;
    }

    // Users: can read/write own doc only
    match /users/{uid} {
      allow read, write: if request.auth != null && request.auth.uid == uid;
    }
  }
}
```

---

## 3 · Firebase Authorized Domains

Console → Authentication → Settings → Authorized domains

Add your domain (e.g. `minishiksha.in`). `localhost` is added by default.

---

## 4 · Files to Upload

Upload the entire `test/` folder to your server root. The folder structure:

```
test/
├── firebase-config.js      ← Firebase SDK init + auth helpers
├── db-api.js               ← Firestore data layer (replaces all PHP API)
├── login.php               ← Google Sign-In page
├── test.php                ← Main landing page (create/join rooms)
├── room.php                ← Exam room
├── stream.php              ← GetStream JWT token server (voice calls)
├── upload.php              ← PDF upload endpoint (server filesystem)
├── includes/
│   ├── config.php          ← Stream API keys + PDF directory
│   └── helpers.php         ← JWT token generation helper
├── views/
│   ├── auth-guard.php      ← Auth guard include
│   ├── login.view.php      ← Sign-in UI
│   ├── test.view.php       ← Landing page UI
│   ├── room.view.php       ← Exam room UI
│   └── stream-codec.view.php ← Voice call SDK
└── wp-content/omr-data/    ← PDF storage directory (auto-created)
```

### Files safe to DELETE (deprecated)
```
api/                        ← Entire folder (RoomController, ExamController, etc.)
api.php                     ← Deprecated API router (now returns 410)
mobile.php                  ← Redirects to room.php
views/mobile.view.php       ← No longer used
migrate.php                 ← Redirects to test.php
views/migrate.view.php      ← No longer used
```

---

## 5 · Voice Channel (GetStream Audio)

- Users click **Join Voice** in the Voice tab of the Tools panel
- **Mute / Unmute** and **Leave** controls
- Each exam room has its own isolated audio channel (`omr-{ROOM_ID}`)
- SDK loaded by `stream-codec.view.php` via ESM import

---

## 6 · PDF Viewer (Native Browser)

PDFs use the browser's **native PDF viewer** inside an `<iframe>`.

- **Auto-scroll** via `#page=N` URL fragment when `page_map` is configured
- Works in Chrome, Edge, Firefox
- PDFs must be same-origin or have CORS headers

---

## 7 · Player Status (BRB / Break / Help)

| Status | Behaviour |
|---|---|
| 🟢 Active | Default |
| 🕐 BRB | Pauses test + chat notification |
| ☕ Break | Chat notification |
| 🆘 Need Help | Chat + alert sound + banner to all players |

---

## 8 · UPSC CSE Marks Calculation

Auto-detected from test tag:
- `gs`, `general`, `prelim`, `paper1` → **GS** (+2, −⅔)
- `csat`, `paper2` → **CSAT** (+2.5, −⅚)
- Other → defaults to GS; switchable via dropdown

---

## 9 · Analysis Mode

After exam ends → **🔍 Analysis Mode** opens full-screen review:
- Question grid (green=correct, red=wrong, grey=skipped)
- Answer comparison with option highlighting
- PDF panel with Paper/Solution tabs + auto-scroll
- Keyboard: `←`/`→` navigate, `Esc` close
