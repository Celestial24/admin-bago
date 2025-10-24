<?php
// facilities_reservation_system.php
session_start();

// Load shared DB helper (keeps filename safe and centralized)
require_once __DIR__ . '/db.php';

class ReservationSystem {
    private $pdo;
    
    public function __construct() {
        $this->pdo = get_pdo();
    }
    
    
    public function makeReservation($data) {
        $pdo = $this->pdo;
        
        try {
            $pdo->beginTransaction();
            
            // Check for time conflicts
            $conflictCheck = $pdo->prepare("
                SELECT COUNT(*) FROM reservations 
                WHERE facility_id = ? AND event_date = ? AND status IN ('pending', 'confirmed')
                AND ((start_time <= ? AND end_time > ?) OR (start_time < ? AND end_time >= ?))
            ");
            $conflictCheck->execute([
                $data['facility_id'],
                $data['event_date'],
                $data['start_time'],
                $data['start_time'],
                $data['end_time'],
                $data['end_time']
            ]);
            
            if ($conflictCheck->fetchColumn() > 0) {
                throw new Exception("Time conflict: The facility is already booked for the selected time slot.");
            }
            
            // Calculate total amount
            $facility = $pdo->query("SELECT hourly_rate, capacity FROM facilities WHERE id = " . intval($data['facility_id']))->fetch();
            
            if ($data['guests_count'] > $facility['capacity']) {
                throw new Exception("Number of guests exceeds facility capacity.");
            }
            
            $start = strtotime($data['start_time']);
            $end = strtotime($data['end_time']);
            $hours = max(1, ceil(($end - $start) / 3600));
            $total_amount = $hours * $facility['hourly_rate'];
            
            $stmt = $pdo->prepare("INSERT INTO reservations (facility_id, customer_name, customer_email, customer_phone, event_type, event_date, start_time, end_time, guests_count, special_requirements, total_amount) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                intval($data['facility_id']),
                htmlspecialchars($data['customer_name']),
                filter_var($data['customer_email'], FILTER_VALIDATE_EMAIL),
                htmlspecialchars($data['customer_phone'] ?? ''),
                htmlspecialchars($data['event_type']),
                $data['event_date'],
                $data['start_time'],
                $data['end_time'],
                intval($data['guests_count']),
                htmlspecialchars($data['special_requirements'] ?? ''),
                $total_amount
            ]);
            
            $pdo->commit();
            return ['success' => true, 'message' => "Reservation request submitted successfully! We will contact you shortly to confirm."];
            
        } catch (Exception $e) {
            $pdo->rollBack();
            return ['success' => false, 'message' => "Error making reservation: " . $e->getMessage()];
        }
    }
    
    public function updateReservationStatus($reservationId, $status) {
        $pdo = $this->pdo;
        
        try {
            $stmt = $pdo->prepare("UPDATE reservations SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->execute([$status, intval($reservationId)]);
            
            return ['success' => true, 'message' => "Reservation status updated successfully!"];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => "Error updating reservation: " . $e->getMessage()];
        }
    }
    
    public function addFacility($data) {
        $pdo = $this->pdo;
        
        try {
            $stmt = $pdo->prepare("INSERT INTO facilities (name, type, capacity, location, description, hourly_rate, amenities) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                htmlspecialchars($data['name']),
                htmlspecialchars($data['type']),
                intval($data['capacity']),
                htmlspecialchars($data['location']),
                htmlspecialchars($data['description']),
                floatval($data['hourly_rate']),
                htmlspecialchars($data['amenities'] ?? '')
            ]);
            
            return ['success' => true, 'message' => "Facility added successfully!"];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => "Error adding facility: " . $e->getMessage()];
        }
    }
    
    public function fetchDashboardData() {
        $pdo = $this->pdo;
        $data = [];
        
        try {
            // Dashboard metrics
            $data['total_facilities'] = $pdo->query("SELECT COUNT(*) FROM facilities WHERE status = 'active'")->fetchColumn();
            $data['today_reservations'] = $pdo->query("SELECT COUNT(*) FROM reservations WHERE event_date = CURDATE() AND status IN ('confirmed', 'pending')")->fetchColumn();
            $data['pending_approvals'] = $pdo->query("SELECT COUNT(*) FROM reservations WHERE status = 'pending'")->fetchColumn();
            $data['monthly_revenue'] = $pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM reservations WHERE status = 'confirmed' AND MONTH(event_date) = MONTH(CURDATE()) AND YEAR(event_date) = YEAR(CURDATE())")->fetchColumn();
            
            // Fetch facilities
            $data['facilities'] = $pdo->query("SELECT * FROM facilities WHERE status = 'active' ORDER BY name")->fetchAll();
            
            // Fetch reservations with pagination
            $data['reservations'] = $pdo->query("
                SELECT r.*, f.name as facility_name, f.capacity as facility_capacity 
                FROM reservations r 
                JOIN facilities f ON r.facility_id = f.id 
                ORDER BY r.event_date DESC, r.start_time DESC 
                LIMIT 50
            ")->fetchAll();
            
            // Today's schedule
            $data['today_schedule'] = $pdo->query("
                SELECT r.*, f.name as facility_name 
                FROM reservations r 
                JOIN facilities f ON r.facility_id = f.id 
                WHERE r.event_date = CURDATE() AND r.status = 'confirmed' 
                ORDER BY r.start_time
            ")->fetchAll();
            
        } catch(PDOException $e) {
            $data['error'] = "Error fetching data: " . $e->getMessage();
        }
        
        return $data;
    }
    
    public function getAvailableTimeSlots($facilityId, $date) {
        $pdo = $this->pdo;
        
        $stmt = $pdo->prepare("
            SELECT start_time, end_time 
            FROM reservations 
            WHERE facility_id = ? AND event_date = ? AND status IN ('confirmed', 'pending')
            ORDER BY start_time
        ");
        $stmt->execute([$facilityId, $date]);
        
        return $stmt->fetchAll();
    }
}

// Initialize system
$reservationSystem = new ReservationSystem();
$dashboard_data = $reservationSystem->fetchDashboardData();

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'make_reservation':
                $result = $reservationSystem->makeReservation($_POST);
                if ($result['success']) {
                    $success_message = $result['message'];
                } else {
                    $error_message = $result['message'];
                }
                break;
                
            case 'update_reservation_status':
                $result = $reservationSystem->updateReservationStatus($_POST['reservation_id'], $_POST['status']);
                if ($result['success']) {
                    $success_message = $result['message'];
                } else {
                    $error_message = $result['message'];
                }
                break;
                
            case 'add_facility':
                $result = $reservationSystem->addFacility($_POST);
                if ($result['success']) {
                    $success_message = $result['message'];
                } else {
                    $error_message = $result['message'];
                }
                break;
                
            case 'check_availability':
                if (isset($_POST['facility_id']) && isset($_POST['event_date'])) {
                    $slots = $reservationSystem->getAvailableTimeSlots($_POST['facility_id'], $_POST['event_date']);
                    header('Content-Type: application/json');
                    echo json_encode($slots);
                    exit;
                }
                break;
                
            case 'update_status':
                if (isset($_POST['reservation_id']) && isset($_POST['status'])) {
                    $stmt = get_pdo()->prepare("UPDATE reservations SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                    $stmt->execute([$_POST['status'], intval($_POST['reservation_id'])]);
                    $_SESSION['message'] = 'Reservation status updated.';
                    header('Location: ' . $_SERVER['PHP_SELF'] . '?tab=reports');
                    exit;
                }
                break;

            case 'export_csv':
                // Build query with optional filters
                $where = [];
                $params = [];
                if (!empty($_POST['from_date'])) { $where[] = 'event_date >= ?'; $params[] = $_POST['from_date']; }
                if (!empty($_POST['to_date'])) { $where[] = 'event_date <= ?'; $params[] = $_POST['to_date']; }
                if (!empty($_POST['status']) && $_POST['status'] !== 'all') { $where[] = 'status = ?'; $params[] = $_POST['status']; }

                $sql = "SELECT r.*, f.name as facility_name FROM reservations r LEFT JOIN facilities f ON r.facility_id = f.id";
                if ($where) { $sql .= ' WHERE ' . implode(' AND ', $where); }
                $sql .= ' ORDER BY r.event_date, r.start_time';

                $stmt = get_pdo()->prepare($sql);
                $stmt->execute($params);

                header('Content-Type: text/csv');
                header('Content-Disposition: attachment; filename="reservations_report.csv"');

                $out = fopen('php://output', 'w');
                fputcsv($out, ['ID','Facility','Customer','Email','Phone','Event Type','Date','Start Time','End Time','Guests','Amount','Status','Created At','Updated At']);

                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    fputcsv($out, [
                        $row['id'],
                        $row['facility_name'],
                        $row['customer_name'],
                        $row['customer_email'],
                        $row['customer_phone'],
                        $row['event_type'],
                        $row['event_date'],
                        $row['start_time'],
                        $row['end_time'],
                        $row['guests_count'],
                        $row['total_amount'],
                        $row['status'],
                        $row['created_at'],
                        $row['updated_at']
                    ]);
                }
                fclose($out);
                exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facilities Reservation System - Hotel Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #1a365d;
            --secondary: #2d3748;
            --accent: #3182ce;
            --success: #38a169;
            --warning: #d69e2e;
            --danger: #e53e3e;
            --info: #00b5d8;
            --light: #f7fafc;
            --dark: #2d3748;
            --border: #e2e8f0;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --radius: 12px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f8fafc;
            color: var(--dark);
            line-height: 1.6;
        }

        .container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding: 0;
            box-shadow: var(--shadow);
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 100;
        }

        .logo-area {
            padding: 2rem 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(255, 255, 255, 0.05);
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 1.25rem;
            font-weight: 700;
        }

        .nav-section {
            padding: 1.5rem 0;
        }

        .nav-title {
            padding: 0 1.5rem 0.75rem;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #a0aec0;
            font-weight: 600;
        }

        .nav-links {
            list-style: none;
        }

        .nav-links li {
            margin: 0.25rem 0.75rem;
        }

        .nav-links a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0.75rem 1rem;
            color: #cbd5e0;
            text-decoration: none;
            border-radius: var(--radius);
            transition: all 0.2s;
            font-weight: 500;
        }

        .nav-links a:hover,
        .nav-links a.active {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .nav-links a.active {
            background: var(--accent);
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 0;
            background: #f8fafc;
            overflow-y: auto;
        }

        .top-header {
            background: white;
            padding: 1rem 2rem;
            border-bottom: 1px solid var(--border);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 90;
        }

        .header-title h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
        }

        /* Dashboard Content */
        .dashboard-content {
            padding: 2rem;
        }

        .tab-content {
            display: none;
            animation: fadeIn 0.3s ease-in;
        }

        .tab-content.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            border-left: 4px solid var(--accent);
            transition: transform 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 1rem;
        }

        .stat-info h3 {
            font-size: 0.875rem;
            font-weight: 600;
            color: #718096;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            background: var(--light);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--accent);
            font-size: 1.5rem;
        }

        /* Facilities Grid */
        .facilities-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .facility-card {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .facility-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        .facility-image {
            height: 200px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
            position: relative;
            overflow: hidden;
        }

        .facility-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .facility-image::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.1);
        }

        .facility-content {
            padding: 1.5rem;
        }

        .facility-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 1rem;
        }

        .facility-name {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 0.5rem;
        }

        .facility-type {
            background: var(--light);
            color: var(--accent);
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .facility-details {
            color: #718096;
            margin-bottom: 1rem;
            font-size: 0.875rem;
        }

        .facility-meta {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            color: #718096;
        }

        .facility-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--success);
            margin-bottom: 1rem;
        }

        /* Buttons */
        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .btn-primary {
            background: var(--accent);
            color: white;
        }

        .btn-primary:hover {
            background: #2c5aa0;
            transform: translateY(-2px);
        }

        .btn-success {
            background: var(--success);
            color: white;
        }

        .btn-warning {
            background: var(--warning);
            color: white;
        }

        .btn-danger {
            background: var(--danger);
            color: white;
        }

        .btn-outline {
            background: transparent;
            border: 1px solid var(--border);
            color: var(--dark);
        }

        .btn-outline:hover {
            background: var(--light);
        }

        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.75rem;
        }

        .btn-block {
            width: 100%;
            justify-content: center;
        }

        /* Tables */
        .table-container {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }

        .table th {
            background: var(--light);
            font-weight: 600;
            color: var(--dark);
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .table tr:hover {
            background: #f8fafc;
        }

        /* Status Badges */
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            display: inline-block;
        }

        .status-pending {
            background: #fefcbf;
            color: #744210;
        }

        .status-confirmed {
            background: #c6f6d5;
            color: #22543d;
        }

        .status-cancelled {
            background: #fed7d7;
            color: #c53030;
        }

        .status-completed {
            background: #bee3f8;
            color: #1a365d;
        }

        /* Forms */
        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--dark);
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 0.875rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(49, 130, 206, 0.1);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        /* Modals */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 2rem;
            border-radius: var(--radius);
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: var(--shadow);
            animation: modalSlideIn 0.3s ease-out;
        }

        @keyframes modalSlideIn {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border);
        }

        .modal-header h3 {
            color: var(--primary);
            font-size: 1.25rem;
            margin: 0;
        }

        .close {
            color: #718096;
            font-size: 1.5rem;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.2s;
        }

        .close:hover {
            color: var(--dark);
        }

        /* Calendar */
        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1rem;
        }

        .calendar-day {
            background: white;
            border-radius: 8px;
            padding: 1rem;
            box-shadow: var(--shadow);
        }

        .calendar-date {
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--border);
        }

        .calendar-event {
            background: var(--light);
            padding: 0.75rem;
            border-radius: 6px;
            margin-bottom: 0.5rem;
            border-left: 4px solid var(--accent);
            transition: transform 0.2s;
        }

        .calendar-event:hover {
            transform: translateX(5px);
        }

        .event-time {
            font-size: 0.875rem;
            color: var(--accent);
            font-weight: 600;
        }

        .event-title {
            font-weight: 600;
            margin: 0.25rem 0;
        }

        .event-details {
            font-size: 0.875rem;
            color: #718096;
        }

        /* Alert Messages */
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            border-left: 4px solid;
        }

        .alert-success {
            background: #f0fff4;
            border-color: #9ae6b4;
            color: #22543d;
        }

        .alert-error {
            background: #fed7d7;
            border-color: #feb2b2;
            color: #c53030;
        }

        .alert-warning {
            background: #fefcbf;
            border-color: #faf089;
            color: #744210;
        }

        /* Loading Spinner */
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid var(--accent);
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 2s linear infinite;
            margin: 20px auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Card Components */
        .card {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-header h3 {
            margin: 0;
            color: var(--primary);
        }

        .card-content {
            padding: 1.5rem;
        }

        /* Utility Classes */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .mb-1 { margin-bottom: 1rem; }
        .mb-2 { margin-bottom: 2rem; }
        .mt-1 { margin-top: 1rem; }
        .mt-2 { margin-top: 2rem; }
        .d-flex { display: flex; }
        .justify-between { justify-content: space-between; }
        .align-center { align-items: center; }
        .gap-1 { gap: 1rem; }

        /* Mobile Sidebar Responsiveness */
        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                left: -100%;
                z-index: 50;
                transition: left 0.3s ease;
                width: 280px;
                height: 100vh;
            }
            .sidebar.sidebar-open {
                left: 0;
            }
            .mobile-menu-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 40;
            }
            .sidebar.sidebar-open ~ .mobile-menu-overlay {
                display: block;
            }
            .main-content {
                margin-left: 0;
            }
            .dashboard-content {
                padding: 1rem;
            }
            .facilities-grid {
                grid-template-columns: 1fr;
            }
            .stats-grid {
                grid-template-columns: 1fr;
            }
            .form-row {
                grid-template-columns: 1fr;
            }
            .modal-content {
                margin: 10% auto;
                width: 95%;
                padding: 1rem;
            }
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .sidebar {
                width: 250px;
            }
            .main-content {
                margin-left: 250px;
            }
        }

        /* Print Styles */
        @media print {
            .sidebar, .top-header, .btn {
                display: none !important;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .tab-content {
                display: block !important;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Mobile Menu Overlay -->
        <div class="mobile-menu-overlay" onclick="closeSidebar()"></div>

        <!-- Sidebar -->
        <nav class="sidebar">
            <div class="logo-area">
                <div class="logo">
                    <img src="atiera.png" alt="Atiéra" style="height:90px; margin-bottom: -70px; margin-top: -70px; width:auto; display:block;">
                </div>
            </div>
            
            <div class="nav-section">
                <div class="nav-title">Main Navigation</div>
                <ul class="nav-links">
                    <li><a href="#" class="active" data-tab="dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="#" data-tab="facilities"><i class="fas fa-building"></i> Facilities</a></li>
                    <li><a href="#" data-tab="reservations"><i class="fas fa-calendar-check"></i> Reservations</a></li>
                    <li><a href="#" data-tab="calendar"><i class="fas fa-calendar-alt"></i> Calendar</a></li>
                    <li><a href="#" data-tab="management"><i class="fas fa-cog"></i> Management</a></li>
                    <li><a href="legalamanagement.php" data-tab="legalamanagement_system"><i class="fas fa-cog"></i> legal management</a></li>
                    <li><a href="document management(archiving).php" data-tab="legalamanagement_system"><i class="fas fa-cog"></i> Document archiving</a></li>
                    <li><a href="visitorslog.php" data-tab="legalamanagement_system"><i class="fas fa-cog"></i> visitors Log</a></li>
                </ul>
                </ul>
                </ul>
                
                </ul>
            </div>

            <div class="nav-section">
                <div class="nav-title">External Links</div>
                <ul class="nav-links">
    
                    <li><a href="#" data-tab="reports"><i class="fas fa-chart-bar"></i> Reports</a></li>
                </ul>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Header -->
            <header class="top-header">
                <div class="header-title">
                    <button class="mobile-menu-btn" onclick="toggleSidebar()">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1 id="page-title">Facilities Reservation System</h1>
                    <p id="page-subtitle">Manage hotel facilities and reservations efficiently</p>
                </div>

                <div class="header-actions">
                    <button class="btn btn-primary" onclick="openModal('reservation-modal')">
                        <i class="fas fa-plus"></i> New Reservation
                    </button>
                    <a href="logout.php" class="btn btn-outline" style="margin-left: 10px;">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </header>

            <!-- Dashboard Content -->
            <div class="dashboard-content">
                <!-- Alert Messages -->
                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success_message) ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error_message) ?>
                    </div>
                <?php endif; ?>

                <!-- Dashboard Tab -->
                <div id="dashboard" class="tab-content active">
                    <!-- Stats Overview -->
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-header">
                                <div class="stat-info">
                                    <h3>Total Facilities</h3>
                                    <div class="stat-number"><?= $dashboard_data['total_facilities'] ?></div>
                                </div>
                                <div class="stat-icon"><i class="fas fa-building"></i></div>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-header">
                                <div class="stat-info">
                                    <h3>Today's Reservations</h3>
                                    <div class="stat-number"><?= $dashboard_data['today_reservations'] ?></div>
                                </div>
                                <div class="stat-icon"><i class="fas fa-calendar-day"></i></div>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-header">
                                <div class="stat-info">
                                    <h3>Pending Approvals</h3>
                                    <div class="stat-number"><?= $dashboard_data['pending_approvals'] ?></div>
                                </div>
                                <div class="stat-icon"><i class="fas fa-clock"></i></div>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-header">
                                <div class="stat-info">
                                    <h3>Monthly Revenue</h3>
                                    <div class="stat-number">₱<?= number_format($dashboard_data['monthly_revenue'], 2) ?></div>
                                </div>
                                <div class="stat-icon"><i class="fas fa-dollar-sign"></i></div>
                            </div>
                        </div>
                    </div>

                    <!-- Today's Schedule -->
                    <div class="card mb-2">
                        <div class="card-header">
                            <h3><i class="fas fa-calendar-day"></i> Today's Schedule</h3>
                        </div>
                        <div class="card-content">
                            <?php if (!empty($dashboard_data['today_schedule'])): ?>
                                <div class="calendar-grid">
                                    <?php foreach ($dashboard_data['today_schedule'] as $event): ?>
                                        <div class="calendar-event">
                                            <div class="event-time">
                                                <?= date('g:i A', strtotime($event['start_time'])) ?> - <?= date('g:i A', strtotime($event['end_time'])) ?>
                                            </div>
                                            <div class="event-title"><?= htmlspecialchars($event['facility_name']) ?></div>
                                            <div class="event-details">
                                                <?= htmlspecialchars($event['customer_name']) ?> • <?= htmlspecialchars($event['event_type']) ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-center" style="color: #718096; padding: 2rem;">
                                    <i class="fas fa-calendar-times"></i> No reservations scheduled for today.
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Quick Facilities Overview -->
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-building"></i> Available Facilities</h3>
                            <button class="btn btn-outline" onclick="switchTab('facilities')">
                                <i class="fas fa-eye"></i> View All
                            </button>
                        </div>
                        <div class="card-content">
                            <div class="facilities-grid">
                                <?php foreach (array_slice($dashboard_data['facilities'], 0, 3) as $facility): ?>
                                    <div class="facility-card">
                                        <div class="facility-image">
                                            <?php if (!empty($facility['image_url']) && file_exists('images/' . $facility['image_url'])): ?>
                                                <img src="images/<?= htmlspecialchars($facility['image_url']) ?>" alt="<?= htmlspecialchars($facility['name']) ?>">
                                            <?php else: ?>
                                                <i class="<?= 
                                                    match($facility['type']) {
                                                        'banquet' => 'fas fa-glass-cheers',
                                                        'meeting' => 'fas fa-briefcase',
                                                        'outdoor' => 'fas fa-umbrella-beach',
                                                        'conference' => 'fas fa-users',
                                                        'dining' => 'fas fa-utensils',
                                                        'lounge' => 'fas fa-cocktail',
                                                        default => 'fas fa-building'
                                                    }
                                                ?>"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div class="facility-content">
                                            <div class="facility-header">
                                                <div>
                                                    <div class="facility-name"><?= htmlspecialchars($facility['name']) ?></div>
                                                    <span class="facility-type"><?= htmlspecialchars($facility['type']) ?></span>
                                                </div>
                                            </div>
                                            <div class="facility-details">
                                                <?= htmlspecialchars($facility['description']) ?>
                                            </div>
                                            <div class="facility-meta">
                                                <div class="meta-item"><i class="fas fa-users"></i> Capacity: <?= $facility['capacity'] ?></div>
                                                <div class="meta-item"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($facility['location']) ?></div>
                                            </div>
                                            <div class="facility-price">₱<?= number_format($facility['hourly_rate'], 2) ?>/hour</div>
                                            <button class="btn btn-primary btn-block" onclick="openReservationModal(<?= $facility['id'] ?>)">
                                                <i class="fas fa-calendar-plus"></i> Book Now
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Facilities Tab -->
                <div id="facilities" class="tab-content">
                    <div class="d-flex justify-between align-center mb-2">
                        <h2><i class="fas fa-building"></i> Hotel Facilities</h2>
                        <button class="btn btn-primary" onclick="openModal('facility-modal')">
                            <i class="fas fa-plus"></i> Add Facility
                        </button>
                    </div>
                    
                    <div class="facilities-grid">
                        <?php foreach ($dashboard_data['facilities'] as $facility): ?>
                            <div class="facility-card">
                                <div class="facility-image">
                                    <?php if (!empty($facility['image_url']) && file_exists('images/' . $facility['image_url'])): ?>
                                        <img src="images/<?= htmlspecialchars($facility['image_url']) ?>" alt="<?= htmlspecialchars($facility['name']) ?>">
                                    <?php else: ?>
                                        <i class="<?= 
                                            match($facility['type']) {
                                                'banquet' => 'fas fa-glass-cheers',
                                                'meeting' => 'fas fa-briefcase',
                                                'outdoor' => 'fas fa-umbrella-beach',
                                                'conference' => 'fas fa-users',
                                                'dining' => 'fas fa-utensils',
                                                'lounge' => 'fas fa-cocktail',
                                                default => 'fas fa-building'
                                            }
                                        ?>"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="facility-content">
                                    <div class="facility-header">
                                        <div>
                                            <div class="facility-name"><?= htmlspecialchars($facility['name']) ?></div>
                                            <span class="facility-type"><?= htmlspecialchars($facility['type']) ?></span>
                                        </div>
                                    </div>
                                    <div class="facility-details">
                                        <?= htmlspecialchars($facility['description']) ?>
                                    </div>
                                    <div class="facility-meta">
                                        <div class="meta-item"><i class="fas fa-users"></i> Capacity: <?= $facility['capacity'] ?></div>
                                        <div class="meta-item"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($facility['location']) ?></div>
                                    </div>
                                    <?php if (!empty($facility['amenities'])): ?>
                                        <div class="mb-1">
                                            <strong><i class="fas fa-star"></i> Amenities:</strong>
                                            <div style="font-size: 0.875rem; color: #718096; margin-top: 0.25rem;">
                                                <?= htmlspecialchars($facility['amenities']) ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                            <div class="facility-price">₱<?= number_format($facility['hourly_rate'], 2) ?>/hour</div>
                                    <button class="btn btn-primary btn-block" onclick="openReservationModal(<?= $facility['id'] ?>)">
                                        <i class="fas fa-calendar-plus"></i> Book This Facility
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Reservations Tab -->
                <div id="reservations" class="tab-content">
                    <div class="d-flex justify-between align-center mb-2">
                        <h2><i class="fas fa-calendar-check"></i> Reservation Management</h2>
                       
                    </div>
                    
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Facility</th>
                                    <th>Customer</th>
                                    <th>Event Type</th>
                                    <th>Date & Time</th>
                                    <th>Guests</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($dashboard_data['reservations'] as $reservation): ?>
                                    <tr>
                                        <td>#<?= $reservation['id'] ?></td>
                                        <td><?= htmlspecialchars($reservation['facility_name']) ?></td>
                                        <td>
                                            <div><?= htmlspecialchars($reservation['customer_name']) ?></div>
                                            <small style="color: #718096;"><?= htmlspecialchars($reservation['customer_email'] ?? '') ?></small>
                                        </td>
                                        <td><?= htmlspecialchars($reservation['event_type']) ?></td>
                                        <td>
                                            <div><?= date('M d, Y', strtotime($reservation['event_date'])) ?></div>
                                            <small style="color: #718096;">
                                                <?= date('g:i A', strtotime($reservation['start_time'])) ?> - <?= date('g:i A', strtotime($reservation['end_time'])) ?>
                                            </small>
                                        </td>
                                        <td><?= $reservation['guests_count'] ?></td>
                                        <td>₱<?= number_format($reservation['total_amount'] ?? 0, 2) ?></td>
                                        <td>
                                            <span class="status-badge status-<?= $reservation['status'] ?>">
                                                <?= ucfirst($reservation['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                <?php if ($reservation['status'] == 'pending'): ?>
                                                    <button class="btn btn-success btn-sm" onclick="updateReservationStatus(<?= $reservation['id'] ?>, 'confirmed')">
                                                        <i class="fas fa-check"></i> Confirm
                                                    </button>
                                                    <button class="btn btn-danger btn-sm" onclick="updateReservationStatus(<?= $reservation['id'] ?>, 'cancelled')">
                                                        <i class="fas fa-times"></i> Cancel
                                                    </button>
                                                <?php elseif ($reservation['status'] == 'confirmed'): ?>
                                                    <button class="btn btn-warning btn-sm" onclick="updateReservationStatus(<?= $reservation['id'] ?>, 'completed')">
                                                        <i class="fas fa-flag-checkered"></i> Complete
                                                    </button>
                                                <?php endif; ?>
                                                <button class="btn btn-outline btn-sm" onclick="viewReservationDetails(<?= $reservation['id'] ?>)">
                                                    <i class="fas fa-eye"></i> View
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Calendar Tab -->
                <!-- Reports Tab -->
                <div id="reports" class="tab-content">
                    <h2 class="mb-2"><i class="fas fa-chart-bar"></i> Reservations Reports</h2>

                    <?php
                    // Server-side: handle GET filters for reports view
                    $r_from = $_GET['from_date'] ?? '';
                    $r_to = $_GET['to_date'] ?? '';
                    $r_status = $_GET['status'] ?? 'all';

                    $r_where = [];
                    $r_params = [];
                    if ($r_from) { $r_where[] = 'r.event_date >= ?'; $r_params[] = $r_from; }
                    if ($r_to) { $r_where[] = 'r.event_date <= ?'; $r_params[] = $r_to; }
                    if ($r_status !== 'all') { $r_where[] = 'r.status = ?'; $r_params[] = $r_status; }

                    $r_sql = "SELECT r.*, f.name as facility_name FROM reservations r LEFT JOIN facilities f ON r.facility_id = f.id";
                    if ($r_where) $r_sql .= ' WHERE ' . implode(' AND ', $r_where);
                    $r_sql .= ' ORDER BY r.event_date DESC, r.start_time DESC';

                    $r_stmt = get_pdo()->prepare($r_sql);
                    $r_stmt->execute($r_params);
                    $r_reservations = $r_stmt->fetchAll(PDO::FETCH_ASSOC);
                    ?>

                    <form method="get" class="filters">
                        From: <input type="date" name="from_date" value="<?= htmlspecialchars($r_from) ?>"> 
                        To: <input type="date" name="to_date" value="<?= htmlspecialchars($r_to) ?>"> 
                        Status: <select name="status">
                            <option value="all" <?= $r_status==='all'?'selected':'' ?>>All</option>
                            <option value="pending" <?= $r_status==='pending'?'selected':'' ?>>Pending</option>
                            <option value="confirmed" <?= $r_status==='confirmed'?'selected':'' ?>>Confirmed</option>
                            <option value="cancelled" <?= $r_status==='cancelled'?'selected':'' ?>>Cancelled</option>
                            <option value="completed" <?= $r_status==='completed'?'selected':'' ?>>Completed</option>
                        </select>
                        <button class="btn">Filter</button>
                    </form>

                    <form method="post" style="margin-bottom:12px">
                        <input type="hidden" name="action" value="export_csv">
                        <input type="hidden" name="from_date" value="<?= htmlspecialchars($r_from) ?>">
                        <input type="hidden" name="to_date" value="<?= htmlspecialchars($r_to) ?>">
                        <input type="hidden" name="status" value="<?= htmlspecialchars($r_status) ?>">
                        <button class="btn">Export CSV</button>
                    </form>

                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Facility</th>
                                    <th>Customer</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Guests</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($r_reservations as $rr): ?>
                                    <tr>
                                        <td><?= $rr['id'] ?></td>
                                        <td><?= htmlspecialchars($rr['facility_name']) ?></td>
                                        <td><?= htmlspecialchars($rr['customer_name']) ?><br><small><?= htmlspecialchars($rr['customer_email']) ?></small></td>
                                        <td><?= htmlspecialchars($rr['event_date']) ?></td>
                                        <td><?= htmlspecialchars($rr['start_time']) ?> - <?= htmlspecialchars($rr['end_time']) ?></td>
                                        <td><?= $rr['guests_count'] ?></td>
                                        <td>₱<?= number_format($rr['total_amount'] ?? 0, 2) ?></td>
                                        <td><?= htmlspecialchars($rr['status']) ?></td>
                                        <td>
                                            <form method="post" style="display:inline">
                                                <input type="hidden" name="action" value="update_status">
                                                <input type="hidden" name="reservation_id" value="<?= $rr['id'] ?>">
                                                <?php if ($rr['status'] !== 'confirmed'): ?>
                                                    <button class="btn" name="status" value="confirmed">Confirm</button>
                                                <?php endif; ?>
                                                <?php if ($rr['status'] !== 'cancelled'): ?>
                                                    <button class="btn btn-danger" name="status" value="cancelled">Cancel</button>
                                                <?php endif; ?>
                                                <?php if ($rr['status'] !== 'completed'): ?>
                                                    <button class="btn" name="status" value="completed">Complete</button>
                                                <?php endif; ?>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div id="calendar" class="tab-content">
                    <h2 class="mb-2"><i class="fas fa-calendar-alt"></i> Reservation Calendar</h2>
                    
                    <div class="calendar-grid">
                        <?php
                        // Display next 7 days
                        for ($i = 0; $i < 7; $i++):
                            $date = date('Y-m-d', strtotime("+$i days"));
                            $display_date = date('D, M d, Y', strtotime($date));
                            $day_events = array_filter($dashboard_data['reservations'], function($event) use ($date) {
                                return $event['event_date'] == $date && $event['status'] == 'confirmed';
                            });
                        ?>
                        <div class="calendar-day">
                            <div class="calendar-date"><?= $display_date ?></div>
                            <div class="calendar-events">
                                <?php foreach ($day_events as $event): ?>
                                <div class="calendar-event">
                                    <div class="event-time">
                                        <?= date('g:i A', strtotime($event['start_time'])) ?> - <?= date('g:i A', strtotime($event['end_time'])) ?>
                                    </div>
                                    <div class="event-title"><?= htmlspecialchars($event['facility_name']) ?></div>
                                    <div class="event-details">
                                        <?= htmlspecialchars($event['customer_name']) ?> • <?= htmlspecialchars($event['event_type']) ?> • <?= $event['guests_count'] ?> guests
                                    </div>
                                </div>
                                <?php endforeach; ?>
                                <?php if (empty($day_events)): ?>
                                <div style="color: #718096; font-style: italic; text-align: center; padding: 1rem;">
                                    <i class="fas fa-calendar-times"></i> No reservations
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endfor; ?>
                    </div>
                </div>

                <!-- Management Tab -->
                <div id="management" class="tab-content">
                    <h2 class="mb-2"><i class="fas fa-cog"></i> System Management</h2>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                        <!-- Facility Management -->
                        <div class="card">
                            <div class="card-header">
                                <h3><i class="fas fa-building"></i> Facility Management</h3>
                            </div>
                            <div class="card-content">
                                <button class="btn btn-primary mb-1" onclick="openModal('facility-modal')">
                                    <i class="fas fa-plus"></i> Add New Facility
                                </button>
                                <div style="max-height: 400px; overflow-y: auto;">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Type</th>
                                                <th>Rate</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($dashboard_data['facilities'] as $facility): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($facility['name']) ?></td>
                                                    <td><?= htmlspecialchars($facility['type']) ?></td>
                                                    <td>₱<?= number_format($facility['hourly_rate'], 2) ?></td>
                                                    <td>
                                                        <span class="status-badge status-<?= $facility['status'] ?? 'active' ?>">
                                                            <?= ucfirst($facility['status'] ?? 'active') ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- System Reports -->
                        <div class="card">
                            <div class="card-header">
                                <h3><i class="fas fa-chart-bar"></i> Quick Reports</h3>
                            </div>
                            <div class="card-content">
                                <div class="d-flex flex-column gap-1">
                                    <div style="padding: 1rem; background: var(--light); border-radius: 8px;">
                                        <strong>Revenue This Month</strong>
                                        <div style="font-size: 1.5rem; font-weight: bold; color: var(--success);">
                                            ₱<?= number_format($dashboard_data['monthly_revenue'], 2) ?>
                                        </div>
                                    </div>
                                    <div style="padding: 1rem; background: var(--light); border-radius: 8px;">
                                        <strong>Pending Approvals</strong>
                                        <div style="font-size: 1.5rem; font-weight: bold; color: var(--warning);">
                                            <?= $dashboard_data['pending_approvals'] ?>
                                        </div>
                                    </div>
                                    <button class="btn btn-outline mt-1" onclick="generateReport()">
                                        <i class="fas fa-file-pdf"></i> Generate Full Report
                                    </button>
                                    <button class="btn btn-outline" onclick="exportData()">
                                        <i class="fas fa-download"></i> Export Data
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modals -->
    <!-- Reservation Modal -->
    <div id="reservation-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Make New Reservation</h3>
                <span class="close" onclick="closeModal('reservation-modal')">&times;</span>
            </div>
            <form id="reservation-form" method="POST">
                <input type="hidden" name="action" value="make_reservation">
                <div class="form-group">
                    <label for="facility_id">Select Facility</label>
                    <select id="facility_id" name="facility_id" class="form-control" required onchange="updateFacilityDetails()">
                        <option value="">Choose a facility...</option>
                        <?php foreach ($dashboard_data['facilities'] as $facility): ?>
                            <option value="<?= $facility['id'] ?>" data-rate="<?= $facility['hourly_rate'] ?>" data-capacity="<?= $facility['capacity'] ?>">
                                <?= htmlspecialchars($facility['name']) ?> - ₱<?= number_format($facility['hourly_rate'], 2) ?>/hour
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div id="facility-details" style="display: none; background: var(--light); padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                    <div><strong><i class="fas fa-users"></i> Capacity:</strong> <span id="capacity-display"></span> people</div>
                    <div><strong><i class="fas fa-dollar-sign"></i> Hourly Rate:</strong> ₱<span id="rate-display"></span></div>
                    <div id="total-cost" style="font-weight: bold; color: var(--success); margin-top: 0.5rem;"></div>
                </div>

                <div class="form-group">
                    <label for="customer_name">Customer Name</label>
                    <input type="text" id="customer_name" name="customer_name" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="customer_email">Email Address</label>
                    <input type="email" id="customer_email" name="customer_email" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="customer_phone">Phone Number</label>
                    <input type="tel" id="customer_phone" name="customer_phone" class="form-control">
                </div>

                <div class="form-group">
                    <label for="event_type">Event Type</label>
                    <select id="event_type" name="event_type" class="form-control" required>
                        <option value="">Select event type...</option>
                        <option value="Wedding">Wedding</option>
                        <option value="Business Meeting">Business Meeting</option>
                        <option value="Conference">Conference</option>
                        <option value="Birthday Party">Birthday Party</option>
                        <option value="Anniversary">Anniversary</option>
                        <option value="Corporate Event">Corporate Event</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="event_date">Event Date</label>
                    <input type="date" id="event_date" name="event_date" class="form-control" required min="<?= date('Y-m-d') ?>" onchange="checkAvailability()">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="start_time">Start Time</label>
                        <input type="time" id="start_time" name="start_time" class="form-control" required onchange="calculateTotal()">
                    </div>
                    <div class="form-group">
                        <label for="end_time">End Time</label>
                        <input type="time" id="end_time" name="end_time" class="form-control" required onchange="calculateTotal()">
                    </div>
                </div>

                <div id="availability-warning" class="alert alert-warning" style="display: none;">
                    <i class="fas fa-exclamation-triangle"></i> <span id="availability-message"></span>
                </div>

                <div class="form-group">
                    <label for="guests_count">Number of Guests</label>
                    <input type="number" id="guests_count" name="guests_count" class="form-control" required min="1" onchange="checkCapacity()">
                    <small id="capacity-warning" style="color: var(--danger); display: none;">
                        <i class="fas fa-exclamation-circle"></i> Exceeds facility capacity!
                    </small>
                </div>

                <div class="form-group">
                    <label for="special_requirements">Special Requirements</label>
                    <textarea id="special_requirements" name="special_requirements" class="form-control" rows="3" placeholder="Any special arrangements or requirements..."></textarea>
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-paper-plane"></i> Submit Reservation Request
                </button>
            </form>
        </div>
    </div>

    <!-- Facility Management Modal -->
    <div id="facility-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add New Facility</h3>
                <span class="close" onclick="closeModal('facility-modal')">&times;</span>
            </div>
            <form id="facility-form" method="POST">
                <input type="hidden" name="action" value="add_facility">
                <div class="form-group">
                    <label for="facility_name">Facility Name</label>
                    <input type="text" id="facility_name" name="name" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="facility_type">Facility Type</label>
                    <select id="facility_type" name="type" class="form-control" required>
                        <option value="">Select type...</option>
                        <option value="banquet">Banquet Hall</option>
                        <option value="meeting">Meeting Room</option>
                        <option value="conference">Conference Room</option>
                        <option value="outdoor">Outdoor Space</option>
                        <option value="dining">Private Dining</option>
                        <option value="lounge">Lounge</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="facility_capacity">Capacity</label>
                    <input type="number" id="facility_capacity" name="capacity" class="form-control" required min="1">
                </div>

                <div class="form-group">
                    <label for="facility_location">Location</label>
                    <input type="text" id="facility_location" name="location" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="facility_description">Description</label>
                    <textarea id="facility_description" name="description" class="form-control" rows="3" required></textarea>
                </div>

                <div class="form-group">
                    <label for="facility_rate">Hourly Rate ($)</label>
                    <input type="number" id="facility_rate" name="hourly_rate" class="form-control" step="0.01" required>
                </div>

                <div class="form-group">
                    <label for="facility_amenities">Amenities</label>
                    <textarea id="facility_amenities" name="amenities" class="form-control" rows="3" placeholder="List amenities separated by commas..."></textarea>
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-plus"></i> Add Facility
                </button>
            </form>
        </div>
    </div>

    <script>
        // Tab navigation
        function switchTab(tabName) {
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.nav-links a').forEach(link => {
                link.classList.remove('active');
            });
            
            document.getElementById(tabName).classList.add('active');
            document.querySelector(`[data-tab="${tabName}"]`).classList.add('active');
            
            // Update page title
            const titles = {
                'dashboard': 'Facilities Reservation Dashboard',
                'facilities': 'Hotel Facilities',
                'reservations': 'Reservation Management',
                'calendar': 'Reservation Calendar',
                'management': 'System Management'
            };
            
            const subtitles = {
                'dashboard': 'Manage hotel facilities and reservations efficiently',
                'facilities': 'Browse and manage all hotel facilities',
                'reservations': 'View and manage all reservations',
                'calendar': 'View upcoming reservations schedule',
                'management': 'System configuration and reports'
            };
            
            document.getElementById('page-title').textContent = titles[tabName] || 'Facilities Reservation System';
            document.getElementById('page-subtitle').textContent = subtitles[tabName] || 'Manage hotel facilities and reservations';
            
            // Save active tab to sessionStorage
            sessionStorage.setItem('activeTab', tabName);
        }

        // Restore active tab on page load
        document.addEventListener('DOMContentLoaded', function() {
            const activeTab = sessionStorage.getItem('activeTab') || 'dashboard';
            switchTab(activeTab);
            
            // Initialize date fields
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('event_date').value = today;
            
            // Set default times
            document.getElementById('start_time').value = '09:00';
            document.getElementById('end_time').value = '12:00';
            
            // Add event listeners to nav links
            document.querySelectorAll('.nav-links a').forEach(link => {
                link.addEventListener('click', function(e) {
                    const href = this.getAttribute('href');
                    const tab = this.getAttribute('data-tab');

                    // If link has a real href (points to a PHP/page), allow normal navigation
                    if (href && href !== '#' && !href.startsWith('javascript:')) {
                        // let browser follow the href (e.g., legalamanagement.php)
                        return;
                    }

                    e.preventDefault();
                    switchTab(tab);
                });
            });
        });

        // Modal functionality
        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        function openReservationModal(facilityId) {
            document.getElementById('facility_id').value = facilityId;
            updateFacilityDetails();
            openModal('reservation-modal');
        }

        // Close modal when clicking outside
        window.addEventListener('click', function(event) {
            if (event.target.classList.contains('modal')) {
                closeModal(event.target.id);
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                document.querySelectorAll('.modal').forEach(modal => {
                    closeModal(modal.id);
                });
            }
        });

        // Facility details and cost calculation
        function updateFacilityDetails() {
            const facilitySelect = document.getElementById('facility_id');
            const selectedOption = facilitySelect.options[facilitySelect.selectedIndex];
            const detailsDiv = document.getElementById('facility-details');
            
            if (selectedOption.value) {
                const rate = selectedOption.getAttribute('data-rate');
                const capacity = selectedOption.getAttribute('data-capacity');
                
                document.getElementById('capacity-display').textContent = capacity;
                document.getElementById('rate-display').textContent = rate;
                detailsDiv.style.display = 'block';
                
                calculateTotal();
                checkAvailability();
            } else {
                detailsDiv.style.display = 'none';
            }
        }

        function calculateTotal() {
            const facilitySelect = document.getElementById('facility_id');
            const startTime = document.getElementById('start_time').value;
            const endTime = document.getElementById('end_time').value;
            const totalCost = document.getElementById('total-cost');
            
            if (facilitySelect.value && startTime && endTime) {
                const rate = parseFloat(facilitySelect.options[facilitySelect.selectedIndex].getAttribute('data-rate'));
                const start = new Date('2000-01-01 ' + startTime);
                const end = new Date('2000-01-01 ' + endTime);
                
                let hours = (end - start) / (1000 * 60 * 60);
                if (hours < 0) hours += 24; // Handle overnight
                
                const total = hours * rate;
                totalCost.innerHTML = `<i class="fas fa-calculator"></i> Estimated Total: ₱${total.toFixed(2)} (${Math.ceil(hours)} hours)`;
            }
        }

        function checkCapacity() {
            const facilitySelect = document.getElementById('facility_id');
            const guests = document.getElementById('guests_count').value;
            const warning = document.getElementById('capacity-warning');
            
            if (facilitySelect.value && guests) {
                const capacity = parseInt(facilitySelect.options[facilitySelect.selectedIndex].getAttribute('data-capacity'));
                if (parseInt(guests) > capacity) {
                    warning.style.display = 'block';
                } else {
                    warning.style.display = 'none';
                }
            }
        }

        async function checkAvailability() {
            const facilityId = document.getElementById('facility_id').value;
            const eventDate = document.getElementById('event_date').value;
            const warningDiv = document.getElementById('availability-warning');
            
            if (!facilityId || !eventDate) return;
            
            try {
                const formData = new FormData();
                formData.append('action', 'check_availability');
                formData.append('facility_id', facilityId);
                formData.append('event_date', eventDate);
                
                const response = await fetch('', {
                    method: 'POST',
                    body: formData
                });
                
                const bookedSlots = await response.json();
                const startTime = document.getElementById('start_time').value;
                const endTime = document.getElementById('end_time').value;
                
                if (startTime && endTime) {
                    const conflict = bookedSlots.some(slot => {
                        return (startTime < slot.end_time && endTime > slot.start_time);
                    });
                    
                    if (conflict) {
                        warningDiv.style.display = 'block';
                        document.getElementById('availability-message').textContent = 
                            'Warning: This time slot may conflict with existing reservations.';
                    } else {
                        warningDiv.style.display = 'none';
                    }
                }
            } catch (error) {
                console.error('Error checking availability:', error);
            }
        }

        // Form submissions with validation
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Basic validation
                const requiredFields = form.querySelectorAll('[required]');
                let valid = true;
                
                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        valid = false;
                        field.style.borderColor = 'var(--danger)';
                    } else {
                        field.style.borderColor = '';
                    }
                });
                
                if (!valid) {
                    alert('Please fill in all required fields.');
                    return;
                }
                
                // Show loading state
                const submitBtn = form.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                submitBtn.disabled = true;
                
                const formData = new FormData(this);
                
                fetch('', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    location.reload();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error saving data. Please try again.');
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                });
            });
        });

        // Update reservation status
        function updateReservationStatus(reservationId, status) {
            const statusText = status === 'confirmed' ? 'confirm' : 
                             status === 'cancelled' ? 'cancel' : 'complete';
                             
            if (confirm(`Are you sure you want to ${statusText} this reservation?`)) {
                const formData = new FormData();
                formData.append('action', 'update_reservation_status');
                formData.append('reservation_id', reservationId);
                formData.append('status', status);
                
                fetch('', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    location.reload();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error updating reservation status.');
                });
            }
        }

        // View reservation details
        function viewReservationDetails(reservationId) {
            alert(`Viewing details for reservation #${reservationId}. This would typically open a detailed view modal.`);
            // Implementation for detailed view would go here
        }

        // Generate report
        function generateReport() {
            alert('Generating comprehensive facilities report... This would typically download a PDF report.');
            // Implementation for report generation would go here
        }

        // Export data
        function exportData() {
            alert('Exporting system data... This would typically download a CSV or Excel file.');
            // Implementation for data export would go here
        }

        // Mobile sidebar functionality
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('sidebar-open');
        }

        function closeSidebar() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.remove('sidebar-open');
        }

        // Auto-refresh data every 5 minutes
        setInterval(() => {
            // In a real application, this would refresh specific data components
            console.log('Auto-refresh check...');
        }, 300000); // 5 minutes
    </script>
</body>
</html>