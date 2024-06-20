<?php
require 'config.php';
require 'functions.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    createMagicLink($email);
    echo "A magic login link has been sent to your email.";
} elseif (isset($_GET['token'])) {
    $token = $_GET['token'];
    $email = validateMagicLink($token);

    if ($email) {
        // Sjekk hvilken tabell brukeren tilhører
        $user_type = "";
        $user_id = "";
        if ($result = $conn->query("SELECT id FROM admins WHERE epost = '$email'")) {
            if ($result->num_rows > 0) {
                $user_type = "admin";
                $user_id = $result->fetch_assoc()['id'];
            }
        } elseif ($result = $conn->query("SELECT id FROM ssk WHERE epost = '$email'")) {
            if ($result->num_rows > 0) {
                $user_type = "ssk";
                $user_id = $result->fetch_assoc()['id'];
            }
        } elseif ($result = $conn->query("SELECT id FROM ridderhatt WHERE epost = '$email'")) {
            if ($result->num_rows > 0) {
                $user_type = "ridderhatt";
                $user_id = $result->fetch_assoc()['id'];
            }
        }

        if ($user_type) {
            // Start session and redirect user

            $_SESSION['email'] = $email;
            $_SESSION['user_type'] = $user_type;
            $_SESSION['user_id'] = $user_id;
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
            <label for="email">E-postadresse</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <button type="submit" class="btn btn-primary">Send magic link</button>
    </form>
</div>
</body>
</html>
