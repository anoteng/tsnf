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