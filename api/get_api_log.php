<?php
// Inkluderer databaseinnstillinger fra config.php
include('config.php');

// Angi content type til JSON
header('Content-Type: application/json');

try {
    // Forbereder SQL-spørringen for å hente loggdata
    $stmt = $conn->prepare("SELECT endpoint, user_name, request_body, created_at FROM api_log ORDER BY created_at DESC");
    $stmt->execute();

    // Henter alle resultatene som en assosiativ array
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Returnerer resultatene som JSON
    echo json_encode($logs);
} catch (PDOException $e) {
    // Returnerer feilmelding hvis noe går galt
    echo json_encode(['error' => $e->getMessage()]);
}
?>
