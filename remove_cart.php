<?php
session_start();

// ✅ DATABASE CONNECTION
require 'connections/localhost.php';

if (!isset($_GET['del']) || !isset($_SESSION['email']) || empty(trim($_GET['del']))) {
    header("Location: cart.php");
    exit;
}

$cart_id = mysqli_real_escape_string($conn, trim($_GET['del']));
$customer_email = mysqli_real_escape_string($conn, $_SESSION['email']);

// First, fetch current quantity
$checkQuery = "SELECT quantity FROM cart WHERE cart_id = '$cart_id' AND customer_email = '$customer_email'";
$result = mysqli_query($conn, $checkQuery) or die(mysqli_error($conn));
$row = mysqli_fetch_assoc($result);

if ($row && $row['quantity'] > 1) {
    // If quantity > 1, reduce it
    $updateQuery = "UPDATE cart SET quantity = quantity - 1 WHERE cart_id = '$cart_id' AND customer_email = '$customer_email'";
    mysqli_query($conn, $updateQuery) or die(mysqli_error($conn));
} else {
    // If only one left, delete it
    $deleteQuery = "DELETE FROM cart WHERE cart_id = '$cart_id' AND customer_email = '$customer_email'";
    mysqli_query($conn, $deleteQuery) or die(mysqli_error($conn));
}

header("Location: cart.php");
exit;
?>
