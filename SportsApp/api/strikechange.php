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
    echo json_encode(["status" => "error", "message" => "Missing required parameter: match_id"]);
    exit();
}

$match_id = $data->match_id;

$database = new Database();
$conn = $database->getConnection();

try {
    // 1. Get the current striker and non-striker
    $query_get_current_players = "SELECT striker, non_striker
                                  FROM current_players
                                  WHERE match_id = ?";
    $stmt_get_current_players = $conn->prepare($query_get_current_players);

    if (!$stmt_get_current_players) {
        throw new Exception("Prepare failed (get current players): " . $conn->error);
    }

    $stmt_get_current_players->bind_param("i", $match_id);
    $stmt_get_current_players->execute();
    $result_get_current_players = $stmt_get_current_players->get_result();
    $current_players = $result_get_current_players->fetch_assoc();
    $stmt_get_current_players->close();

    if (!$current_players || !isset($current_players['striker']) || !isset($current_players['non_striker'])) {
        throw new Exception("Could not retrieve current striker and non-striker for match ID: " . $match_id);
    }

    $current_striker = $current_players['striker'];
    $current_non_striker = $current_players['non_striker'];

    // 2. Swap the striker and non-striker in the current_players table
    $query_swap_strikers = "UPDATE current_players
                            SET striker = ?,
                                non_striker = ?
                            WHERE match_id = ?";
    $stmt_swap_strikers = $conn->prepare($query_swap_strikers);

    if (!$stmt_swap_strikers) {
        throw new Exception("Prepare failed (swap strikers): " . $conn->error);
    }

    $stmt_swap_strikers->bind_param("ssi", $current_non_striker, $current_striker, $match_id);
    if (!$stmt_swap_strikers->execute()) {
        throw new Exception("Execute failed (swap strikers): " . $stmt_swap_strikers->error);
    }
    $stmt_swap_strikers->close();

    echo json_encode(["status" => "success", "message" => "Strikers swapped successfully", "old_striker" => $current_striker, "new_striker" => $current_non_striker, "match_id" => $match_id]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Transaction failed: " . $e->getMessage()]);
}

$conn->close();

?>