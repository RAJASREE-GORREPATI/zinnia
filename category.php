<?php
require 'connections/localhost.php';

$category_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$category_query = mysqli_query($conn, "SELECT name FROM categories WHERE category_id=$category_id");
$category = mysqli_fetch_assoc($category_query);

$product_query = mysqli_query($conn, "SELECT * FROM products WHERE category_id=$category_id");
?>

<!DOCTYPE html>
<html>
<head>
  <title><?php echo $category['name']; ?> - Zinnia Magic</title>
  <style>
    .product {
      display: inline-block;
      width: 200px;
      margin: 15px;
      border: 1px solid #ccc;
      border-radius: 10px;
      padding: 10px;
      text-align: center;
    }
    .product img {
      width: 100%;
      height: 150px;
      object-fit: cover;
    }
  </style>
</head>
<body>

<h2 style="text-align:center;"><?php echo $category['name']; ?> Products</h2>

<div style="display:flex; flex-wrap:wrap; justify-content:center;">
  <?php
  while ($product = mysqli_fetch_assoc($product_query)) {
      echo '<div class="product">';
      echo '<img src="images/' . $product['image'] . '" alt="' . $product['name'] . '">';
      echo '<h4>' . $product['name'] . '</h4>';
      echo '<p>$' . number_format($product['price'], 2) . '</p>';
      echo '<a href="add_to_cart.php?id=' . $product['product_id'] . '">Add to Cart</a>';
      echo '</div>';
  }
  ?>
</div>

</body>
</html>
