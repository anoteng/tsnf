<?php
require 'config.php';
require 'functions.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    createMagicLink($email);
    echo "<p>En innloggingslenke ble sendt til epostadressen din. Husk at det bare fungerer med epostadressen du er registrert med hos Norges sopp- og nyttevekstforening.</p>";
    echo "<p>Det kan ta noen minutter før eposten kommer. Når eposten kommer kan du klikke på lenken eller lime inn adressen i nettleseren din.</p>";
} elseif (isset($_GET['token'])) {
    $token = $_GET['token'];
    $email = validateMagicLink($token);

    if ($email) {
        // Sjekk hvilken tabell brukeren tilhører
        $user_type = "";
        $user_id = "";
        $user_name = "";

        // Sjekk admins
        $sql = "SELECT id, navn FROM admins WHERE epost = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $user_type = "admin";
            $user_id = $user['id'];
            $user_name = $user['navn'];
        }

        // Sjekk ssk
        if (empty($user_type)) {
            $sql = "SELECT id, navn FROM ssk WHERE epost = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                $user_type = "ssk";
                $user_id = $user['id'];
                $user_name = $user['navn'];
            }
        }

        // Sjekk ridderhatt
        if (empty($user_type)) {
            $sql = "SELECT id, navn FROM ridderhatt WHERE epost = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                $user_type = "ridderhatt";
                $user_id = $user['id'];
                $user_name = $user['navn'];
            }
        }

        if ($user_type) {
            // Lagre token i en cookie
            $expiry = strtotime('+30 days'); // Token utløper etter 30 dager
            setcookie('login_token', $token, $expiry, '/', '', false, true);

            // Start session and set session variables
            $_SESSION['email'] = $email;
            $_SESSION['user_type'] = $user_type;
            $_SESSION['user_id'] = $user_id;
            $_SESSION['user_name'] = $user_name;
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
