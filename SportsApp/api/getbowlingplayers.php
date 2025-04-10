<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
require_once __DIR__ . '/../config/Database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(["status" => "error", "message" => "Only POST requests are allowed"]);
    exit();
}

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->match_id)) {
    http_response_code(400); // Bad Request
    echo json_encode(["status" => "error", "message" => "Missing 'match_id' parameter in the request body"]);
    exit();
}

$match_id = $data->match_id;

$database = new Database();
$conn = $database->getConnection();

$query = "SELECT DISTINCT player_name
          FROM bowling_scores bs
          WHERE match_id = ?";

$stmt = $conn->prepare($query);

if (!$stmt) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Prepare failed: " . $conn->error]);
    $conn->close();
    exit();
}

$stmt->bind_param("i", $match_id);
$stmt->execute();

$result = $stmt->get_result();
$players = array();

while ($row = $result->fetch_assoc()) {
    $players[] = $row;
}

$stmt->close();
$conn->close();

if (!empty($players)) {
    echo json_encode(["status" => "success", "players" => $players]);
} else {
    echo json_encode(["status" => "success", "players" => []]); // Return empty array if no players found
}

?>