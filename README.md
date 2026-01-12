# Drohnen-Einsatztagebuch

Ein webbasiertes Einsatztagebuch für Drohneneinsätze mit Dokumentationsfunktionen, PDF-Export und Integration mit dem Flug Dashboard.

## 📋 Beschreibung

Das Drohnen-Einsatztagebuch ist eine PHP-basierte Webanwendung zur Dokumentation von Drohneneinsätzen. Es ermöglicht die Erfassung von Einsatzdaten, Personal, Drohnen, Flugdaten und der vollständigen Einsatzdokumentation. Die Anwendung generiert PDF-Berichte und kann optional mit einem Flug Dashboard integriert werden.

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
- Webserver (Apache, Nginx, etc.)

### Installationsschritte

1. **Repository klonen oder herunterladen**
   ```bash
   git clone https://github.com/denni95112/drohnen-einsatztagebuch.git
   cd drohnen-einsatztagebuch
   ```

2. **Webserver konfigurieren**
   - Richte einen virtuellen Host ein, der auf das Projektverzeichnis zeigt
   - Stelle sicher, dass PHP aktiviert ist

3. **Erstkonfiguration**
   - Öffne die Anwendung im Browser
   - Du wirst automatisch zum Setup weitergeleitet
   - Fülle das Setup-Formular aus:
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
├── config/                    # Konfigurationsdateien
│   ├── config.php              # Hauptkonfiguration (wird beim Setup erstellt)
│   └── config.php.example      # Beispielkonfiguration
├── css/                        # Stylesheets
│   ├── drohnen.css             # Styling für Drohnenverwaltung
│   ├── einsatzliste.css        # Styling für Einsatzliste
│   ├── index.css               # Styling für Hauptübersicht
│   ├── login.css               # Styling für Login-Seite
│   ├── personal.css            # Styling für Personalverwaltung
│   ├── setup.css               # Styling für Setup-Seite
│   └── styles.css              # Globale Styles
├── js/                         # JavaScript-Dateien
│   ├── dokumentation.js        # Funktionen für Einsatzdokumentation
│   ├── einsatzliste.js         # Funktionen für Einsatzliste
│   ├── neuer_einsatz.js        # Funktionen für neuen Einsatz
│   ├── read_only.js            # Funktionen für Lese-Modus
│   └── setup.js                # Funktionen für Setup
├── img/                        # Bilder und Icons
│   ├── flugzeug_landung.png    # Icon für Landung
│   ├── flugzeug_start.png      # Icon für Start
│   ├── personensuche.png       # Icon für Personensuche
│   └── warnung.png             # Icon für Warnung
├── lib/                        # Externe Bibliotheken
│   ├── dompdf/                 # PDF-Generierung
│   └── phpqrcode/              # QR-Code-Generierung
├── uploads/                    # Hochgeladene Dateien (z.B. Logos)
├── cache/                      # Cache-Dateien (z.B. für Version-Checks)
├── updater/                    # Auto-Updater-System
│   ├── updater.php             # Updater-Klasse
│   ├── updater_page.php        # Updater-Weboberfläche
│   ├── updater_api.php         # Updater-API-Endpoint
│   ├── updater.js              # Updater-JavaScript
│   └── updater.css             # Updater-Styles
├── includes/                   # Include-Dateien
│   ├── version.php             # Versionsinformationen
│   ├── csrf.php                # CSRF-Schutz
│   └── ...                     # Weitere Include-Dateien
├── admin.php                   # Administrationsbereich
├── ajax_insert.php             # AJAX-Endpoint für Einträge
├── ajax_read_only.php          # AJAX-Endpoint für Lese-Modus
├── auth.php                    # Authentifizierung
├── db.php                      # Datenbankverbindung und Schema
├── dokumentation.php           # Einsatzdokumentation
├── drohnen.php                 # Drohnenverwaltung
├── einsatz_abschluss.php       # PDF-Generierung
├── einsatzliste.php            # Einsatzliste
├── footer.php                  # Wiederverwendbare Footer-Komponente
├── header.php                  # Wiederverwendbare Header-Komponente
├── index.php                   # Hauptübersicht
├── insert_flight.php           # Flugdaten einfügen
├── login.php                   # Login-Seite
├── logout.php                  # Logout-Funktion
├── neuer_einsatz.php           # Neuen Einsatz starten
├── personal.php                # Personalverwaltung
├── qr_generate.php             # QR-Code-Generierung
├── read_only.php               # Lese-Modus
├── setup.php                   # Erstkonfiguration
├── utils.php                   # Utility-Funktionen
└── version_check.php           # Version-Check für GitHub-Releases
```

## 🔧 Konfiguration

Die Konfiguration erfolgt über `config/config.php`, die beim ersten Setup erstellt wird. Folgende Einstellungen sind möglich:

- `token_name`: Cookie-Name für die Authentifizierung
- `navigation_title`: Titel der Anwendung
- `database_path`: Pfad zur SQLite-Datenbank
- `path_to_dashboard_db`: (Optional) Pfad zur Dashboard-Datenbank
- `dashboard_url`: (Optional) URL zum Flug Dashboard
- `ask_for_install_notification`: (Optional) Ob eine Installationsbenachrichtigung angezeigt werden soll

Die Versionsinformationen werden in `includes/version.php` gespeichert und sollten dort aktualisiert werden.

## 🔐 Sicherheit

- Passwörter werden als SHA-256 Hash gespeichert
- Session-basierte Authentifizierung
- Cookie-basierte "Angemeldet bleiben"-Funktion
- Token-basierter Lese-Modus
- Input-Validierung und XSS-Schutz
- Prepared Statements für alle Datenbankabfragen

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

1. Klicke auf "Einsatz abschließen & PDF erstellen"
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
- **Datenbank**: SQLite3
- **PDF-Generierung**: dompdf
- **QR-Code**: phpqrcode
- **Frontend**: Vanilla JavaScript, CSS3
- **Version-Management**: Semantische Versionierung über GitHub Releases
- **Update-System**: Automatisches Update über GitHub Releases API

## Verwandte Projekte

Dieses Projekt kann zusammen mit dem **[Drohnen-Flug-und-Dienstbuch]([https://github.com/denni95112/drohnen-einsatztagebuch](https://github.com/denni95112/drohnen-flug-und-dienstbuch))** verwendet werden. Das Drohnen-Flug-und-Dienstbuch bietet zusätzliche Funktionen zur Dokumentation von Drohnen Flügen, Einsätzen und Diensten.

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

## 🐛 Fehler melden

Bitte melde Fehler über die [GitHub Issues](https://github.com/denni95112/drohnen-einsatztagebuch/issues).

## 📧 Kontakt

Bei Fragen oder Anregungen kannst du ein Issue auf GitHub erstellen.

---
