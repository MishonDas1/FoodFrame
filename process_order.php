<?php
/**
 * Process Order and Send Email Notification to Admin
 */

session_start();
require_once 'db.php';
require_once 'email_helper.php';

header('Content-Type: application/json');

// Admin email for notifications
define('ADMIN_EMAIL', 'info@api.foodframe.store');

function sendResponse($success, $message, $data = []) {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $data));
    exit;
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Invalid request method');
}

// Validate required fields
$required_fields = ['name', 'phone', 'email', 'address', 'area', 'city', 'cart_data'];
foreach ($required_fields as $field) {
    if (empty($_POST[$field])) {
        sendResponse(false, "Missing required field: $field");
    }
}

// Get form data
$user_name = trim($_POST['name']);
$user_phone = trim($_POST['phone']);
$user_email = trim($_POST['email']);
$address = trim($_POST['address']);
$area = trim($_POST['area']);
$city = trim($_POST['city']);
$notes = isset($_POST['notes']) ? trim($_POST['notes']) : '';
$payment_method = isset($_POST['payment']) ? trim($_POST['payment']) : 'cash';
$cart_data = $_POST['cart_data'];

// Validate email
if (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
    sendResponse(false, 'Invalid email address');
}

// Validate phone
if (!preg_match('/^01[0-9]{9}$/', $user_phone)) {
    sendResponse(false, 'Invalid phone number format');
}

// Decode and validate cart
$cart = json_decode($cart_data, true);
if (!$cart || !is_array($cart) || count($cart) === 0) {
    sendResponse(false, 'Cart is empty');
}

// Calculate total
$subtotal = 0;
$order_items = [];
foreach ($cart as $item) {
    $subtotal += floatval($item['price'] ?? 0);
    $order_items[] = [
        'title' => $item['title'] ?? 'Unknown Item',
        'price' => $item['price'] ?? 0,
        'priceText' => $item['priceText'] ?? '৳0'
    ];
}

$delivery_fee = 50;
$total_amount = $subtotal + $delivery_fee;

// Prepare full address
$full_address = "$address, $area, $city";
if (!empty($notes)) {
    $full_address .= " (Note: $notes)";
}

// Get user ID if logged in
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Insert order into database
$order_items_json = json_encode($order_items);

$stmt = $conn->prepare("INSERT INTO orders (user_id, user_name, user_email, user_phone, delivery_address, order_items, total_amount, payment_method, order_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
$stmt->bind_param("isssssds", $user_id, $user_name, $user_email, $user_phone, $full_address, $order_items_json, $total_amount, $payment_method);

if (!$stmt->execute()) {
    sendResponse(false, 'Failed to create order. Please try again.');
}

$order_id = $stmt->insert_id;
$order_number = 'FF-' . date('Ymd') . '-' . str_pad($order_id, 4, '0', STR_PAD_LEFT);
$stmt->close();

// Send email notification to admin using PHPMailer
$email_sent = sendAdminOrderNotification($order_number, $user_name, $user_email, $user_phone, $full_address, $order_items, $total_amount, $payment_method);

// Log notification
if ($email_sent) {
    $notif_stmt = $conn->prepare("INSERT INTO order_notifications (order_id, notification_type, sent_to, status) VALUES (?, 'email', ?, 'sent')");
    $notif_stmt->bind_param("is", $order_id, ADMIN_EMAIL);
    $notif_stmt->execute();
    $notif_stmt->close();
    
    // Update order as notified
    $conn->query("UPDATE orders SET admin_notified = 1 WHERE id = $order_id");
}

// Send confirmation email to customer using PHPMailer
sendOrderConfirmationEmail($user_email, $user_name, $order_number, $order_items, $total_amount);

$conn->close();

sendResponse(true, 'Order placed successfully!', ['order_id' => $order_number]);
?>
