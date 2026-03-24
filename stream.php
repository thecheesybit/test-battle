<?php
// stream-api.php — GetStream Video Token Server
// Mints JWT tokens for GetStream Video SDK. The API secret NEVER reaches the browser.

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/helpers.php';

// ── READ INPUT ──
$raw   = file_get_contents('php://input');
$input = json_decode($raw, true);
if (!$input && !empty($_POST)) {
    $input = $_POST;
}
$action = $input['action'] ?? '';

switch ($action) {
    case 'get_token':
        actionGetToken($input);
        break;
    default:
        jsonOut(['error' => 'Unknown action: ' . $action], 400);
}

// Helpers have been moved to includes/helpers.php

// ══════════════════════════════════════════════════════════════
//  ACTIONS
// ══════════════════════════════════════════════════════════════

function actionGetToken($input) {
    $roomId     = strtoupper(trim($input['room_id']     ?? ''));
    $playerId   = strtoupper(trim($input['player_id']   ?? ''));
    $playerName = trim($input['player_name'] ?? 'Player');

    if (!$roomId || !$playerId) {
        jsonOut(['error' => 'room_id and player_id are required'], 400);
    }

    // User ID for GetStream: use player code as unique identifier
    $userId = 'omr-' . $playerId;
    $callId = 'omr-' . $roomId;

    $token = generateStreamToken($userId);

    jsonOut([
        'token'       => $token,
        'user_id'     => $userId,
        'call_id'     => $callId,
        'api_key'     => STREAM_API_KEY,
        'player_name' => $playerName,
    ]);
}
