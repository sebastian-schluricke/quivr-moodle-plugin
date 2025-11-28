<?php
/**
 * Session-based chat_id storage endpoint.
 *
 * This endpoint allows the frontend to store/retrieve/clear the chat_id
 * in the PHP session, so users can continue their chat after page reload.
 *
 * Actions:
 *   - GET:  Retrieve the stored chat_id for this course module
 *   - POST: Store a chat_id for this course module
 *   - DELETE: Clear the stored chat_id (for "New Chat" feature)
 *
 * @package    mod_mybrainchat
 * @copyright  2024 ESFL
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);
require_once(__DIR__ . '/../../../config.php');

// Require user to be logged in
require_login();

header('Content-Type: application/json');

// Get course module ID
$cmid = required_param('cmid', PARAM_INT);

// Load the activity and verify access
$cm = get_coursemodule_from_id('mybrainchat', $cmid, 0, false, MUST_EXIST);
$context = context_module::instance($cm->id);
require_capability('mod/mybrainchat:view', $context);

// Session keys for this specific course module and user
$session_key_chatid = 'mybrainchat_chatid_' . $cmid;
$session_key_history = 'mybrainchat_history_' . $cmid;

// Determine action based on request method
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Retrieve stored chat_id and history
        $chat_id = isset($_SESSION[$session_key_chatid]) ? $_SESSION[$session_key_chatid] : null;
        $history = isset($_SESSION[$session_key_history]) ? $_SESSION[$session_key_history] : [];
        echo json_encode([
            'success' => true,
            'chat_id' => $chat_id,
            'history' => $history
        ]);
        break;

    case 'POST':
        // Store chat_id and/or add message to history
        $input = json_decode(file_get_contents('php://input'), true);
        $chat_id = $input['chat_id'] ?? null;
        $message = $input['message'] ?? null;

        // Store chat_id if provided
        if (!empty($chat_id)) {
            // Validate chat_id format (UUID)
            if (!preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i', $chat_id)) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'Invalid chat_id format'
                ]);
                exit;
            }
            $_SESSION[$session_key_chatid] = $chat_id;
        }

        // Add message to history if provided
        if (!empty($message)) {
            if (!isset($_SESSION[$session_key_history])) {
                $_SESSION[$session_key_history] = [];
            }

            // Validate message structure
            if (!isset($message['role']) || !isset($message['content'])) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'Invalid message format (need role and content)'
                ]);
                exit;
            }

            // Limit history to last 50 messages to prevent session bloat
            if (count($_SESSION[$session_key_history]) >= 50) {
                array_shift($_SESSION[$session_key_history]);
            }

            $_SESSION[$session_key_history][] = [
                'role' => $message['role'],
                'content' => $message['content'],
                'timestamp' => time()
            ];
        }

        echo json_encode([
            'success' => true,
            'chat_id' => $_SESSION[$session_key_chatid] ?? null,
            'history_count' => count($_SESSION[$session_key_history] ?? [])
        ]);
        break;

    case 'DELETE':
        // Clear stored chat_id and history (for "New Chat")
        unset($_SESSION[$session_key_chatid]);
        unset($_SESSION[$session_key_history]);
        echo json_encode([
            'success' => true,
            'message' => 'Chat session cleared'
        ]);
        break;

    default:
        http_response_code(405);
        echo json_encode([
            'success' => false,
            'error' => 'Method not allowed'
        ]);
}
