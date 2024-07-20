<?php
require '../config.php';

// Sjekk om forespørselen er POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Kun POST-metoden er tillatt";
    exit;
}

// Sjekk om nødvendige data er satt
if (!isset($_POST['dato'], $_POST['tid_fra'], $_POST['tid_til'], $_POST['sted'], $_POST['arrtype'])) {
    http_response_code(400);
    echo "Manglende data";
    exit;
}

$dato = $_POST['dato'];
$tid_fra = $_POST['tid_fra'];
$tid_til = $_POST['tid_til'];
$sted = $_POST['sted'];
$arrtype = $_POST['arrtype'];
$kommentar = isset($_POST['kommentar']) ? $_POST['kommentar'] : '';

// Sett inn arrangementet i databasen
$stmt = $conn->prepare("INSERT INTO arrangement (dato, tid_fra, tid_til, sted, arrtype, kommentar) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param('ssssss', $dato, $tid_fra, $tid_til, $sted, $arrtype, $kommentar);

if ($stmt->execute()) {
    echo "Arrangement lagt til";
} else {
    http_response_code(500);
    echo "Kunne ikke legge til arrangement: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
