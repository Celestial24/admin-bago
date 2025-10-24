<?php
// Detect environment
if ($_SERVER['HTTP_HOST'] === 'localhost') {
    // Running locally
    $api_url = "http://localhost/RestApi/api.php"; // use http on localhost
} else {
    // Running on hosting server
    $api_url = "https://hr3.atierahotelandrestaurant.com/RestApi/api.php"; // use https online
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $id = $_POST['id'] ?? '';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

    if ($action === 'add') {
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(["name" => $name, "email" => $email]));
        curl_exec($ch);
    }

    if ($action === 'update') {
        curl_setopt($ch, CURLOPT_URL, "$api_url?id=$id");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(["name" => $name, "email" => $email]));
        curl_exec($ch);
    }

    if ($action === 'delete') {
        curl_setopt($ch, CURLOPT_URL, "$api_url?id=$id");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_exec($ch);
    }

    curl_close($ch);
    header("Location: employee_crud.php");
    exit;
}

// Fetch all employees from API using cURL
$ch = curl_init($api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Allow insecure SSL for localhost only
if ($_SERVER['HTTP_HOST'] === 'localhost') {
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
}

$response = curl_exec($ch);
if ($response === false) {
    die("API request failed: " . curl_error($ch));
}
curl_close($ch);

// Decode JSON response safely
$employees = json_decode($response, true);
if (!is_array($employees)) {
    $employees = []; // Prevent foreach() errors
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Employee CRUD</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">

    <h2 class="mb-4">Add Employee</h2>
    <form method="post" class="row g-3">
        <input type="hidden" name="action" value="add">
        <div class="col-md-4">
            <input type="text" class="form-control" name="name" placeholder="Name" required>
        </div>
        <div class="col-md-4">
            <input type="email" class="form-control" name="email" placeholder="Email" required>
        </div>
        <div class="col-md-4">
            <button type="submit" class="btn btn-primary">Add Employee</button>
        </div>
    </form>

    <h2 class="mt-5 mb-4">Employee List</h2>
    <table class="table table-striped table-bordered">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th style="width: 50%;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($employees as $emp): ?>
            <tr>
                <td><?= $emp['id'] ?></td>
                <td><?= htmlspecialchars($emp['name']) ?></td>
                <td><?= htmlspecialchars($emp['email']) ?></td>
                <td>
                    <form method="post" class="d-inline">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= $emp['id'] ?>">
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Delete this employee?')">Delete</button>
                    </form>

                    <form method="post" class="d-inline ms-2">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" value="<?= $emp['id'] ?>">
                        <input type="text" name="name" value="<?= htmlspecialchars($emp['name']) ?>" class="form-control d-inline w-auto me-2" required>
                        <input type="email" name="email" value="<?= htmlspecialchars($emp['email']) ?>" class="form-control d-inline w-auto me-2" required>
                        <button type="submit" class="btn btn-success btn-sm">Update</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
