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
 * Get a scoped chat token from Quivr backend.
 *
 * This endpoint is called by the frontend to obtain a time-limited,
 * brain-scoped token for chat operations. The master API key is never
 * exposed to the frontend.
 *
 * @package     mod_quivrchat
 * @copyright   2024 Sebastian Schluricke <schluricke@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);
require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/filelib.php');

// Require user to be logged in
require_login();

header('Content-Type: application/json');

// Get course module ID
$cmid = required_param('cmid', PARAM_INT);

// Load the activity and verify access
$cm = get_coursemodule_from_id('quivrchat', $cmid, 0, false, MUST_EXIST);
$context = context_module::instance($cm->id);
require_capability('mod/quivrchat:view', $context);

// Get instance settings (API key is stored here, never exposed to frontend)
$instance = $DB->get_record('quivrchat', ['id' => $cm->instance], '*', MUST_EXIST);
$brainid = $instance->brainid;
$apikey = $instance->apikey;

// Quivr API URL - use plugin settings, then environment variable, then default
$API_URL = get_config('mod_quivrchat', 'quivr_api_url');
if (empty($API_URL)) {
    $API_URL = getenv('QUIVR_API_URL') ?: 'http://localhost:5050';
}

// Request a scoped chat token from Quivr
// Use native PHP curl to avoid Moodle's URL blocking
$ch = curl_init("$API_URL/chat/token");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $apikey,
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'brain_id' => $brainid,
    'ttl_minutes' => 10  // Token valid for 10 minutes
]));

$response = curl_exec($ch);
$curl_errno = curl_errno($ch);
$curl_error = curl_error($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
$data = json_decode($response, true);

// Check for errors
if ($curl_errno !== 0) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to connect to Quivr backend',
        'details' => $curl_error
    ]);
    exit;
}

if ($httpcode !== 200) {
    http_response_code($httpcode);
    echo json_encode([
        'error' => 'Failed to obtain chat token',
        'details' => $data['detail'] ?? 'Unknown error'
    ]);
    exit;
}

// Return the token to the frontend
echo json_encode([
    'success' => true,
    'token' => $data['token'],
    'expires_at' => $data['expires_at'],
    'brain_id' => $data['brain_id']
]);
