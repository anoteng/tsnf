<?php
require '../config.php';
session_start();

$data = json_decode(file_get_contents("php://input"), true);

$id = $data['id'];
$user_id = $_SESSION['user_id'];
$index = $data['index'];

$stmt = $conn->prepare("UPDATE arrangement SET ridder$index = ? WHERE id = ?");
$stmt->bind_param("ii", $user_id, $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $stmt->error]);
}

$stmt->close();
?>
