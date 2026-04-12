# Quivr Chat - Moodle Activity Plugin

A Moodle activity module for integrating AI-powered knowledge bases (Quivr Brains) into Moodle courses. Enables students and teachers to interact with RAG-based (Retrieval-Augmented Generation) chatbots directly within the learning platform.

## Overview

This plugin connects Moodle with a [Quivr backend](https://github.com/sebastian-schluricke/quivr-for-moodle) to provide AI-powered chat functionality in courses. Teachers can link specific "Brains" (knowledge bases) to course activities, allowing learners to ask questions and receive answers based on uploaded documents.

**Use Case - Education:** Teachers upload course materials, scripts, or curricula to a Quivr Brain. Students can then ask questions about these materials within the Moodle course and receive AI-generated answers with source references.

## Features

- **AI Chat Integration**: Real-time streaming responses via Server-Sent Events
- **Custom Instructions per Activity**: Different activities can use different prompts with the same Brain (e.g., Socratic tutor, quiz mode, simple language)
- **Example Prompt Templates**: Clickable preset buttons help teachers configure activities quickly
- **Secure Token Architecture**: API keys are never transmitted to the browser
- **Markdown Rendering**: Formatted responses with syntax highlighting for code (marked.js, highlight.js, DOMPurify)
- **MathJax Support**: LaTeX and AsciiMath formula rendering
- **Popup Chat**: Optional floating chat button on all course pages
- **Session Persistence**: Chat history is preserved during the session
- **Multilingual**: German and English language packs

### Moodle Standards Compliance

- **Mustache Templates**: UI rendered via Moodle Output API (`templates/view.mustache`)
- **AMD/ES6 Modules**: JavaScript loaded via `js_call_amd()` (`amd/src/chat.js`, `amd/src/popup.js`)
- **External Services**: All non-streaming AJAX calls use Moodle External Services API (`db/services.php`)
- **Hooks API**: Page injection uses Moodle 4.4+ Hooks (`db/hooks.php`, `classes/hook_callbacks.php`)
- **Input Sanitization**: All user input cleaned via `required_param()` / `optional_param()` / `clean_param()`
- **Proxy Support**: HTTP calls use Moodle's `\curl` wrapper (`lib/filelib.php`)
- **CSS Namespacing**: All selectors scoped under `.mod_quivrchat`
- **Third-party Libraries**: Documented in `thirdpartylibs.xml`

## Screenshots

### Plugin Settings
Configure the Quivr API URL in the site administration.

![Plugin Settings](images/Picture%201%20-%20Plugin%20Settings.png)

### Activity Configuration
Set up a new Quivr Chat activity with brain selection, custom instructions, and popup options.

![Brain Settings](images/Picture%202%20-%20Brain%20Settings%20-%20New%20Activity.png)

### Chat Interface
Students interact with the AI-powered knowledge brain directly in the activity.

![Chat in Activity](images/Picture%203%20-%20Brain%20Chat%20in%20activity.png)

### Course-wide Popup
Optional floating chat button available on all course pages.

![Popup Chat](images/Picture%204%20-%20Brain%20Chat%20in%20course%20-%20global.png)

## Architecture

```
  Moodle External Services           Direct Streaming
  (token, brains, session)              (SSE, chat)
           |                                |
           v                                v
+------------------+   Ajax.call()   +------------------+   fetch()   +------------------+
|                  | <-------------> |                  | <---------> |                  |
|  Moodle Frontend |                 |  Moodle Backend  |   API Key   |  Quivr Backend   |
|  (AMD Modules)   |                 |  (PHP External   |             |  (Python/FastAPI) |
|                  |   Scoped Token  |   Services)      |             |                  |
+------------------+ - - - - - - - - +------------------+             +------------------+
                     (direct to Quivr for streaming)
```

### Hybrid Architecture

- **Non-streaming operations** (token acquisition, brain listing, session management) go through Moodle External Services for proper authentication, sesskey validation, and capability checks.
- **Streaming chat** (real-time token-by-token responses) connects directly from the browser to the Quivr API using a time-limited, brain-scoped token. Moodle External Services cannot handle SSE streaming.

### Token Flow (Security Model)

1. Teacher stores their API key in the Moodle activity
2. API key is stored in the database (never transmitted to frontend)
3. Frontend requests a time-limited, brain-specific token via Moodle External Service
4. Frontend uses this scoped token directly with the Quivr API for streaming chat

## Custom Instructions

Teachers can configure per-activity behavior instructions that control how the AI responds. This allows the same Brain (knowledge base) to power different learning activities:

| Preset | Behavior |
|--------|----------|
| **Socratic Tutor** | Asks counter-questions instead of giving direct answers |
| **Quiz Mode** | Asks questions first, evaluates student answers |
| **Simple Language** | Explains at 4th-grade level with short sentences |
| **Math Tutor** | AsciiMath formatting, step-by-step solutions |
| **Short Answers** | Maximum 3 sentences per response |

Custom instructions override the Brain's default prompt and are sent with every chat message.

## Requirements

- **Moodle**: Version 4.4 or higher (required for Hooks API)
- **PHP**: Version 8.1 or higher
- **Quivr Backend**: [quivr-for-moodle](https://github.com/sebastian-schluricke/quivr-for-moodle) (self-hosted)
- **Database**: MySQL/MariaDB or PostgreSQL

## Installation

### Option 1: ZIP Upload

1. Download the latest release from [GitHub Releases](https://github.com/sebastian-schluricke/moodle-mod_quivrchat/releases)
2. Site administration -> Plugins -> Install plugins
3. Upload the ZIP file
4. Follow the upgrade prompts

### Option 2: Manual

```bash
# Clone into Moodle mod directory
git clone https://github.com/sebastian-schluricke/moodle-mod_quivrchat.git /path/to/moodle/mod/quivrchat

# Note: only the quivrchat/ subdirectory should be in mod/
cp -r moodle-mod_quivrchat/quivrchat /path/to/moodle/mod/

# Run Moodle upgrade
php /path/to/moodle/admin/cli/upgrade.php
```

### Configuration

1. **Set API URL**: Site administration -> Plugins -> Activity modules -> Quivr Chat -> Enter Quivr API URL
2. **Add Activity**: Edit course -> Add activity -> "Quivr Chat"
3. **Configure**: Enter API key, load and select a Brain, optionally add custom instructions and enable popup

## Activity Settings

| Setting | Description |
|---------|-------------|
| Name | Display name of the activity |
| API Key | Teacher's Quivr API key (stored in user preferences) |
| Brain | Selected knowledge base from the Quivr backend |
| Custom Instructions | Per-activity behavior rules for the AI (optional) |
| Use as Popup | Show floating chat button on all course pages |

## File Structure

```
quivrchat/
├── amd/                        # AMD JavaScript modules
│   ├── src/
│   │   ├── chat.js             # Main chat module
│   │   └── popup.js            # Popup overlay module
│   └── build/                  # Minified builds
├── api/                        # Legacy PHP endpoints (kept for backwards compat)
├── classes/
│   ├── event/                  # Moodle events
│   │   ├── course_module_viewed.php
│   │   └── course_module_instance_list_viewed.php
│   ├── external/               # Moodle External Services
│   │   ├── get_brains.php
│   │   ├── get_token.php
│   │   ├── get_instance.php
│   │   ├── get_session.php
│   │   ├── save_session.php
│   │   └── clear_session.php
│   ├── hook_callbacks.php      # Hooks API callbacks
│   └── privacy/
│       └── provider.php        # Privacy API
├── db/
│   ├── access.php              # Capabilities
│   ├── hooks.php               # Hook registrations
│   ├── install.xml             # Database schema
│   ├── services.php            # External service definitions
│   └── upgrade.php             # Database migrations
├── js/vendor/                  # Third-party libraries
│   ├── marked.min.js           # Markdown parser (v12.0.0)
│   ├── purify.min.js           # XSS protection (v3.0.8)
│   └── highlight.min.js        # Syntax highlighting (v11.9.0)
├── lang/                       # Language files (de, en)
├── styles/                     # CSS (namespaced under .mod_quivrchat)
├── templates/
│   └── view.mustache           # Chat UI template
├── lib.php                     # Module API functions
├── mod_form.php                # Activity configuration form
├── settings.php                # Admin settings
├── thirdpartylibs.xml          # Third-party library declarations
├── version.php                 # Plugin version
└── view.php                    # Activity view (renders template)
```

## External Services (AJAX API)

All non-streaming operations are available as Moodle External Services, called from JavaScript via `core/ajax`:

| Service | Type | Description |
|---------|------|-------------|
| `mod_quivrchat_get_brains` | read | Fetch available brains for an API key |
| `mod_quivrchat_get_token` | read | Obtain a scoped chat token for an activity |
| `mod_quivrchat_get_instance` | read | Get the popup chat instance for a course |
| `mod_quivrchat_get_session` | read | Retrieve chat session (chat_id + history) |
| `mod_quivrchat_save_session` | write | Save chat_id and/or a message to session |
| `mod_quivrchat_clear_session` | write | Clear the chat session ("New Chat") |

## Development

### Local Development

```bash
# Clone repository
git clone git@github.com:sebastian-schluricke/moodle-mod_quivrchat.git

# Link plugin to Moodle
ln -s /path/to/moodle-mod_quivrchat/quivrchat /path/to/moodle/mod/quivrchat

# Clear caches after changes
php /path/to/moodle/admin/cli/purge_caches.php
```

### Code Quality

```bash
# Run Moodle CodeChecker
composer create-project moodlehq/moodle-local_codechecker codechecker --no-dev
./codechecker/vendor/bin/phpcs --standard=moodle --extensions=php quivrchat/
```

### Docker Development

```bash
docker cp quivrchat moodle:/var/www/html/mod/quivrchat
docker exec -u www-data moodle php /var/www/html/admin/cli/upgrade.php --non-interactive
docker exec -u www-data moodle php /var/www/html/admin/cli/purge_caches.php
```

## Integration with Quivr Backend

This plugin requires the [quivr-for-moodle](https://github.com/sebastian-schluricke/quivr-for-moodle) backend. Key integration points:

| Plugin (External Service) | Quivr API | Purpose |
|---------------------------|-----------|---------|
| `mod_quivrchat_get_token` | `POST /chat/token` | Create scoped token |
| `mod_quivrchat_get_brains` | `GET /brains/` | List available brains |
| JS `chat.js` (direct) | `POST /chat` | Create chat session |
| JS `chat.js` (direct) | `POST /chat/{id}/question/stream` | Ask question (SSE streaming) |

The streaming endpoint supports `custom_instructions` in the request body, which the Quivr backend passes to the LLM prompt template with highest priority.

## License

GNU General Public License v3.0 - see [LICENSE](quivrchat/LICENSE.md)

## Contributing

Contributions are welcome! Please create a pull request.

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/new-feature`)
3. Run the CodeChecker before submitting
4. Open a pull request

## Support

- [GitHub Issues](https://github.com/sebastian-schluricke/moodle-mod_quivrchat/issues)

## Related Projects

- [quivr-for-moodle](https://github.com/sebastian-schluricke/quivr-for-moodle) - The Quivr backend (ESFL fork)
- [QuivrHQ/quivr](https://github.com/QuivrHQ/quivr) - Original Quivr project
