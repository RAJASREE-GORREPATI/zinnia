<?php
require 'connections/localhost.php';
session_start();

// Sanitize the product ID
if (isset($_GET['id'])) {
    $product_id = mysqli_real_escape_string($conn, $_GET['id']);

    // Get product details
    $query = "SELECT * FROM products WHERE product_id = '$product_id'";
    $result = mysqli_query($conn, $query);
    if (mysqli_num_rows($result) > 0) {
        $product = mysqli_fetch_assoc($result);
    } else {
        // Redirect if product doesn't exist
        header('Location: index.php');
        exit;
    }

    // Add to cart
    if (isset($_POST['add_to_cart'])) {
        $quantity = $_POST['quantity'];
        $product_id = $_POST['product_id'];

        // Create an array for the cart if it doesn't exist
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        // Check if the product is already in the cart
        if (isset($_SESSION['cart'][$product_id])) {
            // Update the quantity if the product is already in the cart
            $_SESSION['cart'][$product_id]['quantity'] += $quantity;
        } else {
            // Add item to cart
            $_SESSION['cart'][$product_id] = [
                'name' => $product['name'],
                'price' => $product['price'],
                'quantity' => $quantity
            ];
        }

        // Provide feedback to the user
        echo "<script>alert('Product added to cart!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - Zinnia</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <div class="nav">
            <a href="index.php">Home</a>
            <a href="about.php">About</a>
            <a href="contact.php">Contact</a>
            <a href="login.php">Login</a> | <a href="register.php">Register</a>
            <a href="cart.php">Cart</a>
        </div>
    </header>

    <section class="product-details">
        <h2><?php echo htmlspecialchars($product['name']); ?></h2>
        <p><?php echo htmlspecialchars($product['description']); ?></p>
        <p>Price: $<?php echo number_format($product['price'], 2); ?></p>

        <form action="product.php?id=<?php echo $product_id; ?>" method="POST">
            <label for="quantity">Quantity:</label>
            <input type="number" name="quantity" value="1" min="1" required>
            <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
            <button type="submit" name="add_to_cart">Add to Cart</button>
        </form>
    </section>

    <footer>
        <p>&copy; 2025 Zinnia - All rights reserved.</p>
    </footer>
</body>
</html>
