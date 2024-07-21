<?php
require '../config.php';
require '../auth.php';

if (!isset($_SESSION['email'])) {
    http_response_code(401);
    echo json_encode(["message" => "Unauthorized"]);
    exit();
}
// Hent kommuner fra databasen
$result = $conn->query("SELECT id, navn FROM kommune ORDER BY navn");

$municipalities = [];
while ($row = $result->fetch_assoc()) {
    $municipalities[] = $row;
}

header('Content-Type: application/json');
echo json_encode($municipalities);

$conn->close();
?>
