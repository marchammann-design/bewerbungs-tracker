<?php
/**
 * Zentrale Datenbankverbindung via PDO (SQLite)
 * Mit automatischer Initialisierung für frische Clones
 */

$dbDir = __DIR__ . '/../db';
$dbPath = $dbDir . '/database.sqlite';
$initSqlPath = $dbDir . '/init.sql';

// 1. Verzeichnis erstellen, falls nicht vorhanden
if (!is_dir($dbDir)) {
    mkdir($dbDir, 0777, true);
}

try {
    // PDO erstellt die Datei automatisch, wenn das Verzeichnis schreibbar ist
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    
    // Foreign Key Constraints aktivieren (SQLite-Default ist OFF)
    $pdo->query('PRAGMA foreign_keys = ON;');
    
    // 2. Prüfen, ob die Datenbank initialisiert werden muss (Tabelle 'users' existiert nicht)
    $result = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='users'");
    if ($result->fetchColumn() === false && file_exists($initSqlPath)) {
        $sql = file_get_contents($initSqlPath);
        $pdo->exec($sql);
    }
} catch (PDOException $e) {
    error_log('Datenbankverbindung fehlgeschlagen: ' . $e->getMessage());
    die('Datenbankfehler. Bitte später erneut versuchen.');
}