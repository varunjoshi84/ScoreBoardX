<?php
class Database {
    private $host = "127.0.0.1";
    private $db_name = "sportsapp";
    private $username = "root";
    private $password = "Ayush@8920";
    public $conn;

    public function getConnection() {
        $this->conn = new mysqli($this->host, $this->username, $this->password, $this->db_name);

        if ($this->conn->connect_error) {
            die("Database connection error: " . $this->conn->connect_error);
        }
        return $this->conn;
    }
}
?>
