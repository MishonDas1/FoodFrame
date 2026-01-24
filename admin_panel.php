<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit;
}

require_once 'db.php';

$admin_name = $_SESSION['admin_username'] ?? 'Admin';

// Handle order status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_status') {
        $order_id = intval($_POST['order_id']);
        $new_status = $_POST['status'];
        
        $allowed_statuses = ['pending', 'confirmed', 'preparing', 'delivered', 'cancelled'];
        if (in_array($new_status, $allowed_statuses)) {
            $stmt = $conn->prepare("UPDATE orders SET order_status = ? WHERE id = ?");
            $stmt->bind_param("si", $new_status, $order_id);
            $stmt->execute();
            $stmt->close();
        }
    }
}

// Get filter parameters
$status_filter = $_GET['status'] ?? 'all';
$search = $_GET['search'] ?? '';
$date_filter = $_GET['date'] ?? '';

// Build query
$where_conditions = [];
$params = [];
$types = '';

if ($status_filter !== 'all') {
    $where_conditions[] = "order_status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

if (!empty($search)) {
    $where_conditions[] = "(user_name LIKE ? OR user_email LIKE ? OR user_phone LIKE ? OR id LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
    $types .= 'ssss';
}

if (!empty($date_filter)) {
    $where_conditions[] = "DATE(created_at) = ?";
    $params[] = $date_filter;
    $types .= 's';
}

$where_clause = count($where_conditions) > 0 ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get orders
$sql = "SELECT * FROM orders $where_clause ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get statistics
$stats_query = "SELECT 
    COUNT(*) as total_orders,
    SUM(CASE WHEN order_status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
    SUM(CASE WHEN order_status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_orders,
    SUM(CASE WHEN order_status = 'preparing' THEN 1 ELSE 0 END) as preparing_orders,
    SUM(CASE WHEN order_status = 'delivered' THEN 1 ELSE 0 END) as delivered_orders,
    SUM(CASE WHEN order_status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_orders,
    SUM(CASE WHEN order_status = 'delivered' THEN total_amount ELSE 0 END) as total_revenue,
    SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as today_orders
FROM orders";
$stats = $conn->query($stats_query)->fetch_assoc();

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - FoodFrame</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: #f0f2f5;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            width: 260px;
            background: linear-gradient(180deg, #1a202c 0%, #2d3748 100%);
            padding: 24px 0;
            z-index: 100;
            overflow-y: auto;
        }

        .sidebar-logo {
            padding: 0 24px 24px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 24px;
        }

        .sidebar-logo a {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .sidebar-logo .icon {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
        }

        .sidebar-logo .text {
            color: white;
            font-size: 1.3rem;
            font-weight: 700;
        }

        .sidebar-nav {
            list-style: none;
        }

        .sidebar-nav li {
            margin-bottom: 4px;
        }

        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 24px;
            color: #a0aec0;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .sidebar-nav a:hover, .sidebar-nav a.active {
            background: rgba(102, 126, 234, 0.15);
            color: white;
            border-left: 3px solid #667eea;
        }

        .sidebar-nav .icon {
            font-size: 20px;
        }

        .sidebar-footer {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 20px 24px;
            border-top: 1px solid rgba(255,255,255,0.1);
        }

        .admin-profile {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
        }

        .admin-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }

        .admin-info {
            flex: 1;
        }

        .admin-info .name {
            color: white;
            font-weight: 600;
            font-size: 0.95rem;
        }

        .admin-info .role {
            color: #a0aec0;
            font-size: 0.8rem;
        }

        .logout-btn {
            display: block;
            width: 100%;
            padding: 12px;
            background: rgba(245, 101, 101, 0.2);
            color: #fc8181;
            border: none;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            transition: all 0.2s ease;
        }

        .logout-btn:hover {
            background: rgba(245, 101, 101, 0.3);
        }

        /* Main Content */
        .main-content {
            margin-left: 260px;
            padding: 32px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
        }

        .page-header h1 {
            font-size: 1.8rem;
            color: #1a202c;
        }

        .page-header p {
            color: #718096;
            margin-top: 4px;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 32px;
        }

        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s ease;
        }

        .stat-card:hover {
            transform: translateY(-3px);
        }

        .stat-card .icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 16px;
        }

        .stat-card.total .icon { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .stat-card.pending .icon { background: linear-gradient(135deg, #f6ad55 0%, #ed8936 100%); }
        .stat-card.delivered .icon { background: linear-gradient(135deg, #48bb78 0%, #38a169 100%); }
        .stat-card.revenue .icon { background: linear-gradient(135deg, #4fd1c5 0%, #38b2ac 100%); }
        .stat-card.today .icon { background: linear-gradient(135deg, #fc8181 0%, #f56565 100%); }

        .stat-card h3 {
            font-size: 1.8rem;
            color: #1a202c;
            margin-bottom: 4px;
        }

        .stat-card p {
            color: #718096;
            font-size: 0.9rem;
        }

        /* Filters */
        .filters-section {
            background: white;
            border-radius: 16px;
            padding: 20px 24px;
            margin-bottom: 24px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            align-items: center;
        }

        .filter-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .filter-group label {
            font-weight: 500;
            color: #4a5568;
            font-size: 0.9rem;
        }

        .filter-group select, .filter-group input {
            padding: 10px 14px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.9rem;
            font-family: inherit;
        }

        .filter-group select:focus, .filter-group input:focus {
            outline: none;
            border-color: #667eea;
        }

        .search-input {
            flex: 1;
            min-width: 200px;
        }

        .filter-btn {
            padding: 10px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .filter-btn:hover {
            transform: translateY(-2px);
        }

        .clear-btn {
            padding: 10px 20px;
            background: #e2e8f0;
            color: #4a5568;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
        }

        /* Orders Table */
        .orders-section {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .orders-header {
            padding: 20px 24px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .orders-header h2 {
            font-size: 1.2rem;
            color: #1a202c;
        }

        .orders-table {
            width: 100%;
            border-collapse: collapse;
        }

        .orders-table th, .orders-table td {
            padding: 16px 20px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        .orders-table th {
            background: #f8fafc;
            font-weight: 600;
            color: #4a5568;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .orders-table tr:hover {
            background: #f8fafc;
        }

        .order-id {
            font-weight: 600;
            color: #667eea;
        }

        .customer-info {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .customer-name {
            font-weight: 600;
            color: #2d3748;
        }

        .customer-contact {
            font-size: 0.85rem;
            color: #718096;
        }

        .order-items {
            max-width: 200px;
        }

        .order-items-list {
            font-size: 0.85rem;
            color: #4a5568;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: capitalize;
        }

        .status-pending { background: #fef3c7; color: #92400e; }
        .status-confirmed { background: #dbeafe; color: #1e40af; }
        .status-preparing { background: #fce7f3; color: #9d174d; }
        .status-delivered { background: #d1fae5; color: #065f46; }
        .status-cancelled { background: #fee2e2; color: #991b1b; }

        .amount {
            font-weight: 700;
            color: #1a202c;
        }

        .order-date {
            font-size: 0.85rem;
            color: #718096;
        }

        .action-btn {
            padding: 8px 12px;
            border: none;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .action-btn.view {
            background: #edf2f7;
            color: #4a5568;
        }

        .action-btn.view:hover {
            background: #e2e8f0;
        }

        /* Status Update Dropdown */
        .status-select {
            padding: 8px 12px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.85rem;
            cursor: pointer;
            background: white;
        }

        .status-select:focus {
            outline: none;
            border-color: #667eea;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #718096;
        }

        .empty-state .icon {
            font-size: 48px;
            margin-bottom: 16px;
        }

        /* Order Modal */
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
            border-radius: 20px;
            max-width: 600px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            animation: modalSlide 0.3s ease;
        }

        @keyframes modalSlide {
            from { opacity: 0; transform: scale(0.95) translateY(20px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }

        .modal-header {
            padding: 24px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h2 {
            color: #1a202c;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 28px;
            color: #a0aec0;
            cursor: pointer;
        }

        .modal-body {
            padding: 24px;
        }

        .detail-section {
            margin-bottom: 24px;
        }

        .detail-section h3 {
            font-size: 1rem;
            color: #667eea;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 2px solid #edf2f7;
        }

        .detail-row {
            display: flex;
            padding: 8px 0;
        }

        .detail-label {
            width: 140px;
            color: #718096;
            font-weight: 500;
        }

        .detail-value {
            flex: 1;
            color: #2d3748;
        }

        .item-list {
            list-style: none;
        }

        .item-list li {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #edf2f7;
        }

        .item-list li:last-child {
            border-bottom: none;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .main-content {
                margin-left: 0;
            }
        }

        @media (max-width: 768px) {
            .orders-table {
                display: block;
                overflow-x: auto;
            }
            .filters-section {
                flex-direction: column;
            }
            .filter-group {
                width: 100%;
            }
            .search-input {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-logo">
            <a href="admin_panel.php">
                <div class="icon">🍽️</div>
                <span class="text">FoodFrame</span>
            </a>
        </div>

        <ul class="sidebar-nav">
            <li>
                <a href="admin_panel.php" class="active">
                    <span class="icon">📊</span>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="admin_panel.php?status=pending">
                    <span class="icon">⏳</span>
                    <span>Pending Orders</span>
                </a>
            </li>
            <li>
                <a href="admin_panel.php?status=confirmed">
                    <span class="icon">✅</span>
                    <span>Confirmed</span>
                </a>
            </li>
            <li>
                <a href="admin_panel.php?status=preparing">
                    <span class="icon">👨‍🍳</span>
                    <span>Preparing</span>
                </a>
            </li>
            <li>
                <a href="admin_panel.php?status=delivered">
                    <span class="icon">🚚</span>
                    <span>Delivered</span>
                </a>
            </li>
        </ul>

        <div class="sidebar-footer">
            <div class="admin-profile">
                <div class="admin-avatar"><?php echo strtoupper(substr($admin_name, 0, 1)); ?></div>
                <div class="admin-info">
                    <div class="name"><?php echo htmlspecialchars($admin_name); ?></div>
                    <div class="role"><?php echo ucfirst($_SESSION['admin_role'] ?? 'Admin'); ?></div>
                </div>
            </div>
            <a href="admin_logout.php" class="logout-btn">🚪 Logout</a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <div class="page-header">
            <div>
                <h1>📊 Dashboard</h1>
                <p>Welcome back, <?php echo htmlspecialchars($admin_name); ?>! Here's your overview.</p>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card total">
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
                <p>Delivered</p>
            </div>
            <div class="stat-card revenue">
                <div class="icon">💰</div>
                <h3>৳<?php echo number_format($stats['total_revenue'] ?? 0); ?></h3>
                <p>Total Revenue</p>
            </div>
            <div class="stat-card today">
                <div class="icon">📅</div>
                <h3><?php echo $stats['today_orders'] ?? 0; ?></h3>
                <p>Today's Orders</p>
            </div>
        </div>

        <!-- Filters -->
        <form class="filters-section" method="GET">
            <div class="filter-group">
                <label>Status:</label>
                <select name="status">
                    <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Orders</option>
                    <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="confirmed" <?php echo $status_filter === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                    <option value="preparing" <?php echo $status_filter === 'preparing' ? 'selected' : ''; ?>>Preparing</option>
                    <option value="delivered" <?php echo $status_filter === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                    <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                </select>
            </div>
            <div class="filter-group">
                <label>Date:</label>
                <input type="date" name="date" value="<?php echo htmlspecialchars($date_filter); ?>">
            </div>
            <div class="filter-group search-input">
                <input type="text" name="search" placeholder="Search by name, email, phone, or order ID..." 
                       value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <button type="submit" class="filter-btn">🔍 Search</button>
            <a href="admin_panel.php" class="clear-btn">Clear</a>
        </form>

        <!-- Orders Table -->
        <div class="orders-section">
            <div class="orders-header">
                <h2>📋 Orders (<?php echo count($orders); ?>)</h2>
            </div>

            <?php if (count($orders) === 0): ?>
                <div class="empty-state">
                    <div class="icon">📭</div>
                    <h3>No orders found</h3>
                    <p>Try adjusting your filters or check back later.</p>
                </div>
            <?php else: ?>
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th>Amount</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): 
                            $order_items = json_decode($order['order_items'], true) ?? [];
                            $order_number = 'FF-' . date('Ymd', strtotime($order['created_at'])) . '-' . str_pad($order['id'], 4, '0', STR_PAD_LEFT);
                        ?>
                        <tr>
                            <td class="order-id"><?php echo $order_number; ?></td>
                            <td>
                                <div class="customer-info">
                                    <span class="customer-name"><?php echo htmlspecialchars($order['user_name']); ?></span>
                                    <span class="customer-contact"><?php echo htmlspecialchars($order['user_phone']); ?></span>
                                </div>
                            </td>
                            <td class="order-items">
                                <div class="order-items-list">
                                    <?php 
                                    $item_names = array_map(function($item) { return $item['title']; }, $order_items);
                                    echo htmlspecialchars(implode(', ', array_slice($item_names, 0, 2)));
                                    if (count($item_names) > 2) echo ' +' . (count($item_names) - 2) . ' more';
                                    ?>
                                </div>
                            </td>
                            <td class="amount">৳<?php echo number_format($order['total_amount']); ?></td>
                            <td><?php echo ucfirst($order['payment_method']); ?></td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <select name="status" class="status-select" onchange="this.form.submit()">
                                        <option value="pending" <?php echo $order['order_status'] === 'pending' ? 'selected' : ''; ?>>⏳ Pending</option>
                                        <option value="confirmed" <?php echo $order['order_status'] === 'confirmed' ? 'selected' : ''; ?>>✅ Confirmed</option>
                                        <option value="preparing" <?php echo $order['order_status'] === 'preparing' ? 'selected' : ''; ?>>👨‍🍳 Preparing</option>
                                        <option value="delivered" <?php echo $order['order_status'] === 'delivered' ? 'selected' : ''; ?>>🚚 Delivered</option>
                                        <option value="cancelled" <?php echo $order['order_status'] === 'cancelled' ? 'selected' : ''; ?>>❌ Cancelled</option>
                                    </select>
                                </form>
                            </td>
                            <td class="order-date"><?php echo date('M j, g:i A', strtotime($order['created_at'])); ?></td>
                            <td>
                                <button class="action-btn view" onclick="showOrderDetails(<?php echo htmlspecialchars(json_encode($order)); ?>, '<?php echo $order_number; ?>')">
                                    👁️ View
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </main>

    <!-- Order Details Modal -->
    <div class="modal-overlay" id="order-modal">
        <div class="modal">
            <div class="modal-header">
                <h2 id="modal-order-id">Order Details</h2>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body" id="modal-body">
                <!-- Content will be inserted by JS -->
            </div>
        </div>
    </div>

    <script>
        function showOrderDetails(order, orderNumber) {
            const items = JSON.parse(order.order_items || '[]');
            let itemsHtml = '';
            items.forEach(item => {
                itemsHtml += `<li><span>${item.title}</span><span>${item.priceText}</span></li>`;
            });

            const statusLabels = {
                pending: '⏳ Pending',
                confirmed: '✅ Confirmed',
                preparing: '👨‍🍳 Preparing',
                delivered: '🚚 Delivered',
                cancelled: '❌ Cancelled'
            };

            const paymentLabels = {
                cash: '💵 Cash on Delivery',
                bkash: '📱 bKash',
                nagad: '📲 Nagad',
                card: '💳 Card'
            };

            document.getElementById('modal-order-id').textContent = orderNumber;
            document.getElementById('modal-body').innerHTML = `
                <div class="detail-section">
                    <h3>👤 Customer Information</h3>
                    <div class="detail-row">
                        <span class="detail-label">Name:</span>
                        <span class="detail-value">${order.user_name}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Email:</span>
                        <span class="detail-value"><a href="mailto:${order.user_email}">${order.user_email}</a></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Phone:</span>
                        <span class="detail-value"><a href="tel:${order.user_phone}">${order.user_phone}</a></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Address:</span>
                        <span class="detail-value">${order.delivery_address}</span>
                    </div>
                </div>
                
                <div class="detail-section">
                    <h3>📦 Order Items</h3>
                    <ul class="item-list">
                        ${itemsHtml}
                    </ul>
                    <div class="detail-row" style="margin-top: 12px; padding-top: 12px; border-top: 2px solid #edf2f7;">
                        <span class="detail-label"><strong>Total:</strong></span>
                        <span class="detail-value"><strong style="color: #667eea; font-size: 1.2rem;">৳${parseFloat(order.total_amount).toLocaleString()}</strong></span>
                    </div>
                </div>
                
                <div class="detail-section">
                    <h3>📋 Order Details</h3>
                    <div class="detail-row">
                        <span class="detail-label">Status:</span>
                        <span class="detail-value"><span class="status-badge status-${order.order_status}">${statusLabels[order.order_status] || order.order_status}</span></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Payment:</span>
                        <span class="detail-value">${paymentLabels[order.payment_method] || order.payment_method}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Order Date:</span>
                        <span class="detail-value">${new Date(order.created_at).toLocaleString()}</span>
                    </div>
                </div>
            `;

            document.getElementById('order-modal').classList.add('show');
        }

        function closeModal() {
            document.getElementById('order-modal').classList.remove('show');
        }

        // Close modal on overlay click
        document.getElementById('order-modal').addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });

        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeModal();
        });
    </script>
</body>
</html>
