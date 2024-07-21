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

if (!isset($_SESSION['email'])) {
    http_response_code(401);
    echo json_encode(["message" => "Unauthorized"]);
    exit();
}
$kommune_id = isset($_GET['kommune_id']) ? $_GET['kommune_id'] : '';

if ($kommune_id) {
    $stmt = $conn->prepare("SELECT id, navn FROM steder WHERE kommune = ? ORDER BY navn");
    $stmt->bind_param('i', $kommune_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $locations = [];
    while ($row = $result->fetch_assoc()) {
        $locations[] = $row;
    }

    header('Content-Type: application/json');
    echo json_encode($locations);

    $stmt->close();
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Kommune ID mangler']);
}

$conn->close();
?>
