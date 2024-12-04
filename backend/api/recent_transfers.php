<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
require_once '../config/database.php';

try {
    $db = new Database();
    $conn = $db->connect();

    // Get recent transfers
    $stmt = $conn->prepare("
        SELECT 
            new_owner_name,
            vehicle_make,
            chassis_number,
            transfer_date
        FROM transfers 
        ORDER BY transfer_date DESC 
        LIMIT 5
    ");
    $stmt->execute();
    $transfers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get statistics
    $today = date('Y-m-d');
    $week_start = date('Y-m-d', strtotime('this week'));
    $month_start = date('Y-m-01');

    // Today's transfers
    $stmt = $conn->prepare("SELECT COUNT(*) FROM transfers WHERE DATE(transfer_date) = ?");
    $stmt->execute([$today]);
    $today_count = $stmt->fetchColumn();

    // This week's transfers
    $stmt = $conn->prepare("SELECT COUNT(*) FROM transfers WHERE transfer_date >= ?");
    $stmt->execute([$week_start]);
    $week_count = $stmt->fetchColumn();

    // This month's transfers
    $stmt = $conn->prepare("SELECT COUNT(*) FROM transfers WHERE transfer_date >= ?");
    $stmt->execute([$month_start]);
    $month_count = $stmt->fetchColumn();

    // Get vehicle types distribution
    $stmt = $conn->prepare("
        SELECT body_type, COUNT(*) as count 
        FROM transfers 
        GROUP BY body_type
    ");
    $stmt->execute();
    $vehicle_types = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'transfers' => $transfers,
        'stats' => [
            'today' => $today_count,
            'week' => $week_count,
            'month' => $month_count
        ],
        'vehicleTypes' => $vehicle_types
    ]);

} catch(PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} 