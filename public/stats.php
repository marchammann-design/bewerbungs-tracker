<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';

// 1. Zugriffsschutz
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// 2. Daten für das Sankey-Diagramm laden
// Query 1: Alle echten Status-Wechsel aus der Historie
// Query 2: Künstlicher Fluss für unbearbeitete Bewerbungen (Beworben -> Keine Rückmeldung)
// UNION ALL kombiniert beide Ergebnisse
$stmt = $pdo->prepare("
    SELECT old_status, new_status, COUNT(*) as count
    FROM (
        -- Teil 1: Echte Status-Wechsel aus der Historie
        SELECT h.old_status, h.new_status
        FROM application_history h
        INNER JOIN applications a ON h.application_id = a.application_id
        WHERE a.user_id = :user_id
        AND h.old_status IS NOT NULL
        AND h.old_status != h.new_status
        
        UNION ALL
        
        -- Teil 2: Künstlicher Fluss für unbearbeitete Bewerbungen
        SELECT 'Beworben' as old_status, 'Keine Rückmeldung' as new_status
        FROM applications a
        WHERE a.user_id = :user_id
        AND a.current_status = 'Beworben'
        AND (SELECT COUNT(*) FROM application_history h2 WHERE h2.application_id = a.application_id AND h2.old_status IS NOT NULL) = 0
    ) combined
    GROUP BY old_status, new_status
");
$stmt->execute([':user_id' => $user_id]);
$flows = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistik - Bewerbungs-Tracker</title>
    
    <!-- Google Charts API laden -->
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    
    <!-- CSS für das Diagramm -->
    <style>
        .chart-container {
            width: 100%;
            max-width: 1300px;
            height: 800px;
            margin: 20px 0;
        }
    </style>

    <script type="text/javascript">
      google.charts.load('current', {'packages':['sankey']});
      google.charts.setOnLoadCallback(drawChart);

      function drawChart() {
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Von');
        data.addColumn('string', 'Nach');
        data.addColumn('number', 'Anzahl');

        // PHP-Array in JavaScript-Array umwandeln
        data.addRows([
            <?php foreach ($flows as $flow): ?>
                ['<?= htmlspecialchars($flow['old_status']) ?>', '<?= htmlspecialchars($flow['new_status']) ?>', <?= (int)$flow['count'] ?>],
            <?php endforeach; ?>
        ]);

        var options = {
          height: 500,
          sankey: {
            node: {
              colors: ['#1a73e8', '#34a853', '#fbbc04', '#ea4335', '#9c27b0'],
              label: { fontSize: 14 }
            },
            link: {
              colorMode: 'gradient',
              colors: ['#a6cee3', '#b2df8a', '#fb9a99', '#fdbf6f']
            }
          }
        };

        var chart = new google.visualization.Sankey(document.getElementById('sankey_basic'));
        chart.draw(data, options);
      }
    </script>
</head>
<body>

    <header>
        <h1>Status-Flüsse (Sankey-Diagramm)</h1>
        <p><a href="dashboard.php">&larr; Zurück zum Dashboard</a></p>
    </header>

    <main>
        <p>Dieses Diagramm zeigt den kompletten Weg deiner Bewerbungen durch alle Status-Änderungen.</p>
        
        <!-- Container für das Diagramm -->
        <div id="sankey_basic" class="chart-container"></div>

        <?php if (empty($flows)): ?>
            <div class="alert alert-error">
                <p>Noch nicht genug Daten vorhanden. Lege Bewerbungen an und ändere deren Status mehrfach, um Flüsse zu generieren.</p>
            </div>
        <?php endif; ?>
    </main>

</body>
</html>