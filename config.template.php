<?php
ini_set('session.gc_maxlifetime', 604800); // 1 uke i sekunder
session_set_cookie_params(604800); // 1 uke i sekunder
session_start();

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
