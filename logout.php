<?php
session_start();

// Clear all session variables
$_SESSION = array();

// Destroy the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logged Out - FoodFrame</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px;
            -webkit-font-smoothing: antialiased;
        }

        .logout-container {
            background: white;
            border-radius: 24px;
            padding: 48px;
            max-width: 480px;
            width: 100%;
            text-align: center;
            box-shadow: 0 25px 80px rgba(0, 0, 0, 0.2);
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logout-icon {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 32px;
            font-size: 56px;
            box-shadow: 0 15px 40px rgba(72, 187, 120, 0.4);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
                box-shadow: 0 15px 40px rgba(72, 187, 120, 0.4);
            }
            50% {
                transform: scale(1.05);
                box-shadow: 0 20px 50px rgba(72, 187, 120, 0.5);
            }
        }

        .logout-container h1 {
            font-size: 2rem;
            color: #1a202c;
            margin-bottom: 12px;
        }

        .logout-container p {
            color: #718096;
            font-size: 1.1rem;
            line-height: 1.6;
            margin-bottom: 32px;
        }

        .buttons {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 16px 32px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            cursor: pointer;
            border: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(102, 126, 234, 0.5);
        }

        .btn-secondary {
            background: #f7fafc;
            color: #4a5568;
            border: 2px solid #e2e8f0;
        }

        .btn-secondary:hover {
            background: #edf2f7;
            border-color: #cbd5e0;
            transform: translateY(-2px);
        }

        .countdown {
            margin-top: 24px;
            padding: 16px;
            background: #f7fafc;
            border-radius: 12px;
            color: #718096;
            font-size: 0.9rem;
        }

        .countdown span {
            font-weight: 700;
            color: #667eea;
        }

        .wave {
            display: inline-block;
            animation: wave 1.5s infinite;
            transform-origin: 70% 70%;
        }

        @keyframes wave {
            0%, 100% { transform: rotate(0deg); }
            25% { transform: rotate(20deg); }
            75% { transform: rotate(-15deg); }
        }

        .decorative-circles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            overflow: hidden;
            z-index: -1;
        }

        .circle {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
        }

        .circle:nth-child(1) {
            width: 300px;
            height: 300px;
            top: -100px;
            left: -100px;
        }

        .circle:nth-child(2) {
            width: 200px;
            height: 200px;
            bottom: -50px;
            right: -50px;
        }

        .circle:nth-child(3) {
            width: 150px;
            height: 150px;
            top: 50%;
            right: 10%;
        }

        @media (max-width: 520px) {
            .logout-container {
                padding: 32px 24px;
            }

            .logout-icon {
                width: 100px;
                height: 100px;
                font-size: 44px;
            }

            .logout-container h1 {
                font-size: 1.6rem;
            }

            .logout-container p {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="decorative-circles">
        <div class="circle"></div>
        <div class="circle"></div>
        <div class="circle"></div>
    </div>

    <div class="logout-container">
        <div class="logout-icon">
            <span class="wave">👋</span>
        </div>
        <h1>See You Soon!</h1>
        <p>You have been successfully logged out. Thank you for using FoodFrame. We hope to see you again soon!</p>
        
        <div class="buttons">
            <a href="login.php" class="btn btn-primary">
                🔐 Login Again
            </a>
            <a href="page.html" class="btn btn-secondary">
                🏠 Go to Homepage
            </a>
        </div>

        <div class="countdown">
            Redirecting to login page in <span id="countdown">5</span> seconds...
        </div>
    </div>

    <script>
        let seconds = 5;
        const countdownEl = document.getElementById('countdown');
        
        const interval = setInterval(() => {
            seconds--;
            countdownEl.textContent = seconds;
            
            if (seconds <= 0) {
                clearInterval(interval);
                window.location.href = 'login.php';
            }
        }, 1000);
    </script>
</body>
</html>
