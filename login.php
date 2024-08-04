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
require 'functions.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $user_id = getUserIdByEmail($email); // En ny funksjon som henter user_id ved hjelp av email
    if ($user_id) {
        createMagicLink($user_id);
        echo "<p>En innloggingslenke ble sendt til epostadressen din. Husk at det bare fungerer med epostadressen du er registrert med hos Norges sopp- og nyttevekstforening.</p>";
        echo "<p>Det kan ta noen minutter før eposten kommer. Når eposten kommer kan du klikke på lenken eller lime inn adressen i nettleseren din.</p>";
        echo "<p>Dersom du ikke får noen epost kan det hjelpe å legge til vaktliste@tsnf.noteng.no som klarert avsender/kontakt</p>";
    } else {
        echo "<p>E-postadressen er ikke registrert.</p>";
    }
} elseif (isset($_GET['token'])) {
    $token = $_GET['token'];
    $user_id = validateMagicLink($token);

    if ($user_id) {
        // Hent brukerdata fra users-tabellen
        $sql = "SELECT u.id, u.navn, ur.role, u.epost FROM users u JOIN user_roles ur ON u.id = ur.user_id WHERE u.id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $user_id = $user['id'];
            $user_name = $user['navn'];
            $user_role = $user['role'];
            $email = $user['epost'];

            // Lagre token i en cookie
            $expiry = strtotime('+30 days'); // Token utløper etter 30 dager
            setcookie('login_token', $token, $expiry, '/', '', false, true);

            // Start session and set session variables
            session_start();
            $_SESSION['email'] = $email;
            $_SESSION['user_id'] = $user_id;
            $_SESSION['user_name'] = $user_name;
            $_SESSION['user_role'] = $user_role;
            header("Location: dashboard.php");
            exit();
        } else {
            echo "User not found.";
        }
    } else {
        echo "Invalid or expired magic link.";
    }
}
?>

<!DOCTYPE html>
<html lang="no">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container">
    <h2>Logg inn</h2>
    <form method="POST" action="login.php">
        <div class="form-group">
            <label for="email">E-postadresse (den du er registrert hos Norges sopp- og nyttevekstforbund med)</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <button type="submit" class="btn btn-primary">Send innloggingslenke</button>
    </form>
</div>
</body>
</html>
