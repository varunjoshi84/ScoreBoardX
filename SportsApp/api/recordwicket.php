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

if (!isset($data->match_id) || !isset($data->batsman_out) || !isset($data->bowler) || !isset($data->new_batsman)) {
    http_response_code(400); // Bad Request
    echo json_encode(["status" => "error", "message" => "Missing required parameters: match_id, batsman_out, bowler, and new_batsman"]);
    exit();
}

$match_id = $data->match_id;
$batsman_out_name = trim($data->batsman_out);
$bowler_name = trim($data->bowler);
$new_batsman_name = trim($data->new_batsman);

if (empty($batsman_out_name) || empty($bowler_name) || empty($new_batsman_name)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Batsman out, bowler, and new batsman names cannot be empty"]);
    exit();
}

$database = new Database();
$conn = $database->getConnection();

// Start transaction to ensure atomicity
$conn->begin_transaction();

try {
    // 1. Validate if batsman_out exists in batting_scores for this match
    $query_validate_batsman_out = "SELECT COUNT(*) AS count
                                   FROM batting_scores
                                   WHERE match_id = ? AND player_name = ?";
    $stmt_validate_batsman_out = $conn->prepare($query_validate_batsman_out);
    $stmt_validate_batsman_out->bind_param("is", $match_id, $batsman_out_name);
    $stmt_validate_batsman_out->execute();
    $result_validate_batsman_out = $stmt_validate_batsman_out->get_result();
    $batsman_out_exists = $result_validate_batsman_out->fetch_assoc()['count'] > 0;
    $stmt_validate_batsman_out->close();

    if (!$batsman_out_exists) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Batsman out '" . $batsman_out_name . "' not found in batting scores for this match."]);
        exit();
    }

    // 2. Validate if bowler exists in bowling_scores for this match
    $query_validate_bowler = "SELECT COUNT(*) AS count
                                FROM bowling_scores
                                WHERE match_id = ? AND player_name = ?";
    $stmt_validate_bowler = $conn->prepare($query_validate_bowler);
    $stmt_validate_bowler->bind_param("is", $match_id, $bowler_name);
    $stmt_validate_bowler->execute();
    $result_validate_bowler = $stmt_validate_bowler->get_result();
    $bowler_exists = $result_validate_bowler->fetch_assoc()['count'] > 0;
    $stmt_validate_bowler->close();

    if (!$bowler_exists) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Bowler '" . $bowler_name . "' not found in bowling scores for this match."]);
        exit();
    }

    // 3. Validate if new_batsman exists in batting_scores for this match (or is a new player to be added)
    // For simplicity, let's assume the new batsman must already be in batting_scores
    $query_validate_new_batsman = "SELECT COUNT(*) AS count
                                    FROM batting_scores
                                    WHERE match_id = ? AND player_name = ?";
    $stmt_validate_new_batsman = $conn->prepare($query_validate_new_batsman);
    $stmt_validate_new_batsman->bind_param("is", $match_id, $new_batsman_name);
    $stmt_validate_new_batsman->execute();
    $result_validate_new_batsman = $stmt_validate_new_batsman->get_result();
    $new_batsman_exists = $result_validate_new_batsman->fetch_assoc()['count'] > 0;
    $stmt_validate_new_batsman->close();

    if (!$new_batsman_exists) {
        // Consider if you want to allow adding a new batsman here. If so, you'd need to insert into batting_scores.
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "New batsman '" . $new_batsman_name . "' not found in batting scores for this match."]);
        exit();
    }

    // 4. Update is_out for the batsman_out in batting_scores
    $query_update_batsman_out = "UPDATE batting_scores
                                  SET is_out = 1
                                  WHERE match_id = ? AND player_name = ?";
    $stmt_update_batsman_out = $conn->prepare($query_update_batsman_out);
    $stmt_update_batsman_out->bind_param("is", $match_id, $batsman_out_name);
    if (!$stmt_update_batsman_out->execute()) {
        throw new Exception("Execute failed (update batsman out status): " . $stmt_update_batsman_out->error);
    }
    $stmt_update_batsman_out->close();

    // 5. Increment wickets_taken for the bowler in bowling_scores
    $query_increment_bowler_wickets = "UPDATE bowling_scores
                                       SET wickets_taken = wickets_taken + 1
                                       WHERE match_id = ? AND player_name = ?";
    $stmt_increment_bowler_wickets = $conn->prepare($query_increment_bowler_wickets);
    $stmt_increment_bowler_wickets->bind_param("is", $match_id, $bowler_name);
    if (!$stmt_increment_bowler_wickets->execute()) {
        throw new Exception("Execute failed (increment bowler wickets): " . $stmt_increment_bowler_wickets->error);
    }
    $stmt_increment_bowler_wickets->close();

    // 6. Update current striker to the new batsman
    $query_update_striker = "UPDATE current_players
                              SET striker = ?
                              WHERE match_id = ? AND striker = ?";
    $stmt_update_striker = $conn->prepare($query_update_striker);
    $stmt_update_striker->bind_param("sis", $new_batsman_name, $match_id, $batsman_out_name);
    $stmt_update_striker->execute();
    $stmt_update_striker->close();

    // If the out batsman was the non-striker, update the non-striker
    $query_update_non_striker = "UPDATE current_players
                                  SET non_striker = ?
                                  WHERE match_id = ? AND non_striker = ?";
    $stmt_update_non_striker = $conn->prepare($query_update_non_striker);
    $stmt_update_non_striker->bind_param("sis", $new_batsman_name, $match_id, $batsman_out_name);
    $stmt_update_non_striker->execute();
    $stmt_update_non_striker->close();

    // Commit the transaction
    $conn->commit();
    echo json_encode(["status" => "success", "message" => "Wicket recorded successfully, batsman out, bowler wickets updated, and new batsman set.", "match_id" => $match_id, "batsman_out" => $batsman_out_name, "bowler" => $bowler_name, "new_batsman" => $new_batsman_name]);

} catch (Exception $e) {
    // Rollback the transaction if any error occurred
    $conn->rollback();
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Transaction failed: " . $e->getMessage()]);
}

$conn->close();

?>