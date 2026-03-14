<?php
ob_start();
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

require 'connections/localhost.php';
$email_success =  $_SESSION['email_success'] ?? false;
$forgot_error = '';
$email='';
$reset_success = false;
// Check if form is submitted
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
if (isset($_POST['forgotpassword'])) {
    // Sanitize input
    $email = trim(mysqli_real_escape_string($conn, $_POST['email']));
    // Validate input
    if (empty($email)){
        $forgot_error = "Email is required.";
    }


    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $forgot_error = "Invalid email format.";
        //echo "Invalid email format.";
    }


    // Check if email already exists
    $stmt = $conn->prepare("SELECT * FROM customers WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
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
            $mail->Body    = 'Hello '.$row["name"].', <br>OTP for forgot password verification is : <br><p><b>'.$otp.'</b></p> <br> This otp is valid for 3 minutes only.';
            $mail->send();
            $hashed_otp = password_hash($otp, PASSWORD_DEFAULT);
            $otp_expires_at = date('Y-m-d H:i:s', strtotime('+3 minutes'));
            $updateQuery = "UPDATE customers SET otp = ?, otp_expires_at = ? WHERE email = ?";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param("sss",$hashed_otp, $otp_expires_at, $email);
            if ($stmt->execute()) {
                $_SESSION['email_success'] = true;
                $email_success = true;
            } else {
                $forgot_error = "Unable to send otp";
            }
        } catch (Exception $e) {
            $forgot_error = "Mailer Error: {$mail->ErrorInfo}";
        }
    }
    else{
        $forgot_error = 'Email does not exists';
    }
}
elseif (isset($_POST['reset_password'])) {
    $forgot_error = '';
    $email = trim(mysqli_real_escape_string($conn, $_POST['email']));
    $otp = trim(mysqli_real_escape_string($conn, $_POST['otp']));
    $password = trim(mysqli_real_escape_string($conn, $_POST['password']));
    $confirm_password = trim(mysqli_real_escape_string($conn, $_POST['confirm_password']));
    if (empty($email) || empty($otp) || empty($password) || empty($confirm_password)) {
        $forgot_error = "All fields are required.";
    }
    elseif (validateString($password)!=""){
        $forgot_error = validateString($password);
    }elseif (validateString($confirm_password)!=""){
        $forgot_error = validateString($confirm_password);
    }
    elseif ($password !== $confirm_password) {
        $forgot_error = "Passwords do not match.";
    } 
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $forgot_error = "Invalid email format.";
    } 
    else {
        try {
            $stmt = $conn->prepare("SELECT otp, otp_expires_at FROM customers WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                if (strtotime($row["otp_expires_at"]) <= time()) {
                    $forgot_error = "OTP EXPIRED.";
                    $email_success = false;
                    $_SESSION['email_success'] = false;
                    // echo "OTP expired";
                    // exit;
                }
                elseif (!password_verify($otp, $row["otp"])) { 
                    $forgot_error = "Invalid OTP";
                    //echo "INVALID OTP";
                    //exit;
                }  
                else {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    //$otp_expires_at = date('Y-m-d H:i:s', strtotime('+0 minutes'));
                    $update_stmt = $conn->prepare("UPDATE customers SET password = ? WHERE email = ?");
                    $update_stmt->bind_param("ss", $hashed_password, $email);
                    
                    if ($update_stmt->execute()) {
                        $reset_success = true;
                        $email_success = false;
                        $_SESSION['email_success'] = false;
                    } else {
                        $forgot_error = "Error updating password";
                        echo "Error updating password";
                        exit;
                    }
                }
            } else {
                $forgot_error = "Email does not exist.";
            }
        } catch (Exception $e) {
            $forgot_error = "Database error occurred";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password Page</title>

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
            /* cursor: pointer; */
            width: 100%;
            font-size: 16px;
        }

        .btn-primary:hover {
            background-color: #4cae4c;
        }

        .hidden {
            display: none;
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
        .success-button {
            cursor: default;
        }
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

    <div class="register-section">
        <div class="register-container">
            <h1>Forgot Password</h1>

            <!-- Start of forgot password form -->
            <form action="forgotpassword.php" method="post">

                <div class="input-group">
                    <label for="email">Your Email</label>
                    <input name="email" type="email" maxlength="100" required placeholder="Enter your email" value="<?php echo htmlspecialchars($email); ?>" <?php if ($email_success) echo 'readonly'; ?>>
                </div>
                <div class="input-group">
                    <input class="<?php echo ($email_success) ? 'hidden' : 'btn-primary'; ?>" name="forgotpassword" type="submit" value="Forgot Password" <?php echo ($email_success) ? 'disabled' : ''; ?>/>
                </div>

                <div class="input-group">
                    <label class="<?php if (!$email_success) echo 'hidden'; ?>" for="otp">Enter OTP</label>
                    <input class="<?php if (!$email_success) echo 'hidden'; ?>" name="otp" type="text" maxlength="8" <?php if ($email_success) echo 'required' ?> placeholder="Enter OTP">
                </div>
                <div class="input-group">
                    <label class="<?php if (!$email_success) echo 'hidden'; ?>" for="password">Password</label>
                    <input class="<?php if (!$email_success) echo 'hidden'; ?>" name="password" type="password" maxlength="30" <?php if ($email_success) echo 'required' ?> placeholder="Enter your password">
                </div>
    
                <div class="input-group">
                    <label class="<?php if (!$email_success) echo 'hidden'; ?>" for="confirm_password">Confirm Password</label>
                    <input class="<?php if (!$email_success) echo 'hidden'; ?>" name="confirm_password" type="password" maxlength="30" <?php if ($email_success) echo 'required' ?> placeholder="Confirm your password">
                </div>
            
                <div class="input-group">
                    <input class="<?php echo (!$email_success) ? 'hidden success-button' : 'btn-primary'; ?>" name="reset_password" type="submit" value="Reset Password"/>
                </div>
                
            </form>
            <!-- End of forgot password form -->

            <div class="link-container">
                <p>To Login <a href="login.php">Click here</a>.</p>
            </div>

            <?php
            // Display success message if registration was successful
            if ($forgot_error!='') {
                echo '<div class="error-message">' . $forgot_error . '</div>';
            } elseif ($reset_success) {
                echo '<div class="success-message">Password Changed Successfully.</div>';
                echo '<script>
                    window.addEventListener("beforeunload", function () {
                        navigator.sendBeacon("destroy_session.php");
                    });
                </script>';
                header("Location: login.php");
                exit();
            } elseif ($email_success && $forgot_error=='') {
                echo '<div class="success-message">You have received an OTP to enter while changing password.</div>';
            }
            elseif ($forgot_error==''){
                echo '<script>
                    window.addEventListener("beforeunload", function () {
                        navigator.sendBeacon("destroy_session.php");
                    });
                </script>';
            }
            ?>
            
        </div>
    </div>

</body>
</html>