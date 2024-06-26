<?php
require 'config.php';
require 'email_config.php'; // Inkluder e-postkonfigurasjonen
require_once 'vendor/autoload.php';

use Eluceo\iCal\Component\Calendar;
use Eluceo\iCal\Component\Event;
use Eluceo\iCal\Property\Event\Attendee;

function generateToken() {
    return bin2hex(random_bytes(16));
}

function createMagicLink($email) {
    global $conn;
    $token = generateToken();
    $expiry = date("Y-m-d H:i:s", strtotime('+1 hour'));

    $stmt = $conn->prepare("INSERT INTO magic_links (email, token, expiry) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $email, $token, $expiry);
    $stmt->execute();
    $stmt->close();

    sendMagicLink($email, $token);
}

function validateMagicLink($token) {
    global $conn;
    $stmt = $conn->prepare("SELECT email, expiry FROM magic_links WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->bind_result($email, $expiry);
    $stmt->fetch();
    $stmt->close();

    if ($email && strtotime($expiry) > time()) {
        return $email;
    } else {
        return false;
    }
}
function sendCalendarInvitation($toEmail, $eventTitle, $eventStart, $eventEnd, $location) {
    $vCalendar = new Calendar('tsnf.noteng.no');

    $vEvent = new Event();
    $vEvent
        ->setDtStart(new \DateTime($eventStart))
        ->setDtEnd(new \DateTime($eventEnd))
        ->setNoTime(false)
        ->setSummary($eventTitle)
        ->setLocation($location);

    $vEvent->addAttendee((new Attendee($toEmail))->setRole('REQ-PARTICIPANT'));

    $vCalendar->addComponent($vEvent);

    $icsFileContent = $vCalendar->render();

    $headers = "From: vaktliste@tsnf.noteng.no\r\n";
    $headers .= "Reply-To: andreas@noteng.no\r\n";
    $headers .= "Content-Type: text/calendar; charset=utf-8; method=REQUEST;\r\n";
    $headers .= "Content-Transfer-Encoding: 7bit;\r\n";

    mail($toEmail, 'TSNF vakt registrert: ' . $eventTitle, $icsFileContent, $headers);
}
function getEventDetails($arrangementID, $type, $number, $userID) {
    global $conn;  // Databasetilkobling
    // Eksempel SQL query, må tilpasses faktisk database struktur
    $query = "SELECT * FROM arrangementer WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $arrangementID);
    $stmt->execute();
    $result = $stmt->get_result();
    $eventData = $result->fetch_assoc();
    $stmt->close();

    if ($eventData) {
        // Tilpass og inkluder deltakerinformasjon
        $participants = getParticipantsInfo($arrangementID); // En funksjon som henter deltakere
        $eventData['participants'] = $participants;
        return $eventData;
    } else {
        return false;
    }
}

function getParticipantsInfo($arrangementID) {
    global $conn;
    $participants = [];
    $query = "SELECT name, phone, email FROM users WHERE arrangement_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $arrangementID);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $participants[] = $row;
    }
    $stmt->close();
    return $participants;
}

?>
