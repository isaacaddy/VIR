<?php
class UserController {
    private $conn;
    private $table_name = "users";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function registerUser($data) {
        try {
            $query = "INSERT INTO " . $this->table_name . "
                    (name, email, password, role)
                    VALUES (:name, :email, :password, :role)";

            $stmt = $this->conn->prepare($query);

            // Sanitize inputs
            $name = htmlspecialchars(strip_tags($data->name));
            $email = htmlspecialchars(strip_tags($data->email));
            $password = password_hash($data->password, PASSWORD_BCRYPT);
            $role = htmlspecialchars(strip_tags($data->role));

            // Bind parameters
            $stmt->bindParam(":name", $name);
            $stmt->bindParam(":email", $email);
            $stmt->bindParam(":password", $password);
            $stmt->bindParam(":role", $role);

            if($stmt->execute()) {
                // Log the activity
                $this->logActivity($email, "User Registration", "New user registered: $email");
                return true;
            }
            return false;
        } catch(PDOException $e) {
            if($e->getCode() == 23000) { // Duplicate entry error
                throw new Exception("Email already exists");
            }
            throw $e;
        }
    }

    private function logActivity($userEmail, $action, $details) {
        $query = "INSERT INTO activity_logs (action, user_email, details)
                VALUES (:action, :user_email, :details)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":action", $action);
        $stmt->bindParam(":user_email", $userEmail);
        $stmt->bindParam(":details", $details);
        $stmt->execute();
    }
} 