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

- **Windows-Nutzer**: 
  - WSL 2 (Windows Subsystem for Linux) muss installiert und aktiviert sein.
  - Docker Desktop muss installiert sein, und die WSL 2-Integration muss in den Docker-Einstellungen aktiviert sein.
- **Linux/macOS-Nutzer**: Docker Engine und Docker Compose müssen installiert sein.
- Ein moderner Browser (Chrome, Firefox, Edge).

## Installation & Start

```bash
# Repository klonen (innerhalb von WSL oder nativem Linux/macOS)
git clone https://github.com/marchammann-design/bewerbungs-tracker.git
cd bewerbungs-tracker

# Container starten (erstellt die Datenbank beim ersten Aufruf automatisch)
docker compose up -d

# Browser öffnen
# http://localhost:8082/