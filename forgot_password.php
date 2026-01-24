<?php
session_start();
require_once 'db.php';
require_once 'email_helper.php';

$message = '';
$message_type = '';
$show_reset_form = false;
$token = '';

// Handle password reset request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    if ($_POST['action'] === 'request_reset') {
        $email = trim($_POST['email'] ?? '');
        
        if (empty($email)) {
            $message = 'Please enter your email address';
            $message_type = 'error';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = 'Please enter a valid email address';
            $message_type = 'error';
        } else {
            // Check if email exists
            $stmt = $conn->prepare("SELECT id, full_name FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                
                // Generate reset token
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Delete any existing tokens for this email
                $conn->query("DELETE FROM password_resets WHERE email = '$email'");
                
                // Create password_resets table if not exists
                $conn->query("CREATE TABLE IF NOT EXISTS password_resets (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    email VARCHAR(150) NOT NULL,
                    token VARCHAR(64) NOT NULL,
                    expires_at DATETIME NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )");
                
                // Store reset token
                $stmt2 = $conn->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
                $stmt2->bind_param("sss", $email, $token, $expires);
                $stmt2->execute();
                $stmt2->close();
                
                // Send reset email
                $reset_link = "https://api.foodframe.store/forgot_password.php?token=" . $token;
                $user_name = $user['full_name'];
                
                sendPasswordResetEmail($email, $user_name, $reset_link, false);
                
                $message = 'Password reset link has been sent to your email address.';
                $message_type = 'success';
            } else {
                // Don't reveal if email exists or not for security
                $message = 'If this email is registered, you will receive a password reset link.';
                $message_type = 'success';
            }
            $stmt->close();
        }
    }
    
    if ($_POST['action'] === 'reset_password') {
        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        if (empty($password) || empty($confirm_password)) {
            $message = 'Please fill in all fields';
            $message_type = 'error';
            $show_reset_form = true;
        } elseif (strlen($password) < 6) {
            $message = 'Password must be at least 6 characters';
            $message_type = 'error';
            $show_reset_form = true;
        } elseif ($password !== $confirm_password) {
            $message = 'Passwords do not match';
            $message_type = 'error';
            $show_reset_form = true;
        } else {
            // Verify token
            $stmt = $conn->prepare("SELECT email FROM password_resets WHERE token = ? AND expires_at > NOW()");
            $stmt->bind_param("s", $token);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $reset = $result->fetch_assoc();
                $email = $reset['email'];
                
                // Update password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt2 = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
                $stmt2->bind_param("ss", $hashed_password, $email);
                
                if ($stmt2->execute()) {
                    // Delete used token
                    $conn->query("DELETE FROM password_resets WHERE email = '$email'");
                    
                    $message = 'Password has been reset successfully! You can now login with your new password.';
                    $message_type = 'success';
                } else {
                    $message = 'Failed to reset password. Please try again.';
                    $message_type = 'error';
                    $show_reset_form = true;
                }
                $stmt2->close();
            } else {
                $message = 'Invalid or expired reset link. Please request a new one.';
                $message_type = 'error';
            }
            $stmt->close();
        }
    }
}

// Check if token provided in URL
if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    // Verify token is valid
    $stmt = $conn->prepare("SELECT email FROM password_resets WHERE token = ? AND expires_at > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $show_reset_form = true;
    } else {
        $message = 'Invalid or expired reset link. Please request a new one.';
        $message_type = 'error';
    }
    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - FoodFrame</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        
        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 24px;
            padding: 48px;
            max-width: 440px;
            width: 100%;
            box-shadow: 0 25px 80px rgba(0, 0, 0, 0.3);
        }
        
        .logo {
            text-align: center;
            margin-bottom: 32px;
        }
        
        .logo-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            margin-bottom: 16px;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
        }
        
        .logo h1 {
            font-size: 1.8rem;
            color: #1a202c;
        }
        
        .logo p {
            color: #718096;
            margin-top: 8px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            font-weight: 500;
            color: #4a5568;
            margin-bottom: 8px;
        }
        
        input {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 1rem;
            font-family: inherit;
            transition: all 0.2s ease;
        }
        
        input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }
        
        .message {
            padding: 14px 18px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 0.95rem;
        }
        
        .message.error {
            background: #fed7d7;
            color: #c53030;
            border: 1px solid #fc8181;
        }
        
        .message.success {
            background: #c6f6d5;
            color: #276749;
            border: 1px solid #68d391;
        }
        
        .btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 35px rgba(102, 126, 234, 0.5);
        }
        
        .links {
            text-align: center;
            margin-top: 24px;
        }
        
        .links a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }
        
        .links a:hover {
            text-decoration: underline;
        }
        
        .divider {
            display: flex;
            align-items: center;
            margin: 24px 0;
        }
        
        .divider::before, .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e2e8f0;
        }
        
        .divider span {
            padding: 0 16px;
            color: #a0aec0;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <div class="logo-icon">🔐</div>
            <h1>FoodFrame</h1>
            <?php if ($show_reset_form): ?>
                <p>Create a new password</p>
            <?php else: ?>
                <p>Reset your password</p>
            <?php endif; ?>
        </div>
        
        <?php if ($message): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($show_reset_form): ?>
            <!-- Reset Password Form -->
            <form method="POST" action="">
                <input type="hidden" name="action" value="reset_password">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                
                <div class="form-group">
                    <label for="password">New Password</label>
                    <input type="password" id="password" name="password" required 
                           placeholder="Enter new password" minlength="6">
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required 
                           placeholder="Confirm new password" minlength="6">
                </div>
                
                <button type="submit" class="btn">Reset Password</button>
            </form>
        <?php elseif ($message_type !== 'success'): ?>
            <!-- Request Reset Form -->
            <form method="POST" action="">
                <input type="hidden" name="action" value="request_reset">
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required 
                           placeholder="Enter your registered email">
                </div>
                
                <button type="submit" class="btn">Send Reset Link</button>
            </form>
        <?php endif; ?>
        
        <div class="links">
            <a href="login.html">← Back to Login</a>
        </div>
    </div>
</body>
</html>
