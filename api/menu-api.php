<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

// Get user role
$roles = $_SESSION['roles'] ?? 'Employee';

// Menu items configuration
$menuItems = [
    'hr_modules' => [
        [
            'id' => 'hr2_employee_self_service',
            'title' => 'Employee Self-Service (HR2)',
            'url' => '/public_html/hr2/employee-self-service.php',
            'icon' => 'user-check',
            'category' => 'HR2',
            'description' => 'Employee self-service portal for personal information management',
            'permissions' => ['Employee', 'Manager', 'Admin']
        ]
    ],
    'hr3_modules' => [
        [
            'id' => 'hr3_time_attendance',
            'title' => 'Time and Attendance (HR3)',
            'url' => '/public_html/timeAndattendance/time.php',
            'icon' => 'clock',
            'category' => 'HR3',
            'description' => 'Time tracking and attendance management system',
            'permissions' => ['Employee', 'Manager', 'Admin']
        ],
        [
            'id' => 'hr3_shift_schedule',
            'title' => 'Shift and Schedule Management (HR3)',
            'url' => '/public_html/shift/assignShift.php',
            'icon' => 'calendar-range',
            'category' => 'HR3',
            'description' => 'Shift assignment and schedule management',
            'permissions' => ['Manager', 'Admin']
        ]
    ],
    'log1_modules' => [
        [
            'id' => 'log1_smart_warehousing',
            'title' => 'Smart Warehousing System (LOG1)',
            'url' => '/public_html/log1/smart-warehousing.php',
            'icon' => 'warehouse',
            'category' => 'LOG1',
            'description' => 'Intelligent warehouse management and inventory tracking',
            'permissions' => ['Manager', 'Admin']
        ],
        [
            'id' => 'log1_asset_lifecycle',
            'title' => 'Asset Lifecycle & Maintenance (LOG1)',
            'url' => '/public_html/log1/asset-lifecycle.php',
            'icon' => 'settings',
            'category' => 'LOG1',
            'description' => 'Asset tracking, maintenance scheduling, and lifecycle management',
            'permissions' => ['Manager', 'Admin']
        ],
        [
            'id' => 'log1_document_tracking',
            'title' => 'Document Tracking & Logistics Records (LOG1)',
            'url' => '/public_html/log1/document-tracking.php',
            'icon' => 'file-search',
            'category' => 'LOG1',
            'description' => 'Document management and logistics record tracking',
            'permissions' => ['Manager', 'Admin']
        ]
    ],
    'core1_modules' => [
        [
            'id' => 'core1_front_desk',
            'title' => 'Front desk & Reception Module (Core 1)',
            'url' => '/public_html/core1/front-desk.php',
            'icon' => 'monitor',
            'category' => 'Core 1',
            'description' => 'Front desk operations and reception management',
            'permissions' => ['Employee', 'Manager', 'Admin']
        ],
        [
            'id' => 'core1_reservation_booking',
            'title' => 'Reservation & Booking Module (Core 1)',
            'url' => '/public_html/core1/reservation-booking.php',
            'icon' => 'calendar-check',
            'category' => 'Core 1',
            'description' => 'Reservation and booking management system',
            'permissions' => ['Employee', 'Manager', 'Admin']
        ],
        [
            'id' => 'core1_guest_relationship',
            'title' => 'Guest Relationship Management (Core 1)',
            'url' => '/public_html/core1/guest-relationship.php',
            'icon' => 'users-2',
            'category' => 'Core 1',
            'description' => 'Guest relationship and customer management',
            'permissions' => ['Employee', 'Manager', 'Admin']
        ]
    ]
];

// Filter menu items based on user permissions
function filterMenuByPermissions($menuItems, $userRole) {
    $filteredItems = [];
    
    foreach ($menuItems as $category => $items) {
        $filteredItems[$category] = [];
        foreach ($items as $item) {
            if (in_array($userRole, $item['permissions'])) {
                $filteredItems[$category][] = $item;
            }
        }
    }
    
    return $filteredItems;
}

// Handle different HTTP methods
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Get all menu items for the user
        $filteredMenu = filterMenuByPermissions($menuItems, $roles);
        
        $response = [
            'status' => 'success',
            'user_role' => $roles,
            'menu_items' => $filteredMenu,
            'total_modules' => array_sum(array_map('count', $filteredMenu)),
            'categories' => array_keys($filteredMenu)
        ];
        
        echo json_encode($response, JSON_PRETTY_PRINT);
        break;
        
    case 'POST':
        // Get specific menu item by ID
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (isset($input['menu_id'])) {
            $menuId = $input['menu_id'];
            $foundItem = null;
            
            foreach ($menuItems as $category => $items) {
                foreach ($items as $item) {
                    if ($item['id'] === $menuId) {
                        if (in_array($roles, $item['permissions'])) {
                            $foundItem = $item;
                            break 2;
                        }
                    }
                }
            }
            
            if ($foundItem) {
                echo json_encode([
                    'status' => 'success',
                    'menu_item' => $foundItem
                ], JSON_PRETTY_PRINT);
            } else {
                http_response_code(404);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Menu item not found or access denied'
                ]);
            }
        } else {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'menu_id is required'
            ]);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode([
            'status' => 'error',
            'message' => 'Method not allowed'
        ]);
        break;
}
?>
