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

namespace mod_quivrchat\external;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;

/**
 * External service to retrieve the chat session (chat_id + history).
 *
 * @package     mod_quivrchat
 * @copyright   2024 Sebastian Schluricke <schluricke@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_session extends external_api {
    /**
     * Describes the parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'cmid' => new external_value(PARAM_INT, 'Course module ID'),
        ]);
    }

    /**
     * Get the chat session data.
     *
     * @param int $cmid Course module ID.
     * @return array
     */
    public static function execute(int $cmid): array {
        $params = self::validate_parameters(self::execute_parameters(), ['cmid' => $cmid]);
        $cmid = $params['cmid'];

        $cm = get_coursemodule_from_id('quivrchat', $cmid, 0, false, MUST_EXIST);
        $context = \context_module::instance($cm->id);
        self::validate_context($context);
        require_capability('mod/quivrchat:view', $context);

        $sessionkeychatid = 'quivrchat_chatid_' . $cmid;
        $sessionkeyhistory = 'quivrchat_history_' . $cmid;

        return [
            'success' => true,
            'chat_id' => isset($_SESSION[$sessionkeychatid]) ? $_SESSION[$sessionkeychatid] : '',
            'history' => isset($_SESSION[$sessionkeyhistory]) ? array_map(function($msg) {
                return [
                    'role' => $msg['role'] ?? '',
                    'content' => $msg['content'] ?? '',
                ];
            }, $_SESSION[$sessionkeyhistory]) : [],
        ];
    }

    /**
     * Describes the return value.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'Whether the request succeeded'),
            'chat_id' => new external_value(PARAM_RAW, 'The stored chat ID, empty if none'),
            'history' => new external_multiple_structure(
                new external_single_structure([
                    'role' => new external_value(PARAM_ALPHA, 'Message role (user or assistant)'),
                    'content' => new external_value(PARAM_RAW, 'Message content'),
                ])
            ),
        ]);
    }
}
