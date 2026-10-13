<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';

$pageTitle = 'Bewerbung bearbeiten';

if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
$user_id = $_SESSION['user_id'];
$errors = [];

$app_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$app_id) { header('Location: dashboard.php'); exit; }

$stmt = $pdo->prepare("SELECT * FROM applications WHERE application_id = :id AND user_id = :user_id");
$stmt->execute([':id' => $app_id, ':user_id' => $user_id]);
$app = $stmt->fetch();
if (!$app) { header('Location: dashboard.php'); exit; }

$status_options = ['Beworben', 'In Prüfung', 'Einladung zum Gespräch', 'Absage erhalten', 'Zusage erhalten', 'Vertrag unterschrieben'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $company = trim($_POST['company'] ?? ''); $position = trim($_POST['position'] ?? ''); $apply_date = trim($_POST['apply_date'] ?? '');
    $contact_person = trim($_POST['contact_person'] ?? ''); $contact_email = trim($_POST['contact_email'] ?? '');
    $contact_phone = trim($_POST['contact_phone'] ?? ''); $job_link = trim($_POST['job_link'] ?? ''); $notes = trim($_POST['notes'] ?? '');
    $new_status = trim($_POST['current_status'] ?? '');

    if (empty($company)) $errors[] = 'Firma ist ein Pflichtfeld.';
    if (empty($position)) $errors[] = 'Position ist ein Pflichtfeld.';
    if (empty($apply_date)) $errors[] = 'Bewerbungsdatum ist ein Pflichtfeld.';

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            $stmtUpdate = $pdo->prepare("UPDATE applications SET company=:company, position=:position, apply_date=:apply_date, contact_person=:contact_person, contact_email=:contact_email, contact_phone=:contact_phone, job_link=:job_link, notes=:notes, current_status=:current_status, updated_at=CURRENT_TIMESTAMP WHERE application_id=:id AND user_id=:user_id");
            $stmtUpdate->execute([':company'=>$company, ':position'=>$position, ':apply_date'=>$apply_date, ':contact_person'=>$contact_person, ':contact_email'=>$contact_email, ':contact_phone'=>$contact_phone, ':job_link'=>$job_link, ':notes'=>$notes, ':current_status'=>$new_status, ':id'=>$app_id, ':user_id'=>$user_id]);
            if ($app['current_status'] !== $new_status) {
                $stmtHist = $pdo->prepare("INSERT INTO application_history (application_id, old_status, new_status) VALUES (:id, :old_status, :new_status)");
                $stmtHist->execute([':id'=>$app_id, ':old_status'=>$app['current_status'], ':new_status'=>$new_status]);
            }
            $pdo->commit(); header('Location: dashboard.php?success=updated'); exit;
        } catch (PDOException $e) { $pdo->rollBack(); $errors[] = 'Fehler beim Speichern.'; }
    }
    $app['company'] = $_POST['company'] ?? $app['company']; $app['position'] = $_POST['position'] ?? $app['position'];
    $app['apply_date'] = $_POST['apply_date'] ?? $app['apply_date']; $app['contact_person'] = $_POST['contact_person'] ?? $app['contact_person'];
    $app['contact_email'] = $_POST['contact_email'] ?? $app['contact_email']; $app['contact_phone'] = $_POST['contact_phone'] ?? $app['contact_phone'];
    $app['job_link'] = $_POST['job_link'] ?? $app['job_link']; $app['notes'] = $_POST['notes'] ?? $app['notes'];
    $app['current_status'] = $_POST['current_status'] ?? $app['current_status'];
}
require_once __DIR__ . '/../includes/header.php';
?>

<h2>Bewerbung bearbeiten</h2>

<div id="messages">
    <?php if (!empty($errors)): ?>
        <div class="alert alert-error"><ul><?php foreach ($errors as $error): ?><li><?= htmlspecialchars($error) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>
</div>

<form method="POST" action="edit.php?id=<?= (int)$app_id ?>">
    <label for="company">Firma *</label>
    <input type="text" id="company" name="company" required value="<?= htmlspecialchars($app['company']) ?>">

    <label for="position">Position *</label>
    <input type="text" id="position" name="position" required value="<?= htmlspecialchars($app['position']) ?>">

    <label for="apply_date">Bewerbungsdatum *</label>
    <input type="date" id="apply_date" name="apply_date" required value="<?= htmlspecialchars($app['apply_date']) ?>">

    <label for="contact_person">Ansprechpartner</label>
    <input type="text" id="contact_person" name="contact_person" value="<?= htmlspecialchars($app['contact_person']) ?>">

    <label for="contact_email">E-Mail Ansprechpartner</label>
    <input type="email" id="contact_email" name="contact_email" value="<?= htmlspecialchars($app['contact_email']) ?>">

    <label for="contact_phone">Telefon</label>
    <input type="text" id="contact_phone" name="contact_phone" value="<?= htmlspecialchars($app['contact_phone']) ?>">

    <label for="job_link">Link zur Stelle</label>
    <input type="url" id="job_link" name="job_link" value="<?= htmlspecialchars($app['job_link']) ?>">

    <label for="notes">Notizen</label>
    <textarea id="notes" name="notes" rows="4"><?= htmlspecialchars($app['notes']) ?></textarea>

    <label for="current_status">Aktueller Status</label>
    <select id="current_status" name="current_status">
        <?php foreach ($status_options as $option): ?>
            <option value="<?= htmlspecialchars($option) ?>" <?= $app['current_status'] === $option ? 'selected' : '' ?>><?= htmlspecialchars($option) ?></option>
        <?php endforeach; ?>
    </select>

    <button type="submit">Änderungen speichern</button>
    <a href="dashboard.php" class="btn btn-secondary">Abbrechen</a>
</form>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>