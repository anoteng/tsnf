<?php
$servername = "localhost";
$username = "username";
$password = "password";
$dbname = "db-name";

// Opprette forbindelse
$conn = new mysqli($servername, $username, $password, $dbname);

// Sjekk forbindelsen
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
