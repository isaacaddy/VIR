<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../config/database.php';

try {
    if (isset($_GET['id'])) {
        getRecordById($pdo, $_GET['id']);
    } else {
        getAllRecords($pdo);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

function getRecordById($pdo, $id) {
    try {
        // First try ownership_changes table
        $sql = "SELECT * FROM ownership_changes WHERE id = ? AND status = 'active'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // If not found, try vehicle_registrations table
        if (!$record) {
            $sql = "SELECT 
                        id,
                        owner_full_name as co_full_name,
                        owner_contact as co_contact,
                        owner_email as co_email,
                        owner_postal_address as co_postal_address,
                        owner_residential_address as co_residential_address,
                        '' as po_full_name,
                        '' as po_contact,
                        '' as po_email,
                        '' as po_postal_address,
                        '' as po_residential_address,
                        '' as po_tin,
                        vehicle_make,
                        model_name,
                        chassis_number,
                        engine_number,
                        year_of_manufacture,
                        body_type,
                        color,
                        fuel_type,
                        cubic_capacity,
                        number_of_cylinders,
                        vehicle_use,
                        vehicle_number,
                        declaration_number,
                        certificate_number,
                        registration_date,
                        status,
                        inspector_name,
                        inspection_date,
                        remarks,
                        created_at,
                        updated_at
                    FROM vehicle_registrations 
                    WHERE id = ? AND status = 'Active'";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id]);
            $record = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        if ($record) {
            echo json_encode([
                'status' => 'success',
                'data' => $record
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Record not found']);
        }
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

function getAllRecords($pdo) {
    try {
        // Get records from both tables
        $sql = "SELECT 
                    id,
                    co_full_name,
                    co_contact,
                    co_email,
                    co_postal_address,
                    co_residential_address,
                    po_full_name,
                    po_contact,
                    po_email,
                    po_postal_address,
                    po_residential_address,
                    po_tin,
                    vehicle_make,
                    model_name,
                    chassis_number,
                    engine_number,
                    year_of_manufacture,
                    body_type,
                    color,
                    fuel_type,
                    cubic_capacity,
                    number_of_cylinders,
                    vehicle_use,
                    vehicle_number,
                    '' as declaration_number,
                    '' as certificate_number,
                    transfer_date as registration_date,
                    status,
                    '' as inspector_name,
                    '' as inspection_date,
                    remarks,
                    created_at,
                    updated_at
                FROM ownership_changes 
                WHERE status = 'active'
                
                UNION ALL
                
                SELECT 
                    id,
                    owner_full_name as co_full_name,
                    owner_contact as co_contact,
                    owner_email as co_email,
                    owner_postal_address as co_postal_address,
                    owner_residential_address as co_residential_address,
                    '' as po_full_name,
                    '' as po_contact,
                    '' as po_email,
                    '' as po_postal_address,
                    '' as po_residential_address,
                    '' as po_tin,
                    vehicle_make,
                    model_name,
                    chassis_number,
                    engine_number,
                    year_of_manufacture,
                    body_type,
                    color,
                    fuel_type,
                    cubic_capacity,
                    number_of_cylinders,
                    vehicle_use,
                    vehicle_number,
                    declaration_number,
                    certificate_number,
                    registration_date,
                    status,
                    inspector_name,
                    inspection_date,
                    remarks,
                    created_at,
                    updated_at
                FROM vehicle_registrations 
                WHERE status = 'Active'
                
                ORDER BY created_at DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'status' => 'success',
            'data' => $records,
            'count' => count($records)
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>