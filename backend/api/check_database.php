<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    // Database configuration
    $host = 'localhost';
    $dbname = 'dvla_db';
    $username = 'root';
    $password = '';

    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if database exists
    $db_check = $pdo->query("SELECT DATABASE() as current_db");
    $current_db = $db_check->fetch(PDO::FETCH_ASSOC);
    
    // Check if table exists
    $table_check = $pdo->query("SHOW TABLES LIKE 'vehicle_registrations'");
    $table_exists = $table_check->fetch() !== false;
    
    // Get table structure if it exists
    $table_structure = [];
    if ($table_exists) {
        $structure_query = $pdo->query("DESCRIBE vehicle_registrations");
        $table_structure = $structure_query->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Count records
    $record_count = 0;
    if ($table_exists) {
        $count_query = $pdo->query("SELECT COUNT(*) as count FROM vehicle_registrations");
        $record_count = $count_query->fetch(PDO::FETCH_ASSOC)['count'];
    }
    
    echo json_encode([
        'status' => 'success',
        'database' => $current_db['current_db'],
        'table_exists' => $table_exists,
        'record_count' => $record_count,
        'table_structure' => $table_structure
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage(),
        'error_code' => $e->getCode()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'General error: ' . $e->getMessage()
    ]);
}
?>