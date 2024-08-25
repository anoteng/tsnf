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



require 'config.php';
require 'email_config.php'; // Inkluder e-postkonfigurasjonen
require_once __DIR__ . '/vendor/autoload.php';  // Juster stien etter behov


use Eluceo\iCal\Domain\Entity\Calendar;
use Eluceo\iCal\Domain\Entity\Event;
use Eluceo\iCal\Domain\Entity\Attendee;
use Eluceo\iCal\Domain\ValueObject\TimeSpan;
use Eluceo\iCal\Domain\ValueObject\DateTime;
use Eluceo\iCal\Domain\ValueObject\Location;
use Eluceo\iCal\Domain\Enum\ParticipationStatus;
use Eluceo\iCal\Domain\ValueObject\EmailAddress;
use Eluceo\iCal\Presentation\Factory\CalendarFactory;

function generateToken() {
    return bin2hex(random_bytes(16));
}

function createMagicLink($email) {
    global $conn;
    $token = generateToken();
    $expiry = date("Y-m-d H:i:s", strtotime('+1 week'));

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
function sendCalendarInvitation($toEmail, $eventTitle, $eventStart, $eventEnd, $location, $adresse, $date, $participants) {

    $vEvent = new Event();
    // Definér tidssonen
    $timeZone = new \DateTimeZone('Europe/Oslo');

    // Bygg fullstendige dato og tid-strenger
    $startDateTimeString = $date . ' ' . $eventStart;
    $endDateTimeString = $date . ' ' . $eventEnd;

    // Oppretter DateTime-objekter for start og sluttid
    $phpStartDateTime = new \DateTime($startDateTimeString, $timeZone);
    $phpEndDateTime = new \DateTime($endDateTimeString, $timeZone);

    // Bruker DateTimeImmutable for iCal, siden det er anbefalt å ha immutability
    $start = new DateTime($phpStartDateTime, true);
    $end = new DateTime($phpEndDateTime, true);

    $occurrence = new TimeSpan($start, $end);

    // Sett location ved å opprette en Location-instans
    if (!empty($location)) {
        $locationObject = new Location($adresse, $location);
    }

    // Prepare participant information
    $participantInfo = "Vaktliste:\n\nSSK:\n";
    foreach ($participants['SSK'] as $participant) {
        $participantInfo .= "Navn: " . $participant['navn'] . ", Epost: " . $participant['epost'] . ", Mobil: " . $participant['telefon'] . "\n";
    }
    $participantInfo .= "\nRidderhatt:\n";
    foreach ($participants['Ridderhatt'] as $participant) {
        $participantInfo .= "Navn: " . $participant['navn'] . ", Epost: " . $participant['epost'] . ", Mobil: " . $participant['telefon'] . "\n";
    }

    $vEvent
        ->setOccurrence($occurrence)
//        ->setNoTime(false)
        ->setSummary($eventTitle)
        ->setDescription($participantInfo)
        ->setLocation($locationObject);

    $attendee = new Attendee(new EmailAddress($toEmail));
    $attendee->setParticipationStatus(ParticipationStatus::NEEDS_ACTION());
    $vEvent->addAttendee($attendee);

    $vCalendar = new Calendar([$vEvent]);
    $componentFactory = new CalendarFactory();
    $icsFileContent = $componentFactory->createCalendar($vCalendar);

    $headers = "From: vaktliste@tsnf.noteng.no\r\n";
    $headers .= "Reply-To: andreas@noteng.no\r\n";
    $headers .= "Content-Type: text/calendar; charset=utf-8; method=REQUEST;\r\n";
    $headers .= "Content-Transfer-Encoding: 7bit;\r\n";
    if (DEVELOPMENT_MODE) {
        $message = $icsFileContent->__toString();
        // Lagre e-posten i en tekstfil
        $logFilePath = '/var/www/tsnfdev/email_log.txt';
        $logMessage = "To: $toEmail\nSubject: $eventTitle\nHeaders: $headers\n\n$message\n\n";
        file_put_contents($logFilePath, $logMessage, FILE_APPEND);
    } else {
        sendICS($toEmail, 'TSNF vakt registrert: ' . $eventTitle, $icsFileContent, $headers);
    }
}
function getEventDetails($arrangementID) {
    global $conn;
    $eventData = [];

    // Hent hovedinformasjon om arrangementet
    $stmt = $conn->prepare(
        "SELECT a.dato AS dato, 
                a.tid_fra AS start, 
                a.tid_til AS end, 
                at.type AS title, 
                s.navn AS sted,
                s.adresse AS adresse
         FROM arrangement a
         LEFT JOIN steder s ON a.sted = s.id
         LEFT JOIN arrtype at ON a.arrtype = at.id
         WHERE a.id = ?"
    );
    $stmt->bind_param("i", $arrangementID);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $eventData = $row;
    }
    $stmt->close();

    // Hent informasjon om alle deltakere (SSK og Ridderhatt)
    $participants = ['SSK' => [], 'Ridderhatt' => []];
    $query = "
        SELECT 'SSK' AS role, CONCAT(fornavn, ' ', etternavn) AS navn, epost, mobil 
        FROM ssk 
        WHERE id IN (
            SELECT ssk1 FROM arrangement WHERE id = ? 
            UNION 
            SELECT ssk2 FROM arrangement WHERE id = ? 
            UNION 
            SELECT ssk3 FROM arrangement WHERE id = ?
        )
        UNION
        SELECT 'Ridderhatt' AS role, CONCAT(fornavn, ' ', etternavn) AS navn, epost, mobil 
        FROM ridderhatt 
        WHERE id IN (
            SELECT ridder1 FROM arrangement WHERE id = ? 
            UNION 
            SELECT ridder2 FROM arrangement WHERE id = ? 
            UNION 
            SELECT ridder3 FROM arrangement WHERE id = ?
        )
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiiiii", $arrangementID, $arrangementID, $arrangementID, $arrangementID, $arrangementID, $arrangementID);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $participants[$row['role']][] = [
            'navn' => $row['navn'],
            'epost' => $row['epost'],
            'telefon' => $row['mobil']
        ];
    }
    $stmt->close();

    $eventData['participants'] = $participants;
    return $eventData;
}


function getParticipantsInfo($arrangementID) {
    global $conn;
    $participants = [];
    $query = "SELECT s.navn, s.epost, s.mobil FROM ssk s
              JOIN arrangement a ON s.id = a.ssk1 OR s.id = a.ssk2 OR s.id = a.ssk3
              WHERE a.id = ? UNION
              SELECT r.navn, r.epost, r.mobil FROM ridderhatt r
              JOIN arrangement a ON r.id = a.ridder1 OR r.id = a.ridder2 OR r.id = a.ridder3
              WHERE a.id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $arrangementID, $arrangementID);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $participants[] = $row;
    }
    $stmt->close();
    return $participants;
}

?>
