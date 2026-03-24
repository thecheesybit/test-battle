<?php
// api/PlayerController.php

class PlayerController {
    public function validateCode($input) {
        $code = strtoupper(trim($input['code'] ?? ''));
        if (!$code) jsonOut(['valid' => false, 'error' => 'No code provided']);

        $index = loadCodesIndex();
        if (!isset($index[$code])) jsonOut(['valid' => false, 'error' => 'Code not found. Check and try again.']);

        $entry = $index[$code];
        $roomId = $entry['room_id'];
        $room = loadRoom($roomId);
        if (!$room) jsonOut(['valid' => false, 'error' => 'Room not found or expired.']);
        if ($room['status'] === 'finished') jsonOut(['valid' => false, 'error' => 'This test has already ended.']);

        $pidx = $entry['player_idx'];
        $player = $room['players'][$pidx] ?? [];
        
        $sessionId = $input['session_id'] ?? '';
        $isActive = $player['joined'] ?? false;
        $timeSinceOnline = time() - ($player['online_at'] ?? 0);
        $activeSession = $player['active_session'] ?? '';

        if ($isActive && $timeSinceOnline < 15 && $activeSession && $sessionId && $activeSession !== $sessionId) {
            jsonOut(['valid' => false, 'error' => 'This player code is currently active in another browser window.']);
        }

        jsonOut([
            'valid' => true,
            'room_id' => $roomId,
            'player_id' => $code,
            'player_idx' => $pidx,
            'player_name' => $room['players'][$pidx]['name'] ?? 'Player',
        ]);
    }

    public function playerJoin($input) {
        $code = strtoupper(trim($input['player_id'] ?? ''));
        $roomId = trim($input['room_id'] ?? '');
        if (!$code || !$roomId) jsonOut(['error' => 'Missing player_id or room_id'], 400);

        $index = loadCodesIndex();
        if (!isset($index[$code])) jsonOut(['error' => 'Invalid player code'], 400);

        $pidx = $index[$code]['player_idx'];
        $sessionId = $input['session_id'] ?? '';
        
        $error = null;
        $success = updateRoom($roomId, function(&$room) use ($pidx, $sessionId, &$error) {
            $player = $room['players'][$pidx] ?? [];
            
            $isActive = $player['joined'] ?? false;
            $timeSinceOnline = time() - ($player['online_at'] ?? 0);
            $activeSession = $player['active_session'] ?? '';

            if ($isActive && $timeSinceOnline < 15 && $activeSession && $sessionId && $activeSession !== $sessionId) {
                $error = 'Player slot is currently active in another window.';
                return false;
            }

            $room['players'][$pidx]['joined'] = true;
            $room['players'][$pidx]['online_at'] = time();
            if ($sessionId) {
                $room['players'][$pidx]['active_session'] = $sessionId;
            }
            return true;
        });

        if (!$success) jsonOut(['error' => 'Room not found'], 404);
        if ($error) jsonOut(['error' => $error], 400);

        $room = loadRoom($roomId);
        $allJoined = true;
        foreach ($room['players'] as $p) {
            if (!$p['joined']) {
                $allJoined = false;
                break;
            }
        }

        jsonOut([
            'success' => true,
            'all_joined' => $allJoined,
            'player_idx' => $pidx,
            'player_name' => $room['players'][$pidx]['name'],
            'room_status' => $room['status'],
        ]);
    }

    public function submitPlayer($input) {
        $roomId = trim($input['room_id'] ?? '');
        $code = strtoupper(trim($input['player_id'] ?? ''));
        if (!$roomId || !$code) jsonOut(['error' => 'Missing params'], 400);

        $index = loadCodesIndex();
        if (!isset($index[$code])) jsonOut(['error' => 'Invalid code'], 400);
        $pidx = $index[$code]['player_idx'];

        $success = updateRoom($roomId, function(&$room) use ($pidx) {
            $room['players'][$pidx]['submitted'] = true;
            $allDone = true;
            foreach ($room['players'] as $p) {
                if (!$p['submitted']) {
                    $allDone = false;
                    break;
                }
            }
            if ($allDone) {
                $room['status'] = 'finished';
                $room['ended_at'] = time();
            }
            return true;
        });

        if (!$success) jsonOut(['error' => 'Room not found'], 404);
        
        $room = loadRoom($roomId);
        
        jsonOut([
            'success' => true,
            'all_done' => $room['status'] === 'finished',
            'submitted' => array_column($room['players'], 'submitted'),
        ]);
    }

    public function startReattempt($input) {
        $roomId = trim($input['room_id'] ?? '');
        $code = strtoupper(trim($input['player_id'] ?? ''));
        if (!$roomId || !$code) jsonOut(['error' => 'Missing params'], 400);

        $index = loadCodesIndex();
        if (!isset($index[$code])) jsonOut(['error' => 'Invalid code'], 400);
        $pidx = $index[$code]['player_idx'];

        $success = updateRoom($roomId, function(&$room) use ($pidx) {
            if ($room['status'] === 'finished') {
                $room['status'] = 'waiting';
                $room['started_at'] = null;
                $room['ended_at'] = null;
                $room['reattempt_active'] = true;
                $room['reattempt_expiry'] = time() + (7 * 24 * 3600);
                $room['pending_reveal'] = null;
                $room['revealed'] = [];
                
                foreach ($room['players'] as $i => $player) {
                    $room['players'][$i]['joined'] = false;
                    $room['players'][$i]['submitted'] = false;
                    
                    $locked = $room['players'][$i]['locked_answers'] ?? [];
                    foreach ($room['questions'] as $q) {
                        if (($player['answers'][$q] ?? null) !== null) {
                            $locked[$q] = $player['answers'][$q];
                        }
                    }
                    $room['players'][$i]['locked_answers'] = $locked;
                }
            }
            
            $room['players'][$pidx]['joined'] = true;
            $room['players'][$pidx]['online_at'] = time();
            return true;
        });

        if (!$success) jsonOut(['error' => 'Room not found'], 404);
        jsonOut(['success' => true]);
    }
}
