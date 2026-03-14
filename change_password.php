<?php
session_start();

require 'connections/localhost.php';

// ✅ Auth check
if (!isset($_SESSION['valid']) || $_SESSION['valid'] !== true) {
    header("Location: login.php");
    exit();
}

$userEmail = $_SESSION['email'];
$message = "";
$error = "";

// ✅ Fetch user data
$stmt = $conn->prepare("SELECT * FROM customers WHERE email = ?");
$stmt->bind_param("s", $userEmail);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

function validateString($input) {
    if (strlen($input) < 8) {
        return "Password should contain atleast 8 characters minimum.";
    }
    elseif (!preg_match('/[A-Z]/', $input)) {
        return "Password contain at least one uppercase letter.";
    }elseif (!preg_match('/[a-z]/', $input)) {
        return "Password contain at least one lowercase letter.";
    }elseif (!preg_match('/[0-9]/', $input)) {
        return "Password contain at least one number.";
    }elseif (!preg_match('/[\W_]/', $input)) { // \W matches non-word characters
        return "Password contain at least one special character.";
    }else{
        return "";
    }
}


// ✅ Handle form submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $old_password = trim($_POST['old_password']);
    $new_password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    if (empty($old_password) || empty($new_password) || empty($confirm_password)){
        $error = "All fields are required.";
    }elseif (validateString($old_password)!=""){
        $error = validateString($old_password);
    }elseif (validateString($new_password)!=""){
        $error = validateString($new_password);
    }elseif (validateString($confirm_password)!=""){
        $error = validateString($confirm_password);
    }elseif ($new_password !== $confirm_password){
        $error = "Password and confirm password should match.";
    }elseif ($old_password == $new_password){
        $error = "New password cannot be same as old password.";
    }elseif (!password_verify($old_password, $user["password"])){
        $error = "Old password was incorrect.";
    }else {
        $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
        $updateQuery = "UPDATE customers SET password = ? WHERE email = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("ss", $hashedPassword, $user["email"]);
        if ($stmt->execute()) {
            $message = "Password changed successfully.";
        }else {
            $error = "Error in changing password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit Profile</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding-top: 100px;
        }

        /* Navigation bar */
        .navbar {
            background-color: #333;
            color: white;
            padding: 15px 30px;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 1000;
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

        .profile-form {
            max-width: 450px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .profile-form h2 {
            text-align: center;
            margin-bottom: 25px;
        }

        .profile-form label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
        }

        .profile-form input, .profile-form textarea {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-sizing: border-box;
        }

        .profile-form input[type="submit"] {
            margin-top: 20px;
            background-color: #333;
            color: white;
            border: none;
            cursor: pointer;
        }

        .profile-form input[type="submit"]:hover {
            background-color: #555;
        }

        .message {
            text-align: center;
            color: green;
            margin-top: 10px;
        }
        .error {
            text-align: center;
            color: red;
            margin-top: 10px;
        }
    </style>
</head>
<body>

    <!-- Top Navigation Bar -->
    <div class="navbar">
        <a href="index.php">← Home</a>
        <a href="logout.php">Logout</a>
    </div>

    <!-- Profile Form -->
    <div class="profile-form">
        <h2>Change Password</h2>
        <?php if ($message || $error): ?>
            <div class="message">
                <?php 
                if ($message) {
                    echo $message;
                    echo '<script>
                        window.addEventListener("beforeunload", function () {
                            navigator.sendBeacon("destroy_session.php");
                        });
                    </script>';
                    header("Location: login.php"); // Replace 'forgot_password.php' with the page you want to refresh
                    exit();
                } 
                if ($error) {
                    echo "<div class='error'>" . $error . "</div>";
                }
                ?>
            </div>
        <?php endif; ?>
        <form method="POST">

            <label>Old Password</label>
            <input type="password" name="old_password" required/>

            <label>New Password</label>
            <input type="password" name="password" required/>

            <label>Confirm Password</label>
            <input type="password" name="confirm_password" required/>

            <input type="submit" value="Change Password">
        </form>
    </div>

</body>
</html>
