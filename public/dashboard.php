<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';

// Zugriffsschutz
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Titel für den Header setzen
$pageTitle = 'Dashboard';

$user_id = $_SESSION['user_id'];
$success_msg = '';

if (isset($_GET['success']) && $_GET['success'] === 'created') {
    $success_msg = 'Bewerbung wurde erfolgreich angelegt!';
} elseif (isset($_GET['success']) && $_GET['success'] === 'updated') {
    $success_msg = 'Bewerbung wurde erfolgreich aktualisiert!';
} elseif (isset($_GET['success']) && $_GET['success'] === 'deleted') {
    $success_msg = 'Bewerbung wurde erfolgreich gelöscht.';
}

$stmt = $pdo->prepare("
    SELECT application_id, company, position, apply_date, current_status 
    FROM applications 
    WHERE user_id = :user_id 
    ORDER BY apply_date DESC
");
$stmt->execute([':user_id' => $user_id]);
$applications = $stmt->fetchAll();

// Header einbinden (ab hier beginnt das HTML)
require_once __DIR__ . '/../includes/header.php';
?>

    <h2>Meine Bewerbungen</h2>
    <p><a href="create.php" class="btn">+ Neue Bewerbung anlegen</a></p>

    <?php if (!empty($success_msg)): ?>
        <div class="alert alert-success">
            <p><?= htmlspecialchars($success_msg) ?></p>
        </div>
    <?php endif; ?>

    <?php if (empty($applications)): ?>
        <p>Du hast noch keine Bewerbungen erfasst.</p>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Datum</th>
                    <th>Firma</th>
                    <th>Position</th>
                    <th>Status</th>
                    <th>Aktionen</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($applications as $app): ?>
                    <tr>
                        <td><?= htmlspecialchars($app['apply_date']) ?></td>
                        <td><?= htmlspecialchars($app['company']) ?></td>
                        <td><?= htmlspecialchars($app['position']) ?></td>
                        <td><?= htmlspecialchars($app['current_status']) ?></td>
                        <td>
                            <a href="view.php?id=<?= (int)$app['application_id'] ?>">Details</a>
                            <a href="edit.php?id=<?= (int)$app['application_id'] ?>">Bearbeiten</a>
                            <form method="POST" action="delete.php" style="display:inline;" onsubmit="return confirm('Diese Bewerbung wirklich unwiderruflich löschen?');">
                                <input type="hidden" name="id" value="<?= (int)$app['application_id'] ?>">
                                <button type="submit" class="btn-delete">Löschen</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

<?php
// Footer einbinden
require_once __DIR__ . '/../includes/footer.php';
?>