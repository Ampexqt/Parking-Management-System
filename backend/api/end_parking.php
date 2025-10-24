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

$userId = (int)$_SESSION['user_id'];

try {
    $pdo = getDBConnection();

    // Locate active session for this user, lock the row and slot row
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("SELECT ps.session_id, ps.slot_id, ps.start_time, ps.status, sl.status AS slot_status, sl.slot_number
                            FROM parking_sessions ps
                            JOIN parking_slots sl ON ps.slot_id = sl.slot_id
                            WHERE ps.user_id = ? AND ps.status = 'active'
                            FOR UPDATE");
    $stmt->execute([$userId]);
    $session = $stmt->fetch();

    if (!$session) {
        $pdo->rollBack();
        echo json_encode(['success'=>false,'message'=>'No active parking session found']);
        exit;
    }

    $slotId = (int)$session['slot_id'];

    // Compute amount using server timestamps: ₱10 per started hour, min 1 hour
    $stmt = $pdo->prepare("SELECT UNIX_TIMESTAMP(?) AS start_ts, UNIX_TIMESTAMP(CURRENT_TIMESTAMP) AS now_ts");
    $stmt->execute([$session['start_time']]);
    $ts = $stmt->fetch();
    $startTs = (int)$ts['start_ts'];
    $nowTs = (int)$ts['now_ts'];
    $diffSec = max(0, $nowTs - $startTs);
    $hours = intdiv($diffSec, 3600);
    $minutes = intdiv($diffSec % 3600, 60);
    $seconds = $diffSec % 60;
    $totalHours = max(1, $hours + ($minutes > 0 ? 1 : 0));
    $amount = $totalHours * 10.00;

    // Finalize session: set end_time and status completed
    $stmt = $pdo->prepare("UPDATE parking_sessions SET end_time = NOW(), status = 'completed' WHERE session_id = ?");
    $stmt->execute([(int)$session['session_id']]);

    // Free the slot
    $stmt = $pdo->prepare("UPDATE parking_slots SET status = 'available', last_updated = NOW() WHERE slot_id = ?");
    $stmt->execute([$slotId]);

    // Create payment record as PENDING with reference number; admin will confirm later
    $ref = 'PMT-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid((string)$session['session_id'], true)), 0, 6));
    $stmt = $pdo->prepare("INSERT INTO payments (session_id, amount, payment_method, payment_status, payment_date, reference_number)
                           VALUES (?, ?, 'cash', 'pending', NOW(), ?)");
    $stmt->execute([(int)$session['session_id'], $amount, $ref]);
    $paymentId = (int)$pdo->lastInsertId();

    // Log action
    $stmt = $pdo->prepare("INSERT INTO logs (user_id, action, log_time) VALUES (?, ?, NOW())");
    $stmt->execute([$userId, 'Ended parking session #'.$session['session_id'].' (freed slot)']);

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Parking session ended',
        'data' => [
            'session_id' => (int)$session['session_id'],
            'slot_id' => $slotId,
            'slot_number' => $session['slot_number'],
            'amount' => $amount,
            'duration_hours' => $totalHours,
            'duration_seconds' => $diffSec,
            'duration_hms' => sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds),
            'payment_id' => $paymentId,
            'reference_number' => $ref,
            'payment_status' => 'pending'
        ]
    ]);
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
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
