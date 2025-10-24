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

    $stmt = $pdo->prepare("SELECT 
            ps.session_id,
            ps.start_time,
            ps.end_time,
            ps.status AS session_status,
            sl.slot_number,
            p.amount,
            p.payment_method,
            p.payment_status,
            p.reference_number,
            p.payment_date
        FROM parking_sessions ps
        JOIN parking_slots sl ON ps.slot_id = sl.slot_id
        LEFT JOIN payments p ON p.session_id = ps.session_id
        WHERE ps.user_id = ? AND ps.status IN ('completed','cancelled')
        ORDER BY COALESCE(ps.end_time, ps.start_time) DESC
        LIMIT 200");
    $stmt->execute([$userId]);
    $rows = $stmt->fetchAll();

    $history = [];
    foreach ($rows as $r) {
        $start = $r['start_time'] ? strtotime($r['start_time']) : null;
        $end = $r['end_time'] ? strtotime($r['end_time']) : null;
        $durSec = ($start && $end) ? max(0, $end - $start) : 0;
        $h = intdiv($durSec, 3600); $m = intdiv($durSec % 3600, 60);
        $duration_label = ($h>0? $h.' hr' . ($h>1?'s':'') . ' ' : '') . $m . ' min';
        $date_label = ($start? date('M d, Y', $start):'-') . ' • ' . ($start? date('g:i A',$start):'--') . ' - ' . ($end? date('g:i A',$end):'--');
        $history[] = [
            'session_id' => (int)$r['session_id'],
            'slot_number' => $r['slot_number'],
            'date_label' => $date_label,
            'duration_label' => $duration_label,
            'amount' => is_null($r['amount']) ? null : (float)$r['amount'],
            'payment_status' => $r['payment_status'] ?: 'n/a',
            'payment_method' => $r['payment_method'] ?: 'n/a',
            'reference_number' => $r['reference_number'] ?: null,
            'start_time' => $r['start_time'],
            'end_time' => $r['end_time'],
        ];
    }

    echo json_encode(['success'=>true,'history'=>$history]);
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
