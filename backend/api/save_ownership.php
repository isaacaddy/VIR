<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../config/database.php';

try {
    // Get posted data and log it
    $rawData = file_get_contents("php://input");
    error_log("Received raw data: " . $rawData);
    
    $data = json_decode($rawData, true);
    
    if (!$data) {
        throw new Exception("No data received or invalid JSON: " . json_last_error_msg());
    }

    // Log the decoded data
    error_log("Decoded data: " . print_r($data, true));

    // Use the $pdo connection from database.php
    $conn = $pdo;

    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    // Prepare SQL statement
    $sql = "INSERT INTO ownership_changes (
        co_full_name, co_postal_address, co_residential_address, co_contact, co_email, co_tin,
        transfer_date, po_full_name, po_postal_address, po_residential_address, po_contact, po_email, po_tin,
        vehicle_make, model_name, chassis_number, year_of_manufacture, body_type, color,
        vehicle_use, fuel_type, cubic_capacity, engine_number, number_of_cylinders, remarks, vehicle_number
    ) VALUES (
        :co_full_name, :co_postal_address, :co_residential_address, :co_contact, :co_email, :co_tin,
        :transfer_date, :po_full_name, :po_postal_address, :po_residential_address, :po_contact, :po_email, :po_tin,
        :vehicle_make, :model_name, :chassis_number, :year_of_manufacture, :body_type, :color,
        :vehicle_use, :fuel_type, :cubic_capacity, :engine_number, :number_of_cylinders, :remarks, :vehicle_number
    )";

    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Failed to prepare statement: " . print_r($conn->errorInfo(), true));
    }

    // Validate required fields
    $required_fields = [
        'co_full_name', 'co_postal_address', 'co_residential_address', 'co_contact', 'co_email', 'co_tin',
        'transfer_date', 'po_full_name', 'po_postal_address', 'po_residential_address', 'po_contact', 'po_email', 'po_tin',
        'vehicle_make', 'model_name', 'chassis_number', 'year_of_manufacture', 'body_type', 'color',
        'vehicle_use', 'fuel_type', 'cubic_capacity', 'engine_number', 'number_of_cylinders', 'vehicle_number'
    ];

    foreach ($required_fields as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            throw new Exception("Missing required field: " . $field);
        }
    }

    // Bind parameters with error checking
    $params = [
        ":co_full_name" => $data['co_full_name'],
        ":co_postal_address" => $data['co_postal_address'],
        ":co_residential_address" => $data['co_residential_address'],
        ":co_contact" => $data['co_contact'],
        ":co_email" => $data['co_email'],
        ":co_tin" => $data['co_tin'],
        ":transfer_date" => $data['transfer_date'],
        ":po_full_name" => $data['po_full_name'],
        ":po_postal_address" => $data['po_postal_address'],
        ":po_residential_address" => $data['po_residential_address'],
        ":po_contact" => $data['po_contact'],
        ":po_email" => $data['po_email'],
        ":po_tin" => $data['po_tin'],
        ":vehicle_make" => $data['vehicle_make'],
        ":model_name" => $data['model_name'],
        ":chassis_number" => $data['chassis_number'],
        ":year_of_manufacture" => $data['year_of_manufacture'],
        ":body_type" => $data['body_type'],
        ":color" => $data['color'],
        ":vehicle_use" => $data['vehicle_use'],
        ":fuel_type" => $data['fuel_type'],
        ":cubic_capacity" => $data['cubic_capacity'],
        ":engine_number" => $data['engine_number'],
        ":number_of_cylinders" => $data['number_of_cylinders'],
        ":remarks" => isset($data['remarks']) ? $data['remarks'] : '',
        ":vehicle_number" => $data['vehicle_number']
    ];

    foreach ($params as $param => $value) {
        if (!$stmt->bindValue($param, $value)) {
            throw new Exception("Failed to bind parameter: " . $param);
        }
    }

    // Execute query with error checking
    if (!$stmt->execute()) {
        $errorInfo = $stmt->errorInfo();
        throw new Exception("Execute failed: " . $errorInfo[2]);
    }

    // Check if any rows were affected
    if ($stmt->rowCount() === 0) {
        throw new Exception("No rows were inserted");
    }

    http_response_code(200);
    echo json_encode(array(
        "status" => "success",
        "message" => "Record saved successfully",
        "id" => $conn->lastInsertId()
    ));

} catch(Exception $e) {
    error_log("Error in save_ownership.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(array(
        "status" => "error",
        "message" => $e->getMessage(),
        "data" => isset($data) ? $data : null,
        "trace" => $e->getTraceAsString()
    ));
}

// Log any database errors
if (isset($conn) && $conn->errorCode() !== '00000') {
    error_log("Database error: " . print_r($conn->errorInfo(), true));
}
?>
