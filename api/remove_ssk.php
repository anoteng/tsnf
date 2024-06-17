<?php
require '../config.php';
session_start();

$data = json_decode(file_get_contents("php://input"), true);

$id = $data['id'];
$user_id = $_SESSION['user_id'];
$index = $data['index'];

// Check if the user is an admin or if they are the assigned SSK
$is_admin = $_SESSION['user_type'] === 'admin';

$stmt = $conn->prepare("SELECT dato, ssk$index FROM arrangement WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($dato, $assigned_ssk);
$stmt->fetch();
$stmt->close();

if (!$is_admin && ($assigned_ssk != $user_id || strtotime($dato) <= strtotime('+1 week'))) {
    echo json_encode(['success' => false, 'message' => 'Du kan ikke fjerne denne vakten.']);
    exit();
}

$stmt = $conn->prepare("UPDATE arrangement SET ssk$index = NULL WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $stmt->error]);
}

$stmt->close();
?>
