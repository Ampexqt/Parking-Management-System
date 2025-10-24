<?php
header('Content-Type: application/json');
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'driver') {
    http_response_code(401);
    echo json_encode(['success'=>false,'message'=>'Unauthorized access']);
    exit;
}
require_once '../config/db.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success'=>false,'message'=>'Method not allowed']);
    exit;
}
$input = $_POST;
if (!isset($input['slot_id'])) {
    http_response_code(400);
    echo json_encode(['success'=>false,'message'=>'slot_id is required']);
    exit;
}
$slotId = (int)$input['slot_id'];
$userId = (int)$_SESSION['user_id'];
try {
    $pdo = getDBConnection();
    $pdo->beginTransaction();
    // Block if user has any pending payments
    $check = $pdo->prepare("SELECT p.payment_id
                             FROM payments p
                             JOIN parking_sessions ps ON p.session_id = ps.session_id
                             WHERE ps.user_id = ? AND p.payment_status = 'pending'
                             LIMIT 1");
    $check->execute([$userId]);
    if ($check->fetch()) {
        throw new Exception('You have a pending payment. Please settle it before starting a new session.');
    }

    $stmt = $pdo->prepare("SELECT status FROM parking_slots WHERE slot_id = ? FOR UPDATE");
    $stmt->execute([$slotId]);
    $slot = $stmt->fetch();
    if (!$slot) {
        throw new Exception('Slot not found');
    }
    if ($slot['status'] !== 'available') {
        throw new Exception('Slot is not available');
    }
    $stmt = $pdo->prepare("SELECT session_id FROM parking_sessions WHERE user_id = ? AND status = 'active' LIMIT 1 FOR UPDATE");
    $stmt->execute([$userId]);
    if ($stmt->fetch()) {
        throw new Exception('You already have an active parking session');
    }
    $stmt = $pdo->prepare("SELECT vehicle_id FROM vehicles WHERE user_id = ? AND is_active = TRUE ORDER BY date_registered DESC LIMIT 1");
    $stmt->execute([$userId]);
    $vehicle = $stmt->fetch();
    if (!$vehicle) {
        throw new Exception('No active vehicle found');
    }
    $vehicleId = (int)$vehicle['vehicle_id'];
    $stmt = $pdo->prepare("INSERT INTO parking_sessions (user_id, slot_id, vehicle_id, start_time, status) VALUES (?, ?, ?, NOW(), 'active')");
    $stmt->execute([$userId, $slotId, $vehicleId]);
    $sessionId = (int)$pdo->lastInsertId();
    $stmt = $pdo->prepare("UPDATE parking_slots SET status = 'occupied', last_updated = NOW() WHERE slot_id = ?");
    $stmt->execute([$slotId]);
    $stmt = $pdo->prepare("INSERT INTO logs (user_id, action, log_time) VALUES (?, ?, NOW())");
    $stmt->execute([$userId, 'Started parking session #'.$sessionId]);
    $pdo->commit();
    echo json_encode(['success'=>true,'message'=>'Parking started','data'=>['session_id'=>$sessionId]]);
} catch (Exception $e) {
    if ($pdo && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(400);
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}
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
