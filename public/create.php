<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';

// Zugriffsschutz: Wenn nicht eingeloggt, rauswerfen
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$errors = [];
$success = '';


// liest die Eingaben aus den HTML Forms 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $company = trim($_POST['company'] ?? '');
    $position = trim($_POST['position'] ?? '');
    $apply_date = trim($_POST['apply_date'] ?? '');
    $contact_person = trim($_POST['contact_person'] ?? '');
    $contact_email = trim($_POST['contact_email'] ?? '');
    $contact_phone = trim($_POST['contact_phone'] ?? '');
    $job_link = trim($_POST['job_link'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    
    // Status wird beim Erstellen immer "Beworben" gesetzt
    $current_status = 'Beworben';
    $user_id = $_SESSION['user_id'];

    // 2. Validierung der Pflichtfelder
    if (empty($company)) $errors[] = 'Firma ist ein Pflichtfeld.';
    if (empty($position)) $errors[] = 'Position ist ein Pflichtfeld.';
    if (empty($apply_date)) $errors[] = 'Bewerbungsdatum ist ein Pflichtfeld.';

    // 3. Wenn keine Fehler, in die Datenbank schreiben
    if (empty($errors)) {
        try {
            // Transaktion starten (stellt sicher, dass beide Inserts erfolgreich sind oder keiner)
            $pdo->beginTransaction();

            // A) Bewerbung in 'applications' einfügen
            $stmtApp = $pdo->prepare("
                INSERT INTO applications 
                (user_id, company, position, apply_date, contact_person, contact_email, contact_phone, job_link, notes, current_status) 
                VALUES (:user_id, :company, :position, :apply_date, :contact_person, :contact_email, :contact_phone, :job_link, :notes, :current_status)
            ");
            $stmtApp->execute([
                ':user_id' => $user_id,
                ':company' => $company,
                ':position' => $position,
                ':apply_date' => $apply_date,
                ':contact_person' => $contact_person,
                ':contact_email' => $contact_email,
                ':contact_phone' => $contact_phone,
                ':job_link' => $job_link,
                ':notes' => $notes,
                ':current_status' => $current_status
            ]);

            // Die ID der gerade eingefügten Bewerbung holen
            $application_id = $pdo->lastInsertId();

            // B) Ersten Eintrag in die Historie schreiben
            $stmtHist = $pdo->prepare("
                INSERT INTO application_history (application_id, old_status, new_status) 
                VALUES (:application_id, NULL, :new_status)
            ");
            $stmtHist->execute([
                ':application_id' => $application_id,
                ':new_status' => $current_status
            ]);

            // Transaktion bestätigen
            $pdo->commit();
            
            // Erfolg: Weiterleitung zum Dashboard
            header('Location: dashboard.php?success=created');
            exit;

        } catch (PDOException $e) {
            // Bei Fehler: Transaktion rückgängig machen
            $pdo->rollBack();
            $errors[] = 'Ein Datenbankfehler ist aufgetreten. Bitte versuche es erneut.';
            error_log('Create Application Error: ' . $e->getMessage());
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
    <title>Neue Bewerbung - Bewerbungs-Tracker</title>
</head>
<body>

    <h1>Neue Bewerbung anlegen</h1>

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
    </div>

    <form method="POST" action="create.php">
        <label for="company">Firma *</label><br>
        <input type="text" id="company" name="company" required value="<?= htmlspecialchars($_POST['company'] ?? '') ?>"><br><br>

        <label for="position">Position *</label><br>
        <input type="text" id="position" name="position" required value="<?= htmlspecialchars($_POST['position'] ?? '') ?>"><br><br>

        <label for="apply_date">Bewerbungsdatum *</label><br>
        <input type="date" id="apply_date" name="apply_date" required value="<?= htmlspecialchars($_POST['apply_date'] ?? '') ?>"><br><br>

        <label for="contact_person">Ansprechpartner</label><br>
        <input type="text" id="contact_person" name="contact_person" value="<?= htmlspecialchars($_POST['contact_person'] ?? '') ?>"><br><br>

        <label for="contact_email">E-Mail Ansprechpartner</label><br>
        <input type="email" id="contact_email" name="contact_email" value="<?= htmlspecialchars($_POST['contact_email'] ?? '') ?>"><br><br>

        <label for="contact_phone">Telefon Ansprechpartner</label><br>
        <input type="text" id="contact_phone" name="contact_phone" value="<?= htmlspecialchars($_POST['contact_phone'] ?? '') ?>"><br><br>

        <label for="job_link">Link zur Stellenanzeige</label><br>
        <input type="url" id="job_link" name="job_link" value="<?= htmlspecialchars($_POST['job_link'] ?? '') ?>"><br><br>

        <label for="notes">Notizen</label><br>
        <textarea id="notes" name="notes" rows="4"><?= htmlspecialchars($_POST['notes'] ?? '') ?></textarea><br><br>

        <button type="submit">Bewerbung speichern</button>
        <a href="dashboard.php">Abbrechen</a>
    </form>

</body>
</html>