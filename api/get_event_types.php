<?php
require '../config.php';
require '../auth.php';

if (!isset($_SESSION['email'])) {
    http_response_code(401);
    echo json_encode(["message" => "Unauthorized"]);
    exit();
}
// Hent arrangementstyper fra databasen
$result = $conn->query("SELECT id, type FROM arrtype");

$event_types = [];
while ($row = $result->fetch_assoc()) {
    $event_types[] = $row;
}

header('Content-Type: application/json');
echo json_encode($event_types);

$conn->close();
?>