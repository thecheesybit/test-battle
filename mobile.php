<?php
// omr-mobile.php — Dedicated Mobile Video Call for OMR Battle Room
// URL: https://minishiksha.in/omr-mobile.php?player_id=XXX&room_id=YYYY

$player_id = strtoupper(trim($_GET['player_id'] ?? ''));
$room_id   = strtoupper(trim($_GET['room_id']   ?? ''));
$msid      = preg_replace('/[^A-Za-z0-9]/', '', $_GET['msid'] ?? '');

// Sanitise
$player_id = preg_replace('/[^A-Z0-9]/', '', $player_id);
$room_id   = preg_replace('/[^A-Z0-9]/', '', $room_id);

if (!$player_id || !$room_id) {
    die("Invalid link. Please scan the QR code from your browser.");
}

require_once __DIR__ . '/views/mobile.view.php';
