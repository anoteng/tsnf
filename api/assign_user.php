<?php
require '../config.php'; // Inkluder din databasekoblingsfil
require '../auth.php';

if (!isset($_SESSION['email'])) {
    http_response_code(401);
    echo json_encode(["message" => "Unauthorized"]);
    exit();
}
// Hent data fra AJAX-forespørselen
$input = json_decode(file_get_contents('php://input'), true);
$arrangementId = $input['arrangementId'];
$type = $input['type'];
$number = $input['number'];
$userId = $input['userId'];

// Oppdater den riktige kolonnen basert på type og nummer
$column = $type . $number;

$sql = "UPDATE arrangement SET {$column} = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $userId, $arrangementId);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => $stmt->error]);
}

$stmt->close();
$conn->close();
?>
