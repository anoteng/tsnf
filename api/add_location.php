<?php
/*
 * This file is part of TSNF Vaktliste.
 *
 * TSNF Vaktliste is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * TSNF Vaktliste is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with TSNF Vaktliste. If not, see <https://www.gnu.org/licenses/>.
 *
 */



require '../config.php';
require '../auth.php';
require '../log_function.php';

if (!isset($_SESSION['email'])) {
    http_response_code(401);
    echo json_encode(["message" => "Unauthorized"]);
    exit();
}
$method = $_SERVER['REQUEST_METHOD'];
$endpoint = $_SERVER['REQUEST_URI'];
$user_name = $_SESSION['user_name'];

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
    logApiCall($conn, $endpoint, $method, $user_name, json_encode($_POST));
} else {
    http_response_code(500);
    echo "Kunne ikke legge til lokasjon: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
