# Quivr Chat - Moodle Activity Plugin

A Moodle activity module that enables students to chat with AI-powered knowledge brains using the [Quivr](https://github.com/QuivrHQ/quivr) platform.

## Description

Quivr Chat integrates the Quivr RAG (Retrieval-Augmented Generation) platform into Moodle, allowing educators to create interactive chat experiences powered by custom knowledge bases. Students can ask questions and receive AI-generated responses based on course-specific content that teachers have uploaded to their Quivr brains.

## Pedagogical Value

A key educational benefit of this plugin is that students can ask questions **without fear of judgment**. This shame-free environment encourages learners to seek help on topics they might otherwise hesitate to ask about in class. Teachers gain valuable insights into student questions, helping them better understand the knowledge gaps, skills, and needs of their learners - and can adapt their teaching accordingly.

## Screenshots

### Plugin Settings
Configure the Quivr API URL in the site administration.

![Plugin Settings](https://raw.githubusercontent.com/sebastian-schluricke/quivr-moodle-plugin/main/images/Picture%201%20-%20Plugin%20Settings.png)

### Activity Configuration
Set up a new Quivr Chat activity with brain selection and popup options.

![Brain Settings](https://raw.githubusercontent.com/sebastian-schluricke/quivr-moodle-plugin/main/images/Picture%202%20-%20Brain%20Settings%20-%20New%20Activity.png)

### Chat Interface
Students interact with the AI-powered knowledge brain directly in the activity.

![Chat in Activity](https://raw.githubusercontent.com/sebastian-schluricke/quivr-moodle-plugin/main/images/Picture%203%20-%20Brain%20Chat%20in%20activity.png)

### Course-wide Popup
Optional floating chat button available on all course pages.

![Popup Chat](https://raw.githubusercontent.com/sebastian-schluricke/quivr-moodle-plugin/main/images/Picture%204%20-%20Brain%20Chat%20in%20course%20-%20global.png)

## Features

- **AI-Powered Chat**: Students can interact with knowledge brains trained on course-specific content
- **Brain Selection**: Teachers configure which Quivr brain to use for each activity
- **Course Popup**: Optional floating chat button on course pages for quick access
- **Secure Integration**: API key management per teacher profile (never exposed to frontend)
- **Session Persistence**: Chat history is preserved during the session
- **Streaming Responses**: Real-time response streaming for better user experience
- **Markdown Support**: AI responses support formatted text, code blocks, and lists
- **Multilingual**: Available in English and German

## Requirements

- Moodle 4.0 or higher
- A running [quivr-for-moodle](https://github.com/sebastian-schluricke/quivr-for-moodle) backend instance
- Valid API key for the Quivr service

## Installation

### Via uploaded ZIP file

1. Log in to your Moodle site as an admin and go to _Site administration > Plugins > Install plugins_
2. Upload the ZIP file with the plugin code
3. Check the plugin validation report and finish the installation

### Manual installation

1. Copy the plugin contents to `{your/moodle/dirroot}/mod/quivrchat`
2. Log in to your Moodle site as an admin and go to _Site administration > Notifications_
3. Complete the installation

Alternatively, run from the command line:
```bash
php admin/cli/upgrade.php
```

## Configuration

### Site-wide settings

1. Go to _Site administration > Plugins > Activity modules > Quivr Chat_
2. Enter the Quivr API URL (e.g., `http://localhost:5050` or your hosted Quivr instance)

### Activity settings

When adding a Quivr Chat activity to a course:

1. Enter your API key (will be saved to your profile for future use)
2. Click "Load Brains" to fetch available brains
3. Select the brain you want students to chat with
4. Optionally enable "Use for course popup" for a floating chat button

## Privacy

This plugin sends user questions to an external Quivr API service. Only the question text is transmitted - no usernames, email addresses, or other personal data are sent. See the Privacy API implementation for details.

## Support

For bug reports and feature requests, please use the GitHub issue tracker.

## License

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program. If not, see <https://www.gnu.org/licenses/>.
