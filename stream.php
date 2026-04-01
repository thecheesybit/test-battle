<?php
// stream.php — GetStream Video Token Server
// Mints JWT tokens for GetStream Video SDK. The API secret NEVER reaches the browser.

header('Content-Type: application/json');

$allowedOrigin = ($_SERVER['HTTP_ORIGIN'] ?? '*');
header('Access-Control-Allow-Origin: ' . $allowedOrigin);
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Max-Age: 86400');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed. Use POST.']);
    exit;
}

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/helpers.php';

// ── READ INPUT ──
$raw   = file_get_contents('php://input');
$input = json_decode($raw, true);
if (!is_array($input) && !empty($_POST)) {
    $input = $_POST;
}
if (!is_array($input)) {
    jsonOut(['error' => 'Invalid request body'], 400);
}
$action = trim($input['action'] ?? '');

switch ($action) {
    case 'get_token':
        actionGetToken($input);
        break;
    default:
        jsonOut(['error' => 'Unknown action'], 400);
}

// ══════════════════════════════════════════════════════════════
//  ACTIONS
// ══════════════════════════════════════════════════════════════

function actionGetToken($input) {
    $roomId     = preg_replace('/[^A-Z0-9]/', '', strtoupper(trim($input['room_id']     ?? '')));
    $playerId   = preg_replace('/[^A-Z0-9]/', '', strtoupper(trim($input['player_id']   ?? '')));
    $playerName = mb_substr(trim($input['player_name'] ?? 'Player'), 0, 50);

    if (!$roomId || !$playerId) {
        jsonOut(['error' => 'room_id and player_id are required'], 400);
    }

    // Room/player validation is now handled client-side via Firestore.
    // This endpoint only mints tokens — the API secret never reaches the browser.

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
