<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $kommune_id = $_GET['kommune_id'];
    $stmt = $conn->prepare("SELECT id, navn FROM steder WHERE kommune = ?");
    $stmt->bind_param("i", $kommune_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $steder = array();
    while ($row = $result->fetch_assoc()) {
        $steder[] = $row;
    }

    $stmt->close();
    header('Content-Type: application/json');
    echo json_encode($steder);
}
?>
