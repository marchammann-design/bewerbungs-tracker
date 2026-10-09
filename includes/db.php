<?php
/**
 * Zentrale Datenbankverbindung via PDO (SQLite)
 */

// Pfad zur SQLite-Datenbank (außerhalb des Webroots)
$dbPath = __DIR__ . '/../data/database.sqlite';

// Prüfen, ob die Datei existiert
if (!file_exists($dbPath)) {
    die('Fehler: Datenbankdatei nicht gefunden unter ' . $dbPath);
}

try {
    // PDO-Verbindung herstellen
    $pdo = new PDO(
        'sqlite:' . $dbPath,
        null,
        null,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );

    // Foreign Key Constraints aktivieren (SQLite-Default ist OFF)
    // Wichtig: Muss pro Verbindung gesetzt werden
    $pdo->query('PRAGMA foreign_keys = ON;');
    
    // Prüfen, ob es geklappt hat
    $fkCheck = $pdo->query('PRAGMA foreign_keys;')->fetch(PDO::FETCH_ASSOC);
    if (!$fkCheck['foreign_keys']) {
        throw new PDOException('Foreign Keys konnten nicht aktiviert werden');
    }

} catch (PDOException $e) {
    // Im Produktivbetrieb: Fehler loggen, nicht anzeigen
    error_log('Datenbankverbindung fehlgeschlagen: ' . $e->getMessage());
    die('Datenbankfehler. Bitte später erneut versuchen.');
}