<?php
header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];

$dataFile = "students.json";

function readData(){
	global $dataFile;
	if (file_exists($dataFile)){
		return json_decode(file_get_contents($dataFile), true) ?: [];
	}
	return [];
}

function addData($data){
	global $dataFile;
	file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT));
}

function deleteData($id){
	$dataArr = readData();
	$newArr = array_filter($dataArr, function($temp) use($id){
		return $temp["id"] != $id;
	});
	addData(array_values($newArr));
	if (count($dataArr) == count($newArr)){
		return false;
	}
	return true;
}

function getNextId($data) {
    if (!empty($data)){
		return max(array_column($data, 'id')) + 1;
	}
	else return 1;
}

if ($method === "GET") {
    echo json_encode(["All students" => readData()]);
	exit;
}

if ($method === "POST") {
    $input = json_decode(file_get_contents("php://input"), true);
	
	if (!isset($input["name"],$input["age"])){
		http_response_code(400);
		echo json_encode(["error" => "Missing parameters!"]);
		exit;
	}

	$name = $input["name"];
	$age = $input["age"];
	$data = readData();

	$newEntry = [
		"id" => getNextId($data),
		"name" => $input["name"],
		"age" => $input["age"]
	];

	$data[] = $newEntry;
	addData($data);

	echo json_encode(["new_student" => $newEntry]);
	exit;
}

if ($method === "DELETE"){
	$input = json_decode(file_get_contents("php://input"), true);

	if (!isset($input["id"])){
		http_response_code(400);
		echo json_encode(["error" => "Missing student ID"]);
		exit;
	}
	$id = $input["id"];
	if(deleteData($id)){
		echo json_encode(["message" => "Student entry deleted successfully!"]);
	}
	else {
		http_response_code(404);
        echo json_encode(["error" => "Student ID not found."]);
	}
}

?>