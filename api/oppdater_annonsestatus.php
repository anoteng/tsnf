<?php
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
