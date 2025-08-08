<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

try {
    if (empty($input['email']) || empty($input['password'])) {
        throw new Exception("Email and password are required");
    }
    
    $sql = "SELECT id, name, email, password, role FROM users WHERE email = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$input['email']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($input['password'], $user['password'])) {
        // Remove password from response
        unset($user['password']);
        
        // Log the login activity
        $log_sql = "INSERT INTO activity_logs (action, user_email, details) VALUES (?, ?, ?)";
        $log_stmt = $pdo->prepare($log_sql);
        $log_stmt->execute(['User Login', $user['email'], 'User logged in successfully']);
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Login successful',
            'user' => $user
        ]);
    } else {
        // Log failed login attempt
        $log_sql = "INSERT INTO activity_logs (action, user_email, details) VALUES (?, ?, ?)";
        $log_stmt = $pdo->prepare($log_sql);
        $log_stmt->execute(['Failed Login', $input['email'], 'Invalid login attempt']);
        
        http_response_code(401);
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid email or password'
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>