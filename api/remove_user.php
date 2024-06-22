<?php
require '../config.php'; // Inkluder din databasekoblingsfil

// Hent data fra AJAX-forespørselen
$input = json_decode(file_get_contents('php://input'), true);
$arrangementId = $input['arrangementId'];
$type = $input['type'];
$number = $input['number'];

// Oppdater den riktige kolonnen basert på type og nummer
$column = $type . $number; // Anta at kolonnenavnene er i formatet ssk1_id, ridder1_id osv.

$sql = "UPDATE arrangement SET {$column} = NULL WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $arrangementId);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => $stmt->error]);
}

$stmt->close();
$conn->close();
?>