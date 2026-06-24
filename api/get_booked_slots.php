<?php
// API endpoint: Returns booked time slots for a given employee + date
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

include_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

$employee_id = isset($_GET['employee_id']) ? intval($_GET['employee_id']) : 0;
$date        = isset($_GET['date']) ? trim($_GET['date']) : '';

if ($employee_id <= 0 || empty($date) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    echo json_encode(['booked_slots' => []]);
    exit();
}

try {
    // Block ALL active booking statuses (pending, serving, pending_cash, pending_transfer)
    $stmt = $pdo->prepare("
        SELECT booking_time 
        FROM bookings 
        WHERE employee_id = ? 
          AND booking_date = ? 
          AND status IN ('pending', 'serving')
          AND (payment_status IN ('pending_cash','pending_transfer','paid') OR payment_status IS NULL OR payment_status = 'unpaid')
    ");
    $stmt->execute([$employee_id, $date]);
    $rows = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Normalise to HH:MM format
    $booked = array_map(function($t) {
        return substr($t, 0, 5);
    }, $rows);

    echo json_encode(['booked_slots' => array_values(array_unique($booked))]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage(), 'booked_slots' => []]);
}
