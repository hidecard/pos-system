<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = mysqli_real_escape_string($conn, $_SESSION['user_id']);
    $item_id = mysqli_real_escape_string($conn, $_POST['item_id']);
    $quantity = mysqli_real_escape_string($conn, $_POST['quantity']);

    // Get item price and available quantity
    $item_result = mysqli_query($conn, "SELECT price, quantity FROM items WHERE id='$item_id'");
    if (!$item_result) {
        die('Error: ' . mysqli_error($conn));
    }
    $item = mysqli_fetch_assoc($item_result);
    $price = $item['price'];
    $available_quantity = $item['quantity'];

    if ($quantity > $available_quantity) {
        echo "Error: Not enough stock available.";
        exit();
    }

    $total = $quantity * $price;

    // Confirm order
    if (isset($_POST['confirm'])) {
        // Insert into orders
        $order_sql = "INSERT INTO orders (user_id, total) VALUES ('$user_id', '$total')";
        if (mysqli_query($conn, $order_sql)) {
            $order_id = mysqli_insert_id($conn);

            // Insert into order_items
            $order_item_sql = "INSERT INTO order_items (order_id, item_id, quantity, price) VALUES ('$order_id', '$item_id', '$quantity', '$price')";
            if (mysqli_query($conn, $order_item_sql)) {
                // Update item quantity
                $new_quantity = $available_quantity - $quantity;
                mysqli_query($conn, "UPDATE items SET quantity='$new_quantity' WHERE id='$item_id'");

                header('Location: index.php');
            } else {
                echo "Error: " . mysqli_error($conn);
            }
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    }
}

// Fetch items for the dropdown menu
$items_result = mysqli_query($conn, "SELECT * FROM items");
if (!$items_result) {
    die('Error: ' . mysqli_error($conn));
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Place Order - POS</title>
</head>
<body>
    <h1>Place Order</h1>
    <form method="POST" action="add_order.php">
        <label for="item_id">Item:</label>
        <select name="item_id" required>
            <?php while ($item = mysqli_fetch_assoc($items_result)): ?>
                <option value="<?= htmlspecialchars($item['id']) ?>"><?= htmlspecialchars($item['name']) ?> (Available: <?= htmlspecialchars($item['quantity']) ?>)</option>
            <?php endwhile; ?>
        </select><br>
        <label for="quantity">Quantity:</label>
        <input type="number" name="quantity" required><br>
        <button type="submit" name="confirm">Confirm Order</button>
    </form>
</body>
</html>
