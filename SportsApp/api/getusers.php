<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
require_once __DIR__ . '/../config/Database.php';
$database = new Database();
$conn = $database->getConnection();
$sql = "SELECT full_name,email,role,created_at FROM users ORDER BY created_at DESC";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    echo json_encode(["status" => "success", "users" => $users]);
} else {
    echo json_encode(["status" => "success", "matches" => []]);
}
?>
