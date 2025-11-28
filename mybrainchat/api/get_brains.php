<?php
/**
 * Get available brains from Quivr backend.
 *
 * This endpoint fetches the list of available brains for the current user
 * using the stored API key.
 *
 * @package    mod_mybrainchat
 * @copyright  2024 ESFL
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);
require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/filelib.php');

// Require user to be logged in
require_login();

header('Content-Type: application/json');

global $USER;

// Get API key from user preferences or from POST parameter
$apikey = optional_param('apikey', '', PARAM_RAW);
if (empty($apikey)) {
    $apikey = get_user_preferences('mybrainchat_apikey', '', $USER->id);
}

if (empty($apikey)) {
    echo json_encode([
        'success' => false,
        'error' => 'No API key available. Please enter an API key first.',
        'brains' => []
    ]);
    exit;
}

// Quivr API URL - use plugin settings, then environment variable, then default
// Use host.docker.internal for Docker environments to reach the host machine
$API_URL = get_config('mod_mybrainchat', 'quivr_api_url');
if (empty($API_URL)) {
    $API_URL = getenv('QUIVR_API_URL') ?: 'http://host.docker.internal:5050';
}

// Request brains from Quivr
$ch = curl_init("$API_URL/brains/");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $apikey,
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
$curl_errno = curl_errno($ch);
$curl_error = curl_error($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Check for errors
if ($curl_errno !== 0) {
    echo json_encode([
        'success' => false,
        'error' => 'Failed to connect to Quivr backend: ' . $curl_error,
        'brains' => []
    ]);
    exit;
}

if ($httpcode !== 200) {
    $data = json_decode($response, true);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch brains: ' . ($data['detail'] ?? 'HTTP ' . $httpcode),
        'brains' => []
    ]);
    exit;
}

$data = json_decode($response, true);

// Transform the response to a simpler format
// API returns {"brains": [...]} so we need to access the brains key
$brains = [];
$brainsList = $data['brains'] ?? $data;
if (is_array($brainsList)) {
    foreach ($brainsList as $brain) {
        // Skip model entries (brain_type = "model")
        if (isset($brain['brain_type']) && $brain['brain_type'] === 'model') {
            continue;
        }
        $brains[] = [
            'id' => (string)($brain['id'] ?? $brain['brain_id'] ?? ''),
            'name' => $brain['name'] ?? 'Unnamed Brain',
            'description' => $brain['description'] ?? ''
        ];
    }
}

// Return the brains list
echo json_encode([
    'success' => true,
    'brains' => $brains
]);
