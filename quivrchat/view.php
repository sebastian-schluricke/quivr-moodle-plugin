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
 * Display the Quivr Chat activity.
 *
 * @package     mod_quivrchat
 * @copyright   2024 Sebastian Schluricke <schluricke@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

$id = required_param('id', PARAM_INT); // Course Module ID.
$popup = optional_param('popup', 0, PARAM_INT); // Is this being loaded in a popup?

$cm = get_coursemodule_from_id('quivrchat', $id, 0, false, MUST_EXIST);
$course = get_course($cm->course);
$context = context_module::instance($cm->id);

// Require login and check access.
require_login($course, true, $cm);
require_capability('mod/quivrchat:view', $context);

// Page setup.
$PAGE->set_url('/mod/quivrchat/view.php', ['id' => $id]);
$PAGE->set_title(format_string($cm->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

// Trigger event.
\mod_quivrchat\event\course_module_viewed::create([
    'objectid' => $cm->instance,
    'context' => $context,
])->trigger();

// Get instance data.
$instance = $DB->get_record('quivrchat', ['id' => $cm->instance], '*', MUST_EXIST);
$brainid = $instance->brainid;
$custominstructions = $instance->custom_instructions ?? '';
// Note: API key is NOT exposed to frontend - it's only used server-side in get_token.php.

// Get Quivr API URL from plugin settings.
$quivrapiurl = get_config('mod_quivrchat', 'quivr_api_url');
if (empty($quivrapiurl)) {
    $quivrapiurl = getenv('QUIVR_API_URL') ?: 'http://localhost:5050';
}

// Get localized strings for JavaScript.
$strings = [
    'connecting' => get_string('connecting', 'mod_quivrchat'),
    'chat_restored' => get_string('chat_restored', 'mod_quivrchat'),
    'chat_welcome' => get_string('chat_welcome', 'mod_quivrchat'),
    'chat_new_started' => get_string('chat_new_started', 'mod_quivrchat'),
    'error_prefix' => get_string('error_prefix', 'mod_quivrchat'),
    'error_unexpected' => get_string('error_unexpected', 'mod_quivrchat'),
    'followup_questions' => get_string('followup_questions', 'mod_quivrchat'),
    'feedback_not_helpful' => get_string('feedback_not_helpful', 'mod_quivrchat'),
];

// Add CSS for Quivr Chat-style UI.
$PAGE->requires->css(new moodle_url('/mod/quivrchat/styles/quivr-chat.css'));

// Add Markdown rendering libraries (loaded before quivr-chat.js).
// marked.js - Markdown parser (v12.0.0).
$PAGE->requires->js(new moodle_url('/mod/quivrchat/js/vendor/marked.min.js'), true);
// DOMPurify - XSS protection for HTML rendering (v3.0.8).
$PAGE->requires->js(new moodle_url('/mod/quivrchat/js/vendor/purify.min.js'), true);
// Highlight.js - Syntax highlighting for code blocks (v11.9.0).
$PAGE->requires->css(new moodle_url('/mod/quivrchat/styles/vendor/highlight-github.css'));
$PAGE->requires->js(new moodle_url('/mod/quivrchat/js/vendor/highlight.min.js'), true);

// Main chat script (must load after libraries).
$PAGE->requires->js(new moodle_url('/mod/quivrchat/js/quivr-chat.js'));

// Add popup-specific adjustments if in popup mode.
// Note: popup-chat.css is now loaded via mod_quivrchat_before_standard_html_head() for all pages.
if ($popup) {
    $PAGE->add_body_class('quivrchat-popup');
    $PAGE->set_pagelayout('popup');
}

// Output start.
echo $OUTPUT->header();

// Get localized UI strings.
$strconnecting = get_string('connecting', 'mod_quivrchat');
$strnewchat = get_string('newchat', 'mod_quivrchat');
$strnewchattitle = get_string('newchat_title', 'mod_quivrchat');
$strplaceholder = get_string('inputplaceholder', 'mod_quivrchat');
$stravataralt = get_string('avatar_alt', 'mod_quivrchat');
$strsend = get_string('send', 'mod_quivrchat');

// Chat UI HTML.
$cmid = $cm->id;
$stringsjson = json_encode($strings);
$custominstructionsjson = json_encode($custominstructions);

$headinghtml = $OUTPUT->heading(format_string($instance->name));

echo <<<HTML
<div class="mod_quivrchat">
{$headinghtml}
<div id="background-container" class="background-container">
  <div class="chat-container">
    <div class="chat-header">
      <h2 id="intro-text" class="intro-text">{$strconnecting}</h2>
      <button id="new_chat_btn" class="new-chat-btn" title="{$strnewchattitle}">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M12 5v14M5 12h14"/>
        </svg>
        {$strnewchat}
      </button>
    </div>

    <div id="chat_history" class="chat-history">
      <!-- Chat messages will appear here -->
    </div>

    <div class="chat-input-container">
      <div class="chat-input-wrapper">
        <img id="quivr-avatar" src="{$CFG->wwwroot}/mod/quivrchat/pix/avatar.svg"
            class="chat-avatar" alt="{$stravataralt}">
        <input type="text" id="chat_input" class="chat-input"
            placeholder="{$strplaceholder}" maxlength="500" disabled>
        <button id="confirm_chat_input" class="chat-send-btn">
            <img src="{$CFG->wwwroot}/mod/quivrchat/pix/send.svg" alt="{$strsend}">
        </button>
      </div>
      <div class="input-counter" id="input_counter">0/500</div>
    </div>
  </div>
</div>
</div>

<script>
// Initialize variables for the chat
const cmid = {$cmid};
const brainId = "{$brainid}";
const quivrApiUrl = "{$quivrapiurl}";
const quivrChatStrings = {$stringsjson};
const customInstructions = {$custominstructionsjson};

document.addEventListener("DOMContentLoaded", function() {
  // The initialization will be handled by the quivr-chat.js file
  if (typeof initQuivrChat === 'function') {
    // Note: API key is no longer passed to frontend - tokens are fetched server-side
    initQuivrChat(cmid, brainId, quivrApiUrl, quivrChatStrings, customInstructions);

    // Add event listener for "New Chat" button
    const newChatBtn = document.getElementById('new_chat_btn');
    if (newChatBtn) {
      newChatBtn.addEventListener('click', function() {
        if (myBrainChatInstance) {
          myBrainChatInstance.startNewChat();
        }
      });
    }
  } else {
    console.error('Quivr Chat script not loaded properly');
  }
});
</script>
HTML;

echo $OUTPUT->footer();
