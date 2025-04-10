<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/../config/Database.php';
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(["status" => "error", "message" => "Only POST requests are allowed"]);
    exit();
}

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->match_id) || !isset($data->toss_winner) || !isset($data->batting_first)) {
    http_response_code(400); // Bad Request
    echo json_encode(["status" => "error", "message" => "Missing required parameters: match_id, toss_winner, batting_first"]);
    exit();
}

$match_id = $data->match_id;
$toss_winner = strtoupper($data->toss_winner); // Ensure uppercase for ENUM comparison
$batting_first = strtoupper($data->batting_first); // Ensure uppercase for ENUM comparison

// Determine bowling_first based on toss_winner and batting_first
$bowling_first = ($toss_winner === $batting_first) ? ($toss_winner === 'A' ? 'B' : 'A') : $toss_winner;

// Validate ENUM values
if (!in_array($toss_winner, ['A', 'B']) || !in_array($batting_first, ['A', 'B']) || !in_array($bowling_first, ['A', 'B'])) {
    http_response_code(400); // Bad Request
    echo json_encode(["status" => "error", "message" => "Invalid value for toss_winner or batting_first (must be 'A' or 'B')"]);
    exit();
}

$database = new Database();
$conn = $database->getConnection();

// Check if toss info already exists for this match
$check_query = "SELECT match_id FROM toss_info WHERE match_id = ?";
$check_stmt = $conn->prepare($check_query);

if (!$check_stmt) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Prepare failed (check): " . $conn->error]);
    $conn->close();
    exit();
}

$check_stmt->bind_param("i", $match_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    // Toss info already exists, perform an UPDATE
    $query = "UPDATE toss_info
              SET toss_winner = ?,
                  batting_first = ?,
                  bowling_first = ?
              WHERE match_id = ?";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Prepare failed (update): " . $conn->error]);
        $conn->close();
        exit();
    }

    $stmt->bind_param("sssi", $toss_winner, $batting_first, $bowling_first, $match_id);
} else {
    // Toss info doesn't exist, perform an INSERT
    $query = "INSERT INTO toss_info (match_id, toss_winner, batting_first, bowling_first)
              VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Prepare failed (insert): " . $conn->error]);
        $conn->close();
        exit();
    }

    $stmt->bind_param("isss", $match_id, $toss_winner, $batting_first, $bowling_first);
}

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Toss information saved successfully"]);
} else {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Error saving toss information: " . $stmt->error]);
}

$stmt->close();
$check_stmt->close();
$conn->close();

?>