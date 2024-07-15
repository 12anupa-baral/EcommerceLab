<?php
include 'config.php';

function registerUser($conn, $firstName, $lastName, $email, $password) {
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $conn->prepare("INSERT INTO Customers (FirstName, LastName, Email, PasswordHash) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $firstName, $lastName, $email, $passwordHash);

    if ($stmt->execute()) {
        echo "User registered successfully.";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

function listUsers($conn) {
    $sql = "SELECT CustomerID, FirstName, LastName, Email, CreatedAt FROM Customers";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo "<table border='1'>
                <tr>
                    <th>CustomerID</th>
                    <th>FirstName</th>
                    <th>LastName</th>
                    <th>Email</th>
                    <th>CreatedAt</th>
                </tr>";
        while($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['CustomerID']}</td>
                    <td>{$row['FirstName']}</td>
                    <td>{$row['LastName']}</td>
                    <td>{$row['Email']}</td>
                    <td>{$row['CreatedAt']}</td>
                  </tr>";
        }
        echo "</table>";
    } else {
        echo "No users found.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = $_POST['firstName'];
    $lastName = $_POST['lastName'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    registerUser($conn, $firstName, $lastName, $email, $password);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    listUsers($conn);
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Registration</title>
</head>
<body>
    <h2>Register User</h2>
    <form method="POST" action="user.php">
        First Name: <input type="text" name="firstName" required><br>
        Last Name: <input type="text" name="lastName" required><br>
        Email: <input type="email" name="email" required><br>
        Password: <input type="password" name="password" required><br>
        <input type="submit" value="Register">
    </form>

    <h2>List of Users</h2>
    <form method="GET" action="user.php">
        <input type="submit" value="List Users">
    </form>
</body>
</html>
