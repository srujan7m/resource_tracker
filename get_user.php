<?php
session_start();
header('Content-Type: application/json');

if (isset($_SESSION['user_id'])) {
    echo json_encode([
        "id" => $_SESSION['user_id'],
        "username" => $_SESSION['username'] ?? null,
        "name" => $_SESSION['name'] ?? null,
        "email" => $_SESSION['email'] ?? null
    ]);
} else {
    echo json_encode(["error" => "User not logged in"]);
}
?>
