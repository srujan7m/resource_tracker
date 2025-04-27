<?php
include 'auth.php';
include 'db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $response = [];

    $totalResourcesQuery = "SELECT COUNT(*) as total FROM resources";
    $totalResourcesResult = $conn->query($totalResourcesQuery);
    $response['total_resources'] = $totalResourcesResult->fetch_assoc()['total'];

    $availableResourcesQuery = "SELECT COUNT(*) as available FROM resources WHERE status = 'available'";
    $availableResourcesResult = $conn->query($availableResourcesQuery);
    $response['available_resources'] = $availableResourcesResult->fetch_assoc()['available'];

    $borrowedResourcesQuery = "SELECT COUNT(*) as borrowed FROM resources WHERE status = 'borrowed'";
    $borrowedResourcesResult = $conn->query($borrowedResourcesQuery);
    $response['borrowed_resources'] = $borrowedResourcesResult->fetch_assoc()['borrowed'];

    $recentActivityQuery = "SELECT r.name as resource_name, u.name as user_name, u.email, bl.borrowed_at, bl.returned_at, 
                            CASE 
                                WHEN bl.returned_at IS NULL THEN 'Borrowed'
                                ELSE 'Returned'
                            END as status
                            FROM borrow_log bl
                            JOIN resources r ON bl.resource_id = r.id
                            JOIN users u ON bl.user_id = u.id
                            ORDER BY bl.borrowed_at DESC LIMIT 10";
    $recentActivityResult = $conn->query($recentActivityQuery);
    $recentActivity = [];
    while ($row = $recentActivityResult->fetch_assoc()) {
        $recentActivity[] = $row;
    }
    $response['recent_activity'] = $recentActivity;

    echo json_encode(["success" => true, "data" => $response]);
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
}
?>