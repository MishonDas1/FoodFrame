<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$user_name = $_SESSION['user_name'] ?? 'User';
$user_email = $_SESSION['user_email'] ?? '';
$user_id = $_SESSION['user_id'] ?? null;

// Get user's order statistics
require_once 'db.php';

$stats = [
    'total_orders' => 0,
    'pending_orders' => 0,
    'delivered_orders' => 0,
    'total_spent' => 0
];

$recent_orders = [];

if ($user_email) {
    // Get stats
    $stmt = $conn->prepare("SELECT 
        COUNT(*) as total_orders,
        SUM(CASE WHEN order_status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
        SUM(CASE WHEN order_status = 'delivered' THEN 1 ELSE 0 END) as delivered_orders,
        SUM(CASE WHEN order_status = 'delivered' THEN total_amount ELSE 0 END) as total_spent
        FROM orders WHERE user_email = ?");
    $stmt->bind_param("s", $user_email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $stats = $row;
    }
    $stmt->close();

    // Get recent orders
    $stmt = $conn->prepare("SELECT * FROM orders WHERE user_email = ? ORDER BY created_at DESC LIMIT 5");
    $stmt->bind_param("s", $user_email);
    $stmt->execute();
    $recent_orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - FoodFrame</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
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

        @keyframes countUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-10px); }
            60% { transform: translateY(-5px); }
        }

        @keyframes cartShake {
            0%, 100% { transform: rotate(0deg); }
            25% { transform: rotate(-10deg); }
            75% { transform: rotate(10deg); }
        }

        /* ===== Base Styles ===== */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
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
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, var(--accent) 0%, transparent 70%);
            top: -100px;
            right: -100px;
            animation: float 8s ease-in-out infinite;
        }

        body::after {
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, var(--white) 0%, transparent 70%);
            bottom: -50px;
            left: -50px;
            animation: float 10s ease-in-out infinite reverse;
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
            gap: 28px;
        }

        .nav-links a {
            color: var(--gray-700);
            text-decoration: none;
            font-weight: 600;
            font-size: 15px;
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

        .cart-nav-btn {
            position: relative;
            background: var(--gray-100);
            border: 2px solid transparent;
            padding: 10px 18px;
            border-radius: 50px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            color: var(--gray-700);
            transition: all 0.3s ease;
        }

        .cart-nav-btn:hover {
            background: var(--white);
            border-color: var(--primary);
            color: var(--primary);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .cart-nav-btn:hover .cart-icon {
            animation: cartShake 0.5s ease;
        }

        .cart-icon {
            font-size: 18px;
        }

        .cart-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: linear-gradient(135deg, #ff6b6b, #ee5a5a);
            color: white;
            font-size: 11px;
            font-weight: 700;
            min-width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 8px rgba(238, 90, 90, 0.4);
        }

        .logout-btn {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a5a 100%);
            color: white;
            padding: 12px 24px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 700;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 15px rgba(238, 90, 90, 0.3);
            border: none;
            cursor: pointer;
        }

        .logout-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(238, 90, 90, 0.4);
            color: white;
        }

        /* ===== Dashboard Container ===== */
        .dashboard-container {
            padding: 40px 32px;
            max-width: 1300px;
            margin: 0 auto;
        }

        /* ===== Welcome Section ===== */
        .welcome-section {
            background: var(--white);
            border-radius: var(--radius-xl);
            padding: 50px 40px;
            margin-bottom: 32px;
            box-shadow: var(--shadow-xl);
            text-align: center;
            animation: fadeInUp 0.6s ease;
            position: relative;
            overflow: hidden;
        }

        .welcome-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: var(--gradient);
        }

        .welcome-section h1 {
            font-size: 2.8rem;
            color: var(--dark);
            margin-bottom: 10px;
            font-weight: 800;
        }

        .welcome-section h1 span {
            background: var(--gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .welcome-section p {
            color: var(--gray-600);
            font-size: 1.15rem;
        }

        .user-avatar {
            width: 120px;
            height: 120px;
            background: var(--gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 28px;
            font-size: 3rem;
            color: white;
            font-weight: 700;
            box-shadow: 0 15px 40px rgba(102, 126, 234, 0.4);
            animation: float 4s ease-in-out infinite;
            position: relative;
        }

        .user-avatar::after {
            content: '';
            position: absolute;
            inset: -5px;
            border-radius: 50%;
            border: 3px solid rgba(102, 126, 234, 0.3);
            animation: pulse 2s ease-in-out infinite;
        }

        /* ===== Stats Grid ===== */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 24px;
            margin-bottom: 32px;
        }

        .stat-card {
            background: var(--white);
            border-radius: var(--radius-lg);
            padding: 30px;
            box-shadow: var(--shadow-lg);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            animation: fadeInUp 0.6s ease backwards;
            position: relative;
            overflow: hidden;
        }

        .stat-card:nth-child(1) { animation-delay: 0.1s; }
        .stat-card:nth-child(2) { animation-delay: 0.2s; }
        .stat-card:nth-child(3) { animation-delay: 0.3s; }
        .stat-card:nth-child(4) { animation-delay: 0.4s; }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: var(--gradient);
            transform: scaleX(0);
            transition: transform 0.4s ease;
        }

        .stat-card:hover::before {
            transform: scaleX(1);
        }

        .stat-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-xl);
        }

        .stat-card .icon {
            width: 64px;
            height: 64px;
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        .stat-card:hover .icon {
            transform: scale(1.1) rotate(5deg);
        }

        .stat-card.orders .icon {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            box-shadow: 0 8px 20px rgba(240, 147, 251, 0.3);
        }

        .stat-card.pending .icon {
            background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
            box-shadow: 0 8px 20px rgba(252, 182, 159, 0.3);
        }

        .stat-card.delivered .icon {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            box-shadow: 0 8px 20px rgba(67, 233, 123, 0.3);
        }

        .stat-card.spent .icon {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
        }

        .stat-card h3 {
            font-size: 2.5rem;
            color: var(--dark);
            margin-bottom: 6px;
            font-weight: 800;
        }

        .stat-card p {
            color: var(--gray-600);
            font-size: 0.95rem;
            font-weight: 500;
        }

        /* ===== Cart Section ===== */
        .cart-section {
            background: var(--white);
            border-radius: var(--radius-xl);
            padding: 32px;
            margin-bottom: 32px;
            box-shadow: var(--shadow-lg);
            animation: fadeInUp 0.6s ease 0.3s backwards;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 2px solid var(--gray-200);
        }

        .section-header h2 {
            font-size: 1.5rem;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .section-header h2 span {
            font-size: 28px;
        }

        .cart-actions {
            display: flex;
            gap: 12px;
        }

        .btn {
            padding: 12px 24px;
            border-radius: 50px;
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: none;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-primary {
            background: var(--gradient);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }

        .btn-outline {
            background: transparent;
            color: var(--gray-600);
            border: 2px solid var(--gray-300);
        }

        .btn-outline:hover {
            border-color: var(--danger);
            color: var(--danger);
            background: rgba(252, 129, 129, 0.1);
        }

        .btn-danger {
            background: linear-gradient(135deg, #ff6b6b, #ee5a5a);
            color: white;
            box-shadow: 0 4px 15px rgba(238, 90, 90, 0.3);
        }

        .btn-danger:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(238, 90, 90, 0.4);
        }

        .cart-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }

        .cart-item {
            background: var(--gray-100);
            border-radius: var(--radius-md);
            padding: 16px;
            display: flex;
            gap: 16px;
            transition: all 0.3s ease;
            animation: slideInRight 0.4s ease backwards;
            border: 2px solid transparent;
        }

        .cart-item:hover {
            background: var(--white);
            border-color: var(--primary);
            transform: translateX(5px);
            box-shadow: var(--shadow-md);
        }

        .cart-item-img {
            width: 80px;
            height: 80px;
            border-radius: var(--radius-sm);
            object-fit: cover;
            background: var(--gray-200);
        }

        .cart-item-info {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .cart-item-title {
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 4px;
            font-size: 1rem;
        }

        .cart-item-price {
            color: var(--primary);
            font-weight: 700;
            font-size: 1.1rem;
        }

        .cart-item-remove {
            align-self: center;
            background: rgba(252, 129, 129, 0.1);
            border: none;
            color: var(--danger);
            width: 36px;
            height: 36px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .cart-item-remove:hover {
            background: var(--danger);
            color: white;
            transform: rotate(90deg);
        }

        .cart-empty {
            text-align: center;
            padding: 60px 20px;
            color: var(--gray-500);
        }

        .cart-empty-icon {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        .cart-empty h3 {
            font-size: 1.3rem;
            color: var(--gray-700);
            margin-bottom: 8px;
        }

        .cart-total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 0;
            margin-top: 20px;
            border-top: 2px dashed var(--gray-300);
        }

        .cart-total-label {
            font-size: 1.2rem;
            color: var(--gray-600);
            font-weight: 600;
        }

        .cart-total-value {
            font-size: 2rem;
            font-weight: 800;
            background: var(--gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* ===== Quick Actions ===== */
        .quick-actions {
            background: var(--white);
            border-radius: var(--radius-xl);
            padding: 32px;
            box-shadow: var(--shadow-lg);
            animation: fadeInUp 0.6s ease 0.4s backwards;
            margin-bottom: 32px;
        }

        .quick-actions h2 {
            color: var(--dark);
            margin-bottom: 24px;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .actions-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
        }

        .action-btn {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 20px 24px;
            background: var(--gray-100);
            border-radius: var(--radius-md);
            text-decoration: none;
            color: var(--gray-700);
            font-weight: 600;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 2px solid transparent;
        }

        .action-btn:hover {
            background: var(--white);
            border-color: var(--primary);
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
            color: var(--primary);
        }

        .action-btn .action-icon {
            width: 48px;
            height: 48px;
            background: var(--gradient);
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 22px;
            transition: all 0.3s ease;
        }

        .action-btn:hover .action-icon {
            transform: scale(1.1) rotate(5deg);
        }

        /* ===== Recent Orders ===== */
        .orders-section {
            background: var(--white);
            border-radius: var(--radius-xl);
            padding: 32px;
            box-shadow: var(--shadow-lg);
            animation: fadeInUp 0.6s ease 0.5s backwards;
        }

        .order-card {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 24px;
            background: var(--gray-100);
            border-radius: var(--radius-md);
            margin-bottom: 12px;
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }

        .order-card:hover {
            background: var(--white);
            transform: translateX(10px);
            box-shadow: var(--shadow-md);
            border-left-color: var(--primary);
        }

        .order-card:last-child {
            margin-bottom: 0;
        }

        .order-info h4 {
            color: var(--primary);
            font-weight: 700;
            margin-bottom: 6px;
            font-size: 1rem;
        }

        .order-info .order-items {
            color: var(--gray-600);
            font-size: 0.9rem;
            margin-bottom: 4px;
        }

        .order-info .order-date {
            color: var(--gray-500);
            font-size: 0.85rem;
        }

        .order-meta {
            text-align: right;
        }

        .order-meta .order-amount {
            font-weight: 800;
            color: var(--dark);
            font-size: 1.2rem;
            margin-bottom: 8px;
        }

        .order-status {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: capitalize;
        }

        .status-pending { background: rgba(246, 173, 85, 0.15); color: #dd6b20; }
        .status-confirmed { background: rgba(66, 153, 225, 0.15); color: #3182ce; }
        .status-preparing { background: rgba(237, 100, 166, 0.15); color: #d53f8c; }
        .status-delivered { background: rgba(72, 187, 120, 0.15); color: #38a169; }
        .status-cancelled { background: rgba(252, 129, 129, 0.15); color: #e53e3e; }

        /* ===== Empty State ===== */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-state-icon {
            font-size: 72px;
            margin-bottom: 20px;
            animation: bounce 2s ease infinite;
        }

        .empty-state h3 {
            color: var(--gray-700);
            font-size: 1.4rem;
            margin-bottom: 10px;
        }

        .empty-state p {
            color: var(--gray-500);
            margin-bottom: 24px;
        }

        /* ===== Responsive Design ===== */
        @media (max-width: 1200px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .actions-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .navbar {
                padding: 12px 16px;
                flex-wrap: wrap;
                gap: 12px;
                justify-content: center;
            }
            
            .logo {
                font-size: 1.3rem;
            }
            
            .logo-icon {
                font-size: 24px;
                width: 40px;
                height: 40px;
            }

            .nav-links {
                gap: 8px;
                flex-wrap: wrap;
                justify-content: center;
                width: 100%;
            }
            
            .nav-links a {
                padding: 8px 14px;
                font-size: 13px;
            }
            
            .cart-nav-btn {
                padding: 10px 16px;
                font-size: 13px;
            }
            
            .logout-btn {
                padding: 10px 18px;
            }

            .dashboard-container {
                padding: 16px 12px;
            }

            .welcome-section {
                padding: 28px 18px;
                border-radius: 20px;
                margin-bottom: 24px;
            }
            
            .user-avatar {
                width: 70px;
                height: 70px;
                font-size: 1.8rem;
                margin-bottom: 16px;
            }

            .welcome-section h1 {
                font-size: 1.5rem;
            }
            
            .welcome-section p {
                font-size: 0.9rem;
            }

            .stats-grid {
                grid-template-columns: 1fr 1fr;
                gap: 12px;
            }

            .actions-grid {
                grid-template-columns: 1fr;
            }

            .stat-card {
                padding: 18px 14px;
                border-radius: 16px;
            }
            
            .stat-card .icon {
                font-size: 28px;
                margin-bottom: 12px;
            }
            
            .stat-card h3 {
                font-size: 1.4rem;
            }
            
            .stat-card p {
                font-size: 0.75rem;
            }
            
            .dashboard-section {
                margin-bottom: 24px;
            }

            .section-header {
                flex-direction: column;
                gap: 12px;
                text-align: center;
                margin-bottom: 16px;
            }
            
            .section-header h2 {
                font-size: 1.3rem;
            }

            .cart-grid {
                grid-template-columns: 1fr;
                gap: 14px;
            }
            
            .cart-item-card {
                padding: 16px;
                border-radius: 16px;
            }
            
            .cart-item-image {
                width: 70px;
                height: 70px;
            }
            
            .cart-item-details h3 {
                font-size: 0.95rem;
            }
            
            .cart-item-price {
                font-size: 1rem;
            }

            .order-card {
                flex-direction: column;
                gap: 14px;
                text-align: center;
                padding: 18px;
                border-radius: 16px;
            }

            .order-meta {
                text-align: center;
            }
            
            .order-card h3 {
                font-size: 1rem;
            }
            
            .order-amount {
                font-size: 1.1rem;
            }
            
            /* Cart Summary Mobile */
            .cart-summary-bar {
                flex-direction: column;
                gap: 14px;
                padding: 18px;
                border-radius: 16px;
            }
            
            .checkout-btn {
                width: 100%;
                padding: 14px;
                font-size: 14px;
            }
        }
        
        /* Small Mobile */
        @media (max-width: 480px) {
            .navbar {
                padding: 10px 12px;
            }
            
            .logo {
                font-size: 1.1rem;
            }
            
            .logo-icon {
                width: 35px;
                height: 35px;
                font-size: 20px;
            }
            
            .nav-links a {
                padding: 6px 10px;
                font-size: 12px;
            }
            
            .cart-nav-btn {
                padding: 8px 12px;
                font-size: 12px;
            }
            
            .welcome-section {
                padding: 22px 16px;
                border-radius: 16px;
            }
            
            .user-avatar {
                width: 60px;
                height: 60px;
                font-size: 1.5rem;
            }
            
            .welcome-section h1 {
                font-size: 1.3rem;
                line-height: 1.4;
            }
            
            .stats-grid {
                gap: 10px;
            }
            
            .stat-card {
                padding: 14px 12px;
            }
            
            .stat-card .icon {
                font-size: 24px;
            }
            
            .stat-card h3 {
                font-size: 1.2rem;
            }
            
            .section-header h2 {
                font-size: 1.2rem;
            }
            
            .cart-item-card {
                padding: 14px;
            }
            
            .cart-item-image {
                width: 60px;
                height: 60px;
            }
            
            .order-card {
                padding: 14px;
            }
            
            .empty-state {
                padding: 40px 20px;
            }
            
            .empty-state .icon {
                font-size: 48px;
            }
            
            .empty-state h3 {
                font-size: 1.1rem;
            }
        }
        
        /* Touch-friendly interactions */
        @media (hover: none) and (pointer: coarse) {
            .stat-card:active {
                transform: scale(0.98);
            }
            
            .cart-item-card:active {
                transform: scale(0.99);
            }
            
            .order-card:active {
                transform: scale(0.99);
            }
            
            .checkout-btn:active {
                transform: scale(0.97);
            }
            
            .nav-links a:active {
                background: rgba(102, 126, 234, 0.2);
            }
        }
        
        /* Safe area for notched devices */
        @supports (padding: max(0px)) {
            .navbar {
                padding-top: max(12px, env(safe-area-inset-top));
                padding-left: max(16px, env(safe-area-inset-left));
                padding-right: max(16px, env(safe-area-inset-right));
            }
            
            .dashboard-container {
                padding-bottom: max(20px, env(safe-area-inset-bottom));
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="page.html" class="logo">
            <div class="logo-icon">🍽️</div>
            FoodFrame
        </a>
        <div class="nav-links">
            <a href="page.html">Home</a>
            <a href="page.html#best-selling-products">Menu</a>
            <a href="contact.html">Contact</a>
            <button class="cart-nav-btn" onclick="scrollToCart()">
                <span class="cart-icon">🛒</span>
                My Cart
                <span class="cart-badge" id="nav-cart-count">0</span>
            </button>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </nav>

    <div class="dashboard-container">
        <!-- Welcome Section -->
        <div class="welcome-section">
            <div class="user-avatar">
                <?php echo strtoupper(substr($user_name, 0, 1)); ?>
            </div>
            <h1>Welcome back, <span><?php echo htmlspecialchars($user_name); ?></span>!</h1>
            <p>Here's what's happening with your FoodFrame account today.</p>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card orders">
                <div class="icon">📦</div>
                <h3><?php echo $stats['total_orders'] ?? 0; ?></h3>
                <p>Total Orders</p>
            </div>
            <div class="stat-card pending">
                <div class="icon">⏳</div>
                <h3><?php echo $stats['pending_orders'] ?? 0; ?></h3>
                <p>Pending Orders</p>
            </div>
            <div class="stat-card delivered">
                <div class="icon">✅</div>
                <h3><?php echo $stats['delivered_orders'] ?? 0; ?></h3>
                <p>Delivered Orders</p>
            </div>
            <div class="stat-card spent">
                <div class="icon">💰</div>
                <h3>৳<?php echo number_format($stats['total_spent'] ?? 0); ?></h3>
                <p>Total Spent</p>
            </div>
        </div>

        <!-- Cart Section -->
        <div class="cart-section" id="cart-section">
            <div class="section-header">
                <h2><span>🛒</span> My Shopping Cart</h2>
                <div class="cart-actions">
                    <a href="page.html#best-selling-products" class="btn btn-primary">
                        ➕ Add More Items
                    </a>
                    <button class="btn btn-outline" id="clear-cart-btn" onclick="clearDashboardCart()">
                        🗑️ Clear Cart
                    </button>
                </div>
            </div>
            <div id="dashboard-cart-items" class="cart-grid">
                <!-- Cart items will be loaded here -->
            </div>
            <div class="cart-total-row" id="cart-total-row" style="display: none;">
                <span class="cart-total-label">Total Amount:</span>
                <span class="cart-total-value" id="cart-total">৳0</span>
            </div>
            <div style="text-align: center; margin-top: 20px;" id="checkout-btn-container" style="display: none;">
                <a href="checkout.php" class="btn btn-primary" style="padding: 16px 40px; font-size: 16px;">
                    🚀 Proceed to Checkout
                </a>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <h2>⚡ Quick Actions</h2>
            <div class="actions-grid">
                <a href="page.html#best-selling-products" class="action-btn">
                    <div class="action-icon">🍔</div>
                    <span>Browse Menu</span>
                </a>
                <a href="#orders-section" class="action-btn">
                    <div class="action-icon">📋</div>
                    <span>Order History</span>
                </a>
                <a href="checkout.php" class="action-btn">
                    <div class="action-icon">💳</div>
                    <span>Checkout</span>
                </a>
                <a href="contact.html" class="action-btn">
                    <div class="action-icon">💬</div>
                    <span>Get Support</span>
                </a>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="orders-section" id="orders-section">
            <div class="section-header">
                <h2><span>📋</span> Recent Orders</h2>
            </div>
            <?php if (count($recent_orders) === 0): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">🛒</div>
                    <h3>No orders yet</h3>
                    <p>Start ordering delicious food from our menu!</p>
                    <a href="page.html#best-selling-products" class="btn btn-primary">Browse Menu</a>
                </div>
            <?php else: ?>
                <?php foreach ($recent_orders as $order): 
                    $order_items = json_decode($order['order_items'], true) ?? [];
                    $order_number = 'FF-' . date('Ymd', strtotime($order['created_at'])) . '-' . str_pad($order['id'], 4, '0', STR_PAD_LEFT);
                ?>
                <div class="order-card">
                    <div class="order-info">
                        <h4><?php echo $order_number; ?></h4>
                        <div class="order-items">
                            <?php 
                            $item_names = array_map(function($item) { return $item['title']; }, $order_items);
                            echo htmlspecialchars(implode(', ', array_slice($item_names, 0, 3)));
                            if (count($item_names) > 3) echo ' +' . (count($item_names) - 3) . ' more';
                            ?>
                        </div>
                        <div class="order-date">
                            <?php echo date('F j, Y • g:i A', strtotime($order['created_at'])); ?>
                        </div>
                    </div>
                    <div class="order-meta">
                        <div class="order-amount">৳<?php echo number_format($order['total_amount']); ?></div>
                        <span class="order-status status-<?php echo $order['order_status']; ?>">
                            <?php echo $order['order_status']; ?>
                        </span>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Cart functionality for dashboard
        var CART_KEY = 'foodframe_cart_v1';

        function getCart() {
            try {
                var stored = localStorage.getItem(CART_KEY);
                return stored ? JSON.parse(stored) : [];
            } catch (err) {
                return [];
            }
        }

        function saveCart(cart) {
            try {
                localStorage.setItem(CART_KEY, JSON.stringify(cart));
            } catch (err) {
                console.warn('Cart save failed:', err);
            }
        }

        function renderDashboardCart() {
            var cart = getCart();
            var container = document.getElementById('dashboard-cart-items');
            var totalRow = document.getElementById('cart-total-row');
            var checkoutBtn = document.getElementById('checkout-btn-container');
            var navCount = document.getElementById('nav-cart-count');
            var clearBtn = document.getElementById('clear-cart-btn');

            if (navCount) {
                navCount.textContent = cart.length;
            }

            if (cart.length === 0) {
                container.innerHTML = '<div class="cart-empty"><div class="cart-empty-icon">🛒</div><h3>Your cart is empty</h3><p>Add some delicious items from our menu!</p><a href="page.html#best-selling-products" class="btn btn-primary">Browse Menu</a></div>';
                if (totalRow) totalRow.style.display = 'none';
                if (checkoutBtn) checkoutBtn.style.display = 'none';
                if (clearBtn) clearBtn.style.display = 'none';
                return;
            }

            var html = '';
            var total = 0;
            cart.forEach(function(item, index) {
                html += '<div class="cart-item" style="animation-delay: ' + (index * 0.1) + 's;">';
                html += '<img class="cart-item-img" src="' + (item.image || 'https://via.placeholder.com/80') + '" alt="' + item.title + '">';
                html += '<div class="cart-item-info">';
                html += '<div class="cart-item-title">' + item.title + '</div>';
                html += '<div class="cart-item-price">' + item.priceText + '</div>';
                html += '</div>';
                html += '<button class="cart-item-remove" onclick="removeFromDashboardCart(' + index + ')" title="Remove item">×</button>';
                html += '</div>';
                total += item.price || 0;
            });

            container.innerHTML = html;
            
            var totalEl = document.getElementById('cart-total');
            if (totalEl) {
                totalEl.textContent = '৳' + total.toLocaleString();
            }

            if (totalRow) totalRow.style.display = 'flex';
            if (checkoutBtn) checkoutBtn.style.display = 'block';
            if (clearBtn) clearBtn.style.display = 'inline-flex';
        }

        function removeFromDashboardCart(index) {
            var cart = getCart();
            if (index >= 0 && index < cart.length) {
                var removed = cart.splice(index, 1)[0];
                saveCart(cart);
                renderDashboardCart();
                showToast(removed.title + ' removed from cart');
            }
        }

        function clearDashboardCart() {
            if (confirm('Are you sure you want to clear your cart?')) {
                saveCart([]);
                renderDashboardCart();
                showToast('Cart cleared successfully');
            }
        }

        function scrollToCart() {
            document.getElementById('cart-section').scrollIntoView({ behavior: 'smooth' });
        }

        function showToast(message) {
            var toast = document.createElement('div');
            toast.style.cssText = 'position: fixed; bottom: 30px; right: 30px; background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 16px 28px; border-radius: 12px; font-weight: 600; box-shadow: 0 10px 40px rgba(102, 126, 234, 0.4); z-index: 9999; animation: slideInRight 0.4s ease;';
            toast.textContent = message;
            document.body.appendChild(toast);
            setTimeout(function() {
                toast.style.animation = 'fadeInUp 0.4s ease reverse forwards';
                setTimeout(function() { toast.remove(); }, 400);
            }, 2500);
        }

        // Initialize on load
        document.addEventListener('DOMContentLoaded', function() {
            renderDashboardCart();
        });

        // Listen for storage changes (if user adds items in another tab)
        window.addEventListener('storage', function(e) {
            if (e.key === CART_KEY) {
                renderDashboardCart();
            }
        });
    </script>
</body>
</html>
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 24px;
        }

        .nav-links a {
            color: #4a5568;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s ease;
        }

        .nav-links a:hover {
            color: #667eea;
        }

        .logout-btn {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a5a 100%);
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            box-shadow: 0 4px 15px rgba(238, 90, 90, 0.3);
        }

        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(238, 90, 90, 0.4);
            color: white;
        }

        .dashboard-container {
            padding: 40px 32px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .welcome-section {
            background: white;
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 32px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .welcome-section h1 {
            font-size: 2.5rem;
            color: #1a202c;
            margin-bottom: 8px;
        }

        .welcome-section h1 span {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .welcome-section p {
            color: #718096;
            font-size: 1.1rem;
        }

        .user-avatar {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            font-size: 2.5rem;
            color: white;
            font-weight: 600;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 24px;
            margin-bottom: 32px;
        }

        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 28px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
        }

        .stat-card .icon {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 16px;
        }

        .stat-card.orders .icon {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        .stat-card.favorites .icon {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        .stat-card.reviews .icon {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        }

        .stat-card.rewards .icon {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        }

        .stat-card h3 {
            font-size: 2rem;
            color: #1a202c;
            margin-bottom: 4px;
        }

        .stat-card p {
            color: #718096;
            font-size: 0.95rem;
        }

        .quick-actions {
            background: white;
            border-radius: 20px;
            padding: 32px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }

        .quick-actions h2 {
            color: #1a202c;
            margin-bottom: 24px;
            font-size: 1.5rem;
        }

        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
        }

        .action-btn {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 18px 24px;
            background: #f7fafc;
            border-radius: 12px;
            text-decoration: none;
            color: #2d3748;
            font-weight: 500;
            transition: all 0.2s ease;
            border: 2px solid transparent;
        }

        .action-btn:hover {
            background: #edf2f7;
            border-color: #667eea;
            transform: translateX(5px);
        }

        .action-btn .action-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
        }

        .user-info {
            background: white;
            border-radius: 20px;
            padding: 32px;
            margin-top: 32px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }

        .user-info h2 {
            color: #1a202c;
            margin-bottom: 24px;
            font-size: 1.5rem;
        }

        .info-row {
            display: flex;
            padding: 16px 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            width: 150px;
            color: #718096;
            font-weight: 500;
        }

        .info-value {
            color: #2d3748;
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .navbar {
                padding: 12px 16px;
                flex-wrap: wrap;
                gap: 12px;
            }

            .nav-links {
                gap: 16px;
            }

            .dashboard-container {
                padding: 24px 16px;
            }

            .welcome-section h1 {
                font-size: 1.8rem;
            }

            .stat-card {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="https://foodframe.store/page" class="logo">
            <div class="logo-icon">🍽️</div>
            FoodFrame
        </a>
        <div class="nav-links">
            <a href="https://foodframe.store/page">Home</a>
            <a href="team.html">Team</a>
            <a href="contact.html">Contact</a>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </nav>

    <div class="dashboard-container">
        <div class="welcome-section">
            <div class="user-avatar">
                <?php echo strtoupper(substr($user_name, 0, 1)); ?>
            </div>
            <h1>Welcome back, <span><?php echo htmlspecialchars($user_name); ?></span>!</h1>
            <p>Here's what's happening with your account today.</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card orders">
                <div class="icon">📦</div>
                <h3><?php echo $stats['total_orders'] ?? 0; ?></h3>
                <p>Total Orders</p>
            </div>
            <div class="stat-card favorites">
                <div class="icon">⏳</div>
                <h3><?php echo $stats['pending_orders'] ?? 0; ?></h3>
                <p>Pending Orders</p>
            </div>
            <div class="stat-card reviews">
                <div class="icon">✅</div>
                <h3><?php echo $stats['delivered_orders'] ?? 0; ?></h3>
                <p>Delivered Orders</p>
            </div>
            <div class="stat-card rewards">
                <div class="icon">💰</div>
                <h3>৳<?php echo number_format($stats['total_spent'] ?? 0); ?></h3>
                <p>Total Spent</p>
            </div>
        </div>

        <div class="quick-actions">
            <h2>Quick Actions</h2>
            <div class="actions-grid">
                <a href="page.html" class="action-btn">
                    <div class="action-icon">🍔</div>
                    <span>Browse Menu</span>
                </a>
                <a href="#" class="action-btn">
                    <div class="action-icon">📋</div>
                    <span>Order History</span>
                </a>
                <a href="#" class="action-btn">
                    <div class="action-icon">⚙️</div>
                    <span>Account Settings</span>
                </a>
                <a href="contact.html" class="action-btn">
                    <div class="action-icon">💬</div>
                    <span>Get Support</span>
                </a>
            </div>
        </div>

        <div class="user-info">
            <h2>Account Information</h2>
            <div class="info-row">
                <span class="info-label">Full Name</span>
                <span class="info-value"><?php echo htmlspecialchars($user_name); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Email</span>
                <span class="info-value"><?php echo htmlspecialchars($user_email); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Member Since</span>
                <span class="info-value"><?php echo date('F Y'); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Status</span>
                <span class="info-value" style="color: #48bb78;">● Active</span>
            </div>
        </div>

        <!-- Recent Orders Section -->
        <div class="user-info" style="margin-top: 32px;">
            <h2>📋 Recent Orders</h2>
            <?php if (count($recent_orders) === 0): ?>
                <div style="text-align: center; padding: 40px 20px; color: #718096;">
                    <div style="font-size: 48px; margin-bottom: 16px;">🛒</div>
                    <p>No orders yet. Start ordering delicious food!</p>
                    <a href="page.html" style="display: inline-block; margin-top: 16px; padding: 12px 24px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-decoration: none; border-radius: 8px; font-weight: 600;">Browse Menu</a>
                </div>
            <?php else: ?>
                <div class="orders-list">
                    <?php foreach ($recent_orders as $order): 
                        $order_items = json_decode($order['order_items'], true) ?? [];
                        $order_number = 'FF-' . date('Ymd', strtotime($order['created_at'])) . '-' . str_pad($order['id'], 4, '0', STR_PAD_LEFT);
                        $status_colors = [
                            'pending' => '#f6ad55',
                            'confirmed' => '#4299e1',
                            'preparing' => '#ed64a6',
                            'delivered' => '#48bb78',
                            'cancelled' => '#fc8181'
                        ];
                        $status_color = $status_colors[$order['order_status']] ?? '#a0aec0';
                    ?>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 20px; background: #f7fafc; border-radius: 12px; margin-bottom: 12px;">
                        <div>
                            <div style="font-weight: 600; color: #667eea; margin-bottom: 4px;"><?php echo $order_number; ?></div>
                            <div style="font-size: 0.9rem; color: #718096;">
                                <?php 
                                $item_names = array_map(function($item) { return $item['title']; }, $order_items);
                                echo htmlspecialchars(implode(', ', array_slice($item_names, 0, 2)));
                                if (count($item_names) > 2) echo ' +' . (count($item_names) - 2) . ' more';
                                ?>
                            </div>
                            <div style="font-size: 0.85rem; color: #a0aec0; margin-top: 4px;">
                                <?php echo date('M j, Y • g:i A', strtotime($order['created_at'])); ?>
                            </div>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-weight: 700; color: #1a202c; margin-bottom: 4px;">৳<?php echo number_format($order['total_amount']); ?></div>
                            <span style="display: inline-block; padding: 4px 12px; background: <?php echo $status_color; ?>20; color: <?php echo $status_color; ?>; border-radius: 20px; font-size: 0.8rem; font-weight: 600; text-transform: capitalize;">
                                <?php echo $order['order_status']; ?>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
