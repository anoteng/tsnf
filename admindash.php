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

// Sett tidsgrensen for inaktivitet (i sekunder)
$inactive = 604800; // 1 uke

if (!isset($_SESSION['user_type'])) {
    // Brukeren er ikke innlogget, videresend til index.php
    header("Location: index.php");
    exit;
}
$user_type = $_SESSION['user_type'];
$user_id = $_SESSION['user_id'];
// Sjekk når sist aktivitet skjedde
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $inactive) {
    // Sesjonen har utløpt pga inaktivitet, ødelegg den og videresend til index.php
    session_unset(); // Fjern alle sesjonsvariabler
    session_destroy(); // Ødelegg sesjonen
    header("Location: index.php");
    exit;
}

// Oppdater sist aktivitetstidspunkt
$_SESSION['last_activity'] = time();
if ($user_type !== 'admin') {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="no">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashbord</title>
    <link rel="stylesheet" href="adminstyle.css">
    <script src="admindash.js" defer></script>
</head>
<body>
<h1>Admin Dashbord</h1>
<ul class="menu">
    <li><a href="#" onclick="openModal('userAdminModal')">Brukeradministrasjon</a></li>
    <li><a href="#" onclick="openModal('locationAdminModal')">Stedsadministrasjon</a></li>
    <li><a href="#" onclick="openModal('eventAdminModal')">Arrangementadministrasjon</a></li>
    <li><a href="#" onclick="openModal('apiLogSection')">Vis logg</a></li>
    <li><a href="dashboard.php">Tilbake til dashbord</a></li>
</ul>

<!-- Brukeradministrasjon Modal -->
<div id="userAdminModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('userAdminModal')">&times;</span>
        <h2>Brukeradministrasjon</h2>
        <form id="addUserForm">
            <label for="fornavn">Fornavn:</label>
            <input type="text" id="fornavn" name="fornavn" required><br>
            <label for="etternavn">Etternavn:</label>
            <input type="text" id="etternavn" name="etternavn" required><br>
            <label for="epost">E-post:</label>
            <input type="email" id="epost" name="epost" required><br>
            <label for="adresse">Adresse:</label>
            <input type="text" id="adresse" name="adresse"><br>
            <label for="poststed">Poststed:</label>
            <input type="text" id="poststed" name="poststed"><br>
            <label for="postnr">Postnummer:</label>
            <input type="text" id="postnr" name="postnr"><br>
            <label for="user_level">Brukernivå:</label>
            <select id="user_level" name="user_level" required>
                <option value="admin">Admin</option>
                <option value="ssk">SSK</option>
                <option value="ridderhatt">Ridderhatt</option>
            </select><br>
            <label for="tsnf_medlem">TSNF Medlem:</label>
            <input type="checkbox" id="tsnf_medlem" name="tsnf_medlem" checked><br>
            <button type="submit">Legg til bruker</button>
        </form>
    </div>
</div>

<!-- Stedsadministrasjon Modal -->
<div id="locationAdminModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('locationAdminModal')">&times;</span>
        <h2>Stedsadministrasjon</h2>
        <form id="addLocationForm">
            <label for="navn">Stedsnavn:</label>
            <input type="text" id="navn" name="navn" required><br>
            <label for="adresse">Adresse:</label>
            <input type="text" id="adresse" name="adresse" required><br>
            <label for="kommuneLocation">Kommune:</label>
            <select id="kommuneLocation" name="kommune" required></select><br>
            <button type="submit">Legg til sted</button>
        </form>
    </div>
</div>
<!-- Ny seksjon for API-logg -->
<div id="apiLogSection">
    <h2>API Log</h2>
    <table>
        <thead>
        <tr>
            <th>Endpoint</th>
            <th>Username</th>
            <th>Request Body</th>
            <th>Created At</th>
        </tr>
        </thead>
        <tbody id="logTableBody">
        <!-- Log data will be inserted here by JavaScript -->
        </tbody>
    </table>
</div>
<!-- Arrangementadministrasjon Modal -->
<div id="eventAdminModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('eventAdminModal')">&times;</span>
        <h2>Arrangementadministrasjon</h2>
        <form id="addEventForm">
            <label for="dato">Dato:</label>
            <input type="date" id="dato" name="dato" required><br>
            <label for="tid_fra">Starttid:</label>
            <input type="time" id="tid_fra" name="tid_fra" required><br>
            <label for="tid_til">Sluttid:</label>
            <input type="time" id="tid_til" name="tid_til" required><br>
            <label for="kommuneEvent">Kommune:</label>
            <select id="kommuneEvent" name="kommune" onchange="loadLocations()" required></select><br>
            <label for="sted">Sted:</label>
            <select id="sted" name="sted" required></select><br>
            <label for="arrtype">Arrangementstype:</label>
            <select id="arrtype" name="arrtype" required></select><br>
            <label for="kommentar">Kommentar:</label>
            <textarea id="kommentar" name="kommentar"></textarea><br>
            <label for="max_ssk">Max SSK:</label>
            <input type="number" id="max_ssk" name="max_ssk" value="2" min="1" max="3"><br>
            <label for="ssk1">SSK1:</label>
            <select class="searchable-select" id="ssk1" name="ssk1"></select><br>
            <label for="ssk2">SSK2:</label>
            <select class="searchable-select" id="ssk2" name="ssk2"></select><br>
            <label for="ssk3">SSK3:</label>
            <select class="searchable-select" id="ssk3" name="ssk3"></select><br>
            <label for="ridderhatt1">Ridderhatt 1:</label>
            <select class="searchable-select" id="ridderhatt1" name="ridderhatt1"></select><br>
            <label for="ridderhatt2">Ridderhatt 2:</label>
            <select class="searchable-select" id="ridderhatt2" name="ridderhatt2"></select><br>
            <label for="ridderhatt3">Ridderhatt 3:</label>
            <select class="searchable-select" id="ridderhatt3" name="ridderhatt3"></select><br>
            <button type="submit">Legg til arrangement</button>
        </form>
    </div>
</div>

<footer>
    <p>&copy; <?php echo date("Y"); ?> Andreas Noteng. <a href="changelog.php">Changelog</a>. <a href="https://github.com/anoteng/tsnf/issues">report bugs</a></p>
</footer>
</body>
</html>
