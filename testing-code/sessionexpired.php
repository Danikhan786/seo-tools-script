<?php
session_start();

function getToolData()
{
    $file = "cookie.json";
    return file_exists($file) ? json_decode(file_get_contents($file), true) : [];
}

$toolData = getToolData();
$toolName = "ABUBAKKARAHMAD";

if (!isset($toolData[$toolName])) {
    exit("Tool data for " . htmlspecialchars($toolName) . " not found.");
}

$noAccessUrl = $toolData[$toolName]["secure"]["noaccessurl"];

if (!isset($_SESSION["user"])) {
    $redirectUrl = htmlspecialchars($noAccessUrl);
    $redirectDelay = 3;
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Session Expired</title>
        <style>
            body, html {
                height: 100%;
                margin: 0;
                display: flex;
                justify-content: center;
                align-items: center;
                font-family: 'Arial', sans-serif;
                background-color: #f8f9fa;
            }

            .container {
                text-align: center;
                background: #fff;
                padding: 30px;
                border-radius: 10px;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            }

            .container h1 {
                color: #dc3545;
                margin-bottom: 20px;
            }

            .container p {
                font-size: 18px;
                margin-bottom: 20px;
            }

            .container .countdown {
                font-size: 24px;
                font-weight: bold;
            }
        </style>
        <script>
            let countdownNumber = <?php echo $redirectDelay; ?>;

            function updateCountdown() {
                const countdownElement = document.getElementById('countdown');
                countdownElement.textContent = countdownNumber;
                if (countdownNumber > 0) {
                    countdownNumber--;
                    setTimeout(updateCountdown, 1000);
                } else {
                    window.location.href = '<?php echo $redirectUrl; ?>';
                }
            }

            window.onload = () => {
                updateCountdown();
            };
        </script>
    </head>
    <body>
        <div class="container">
            <h1>Session Expired</h1>
            <p>Your session has expired.</p>
            <p class="countdown">You will be redirected in <span id="countdown"><?php echo $redirectDelay; ?></span> seconds.</p>
        </div>
    </body>
    </html>
    <?php
    exit;
}
?>