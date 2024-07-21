<?php
require '../functions.php';  // Sørg for riktig sti til funksjonsfilen
require '../auth.php';

if (!isset($_SESSION['email'])) {
    http_response_code(401);
    echo json_encode(["message" => "Unauthorized"]);
    exit();
}
// Sjekk for POST request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Hente rå POST data og dekode JSON
    $inputJSON = file_get_contents('php://input');
    $input = json_decode($inputJSON, true);
    if (is_array($input)) {
        $arrangementID = $input['arrangementID'] ?? null;

        // Hente arrangementdetaljer fra databasen
        $eventDetails = getEventDetails($arrangementID);
        if ($eventDetails && !empty($eventDetails['participants'])) {
            foreach ($eventDetails['participants']['SSK'] as $email) {
                sendCalendarInvitation($email['epost'], $eventDetails['title'], $eventDetails['start'], $eventDetails['end'], $eventDetails['sted'], $eventDetails['adresse'], $eventDetails['dato'], $eventDetails['participants']);
            }
            echo json_encode(array("status" => "success", "message" => "Invitations sent successfully"));
        } else {
            echo json_encode(array("status" => "error", "message" => "Failed to retrieve event details or no participants found"));
        }
    } else {
        echo json_encode(array("status" => "error", "message" => "Invalid JSON"));
    }
} else {
    echo json_encode(array("status" => "error", "message" => "Invalid request"));
}

