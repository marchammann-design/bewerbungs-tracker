<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';

// Wenn der Account ncht eingeloggt ist wird er zu Login zurück geworfen und das Script beendet
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$errors = [];
$success = '';

// 2. ID aus der URL holen und prüfen
$app_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$app_id) {
    header('Location: dashboard.php');
    exit;
}

// 3. Bewerbung laden UND prüfen, ob sie dem User gehört
$stmt = $pdo->prepare("SELECT * FROM applications WHERE application_id = :id AND user_id = :user_id");
$stmt->execute([':id' => $app_id, ':user_id' => $user_id]);
$app = $stmt->fetch();

// Wenn keine Bewerbung gefunden wurde (oder fremde ID), rauswerfen
if (!$app) {
    header('Location: dashboard.php');
    exit;
}

// 4. POST-Handling (Formular wurde abgesendet)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $company = trim($_POST['company'] ?? '');
    $position = trim($_POST['position'] ?? '');
    $apply_date = trim($_POST['apply_date'] ?? '');
    $contact_person = trim($_POST['contact_person'] ?? '');
    $contact_email = trim($_POST['contact_email'] ?? '');
    $contact_phone = trim($_POST['contact_phone'] ?? '');
    $job_link = trim($_POST['job_link'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    $new_status = trim($_POST['current_status'] ?? '');

    // Validierung
    if (empty($company)) $errors[] = 'Firma ist ein Pflichtfeld.';
    if (empty($position)) $errors[] = 'Position ist ein Pflichtfeld.';
    if (empty($apply_date)) $errors[] = 'Bewerbungsdatum ist ein Pflichtfeld.';

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // A) Bewerbung aktualisieren
            $stmtUpdate = $pdo->prepare("
                UPDATE applications 
                SET company = :company, position = :position, apply_date = :apply_date, 
                    contact_person = :contact_person, contact_email = :contact_email, 
                    contact_phone = :contact_phone, job_link = :job_link, notes = :notes, 
                    current_status = :current_status, updated_at = CURRENT_TIMESTAMP
                WHERE application_id = :id AND user_id = :user_id
            ");
            $stmtUpdate->execute([
                ':company' => $company, ':position' => $position, ':apply_date' => $apply_date,
                ':contact_person' => $contact_person, ':contact_email' => $contact_email,
                ':contact_phone' => $contact_phone, ':job_link' => $job_link, ':notes' => $notes,
                ':current_status' => $new_status, ':id' => $app_id, ':user_id' => $user_id
            ]);

            // B) Historie prüfen: Hat sich der Status geändert?
            if ($app['current_status'] !== $new_status) {
                $stmtHist = $pdo->prepare("
                    INSERT INTO application_history (application_id, old_status, new_status) 
                    VALUES (:id, :old_status, :new_status)
                ");
                $stmtHist->execute([
                    ':id' => $app_id, 
                    ':old_status' => $app['current_status'], 
                    ':new_status' => $new_status
                ]);
            }

            $pdo->commit();
            header('Location: dashboard.php?success=updated');
            exit;

        } catch (PDOException $e) {
            $pdo->rollBack();
            $errors[] = 'Fehler beim Speichern.';
            error_log('Update Error: ' . $e->getMessage());
        }
    }
    
    // Bei Fehlern oder erneutem Laden: Daten aus POST übernehmen, sonst aus DB
    $app['company'] = $_POST['company'] ?? $app['company'];
    $app['position'] = $_POST['position'] ?? $app['position'];
    $app['apply_date'] = $_POST['apply_date'] ?? $app['apply_date'];
    $app['contact_person'] = $_POST['contact_person'] ?? $app['contact_person'];
    $app['contact_email'] = $_POST['contact_email'] ?? $app['contact_email'];
    $app['contact_phone'] = $_POST['contact_phone'] ?? $app['contact_phone'];
    $app['job_link'] = $_POST['job_link'] ?? $app['job_link'];
    $app['notes'] = $_POST['notes'] ?? $app['notes'];
    $app['current_status'] = $_POST['current_status'] ?? $app['current_status'];
}

// Mögliche Status-Optionen
$status_options = ['Beworben', 'Einladung zum Gespräch', 'Absage erhalten', 'Zusage erhalten', 'Vertrag unterschrieben', 'Geghostet'];
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>Bewerbung bearbeiten</title>
</head>
<body>

    <h1>Bewerbung bearbeiten</h1>

    <div id="messages">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul><?php foreach ($errors as $error): ?><li><?= htmlspecialchars($error) ?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>
    </div>

    <form method="POST" action="edit.php?id=<?= (int)$app_id ?>">
        <label for="company">Firma *</label><br>
        <input type="text" id="company" name="company" required value="<?= htmlspecialchars($app['company']) ?>"><br><br>

        <label for="position">Position *</label><br>
        <input type="text" id="position" name="position" required value="<?= htmlspecialchars($app['position']) ?>"><br><br>

        <label for="apply_date">Bewerbungsdatum *</label><br>
        <input type="date" id="apply_date" name="apply_date" required value="<?= htmlspecialchars($app['apply_date']) ?>"><br><br>

        <label for="contact_person">Ansprechpartner</label><br>
        <input type="text" id="contact_person" name="contact_person" value="<?= htmlspecialchars($app['contact_person']) ?>"><br><br>

        <label for="contact_email">E-Mail Ansprechpartner</label><br>
        <input type="email" id="contact_email" name="contact_email" value="<?= htmlspecialchars($app['contact_email']) ?>"><br><br>

        <label for="contact_phone">Telefon</label><br>
        <input type="text" id="contact_phone" name="contact_phone" value="<?= htmlspecialchars($app['contact_phone']) ?>"><br><br>

        <label for="job_link">Link zur Stelle</label><br>
        <input type="url" id="job_link" name="job_link" value="<?= htmlspecialchars($app['job_link']) ?>"><br><br>

        <label for="notes">Notizen</label><br>
        <textarea id="notes" name="notes" rows="4"><?= htmlspecialchars($app['notes']) ?></textarea><br><br>

        <label for="current_status">Aktueller Status</label><br>
        <select id="current_status" name="current_status">
            <?php foreach ($status_options as $option): ?>
                <option value="<?= htmlspecialchars($option) ?>" <?= $app['current_status'] === $option ? 'selected' : '' ?>>
                    <?= htmlspecialchars($option) ?>
                </option>
            <?php endforeach; ?>
        </select><br><br>

        <button type="submit">Änderungen speichern</button>
        <a href="dashboard.php">Abbrechen</a>
    </form>

</body>
</html>