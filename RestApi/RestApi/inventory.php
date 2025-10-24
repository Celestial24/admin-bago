<?php
$api_url = "http://localhost/RestApi/inventoryApi.php"; // <-- make sure this matches your API file

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = $_POST['id'] ?? '';
    $ProductName = $_POST['ProductName'] ?? '';
    $Category = $_POST['Category'] ?? '';
    $UnitPrice = $_POST['UnitPrice'] ?? '';
    $StockQuantity = $_POST['StockQuantity'] ?? '';
    $Supplier = $_POST['Supplier'] ?? '';
    $ReorderLevel = $_POST['ReorderLevel'] ?? '';

    if ($action === 'add') {
        $ch = curl_init($api_url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            "ProductName" => $ProductName,
            "Category" => $Category,
            "UnitPrice" => $UnitPrice,
            "StockQuantity" => $StockQuantity,
            "Supplier" => $Supplier,
            "ReorderLevel" => $ReorderLevel
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_exec($ch);
        curl_close($ch);
    }

    if ($action === 'update') {
        $ch = curl_init("$api_url?id=$id");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            "ProductName" => $ProductName,
            "Category" => $Category,
            "UnitPrice" => $UnitPrice,
            "StockQuantity" => $StockQuantity,
            "Supplier" => $Supplier,
            "ReorderLevel" => $ReorderLevel
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_exec($ch);
        curl_close($ch);
    }

    if ($action === 'delete') {
        $ch = curl_init("$api_url?id=$id");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        curl_close($ch);
    }

    header("Location: inventory.php");
    exit;
}

// Fetch all products from API
$products = json_decode(file_get_contents($api_url), true);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Product CRUD</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">

    <h2 class="mb-4">Add Product</h2>
    <form method="post" class="row g-3">
        <input type="hidden" name="action" value="add">
        <div class="col-md-3">
            <input type="text" class="form-control" name="ProductName" placeholder="Product Name" required>
        </div>
        <div class="col-md-2">
            <input type="text" class="form-control" name="Category" placeholder="Category">
        </div>
        <div class="col-md-2">
            <input type="number" step="0.01" class="form-control" name="UnitPrice" placeholder="Unit Price" required>
        </div>
        <div class="col-md-2">
            <input type="number" class="form-control" name="StockQuantity" placeholder="Stock Quantity" required>
        </div>
        <div class="col-md-2">
            <input type="text" class="form-control" name="Supplier" placeholder="Supplier">
        </div>
        <div class="col-md-1">
            <input type="number" class="form-control" name="ReorderLevel" placeholder="Reorder">
        </div>
        <div class="col-12">
            <button type="submit" class="btn btn-primary">Add Product</button>
        </div>
    </form>

    <h2 class="mt-5 mb-4">Product List</h2>
    <table class="table table-striped table-bordered">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Category</th>
                <th>Unit Price</th>
                <th>Stock</th>
                <th>Supplier</th>
                <th>Reorder Level</th>
                <th style="width: 40%;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if(is_array($products)): ?>
                <?php foreach($products as $prod): ?>
                <tr>
                    <td><?= $prod['ProductID'] ?></td>
                    <td><?= htmlspecialchars($prod['ProductName']) ?></td>
                    <td><?= htmlspecialchars($prod['Category']) ?></td>
                    <td><?= htmlspecialchars($prod['UnitPrice']) ?></td>
                    <td><?= htmlspecialchars($prod['StockQuantity']) ?></td>
                    <td><?= htmlspecialchars($prod['Supplier']) ?></td>
                    <td><?= htmlspecialchars($prod['ReorderLevel']) ?></td>
                    <td>
                        <form method="post" class="d-inline">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $prod['ProductID'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Delete this product?')">Delete</button>
                        </form>

                        <form method="post" class="d-inline ms-2">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="id" value="<?= $prod['ProductID'] ?>">

                            <input type="text" name="ProductName" value="<?= htmlspecialchars($prod['ProductName']) ?>" class="form-control d-inline w-auto me-2" required>
                            <input type="text" name="Category" value="<?= htmlspecialchars($prod['Category']) ?>" class="form-control d-inline w-auto me-2">
                            <input type="number" step="0.01" name="UnitPrice" value="<?= htmlspecialchars($prod['UnitPrice']) ?>" class="form-control d-inline w-auto me-2" required>
                            <input type="number" name="StockQuantity" value="<?= htmlspecialchars($prod['StockQuantity']) ?>" class="form-control d-inline w-auto me-2" required>
                            <input type="text" name="Supplier" value="<?= htmlspecialchars($prod['Supplier']) ?>" class="form-control d-inline w-auto me-2">
                            <input type="number" name="ReorderLevel" value="<?= htmlspecialchars($prod['ReorderLevel']) ?>" class="form-control d-inline w-auto me-2">

                            <button type="submit" class="btn btn-success btn-sm">Update</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="8">No products found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
