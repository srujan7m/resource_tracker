<?php
session_start();
header('Content-Type: application/json');

$log_file = 'debug_log.txt';
function writeLog($message) {
    global $log_file;
    file_put_contents($log_file, date('[Y-m-d H:i:s] ') . $message . PHP_EOL, FILE_APPEND);
}

writeLog('Session data: ' . json_encode($_SESSION));
writeLog('Raw input: ' . file_get_contents('php://input'));

if (!isset($_SESSION['user_id'])) {
    $error = "User not logged in";
    writeLog('Error: ' . $error);
    echo json_encode(["error" => $error]);
    exit();
}

$user_id = $_SESSION['user_id'];
writeLog('User ID: ' . $user_id);

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data) {
    $error = "Invalid JSON data";
    writeLog('Error: ' . $error . ' - Input: ' . $json);
    echo json_encode(["error" => $error]);
    exit();
}

writeLog('Parsed data: ' . json_encode($data));

require_once 'db.php';

if ($conn->connect_error) {
    $error = "Database connection failed: " . $conn->connect_error;
    writeLog('Error: ' . $error);
    echo json_encode(["error" => $error]);
    exit();
}

$resource_id = isset($data['resource_id']) ? intval($data['resource_id']) : 0;
$action = isset($data['action']) ? $conn->real_escape_string($data['action']) : '';
writeLog('Action: ' . $action . ', Resource ID: ' . $resource_id);

if (!$resource_id || !$action) {
    $error = "Invalid parameters";
    writeLog('Error: ' . $error);
    echo json_encode(["error" => $error]);
    exit();
}

if ($action === 'borrow') {
    $stmt = $conn->prepare("SELECT id, name, status FROM resources WHERE id = ?");
    $stmt->bind_param("i", $resource_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $resource = $result->fetch_assoc();
    $stmt->close();

    if (!$resource) {
        $error = "Resource not found";
        writeLog('Error: ' . $error);
        echo json_encode(["error" => $error]);
        exit();
    }

    if ($resource['status'] === 'borrowed') {
        $error = "Resource is not available";
        writeLog('Error: ' . $error);
        echo json_encode(["error" => $error]);
        exit();
    }

    $stmt = $conn->prepare("SELECT COUNT(*) AS active_borrows FROM borrow_log WHERE user_id = ? AND resource_id = ? AND returned_at IS NULL");
    $stmt->bind_param("ii", $user_id, $resource_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $active_borrows = $result->fetch_assoc();
    $stmt->close();

    if ($active_borrows['active_borrows'] > 0) {
        $error = "You already have this resource";
        writeLog('Error: ' . $error);
        echo json_encode(["error" => $error]);
        exit();
    }

    $conn->begin_transaction();
    
    try {
        $stmt = $conn->prepare("UPDATE resources SET status = 'borrowed' WHERE id = ? AND status != 'borrowed'");
        $stmt->bind_param("i", $resource_id);
        $stmt->execute();
        
        if ($stmt->affected_rows === 0) {
            throw new Exception("Resource is no longer available");
        }
        $stmt->close();

        $now = date("Y-m-d H:i:s");
        $stmt = $conn->prepare("INSERT INTO borrow_log (user_id, resource_id, borrowed_at) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $user_id, $resource_id, $now);
        $stmt->execute();
        $stmt->close();
        
        $conn->commit();
        
        $success_message = "You've successfully borrowed: " . $resource['name'];
        writeLog('Success: ' . $success_message);
        echo json_encode([
            "success" => true, 
            "message" => $success_message,
            "resource_id" => $resource_id
        ]);
    } catch (Exception $e) {
        $conn->rollback();
        $error = $e->getMessage();
        writeLog('Error: ' . $error);
        echo json_encode(["error" => $error]);
    }
} elseif ($action === 'return') {
    $stmt = $conn->prepare("SELECT id, name FROM resources WHERE id = ?");
    $stmt->bind_param("i", $resource_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $resource = $result->fetch_assoc();
    $stmt->close();

    if (!$resource) {
        $error = "Resource not found";
        writeLog('Error: ' . $error);
        echo json_encode(["error" => $error]);
        exit();
    }

    $stmt = $conn->prepare("SELECT id FROM borrow_log WHERE user_id = ? AND resource_id = ? AND returned_at IS NULL ORDER BY borrowed_at DESC LIMIT 1");
    $stmt->bind_param("ii", $user_id, $resource_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    if ($row) {
        $borrow_id = $row['id'];
        
        $conn->begin_transaction();
        
        try {
            $now = date("Y-m-d H:i:s");
            $stmt = $conn->prepare("UPDATE borrow_log SET returned_at = ? WHERE id = ?");
            $stmt->bind_param("si", $now, $borrow_id);
            $stmt->execute();
            $stmt->close();

            $stmt = $conn->prepare("UPDATE resources SET status = 'available' WHERE id = ?");
            $stmt->bind_param("i", $resource_id);
            $stmt->execute();
            $stmt->close();
            
            $conn->commit();
            
            $success_message = "You've successfully returned: " . $resource['name'];
            writeLog('Success: ' . $success_message);
            echo json_encode([
                "success" => true, 
                "message" => $success_message,
                "resource_id" => $resource_id
            ]);
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Transaction failed: " . $e->getMessage();
            writeLog('Error: ' . $error);
            echo json_encode(["error" => $error]);
        }
    } else {
        $error = "You haven't borrowed this resource";
        writeLog('Error: ' . $error);
        echo json_encode(["error" => $error]);
    }
} else {
    $error = "Invalid action";
    writeLog('Error: ' . $error);
    echo json_encode(["error" => $error]);
}

$conn->close();
?>
