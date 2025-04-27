<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "User not logged in"]);
    exit();
}

$conn = new mysqli("localhost", "root", "", "resource_tracker");

if ($conn->connect_error) {
    echo json_encode(["error" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

$result = $conn->query("SELECT id, name, available, borrowed FROM resources");

if (!$result) {
    echo json_encode(["error" => "Error fetching resources: " . $conn->error]);
    exit();
}

$resources = [];
while ($row = $result->fetch_assoc()) {
    $resources[] = $row;
}

$conn->close();

echo json_encode($resources);
?>
