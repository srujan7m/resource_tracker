<?php
include "db.php";
$user_id = $_POST['user_id'];
$resource_id = $_POST['resource_id'];
$now = date("Y-m-d H:i:s");

$sql = "INSERT INTO borrow_log (user_id, resource_id, borrowed_at)
        VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iis", $user_id, $resource_id, $now);
$stmt->execute();

$conn->query("UPDATE resources SET status = 'borrowed' WHERE id = $resource_id");

echo "Resource borrowed!";
?>
