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

if (!isset($data->match_id) || !isset($data->striker) || !isset($data->non_striker) || !isset($data->current_bowler)) {
    http_response_code(400); // Bad Request
    echo json_encode(["status" => "error", "message" => "Missing required parameters: match_id, striker, non_striker, current_bowler"]);
    exit();
}

$match_id = $data->match_id;
$striker = trim($data->striker);
$non_striker = trim($data->non_striker);
$current_bowler = trim($data->current_bowler);

if ($striker === $non_striker) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Striker and Non-Striker cannot be the same player."]);
    exit();
}

$database = new Database();
$conn = $database->getConnection();

$conn->begin_transaction();

try {
    // 1. Check if toss info exists and get the team batting first
    $query_toss = "SELECT batting_first
                   FROM toss_info
                   WHERE match_id = ?";
    $stmt_toss = $conn->prepare($query_toss);
    if (!$stmt_toss) {
        throw new Exception("Prepare failed (toss info): " . $conn->error);
    }
    $stmt_toss->bind_param("i", $match_id);
    $stmt_toss->execute();
    $result_toss = $stmt_toss->get_result();
    $toss_info = $result_toss->fetch_assoc();
    $stmt_toss->close();

    if (!$toss_info || !isset($toss_info['batting_first'])) {
        throw new Exception("Toss information not found for match ID: " . $match_id);
    }

    $team_batting = $toss_info['batting_first'];

    // 2. Check if current players are already set for this match
    $check_query = "SELECT match_id FROM current_players WHERE match_id = ?";
    $check_stmt = $conn->prepare($check_query);
    if (!$check_stmt) {
        throw new Exception("Prepare failed (check current players): " . $conn->error);
    }
    $check_stmt->bind_param("i", $match_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        // Current players exist, perform an UPDATE
        $query_update_current = "UPDATE current_players
                                 SET striker = ?,
                                     non_striker = ?,
                                     current_bowler = ?,
                                     team_batting = ?,
                                     updated_at = NOW()
                                 WHERE match_id = ?";
        $stmt_update_current = $conn->prepare($query_update_current);
        if (!$stmt_update_current) {
            throw new Exception("Prepare failed (update current players): " . $conn->error);
        }
        $stmt_update_current->bind_param("ssssi", $striker, $non_striker, $current_bowler, $team_batting, $match_id);
        if (!$stmt_update_current->execute()) {
            throw new Exception("Execute failed (update current players): " . $stmt_update_current->error);
        }
        $stmt_update_current->close();
    } else {
        // Current players don't exist, perform an INSERT
        $query_insert_current = "INSERT INTO current_players (match_id, striker, non_striker, current_bowler, team_batting)
                                 VALUES (?, ?, ?, ?, ?)";
        $stmt_insert_current = $conn->prepare($query_insert_current);
        if (!$stmt_insert_current) {
            throw new Exception("Prepare failed (insert current players): " . $conn->error);
        }
        $stmt_insert_current->bind_param("issss", $match_id, $striker, $non_striker, $current_bowler, $team_batting);
        if (!$stmt_insert_current->execute()) {
            throw new Exception("Execute failed (insert current players): " . $stmt_insert_current->error);
        }
        $stmt_insert_current->close();
    }

    $conn->commit();
    echo json_encode(["status" => "success", "message" => "Current players and team batting set successfully", "team_batting" => $team_batting]);

} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Transaction failed: " . $e->getMessage()]);
}

$conn->close();

?>