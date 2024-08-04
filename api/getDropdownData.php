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
function getDropdownData($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
    return $items;
}

$table = isset($_GET['table']) ? $_GET['table'] : '';

// Check if a specific filter is required (for 'steder' based on 'kommune')
if (isset($_GET['filter']) && $_GET['filter'] == "kommune") {
    $filter = (int)$_GET['filter_string'];
    $sql = "SELECT * FROM " . $table . " WHERE kommune = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $filter);
} elseif (isset($_GET['filter']) && $_GET['filter'] == "user_role"){
    $sql = "SELECT u.* FROM users u LEFT JOIN user_roles ur on ur.user_id = u.id WHERE ur.role = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $_GET['filter_string']);
} else {
    $sql = "SELECT * FROM " . $table;
    $stmt = $conn->prepare($sql);
}

// Collect data
$data = getDropdownData($stmt);

// Packing data and returning as JSON
header('Content-Type: application/json');
echo json_encode($data);