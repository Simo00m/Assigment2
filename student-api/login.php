<?php
require 'db.php';
require 'auth.php';

header("Content-Type: application/json");

$input = json_decode(file_get_contents("php://input"), true);

if (!isset($input['username'], $input['password'])) {
    http_response_code(400);
    echo json_encode(["error" => "Missing username or password"]);
    exit;
}

$username = $input['username'];
$password = $input['password'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user['password'])) {
    $token = generate_jwt($user);
    echo json_encode(["token" => $token]);
} else {
    http_response_code(401);
    echo json_encode(["error" => "Invalid credentials"]);
}
?>
