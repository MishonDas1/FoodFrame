<?php
session_start();
require_once 'db.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validation
    $errors = [];

    if (empty($username)) {
        $errors[] = 'Username is required';
    }

    if (empty($password)) {
        $errors[] = 'Password is required';
    }

    // If no validation errors, check credentials
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id, full_name, email, password FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password'])) {
                // Login successful - set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['logged_in'] = true;

                // Redirect to dashboard
                header('Location: dashboard.php');
                exit;
            } else {
                $message = 'Invalid username or password';
                $messageType = 'error';
            }
        } else {
            $message = 'Invalid username or password';
            $messageType = 'error';
        }
        $stmt->close();
    } else {
        $message = implode('<br>', $errors);
        $messageType = 'error';
    }
}
?>
<!DOCTYPE html> 
<html lang="en"> 
<head> 
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Log-in</title> 
    <link rel="stylesheet" type="text/css" href="project1.css">
    <style>
        .message {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 16px;
            font-size: 0.9rem;
            text-align: center;
        }
        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head> 
<body> 
    <div class="login-container"> 
        <h1 class="login-title">Log-In</h1> 

        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
 
        <form class="login-form" action="login.php" method="POST"> 
            <label for="username" class="form-label">Username</label> 
            <input type="text" id="username" name="username" class="form-input" required
                   value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"> 
 
            <label for="password" class="form-label">Password</label> 
            <input type="password" id="password" name="password" class="form-input" required> 
 
            <button type="submit" class="login-button">Log-In</button><br>
            <div> 
                <a href="register.php">Click here to Register</a><br>
            </div> 
        </form> 
    </div>
</body> 
</html>
