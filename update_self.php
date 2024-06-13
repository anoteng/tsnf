<?php
session_start();
require 'config.php';

// Sjekk om brukeren er logget inn som ssk eller ridderhatt
if (!isset($_SESSION['user_type']) || !in_array($_SESSION['user_type'], ['ssk', 'ridderhatt'])) {
    echo "Du har ikke tilgang til denne siden.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $type = $_POST['type'];
    $index = $_POST['index'];
    $value = !empty($_POST['value']) ? $_POST['value'] : NULL;

    $column = ($type == 'ssk') ? "ssk$index" : "ridder$index";

    $stmt = $conn->prepare("UPDATE arrangement SET $column = ? WHERE id = ?");
    $stmt->bind_param("ii", $value, $id);

    if ($stmt->execute()) {
        echo "Ditt valg ble oppdatert!";
    } else {
        echo "Feil ved oppdatering: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
