<?php
require '../config.php';

// Sjekk om forespørselen er POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Kun POST-metoden er tillatt";
    exit;
}

// Sjekk om nødvendige data er satt
if (!isset($_POST['fornavn'], $_POST['etternavn'], $_POST['epost'], $_POST['user_level'])) {
    http_response_code(400);
    echo "Manglende data";
    exit;
}

$fornavn = $_POST['fornavn'];
$etternavn = $_POST['etternavn'];
$epost = $_POST['epost'];
$user_level = $_POST['user_level'];

$adresse = isset($_POST['adresse']) ? $_POST['adresse'] : null;
$poststed = isset($_POST['poststed']) ? $_POST['poststed'] : null;
$postnr = isset($_POST['postnr']) ? $_POST['postnr'] : null;
$tsnf_medlem = isset($_POST['tsnf_medlem']) ? (bool)$_POST['tsnf_medlem'] : 1;

$table = '';
$stmt = null;

switch ($user_level) {
    case 'admin':
        $table = 'admins';
        $stmt = $conn->prepare("INSERT INTO $table (fornavn, etternavn, epost) VALUES (?, ?, ?)");
        $stmt->bind_param('sss', $fornavn, $etternavn, $epost);
        break;
    case 'ssk':
        $table = 'ssk';
        $stmt = $conn->prepare("INSERT INTO $table (fornavn, etternavn, epost, adresse, poststed, postnr, tsnf_medlem) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('ssssssi', $fornavn, $etternavn, $epost, $adresse, $poststed, $postnr, $tsnf_medlem);
        break;
    case 'ridderhatt':
        $table = 'ridderhatt';
        $stmt = $conn->prepare("INSERT INTO $table (fornavn, etternavn, epost, adresse, poststed, postnr) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('ssssss', $fornavn, $etternavn, $epost, $adresse, $poststed, $postnr);
        break;
    default:
        http_response_code(400);
        echo "Ugyldig brukernivå";
        exit;
}

if ($stmt->execute()) {
    echo "Bruker lagt til i $table";
} else {
    http_response_code(500);
    echo "Kunne ikke legge til bruker: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
