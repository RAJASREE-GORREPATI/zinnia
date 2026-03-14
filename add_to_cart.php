<?php
session_start();
require 'connections/localhost.php';

if (isset($_GET['id']) && isset($_GET['category']) && isset($_SESSION['email'])) {
    $product_id = mysqli_real_escape_string($conn, $_GET['id']);
    $category = mysqli_real_escape_string($conn, $_GET['category']);
    $customer_email = mysqli_real_escape_string($conn, $_SESSION['email']);

    // Get stock and product name from the database
    $productQuery = "SELECT stock_quantity, name FROM products WHERE product_id = '$product_id'";
    $productResult = mysqli_query($conn, $productQuery);

    if (mysqli_num_rows($productResult) > 0) {
        $product = mysqli_fetch_assoc($productResult);
        $stock_quantity = $product['stock_quantity'];
        $product_name = $product['name'];

        if ($stock_quantity <= 0) {
            header("Location: categoryview.php?category=$category&message=" . urlencode('Product is out of stock.') . "&messageType=error");
            exit;
        }

        $requested_quantity = isset($_GET['quantity']) ? (int)$_GET['quantity'] : 1;

        if ($requested_quantity > $stock_quantity) {
            $requested_quantity = $stock_quantity;
            header("Location: categoryview.php?category=$category&message=" . urlencode("Only $stock_quantity items are available. Added $stock_quantity to cart.") . "&messageType=warning");
            exit;
        }

        $checkQuery = "SELECT * FROM cart WHERE customer_email = '$customer_email' AND product_id = '$product_id'";
        $result = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($result) > 0) {
            $updateQuery = "UPDATE cart SET quantity = quantity + $requested_quantity WHERE customer_email = '$customer_email' AND product_id = '$product_id'";
            if (mysqli_query($conn, $updateQuery)) {
                header("Location: categoryview.php?category=$category&message=" . urlencode("$product_name already in cart. Increased quantity.") . "&messageType=success");
            } else {
                header("Location: categoryview.php?category=$category&message=" . urlencode('Failed to update the cart. Please try again.') . "&messageType=error");
            }
        } else {
            $insertQuery = "INSERT INTO cart (customer_email, product_id, quantity, date_added) VALUES ('$customer_email', '$product_id', $requested_quantity, NOW())";
            if (mysqli_query($conn, $insertQuery)) {
                header("Location: categoryview.php?category=$category&message=" . urlencode("$product_name added to cart successfully.") . "&messageType=success");
            } else {
                header("Location: categoryview.php?category=$category&message=" . urlencode('Failed to add item to cart. Please try again.') . "&messageType=error");
            }
        }
    } else {
        header("Location: categoryview.php?category=$category&message=" . urlencode('Product not found.') . "&messageType=error");
    }

    exit;
} else {
    if (!isset($_SESSION['email'])) {
        header("Location: login.php?message=" . urlencode('Please login to add items to cart.') . "&messageType=error");
    } else {
        header("Location: categoryview.php?message=" . urlencode('Invalid action.') . "&messageType=error");
    }
    exit;
}
?>
