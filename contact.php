<?php
ob_start();
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

require 'connections/localhost.php';

$email_success = false;  // Variable to track email success
$forgot_error = '';
$email='';
$name='';
$message='';
$formMessage='';
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit;
}
function send_support_message($name,$subject,$message,$email){
    $sender = 'gbvsaiprakash@gmail.com';
    $reciever = $send_message = "";
    if ($name!="Zinnia Support"){
        $reciever = 'gbvsaiprakash@gmail.com';
        $send_message = 'Hi Team,<br> You have recieved a message from zinnia contact us from. The details are: <br> Name : '.$name.'<br>Email Address : '.$email.'<br>Message : '.$message.'<br> Thanks & Regards, <br> Zinnia Support';
    }
    else{
        $reciever = $email;
        $send_message = $message;
    }
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();                                            // Send using SMTP
        $mail->Host       = 'smtp.gmail.com';                       // Set the SMTP server
        $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
        $mail->Username   = 'gbvsaiprakash@gmail.com';                 // Gmail address
        $mail->Password   = 'tthf qqpt fcia ehvj';                    // Gmail App Password (not your login)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Use STARTTLS encryption
        $mail->Port       = 587;                                    // TCP port for TLS
        $mail->setFrom($sender, $name);
        $mail->addAddress($reciever, 'User');         // Recipient
        
        $mail->isHTML(true);                                    // Set email format to HTML
        $mail->Subject = $subject;
        $mail->Body    = $send_message;
        $mail->send();
        return true;
        } catch (Exception $e) {
            $forgot_error = "Mailer Error: {$mail->ErrorInfo}";
            return false;
        }
}

if (isset($_POST['submit'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    if (empty($email) || empty($message) || empty($name)){
        $forgot_error = "All fields required.";
    }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $forgot_error = "Invalid email format.";
    }
    else{
        $first_mail = send_support_message($name,'You have recieved a message',$message,$email);
        $second_mail = send_support_message('Zinnia Support','We have recieved you message','Thank you for contacting us! We will get back to you shortly.',$email);
        if ($first_mail){
            $query = "INSERT INTO contactus (name, email, message) VALUES ('$name', '$email', '$message')";
            if (mysqli_query($conn, $query)) {
                $email_success = true;
                $formMessage = "<p style='color: green; text-align: center;'>Thank you for contacting us! We will get back to you shortly.</p>";
                $name = $email = $message = "";
            } else {
                $formMessage = "<p style='color: red; text-align: center;'>There was an error submitting your message. Please try again later.</p>";
            }
        }
        else{
            $formMessage = "<p style='color: red; text-align: center;'>There was an error submitting your message. Please try again later.</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Zinnia Magic</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0; padding: 0;
            background-color: #f4f4f4;
        }

        .go-back-btn {
            padding: 10px 20px;
            font-size: 16px;
            color: white;
            background-color: #28a745;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            position: absolute;
            top: 20px;
            left: 20px;
        }
        .go-back-btn:hover {
            background-color: #218838;
        }

        .contact-form {
            max-width: 600px;
            margin: 80px auto;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            box-sizing: border-box;
        }

        .contact-form h1 {
            text-align: center;
            font-size: 24px;
            color: #333;
            margin-bottom: 20px;
        }

        .contact-form label {
            display: block;
            font-size: 16px;
            color: #333;
            margin-bottom: 8px;
        }

        .contact-form input,
        .contact-form textarea {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            color: #333;
            margin-bottom: 20px;
        }

        .contact-form textarea {
            height: 150px;
            resize: vertical;
        }

        .contact-form button {
            width: 100%;
            padding: 14px;
            background-color: #333;
            color: white;
            font-size: 18px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .contact-form button:hover {
            background-color: #555;
        }

        .footer {
            text-align: center;
            padding: 20px;
            background-color: #333;
            color: white;
        }
    </style>
</head>
<body>

    <!-- Go Back Button -->
    <button class="go-back-btn" onclick="window.history.back();">Go Back</button>

    <!-- Contact Form Section -->
    <div class="contact-form">
        <h1>Contact Us</h1>
        
        <form action="contact.php" method="post">
            <label for="name">Your Name:</label>
            <input type="text" id="name" name="name" placeholder="Enter your name" value="<?php echo htmlspecialchars($name); ?>" required>

            <label for="email">Your Email:</label>
            <input type="email" id="email" name="email" placeholder="Enter your email" value="<?php echo htmlspecialchars($email); ?>" required>

            <label for="message">Your Message:</label>
            <textarea id="message" name="message" placeholder="Enter your message" required><?php echo htmlspecialchars($message); ?></textarea>

            <button type="submit" name="submit">Submit</button>
        </form>

        <!-- Message Display -->
        <div class="form-message">
            <?php echo $formMessage; ?>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>&copy; 2025 Zinnia Magic | All Rights Reserved</p>
    </div>

</body>
</html>
