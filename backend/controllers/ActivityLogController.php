<?php
include_once '../config/database.php';

class ActivityLogController {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function restore($id) {
        $query = "UPDATE activity_logs SET deleted_at = NULL WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        
        try {
            $stmt->execute([$id]);
            return $stmt->rowCount() > 0;
        } catch(PDOException $e) {
            return false;
        }
    }
    
    public function getLogs() {
        $query = "SELECT * FROM activity_logs ORDER BY timestamp DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        $logs = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $logs[] = array(
                'id' => $row['id'],
                'action' => $row['action'],
                'user' => $row['user'],
                'timestamp' => $row['timestamp']
            );
        }
        
        return $logs;
    }
    
    public function logActivity($action, $user) {
        try {
            $query = "INSERT INTO activity_logs (action, user) VALUES (?, ?)";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([$action, $user]);
        } catch(PDOException $e) {
            error_log("Error logging activity: " . $e->getMessage());
            return false;
        }
    }
} 