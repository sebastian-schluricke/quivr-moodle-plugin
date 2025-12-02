# Quivr Chat - Moodle Activity Plugin

A Moodle activity module for integrating AI-powered knowledge bases (Quivr Brains) into Moodle courses. Enables students and teachers to interact with RAG-based (Retrieval-Augmented Generation) chatbots directly within the learning platform.

## Overview

This plugin connects Moodle with a [Quivr backend](https://github.com/sebastian-schluricke/quivr-for-moodle) to provide AI-powered chat functionality in courses. Teachers can link specific "Brains" (knowledge bases) to course activities, allowing learners to ask questions and receive answers based on uploaded documents.

**Use Case - Education:** Teachers upload course materials, scripts, or curricula to a Quivr Brain. Students can then ask questions about these materials within the Moodle course and receive AI-generated answers with source references.

## Features

- **AI Chat Integration**: Streaming responses for fluid conversation
- **Secure Token Architecture**: API keys are never transmitted to the browser
- **Markdown Rendering**: Formatted responses with syntax highlighting for code
- **Popup Chat**: Optional floating chat button on all course pages
- **Session Persistence**: Chat history is preserved during the session
- **Multilingual**: German and English

## Screenshots

### Plugin Settings
Configure the Quivr API URL in the site administration.

![Plugin Settings](images/Picture%201%20-%20Plugin%20Settings.png)

### Activity Configuration
Set up a new Quivr Chat activity with brain selection and popup options.

![Brain Settings](images/Picture%202%20-%20Brain%20Settings%20-%20New%20Activity.png)

### Chat Interface
Students interact with the AI-powered knowledge brain directly in the activity.

![Chat in Activity](images/Picture%203%20-%20Brain%20Chat%20in%20activity.png)

### Course-wide Popup
Optional floating chat button available on all course pages.

![Popup Chat](images/Picture%204%20-%20Brain%20Chat%20in%20course%20-%20global.png)

## Architecture

```
┌─────────────────┐         ┌─────────────────┐         ┌─────────────────┐
│                 │         │                 │         │                 │
│  Moodle Frontend│◄───────►│  Moodle Backend │◄───────►│  Quivr Backend  │
│  (JavaScript)   │  Token  │  (PHP)          │  API    │  (Python/FastAPI)│
│                 │         │                 │  Key    │                 │
└─────────────────┘         └─────────────────┘         └─────────────────┘
```

### Token Flow (Security Model)

1. Teacher stores their API key in the Moodle activity
2. API key is stored in the database (never transmitted to frontend)
3. Frontend requests a time-limited, brain-specific token from the Moodle backend
4. Frontend uses this scoped token directly with the Quivr API for chat operations

## Requirements

- **Moodle**: Version 4.0 or higher
- **PHP**: Version 8.0 or higher
- **Quivr Backend**: [quivr-for-moodle](https://github.com/sebastian-schluricke/quivr-for-moodle) (self-hosted)
- **Database**: MySQL/MariaDB or PostgreSQL

## Installation

### 1. Install Plugin

```bash
# Copy plugin to Moodle directory
cp -r quivrchat /path/to/moodle/mod/

# Run Moodle upgrade
php /path/to/moodle/admin/cli/upgrade.php
```

Or via the Moodle interface:
1. Site administration → Plugins → Install plugins
2. Upload the plugin ZIP file

### 2. Configure Plugin

1. **Set API URL**:
   - Site administration → Plugins → Activity modules → Quivr Chat
   - Enter Quivr API URL (e.g., `https://quivr.your-school.com`)

### 3. Add Activity to Course

1. Edit course → Add activity → "Quivr Chat"
2. Enter name
3. Enter API key (from Quivr backend)
4. Click "Load Brains"
5. Select brain
6. Optional: Enable "Use as popup chat"
7. Save

## Configuration

### Site-wide Settings

| Setting | Description | Default |
|---------|-------------|---------|
| `quivr_api_url` | URL of the Quivr backend | - |

### Activity Settings

| Setting | Description |
|---------|-------------|
| Name | Display name of the activity |
| API Key | Teacher's Quivr API key |
| Brain | Selected knowledge base |
| As Popup | Chat as floating window throughout the course |

## File Structure

```
quivrchat/
├── api/                    # PHP API endpoints
│   ├── get_token.php       # Retrieve scoped token
│   ├── get_brains.php      # Load available brains
│   ├── session_chat.php    # Manage chat session
│   └── get_instance.php    # Retrieve popup instance
├── classes/                # Moodle classes
├── db/                     # Database schema & permissions
│   ├── access.php
│   ├── install.xml
│   └── upgrade.php
├── js/                     # Frontend JavaScript
│   ├── quivr-chat.js       # Main chat class
│   ├── popup-chat.js       # Popup overlay
│   └── vendor/             # External libraries
├── lang/                   # Language files
│   ├── de/
│   └── en/
├── styles/                 # CSS
│   ├── quivr-chat.css
│   └── vendor/
├── lib.php                 # Moodle core hooks
├── mod_form.php            # Activity form
├── view.php                # Main view
└── settings.php            # Admin settings
```

## Development

### Local Development Environment

```bash
# Clone repository
git clone git@github.com:sebastian-schluricke/quivr-moodle-plugin.git

# Link plugin to Moodle installation
ln -s /path/to/quivr-moodle-plugin/quivrchat /path/to/moodle/mod/quivrchat

# Clear caches after changes
php /path/to/moodle/admin/cli/purge_caches.php
```

### Docker Development

```bash
# Copy plugin to Docker container
docker cp quivrchat moodle-container:/var/www/html/mod/quivrchat

# Run upgrade
docker exec moodle-container php /var/www/html/admin/cli/upgrade.php --non-interactive

# Clear caches
docker exec moodle-container php /var/www/html/admin/cli/purge_caches.php
```

### Debugging

Enable debug mode in Moodle:
```php
// config.php
@error_reporting(E_ALL | E_STRICT);
@ini_set('display_errors', '1');
$CFG->debug = (E_ALL | E_STRICT);
$CFG->debugdisplay = 1;
```

Browser console shows JavaScript debug information.

## API Endpoints

### GET /mod/quivrchat/api/get_brains.php

Loads available brains for the API key.

**Parameters:**
- `apikey` (optional): API key, if not stored in user preferences

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

Retrieves a time-limited chat token.

**Parameters:**
- `instanceid`: ID of the Quivr Chat instance

**Response:**
```json
{
  "success": true,
  "token": "eyJ...",
  "brainId": "uuid"
}
```

### GET/POST/DELETE /mod/quivrchat/api/session_chat.php

Manages chat session data (chat_id, history).

## Integration with Quivr Backend

This plugin requires the [quivr-for-moodle](https://github.com/sebastian-schluricke/quivr-for-moodle) backend. Key endpoints:

| Plugin Endpoint | Quivr API | Purpose |
|-----------------|-----------|---------|
| `get_token.php` | `POST /chat/token` | Create scoped token |
| `get_brains.php` | `GET /brains/` | List brains |
| JS QuivrChat | `POST /chat` | Create chat session |
| JS QuivrChat | `POST /chat/{id}/question/stream` | Ask question (streaming) |

## Known Limitations

- Feedback thumbs feature is disabled (see TODO.md)
- Streaming parser supports multiple formats (JSON chunks, SSE, plain text)

## License

GNU General Public License v3.0 - see [LICENSE](quivrchat/LICENSE.md)

## Contributing

Contributions are welcome! Please create a pull request.

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/new-feature`)
3. Commit your changes
4. Push the branch (`git push origin feature/new-feature`)
5. Open a pull request

## Support

For questions or issues:
- [GitHub Issues](https://github.com/sebastian-schluricke/quivr-moodle-plugin/issues)

## Related Projects

- [quivr-for-moodle](https://github.com/sebastian-schluricke/quivr-for-moodle) - The Quivr backend (fork)
- [QuivrHQ/quivr](https://github.com/QuivrHQ/quivr) - Original Quivr project
