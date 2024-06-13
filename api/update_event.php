<?php
require '../config.php';

$data = json_decode(file_get_contents('php://input'), true);
$response = array('success' => false);

$id = $data['id'];
$tid_fra = !empty($data['tid_fra']) ? $data['tid_fra'] : NULL;
$tid_til = !empty($data['tid_til']) ? $data['tid_til'] : NULL;
$ssk1 = !empty($data['ssk1']) ? $data['ssk1'] : NULL;
$ssk2 = !empty($data['ssk2']) ? $data['ssk2'] : NULL;
$ssk3 = !empty($data['ssk3']) ? $data['ssk3'] : NULL;
$ridder1 = !empty($data['ridder1']) ? $data['ridder1'] : NULL;
$ridder2 = !empty($data['ridder2']) ? $data['ridder2'] : NULL;
$ridder3 = !empty($data['ridder3']) ? $data['ridder3'] : NULL;

$stmt = $conn->prepare("UPDATE arrangement SET tid_fra=?, tid_til=?, ssk1=?, ssk2=?, ssk3=?, ridder1=?, ridder2=?, ridder3=? WHERE id=?");
$stmt->bind_param('ssssssssi', $tid_fra, $tid_til, $ssk1, $ssk2, $ssk3, $ridder1, $ridder2, $ridder3, $id);

if ($stmt->execute()) {
    $response['success'] = true;
} else {
    $response['message'] = $stmt->error;
}

$stmt->close();
$conn->close();

header('Content-Type: application/json');
echo json_encode($response);
?>
