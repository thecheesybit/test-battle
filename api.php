<?php
// api.php — Backend API Router for MiniShiksha OMR System
// Handles all AJAX requests via POST only

header('Content-Type: application/json');

// CORS — restrict to same origin in production; use specific domain if needed
$allowedOrigin = ($_SERVER['HTTP_ORIGIN'] ?? '*');
header('Access-Control-Allow-Origin: ' . $allowedOrigin);
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Max-Age: 86400');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed. Use POST.']);
    exit;
}

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/api/ServiceDiscovery.php';

// ── READ INPUT ──
$raw = file_get_contents('php://input');
$input = json_decode($raw, true);

// Fallback to POST form data (for multipart/form-data uploads)
if (!is_array($input) && !empty($_POST)) {
    $input = $_POST;
}

if (!is_array($input) || empty($input)) {
    jsonOut(['error' => 'Invalid or empty request body'], 400);
}

$action = trim($input['action'] ?? '');
if ($action === '') {
    jsonOut(['error' => 'Missing action parameter'], 400);
}

// ── ROUTE VIA SERVICE DISCOVERY ──
ServiceDiscovery::route($action, $input);
