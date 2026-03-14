<?php
session_start();

require 'connections/localhost.php';
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit;
}

$customer_email = mysqli_real_escape_string($conn, $_SESSION['email']);

$query = "SELECT c.cart_id, p.name, p.price, c.quantity, p.stock_quantity
          FROM cart c
          INNER JOIN products p ON c.product_id = p.product_id
          WHERE c.customer_email = '$customer_email'";

$result = mysqli_query($conn, $query) or die(mysqli_error($conn));

// Store cart details in the session
$cart_items = [];
$total_amount = 0;

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $total = $row['price'] * $row['quantity'];
        $cart_items[] = [
            'cart_id' => $row['cart_id'], // Store cart_id for each item
            'product' => $row['name'],
            'price' => $row['price'],
            'quantity' => $row['quantity'],
            'total' => $total,
            'stock_quantity' => $row['stock_quantity'] // Store stock_quantity
        ];
        $total_amount += $total;
    }

    // Store cart items and total in session
    $_SESSION['cart_items'] = $cart_items;
    $_SESSION['total_amount'] = $total_amount;
} else {
    $_SESSION['cart_items'] = [];
    $_SESSION['total_amount'] = 0;
}

// Check if the quantity update form has been submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_quantity'])) {
    $cart_id = $_POST['cart_id'];
    $new_quantity = $_POST['quantity'];

    // Get stock quantity for the product
    $stock_query = "SELECT p.stock_quantity FROM cart c 
                    INNER JOIN products p ON c.product_id = p.product_id 
                    WHERE c.cart_id = '$cart_id' AND c.customer_email = '$customer_email'";
    $stock_result = mysqli_query($conn, $stock_query);
    $stock_data = mysqli_fetch_assoc($stock_result);

    $available_stock = $stock_data['stock_quantity'];

    // Check if requested quantity exceeds available stock
    if ($new_quantity > $available_stock) {
        // Redirect with a message indicating the available stock
        $message = "Only $available_stock items are available in stock.";
        header("Location: cart.php?message=$message&messageType=warning");
        exit;
    }

    if ($new_quantity > 0) {
        // Update the quantity in the cart database
        $update_query = "UPDATE cart SET quantity = '$new_quantity' WHERE cart_id = '$cart_id' AND customer_email = '$customer_email'";
        if (mysqli_query($conn, $update_query)) {
            // Refresh the session and cart items after update
            header("Location: cart.php");
            exit;
        } else {
            echo "Error updating quantity: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Cart - Zinnia Magic</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 20px;
        }

        .navbar {
            background-color: #2c3e50;
            color: white;
            padding: 12px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .navbar a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            padding: 8px 14px;
            border-radius: 5px;
        }

        .navbar a:hover {
            background-color: #34495e;
        }

        .navbar-right {
            display: flex;
            gap: 20px;
        }

        .logout-btn {
            background-color: #e67e22;
            color: white;
            padding: 8px 14px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .logout-btn:hover {
            background-color: #d35400;
        }

        h2 {
            text-align: center;
            margin: 25px 0 10px;
            color: #2c3e50;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 12px;
            text-align: left;
        }

        .cart-btn, .remove-btn {
            background-color: #28a745;
            color: white;
            padding: 6px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .remove-btn {
            background-color: #dc3545;
        }

        .remove-btn:hover, .cart-btn:hover {
            background-color: #d35400;
        }

        /* Checkout button */
        .checkout-btn {
            background-color: #f39c12;
            color: white;
            padding: 8px 14px;
            font-size: 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .checkout-btn:hover {
            background-color: #e67e22;
        }

        .alert {
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
        }

        .alert-warning {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<div class="navbar">
    <div class="navbar-left">
        <a href="index.php">Home</a>
        <a href="categoryview.php" class="navbar-btn">Shop</a>
    </div>

    <div class="navbar-right">
        <a href="payment.php" class="checkout-btn">Checkout</a>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
</div>

<h2>Your Cart</h2>

<?php
if (isset($_GET['message']) && isset($_GET['messageType'])) {
    $message = htmlspecialchars($_GET['message']);
    $messageType = $_GET['messageType'];

    if ($messageType == 'warning') {
        echo "<div class='alert alert-warning'>$message</div>";
    }
}

if (count($cart_items) > 0) {
    echo '<table>';
    echo '<tr><th>Product</th><th>Price</th><th>Quantity</th><th>Total</th><th>Actions</th></tr>';

    // Display cart items
    foreach ($cart_items as $item) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($item['product']) . '</td>';
        echo '<td>₹' . $item['price'] . '</td>';
        echo '<td>';
        // Form to update quantity
        echo '<form method="POST" action="cart.php" style="display:inline-block;">';
        echo '<input type="hidden" name="cart_id" value="' . $item['cart_id'] . '">';
        echo '<input type="number" name="quantity" value="' . $item['quantity'] . '" min="1" style="width: 60px;">';
        echo '<input type="submit" name="update_quantity" value="Update" class="cart-btn">';
        echo '</form>';
        echo '</td>';
        echo '<td>₹' . $item['total'] . '</td>';
        echo '<td>';
        echo '<a href="remove_cart.php?del=' . $item['cart_id'] . '" class="remove-btn">Remove</a>';
        echo '</td>';
        echo '</tr>';
    }

    echo '</table>';
    echo "<h3>Total Amount: ₹" . number_format($total_amount, 2) . "</h3>";
} else {
    echo "<p><center>Your cart is empty.</center></p>";
}
?>

</body>
</html>

<?php $conn->close(); ?>
