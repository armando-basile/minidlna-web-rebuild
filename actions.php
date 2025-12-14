<?php

header('Content-Type: application/json');

// Aggiungi error reporting per debug
error_reporting(E_ALL);
ini_set('display_errors', 0); // Non mostrare errori nel output
ini_set('log_errors', 1);

include_once "minidlna.php";

class Actions {
    
    public static function handleRequest() {
        try {
            $action = $_POST['action'] ?? $_GET['action'] ?? null;
            
            if (!$action) {
                self::sendError("No action specified");
                return;
            }
            
            switch ($action) {
                case 'getStatus':
                    self::getStatus();
                    break;
                    
                case 'rebuild':
                    self::rebuild();
                    break;
                    
                default:
                    self::sendError("Unknown action: $action");
            }
            
        } catch (Exception $e) {
            self::sendError($e->getMessage());
        }
    }
    
    private static function getStatus() {
        try {
            $status = minidlna::GetMiniDLNAStatus();
            
            self::sendSuccess([
                'audio' => isset($status->AUDIO[1]) ? (int)$status->AUDIO[1] : 0,
                'video' => isset($status->VIDEO[1]) ? (int)$status->VIDEO[1] : 0,
                'images' => isset($status->IMAGES[1]) ? (int)$status->IMAGES[1] : 0
            ]);
            
        } catch (Exception $e) {
            self::sendError("Error getting status: " . $e->getMessage());
        }
    }
    
    private static function rebuild() {
        try {
            $result = minidlna::RebuildMiniDLNA();
            
            self::sendSuccess([
                'message' => $result
            ]);
            
        } catch (Exception $e) {
            self::sendError("Error during rebuild: " . $e->getMessage());
        }
    }
    
    private static function sendSuccess($data) {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data' => $data
        ]);
        exit;
    }
    
    private static function sendError($message) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => $message
        ]);
        exit;
    }
}

// Handle the request
Actions::handleRequest();
?>