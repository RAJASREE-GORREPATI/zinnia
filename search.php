<?php
session_start();

require 'connections/localhost.php';

$query = isset($_GET['query']) ? trim($_GET['query']) : '';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Search Results for "<?php echo htmlspecialchars($query); ?>"</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f9f9f9;
            margin: 0;
        }
        .navbar {
            background-color: #333;
            padding: 12px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }
        .navbar a {
            color: white;
            text-decoration: none;
            margin-right: 20px;
        }
        .search-form input[type="text"] {
            padding: 8px;
            font-size: 14px;
            border: none;
            border-radius: 4px 0 0 4px;
            outline: none;
        }
        .search-form button {
            padding: 8px 12px;
            font-size: 14px;
            background-color: #27ae60;
            color: white;
            border: none;
            border-radius: 0 4px 4px 0;
            cursor: pointer;
        }
        h2 {
            text-align: center;
            margin-top: 20px;
        }
        .product-grid {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 25px;
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
            margin: 10px 0 5px;
        }
        .product-price {
            color: #27ae60;
            margin: 5px 0;
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
        }
        .cart-btn:hover {
            background-color: #d35400;
        }
    </style>
</head>
<body>

<div class="navbar">
    <a href="index.php">Home</a>
    <form class="search-form" action="search.php" method="GET">
        <input type="text" name="query" placeholder="Search products..." value="<?php echo htmlspecialchars($query); ?>" required>
        <button type="submit">Search</button>
    </form>
    <a href="cart.php">Cart</a>
</div>

<h2>Search Results for "<?php echo htmlspecialchars($query); ?>"</h2>

<div class="product-grid">
<?php
if ($query !== '') {
    $sql = "SELECT p.product_id, p.name, p.price, p.description, p.image, c.name AS category 
            FROM products p
            JOIN categories c ON p.category_id = c.category_id
            WHERE p.name LIKE ? OR p.description LIKE ?";
    $stmt = $conn->prepare($sql);
    $likeQuery = '%' . $query . '%';
    $stmt->bind_param("ss", $likeQuery, $likeQuery);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo '<div class="product-card">';
            echo '<img src="images/' . htmlspecialchars($row['image']) . '" alt="' . htmlspecialchars($row['name']) . '">';
            echo '<div class="product-name">' . htmlspecialchars($row['name']) . '</div>';
            echo '<div class="product-price">₹' . htmlspecialchars($row['price']) . '</div>';
            echo '<div class="product-desc">' . htmlspecialchars($row['description']) . '</div>';
            echo '<div class="product-category"><strong>Category:</strong> ' . htmlspecialchars($row['category']) . '</div>';
            echo '<form method="GET" action="add_to_cart.php">';
            echo '<input type="hidden" name="id" value="' . $row['product_id'] . '">';
            echo '<input type="hidden" name="category" value="' . htmlspecialchars($row['category']) . '">';
            echo '<button type="submit" class="cart-btn">Add to Cart</button>';
            echo '</form>';
            echo '</div>';
        }
    } else {
        echo "<p style='text-align:center;'>No matching products found.</p>";
    }

    $stmt->close();
} else {
    echo "<p style='text-align:center;'>Please enter a search term.</p>";
}
$conn->close();
?>
</div>

</body>
</html>
