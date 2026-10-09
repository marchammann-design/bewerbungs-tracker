<?php
require_once __DIR__ . '/../includes/db.php';

// Test: Tabellen abfragen
$tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name;")->fetchAll();

echo "<h1>Datenbank-Test</h1>";
echo "<p>Verbindung erfolgreich!</p>";
echo "<h2>Vorhandene Tabellen:</h2>";
echo "<ul>";
foreach ($tables as $table) {
    echo "<li>" . htmlspecialchars($table['name']) . "</li>";
}
echo "</ul>";

// Test: Foreign Keys prüfen (mit explizitem FETCH_NUM)
$fkStatus = $pdo->query("PRAGMA foreign_keys;")->fetch(PDO::FETCH_NUM);
echo "<p>Foreign Keys aktiv: " . ($fkStatus[0] ? 'Ja' : 'Nein') . "</p>";