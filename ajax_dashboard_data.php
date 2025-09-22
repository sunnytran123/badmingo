<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    exit(json_encode(['error' => 'Unauthorized']));
}

include 'config/database.php';

header('Content-Type: application/json');

$data_type = $_GET['type'] ?? '';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';

// Validate dates
if (!$start_date || !$end_date) {
    http_response_code(400);
    exit(json_encode(['error' => 'Missing start_date or end_date']));
}

try {
    switch ($data_type) {
        case 'top_courts':
            $stmt = $conn->prepare("
                SELECT c.court_id, c.court_name, COUNT(b.booking_id) AS count 
                FROM courts c 
                LEFT JOIN bookings b ON c.court_id = b.court_id 
                    AND b.booking_date BETWEEN ? AND ? 
                    AND b.status IN ('confirmed', 'pending')
                GROUP BY c.court_id, c.court_name 
                ORDER BY count DESC 
                LIMIT 5
            ");
            $stmt->bind_param('ss', $start_date, $end_date);
            $stmt->execute();
            $result = $stmt->get_result();
            $data = $result->fetch_all(MYSQLI_ASSOC);
            
            // Format for chart
            $response = [
                'labels' => array_map(function($row) { return $row['court_name']; }, $data),
                'data' => array_map(function($row) { return (int)$row['count']; }, $data)
            ];
            break;
            
        case 'top_products':
            $stmt = $conn->prepare("
                SELECT p.product_name, SUM(oi.quantity) AS total_quantity 
                FROM products p 
                LEFT JOIN order_items oi ON p.product_id = oi.product_id 
                LEFT JOIN orders o ON oi.order_id = o.order_id 
                    AND DATE(o.created_at) BETWEEN ? AND ? 
                    AND o.status = 'completed'
                GROUP BY p.product_id, p.product_name 
                ORDER BY total_quantity DESC 
                LIMIT 5
            ");
            $stmt->bind_param('ss', $start_date, $end_date);
            $stmt->execute();
            $result = $stmt->get_result();
            $data = $result->fetch_all(MYSQLI_ASSOC);
            
            // Format for chart
            $response = [
                'labels' => array_map(function($row) { return $row['product_name']; }, $data),
                'data' => array_map(function($row) { return (int)($row['total_quantity'] ?? 0); }, $data)
            ];
            break;
            
        case 'peak_hours':
            $stmt = $conn->prepare("
                SELECT HOUR(start_time) AS hour, COUNT(*) AS count 
                FROM bookings 
                WHERE booking_date BETWEEN ? AND ? 
                    AND status IN ('confirmed', 'pending')
                GROUP BY HOUR(start_time) 
                ORDER BY hour
            ");
            $stmt->bind_param('ss', $start_date, $end_date);
            $stmt->execute();
            $result = $stmt->get_result();
            $hourly_data = $result->fetch_all(MYSQLI_ASSOC);
            
            // Create array for all hours (6-22)
            $hours_array = array_fill(6, 17, 0); // 6 to 22 inclusive
            foreach ($hourly_data as $row) {
                $hour = (int)$row['hour'];
                if ($hour >= 6 && $hour <= 22) {
                    $hours_array[$hour] = (int)$row['count'];
                }
            }
            
            $response = [
                'labels' => array_map(function($h) { return $h . ':00'; }, range(6, 22)),
                'data' => array_values($hours_array)
            ];
            break;
            
        case 'stats':
            // Doanh thu bookings
            $stmt = $conn->prepare("SELECT SUM(total_price) AS total FROM bookings WHERE booking_date BETWEEN ? AND ? AND status = 'confirmed'");
            $stmt->bind_param('ss', $start_date, $end_date);
            $stmt->execute();
            $booking_revenue = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
            
            // Doanh thu orders
            $stmt = $conn->prepare("SELECT SUM(total_amount) AS total FROM orders WHERE DATE(created_at) BETWEEN ? AND ? AND status = 'completed'");
            $stmt->bind_param('ss', $start_date, $end_date);
            $stmt->execute();
            $order_revenue = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
            
            // Số booking
            $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM bookings WHERE booking_date BETWEEN ? AND ?");
            $stmt->bind_param('ss', $start_date, $end_date);
            $stmt->execute();
            $booking_count = $stmt->get_result()->fetch_assoc()['count'] ?? 0;
            
            // Số orders
            $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM orders WHERE DATE(created_at) BETWEEN ? AND ?");
            $stmt->bind_param('ss', $start_date, $end_date);
            $stmt->execute();
            $order_count = $stmt->get_result()->fetch_assoc()['count'] ?? 0;
            
            $response = [
                'booking_revenue' => (float)$booking_revenue,
                'order_revenue' => (float)$order_revenue,
                'booking_count' => (int)$booking_count,
                'order_count' => (int)$order_count
            ];
            break;
            
        default:
            http_response_code(400);
            exit(json_encode(['error' => 'Invalid data type']));
    }
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
