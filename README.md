# OMR Battle Room (v3.0.0)

OMR Battle Room is a real-time, multiplayer test-taking application built on a vanilla PHP stack. It allows candidates to participate in simultaneous OMR code-driven sessions with live streaming features, collaborative chat, and synchronized question progression.

## 🏗️ Project Architecture & How It Works

The project is designed to be lightweight, fast, and entirely file-driven (no external SQL database required), making it incredibly easy to deploy on any standard PHP hosting environment (like inside an existing WordPress structure).

### How It Works
1. **Host Server (Vanilla PHP):** The backend serves HTML/JS via plain PHP views and acts as a RESTful JSON API.
2. **File-based I/O Engine:** Room state, chat messages, and player answers are stored in active JSON flat-files inside `wp-content/omr-data/`.
3. **Polling Synchronisation:** The frontend clients perform short-polling (`action=sync`) to fetch the latest JSON state and seamlessly update the DOM reactively.
4. **GetStream.io Integration:** We offload server-straining video and voice communication directly to GetStream.io's robust WebRTC infrastructure. The PHP backend orchestrates the connection, but the actual video traffic is peer-to-peer/SFU based.

### Component Structure
- `test.php`: The primary dashboard index. Users can create a Room or load existing tests.
- `room.php`: The main Battle arena. Houses the OMR bubbling sheet, the PDF viewer, the Live Chat, and the Video Grid.
- `mobile.php`: A barebones client specifically optimized for casting a user's camera to the main session.
- `api/`: Houses the backend microservices.
  - `ServiceDiscovery.php`: A dynamic router that maps API requests to specific controllers (`?action=sync` -> `ExamController::sync`).
  - `RoomController.php`, `ExamController.php`, `TestController.php`, `PlayerController.php`: Highly cohesive controllers handling logic for their respective domains.
- `includes/helpers.php`: Core utility methods, including the critical `updateRoom()` method that utilizes `flock(LOCK_EX)` to ensure data integrity during parallel JSON file modifications.

---

## 🗺️ Version History & Roadmap

### v1.0.0 - The Monolith
- **Core Engine:** Basic file-polling OMR structure established for test sharing.
- **Monolithic API:** A single massive `omr-api.php` file handled all routing logic using a giant un-scoped switch statement.
- **Basic UI:** Static HTML interface for test taking with minimal interactive features. 

### v2.0.0 - The Collaborative Leap
- **Split-Screen PDF Viewer:** Added the ability to side-load a question paper PDF alongside the bubbling sheet for a tightly unified test-taking experience.
- **Live Chat & BRB:** Introduced a synchronised live chat panel and a "Be Right Back" feature to globally pause the test countdown.
- **Feature Enhancements:** Custom timers (Stopwatch / Countdown), reveal constraints (majority voting to reveal correct answers), and individual locked-question states for re-attempts.

### v3.0.0 - Microservices & Stream Video (Current Release)
- **Microservices Refactoring:** Completely dismantled the monolithic API script into strictly-scoped controllers routed by `ServiceDiscovery.php`.
- **Robust Concurrent I/O:** Added exclusive OS-level file locking (`flock`) to prevent JSON data truncation/corruption when dozens of fast-polling clients interact simultaneously. Write overhead was reduced drastically.
- **Stream Video SDK Integration:** Added WebRTC Video and Voice calling seamlessly bounded into `room.php`. Includes Web Audio API synthesized ringing alerts.
- **Mobile Device Transfer:** Allowed users to scan a dynamically generated QR code and immediately hand off their camera/mic to their smartphone. Prevented device-spam via a strict 1-to-1 Mobile Session ID (`msid`) protocol that actively terminates superseded sessions.
- **Sanitization:** Implemented deep filesystem normalization and structural renaming to avoid WordPress-core clashes (`dashboard` vs `index`).

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
