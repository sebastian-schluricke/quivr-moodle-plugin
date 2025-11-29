# Quivr Chat - Moodle Activity Plugin

Ein Moodle-Aktivitätsmodul zur Integration von KI-gestützten Wissensdatenbanken (Quivr Brains) in Moodle-Kurse. Ermöglicht Schüler:innen und Lehrkräften die Interaktion mit RAG-basierten (Retrieval-Augmented Generation) Chatbots direkt in der Lernplattform.

## Übersicht

Dieses Plugin verbindet Moodle mit einem [Quivr-Backend](https://github.com/sebastian-schluricke/quivr-for-moodle), um KI-gestützte Chat-Funktionalität in Kursen bereitzustellen. Lehrkräfte können spezifische "Brains" (Wissensdatenbanken) mit Kursaktivitäten verknüpfen, sodass Lernende Fragen stellen und Antworten basierend auf den hinterlegten Dokumenten erhalten.

**Anwendungsfall Schule:** Lehrkräfte laden Unterrichtsmaterialien, Skripte oder Lehrpläne in ein Quivr-Brain hoch. Schüler:innen können dann im Moodle-Kurs Fragen zu diesen Materialien stellen und erhalten KI-generierte Antworten mit Quellenangaben.

## Features

- **KI-Chat-Integration**: Streaming-Antworten für flüssige Konversation
- **Sichere Token-Architektur**: API-Keys werden nie an den Browser übertragen
- **Markdown-Rendering**: Formatierte Antworten mit Syntax-Highlighting für Code
- **Popup-Chat**: Optionaler schwebender Chat-Button auf allen Kursseiten
- **Session-Persistenz**: Chat-Verlauf bleibt während der Sitzung erhalten
- **Mehrsprachig**: Deutsch und Englisch

## Architektur

```
┌─────────────────┐         ┌─────────────────┐         ┌─────────────────┐
│                 │         │                 │         │                 │
│  Moodle Frontend│◄───────►│  Moodle Backend │◄───────►│  Quivr Backend  │
│  (JavaScript)   │  Token  │  (PHP)          │  API    │  (Python/FastAPI)│
│                 │         │                 │  Key    │                 │
└─────────────────┘         └─────────────────┘         └─────────────────┘
```

### Token-Flow (Sicherheitsmodell)

1. Lehrkraft speichert ihren API-Key in der Moodle-Aktivität
2. API-Key wird in der Datenbank gespeichert (nie an Frontend übertragen)
3. Frontend fordert vom Moodle-Backend einen zeitlich begrenzten, brain-spezifischen Token an
4. Frontend nutzt diesen scoped Token direkt mit der Quivr-API für Chat-Operationen

## Voraussetzungen

- **Moodle**: Version 4.0 oder höher
- **PHP**: Version 8.0 oder höher
- **Quivr-Backend**: [quivr-for-moodle](https://github.com/sebastian-schluricke/quivr-for-moodle) (selbst gehostet)
- **Datenbank**: MySQL/MariaDB oder PostgreSQL

## Installation

### 1. Plugin installieren

```bash
# Plugin in Moodle-Verzeichnis kopieren
cp -r quivrchat /path/to/moodle/mod/

# Moodle-Upgrade ausführen
php /path/to/moodle/admin/cli/upgrade.php
```

Oder über die Moodle-Oberfläche:
1. Administration → Plugins → Plugin installieren
2. ZIP-Datei des Plugins hochladen

### 2. Plugin konfigurieren

1. **API-URL festlegen**:
   - Website-Administration → Plugins → Aktivitäten → Quivr Chat
   - Quivr API URL eingeben (z.B. `https://quivr.ihre-schule.de`)

### 3. Aktivität in Kurs hinzufügen

1. Kurs bearbeiten → Aktivität hinzufügen → "Quivr Chat"
2. Namen eingeben
3. API-Key eingeben (vom Quivr-Backend)
4. "Brains laden" klicken
5. Brain auswählen
6. Optional: "Als Popup-Chat verwenden" aktivieren
7. Speichern

## Konfiguration

### Site-weite Einstellungen

| Einstellung | Beschreibung | Standardwert |
|-------------|--------------|--------------|
| `quivr_api_url` | URL des Quivr-Backends | - |

### Aktivitäts-Einstellungen

| Einstellung | Beschreibung |
|-------------|--------------|
| Name | Anzeigename der Aktivität |
| API-Key | Quivr API-Key der Lehrkraft |
| Brain | Ausgewählte Wissensdatenbank |
| Als Popup | Chat als schwebendes Fenster im gesamten Kurs |

## Dateistruktur

```
quivrchat/
├── api/                    # PHP-API-Endpunkte
│   ├── get_token.php       # Scoped Token abrufen
│   ├── get_brains.php      # Verfügbare Brains laden
│   ├── session_chat.php    # Chat-Session verwalten
│   └── get_instance.php    # Popup-Instanz abrufen
├── classes/                # Moodle-Klassen
├── db/                     # Datenbank-Schema & Berechtigungen
│   ├── access.php
│   ├── install.xml
│   └── upgrade.php
├── js/                     # Frontend JavaScript
│   ├── quivr-chat.js       # Haupt-Chat-Klasse
│   ├── popup-chat.js       # Popup-Overlay
│   └── vendor/             # Externe Bibliotheken
├── lang/                   # Sprachdateien
│   ├── de/
│   └── en/
├── styles/                 # CSS
│   ├── quivr-chat.css
│   └── vendor/
├── lib.php                 # Moodle Core Hooks
├── mod_form.php            # Aktivitäts-Formular
├── view.php                # Hauptansicht
└── settings.php            # Admin-Einstellungen
```

## Entwicklung

### Lokale Entwicklungsumgebung

```bash
# Repository klonen
git clone git@github.com:sebastian-schluricke/quivr-moodle-plugin.git

# Plugin in Moodle-Installation verlinken
ln -s /path/to/quivr-moodle-plugin/quivrchat /path/to/moodle/mod/quivrchat

# Nach Änderungen Caches leeren
php /path/to/moodle/admin/cli/purge_caches.php
```

### Docker-Entwicklung

```bash
# Plugin in Docker-Container kopieren
docker cp quivrchat moodle-container:/var/www/html/mod/quivrchat

# Upgrade ausführen
docker exec moodle-container php /var/www/html/admin/cli/upgrade.php --non-interactive

# Caches leeren
docker exec moodle-container php /var/www/html/admin/cli/purge_caches.php
```

### Debugging

Debug-Modus in Moodle aktivieren:
```php
// config.php
@error_reporting(E_ALL | E_STRICT);
@ini_set('display_errors', '1');
$CFG->debug = (E_ALL | E_STRICT);
$CFG->debugdisplay = 1;
```

Browser-Konsole zeigt JavaScript-Debug-Informationen.

## API-Endpunkte

### GET /mod/quivrchat/api/get_brains.php

Lädt verfügbare Brains für den API-Key.

**Parameter:**
- `apikey` (optional): API-Key, falls nicht in User-Preferences gespeichert

**Response:**
```json
{
  "success": true,
  "brains": [
    {"id": "uuid", "name": "Brain Name"}
  ]
}
```

### POST /mod/quivrchat/api/get_token.php

Holt einen zeitlich begrenzten Chat-Token.

**Parameter:**
- `instanceid`: ID der Quivr-Chat-Instanz

**Response:**
```json
{
  "success": true,
  "token": "eyJ...",
  "brainId": "uuid"
}
```

### GET/POST/DELETE /mod/quivrchat/api/session_chat.php

Verwaltet Chat-Session-Daten (chat_id, history).

## Zusammenspiel mit Quivr-Backend

Dieses Plugin benötigt das [quivr-for-moodle](https://github.com/sebastian-schluricke/quivr-for-moodle) Backend. Wichtige Endpunkte:

| Plugin-Endpunkt | Quivr-API | Zweck |
|-----------------|-----------|-------|
| `get_token.php` | `POST /chat/token` | Scoped Token erstellen |
| `get_brains.php` | `GET /brains/` | Brains auflisten |
| JS QuivrChat | `POST /chat` | Chat-Session erstellen |
| JS QuivrChat | `POST /chat/{id}/question/stream` | Frage stellen (Streaming) |

## Bekannte Einschränkungen

- Feedback-Daumen-Feature ist deaktiviert (siehe TODO.md)
- UI primär auf Deutsch (hardcodierte Strings in view.php)
- Streaming-Parser unterstützt mehrere Formate (JSON-Chunks, SSE, Plain Text)

## Lizenz

GNU General Public License v3.0 - siehe [LICENSE](quivrchat/LICENSE.md)

## Mitwirken

Beiträge sind willkommen! Bitte erstellen Sie einen Pull Request.

1. Repository forken
2. Feature-Branch erstellen (`git checkout -b feature/neue-funktion`)
3. Änderungen committen
4. Branch pushen (`git push origin feature/neue-funktion`)
5. Pull Request öffnen

## Support

Bei Fragen oder Problemen:
- [GitHub Issues](https://github.com/sebastian-schluricke/quivr-moodle-plugin/issues)

## Verwandte Projekte

- [quivr-for-moodle](https://github.com/sebastian-schluricke/quivr-for-moodle) - Das Quivr-Backend (Fork)
- [QuivrHQ/quivr](https://github.com/QuivrHQ/quivr) - Original Quivr-Projekt
