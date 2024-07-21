<?php
require 'config.php';

function validateToken($conn) {
    if (isset($_COOKIE['login_token'])) {
        $token = $_COOKIE['login_token'];
        $stmt = $conn->prepare("SELECT email, expiry FROM magic_links WHERE token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($email, $expiry);
            $stmt->fetch();
            if (strtotime($expiry) > time()) {
                // Token er gyldig
                // Sette session-variabler
                $_SESSION['email'] = $email;

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
                    // Sette session-variabler
                    $_SESSION['user_type'] = $user_type;
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['user_name'] = $user_name;
                    return true;
                }
            }
        }
    }
    return false;
}

if (!isset($_SESSION['email']) && !validateToken($conn)) {
    header("Location: login.php");
    exit();
}