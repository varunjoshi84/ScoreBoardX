<?php
    require __DIR__ . '/../middlewares/JWT.php';
    require __DIR__ . '/../controllers/AuthController.php';
    $register = new RegisterController();
    $jwt = new JWT();

    header("Content-Type: application/json");
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    
    $secret_key = "ayush8920";
    if ($_SERVER["REQUEST_METHOD"] === "GET") {
        echo json_encode(["error" => "This Method is Not Allowed"]);
        exit;
    }
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $json = file_get_contents("php://input");
        $data = json_decode($json, true);
        $required_fields = ["full_name", "email", "password", "role"];
        foreach ($required_fields as $field) {
            if (!isset($data[$field])) {
                echo json_encode(["error" => "$field is Required !!"]);
                exit;
            }
        }
        if(!($data['role']=='customer')){
            echo json_encode(["error"=>"This role is not allowed.."]);
            exit;
        }
        $password = password_hash($data['password'], PASSWORD_BCRYPT);
        $response = $register->Register(
            $data['full_name'],
            $data['email'],
            $password,
            $data['role'],
        );
        $payload = [
            "email" => $data['email'],
        ];
        $token = $jwt->createJWT($payload);
        if(!isset($response['error'])){
            $payload = [
                "email" => $data['email']
            ];
            echo json_encode([
                "message" => "Registration Successful",
                "token" => $token
            ]);
        }
        else{
            echo json_encode([ 'error'=>$response['error']]);
        }
    }
?>
