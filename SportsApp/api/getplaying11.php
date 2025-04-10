<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
require_once __DIR__ . '/../config/Database.php';
$database = new Database();
$conn = $database->getConnection();
$data = json_decode(file_get_contents("php://input"));
if (!isset($data->match_id)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Missing match_id in request body."]);
    exit;
}
$match_id = intval($data->match_id);
$sql = "SELECT team, player_name, type FROM playing_eleven WHERE match_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $match_id);
$stmt->execute();
$result = $stmt->get_result();
$team_a = [];
$team_b = [];
while ($row = $result->fetch_assoc()) {
    $player = [
        "player_name" => $row['player_name'],
        "type" => $row['type']
    ];
    if ($row['team'] === 'A') {
        $team_a[] = $player;
    } else if ($row['team'] === 'B') {
        $team_b[] = $player;
    }
}
echo json_encode([
    "status" => "success",
    "match_id" => $match_id,
    "team_a" => $team_a,
    "team_b" => $team_b
]);
$stmt->close();
$conn->close();
?>
