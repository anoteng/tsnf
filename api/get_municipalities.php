<?php
require '../config.php';

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
