<?php
session_start();

// Retrieve the cart items and total amount from session
$cart_items = isset($_SESSION['cart_items']) ? $_SESSION['cart_items'] : [];
$total_amount = isset($_SESSION['total_amount']) ? $_SESSION['total_amount'] : 0;

// Check if the cart is empty
$cart_empty = empty($cart_items);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment - Zinnia Magic</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 20px;
        }

        h2 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 20px;
        }

        h3 {
            text-align: center;
            color: #e67e22;
            margin-top: 10px;
        }

        table {
            width: 80%;
            margin: 0 auto;
            border-collapse: collapse;
            background-color: white;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 12px;
            text-align: center;
        }

        th {
            background-color: #f39c12;
            color: white;
        }

        td {
            font-size: 16px;
        }

        .checkout-btn {
            display: block;
            width: 200px;
            margin: 20px auto;
            background-color: #f39c12;
            color: white;
            padding: 12px;
            font-size: 18px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
        }

        .checkout-btn:hover {
            background-color: #e67e22;
        }

        .payment-method {
            text-align: center;
            margin-top: 30px;
        }

        .payment-method p {
            font-size: 18px;
            color: #34495e;
            margin-bottom: 15px;
        }

        .payment-method input[type="submit"] {
            padding: 12px 25px;
            font-size: 18px;
            background-color: #27ae60;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .payment-method input[type="submit"]:hover {
            background-color: #2ecc71;
        }

        .payment-method-info {
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
            margin-top: 10px;
        }

        .payment-method-label {
            font-size: 18px;
            color: #2c3e50;
        }

        /* Navigation bar styles */
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #2c3e50;
            padding: 10px 20px;
            color: white;
        }

        .navbar a {
            color: white;
            text-decoration: none;
            font-size: 18px;
        }

        .navbar a:hover {
            color: #f39c12;
        }

        .navbar .left {
            flex: 1;
        }

        .navbar .right {
            display: flex;
            justify-content: space-around;
            width: 250px;
        }
    </style>
</head>
<body>

<!-- Navigation Bar -->
<div class="navbar">
    <div class="left">
        <a href="index.php">Home</a>
        <a href="categoryview.php" style="margin-left: 20px;">Shop</a> <!-- Added Shop link -->
    </div>
    <div class="right">
        <a href="cart.php">Cart</a>
        <a href="logout.php">Logout</a>
    </div>
</div>

<h2>Review Your Cart and Payment Method</h2>

<?php if ($cart_empty): ?>
    <p style="color: red; text-align: center;">Your cart is empty. Please add products to your cart before proceeding to payment.</p>
<?php else: ?>

    <!-- Cart Summary Table -->
    <table>
        <tr><th>Product</th><th>Price</th><th>Quantity</th><th>Total</th></tr>
        <?php
        foreach ($cart_items as $item) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($item['product']) . "</td>";
            echo "<td>₹" . $item['price'] . "</td>";
            echo "<td>" . $item['quantity'] . "</td>";
            echo "<td>₹" . $item['total'] . "</td>";
            echo "</tr>";
        }
        ?>
    </table>

    <h3>Total Amount: ₹<?php echo number_format($total_amount, 2); ?></h3>

    <!-- Payment Method Section -->
    <div class="payment-method">
        <p class="payment-method-label">Payment Method: <strong>Cash on Delivery</strong></p> <!-- Displaying Cash on Delivery next to Payment Method -->
        
        <form method="POST" action="checkoutconfirm.php">
            <!-- Hidden input for payment method -->
            <input type="hidden" name="payment_method" value="Cash on Delivery">
            <input type="submit" value="Place Order">
        </form>
    </div>

<?php endif; ?>

</body>
</html>
