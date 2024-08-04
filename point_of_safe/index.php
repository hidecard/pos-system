<?php
session_start();
include 'db.php';

// Fetch categories, items, and customers
$categories_result = mysqli_query($conn, "SELECT * FROM categories");
if (!$categories_result) {
    die('Error fetching categories: ' . mysqli_error($conn));
}

$items_result = mysqli_query($conn, "SELECT * FROM items");
if (!$items_result) {
    die('Error fetching items: ' . mysqli_error($conn));
}

$customers_result = mysqli_query($conn, "SELECT * FROM customers");
if (!$customers_result) {
    die('Error fetching customers: ' . mysqli_error($conn));
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Add category
    if (isset($_POST['add_category'])) {
        if (!isset($_SESSION['user_id'])) {
            header('Location: login.php');
            exit();
        }
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $sql = "INSERT INTO categories (name) VALUES ('$name')";
        if (mysqli_query($conn, $sql)) {
            echo "Category added successfully!";
            header("Location: index.php");
            exit();
        } else {
            echo "Error adding category: " . mysqli_error($conn);
        }
    }

    // Add item
    if (isset($_POST['add_item'])) {
        if (!isset($_SESSION['user_id'])) {
            header('Location: login.php');
            exit();
        }
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $description = mysqli_real_escape_string($conn, $_POST['description']);
        $price = mysqli_real_escape_string($conn, $_POST['price']);
        $quantity = mysqli_real_escape_string($conn, $_POST['quantity']);
        $category_id = mysqli_real_escape_string($conn, $_POST['category_id']);
        $sql = "INSERT INTO items (name, description, price, quantity, category_id) VALUES ('$name', '$description', '$price', '$quantity', '$category_id')";
        if (mysqli_query($conn, $sql)) {
            echo "Item added successfully!";
            header("Location: index.php");
            exit();
        } else {
            echo "Error adding item: " . mysqli_error($conn);
        }
    }

    // Add customer
    if (isset($_POST['add_customer'])) {
        if (!isset($_SESSION['user_id'])) {
            header('Location: login.php');
            exit();
        }
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $phone = mysqli_real_escape_string($conn, $_POST['phone']);
        $address = mysqli_real_escape_string($conn, $_POST['address']);
        $sql = "INSERT INTO customers (name, phone, address) VALUES ('$name', '$phone', '$address')";
        if (mysqli_query($conn, $sql)) {
            echo "Customer added successfully!";
            header("Location: index.php");
            exit();
        } else {
            echo "Error adding customer: " . mysqli_error($conn);
        }
    }

    // Place order
    if (isset($_POST['place_order'])) {
        if (!isset($_SESSION['user_id'])) {
            header('Location: login.php');
            exit();
        }
        $item_id = mysqli_real_escape_string($conn, $_POST['item_id']);
        $quantity = mysqli_real_escape_string($conn, $_POST['quantity']);
        $customer_id = mysqli_real_escape_string($conn, $_POST['customer_id']);
        $item_result = mysqli_query($conn, "SELECT * FROM items WHERE id = '$item_id'");
        if (!$item_result) {
            die('Error fetching item: ' . mysqli_error($conn));
        }
        $item = mysqli_fetch_assoc($item_result);
        if (!$item) {
            die('Item not found');
        }
        if ($item['quantity'] < $quantity) {
            echo "Error: Not enough stock available.";
        } else {
            $total = $quantity * $item['price'];
            $user_id = $_SESSION['user_id'];
            $sql = "INSERT INTO orders (user_id, item_id, quantity, total, customer_id) VALUES ('$user_id', '$item_id', '$quantity', '$total', '$customer_id')";
            if (mysqli_query($conn, $sql)) {
                $new_quantity = $item['quantity'] - $quantity;
                $sql = "UPDATE items SET quantity = '$new_quantity' WHERE id = '$item_id'";
                if (mysqli_query($conn, $sql)) {
                    echo "Order placed successfully!";
                    header("Location: index.php");
                    exit();
                } else {
                    echo "Error updating item quantity: " . mysqli_error($conn);
                }
            } else {
                echo "Error placing order: " . mysqli_error($conn);
            }
        }
    }
}

 // Handle "Paid" button action
 if (isset($_POST['mark_paid'])) {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }
    $order_id = mysqli_real_escape_string($conn, $_POST['order_id']);
    $sql = "UPDATE orders SET paid = TRUE WHERE id = '$order_id'";
    if (mysqli_query($conn, $sql)) {
        $_SESSION['message'] = "Order marked as paid successfully!";
        header("Location: index.php");
        exit();
    } else {
        echo "Error marking order as paid: " . mysqli_error($conn);
    }
}

// Fetch orders and calculate total amount
$orders_result = mysqli_query($conn, "SELECT orders.*, customers.name AS customer_name, items.name AS item_name FROM orders JOIN customers ON orders.customer_id = customers.id JOIN items ON orders.item_id = items.id");
if (!$orders_result) {
    die('Error fetching orders: ' . mysqli_error($conn));
}
$total_amount_result = mysqli_query($conn, "SELECT SUM(total) as total_amount FROM orders");
if (!$total_amount_result) {
    die('Error calculating total amount: ' . mysqli_error($conn));
}
$total_amount_row = mysqli_fetch_assoc($total_amount_result);
$total_amount = $total_amount_row['total_amount'] ?? 0; // Ensure total_amount is set

// Close the connection
mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Point of Sale System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <style>
        .container { margin-top: 20px; }
        .card { margin-bottom: 20px; }
        .alert { margin-top: 20px; }
        .sidenav {
            height: 100%;
            width: 250px;
            position: fixed;
            top: 0;
            left: 0;
            padding-top: 20px;
            overflow-y: auto;
        }
        .sidenav a {
            padding: 8px 16px;
            text-decoration: none;
            font-size: 18px;
            color: white;
            display: block;
        }
        .sidenav a:hover {
            background-color: #fff;
            color: black;
        }
        .main {
            margin-left: 260px;
            padding: 20px;
        }
        .section {
            display: none;
            padding: 20px;
            margin-bottom: 20px;
        }
        .section.active {
            display: block;
        }
    </style>
</head>
<body>
    <div class="sidenav bg-primary text-light">
        <h2 class="text-center">Hz Shop</h2>
        <a href="#" onclick="showSection('add-item')">Add Item</a>
        <a href="#" onclick="showSection('item-list')">Item List</a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="#" onclick="showSection('add-category')">Add Category</a>
            <a href="#" onclick="showSection('place-order')">Add Safe</a>
            <a href="#" onclick="showSection('order-list')">Safe List</a>
            <a href="#" onclick="showSection('add-customer')">Add Customer</a>
            <a href="#" onclick="showSection('customer-list')">Customer List</a>
        <?php endif; ?>
    </div>
    <div class="main">
        <h6 class="mb-4">Shop POS System</h6>

        <!-- Item List -->
        <div id="item-list" class="section">
            <h2 class="card-title">Item List</h2>
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Item Name</th>
                        <th>Description</th>
                        <th>Price</th>
                        <th>Quantity</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($item = mysqli_fetch_assoc($items_result)): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['name']) ?></td>
                            <td><?= htmlspecialchars($item['description']) ?></td>
                            <td><?= htmlspecialchars($item['price']) ?></td>
                            <td><?= htmlspecialchars($item['quantity']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <?php if (isset($_SESSION['user_id'])): ?>
            <!-- Add Category -->
            <div id="add-category" class="section">
                <h2 class="card-title">Add Category</h2>
                <form method="POST" action="index.php">
                    <div class="form-group">
                        <label for="name">Category Name:</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <button type="submit" class="btn mt-2 btn-primary" name="add_category">Add Category</button>
                </form>
            </div>

            <!-- Add Item -->
            <div id="add-item" class="section">
                <h2 class="card-title">Add Item</h2>
                <form method="POST" action="index.php">
                    <div class="form-group">
                        <label for="name">Item Name:</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Description:</label>
                        <textarea class="form-control" name="description"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="price">Price:</label>
                        <input type="number" class="form-control" name="price" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="quantity">Quantity:</label>
                        <input type="number" class="form-control" name="quantity" required>
                    </div>
                    <div class="form-group">
                        <label for="category_id">Category:</label>
                        <select class="form-control" name="category_id" required>
                            <?php 
                            mysqli_data_seek($categories_result, 0); // Reset result pointer to the beginning
                            while ($category = mysqli_fetch_assoc($categories_result)): ?>
                                <option value="<?= htmlspecialchars($category['id']) ?>"><?= htmlspecialchars($category['name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn mt-2 btn-primary" name="add_item">Add Item</button>
                </form>
            </div>

            <!-- Add Customer -->
            <div id="add-customer" class="section">
                <h2 class="card-title">Add Customer</h2>
                <form method="POST" action="index.php">
                    <div class="form-group">
                        <label for="name">Name:</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone:</label>
                        <input type="text" class="form-control" name="phone">
                    </div>
                    <div class="form-group">
                        <label for="address">Address:</label>
                        <textarea class="form-control" name="address"></textarea>
                    </div>
                    <button type="submit" class="btn mt-2 btn-primary" name="add_customer">Add Customer</button>
                </form>
            </div>

            <!-- Place Order -->
            <div id="place-order" class="section">
                <h2 class="card-title">Add safe</h2>
                <form method="POST" action="index.php">
                    <div class="form-group">
                        <label for="item_id">Item:</label>
                        <select class="form-control" name="item_id" required>
                            <?php 
                            mysqli_data_seek($items_result, 0); // Reset result pointer to the beginning
                            while ($item = mysqli_fetch_assoc($items_result)): ?>
                                <option value="<?= htmlspecialchars($item['id']) ?>"><?= htmlspecialchars($item['name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="quantity">Quantity:</label>
                        <input type="number" class="form-control" name="quantity" required>
                    </div>
                    <div class="form-group">
                        <label for="customer_id">Customer:</label>
                        <select class="form-control" name="customer_id" required>
                            <?php 
                            mysqli_data_seek($customers_result, 0); // Reset result pointer to the beginning
                            while ($customer = mysqli_fetch_assoc($customers_result)): ?>
                                <option value="<?= htmlspecialchars($customer['id']) ?>"><?= htmlspecialchars($customer['name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn mt-2 btn-primary" name="place_order">Add safe</button>
                </form>
            </div>

            <!-- Order List -->
            <div id="order-list" class="section">
        <h2 class="card-title">Safe List</h2>
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Item Name</th>
                    <th>Quantity</th>
                    <th>Total</th>
                    <th>Customer Name</th>
                    <th>Date</th>
                    <th>Paid</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($order = mysqli_fetch_assoc($orders_result)): ?>
                    <tr>
                        <td><?= htmlspecialchars($order['id']) ?></td>
                        <td><?= htmlspecialchars($order['item_name']) ?></td>
                        <td><?= htmlspecialchars($order['quantity']) ?></td>
                        <td><?= htmlspecialchars($order['total']) ?></td>
                        <td><?= htmlspecialchars($order['customer_name']) ?></td>
                        <td><?= htmlspecialchars($order['date']) ?></td>
                        <td><?= $order['paid'] ? 'Yes' : 'No' ?></td>
                        <td>
                            <?php if (!$order['paid']): ?>
                                <form method="POST" action="index.php" style="display:inline;">
                                    <input type="hidden" name="order_id" value="<?= htmlspecialchars($order['id']) ?>">
                                    <button type="submit" class="btn btn-success" name="mark_paid">Mark as Paid</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <h3>Total Sales: <?= htmlspecialchars($total_amount) ?></h3>
    </div>

            <!-- Customer List -->
            <div id="customer-list" class="section">
                <h2 class="card-title">Customer List</h2>
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Customer Name</th>
                            <th>Phone</th>
                            <th>Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        mysqli_data_seek($customers_result, 0); // Reset result pointer to the beginning
                        while ($customer = mysqli_fetch_assoc($customers_result)): ?>
                            <tr>
                                <td><?= htmlspecialchars($customer['name']) ?></td>
                                <td><?= htmlspecialchars($customer['phone']) ?></td>
                                <td><?= htmlspecialchars($customer['address']) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">
                <p>Please <a href="login.php">login</a> to access the admin features.</p>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function showSection(sectionId) {
            // Hide all sections
            var sections = document.querySelectorAll('.section');
            sections.forEach(function(section) {
                section.classList.remove('active');
            });
            // Show the selected section
            var section = document.getElementById(sectionId);
            if (section) {
                section.classList.add('active');
            }
        }

        // Show the item list by default
        document.addEventListener('DOMContentLoaded', function() {
            showSection('item-list');
        });
    </script>

    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
    </body>
</html>
