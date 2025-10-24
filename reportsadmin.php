<?php
// reportsadmin.php
session_start();

// Use shared DB helper
require_once __DIR__ . '/db.php';

// Simple helper to get DB connection
$db = (new Database())->getConnection();

// Handle status updates via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_status' && isset($_POST['reservation_id']) && isset($_POST['status'])) {
        $stmt = $db->prepare("UPDATE reservations SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$_POST['status'], intval($_POST['reservation_id'])]);
        $_SESSION['message'] = 'Reservation status updated.';
        header('Location: reportsadmin.php');
        exit;
    }

    if ($_POST['action'] === 'export_csv') {
        // Build query with optional filters
        $where = [];
        $params = [];
        if (!empty($_POST['from_date'])) {
            $where[] = 'event_date >= ?';
            $params[] = $_POST['from_date'];
        }
        if (!empty($_POST['to_date'])) {
            $where[] = 'event_date <= ?';
            $params[] = $_POST['to_date'];
        }
        if (!empty($_POST['status']) && $_POST['status'] !== 'all') {
            $where[] = 'status = ?';
            $params[] = $_POST['status'];
        }

        $sql = "SELECT r.*, f.name as facility_name FROM reservations r LEFT JOIN facilities f ON r.facility_id = f.id";
        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY r.event_date, r.start_time';

        $stmt = $db->prepare($sql);
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

// Fetch filters from GET
$from = $_GET['from_date'] ?? '';
$to = $_GET['to_date'] ?? '';
$status = $_GET['status'] ?? 'all';

$where = [];
$params = [];
if ($from) { $where[] = 'r.event_date >= ?'; $params[] = $from; }
if ($to) { $where[] = 'r.event_date <= ?'; $params[] = $to; }
if ($status !== 'all') { $where[] = 'r.status = ?'; $params[] = $status; }

$sql = "SELECT r.*, f.name as facility_name FROM reservations r LEFT JOIN facilities f ON r.facility_id = f.id";
if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
$sql .= ' ORDER BY r.event_date DESC, r.start_time DESC';

$stmt = $db->prepare($sql);
$stmt->execute($params);
$reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Simple HTML output
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reservations Reports</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; margin: 20px }
        table { border-collapse: collapse; width: 100% }
        th, td { border: 1px solid #ddd; padding: 8px }
        th { background: #f4f4f4 }
        .btn { padding: 8px 12px; background: #3182ce; color: white; border: none; border-radius: 4px; cursor: pointer }
        .btn-danger { background: #e53e3e }
        .filters { margin-bottom: 12px }
    </style>
</head>
<body>
    <h1>Reservations Reports</h1>
    <?php if (!empty($_SESSION['message'])): ?>
        <div style="padding:8px; background: #e6fffa; border: 1px solid #b2f5ea; margin-bottom:12px"><?php echo htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?></div>
    <?php endif; ?>

    <form method="get" class="filters">
        From: <input type="date" name="from_date" value="<?= htmlspecialchars($from) ?>"> 
        To: <input type="date" name="to_date" value="<?= htmlspecialchars($to) ?>"> 
        Status: <select name="status">
            <option value="all" <?= $status==='all'?'selected':'' ?>>All</option>
            <option value="pending" <?= $status==='pending'?'selected':'' ?>>Pending</option>
            <option value="confirmed" <?= $status==='confirmed'?'selected':'' ?>>Confirmed</option>
            <option value="cancelled" <?= $status==='cancelled'?'selected':'' ?>>Cancelled</option>
            <option value="completed" <?= $status==='completed'?'selected':'' ?>>Completed</option>
        </select>
        <button class="btn">Filter</button>
    </form>

    <form method="post" style="margin-bottom:12px">
        <input type="hidden" name="action" value="export_csv">
        <input type="hidden" name="from_date" value="<?= htmlspecialchars($from) ?>">
        <input type="hidden" name="to_date" value="<?= htmlspecialchars($to) ?>">
        <input type="hidden" name="status" value="<?= htmlspecialchars($status) ?>">
        <button class="btn">Export CSV</button>
    </form>

    <table>
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
            <?php foreach ($reservations as $r): ?>
                <tr>
                    <td><?= $r['id'] ?></td>
                    <td><?= htmlspecialchars($r['facility_name']) ?></td>
                    <td><?= htmlspecialchars($r['customer_name']) ?><br><small><?= htmlspecialchars($r['customer_email']) ?></small></td>
                    <td><?= htmlspecialchars($r['event_date']) ?></td>
                    <td><?= htmlspecialchars($r['start_time']) ?> - <?= htmlspecialchars($r['end_time']) ?></td>
                    <td><?= $r['guests_count'] ?></td>
                    <td>₱<?= number_format($r['total_amount'] ?? 0, 2) ?></td>
                    <td><?= htmlspecialchars($r['status']) ?></td>
                    <td>
                        <form method="post" style="display:inline">
                            <input type="hidden" name="action" value="update_status">
                            <input type="hidden" name="reservation_id" value="<?= $r['id'] ?>">
                            <?php if ($r['status'] !== 'confirmed'): ?>
                                <button class="btn" name="status" value="confirmed">Confirm</button>
                            <?php endif; ?>
                            <?php if ($r['status'] !== 'cancelled'): ?>
                                <button class="btn btn-danger" name="status" value="cancelled">Cancel</button>
                            <?php endif; ?>
                            <?php if ($r['status'] !== 'completed'): ?>
                                <button class="btn" name="status" value="completed">Complete</button>
                            <?php endif; ?>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</body>
</html>
