<?php
// Set headers for JSON response and allow CORS (for testing with Postman)
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

// Database connection details
$host = "localhost";
$dbname = "bvc_students";
$username = "root"; // Default WAMP/XAMPP username (update if changed)
$password = "";     // Default WAMP/XAMPP password (update if changed)

// Connect to the database
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed: " . $e->getMessage()]);
    exit();
}

// Get the HTTP method used in the request
$method = $_SERVER['REQUEST_METHOD'];

// Handle API routes
switch ($method) {
    case 'GET':
        // Route: /students - Fetch all students
        $stmt = $pdo->prepare("SELECT * FROM students");
        $stmt->execute();
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($students);
        break;

    case 'POST':
        // Route: /students - Add a new student
        $data = json_decode(file_get_contents("php://input"), true);
        
        // Validate input
        if (!isset($data['name']) || !isset($data['age'])) {
            http_response_code(400);
            echo json_encode(["error" => "Name and Age are required"]);
            exit();
        }

        $name = $data['name'];
        $age = $data['age'];

        $stmt = $pdo->prepare("INSERT INTO students (name, age) VALUES (:name, :age)");
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':age', $age);

        if ($stmt->execute()) {
            http_response_code(201);
            echo json_encode(["message" => "Student added successfully"]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Failed to add student"]);
        }
        break;

    case 'DELETE':
        // Route: /students - Delete a student by ID
        $data = json_decode(file_get_contents("php://input"), true);

        // Validate input
        if (!isset($data['id'])) {
            http_response_code(400);
            echo json_encode(["error" => "ID is required"]);
            exit();
        }

        $id = $data['id'];

        $stmt = $pdo->prepare("DELETE FROM students WHERE id = :id");
        $stmt->bindParam(':id', $id);

        if ($stmt->execute() && $stmt->rowCount() > 0) {
            echo json_encode(["message" => "Student deleted successfully"]);
        } else {
            http_response_code(404);
            echo json_encode(["error" => "Student not found"]);
        }
        break;

    default:
        // Invalid method
        http_response_code(405);
        echo json_encode(["error" => "Method not allowed"]);
        break;
}
?>