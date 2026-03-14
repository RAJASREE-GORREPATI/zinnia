<?php
session_start();

// Database connection
require 'connections/localhost.php';
if (!isset($_POST['cart_id']) || !isset($_POST['quantity']) || !isset($_SESSION['email'])) {
    header("Location: cart.php");
    exit;
}

$cart_id = mysqli_real_escape_string($conn, $_POST['cart_id']);
$quantity = mysqli_real_escape_string($conn, $_POST['quantity']);
$customer_email = mysqli_real_escape_string($conn, $_SESSION['email']);

// Update the cart quantity
$updateQuery = "UPDATE cart SET quantity = '$quantity' WHERE cart_id = '$cart_id' AND customer_email = '$customer_email'";
mysqli_query($conn, $updateQuery) or die(mysqli_error($conn));

header("Location: cart.php");
exit;
?>
