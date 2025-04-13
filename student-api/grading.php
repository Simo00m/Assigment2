<?php
require 'db.php';
require 'auth.php';

header("Content-Type: application/json");

// Verify token and get user info
$user = verify_jwt();

// Only instructors can grade
if ($user->role !== 'instructor') {
    http_response_code(403);
    echo json_encode(["error" => "Only instructors can perform this action"]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents("php://input"), true);

// Handle POST: Add or update grade
if ($method === "POST") {
    if (!isset($input['student_id'], $input['course'], $input['grade'])) {
        http_response_code(400);
        echo json_encode(["error" => "Missing student_id, course, or grade"]);
        exit;
    }

    $student_id = $input['student_id'];
    $course_name = $input['course'];
    $grade = $input['grade'];

    try {
        // Insert course if it doesn't exist
        $stmt = $pdo->prepare("INSERT IGNORE INTO courses (name) VALUES (?)");
        $stmt->execute([$course_name]);

        // Get course ID
        $stmt = $pdo->prepare("SELECT id FROM courses WHERE name = ?");
        $stmt->execute([$course_name]);
        $course_id = $stmt->fetch()['id'];

        // Insert or update enrollment
        $stmt = $pdo->prepare("
            INSERT INTO enrollments (student_id, course_id, grade)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE grade = VALUES(grade)
        ");
        $stmt->execute([$student_id, $course_id, $grade]);

        echo json_encode(["message" => "Grade assigned successfully"]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => "Database error: " . $e->getMessage()]);
    }
    exit;
}

// Handle DELETE: Remove course/grade entry
if ($method === "DELETE") {
    if (!isset($input['student_id'], $input['course'])) {
        http_response_code(400);
        echo json_encode(["error" => "Missing student_id or course"]);
        exit;
    }

    $student_id = $input['student_id'];
    $course_name = $input['course'];

    try {
        // Get course ID
        $stmt = $pdo->prepare("SELECT id FROM courses WHERE name = ?");
        $stmt->execute([$course_name]);
        $course = $stmt->fetch();

        if (!$course) {
            http_response_code(404);
            echo json_encode(["error" => "Course not found"]);
            exit;
        }

        $course_id = $course['id'];

        // Delete enrollment
        $stmt = $pdo->prepare("DELETE FROM enrollments WHERE student_id = ? AND course_id = ?");
        $stmt->execute([$student_id, $course_id]);

        echo json_encode(["message" => "Enrollment deleted successfully"]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => "Database error: " . $e->getMessage()]);
    }
    exit;
}

http_response_code(405);
echo json_encode(["error" => "Invalid HTTP method"]);
?>
