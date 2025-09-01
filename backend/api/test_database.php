<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Add error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration - Docker compatible
$host = getenv('DB_HOST') ?: 'mysql';
$dbname = getenv('DB_NAME') ?: 'dvla_db';
$username = getenv('DB_USER') ?: 'vir_user';
$password = getenv('DB_PASSWORD') ?: 'vir_password';

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    if ($db) {
        // Test if activity_logs table exists
        $query = "SHOW TABLES LIKE 'activity_logs'";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $tableExists = $stmt->rowCount() > 0;
        
        // If table exists, get column info
        $columns = [];
        if ($tableExists) {
            $query = "DESCRIBE activity_logs";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Database connection successful',
            'table_exists' => $tableExists,
            'columns' => $columns,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Database connection failed'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>