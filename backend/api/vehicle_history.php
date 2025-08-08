<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            if (isset($_GET['search'])) {
                searchVehicleHistory($pdo, $_GET['search'], $_GET['filter'] ?? 'all');
            } elseif (isset($_GET['vehicle_id'])) {
                getVehicleHistory($pdo, $_GET['vehicle_id']);
            } else {
                getAllHistory($pdo);
            }
            break;
        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            addHistoryEntry($pdo, $input);
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

function searchVehicleHistory($pdo, $search_term, $filter) {
    try {
        $search_term = '%' . $search_term . '%';
        
        // Base query
        $sql = "SELECT vh.*, vr.owner_full_name as current_owner 
                FROM vehicle_history vh 
                LEFT JOIN vehicle_registrations vr ON vh.vehicle_id = vr.id 
                WHERE (vh.vehicle_number LIKE ? OR vh.chassis_number LIKE ? OR vr.owner_full_name LIKE ?)";
        
        $params = [$search_term, $search_term, $search_term];
        
        // Add filter conditions
        if ($filter !== 'all') {
            switch ($filter) {
                case 'registrations':
                    $sql .= " AND vh.action_type = 'registration'";
                    break;
                case 'updates':
                    $sql .= " AND vh.action_type = 'update'";
                    break;
                case 'ownership_changes':
                    $sql .= " AND vh.action_type = 'ownership_change'";
                    break;
            }
        }
        
        $sql .= " ORDER BY vh.performed_at DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Process history data to match frontend format
        $processed_history = [];
        foreach ($history as $record) {
            $processed_record = [
                'id' => $record['id'],
                'vehicle_number' => $record['vehicle_number'],
                'chassis_number' => $record['chassis_number'],
                'owner_name' => $record['current_owner'] ?? 'Unknown',
                'action_type' => $record['action_type'],
                'action_description' => $record['action_description'],
                'timestamp' => $record['performed_at'],
                'is_current' => false, // Will be set to true for the most recent record
                'details' => []
            ];
            
            // Parse JSON data
            if ($record['new_data']) {
                $new_data = json_decode($record['new_data'], true);
                if ($new_data) {
                    $processed_record['details'] = $new_data;
                }
            }
            
            // Add previous data for updates
            if ($record['previous_data'] && $record['action_type'] === 'update') {
                $previous_data = json_decode($record['previous_data'], true);
                if ($previous_data) {
                    $processed_record['details']['previous_data'] = $previous_data;
                }
            }
            
            $processed_history[] = $processed_record;
        }
        
        // Mark the most recent record as current
        if (!empty($processed_history)) {
            $processed_history[0]['is_current'] = true;
        }
        
        echo json_encode([
            'status' => 'success',
            'data' => $processed_history,
            'count' => count($processed_history)
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

function getVehicleHistory($pdo, $vehicle_id) {
    try {
        $sql = "SELECT vh.*, vr.owner_full_name as current_owner 
                FROM vehicle_history vh 
                LEFT JOIN vehicle_registrations vr ON vh.vehicle_id = vr.id 
                WHERE vh.vehicle_id = ? 
                ORDER BY vh.performed_at DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$vehicle_id]);
        $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'status' => 'success',
            'data' => $history,
            'count' => count($history)
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

function getAllHistory($pdo) {
    try {
        $sql = "SELECT vh.*, vr.owner_full_name as current_owner 
                FROM vehicle_history vh 
                LEFT JOIN vehicle_registrations vr ON vh.vehicle_id = vr.id 
                ORDER BY vh.performed_at DESC 
                LIMIT 100";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'status' => 'success',
            'data' => $history,
            'count' => count($history)
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

function addHistoryEntry($pdo, $data) {
    try {
        $required_fields = ['vehicle_id', 'vehicle_number', 'chassis_number', 'action_type', 'action_description', 'performed_by'];
        
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                throw new Exception("Field '$field' is required");
            }
        }
        
        $sql = "INSERT INTO vehicle_history (vehicle_id, vehicle_number, chassis_number, action_type, action_description, previous_data, new_data, performed_by, remarks) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            $data['vehicle_id'],
            $data['vehicle_number'],
            $data['chassis_number'],
            $data['action_type'],
            $data['action_description'],
            isset($data['previous_data']) ? json_encode($data['previous_data']) : null,
            isset($data['new_data']) ? json_encode($data['new_data']) : null,
            $data['performed_by'],
            $data['remarks'] ?? null
        ]);
        
        if ($result) {
            echo json_encode([
                'status' => 'success',
                'message' => 'History entry added successfully',
                'id' => $pdo->lastInsertId()
            ]);
        } else {
            throw new Exception("Failed to add history entry");
        }
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>