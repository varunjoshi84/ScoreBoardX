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

// Start transaction to ensure atomicity
$conn->begin_transaction();

try {
    // 1. Get the team batting identifier and current bowler
    $query_current_match_info = "SELECT cp.team_batting, cp.current_bowler
                                 FROM current_players cp
                                 WHERE cp.match_id = ?";
    $stmt_current_match_info = $conn->prepare($query_current_match_info);

    if (!$stmt_current_match_info) {
        throw new Exception("Prepare failed (get batting team and bowler): " . $conn->error);
    }

    $stmt_current_match_info->bind_param("i", $match_id);
    $stmt_current_match_info->execute();
    $result_current_match_info = $stmt_current_match_info->get_result();
    $current_match_data = $result_current_match_info->fetch_assoc();
    $stmt_current_match_info->close();

    if (!$current_match_data || !isset($current_match_data['team_batting']) || !isset($current_match_data['current_bowler'])) {
        throw new Exception("Could not retrieve batting team or current bowler for match ID: " . $match_id);
    }

    $team_batting_identifier = $current_match_data['team_batting'];
    $current_bowler = $current_match_data['current_bowler'];

    // 2. Get the name of the batting team
    $query_get_team_name = "SELECT team_a, team_b
                            FROM matches
                            WHERE id = ?";
    $stmt_get_team_name = $conn->prepare($query_get_team_name);

    if (!$stmt_get_team_name) {
        throw new Exception("Prepare failed (get team name): " . $conn->error);
    }

    $stmt_get_team_name->bind_param("i", $match_id);
    $stmt_get_team_name->execute();
    $result_get_team_name = $stmt_get_team_name->get_result();
    $match_info = $result_get_team_name->fetch_assoc();
    $stmt_get_team_name->close();

    if (!$match_info || !isset($match_info['team_a']) || !isset($match_info['team_b'])) {
        throw new Exception("Could not retrieve team names from matches table for match ID: " . $match_id);
    }

    $batting_team_name = '';
    if ($team_batting_identifier === 'A') {
        $batting_team_name = $match_info['team_a'];
    } elseif ($team_batting_identifier === 'B') {
        $batting_team_name = $match_info['team_b'];
    } else {
        throw new Exception("Invalid team batting identifier in current_players: " . $team_batting_identifier);
    }

    // 3. Update the score for the batting team in the scores table (+1 for wide)
    $query_update_scores = "UPDATE scores
                            SET score = score + 1
                            WHERE match_id = ? AND team_name = ?";
    $stmt_update_scores = $conn->prepare($query_update_scores);

    if (!$stmt_update_scores) {
        throw new Exception("Prepare failed (update scores table for wide ball): " . $conn->error);
    }

    $stmt_update_scores->bind_param("is", $match_id, $batting_team_name);
    if (!$stmt_update_scores->execute()) {
        throw new Exception("Execute failed (update scores table for wide ball): " . $stmt_update_scores->error);
    }
    $stmt_update_scores->close();

    // 4. Update the runs conceded for the current bowler in bowling_scores
    $query_update_bowler_runs = "UPDATE bowling_scores
                                 SET runs_conceded = runs_conceded + 1
                                 WHERE match_id = ? AND player_name = ?";
    $stmt_update_bowler_runs = $conn->prepare($query_update_bowler_runs);

    if (!$stmt_update_bowler_runs) {
        throw new Exception("Prepare failed (update bowler runs for wide ball): " . $conn->error);
    }

    $stmt_update_bowler_runs->bind_param("is", $match_id, $current_bowler);
    if (!$stmt_update_bowler_runs->execute()) {
        throw new Exception("Execute failed (update bowler runs for wide ball): " . $stmt_update_bowler_runs->error);
    }
    $stmt_update_bowler_runs->close();

    // Commit the transaction
    $conn->commit();
    echo json_encode(["status" => "success", "message" => "Wide ball recorded, team score and bowler runs updated", "runs_added" => 1, "batting_team" => $batting_team_name, "bowler" => $current_bowler]);

} catch (Exception $e) {
    // Rollback the transaction if any error occurred
    $conn->rollback();
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Transaction failed: " . $e->getMessage()]);
}

$conn->close();

?>