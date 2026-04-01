# MiniShiksha OMR — Remaining Setup Checklist

## 1 · GetStream — Enable `audio_room` call type

The voice channel uses GetStream's **audio_room** call type.

1. Go to [GetStream Dashboard](https://dashboard.getstream.io) → your app
2. Navigate to **Video & Audio → Call Types**
3. Click **Add call type** → name it exactly `audio_room`
4. Settings: disable video, enable audio, max participants = 50
5. Save.

> The existing `STREAM_API_KEY` and `STREAM_API_SECRET` in `.env` / `config.php` are reused — no new credentials needed.

---

## 2 · Firestore Security Rules

In Firebase Console → Firestore → Rules, paste:

```
rules_version = '2';
service cloud.firestore {
  match /databases/{database}/documents {

    // Only authenticated users can read/write their own results
    match /results/{docId} {
      allow read, write: if request.auth != null
        && (docId.matches('^.*_' + request.auth.uid + '$')
            || resource.data.all_player_uids.hasAny([request.auth.uid]));
    }

    // Tests: any authenticated user can read; only super admin can write
    match /tests/{testId} {
      allow read: if request.auth != null;
      allow write: if request.auth != null
        && request.auth.token.email == 'ak818ace@gmail.com';
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

Add your WordPress domain (e.g. `minishiksha.in`). `localhost` is added by default.

---

## 4 · Data Migration

1. Sign in at `https://yourdomain.com/migrate.php` with `ak818ace@gmail.com`
2. Click **Start Migration** — reads from `/wp-content/omr-data/*.json`
3. Each test is written to Firestore `/tests/{name}` — duplicates are skipped automatically
4. PDFs stay on the PHP server; only metadata/answer keys go to Firestore
5. Run as many times as needed — idempotent

---

## 5 · Files to Upload via FTP/SSH

Upload these new/changed files to your WordPress server root (`public_html/` or wherever `test.php` lives):

```
firebase-config.js          ← NEW — shared Firebase SDK init
login.php                   ← NEW — Google Sign-In page
migrate.php                 ← NEW — migration tool (admin only)
FIREBASE_SETUP.md           ← NEW — detailed Firebase guide
views/login.view.php        ← NEW
views/migrate.view.php      ← NEW
views/auth-guard.php        ← NEW
views/room.view.php         ← UPDATED
views/test.view.php         ← UPDATED
api/PlayerController.php    ← UPDATED (rename_player action)
api/ServiceDiscovery.php    ← UPDATED
```

> **Do NOT upload** `.env` — that file stays local / set env vars in your server control panel.

---

## 6 · Jitsi Meet — No Setup Required

Every exam room gets a stable Jitsi Meet link: `https://meet.jit.si/minishiksha-{ROOM_ID}`

- Completely free, no account, no API key
- Opens in new tab when user clicks **Open** in the Voice panel
- **Copy** copies the link to clipboard
- **QR** renders a scannable QR code

---

## 7 · Voice Channel (GetStream Audio)

The voice panel uses `StreamVideoClient` from the GetStream Video JS SDK. The SDK is loaded by `stream-codec.view.php` (already on your server). After completing step 1 above:

- Users click **Join Voice** inside the Voice tab of the Tools panel
- They can **Mute / Unmute** and **Leave**
- Participant list updates in real-time
- Each exam room has its own isolated audio channel (`omr-{ROOM_ID}`)

> If the SDK isn't available (old `stream-codec.view.php`), voice join shows a toast and gracefully fails — Jitsi Meet still works as fallback.

---

## 8 · Player Status (BRB / Break / Help)

No server setup needed — works via the existing chat `send_message` API.

| Status　　　| Behaviour                                                    |
| -------------| --------------------------------------------------------------|
| 🟢 Active　　| Default, no notification                                     |
| 🕐 BRB　　　| Posts a chat message to the room                             |
| ☕ Break　　 | Posts a chat message to the room                             |
| 🆘 Need Help | Posts chat + plays alert sound + shows banner to all players |

---

## 9 · Super Admin

Only `ak818ace@gmail.com` can access `/migrate.php`. Any other signed-in user sees an "Access Restricted" page. No additional setup needed.

---

## 10 · `.gitignore` — Verify These Are Excluded

```
.env
firebase-service-account.json
wp-content/omr-data/
node_modules/
```

---

## 11 · PDF Viewer (Native Browser, no pdf.js)

PDFs are now displayed using the browser's **native PDF viewer** inside an `<iframe>`. No CDN or library is needed.

- **Auto-scroll** to the correct page when navigating questions works via `#page=N` URL fragment
- For auto-scroll to work, `page_map` must be set when creating the test (map page number → first question on that page)
- Users can zoom, search and navigate using the browser's built-in PDF toolbar
- Works in Chrome, Edge, Firefox — Safari may show a download prompt instead of inline view

### PDF URL requirements

PDFs must be served from the **same origin** as the OMR app (or with `Access-Control-Allow-Origin: *` header), otherwise the browser blocks the iframe.

If your PDFs live at `/wp-content/uploads/...` and the app is at `/`, they are same-origin — no extra config needed.

---

## 12 · UPSC CSE Marks Calculation

The result screen automatically shows a UPSC-style marks breakdown after every exam.

**Auto-detection**: the tag field on the test card is checked:
- Tags containing `gs`, `general`, `prelim`, `paper1` → **GS pattern** (+2 correct, −⅔ wrong)  
- Tags containing `csat`, `paper2` → **CSAT pattern** (+2.5 correct, −⅚ wrong)
- Any other tag → defaults to GS; user can switch via the dropdown in the marks panel

**Pattern reference:**

| Paper | Per Q | Negative |
|---|---|---|
| GS Paper 1 | +2.00 | −0.666 |
| CSAT Paper 2 | +2.50 | −0.833 |
| Custom | +1.00 | −0.333 |

Users can switch pattern using the dropdown on the result page — no page reload needed.

---

## 13 · Analysis Mode

After an exam ends, clicking **🔍 Analysis Mode** opens a full-screen review overlay:

| Panel | Content |
|---|---|
| Left | Question grid — green=correct, red=wrong, grey=skipped; click any to jump |
| Center | Question detail: your answer vs correct answer, option highlighting, mark status, UPSC ±marks |
| Right (desktop) | PDF panel with **Paper** / **Solution** tabs; auto-jumps to the relevant page |

**Keyboard shortcuts inside Analysis Mode:**
- `←` / `→` — previous / next question
- `Esc` — close Analysis Mode

> Analysis Mode uses the same `page_map` as the live exam for PDF auto-scroll. If no `page_map` is set, the PDF panel still shows the full PDF without auto-jump.
