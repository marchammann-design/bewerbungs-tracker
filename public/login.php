<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$errors = [];
$success = '';

// Prüfen ob die Felder Password und Email ausgefüllt sind
// Keine Zielgerichtete Fehlermeldung, Allgemeine Fehlermeldung

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Ungültige Anmeldedaten';
    }

    if (empty($password))  {
        $errors[] = 'Ungültige Anmeldedaten';
    }

    // Prepared Statement: Wir holen den User NUR anhand der E-Mail.
    // Niemals das Passwort im SQL-Query prüfen (Timing-Attacken)!
    
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT user_id, email, password_hash FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();


        if ($user && password_verify($password, $user['password_hash'])) {

            //Neue Session wird generiert
            //Daten des User werden in der Session gespeichert
            //weiterleitung zum Dashboard und beenden des Scripts
            
            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_email'] = $user['email'];

            header('Location: dashboard.php');
            exit;


        } else {
            $errors[] = 'Ungültige Anmeldedaten';
        }

    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>Login - Bewerbungs-Tracker</title>
</head>
<body>

    <h1>Login</h1>

    <div id="messages">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <p><?= htmlspecialchars($success) ?></p>
            </div>
        <?php endif; ?>
    </div>
    </div>

    <form method="POST" action="login.php">
        <label for="email">E-Mail Adresse:</label><br>
        <input type="email" id="email" name="email" required autocomplete="email"><br><br>

        <label for="password">Passwort:</label><br>
        <input type="password" id="password" name="password" required autocomplete="current-password"><br><br>

        <button type="submit">Einloggen</button>
    </form>

    <p>Noch kein Konto? <a href="register.php">Jetzt registrieren</a></p>

</body>
</html>
