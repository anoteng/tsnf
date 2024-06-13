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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/luxon/2.1.1/luxon.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tabulator/5.2.7/js/tabulator.min.js"></script>
    <script src="dashboard.js" defer></script>
</head>
<body>
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
</body>
</html>
