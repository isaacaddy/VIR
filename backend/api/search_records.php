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
    if (isset($_GET['search'])) {
        searchRecords($pdo, $_GET['search']);
    } else {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Search parameter is required']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

function searchRecords($pdo, $search_term) {
    try {
        $search_term = '%' . $search_term . '%';
        
        // Search in ownership_changes table (for records.html compatibility)
        $sql = "SELECT 
                    id,
                    co_full_name,
                    co_contact,
                    co_email,
                    co_postal_address,
                    co_residential_address,
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
                    created_at
                FROM ownership_changes 
                WHERE (chassis_number LIKE ? OR vehicle_number LIKE ? OR co_full_name LIKE ?)
                AND status = 'active'
                
                UNION ALL
                
                SELECT 
                    id,
                    owner_full_name as co_full_name,
                    owner_contact as co_contact,
                    owner_email as co_email,
                    owner_postal_address as co_postal_address,
                    owner_residential_address as co_residential_address,
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
                    created_at
                FROM vehicle_registrations 
                WHERE (chassis_number LIKE ? OR vehicle_number LIKE ? OR owner_full_name LIKE ?)
                AND status = 'Active'
                
                ORDER BY created_at DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$search_term, $search_term, $search_term, $search_term, $search_term, $search_term]);
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