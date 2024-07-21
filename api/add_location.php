<?php
require '../config.php';
require '../auth.php';

if (!isset($_SESSION['email'])) {
    http_response_code(401);
    echo json_encode(["message" => "Unauthorized"]);
    exit();
}
// Sjekk om forespørselen er POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Kun POST-metoden er tillatt";
    exit;
}

// Sjekk om nødvendige data er satt
if (!isset($_POST['navn'], $_POST['adresse'])) {
    http_response_code(400);
    echo "Manglende data";
    exit;
}

$navn = $_POST['navn'];
$adresse = $_POST['adresse'];

// Sett inn lokasjonen i databasen
$stmt = $conn->prepare("INSERT INTO steder (navn, adresse) VALUES (?, ?)");
$stmt->bind_param('ss', $navn, $adresse);

if ($stmt->execute()) {
    echo "Lokasjon lagt til";
} else {
    http_response_code(500);
    echo "Kunne ikke legge til lokasjon: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
