<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit;
}
require 'connections/localhost.php';

$category = isset($_GET['category']) ? mysqli_real_escape_string($conn, $_GET['category']) : '';
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$message = isset($_GET['message']) ? $_GET['message'] : '';
$messageType = isset($_GET['messageType']) ? $_GET['messageType'] : 'success';

$query = "SELECT name FROM categories";
$categories = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($category ?: 'All'); ?> Products</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }

        .navbar {
            background-color: #2c3e50;
            color: white;
            padding: 12px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }

        .navbar-left {
            display: flex;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
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

        .dropdown-select {
            padding: 8px 10px;
            font-size: 14px;
            border-radius: 5px;
            border: none;
        }

        .search-form input[type="text"] {
            padding: 6px 10px;
            border-radius: 5px;
            border: none;
            font-size: 14px;
        }

        .search-form button {
            padding: 6px 12px;
            background-color: #e67e22;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .search-form {
            display: flex;
            gap: 6px;
            align-items: center;
        }

        h2 {
            text-align: center;
            margin: 25px 0 10px;
            color: #2c3e50;
        }

        .message-box {
            text-align: center;
            font-size: 16px;
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 5px;
            width: 80%;
            margin-left: auto;
            margin-right: auto;
        }

        .message-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .product-grid {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 30px;
            padding: 20px;
        }

        .product-card {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 10px;
            width: 230px;
            padding: 15px;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0,0,0,0.08);
            transition: transform 0.2s;
        }

        .product-card:hover {
            transform: scale(1.02);
        }

        .product-card img {
            width: 100%;
            height: 160px;
            object-fit: cover;
            border-radius: 8px;
        }

        .product-name {
            font-weight: bold;
            margin-top: 12px;
            font-size: 16px;
            color: #2c3e50;
        }

        .product-price {
            color: #27ae60;
            margin: 8px 0;
            font-weight: bold;
        }

        .product-desc {
            font-size: 14px;
            color: #555;
            margin-bottom: 10px;
        }

        .cart-btn {
            background-color: #e67e22;
            color: white;
            padding: 8px 14px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }

        .cart-btn:hover {
            background-color: #d35400;
        }

        p {
            text-align: center;
            color: #555;
            margin-top: 30px;
        }

        .navbar-right {
            display: flex;
            gap: 15px;
            align-items: center;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<div class="navbar">
    <div class="navbar-left">
        <a href="index.php">← Home</a>

        <select class="dropdown-select" onchange="location.href='categoryview.php?category=' + encodeURIComponent(this.value)">
            <option value="">All Categories</option>
            <?php while ($cat = $categories->fetch_assoc()) {
                $cat_name = htmlspecialchars($cat['name']);
                $selected = ($cat_name === $category) ? 'selected' : '';
                echo "<option value=\"{$cat_name}\" {$selected}>{$cat_name}</option>";
            } ?>
        </select>

        <form class="search-form" method="GET" action="categoryview.php">
            <input type="text" name="search" placeholder="Search items..." value="<?php echo htmlspecialchars($search); ?>">
            <?php if ($category) echo "<input type='hidden' name='category' value='" . htmlspecialchars($category) . "'>"; ?>
            <button type="submit">Search</button>
        </form>
    </div>

    <div class="navbar-right">
        <a href="cart.php">Cart</a>
        <a href="logout.php">Logout</a>
    </div>
</div>

<!-- Page Title -->
<h2>
<?php 
    echo $search ? "Search results for: \"$search\"" : ($category ? htmlspecialchars($category) : "All") . " Products"; 
?>
</h2>

<!-- Display Message -->
<?php
if ($message) {
    $class = $messageType === 'error' ? 'message-box message-error' : 'message-box message-success';
    echo "<div class=\"$class\">" . htmlspecialchars($message) . "</div>";
}
?>

<!-- Product Grid -->
<div class="product-grid">
<?php
$query = "SELECT p.product_id, p.name, p.price, p.description, p.image 
          FROM products p
          INNER JOIN categories c ON p.category_id = c.category_id
          WHERE 1";

$params = [];
$types = '';

if (!empty($category)) {
    $query .= " AND c.name = ?";
    $types .= 's';
    $params[] = $category;
}

if (!empty($search)) {
    $query .= " AND p.name LIKE ?";
    $types .= 's';
    $params[] = "%$search%";
}

$stmt = $conn->prepare($query);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo '<div class="product-card">';
        echo '<img src="images/' . htmlspecialchars($row['image']) . '" alt="' . htmlspecialchars($row['name']) . '">';
        echo '<div class="product-name">' . htmlspecialchars($row['name']) . '</div>';
        echo '<div class="product-price">₹' . htmlspecialchars($row['price']) . '</div>';
        echo '<form method="GET" action="add_to_cart.php">';
        echo '<input type="hidden" name="id" value="' . $row['product_id'] . '">';
        echo '<input type="hidden" name="category" value="' . htmlspecialchars($category) . '">';
        echo '<button type="submit" class="cart-btn">Add to Cart</button>';
        echo '</form>';
        echo '</div>';
    }
} else {
    echo "<p>No products found.</p>";
}
?>
</div>

</body>
</html>

<?php $conn->close(); ?>
