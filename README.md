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

## 🚀 Installation

### Voraussetzungen

- PHP 7.4 oder höher
- SQLite3 (meist bereits in PHP enthalten)
- PHP Extensions:
  - PDO
  - PDO_SQLITE
  - cURL (für Bibliotheksdownloads)
  - ZipArchive (für Bibliotheksinstallation)
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
├── config/              # Konfigurationsdateien
│   └── config.php       # Hauptkonfiguration (wird beim Setup erstellt)
├── css/                 # Stylesheets
│   ├── index.css
│   ├── login.css
│   ├── setup.css
│   └── ...
├── js/                  # JavaScript-Dateien
│   ├── dokumentation.js
│   ├── einsatzliste.js
│   └── ...
├── img/                 # Bilder und Icons
├── lib/                 # Externe Bibliotheken (dompdf, phpqrcode)
├── admin.php            # Administrationsbereich
├── auth.php             # Authentifizierung
├── db.php               # Datenbankverbindung und Schema
├── dokumentation.php     # Einsatzdokumentation
├── einsatz_abschluss.php # PDF-Generierung
├── index.php            # Hauptübersicht
├── login.php            # Login-Seite
├── neuer_einsatz.php    # Neuen Einsatz starten
├── setup.php            # Erstkonfiguration
└── utils.php            # Utility-Funktionen
```

## 🔧 Konfiguration

Die Konfiguration erfolgt über `config/config.php`, die beim ersten Setup erstellt wird. Folgende Einstellungen sind möglich:

- `token_name`: Cookie-Name für die Authentifizierung
- `navigation_title`: Titel der Anwendung
- `database_path`: Pfad zur SQLite-Datenbank
- `path_to_dashboard_db`: (Optional) Pfad zur Dashboard-Datenbank
- `dashboard_url`: (Optional) URL zum Flug Dashboard

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

## 🛠️ Technische Details

- **Backend**: PHP 7.4+
- **Datenbank**: SQLite3
- **PDF-Generierung**: dompdf
- **QR-Code**: phpqrcode
- **Frontend**: Vanilla JavaScript, CSS3

## 📝 Lizenz

Dieses Projekt ist unter der MIT-Lizenz lizenziert - siehe [LICENSE](LICENSE) Datei für Details.

## 👤 Autor

**Dennis Bögner**

- GitHub: [@denni95112](https://github.com/denni95112)


## ⚠️ Bekannte Einschränkungen

- Die Anwendung benötigt JavaScript für die vollständige Funktionalität
- PDF-Generierung erfordert die dompdf-Bibliothek (kann bei der Einrichtung automatisch heruntergeladen werden)
- QR-Code-Generierung erfordert die phpqrcode-Bibliothek (kann bei der Einrichtung automatisch heruntergeladen werden)

## 🐛 Fehler melden

Bitte melde Fehler über die [GitHub Issues](https://github.com/denni95112/drohnen-einsatztagebuch/issues).

## 📧 Kontakt

Bei Fragen oder Anregungen kannst du ein Issue auf GitHub erstellen.

---
