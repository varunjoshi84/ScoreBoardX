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

if (!isset($data->match_id) || !isset($data->new_bowler)) {
    http_response_code(400); // Bad Request
    echo json_encode(["status" => "error", "message" => "Missing required parameters: match_id and new_bowler"]);
    exit();
}

$match_id = $data->match_id;
$new_bowler = trim($data->new_bowler); // Sanitize input

if (empty($new_bowler)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "New bowler name cannot be empty"]);
    exit();
}

$database = new Database();
$conn = $database->getConnection();

try {
    // 1. Validate if the new bowler exists in the bowling_scores table for this match
    $query_validate_bowler = "SELECT COUNT(*) AS count
                              FROM bowling_scores
                              WHERE match_id = ? AND player_name = ?";
    $stmt_validate_bowler = $conn->prepare($query_validate_bowler);

    if (!$stmt_validate_bowler) {
        throw new Exception("Prepare failed (validate bowler): " . $conn->error);
    }

    $stmt_validate_bowler->bind_param("is", $match_id, $new_bowler);
    $stmt_validate_bowler->execute();
    $result_validate_bowler = $stmt_validate_bowler->get_result();
    $bowler_exists_row = $result_validate_bowler->fetch_assoc();
    $stmt_validate_bowler->close();

    if ($bowler_exists_row['count'] == 0) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Bowler '" . $new_bowler . "' is not found in the bowling lineup for this match."]);
        exit();
    }

    // 2. Update the current_bowler in the current_players table
    $query_update_bowler = "UPDATE current_players
                            SET current_bowler = ?
                            WHERE match_id = ?";
    $stmt_update_bowler = $conn->prepare($query_update_bowler);

    if (!$stmt_update_bowler) {
        throw new Exception("Prepare failed (update current bowler): " . $conn->error);
    }

    $stmt_update_bowler->bind_param("si", $new_bowler, $match_id);
    if (!$stmt_update_bowler->execute()) {
        throw new Exception("Execute failed (update current bowler): " . $stmt_update_bowler->error);
    }
    $stmt_update_bowler->close();

    echo json_encode(["status" => "success", "message" => "Current bowler updated successfully", "match_id" => $match_id, "new_bowler" => $new_bowler]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Transaction failed: " . $e->getMessage()]);
}

$conn->close();

?>