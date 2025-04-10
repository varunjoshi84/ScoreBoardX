<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
require_once __DIR__ . '/../middlewares/JWT.php';
require_once __DIR__ . '/../config/Database.php';
$jwt = new JWT();
$database = new Database();
$conn = $database->getConnection();
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $json = file_get_contents("php://input");
    $data = json_decode($json, true);
    if (!isset($data['token']) || !isset($data['currentPassword']) || !isset($data['newPassword'])) {
        http_response_code(400);
        echo json_encode(["error" => "Missing required fields: token, currentPassword, newPassword"]);
        exit();
    }
    $token = $data['token'];
    $currentPassword = $data['currentPassword'];
    $newPassword = $data['newPassword'];
    $payload = $jwt->decodeJWT($token);
    if (!$payload || !isset($payload['email'])) {
        http_response_code(401);
        echo json_encode(["error" => "Invalid or expired token"]);
        exit();
    }
    $email = $payload['email'];
    $sql = "SELECT id, password, role FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    if ($user && isset($user['role'])) {
        if (password_verify($currentPassword, $user['password'])) {
            $hashedNewPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $updateSql = "UPDATE users SET password = ? WHERE id = ?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param("si", $hashedNewPassword, $user['id']); // Use user ID for update
            if ($updateStmt->execute()) {
                echo json_encode(["message" => "Admin password updated successfully"]);
            } else {
                http_response_code(500);
                echo json_encode(["error" => "Failed to update admin password", "details" => $conn->error]);
            }
            $updateStmt->close();
        } else {
            http_response_code(401);
            echo json_encode(["error" => "Incorrect current password"]);
        }
    } else {
        http_response_code(403);
        echo json_encode(["error" => "Unauthorized: Only admins can change their password"]);
    }
} else {
    echo json_encode(["error" => "This Method is Not Allowed"]);
}
$conn->close();
?>