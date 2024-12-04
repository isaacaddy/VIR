<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: DELETE');
header('Access-Control-Allow-Headers: Content-Type');

include_once '../config/database.php';
include_once '../helpers/activity_logger.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Get record ID
    $data = json_decode(file_get_contents("php://input"));
    
    if (!isset($data->id)) {
        throw new Exception("Record ID is required");
    }
    
    // Soft delete the record
    $query = "UPDATE ownership_changes SET status = 'deleted' WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $data->id);
    
    if($stmt->execute()) {
        logActivity($db, $_SESSION['user_id'], 'delete_record');
        echo json_encode([
            "status" => "success",
            "message" => "Record deleted successfully"
        ]);
    } else {
        throw new Exception("Unable to delete record");
    }
    
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
?> 