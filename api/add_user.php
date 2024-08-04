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

$stmt = $conn->prepare("INSERT INTO users (fornavn, etternavn, epost, adresse, poststed, postnr, tsnf_medlem) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param('ssssssi', $fornavn, $etternavn, $epost, $adresse, $poststed, $postnr, $tsnf_medlem);

if ($stmt->execute()) {
    $user_id = $stmt->insert_id;
    $stmt->close();

    $stmt = $conn->prepare("INSERT INTO user_roles (user_id, role) VALUES (?, ?)");
    $stmt->bind_param('is', $user_id, $user_level);

    if ($stmt->execute()) {
        echo "Bruker lagt til med rolle $user_level";
        logApiCall($conn, $endpoint, $method, $user_name, json_encode($_POST));
    } else {
        http_response_code(500);
        echo "Kunne ikke legge til brukerrolle: " . $stmt->error;
    }

    $stmt->close();
} else {
    http_response_code(500);
    echo "Kunne ikke legge til bruker: " . $stmt->error;
}

$conn->close();
?>
