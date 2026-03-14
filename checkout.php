<?php
session_start();

require 'connections/localhost.php';

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit;
}

$customer_email = mysqli_real_escape_string($conn, $_SESSION['email']);

$query = "SELECT c.cart_id, p.name, p.price, c.quantity 
          FROM cart c
          INNER JOIN products p ON c.product_id = p.product_id
          WHERE c.customer_email = '$customer_email'";

$result = mysqli_query($conn, $query) or die(mysqli_error($conn));

$total_amount = 0;

// If cart is empty, redirect to cart.php
if (mysqli_num_rows($result) == 0) {
    header("Location: cart.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout</title>
    <style>
        /* Your CSS styles here */
    </style>
</head>
<body>

<!-- Navbar -->
<div class="navbar">
    <div class="navbar-left">
        <a href="index.php">Home</a>
        <a href="categoryview.php">Shop</a>
    </div>

    <div class="navbar-right">
        <a href="cart.php" class="checkout-btn">Cart</a>
        <a href="logout.php" class="checkout-btn">Logout</a>
    </div>
</div>

<h2>Checkout</h2>

<?php
if (mysqli_num_rows($result) > 0) {
    echo '<table>';
    echo '<tr><th>Product</th><th>Price</th><th>Quantity</th><th>Total</th></tr>';

    while ($row = mysqli_fetch_assoc($result)) {
        $total = $row['price'] * $row['quantity'];
        $total_amount += $total;
        echo '<tr>';
        echo '<td>' . htmlspecialchars($row['name']) . '</td>';
        echo '<td>₹' . $row['price'] . '</td>';
        echo '<td>' . $row['quantity'] . '</td>';
        echo '<td>₹' . $total . '</td>';
        echo '</tr>';
    }

    echo '</table>';
    echo '<p class="total-amount">Total Amount: ₹' . $total_amount . '</p>';
}
?>

<!-- Confirm Checkout Button -->
<?php
if (mysqli_num_rows($result) > 0) {
    echo '<form method="POST" action="payment.php">';
    echo '<button type="submit" class="confirm-btn">checkout</button>';
    echo '</form>';
}
?>

</body>
</html>

<?php $conn->close(); ?>
