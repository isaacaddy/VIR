<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

include_once '../config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    // Fetch activity logs focused on user actions
    $query = "SELECT 
                id,
                action,
                user_email as user,
                created_at as timestamp
              FROM activity_logs 
              ORDER BY created_at DESC 
              LIMIT 10";
              
    error_log("Executing query: " . $query);
              
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    error_log("Retrieved " . count($logs) . " log rows: " . json_encode($logs));
    
    echo json_encode([
        'status' => 'success',
        'logs' => $logs
    ]);

} catch (Exception $e) {
    error_log("Error retrieving logs: " . $e->getMessage());
    
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?> 