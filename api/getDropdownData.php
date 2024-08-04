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



require_once '../config.php'; // Inkluderer databasekonfigurasjoner
require '../auth.php';

if (!isset($_SESSION['email'])) {
    http_response_code(401);
    echo json_encode(["message" => "Unauthorized"]);
    exit();
}
function getDropdownData($conn, $table, $filter = null) {
    $sql = "SELECT * FROM " . $table;
    if ($filter) {
        $sql .= " WHERE kommune = ?";
    }
    $stmt = $conn->prepare($sql);
    if ($filter) {
        $stmt->bind_param("i", $filter);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
    return $items;
}


// Check if a specific filter is required (for 'steder' based on 'kommune')
$filter = isset($_GET['kommune']) ? (int)$_GET['kommune'] : null;
$table = isset($_GET['table']) ? $_GET['table'] : '';

// Collect data
$data = getDropdownData($conn, $table, $filter);

// Packing data and returning as JSON
header('Content-Type: application/json');
echo json_encode($data);