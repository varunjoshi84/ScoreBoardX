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
    // 1. Get the current striker, non-striker, and bowler from current_players
    $query_current_players = "SELECT striker, non_striker, current_bowler
                              FROM current_players
                              WHERE match_id = ?";
    $stmt_current_players = $conn->prepare($query_current_players);

    if (!$stmt_current_players) {
        throw new Exception("Prepare failed (get current players): " . $conn->error);
    }

    $stmt_current_players->bind_param("i", $match_id);
    $stmt_current_players->execute();
    $result_current_players = $stmt_current_players->get_result();
    $current_players = $result_current_players->fetch_assoc();
    $stmt_current_players->close();

    if (!$current_players || !isset($current_players['striker']) || !isset($current_players['non_striker']) || !isset($current_players['current_bowler'])) {
        throw new Exception("Could not retrieve current striker, non-striker, or bowler for match ID: " . $match_id);
    }

    $striker_name = $current_players['striker'];
    $non_striker_name = $current_players['non_striker'];
    $bowler_name = $current_players['current_bowler'];

    $player_scores = [];

    // 2. Get the striker's batting score
    $query_striker_score = "SELECT runs_scored, balls_faced
                            FROM batting_scores
                            WHERE match_id = ? AND player_name = ?";
    $stmt_striker_score = $conn->prepare($query_striker_score);
    $stmt_striker_score->bind_param("is", $match_id, $striker_name);
    $stmt_striker_score->execute();
    $result_striker_score = $stmt_striker_score->get_result();
    $striker_score = $result_striker_score->fetch_assoc();
    $stmt_striker_score->close();

    $player_scores['striker'] = [
        'name' => $striker_name,
        'runs_scored' => $striker_score['runs_scored'] ?? 0,
        'balls_faced' => $striker_score['balls_faced'] ?? 0,
    ];

    // 3. Get the non-striker's batting score
    $query_non_striker_score = "SELECT runs_scored, balls_faced
                                FROM batting_scores
                                WHERE match_id = ? AND player_name = ?";
    $stmt_non_striker_score = $conn->prepare($query_non_striker_score);
    $stmt_non_striker_score->bind_param("is", $match_id, $non_striker_name);
    $stmt_non_striker_score->execute();
    $result_non_striker_score = $stmt_non_striker_score->get_result();
    $non_striker_score = $result_non_striker_score->fetch_assoc();
    $stmt_non_striker_score->close();

    $player_scores['non_striker'] = [
        'name' => $non_striker_name,
        'runs_scored' => $non_striker_score['runs_scored'] ?? 0,
        'balls_faced' => $non_striker_score['balls_faced'] ?? 0,
    ];

    // 4. Get the bowler's bowling score
    $query_bowler_score = "SELECT overs_bowled, runs_conceded, wickets_taken
                             FROM bowling_scores
                             WHERE match_id = ? AND player_name = ?";
    $stmt_bowler_score = $conn->prepare($query_bowler_score);
    $stmt_bowler_score->bind_param("is", $match_id, $bowler_name);
    $stmt_bowler_score->execute();
    $result_bowler_score = $stmt_bowler_score->get_result();
    $bowler_score = $result_bowler_score->fetch_assoc();
    $stmt_bowler_score->close();

    $player_scores['bowler'] = [
        'name' => $bowler_name,
        'overs_bowled' => number_format($bowler_score['overs_bowled'] ?? 0, 1),
        'runs_conceded' => $bowler_score['runs_conceded'] ?? 0,
        'wickets_taken' => $bowler_score['wickets_taken'] ?? 0,
    ];

    echo json_encode(["status" => "success", "data" => $player_scores]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Failed to retrieve current player scores: " . $e->getMessage()]);
}

$conn->close();

?>