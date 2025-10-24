<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json");
require 'db.php'; // Make sure this connects $conn as PDO

$method = $_SERVER['REQUEST_METHOD'];
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

// GET: Fetch all products or a single product
if ($method === 'GET') {
    if ($id) {
        $stmt = $conn->prepare("SELECT * FROM Products WHERE ProductID = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($product) {
            echo json_encode($product);
        } else {
            echo json_encode(["message" => "Product not found"]);
        }
    } else {
        $stmt = $conn->query("SELECT * FROM Products");
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($products);
    }
}

// POST: Create a new product
elseif ($method === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);

    if (!isset($input['ProductName']) || !isset($input['UnitPrice'])) {
        echo json_encode(["message" => "ProductName and UnitPrice are required"]);
        exit;
    }

    $stmt = $conn->prepare("
        INSERT INTO Products (ProductName, Category, UnitPrice, StockQuantity, Supplier, ReorderLevel)
        VALUES (:ProductName, :Category, :UnitPrice, :StockQuantity, :Supplier, :ReorderLevel)
    ");
    $stmt->bindParam(':ProductName', $input['ProductName']);
    $stmt->bindParam(':Category', $input['Category']);
    $stmt->bindParam(':UnitPrice', $input['UnitPrice']);
    $stmt->bindParam(':StockQuantity', $input['StockQuantity']);
    $stmt->bindParam(':Supplier', $input['Supplier']);
    $stmt->bindParam(':ReorderLevel', $input['ReorderLevel']);

    if ($stmt->execute()) {
        echo json_encode([
            "ProductID" => $conn->lastInsertId(),
            "ProductName" => $input['ProductName'],
            "Category" => $input['Category'],
            "UnitPrice" => $input['UnitPrice'],
            "StockQuantity" => $input['StockQuantity'],
            "Supplier" => $input['Supplier'],
            "ReorderLevel" => $input['ReorderLevel']
        ]);
    } else {
        echo json_encode(["message" => "Failed to create product"]);
    }
}

// PUT: Update a product
elseif ($method === 'PUT') {
    if (!$id) {
        echo json_encode(["message" => "ID is required for update"]);
        exit;
    }
    $input = json_decode(file_get_contents("php://input"), true);

    $stmt = $conn->prepare("
        UPDATE Products 
        SET ProductName = :ProductName, 
            Category = :Category, 
            UnitPrice = :UnitPrice, 
            StockQuantity = :StockQuantity, 
            Supplier = :Supplier, 
            ReorderLevel = :ReorderLevel
        WHERE ProductID = :id
    ");
    $stmt->bindParam(':ProductName', $input['ProductName']);
    $stmt->bindParam(':Category', $input['Category']);
    $stmt->bindParam(':UnitPrice', $input['UnitPrice']);
    $stmt->bindParam(':StockQuantity', $input['StockQuantity']);
    $stmt->bindParam(':Supplier', $input['Supplier']);
    $stmt->bindParam(':ReorderLevel', $input['ReorderLevel']);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo json_encode(["message" => "Product updated"]);
    } else {
        echo json_encode(["message" => "Failed to update product"]);
    }
}

// DELETE: Delete a product
elseif ($method === 'DELETE') {
    if (!$id) {
        echo json_encode(["message" => "ID is required for delete"]);
        exit;
    }
    $stmt = $conn->prepare("DELETE FROM Products WHERE ProductID = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    if ($stmt->execute()) {
        echo json_encode(["message" => "Product deleted"]);
    } else {
        echo json_encode(["message" => "Failed to delete product"]);
    }
}

// Unsupported method
else {
    echo json_encode(["message" => "Method not allowed"]);
}
?>
