<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

include_once '../config/database.php';
include_once '../helpers/activity_logger.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    // Get the user email before destroying the session
    $userEmail = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : 'unknown@user.com';

    // Log the logout activity
    logActivity($db, 'logout', $userEmail);

    // Clear session data
    session_unset();
    session_destroy();

    echo json_encode([
        'status' => 'success',
        'message' => 'Logged out successfully'
    ]);

} catch (Exception $e) {
    error_log("Logout error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
} 

?> 