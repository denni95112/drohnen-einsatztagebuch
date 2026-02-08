# Drohnen-Einsatztagebuch

Ein webbasiertes Einsatztagebuch für Drohneneinsätze mit Dokumentation, PDF-Export und optionaler Integration mit dem Flug-Dienstbuch. Entwickelt mit PHP und SQLite, konzipiert für BOS und Drohnenbetreiber.

📖 **Ausführliche Anleitung**: [Wiki](https://github.com/denni95112/drohnen-einsatztagebuch/wiki)

---

## ✨ Funktionen

- 📋 **Einsatzverwaltung** – Einsätze erfassen, dokumentieren und abschließen
- 👥 **Personal & Drohnen** – Stammdaten verwalten, optional aus Flug-Dienstbuch
- 📝 **Live-Dokumentation** – Echtzeit-Einträge, Quick Actions (Start/Landung, Personensuche, Warnung)
- 📄 **PDF-Export** – Einsatzberichte als PDF
- 📱 **QR-Code & Lese-Modus** – Token-basierter Zugriff ohne Login
- 🔗 **Dashboard-Integration** – Optional Anbindung an Drohnen-Flug-und-Dienstbuch
- 📍 **GPS** – Adresse per GPS ermitteln
- 🔐 **Authentifizierung** – Passwort + Admin-Rechte
- 🔄 **Auto-Updater** – Update-System mit Backup und Rollback

---

## 📸 Screenshots

<p float="left">
  <img src="https://github.com/user-attachments/assets/51af15f9-aebf-4c76-beab-a5310547c811" width="150" />
  <img src="https://github.com/user-attachments/assets/520e179d-4729-4474-9a41-bb8f0b0db9f0" width="150" />
  <img src="https://github.com/user-attachments/assets/ee7cdb43-079c-41d1-919b-7f4806e8962b" width="150" />
  <img src="https://github.com/user-attachments/assets/d84bdc43-6c8d-4f1f-a25e-f192124108a1" width="150" />
  <img src="https://github.com/user-attachments/assets/380f5333-9a6c-4020-addc-4daf425d1110" width="150" />
  <img src="https://github.com/user-attachments/assets/efe93dec-3e50-4021-addc-57181499f16f" width="150" />
</p>

---

## 🚀 Schnellstart

### Anforderungen

- PHP 7.4+
- SQLite3-Erweiterung
- PHP-Erweiterungen: PDO, PDO_SQLITE, cURL, ZipArchive, DOM/XML, GD
- Webserver (Apache, Nginx, etc.) mit URL-Rewriting (mod_rewrite für Apache)

### Installation

1. Repository klonen und ins Projektverzeichnis wechseln:
   ```bash
   git clone https://github.com/denni95112/drohnen-einsatztagebuch.git
   cd drohnen-einsatztagebuch
   ```

2. Webserver auf das Projektverzeichnis zeigen; PHP mit SQLite3 aktivieren; mod_rewrite (Apache) aktivieren.

3. Berechtigungen setzen (Linux/Unix):
   ```bash
   chmod -R 755 .
   chmod -R 777 config/ uploads/ 2>/dev/null || true
   ```

4. Anwendung im Browser aufrufen (z.B. `http://ihre-domain/public/index.php`) – bei fehlender Konfiguration erscheint das Setup. Die [Einrichtung](https://github.com/denni95112/drohnen-einsatztagebuch/wiki/Einrichtung) durchführen (Einheit, Ort, Passwörter, Datenbankpfad, optional Dashboard-Integration). Fehlende Bibliotheken (dompdf, phpqrcode) können im Setup heruntergeladen werden.

---

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

---

## 🔒 Sicherheit

- SQL-Injection-Schutz (Prepared Statements)
- CSRF-Schutz für alle Formulare und API-Endpunkte
- Passwort-Hashing (SHA-256), Session- und Cookie-basierte Authentifizierung
- Token-basierter Lese-Modus
- Rate Limiting für API und Anmeldung
- HTTP-Sicherheitsheader (X-Content-Type-Options, X-Frame-Options, etc.)
- Input-Validierung und XSS-Schutz

---

## 👨‍💻 Für Entwickler

### API-Endpunkte

| Endpunkt | Funktion |
|----------|----------|
| `POST/GET /api/v1/auth/*` | Login, Logout, Check |
| `GET/POST/PUT /api/v1/einsatz` | Einsätze auflisten, erstellen, aktualisieren |
| `GET /api/v1/einsatz/{id}/pdf` | PDF-Bericht |
| `GET/POST /api/v1/einsatz/{id}/dokumentation` | Dokumentation abrufen/hinzufügen |
| `POST /api/v1/einsatz/{id}/complete` | Einsatz abschließen |
| `GET/POST/PUT/DELETE /api/v1/personal` | Personal-Verwaltung |
| `GET/POST/PUT/DELETE /api/v1/drohnen` | Drohnen-Verwaltung |
| `POST /api/v1/flights` | Flugdaten (Dashboard-Integration) |
| `GET /api/v1/qr?data=...` | QR-Code generieren |

Alle API-Requests erfordern Authentifizierung (außer Lese-Modus) und CSRF-Token. JSON-Responses: `{ "success", "data", "message" }`.

### Projektstruktur

```
├── app/          # MVC (Controllers, Models, Services, Middleware, Utils)
├── api/          # RESTful API (router, v1/)
├── views/        # Layouts, pages, components
├── public/       # Front Controller (index.php), css, js, img
├── config/       # Konfiguration
├── includes/     # version, csrf, security_headers, rate_limit, changelog_data
├── lib/          # dompdf, phpqrcode (optional, Setup-Download)
├── updater/      # Update-System
├── .htaccess     # URL-Rewriting
├── index.php     # Weiterleitung zu public/
└── bootstrap.php
```

---

## ℹ️ Weitere Informationen

- **Verwandtes Projekt**: [Drohnen-Flug-und-Dienstbuch](https://github.com/denni95112/drohnen-flug-und-dienstbuch)
- **Lizenz**: MIT – siehe [LICENSE](LICENSE)
- **Autor**: [Dennis Bögner](https://github.com/denni95112) (@denni95112)
