<?php
// includes/helpers.php
// Minimal helpers for stream.php (JWT token minting).
// All room/test/player data is now in Firebase Firestore — no JSON file operations.

function jsonOut($data, $code = 200)
{
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// ══════════════════════════════════════════════════════════════
//  STREAM API HELPERS
// ══════════════════════════════════════════════════════════════

function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

/**
 * Generate a GetStream-compatible JWT token.
 * GetStream expects: header.payload.signature
 * - header:  { "typ": "JWT", "alg": "HS256" }
 * - payload: { "user_id": "<id>", "iss": "stream-video", "iat": <now>, "exp": <now+3600> }
 */
function generateStreamToken($userId) {
    $now = (int) time();
    $header = base64url_encode(json_encode([
        'typ' => 'JWT',
        'alg' => 'HS256'
    ]));
    $payload = base64url_encode(json_encode([
        'user_id' => (string) $userId,
        'iss'     => 'stream-video',
        'sub'     => 'user/' . $userId,
        'iat'     => $now,
        'exp'     => $now + 86400, // 24 hours
    ]));
    $signature = base64url_encode(
        hash_hmac('sha256', $header . '.' . $payload, STREAM_API_SECRET, true)
    );
    return $header . '.' . $payload . '.' . $signature;
}
