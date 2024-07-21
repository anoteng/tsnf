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



require '../config.php'; // Inkluder din databasekoblingsfil
require '../auth.php';

if (!isset($_SESSION['email'])) {
    http_response_code(401);
    echo json_encode(["message" => "Unauthorized"]);
    exit();
}
// Hent data fra AJAX-forespørselen
$input = json_decode(file_get_contents('php://input'), true);
$arrangementId = $input['arrangementId'];
$type = $input['type'];
$number = $input['number'];

// Oppdater den riktige kolonnen basert på type og nummer
$column = $type . $number; // Anta at kolonnenavnene er i formatet ssk1_id, ridder1_id osv.

$sql = "UPDATE arrangement SET {$column} = NULL WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $arrangementId);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => $stmt->error]);
}

$stmt->close();
$conn->close();
?>
