<?php
// Admin Pending Payments API

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

try {
    $pdo = getDBConnection();
    
    // Get pending payments with related information
    $stmt = $pdo->prepare("
        SELECT 
            p.payment_id,
            p.amount,
            p.payment_date,
            ps.session_id,
            sl.slot_number,
            u.full_name as driver_name,
            v.plate_number,
            v.vehicle_type,
            ps.start_time,
            ps.end_time
        FROM payments p
        JOIN parking_sessions ps ON p.session_id = ps.session_id
        JOIN parking_slots sl ON ps.slot_id = sl.slot_id
        JOIN users u ON ps.user_id = u.user_id
        JOIN vehicles v ON ps.vehicle_id = v.vehicle_id
        WHERE p.payment_status = 'pending'
        ORDER BY p.payment_date ASC
    ");
    $stmt->execute();
    $payments = $stmt->fetchAll();
    
    // Process payments to add calculated fields
    foreach ($payments as &$payment) {
        // Calculate duration
        $startTime = new DateTime($payment['start_time']);
        $endTime = new DateTime($payment['end_time'] ?: 'now');
        $duration = $startTime->diff($endTime);
        $hours = $duration->h + ($duration->days * 24);
        $minutes = $duration->i;
        $payment['duration'] = $hours . 'h ' . $minutes . 'm';
        
        // Format time ago
        $payment['time_ago'] = getTimeAgo($payment['payment_date']);
        
        // Format requested time
        $payment['requested_time'] = date('g:i A', strtotime($payment['payment_date']));
    }
    
    echo json_encode([
        'success' => true,
        'payments' => $payments
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}

// Get time ago string
function getTimeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) {
        return 'Just now';
    } elseif ($time < 3600) {
        $minutes = floor($time / 60);
        return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
    } elseif ($time < 86400) {
        $hours = floor($time / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($time < 2592000) {
        $days = floor($time / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return date('M j, Y', strtotime($datetime));
    }
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
