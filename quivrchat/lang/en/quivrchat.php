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
 * @copyright   
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Quivr Chat';
$string['privacy:metadata'] = 'Quivr Chat does not store any personal data';
$string['modulename'] = 'Quivr Chat';
$string['modulenameplural'] = 'Quivr Chats';
$string['modulename_help'] = 'This module lets students chat with an AI-powered knowledge brain.';
$string['quivrchatname'] = 'Chat title';
$string['quivrchatname_help'] = 'The name of this chat activity.';
$string['pluginadministration'] = 'Quivr Chat administration';
$string['view'] = 'View chat';
$string['quivrchat:view'] = 'View the Quivr Chat! activity';
$string['quivrchat:addinstance'] = 'Add a new Quivr Chat! activity';

// Settings
$string['quivr_api_url'] = 'Quivr API URL';
$string['quivr_api_url_desc'] = 'The URL of the Quivr backend server (e.g., http://localhost:5050).';

// Form fields
$string['brainid'] = 'Brain ID';
$string['brainid_help'] = 'The UUID of the Quivr Brain to chat with.';
$string['apikey'] = 'API Key';
$string['apikey_help'] = 'The API key for accessing the Quivr Brain. Will be saved to your profile.';
$string['apikey_from_profile'] = 'Use API key from profile';
$string['apikey_from_profile_desc'] = 'Your saved API key will be used. Leave empty to use the saved key, or enter a new one.';
$string['quivrchatfieldset'] = 'Brain Settings';
$string['quivrchatsettings'] = 'Configure the connection to the Quivr Brain.';

// Brain selection
$string['loadbrains'] = 'Load Brains';
$string['selectbrain'] = '-- Select Brain --';
$string['loadingbrains'] = 'Loading brains...';
$string['brainsloaded'] = 'Brains loaded successfully';
$string['nobrainsfound'] = 'No brains found. Please check your API key.';
$string['errorloadingbrains'] = 'Error loading brains';

// Popup settings
$string['popupsettings'] = 'Popup Settings';
$string['use_for_popup'] = 'Use for course popup';
$string['use_for_popup_desc'] = 'Use this activity for the course-wide popup chat button';
$string['use_for_popup_help'] = 'If checked, this activity will be used when students click the "Quivr Chat" popup button on course pages. Only one activity per course can be used for the popup - enabling this will automatically disable it for other activities in the same course.';
