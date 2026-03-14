<?php
session_start();

// ✅ Database connection
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

// ✅ Handle form submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $newName = trim($_POST['name']);
    $newAddress = trim($_POST['address']);
    if (empty($newName)) {
        $error = "Name cannot be empty.";
    } elseif (strlen($newName) < 4){
        $error = "Name should contain 4 letters minimum.";
    } elseif(!preg_match('/^[A-Za-z\s]+$/', $newName)){
        $error = "Name should contain alphabets and spaces only";
    }else{
        $updateQuery = "UPDATE customers SET name = ?, address = ? WHERE email = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("sss", $newName, $newAddress, $user['email']);
        if ($stmt->execute()) {
            $_SESSION['name'] = $newName;
            $message = "Profile updated successfully.";
        } else {
            $error = "Error updating profile.";
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
            padding-top: 80px;
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
        <h2>Edit Profile</h2>
        <?php if ($message || $error): ?>
            <div class="message">
                <?php 
                if ($message) {
                    echo $message;
                } 
                if ($error) {
                    echo "<div class='error'>" . $error . "</div>";
                }
                ?>
            </div>
        <?php endif; ?>
        <form method="POST">
            <label>Name:</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($_SESSION['name']); ?>" required>

            <label>Email (read-only):</label>
            <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>

            <label>Address:</label>
            <textarea name="address" rows="3" required><?php echo htmlspecialchars($user['address']); ?></textarea>


            <input type="submit" value="Update Profile">
        </form>
    </div>

</body>
</html>
