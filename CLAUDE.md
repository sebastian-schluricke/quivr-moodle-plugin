# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Quivr Chat (`mod_quivrchat`) is a Moodle activity module that integrates the Quivr AI chat platform into Moodle courses. It allows students to interact with AI-powered knowledge brains configured by teachers.

This plugin connects to a Quivr backend (ESFL fork) located at `/mnt/c/anwender/github/ESFL/quivr/`. Both repositories work together - the Moodle plugin is the frontend integration, the Quivr repo is the RAG backend.

## Repository Structure

- `/quivrchat/` - The Moodle plugin (installs to `mod/quivrchat` in Moodle)
- `/testing/` - Testing utilities and API examples

Note: The root README.md references `mybrainchat/` but the plugin has been renamed to `quivrchat/`.

## Architecture

### Token Flow (Security Model)
The plugin uses a scoped token architecture to avoid exposing API keys to the frontend:
1. Teacher stores their API key in Moodle (saved to user preferences)
2. API key is stored per-instance in the `quivrchat` table
3. Frontend calls `api/get_token.php` to obtain a scoped, time-limited token
4. Frontend uses this token directly with the Quivr API for chat operations

### Key Components

**PHP Backend (`quivrchat/`):**
- `lib.php` - Core Moodle hooks including `quivrchat_before_footer()` for popup injection
- `view.php` - Main activity view, initializes chat UI
- `mod_form.php` - Activity configuration form with brain selection
- `settings.php` - Site-wide settings (Quivr API URL)

**API Endpoints (`quivrchat/api/`):**
- `get_token.php` - Issues scoped chat tokens (POST to Quivr `/chat/token`)
- `session_chat.php` - GET/POST/DELETE for session persistence of chat_id and history
- `get_brains.php` - Fetches available brains for form dropdown
- `get_instance.php` - Returns the popup-designated instance for a course
- `chat.php` - Legacy non-streaming chat endpoint (deprecated)

**Frontend (`quivrchat/js/`):**
- `quivr-chat.js` - `QuivrChat` class handles chat UI, streaming responses, token management
- `popup-chat.js` - `QuivrChatPopup` module for modal chat overlay on course pages

### Database Schema
Single table `quivrchat` with fields: id, course, name, intro, introformat, brainid, apikey, use_for_popup, timecreated, timemodified

### Popup Chat Feature
When `use_for_popup=1` on an instance, a floating chat button appears on all course pages. Only one instance per course can be the popup. The popup loads the chat in an iframe modal.

## Development

### Installation
```bash
# Copy plugin to Moodle
cp -r quivrchat /path/to/moodle/mod/

# Run Moodle upgrade
php /path/to/moodle/admin/cli/upgrade.php
```

### Configuration
1. Site admin: Set Quivr API URL at Site administration > Plugins > Activity modules > Quivr Chat
2. Activity setup: Enter API key, click "Load Brains", select brain

### Testing API Connection
```bash
# Test brain listing
curl -H "Authorization: Bearer YOUR_API_KEY" https://your-quivr-api/brains/
```

### Key Moodle Patterns Used
- `require_login()` and `require_capability()` for access control
- `get_user_preferences()` / `set_user_preference()` for API key storage
- `get_config('mod_quivrchat', ...)` for site-wide settings
- `$PAGE->requires->js()` / `->css()` for asset loading
- `get_fast_modinfo()` for course module information

## Known Limitations

- Feedback thumbs feature is disabled (see `TODO.md` for implementation details)
- Streaming response parsing handles multiple formats (JSON chunks, SSE, plain text)
- UI is primarily in German (hardcoded strings in view.php, language files support en/de)

## Related: Quivr Backend

The Quivr backend repository (`/mnt/c/anwender/github/ESFL/quivr/`) provides the RAG (Retrieval-Augmented Generation) API. Key integration points:

### Quivr API Endpoints Used by This Plugin

| Plugin Endpoint | Quivr API | Purpose |
|-----------------|-----------|---------|
| `api/get_token.php` | `POST /chat/token` | Obtain scoped chat token (TTL 1-60 min) |
| `api/get_brains.php` | `GET /brains/` | List available brains for selection |
| JS `QuivrChat` | `POST /chat` | Create new chat session |
| JS `QuivrChat` | `POST /chat/{chat_id}/question/stream` | Send question, receive streaming response |

### Scoped Token System

The `/chat/token` endpoint (in Quivr backend at `modules/chat_token/`) creates JWT tokens scoped to a specific brain:
- Moodle backend calls this with the master API key
- Returns a short-lived token (default 10 min) that can only access the specified brain
- Frontend uses this scoped token directly with Quivr API
- Token contains `scoped_brain_id` claim, validated by `validate_scoped_token()` in chat routes

### Quivr Backend Development

See `/mnt/c/anwender/github/ESFL/quivr/CLAUDE.md` for full backend documentation. Quick start:

```bash
cd /mnt/c/anwender/github/ESFL/quivr/backend
supabase start

cd api
source venv/bin/activate
python -m uvicorn quivr_api.main:app --host 0.0.0.0 --port 5050 --reload
```

Default URLs:
- Quivr API: http://localhost:5050 (Swagger docs at /docs)
- Supabase Studio: http://localhost:54323
- Default login: admin@quivr.app / admin
