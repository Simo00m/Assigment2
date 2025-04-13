<?php
require 'db.php';

header("Content-Type: application/json");

$input = json_decode(file_get_contents("php://input"), true);

if (!isset($input['username'], $input['password'], $input['role'])) {
    http_response_code(400);
    echo json_encode(["error" => "Missing required fields"]);
    exit;
}

$username = $input['username'];
$password = password_hash($input['password'], PASSWORD_DEFAULT);
$role = strtolower($input['role']);

if (!in_array($role, ['student', 'instructor'])) {
    http_response_code(400);
    echo json_encode(["error" => "Role must be 'student' or 'instructor'"]);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    $stmt->execute([$username, $password, $role]);

    echo json_encode(["message" => "User registered successfully"]);
} catch (PDOException $e) {
    http_response_code(409);
    echo json_encode(["error" => "Username already exists"]);
}
?>
