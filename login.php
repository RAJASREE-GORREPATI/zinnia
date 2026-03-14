<?php
ob_start();
session_start();

require 'connections/localhost.php';

$login_error = ''; // Initialize login error variable

// Process the login when the form is submitted
if (isset($_POST['login'])) {
    $email = trim(mysqli_real_escape_string($conn, $_POST['email']));
    $password = trim(mysqli_real_escape_string($conn, $_POST['password']));

    // Basic validation
    if (empty($email) || empty($password)) {
        $login_error = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $login_error = "Invalid email format.";
    } else {
        // Query to fetch the password
        $query = "SELECT password, name FROM customers WHERE email = '$email'";
        $query_run = mysqli_query($conn, $query);

        if (mysqli_num_rows($query_run) > 0) {
            // User exists, fetch password and verify
            $user = mysqli_fetch_assoc($query_run);

            // Check password
            if (password_verify($password, $user['password'])) {
                // Successful login
                $_SESSION['valid'] = true;
                $_SESSION['email'] = $email;
                $_SESSION['name'] = $user['name'];

                // Redirect to the homepage (index.php)
                header("Location: index.php");
                exit; // Ensure no further code is executed
            } else {
                $login_error = "Incorrect password.";
            }
        } else {
            $login_error = "User does not exist.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>

    <!-- Embedded CSS -->
    <style>
        body, h1, p, form {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        /* Updated body with flexbox to align content to the right */
        body {
            background-image: url('images/login.jpg'); /* Path to your background image */
            background-size: cover; /* Ensure the image covers the entire background */
            background-position: center; /* Center the image */
            background-attachment: fixed; /* Make the background fixed when scrolling */
            display: flex;
            justify-content: flex-end; /* Move content to the right */
            align-items: center;
            height: 100vh;
            padding-top: 60px;
            box-sizing: border-box;
        }

        /* Top Navigation Bar */
        .top-nav {
            position: absolute;
            top: 0;
            width: 100%;
            padding: 10px 20px;
            box-sizing: border-box;
            display: flex;
            justify-content: flex-end;
            z-index: 999;
        }

        .admin-btn {
            background-color: #007bff;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
        }

        .admin-btn:hover {
            background-color: #4cae4c;
        }

        /* Adjusted login-section for the right-aligned form */
        .login-section {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            padding: 20px;
            box-sizing: border-box;
            margin-right: 250px;
            margin-bottom:50px;
        }

        .login-container h1 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
            font-size: 24px;
        }

        /* Input Fields */
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

        /* Primary Button */
        .btn-primary {
            padding: 10px 20px;
            background-color:rgb(92, 124, 184);
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

        /* Link Container */
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

        /* Error Message */
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin-top: 15px;
            text-align: center;
        }
    </style>

</head>
<body>

    <!-- Admin Login Button on Top Right -->
    <div class="top-nav">
        <a class="admin-btn" href="admin/emp_login.php">Employee Login</a>
    </div>

    <div class="login-section">
        <div class="login-container">
            <h1>User Login</h1>

            <!-- Start of login form -->
            <form action="login.php" method="post">
                <div class="input-group">
                    <label for="email">Your Email</label>
                    <input name="email" type="email" maxlength="30" required placeholder="Enter email">
                </div>

                <div class="input-group">
                    <label for="password">Password</label>
                    <input name="password" type="password" maxlength="30" required placeholder="Enter password">
                </div>

                <div class="input-group">
                    <input class="btn-primary" name="login" type="submit" value="Login">
                </div>
            </form>
            <!-- End of login form -->

            <div class="link-container">
        		<a href="forgotpassword.php">Forgot Password?</a>
                <p>Don't have an account? <a href="register.php">Register here</a>.</p>
            </div>

            <?php
            // Display login error message if set
            if (!empty($login_error)) {
                echo '<div class="error-message">' . $login_error . '</div>';
            }
            ?>
        </div>
    </div>

</body>
</html>
