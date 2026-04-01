<?php
// omr-room.php — OMR Battle Room
// URL: https://minishiksha.in/omr-room.php?player_id=XXX&room_id=YYYY
// Or:  https://minishiksha.in/omr-room.php (shows code entry)

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/helpers.php';

$player_id = strtoupper(trim($_GET['player_id'] ?? ''));
$room_id   = strtoupper(trim($_GET['room_id']   ?? ''));
$is_host   = isset($_GET['host']) && $_GET['host'] == '1';

// Sanitise
$player_id = preg_replace('/[^A-Z0-9]/', '', $player_id);
$room_id   = preg_replace('/[^A-Z0-9]/', '', $room_id);

require_once __DIR__ . '/views/room.view.php';