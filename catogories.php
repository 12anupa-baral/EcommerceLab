<?php
include 'config.php';

// Function to add a new category
function addCategory($conn, $categoryName) {
    // Sanitize input
    $categoryName = htmlspecialchars(trim($categoryName));
    // Prepare and execute the query
    $stmt = $conn->prepare("INSERT INTO Categories (CategoryName) VALUES (?)");
    $stmt->bind_param("s", $categoryName);

    if ($stmt->execute()) {
        echo "<p>Category added successfully.</p>";
    } else {
        echo "<p>Error: " . $stmt->error . "</p>";
    }

    $stmt->close();
}

// Function to list all categories
function listCategories($conn) {
    $sql = "SELECT CategoryID, CategoryName FROM Categories";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo "<h2>List of Categories</h2>";
        echo "<table border='1'>
                <tr>
                    <th>CategoryID</th>
                    <th>CategoryName</th>
                </tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['CategoryID']}</td>
                    <td>{$row['CategoryName']}</td>
                  </tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No categories found.</p>";
    }
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_catogory'])) {
    $categoryName = $_POST['catogoryName'];
    addCategory($conn, $categoryName);
}

// Always display the list of categories on the page
listCategories($conn);

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Category Management</title>
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
    <h2>Add New Category</h2>
    <form method="POST" action="catogories.php">
        Category Name: <input type="text" name="categoryName" required><br>
        <input type="submit" name="add_category" value="Add Category">
    </form>

</body>
</html>
