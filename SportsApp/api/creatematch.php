<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// The rest of your PHP code for handling POST requests
require_once __DIR__ . '/../config/Database.php';
$database = new Database();
$conn = $database->getConnection();
$data = json_decode(file_get_contents("php://input"));

if (
    isset($data->team_a) &&
    isset($data->team_b) &&
    isset($data->match_date) &&
    isset($data->location)
) {
    $team_a = $conn->real_escape_string($data->team_a);
    $team_b = $conn->real_escape_string($data->team_b);
    $location = $conn->real_escape_string($data->location);
    $date_input = $data->match_date;
    $date_obj = DateTime::createFromFormat('d-m-Y-H-i', $date_input);
    if (!$date_obj) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Invalid date format. Use DD-MM-YYYY-HH-MM."]);
        exit;
    }
    $match_date = $date_obj->format('Y-m-d H:i:s');

    $conn->begin_transaction(); // Start transaction to ensure both inserts succeed or fail together

    $sql_matches = "INSERT INTO matches (team_a, team_b, match_date, location)
                    VALUES ('$team_a', '$team_b', '$match_date', '$location')";

    if ($conn->query($sql_matches) === TRUE) {
        $match_id = $conn->insert_id; // Get the ID of the newly created match

        $sql_scores_a = "INSERT INTO scores (match_id, team_name, score, wickets, overs) VALUES ($match_id, '$team_a', 0, 0, 0.0)";
        $sql_scores_b = "INSERT INTO scores (match_id, team_name, score, wickets, overs) VALUES ($match_id, '$team_b', 0, 0, 0.0)";
        $sql_innings = "INSERT INTO innings (match_id, inning_number) VALUES ($match_id, 1)";

        if (
            $conn->query($sql_scores_a) === TRUE &&
            $conn->query($sql_scores_b) === TRUE &&
            $conn->query($sql_innings) === TRUE
        ) {
            $conn->commit();
            echo json_encode(["status" => "success", "message" => "Match created successfully with initial scores and innings."]);
        } else {
            $conn->rollback();
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Database error setting initial data: " . $conn->error]);
        }
    } else {
        $conn->rollback(); // Rollback the transaction if inserting match failed
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Database error creating match: " . $conn->error]);
    }
} else {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Missing required fields."]);
}

$conn->close();
?>