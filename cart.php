<?php
session_start();
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

// Assuming the user is logged in, use a dummy UserID for demonstration
// Replace this with the actual logged-in user's ID
$loggedInUserID = 1; 

// Add Product to Cart
if (isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];

    $sql = "SELECT Price FROM products WHERE ProductID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($price);
    $stmt->fetch();

    if ($stmt->num_rows > 0) {
        $sql = "INSERT INTO shoppingcart (UserID, ProductID, Quantity, Price) VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE Quantity = Quantity + VALUES(Quantity), Price = VALUES(Price)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiii", $loggedInUserID, $product_id, $quantity, $price);
        $stmt->execute();
    }
    $stmt->close();
}

// Update Cart
if (isset($_POST['update_cart'])) {
    $cart_id = $_POST['cart_id'];
    $quantity = $_POST['quantity'];

    $sql = "UPDATE cart SET Quantity = ? WHERE CartID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $quantity, $cart_id);
    $stmt->execute();
    $stmt->close();
}

// Remove Product from Cart
if (isset($_POST['remove_from_cart'])) {
    $cart_id = $_POST['cart_id'];

    $sql = "DELETE FROM cart WHERE CartID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $cart_id);
    $stmt->execute();
    $stmt->close();
}

// Fetch Cart Items
$sql = "SELECT c.CartID, p.ProductName, c.Quantity, c.Price, (c.Quantity * c.Price) AS Total
        FROM cart c
        JOIN products p ON c.ProductID = p.ProductID
        WHERE c.UserID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $loggedInUserID);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Shopping Cart</title>
</head>
<body>
    <h2>Shopping Cart</h2>
    <table border="1">
        <tr>
            <th>Product Name</th>
            <th>Quantity</th>
            <th>Price</th>
            <th>Total</th>
            <th>Actions</th>
        </tr>
        <?php
        $totalAmount = 0;
        while ($row = $result->fetch_assoc()) {
            $totalAmount += $row['Total'];
            echo "<tr>
                    <td>{$row['ProductName']}</td>
                    <td>
                        <form action='cart.php' method='POST'>
                            <input type='hidden' name='cart_id' value='{$row['CartID']}'>
                            <input type='number' name='quantity' value='{$row['Quantity']}' min='1' required>
                            <input type='submit' name='update_cart' value='Update'>
                        </form>
                    </td>
                    <td>{$row['Price']}</td>
                    <td>{$row['Total']}</td>
                    <td>
                        <form action='cart.php' method='POST'>
                            <input type='hidden' name='cart_id' value='{$row['CartID']}'>
                            <input type='submit' name='remove_from_cart' value='Remove'>
                        </form>
                    </td>
                  </tr>";
        }
        ?>
    </table>
    <h3>Total Amount: $<?php echo number_format($totalAmount, 2); ?></h3>
    <hr>
    <h3>Add New Product</h3>
    <form action="cart.php" method="POST">
        <label for="product_id">Product:</label><br>
        <select id="product_id" name="product_id" required>
            <?php
            $sql = "SELECT ProductID, ProductName FROM products";
            $result = $conn->query($sql);
            while ($row = $result->fetch_assoc()) {
                echo "<option value='{$row['ProductID']}'>{$row['ProductName']}</option>";
            }
            ?>
        </select><br><br>

        <label for="quantity">Quantity:</label><br>
        <input type="number" id="quantity" name="quantity" min="1" required><br><br>

        <input type="submit" name="add_to_cart" value="Add to Cart">
    </form>
</body>
</html>

<?php
$conn->close();
?>
