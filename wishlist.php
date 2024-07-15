<?php
session_start();
include 'config.php';

// Function to add product to the wish list
function addToWishlist($conn, $productId) {
    // Sanitize input
    $productId = htmlspecialchars(trim($productId));
    
    if (!isset($_SESSION['wishlist'])) {
        $_SESSION['wishlist'] = [];
    }
    
    if (!in_array($productId, $_SESSION['wishlist'])) {
        $_SESSION['wishlist'][] = $productId;
    }
}

// Function to remove product from the wish list
function removeFromWishlist($conn, $productId) {
    // Sanitize input
    $productId = htmlspecialchars(trim($productId));
    
    if (isset($_SESSION['wishlist'])) {
        if (($key = array_search($productId, $_SESSION['wishlist'])) !== false) {
            unset($_SESSION['wishlist'][$key]);
        }
    }
}

// Function to get the wish list contents
function getWishlistContents($conn) {
    $wishlistContents = [];
    
    if (isset($_SESSION['wishlist']) && count($_SESSION['wishlist']) > 0) {
        $placeholders = implode(',', array_fill(0, count($_SESSION['wishlist']), '?'));
        $types = str_repeat('i', count($_SESSION['wishlist']));
        $stmt = $conn->prepare("SELECT ProductID, ProductName, Price FROM Products WHERE ProductID IN ($placeholders)");
        $stmt->bind_param($types, ...$_SESSION['wishlist']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $wishlistContents[] = $row;
        }
        
        $stmt->close();
    }
    
    return $wishlistContents;
}

// Handle form submissions for adding to wish list and removing from wish list
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_to_wishlist'])) {
        $productId = $_POST['product_id'];
        addToWishlist($conn, $productId);
    }
    
    if (isset($_POST['remove_from_wishlist'])) {
        $productId = $_POST['product_id'];
        removeFromWishlist($conn, $productId);
    }
}

// Always display the wish list contents on the page
$wishlistContents = getWishlistContents($conn);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Wish List</title>
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
    <h2>Wish List</h2>
    <?php if (count($wishlistContents) > 0): ?>
        <table border='1'>
            <tr>
                <th>ProductID</th>
                <th>ProductName</th>
                <th>Price</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($wishlistContents as $item): ?>
                <tr>
                    <td><?php echo $item['ProductID']; ?></td>
                    <td><?php echo $item['ProductName']; ?></td>
                    <td><?php echo $item['Price']; ?></td>
                    <td>
                        <form method="POST" action="wishlist.php">
                            <input type="hidden" name="product_id" value="<?php echo $item['ProductID']; ?>">
                            <input type="submit" name="remove_from_wishlist" value="Remove">
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>Your wish list is empty.</p>
    <?php endif; ?>

    <h2>Available Products</h2>
    <?php
    // Fetch all products to allow adding them to the wish list
    $sql = "SELECT ProductID, ProductName, Price FROM Products";
    $result = $conn->query($sql);
    if ($result->num_rows > 0): ?>
        <table border='1'>
            <tr>
                <th>ProductID</th>
                <th>ProductName</th>
                <th>Price</th>
                <th>Actions</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['ProductID']; ?></td>
                    <td><?php echo $row['ProductName']; ?></td>
                    <td><?php echo $row['Price']; ?></td>
                    <td>
                        <form method="POST" action="wishlist.php">
                            <input type="hidden" name="product_id" value="<?php echo $row['ProductID']; ?>">
                            <input type="submit" name="add_to_wishlist" value="Add to Wish List">
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>No products available.</p>
    <?php endif; ?>

    <?php $conn->close(); ?>
</body>
</html>
