<?php
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
        $sql .= " WHERE kommune_id = ?";
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
$filter = isset($_GET['kommune_id']) ? (int)$_GET['kommune_id'] : null;
$table = isset($_GET['table']) ? $_GET['table'] : '';

// Collect data
$data = getDropdownData($conn, $table, $filter);

// Packing data and returning as JSON
header('Content-Type: application/json');
echo json_encode($data);