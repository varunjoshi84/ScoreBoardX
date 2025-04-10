<?php 
    require __DIR__ . '/../middlewares/JWT.php';
    require __DIR__ . '/../controllers/UserController.php';
    $user = new UserController();
    $jwt = new JWT();
    header("Content-Type: application/json");
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    $secret_key = "ayush8920";
    if ($_SERVER["REQUEST_METHOD"] === "GET") {
        echo json_encode(["error" => "This Method is Not Allowed"]);
        exit;
    }
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $json = file_get_contents("php://input");
        $data = json_decode($json, true);
        if(!isset($data['token'])){
            echo json_encode(["error" => "Unauthorised Access"]);
            exit;
        }
        $payload = $jwt->decodeJWT($data['token']);
        $response = $user->getUser($payload['email']);
        echo json_encode($response);
    }
    if ($_SERVER["REQUEST_METHOD"] === "DELETE") {
        $json = file_get_contents("php://input");
        $data = json_decode($json, true);
        if(!isset($data['token'])){
            echo json_encode(["error" => "Unauthorised Access"]);
            exit;
        }
        $payload = $jwt->decodeJWT($data['token']);
        $response = $user->deleteUser($payload['email']);
        echo json_encode($response);
    }
?>