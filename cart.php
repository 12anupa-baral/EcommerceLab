<?php
session_start();
include 'config.php';

// Function to add product to the cart
function addToCart($productId, $quantity) {
    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId] += $quantity;
    } else {
        $_SESSION['cart'][$productId] = $quantity;
    }
}

// Function to update product quantity in the cart
function updateCart($productId, $quantity) {
    if (isset($_SESSION['cart'][$productId])) {
        if ($quantity <= 0) {
            unset($_SESSION['cart'][$productId]);
        } else {
            $_SESSION['cart'][$productId] = $quantity;
        }
    }
}

// Function to remove product from the cart
function removeFromCart($productId) {
    if (isset($_SESSION['cart'][$productId])) {
        unset($_SESSION['cart'][$productId]);
    }
}

// Function to get the cart contents
function getCartContents($conn) {
    $cartContents = [];
    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $productId => $quantity) {
            $stmt = $conn->prepare("SELECT ProductID, ProductName, Price FROM Products WHERE ProductID = ?");
            $stmt->bind_param("i", $productId);
            $stmt->execute();
            $result = $stmt->get_result();
            $product = $result->fetch_assoc();
            $stmt->close();

            if ($product) {
                $product['quantity'] = $quantity;
                $product['total_price'] = $product['Price'] * $quantity;
                $cartContents[] = $product;
            }
        }
    }
    return $cartContents;
}

// Function to get all products
function getAllProducts($conn) {
    $sql = "SELECT ProductID, ProductName, Price FROM Products";
    $result = $conn->query($sql);
    $products = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }
    return $products;
}

// Handle form submissions for adding to cart, updating, and removing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_to_cart'])) {
        $productId = $_POST['product_id'];
        $quantity = $_POST['quantity'];
        addToCart($productId, $quantity);
    }

    if (isset($_POST['update_cart'])) {
        $productId = $_POST['product_id'];
        $quantity = $_POST['quantity'];
        updateCart($productId, $quantity);
    }

    if (isset($_POST['remove_from_cart'])) {
        $productId = $_POST['product_id'];
        removeFromCart($productId);
    }
}

// Always display the cart contents and available products on the page
$cartContents = getCartContents($conn);
$allProducts = getAllProducts($conn);

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Shopping Cart</title>
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
    <h2>Available Products</h2>
    <?php if (count($allProducts) > 0): ?>
        <table border='1'>
            <tr>
                <th>ProductID</th>
                <th>ProductName</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($allProducts as $product): ?>
                <tr>
                    <td><?php echo $product['ProductID']; ?></td>
                    <td><?php echo $product['ProductName']; ?></td>
                    <td><?php echo $product['Price']; ?></td>
                    <td>
                        <form method="POST" action="cart.php">
                            <input type="hidden" name="product_id" value="<?php echo $product['ProductID']; ?>">
                            <input type="number" name="quantity" value="1" min="1" required>
                            <input type="submit" name="add_to_cart" value="Add to Cart">
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>No products available.</p>
    <?php endif; ?>

    <h2>Shopping Cart</h2>
    <?php if (count($cartContents) > 0): ?>
        <table border='1'>
            <tr>
                <th>ProductID</th>
                <th>ProductName</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Total Price</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($cartContents as $item): ?>
                <tr>
                    <td><?php echo $item['ProductID']; ?></td>
                    <td><?php echo $item['ProductName']; ?></td>
                    <td><?php echo $item['Price']; ?></td>
                    <td>
                        <form method="POST" action="cart.php">
                            <input type="hidden" name="product_id" value="<?php echo $item['ProductID']; ?>">
                            <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="0" required>
                            <input type="submit" name="update_cart" value="Update">
                        </form>
                    </td>
                    <td><?php echo $item['total_price']; ?></td>
                    <td>
                        <form method="POST" action="cart.php">
                            <input type="hidden" name="product_id" value="<?php echo $item['ProductID']; ?>">
                            <input type="submit" name="remove_from_cart" value="Remove">
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>Your cart is empty.</p>
    <?php endif; ?>
</body>
</html>
