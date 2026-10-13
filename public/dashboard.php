<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';

// Zugriffsschutz
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$success_msg = '';

// Erfolgsmeldung aus der URL lesen (nach dem Redirect von create.php)
if (isset($_GET['success']) && $_GET['success'] === 'created') {
    $success_msg = 'Bewerbung wurde erfolgreich angelegt!';
}

// Bewerbungen des aktuellen Users laden (Sortiert nach Datum, neuste zuerst)
$stmt = $pdo->prepare("
    SELECT application_id, company, position, apply_date, current_status , notes
    FROM applications 
    WHERE user_id = :user_id 
    ORDER BY apply_date DESC
");
$stmt->execute([':user_id' => $user_id]);
$applications = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>Dashboard - Bewerbungs-Tracker</title>
</head>
<body>

    <header>
        <h1>Dashboard</h1>
        <p>Eingeloggt als: <?= htmlspecialchars($_SESSION['user_email']) ?> | <a href="logout.php">Ausloggen</a></p>
    </header>

    <main>
        <?php if (!empty($success_msg)): ?>
            <div class="alert alert-success">
                <p><?= htmlspecialchars($success_msg) ?></p>
            </div>
        <?php endif; ?>

        <h2>Meine Bewerbungen</h2>
        <p><a href="create.php">+ Neue Bewerbung anlegen</a></p>

        <?php if (empty($applications)): ?>
            <p>Du hast noch keine Bewerbungen erfasst.</p>
        <?php else: ?>
            <table border="1">
                <thead>
                    <tr>
                        <th>Datum</th>
                        <th>Firma</th>
                        <th>Position</th>
                        <th>Status</th>
                        <th>Notizen</th>
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
                            <td><?= htmlspecialchars($app['notes']) ?></td>
                                                        <td>
                                <a href="edit.php?id=<?= (int)$app['application_id'] ?>">Bearbeiten</a>
                                <a href="view.php?id=<?= (int)$app['application_id'] ?>">Details</a>
                                
                                <!-- Löschen-Formular (POST-Methode für Sicherheit) -->
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
    </main>

</body>
</html>