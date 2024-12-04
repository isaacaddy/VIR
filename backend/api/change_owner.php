<?php
include_once '../config/database.php';
include_once '../controllers/ActivityLogController.php';
// After successful ownership change
if ($result) {
    // Log the ownership change
    $activityLogController = new ActivityLogController($db);
    $activityLogController->logActivity(
        "Changed vehicle ownership: {$data->vehicleReg} from {$data->currentOwner} to {$data->newOwner}",
        $_SESSION['user_email'] // or however you store the current user
    );

    // Your existing success response
    http_response_code(200);
    echo json_encode(array(
        "status" => "success",
        "message" => "Ownership changed successfully"
    ));
} else {
    // Handle error case
    http_response_code(400); // Bad request
    echo json_encode(array(
        "status" => "error",
        "message" => "Failed to change ownership"
    ));
} 