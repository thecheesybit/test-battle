<?php
// includes/config.example.php
// Minimal config — all dynamic data is in Firebase Firestore.
// Only PDF files are stored on the server filesystem.

// PDF storage directory
define('OMR_DATA_DIR', __DIR__ . '/../wp-content/omr-data/');

// GetStream Video Credentials (used by stream.php for JWT token minting)
// Add your keys below and rename this file to config.php
define('STREAM_API_KEY', 'YOUR_STREAM_API_KEY_HERE');
define('STREAM_API_SECRET', 'YOUR_STREAM_API_SECRET_HERE');
