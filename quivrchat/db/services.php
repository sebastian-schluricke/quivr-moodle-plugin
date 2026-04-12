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
 * External services definitions for mod_quivrchat.
 *
 * @package     mod_quivrchat
 * @copyright   2024 Sebastian Schluricke <schluricke@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'mod_quivrchat_get_brains' => [
        'classname' => 'mod_quivrchat\external\get_brains',
        'description' => 'Fetch available brains from the Quivr backend.',
        'type' => 'read',
        'ajax' => true,
        'loginrequired' => true,
    ],
    'mod_quivrchat_get_token' => [
        'classname' => 'mod_quivrchat\external\get_token',
        'description' => 'Obtain a scoped chat token for a quivrchat activity.',
        'type' => 'read',
        'ajax' => true,
        'loginrequired' => true,
        'capabilities' => 'mod/quivrchat:view',
    ],
    'mod_quivrchat_get_instance' => [
        'classname' => 'mod_quivrchat\external\get_instance',
        'description' => 'Get the primary quivrchat instance for a course (for popup).',
        'type' => 'read',
        'ajax' => true,
        'loginrequired' => true,
    ],
    'mod_quivrchat_get_session' => [
        'classname' => 'mod_quivrchat\external\get_session',
        'description' => 'Retrieve the chat session (chat_id and message history).',
        'type' => 'read',
        'ajax' => true,
        'loginrequired' => true,
        'capabilities' => 'mod/quivrchat:view',
    ],
    'mod_quivrchat_save_session' => [
        'classname' => 'mod_quivrchat\external\save_session',
        'description' => 'Save chat session data (chat_id and/or a message).',
        'type' => 'write',
        'ajax' => true,
        'loginrequired' => true,
        'capabilities' => 'mod/quivrchat:view',
    ],
    'mod_quivrchat_clear_session' => [
        'classname' => 'mod_quivrchat\external\clear_session',
        'description' => 'Clear the chat session (for "New Chat" feature).',
        'type' => 'write',
        'ajax' => true,
        'loginrequired' => true,
        'capabilities' => 'mod/quivrchat:view',
    ],
];
