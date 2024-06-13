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
        // Sjekk hvilken tabell brukeren tilhører og hent user_id
        $user_type = "";
        $user_id = "";

        $result = $conn->query("SELECT id FROM admins WHERE epost = '$email'");
        if ($result->num_rows > 0) {
            $user_type = "admin";
            $user_id = $result->fetch_assoc()['id'];
        } else {
            $result = $conn->query("SELECT id FROM ssk WHERE epost = '$email'");
            if ($result->num_rows > 0) {
                $user_type = "ssk";
                $user_id = $result->fetch_assoc()['id'];
            } else {
                $result = $conn->query("SELECT id FROM ridderhatt WHERE epost = '$email'");
                if ($result->num_rows > 0) {
                    $user_type = "ridderhatt";
                    $user_id = $result->fetch_assoc()['id'];
                }
            }
        }

        if ($user_type && $user_id) {
            // Start session and redirect user
            session_start();
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
