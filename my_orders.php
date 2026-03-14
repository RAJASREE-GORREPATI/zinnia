<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit;
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "project_zinnia";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$customer_email = mysqli_real_escape_string($conn, $_SESSION['email']);

// Get customer ID
$customer_query = "SELECT customer_id, name FROM customers WHERE email = '$customer_email'";
$customer_result = mysqli_query($conn, $customer_query);
$customer_data = mysqli_fetch_assoc($customer_result);
$customer_id = $customer_data['customer_id'];
$customer_name = $customer_data['name'];

// Fetch all orders for the customer
$order_query = "SELECT * FROM orders WHERE customer_id = '$customer_id' ORDER BY order_date DESC";
$order_result = mysqli_query($conn, $order_query);
if (isset($_POST['cancel_order'])) {
    $order_id = $_POST['order_id'];
    $new_status = "cancelled";

        // Check current status
    $currentStatusRes = $conn->query("SELECT order_status FROM orders WHERE order_id = $order_id");
    $currentStatus = $currentStatusRes->fetch_assoc()['order_status'];

        // Update order status
    $stmt = $conn->prepare("UPDATE orders SET order_status = ? WHERE order_id = ?");
    $stmt->bind_param("si", $new_status, $order_id);
    if ($stmt->execute()) {
        $message = "Order #$order_id was cancelled Successfully.";

            // If changed to cancelled, restore stock
        if ($currentStatus !== 'cancelled' && $new_status === 'cancelled') {
            $details = $conn->query("SELECT product_id, quantity FROM order_details WHERE order_id = $order_id");
            while ($row = $details->fetch_assoc()) {
                $product_id = $row['product_id'];
                $quantity = $row['quantity'];
                $conn->query("UPDATE products SET stock_quantity = stock_quantity + $quantity WHERE product_id = $product_id");
            }
            $message .= " Stock updated.";
        }
    } else {
        $errorMessage = "Failed to update status.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Orders</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 0;
            margin: 0;
            background-color: #f0f0f0;
            color: #333;
        }

        .navbar {
            background-color: #333;
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar a {
            color: white;
            text-decoration: none;
            font-weight: bold;
            padding: 8px 16px;
            border-radius: 5px;
            transition: background 0.3s;
        }

        .navbar a:hover {
            background-color: #555;
        }


        .container {
            max-width: 800px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .order-card {
            background-color: #fff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .order-info {
            margin: 10px 0;
        }

        .item-card {
            background-color: #f9f9f9;
            padding: 12px;
            margin-top: 10px;
            border-radius: 8px;
        }

        .order-title {
            font-size: 20px;
            font-weight: bold;
            color: #0066cc;
        }

        .status {
            font-weight: bold;
            color: #555;
        }
        .delete-button { background-color: #e74c3c; margin-right: 10px; }
        .delete-button:hover { background-color: #c0392b; }
    </style>
</head>
<body>

<div class="navbar">
    <div><a href="index.php">← Home</a></div>
    <div><a href="logout.php">Logout</a></div>
</div>

<div class="container">
    <h2>My Orders</h2>
    <p>Welcome, <?php echo htmlspecialchars($customer_name); ?>!</p>

    <?php if (mysqli_num_rows($order_result) > 0): ?>
        <?php while ($order = mysqli_fetch_assoc($order_result)): ?>
            <div class="order-card">
                <div class="order-title">Order ID: <?php echo $order['order_id']; if ($order["order_status"] == "pending" || $order["order_status"] == "shipped"){?> <form method="POST" action="" style="text-align: right;"><input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>"><button type="submit" name="cancel_order" class="delete-button">Cancel</button></form><?php } ?></div>
                <div class="order-info"><strong>Date:</strong> <?php echo $order['order_date']; ?></div>
                <div class="order-info"><strong>Status:</strong> <span class="status" style="color: green;"><?php echo $order['order_status']; ?></span></div>
                <div class="order-info"><strong>Total:</strong> ₹<?php echo number_format($order['total_amount'], 2); ?></div>

                <h4>Items:</h4>
                <?php
                $order_id = $order['order_id'];
                $details_query = "
                    SELECT od.quantity, od.total, p.name 
                    FROM order_details od 
                    INNER JOIN products p ON od.product_id = p.product_id 
                    WHERE od.order_id = '$order_id'
                ";
                $details_result = mysqli_query($conn, $details_query);
                while ($item = mysqli_fetch_assoc($details_result)):
                ?>
                    <div class="item-card">
                        <div><strong>Product:</strong> <?php echo htmlspecialchars($item['name']); ?></div>
                        <div><strong>Quantity:</strong> <?php echo $item['quantity']; ?></div>
                        <div><strong>Total:</strong> ₹<?php echo number_format($item['total'], 2); ?></div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>You have not placed any orders yet.</p>
    <?php endif; ?>
</div>

</body>
</html>

<?php $conn->close(); ?>
