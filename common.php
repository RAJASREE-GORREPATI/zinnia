<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page - Zinnia Magic</title>
    <style>
        /* Basic reset and styling */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        /* Container for the content */
        .container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: auto; /* Set width to auto to fit the content */
            max-width: 500px; /* Set max-width to limit expansion */
        }

        /* Heading styling */
        h2 {
            font-size: 28px;
            font-weight: bold;
            color: #333;
            margin-bottom: 20px;
        }

        /* Description under the heading */
        p {
            font-size: 16px;
            color: #555;
            margin-bottom: 30px;
        }

        /* Button styles */
        .button {
            padding: 12px 20px;
            font-size: 18px;
            color: white;
            background-color: #f39c12; /* Yellow color */
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 10px 0;
            transition: background-color 0.3s ease;
            width: auto; /* Make button size fit text */
        }

        .button:hover {
            background-color: #e67e22; /* Orange color on hover */
        }

        /* Style for button container */
        .button-container {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .button-container a {
            width: 100%;
            margin-bottom: 15px;
        }

        /* Responsive design for smaller screens */
        @media (max-width: 480px) {
            h2 {
                font-size: 24px;
            }

            .button {
                width: 100%;
                font-size: 16px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Welcome to Zinnia E-Commerce</h2>
    <p>Please select your login type:</p>

    <div class="button-container">
        <!-- Admin Login Button -->
        <a href="admin/login.php" class="button">Admin Login</a>

        <!-- User Login Button -->
        <a href="login.php" class="button">User Login</a>
    </div>
</div>

</body>
</html>
