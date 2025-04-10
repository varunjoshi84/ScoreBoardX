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

$data = json_decode(file_get_contents("php://input"));

// Only allow POST method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(["status" => "error", "message" => "Only POST requests are allowed"]);
    exit();
}

// Check if match_id is provided
if (!isset($data->match_id)) {
    http_response_code(400); // Bad Request
    echo json_encode(["status" => "error", "message" => "Missing 'match_id' parameter"]);
    exit();
}

$match_id = $data->match_id; // ✅ FIXED missing semicolon here

$database = new Database();
$conn = $database->getConnection();

// Prepare the query
$query = "SELECT match_id, team_batting, striker, non_striker, current_bowler
          FROM current_players
          WHERE match_id = ?";

$stmt = $conn->prepare($query);

// Check for preparation failure
if (!$stmt) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Prepare failed: " . $conn->error]);
    $conn->close();
    exit();
}

// Bind and execute
$stmt->bind_param("i", $match_id);
$stmt->execute();

$result = $stmt->get_result();
$currentPlayers = $result->fetch_assoc();

$stmt->close();
$conn->close();

// Send response
if ($currentPlayers) {
    echo json_encode(["status" => "success", "current_players" => $currentPlayers]);
} else {
    http_response_code(404); // Not Found
    echo json_encode(["status" => "error", "message" => "Current players not found for match ID: " . $match_id]);
}
?>
