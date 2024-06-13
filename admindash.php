<?php
session_start();
require 'config.php';

// Sjekk om brukeren er logget inn som admin
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    echo "Du har ikke tilgang til denne siden.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="no">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
</head>
<body>
<h1>Velkommen til admin dashboard</h1>

<h2><a href="add_event.php">Legg til nytt arrangement</a></h2>

</body>
</html>
