<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';

$pageTitle = 'Neue Bewerbung anlegen';

if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $company = trim($_POST['company'] ?? '');
    $position = trim($_POST['position'] ?? '');
    $apply_date = trim($_POST['apply_date'] ?? '');
    $contact_person = trim($_POST['contact_person'] ?? '');
    $contact_email = trim($_POST['contact_email'] ?? '');
    $contact_phone = trim($_POST['contact_phone'] ?? '');
    $job_link = trim($_POST['job_link'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    $current_status = 'Beworben';
    $user_id = $_SESSION['user_id'];

    if (empty($company)) $errors[] = 'Firma ist ein Pflichtfeld.';
    if (empty($position)) $errors[] = 'Position ist ein Pflichtfeld.';
    if (empty($apply_date)) $errors[] = 'Bewerbungsdatum ist ein Pflichtfeld.';

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            $stmtApp = $pdo->prepare("INSERT INTO applications (user_id, company, position, apply_date, contact_person, contact_email, contact_phone, job_link, notes, current_status) VALUES (:user_id, :company, :position, :apply_date, :contact_person, :contact_email, :contact_phone, :job_link, :notes, :current_status)");
            $stmtApp->execute([':user_id' => $user_id, ':company' => $company, ':position' => $position, ':apply_date' => $apply_date, ':contact_person' => $contact_person, ':contact_email' => $contact_email, ':contact_phone' => $contact_phone, ':job_link' => $job_link, ':notes' => $notes, ':current_status' => $current_status]);
            $application_id = $pdo->lastInsertId();
            $stmtHist = $pdo->prepare("INSERT INTO application_history (application_id, old_status, new_status) VALUES (:application_id, NULL, :new_status)");
            $stmtHist->execute([':application_id' => $application_id, ':new_status' => $current_status]);
            $pdo->commit();
            header('Location: dashboard.php?success=created'); exit;
        } catch (PDOException $e) {
            $pdo->rollBack();
            $errors[] = 'Ein Datenbankfehler ist aufgetreten.';
            error_log('Create Error: ' . $e->getMessage());
        }
    }
}
require_once __DIR__ . '/../includes/header.php';
?>

<h2>Neue Bewerbung anlegen</h2>

<div id="messages">
    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <ul><?php foreach ($errors as $error): ?><li><?= htmlspecialchars($error) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>
</div>

<form method="POST" action="create.php">
    <label for="company">Firma *</label>
    <input type="text" id="company" name="company" required value="<?= htmlspecialchars($_POST['company'] ?? '') ?>">

    <label for="position">Position *</label>
    <input type="text" id="position" name="position" required value="<?= htmlspecialchars($_POST['position'] ?? '') ?>">

    <label for="apply_date">Bewerbungsdatum *</label>
    <input type="date" id="apply_date" name="apply_date" required value="<?= htmlspecialchars($_POST['apply_date'] ?? '') ?>">

    <label for="contact_person">Ansprechpartner</label>
    <input type="text" id="contact_person" name="contact_person" value="<?= htmlspecialchars($_POST['contact_person'] ?? '') ?>">

    <label for="contact_email">E-Mail Ansprechpartner</label>
    <input type="email" id="contact_email" name="contact_email" value="<?= htmlspecialchars($_POST['contact_email'] ?? '') ?>">

    <label for="contact_phone">Telefon Ansprechpartner</label>
    <input type="text" id="contact_phone" name="contact_phone" value="<?= htmlspecialchars($_POST['contact_phone'] ?? '') ?>">

    <label for="job_link">Link zur Stellenanzeige</label>
    <input type="url" id="job_link" name="job_link" value="<?= htmlspecialchars($_POST['job_link'] ?? '') ?>">

    <label for="notes">Notizen</label>
    <textarea id="notes" name="notes" rows="4"><?= htmlspecialchars($_POST['notes'] ?? '') ?></textarea>

    <button type="submit">Bewerbung speichern</button>
    <a href="dashboard.php" class="btn btn-secondary">Abbrechen</a>
</form>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>