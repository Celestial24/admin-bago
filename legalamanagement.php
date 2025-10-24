<?php
// config.php
class Database {
    private $host = "localhost";
    private $db_name = "legal_management_system";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        return $this->conn;
    }
}

// AI Risk Assessment Class
class ContractRiskAnalyzer {
    private $riskFactors = [
        'financial_terms' => [
            'long_term_lease' => ['weight' => 15, 'high_risk' => 'Lease term > 10 years'],
            'unfavorable_rent' => ['weight' => 10, 'high_risk' => 'Guaranteed minimum rent + revenue share'],
            'hidden_fees' => ['weight' => 8, 'high_risk' => 'Undisclosed additional charges'],
            'security_deposit' => ['weight' => 7, 'high_risk' => 'Security deposit > 6 months']
        ],
        'operational_control' => [
            'restrictive_hours' => ['weight' => 8, 'high_risk' => 'Limited operating hours'],
            'supplier_restrictions' => ['weight' => 10, 'high_risk' => 'Exclusive supplier requirements'],
            'renovation_limits' => ['weight' => 7, 'high_risk' => 'Strict renovation restrictions'],
            'staffing_controls' => ['weight' => 5, 'high_risk' => 'Limited staffing autonomy']
        ],
        'legal_protection' => [
            'unlimited_liability' => ['weight' => 12, 'high_risk' => 'Unlimited liability clauses'],
            'personal_guarantee' => ['weight' => 10, 'high_risk' => 'Personal guarantees required'],
            'unilateral_amendments' => ['weight' => 8, 'high_risk' => 'Unilateral amendment rights'],
            'dispute_resolution' => ['weight' => 6, 'high_risk' => 'Unfavorable dispute resolution']
        ],
        'flexibility_exit' => [
            'termination_penalties' => ['weight' => 8, 'high_risk' => 'Heavy termination penalties'],
            'renewal_restrictions' => ['weight' => 6, 'high_risk' => 'Automatic renewal without notice'],
            'assignment_rights' => ['weight' => 4, 'high_risk' => 'Limited assignment rights'],
            'force_majeure' => ['weight' => 2, 'high_risk' => 'No force majeure clause']
        ]
    ];

    public function analyzeContract($contractData) {
        $totalScore = 0;
        $maxPossibleScore = 0;
        $riskFactorsFound = [];
        $recommendations = [];

        foreach ($this->riskFactors as $category => $factors) {
            foreach ($factors as $factorKey => $factor) {
                $maxPossibleScore += $factor['weight'];
                
                // Simulate AI detection - in real implementation, this would analyze contract text
                if ($this->detectRiskFactor($contractData, $factorKey)) {
                    $totalScore += $factor['weight'];
                    $riskFactorsFound[] = [
                        'category' => $category,
                        'factor' => $factor['high_risk'],
                        'weight' => $factor['weight']
                    ];
                }
            }
        }

        // Calculate risk percentage
        $riskPercentage = ($totalScore / $maxPossibleScore) * 100;
        
        // Determine risk level
        if ($riskPercentage >= 70) {
            $riskLevel = 'High';
            $recommendations = $this->getHighRiskRecommendations();
        } elseif ($riskPercentage >= 31) {
            $riskLevel = 'Medium';
            $recommendations = $this->getMediumRiskRecommendations();
        } else {
            $riskLevel = 'Low';
            $recommendations = $this->getLowRiskRecommendations();
        }

        return [
            'risk_score' => round($riskPercentage),
            'risk_level' => $riskLevel,
            'risk_factors' => $riskFactorsFound,
            'recommendations' => $recommendations,
            'analysis_summary' => $this->generateAnalysisSummary($riskLevel, $riskFactorsFound)
        ];
    }

    private function detectRiskFactor($contractData, $factorKey) {
        // Simulated AI detection - in production, this would use NLP/text analysis
        $keywords = [
            'long_term_lease' => ['10 years', '15 years', '20 years', 'long-term', 'extended term'],
            'unfavorable_rent' => ['minimum rent', 'revenue share', 'percentage of sales', 'guaranteed payment'],
            'hidden_fees' => ['additional charges', 'hidden costs', 'undisclosed fees', 'extra payments'],
            'security_deposit' => ['security deposit', '6 months', 'advance payment', 'deposit amount'],
            'restrictive_hours' => ['operating hours', 'business hours', 'time restrictions', 'hour limitations'],
            'supplier_restrictions' => ['exclusive supplier', 'approved vendors', 'vendor restrictions', 'supplier limitations'],
            'renovation_limits' => ['renovation restrictions', 'modification limits', 'alteration approval', 'structural changes'],
            'staffing_controls' => ['staff approval', 'employee restrictions', 'hiring limitations', 'personnel controls'],
            'unlimited_liability' => ['unlimited liability', 'full responsibility', 'complete liability', 'total responsibility'],
            'personal_guarantee' => ['personal guarantee', 'individual assurance', 'personal commitment', 'individual warranty'],
            'unilateral_amendments' => ['unilateral amendment', 'one-sided changes', 'sole discretion', 'exclusive right'],
            'termination_penalties' => ['termination fee', 'early termination', 'cancellation penalty', 'break clause fee'],
            'renewal_restrictions' => ['automatic renewal', 'auto-renew', 'automatic extension', 'self-renewing']
        ];

        $contractText = strtolower($contractData['contract_name'] . ' ' . $contractData['description']);
        
        if (isset($keywords[$factorKey])) {
            foreach ($keywords[$factorKey] as $keyword) {
                if (strpos($contractText, strtolower($keyword)) !== false) {
                    return true;
                }
            }
        }

        // Random factor for demo purposes - remove in production
        return rand(0, 100) < 30; // 30% chance to detect a risk factor for demo
    }

    private function getHighRiskRecommendations() {
        return [
            'Immediate legal review required',
            'Negotiate key risk clauses',
            'Consider alternative agreements',
            'Implement risk mitigation strategies',
            'Regular compliance monitoring'
        ];
    }

    private function getMediumRiskRecommendations() {
        return [
            'Standard legal review recommended',
            'Clarify ambiguous terms',
            'Document all understandings',
            'Establish monitoring procedures',
            'Plan for periodic reviews'
        ];
    }

    private function getLowRiskRecommendations() {
        return [
            'Routine monitoring sufficient',
            'Maintain proper documentation',
            'Schedule annual reviews',
            'Monitor regulatory changes',
            'Standard compliance procedures'
        ];
    }

    private function generateAnalysisSummary($riskLevel, $riskFactors) {
        $factorCount = count($riskFactors);
        
        if ($riskLevel === 'High') {
            return "Critical risk level detected with {$factorCount} high-risk factors requiring immediate attention.";
        } elseif ($riskLevel === 'Medium') {
            return "Moderate risk level with {$factorCount} risk factors needing standard review.";
        } else {
            return "Low risk level with minimal risk factors. Standard monitoring recommended.";
        }
    }
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $database = new Database();
    $db = $database->getConnection();
    
    if (isset($_POST['add_employee'])) {
        $name = $_POST['employee_name'];
        $position = $_POST['employee_position'];
        $email = $_POST['employee_email'];
        $phone = $_POST['employee_phone'];
        
        $query = "INSERT INTO employees (name, position, email, phone) VALUES (?, ?, ?, ?)";
        $stmt = $db->prepare($query);
        
        if ($stmt->execute([$name, $position, $email, $phone])) {
            $success_message = "Employee added successfully!";
        } else {
            $error_message = "Failed to add employee.";
        }
    }
    
    // Handle contract upload with AI analysis
    if (isset($_POST['add_contract'])) {
        $contract_name = $_POST['contract_name'];
        $case_id = $_POST['contract_case'];
        $description = $_POST['contract_description'] ?? '';
        
        // AI Risk Analysis
        $analyzer = new ContractRiskAnalyzer();
        $contractData = [
            'contract_name' => $contract_name,
            'description' => $description
        ];
        
        $riskAnalysis = $analyzer->analyzeContract($contractData);
        
        // Handle file upload
        $file_name = '';
        if (isset($_FILES['contract_file']) && $_FILES['contract_file']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/contracts/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_tmp_name = $_FILES['contract_file']['tmp_name'];
            $file_original_name = $_FILES['contract_file']['name'];
            $file_extension = pathinfo($file_original_name, PATHINFO_EXTENSION);
            $file_name = uniqid() . '_' . $contract_name . '.' . $file_extension;
            $file_path = $upload_dir . $file_name;
            
            if (move_uploaded_file($file_tmp_name, $file_path)) {
                $file_name = $file_path;
            } else {
                $error_message = "Failed to upload file.";
            }
        }
        
        $query = "INSERT INTO contracts (contract_name, case_id, description, file_path, risk_level, risk_score, risk_factors, recommendations, analysis_summary) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($query);
        
        $risk_factors_json = json_encode($riskAnalysis['risk_factors']);
        $recommendations_json = json_encode($riskAnalysis['recommendations']);
        
        if ($stmt->execute([
            $contract_name, 
            $case_id, 
            $description,
            $file_name, 
            $riskAnalysis['risk_level'], 
            $riskAnalysis['risk_score'],
            $risk_factors_json,
            $recommendations_json,
            $riskAnalysis['analysis_summary']
        ])) {
            $success_message = "Contract uploaded successfully! AI Risk Analysis Completed.";
        } else {
            $error_message = "Failed to upload contract.";
        }
    }

    // Handle PDF Export (Idinagdag para sa PDF Report na may Password)
    if (isset($_POST['action']) && $_POST['action'] === 'export_pdf') {
        $password = 'legal2025'; // Password para sa PDF Report (Simulasyon)
        
        // Kunin ang lahat ng data ng kontrata para sa ulat
        $query = "SELECT contract_name, risk_level, risk_score, analysis_summary FROM contracts ORDER BY created_at DESC";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $contracts_to_report = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // --- SIMULASYON NG PDF GENERATION (Dahil hindi available ang external libraries) ---
        
        // I-set ang headers para sa pag-download ng file (ginamit ang .txt para sa simulation)
        header('Content-Type: application/octet-stream'); 
        header('Content-Disposition: attachment; filename="Legal_Contracts_Report_Protected.txt"');
        
        // Mag-output ng simpleng text na nagsasabi na nag-generate ng protected file
        echo "========================================================\n";
        echo "== NAKA-PROTEKTANG PDF REPORT NG KONTRATA (SIMULASYON) ==\n";
        echo "========================================================\n\n";
        echo "Ipinagbabawal ang pagtingin nang walang pahintulot.\n";
        echo "Ito ay naglalaman ng sensitibong legal na impormasyon.\n\n";
        echo "========================================================\n";
        echo "PASSWORD SA PAGBUKAS NG PDF (Ito ang kailangan mo sa totoong PDF): " . $password . "\n";
        echo "========================================================\n\n";
        
        echo "Kontrata sa Report:\n";
        foreach ($contracts_to_report as $contract) {
            echo "- " . $contract['contract_name'] . " (Risk: " . $contract['risk_level'] . ", Score: " . $contract['risk_score'] . "/100)\n";
            echo "  Buod ng Pagsusuri: " . $contract['analysis_summary'] . "\n";
        }
        
        exit;
    }
}

// Fetch employees from database
$database = new Database();
$db = $database->getConnection();
$employees = [];
$contracts = [];

try {
    $query = "SELECT * FROM employees";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $exception) {
    $error_message = "Error fetching employees: " . $exception->getMessage();
}

// Fetch contracts from database
try {
    $query = "SELECT * FROM contracts ORDER BY created_at DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $contracts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $exception) {
    $error_message = "Error fetching contracts: " . $exception->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Legal Management System - Hotel & Restaurant</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Global Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Login Screen */
        .login-container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: linear-gradient(135deg, #1a2a6c, #b21f1f, #fdbb2d);
        }

        .login-form {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        .login-form h2 {
            margin-bottom: 30px;
            color: #333;
        }

        .pin-input {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }

        .pin-digit {
            width: 50px;
            height: 50px;
            margin: 0 5px;
            text-align: center;
            font-size: 24px;
            border: 2px solid #ddd;
            border-radius: 5px;
            outline: none;
            transition: border-color 0.3s;
        }

        .pin-digit:focus {
            border-color: #4a6cf7;
        }

        .login-btn {
            background: #4a6cf7;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
            transition: background 0.3s;
        }

        .login-btn:hover {
            background: #3a5bd9;
        }

        .error-message {
            color: #e74c3c;
            margin-top: 10px;
            display: none;
        }

        /* Dashboard */
        .dashboard {
            display: none;
        }

        .header {
            background: white;
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #4a6cf7;
        }

        .user-info {
            display: flex;
            align-items: center;
        }

        .logout-btn {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            margin-left: 15px;
        }

        /* Navigation */
        .nav-tabs {
            display: flex;
            background: white;
            border-radius: 5px;
            overflow: hidden;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .nav-tab {
            flex: 1;
            text-align: center;
            padding: 15px;
            cursor: pointer;
            transition: background 0.3s;
            border-bottom: 3px solid transparent;
        }

        .nav-tab.active {
            background: #f0f4ff;
            border-bottom: 3px solid #4a6cf7;
            color: #4a6cf7;
            font-weight: bold;
        }

        .nav-tab:hover:not(.active) {
            background: #f8f9fa;
        }

        /* Content Sections */
        .content-section {
            display: none;
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .content-section.active {
            display: block;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .section-title {
            font-size: 22px;
            color: #333;
        }

        .add-btn {
            background: #2ecc71;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
        }

        .add-btn i {
            margin-right: 5px;
        }

        /* Tables */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .data-table th, .data-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .data-table th {
            background: #f8f9fa;
            font-weight: 600;
        }

        .data-table tr:hover {
            background: #f8f9fa;
        }

        .action-btn {
            background: none;
            border: none;
            cursor: pointer;
            margin-right: 10px;
            color: #4a6cf7;
        }

        .delete-btn {
            color: #e74c3c;
        }

        /* Forms */
        .form-container {
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            display: none;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 20px;
        }

        .cancel-btn {
            background: #95a5a6;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            margin-right: 10px;
        }

        .save-btn {
            background: #4a6cf7;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
        }

        /* Status Badges */
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-high {
            background: #ffeaa7;
            color: #e17055;
        }

        .status-medium {
            background: #81ecec;
            color: #00cec9;
        }

        .status-low {
            background: #55efc4;
            color: #00b894;
        }

        .status-open {
            background: #ffeaa7;
            color: #e17055;
        }

        .status-closed {
            background: #55efc4;
            color: #00b894;
        }

        .status-pending {
            background: #81ecec;
            color: #00cec9;
        }

        /* Error and Success Messages */
        .alert {
            padding: 12px 15px;
            border-radius: 5px;
            margin-bottom: 15px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .error-text {
            color: #e74c3c;
            font-size: 14px;
            margin-top: 5px;
        }

        .form-control.error {
            border-color: #e74c3c;
        }

        /* File Upload Styles */
        .file-info {
            margin-top: 5px;
            font-size: 14px;
            color: #666;
        }

        /* AI Analysis Styles */
        .ai-analysis-section {
            background: #f8f9fa;
            border-left: 4px solid #4a6cf7;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
        }
        
        .risk-factors {
            margin: 10px 0;
        }
        
        .risk-factor-item {
            background: white;
            padding: 8px 12px;
            margin: 5px 0;
            border-radius: 4px;
            border-left: 3px solid #e74c3c;
        }
        
        .recommendation-item {
            background: #e8f4fd;
            padding: 8px 12px;
            margin: 5px 0;
            border-radius: 4px;
            border-left: 3px solid #3498db;
        }
        
        .ai-badge {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            margin-left: 10px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .nav-tabs {
                flex-direction: column;
            }
            
            .header-content {
                flex-direction: column;
                text-align: center;
            }
            
            .user-info {
                margin-top: 10px;
            }
        }
    </style>
</head>
<body>
    <!-- Login Screen -->
    <div class="login-container" id="loginScreen">
        <div class="login-form">
            <h2>Legal Management System</h2>
            <p>Enter your PIN to access the system</p>
            <div class="pin-input">
                <input type="password" maxlength="1" class="pin-digit" id="pin1">
                <input type="password" maxlength="1" class="pin-digit" id="pin2">
                <input type="password" maxlength="1" class="pin-digit" id="pin3">
                <input type="password" maxlength="1" class="pin-digit" id="pin4">
            </div>
            <button class="login-btn" id="loginBtn">Login</button>
            <div class="error-message" id="errorMessage">Invalid PIN. Please try again.</div>
        </div>
    </div>

    <!-- Dashboard -->
    <div class="dashboard" id="dashboard">
        <div class="header">
            <div class="container">
                <div class="header-content">
                    <div class="logo">Legal Management System</div>
                    <div class="user-info">
                        <span>Welcome, Admin</span>
                        <button class="logout-btn" id="logoutBtn">Logout</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="container">
            <!-- Success/Error Messages -->
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-error"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <div class="nav-tabs">
                <div class="nav-tab active" data-target="employees">Employees</div>
                <div class="nav-tab" data-target="documents">Documents</div>
                <div class="nav-tab" data-target="billing">Billing</div>
                <div class="nav-tab" data-target="contracts">Contracts</div>
                <div class="nav-tab" data-target="risk_analysis">Risk Analysis</div>
                <div class="nav-tab" data-target="members">Members</div>
            </div>

            <!-- Employees Section -->
            <div class="content-section active" id="employees">
                <div class="section-header">
                    <h2 class="section-title">Employee Information</h2>
                    <button class="add-btn" id="addEmployeeBtn">
                        <i>+</i> Add Employee
                    </button>
                </div>

                <!-- Add Employee Form -->
                <div class="form-container" id="employeeForm">
                    <h3>Add Employee</h3>
                    <form method="POST" id="employeeFormData">
                        <div class="form-group">
                            <label for="employeeName">Name</label>
                            <input type="text" id="employeeName" name="employee_name" class="form-control" placeholder="Enter employee name" required>
                        </div>
                        <div class="form-group">
                            <label for="employeePosition">Position</label>
                            <input type="text" id="employeePosition" name="employee_position" class="form-control" placeholder="Enter position" required>
                        </div>
                        <div class="form-group">
                            <label for="employeeEmail">Email</label>
                            <input type="email" id="employeeEmail" name="employee_email" class="form-control" placeholder="Enter email" required>
                        </div>
                        <div class="form-group">
                            <label for="employeePhone">Phone</label>
                            <input type="text" id="employeePhone" name="employee_phone" class="form-control" placeholder="Enter phone number" required>
                        </div>
                        <div class="form-actions">
                            <button type="button" class="cancel-btn" id="cancelEmployeeBtn">Cancel</button>
                            <button type="submit" class="save-btn" name="add_employee" id="saveEmployeeBtn">Save Employee</button>
                        </div>
                    </form>
                </div>

                <!-- Employees Table -->
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Employee ID</th>
                            <th>Name</th>
                            <th>Position</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="employeesTableBody">
                        <?php foreach ($employees as $employee): ?>
                            <tr>
                                <td>E-<?php echo str_pad($employee['id'], 3, '0', STR_PAD_LEFT); ?></td>
                                <td><?php echo htmlspecialchars($employee['name']); ?></td>
                                <td><?php echo htmlspecialchars($employee['position']); ?></td>
                                <td><?php echo htmlspecialchars($employee['email']); ?></td>
                                <td><?php echo htmlspecialchars($employee['phone']); ?></td>
                                <td>
                                    <button class="action-btn view-btn">View</button>
                                    <button class="action-btn">Edit</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Documents Section -->
            <div class="content-section" id="documents">
                <div class="section-header">
                    <h2 class="section-title">Case Documents</h2>
                    <button class="add-btn" id="addDocumentBtn">
                        <i>+</i> Upload Document
                    </button>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Document Name</th>
                            <th>Case</th>
                            <th>Date Uploaded</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="documentsTableBody">
                        <!-- Documents will be populated here -->
                    </tbody>
                </table>
            </div>

            <div class="content-section" id="billing">
                <div class="section-header">
                    <h2 class="section-title">Billing & Invoices</h2>
                    <button class="add-btn" id="addInvoiceBtn">
                        <i>+</i> Create Invoice
                    </button>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th>Client</th>
                            <th>Amount</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="billingTableBody">
                        <!-- Billing records will be populated here -->
                    </tbody>
                </table>
            </div>

            <!-- Contracts Section -->
            <div class="content-section" id="contracts">
                <div class="section-header">
                    <h2 class="section-title">Contracts <span class="ai-badge">AI-Powered Analysis</span></h2>
                    <div style="display: flex; gap: 10px;">
                        <!-- Button para sa Secured PDF Report (Idinagdag) -->
                        <button class="add-btn" id="exportPdfBtn" style="background: #e74c3c; /* Pula para sa ulat */">
                            &#x1F4C4; Generate Secured PDF
                        </button>
                        <button class="add-btn" id="addContractBtn">
                            <i>+</i> Upload Contract
                        </button>
                    </div>
                </div>

                <!-- Add Contract Form -->
                <div class="form-container" id="contractForm">
                    <h3>Upload Contract <span class="ai-badge">AI Risk Analysis</span></h3>
                    <form method="POST" enctype="multipart/form-data" id="contractFormData">
                        <div class="form-group">
                            <label for="contractName">Contract Name</label>
                            <input type="text" id="contractName" name="contract_name" class="form-control" placeholder="Enter contract name" required>
                        </div>
                        <div class="form-group">
                            <label for="contractCase">Case ID</label>
                            <input type="text" id="contractCase" name="contract_case" class="form-control" placeholder="Enter case ID (e.g., C-001)" required>
                        </div>
                        <div class="form-group">
                            <label for="contractDescription">Contract Description</label>
                            <textarea id="contractDescription" name="contract_description" class="form-control" placeholder="Describe the contract terms, key clauses, and important details for AI analysis" rows="4"></textarea>
                            <div class="file-info">AI will analyze this description to detect risk factors</div>
                        </div>
                        <div class="form-group">
                            <label for="contractFile">Contract File</label>
                            <input type="file" id="contractFile" name="contract_file" class="form-control" accept=".pdf,.doc,.docx" required>
                            <div class="file-info">Accepted formats: PDF, DOC, DOCX (Max: 10MB)</div>
                        </div>
                        
                        <div class="ai-analysis-section">
                            <h4>､AI Risk Assessment</h4>
                            <p><strong>Note:</strong> Our AI system will automatically analyze your contract for:</p>
                            <ul>
                                <li>Financial risk factors (lease terms, rent structure)</li>
                                <li>Operational restrictions (hours, suppliers, staffing)</li>
                                <li>Legal protection issues (liability, guarantees)</li>
                                <li>Flexibility and exit concerns</li>
                            </ul>
                            <p><em>Risk score and level will be automatically calculated</em></p>
                        </div>
                        
                        <div class="form-actions">
                            <button type="button" class="cancel-btn" id="cancelContractBtn">Cancel</button>
                            <button type="submit" class="save-btn" name="add_contract" id="saveContractBtn">
                                <i>､/i> Upload & Analyze Contract
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Contracts Table -->
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Contract Name</th>
                            <th>Case</th>
                            <th>Risk Level</th>
                            <th>Risk Score</th>
                            <th>Upload Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="contractsTableBody">
                        <?php foreach ($contracts as $contract): 
                            $risk_factors = json_decode($contract['risk_factors'] ?? '[]', true);
                            $recommendations = json_decode($contract['recommendations'] ?? '[]', true);
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($contract['contract_name']); ?></td>
                                <td><?php echo htmlspecialchars($contract['case_id']); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower($contract['risk_level']); ?>">
                                        <?php echo htmlspecialchars($contract['risk_level']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($contract['risk_score']); ?>/100</td>
                                <td><?php echo date('Y-m-d', strtotime($contract['created_at'])); ?></td>
                                <td>
                                    <button class="action-btn view-btn">View</button>
                                    <button class="action-btn analyze-btn" data-contract='<?php echo htmlspecialchars(json_encode($contract)); ?>'>Analyze</button>
                                    <?php if (!empty($contract['file_path'])): ?>
                                        <button class="action-btn download-btn" data-file="<?php echo htmlspecialchars($contract['file_path']); ?>">Download</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="content-section" id="risk_analysis">
                <div class="section-header">
                    <h2 class="section-title">Contract Risk Analysis</h2>
                </div>
                <div id="riskChartContainer">
                    <canvas id="riskChart" width="400" height="200"></canvas>
                </div>
                <div id="analysisResults">
                    <!-- Analysis results will be displayed here -->
                </div>
            </div>

            <div class="content-section" id="members">
                <div class="section-header">
                    <h2 class="section-title">Team Members</h2>
                    <button class="add-btn" id="addMemberBtn">
                        <i>+</i> Add Member
                    </button>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Position</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="membersTableBody">
                        <!-- Members will be populated here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // PIN Authentication
        document.addEventListener('DOMContentLoaded', function() {
            const pinInputs = document.querySelectorAll('.pin-digit');
            const loginBtn = document.getElementById('loginBtn');
            const errorMessage = document.getElementById('errorMessage');
            const loginScreen = document.getElementById('loginScreen');
            const dashboard = document.getElementById('dashboard');
            const logoutBtn = document.getElementById('logoutBtn');
            
            // Correct PIN (in a real application, this would be stored securely)
            const correctPIN = '1234';
            
            // Focus on first PIN input
            pinInputs[0].focus();
            
            // Move to next input when a digit is entered
            pinInputs.forEach((input, index) => {
                input.addEventListener('input', function() {
                    if (this.value.length === 1 && index < pinInputs.length - 1) {
                        pinInputs[index + 1].focus();
                    }
                });
                
                // Allow backspace to move to previous input
                input.addEventListener('keydown', function(e) {
                    if (e.key === 'Backspace' && this.value.length === 0 && index > 0) {
                        pinInputs[index - 1].focus();
                    }
                });
            });
            
            // Login functionality
            loginBtn.addEventListener('click', function() {
                const enteredPIN = Array.from(pinInputs).map(input => input.value).join('');
                
                if (enteredPIN === correctPIN) {
                    // Successful login
                    loginScreen.style.display = 'none';
                    dashboard.style.display = 'block';
                    
                    // Initialize dashboard data
                    initializeDashboard();
                } else {
                    // Failed login
                    errorMessage.style.display = 'block';
                    pinInputs.forEach(input => {
                        input.value = '';
                    });
                    pinInputs[0].focus();
                }
            });
            
            // Logout functionality
            logoutBtn.addEventListener('click', function() {
                dashboard.style.display = 'none';
                loginScreen.style.display = 'flex';
                
                // Clear PIN inputs
                pinInputs.forEach(input => {
                    input.value = '';
                });
                pinInputs[0].focus();
                errorMessage.style.display = 'none';
            });
            
            // Navigation tabs
            const navTabs = document.querySelectorAll('.nav-tab');
            const contentSections = document.querySelectorAll('.content-section');
            
            navTabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    const targetId = this.getAttribute('data-target');
                    
                    // Update active tab
                    navTabs.forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                    
                    // Show corresponding content section
                    contentSections.forEach(section => {
                        section.classList.remove('active');
                        if (section.id === targetId) {
                            section.classList.add('active');
                        }
                    });
                });
            });
            
            // Initialize dashboard with sample data
            function initializeDashboard() {
                // Sample data for other sections
                const documents = [
                    { name: 'Employment Contract.pdf', case: 'C-001', date: '2023-05-20' },
                    { name: 'Supplier Agreement.docx', case: 'C-002', date: '2023-06-25' }
                ];
                
                const billing = [
                    { invoice: 'INV-001', client: 'Hotel Management', amount: '$2,500', dueDate: '2023-07-15', status: 'paid' },
                    { invoice: 'INV-002', client: 'Restaurant Owner', amount: '$1,800', dueDate: '2023-08-05', status: 'pending' }
                ];

                const members = [
                    { name: 'Robert Wilson', position: 'Senior Legal Counsel', email: 'robert@legalteam.com', phone: '(555) 111-2222' },
                    { name: 'Emily Davis', position: 'Legal Assistant', email: 'emily@legalteam.com', phone: '(555) 333-4444' }
                ];

                // Populate tables with data
                populateTable('documentsTableBody', documents, 'document');
                populateTable('billingTableBody', billing, 'billing');
                populateTable('membersTableBody', members, 'member');

                // Initialize risk analysis chart with real contract data
                initializeRiskChart();

                // Set up form handlers
                setupFormHandlers();
            }
            
            // Function to populate tables with data
            function populateTable(tableId, data, type) {
                const tableBody = document.getElementById(tableId);
                if (!tableBody) return;
                
                tableBody.innerHTML = '';
                
                data.forEach(item => {
                    const row = document.createElement('tr');
                    
                    if (type === 'document') {
                        row.innerHTML = `
                            <td>${item.name}</td>
                            <td>${item.case}</td>
                            <td>${item.date}</td>
                            <td><button class="action-btn view-btn">View</button></td>
                        `;
                    } else if (type === 'billing') {
                        const statusClass = `status-${item.status}`;
                        row.innerHTML = `
                            <td>${item.invoice}</td>
                            <td>${item.client}</td>
                            <td>${item.amount}</td>
                            <td>${item.dueDate}</td>
                            <td><span class="status-badge ${statusClass}">${item.status}</span></td>
                            <td>
                                <button class="action-btn view-btn">View</button>
                                <button class="action-btn">Edit</button>
                            </td>
                        `;
                    } else if (type === 'member') {
                        row.innerHTML = `
                            <td>${item.name}</td>
                            <td>${item.position}</td>
                            <td>${item.email}</td>
                            <td>${item.phone}</td>
                            <td>
                                <button class="action-btn view-btn">View</button>
                                <button class="action-btn">Edit</button>
                            </td>
                        `;
                    }
                    
                    tableBody.appendChild(row);
                });
            }

            // Function to initialize risk analysis chart with real data
            function initializeRiskChart() {
                const ctx = document.getElementById('riskChart');
                if (!ctx) return;

                const chartCtx = ctx.getContext('2d');

                // Get risk levels from actual contracts data
                const contracts = <?php echo json_encode($contracts); ?>;
                const riskCounts = { High: 0, Medium: 0, Low: 0 };
                
                contracts.forEach(contract => {
                    if (riskCounts.hasOwnProperty(contract.risk_level)) {
                        riskCounts[contract.risk_level]++;
                    }
                });

                const chart = new Chart(chartCtx, {
                    type: 'bar',
                    data: {
                        labels: ['High Risk', 'Medium Risk', 'Low Risk'],
                        datasets: [{
                            label: 'Number of Contracts',
                            data: [riskCounts.High, riskCounts.Medium, riskCounts.Low],
                            backgroundColor: [
                                'rgba(231, 76, 60, 0.6)',
                                'rgba(241, 196, 15, 0.6)',
                                'rgba(46, 204, 113, 0.6)'
                            ],
                            borderColor: [
                                'rgba(231, 76, 60, 1)',
                                'rgba(241, 196, 15, 1)',
                                'rgba(46, 204, 113, 1)'
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top'
                            },
                            title: {
                                display: true,
                                text: 'Contract Risk Distribution'
                            }
                        }
                    }
                });

                // Display analysis results
                const totalContracts = contracts.length;
                const highRiskPercentage = totalContracts > 0 ? ((riskCounts.High / totalContracts) * 100).toFixed(1) : 0;
                const analysisResults = document.getElementById('analysisResults');
                if (analysisResults) {
                    analysisResults.innerHTML = `
                        <h3>Risk Analysis Summary</h3>
                        <p>Total Contracts: ${totalContracts}</p>
                        <p>High Risk Contracts: ${riskCounts.High} (${highRiskPercentage}%)</p>
                        <p>Medium Risk Contracts: ${riskCounts.Medium}</p>
                        <p>Low Risk Contracts: ${riskCounts.Low}</p>
                        <p><strong>Recommendation:</strong> ${riskCounts.High > 0 ? 'Review high-risk contracts immediately.' : 'All contracts are within acceptable risk levels.'}</p>
                    `;
                }
            }

            // Enhanced Form Handlers
            function setupFormHandlers() {
                // Employee form handlers
                const addEmployeeBtn = document.getElementById('addEmployeeBtn');
                const employeeForm = document.getElementById('employeeForm');
                const cancelEmployeeBtn = document.getElementById('cancelEmployeeBtn');
                const employeeFormData = document.getElementById('employeeFormData');

                if (addEmployeeBtn && employeeForm) {
                    addEmployeeBtn.addEventListener('click', function() {
                        employeeForm.style.display = 'block';
                        employeeForm.scrollIntoView({ behavior: 'smooth' });
                    });
                }

                if (cancelEmployeeBtn && employeeForm) {
                    cancelEmployeeBtn.addEventListener('click', function() {
                        employeeForm.style.display = 'none';
                        resetEmployeeForm();
                    });
                }

                if (employeeFormData) {
                    employeeFormData.addEventListener('submit', function(e) {
                        e.preventDefault();
                        if (validateEmployeeForm()) {
                            this.submit();
                        }
                    });
                }

                // Contract form handlers
                const addContractBtn = document.getElementById('addContractBtn');
                const contractForm = document.getElementById('contractForm');
                const cancelContractBtn = document.getElementById('cancelContractBtn');
                const contractFormData = document.getElementById('contractFormData');

                if (addContractBtn && contractForm) {
                    addContractBtn.addEventListener('click', function() {
                        contractForm.style.display = 'block';
                        contractForm.scrollIntoView({ behavior: 'smooth' });
                    });
                }

                if (cancelContractBtn && contractForm) {
                    cancelContractBtn.addEventListener('click', function() {
                        contractForm.style.display = 'none';
                        resetContractForm();
                    });
                }

                if (contractFormData) {
                    contractFormData.addEventListener('submit', function(e) {
                        e.preventDefault();
                        if (validateContractForm()) {
                            this.submit();
                        }
                    });
                }

                // Client-side form validation for employees
                function validateEmployeeForm() {
                    const name = document.getElementById('employeeName').value.trim();
                    const position = document.getElementById('employeePosition').value.trim();
                    const email = document.getElementById('employeeEmail').value.trim();
                    const phone = document.getElementById('employeePhone').value.trim();
                    
                    clearEmployeeErrors();
                    
                    let isValid = true;
                    
                    if (!name) {
                        showError('employeeName', 'Name is required');
                        isValid = false;
                    }
                    
                    if (!position) {
                        showError('employeePosition', 'Position is required');
                        isValid = false;
                    }
                    
                    if (!email) {
                        showError('employeeEmail', 'Email is required');
                        isValid = false;
                    } else if (!isValidEmail(email)) {
                        showError('employeeEmail', 'Please enter a valid email address');
                        isValid = false;
                    }
                    
                    if (!phone) {
                        showError('employeePhone', 'Phone number is required');
                        isValid = false;
                    }
                    
                    return isValid;
                }

                // Client-side form validation for contracts
                function validateContractForm() {
                    const name = document.getElementById('contractName').value.trim();
                    const caseId = document.getElementById('contractCase').value.trim();
                    const file = document.getElementById('contractFile').files[0];
                    
                    clearContractErrors();
                    
                    let isValid = true;
                    
                    if (!name) {
                        showError('contractName', 'Contract name is required');
                        isValid = false;
                    }
                    
                    if (!caseId) {
                        showError('contractCase', 'Case ID is required');
                        isValid = false;
                    }
                    
                    if (!file) {
                        showError('contractFile', 'Please select a file');
                        isValid = false;
                    } else if (file.size > 10 * 1024 * 1024) { // 10MB limit
                        showError('contractFile', 'File size must be less than 10MB');
                        isValid = false;
                    } else if (!['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'].includes(file.type)) {
                        showError('contractFile', 'Please upload a PDF, DOC, or DOCX file');
                        isValid = false;
                    }
                    
                    return isValid;
                }

                // Email validation helper
                function isValidEmail(email) {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    return emailRegex.test(email);
                }

                // Show error message
                function showError(fieldId, message) {
                    const field = document.getElementById(fieldId);
                    if (field) {
                        field.classList.add('error');
                        
                        let errorElement = field.parentNode.querySelector('.error-text');
                        if (!errorElement) {
                            errorElement = document.createElement('div');
                            errorElement.className = 'error-text';
                            field.parentNode.appendChild(errorElement);
                        }
                        errorElement.textContent = message;
                    }
                }

                // Clear employee error messages
                function clearEmployeeErrors() {
                    const fields = ['employeeName', 'employeePosition', 'employeeEmail', 'employeePhone'];
                    fields.forEach(fieldId => {
                        const field = document.getElementById(fieldId);
                        if (field) {
                            field.classList.remove('error');
                            
                            const errorElement = field.parentNode.querySelector('.error-text');
                            if (errorElement) {
                                errorElement.remove();
                            }
                        }
                    });
                }

                // Clear contract error messages
                function clearContractErrors() {
                    const fields = ['contractName', 'contractCase', 'contractFile'];
                    fields.forEach(fieldId => {
                        const field = document.getElementById(fieldId);
                        if (field) {
                            field.classList.remove('error');
                            
                            const errorElement = field.parentNode.querySelector('.error-text');
                            if (errorElement) {
                                errorElement.remove();
                            }
                        }
                    });
                }

                // Reset employee form
                function resetEmployeeForm() {
                    const fields = ['employeeName', 'employeePosition', 'employeeEmail', 'employeePhone'];
                    fields.forEach(fieldId => {
                        const field = document.getElementById(fieldId);
                        if (field) {
                            field.value = '';
                        }
                    });
                    clearEmployeeErrors();
                }

                // Reset contract form
                function resetContractForm() {
                    const fields = ['contractName', 'contractCase', 'contractDescription'];
                    fields.forEach(fieldId => {
                        const field = document.getElementById(fieldId);
                        if (field) {
                            field.value = '';
                        }
                    });
                    document.getElementById('contractFile').value = '';
                    clearContractErrors();
                }

                // Other form handlers for different sections
                document.getElementById('addDocumentBtn')?.addEventListener('click', function() {
                    alert('Document upload form would appear here');
                });
                
                document.getElementById('addInvoiceBtn')?.addEventListener('click', function() {
                    alert('Invoice creation form would appear here');
                });

                document.getElementById('addMemberBtn')?.addEventListener('click', function() {
                    alert('Team member addition form would appear here');
                });

                // === PDF Export Button Handler (Idinagdag) ===
                document.getElementById('exportPdfBtn')?.addEventListener('click', function() {
                    const password = 'legal2025'; // I-display ang password para sa demo
                    
                    // Gumamit ng custom modal para sa mas magandang UI, pero gumamit muna ng confirm()
                    const confirmation = confirm("Sigurado ka bang gusto mong i-download ang Secured PDF Report?\n\n***SIMULATION LAMANG***\nAng totoong PDF ay may password na: " + password + "\n\nPindutin ang OK upang magpatuloy sa pag-download. Ang file ay magiging .txt file sa demo na ito.");

                    if (confirmation) {
                        // I-submit ang form para ma-trigger ang PHP export logic
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = ''; // Ipadala sa sariling page

                        const actionInput = document.createElement('input');
                        actionInput.type = 'hidden';
                        actionInput.name = 'action';
                        actionInput.value = 'export_pdf';

                        form.appendChild(actionInput);
                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            }
        });

        // Enhanced Analyze button handler with AI analysis display
        document.addEventListener('click', function(e) {
            // View button handler
            if (e.target && e.target.classList.contains('view-btn')) {
                const row = e.target.closest('tr');
                if (!row) return;

                const tbody = row.closest('tbody');
                if (tbody && tbody.id === 'employeesTableBody') {
                    const cells = row.querySelectorAll('td');
                    const info = {
                        'Employee ID': cells[0] ? cells[0].innerText.trim() : '',
                        'Name': cells[1] ? cells[1].innerText.trim() : '',
                        'Position': cells[2] ? cells[2].innerText.trim() : '',
                        'Email': cells[3] ? cells[3].innerText.trim() : '',
                        'Phone': cells[4] ? cells[4].innerText.trim() : ''
                    };

                    let html = '<div style="line-height:1.6;">';
                    for (const key in info) {
                        html += '<div style="margin-bottom:8px;"><strong>' + key + ':</strong> ' + info[key] + '</div>';
                    }
                    html += '</div>';

                    document.getElementById('detailsTitle').innerText = 'Employee Details';
                    document.getElementById('detailsBody').innerHTML = html;
                    document.getElementById('detailsModal').style.display = 'flex';
                } else if (tbody && tbody.id === 'contractsTableBody') {
                    const cells = row.querySelectorAll('td');
                    const info = {
                        'Contract Name': cells[0] ? cells[0].innerText.trim() : '',
                        'Case ID': cells[1] ? cells[1].innerText.trim() : '',
                        'Risk Level': cells[2] ? cells[2].innerText.trim() : '',
                        'Risk Score': cells[3] ? cells[3].innerText.trim() : '',
                        'Upload Date': cells[4] ? cells[4].innerText.trim() : ''
                    };

                    let html = '<div style="line-height:1.6;">';
                    for (const key in info) {
                        html += '<div style="margin-bottom:8px;"><strong>' + key + ':</strong> ' + info[key] + '</div>';
                    }
                    html += '</div>';

                    document.getElementById('detailsTitle').innerText = 'Contract Details';
                    document.getElementById('detailsBody').innerHTML = html;
                    document.getElementById('detailsModal').style.display = 'flex';
                }
            }

            // Analyze button handler for contracts with AI analysis
            if (e.target && e.target.classList.contains('analyze-btn')) {
                const contractData = JSON.parse(e.target.getAttribute('data-contract'));
                
                let riskFactorsHtml = '';
                let recommendationsHtml = '';
                
                // Parse risk factors
                try {
                    const riskFactors = JSON.parse(contractData.risk_factors || '[]');
                    riskFactors.forEach(factor => {
                        riskFactorsHtml += `
                            <div class="risk-factor-item">
                                <strong>${factor.category.replace('_', ' ').toUpperCase()}:</strong> 
                                ${factor.factor} (Weight: ${factor.weight})
                            </div>
                        `;
                    });
                } catch (e) {
                    riskFactorsHtml = '<div class="risk-factor-item">No specific risk factors identified</div>';
                }
                
                // Parse recommendations
                try {
                    const recommendations = JSON.parse(contractData.recommendations || '[]');
                    recommendations.forEach(rec => {
                        recommendationsHtml += `<div class="recommendation-item">${rec}</div>`;
                    });
                } catch (e) {
                    recommendationsHtml = '<div class="recommendation-item">No specific recommendations available</div>';
                }

                const html = `
                    <div style="line-height:1.6;">
                        <div class="ai-analysis-section">
                            <h4>､AI Risk Analysis Report</h4>
                            <p><strong>Contract:</strong> ${contractData.contract_name}</p>
                            <p><strong>Case ID:</strong> ${contractData.case_id}</p>
                            <p><strong>Risk Level:</strong> <span class="status-badge status-${contractData.risk_level.toLowerCase()}">${contractData.risk_level}</span></p>
                            <p><strong>Risk Score:</strong> ${contractData.risk_score}/100</p>
                            <p><strong>Analysis Summary:</strong> ${contractData.analysis_summary || 'No summary available'}</p>
                        </div>
                        
                        <div class="ai-analysis-section">
                            <h5>剥 Identified Risk Factors</h5>
                            <div class="risk-factors">
                                ${riskFactorsHtml || '<div class="risk-factor-item">No risk factors detected</div>'}
                            </div>
                        </div>
                        
                        <div class="ai-analysis-section">
                            <h5>庁 AI Recommendations</h5>
                            <div class="recommendations">
                                ${recommendationsHtml || '<div class="recommendation-item">No recommendations available</div>'}
                            </div>
                        </div>
                    </div>
                `;

                document.getElementById('detailsTitle').innerText = 'AI Risk Analysis';
                document.getElementById('detailsBody').innerHTML = html;
                document.getElementById('detailsModal').style.display = 'flex';
            }

            // Download button handler for contracts
            if (e.target && e.target.classList.contains('download-btn')) {
                const filePath = e.target.getAttribute('data-file');
                if (filePath) {
                    window.open(filePath, '_blank');
                }
            }

            if (e.target && e.target.id === 'closeDetails') {
                document.getElementById('detailsModal').style.display = 'none';
            }
        });

        // Real-time AI analysis preview (optional enhancement)
        document.getElementById('contractDescription')?.addEventListener('input', function(e) {
            const description = e.target.value;
            if (description.length > 50) {
                // In a real implementation, this would call an API for real-time analysis
                console.log('AI analyzing contract description...');
            }
        });
    </script>

    <!-- Details Modal -->
    <div id="detailsModal" style="display:none; position:fixed; left:0; top:0; right:0; bottom:0; background:rgba(0,0,0,0.5); align-items:center; justify-content:center; z-index:1000;">
        <div style="background:white; width:90%; max-width:600px; border-radius:8px; padding:20px; position:relative;">
            <button id="closeDetails" style="position:absolute; right:12px; top:12px; background:#e74c3c; color:white; border:none; padding:6px 10px; border-radius:4px; cursor:pointer;">Close</button>
            <h3 id="detailsTitle">Details</h3>
            <div id="detailsBody">
                <!-- dynamic content -->
            </div>
        </div>
    </div>
</body>
</html>
