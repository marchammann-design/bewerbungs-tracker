<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';

$pageTitle = 'Login - Bewerbungs-Tracker';
$errors = [];

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Ungültige Anmeldedaten.';
    }
    if (empty($password)) {
        $errors[] = 'Ungültige Anmeldedaten.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT user_id, email, password_hash FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_email'] = $user['email'];
            header('Location: dashboard.php');
            exit;
        } else {
            $errors[] = 'Ungültige Anmeldedaten.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <main class="auth-container">
        <div class="auth-card">
            <h1>Bewerbungs-Tracker</h1>
            <h1>Login</h1>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php">
                <label for="email">E-Mail Adresse:</label>
                <input type="email" id="email" name="email" required autocomplete="email">

                <label for="password">Passwort:</label>
                <input type="password" id="password" name="password" required autocomplete="current-password">

                <button type="submit">Einloggen</button>
            </form>

            <p class="auth-link">Noch kein Konto? <a href="register.php">Jetzt registrieren</a></p>
        </div>
    </main>

    <footer class="site-footer">
        <p>&copy; <?= date('Y') ?> Bewerbungs-Tracker</p>
    </footer>

</body>
</html>