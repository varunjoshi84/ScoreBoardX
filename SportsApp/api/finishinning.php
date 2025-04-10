<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../config/Database.php';
$database = new Database();
$conn = $database->getConnection();
$data = json_decode(file_get_contents("php://input"));

if (!isset($data->match_id)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Missing required parameter: match_id"]);
    exit();
}

$match_id = $conn->real_escape_string($data->match_id);

$conn->begin_transaction();

try {
    // 1. Get the current inning number
    $sql_get_current_inning = "SELECT inning_number, inning_id
                               FROM innings
                               WHERE match_id = ?
                               ORDER BY inning_number DESC
                               LIMIT 1";
    $stmt_get_current_inning = $conn->prepare($sql_get_current_inning);
    $stmt_get_current_inning->bind_param("i", $match_id);
    $stmt_get_current_inning->execute();
    $result_current_inning = $stmt_get_current_inning->get_result();

    if ($result_current_inning->num_rows === 0) {
        throw new Exception("No innings found for match ID: " . $match_id);
    }

    $row_current_inning = $result_current_inning->fetch_assoc();
    $current_inning = (int)$row_current_inning['inning_number'];
    $current_inning_id = (int)$row_current_inning['inning_id'];
    $stmt_get_current_inning->close();

    if ($current_inning === 1) {
        // 2. Delete current players record
        $sql_delete_current_players = "DELETE FROM current_players WHERE match_id = ?";
        $stmt_delete_current_players = $conn->prepare($sql_delete_current_players);
        $stmt_delete_current_players->bind_param("i", $match_id);
        if ($stmt_delete_current_players->execute()) {
            $stmt_delete_current_players->close();

            // 3. Update the existing inning record to inning 2
            $sql_update_inning = "UPDATE innings
                                  SET inning_number = 2
                                  WHERE inning_id = ?";
            $stmt_update_inning = $conn->prepare($sql_update_inning);
            $stmt_update_inning->bind_param("i", $current_inning_id);

            if ($stmt_update_inning->execute()) {
                $stmt_update_inning->close();

                // 4. Swap batting_first and bowling_first in toss_info
                $sql_get_toss = "SELECT batting_first, bowling_first FROM toss_info WHERE match_id = ?";
                $stmt_get_toss = $conn->prepare($sql_get_toss);
                $stmt_get_toss->bind_param("i", $match_id);
                $stmt_get_toss->execute();
                $result_get_toss = $stmt_get_toss->get_result();

                if ($result_get_toss->num_rows > 0) {
                    $toss = $result_get_toss->fetch_assoc();
                    $batting = $toss['batting_first'];
                    $bowling = $toss['bowling_first'];
                    $stmt_get_toss->close();

                    $sql_update_toss = "UPDATE toss_info SET batting_first = ?, bowling_first = ? WHERE match_id = ?";
                    $stmt_update_toss = $conn->prepare($sql_update_toss);
                    $stmt_update_toss->bind_param("ssi", $bowling, $batting, $match_id);
                    if (!$stmt_update_toss->execute()) {
                        $conn->rollback();
                        throw new Exception("Error swapping toss info: " . $stmt_update_toss->error);
                    }
                    $stmt_update_toss->close();
                } else {
                    throw new Exception("No toss_info found for match ID: " . $match_id);
                }

                // 5. Final commit
                $conn->commit();
                echo json_encode(["status" => "success", "message" => "Inning 1 finished. Toss info swapped, current players cleared, and inning updated to 2."]);
            } else {
                $conn->rollback();
                throw new Exception("Error updating inning: " . $stmt_update_inning->error);
            }
        } else {
            $conn->rollback();
            throw new Exception("Error deleting current players: " . $stmt_delete_current_players->error);
        }
    } elseif ($current_inning === 2) {
        // 4. Mark match status as complete
        $sql_mark_match_complete = "UPDATE matches SET status = 'completed' WHERE id = ?";
        $stmt_mark_match_complete = $conn->prepare($sql_mark_match_complete);
        $stmt_mark_match_complete->bind_param("i", $match_id);

        if ($stmt_mark_match_complete->execute()) {
            $stmt_mark_match_complete->close();
            $conn->commit();
            echo json_encode(["status" => "success", "message" => "Inning 2 finished. Match marked as completed."]);
        } else {
            $conn->rollback();
            throw new Exception("Error marking match as completed: " . $stmt_mark_match_complete->error);
        }
    } else {
        $conn->rollback();
        echo json_encode(["status" => "error", "message" => "Invalid inning number: " . $current_inning]);
    }
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
