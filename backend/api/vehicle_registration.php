<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

try {
    switch ($method) {
        case 'POST':
            registerVehicle($pdo, $input);
            break;
        case 'GET':
            if (isset($_GET['id'])) {
                getVehicleById($pdo, $_GET['id']);
            } else {
                getAllVehicles($pdo);
            }
            break;
        case 'PUT':
            updateVehicle($pdo, $input);
            break;
        case 'DELETE':
            deleteVehicle($pdo, $_GET['id']);
            break;
        default:
            http_response_code(405);
            echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

function registerVehicle($pdo, $data) {
    try {
        // Validate required fields
        $required_fields = [
            'vehicle_number', 'chassis_number', 'engine_number', 'vehicle_make', 
            'model_name', 'year_of_manufacture', 'body_type', 'color', 'fuel_type',
            'cubic_capacity', 'number_of_cylinders', 'vehicle_use', 'declaration_number',
            'owner_full_name', 'owner_postal_address', 'owner_residential_address',
            'owner_contact', 'owner_email', 'registration_date', 'certificate_number'
        ];

        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                throw new Exception("Field '$field' is required");
            }
        }

        // Check for duplicate vehicle number or chassis number
        $check_sql = "SELECT id FROM vehicle_registrations WHERE vehicle_number = ? OR chassis_number = ?";
        $check_stmt = $pdo->prepare($check_sql);
        $check_stmt->execute([$data['vehicle_number'], $data['chassis_number']]);
        
        if ($check_stmt->fetch()) {
            throw new Exception("Vehicle number or chassis number already exists");
        }

        // Insert vehicle registration
        $sql = "INSERT INTO vehicle_registrations (
            vehicle_number, chassis_number, engine_number, vehicle_make, model_name,
            year_of_manufacture, body_type, color, fuel_type, cubic_capacity,
            number_of_cylinders, vehicle_use, declaration_number, owner_full_name,
            owner_postal_address, owner_residential_address, owner_contact, owner_email,
            registration_date, certificate_number, status, inspector_name, inspection_date, remarks
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            $data['vehicle_number'],
            $data['chassis_number'],
            $data['engine_number'],
            $data['vehicle_make'],
            $data['model_name'],
            $data['year_of_manufacture'],
            $data['body_type'],
            $data['color'],
            $data['fuel_type'],
            $data['cubic_capacity'],
            $data['number_of_cylinders'],
            $data['vehicle_use'],
            $data['declaration_number'],
            $data['owner_full_name'],
            $data['owner_postal_address'],
            $data['owner_residential_address'],
            $data['owner_contact'],
            $data['owner_email'],
            $data['registration_date'],
            $data['certificate_number'],
            $data['status'] ?? 'Active',
            $data['inspector_name'] ?? null,
            $data['inspection_date'] ?? null,
            $data['remarks'] ?? null
        ]);

        if ($result) {
            $vehicle_id = $pdo->lastInsertId();
            
            // Log the registration in history
            logVehicleHistory($pdo, $vehicle_id, $data['vehicle_number'], $data['chassis_number'], 
                'registration', 'Initial vehicle registration', null, json_encode($data), 
                $data['inspector_name'] ?? 'System', 'Vehicle registration completed successfully');

            // Update statistics
            updateRegistrationStats($pdo, date('Y-m-d'), 'new_registration');

            echo json_encode([
                'status' => 'success',
                'message' => 'Vehicle registered successfully',
                'vehicle_id' => $vehicle_id
            ]);
        } else {
            throw new Exception("Failed to register vehicle");
        }

    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

function getAllVehicles($pdo) {
    try {
        $sql = "SELECT * FROM vehicle_registrations WHERE status != 'Inactive' ORDER BY created_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Debug: Log the query and results
        error_log("Vehicle Registration API: Found " . count($vehicles) . " records");
        if (count($vehicles) > 0) {
            error_log("Sample record fields: " . implode(', ', array_keys($vehicles[0])));
        }

        echo json_encode([
            'status' => 'success',
            'data' => $vehicles,
            'count' => count($vehicles),
            'debug' => [
                'sql' => $sql,
                'record_count' => count($vehicles),
                'sample_fields' => count($vehicles) > 0 ? array_keys($vehicles[0]) : []
            ]
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        error_log("Vehicle Registration API Error: " . $e->getMessage());
        echo json_encode([
            'status' => 'error', 
            'message' => $e->getMessage(),
            'debug' => [
                'file' => __FILE__,
                'line' => __LINE__
            ]
        ]);
    }
}

function getVehicleById($pdo, $id) {
    try {
        $sql = "SELECT * FROM vehicle_registrations WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $vehicle = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($vehicle) {
            echo json_encode([
                'status' => 'success',
                'data' => $vehicle
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Vehicle not found']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

function updateVehicle($pdo, $data) {
    try {
        if (empty($data['id'])) {
            throw new Exception("Vehicle ID is required");
        }

        // Get current vehicle data for history logging
        $current_sql = "SELECT * FROM vehicle_registrations WHERE id = ?";
        $current_stmt = $pdo->prepare($current_sql);
        $current_stmt->execute([$data['id']]);
        $current_data = $current_stmt->fetch(PDO::FETCH_ASSOC);

        if (!$current_data) {
            throw new Exception("Vehicle not found");
        }

        // Update vehicle registration
        $sql = "UPDATE vehicle_registrations SET 
            vehicle_make = ?, model_name = ?, year_of_manufacture = ?, body_type = ?, 
            color = ?, fuel_type = ?, cubic_capacity = ?, number_of_cylinders = ?, 
            vehicle_use = ?, owner_full_name = ?, owner_postal_address = ?, 
            owner_residential_address = ?, owner_contact = ?, owner_email = ?, 
            status = ?, remarks = ?, updated_at = CURRENT_TIMESTAMP
            WHERE id = ?";

        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            $data['vehicle_make'] ?? $current_data['vehicle_make'],
            $data['model_name'] ?? $current_data['model_name'],
            $data['year_of_manufacture'] ?? $current_data['year_of_manufacture'],
            $data['body_type'] ?? $current_data['body_type'],
            $data['color'] ?? $current_data['color'],
            $data['fuel_type'] ?? $current_data['fuel_type'],
            $data['cubic_capacity'] ?? $current_data['cubic_capacity'],
            $data['number_of_cylinders'] ?? $current_data['number_of_cylinders'],
            $data['vehicle_use'] ?? $current_data['vehicle_use'],
            $data['owner_full_name'] ?? $current_data['owner_full_name'],
            $data['owner_postal_address'] ?? $current_data['owner_postal_address'],
            $data['owner_residential_address'] ?? $current_data['owner_residential_address'],
            $data['owner_contact'] ?? $current_data['owner_contact'],
            $data['owner_email'] ?? $current_data['owner_email'],
            $data['status'] ?? $current_data['status'],
            $data['remarks'] ?? $current_data['remarks'],
            $data['id']
        ]);

        if ($result) {
            // Log the update in history
            logVehicleHistory($pdo, $data['id'], $current_data['vehicle_number'], 
                $current_data['chassis_number'], 'update', 'Vehicle information updated', 
                json_encode($current_data), json_encode($data), 
                $data['updated_by'] ?? 'System', 'Vehicle information updated successfully');

            echo json_encode([
                'status' => 'success',
                'message' => 'Vehicle updated successfully'
            ]);
        } else {
            throw new Exception("Failed to update vehicle");
        }

    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

function deleteVehicle($pdo, $id) {
    try {
        if (empty($id)) {
            throw new Exception("Vehicle ID is required");
        }

        // Get vehicle data before deletion for history
        $vehicle_sql = "SELECT * FROM vehicle_registrations WHERE id = ?";
        $vehicle_stmt = $pdo->prepare($vehicle_sql);
        $vehicle_stmt->execute([$id]);
        $vehicle_data = $vehicle_stmt->fetch(PDO::FETCH_ASSOC);

        if (!$vehicle_data) {
            throw new Exception("Vehicle not found");
        }

        // Soft delete - update status to 'Inactive'
        $sql = "UPDATE vehicle_registrations SET status = 'Inactive', updated_at = CURRENT_TIMESTAMP WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([$id]);

        if ($result) {
            // Log the deletion in history
            logVehicleHistory($pdo, $id, $vehicle_data['vehicle_number'], 
                $vehicle_data['chassis_number'], 'update', 'Vehicle deactivated', 
                json_encode($vehicle_data), json_encode(['status' => 'Inactive']), 
                'System', 'Vehicle registration deactivated');

            echo json_encode([
                'status' => 'success',
                'message' => 'Vehicle deactivated successfully'
            ]);
        } else {
            throw new Exception("Failed to deactivate vehicle");
        }

    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

function logVehicleHistory($pdo, $vehicle_id, $vehicle_number, $chassis_number, $action_type, $description, $previous_data, $new_data, $performed_by, $remarks) {
    try {
        $sql = "INSERT INTO vehicle_history (vehicle_id, vehicle_number, chassis_number, action_type, action_description, previous_data, new_data, performed_by, remarks) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$vehicle_id, $vehicle_number, $chassis_number, $action_type, $description, $previous_data, $new_data, $performed_by, $remarks]);
    } catch (Exception $e) {
        // Log error but don't fail the main operation
        error_log("Failed to log vehicle history: " . $e->getMessage());
    }
}

function updateRegistrationStats($pdo, $date, $type) {
    try {
        // Check if stats exist for the date
        $check_sql = "SELECT id FROM registration_statistics WHERE stat_date = ?";
        $check_stmt = $pdo->prepare($check_sql);
        $check_stmt->execute([$date]);
        
        if ($check_stmt->fetch()) {
            // Update existing stats
            $column = '';
            switch ($type) {
                case 'new_registration':
                    $column = 'new_registrations';
                    break;
                case 'renewal':
                    $column = 'renewals';
                    break;
                case 'transfer':
                    $column = 'ownership_transfers';
                    break;
                case 'inspection':
                    $column = 'inspections_completed';
                    break;
            }
            
            if ($column) {
                $update_sql = "UPDATE registration_statistics SET $column = $column + 1, total_registrations = total_registrations + 1 WHERE stat_date = ?";
                $update_stmt = $pdo->prepare($update_sql);
                $update_stmt->execute([$date]);
            }
        } else {
            // Create new stats entry
            $insert_sql = "INSERT INTO registration_statistics (stat_date, total_registrations, new_registrations, renewals, ownership_transfers, inspections_completed) VALUES (?, 1, 1, 0, 0, 0)";
            $insert_stmt = $pdo->prepare($insert_sql);
            $insert_stmt->execute([$date]);
        }
    } catch (Exception $e) {
        // Log error but don't fail the main operation
        error_log("Failed to update registration stats: " . $e->getMessage());
    }
}
?>