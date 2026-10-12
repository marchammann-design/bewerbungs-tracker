<?php
// Zentrale Session-Verwaltung
// WICHTIG: Keine Leerzeilen vor diesem <?php Tag!

if (session_status() === PHP_SESSION_NONE) {
    
    // 1. Internen Temp-Ordner des Containers nutzen (vermeidet Docker/WSL Rechte-Probleme)
    session_save_path(sys_get_temp_dir());
    
    // 2. Sicherheitseinstellungen
    ini_set('session.cookie_lifetime', 0);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_samesite', 'Lax');
    
    // 3. Session starten
    session_start();
}
