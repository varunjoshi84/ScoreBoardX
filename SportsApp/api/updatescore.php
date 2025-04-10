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

if (!isset($data->match_id) || !isset($data->runs)) {
    http_response_code(400); // Bad Request
    echo json_encode(["status" => "error", "message" => "Missing required parameters: match_id and runs"]);
    exit();
}

$match_id = $data->match_id;
$runs = intval($data->runs); // Ensure runs is an integer

if ($runs < 0) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Runs cannot be negative"]);
    exit();
}

$database = new Database();
$conn = $database->getConnection();

// Start transaction to ensure atomicity
$conn->begin_transaction();

try {
    // 1. Get the team batting identifier from current_players
    $query_team_batting_identifier = "SELECT team_batting
                                       FROM current_players
                                       WHERE match_id = ?";
    $stmt_team_batting_identifier = $conn->prepare($query_team_batting_identifier);

    if (!$stmt_team_batting_identifier) {
        throw new Exception("Prepare failed (get team batting identifier): " . $conn->error);
    }

    $stmt_team_batting_identifier->bind_param("i", $match_id);
    $stmt_team_batting_identifier->execute();
    $result_team_batting_identifier = $stmt_team_batting_identifier->get_result();
    $team_batting_row = $result_team_batting_identifier->fetch_assoc();
    $stmt_team_batting_identifier->close();

    if (!$team_batting_row || !isset($team_batting_row['team_batting'])) {
        throw new Exception("Could not retrieve the batting team identifier for match ID: " . $match_id);
    }

    $team_batting_identifier = $team_batting_row['team_batting'];

    // 2. Get the name of the batting team from the matches table
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
    $query_get_overs = "SELECT overs from scores WHERE match_id = ? and team_name = ?";
    $stmt_get_overs = $conn->prepare($query_get_overs);
    $stmt_get_overs->bind_param("is", $match_id,$batting_team_name);
    $stmt_get_overs->execute();
    $result_get_overs = $stmt_get_overs->get_result();
    $over_info = $result_get_overs->fetch_assoc();
    $stmt_get_overs->close();

    if (!$over_info || !isset($over_info['overs'])) {
        throw new Exception("Could not retrieve over information for the batting team.");
    }

    $overs = $over_info['overs'];
    if(($overs*10)%10 == 5){
        $overs = floor($overs)+1;
    }
    else{
        $overs = $overs+0.1;
    }

    // 3. Update the score for the batting team in the scores table using match_id and team name
    $query_update_scores = "UPDATE scores
                            SET score = score + ?,
                                overs = ?
                            WHERE match_id = ? AND team_name = ?";
    $stmt_update_scores = $conn->prepare($query_update_scores);

    if (!$stmt_update_scores) {
        throw new Exception("Prepare failed (update scores table): " . $conn->error);
    }

    $stmt_update_scores->bind_param("idis", $runs, $overs, $match_id, $batting_team_name);
    if (!$stmt_update_scores->execute()) {
        throw new Exception("Execute failed (update scores table): " . $stmt_update_scores->error);
    }
    $stmt_update_scores->close();

    // 4. Get the current striker for the match
    $query_striker = "SELECT striker,current_bowler
                      FROM current_players
                      WHERE match_id = ?";
    $stmt_striker = $conn->prepare($query_striker);

    if (!$stmt_striker) {
        throw new Exception("Prepare failed (get striker): " . $conn->error);
    }

    $stmt_striker->bind_param("i", $match_id);
    $stmt_striker->execute();
    $result_striker = $stmt_striker->get_result();
    $striker_row = $result_striker->fetch_assoc();
    $stmt_striker->close();
    $current_bowler = $striker_row['current_bowler'];
    $query_bowler = "SELECT *
                      FROM bowling_scores
                      WHERE match_id = ? and player_name = ?";
    $stmt_bowler = $conn->prepare($query_bowler);

    if (!$stmt_bowler) {
        throw new Exception("Prepare failed (get striker): " . $conn->error);
    }

    $stmt_bowler->bind_param("is", $match_id, $current_bowler);
    $stmt_bowler->execute();
    $result_bowler = $stmt_bowler->get_result();
    $bowler_row = $result_bowler->fetch_assoc();
    $stmt_bowler->close();

    if ($striker_row && isset($striker_row['striker'])) {
        $current_striker = $striker_row['striker'];

        // 5. Update the batting score for the current striker
        // Check if a batting score entry exists for this striker and match
        $query_check_batting = "SELECT id
                                FROM batting_scores
                                WHERE match_id = ? AND player_name = ?";
        $stmt_check_batting = $conn->prepare($query_check_batting);
        $stmt_check_batting->bind_param("is", $match_id, $current_striker);
        $stmt_check_batting->execute();
        $result_check_batting = $stmt_check_batting->get_result();
        $bowler_overs = $bowler_row['overs_bowled'];
        if(($bowler_overs*10)%10 == 5){
            $bowler_overs = floor($bowler_overs)+1;
        }
        else{
            $bowler_overs = $bowler_overs+0.1;
        }

        if ($result_check_batting->num_rows > 0) {
            $query_update_batting = "UPDATE batting_scores
                                     SET runs_scored = runs_scored + ?,
                                         balls_faced = balls_faced + 1
                                     WHERE match_id = ? AND player_name = ?";
            $stmt_update_batting = $conn->prepare($query_update_batting);
            $stmt_update_batting->bind_param("iis", $runs, $match_id, $current_striker);
            if (!$stmt_update_batting->execute()) {
                throw new Exception("Execute failed (update batting): " . $stmt_update_batting->error);
            }
            $stmt_update_batting->close();
            $query_update_bowling = "UPDATE bowling_scores
                                     SET runs_conceded = runs_conceded + ?,
                                         overs_bowled = ?
                                     WHERE match_id = ? AND player_name = ?";
            $stmt_update_bowling = $conn->prepare($query_update_bowling);
            $stmt_update_bowling->bind_param("idis", $runs, $bowler_overs, $match_id, $current_bowler);
            if (!$stmt_update_bowling->execute()) {
                throw new Exception("Execute failed (update batting): " . $stmt_update_batting->error);
            }
            $stmt_update_bowling->close();
        } else {
            // Insert new batting score entry
            $query_insert_batting = "INSERT INTO batting_scores (match_id, player_name, runs_scored, balls_faced)
                                     VALUES (?, ?, ?, 1)";
            $stmt_insert_batting = $conn->prepare($query_insert_batting);
            $stmt_insert_batting->bind_param("isi", $match_id, $current_striker, $runs);
            if (!$stmt_insert_batting->execute()) {
                throw new Exception("Execute failed (insert batting): " . $stmt_insert_batting->error);
            }
            $stmt_insert_batting->close();
        }
        $result_check_batting->free(); // Free the result set
    } else {
        throw new Exception("Could not retrieve current striker for match ID: " . $match_id);
    }

    // Commit the transaction if all operations were successful
    $conn->commit();
    echo json_encode(["status" => "success", "message" => "Score updated successfully for team and batsman", "runs_added" => $runs, "batting_team" => $batting_team_name, "striker" => $current_striker ?? null]);

} catch (Exception $e) {
    // Rollback the transaction if any error occurred
    $conn->rollback();
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Transaction failed: " . $e->getMessage()]);
}

$conn->close();

?>