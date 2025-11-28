# TODO: Moodle Plugin mybrainchat

## Thumbs Feedback Feature

**Status:** Deaktiviert (Button auskommentiert in `hugo-script.js`)

### Beschreibung
Implementierung der Antwort-Bewertung (Thumbs Up/Down) mit Anbindung an die Quivr API.

### Aktueller Stand

| Komponente | Status |
|------------|--------|
| Thumb-Down Button UI | Vorhanden (CSS + JS) |
| CSS Styles | Vorhanden (`styles/hugo-style.css`) |
| Button Click Handler | Nur visuell - keine API-Anbindung |
| Quivr API Endpoint | Vorhanden: `PUT /chat/{chat_id}/{message_id}` |
| message_id im Stream | Wird in Streaming-Response mitgesendet |

### Was fehlt

1. **message_id aus Stream extrahieren und speichern**
   - Die `message_id` wird im Quivr Stream gesendet
   - Muss im Frontend als `data-message-id` Attribut am Antwort-Container gespeichert werden
   - Relevante Stelle: `handleStreamingResponse()` in `hugo-script.js`

2. **PHP Proxy-Endpoint erstellen**
   - Datei: `api/rate_message.php`
   - Notwendig wegen CORS und API-Key-Handling
   - Endpoint ruft Quivr API auf: `PUT /chat/{chat_id}/{message_id}`
   - Body: `{"thumbs": true/false}`

3. **JavaScript API-Call implementieren**
   - Im `dislikeButton.addEventListener("click", ...)` Handler
   - Aufruf des PHP Proxy-Endpoints mit chat_id und message_id

### Quivr API Details

```
PUT /chat/{chat_id}/{message_id}
Authorization: Bearer {token}
Content-Type: application/json

{
  "thumbs": false  // false = negative Bewertung, true = positive
}
```

**Relevante Dateien im Quivr Backend:**
- `quivr_api/modules/chat/controller/chat_routes.py` (Zeile 142-163)
- `quivr_api/modules/chat/dto/inputs.py` - `ChatMessageProperties`
- `quivr_api/modules/chat/service/chat_service.py` - `update_chat_message()`

### Geschätzter Aufwand

| Aufgabe | Aufwand |
|---------|---------|
| message_id aus Stream extrahieren | ~20 Min |
| PHP Proxy `api/rate_message.php` | ~30 Min |
| JavaScript API-Call implementieren | ~20 Min |
| Testen & Debugging | ~20 Min |
| **Gesamt Basis-Feature** | **~1,5 Stunden** |

### Optionale Erweiterungen

| Feature | Zusatzaufwand |
|---------|---------------|
| Thumb-Up Button hinzufuegen | +15 Min |
| Visuelles Feedback (Erfolgsmeldung) | +10 Min |
| Kommentarfeld fuer Feedback | +45 Min |

### Betroffene Dateien

- `js/hugo-script.js` - Hauptlogik (Button ist auskommentiert Zeile 429-430)
- `styles/hugo-style.css` - CSS fuer Feedback-Container (Zeile 276-299)
- `pix/thumbs-down.svg` - Icon
- `pix/thumbs-down-grey.svg` - Icon (nach Klick)
- NEU: `api/rate_message.php` - PHP Proxy (zu erstellen)
