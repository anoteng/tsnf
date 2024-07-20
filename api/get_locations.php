<?php
require '../config.php';

$kommune_id = isset($_GET['kommune_id']) ? $_GET['kommune_id'] : '';

if ($kommune_id) {
    $stmt = $conn->prepare("SELECT id, navn FROM steder WHERE kommune = ? ORDER BY navn");
    $stmt->bind_param('i', $kommune_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $locations = [];
    while ($row = $result->fetch_assoc()) {
        $locations[] = $row;
    }

    header('Content-Type: application/json');
    echo json_encode($locations);

    $stmt->close();
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Kommune ID mangler']);
}

$conn->close();
?>
