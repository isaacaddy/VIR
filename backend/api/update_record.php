<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

include_once '../config/database.php';
include_once '../helpers/activity_logger.php';

try {
    // Get posted data
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!$data) {
        throw new Exception("No data received");
    }

    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        throw new Exception("Database connection failed");
    }

    // Prepare update query
    $query = "UPDATE ownership_changes SET 
        co_full_name = :co_full_name,
        co_postal_address = :co_postal_address,
        co_residential_address = :co_residential_address,
        co_contact = :co_contact,
        co_email = :co_email,
        co_tin = :co_tin,
        po_full_name = :po_full_name,
        po_postal_address = :po_postal_address,
        po_residential_address = :po_residential_address,
        po_contact = :po_contact,
        po_email = :po_email,
        po_tin = :po_tin,
        vehicle_make = :vehicle_make,
        model_name = :model_name,
        chassis_number = :chassis_number,
        year_of_manufacture = :year_of_manufacture,
        body_type = :body_type,
        color = :color,
        vehicle_use = :vehicle_use,
        fuel_type = :fuel_type,
        engine_number = :engine_number,
        cubic_capacity = :cubic_capacity,
        number_of_cylinders = :number_of_cylinders,
        vehicle_number = :vehicle_number
        WHERE id = :id AND status = 'active'";

    $stmt = $db->prepare($query);

    // Bind values
    $stmt->bindValue(':co_full_name', $data['co_full_name']);
    $stmt->bindValue(':co_postal_address', $data['co_postal_address']);
    $stmt->bindValue(':co_residential_address', $data['co_residential_address']);
    $stmt->bindValue(':co_contact', $data['co_contact']);
    $stmt->bindValue(':co_email', $data['co_email']);
    $stmt->bindValue(':co_tin', $data['co_tin']);
    $stmt->bindValue(':po_full_name', $data['po_full_name']);
    $stmt->bindValue(':po_postal_address', $data['po_postal_address']);
    $stmt->bindValue(':po_residential_address', $data['po_residential_address']);
    $stmt->bindValue(':po_contact', $data['po_contact']);
    $stmt->bindValue(':po_email', $data['po_email']);
    $stmt->bindValue(':po_tin', $data['po_tin']);
    $stmt->bindValue(':vehicle_make', $data['vehicle_make']);
    $stmt->bindValue(':model_name', $data['model_name']);
    $stmt->bindValue(':chassis_number', $data['chassis_number']);
    $stmt->bindValue(':year_of_manufacture', $data['year_of_manufacture']);
    $stmt->bindValue(':body_type', $data['body_type']);
    $stmt->bindValue(':color', $data['color']);
    $stmt->bindValue(':vehicle_use', $data['vehicle_use']);
    $stmt->bindValue(':fuel_type', $data['fuel_type']);
    $stmt->bindValue(':engine_number', $data['engine_number']);
    $stmt->bindValue(':cubic_capacity', $data['cubic_capacity']);
    $stmt->bindValue(':number_of_cylinders', $data['number_of_cylinders']);
    $stmt->bindValue(':vehicle_number', $data['vehicle_number']);
    $stmt->bindValue(':id', $data['id']);

    // Execute the query
    if ($stmt->execute()) {
        // Log the edit activity
        include_once '../helpers/activity_logger.php';
        if (!logActivity($db, 'edit_record', $_SESSION['user_email'])) {
            error_log("Failed to log edit activity");
        }
        
        echo json_encode([
            "status" => "success",
            "message" => "Record updated successfully"
        ]);
    } else {
        throw new Exception("Unable to update record");
    }
} catch (Exception $e) {
    echo json_encode(array("message" => $e->getMessage()));
} 