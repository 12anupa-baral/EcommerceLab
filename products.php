<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "Ecommerce";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Product Management</title>
</head>
<body>
    <h2>Add New Product</h2>
    <form action="products.php" method="POST">
        <label for="name">Product Name:</label><br>
        <input type="text" id="name" name="name" required><br><br>

        <label for="category">Category:</label><br>
        <select id="category" name="category" required>
            <?php
            $sql = "SELECT * FROM categories";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<option value='" . $row['CategoryID'] . "'>" . $row['CategoryName'] . "</option>";
                }
            }
            ?>
        </select><br><br>

        <label for="price">Price:</label><br>
        <input type="number" id="price" name="price" step="0.01" required><br><br>

        <input type="submit" name="submit" value="Add Product">
    </form>

    <?php
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $name = $_POST['name'];
        $category_id = $_POST['category'];
        $price = $_POST['price'];

        // $sql = "INSERT INTO products (ProductName, CategoryID, Price) VALUES ('$name', '$category_id', '$price')";
        $sql = "INSERT INTO products (ProductName, Price) VALUES ('$name', '$price')";
        
        if ($conn->query($sql) === TRUE) {
            echo "New product added successfully";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }

    $conn->close();
    ?>
</body>
</html>
