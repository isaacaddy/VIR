<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

include_once '../config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        throw new Exception("Database connection failed");
    }

    // Check if specific ID is requested
    $id = isset($_GET['id']) ? $_GET['id'] : null;
    
    if ($id) {
        // Fetch specific record with ALL fields
        $query = "SELECT 
            id,
            co_full_name,
            co_postal_address,
            co_residential_address,
            co_contact,
            co_email,
            co_tin,
            po_full_name,
            po_postal_address,
            po_residential_address,
            po_contact,
            po_email,
            po_tin,
            vehicle_make,
            model_name,
            chassis_number,
            year_of_manufacture,
            body_type,
            color,
            vehicle_use,
            fuel_type,
            engine_number,
            cubic_capacity,
            number_of_cylinders,
            vehicle_number,
            created_at,
            status
        FROM ownership_changes 
        WHERE id = :id AND status = 'active'";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id);
    } else {
        // Fetch all records with limited fields for list view
        $query = "SELECT 
            id,
            co_full_name,
            co_email,
            co_contact,
            chassis_number,
            vehicle_number,
            vehicle_make,
            created_at,
            status
        FROM ownership_changes 
        WHERE status = 'active'
        ORDER BY created_at DESC";
        $stmt = $db->prepare($query);
    }
    
    if (!$stmt->execute()) {
        throw new Exception("Query execution failed: " . print_r($stmt->errorInfo(), true));
    }
    
    if ($id) {
        // Single record
        $record = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$record) {
            throw new Exception("Record not found");
        }
        $data = $record;
    } else {
        // Multiple records
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    echo json_encode([
        "status" => "success",
        "data" => $data
    ]);
    
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
?> 