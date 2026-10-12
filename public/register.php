<?php

// als erster wird die Datenbankverbindung über den Pfad zu ihr geladen
require_once __DIR__ .'/../includes/db.php';

// Varriablen für den Erfolg und Fehlermeldung
$errors = [];
$success ='';

// prüft ob das Formular per POST abgeschickt wurde
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email)) {
        $errors[] = 'Eine E-Mail ist erforderlich';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Ungültiges E-Mail Format';
    }

    if (empty($password)) {
        $errors[] = 'Ein Password ist erforderlich';
    }elseif (strlen($password) < 8) {
        $errors[] = 'Passwort muss mindestens 8 Zeichen lanh sein!';
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
        // Fehlercode 23000 = UNIQUE Constraint verletzt
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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>Registrierung - Bewerbungs-Tracker</title>
</head>
<body>

    <h1>Neues Konto erstellen</h1>

        <!-- htmlspecialchars wandelt sonderzeichen in HTML-Entities um
         damit sie als sichere Inhalte angezeigt werden und nicht als code 
         ausgeführt werden können, Schutz vor XSS (Cross-Site-Scripting) -->
    <div id="messages">
        <?php if (!empty($errors)): ?>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <p>
                <?php echo htmlspecialchars($success) ?>
            </p>
        <?php endif; ?>
    </div>

    <form method="POST" action="register.php">
        <label for="email">E-Mail Adresse</label><br>
        <input type="email" id="email" name="email" required autocomplete="email"><br><br>

        <label for="password">Password (min. 8 Zeichen)</label><br>
        <input type="passowrd" id="password" name="password" required minlength="8" autocomplete="new-password"><br><br>

        <button type="submit">Registrieren</button>
    
    </form>

    <p>Bereits registriert? <a href="login.php">Zum Login</a></p>
    
</body>
</html>