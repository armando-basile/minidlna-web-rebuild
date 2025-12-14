<?php
header('Content-Type: application/json');

include_once "core/minidlna.php";

class Actions {
    
    /**
     * Main handler for AJAX requests
     */
    public static function handleRequest() {

        try {
            // Get action from request
            $action = $_POST['action'] ?? $_GET['action'] ?? null;
            
            if (!$action) {
                self::sendError("No action specified");
                return;
            }
            
            // Route to appropriate method
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
    
    /**
     * Get MiniDLNA status
     */
    private static function getStatus() {
        try {
            $status = minidlna::GetMiniDLNAStatus();
            
            self::sendSuccess([
                'audio' => $status->AUDIO,
                'video' => $status->VIDEO,
                'images' => $status->IMAGES
            ]);
            
        } catch (Exception $e) {
            self::sendError("Error getting status: " . $e->getMessage());
        }
    }
    
    /**
     * Rebuild MiniDLNA database
     */
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
    
    /**
     * Send success response
     */
    private static function sendSuccess($data) {
        echo json_encode([
            'success' => true,
            'data' => $data
        ]);
        exit;
    }
    
    /**
     * Send error response
     */
    private static function sendError($message) {
        echo json_encode([
            'success' => false,
            'error' => $message
        ]);
        exit;
    }
}

// check for user log in
if ((!isset($_SESSION["authenticated"])) || ($_SESSION["authenticated"] !== true)) {
    exit(1);
}

// Handle the request
Actions::handleRequest();