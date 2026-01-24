<?php
require_once 'db.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email_address'] ?? '');
    $password = $_POST['user_password'] ?? '';
    $dob = $_POST['date_of_birth'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $accept_terms = isset($_POST['accept_terms']);

    // Validation
    $errors = [];

    if (empty($full_name)) {
        $errors[] = 'Full name is required';
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid email address is required';
    }

    if (empty($password) || strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters';
    }

    if (empty($gender)) {
        $errors[] = 'Please select your gender';
    }

    if (!$accept_terms) {
        $errors[] = 'You must accept the terms';
    }

    // Check if email already exists
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $errors[] = 'Email address already registered';
        }
        $stmt->close();
    }

    // If no errors, insert user
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, date_of_birth, gender, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("sssss", $full_name, $email, $hashed_password, $dob, $gender);
        
        if ($stmt->execute()) {
            $message = 'Registration successful! You can now login.';
            $messageType = 'success';
        } else {
            $message = 'Registration failed. Please try again.';
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
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>User Registration</title>
    <link rel="stylesheet" href="Registyle.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* ===== MESSAGE ANIMATIONS ===== */
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.02); }
        }
        
        @keyframes shimmer {
            0% { background-position: -200% center; }
            100% { background-position: 200% center; }
        }
        
        @keyframes checkmark {
            0% { transform: scale(0) rotate(-45deg); }
            50% { transform: scale(1.2) rotate(-45deg); }
            100% { transform: scale(1) rotate(-45deg); }
        }
        
        /* ===== MESSAGE STYLES ===== */
        .message {
            padding: 18px 24px;
            border-radius: 16px;
            margin-bottom: 24px;
            font-size: 0.95rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideDown 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            position: relative;
            overflow: hidden;
        }
        
        .message::before {
            font-size: 1.4rem;
            flex-shrink: 0;
        }
        
        .message::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(
                90deg,
                transparent,
                rgba(255, 255, 255, 0.3),
                transparent
            );
            animation: shimmer 2s ease-in-out infinite;
        }
        
        .message.success {
            background: linear-gradient(135deg, #10b981, #059669);
            color: #fff;
            border: none;
            box-shadow: 
                0 10px 30px rgba(16, 185, 129, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 0.2);
        }
        
        .message.success::before {
            content: '✓';
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            animation: checkmark 0.5s ease-out 0.3s both;
        }
        
        .message.error {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: #fff;
            border: none;
            box-shadow: 
                0 10px 30px rgba(239, 68, 68, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 0.2);
        }
        
        .message.error::before {
            content: '✕';
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            font-weight: bold;
        }
        
        /* ===== LOGIN LINK ===== */
        .login-link {
            text-align: center;
            margin-top: 28px;
            padding-top: 24px;
            border-top: 2px solid rgba(106, 160, 255, 0.1);
            color: #6b7b8a;
            font-size: 0.95rem;
            animation: slideDown 0.6s cubic-bezier(0.16, 1, 0.3, 1) 0.3s both;
        }
        
        .login-link a {
            color: transparent;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            background-clip: text;
            text-decoration: none;
            font-weight: 600;
            position: relative;
            padding: 4px 8px;
            border-radius: 6px;
            transition: all 0.3s ease;
        }
        
        .login-link a::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            width: 0;
            height: 2px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            transition: all 0.3s ease;
            transform: translateX(-50%);
            border-radius: 2px;
        }
        
        .login-link a:hover {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
            -webkit-background-clip: unset;
            background-clip: unset;
            color: #667eea;
        }
        
        .login-link a:hover::before {
            width: 100%;
        }
        
        /* ===== TERMS LINK STYLING ===== */
        .reg-form a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            position: relative;
            transition: all 0.3s ease;
        }
        
        .reg-form a::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            transition: width 0.3s ease;
        }
        
        .reg-form a:hover {
            color: #764ba2;
        }
        
        .reg-form a:hover::after {
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="reg-container">
        <div class="reg-card">
            <h2>New User Registration</h2>
            <p class="subtitle">Create your account — it only takes a minute.</p>

            <?php if ($message): ?>
                <div class="message <?php echo $messageType; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <form class="reg-form" action="register.php" method="POST" novalidate>
                <div>
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name" placeholder="Enter your name" required 
                           value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>" />
                </div>

                <div>
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email_address" placeholder="you@example.com" required 
                           value="<?php echo htmlspecialchars($_POST['email_address'] ?? ''); ?>" />
                </div>

                <div>
                    <label for="password">Password</label>
                    <input type="password" id="password" name="user_password" placeholder="Choose a secure password" required />
                </div>

                <div>
                    <label for="dob">Date of Birth</label>
                    <input type="date" id="dob" name="date_of_birth" 
                           value="<?php echo htmlspecialchars($_POST['date_of_birth'] ?? ''); ?>" />
                </div>

                <div class="full-row">
                    <fieldset>
                        <legend>Gender</legend>
                        <div class="radio-group">
                            <label class="inline">
                                <input type="radio" id="gender_male" name="gender" value="male" required 
                                       <?php echo (($_POST['gender'] ?? '') === 'male') ? 'checked' : ''; ?> />
                                <span>Male</span>
                            </label>

                            <label class="inline">
                                <input type="radio" id="gender_female" name="gender" value="female" 
                                       <?php echo (($_POST['gender'] ?? '') === 'female') ? 'checked' : ''; ?> />
                                <span>Female</span>
                            </label>

                            <label class="inline">
                                <input type="radio" id="gender_other" name="gender" value="other" 
                                       <?php echo (($_POST['gender'] ?? '') === 'other') ? 'checked' : ''; ?> />
                                <span>Other</span>
                            </label>
                        </div>
                    </fieldset>
                </div>

                <div class="full-row">
                    <label class="inline" style="gap:8px">
                        <input type="checkbox" id="terms" name="accept_terms" required 
                               <?php echo isset($_POST['accept_terms']) ? 'checked' : ''; ?> />
                        <span>I agree to the <a href="https://www.facebook.com/share/1Bd6KgEkKs/" target="_blank" rel="noopener">OUR FACEBOOK PAGE</a></span>
                    </label>
                </div>

                <div class="form-actions">
                    <button type="reset" class="btn secondary" aria-label="Reset form">Reset</button>
                    <button type="submit" class="btn primary" aria-label="Register now">Register Now</button>
                </div>
            </form>

            <p class="login-link">Already have an account? <a href="login.html">Login here</a></p>
        </div>
    </div>
</body>
</html>
