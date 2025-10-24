<?php
header('Content-Type: application/json');
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'driver') {
    http_response_code(401);
    echo json_encode(['success'=>false,'message'=>'Unauthorized access']);
    exit;
}
require_once '../config/db.php';
try {
    $pdo = getDBConnection();
    $userId = (int)$_SESSION['user_id'];
    // Find the most recent pending payment for this user's latest completed session
    $stmt = $pdo->prepare("SELECT p.payment_id, p.amount, p.payment_status, p.reference_number, p.payment_date,
                                  ps.session_id, ps.start_time, ps.end_time,
                                  sl.slot_number,
                                  TIMESTAMPDIFF(SECOND, ps.start_time, IFNULL(ps.end_time, CURRENT_TIMESTAMP)) AS duration_seconds
                           FROM payments p
                           JOIN parking_sessions ps ON p.session_id = ps.session_id
                           JOIN parking_slots sl ON ps.slot_id = sl.slot_id
                           WHERE ps.user_id = ? AND p.payment_status = 'pending'
                           ORDER BY p.payment_date DESC
                           LIMIT 1");
    $stmt->execute([$userId]);
    $row = $stmt->fetch();
    if (!$row) {
        echo json_encode(['success'=>true,'pending'=>false]);
        exit;
    }
    $seconds = (int)$row['duration_seconds'];
    $h = intdiv($seconds, 3600); $m = intdiv($seconds % 3600, 60); $s = $seconds % 60;
    echo json_encode([
        'success' => true,
        'pending' => true,
        'data' => [
            'reference_number' => $row['reference_number'],
            'slot_number' => $row['slot_number'],
            'amount' => (float)$row['amount'],
            'payment_status' => $row['payment_status'],
            'duration_seconds' => $seconds,
            'duration_hms' => sprintf('%02d:%02d:%02d', $h, $m, $s),
            'session_id' => (int)$row['session_id']
        ]
    ]);
} catch (Exception $e) {
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
