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

// saveChanges.php
require_once '../config.php'; // Inkluderer databasekonfigurasjoner
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
// Oppdaterer arrangementsdata
function updateArrangement($conn, $data) {
    $sql = "UPDATE arrangementer SET dato=?, tid_fra=?, tid_til=?, sted=?, arrtype=?, ssk1=?, ssk2=?, ssk3=?, ridder1=?, ridder2=?, ridder3=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssiiiiiiiii", $data['dato'], $data['tid_fra'], $data['tid_til'], $data['sted'], $data['arrtype'], $data['ssk1'], $data['ssk2'], $data['ssk3'], $data['ridder1'], $data['ridder2'], $data['ridder3'], $data['id']);
    $stmt->execute();
    return $stmt->affected_rows;
}

// Tar imot data fra AJAX-kall
//$postdata = file_get_contents("php://input");
$data = json_decode($request_body, true);

// Utfører oppdatering
$result = updateArrangement($conn, $data);
echo $result > 0 ? "Success" : "Error";
logApiCall($conn, $endpoint, $method, $user_name, $request_body);
$conn->close();
