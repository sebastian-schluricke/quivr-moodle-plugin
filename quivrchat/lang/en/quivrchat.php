<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin strings are defined here.
 *
 * @package     mod_quivrchat
 * @category    string
 * @copyright   2024 Sebastian Schluricke <schluricke@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// General.
$string['pluginname'] = 'Quivr Chat';
$string['modulename'] = 'Quivr Chat';
$string['modulenameplural'] = 'Quivr Chats';
$string['modulename_help'] = 'The Quivr Chat module allows students to interact with an AI-powered knowledge brain. Teachers can configure the brain connection and students can ask questions to receive AI-generated answers based on the brain\'s knowledge base.';
$string['quivrchatname'] = 'Chat title';
$string['quivrchatname_help'] = 'The name of this chat activity.';
$string['pluginadministration'] = 'Quivr Chat administration';
$string['view'] = 'View chat';
$string['quivrchat:view'] = 'View the Quivr Chat activity';
$string['quivrchat:addinstance'] = 'Add a new Quivr Chat activity';
$string['noquivrchatinstances'] = 'There are no Quivr Chat instances in this course.';

// Settings.
$string['quivr_api_url'] = 'Quivr API URL';
$string['quivr_api_url_desc'] = 'The URL of the Quivr backend server (e.g., http://localhost:5050).';

// Form fields.
$string['brainid'] = 'Brain ID';
$string['brainid_help'] = 'The UUID of the Quivr Brain to chat with.';
$string['apikey'] = 'API Key';
$string['apikey_help'] = 'The API key for accessing the Quivr Brain. Will be saved to your profile.';
$string['apikey_from_profile'] = 'Use API key from profile';
$string['apikey_from_profile_desc'] = 'Your saved API key will be used. Leave empty to use the saved key, or enter a new one.';
$string['quivrchatfieldset'] = 'Brain Settings';
$string['quivrchatsettings'] = 'Configure the connection to the Quivr Brain.';

// Brain selection.
$string['loadbrains'] = 'Load Brains';
$string['selectbrain'] = '-- Select Brain --';
$string['loadingbrains'] = 'Loading brains...';
$string['brainsloaded'] = 'Brains loaded successfully';
$string['nobrainsfound'] = 'No brains found. Please check your API key.';
$string['errorloadingbrains'] = 'Error loading brains';

// Popup settings.
$string['popupsettings'] = 'Popup Settings';
$string['use_for_popup'] = 'Use for course popup';
$string['use_for_popup_desc'] = 'Use this activity for the course-wide popup chat button';
$string['use_for_popup_help'] = 'If checked, this activity will be used when students click the "Quivr Chat" popup button on course pages. Only one activity per course can be used for the popup - enabling this will automatically disable it for other activities in the same course.';

// Chat UI strings.
$string['connecting'] = 'Connecting to brain...';
$string['newchat'] = 'New Chat';
$string['newchat_title'] = 'Start a new chat';
$string['inputplaceholder'] = 'What would you like to know?';
$string['chat_restored'] = 'Chat restored. Ask a question to Quivr Chat.';
$string['chat_welcome'] = 'Welcome! Ask a question to Quivr Chat.';
$string['chat_new_started'] = 'New chat started. Ask a question to Quivr Chat.';
$string['error_prefix'] = 'Error: ';
$string['error_unexpected'] = 'Unexpected response from server.';
$string['followup_questions'] = 'Follow-up questions:';
$string['feedback_not_helpful'] = 'Answer is not helpful!';
$string['send'] = 'Send';
$string['avatar_alt'] = 'Chat avatar';

// Privacy API.
$string['privacy:metadata'] = 'The Quivr Chat plugin sends user questions to an external Quivr API service for AI-powered responses. No personally identifiable information is transmitted.';
$string['privacy:metadata:quivr_api'] = 'User questions are sent to the configured Quivr API service to generate AI-powered responses. Only the question text is transmitted; no usernames, email addresses, or other personal data are sent.';
$string['privacy:metadata:quivr_api:question'] = 'The question text entered by the user is sent to the Quivr API to generate a response.';
$string['privacy:metadata:preference:apikey'] = 'Teachers can store their Quivr API key in their user preferences for convenient access when creating chat activities.';
