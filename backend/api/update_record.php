<?php
// Prevent any output before JSON
ob_start();

// Suppress PHP errors/warnings from corrupting JSON output
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Clear any previous output
ob_clean();

include_once '../config/database.php';

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

    // Fetch previous data before update for history logging
    $prevQuery = "SELECT * FROM ownership_changes WHERE id = :id AND status = 'active'";
    $prevStmt = $db->prepare($prevQuery);
    $prevStmt->bindValue(':id', $data['id']);
    $prevStmt->execute();
    $previousRow = $prevStmt->fetch(PDO::FETCH_ASSOC);

    if (!$previousRow) {
        throw new Exception("Record not found or inactive");
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
        // Log the edit activity (optional - don't fail if session not available)
        try {
            session_start();
            if (isset($_SESSION['user_email'])) {
                include_once '../helpers/activity_logger.php';
                logActivity($db, 'edit_record', $_SESSION['user_email']);
            }
        } catch (Exception $logError) {
            // Log error but don't fail the main operation
            error_log("Failed to log edit activity: " . $logError->getMessage());
        }

        // Also log into vehicle_history for searchability
        try {
            // Determine vehicle_id from registrations via chassis_number or vehicle_number
            $lookupSql = "SELECT id, vehicle_number FROM vehicle_registrations WHERE chassis_number = :chassis OR vehicle_number = :vehicle LIMIT 1";
            $lookupStmt = $db->prepare($lookupSql);
            $lookupStmt->bindValue(':chassis', $data['chassis_number']);
            $lookupStmt->bindValue(':vehicle', $data['vehicle_number']);
            $lookupStmt->execute();
            $vehicleRow = $lookupStmt->fetch(PDO::FETCH_ASSOC);

            if ($vehicleRow && isset($vehicleRow['id'])) {
                $vehicleId = (int)$vehicleRow['id'];
                $vehicleNumber = $vehicleRow['vehicle_number'] ?: ($data['vehicle_number'] ?? ($previousRow['vehicle_number'] ?? ''));

                // Build previous and new data payloads (store only relevant fields)
                $relevantFields = [
                    'co_full_name','co_postal_address','co_residential_address','co_contact','co_email','co_tin',
                    'po_full_name','po_postal_address','po_residential_address','po_contact','po_email','po_tin',
                    'vehicle_make','model_name','chassis_number','year_of_manufacture','body_type','color',
                    'vehicle_use','fuel_type','cubic_capacity','engine_number','number_of_cylinders','remarks','vehicle_number'
                ];

                $previousData = [];
                foreach ($relevantFields as $f) { $previousData[$f] = $previousRow[$f] ?? null; }

                $newData = [];
                foreach ($relevantFields as $f) { $newData[$f] = $data[$f] ?? ($previousRow[$f] ?? null); }

                // Insert history record
                $histSql = "INSERT INTO vehicle_history (vehicle_id, vehicle_number, chassis_number, action_type, action_description, previous_data, new_data, performed_by, remarks) VALUES (?,?,?,?,?,?,?,?,?)";
                $histStmt = $db->prepare($histSql);
                $performedBy = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : 'System';
                $histStmt->execute([
                    $vehicleId,
                    $vehicleNumber,
                    $newData['chassis_number'] ?? $previousRow['chassis_number'] ?? '',
                    'update',
                    'Ownership change record updated',
                    json_encode($previousData),
                    json_encode($newData),
                    $performedBy,
                    $data['remarks'] ?? null
                ]);
            } else {
                error_log('vehicle_history log skipped: vehicle not found for chassis=' . ($data['chassis_number'] ?? '') . ' vehicle=' . ($data['vehicle_number'] ?? ''));
            }
        } catch (Exception $e) {
            // Do not fail main operation due to history logging
            error_log('Failed to log vehicle history for update_record: ' . $e->getMessage());
        }
        
        // Clear any output buffer and send clean JSON
        ob_clean();
        echo json_encode([
            "status" => "success",
            "message" => "Record updated successfully"
        ]);
    } else {
        throw new Exception("Unable to update record");
    }
} catch (Exception $e) {
    // Clear any output buffer and send clean JSON
    ob_clean();
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}

// End output buffering
ob_end_flush(); 