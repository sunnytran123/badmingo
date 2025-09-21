<?php
include 'config/database.php';
$date = $_GET['date'] ?? '';
$court = intval($_GET['court'] ?? 0);

$result = [];
if ($date && $court) {
    $stmt = $conn->prepare("SELECT start_time, end_time FROM bookings WHERE booking_date = ? AND court_id = ? AND status != 'cancelled'");
    $stmt->bind_param("si", $date, $court);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $result[] = [
            'start_time' => substr($row['start_time'], 0, 5), // 13:00:00 -> 13:00
            'end_time' => substr($row['end_time'], 0, 5)       // 14:00:00 -> 14:00
        ];
    }
}
header('Content-Type: application/json');
echo json_encode($result);