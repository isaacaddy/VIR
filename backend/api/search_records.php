<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

include_once '../config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    $searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
    
    $query = "SELECT 
        id,
        co_full_name,
        co_email,
        co_contact,
        chassis_number,
        vehicle_make,
        created_at
    FROM ownership_changes 
    WHERE status = 'active'
    AND (
        co_full_name LIKE :search
        OR chassis_number LIKE :search
        OR co_email LIKE :search
        OR co_contact LIKE :search
    )
    ORDER BY created_at DESC";
    
    $stmt = $db->prepare($query);
    
    $searchTerm = "%{$searchTerm}%";
    $stmt->bindParam(':search', $searchTerm);
    
    $stmt->execute();
    
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        "status" => "success",
        "data" => $records
    ]);
    
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
?> 