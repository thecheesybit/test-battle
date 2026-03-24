<?php
// includes/helpers.php

// ══════════════════════════════════════════════════════════════
//  JSON & FILE PATH HELPERS
// ══════════════════════════════════════════════════════════════

function jsonOut($data, $code = 200)
{
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

function roomPath($roomId)
{
    return OMR_DATA_DIR . 'room_' . preg_replace('/[^a-zA-Z0-9_\-]/', '', $roomId) . '.json';
}

function testPath($testName)
{
    return OMR_DATA_DIR . preg_replace('/[^a-zA-Z0-9_\-]/', '_', $testName) . '.json';
}

function codesIndexPath()
{
    return OMR_DATA_DIR . '_codes_index.json';
}

// ══════════════════════════════════════════════════════════════
//  DATA READ/WRITE HELPERS
// ══════════════════════════════════════════════════════════════

function loadRoom($roomId)
{
    $path = roomPath($roomId);
    if (!file_exists($path))
        return null;
    $data = json_decode(file_get_contents($path), true);
    return $data;
}

function saveRoom($roomId, $data)
{
    $data['last_updated'] = time();
    $path = roomPath($roomId);
    // Atomic write
    $tmp = $path . '.tmp';
    file_put_contents($tmp, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    rename($tmp, $path);
    return true;
}

/**
 * Safely updates a room's JSON data using an exclusive file lock to completely prevent race conditions.
 * @param string $roomId The ID of the room
 * @param Closure $callback Function that receives the decoded room array by reference to modify it. Return false to abort save.
 * @return bool True if successfully updated, false otherwise.
 */
function updateRoom($roomId, Closure $callback)
{
    $path = roomPath($roomId);
    if (!file_exists($path)) {
        return false;
    }

    $fp = fopen($path, 'c+');
    if (!$fp) {
        return false;
    }

    if (flock($fp, LOCK_EX)) {
        clearstatcache(true, $path);
        $filesize = filesize($path);
        $raw = '';
        if ($filesize > 0) {
            $raw = fread($fp, $filesize);
        }
        
        $data = $raw ? json_decode($raw, true) : null;
        if (!$data) {
            flock($fp, LOCK_UN);
            fclose($fp);
            return false;
        }

        $result = $callback($data);

        if ($result !== false) {
            $data['last_updated'] = time();
            $newRaw = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            ftruncate($fp, 0);
            fseek($fp, 0);
            fwrite($fp, $newRaw);
            fflush($fp);
        }
        
        flock($fp, LOCK_UN);
        fclose($fp);
        return true;
    } else {
        fclose($fp);
        return false;
    }
}

function loadCodesIndex()
{
    $path = codesIndexPath();
    if (!file_exists($path))
        return [];
    return json_decode(file_get_contents($path), true) ?: [];
}

function saveCodesIndex($index)
{
    $path = codesIndexPath();
    $tmp = $path . '.tmp';
    file_put_contents($tmp, json_encode($index, JSON_PRETTY_PRINT));
    rename($tmp, $path);
}

// ══════════════════════════════════════════════════════════════
//  GENERATION HELPERS
// ══════════════════════════════════════════════════════════════

function generateCode($existing = [])
{
    // 3-char alphanumeric code (uppercase), easy to type
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    do {
        $code = '';
        for ($i = 0; $i < 3; $i++) {
            $code .= $chars[random_int(0, strlen($chars) - 1)];
        }
    } while (isset($existing[$code]));
    return $code;
}

function generateRoomId()
{
    return strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
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
    $now = time();
    $header = base64url_encode(json_encode([
        'typ' => 'JWT',
        'alg' => 'HS256'
    ]));
    $payload = base64url_encode(json_encode([
        'user_id' => $userId,
        'iss'     => 'stream-video',
        'sub'     => 'user/' . $userId,
        'iat'     => $now,
        'exp'     => $now + 3600, // 1 hour
    ]));
    $signature = base64url_encode(
        hash_hmac('sha256', $header . '.' . $payload, STREAM_API_SECRET, true)
    );
    return $header . '.' . $payload . '.' . $signature;
}
