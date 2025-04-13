<?php
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

require_once __DIR__ . '/vendor/autoload.php';

$jwt_secret = "SecretKey";

function generate_jwt($user) {
    global $jwt_secret;

    $payload = [
        "id" => $user["id"],
        "username" => $user["username"],
        "role" => $user["role"],
        "exp" => time() + 3600  // Token expires in 1 hour
    ];

    return JWT::encode($payload, $jwt_secret, 'HS256');
}

function verify_jwt() {
    global $jwt_secret;

    $headers = getallheaders();
    if (!isset($headers['Authorization'])) {
        http_response_code(401);
        echo json_encode(["error" => "Missing Authorization header"]);
        exit;
    }

    $authHeader = $headers['Authorization'];
    $token = str_replace("Bearer ", "", $authHeader);

    try {
        return JWT::decode($token, new Key($jwt_secret, 'HS256'));
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(["error" => "Invalid token: " . $e->getMessage()]);
        exit;
    }
}
?>
