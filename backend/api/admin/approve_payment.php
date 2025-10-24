<?php
// Admin Approve Payment API

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

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['payment_id'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Payment ID is required'
    ]);
    exit;
}

try {
    $pdo = getDBConnection();
    $paymentId = $input['payment_id'];
    $adminId = $_SESSION['user_id'];
    
    // Start transaction
    $pdo->beginTransaction();
    
    // Get payment details
    $stmt = $pdo->prepare("
        SELECT p.*, ps.session_id, ps.user_id, ps.slot_id
        FROM payments p
        JOIN parking_sessions ps ON p.session_id = ps.session_id
        WHERE p.payment_id = ? AND p.payment_status = 'pending'
    ");
    $stmt->execute([$paymentId]);
    $payment = $stmt->fetch();
    
    if (!$payment) {
        throw new Exception('Payment not found or already processed');
    }
    
    // Update payment status to approved
    $stmt = $pdo->prepare("
        UPDATE payments 
        SET payment_status = 'approved', approved_by = ?, payment_date = NOW()
        WHERE payment_id = ?
    ");
    $stmt->execute([$adminId, $paymentId]);
    
    // Update parking session to completed
    $stmt = $pdo->prepare("
        UPDATE parking_sessions 
        SET status = 'completed', end_time = NOW()
        WHERE session_id = ?
    ");
    $stmt->execute([$payment['session_id']]);
    
    // Update parking slot to available
    $stmt = $pdo->prepare("
        UPDATE parking_slots 
        SET status = 'available', last_updated = NOW()
        WHERE slot_id = ?
    ");
    $stmt->execute([$payment['slot_id']]);
    
    // Log the approval
    $stmt = $pdo->prepare("
        INSERT INTO logs (user_id, action, log_time)
        VALUES (?, ?, NOW())
    ");
    $stmt->execute([$adminId, "Approved payment #{$paymentId} for amount $" . $payment['amount']]);
    
    // Commit transaction
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Payment approved successfully',
        'data' => [
            'payment_id' => $paymentId,
            'amount' => $payment['amount'],
            'approved_by' => $adminId
        ]
    ]);
    
} catch (Exception $e) {
    $pdo->rollBack();
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
