<?php
require_once __DIR__ . '/../includes/db.php';

$pageTitle = 'Registrieren - Bewerbungs-Tracker';
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email)) {
        $errors[] = 'E-Mail ist erforderlich.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Ungültiges E-Mail-Format.';
    }

    if (empty($password)) {
        $errors[] = 'Passwort ist erforderlich.';
    } elseif (strlen($password) < 8) {
        $errors[] = 'Passwort muss mindestens 8 Zeichen lang sein.';
    }

    if (empty($errors)) {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        
        try {
            $insertStmt = $pdo->prepare("INSERT INTO users (email, password_hash) VALUES (:email, :password_hash)");
            $insertStmt->execute([
                ':email' => $email,
                ':password_hash' => $passwordHash
            ]);
            
            $success = 'Registrierung erfolgreich! Du kannst dich jetzt einloggen.';
            
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $errors[] = 'Diese E-Mail-Adresse ist bereits registriert.';
            } else {
                $errors[] = 'Ein Datenbankfehler ist aufgetreten.';
                error_log('Registrierungsfehler: ' . $e->getMessage());
            }
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
            <h1>Registrieren</h1>

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

            <form method="POST" action="register.php">
                <label for="email">E-Mail Adresse:</label>
                <input type="email" id="email" name="email" required autocomplete="email">

                <label for="password">Passwort:</label>
                <input type="password" id="password" name="password" required autocomplete="new-password">
                <p class="form-hint">Mindestens 8 Zeichen</p>

                <button type="submit">Registrieren</button>
            </form>

            <p class="auth-link">Bereits ein Konto? <a href="login.php">Zum Login</a></p>
        </div>
    </main>

    <footer class="site-footer">
        <p>&copy; <?= date('Y') ?> Bewerbungs-Tracker</p>
    </footer>

</body>
</html>