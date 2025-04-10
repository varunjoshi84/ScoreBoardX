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
    echo json_encode(["status" => "error", "message" => "Missing required parameter: match_id in the request body"]);
    exit();
}

$match_id = $data->match_id;

$database = new Database();
$conn = $database->getConnection();

try {
    // 1. Get the current batting team identifier
    $query_batting_team = "SELECT team_batting
                           FROM current_players
                           WHERE match_id = ?";
    $stmt_batting_team = $conn->prepare($query_batting_team);
    if (!$stmt_batting_team) {
        throw new Exception("Prepare failed (get batting team): " . $conn->error);
    }
    $stmt_batting_team->bind_param("i", $match_id);
    $stmt_batting_team->execute();
    $result_batting_team = $stmt_batting_team->get_result();
    $batting_team_row = $result_batting_team->fetch_assoc();
    $stmt_batting_team->close();

    if (!$batting_team_row || !isset($batting_team_row['team_batting'])) {
        throw new Exception("Could not retrieve the batting team identifier for match ID: " . $match_id);
    }

    $batting_team_identifier = $batting_team_row['team_batting'];

    // 2. Get current striker and non-striker
    $query_current_players = "SELECT striker, non_striker
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

    $striker = $current_players['striker'] ?? null;
    $non_striker = $current_players['non_striker'] ?? null;

    // 3. Get not-out batsmen of the batting team (excluding current players)
    $query_not_out_batsmen = "SELECT bs.player_name
                              FROM batting_scores bs
                              WHERE bs.match_id = ? AND bs.team_name = ? AND bs.is_out = 0
                              AND bs.player_name NOT IN (?, ?)";
    $stmt_not_out_batsmen = $conn->prepare($query_not_out_batsmen);
    if (!$stmt_not_out_batsmen) {
        throw new Exception("Prepare failed (get not out batsmen): " . $conn->error);
    }
    $stmt_not_out_batsmen->bind_param("isss", $match_id, $batting_team_identifier, $striker, $non_striker);
    $stmt_not_out_batsmen->execute();
    $result_not_out_batsmen = $stmt_not_out_batsmen->get_result();
    $not_out_batsmen = [];
    while ($row = $result_not_out_batsmen->fetch_assoc()) {
        $not_out_batsmen[] = $row;
    }
    $stmt_not_out_batsmen->close();

    echo json_encode(["status" => "success", "data" => $not_out_batsmen]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Failed to retrieve not-out batsmen: " . $e->getMessage()]);
} finally {
    if ($conn) {
        $conn->close();
    }
}
?>