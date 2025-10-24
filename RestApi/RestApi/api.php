

<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>
<?php
header("Content-Type: application/json");
require 'db.php';

$method = $_SERVER['REQUEST_METHOD'];
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

// GET: Fetch all users or single user
if ($method === 'GET') {
    if ($id) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            echo json_encode($user);
        } else {
            echo json_encode(["message" => "User not found"]);
        }
    } else {
        $stmt = $conn->query("SELECT * FROM users");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($users);
    }
}

// POST: Create a new user
elseif ($method === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    if (!isset($input['name']) || !isset($input['email'])) {
        echo json_encode(["message" => "Name and email required"]);
        exit;
    }
    $stmt = $conn->prepare("INSERT INTO users (name, email) VALUES (:name, :email)");
    $stmt->bindParam(':name', $input['name']);
    $stmt->bindParam(':email', $input['email']);
    if ($stmt->execute()) {
        echo json_encode([
            "id" => $conn->lastInsertId(),
            "name" => $input['name'],
            "email" => $input['email']
        ]);
    } else {
        echo json_encode(["message" => "Failed to create user"]);
    }
}

// PUT: Update a user
elseif ($method === 'PUT') {
    if (!$id) {
        echo json_encode(["message" => "ID is required for update"]);
        exit;
    }
    $input = json_decode(file_get_contents("php://input"), true);
    $stmt = $conn->prepare("UPDATE users SET name = :name, email = :email WHERE id = :id");
    $stmt->bindParam(':name', $input['name']);
    $stmt->bindParam(':email', $input['email']);
    $stmt->bindParam(':id', $id);
    if ($stmt->execute()) {
        echo json_encode(["message" => "User updated"]);
    } else {
        echo json_encode(["message" => "Failed to update user"]);
    }
}

// DELETE: Delete a user
elseif ($method === 'DELETE') {
    if (!$id) {
        echo json_encode(["message" => "ID is required for delete"]);
        exit;
    }
    $stmt = $conn->prepare("DELETE FROM users WHERE id = :id");
    $stmt->bindParam(':id', $id);
    if ($stmt->execute()) {
        echo json_encode(["message" => "User deleted"]);
    } else {
        echo json_encode(["message" => "Failed to delete user"]);
    }
}

// Unsupported method
else {
    echo json_encode(["message" => "Method not allowed"]);
}
?>
