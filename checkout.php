<?php
session_start();

// Check if user is logged in
$is_logged_in = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
$user_name = $_SESSION['user_name'] ?? '';
$user_email = $_SESSION['user_email'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - FoodFrame</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* ===== CSS Variables ===== */
        :root {
            --primary: #667eea;
            --primary-dark: #5a67d8;
            --secondary: #764ba2;
            --accent: #f5c453;
            --success: #48bb78;
            --warning: #f6ad55;
            --danger: #fc8181;
            --dark: #1a202c;
            --gray-100: #f7fafc;
            --gray-200: #edf2f7;
            --gray-300: #e2e8f0;
            --gray-400: #cbd5e0;
            --gray-500: #a0aec0;
            --gray-600: #718096;
            --gray-700: #4a5568;
            --gray-800: #2d3748;
            --white: #ffffff;
            --gradient: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            --shadow-sm: 0 2px 4px rgba(0,0,0,0.05);
            --shadow-md: 0 4px 20px rgba(0,0,0,0.1);
            --shadow-lg: 0 10px 40px rgba(0,0,0,0.15);
            --shadow-xl: 0 20px 60px rgba(0,0,0,0.2);
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 20px;
            --radius-xl: 28px;
        }

        /* ===== Keyframe Animations ===== */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes slideInLeft {
            from { opacity: 0; transform: translateX(-30px); }
            to { opacity: 1; transform: translateX(0); }
        }

        @keyframes slideInRight {
            from { opacity: 0; transform: translateX(30px); }
            to { opacity: 1; transform: translateX(0); }
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        @keyframes shimmer {
            0% { background-position: -200% center; }
            100% { background-position: 200% center; }
        }

        @keyframes gradientFlow {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        @keyframes checkmark {
            0% { transform: scale(0) rotate(45deg); }
            50% { transform: scale(1.2) rotate(45deg); }
            100% { transform: scale(1) rotate(45deg); }
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-15px); }
            60% { transform: translateY(-7px); }
        }

        @keyframes modalSlide {
            from {
                opacity: 0;
                transform: scale(0.9) translateY(20px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        @keyframes confetti {
            0% { transform: translateY(0) rotate(0deg); opacity: 1; }
            100% { transform: translateY(100vh) rotate(720deg); opacity: 0; }
        }

        /* ===== Base Styles ===== */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            background-size: 400% 400%;
            animation: gradientFlow 15s ease infinite;
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
            position: relative;
            overflow-x: hidden;
        }

        /* Floating Background Elements */
        body::before,
        body::after {
            content: '';
            position: fixed;
            border-radius: 50%;
            opacity: 0.1;
            pointer-events: none;
        }

        body::before {
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, var(--accent) 0%, transparent 70%);
            top: -150px;
            right: -150px;
            animation: float 10s ease-in-out infinite;
        }

        body::after {
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, var(--white) 0%, transparent 70%);
            bottom: -100px;
            left: -100px;
            animation: float 12s ease-in-out infinite reverse;
        }

        /* ===== Navbar ===== */
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            padding: 16px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--shadow-lg);
            position: sticky;
            top: 0;
            z-index: 100;
            animation: fadeInDown 0.6s ease;
        }

        .logo {
            font-size: 24px;
            font-weight: 800;
            background: var(--gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: transform 0.3s ease;
        }

        .logo:hover {
            transform: scale(1.05);
        }

        .logo-icon {
            width: 42px;
            height: 42px;
            background: var(--gradient);
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 22px;
            box-shadow: var(--shadow-md);
            transition: all 0.3s ease;
        }

        .logo:hover .logo-icon {
            transform: rotate(10deg) scale(1.1);
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 24px;
        }

        .nav-links a {
            color: var(--gray-700);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            position: relative;
            padding: 8px 0;
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--gradient);
            transition: width 0.3s ease;
        }

        .nav-links a:hover::after {
            width: 100%;
        }

        .nav-links a:hover {
            color: var(--primary);
        }

        /* ===== Checkout Container ===== */
        .checkout-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
            display: grid;
            grid-template-columns: 1fr 420px;
            gap: 32px;
        }

        @media (max-width: 900px) {
            .checkout-container {
                grid-template-columns: 1fr;
            }
            
            .order-summary {
                position: relative;
                top: 0;
            }
        }
        
        /* Tablet Styles */
        @media (max-width: 768px) {
            .checkout-wrapper {
                padding: 20px 16px;
            }
            
            .checkout-nav {
                padding: 14px 16px;
            }
            
            .checkout-nav .logo {
                font-size: 1.3rem;
            }
            
            .checkout-form-section {
                padding: 32px 24px;
                border-radius: 20px;
            }
            
            .order-summary {
                padding: 28px 24px;
                border-radius: 20px;
            }
            
            h1 {
                font-size: 1.8rem;
            }
            
            h2 {
                font-size: 1.2rem;
            }
            
            .subtitle {
                font-size: 0.95rem;
            }
            
            .form-section {
                margin-bottom: 28px;
            }
            
            .form-section h3 {
                font-size: 1.05rem;
            }
            
            input, textarea, select {
                padding: 14px 16px;
                font-size: 16px; /* Prevents iOS zoom */
            }
            
            .payment-option {
                padding: 16px;
            }
        }
        
        /* Mobile Styles */
        @media (max-width: 600px) {
            body {
                font-size: 15px;
            }
            
            .checkout-wrapper {
                padding: 16px 12px;
            }
            
            .checkout-nav {
                padding: 12px 14px;
            }
            
            .checkout-nav .logo {
                font-size: 1.1rem;
                gap: 8px;
            }
            
            .checkout-nav .logo-icon {
                font-size: 24px;
            }
            
            .checkout-form-section {
                padding: 24px 18px;
                border-radius: 18px;
            }
            
            .checkout-form-section::before {
                height: 4px;
            }
            
            .order-summary {
                padding: 22px 18px;
                border-radius: 18px;
            }
            
            h1 {
                font-size: 1.5rem;
                margin-bottom: 6px;
            }
            
            h2 {
                font-size: 1.1rem;
                margin-bottom: 18px;
                gap: 8px;
            }
            
            h2 span {
                font-size: 20px;
            }
            
            .subtitle {
                font-size: 0.9rem;
                margin-bottom: 24px;
            }
            
            .form-section {
                margin-bottom: 24px;
            }
            
            .form-section h3 {
                font-size: 1rem;
                margin-bottom: 16px;
                padding-bottom: 10px;
            }
            
            .form-group {
                margin-bottom: 18px;
            }
            
            .form-row {
                grid-template-columns: 1fr;
                gap: 14px;
            }
            
            label {
                font-size: 0.9rem;
                margin-bottom: 6px;
            }
            
            input, textarea, select {
                padding: 14px 14px;
                border-radius: 12px;
                font-size: 16px;
            }
            
            textarea {
                min-height: 100px;
            }
            
            .payment-options {
                gap: 10px;
            }
            
            .payment-option {
                padding: 14px;
                gap: 12px;
                border-radius: 12px;
            }
            
            .payment-icon {
                font-size: 22px;
            }
            
            .payment-name {
                font-size: 0.95rem;
            }
            
            .payment-desc {
                font-size: 0.8rem;
            }
            
            /* Cart Items Mobile */
            .cart-items-list {
                max-height: 220px;
            }
            
            .cart-item {
                padding: 10px 0;
                gap: 10px;
            }
            
            .cart-item-img {
                width: 50px;
                height: 50px;
                border-radius: 8px;
            }
            
            .cart-item-title {
                font-size: 0.9rem;
            }
            
            .cart-item-price {
                font-size: 0.9rem;
            }
            
            .summary-row {
                padding: 10px 0;
                font-size: 0.95rem;
            }
            
            .summary-row.total {
                font-size: 1.1rem;
            }
            
            .checkout-btn {
                padding: 16px 20px;
                font-size: 1rem;
                border-radius: 14px;
            }
            
            .back-link {
                font-size: 0.9rem;
                margin-bottom: 18px;
            }
            
            /* Modal Mobile */
            .modal {
                padding: 32px 24px;
                border-radius: 20px;
                margin: 16px;
            }
            
            .modal-icon {
                width: 70px;
                height: 70px;
                font-size: 32px;
                margin-bottom: 20px;
            }
            
            .modal h2 {
                font-size: 1.3rem;
            }
            
            .modal p {
                font-size: 0.95rem;
            }
            
            .order-number {
                padding: 14px;
                font-size: 1rem;
            }
            
            .modal-btn {
                padding: 14px 28px;
                font-size: 0.95rem;
            }
            
            /* Login Prompt Mobile */
            .login-prompt {
                padding: 16px;
                border-radius: 12px;
            }
        }
        
        /* Very Small Screens */
        @media (max-width: 400px) {
            .checkout-wrapper {
                padding: 12px 8px;
            }
            
            .checkout-form-section,
            .order-summary {
                padding: 20px 14px;
                border-radius: 16px;
            }
            
            h1 {
                font-size: 1.3rem;
            }
            
            input, textarea, select {
                padding: 12px;
            }
            
            .payment-option {
                padding: 12px;
            }
            
            .checkout-btn {
                padding: 14px 16px;
            }
        }
        
        /* Touch-friendly interactions */
        @media (hover: none) and (pointer: coarse) {
            .payment-option:active {
                transform: scale(0.98);
                border-color: var(--primary);
            }
            
            .checkout-btn:active {
                transform: scale(0.98);
            }
            
            input:focus, textarea:focus, select:focus {
                transform: none;
            }
        }
        
        /* Safe area for notched devices */
        @supports (padding: max(0px)) {
            .checkout-wrapper {
                padding-left: max(16px, env(safe-area-inset-left));
                padding-right: max(16px, env(safe-area-inset-right));
                padding-bottom: max(20px, env(safe-area-inset-bottom));
            }
            
            .checkout-nav {
                padding-top: max(12px, env(safe-area-inset-top));
            }
        }

        /* ===== Form Section ===== */
        .checkout-form-section {
            background: var(--white);
            border-radius: var(--radius-xl);
            padding: 48px;
            box-shadow: var(--shadow-xl);
            animation: slideInLeft 0.6s ease;
            position: relative;
            overflow: hidden;
        }

        .checkout-form-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: var(--gradient);
        }

        /* ===== Order Summary ===== */
        .order-summary {
            background: var(--white);
            border-radius: var(--radius-xl);
            padding: 36px;
            box-shadow: var(--shadow-xl);
            height: fit-content;
            position: sticky;
            top: 100px;
            animation: slideInRight 0.6s ease;
        }

        .order-summary::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, var(--success), #38f9d7);
            border-radius: var(--radius-xl) var(--radius-xl) 0 0;
        }

        h1 {
            font-size: 2.2rem;
            color: var(--dark);
            margin-bottom: 8px;
            font-weight: 800;
        }

        h2 {
            font-size: 1.4rem;
            color: var(--dark);
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        h2 span {
            font-size: 24px;
        }

        .subtitle {
            color: var(--gray-600);
            margin-bottom: 32px;
            font-size: 1.05rem;
        }

        .form-section {
            margin-bottom: 36px;
        }

        .form-section h3 {
            font-size: 1.15rem;
            color: var(--gray-800);
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 2px solid var(--gray-200);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-section h3::before {
            content: '';
            width: 4px;
            height: 20px;
            background: var(--gradient);
            border-radius: 2px;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        @media (max-width: 600px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }

        label {
            display: block;
            font-weight: 600;
            color: var(--gray-700);
            margin-bottom: 8px;
            font-size: 0.95rem;
        }

        input, textarea, select {
            width: 100%;
            padding: 16px 18px;
            border: 2px solid var(--gray-300);
            border-radius: var(--radius-md);
            font-size: 1rem;
            font-family: inherit;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: var(--gray-100);
        }

        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: var(--primary);
            background: var(--white);
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.15);
            transform: translateY(-2px);
        }

        textarea {
            resize: vertical;
            min-height: 120px;
        }

        /* ===== Payment Options ===== */
        .payment-options {
            display: grid;
            gap: 14px;
        }

        .payment-option {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 20px;
            border: 2px solid var(--gray-300);
            border-radius: var(--radius-md);
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: var(--gray-100);
        }

        .payment-option:hover {
            border-color: var(--primary);
            background: var(--white);
            transform: translateX(5px);
            box-shadow: var(--shadow-md);
        }

        .payment-option.selected {
            border-color: var(--primary);
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.15);
        }

        .payment-option input[type="radio"] {
            width: auto;
            margin: 0;
        }

        .payment-icon {
            font-size: 24px;
        }

        .payment-info {
            flex: 1;
        }

        .payment-name {
            font-weight: 600;
            color: #2d3748;
        }

        .payment-desc {
            font-size: 0.85rem;
            color: #718096;
        }

        /* Order Summary Styles */
        .cart-items-list {
            max-height: 300px;
            overflow-y: auto;
            margin-bottom: 20px;
        }

        .cart-item {
            display: flex;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .cart-item-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 10px;
        }

        .cart-item-info {
            flex: 1;
        }

        .cart-item-title {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 4px;
        }

        .cart-item-price {
            color: #667eea;
            font-weight: 600;
        }

        .cart-item-remove {
            background: none;
            border: none;
            color: #e53e3e;
            cursor: pointer;
            font-size: 20px;
            padding: 4px 8px;
        }

        .empty-cart-message {
            text-align: center;
            padding: 40px 20px;
            color: #718096;
        }

        .empty-cart-message .icon {
            font-size: 48px;
            margin-bottom: 16px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .summary-row.total {
            border-bottom: none;
            padding-top: 16px;
            font-size: 1.2rem;
            font-weight: 700;
            color: #1a202c;
        }

        .summary-row.total .amount {
            color: #667eea;
        }

        .checkout-btn {
            width: 100%;
            padding: 16px 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 24px;
            transition: all 0.3s ease;
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }

        .checkout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 35px rgba(102, 126, 234, 0.5);
        }

        .checkout-btn:disabled {
            background: #a0aec0;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            margin-bottom: 24px;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        /* Success Modal */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            padding: 20px;
        }

        .modal-overlay.show {
            display: flex;
        }

        .modal {
            background: white;
            border-radius: 24px;
            padding: 48px;
            max-width: 500px;
            width: 100%;
            text-align: center;
            animation: modalSlide 0.3s ease;
        }

        @keyframes modalSlide {
            from {
                opacity: 0;
                transform: scale(0.9) translateY(20px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        .modal-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            font-size: 40px;
            color: white;
        }

        .modal h2 {
            color: #1a202c;
            margin-bottom: 12px;
        }

        .modal p {
            color: #718096;
            margin-bottom: 24px;
        }

        .order-number {
            background: #f7fafc;
            padding: 16px;
            border-radius: 12px;
            font-family: monospace;
            font-size: 1.2rem;
            color: #667eea;
            margin-bottom: 24px;
        }

        .modal-btn {
            padding: 14px 32px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }

        /* Loading Spinner */
        .loading-spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255,255,255,0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin-right: 8px;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .checkout-btn.loading .loading-spinner {
            display: inline-block;
        }

        .checkout-btn.loading .btn-text {
            opacity: 0.7;
        }

        /* Login Prompt */
        .login-prompt {
            background: #fef3c7;
            border: 1px solid #f6e05e;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .login-prompt a {
            color: #667eea;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="page.html" class="logo">
            <div class="logo-icon">🍽️</div>
            FoodFrame
        </a>
    </nav>

    <div class="checkout-container">
        <div class="checkout-form-section">
            <a href="page.html" class="back-link">← Back to Menu</a>
            <h1>Checkout</h1>
            <p class="subtitle">Complete your order and enjoy delicious food!</p>

            <?php if (!$is_logged_in): ?>
            <div class="login-prompt">
                <span>👋</span>
                <span>Already have an account? <a href="login.html">Log in</a> for faster checkout!</span>
            </div>
            <?php endif; ?>

            <form id="checkout-form">
                <div class="form-section">
                    <h3>👤 Contact Information</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Full Name *</label>
                            <input type="text" id="name" name="name" required 
                                   value="<?php echo htmlspecialchars($user_name); ?>"
                                   placeholder="Enter your full name">
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone Number *</label>
                            <input type="tel" id="phone" name="phone" required 
                                   placeholder="01XXXXXXXXX">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" required 
                               value="<?php echo htmlspecialchars($user_email); ?>"
                               placeholder="your@email.com">
                    </div>
                </div>

                <div class="form-section">
                    <h3>📍 Delivery Address</h3>
                    <div class="form-group">
                        <label for="address">Street Address *</label>
                        <input type="text" id="address" name="address" required 
                               placeholder="House/Flat No, Street Name">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="area">Area/Locality *</label>
                            <input type="text" id="area" name="area" required 
                                   placeholder="Area name">
                        </div>
                        <div class="form-group">
                            <label for="city">City *</label>
                            <input type="text" id="city" name="city" required 
                                   value="Dhaka" placeholder="City">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="notes">Delivery Notes (Optional)</label>
                        <textarea id="notes" name="notes" 
                                  placeholder="Any special instructions for delivery..."></textarea>
                    </div>
                </div>

                <div class="form-section">
                    <h3>💳 Payment Method</h3>
                    <div class="payment-options">
                        <label class="payment-option selected">
                            <input type="radio" name="payment" value="cash" checked>
                            <span class="payment-icon">💵</span>
                            <div class="payment-info">
                                <div class="payment-name">Cash on Delivery</div>
                                <div class="payment-desc">Pay when you receive your order</div>
                            </div>
                        </label>
                        <label class="payment-option">
                            <input type="radio" name="payment" value="bkash">
                            <span class="payment-icon">📱</span>
                            <div class="payment-info">
                                <div class="payment-name">bKash</div>
                                <div class="payment-desc">Pay with bKash mobile banking</div>
                            </div>
                        </label>
                        <label class="payment-option">
                            <input type="radio" name="payment" value="nagad">
                            <span class="payment-icon">📲</span>
                            <div class="payment-info">
                                <div class="payment-name">Nagad</div>
                                <div class="payment-desc">Pay with Nagad digital payment</div>
                            </div>
                        </label>
                        <label class="payment-option">
                            <input type="radio" name="payment" value="card">
                            <span class="payment-icon">💳</span>
                            <div class="payment-info">
                                <div class="payment-name">Credit/Debit Card</div>
                                <div class="payment-desc">Visa, Mastercard accepted</div>
                            </div>
                        </label>
                    </div>
                </div>

                <input type="hidden" id="cart-data" name="cart_data" value="">
            </form>
        </div>

        <div class="order-summary">
            <h2>🛒 Order Summary</h2>
            
            <div class="cart-items-list" id="checkout-cart-items">
                <div class="empty-cart-message">
                    <div class="icon">🛒</div>
                    <p>Your cart is empty</p>
                </div>
            </div>

            <div id="summary-details" style="display: none;">
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span id="subtotal">৳0</span>
                </div>
                <div class="summary-row">
                    <span>Delivery Fee</span>
                    <span id="delivery-fee">৳50</span>
                </div>
                <div class="summary-row total">
                    <span>Total</span>
                    <span class="amount" id="total-amount">৳0</span>
                </div>
            </div>

            <button type="submit" form="checkout-form" class="checkout-btn" id="place-order-btn" disabled>
                <span class="loading-spinner"></span>
                <span class="btn-text">Place Order</span>
            </button>
        </div>
    </div>

    <!-- Success Modal -->
    <div class="modal-overlay" id="success-modal">
        <div class="modal">
            <div class="modal-icon">✓</div>
            <h2>Order Placed Successfully!</h2>
            <p>Thank you for your order. We'll prepare your food and deliver it soon.</p>
            <div class="order-number" id="order-number">ORDER-XXXXXX</div>
            <a href="page.html" class="modal-btn">Continue Shopping</a>
        </div>
    </div>

    <script>
        (function() {
            const CART_KEY = 'foodframe_cart_v1';
            const DELIVERY_FEE = 50;

            function loadCart() {
                try {
                    const stored = localStorage.getItem(CART_KEY);
                    return stored ? JSON.parse(stored) : [];
                } catch (err) {
                    return [];
                }
            }

            function saveCart(cart) {
                try {
                    localStorage.setItem(CART_KEY, JSON.stringify(cart));
                } catch (err) {}
            }

            function clearCart() {
                localStorage.removeItem(CART_KEY);
            }

            function renderCart() {
                const cart = loadCart();
                const container = document.getElementById('checkout-cart-items');
                const summaryDetails = document.getElementById('summary-details');
                const placeOrderBtn = document.getElementById('place-order-btn');
                const cartDataInput = document.getElementById('cart-data');

                if (cart.length === 0) {
                    container.innerHTML = `
                        <div class="empty-cart-message">
                            <div class="icon">🛒</div>
                            <p>Your cart is empty</p>
                            <a href="page.html" style="color: #667eea;">Browse Menu</a>
                        </div>
                    `;
                    summaryDetails.style.display = 'none';
                    placeOrderBtn.disabled = true;
                    return;
                }

                let html = '';
                let subtotal = 0;

                cart.forEach((item, index) => {
                    subtotal += item.price || 0;
                    html += `
                        <div class="cart-item">
                            <img class="cart-item-img" src="${item.image || ''}" alt="${item.title}">
                            <div class="cart-item-info">
                                <div class="cart-item-title">${item.title}</div>
                                <div class="cart-item-price">${item.priceText}</div>
                            </div>
                            <button class="cart-item-remove" data-index="${index}" type="button">&times;</button>
                        </div>
                    `;
                });

                container.innerHTML = html;
                summaryDetails.style.display = 'block';
                placeOrderBtn.disabled = false;

                // Update totals
                document.getElementById('subtotal').textContent = '৳' + subtotal;
                document.getElementById('delivery-fee').textContent = '৳' + DELIVERY_FEE;
                document.getElementById('total-amount').textContent = '৳' + (subtotal + DELIVERY_FEE);

                // Set cart data for form submission
                cartDataInput.value = JSON.stringify(cart);

                // Bind remove buttons
                container.querySelectorAll('.cart-item-remove').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const idx = parseInt(this.getAttribute('data-index'), 10);
                        const cart = loadCart();
                        cart.splice(idx, 1);
                        saveCart(cart);
                        renderCart();
                    });
                });
            }

            // Payment option selection
            document.querySelectorAll('.payment-option').forEach(option => {
                option.addEventListener('click', function() {
                    document.querySelectorAll('.payment-option').forEach(o => o.classList.remove('selected'));
                    this.classList.add('selected');
                    this.querySelector('input[type="radio"]').checked = true;
                });
            });

            // Form submission
            document.getElementById('checkout-form').addEventListener('submit', async function(e) {
                e.preventDefault();

                const cart = loadCart();
                if (cart.length === 0) {
                    alert('Your cart is empty!');
                    return;
                }

                const btn = document.getElementById('place-order-btn');
                btn.classList.add('loading');
                btn.disabled = true;

                const formData = new FormData(this);
                formData.append('cart_data', JSON.stringify(cart));

                try {
                    const response = await fetch('process_order.php', {
                        method: 'POST',
                        body: formData
                    });

                    const result = await response.json();

                    if (result.success) {
                        // Clear cart
                        clearCart();

                        // Show success modal
                        document.getElementById('order-number').textContent = result.order_id;
                        document.getElementById('success-modal').classList.add('show');
                    } else {
                        alert(result.message || 'Failed to place order. Please try again.');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Something went wrong. Please try again.');
                } finally {
                    btn.classList.remove('loading');
                    btn.disabled = false;
                }
            });

            // Initialize
            renderCart();
        })();
    </script>
</body>
</html>
