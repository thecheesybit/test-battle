# OMR Battle Room (v3.0.0)

OMR Battle Room is a real-time, multiplayer test-taking application built on a vanilla PHP stack. It allows candidates to participate in simultaneous OMR code-driven sessions with live streaming features, collaborative chat, and synchronized question progression.

## 🚀 Version 3.0.0 Updates

This version brings a massive architectural overhaul, focusing on robust I/O, microservice scalability, and enhanced real-time interaction.

### 1. Architectural Refactoring & Microservices
The backend has been modularized away from a monolithic `omr-api.php` file into scoped controllers.
- **Service Discovery** (`api/ServiceDiscovery.php`): Dynamically resolves routes, acting as an internal registry for the application.
- **Microservice Controllers**: Logic is split strictly by domain context ensuring high cohesion:
  - `RoomController`: Room creation, state polling, cleanup.
  - `PlayerController`: Joining sessions and answering locks.
  - `TestController`: Managing paper JSON uploads and mappings.
  - `ExamController`: State synchronisation and payload validation.
- **Robust File-Locking (flock)**: Solved critical data corruption caused by race conditions during simultaneous JSON writes under heavy polling. The new `updateRoom()` uses exclusive locking logic (`LOCK_EX`).
- **Synchronisation Optimization**: Heavy disk I/O reduced dramatically by limiting online polling writes to exactly 10-second intervals or forced state changes.

### 2. Stream Video Integration
Integrated **GetStream.io Video & Voice SDK** right into the room interface.
- Includes granular controls for Camera, Microphone, and Speaker toggling directly from the `stream-codec.view.php`.
- Dynamic ringing sound alerts (`SFX`) built natively via Web Audio API to signal incoming calls without extra file payloads.
- **Mobile Transfer (1:1 enforced)**: Candidates can instantly cast their video/mic feed onto their phone via a dynamically generated QR Code. 
- *Strict 1:1 Security*: To prevent multi-device cheating or connection overload, generating a new QR Code mints a fresh Mobile Session ID (`msid`). If an older instance continues to poll, the backend forcefully disconnects the outdated devices.

### 3. Progressive Frontend Enhancements
- Unified all entry points into `test.php`, `room.php`, and `mobile.php` for cleaner URL resolution while perfectly avoiding WordPress's core `index.php` clashes.
- Refined dark UI, mobile-responsive grids, and unified styling architecture.

---

## 🛠 Prerequisites & Installation

1. Drop the repository contents into a directory served by PHP 8.1+ (Apache/Nginx).
2. The folder requires `write` permissions to the `wp-content/omr-data/` folder to persist states.
3. **Configure API Secrets**:
   Copy `includes/config.example.php` to `includes/config.php` and add your **Stream API Credentials**:
   ```php
   define('STREAM_API_KEY', 'YOUR_KEY_HERE');
   define('STREAM_API_SECRET', 'YOUR_SECRET_HERE');
   ```

## 🔒 Security Note
`includes/config.php` and `wp-content/omr-data/` are strictly ignored in source control (via `.gitignore`) to prevent accidental leaks of your secrets and database state.
