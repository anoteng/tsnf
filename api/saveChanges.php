<?php
// saveChanges.php
require_once '../config.php'; // Inkluderer databasekonfigurasjoner

// Oppdaterer arrangementsdata
function updateArrangement($conn, $data) {
    $sql = "UPDATE arrangementer SET dato=?, tid_fra=?, tid_til=?, sted=?, arrtype=?, ssk1=?, ssk2=?, ssk3=?, ridder1=?, ridder2=?, ridder3=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssiiiiiiiii", $data['dato'], $data['tid_fra'], $data['tid_til'], $data['sted'], $data['arrtype'], $data['ssk1'], $data['ssk2'], $data['ssk3'], $data['ridder1'], $data['ridder2'], $data['ridder3'], $data['id']);
    $stmt->execute();
    return $stmt->affected_rows;
}

// Tar imot data fra AJAX-kall
$postdata = file_get_contents("php://input");
$data = json_decode($postdata, true);

// Utfører oppdatering
$result = updateArrangement($conn, $data);
echo $result > 0 ? "Success" : "Error";

$conn->close();
