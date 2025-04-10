<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

require_once __DIR__ . '/../config/Database.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$database = new Database();
$conn = $database->getConnection();
$data = json_decode(file_get_contents("php://input"));

if (!isset($data->match_id) || !isset($data->team_a) || !isset($data->team_b)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Missing required fields."]);
    exit;
}

$match_id = intval($data->match_id);

// Begin MySQL transaction
$conn->begin_transaction();

// Insert into playing_eleven
$stmt_playing_eleven = $conn->prepare("INSERT INTO playing_eleven (match_id, team, player_name, type) VALUES (?, ?, ?, ?)");
if (!$stmt_playing_eleven) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Prepare failed (playing_eleven): " . $conn->error]);
    $conn->rollback();
    exit;
}
foreach ($data->team_a as $player) {
    $team = 'A';
    $player_name = $player->player_name;
    $type = $player->type;
    $stmt_playing_eleven->bind_param("isss", $match_id, $team, $player_name, $type);
    $stmt_playing_eleven->execute();
}

foreach ($data->team_b as $player) {
    $team = 'B';
    $player_name = $player->player_name;
    $type = $player->type;
    $stmt_playing_eleven->bind_param("isss", $match_id, $team, $player_name, $type);
    $stmt_playing_eleven->execute();
}

$stmt_playing_eleven->close();

// Insert into batting_scores
$stmt_batting = $conn->prepare("INSERT INTO batting_scores (match_id, team_name, player_name) VALUES (?, ?, ?)");
if (!$stmt_batting) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Prepare failed (batting_scores): " . $conn->error]);
    $conn->rollback();
    exit;
}

foreach ($data->team_a as $player) {
    $team = 'A';
    $player_name = $player->player_name;
    $stmt_batting->bind_param("iss", $match_id, $team, $player_name);
    $stmt_batting->execute();
}
foreach ($data->team_b as $player) {
    $team = 'B';
    $player_name = $player->player_name;
    $stmt_batting->bind_param("iss", $match_id, $team, $player_name);
    $stmt_batting->execute();
}

$stmt_batting->close();

// Insert into bowling_scores (only for bowlers and all-rounders)
$stmt_bowling = $conn->prepare("INSERT INTO bowling_scores (match_id, team_name, player_name) VALUES (?, ?, ?)");
if (!$stmt_bowling) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Prepare failed (bowling_scores): " . $conn->error]);
    $conn->rollback();
    exit;
}
foreach ($data->team_a as $player) {
    if ($player->type === 'bowler' || $player->type === 'allrounder') {
        $team = 'A';
        $player_name = $player->player_name;
        $stmt_bowling->bind_param("iss", $match_id, $team, $player_name);
        $stmt_bowling->execute();
    }
}
foreach ($data->team_b as $player) {
    if ($player->type === 'bowler' || $player->type === 'allrounder') {
        $team = 'B';
        $player_name = $player->player_name;
        $stmt_bowling->bind_param("iss", $match_id, $team, $player_name);
        $stmt_bowling->execute();
    }
}

$stmt_bowling->close();

// Commit transaction
$conn->commit();

echo json_encode(["status" => "success", "message" => "Data inserted successfully."]);
?>
