    <?php
    /**
     * Smart Warehousing System API (LOG1)
     * Handles warehouse management and inventory tracking
     */

    require_once '../api/base-api.php';

    class SmartWarehousingAPI extends BaseAPI {
        
        public function __construct() {
            parent::__construct();
            $this->handleRequest();
        }
        
        private function handleRequest() {
            $method = $_SERVER['REQUEST_METHOD'];
            
            switch ($method) {
                case 'GET':
                    $this->getWarehouseData();
                    break;
                case 'POST':
                    $this->addInventoryItem();
                    break;
                case 'PUT':
                    $this->updateInventoryItem();
                    break;
                case 'DELETE':
                    $this->deleteInventoryItem();
                    break;
                default:
                    $this->sendErrorResponse('Method not allowed', 405);
            }
        }
        
        private function getWarehouseData() {
            try {
                if (!$this->validatePermission(['Manager', 'Admin'])) {
                    return;
                }
                
                $sql = "SELECT 
                            item_id,
                            item_name,
                            category,
                            quantity,
                            unit_price,
                            location,
                            supplier,
                            last_updated,
                            status
                        FROM warehouse_inventory 
                        ORDER BY item_name";
                
                $inventory = $this->db->fetchAll($sql);
                
                $this->sendSuccessResponse($inventory, 'Warehouse data retrieved successfully');
                
            } catch (Exception $e) {
                $this->sendErrorResponse('Failed to retrieve warehouse data: ' . $e->getMessage());
            }
        }
        
        private function addInventoryItem() {
            try {
                if (!$this->validatePermission(['Manager', 'Admin'])) {
                    return;
                }
                
                $data = $this->getRequestData();
                $data = $this->sanitizeInput($data);
                
                $requiredFields = ['item_name', 'category', 'quantity', 'unit_price', 'location'];
                foreach ($requiredFields as $field) {
                    if (!isset($data[$field]) || empty($data[$field])) {
                        $this->sendErrorResponse("Field '$field' is required");
                        return;
                    }
                }
                
                $sql = "INSERT INTO warehouse_inventory 
                            (item_name, category, quantity, unit_price, location, supplier, status, created_by)
                        VALUES (?, ?, ?, ?, ?, ?, 'Active', ?)";
                
                $params = [
                    $data['item_name'],
                    $data['category'],
                    $data['quantity'],
                    $data['unit_price'],
                    $data['location'],
                    $data['supplier'] ?? '',
                    $this->userId
                ];
                
                $this->db->query($sql, $params);
                
                $this->sendSuccessResponse(null, 'Inventory item added successfully');
                
            } catch (Exception $e) {
                $this->sendErrorResponse('Failed to add inventory item: ' . $e->getMessage());
            }
        }
        
        private function updateInventoryItem() {
            try {
                if (!$this->validatePermission(['Manager', 'Admin'])) {
                    return;
                }
                
                $data = $this->getRequestData();
                $data = $this->sanitizeInput($data);
                
                if (!isset($data['item_id'])) {
                    $this->sendErrorResponse('Item ID is required');
                    return;
                }
                
                $allowedFields = ['item_name', 'category', 'quantity', 'unit_price', 'location', 'supplier', 'status'];
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
                
                $updateFields[] = "last_updated = NOW()";
                $updateValues[] = $data['item_id'];
                
                $sql = "UPDATE warehouse_inventory SET " . implode(', ', $updateFields) . " WHERE item_id = ?";
                
                $this->db->query($sql, $updateValues);
                
                $this->sendSuccessResponse(null, 'Inventory item updated successfully');
                
            } catch (Exception $e) {
                $this->sendErrorResponse('Failed to update inventory item: ' . $e->getMessage());
            }
        }
        
        private function deleteInventoryItem() {
            try {
                if (!$this->validatePermission(['Admin'])) {
                    return;
                }
                
                $itemId = $_GET['item_id'] ?? null;
                
                if (!$itemId) {
                    $this->sendErrorResponse('Item ID is required');
                    return;
                }
                
                $sql = "UPDATE warehouse_inventory SET status = 'Deleted' WHERE item_id = ?";
                $this->db->query($sql, [$itemId]);
                
                $this->sendSuccessResponse(null, 'Inventory item deleted successfully');
                
            } catch (Exception $e) {
                $this->sendErrorResponse('Failed to delete inventory item: ' . $e->getMessage());
            }
        }
    }

    // Initialize the API
    new SmartWarehousingAPI();
    ?>
