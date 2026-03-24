<?php
// api/TestController.php

class TestController {
    public function checkTest($input) {
        $testName = trim($input['test_name'] ?? '');
        if (!$testName) jsonOut(['exists' => false]);
        $path = testPath($testName);
        if (file_exists($path)) {
            $data = json_decode(file_get_contents($path), true);
            jsonOut([
                'exists' => true,
                'q_count' => count($data['responses'] ?? []),
                'page_map' => $data['page_map'] ?? null,
            ]);
        }
        jsonOut(['exists' => false]);
    }

    public function saveTest($input) {
        $testName = trim($input['test_name'] ?? '');
        $jsonData = $input['json_data'] ?? null;

        if (!$testName) jsonOut(['error' => 'Test name required'], 400);
        if (!$jsonData || !isset($jsonData['responses']) || !is_array($jsonData['responses']) || count($jsonData['responses']) === 0) {
            jsonOut(['error' => 'Valid JSON with "responses" object required'], 400);
        }

        // Limit JSON data size to prevent abuse (1MB max when re-encoded)
        $encoded = json_encode($jsonData, JSON_UNESCAPED_UNICODE);
        if (strlen($encoded) > 1048576) {
            jsonOut(['error' => 'Test data too large (max 1MB)'], 400);
        }

        $path = testPath($testName);
        // Atomic write: write to temp then rename
        $tmp = $path . '.tmp';
        file_put_contents($tmp, json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        rename($tmp, $path);

        jsonOut([
            'success' => true,
            'name' => $testName,
            'q_count' => count($jsonData['responses']),
        ]);
    }

    public function listTests() {
        $tests = [];
        $dir = OMR_DATA_DIR;
        if (!is_dir($dir)) jsonOut(['tests' => []]);

        foreach (glob($dir . '*.json') as $file) {
            $basename = basename($file);
            if (strpos($basename, 'room_') === 0) continue;
            if ($basename === '_codes_index.json') continue;

            $data = json_decode(file_get_contents($file), true);
            if (!$data || !isset($data['responses'])) continue;

            $testName = pathinfo($basename, PATHINFO_FILENAME);
            $tests[] = [
                'name' => $testName,
                'filename' => $basename,
                'q_count' => count($data['responses']),
                'test_info' => $data['test_info'] ?? [],
                'has_pdf' => !empty($data['pdf_file']),
                'has_solution_pdf' => !empty($data['solution_pdf_file']),
                'has_page_map' => !empty($data['page_map']),
                'created_at' => filemtime($file),
            ];
        }
        usort($tests, function ($a, $b) { return $b['created_at'] - $a['created_at']; });
        jsonOut(['tests' => $tests]);
    }

    public function updateTestTag($input) {
        $testName = trim($input['test_name'] ?? '');
        $newTag = trim($input['new_tag'] ?? '');

        if (!$testName || !$newTag) jsonOut(['error' => 'Test name and new tag required'], 400);

        $testFile = testPath($testName);
        if (!file_exists($testFile)) jsonOut(['error' => 'Test not found'], 404);

        $data = json_decode(file_get_contents($testFile), true);
        if (!$data) jsonOut(['error' => 'Failed to read test data'], 500);

        if (!isset($data['test_info'])) {
            $data['test_info'] = [];
        }
        $data['test_info']['tag'] = $newTag;
        
        file_put_contents($testFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        jsonOut([
            'success' => true,
            'test_name' => $testName,
            'new_tag' => $newTag
        ]);
    }

    public function uploadPdf($input) {
        $testName = trim($input['test_name'] ?? '');
        if (!$testName) jsonOut(['error' => 'Test name required'], 400);

        if (!isset($_FILES['pdf_file']) || $_FILES['pdf_file']['error'] !== UPLOAD_ERR_OK) {
            jsonOut(['error' => 'No PDF file uploaded or upload error'], 400);
        }

        $file = $_FILES['pdf_file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'pdf') jsonOut(['error' => 'Only PDF files are allowed'], 400);

        // Validate file size (20MB max)
        if ($file['size'] > 20 * 1024 * 1024) {
            jsonOut(['error' => 'PDF file too large (max 20MB)'], 400);
        }

        // Validate MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        if ($mime !== 'application/pdf') {
            jsonOut(['error' => 'Invalid file type. Only PDF files are accepted.'], 400);
        }

        $safeName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $testName);
        $pdfFilename = $safeName . '.pdf';
        $destPath = OMR_DATA_DIR . $pdfFilename;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            jsonOut(['error' => 'Failed to save PDF file'], 500);
        }

        $testFile = testPath($testName);
        if (file_exists($testFile)) {
            $data = json_decode(file_get_contents($testFile), true);
            if ($data) {
                $data['pdf_file'] = $pdfFilename;
                unset($data['page_map']);
                file_put_contents($testFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }
        }

        jsonOut([
            'success' => true,
            'pdf_file' => $pdfFilename,
            'pdf_url' => 'wp-content/omr-data/' . $pdfFilename,
        ]);
    }

    public function uploadSolutionPdf($input) {
        $testName = trim($input['test_name'] ?? '');
        if (!$testName) jsonOut(['error' => 'Test name required'], 400);

        if (!isset($_FILES['pdf_file']) || $_FILES['pdf_file']['error'] !== UPLOAD_ERR_OK) {
            jsonOut(['error' => 'No PDF file uploaded or upload error'], 400);
        }

        $file = $_FILES['pdf_file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'pdf') jsonOut(['error' => 'Only PDF files are allowed'], 400);

        // Validate file size (20MB max)
        if ($file['size'] > 20 * 1024 * 1024) {
            jsonOut(['error' => 'Solution PDF too large (max 20MB)'], 400);
        }

        // Validate MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        if ($mime !== 'application/pdf') {
            jsonOut(['error' => 'Invalid file type. Only PDF files are accepted.'], 400);
        }

        $safeName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $testName);
        $pdfFilename = $safeName . '_solution.pdf';
        $destPath = OMR_DATA_DIR . $pdfFilename;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            jsonOut(['error' => 'Failed to save solution PDF file'], 500);
        }

        $testFile = testPath($testName);
        if (file_exists($testFile)) {
            $data = json_decode(file_get_contents($testFile), true);
            if ($data) {
                $data['solution_pdf_file'] = $pdfFilename;
                file_put_contents($testFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }
        }

        jsonOut([
            'success' => true,
            'pdf_file' => $pdfFilename,
            'pdf_url' => 'wp-content/omr-data/' . $pdfFilename,
        ]);
    }

    public function savePageMap($input) {
        $testName = trim($input['test_name'] ?? '');
        $pageMap = $input['page_map'] ?? null;

        if (!$testName) jsonOut(['error' => 'Test name required'], 400);
        if (!$pageMap || !is_array($pageMap)) jsonOut(['error' => 'page_map must be an object'], 400);

        $testFile = testPath($testName);
        if (!file_exists($testFile)) jsonOut(['error' => 'Test not found'], 404);

        $data = json_decode(file_get_contents($testFile), true);
        if (!$data) jsonOut(['error' => 'Failed to read test data'], 500);

        // Validate page_map structure
        foreach ($pageMap as $pageNum => $mapping) {
            if (!is_array($mapping)) {
                jsonOut(['error' => 'Invalid page_map: each entry must be an object'], 400);
            }
        }

        $data['page_map'] = $pageMap;
        $tmp = $testFile . '.tmp';
        file_put_contents($tmp, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        rename($tmp, $testFile);

        jsonOut([
            'success' => true,
            'test_name' => $testName,
            'pages_mapped' => count($pageMap),
        ]);
    }
}
