<?php
// Admin Payment Details API

// Set content type to JSON
header('Content-Type: application/json');

// Start session
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit;
}

// Include database configuration
require_once '../../config/db.php';

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Payment ID is required'
    ]);
    exit;
}

try {
    $pdo = getDBConnection();
    $paymentId = $_GET['id'];
    
    // Get payment details with related information
    $stmt = $pdo->prepare("
        SELECT 
            p.payment_id,
            p.amount,
            p.payment_method,
            p.payment_status,
            p.payment_date,
            ps.session_id,
            ps.start_time,
            ps.end_time,
            ps.status as session_status,
            sl.slot_number,
            u.user_id,
            u.full_name as driver_name,
            u.email as driver_email,
            v.vehicle_id,
            v.plate_number as vehicle_plate,
            v.vehicle_type,
            v.color as vehicle_color
        FROM payments p
        JOIN parking_sessions ps ON p.session_id = ps.session_id
        JOIN parking_slots sl ON ps.slot_id = sl.slot_id
        JOIN users u ON ps.user_id = u.user_id
        JOIN vehicles v ON ps.vehicle_id = v.vehicle_id
        WHERE p.payment_id = ?
    ");
    $stmt->execute([$paymentId]);
    $payment = $stmt->fetch();
    
    if (!$payment) {
        throw new Exception('Payment not found');
    }
    
    // Calculate duration
    $startTime = new DateTime($payment['start_time']);
    $endTime = new DateTime($payment['end_time'] ?: 'now');
    $duration = $startTime->diff($endTime);
    $hours = $duration->h + ($duration->days * 24);
    $minutes = $duration->i;
    $payment['duration'] = $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ' . $minutes . ' minute' . ($minutes > 1 ? 's' : '');
    
    // Format times
    $payment['start_time_formatted'] = date('g:i A', strtotime($payment['start_time']));
    $payment['end_time_formatted'] = $payment['end_time'] ? date('g:i A', strtotime($payment['end_time'])) : 'Still active';
    $payment['requested_time'] = date('g:i A', strtotime($payment['payment_date']));
    
    echo json_encode([
        'success' => true,
        'payment' => $payment
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}

// Get database connection
function getDBConnection() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
        return $pdo;
    } catch (PDOException $e) {
        throw new Exception('Database connection failed: ' . $e->getMessage());
    }
}
?>
