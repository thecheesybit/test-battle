<?php
// migrate.php — One-time migration tool: JSON files → Firestore
// Protected: requires Firebase auth (client-side) + admin confirmation

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/helpers.php';

// API endpoint: list all tests for migration
if (isset($_GET['api']) && $_GET['api'] === 'list_tests') {
    header('Content-Type: application/json');
    $files  = glob(OMR_DATA_DIR . '*.json') ?: [];
    $tests  = [];
    foreach ($files as $f) {
        $base = basename($f, '.json');
        if (str_starts_with($base, 'room_') || str_starts_with($base, '_')) continue;
        $data = json_decode(file_get_contents($f), true);
        if (!$data || !isset($data['responses'])) continue;
        $tests[] = [
            'name'          => $base,
            'title'         => $data['test_info']['title']   ?? $base,
            'subject'       => $data['test_info']['subject']  ?? '',
            'tag'           => $data['test_info']['tag']      ?? 'General',
            'question_count'=> count($data['responses'] ?? []),
            'has_questions' => !empty($data['questions']),
            'has_options'   => !empty($data['options']),
            'has_pdf'       => !empty($data['pdf_url']),
            'has_solution'  => !empty($data['solution_pdf_url']),
            'has_page_map'  => !empty($data['page_map']),
            'answer_key'    => $data['responses']     ?? [],
            'question_texts'=> $data['questions']     ?? [],
            'options'       => $data['options']       ?? [],
            'pdf_url'       => $data['pdf_url']       ?? null,
            'solution_pdf_url' => $data['solution_pdf_url'] ?? null,
            'page_map'      => $data['page_map']      ?? [],
        ];
    }
    echo json_encode(['tests' => $tests, 'total' => count($tests)]);
    exit;
}

require_once __DIR__ . '/views/migrate.view.php';
