<?php
/**
 * Base API Class for Integration Modules
 * Provides common functionality for all API endpoints
 */

require_once '../config/database.php';

class BaseAPI {
    protected $db;
    protected $userRole;
    protected $userId;
    
    public function __construct() {
        // Start session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Check authentication
        if (!isset($_SESSION['user_id'])) {
            $this->sendErrorResponse('Unauthorized access', 401);
            exit;
        }
        
        // Initialize database connection
        $this->db = IntegrationDatabase::getInstance();
        $this->userRole = $_SESSION['roles'] ?? 'Employee';
        $this->userId = $_SESSION['user_id'];
        
        // Set headers
        $this->setHeaders();
    }
    
    protected function setHeaders() {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
    }
    
    protected function sendSuccessResponse($data, $message = 'Success', $code = 200) {
        http_response_code($code);
        echo json_encode([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
            'timestamp' => date('Y-m-d H:i:s')
        ], JSON_PRETTY_PRINT);
    }
    
    protected function sendErrorResponse($message, $code = 400) {
        http_response_code($code);
        echo json_encode([
            'status' => 'error',
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s')
        ], JSON_PRETTY_PRINT);
    }
    
    protected function validatePermission($requiredRoles) {
        if (!in_array($this->userRole, $requiredRoles)) {
            $this->sendErrorResponse('Insufficient permissions', 403);
            return false;
        }
        return true;
    }
    
    protected function getRequestData() {
        $input = file_get_contents('php://input');
        return json_decode($input, true);
    }
    
    protected function sanitizeInput($data) {
        if (is_array($data)) {
            return array_map([$this, 'sanitizeInput'], $data);
        }
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
}
?>
