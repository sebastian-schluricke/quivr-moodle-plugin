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
 * Get available brains from Quivr backend.
 *
 * This endpoint fetches the list of available brains for the current user
 * using the stored API key.
 *
 * @package     mod_quivrchat
 * @copyright   2024 Sebastian Schluricke <schluricke@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);
require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/filelib.php');

// Require user to be logged in.
require_login();

header('Content-Type: application/json');

global $USER;

// Get API key from user preferences or from POST parameter.
$apikey = optional_param('apikey', '', PARAM_RAW);
if (empty($apikey)) {
    $apikey = get_user_preferences('quivrchat_apikey', '', $USER->id);
}

if (empty($apikey)) {
    echo json_encode([
        'success' => false,
        'error' => 'No API key available. Please enter an API key first.',
        'brains' => [],
    ]);
    exit;
}

// Quivr API URL - use plugin settings, then environment variable, then default.
// Use host.docker.internal for Docker environments to reach the host machine.
$apiurl = get_config('mod_quivrchat', 'quivr_api_url');
if (empty($apiurl)) {
    $apiurl = getenv('QUIVR_API_URL') ?: 'http://host.docker.internal:5050';
}

// Request brains from Quivr.
$ch = curl_init("$apiurl/brains/");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $apikey,
    'Content-Type: application/json',
]);

$response = curl_exec($ch);
$curlerrno = curl_errno($ch);
$curlerror = curl_error($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Check for errors.
if ($curlerrno !== 0) {
    echo json_encode([
        'success' => false,
        'error' => 'Failed to connect to Quivr backend: ' . $curlerror,
        'brains' => [],
    ]);
    exit;
}

if ($httpcode !== 200) {
    $data = json_decode($response, true);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch brains: ' . ($data['detail'] ?? 'HTTP ' . $httpcode),
        'brains' => [],
    ]);
    exit;
}

$data = json_decode($response, true);

// Transform the response to a simpler format.
// API returns {"brains": [...]} so we need to access the brains key.
$brains = [];
$brainslist = $data['brains'] ?? $data;
if (is_array($brainslist)) {
    foreach ($brainslist as $brain) {
        // Skip model entries (brain_type = "model").
        if (isset($brain['brain_type']) && $brain['brain_type'] === 'model') {
            continue;
        }
        $brains[] = [
            'id' => (string)($brain['id'] ?? $brain['brain_id'] ?? ''),
            'name' => $brain['name'] ?? 'Unnamed Brain',
            'description' => $brain['description'] ?? '',
        ];
    }
}

// Return the brains list.
echo json_encode([
    'success' => true,
    'brains' => $brains,
]);
