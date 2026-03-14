<?php
// Start session
ob_start();
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
// Database connection details
require 'connections/localhost.php';
$error  = '';
$message = '';
$verify_email_success = isset($_SESSION["verify_email_success"]) ? $_SESSION["verify_email_success"] : false;
$registration_success = false;  // Variable to track registration success
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
function generateSecureCode() {
    $letters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $digits = '0123456789';
    
    // Pick 3 random letters
    $letterPart = '';
    for ($i = 0; $i < 3; $i++) {
        $letterPart .= $letters[rand(0, strlen($letters) - 1)];
    }
    
    // Pick 3 random digits
    $digitPart = '';
    for ($i = 0; $i < 3; $i++) {
        $digitPart .= $digits[rand(0, strlen($digits) - 1)];
    }
    
    // Pick 2 random characters (either letter or digit)
    $allChars = $letters . $digits;
    $extraPart = '';
    for ($i = 0; $i < 2; $i++) {
        $extraPart .= $allChars[rand(0, strlen($allChars) - 1)];
    }
    
    // Combine all parts
    $code = $letterPart . $digitPart . $extraPart;
    
    // Shuffle to avoid fixed pattern (e.g. LLLDDDX)
    return str_shuffle($code);
}
// Check if form is submitted
if (isset($_POST['verify_email'])) {
    // Sanitize input
    $name = trim(mysqli_real_escape_string($conn, $_POST['name']));
    $email = trim(mysqli_real_escape_string($conn, $_POST['email']));
    $address = trim(mysqli_real_escape_string($conn, $_POST['address'])); // New address field

    // Validate input
    if (empty($name) || empty($email) || empty($address)) {
        $error = "All fields are required.";
    }
    elseif (!preg_match('/^[A-Za-z\s]+$/', $name)){
        $error = "Name should contain alphabets only";
    }
    elseif (strlen($name) < 4){
        $error = "Name should contain atleast 4 characters minimum";
    }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    }else{
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("SELECT * FROM customers WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $message = "Email already exists. Please login.";
        }else{
            $mail = new PHPMailer(true);
            $otp = generateSecureCode();
            try {
                $mail->isSMTP();                                            // Send using SMTP
                $mail->Host       = 'smtp.gmail.com';                       // Set the SMTP server
                $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
                $mail->Username   = 'gbvsaiprakash@gmail.com';                 // Gmail address
                $mail->Password   = 'tthf qqpt fcia ehvj';                    // Gmail App Password (not your login)
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Use STARTTLS encryption
                $mail->Port       = 587;                                    // TCP port for TLS
            
                $mail->setFrom('gbvsaiprakash@gmail.com', 'Zinnia Support');
                $mail->addAddress($email, 'User');         // Recipient
            
                $mail->isHTML(true);                                    // Set email format to HTML
                $mail->Subject = 'Password Reset OTP';
                $mail->Body    = 'Hello '.$name.', <br>OTP for Zennia account verification is : <br><p><b>'.$otp.'</b></p> <br> This otp is valid for 3 minutes only.';
                if ($mail->send()){
                    $_SESSION['otp'] = password_hash($otp, PASSWORD_DEFAULT);
                    $_SESSION['otp_expires_at'] = date('Y-m-d H:i:s', strtotime('+3 minutes'));
                    $_SESSION['email'] = $email;
                    $_SESSION['name'] = $name;
                    $_SESSION['address'] = $address;
                    $_SESSION["verify_email_success"] = true;
                    $verify_email_success = true;
                    $message = "We have send an otp for verification to your email address";
                }
                else{
                    $error = "Email address was not found";
                }
            } catch (Exception $e) {
                $error = "Mailer Error: {$mail->ErrorInfo}";
            }
        }
    }
}
elseif (isset($_POST['register'])) {
    $otp = trim(mysqli_real_escape_string($conn, $_POST['verify_otp']));
    $password = trim(mysqli_real_escape_string($conn, $_POST['password']));
    $confirm_password = trim(mysqli_real_escape_string($conn, $_POST['confirm_password']));
    if (empty($otp) || empty($password) || empty($confirm_password)) {
        $error = "All fields are required.";
    }
    elseif (validateString($password)!=""){
        $error = validateString($password);
    }elseif (validateString($confirm_password)!=""){
        $error = validateString($confirm_password);
    }elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    }elseif($_SESSION['otp_expires_at'] < date('Y-m-d H:i:s')){
        $error = "OTP is expired";
        $verify_email_success = false;
        $_SESSION['verify_email_success'] = false;
        exit;
    }
    elseif (!password_verify($otp, $_SESSION["otp"])){
        $error = "Invalid OTP. Try again!";
    }else{
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO customers (name, email, password, address) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $_SESSION['name'], $_SESSION['email'], $hashed_password, $_SESSION['address']);
        if ($stmt->execute()) {
            $message = "Account Registered successfully.";
            $registration_success = true;
            $verify_email_success = false;
            $_SESSION['verify_email_success'] = false;
        }else {
            $error = "Error in account creation.";
        }
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
            <?php if (!$verify_email_success):?>
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
                        <label for="address">Your Address</label>
                        <input name="address" type="text" maxlength="255" required placeholder="Enter your address">
                    </div>
    
                    <div class="input-group">
                        <input class="btn-primary" name="verify_email" type="submit" value="Verify Account">
                    </div>
                </form>
            <?php elseif ($_SESSION['verify_email_success'] === true): ?>
                <form action="register.php" method="post">
                    <div class="input-group">
                        <label for="verify_otp">Enter OTP</label>
                        <input name="verify_otp" type="text" maxlength="8" required placeholder="Enter your OTP">
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
                        <input class="btn-primary" name="register" type="submit" value="Register">
                    </div>
                </form>
            <?php endif; ?>
            <!-- End of register form -->

            <div class="link-container">
                <p>Already have an account? <a href="login.php">Login here</a>.</p>
            </div>
            <?php if (!empty($message) || !empty($error)): ?>
                <?php if (!empty($message)): ?>
                    <p style="color: green;text-align:center;"><?php echo htmlspecialchars($message); ?></p>
                    
                <?php endif; ?>
                <?php if (!empty($error)): ?>
                    <p style="color: red;text-align:center;"><?php echo htmlspecialchars($error); ?></p>
                <?php endif; ?>
            <?php endif; ?>
            <?php if ($registration_success): ?>
                <div class="success-message">Registration successful! You can <a href="login.php">Login</a> now.</div>
            <?php endif; ?>

        </div>
    </div>

</body>
</html>
