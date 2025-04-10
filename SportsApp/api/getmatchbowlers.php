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
    // 1. Get the identifier of the current batting team
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

    // 2. Determine the bowling team identifier
    $bowling_team_identifier = ($batting_team_identifier === 'A') ? 'B' : 'A';

    // 3. Get the distinct bowlers from the bowling_scores table for the bowling team
    $query_get_bowlers = "SELECT DISTINCT player_name
                            FROM bowling_scores
                            WHERE match_id = ? AND team_name = ?";
    $stmt_get_bowlers = $conn->prepare($query_get_bowlers);

    if (!$stmt_get_bowlers) {
        throw new Exception("Prepare failed (get bowlers of bowling team): " . $conn->error);
    }

    $stmt_get_bowlers->bind_param("is", $match_id, $bowling_team_identifier);
    $stmt_get_bowlers->execute();
    $result_get_bowlers = $stmt_get_bowlers->get_result();
    $bowlers = [];
    while ($row = $result_get_bowlers->fetch_assoc()) {
        $bowlers[] = $row;
    }
    $stmt_get_bowlers->close();

    echo json_encode(["status" => "success", "data" => $bowlers]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Failed to retrieve bowlers of the current bowling team: " . $e->getMessage()]);
}

$conn->close();

?>