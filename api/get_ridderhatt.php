<?php
require '../config.php';

$response = array('success' => false, 'ridderhatt' => array());

$query = "SELECT id, navn FROM ridderhatt";
if ($result = $conn->query($query)) {
    while ($row = $result->fetch_assoc()) {
        $response['ridderhatt'][] = $row;
    }
    $response['success'] = true;
} else {
    $response['message'] = $conn->error;
}

header('Content-Type: application/json');
echo json_encode($response);
?>
