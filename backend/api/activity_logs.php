<?php
// Ensure we always output JSON, even on fatal errors
ini_set('display_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Custom error handler to ensure JSON output
function handleError($errno, $errstr, $errfile, $errline) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Server error: ' . $errstr,
        'debug' => [
            'file' => basename($errfile),
            'line' => $errline
        ]
    ]);
    exit();
}

set_error_handler('handleError');

try {
    require_once '../config/database.php';
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database connection failed: ' . $e->getMessage()
    ]);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                getActivityLogById($pdo, $_GET['id']);
            } else {
                getAllActivityLogs($pdo);
            }
            break;
        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            addActivityLog($pdo, $input);
            break;
        case 'DELETE':
            $input = json_decode(file_get_contents('php://input'), true);
            if (isset($_GET['id'])) {
                deleteActivityLogById($pdo, $_GET['id']);
            } elseif (isset($input['ids']) && is_array($input['ids'])) {
                deleteMultipleActivityLogs($pdo, $input['ids']);
            } elseif (isset($input['delete_all']) && $input['delete_all'] === true) {
                deleteAllActivityLogs($pdo);
            } else {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Invalid delete request']);
            }
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

function getAllActivityLogs($pdo) {
    try {
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
        $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
        
        $sql = "SELECT id, action, user_email, details, created_at FROM activity_logs ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Clean and validate the data before sending
        $cleanLogs = [];
        foreach ($logs as $log) {
            $cleanLog = [
                'id' => $log['id'],
                'action' => $log['action'] ?? 'Unknown Action',
                'user_email' => $log['user_email'] ?? 'Unknown User',
                'details' => $log['details'] ?? null,
                'created_at' => $log['created_at'] ?? null
            ];
            
            // Skip logs with malformed data
            if (strpos($cleanLog['action'], 'undefined') !== false || 
                strpos($cleanLog['action'], 'tableystem') !== false ||
                ($cleanLog['details'] && strpos($cleanLog['details'], 'undefined') !== false)) {
                continue;
            }
            
            $cleanLogs[] = $cleanLog;
        }
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM activity_logs WHERE 
                     action NOT LIKE '%undefined%' AND 
                     action NOT LIKE '%tableystem%' AND 
                     (details IS NULL OR details NOT LIKE '%undefined%')";
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute();
        $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        echo json_encode([
            'status' => 'success',
            'logs' => $cleanLogs,
            'total' => $total,
            'count' => count($cleanLogs)
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

function getActivityLogById($pdo, $id) {
    try {
        $sql = "SELECT * FROM activity_logs WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $log = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($log) {
            echo json_encode([
                'status' => 'success',
                'log' => $log
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Activity log not found']);
        }
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

function addActivityLog($pdo, $data) {
    try {
        $required_fields = ['action', 'user_email'];
        
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                throw new Exception("Field '$field' is required");
            }
        }
        
        // Validate and clean the action field
        $action = trim($data['action']);
        $user_email = trim($data['user_email']);
        $details = isset($data['details']) ? trim($data['details']) : null;
        
        // Check for malformed data patterns
        if (strpos($action, 'undefined') !== false || 
            strpos($action, 'tableystem') !== false ||
            strpos($details, 'undefined') !== false ||
            strpos($details, 'Invalid Date') !== false) {
            
            // Log the malformed attempt for debugging
            error_log("Malformed activity log attempt: Action='$action', Details='$details', User='$user_email'");
            
            throw new Exception("Invalid log data detected. Please check your input.");
        }
        
        // Additional validation
        if (strlen($action) > 100) {
            throw new Exception("Action field is too long (max 100 characters)");
        }
        
        if (strlen($user_email) > 100) {
            throw new Exception("User email field is too long (max 100 characters)");
        }
        
        if ($details && strlen($details) > 500) {
            throw new Exception("Details field is too long (max 500 characters)");
        }
        
        $sql = "INSERT INTO activity_logs (action, user_email, details) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([$action, $user_email, $details]);
        
        if ($result) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Activity log added successfully',
                'id' => $pdo->lastInsertId()
            ]);
        } else {
            throw new Exception("Failed to add activity log");
        }
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

// Helper function to log activities (can be called from other APIs)
function logActivity($pdo, $action, $user_email, $details = null) {
    try {
        // Validate and clean input
        $action = trim($action);
        $user_email = trim($user_email);
        $details = $details ? trim($details) : null;
        
        // Check for malformed data patterns
        if (strpos($action, 'undefined') !== false || 
            strpos($action, 'tableystem') !== false ||
            ($details && strpos($details, 'undefined') !== false) ||
            ($details && strpos($details, 'Invalid Date') !== false)) {
            
            error_log("Malformed activity log attempt blocked: Action='$action', Details='$details', User='$user_email'");
            return false;
        }
        
        $sql = "INSERT INTO activity_logs (action, user_email, details) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$action, $user_email, $details]);
        return true;
    } catch (Exception $e) {
        error_log("Failed to log activity: " . $e->getMessage());
        return false;
    }
}

function deleteActivityLogById($pdo, $id) {
    try {
        $sql = "DELETE FROM activity_logs WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([$id]);
        
        if ($result && $stmt->rowCount() > 0) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Activity log deleted successfully'
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Activity log not found']);
        }
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

function deleteMultipleActivityLogs($pdo, $ids) {
    try {
        if (empty($ids)) {
            throw new Exception("No IDs provided for deletion");
        }
        
        // Validate that all IDs are integers
        $cleanIds = array_filter(array_map('intval', $ids));
        if (count($cleanIds) !== count($ids)) {
            throw new Exception("Invalid ID format provided");
        }
        
        $placeholders = str_repeat('?,', count($cleanIds) - 1) . '?';
        $sql = "DELETE FROM activity_logs WHERE id IN ($placeholders)";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute($cleanIds);
        
        $deletedCount = $stmt->rowCount();
        
        echo json_encode([
            'status' => 'success',
            'message' => "Successfully deleted $deletedCount activity log(s)",
            'deleted_count' => $deletedCount
        ]);
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

function deleteAllActivityLogs($pdo) {
    try {
        $sql = "DELETE FROM activity_logs";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute();
        
        $deletedCount = $stmt->rowCount();
        
        echo json_encode([
            'status' => 'success',
            'message' => "Successfully deleted all activity logs ($deletedCount records)",
            'deleted_count' => $deletedCount
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>