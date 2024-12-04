<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';
include_once '../controllers/UserController.php';

$database = new Database();
$db = $database->getConnection();
$userController = new UserController($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get posted data
        $data = json_decode(file_get_contents("php://input"));

        // Validate required fields
        if (empty($data->name) || empty($data->email) || empty($data->password) || empty($data->role)) {
            throw new Exception("Please fill all required fields");
        }

        // Validate email format
        if (!filter_var($data->email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format");
        }

        // Validate password length
        if (strlen($data->password) < 6) {
            throw new Exception("Password must be at least 6 characters long");
        }

        // Register the user
        if ($userController->registerUser($data)) {
            http_response_code(201);
            echo json_encode([
                "status" => "success",
                "message" => "User registered successfully"
            ]);
        } else {
            throw new Exception("Unable to register user");
        }

    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            "status" => "error",
            "message" => $e->getMessage()
        ]);
    }
} 