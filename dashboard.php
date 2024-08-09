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
require 'auth.php';

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

?>
<!DOCTYPE html>
<html lang="no">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/no.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tabulator/5.2.7/css/tabulator.min.css">
    <link rel="stylesheet" href="style.css">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/luxon/2.1.1/luxon.min.js"></script>

    <link href="https://unpkg.com/tabulator-tables@6.2.1/dist/css/tabulator.min.css" rel="stylesheet">
    <script type="text/javascript" src="https://unpkg.com/tabulator-tables@6.2.1/dist/js/tabulator.min.js"></script>
    <script src="dashboard.js" defer></script>
    <script src="editModal.js" defer></script>
</head>
<body data-user-type="<?php echo $user_type; ?>" data-user-id="<?php echo $user_id; ?>" data-user-name="<?php echo $_SESSION['user_name']; ?>">
<div class="container">
    <?php
    if ($user_type == 'admin') {
        echo "<p><a href=\"admindash.php\">Gå til admindashboard</a></p>";
    }
    ?>
    <h1>Arrangementer</h1>
<!--    <button id="filterLedige" class="btn btn-secondary">Vis ledige vakter</button>-->
    <button id="filterMine" class="btn btn-secondary">Vis mine vakter</button>
    <button id="clearFilters" class="btn btn-secondary">Fjern filtre</button>
<!--    <p><i>NB! Sortering på dato er midlertidig ikke i orden, jobber med å løse problemet</i></p> -->
    <div id="arrangementTable"></div>
    <button id="logout" class="btn btn-danger">Logg ut</button>
</div>

<!-- Edit Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span id="editModalClose" class="close">&times;</span>
        <h2>Rediger Arrangement</h2>
        <form id="editForm">
            <input type="hidden" id="edit-id">
            <div class="form-group">
                <label for="edit-dato">Dato:</label>
                <input type="date" id="edit-dato" class="form-control">
            </div>
            <div class="form-group">
                <label for="edit-tid_fra">Tid fra:</label>
                <input type="time" id="edit-tid_fra" class="form-control">
            </div>
            <div class="form-group">
                <label for="edit-tid_til">Tid til:</label>
                <input type="time" id="edit-tid_til" class="form-control">
            </div>
            <div class="form-group">
                <label for="edit-kommune">Kommune:</label>
                <select id="edit-kommune" class="form-control">
                    <!-- Kommune options loaded dynamically -->
                </select>
            </div>
            <div class="form-group">
                <label for="edit-sted">Sted:</label>
                <select id="edit-sted" class="form-control">
                    <!-- Sted options loaded dynamically based on selected kommune -->
                </select>
            </div>
            <div class="form-group">
                <label for="edit-arrtype">Arrangementstype:</label>
                <select id="edit-arrtype" class="form-control">
                    <!-- Arrangementstype options loaded dynamically -->
                </select>
            </div>
            <div class="form-group">
                <label for="edit-max_ssk" class="col-form-label">Max antall SSK:</label>
                <input type="number" class="form-control" id="edit-max_ssk" name="edit-max_ssk">
            </div>
            <div class="form-group">
                <label for="edit-ssk1">SSK 1:</label>
                <select id="edit-ssk1" class="form-control">
                    <!-- SSK options loaded dynamically -->
                </select>
            </div>
            <div class="form-group">
                <label for="edit-ssk2">SSK 2:</label>
                <select id="edit-ssk2" class="form-control">
                    <!-- SSK options loaded dynamically -->
                </select>
            </div>
            <div class="form-group">
                <label for="edit-ssk3">SSK 3:</label>
                <select id="edit-ssk3" class="form-control">
                    <!-- SSK options loaded dynamically -->
                </select>
            </div>
            <div class="form-group">
                <label for="edit-ridder1">Ridderhatt 1:</label>
                <select id="edit-ridder1" class="form-control">
                    <!-- Ridderhatt options loaded dynamically -->
                </select>
            </div>
            <div class="form-group">
                <label for="edit-ridder2">Ridderhatt 2:</label>
                <select id="edit-ridder2" class="form-control">
                    <!-- Ridderhatt options loaded dynamically -->
                </select>
            </div>
            <div class="form-group">
                <label for="edit-ridder3">Ridderhatt 3:</label>
                <select id="edit-ridder3" class="form-control">
                    <!-- Ridderhatt options loaded dynamically -->
                </select>
            </div>
            <button type="button" id="editModalSave" class="btn btn-primary">Lagre</button>
        </form>
    </div>
</div>
<footer>
    <p>&copy; <?php echo date("Y"); ?> Andreas Noteng. <a href="changelog.php">Changelog</a>. <a href="https://github.com/anoteng/tsnf/issues">report bugs</a></p>
</footer>
</body>
</html>
