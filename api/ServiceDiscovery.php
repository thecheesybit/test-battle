<?php
// api/ServiceDiscovery.php

class ServiceDiscovery {
    public static $registry = [
        // Room
        'create_room'         => ['RoomController', 'createRoom'],
        'get_room'            => ['RoomController', 'getRoom'],
        'check_recent_rooms'  => ['RoomController', 'checkRecentRooms'],
        'update_call_state'   => ['RoomController', 'updateCallState'],
        'cleanup'             => ['RoomController', 'cleanup'],
        
        // Player
        'validate_code'       => ['PlayerController', 'validateCode'],
        'player_join'         => ['PlayerController', 'playerJoin'],
        'submit_player'       => ['PlayerController', 'submitPlayer'],
        'start_reattempt'     => ['PlayerController', 'startReattempt'],

        // Test
        'check_test'          => ['TestController', 'checkTest'],
        'save_test'           => ['TestController', 'saveTest'],
        'list_tests'          => ['TestController', 'listTests'],
        'update_test_tag'     => ['TestController', 'updateTestTag'],
        'upload_pdf'          => ['TestController', 'uploadPdf'],
        'upload_solution_pdf' => ['TestController', 'uploadSolutionPdf'],
        'save_page_map'       => ['TestController', 'savePageMap'],

        // Exam
        'update_answer'       => ['ExamController', 'updateAnswer'],
        'reveal_answer'       => ['ExamController', 'revealAnswer'],
        'respond_reveal'      => ['ExamController', 'respondReveal'],
        'start_test'          => ['ExamController', 'startTest'],
        'sync'                => ['ExamController', 'sync'],
        'update_current_q'    => ['ExamController', 'updateCurrentQ'],
        'brb'                 => ['ExamController', 'brb'],
        'send_message'        => ['ExamController', 'sendMessage'],
        
        // Discovery
        'discover'            => ['ServiceDiscovery', 'discover'],
    ];

    public static function route($action, $input) {
        if (!isset(self::$registry[$action])) {
            jsonOut(['error' => 'Unknown action: ' . $action], 400);
        }

        [$controllerClass, $method] = self::$registry[$action];

        if ($controllerClass === 'ServiceDiscovery') {
            (new self())->$method($input);
            return;
        }

        require_once __DIR__ . '/' . $controllerClass . '.php';
        $controller = new $controllerClass();
        $controller->$method($input);
    }

    public function discover($input) {
        // Return external representation — hide internal class/method details
        $actions = array_keys(self::$registry);
        // Remove discover itself from the list
        $actions = array_values(array_filter($actions, fn($a) => $a !== 'discover'));
        jsonOut([
            'success' => true,
            'description' => 'MiniShiksha OMR API',
            'version' => '3.0',
            'actions' => $actions,
            'method'  => 'POST',
            'endpoint' => 'api.php',
        ]);
    }
}
