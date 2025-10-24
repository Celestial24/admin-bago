<?php
/**
 * Front Desk & Reception Module API (Core 1)
 * Handles front desk operations and reception management
 */

require_once '../api/base-api.php';

class FrontDeskAPI extends BaseAPI {
    
    public function __construct() {
        parent::__construct();
        $this->handleRequest();
    }
    
    private function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        
        switch ($method) {
            case 'GET':
                $this->getFrontDeskData();
                break;
            case 'POST':
                $this->addGuest();
                break;
            case 'PUT':
                $this->updateGuest();
                break;
            case 'DELETE':
                $this->deleteGuest();
                break;
            default:
                $this->sendErrorResponse('Method not allowed', 405);
        }
    }
    
    private function getFrontDeskData() {
        try {
            $action = $_GET['action'] ?? 'guests';
            
            switch ($action) {
                case 'guests':
                    $this->getGuests();
                    break;
                case 'reservations':
                    $this->getReservations();
                    break;
                case 'checkins':
                    $this->getCheckIns();
                    break;
                default:
                    $this->sendErrorResponse('Invalid action');
            }
            
        } catch (Exception $e) {
            $this->sendErrorResponse('Failed to retrieve front desk data: ' . $e->getMessage());
        }
    }
    
    private function getGuests() {
        $sql = "SELECT 
                    guest_id,
                    first_name,
                    last_name,
                    email,
                    phone,
                    check_in_date,
                    check_out_date,
                    room_number,
                    status,
                    created_at
                FROM guests 
                ORDER BY created_at DESC";
        
        $guests = $this->db->fetchAll($sql);
        $this->sendSuccessResponse($guests, 'Guests retrieved successfully');
    }
    
    private function getReservations() {
        $sql = "SELECT 
                    reservation_id,
                    guest_name,
                    email,
                    phone,
                    check_in_date,
                    check_out_date,
                    room_type,
                    status,
                    created_at
                FROM reservations 
                ORDER BY created_at DESC";
        
        $reservations = $this->db->fetchAll($sql);
        $this->sendSuccessResponse($reservations, 'Reservations retrieved successfully');
    }
    
    private function getCheckIns() {
        $sql = "SELECT 
                    checkin_id,
                    guest_id,
                    guest_name,
                    room_number,
                    check_in_time,
                    check_out_time,
                    status
                FROM checkins 
                WHERE DATE(check_in_time) = CURDATE()
                ORDER BY check_in_time DESC";
        
        $checkins = $this->db->fetchAll($sql);
        $this->sendSuccessResponse($checkins, 'Today\'s check-ins retrieved successfully');
    }
    
    private function addGuest() {
        try {
            $data = $this->getRequestData();
            $data = $this->sanitizeInput($data);
            
            $requiredFields = ['first_name', 'last_name', 'email', 'phone'];
            foreach ($requiredFields as $field) {
                if (!isset($data[$field]) || empty($data[$field])) {
                    $this->sendErrorResponse("Field '$field' is required");
                    return;
                }
            }
            
            $sql = "INSERT INTO guests 
                        (first_name, last_name, email, phone, check_in_date, check_out_date, room_number, status, created_by)
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'Checked In', ?)";
            
            $params = [
                $data['first_name'],
                $data['last_name'],
                $data['email'],
                $data['phone'],
                $data['check_in_date'] ?? date('Y-m-d'),
                $data['check_out_date'] ?? null,
                $data['room_number'] ?? null,
                $this->userId
            ];
            
            $this->db->query($sql, $params);
            
            $this->sendSuccessResponse(null, 'Guest added successfully');
            
        } catch (Exception $e) {
            $this->sendErrorResponse('Failed to add guest: ' . $e->getMessage());
        }
    }
    
    private function updateGuest() {
        try {
            $data = $this->getRequestData();
            $data = $this->sanitizeInput($data);
            
            if (!isset($data['guest_id'])) {
                $this->sendErrorResponse('Guest ID is required');
                return;
            }
            
            $allowedFields = ['first_name', 'last_name', 'email', 'phone', 'room_number', 'status'];
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
            
            $updateValues[] = $data['guest_id'];
            $sql = "UPDATE guests SET " . implode(', ', $updateFields) . " WHERE guest_id = ?";
            
            $this->db->query($sql, $updateValues);
            
            $this->sendSuccessResponse(null, 'Guest updated successfully');
            
        } catch (Exception $e) {
            $this->sendErrorResponse('Failed to update guest: ' . $e->getMessage());
        }
    }
    
    private function deleteGuest() {
        try {
            $guestId = $_GET['guest_id'] ?? null;
            
            if (!$guestId) {
                $this->sendErrorResponse('Guest ID is required');
                return;
            }
            
            $sql = "UPDATE guests SET status = 'Deleted' WHERE guest_id = ?";
            $this->db->query($sql, [$guestId]);
            
            $this->sendSuccessResponse(null, 'Guest deleted successfully');
            
        } catch (Exception $e) {
            $this->sendErrorResponse('Failed to delete guest: ' . $e->getMessage());
        }
    }
}

// Initialize the API
new FrontDeskAPI();
?>

