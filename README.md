# Bewerbungs-Tracker

Webbasierte Anwendung zur Verwaltung, Historisierung und Visualisierung von Bewerbungsprozessen.

## Funktionsumfang

- Registrierung & Login mit sicherem Passwort-Hashing (`password_hash` / `password_verify`)
- CRUD für Bewerbungen (Anlegen, Bearbeiten, Anzeigen, Löschen)
- Lückenlose Status-Historisierung (jeder Wechsel wird mit Zeitstempel gespeichert)
- Sankey-Diagramm zur Visualisierung von Drop-off-Raten (Google Charts API)
- Druckoptimierte Ansicht (Print-CSS) für den PDF-Export
- Responsive Design (Mobile-First)

## Tech-Stack

| Bereich     | Technologie                          |
|-------------|--------------------------------------|
| Backend     | PHP 8.x (Vanilla, kein Framework)    |
| Frontend    | HTML5, CSS3, Vanilla JavaScript      |
| Datenbank   | SQLite 3                             |
| Server      | Docker (Nginx + PHP-FPM)             |
| Charts      | Google Charts API (Sankey)           |
| Versionskontrolle | Git / GitHub                   |

## Voraussetzungen

- Docker Desktop (oder Docker Engine + Docker Compose)
- Browser (Chrome, Firefox, Edge)

## Installation & Start

```bash
# Repository klonen
git clone https://github.com/marchammann-design/bewerbungs-tracker.git
cd bewerbungs-tracker

# Container starten
docker compose up -d

# Browser öffnen
# http://localhost:8082/