<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';

// 1. Zugriffsschutz
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// 2. ID aus POST holen (NICHT aus GET!)
$app_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$app_id) {
    // Ungültige Anfrage, zurück zum Dashboard
    header('Location: dashboard.php');
    exit;
}

// 3. Ownership-Check: Prüfen, ob die Bewerbung dem User gehört
// Wir nutzen hier ein SELECT, um sicherzugehen, bevor wir löschen.
$stmt = $pdo->prepare("SELECT application_id FROM applications WHERE application_id = :id AND user_id = :user_id");
$stmt->execute([':id' => $app_id, ':user_id' => $user_id]);
$app = $stmt->fetch();

if (!$app) {
    // Bewerbung existiert nicht oder gehört einem anderen User
    header('Location: dashboard.php');
    exit;
}

// 4. Löschen ausführen
try {
    // Dank "ON DELETE CASCADE" im Datenbankschema werden die zugehörigen 
    // Historien-Einträge in application_history automatisch mitgelöscht.
    $stmtDelete = $pdo->prepare("DELETE FROM applications WHERE application_id = :id AND user_id = :user_id");
    $stmtDelete->execute([':id' => $app_id, ':user_id' => $user_id]);
    
    header('Location: dashboard.php?success=deleted');
    exit;

} catch (PDOException $e) {
    error_log('Delete Error: ' . $e->getMessage());
    header('Location: dashboard.php?error=delete_failed');
    exit;
}