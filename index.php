<?php
ob_start();
session_start();

// Database connection
require 'connections/localhost.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Zinnia Magic</title>

    <style>
        body, h1, p, form, header, footer {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            box-sizing: border-box;
        }

        body {
            background-color: #f4f4f4;
            color: #333;
        }

        header {
            background-color: rgba(51, 51, 51, 0.8);
            color: white;
            padding: 20px;
            text-align: center;
            position: relative;
            z-index: 2;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
            letter-spacing: 1px;
        }

        header nav {
            margin-top: 15px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        header nav a {
            color: white;
            text-decoration: none;
            margin: 0 15px;
            font-size: 1.1em;
            text-transform: uppercase;
            padding: 10px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        header nav a:hover {
            background-color: rgba(255, 255, 255, 0.3);
            transform: scale(1.1);
        }

        .header-right {
            position: absolute;
            right: 20px;
            top: 20px;
            display: flex;
            gap: 20px;
            font-size: 1.1em;
            align-items: center;
        }

        .header-right a {
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 5px;
            background-color: rgba(255, 255, 255, 0.2);
            transition: background-color 0.3s ease;
        }

        .header-right a:hover {
            background-color: rgba(255, 255, 255, 0.5);
        }

        /* Dropdown menu styles */
        .dropdown {
            position: relative;
            display: inline-block;
        }

        .dropbtn {
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 8px 15px;
            font-size: 1.1em;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .dropbtn:hover {
            background-color: rgba(255, 255, 255, 0.5);
        }

        .dropdown-content {
            display: none;
            position: absolute;
            background-color: #444;
            min-width: 160px;
            box-shadow: 0px 8px 16px rgba(0,0,0,0.2);
            z-index: 3;
            right: 0;
            border-radius: 5px;
        }

        .dropdown-content a {
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            display: block;
            border-bottom: 1px solid #555;
        }

        .dropdown-content a:hover {
            background-color: #555;
        }

        .dropdown:hover .dropdown-content {
            display: block;
        }

        .background-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('images/background.png') no-repeat center center fixed;
            background-size: cover;
            z-index: -1;  /* Ensure it stays behind content */
        }

        footer {
            background-color: #333;
            color: white;
            padding: 15px;
            text-align: center;
            margin-top: 20px;
        }

        footer a {
            color: white;
            text-decoration: none;
            padding: 5px 10px;
            font-size: 1em;
            margin: 5px;
            border-radius: 5px;
        }

        footer a:hover {
            background-color: #555;
        }

        /* Category section */
        .categories-section {
            text-align: center;
            margin-top: 40px;
        }

        .category-container {
            display: flex;
            justify-content: center;
            gap: 30px;
            flex-wrap: wrap;
            margin-top: 20px;
        }

        .category {
            width: 200px;
            text-align: center;
        }

        .category img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 10px;
        }

        .category h4 {
            margin-top: 10px;
            font-size: 1.2em;
            color: #333;
        }

        .category a {
            text-decoration: none;
            color: #ff5733;
            margin-top: 10px;
            display: inline-block;
            font-size: 1.1em;
        }

    </style>
</head>
<body>

    <div class="background-image"></div>

    <header>
        <h1>Welcome to Zinnia Products</h1>
        <nav>
            <a href="index.php">Home</a>
            <a href="about.php">About</a>
            <a href="categoryview.php">Categories</a>
        </nav>

        <div class="header-right">
            <a href="cart.php">Cart</a>
            <?php if (isset($_SESSION['valid']) && $_SESSION['valid'] === true): ?>
                <div class="dropdown">
                    <button class="dropbtn">
                        <?php echo 'Hello, ' . htmlspecialchars($_SESSION['name']); ?>
                    </button>
                    <div class="dropdown-content">
                        <a href="edit_profile.php">Edit Profile</a>
                        <a href="change_password.php">change Password</a>
                        <a href="my_orders.php">My Orders</a>
                        <a href="contact.php">Support</a>
                        <a href="logout.php">Logout</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="login.php">Login</a>
            <?php endif; ?>
        </div>
    </header>

    <div class="categories-section">
        <div class="category-container">
        <div class="category">
            <a href="categoryview.php?category=home decor">
            <img src="images/home_decor.jpg" alt="Home Decor">
        </a>
        <h4>Home Decor</h4>
        </div>
        <div class="category">
            <a href="categoryview.php?category=stationary items">
            <img src="images/stationery.jpg" alt="Stationary Items">
            </a>
        <h4>Stationary Items</h4>
        </div>
        <div class="category">
            <a href="categoryview.php?category=bath and body">
            <img src="images/bath.jpg" alt="Bath and Body">
            </a>
        <h4>Bath and Body</h4>
        </div>
        <div class="category">
            <a href="categoryview.php?category=jewelry">
            <img src="images/jewel.jpg" alt="Jewelry">
            </a>
        <h4>Jewelry</h4>
        </div>
        <div class="category">
            <a href="categoryview.php?category=apparel">
            <img src="images/apparel.jpg" alt="Apparel">
            </a>
        <h4>Apparel</h4>
        </div>
        <div class="category">
            <a href="categoryview.php?category=accessories">
            <img src="images/accesories.jpg" alt="Accessories">
            </a>
        <h4>Accessories</h4>
    </div>
</div>
