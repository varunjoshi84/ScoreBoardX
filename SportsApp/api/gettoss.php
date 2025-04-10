<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/../config/Database.php';
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(["status" => "error", "message" => "Only POST requests are allowed"]);
    exit();
}

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->match_id)) {
    http_response_code(400); // Bad Request
    echo json_encode(["status" => "error", "message" => "Missing 'match_id' parameter"]);
    exit();
}

$match_id = $data->match_id;

$database = new Database();
$conn = $database->getConnection();

$query = "SELECT match_id, toss_winner, batting_first, bowling_first
          FROM toss_info
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

if ($result->num_rows > 0) {
    $toss_info = $result->fetch_assoc();
    echo json_encode(["status" => "success", "toss_info" => $toss_info]);
} else {
    http_response_code(404); // Not Found
    echo json_encode(["status" => "error", "message" => "Toss information not found for match ID: " . $match_id]);
}

$stmt->close();
$conn->close();

?>