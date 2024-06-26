<?php
require '../functions.php';  // Sørg for riktig sti til funksjonsfilen

// Sjekk for POST request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $arrangementID = $_POST['arrangementID'];
    $type = $_POST['type'];
    $number = $_POST['number'];
    $userID = $_POST['userid'];

    // Hente arrangementdetaljer fra databasen
    $eventDetails = getEventDetails($arrangementID, $type, $number, $userID);
    if ($eventDetails) {
        sendCalendarInvitation($eventDetails['email'], $eventDetails['title'], $eventDetails['start'], $eventDetails['end'], $eventDetails['location'], $eventDetails['participants']);
        echo json_encode(array("status" => "success", "message" => "Invitation sent successfully"));
    } else {
        echo json_encode(array("status" => "error", "message" => "Failed to retrieve event details"));
    }
} else {
    echo json_encode(array("status" => "error", "message" => "Invalid request"));
}
