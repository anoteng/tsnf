<?php
session_start();
require 'config.php';
$user_type = $_SESSION['user_type'];
$user_id = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="no">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tabulator/5.2.7/css/tabulator.min.css">
    <link rel="stylesheet" href="style.css">
<!--    <style>
        .tabulator .tabulator-header .tabulator-col .tabulator-col-content .tabulator-title {
            white-space: nowrap;
        }
        .edit-button, .assign-button, .remove-button {
            padding: 5px 10px;
            font-size: 0.8em;
        }
        #arrangementTable {
            width: 90%;
            margin: auto;
        }
    </style>-->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/luxon/2.1.1/luxon.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tabulator/5.2.7/js/tabulator.min.js"></script>
    <script src="dashboard_modified.js" defer></script>
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
    <button id="filterLedige" class="btn btn-secondary">Vis ledige vakter</button>
    <button id="filterMine" class="btn btn-secondary">Vis mine vakter</button>
    <button id="clearFilters" class="btn btn-secondary">Fjern filtre</button>

    <div id="arrangementTable"></div>
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

</body>
</html>
