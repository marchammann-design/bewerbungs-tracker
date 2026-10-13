<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';

$pageTitle = 'Statistik';

if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT old_status, new_status, COUNT(*) as count
    FROM (
        SELECT h.old_status, h.new_status
        FROM application_history h
        INNER JOIN applications a ON h.application_id = a.application_id
        WHERE a.user_id = :user_id AND h.old_status IS NOT NULL AND h.old_status != h.new_status
        UNION ALL
        SELECT 'Beworben' as old_status, 'Keine Rückmeldung' as new_status
        FROM applications a
        WHERE a.user_id = :user_id AND a.current_status = 'Beworben'
        AND (SELECT COUNT(*) FROM application_history h2 WHERE h2.application_id = a.application_id AND h2.old_status IS NOT NULL) = 0
    ) combined
    GROUP BY old_status, new_status
");
$stmt->execute([':user_id' => $user_id]);
$flows = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<h2>Status-Flüsse (Sankey-Diagramm)</h2>
<p>Dieses Diagramm zeigt den kompletten Weg deiner Bewerbungen durch alle Status-Änderungen.</p>

<div id="sankey_basic" class="chart-container"></div>

<?php if (empty($flows)): ?>
    <div class="alert alert-error">
        <p>Noch nicht genug Daten vorhanden. Lege Bewerbungen an und ändere deren Status mehrfach, um Flüsse zu generieren.</p>
    </div>
<?php endif; ?>

<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">
  google.charts.load('current', {'packages':['sankey']});
  google.charts.setOnLoadCallback(drawChart);

  function drawChart() {
    var data = new google.visualization.DataTable();
    data.addColumn('string', 'Von');
    data.addColumn('string', 'Nach');
    data.addColumn('number', 'Anzahl');
    data.addRows([
        <?php foreach ($flows as $flow): ?>
            ['<?= htmlspecialchars($flow['old_status']) ?>', '<?= htmlspecialchars($flow['new_status']) ?>', <?= (int)$flow['count'] ?>],
        <?php endforeach; ?>
    ]);
    var options = {
      height: 400,
      sankey: {
        node: { colors: ['#0d9488', '#16a34a', '#f59e0b', '#dc2626', '#9333ea'], label: { fontSize: 14 } },
        link: { colorMode: 'gradient', colors: ['#99f6e4', '#bbf7d0', '#fde68a', '#fecaca'] }
      }
    };
    var chart = new google.visualization.Sankey(document.getElementById('sankey_basic'));
    chart.draw(data, options);
  }
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>