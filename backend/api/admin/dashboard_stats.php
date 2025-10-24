<?php
// Admin Dashboard Stats API

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
    
    // Get parking slots statistics
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_slots,
            SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available_slots,
            SUM(CASE WHEN status = 'occupied' THEN 1 ELSE 0 END) as occupied_slots,
            SUM(CASE WHEN status = 'maintenance' THEN 1 ELSE 0 END) as maintenance_slots
        FROM parking_slots
    ");
    $stmt->execute();
    $slotStats = $stmt->fetch();
    
    // Get today's parking sessions
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as today_sessions
        FROM parking_sessions
        WHERE DATE(start_time) = CURDATE()
    ");
    $stmt->execute();
    $todaySessions = $stmt->fetch()['today_sessions'];
    
    // Get today's revenue
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(amount), 0) as today_revenue
        FROM payments
        WHERE DATE(payment_date) = CURDATE() AND payment_status = 'approved'
    ");
    $stmt->execute();
    $todayRevenue = $stmt->fetch()['today_revenue'];
    
    // Get active drivers count
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as active_drivers
        FROM users
        WHERE role = 'driver' AND status = 'active'
    ");
    $stmt->execute();
    $activeDrivers = $stmt->fetch()['active_drivers'];
    
    echo json_encode([
        'success' => true,
        'stats' => [
            'total_slots' => (int)$slotStats['total_slots'],
            'available_slots' => (int)$slotStats['available_slots'],
            'occupied_slots' => (int)$slotStats['occupied_slots'],
            'maintenance_slots' => (int)$slotStats['maintenance_slots'],
            'today_sessions' => (int)$todaySessions,
            'today_revenue' => (float)$todayRevenue,
            'active_drivers' => (int)$activeDrivers
        ]
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
