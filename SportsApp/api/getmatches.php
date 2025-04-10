<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
require_once __DIR__ . '/../config/Database.php';
$database = new Database();
$conn = $database->getConnection();
$updateQuery = "
    UPDATE matches 
    SET status = 'Live' 
    WHERE status = 'Upcoming' 
    AND match_date <= NOW()
";
$conn->query($updateQuery);
$sql = "SELECT * FROM matches ORDER BY match_date ASC";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    $matches = [];
    while ($row = $result->fetch_assoc()) {
        $matches[] = $row;
    }
    echo json_encode(["status" => "success", "matches" => $matches]);
} else {
    echo json_encode(["status" => "success", "matches" => []]);
}
?>
