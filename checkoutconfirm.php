<?php
session_start();

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit;
}

require 'connections/localhost.php';

$customer_email = mysqli_real_escape_string($conn, $_SESSION['email']);

// Get customer details (name and ID)
$customer_result = mysqli_query($conn, "SELECT customer_id, name FROM customers WHERE email = '$customer_email'");
$customer_data = mysqli_fetch_assoc($customer_result);
$customer_id = $customer_data['customer_id'];
$customer_name = $customer_data['name'];

// Fetch cart items before clearing
$cart_query = "SELECT p.name, p.price, c.quantity, c.product_id 
               FROM cart c
               INNER JOIN products p ON c.product_id = p.product_id
               WHERE c.customer_email = '$customer_email'";
$cart_result = mysqli_query($conn, $cart_query);

$total_amount = 0;
$order_items = [];
while ($row = mysqli_fetch_assoc($cart_result)) {
    $total = $row['price'] * $row['quantity'];
    $total_amount += $total;
    $order_items[] = [
        'product_id' => $row['product_id'],
        'name' => $row['name'],
        'price' => $row['price'],
        'quantity' => $row['quantity']
    ];
}

// Step 1: Insert new order with current date and time (NOW() function for both date and time)
$order_status = 'Pending';

$insert_order_query = "INSERT INTO orders (customer_id, total_amount, order_date, order_status) 
                       VALUES ('$customer_id', '$total_amount', NOW(), '$order_status')";
mysqli_query($conn, $insert_order_query);

// Get the new order ID
$order_id = mysqli_insert_id($conn);

// Step 2: Insert order details into order_details table
foreach ($order_items as $item) {
    $product_id = $item['product_id'];
    $quantity = $item['quantity'];
    $total = $item['price'] * $quantity;

    // Insert into order_details
    $order_details_query = "INSERT INTO order_details (order_id, product_id, quantity, total) 
                            VALUES ('$order_id', '$product_id', '$quantity', '$total')";
    mysqli_query($conn, $order_details_query);

    // Step 3: Reduce stock quantity in products table
    $reduce_stock_query = "UPDATE products SET stock_quantity = stock_quantity - '$quantity' WHERE product_id = '$product_id'";
    mysqli_query($conn, $reduce_stock_query);
}

// Step 4: Clear the cart
mysqli_query($conn, "DELETE FROM cart WHERE customer_email = '$customer_email'");
$_SESSION['cart_items'] = [];

// Payment method (hardcoded)
$payment_method = "Cash on Delivery";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Order Confirmation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 30px;
            background-color: #f9f9f9;
            color: #333;
        }
        .receipt-container {
            background: #fff;
            padding: 25px;
            border-radius: 12px;
            max-width: 700px;
            margin: auto;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .receipt-container h2 {
            color: #28a745;
        }
        .info-block {
            margin-bottom: 15px;
        }
        .info-block strong {
            width: 160px;
            display: inline-block;
        }
        .item-card {
            background-color: #f1f1f1;
            padding: 12px 18px;
            margin-bottom: 12px;
            border-radius: 8px;
        }
        .total {
            font-size: 18px;
            font-weight: bold;
            margin-top: 20px;
        }
        .print-btn {
            margin-top: 30px;
            padding: 10px 20px;
            background-color: #28a745;
            border: none;
            border-radius: 6px;
            color: white;
            font-size: 16px;
            cursor: pointer;
        }
        .print-btn:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>

<div class="receipt-container">
    <h2>Order placed successfully!</h2>
    <p>Your order has been confirmed. Below is your receipt:</p>

    <div class="info-block"><strong>Customer Name:</strong> <?php echo $customer_name; ?></div>
    <div class="info-block"><strong>Order ID:</strong> <?php echo $order_id; ?></div>
    <div class="info-block"><strong>Order Date & Time:</strong> <?php echo date('Y-m-d H:i:s'); ?></div>
    <div class="info-block"><strong>Payment Method:</strong> <?php echo $payment_method; ?></div>

    <h3>Items Ordered:</h3>
    <?php foreach ($order_items as $item): ?>
        <div class="item-card">
            <div><strong>Product:</strong> <?php echo htmlspecialchars($item['name']); ?></div>
            <div><strong>Quantity:</strong> <?php echo $item['quantity']; ?></div>
            <div><strong>Price:</strong> ₹<?php echo number_format($item['price'], 2); ?></div>
        </div>
    <?php endforeach; ?>

    <div class="total">Total Amount: ₹<?php echo number_format($total_amount, 2); ?></div>

    <button class="print-btn" onclick="window.print()">Print Receipt</button>
</div>

</body>
</html>

<?php $conn->close(); ?>
