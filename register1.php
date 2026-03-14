<?php
// Start session
ob_start();
session_start();

// Database connection details
$servername = "localhost";
$username = "root";  // Default username for XAMPP
$password = "";  // Default password is empty for XAMPP
$dbname = "project_zinnia";  // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$registration_success = false;  // Variable to track registration success

// Check if form is submitted
if (isset($_POST['register'])) {
    // Sanitize input
    $name = trim(mysqli_real_escape_string($conn, $_POST['name']));
    $email = trim(mysqli_real_escape_string($conn, $_POST['email']));
    $password = trim(mysqli_real_escape_string($conn, $_POST['password']));
    $confirm_password = trim(mysqli_real_escape_string($conn, $_POST['confirm_password']));
    $address = trim(mysqli_real_escape_string($conn, $_POST['address'])); // New address field

    // Validate input
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password) || empty($address)) {
        echo "All fields are required.";
        exit;
    }

    if ($password !== $confirm_password) {
        echo "Passwords do not match.";
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Invalid email format.";
        exit;
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Check if email already exists
    $email_check_query = "SELECT * FROM customers WHERE email = '$email'";
    $email_check_result = mysqli_query($conn, $email_check_query);
    if (mysqli_num_rows($email_check_result) > 0) {
        echo "Email already exists. Please login.";
        exit;
    }

    // Insert new user into the database, including address
    $insert_query = "INSERT INTO customers (name, email, password, address) VALUES ('$name', '$email', '$hashed_password', '$address')";
    if (mysqli_query($conn, $insert_query)) {
        $registration_success = true;  // Set the success flag to true
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Page</title>

    <!-- Embedded CSS -->
    <style>
        body, h1, p, form {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        body {
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .register-section {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            padding: 20px;
            box-sizing: border-box;
        }

        .register-container h1 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
            font-size: 24px;
        }

        .input-group {
            margin-bottom: 15px;
            display: flex;
            flex-direction: column;
        }

        .input-group label {
            font-size: 16px;
            color: #555;
            margin-bottom: 5px;
        }

        .input-group input {
            padding: 10px;
            font-size: 16px;
            border-radius: 5px;
            border: 1px solid #ddd;
            box-sizing: border-box;
        }

        .btn-primary {
            padding: 10px 20px;
            background-color: #5cb85c;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
        }

        .btn-primary:hover {
            background-color: #4cae4c;
        }

        .link-container {
            text-align: center;
            margin-top: 10px;
        }

        .link-container a {
            text-decoration: none;
            color: #007bff;
        }

        .link-container a:hover {
            text-decoration: underline;
        }

        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 5px;
            margin-top: 15px;
            text-align: center;
        }
    </style>

</head>
<body>

    <div class="register-section">
        <div class="register-container">
            <h1>Create an Account</h1>

            <!-- Start of register form -->
            <form action="register.php" method="post">
                <div class="input-group">
                    <label for="name">Your Name</label>
                    <input name="name" type="text" maxlength="50" required placeholder="Enter your name">
                </div>

                <div class="input-group">
                    <label for="email">Your Email</label>
                    <input name="email" type="email" maxlength="100" required placeholder="Enter your email">
                </div>

                <div class="input-group">
                    <label for="password">Password</label>
                    <input name="password" type="password" maxlength="30" required placeholder="Enter your password">
                </div>

                <div class="input-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input name="confirm_password" type="password" maxlength="30" required placeholder="Confirm your password">
                </div>

                <div class="input-group">
                    <label for="address">Your Address</label>
                    <input name="address" type="text" maxlength="255" required placeholder="Enter your address">
                </div>

                <div class="input-group">
                    <input class="btn-primary" name="register" type="submit" value="Register">
                </div>
            </form>
            <!-- End of register form -->

            <div class="link-container">
                <p>Already have an account? <a href="login.php">Login here</a>.</p>
            </div>

            <?php
            // Display success message if registration was successful
            if ($registration_success) {
                echo '<div class="success-message">Registration successful! You can now <a href="login.php">Login</a>.</div>';
            }
            ?>

        </div>
    </div>

</body>
</html>
