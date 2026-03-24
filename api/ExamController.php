<?php
// api/ExamController.php

class ExamController {
    public function updateAnswer($input) {
        $roomId = trim($input['room_id'] ?? '');
        $code = strtoupper(trim($input['player_id'] ?? ''));
        $qid = trim($input['q_id'] ?? '');
        $answer = $input['answer'] !== null ? strtolower(trim($input['answer'])) : null;
        $marked = isset($input['marked']) ? (bool)$input['marked'] : null;
        $skipped = isset($input['skipped']) ? (bool)$input['skipped'] : null;

        if (!$roomId || !$code || !$qid) jsonOut(['error' => 'Missing params'], 400);

        $index = loadCodesIndex();
        if (!isset($index[$code])) jsonOut(['error' => 'Invalid code'], 400);
        $pidx = $index[$code]['player_idx'];

        $error = null;
        $lockedObj = null;

        $success = updateRoom($roomId, function(&$room) use ($pidx, $qid, $answer, $marked, $skipped, $input, &$error, &$lockedObj) {
            if ($room['status'] !== 'active') {
                $error = 'Test not active';
                return false;
            }
            if (!isset($room['players'][$pidx])) {
                $error = 'Player not found';
                return false;
            }

            if (!empty($room['players'][$pidx]['locked_answers'][$qid])) {
                $lockedObj = true;
                return true; 
            }

            if ($answer !== null && $answer !== '') {
                if (!in_array($answer, ['a', 'b', 'c', 'd'], true)) {
                    $error = 'Invalid answer. Must be a, b, c, or d';
                    return false;
                }
            }

            if ($answer !== null) {
                $room['players'][$pidx]['answers'][$qid] = ($answer === '') ? null : $answer;
                if ($answer !== '') {
                    $room['players'][$pidx]['skipped'][$qid] = false;
                }
            }
            if ($marked !== null) $room['players'][$pidx]['marked'][$qid] = $marked;
            if ($skipped !== null) {
                $room['players'][$pidx]['skipped'][$qid] = $skipped;
                if ($skipped) $room['players'][$pidx]['answers'][$qid] = null;
            }

            $room['players'][$pidx]['online_at'] = time();
            if (!empty($input['current_q'])) {
                $room['players'][$pidx]['current_question'] = trim($input['current_q']);
            }
            return true;
        });

        if (!$success) jsonOut(['error' => 'Room not found'], 404);
        if ($error) jsonOut(['error' => $error], 400);
        if ($lockedObj) jsonOut(['success' => true, 'ts' => time(), 'locked' => true]);

        jsonOut(['success' => true, 'ts' => time()]);
    }

    public function updateCurrentQ($input) {
        $roomId = trim($input['room_id'] ?? '');
        $code = strtoupper(trim($input['player_id'] ?? ''));
        $currentQ = trim($input['current_q'] ?? '');

        if (!$roomId || !$code || !$currentQ) jsonOut(['error' => 'Missing params'], 400);

        $index = loadCodesIndex();
        if (!isset($index[$code])) jsonOut(['error' => 'Invalid code'], 400);
        $pidx = $index[$code]['player_idx'];

        $success = updateRoom($roomId, function(&$room) use ($pidx, $currentQ) {
            if (!isset($room['players'][$pidx])) return false;
            $room['players'][$pidx]['current_question'] = $currentQ;
            $room['players'][$pidx]['online_at'] = time();
            return true;
        });

        if (!$success) jsonOut(['error' => 'Room or player not found'], 404);
        jsonOut(['success' => true]);
    }

    public function revealAnswer($input) {
        $roomId = trim($input['room_id'] ?? '');
        $code = strtoupper(trim($input['player_id'] ?? ''));
        $qid = trim($input['q_id'] ?? '');
        $level = intval($input['level'] ?? 1);

        if (!$roomId || !$qid) jsonOut(['error' => 'Missing params'], 400);

        $index = loadCodesIndex();
        $requesterIdx = -1;
        if ($code && isset($index[$code])) {
            $requesterIdx = $index[$code]['player_idx'];
        }

        $mode = 'voting';
        $revealedLevel = null;
        
        $success = updateRoom($roomId, function(&$room) use ($qid, $level, $requesterIdx, &$mode, &$revealedLevel) {
            $playerCount = count($room['players']);
            if ($playerCount <= 1) {
                $current = $room['revealed'][$qid] ?? 0;
                if ($level > $current) {
                    $room['revealed'][$qid] = $level;
                }
                $mode = 'instant';
                $revealedLevel = $room['revealed'][$qid];
            } else {
                $room['pending_reveal'] = [
                    'qid' => $qid,
                    'level' => $level,
                    'requester_idx' => $requesterIdx,
                    'requester_name' => $room['players'][$requesterIdx]['name'] ?? 'Player',
                    'requested_at' => time(),
                    'votes' => [],
                ];
            }
            return true;
        });

        if (!$success) jsonOut(['error' => 'Room not found'], 404);

        if ($mode === 'instant') {
            jsonOut(['success' => true, 'revealed_level' => $revealedLevel, 'mode' => 'instant']);
        }
        jsonOut(['success' => true, 'mode' => 'voting', 'qid' => $qid]);
    }

    public function respondReveal($input) {
        $roomId = trim($input['room_id'] ?? '');
        $code = strtoupper(trim($input['player_id'] ?? ''));
        $accept = !empty($input['accept']);

        if (!$roomId || !$code) jsonOut(['error' => 'Missing params'], 400);

        $index = loadCodesIndex();
        $voterIdx = -1;
        if (isset($index[$code])) {
            $voterIdx = $index[$code]['player_idx'];
        }
        if ($voterIdx < 0) jsonOut(['error' => 'Invalid player'], 400);

        $error = null;
        $resultStr = 'waiting';

        $success = updateRoom($roomId, function(&$room) use ($voterIdx, $accept, &$error, &$resultStr) {
            $pending = $room['pending_reveal'] ?? null;
            if (!$pending) {
                $error = 'No pending reveal request';
                return false;
            }

            $room['pending_reveal']['votes'][$voterIdx] = $accept;

            if (!$accept) {
                $room['pending_reveal'] = null;
                $resultStr = 'rejected';
                return true;
            }

            $requesterIdx = $pending['requester_idx'];
            $allVoted = true;
            $allAccepted = true;
            foreach ($room['players'] as $i => $p) {
                if ($i == $requesterIdx) continue;
                if (!isset($room['pending_reveal']['votes'][$i])) {
                    $allVoted = false;
                    break;
                }
                if (!$room['pending_reveal']['votes'][$i]) {
                    $allAccepted = false;
                }
            }

            if ($allVoted && $allAccepted) {
                $qid = $pending['qid'];
                $level = $pending['level'];
                $current = $room['revealed'][$qid] ?? 0;
                if ($level > $current) {
                    $room['revealed'][$qid] = $level;
                }
                $room['pending_reveal'] = null;
                $resultStr = 'revealed';
            }
            return true;
        });

        if (!$success) jsonOut(['error' => 'Room not found'], 404);
        if ($error) jsonOut(['error' => $error], 400);

        jsonOut(['success' => true, 'result' => $resultStr]);
    }

    public function startTest($input) {
        $roomId = trim($input['room_id'] ?? '');
        $code = strtoupper(trim($input['player_id'] ?? ''));
        if (!$roomId) jsonOut(['error' => 'Missing room_id'], 400);

        $error = null;
        $startedAt = null;

        $success = updateRoom($roomId, function(&$room) use ($code, &$error, &$startedAt) {
            if ($room['status'] !== 'waiting') {
                $error = 'Test already started or ended';
                return false;
            }
            // Verify requester is the host (player index 0)
            $isHost = false;
            foreach ($room['players'] as $i => $p) {
                if (($p['code'] ?? '') === $code && $i === 0) {
                    $isHost = true;
                    break;
                }
            }
            if (!$isHost) {
                $error = 'Only the host can start the test';
                return false;
            }
            $room['status'] = 'active';
            $room['started_at'] = time();
            $startedAt = $room['started_at'];
            return true;
        });

        if (!$success) jsonOut(['error' => 'Room not found'], 404);
        if ($error) jsonOut(['error' => $error], 400);

        jsonOut(['success' => true, 'started_at' => $startedAt]);
    }

    public function sync($input) {
        $roomId = trim($input['room_id'] ?? '');
        $code = strtoupper(trim($input['player_id'] ?? ''));
        $sessionId = $input['session_id'] ?? '';
        $msid = $input['msid'] ?? '';
        if (!$roomId) jsonOut(['error' => 'Missing room_id'], 400);

        $index = loadCodesIndex();
        $pidx = -1;
        if ($code && isset($index[$code])) {
            $pidx = $index[$code]['player_idx'];
        }

        $now = time();
        $needsWrite = false;
        $superseded = false;
        
        $claimMsid = !empty($input['claim_msid']);
        
        $room = loadRoom($roomId);
        if (!$room) jsonOut(['error' => 'Room not found'], 404);

        if ($pidx >= 0 && isset($room['players'][$pidx])) {
            $lastOnline = $room['players'][$pidx]['online_at'] ?? 0;
            // Write optimization: only write ping if 10 seconds passed
            if ($now - $lastOnline > 10) {
                $needsWrite = true;
            }
            if ($sessionId && ($room['players'][$pidx]['active_session'] ?? '') !== $sessionId) {
                $needsWrite = true;
            }
            if ($msid) {
                if (!$claimMsid && isset($room['players'][$pidx]['active_msid']) && $room['players'][$pidx]['active_msid'] !== $msid) {
                    $superseded = true;
                }
                if ($claimMsid || ($room['players'][$pidx]['active_msid'] ?? '') !== $msid) {
                    $needsWrite = true;
                }
            }
        }

        if ($superseded) {
            jsonOut(['error' => 'superseded'], 400);
        }

        if ($room['timer_mode'] === 'countdown' && $room['started_at'] && $room['status'] === 'active') {
            $elapsed = $now - $room['started_at'];
            if ($elapsed >= $room['duration_sec']) {
                $needsWrite = true;
            }
        }

        if ($needsWrite) {
            updateRoom($roomId, function(&$r) use ($pidx, $sessionId, $msid, $now, $claimMsid) {
                
                // Active garbage collection across the board for dropped mobile handovers
                // Mobile sessions expire after 30 minutes max, or 12s without ping
                foreach ($r['players'] as $i => $p) {
                    if (!empty($p['active_msid'])) {
                        $msidAge = $now - ($p['msid_ping'] ?? 0);
                        $msidCreated = $p['msid_created'] ?? ($p['msid_ping'] ?? 0);
                        $msidTotalAge = $now - $msidCreated;
                        // Prune if no ping for 12s OR session older than 30 minutes
                        if ($msidAge > 12 || $msidTotalAge > 1800) {
                            unset($r['players'][$i]['active_msid']);
                            unset($r['players'][$i]['msid_ping']);
                            unset($r['players'][$i]['msid_created']);
                        }
                    }
                }

                // Server-side timeout enforcement for pending_reveal (15s max)
                if (!empty($r['pending_reveal'])) {
                    $revealAge = $now - ($r['pending_reveal']['requested_at'] ?? 0);
                    if ($revealAge > 15) {
                        $r['pending_reveal'] = null;
                    }
                }

                if ($pidx >= 0 && isset($r['players'][$pidx])) {
                    $r['players'][$pidx]['online_at'] = $now;
                    if ($sessionId) {
                        $r['players'][$pidx]['active_session'] = $sessionId;
                    }
                    if ($msid) {
                        if (!$claimMsid && isset($r['players'][$pidx]['active_msid']) && $r['players'][$pidx]['active_msid'] !== $msid) {
                            return false; // Error handled above before write
                        }
                        $r['players'][$pidx]['active_msid'] = $msid;
                        $r['players'][$pidx]['msid_ping'] = $now;
                        if (empty($r['players'][$pidx]['msid_created'])) {
                            $r['players'][$pidx]['msid_created'] = $now;
                        }
                    }
                }
                if ($r['timer_mode'] === 'countdown' && $r['started_at'] && $r['status'] === 'active') {
                    $elapsed = $now - $r['started_at'];
                    if ($elapsed >= $r['duration_sec']) {
                        $r['status'] = 'finished';
                        $r['ended_at'] = $now;
                        foreach ($r['players'] as $i => $p) {
                            $r['players'][$i]['submitted'] = true;
                        }
                    }
                }
                return true;
            });
            $room = loadRoom($roomId);
        }

        $elapsed = $room['started_at'] ? ($now - $room['started_at']) : 0;
        $timeRemaining = null;
        if ($room['timer_mode'] === 'countdown' && $room['started_at']) {
            $timeRemaining = max(0, $room['duration_sec'] - $elapsed);
        }

        $onlineStatus = [];
        foreach ($room['players'] as $p) {
            $onlineStatus[$p['idx']] = ($now - ($p['online_at'] ?? 0)) < 15;
        }

        $summary = [
            'room_id' => $room['room_id'],
            'test_name' => $room['test_name'],
            'test_info' => $room['test_info'],
            'questions' => $room['questions'],
            'question_texts' => $room['question_texts'] ?? [],
            'options' => $room['options'] ?? [],
            'status' => $room['status'],
            'timer_mode' => $room['timer_mode'],
            'duration_sec' => $room['duration_sec'],
            'started_at' => $room['started_at'],
            'elapsed_sec' => $elapsed,
            'time_remaining' => $timeRemaining,
            'player_count' => $room['player_count'],
            'revealed' => $room['revealed'],
            'online' => $onlineStatus,
            'chat' => array_slice($room['chat'] ?? [], -20),
            'last_updated' => $room['last_updated'],
            'pdf_url' => $room['pdf_url'] ?? null,
            'solution_pdf_url' => $room['solution_pdf_url'] ?? null,
            'page_map' => $room['page_map'] ?? null,
            'pending_reveal' => $room['pending_reveal'] ?? null,
            'paused_until' => $room['paused_until'] ?? 0,
            'total_paused_sec' => $room['total_paused_sec'] ?? 0,
            'brb_used' => $room['brb_used'] ?? [],
            'call_active' => $room['call_active'] ?? null,
            'players' => [],
        ];

        foreach ($room['players'] as $i => $p) {
            $summary['players'][$i] = [
                'idx' => $p['idx'],
                'name' => $p['name'],
                'code' => $p['code'],
                'joined' => $p['joined'],
                'submitted' => $p['submitted'],
                'answers' => $p['answers'],
                'marked' => $p['marked'],
                'skipped' => $p['skipped'],
                'current_question' => $p['current_question'] ?? null,
                'locked_answers' => $p['locked_answers'] ?? [],
            ];
        }

        if ($room['status'] === 'finished') {
            $summary['answer_key'] = $room['answer_key'];
        }
        
        $summary['answer_key_partial'] = [];
        foreach ($room['revealed'] as $qid => $level) {
            if ($level >= 1) {
                $summary['answer_key_partial'][$qid] = $room['answer_key'][$qid] ?? null;
            }
        }

        jsonOut($summary);
    }

    public function sendMessage($input) {
        $roomId = trim($input['room_id'] ?? '');
        $code = strtoupper(trim($input['player_id'] ?? ''));
        $message = trim($input['message'] ?? '');

        if (!$roomId || !$message) jsonOut(['error' => 'Missing params'], 400);

        $index = loadCodesIndex();
        
        $success = updateRoom($roomId, function(&$room) use ($code, $message, $index) {
            $name = 'Player';
            if ($code && isset($index[$code])) {
                $pidx = $index[$code]['player_idx'];
                $name = $room['players'][$pidx]['name'] ?? 'Player';
            }

            $room['chat'][] = [
                'from' => $name,
                'msg' => substr(htmlspecialchars($message, ENT_QUOTES), 0, 200),
                'ts' => time(),
            ];

            if (count($room['chat']) > 50) {
                $room['chat'] = array_slice($room['chat'], -50);
            }
            return true;
        });

        if (!$success) jsonOut(['error' => 'Room not found'], 404);
        jsonOut(['success' => true]);
    }

    public function brb($input) {
        $roomId = trim($input['room_id'] ?? '');
        $code = strtoupper(trim($input['player_id'] ?? ''));
        if (!$roomId || !$code) jsonOut(['error' => 'Missing params'], 400);

        $index = loadCodesIndex();
        $error = null;
        $pausedUntil = 0;

        $success = updateRoom($roomId, function(&$room) use ($code, $index, &$error, &$pausedUntil) {
            $pidx = -1;
            $name = 'Player';
            if (isset($index[$code])) {
                $pidx = $index[$code]['player_idx'];
                $name = $room['players'][$pidx]['name'] ?? 'Player';
            }

            $brbUsed = $room['brb_used'] ?? [];
            if (!empty($brbUsed[$pidx])) {
                $error = 'You already used your BRB break';
                return false;
            }

            $now = time();
            if (($room['paused_until'] ?? 0) > $now) {
                $error = 'Test is already paused';
                return false;
            }

            // Only allow BRB when test is actively running
            if (($room['status'] ?? '') !== 'active') {
                $error = 'Test is not active';
                return false;
            }

            $room['paused_until'] = $now + 300;
            $pausedUntil = $room['paused_until'];
            $room['total_paused_sec'] = ($room['total_paused_sec'] ?? 0) + 300;
            $room['brb_used'][$pidx] = true;

            $room['chat'][] = [
                'from' => '⏸ System',
                'msg' => htmlspecialchars($name, ENT_QUOTES) . ' used BRB — test paused for 5 minutes',
                'ts' => $now,
            ];
            return true;
        });

        if (!$success) jsonOut(['error' => 'Room not found'], 404);
        if ($error) jsonOut(['error' => $error], 400);

        jsonOut(['success' => true, 'paused_until' => $pausedUntil]);
    }
}
