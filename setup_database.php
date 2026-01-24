<?php
/**
 * Database Setup Script for FoodFrame E-commerce
 * Run this once to create required tables
 */

require_once 'db.php';

// Create orders table
$sql_orders = "CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    user_name VARCHAR(100) NOT NULL,
    user_email VARCHAR(150) NOT NULL,
    user_phone VARCHAR(20) NOT NULL,
    delivery_address TEXT NOT NULL,
    order_items TEXT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50) DEFAULT 'cash',
    order_status ENUM('pending', 'confirmed', 'preparing', 'delivered', 'cancelled') DEFAULT 'pending',
    admin_notified TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

// Create admin_users table
$sql_admin = "CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(150) NOT NULL,
    role ENUM('admin', 'manager') DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

// Create products table (optional - for future product management)
$sql_products = "CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    image_url VARCHAR(255),
    category VARCHAR(50),
    is_available TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

// Create order_notifications table
$sql_notifications = "CREATE TABLE IF NOT EXISTS order_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    notification_type ENUM('email', 'sms') DEFAULT 'email',
    sent_to VARCHAR(150) NOT NULL,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('sent', 'failed') DEFAULT 'sent',
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
)";

// Execute queries
$tables = [
    'orders' => $sql_orders,
    'admin_users' => $sql_admin,
    'products' => $sql_products,
    'order_notifications' => $sql_notifications
];

$success = true;
$messages = [];

foreach ($tables as $name => $sql) {
    if ($conn->query($sql) === TRUE) {
        $messages[] = "✅ Table '$name' created successfully or already exists.";
    } else {
        $messages[] = "❌ Error creating table '$name': " . $conn->error;
        $success = false;
    }
}

// Create default admin user if not exists
$default_admin_password = password_hash('Mishon24', PASSWORD_DEFAULT);
$check_admin = $conn->query("SELECT id FROM admin_users WHERE username = 'admin'");

if ($check_admin->num_rows == 0) {
    $admin_email = 'info@api.foodframe.store';
    $stmt = $conn->prepare("INSERT INTO admin_users (username, password, email, role) VALUES (?, ?, ?, 'admin')");
    $stmt->bind_param("sss", $admin_username, $default_admin_password, $admin_email);
    $admin_username = 'admin';
    
    if ($stmt->execute()) {
        $messages[] = "✅ Default admin user created. Username: admin, Password: admin123";
    } else {
        $messages[] = "❌ Error creating admin user: " . $stmt->error;
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
    <title>Database Setup - FoodFrame</title>
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
            border-radius: 20px;
            padding: 40px;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
        }
        h1 {
            color: #1a202c;
            margin-bottom: 24px;
            text-align: center;
        }
        .message {
            padding: 12px 16px;
            margin-bottom: 12px;
            border-radius: 8px;
            background: #f7fafc;
            border-left: 4px solid #667eea;
        }
        .success { border-left-color: #48bb78; background: #f0fff4; }
        .error { border-left-color: #f56565; background: #fff5f5; }
        .note {
            margin-top: 24px;
            padding: 16px;
            background: #fef3c7;
            border-radius: 8px;
            color: #92400e;
        }
        .btn {
            display: inline-block;
            margin-top: 24px;
            padding: 12px 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🍽️ FoodFrame Database Setup</h1>
        
        <?php foreach ($messages as $msg): ?>
            <div class="message <?php echo strpos($msg, '✅') !== false ? 'success' : 'error'; ?>">
                <?php echo $msg; ?>
            </div>
        <?php endforeach; ?>
        
        <div class="note">
            <strong>⚠️ Important:</strong>
            <ul style="margin-top: 8px; padding-left: 20px;">
                <li>Delete this file after setup for security</li>
                <li>Change the default admin password immediately</li>
                <li>Update admin email in this file before running</li>
            </ul>
        </div>
        
        <a href="admin_login.php" class="btn">Go to Admin Panel</a>
    </div>
</body>
</html>
