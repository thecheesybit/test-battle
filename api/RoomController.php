<?php
// api/RoomController.php

class RoomController {
    
    public function createRoom($input) {
        $this->autoCleanupIfDue();
        
        $testName = trim($input['test_name'] ?? '');
        $jsonData = $input['json_data'] ?? null;
        $timerMode = $input['timer_mode'] ?? 'countdown';
        $durationMin = intval($input['duration_minutes'] ?? 120);
        $playerCount = intval($input['player_count'] ?? 1);
        $playerNames = $input['player_names'] ?? [];

        if (!$testName) jsonOut(['error' => 'Test name required'], 400);
        if ($playerCount < 1 || $playerCount > 4) jsonOut(['error' => 'Player count must be 1-4'], 400);

        $playerCount = max(1, min(4, $playerCount));
        for ($i = 0; $i < $playerCount; $i++) {
            if (empty($playerNames[$i])) $playerNames[$i] = 'Player ' . ($i + 1);
        }
        $playerNames = array_slice($playerNames, 0, $playerCount);

        $answerKey = null;
        if ($jsonData && isset($jsonData['responses'])) {
            $answerKey = $jsonData['responses'];
            $testFile = testPath($testName);
            file_put_contents($testFile, json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        } else {
            $testFile = testPath($testName);
            if (file_exists($testFile)) {
                $saved = json_decode(file_get_contents($testFile), true);
                $answerKey = $saved['responses'] ?? null;
                $jsonData = $saved;
            }
        }
        if (!$answerKey) jsonOut(['error' => 'No answer key found. Please paste the JSON data.'], 400);

        $normalised = [];
        foreach ($answerKey as $q => $ans) {
            $normalised[$q] = strtolower(trim($ans));
        }

        $questions = array_keys($normalised);
        usort($questions, function ($a, $b) {
            $na = intval(preg_replace('/\D/', '', $a));
            $nb = intval(preg_replace('/\D/', '', $b));
            return $na - $nb;
        });

        $roomId = generateRoomId();
        
        $indexLockPath = OMR_DATA_DIR . '.codes_lock';
        $lockFp = fopen($indexLockPath, 'w+');
        $playerCodes = [];
        if (flock($lockFp, LOCK_EX)) {
            $codesIndex = loadCodesIndex();
            for ($i = 0; $i < $playerCount; $i++) {
                $code = generateCode($codesIndex);
                $playerCodes[$i] = $code;
                $codesIndex[$code] = ['room_id' => $roomId, 'player_idx' => $i, 'created' => time()];
            }
            saveCodesIndex($codesIndex);
            flock($lockFp, LOCK_UN);
        }
        fclose($lockFp);

        if (empty($playerCodes) || count($playerCodes) !== $playerCount) {
            jsonOut(['error' => 'Failed to generate player codes. Please retry.'], 500);
        }

        $players = [];
        for ($i = 0; $i < $playerCount; $i++) {
            $players[$i] = [
                'idx' => $i,
                'name' => $playerNames[$i],
                'code' => $playerCodes[$i],
                'joined' => ($i === 0),
                'submitted' => false,
                'answers' => array_fill_keys($questions, null),
                'marked' => array_fill_keys($questions, false),
                'skipped' => array_fill_keys($questions, false),
                'online_at' => time(),
            ];
        }

        $pdfUrl = null;
        $pdfFile = $jsonData['pdf_file'] ?? null;
        if ($pdfFile && file_exists(OMR_DATA_DIR . $pdfFile)) {
            $pdfUrl = 'wp-content/omr-data/' . $pdfFile;
        }

        $solPdfUrl = null;
        $solPdfFile = $jsonData['solution_pdf_file'] ?? null;
        if ($solPdfFile && file_exists(OMR_DATA_DIR . $solPdfFile)) {
            $solPdfUrl = 'wp-content/omr-data/' . $solPdfFile;
        }

        $pageMap = $jsonData['page_map'] ?? null;

        $room = [
            'room_id' => $roomId,
            'test_name' => $testName,
            'test_info' => $jsonData['test_info'] ?? [],
            'questions' => $questions,
            'question_texts' => $jsonData['questions'] ?? [],
            'options' => $jsonData['options'] ?? [],
            'answer_key' => $normalised,
            'revealed' => [],
            'timer_mode' => $timerMode,
            'duration_sec' => $durationMin * 60,
            'started_at' => null,
            'ended_at' => null,
            'status' => 'waiting',
            'player_count' => $playerCount,
            'players' => $players,
            'chat' => [],
            'pdf_url' => $pdfUrl,
            'solution_pdf_url' => $solPdfUrl,
            'page_map' => $pageMap,
            'created_at' => time(),
            'last_updated' => time(),
        ];

        saveRoom($roomId, $room); 

        jsonOut([
            'success' => true,
            'room_id' => $roomId,
            'player_codes' => $playerCodes,
            'player_count' => $playerCount,
            'q_count' => count($questions),
            'join_url' => 'https://minishiksha.in/room.php',
        ]);
    }

    public function getRoom($input) {
        $roomId = trim($input['room_id'] ?? '');
        if (!$roomId) jsonOut(['error' => 'Missing room_id'], 400);
        $room = loadRoom($roomId);
        if (!$room) jsonOut(['error' => 'Room not found'], 404);
        
        if ($room['status'] !== 'finished') {
            unset($room['answer_key']);
        }
        jsonOut($room);
    }

    public function checkRecentRooms($input) {
        $codes = $input['codes'] ?? [];
        if (!is_array($codes) || empty($codes)) jsonOut(['success' => true, 'rooms' => []]);

        $index = loadCodesIndex();
        $activeRooms = [];

        foreach ($codes as $code) {
            $code = strtoupper(trim($code));
            if (isset($index[$code])) {
                $roomId = $index[$code]['room_id'];
                $pidx = $index[$code]['player_idx'];
                $room = loadRoom($roomId);
                
                if ($room) {
                    $player = $room['players'][$pidx] ?? null;
                    if ($player) {
                        $canReattempt = false;
                        if ($room['status'] === 'finished') {
                            $unattempted = 0;
                            foreach ($room['questions'] ?? [] as $q) {
                                if (($player['answers'][$q] ?? null) === null) {
                                    $unattempted++;
                                }
                            }
                            if ($unattempted > 0) $canReattempt = true;
                        }

                        if (empty($player['submitted']) || $canReattempt) {
                            $activeRooms[] = [
                                'code' => $code,
                                'room_id' => $roomId,
                                'test_name' => $room['test_name'],
                                'status' => $room['status'],
                                'player_name' => $player['name'],
                                'can_reattempt' => $canReattempt
                            ];
                        }
                    }
                }
            }
        }
        jsonOut(['success' => true, 'rooms' => $activeRooms]);
    }

    public function updateCallState($input) {
        $roomId = trim($input['room_id'] ?? '');
        $code = strtoupper(trim($input['player_id'] ?? ''));
        $active = !empty($input['active']);

        if (!$roomId || !$code) jsonOut(['error' => 'Missing params'], 400);

        // Validate player belongs to this room
        $index = loadCodesIndex();
        if (!isset($index[$code]) || $index[$code]['room_id'] !== $roomId) {
            jsonOut(['error' => 'Player does not belong to this room'], 403);
        }
        
        $success = updateRoom($roomId, function(&$room) use ($code, $active, $index, $roomId) {
            $callerIdx = $index[$code]['player_idx'];
            $callerName = $room['players'][$callerIdx]['name'] ?? 'Player';

            if ($active) {
                $room['call_active'] = [
                    'active'      => true,
                    'caller_idx'  => $callerIdx,
                    'caller_name' => $callerName,
                    'caller_code' => $code,
                    'call_id'     => 'omr-' . $roomId,
                    'started_at'  => time(),
                ];
            } else {
                $room['call_active'] = null;
            }
            return true;
        });

        if (!$success) jsonOut(['error' => 'Room not found'], 404);
        
        $room = loadRoom($roomId);
        jsonOut(['success' => true, 'call_active' => $room['call_active'] ?? null]);
    }

    public function cleanup() {
        $removed = $this->doCleanup();
        jsonOut(['cleaned' => $removed]);
    }

    private function autoCleanupIfDue() {
        $stampFile = OMR_DATA_DIR . '_last_cleanup.txt';
        $lastRun = file_exists($stampFile) ? (int)file_get_contents($stampFile) : 0;
        if (time() - $lastRun < CLEANUP_INTERVAL_SEC) return;
        file_put_contents($stampFile, (string)time());
        $this->doCleanup();
    }

    private function doCleanup() {
        $cutoff = time() - (ROOM_EXPIRY_HOURS * 3600);
        
        $indexLockPath = OMR_DATA_DIR . '.codes_lock';
        $lockFp = fopen($indexLockPath, 'w+');
        if (!flock($lockFp, LOCK_EX)) {
            fclose($lockFp);
            return 0;
        }

        $index = loadCodesIndex();
        $removed = 0;
        foreach (glob(OMR_DATA_DIR . 'room_*.json') as $file) {
            $data = json_decode(file_get_contents($file), true);
            if (!$data) {
                @unlink($file);
                continue;
            }
            if (($data['created_at'] ?? 0) < $cutoff) {
                // Don't cleanup active rooms — players may still be in an exam
                if (($data['status'] ?? '') === 'active') {
                    continue;
                }
                if (!empty($data['reattempt_active']) && !empty($data['reattempt_expiry'])) {
                    if (time() < $data['reattempt_expiry']) {
                        continue;
                    }
                }
                foreach ($data['players'] ?? [] as $p) {
                    $c = $p['code'] ?? '';
                    if ($c && isset($index[$c])) unset($index[$c]);
                }
                @unlink($file);
                $removed++;
            }
        }
        saveCodesIndex($index);
        flock($lockFp, LOCK_UN);
        fclose($lockFp);
        
        return $removed;
    }
}
