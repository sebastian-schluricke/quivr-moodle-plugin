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
use core_external\external_single_structure;
use core_external\external_value;

/**
 * External service to save chat session data (chat_id and/or message).
 *
 * @package     mod_quivrchat
 * @copyright   2024 Sebastian Schluricke <schluricke@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class save_session extends external_api {
    /**
     * Describes the parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'cmid' => new external_value(PARAM_INT, 'Course module ID'),
            'chatid' => new external_value(PARAM_ALPHANUMEXT, 'Chat ID (UUID) to store', VALUE_DEFAULT, ''),
            'messagerole' => new external_value(PARAM_ALPHA, 'Message role (user or assistant)', VALUE_DEFAULT, ''),
            'messagecontent' => new external_value(PARAM_RAW, 'Message content', VALUE_DEFAULT, ''),
        ]);
    }

    /**
     * Save chat session data.
     *
     * @param int $cmid Course module ID.
     * @param string $chatid Chat ID to store.
     * @param string $messagerole Message role.
     * @param string $messagecontent Message content.
     * @return array
     */
    public static function execute(int $cmid, string $chatid = '', string $messagerole = '',
            string $messagecontent = ''): array {
        $params = self::validate_parameters(self::execute_parameters(), [
            'cmid' => $cmid,
            'chatid' => $chatid,
            'messagerole' => $messagerole,
            'messagecontent' => $messagecontent,
        ]);

        $cm = get_coursemodule_from_id('quivrchat', $params['cmid'], 0, false, MUST_EXIST);
        $context = \context_module::instance($cm->id);
        self::validate_context($context);
        require_capability('mod/quivrchat:view', $context);

        $sessionkeychatid = 'quivrchat_chatid_' . $params['cmid'];
        $sessionkeyhistory = 'quivrchat_history_' . $params['cmid'];

        // Store chat_id if provided.
        if (!empty($params['chatid'])) {
            if (!preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i', $params['chatid'])) {
                throw new \moodle_exception('error_invalid_chatid_format', 'mod_quivrchat');
            }
            $_SESSION[$sessionkeychatid] = $params['chatid'];
        }

        // Add message to history if provided.
        if (!empty($params['messagerole']) && $params['messagecontent'] !== '') {
            if (!isset($_SESSION[$sessionkeyhistory])) {
                $_SESSION[$sessionkeyhistory] = [];
            }
            // Limit history to last 50 messages.
            if (count($_SESSION[$sessionkeyhistory]) >= 50) {
                array_shift($_SESSION[$sessionkeyhistory]);
            }
            $_SESSION[$sessionkeyhistory][] = [
                'role' => $params['messagerole'],
                'content' => $params['messagecontent'],
                'timestamp' => time(),
            ];
        }

        return [
            'success' => true,
            'chat_id' => $_SESSION[$sessionkeychatid] ?? '',
            'history_count' => count($_SESSION[$sessionkeyhistory] ?? []),
        ];
    }

    /**
     * Describes the return value.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'Whether the operation succeeded'),
            'chat_id' => new external_value(PARAM_RAW, 'The stored chat ID'),
            'history_count' => new external_value(PARAM_INT, 'Number of messages in history'),
        ]);
    }
}
