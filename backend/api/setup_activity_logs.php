<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

include_once '../config/database.php';
include_once '../helpers/activity_logger.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Ensure the activity_logs table exists with correct structure
    $tableCreated = ensureActivityLogTable($db);
    
    if ($tableCreated) {
        // Add some sample data if table is empty
        $checkQuery = "SELECT COUNT(*) as count FROM activity_logs";
        $stmt = $db->prepare($checkQuery);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['count'] == 0) {
            // Insert sample activity logs
            logActivity($db, 'User Login', 'admin@example.com');
            logActivity($db, 'Record Created', 'user@example.com');
            logActivity($db, 'Record Updated', 'admin@example.com');
        }
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Activity logs table setup complete',
            'record_count' => $result['count']
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to create activity_logs table'
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Setup error: ' . $e->getMessage()
    ]);
}
?>