<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS"); // Include OPTIONS
header("Access-Control-Allow-Headers: Content-Type, Authorization"); 

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200); // Respond with OK for preflight requests
    exit();
}

require_once __DIR__ . '/../config/Database.php';
$database = new Database();
$conn = $database->getConnection();
$data = json_decode(file_get_contents("php://input"));

if (isset($data->match_id)) {
    $match_id = $conn->real_escape_string($data->match_id);
    $sql = "SELECT * FROM matches WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $match_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $match = $result->fetch_assoc();
            echo json_encode(["status" => "success", "match" => $match]);
        } else {
            http_response_code(404);
            echo json_encode(["status" => "error", "message" => "Match not found"]);
        }
        $stmt->close();
    } else {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Database error: " . $conn->error]);
    }
} else {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Missing 'match_id' in the request body"]);
}
$conn->close();
?>