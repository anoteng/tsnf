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

require_once '../config.php';
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
$max_ssk = isset($_POST['max_ssk']) ? $_POST['max_ssk'] : null;
$ssk1 = isset($_POST['ssk1']) ? $_POST['ssk1'] : null;
$ssk2 = isset($_POST['ssk2']) ? $_POST['ssk2'] : null;
$ssk3 = isset($_POST['ssk3']) ? $_POST['ssk3'] : null;
$ridderhatt1 = isset($_POST['ridderhatt1']) ? $_POST['ridderhatt1'] : null;
$ridderhatt2 = isset($_POST['ridderhatt2']) ? $_POST['ridderhatt2'] : null;
$ridderhatt3 = isset($_POST['ridderhatt3']) ? $_POST['ridderhatt3'] : null;

$stmt = $conn->prepare("INSERT INTO arrangement (dato, tid_fra, tid_til, sted, arrtype, kommentar, max_ssk, ssk1, ssk2, ssk3, ridder1, ridder2, ridder3) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param('ssssssissssss', $dato, $tid_fra, $tid_til, $sted, $arrtype, $kommentar, $max_ssk, $ssk1, $ssk2, $ssk3, $ridderhatt1, $ridderhatt2, $ridderhatt3);

if ($stmt->execute()) {
    echo "Arrangement lagt til";
    logApiCall($conn, $endpoint, $method, $user_name, json_decode($request_body));
} else {
    http_response_code(500);
    echo "Kunne ikke legge til arrangement: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
