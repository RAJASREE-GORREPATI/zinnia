<?php
// Includes header and database connection
ob_start();
session_start();
require 'connections/localhost.php';

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Zinnia Magic</title>

    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #fffdf8;
            color: #333;
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
            z-index: 10;
        }

        .go-back-btn:hover {
            background-color: #218838;
        }

        .container {
            display: flex;
            justify-content: center;
            align-items: stretch;
            flex-wrap: wrap;
            padding: 50px 60px;
            gap: 40px;
        }

        .about-text, .about-image {
            flex: 1;
            min-width: 350px;
            max-width: 600px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .about-text h1, .about-text h2 {
            color: #b22222;
            margin-top: 0;
        }

        .about-text p,
        .about-text ul {
            line-height: 1.6;
            text-align: justify;
        }

        .about-text ul {
            padding-left: 20px;
        }

        .about-image {
            align-items: center;
            justify-content: flex-start;
            margin-top: 50px; /* Adds space from the top */
        }

        .about-image img {
            max-width: 70%;
            height: auto;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        .footer {
            text-align: center;
            padding: 20px;
            background-color: #333;
            color: white;
            margin-top: 40px;
        }
    </style>
</head>
<body>

    <!-- Back Button with Arrow -->
    <button class="go-back-btn" onclick="window.history.back();">← Back</button>

    <div class="container">
        <div class="about-text"></br>
            <h1>Discover the Magic of Zinnia</h1>
            <p>
                Zinnias are dazzling annual flowers that bloom in a rainbow of colors—scarlet, magenta, gold, and even lime green.
                Native to Mexico, they were named after the German botanist Johann Zinn.
                These cheerful blooms represent <strong>thoughtfulness</strong>, <strong>lasting affection</strong>, and <strong>youthful energy</strong>.
            </p>
            <p>
                In many cultures, Zinnias symbolize <strong>friendship</strong> and are often given as tokens of remembrance and love.
                Their vivid colors and resilience in the sun make them a garden favorite, drawing bees, butterflies, and joy wherever they grow.
            </p></br>
            <h2>Why We Choose the Zinnia ?</h2>
            <p>
                Zinnias bloom with little care, thrive in warm sunshine, and continue to flower long after other blooms have faded.
                Their ability to thrive even in tough conditions makes them a metaphor for strength and positivity—
                values we believe in and celebrate through every product at Zinnia Magic.
            </p></br>
            <h2>Do You Know?</h2>
            <ul>
                <li>🌼 Zinnias were the first flower to bloom in space! NASA grew them aboard the International Space Station in 2016.</li>
                <li>🌸 They attract pollinators like butterflies, making them essential for a healthy garden ecosystem.</li>
                <li>🎨 Artists love Zinnias for their geometry and spectrum of shades—they're often used in patterns and textile designs.</li>
                <li>🌿 Zinnias symbolize healing, making them a thoughtful gift for someone recovering or going through change.</li>
            </ul>
            <p>
                By weaving Zinnia-inspired patterns, colors, and themes into our fashion, home decor, and lifestyle accessories,
                we aim to bring the flower’s magic to your daily life. Experience the joy and charm of Zinnias—because magic begins with meaning.
            </p>
        </div>

        <div class="about-image">
            <img src="images/zinnia_flower.png" alt="Beautiful Zinnia Flower">
        </div>
    </div>

    <div class="footer">
        <p>&copy; 2025 Zinnia Magic | All Rights Reserved</p>
    </div>

</body>
</html>
