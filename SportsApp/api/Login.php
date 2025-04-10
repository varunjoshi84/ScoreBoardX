<?php
    require __DIR__ . '/../controllers/AuthController.php';
    require __DIR__ . '/../middlewares/JWT.php';

    header("Content-Type: application/json");
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    
    $login = new LoginController();
    $jwt = new JWT();
    if ($_SERVER["REQUEST_METHOD"] === "GET") {
        echo json_encode(["error" => "This Method is Not Allowed"]);
        exit;
    }
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $json = file_get_contents("php://input");
        $data = json_decode($json, true);
        if (!isset($data["email"])) {
            echo json_encode(["error" => "Email is Required !!"]);
            exit;
        }
        if (!isset($data["password"])) {
            echo json_encode(["error" => "Password is Required !!"]);
            exit;
        }
        $response = $login->Login($data['email'],$data['password']);
        if(!isset($response['error'])){
            $payload = [
                "email" => $data['email'],
            ];
            $token = $jwt->createJWT($payload);
            echo json_encode([
                "message" => "Login Successful",
                "role"=> $response['role'],
                "token" => $token
            ]);
        }
        else{
            echo json_encode(['error'=>$response['error']]);
        }
    }
?>
