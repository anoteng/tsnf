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

