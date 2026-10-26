<?php
/**
 * Zentrale Datenbankverbindung via PDO (SQLite)
 * Mit automatischer Initialisierung für frische Clones
 */

$dbDir = __DIR__ . '/../db';
$dbPath = $dbDir . '/database.sqlite';
$initSqlPath = $dbDir . '/init.sql';

// 1. Verzeichnis erstellen, falls nicht vorhanden (wichtig für frischen Clone)
if (!is_dir($dbDir)) {
    mkdir($dbDir, 0777, true);
}

// 2. Datenbankdatei erstellen, falls sie nicht existiert
if (!file_exists($dbPath)) {
    touch($dbPath);
    chmod($dbPath, 0666); // Schreibrechte für den PHP-Container-User (www-data) sicherstellen
}

try {
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // 3. Prüfen, ob die Datenbank leer ist (keine Tabellen). Wenn ja, init.sql ausführen.
    $result = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='users'");
    if ($result->fetchColumn() === false && file_exists($initSqlPath)) {
        $sql = file_get_contents($initSqlPath);
        $pdo->exec($sql);
    }
} catch (PDOException $e) {
    // Im Produktivbetrieb sollte dies in ein Log geschrieben werden, nicht ausgegeben
    die('Datenbankfehler: ' . $e->getMessage());
}