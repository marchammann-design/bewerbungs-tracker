<?php
require_once __DIR__ . '/../includes/session.php';

// Zugriffsschutz: Wenn nicht eingeloggt, rauswerfen
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
</head>
<body>
    <h1>Willkommen im Dashboard</h1>
    <p>Eingeloggt als: <?= htmlspecialchars($_SESSION['user_email']) ?></p>
    <p>User-ID: <?= (int)$_SESSION['user_id'] ?></p>
    
    <a href="logout.php">Ausloggen</a>
</body>
</html>