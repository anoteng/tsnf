<?php
require '../config.php';

$response = array('success' => false, 'ssk' => array());

$query = "SELECT id, navn FROM ssk";
if ($result = $conn->query($query)) {
    while ($row = $result->fetch_assoc()) {
        $response['ssk'][] = $row;
    }
    $response['success'] = true;
} else {
    $response['message'] = $conn->error;
}

header('Content-Type: application/json');
echo json_encode($response);
?>
