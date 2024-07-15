<?php
include 'config.php';

// Function to add a new product
function addProduct($conn, $productName, $price) {
    // Sanitize input
    $productName = htmlspecialchars(trim($productName));
    $price = floatval($price);

    // Prepare and execute the query
    $stmt = $conn->prepare("INSERT INTO Products (ProductName, Price) VALUES (?, ?)");
    $stmt->bind_param("sd", $productName, $price);

    if ($stmt->execute()) {
        echo "<p>Product added successfully.</p>";
    } else {
        echo "<p>Error: " . $stmt->error . "</p>";
    }

    $stmt->close();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $productName = $_POST['product_name'];
    $price = $_POST['price'];
    addProduct($conn, $productName, $price);
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add New Product</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <h2>Add New Product</h2>
    <form method="POST" action="add_product.php">
        Product Name: <input type="text" name="product_name" required><br>
        Price: <input type="number" step="0.01" name="price" required><br>
        <input type="submit" name="add_product" value="Add Product">
    </form>
</body>
</html>
