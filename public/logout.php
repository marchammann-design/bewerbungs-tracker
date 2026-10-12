<?php
require_once __DIR__ . '/../includes/session.php';

// 1. Alle Session-Variablen löschen
$_SESSION = [];

// 2. Session-Cookie beim Client löschen
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Session auf dem Server zerstören
session_destroy();

// 4. Zum Login weiterleiten
header('Location: login.php');
exit;