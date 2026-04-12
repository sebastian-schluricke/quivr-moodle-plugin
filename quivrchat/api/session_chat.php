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
 * Session-based chat_id storage endpoint.
 *
 * This endpoint allows the frontend to store/retrieve/clear the chat_id
 * in the PHP session, so users can continue their chat after page reload.
 *
 * Actions:
 *   - GET:  Retrieve the stored chat_id for this course module.
 *   - POST: Store a chat_id for this course module.
 *   - DELETE: Clear the stored chat_id (for "New Chat" feature).
 *
 * @package     mod_quivrchat
 * @copyright   2024 Sebastian Schluricke <schluricke@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);
require_once(__DIR__ . '/../../../config.php');

// Require user to be logged in.
require_login();

header('Content-Type: application/json');

// Get course module ID.
$cmid = required_param('cmid', PARAM_INT);

// Load the activity and verify access.
$cm = get_coursemodule_from_id('quivrchat', $cmid, 0, false, MUST_EXIST);
$context = context_module::instance($cm->id);
require_capability('mod/quivrchat:view', $context);

// Session keys for this specific course module and user.
$sessionkeychatid = 'quivrchat_chatid_' . $cmid;
$sessionkeyhistory = 'quivrchat_history_' . $cmid;

// Determine action based on request method.
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Retrieve stored chat_id and history.
        $chatid = isset($_SESSION[$sessionkeychatid]) ? $_SESSION[$sessionkeychatid] : null;
        $history = isset($_SESSION[$sessionkeyhistory]) ? $_SESSION[$sessionkeyhistory] : [];
        echo json_encode([
            'success' => true,
            'chat_id' => $chatid,
            'history' => $history,
        ]);
        break;

    case 'POST':
        // Store chat_id and/or add message to history.
        $input = json_decode(file_get_contents('php://input'), true);
        $chatid = isset($input['chat_id']) ? clean_param($input['chat_id'], PARAM_ALPHANUMEXT) : null;
        $message = $input['message'] ?? null;

        // Store chat_id if provided.
        if (!empty($chatid)) {
            // Validate chat_id format (UUID).
            if (!preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i', $chatid)) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => get_string('error_invalid_chatid_format', 'mod_quivrchat'),
                ]);
                exit;
            }
            $_SESSION[$sessionkeychatid] = $chatid;
        }

        // Add message to history if provided.
        if (!empty($message)) {
            if (!isset($_SESSION[$sessionkeyhistory])) {
                $_SESSION[$sessionkeyhistory] = [];
            }

            // Validate message structure.
            if (!isset($message['role']) || !isset($message['content'])) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => get_string('error_invalid_message_format', 'mod_quivrchat'),
                ]);
                exit;
            }

            // Clean message fields.
            $role = clean_param($message['role'], PARAM_ALPHA);
            $content = clean_param($message['content'], PARAM_TEXT);

            // Limit history to last 50 messages to prevent session bloat.
            if (count($_SESSION[$sessionkeyhistory]) >= 50) {
                array_shift($_SESSION[$sessionkeyhistory]);
            }

            $_SESSION[$sessionkeyhistory][] = [
                'role' => $role,
                'content' => $content,
                'timestamp' => time(),
            ];
        }

        echo json_encode([
            'success' => true,
            'chat_id' => $_SESSION[$sessionkeychatid] ?? null,
            'history_count' => count($_SESSION[$sessionkeyhistory] ?? []),
        ]);
        break;

    case 'DELETE':
        // Clear stored chat_id and history (for "New Chat").
        unset($_SESSION[$sessionkeychatid]);
        unset($_SESSION[$sessionkeyhistory]);
        echo json_encode([
            'success' => true,
            'message' => get_string('chat_session_cleared', 'mod_quivrchat'),
        ]);
        break;

    default:
        http_response_code(405);
        echo json_encode([
            'success' => false,
            'error' => get_string('error_method_not_allowed', 'mod_quivrchat'),
        ]);
}
