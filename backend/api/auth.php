<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';
include_once '../models/User.php';

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

$data = json_decode(file_get_contents("php://input"));

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(isset($data->action)) {
        switch($data->action) {
            case 'login':
                $user->email = $data->email;
                $user->password = $data->password;

                $result = $user->login();
                if($result) {
                    http_response_code(200);
                    echo json_encode([
                        "status" => "success",
                        "message" => "Login successful",
                        "data" => [
                            "id" => $result['id'],
                            "name" => $result['name'],
                            "email" => $result['email'],
                            "role" => $result['role']
                        ]
                    ]);
                } else {
                    http_response_code(401);
                    echo json_encode([
                        "status" => "error",
                        "message" => "Invalid credentials"
                    ]);
                }
                break;

            case 'register':
                $user->name = $data->name;
                $user->email = $data->email;
                $user->password = $data->password;
                $user->role = $data->role ?? 'member';

                if($user->create()) {
                    http_response_code(201);
                    echo json_encode([
                        "status" => "success",
                        "message" => "User created successfully"
                    ]);
                } else {
                    http_response_code(400);
                    echo json_encode([
                        "status" => "error",
                        "message" => "Unable to create user"
                    ]);
                }
                break;
        }
    }
} 