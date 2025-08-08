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
        searchVehicles($pdo, $_GET['search']);
    } else {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Search parameter is required']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

function searchVehicles($pdo, $search_term) {
    try {
        $search_term = '%' . $search_term . '%';
        
        $sql = "SELECT vr.*, 
                       CASE 
                           WHEN vr.status = 'Active' THEN 'active'
                           WHEN vr.status = 'Inactive' THEN 'inactive'
                           WHEN vr.status = 'Suspended' THEN 'suspended'
                           WHEN vr.status = 'Expired' THEN 'expired'
                           ELSE 'unknown'
                       END as search_status
                FROM vehicle_registrations vr 
                WHERE (vr.vehicle_number LIKE ? 
                       OR vr.chassis_number LIKE ? 
                       OR vr.owner_full_name LIKE ?
                       OR vr.vehicle_make LIKE ?
                       OR vr.model_name LIKE ?)
                AND vr.status != 'Inactive'
                ORDER BY vr.created_at DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$search_term, $search_term, $search_term, $search_term, $search_term]);
        $vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Format data for frontend compatibility
        $formatted_vehicles = [];
        foreach ($vehicles as $vehicle) {
            $formatted_vehicle = [
                'id' => $vehicle['id'],
                'co_full_name' => $vehicle['owner_full_name'],
                'co_contact' => $vehicle['owner_contact'],
                'co_email' => $vehicle['owner_email'],
                'co_postal_address' => $vehicle['owner_postal_address'],
                'co_residential_address' => $vehicle['owner_residential_address'],
                'vehicle_make' => $vehicle['vehicle_make'],
                'model_name' => $vehicle['model_name'],
                'chassis_number' => $vehicle['chassis_number'],
                'engine_number' => $vehicle['engine_number'],
                'year_of_manufacture' => $vehicle['year_of_manufacture'],
                'body_type' => $vehicle['body_type'],
                'color' => $vehicle['color'],
                'fuel_type' => $vehicle['fuel_type'],
                'cubic_capacity' => $vehicle['cubic_capacity'],
                'number_of_cylinders' => $vehicle['number_of_cylinders'],
                'vehicle_use' => $vehicle['vehicle_use'],
                'vehicle_number' => $vehicle['vehicle_number'],
                'declaration_number' => $vehicle['declaration_number'],
                'certificate_number' => $vehicle['certificate_number'],
                'registration_date' => $vehicle['registration_date'],
                'status' => $vehicle['search_status'],
                'inspector_name' => $vehicle['inspector_name'],
                'inspection_date' => $vehicle['inspection_date'],
                'remarks' => $vehicle['remarks'],
                'created_at' => $vehicle['created_at'],
                'updated_at' => $vehicle['updated_at']
            ];
            
            $formatted_vehicles[] = $formatted_vehicle;
        }
        
        echo json_encode([
            'status' => 'success',
            'data' => $formatted_vehicles,
            'count' => count($formatted_vehicles)
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>