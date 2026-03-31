<?php
/**
 * Changelog data
 * This file contains all version history and changelog entries
 */

$changelog = [
    [
        'version' => '1.1.3',
        'date' => '2026-04-01',
        'changes' => [
            'Statische Assets (CSS, JS, Leaflet, Logo): URL-Pfad wird aus dem Skript-Pfad ermittelt (`getPublicAssetUrlPath`) – funktioniert auch, wenn die Anwendung in einem Unterverzeichnis liegt (nicht mehr fester Pfad `/public/…` von der Domainwurzel)',
            '`.gitignore`: nur noch `/vendor/` im Projektroot (Composer); `public/vendor/leaflet/` wird versioniert und beim Deploy mit ausgeliefert (behebt 404 für Leaflet auf dem Server)',
        ],
        'bugfixes' => [
        ],
        'new_features' => [
        ]
    ],
    [
        'version' => '1.1.2',
        'date' => '2026-03-31',
        'changes' => [
            'Flug-Dienstbuch-API: Flugstandorte werden nur noch aus den letzten 7 Tagen geladen (passend zum Flug-Dienstbuch)',
            'HTTP-Zugriff auf die Dashboard-API über PHP-Streams, falls cURL nicht installiert ist (z. B. „Verbindung testen“ und alle API-Aufrufe)',
            'Content-Security-Policy: Leaflet (Karte) lokal ausgeliefert; Nominatim für Geocoding in connect-src erlaubt',
        ],
        'bugfixes' => [
            'Admin: „Verbindung testen“ führt nicht mehr zu HTTP 500, wenn die Datenbank nicht erreichbar ist (Test läuft vor DB-Verbindung)',
            'Admin: API-Test funktioniert ohne php-curl, sofern allow_url_fopen aktiv ist',
        ],
        'new_features' => [
            'Bei aktivierter Dashboard-API: Personal wird bei jedem Start eines neuen Einsatzes (Formular und REST-API) mit dem Flug-Dienstbuch abgeglichen',
            'Neuer Einsatz: optional „Als Flugstandort im Flug-Dienstbuch anlegen“ (Koordinaten + Adresse per API)',
            'Neuer Einsatz: Karte im Dialog (Leaflet), Ortssuche per Adresse/Stadt (Nominatim), Alternative wenn GPS ausfällt',
        ]
    ],
    [
        'version' => '1.1.1',
        'date' => '2026-02-11',
        'changes' => [
        ],
        'bugfixes' => [
            'Piloten mit ungültigen Lizenzen können nicht mehr als Drohnenpilot ausgewählt werden. (Co-Pilot weiterhin möglich)',
        ],
        'new_features' => [
        ]
    ],
    [
        'version' => '1.1.0',
        'date' => '2026-02-08',
        'changes' => [
            'Vollständige Code-Umstrukturierung mit MVC-Pattern',
            'RESTful API-Architektur implementiert',
            'Neue organisierte Ordnerstruktur (app/, api/, views/, public/)',
            'Alle Backward-Compatibility-Wrapper entfernt',
            'Front-Controller-Pattern für Web-Seiten',
            'PSR-4 Autoloader für Klassen',
            'Moderne API-Endpunkte unter /api/v1/',
            'Alle JavaScript-Dateien auf RESTful API umgestellt',
            'Alle Views auf neue Struktur aktualisiert',
            'Klarere Trennung von Controller, Model und View',
            'Middleware-System für Authentifizierung, CSRF-Schutz und Rate Limiting',
            'Service-Layer für Business-Logik',
            'Verbesserte Code-Organisation und Wartbarkeit'
        ],
        'bugfixes' => [

        ],
        'new_features' => [
            'Drohnen Mission Mapper-Beta jetzt verfügbar',
            'RESTful API für alle Funktionen',
            'Moderne MVC-Architektur',
            'Service-Layer für Business-Logik',
            'Dashboard-Integration über APIhinzugefügt',      
            'Middleware-System für Sicherheit',
            'PSR-4 Autoloading',
            'Front-Controller für Web-Seiten',
            'Organisierte Projektstruktur',
            'Verbesserte API-Dokumentation',
            'Wiki Link hinzugefügt'
        ]
    ],
    [
        'version' => '1.0.1',
        'date' => '2026-01-11',
        'changes' => [
        ],
        'bugfixes' => [
            // List of bugfixes
        ],
        'new_features' => [
            'Buy Me a Coffee Button hinzugefügt',
            'Update-Tool für direkte Aktualisierung von GitHub Releases',
            'Changelog-Seite hinzugefügt',
            'Update-Tool Integration hinzugefügt',
            'Fehlende Bibliotheken werden in Admin-Bereich neu installieren'
        ]
    ],
    [
        'version' => '1.0.0',
        'date' => '2025-11-29',
        'changes' => [
            'Erste stabile Version'
        ],
        'bugfixes' => [
            // No bugfixes in initial version
        ],
        'new_features' => [
            'Flugprotokoll-Verwaltung',
            'Pilot-Verwaltung',
            'Batterie-Verfolgung',
            'Standort-Verwaltung',
            'Dashboard mit Flugstatistiken',
            'Sichere Authentifizierung',
            'PWA-Unterstützung',
            'Admin-Funktionalität'
        ]
    ]
];
