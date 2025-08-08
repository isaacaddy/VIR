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
        case 'GET':
            if (isset($_GET['id'])) {
                getInspectorById($pdo, $_GET['id']);
            } else {
                getAllInspectors($pdo);
            }
            break;
        case 'POST':
            addInspector($pdo, $input);
            break;
        case 'PUT':
            updateInspector($pdo, $input);
            break;
        case 'DELETE':
            deleteInspector($pdo, $_GET['id']);
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

function getAllInspectors($pdo) {
    try {
        $sql = "SELECT * FROM inspectors WHERE status = 'Active' ORDER BY name";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $inspectors = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'status' => 'success',
            'data' => $inspectors,
            'count' => count($inspectors)
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

function getInspectorById($pdo, $id) {
    try {
        $sql = "SELECT * FROM inspectors WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $inspector = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($inspector) {
            echo json_encode([
                'status' => 'success',
                'data' => $inspector
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Inspector not found']);
        }
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

function addInspector($pdo, $data) {
    try {
        $required_fields = ['name', 'employee_id', 'contact', 'email', 'department', 'hire_date'];
        
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                throw new Exception("Field '$field' is required");
            }
        }
        
        // Check for duplicate employee_id or email
        $check_sql = "SELECT id FROM inspectors WHERE employee_id = ? OR email = ?";
        $check_stmt = $pdo->prepare($check_sql);
        $check_stmt->execute([$data['employee_id'], $data['email']]);
        
        if ($check_stmt->fetch()) {
            throw new Exception("Employee ID or email already exists");
        }
        
        $sql = "INSERT INTO inspectors (name, employee_id, contact, email, department, status, hire_date) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            $data['name'],
            $data['employee_id'],
            $data['contact'],
            $data['email'],
            $data['department'],
            $data['status'] ?? 'Active',
            $data['hire_date']
        ]);
        
        if ($result) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Inspector added successfully',
                'id' => $pdo->lastInsertId()
            ]);
        } else {
            throw new Exception("Failed to add inspector");
        }
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

function updateInspector($pdo, $data) {
    try {
        if (empty($data['id'])) {
            throw new Exception("Inspector ID is required");
        }
        
        $sql = "UPDATE inspectors SET name = ?, contact = ?, email = ?, department = ?, status = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            $data['name'],
            $data['contact'],
            $data['email'],
            $data['department'],
            $data['status'],
            $data['id']
        ]);
        
        if ($result) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Inspector updated successfully'
            ]);
        } else {
            throw new Exception("Failed to update inspector");
        }
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

function deleteInspector($pdo, $id) {
    try {
        if (empty($id)) {
            throw new Exception("Inspector ID is required");
        }
        
        // Soft delete - update status to 'Inactive'
        $sql = "UPDATE inspectors SET status = 'Inactive' WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([$id]);
        
        if ($result) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Inspector deactivated successfully'
            ]);
        } else {
            throw new Exception("Failed to deactivate inspector");
        }
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>