# Quivr Moodle Plugin

A Moodle plugin that integrates Quivr's AI brain functionality into Moodle courses, allowing students and teachers to interact with AI-powered knowledge bases.

## Overview

This repository contains the "mybrainchat" Moodle activity module that connects to the Quivr API to provide AI-powered chat functionality within Moodle courses. The plugin allows course creators to connect specific Quivr brains to course activities, enabling students to ask questions and receive answers based on the knowledge stored in those brains.

## Features

- Integration with Quivr API for AI-powered chat
- Streaming responses for a more interactive experience
- Support for follow-up questions
- Feedback mechanisms for responses
- Customizable UI with different background options
- Multi-language support (English and German)

## Repository Structure

- `/mybrainchat/` - The main Moodle plugin directory
  - `/api/` - API integration with Quivr
  - `/classes/` - PHP classes for the plugin
  - `/db/` - Database definitions and access control
  - `/js/` - JavaScript files for the frontend
  - `/lang/` - Language files
  - `/pix/` - Images and icons
  - `/styles/` - CSS stylesheets

- `/testing/` - Testing utilities and examples

## Installation

See the [plugin README](./mybrainchat/README.md) for detailed installation instructions.

## Requirements

- Moodle 3.9 or higher
- PHP 7.4 or higher
- Access to a Quivr API instance
- API key for the Quivr service

## Development

### Setup

1. Clone this repository into your Moodle modules directory:
   ```
   git clone https://github.com/ESFL/quivr-moodle-plugin.git /path/to/moodle/mod/mybrainchat
   ```

2. Install the plugin through the Moodle admin interface or using the command line:
   ```
   php admin/cli/upgrade.php
   ```

### Testing

The `/testing/` directory contains examples and utilities for testing the plugin's functionality.

## License

This project is licensed under the GNU GPL v3 - see the [LICENSE](./mybrainchat/LICENSE.md) file for details.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request