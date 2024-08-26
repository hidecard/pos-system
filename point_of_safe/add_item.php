<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $category_id = mysqli_real_escape_string($conn, $_POST['category_id']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $quantity = mysqli_real_escape_string($conn, $_POST['quantity']);

    $sql = "INSERT INTO items (category_id, name, description, price, quantity) VALUES ('$category_id', '$name', '$description', '$price', '$quantity')";

    if (mysqli_query($conn, $sql)) {
        header('Location: index.php');
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}

// Fetch categories for the dropdown menu
$categories_result = mysqli_query($conn, "SELECT * FROM categories");
if (!$categories_result) {
    die('Error: ' . mysqli_error($conn));
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Item - POS</title>
</head>
<body>
    <h1>Add Item</h1>
    <form method="POST" action="add_item.php">
        <label for="category_id">Category:</label>
        <select name="category_id" required>
            <?php while ($category = mysqli_fetch_assoc($categories_result)): ?>
                <option value="<?= htmlspecialchars($category['id']) ?>"><?= htmlspecialchars($category['name']) ?></option>
            <?php endwhile; ?>
        </select><br>
        <label for="name">Item Name:</label>
        <input type="text" name="name" required><br>
        <label for="description">Description:</label>
        <textarea name="description" required></textarea><br>
        <label for="price">Price:</label>
        <input type="number" step="0.01" name="price" required><br>
        <label for="quantity">Quantity:</label>
        <input type="number" name="quantity" required><br>
        <button type="submit">Add Item</button>
    </form>
</body>
</html>
