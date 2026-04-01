<?php
// upload.php — Minimal PDF upload endpoint for MiniShiksha OMR
// Handles question paper and solution PDF uploads to server filesystem.
// All other data operations are handled client-side via Firebase Firestore.

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

require_once __DIR__ . '/includes/config.php';

function out($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data);
    exit;
}

$action = $_POST['action'] ?? '';
$testName = trim($_POST['test_name'] ?? '');

if (!$testName) out(['error' => 'Test name required'], 400);
if (!isset($_FILES['pdf_file']) || $_FILES['pdf_file']['error'] !== UPLOAD_ERR_OK) {
    out(['error' => 'No PDF file uploaded or upload error'], 400);
}

$file = $_FILES['pdf_file'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if ($ext !== 'pdf') out(['error' => 'Only PDF files are allowed'], 400);

// Validate file size (20MB max)
if ($file['size'] > 20 * 1024 * 1024) {
    out(['error' => 'PDF file too large (max 20MB)'], 400);
}

// Validate MIME type
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);
if ($mime !== 'application/pdf') {
    out(['error' => 'Invalid file type. Only PDF files are accepted.'], 400);
}

$safeName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $testName);

switch ($action) {
    case 'upload_pdf':
        $pdfFilename = $safeName . '.pdf';
        break;
    case 'upload_solution_pdf':
        $pdfFilename = $safeName . '_solution.pdf';
        break;
    default:
        out(['error' => 'Unknown action. Use upload_pdf or upload_solution_pdf'], 400);
}

$destPath = OMR_DATA_DIR . $pdfFilename;

if (!move_uploaded_file($file['tmp_name'], $destPath)) {
    out(['error' => 'Failed to save PDF file'], 500);
}

out([
    'success'  => true,
    'pdf_file' => $pdfFilename,
    'pdf_url'  => 'wp-content/omr-data/' . $pdfFilename,
]);
