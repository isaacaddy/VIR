<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/database.php';

$action = $_GET['action'] ?? 'check';

try {
    if ($action === 'check') {
        // Check for malformed entries
        $sql = "SELECT * FROM activity_logs WHERE 
                action LIKE '%tableystem%' OR 
                action LIKE '%undefined%' OR 
                details LIKE '%undefined%' OR
                action LIKE '%BackupundefinedDatabase%' OR
                details LIKE '%Invalid Date%'
                ORDER BY created_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $malformed_logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Also get recent logs to see patterns
        $recent_sql = "SELECT * FROM activity_logs ORDER BY created_at DESC LIMIT 20";
        $recent_stmt = $pdo->prepare($recent_sql);
        $recent_stmt->execute();
        $recent_logs = $recent_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Log analysis completed',
            'malformed_logs' => $malformed_logs,
            'malformed_count' => count($malformed_logs),
            'recent_logs' => $recent_logs
        ]);
        
    } elseif ($action === 'clean') {
        // Clean up malformed entries
        $sql = "DELETE FROM activity_logs WHERE 
                action LIKE '%tableystem%' OR 
                action LIKE '%undefined%' OR 
                details LIKE '%undefined%' OR
                action LIKE '%BackupundefinedDatabase%' OR
                details LIKE '%Invalid Date%'";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute();
        $deleted_count = $stmt->rowCount();
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Malformed logs cleaned up',
            'deleted_count' => $deleted_count
        ]);
        
    } elseif ($action === 'add_test') {
        // Add a test backup log to see the pattern
        $sql = "INSERT INTO activity_logs (action, user_email, details) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            'System Backup',
            'test@example.com',
            'Database backup created successfully'
        ]);
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Test backup log added',
            'id' => $pdo->lastInsertId()
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>