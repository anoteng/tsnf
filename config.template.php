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
