<?php
// Request Exit API - Submit exit request for current parking session

// Set content type to JSON
header('Content-Type: application/json');

// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'driver') {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit;
}

// Include database configuration
require_once '../config/db.php';

try {
    $pdo = getDBConnection();
    $userId = $_SESSION['user_id'];
    
    // Check if user has an active parking session
    $stmt = $pdo->prepare("
        SELECT session_id, start_time, slot_id
        FROM parking_sessions
        WHERE user_id = ? AND status = 'active'
        ORDER BY start_time DESC
        LIMIT 1
    ");
    
    $stmt->execute([$userId]);
    $session = $stmt->fetch();
    
    if (!$session) {
        echo json_encode([
            'success' => false,
            'message' => 'No active parking session found'
        ]);
        exit;
    }
    
    // Calculate parking duration and amount using server timestamps to avoid timezone drift
    $stmt = $pdo->prepare("SELECT UNIX_TIMESTAMP(?) AS start_ts, UNIX_TIMESTAMP(CURRENT_TIMESTAMP) AS now_ts");
    $stmt->execute([$session['start_time']]);
    $ts = $stmt->fetch();
    $startTs = (int)$ts['start_ts'];
    $nowTs = (int)$ts['now_ts'];
    $diffSec = max(0, $nowTs - $startTs);
    $hours = intdiv($diffSec, 3600);
    $minutes = intdiv($diffSec % 3600, 60);

    // Pricing: ₱10 for the first started hour, then ₱10 per additional hour (i.e., ₱10 per started hour, min 1 hour)
    $totalHours = max(1, $hours + ($minutes > 0 ? 1 : 0));
    $amount = $totalHours * 10.00;
    
    // Start transaction
    $pdo->beginTransaction();
    
    try {
        // Create payment record
        $stmt = $pdo->prepare("
            INSERT INTO payments (session_id, amount, payment_method, payment_status, payment_date)
            VALUES (?, ?, 'cash', 'pending', NOW())
        ");
        $stmt->execute([$session['session_id'], $amount]);
        $paymentId = $pdo->lastInsertId();
        
        // Log the exit request
        $stmt = $pdo->prepare("
            INSERT INTO logs (user_id, action, log_time)
            VALUES (?, ?, NOW())
        ");
        $stmt->execute([$userId, "Exit request submitted for session {$session['session_id']}"]);
        
        // Commit transaction
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Exit request submitted successfully',
            'data' => [
                'session_id' => $session['session_id'],
                'payment_id' => $paymentId,
                'amount' => $amount,
                'duration' => $totalHours . ' hour' . ($totalHours > 1 ? 's' : ''),
                'status' => 'pending_approval'
            ]
        ]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
    
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
