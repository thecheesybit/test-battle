<?php
// api.php — Backend API Router for MiniShiksha OMR System
// Handles all AJAX requests

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/api/ServiceDiscovery.php';

// ── READ INPUT ──
// Handle both JSON body and GET/POST params
$raw = file_get_contents('php://input');
$input = json_decode($raw, true);
if (!$input && !empty($_GET)) {
    $input = $_GET;
}
if (!$input && !empty($_POST)) {
    $input = $_POST;
}
$action = $input['action'] ?? '';

// ── ROUTE VIA SERVICE DISCOVERY ──
ServiceDiscovery::route($action, $input);
