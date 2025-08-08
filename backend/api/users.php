<?php
// Start output buffering to catch any unwanted output
ob_start();

// Disable error display and enable error logging
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Set headers first
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    ob_end_clean();
    exit(0);
}

// Custom error handler to ensure JSON output
function handleError($errno, $errstr, $errfile, $errline) {
    ob_end_clean(); // Clear any buffered output
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

// Custom exception handler
function handleException($exception) {
    ob_end_clean(); // Clear any buffered output
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Uncaught exception: ' . $exception->getMessage(),
        'debug' => [
            'file' => basename($exception->getFile()),
            'line' => $exception->getLine()
        ]
    ]);
    exit();
}

set_error_handler('handleError');
set_exception_handler('handleException');

try {
    require_once '../config/database.php';
} catch (Exception $e) {
    ob_end_clean(); // Clear any buffered output
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database connection failed: ' . $e->getMessage()
    ]);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

try {
    // Clear any buffered output before processing
    ob_end_clean();
    ob_start();
    
    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                getUserById($pdo, $_GET['id']);
            } else {
                getAllUsers($pdo);
            }
            break;
        case 'POST':
            registerUser($pdo, $input);
            break;
        case 'PUT':
            updateUser($pdo, $input);
            break;
        case 'DELETE':
            deleteUser($pdo, $_GET['id']);
            break;
        default:
            ob_end_clean();
            http_response_code(405);
            echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
            break;
    }
} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

function getAllUsers($pdo) {
    try {
        $sql = "SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        ob_end_clean();
        echo json_encode([
            'status' => 'success',
            'users' => $users,
            'count' => count($users)
        ]);
        
    } catch (Exception $e) {
        ob_end_clean();
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

function getUserById($pdo, $id) {
    try {
        $sql = "SELECT id, name, email, role, created_at FROM users WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            echo json_encode([
                'status' => 'success',
                'user' => $user
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'User not found']);
        }
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

function registerUser($pdo, $data) {
    try {
        // Debug: Log the received data
        error_log("Registration data received: " . json_encode($data));
        
        if (!$data || !is_array($data)) {
            throw new Exception("Invalid data format received");
        }
        
        $required_fields = ['name', 'email', 'password'];
        
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                throw new Exception("Field '$field' is required");
            }
        }
        
        // Validate email format
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format");
        }
        
        // Check if email already exists
        $check_sql = "SELECT id FROM users WHERE email = ?";
        $check_stmt = $pdo->prepare($check_sql);
        $check_stmt->execute([$data['email']]);
        
        if ($check_stmt->fetch()) {
            throw new Exception("Email already exists");
        }
        
        // Hash password
        $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            $data['name'],
            $data['email'],
            $hashed_password,
            $data['role'] ?? 'member'
        ]);
        
        if ($result) {
            $user_id = $pdo->lastInsertId();
            
            // Log the activity (but don't fail if this fails)
            try {
                logUserActivity($pdo, 'User Registration', $data['email'], 'New user account created');
            } catch (Exception $log_error) {
                error_log("Failed to log activity: " . $log_error->getMessage());
            }
            
            ob_end_clean();
            echo json_encode([
                'status' => 'success',
                'message' => 'User registered successfully',
                'user_id' => $user_id
            ]);
        } else {
            throw new Exception("Failed to register user");
        }
        
    } catch (Exception $e) {
        error_log("Registration error: " . $e->getMessage());
        ob_end_clean();
        http_response_code(400);
        echo json_encode([
            'status' => 'error', 
            'message' => $e->getMessage(),
            'debug' => [
                'received_data' => $data ?? 'null',
                'error_line' => $e->getLine()
            ]
        ]);
    }
}

function updateUser($pdo, $data) {
    try {
        if (empty($data['id'])) {
            throw new Exception("User ID is required");
        }
        
        $sql = "UPDATE users SET name = ?, email = ?, role = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            $data['name'],
            $data['email'],
            $data['role'],
            $data['id']
        ]);
        
        if ($result) {
            // Log the activity
            logUserActivity($pdo, 'User Updated', $data['email'], 'User information updated');
            
            echo json_encode([
                'status' => 'success',
                'message' => 'User updated successfully'
            ]);
        } else {
            throw new Exception("Failed to update user");
        }
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

function deleteUser($pdo, $id) {
    try {
        if (empty($id)) {
            throw new Exception("User ID is required");
        }
        
        // Get user info before deletion for logging
        $user_sql = "SELECT email FROM users WHERE id = ?";
        $user_stmt = $pdo->prepare($user_sql);
        $user_stmt->execute([$id]);
        $user = $user_stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            throw new Exception("User not found");
        }
        
        $sql = "DELETE FROM users WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([$id]);
        
        if ($result) {
            // Log the activity
            logUserActivity($pdo, 'User Deleted', $user['email'], 'User account deleted');
            
            echo json_encode([
                'status' => 'success',
                'message' => 'User deleted successfully'
            ]);
        } else {
            throw new Exception("Failed to delete user");
        }
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

function logUserActivity($pdo, $action, $user_email, $details) {
    try {
        $sql = "INSERT INTO activity_logs (action, user_email, details) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$action, $user_email, $details]);
    } catch (Exception $e) {
        // Log error but don't fail the main operation
        error_log("Failed to log user activity: " . $e->getMessage());
    }
}
?>