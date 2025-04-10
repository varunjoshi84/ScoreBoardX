<?php
require_once __DIR__ . '/../config/Database.php';

class LoginController {
    private $conn;
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    public function Login($email, $password) {
        $sql = "SELECT email, password, role FROM users WHERE email = ?";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            if (!$user) {
                return ["error" => "User not found"];
            }
            if (!password_verify($password, $user['password'])) {
                return ["error" => "Invalid password"];
            }
            return ["message" => "Login successful","role"=>$user['role']];
        } catch (Exception $e) {
            return ["error" => "Error querying data: " . $e->getMessage()];
        }
    }
}

class RegisterController {
    private $conn;
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    public function Register($full_name, $email, $password, $role) {
        $sql = "INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ssss", $full_name, $email, $password, $role);
            $stmt->execute();
            return ["message" => "User registered successfully"];
        } catch (Exception $e) {
            return ["error" => "Error inserting data: " . $e->getMessage()];
        }
    }
}
?>
