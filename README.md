# Drohnen-Einsatztagebuch

Ein webbasiertes Einsatztagebuch für Drohneneinsätze mit Dokumentationsfunktionen, PDF-Export und Integration mit dem Flug Dashboard.

📖 **Ausführliche Anleitung**: [Wiki](https://github.com/denni95112/drohnen-einsatztagebuch/wiki)

## 📋 Beschreibung

Das Drohnen-Einsatztagebuch ist eine PHP-basierte Webanwendung zur Dokumentation von Drohneneinsätzen. Es ermöglicht die Erfassung von Einsatzdaten, Personal, Drohnen, Flugdaten und der vollständigen Einsatzdokumentation. Die Anwendung generiert PDF-Berichte und kann optional mit einem Flug Dashboard integriert werden.

Die Anwendung verwendet eine moderne, RESTful API-basierte Architektur mit klarer Trennung von Controller, Model und View (MVC-Pattern).

## ✨ Features

- **Einsatzverwaltung**: Erfassung und Verwaltung von Drohneneinsätzen
- **Personaldokumentation**: Verwaltung von Personal und Zuordnung zu Einsätzen
- **Drohnenverwaltung**: Verwaltung der verfügbaren Drohnen
- **Live-Dokumentation**: Echtzeit-Dokumentation während des Einsatzes
- **Quick Actions**: Schnelle Aktionen für Drohnenflüge (Start/Landung, Personensuche, Warnungen)
- **PDF-Export**: Automatische Generierung von Einsatzberichten als PDF
- **QR-Code**: Generierung von QR-Codes für den Lese-Modus
- **Lese-Modus**: Öffentlicher Lese-Modus mit Token-basiertem Zugriff
- **Dashboard-Integration**: Optional Integration mit Flug Dashboard für automatische Flugdatenübertragung
- **GPS-Integration**: Automatische Adressermittlung über GPS-Koordinaten
- **RESTful API**: Moderne API-Architektur für alle Funktionen
- **Automatische Bibliotheksinstallation**: Automatischer Download und Installation benötigter Bibliotheken
- **Auto-Updater**: Automatisches Update-System mit direkter Integration in die Weboberfläche
- **Version-Benachrichtigungen**: Automatische Benachrichtigung über verfügbare Updates im Header
- **Sichere Updates**: Automatische Backups vor Updates mit Rollback-Funktionalität

## Screenshots
<p float="left">
  <img src="https://github.com/user-attachments/assets/51af15f9-aebf-4c76-beab-a5310547c811" width="150" />
  <img src="https://github.com/user-attachments/assets/520e179d-4729-4474-9a41-bb8f0b0db9f0" width="150" />
  <img src="https://github.com/user-attachments/assets/ee7cdb43-079c-41d1-919b-7f4806e8962b" width="150" />
  <img src="https://github.com/user-attachments/assets/d84bdc43-6c8d-4f1f-a25e-f192124108a1" width="150" />
  <img src="https://github.com/user-attachments/assets/380f5333-9a6c-4020-addc-4daf425d1110" width="150" />
  <img src="https://github.com/user-attachments/assets/efe93dec-3e50-4021-addc-57181499f16f" width="150" />
</p>

## 📖 Verwendung & Dokumentation

Die ausführliche Bedienungsanleitung mit allen Funktionen findet sich im **[Wiki](https://github.com/denni95112/drohnen-einsatztagebuch/wiki)**:

| Thema | Wiki-Seite |
|-------|------------|
| Einstieg | [Einrichtung](https://github.com/denni95112/drohnen-einsatztagebuch/wiki/Einrichtung), [Anmeldung (Login)](https://github.com/denni95112/drohnen-einsatztagebuch/wiki/Anmeldung-Login), [Startseite](https://github.com/denni95112/drohnen-einsatztagebuch/wiki/Startseite) |
| Einsätze | [Einsatzliste](https://github.com/denni95112/drohnen-einsatztagebuch/wiki/Einsatzliste), [Neuer Einsatz](https://github.com/denni95112/drohnen-einsatztagebuch/wiki/Neuer-Einsatz), [Dokumentation](https://github.com/denni95112/drohnen-einsatztagebuch/wiki/Dokumentation) |
| Stammdaten | [Personal verwalten](https://github.com/denni95112/drohnen-einsatztagebuch/wiki/Personal-verwalten), [Drohnen verwalten](https://github.com/denni95112/drohnen-einsatztagebuch/wiki/Drohnen-verwalten) |
| Export & Teilen | [PDF-Export](https://github.com/denni95112/drohnen-einsatztagebuch/wiki/PDF-Export), [Lese-Modus und QR-Code](https://github.com/denni95112/drohnen-einsatztagebuch/wiki/Lese-Modus-QR-Code) |
| Admin & Integration | [Admin-Login](https://github.com/denni95112/drohnen-einsatztagebuch/wiki/Admin-Login), [Dashboard-Integration](https://github.com/denni95112/drohnen-einsatztagebuch/wiki/Dashboard-Integration), [Updates](https://github.com/denni95112/drohnen-einsatztagebuch/wiki/Updates) |
| Sonstiges | [Kopfzeilen-Benachrichtigungen](https://github.com/denni95112/drohnen-einsatztagebuch/wiki/Kopfzeilen-Benachrichtigungen), [Über](https://github.com/denni95112/drohnen-einsatztagebuch/wiki/Über), [Changelog](https://github.com/denni95112/drohnen-einsatztagebuch/wiki/Changelog) |

## 🚀 Installation

### Voraussetzungen

- PHP 7.4 oder höher
- SQLite3 (meist bereits in PHP enthalten)
- PHP Extensions:
  - PDO
  - PDO_SQLITE
  - cURL (für Bibliotheksdownloads)
  - ZipArchive (für Bibliotheksinstallation)
  - DOM/XML (für PDF-Generierung mit dompdf)
  - GD (für QR-Code-Generierung)
- Webserver (Apache, Nginx, etc.) mit URL-Rewriting (mod_rewrite für Apache)

### Installationsschritte

1. **Repository klonen oder herunterladen**
   ```bash
   git clone https://github.com/denni95112/drohnen-einsatztagebuch.git
   cd drohnen-einsatztagebuch
   ```

2. **Webserver konfigurieren**
   - Richte einen virtuellen Host ein, der auf das Projektverzeichnis zeigt
   - Stelle sicher, dass PHP aktiviert ist
   - Aktiviere mod_rewrite (Apache) oder konfiguriere URL-Rewriting (Nginx)
   - Die `.htaccess`-Datei im Root-Verzeichnis konfiguriert automatisch die URL-Weiterleitung

3. **Erstkonfiguration**
   - Öffne die Anwendung im Browser (z.B. `http://localhost/drohnen-einsatztagebuch/public/index.php?page=setup`)
   - Du wirst automatisch zum Setup weitergeleitet, falls noch keine Konfiguration existiert
   - Fülle das Setup-Formular aus (Details: [Wiki – Einrichtung](https://github.com/denni95112/drohnen-einsatztagebuch/wiki/Einrichtung)):
     - Ort und Einheit
     - Passwörter (Standard und Admin)
     - Datenbankpfad (optional, Standard: `einsatzbuch.db`)
     - Dashboard-Integration (optional)

4. **Bibliotheken installieren**
   - Falls benötigte Bibliotheken fehlen, können diese direkt im Setup heruntergeladen werden
   - Alternativ manuell:
     - `dompdf` in `lib/dompdf/`
     - `phpqrcode` in `lib/phpqrcode/`

## 📁 Projektstruktur

```
drohnen-einsatztagebuch/
├── app/                          # Application Core (MVC-Architektur)
│   ├── Controllers/              # API Controller
│   │   ├── AuthController.php
│   │   ├── EinsatzController.php
│   │   ├── DokumentationController.php
│   │   ├── PersonalController.php
│   │   ├── DrohnenController.php
│   │   └── FlightsController.php
│   ├── Models/                   # Datenmodelle
│   │   ├── BaseModel.php
│   │   ├── Einsatz.php
│   │   ├── Dokumentation.php
│   │   ├── Personal.php
│   │   ├── Drohne.php
│   │   └── Flight.php
│   ├── Services/                 # Business Logic
│   │   ├── AuthService.php
│   │   ├── PDFService.php
│   │   ├── QRCodeService.php
│   │   └── DashboardIntegrationService.php
│   ├── Middleware/               # Middleware-Komponenten
│   │   ├── AuthMiddleware.php
│   │   ├── CSRFMiddleware.php
│   │   └── RateLimitMiddleware.php
│   ├── Utils/                    # Utility-Klassen
│   │   ├── Database.php
│   │   ├── Response.php
│   │   └── Validator.php
│   └── autoload.php              # PSR-4 Autoloader
│
├── api/                          # RESTful API
│   ├── router.php                # API Router
│   ├── .htaccess                 # API URL-Rewriting
│   └── v1/                       # API Version 1
│       ├── index.php
│       ├── qr.php                # QR-Code Endpoint
│       └── install_notification.php
│
├── views/                        # Presentation Layer
│   ├── layouts/                  # Layout-Komponenten
│   │   ├── header.php
│   │   └── footer.php
│   ├── pages/                    # Seiten-Views
│   │   ├── index.php
│   │   ├── login.php
│   │   ├── admin.php
│   │   ├── dokumentation.php
│   │   ├── einsatzliste.php
│   │   ├── neuer_einsatz.php
│   │   ├── personal.php
│   │   ├── drohnen.php
│   │   ├── read_only.php
│   │   ├── setup.php
│   │   ├── about.php
│   │   └── changelog.php
│   └── components/               # Wiederverwendbare Komponenten
│
├── public/                       # Öffentliche Assets & Front Controller
│   ├── index.php                 # Front Controller für Web-Seiten
│   ├── css/                      # Stylesheets
│   │   ├── drohnen.css
│   │   ├── einsatzliste.css
│   │   ├── index.css
│   │   ├── login.css
│   │   ├── personal.css
│   │   ├── setup.css
│   │   ├── styles.css
│   │   ├── about.css
│   │   └── changelog.css
│   ├── js/                       # JavaScript-Dateien
│   │   ├── dokumentation.js
│   │   ├── einsatzliste.js
│   │   ├── neuer_einsatz.js
│   │   ├── read_only.js
│   │   ├── setup.js
│   │   └── install_notification.js
│   ├── img/                      # Bilder und Icons
│   │   ├── flugzeug_landung.png
│   │   ├── flugzeug_start.png
│   │   ├── personensuche.png
│   │   └── warnung.png
│   └── .htaccess
│
├── config/                       # Konfigurationsdateien
│   ├── config.php                # Hauptkonfiguration (wird beim Setup erstellt)
│   └── config.php.example        # Beispielkonfiguration
│
├── includes/                     # Include-Dateien
│   ├── version.php               # Versionsinformationen
│   ├── csrf.php                  # CSRF-Schutz
│   ├── error_reporting.php       # Fehlerbehandlung
│   ├── security_headers.php      # Sicherheits-Header
│   ├── rate_limit.php            # Rate Limiting
│   ├── changelog_data.php        # Changelog-Daten
│   └── buy_me_a_coffee.php       # Support-Komponente
│
├── lib/                          # Externe Bibliotheken
│   ├── dompdf/                   # PDF-Generierung
│   └── phpqrcode/                # QR-Code-Generierung
│
├── updater/                      # Auto-Updater-System
│   ├── updater.php               # Updater-Klasse
│   ├── updater_page.php          # Updater-Weboberfläche
│   ├── updater_api.php           # Updater-API-Endpoint
│   ├── updater.js                # Updater-JavaScript
│   └── updater.css               # Updater-Styles
│
├── uploads/                      # Hochgeladene Dateien (z.B. Logos)
│
├── .htaccess                     # URL-Rewriting für Root
├── index.php                     # Root-Index (leitet zu public/index.php weiter)
├── auth.php                      # Authentifizierung (Kompatibilität)
├── db.php                        # Datenbankverbindung (Kompatibilität)
├── utils.php                     # Utility-Funktionen
├── bootstrap.php                 # Bootstrap-Loader
├── logout.php                    # Logout-Handler
└── README.md                     # Diese Datei
```

## 🔌 RESTful API

Die Anwendung bietet eine vollständige RESTful API unter `/api/v1/`:

### Authentifizierung
- `POST /api/v1/auth/login` - Login
- `POST /api/v1/auth/logout` - Logout
- `GET /api/v1/auth/check` - Authentifizierungsstatus prüfen

### Einsätze
- `GET /api/v1/einsatz` - Alle Einsätze auflisten
- `GET /api/v1/einsatz/{id}` - Einsatz-Details abrufen
- `POST /api/v1/einsatz` - Neuen Einsatz erstellen
- `PUT /api/v1/einsatz/{id}` - Einsatz aktualisieren
- `POST /api/v1/einsatz/{id}/complete` - Einsatz abschließen
- `GET /api/v1/einsatz/{id}/pdf` - PDF-Bericht generieren
- `GET /api/v1/einsatz/{id}/dokumentation` - Dokumentation abrufen
- `POST /api/v1/einsatz/{id}/dokumentation` - Dokumentationseintrag hinzufügen

### Personal
- `GET /api/v1/personal` - Alle Personen auflisten
- `POST /api/v1/personal` - Person erstellen
- `PUT /api/v1/personal/{id}` - Person aktualisieren
- `DELETE /api/v1/personal/{id}` - Person löschen

### Drohnen
- `GET /api/v1/drohnen` - Alle Drohnen auflisten
- `POST /api/v1/drohnen` - Drohne erstellen
- `PUT /api/v1/drohnen/{id}` - Drohne aktualisieren
- `DELETE /api/v1/drohnen/{id}` - Drohne löschen

### Weitere Endpoints
- `POST /api/v1/flights` - Flugdaten einfügen (Dashboard-Integration)
- `GET /api/v1/qr?data=...` - QR-Code generieren

Alle API-Endpunkte liefern JSON-Antworten im Format:
```json
{
  "success": true,
  "data": { ... },
  "message": "..."
}
```

## 🌐 URL-Struktur

### Web-Seiten
Alle Seiten werden über den Front Controller aufgerufen:
- `/public/index.php?page=index` - Hauptübersicht
- `/public/index.php?page=login` - Login
- `/public/index.php?page=admin` - Administration
- `/public/index.php?page=dokumentation&einsatz_id=X` - Einsatzdokumentation
- `/public/index.php?page=einsatzliste` - Alle Einsätze
- `/public/index.php?page=neuer_einsatz` - Neuen Einsatz starten
- `/public/index.php?page=personal` - Personalverwaltung
- `/public/index.php?page=drohnen` - Drohnenverwaltung
- `/public/index.php?page=read_only&einsatz_id=X&token=...` - Lese-Modus
- `/public/index.php?page=setup` - Erstkonfiguration
- `/public/index.php?page=about` - Über
- `/public/index.php?page=changelog` - Changelog

### Assets
Assets werden automatisch von `public/` bereitgestellt:
- `/css/...` - Stylesheets
- `/js/...` - JavaScript-Dateien
- `/img/...` - Bilder

Die `.htaccess`-Datei leitet diese Anfragen automatisch an `public/` weiter.

## 🔧 Konfiguration

Die Konfiguration erfolgt über `config/config.php`, die beim ersten Setup erstellt wird. Folgende Einstellungen sind möglich:

- `token_name`: Cookie-Name für die Authentifizierung
- `navigation_title`: Titel der Anwendung
- `database_path`: Pfad zur SQLite-Datenbank
- `path_to_dashboard_db`: (Optional) Pfad zur Dashboard-Datenbank
- `dashboard_url`: (Optional) URL zum Flug Dashboard
- `read_token`: Token für den Lese-Modus
- `domain`: Domain der Anwendung (für QR-Codes)
- `ask_for_install_notification`: (Optional) Ob eine Installationsbenachrichtigung angezeigt werden soll

Die Versionsinformationen werden in `includes/version.php` gespeichert und sollten dort aktualisiert werden.

## 🔐 Sicherheit

- Passwörter werden als SHA-256 Hash gespeichert
- Session-basierte Authentifizierung
- Cookie-basierte "Angemeldet bleiben"-Funktion
- Token-basierter Lese-Modus
- CSRF-Schutz für alle Formulare und API-Endpunkte
- Input-Validierung und XSS-Schutz
- Prepared Statements für alle Datenbankabfragen
- Rate Limiting für API-Endpunkte
- Sicherheits-Header (X-Content-Type-Options, X-Frame-Options, etc.)

## 📖 Verwendung

### Neuen Einsatz starten

1. Klicke auf "Neuen Einsatz starten"
2. Fülle die Einsatzdaten aus:
   - Einsatznummer (optional, wird automatisch generiert)
   - Adresse (kann per GPS ermittelt werden)
   - Einsatzart
   - Gruppenführer und dokumentierende Person
   - Anwesendes Personal
3. Klicke auf "Einsatz starten"

### Einsatz dokumentieren

- **Manuelle Einträge**: Textfeld für freie Dokumentation
- **Quick Actions**: 
  - Drohnenstart/-landung
  - Personensuche melden
  - Technische Störung melden
- **Personal aktualisieren**: Anwesendes Personal während des Einsatzes ändern

### Einsatz abschließen

1. Klicke auf "Einsatz abschließen"
2. Ein PDF-Bericht wird automatisch generiert und heruntergeladen
3. Der Einsatz wird als abgeschlossen markiert

### Lese-Modus

- Generiere einen QR-Code für den Lese-Modus
- Der QR-Code enthält einen Token-basierten Link
- Öffne den Link, um die Dokumentation ohne Login einzusehen

### Updates installieren

Das System bietet ein integriertes Auto-Update-System:

1. **Version-Benachrichtigung**: 
   - Wenn eine neue Version verfügbar ist, erscheint ein Benachrichtigungssymbol (🔔) im Header
   - Als Admin wird direkt zum Update-Tool weitergeleitet
   - Als Nicht-Admin wird zur GitHub-Release-Seite weitergeleitet

2. **Update-Tool verwenden**:
   - Zugriff über den Link in der Benachrichtigung oder direkt über `updater/updater_page.php`
   - Klicke auf "Auf Updates prüfen", um nach verfügbaren Updates zu suchen
   - Wenn ein Update verfügbar ist, kann es direkt über "Jetzt aktualisieren" installiert werden

3. **Sicherheitsfeatures**:
   - Automatisches Backup vor jedem Update
   - Schutz wichtiger Dateien (Config, Uploads, Datenbanken)
   - Automatisches Rollback bei Fehlern
   - Detaillierte Update-Logs

**Wichtig**: Das Update-Tool ist nur für Administratoren zugänglich und erfordert Admin-Authentifizierung.

## 🛠️ Technische Details

- **Backend**: PHP 7.4+
- **Architektur**: MVC-Pattern mit RESTful API
- **Datenbank**: SQLite3
- **PDF-Generierung**: dompdf
- **QR-Code**: phpqrcode
- **Frontend**: Vanilla JavaScript, CSS3
- **Autoloading**: PSR-4 Autoloader
- **API**: RESTful API mit JSON-Responses
- **Version-Management**: Semantische Versionierung über GitHub Releases
- **Update-System**: Automatisches Update über GitHub Releases API

## Verwandte Projekte

Dieses Projekt kann zusammen mit dem **[Drohnen-Flug-und-Dienstbuch](https://github.com/denni95112/drohnen-flug-und-dienstbuch)** verwendet werden. Das Drohnen-Flug-und-Dienstbuch bietet zusätzliche Funktionen zur Dokumentation von Drohnen Flügen, Einsätzen und Diensten.

## 📝 Lizenz

Dieses Projekt ist unter der MIT-Lizenz lizenziert - siehe [LICENSE](LICENSE) Datei für Details.

## 👤 Autor

**Dennis Bögner**

- GitHub: [@denni95112](https://github.com/denni95112)

## ⚠️ Bekannte Einschränkungen

- Die Anwendung benötigt JavaScript für die vollständige Funktionalität
- PDF-Generierung erfordert die dompdf-Bibliothek (kann bei der Einrichtung automatisch heruntergeladen werden)
- QR-Code-Generierung erfordert die phpqrcode-Bibliothek (kann bei der Einrichtung automatisch heruntergeladen werden)
- Das Update-System erfordert cURL und Internetverbindung für den Zugriff auf die GitHub API
- Updates können nur durch Administratoren durchgeführt werden
- URL-Rewriting muss im Webserver aktiviert sein (mod_rewrite für Apache)

## 🐛 Fehler melden

Bitte melde Fehler über die [GitHub Issues](https://github.com/denni95112/drohnen-einsatztagebuch/issues).

## 📧 Kontakt

Bei Fragen oder Anregungen kannst du ein Issue auf GitHub erstellen.

---
