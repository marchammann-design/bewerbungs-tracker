<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';

// 1. Zugriffsschutz
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// 2. ID aus der URL holen und validieren
$app_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$app_id) {
    header('Location: dashboard.php');
    exit;
}

// 3. Bewerbung laden (mit Ownership-Check)
$stmtApp = $pdo->prepare("SELECT * FROM applications WHERE application_id = :id AND user_id = :user_id");
$stmtApp->execute([':id' => $app_id, ':user_id' => $user_id]);
$app = $stmtApp->fetch();

// Wenn nicht gefunden oder fremde ID -> Rauswerfen
if (!$app) {
    header('Location: dashboard.php');
    exit;
}

// 4. Historie laden (Chronologisch aufsteigend)
$stmtHist = $pdo->prepare("
    SELECT old_status, new_status, changed_at 
    FROM application_history 
    WHERE application_id = :id 
    ORDER BY changed_at ASC
");
$stmtHist->execute([':id' => $app_id]);
$history = $stmtHist->fetchAll();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detailansicht: <?= htmlspecialchars($app['company']) ?></title>
</head>
<body>

    <header>
        <h1>Detailansicht</h1>
        <p><a href="dashboard.php">&larr; Zurück zum Dashboard</a></p>
    </header>

    <main>
        <h2><?= htmlspecialchars($app['company']) ?> - <?= htmlspecialchars($app['position']) ?></h2>

        <!-- Bewerbungs-Details -->
        <section class="details">
            <h3>Allgemeine Informationen</h3>
            <ul>
                <li><strong>Bewerbungsdatum:</strong> <?= htmlspecialchars($app['apply_date']) ?></li>
                <li><strong>Aktueller Status:</strong> <?= htmlspecialchars($app['current_status']) ?></li>
                <li><strong>Link zur Stelle:</strong> 
                    <?php if (!empty($app['job_link'])): ?>
                        <a href="<?= htmlspecialchars($app['job_link']) ?>" target="_blank" rel="noopener noreferrer">Öffnen</a>
                    <?php else: ?>
                        Kein Link hinterlegt
                    <?php endif; ?>
                </li>
            </ul>

            <h3>Kontakt</h3>
            <ul>
                <li><strong>Ansprechpartner:</strong> <?= htmlspecialchars($app['contact_person'] ?: 'Nicht angegeben') ?></li>
                <li><strong>E-Mail:</strong> <?= htmlspecialchars($app['contact_email'] ?: 'Nicht angegeben') ?></li>
                <li><strong>Telefon:</strong> <?= htmlspecialchars($app['contact_phone'] ?: 'Nicht angegeben') ?></li>
            </ul>

            <?php if (!empty($app['notes'])): ?>
                <h3>Notizen</h3>
                <p><?= nl2br(htmlspecialchars($app['notes'])) ?></p>
            <?php endif; ?>
        </section>

        <!-- Status-Historie (Timeline) -->
        <section class="history">
            <h3>Status-Verlauf (Historie)</h3>
            <?php if (empty($history)): ?>
                <p>Keine Historie vorhanden.</p>
            <?php else: ?>
                <ul>
                    <?php foreach ($history as $entry): ?>
                        <li>
                            <?php
                                // SQLite speichert immer in UTC. Wir rechnen es hier in die deutsche Zeit um.
                                $date = new DateTime($entry['changed_at'], new DateTimeZone('UTC'));
                                $date->setTimezone(new DateTimeZone('Europe/Berlin'));
                                ?>
                                <strong><?= $date->format('d.m.Y H:i') ?> Uhr</strong>:  
                            <?php if ($entry['old_status'] === null): ?>
                                Erstellung &rarr; <?= htmlspecialchars($entry['new_status']) ?>
                            <?php else: ?>
                                <?= htmlspecialchars($entry['old_status']) ?> &rarr; <?= htmlspecialchars($entry['new_status']) ?>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>

        <!-- Aktionen -->
        <section class="actions">
            <a href="edit.php?id=<?= (int)$app['application_id'] ?>">Bearbeiten</a>
            <form method="POST" action="delete.php" onsubmit="return confirm('Diese Bewerbung wirklich unwiderruflich löschen?');">
                <input type="hidden" name="id" value="<?= (int)$app['application_id'] ?>">
                <button type="submit">Löschen</button>
            </form>
        </section>
    </main>

</body>
</html>