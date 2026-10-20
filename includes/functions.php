<?php
/**
 * Gibt die passende CSS-Klasse für einen Bewerbungsstatus zurück.
 * 
 * @param string $status Der aktuelle Bewerbungsstatus
 * @return string Die CSS-Klasse für das Badge
 */
function getStatusBadgeClass(string $status): string {
    $status = strtolower($status);
    
    if (str_contains($status, 'absage') || str_contains($status, 'abgelehnt') || str_contains($status, 'geghostet')) {
        return 'badge-absage';
    }
    if (str_contains($status, 'zusage') || str_contains($status, 'vertrag') || str_contains($status, 'angenommen')) {
        return 'badge-zusage';
    }
    if (str_contains($status, 'prüfung') || str_contains($status, 'gespräch') || str_contains($status, 'einladung')) {
        return 'badge-pruefung';
    }
    if (str_contains($status, 'beworben')) {
        return 'badge-beworben';
    }
    
    return 'badge-default';
}