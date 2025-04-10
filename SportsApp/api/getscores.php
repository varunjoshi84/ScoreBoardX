<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../config/Database.php';
$database = new Database();
$conn = $database->getConnection();
$data = json_decode(file_get_contents("php://input"));

if (isset($data->match_id)) {
    $match_id = $conn->real_escape_string($data->match_id);

    $sql = "SELECT team_name, score, wickets, overs
            FROM scores
            WHERE match_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $match_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $scores = [];
    while ($row = $result->fetch_assoc()) {
        $scores[] = $row;
    }

    if (!empty($scores)) {
        // Structure the response by team name for easier access on the frontend
        $response = [
            'status' => 'success',
            'scores' => []
        ];
        foreach ($scores as $score_data) {
            $response['scores'][$score_data['team_name']] = [
                'score' => (int)$score_data['score'],
                'wickets' => $score_data['wickets'] !== null ? (int)$score_data['wickets'] : null,
                'overs' => $score_data['overs'] !== null ? (float)$score_data['overs'] : null
            ];
        }
        echo json_encode($response);
    } else {
        http_response_code(404);
        echo json_encode(["status" => "error", "message" => "Scores not found for match ID: " . $match_id]);
    }

    $stmt->close();
} else {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Missing 'match_id' in the request body"]);
}

$conn->close();
?>