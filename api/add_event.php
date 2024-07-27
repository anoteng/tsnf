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
$request_body = file_get_contents('php://input');

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
    // Log API call
    logApiCall($conn, $endpoint, $method, $user_name, $request_body);
} else {
    http_response_code(500);
    echo "Kunne ikke legge til arrangement: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
