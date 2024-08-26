<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);

    $sql = "INSERT INTO categories (name) VALUES ('$name')";

    if (mysqli_query($conn, $sql)) {
        header('Location: index.php');
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Category - POS</title>
</head>
<body>
    <h1>Add Category</h1>
    <form method="POST" action="add_category.php">
        <label for="name">Category Name:</label>
        <input type="text" name="name" required><br>
        <button type="submit">Add Category</button>
    </form>
</body>
</html>
