# Quivr Chat - Moodle Activity Plugin

A Moodle activity module that enables students to chat with AI-powered knowledge brains using the Quivr platform.

## Features

- **AI-Powered Chat**: Students can interact with knowledge brains that have been trained on course-specific content
- **Brain Selection**: Teachers can configure which Quivr brain to use for each activity
- **Course Popup**: Optional floating chat button on course pages for quick access
- **Secure Integration**: API key management per teacher profile

## Requirements

- Moodle 4.1 or higher (version 2022041900)
- A running Quivr backend instance
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
2. Enter the Quivr API URL (e.g., `https://api.quivr.esfl.io`)

### Activity settings

When adding a Quivr Chat activity to a course:

1. Enter your API key (will be saved to your profile for future use)
2. Click "Load Brains" to fetch available brains
3. Select the brain you want students to chat with
4. Optionally enable "Use for course popup" for a floating chat button

## Usage

Students can access the chat through:
- The activity link in the course
- The floating popup button (if enabled)

## License

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program. If not, see <https://www.gnu.org/licenses/>.
