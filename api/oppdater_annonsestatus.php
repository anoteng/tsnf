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



header("Content-Type: application/json");

require '../config.php';
require '../auth.php';

if (!isset($_SESSION['email'])) {
    http_response_code(401);
    echo json_encode(["message" => "Unauthorized"]);
    exit();
}

header("Content-Type: application/json");

require '../config.php';


$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = $input['id'];
    $field = array_key_exists('annonsert_fb', $input) ? 'annonsert_fb' : 'annonsert_kalender';
    $value = $input[$field];

    // Prepare statement
    $stmt = $conn->prepare("UPDATE arrangement SET $field = ? WHERE id = ?");
    $stmt->bind_param("ii", $value, $id);

    if ($stmt->execute()) {
        echo json_encode(["message" => "Update successful"]);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Update failed"]);
    }

    $stmt->close();
    $conn->close();
} else {
    http_response_code(405);
    echo json_encode(["message" => "Method not allowed"]);
}
?>
