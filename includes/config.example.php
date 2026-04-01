<?php
// includes/config.example.php

// Define constants for paths
define('OMR_BASE_URL', '/'); // Set to your base URL
define('OMR_DATA_DIR', __DIR__ . '/../wp-content/omr-data/');

// Cleanup settings
define('CLEANUP_INTERVAL_SEC', 3600);   // Run cleanup at most once per hour
define('ROOM_EXPIRY_HOURS', 24);        // Delete rooms older than 24 hours

// Add your secret keys below and rename this file to config.php
define('STREAM_API_KEY', 'YOUR_STREAM_API_KEY_HERE');
define('STREAM_API_SECRET', 'YOUR_STREAM_API_SECRET_HERE');
