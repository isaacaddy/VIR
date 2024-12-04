<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

include_once '../config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    // Get total transfers
    $totalQuery = "SELECT COUNT(*) as total FROM ownership_changes";
    $totalStmt = $db->query($totalQuery);
    $totalResult = $totalStmt->fetch(PDO::FETCH_ASSOC);

    // Get today's transfers
    $todayQuery = "SELECT COUNT(*) as today FROM ownership_changes 
                   WHERE DATE(created_at) = CURDATE()";
    $todayStmt = $db->query($todayQuery);
    $todayResult = $todayStmt->fetch(PDO::FETCH_ASSOC);

    // Get recent activities (last 24 hours)
    $recentQuery = "SELECT COUNT(*) as recent FROM activity_logs 
                    WHERE created_at >= NOW() - INTERVAL 24 HOUR";
    $recentStmt = $db->query($recentQuery);
    $recentResult = $recentStmt->fetch(PDO::FETCH_ASSOC);

    // Get total registered vehicles
    $vehiclesQuery = "SELECT COUNT(*) as total FROM ownership_changes";
    $vehiclesStmt = $db->query($vehiclesQuery);
    $vehiclesResult = $vehiclesStmt->fetch(PDO::FETCH_ASSOC);

    // Get transfer trends (last 7 days)
    $trendsQuery = "SELECT DATE(created_at) as date, COUNT(*) as count 
                   FROM ownership_changes 
                   WHERE created_at >= NOW() - INTERVAL 7 DAY 
                   GROUP BY DATE(created_at) 
                   ORDER BY date";
    $trendsStmt = $db->query($trendsQuery);
    $trends = $trendsStmt->fetchAll(PDO::FETCH_ASSOC);

    // Get vehicle types distribution
    $typesQuery = "SELECT body_type, COUNT(*) as count 
                  FROM ownership_changes 
                  GROUP BY body_type";
    $typesStmt = $db->query($typesQuery);
    $types = $typesStmt->fetchAll(PDO::FETCH_ASSOC);

    // Get recent transfers
    $recentTransfersQuery = "SELECT 
                                co_full_name as new_owner_name,
                                vehicle_make,
                                chassis_number,
                                created_at as transfer_date
                            FROM ownership_changes 
                            ORDER BY created_at DESC 
                            LIMIT 5";
    $recentTransfersStmt = $db->query($recentTransfersQuery);
    $recentTransfers = $recentTransfersStmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'stats' => [
            'total' => $totalResult['total'],
            'today' => $todayResult['today'],
            'recent' => $recentResult['recent'],
            'totalCars' => $vehiclesResult['total']
        ],
        'trends' => $trends,
        'vehicleTypes' => $types,
        'recentTransfers' => $recentTransfers
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
} 