<?php
// omr-mobile.php — Dedicated Mobile Video Call for OMR Battle Room
// URL: https://minishiksha.in/mobile.php?player_id=XXX&room_id=YYYY&msid=ZZZ

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/helpers.php';

$player_id = preg_replace('/[^A-Z0-9]/', '', strtoupper(trim($_GET['player_id'] ?? '')));
$room_id   = preg_replace('/[^A-Z0-9]/', '', strtoupper(trim($_GET['room_id']   ?? '')));
$msid      = preg_replace('/[^A-Za-z0-9]/', '', $_GET['msid'] ?? '');

if (!$player_id || !$room_id) {
    die("Invalid link. Please scan the QR code from your browser.");
}

// Look up player name from room data
$myName = 'Mobile Player';
$room = loadRoom($room_id);
if ($room) {
    foreach ($room['players'] as $p) {
        if (($p['code'] ?? '') === $player_id) {
            $myName = $p['name'] ?? 'Mobile Player';
            break;
        }
    }
}

require_once __DIR__ . '/views/mobile.view.php';
