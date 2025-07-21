<?php
define('AJAX_SCRIPT', true);
require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/filelib.php'); // Moodle curl
require_login();

header('Content-Type: application/json');

// Moodle parameters
$cmid = required_param('cmid', PARAM_INT);
$question = required_param('question', PARAM_TEXT);

// Load the activity
$cm = get_coursemodule_from_id('mybrainchat', $cmid, 0, false, MUST_EXIST);
$context = context_module::instance($cm->id);
require_capability('mod/mybrainchat:view', $context);

$instance = $DB->get_record('mybrainchat', ['id' => $cm->instance], '*', MUST_EXIST);
$brainid = $instance->brainid;
$apikey = $instance->apikey;

// API URL for quivr backend
$API_URL = 'https://api.quivr.esfl.io';

// Step 1: Create chat
$chat = curl_post_json("$API_URL/chat", [
    'name'     => 'Moodle Chat',
    'brain_id' => $brainid
], $apikey);

if (!isset($chat['chat_id'])) {
    http_response_code(500);
    echo json_encode(['error' => 'Chat creation failed']);
    exit;
}

$chatid = $chat['chat_id'];

// Step 2: Ask question (non-streaming response expected here)
$reply = curl_post_json(
    "$API_URL/chat/$chatid/question/stream?brain_id=$brainid",
    ['question' => $question],
    $apikey
);

echo json_encode(['answer' => $reply['fullMessage'] ?? 'No response.']);


// Helper function to POST JSON using Moodle's curl
function curl_post_json($url, $data, $apikey) {
    $curl = new curl();

    // Build the header lines
    $headers = [
        "Authorization: Bearer $apikey",
        "Content-Type: application/json",
    ];

    // Use string keys for curl options
    $options = [
        'CURLOPT_HTTPHEADER' => $headers,
    ];

    // JSON-encode the payload
    $json_data = json_encode($data);

    // Send the request with JSON body and options
    $response = $curl->post($url, $json_data, $options);

    return json_decode($response, true);
}

