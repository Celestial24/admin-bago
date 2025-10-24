<?php
/**
 * Employee Self-Service API (HR2)
 * Handles employee self-service functionality
 */

require_once '../api/base-api.php';

class EmployeeSelfServiceAPI extends BaseAPI {
    
    public function __construct() {
        parent::__construct();
        $this->handleRequest();
    }
    
    private function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        
        switch ($method) {
            case 'GET':
                $this->getEmployeeData();
                break;
            case 'POST':
                $this->updateEmployeeData();
                break;
            case 'PUT':
                $this->updateEmployeeProfile();
                break;
            default:
                $this->sendErrorResponse('Method not allowed', 405);
        }
    }
    
    private function getEmployeeData() {
        try {
            $employeeId = $_SESSION['employee_id'] ?? null;
            
            if (!$employeeId) {
                $this->sendErrorResponse('Employee ID not found', 404);
                return;
            }
            
            $sql = "SELECT 
                        employee_id,
                        first_name,
                        last_name,
                        email,
                        phone,
                        department,
                        position,
                        hire_date,
                        status
                    FROM employees 
                    WHERE employee_id = ?";
            
            $employee = $this->db->fetchOne($sql, [$employeeId]);
            
            if ($employee) {
                $this->sendSuccessResponse($employee, 'Employee data retrieved successfully');
            } else {
                $this->sendErrorResponse('Employee not found', 404);
            }
            
        } catch (Exception $e) {
            $this->sendErrorResponse('Failed to retrieve employee data: ' . $e->getMessage());
        }
    }
    
    private function updateEmployeeData() {
        try {
            $data = $this->getRequestData();
            $data = $this->sanitizeInput($data);
            
            $employeeId = $_SESSION['employee_id'] ?? null;
            
            if (!$employeeId) {
                $this->sendErrorResponse('Employee ID not found', 404);
                return;
            }
            
            // Allowed fields for self-service update
            $allowedFields = ['phone', 'email'];
            $updateFields = [];
            $updateValues = [];
            
            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $updateFields[] = "$field = ?";
                    $updateValues[] = $data[$field];
                }
            }
            
            if (empty($updateFields)) {
                $this->sendErrorResponse('No valid fields to update');
                return;
            }
            
            $updateValues[] = $employeeId;
            $sql = "UPDATE employees SET " . implode(', ', $updateFields) . " WHERE employee_id = ?";
            
            $this->db->query($sql, $updateValues);
            
            $this->sendSuccessResponse(null, 'Employee data updated successfully');
            
        } catch (Exception $e) {
            $this->sendErrorResponse('Failed to update employee data: ' . $e->getMessage());
        }
    }
    
    private function updateEmployeeProfile() {
        try {
            $data = $this->getRequestData();
            $data = $this->sanitizeInput($data);
            
            $employeeId = $_SESSION['employee_id'] ?? null;
            
            if (!$employeeId) {
                $this->sendErrorResponse('Employee ID not found', 404);
                return;
            }
            
            // Update profile information
            $sql = "UPDATE employees SET 
                        first_name = ?,
                        last_name = ?,
                        phone = ?,
                        email = ?
                    WHERE employee_id = ?";
            
            $params = [
                $data['first_name'] ?? '',
                $data['last_name'] ?? '',
                $data['phone'] ?? '',
                $data['email'] ?? '',
                $employeeId
            ];
            
            $this->db->query($sql, $params);
            
            $this->sendSuccessResponse(null, 'Profile updated successfully');
            
        } catch (Exception $e) {
            $this->sendErrorResponse('Failed to update profile: ' . $e->getMessage());
        }
    }
}

// Initialize the API
new EmployeeSelfServiceAPI();
?>
