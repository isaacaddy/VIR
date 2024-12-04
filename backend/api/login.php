<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

include_once '../config/database.php';
include_once '../helpers/activity_logger.php';

try {
    // Get posted data
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!isset($data['email']) || !isset($data['password'])) {
        throw new Exception('Email and password are required');
    }

    $database = new Database();
    $db = $database->getConnection();

    // Ensure activity_logs table exists
    ensureActivityLogTable($db);

    // Check user credentials
    $query = "SELECT * FROM users WHERE email = :email AND status = 'active'";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':email', $data['email']);
    $stmt->execute();
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($data['password'], $user['password'])) {
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        
        // Log the successful login
        if (!logActivity($db, 'login', $user['email'])) {
            error_log("Failed to log login activity");
        }

        echo json_encode([
            'status' => 'success',
            'message' => 'Login successful',
            'user' => [
                'id' => $user['id'],
                'email' => $user['email'],
                'role' => $user['role']
            ]
        ]);
    } else {
        throw new Exception('Invalid credentials');
    }
} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
} 