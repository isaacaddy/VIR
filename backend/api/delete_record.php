<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

try {
    if ($method === 'POST' || $method === 'DELETE') {
        $id = $input['id'] ?? $_GET['id'] ?? null;
        if ($id) {
            deleteRecord($pdo, $id);
        } else {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Record ID is required']);
        }
    } else {
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

function deleteRecord($pdo, $id) {
    try {
        $pdo->beginTransaction();
        
        // First try to delete from ownership_changes
        $sql1 = "UPDATE ownership_changes SET status = 'deleted' WHERE id = ?";
        $stmt1 = $pdo->prepare($sql1);
        $result1 = $stmt1->execute([$id]);
        $affected1 = $stmt1->rowCount();
        
        // If no rows affected, try vehicle_registrations
        if ($affected1 === 0) {
            $sql2 = "UPDATE vehicle_registrations SET status = 'Inactive' WHERE id = ?";
            $stmt2 = $pdo->prepare($sql2);
            $result2 = $stmt2->execute([$id]);
            $affected2 = $stmt2->rowCount();
            
            if ($affected2 === 0) {
                $pdo->rollBack();
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Record not found']);
                return;
            }
            
            // Log the deletion in vehicle history
            $vehicle_sql = "SELECT * FROM vehicle_registrations WHERE id = ?";
            $vehicle_stmt = $pdo->prepare($vehicle_sql);
            $vehicle_stmt->execute([$id]);
            $vehicle_data = $vehicle_stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($vehicle_data) {
                $history_sql = "INSERT INTO vehicle_history (vehicle_id, vehicle_number, chassis_number, action_type, action_description, previous_data, new_data, performed_by, remarks) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $history_stmt = $pdo->prepare($history_sql);
                $history_stmt->execute([
                    $id,
                    $vehicle_data['vehicle_number'],
                    $vehicle_data['chassis_number'],
                    'update',
                    'Record deleted',
                    json_encode($vehicle_data),
                    json_encode(['status' => 'Inactive']),
                    'System',
                    'Record deleted by user request'
                ]);
            }
        }
        
        $pdo->commit();
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Record deleted successfully'
        ]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>