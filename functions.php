<?php
require 'config.php';
require 'email_config.php'; // Inkluder e-postkonfigurasjonen

function generateToken() {
    return bin2hex(random_bytes(16));
}

function createMagicLink($email) {
    global $conn;
    $token = generateToken();
    $expiry = date("Y-m-d H:i:s", strtotime('+1 hour'));

    $stmt = $conn->prepare("INSERT INTO magic_links (email, token, expiry) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $email, $token, $expiry);
    $stmt->execute();
    $stmt->close();

    sendMagicLink($email, $token);
}

function validateMagicLink($token) {
    global $conn;
    $stmt = $conn->prepare("SELECT email, expiry FROM magic_links WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->bind_result($email, $expiry);
    $stmt->fetch();
    $stmt->close();

    if ($email && strtotime($expiry) > time()) {
        return $email;
    } else {
        return false;
    }
}
?>
