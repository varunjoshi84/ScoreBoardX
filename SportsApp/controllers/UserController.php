<?php 
require_once __DIR__ . '/../config/Database.php';

class UserController {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getUser($email) {
        $sql = "SELECT full_name, email, role FROM users WHERE email = ?";
        try {
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $this->conn->error);
            }

            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if (!$user) {
                return ["error" => "User not found"];
            }

            return ["User" => $user];
        } catch (Exception $e) {
            return ["error" => "Error Fetching data: " . $e->getMessage()];
        }
    }

    public function deleteUser($email) {
        $response = $this->getUser($email);
        if (isset($response['error'])) {
            return ["error" => "User not found"];
        }

        $sql = "DELETE FROM users WHERE email = ?";
        try {
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $this->conn->error);
            }

            $stmt->bind_param("s", $email);
            $stmt->execute();

            return ["message" => "User Deleted Successfully"];
        } catch (Exception $e) {
            return ["error" => "Error Deleting User: " . $e->getMessage()];
        }
    }
}
?>
