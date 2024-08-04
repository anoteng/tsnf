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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Kun POST-metoden er tillatt";
    exit;
}

$data = json_decode($request_body, true);
$id = $data['id'];

// Sjekk om nødvendige data er satt
if (!isset($id)) {
    http_response_code(400);
    echo "Manglende data";
    exit;
}
function convertDateFormat($date) {
    $dateArray = explode('.', $date);
    return $dateArray[2] . '-' . $dateArray[1] . '-' . $dateArray[0];
}

$dato = isset($data['dato']) ? convertDateFormat($data['dato']) : null;
$tid_fra = isset($data['tid_fra']) ? $data['tid_fra'] : null;
$tid_til = isset($data['tid_til']) ? $data['tid_til'] : null;
$sted = isset($data['sted']) ? $data['sted'] : null;
$arrtype = isset($data['arrtype']) ? $data['arrtype'] : null;
$kommentar = isset($data['kommentar']) ? $data['kommentar'] : null;
$max_ssk = isset($data['max_ssk']) ? $data['max_ssk'] : null;
$ssk1 = isset($data['ssk1']) && is_numeric($data['ssk1']) ? $data['ssk1'] : null;
$ssk2 = isset($data['ssk2']) && is_numeric($data['ssk2'])? $data['ssk2'] : null;
$ssk3 = isset($data['ssk3']) && is_numeric($data['ssk3'])? $data['ssk3'] : null;
$ridderhatt1 = isset($data['ridder1']) && is_numeric($data['ridder1']) ? $data['ridder1'] : null;
$ridderhatt2 = isset($data['ridder2']) && is_numeric($data['ridder1']) ? $data['ridder2'] : null;
$ridderhatt3 = isset($data['ridder3']) && is_numeric($data['ridder1']) ? $data['ridder3'] : null;

$stmt = $conn->prepare("UPDATE arrangement SET dato = ?, tid_fra = ?, tid_til = ?, sted = ?, arrtype = ?, kommentar = ?, max_ssk = ?, ssk1 = ?, ssk2 = ?, ssk3 = ?, ridder1 = ?, ridder2 = ?, ridder3 = ? WHERE id = ?");
$stmt->bind_param('sssiisiiiiiiii', $dato, $tid_fra, $tid_til, $sted, $arrtype, $kommentar, $max_ssk, $ssk1, $ssk2, $ssk3, $ridderhatt1, $ridderhatt2, $ridderhatt3, $id);

if ($stmt->execute()) {
    if ($stmt->affected_rows < 1) {
        http_response_code(500);
        echo json_encode(["message" => var_dump($stmt)]);
    }
    echo json_encode(["message" => "Arrangement oppdatert"]);
    logApiCall($conn, $endpoint, $method, $user_name, $request_body);
} else {
    http_response_code(500);
    echo json_encode(["message" => "Kunne ikke oppdatere arrangement: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
