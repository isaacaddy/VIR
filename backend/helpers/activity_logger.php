<?php
function logActivity($db, $action, $user_email) {
    try {
        // Debug log
        error_log("Attempting to log activity: Action=$action, User=$user_email");

        $query = "INSERT INTO activity_logs (action, user_email) VALUES (:action, :user_email)";
        $stmt = $db->prepare($query);
        
        // Bind parameters
        $stmt->bindParam(':action', $action);
        $stmt->bindParam(':user_email', $user_email);
        
        // Execute and check result
        $result = $stmt->execute();
        
        if ($result) {
            error_log("Activity logged successfully");
            return true;
        } else {
            error_log("Failed to log activity: " . json_encode($stmt->errorInfo()));
            return false;
        }
    } catch (Exception $e) {
        error_log("Exception while logging activity: " . $e->getMessage());
        return false;
    }
}

// Function to verify table exists
function ensureActivityLogTable($db) {
    try {
        $sql = "CREATE TABLE IF NOT EXISTS activity_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            action VARCHAR(50) NOT NULL,
            user_email VARCHAR(100) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        $db->exec($sql);
        error_log("Activity logs table verified/created");
        return true;
    } catch (Exception $e) {
        error_log("Failed to create activity_logs table: " . $e->getMessage());
        return false;
    }
}