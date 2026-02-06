<?php
/**
 * Changelog data
 * This file contains all version history and changelog entries
 */

$changelog = [
    [
        'version' => '1.1.0',
        'date' => '2026-02-XX',
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
