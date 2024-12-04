<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');

include_once '../config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    // Debug: Log the current session
    error_log("Current session: " . print_r($_SESSION, true));

    // First, check if we have a users table
    $checkTableQuery = "SHOW TABLES LIKE 'users'";
    $tableResult = $db->query($checkTableQuery);
    
    if ($tableResult->rowCount() === 0) {
        throw new Exception('Users table does not exist');
    }

    // Describe the users table to check its structure
    $describeQuery = "DESCRIBE users";
    $describeResult = $db->query($describeQuery);
    error_log("Table structure: " . print_r($describeResult->fetchAll(PDO::FETCH_ASSOC), true));

    // Get all users to verify data
    $allUsersQuery = "SELECT id, name, email FROM users";
    $allUsersStmt = $db->query($allUsersQuery);
    $allUsers = $allUsersStmt->fetchAll(PDO::FETCH_ASSOC);
    error_log("All users in database: " . print_r($allUsers, true));

    // Now try to get the specific user
    $query = "SELECT name FROM users WHERE email = :email";
    $stmt = $db->prepare($query);
    $userEmail = $_SESSION['user_email'] ?? '';
    $stmt->bindParam(':email', $userEmail);
    $stmt->execute();
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && isset($user['name'])) {
        echo json_encode([
            'status' => 'success',
            'user' => [
                'name' => $user['name']
            ]
        ]);
    } else {
        throw new Exception("No user found with email: $userEmail");
    }

} catch (Exception $e) {
    error_log("Error in user_info.php: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'debug_info' => [
            'session' => $_SESSION,
            'error' => $e->getMessage()
        ]
    ]);
} 