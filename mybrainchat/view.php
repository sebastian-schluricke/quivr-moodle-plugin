<?php
require(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

$id = required_param('id', PARAM_INT); // Course Module ID

$cm = get_coursemodule_from_id('mybrainchat', $id, 0, false, MUST_EXIST);
$course = get_course($cm->course);
$context = context_module::instance($cm->id);

// Require login and check access.
require_login($course, true, $cm);
require_capability('mod/mybrainchat:view', $context);

// Page setup
$PAGE->set_url('/mod/mybrainchat/view.php', ['id' => $id]);
$PAGE->set_title(format_string($cm->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

// Trigger event
\mod_mybrainchat\event\course_module_viewed::create([
    'objectid' => $cm->instance,
    'context' => $context,
])->trigger();

// Get instance data
$instance = $DB->get_record('mybrainchat', ['id' => $cm->instance], '*', MUST_EXIST);
$brainid = $instance->brainid;
$apikey = $instance->apikey; // Wird aktuell nicht im Frontend verwendet

// Add CSS for Hugo-style UI
$PAGE->requires->css(new moodle_url('/mod/mybrainchat/styles/hugo-style.css'));
$PAGE->requires->js(new moodle_url('/mod/mybrainchat/js/hugo-script.js'));

// Output start
echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($instance->name));

// Chat UI HTML
$cmid = $cm->id;
echo <<<HTML
<div id="background-container" class="background-container">
  <div class="chat-container">
    <div class="chat-header">
      <h2 id="intro-text" class="intro-text">Verbindung zum Brain wird hergestellt...</h2>
      <div class="chat-controls">
        <button id="open_confirm_box" class="chat-close-btn">{$OUTPUT->pix_icon('i/close', 'Close')}</button>
      </div>
    </div>
    
    <div id="chat_history" class="chat-history">
      <!-- Chat messages will appear here -->
    </div>
    
    <div class="chat-input-container">
      <div class="chat-input-wrapper">
        <img id="hugo-avatar" src="{$CFG->wwwroot}/mod/mybrainchat/pix/avatar.svg" class="chat-avatar" alt="Avatar">
        <input type="text" id="chat_input" class="chat-input" placeholder="Was möchtest du wissen?" maxlength="500" disabled>
        <button id="confirm_chat_input" class="chat-send-btn">{$OUTPUT->pix_icon('i/send', 'Send')}</button>
      </div>
      <div class="input-counter" id="input_counter">0/500</div>
    </div>
  </div>
  
  <!-- Confirmation dialog -->
  <div id="confirmBox" class="confirm-box" style="display: none;">
    <div class="confirm-content">
      <p>Möchtest du den Chat wirklich beenden?</p>
      <div class="confirm-buttons">
        <button id="confirm_close_chat_btn" class="confirm-yes">Ja</button>
        <button id="cancel_confirm_box_btn" class="confirm-no">Nein</button>
      </div>
    </div>
  </div>
</div>

<script>
// Initialize variables for the chat
const cmid = {$cmid};
const brainId = "{$brainid}";
const apiKey = "{$apikey}";
document.addEventListener("DOMContentLoaded", function() {
  // The initialization will be handled by the hugo-script.js file
  if (typeof initMyBrainChat === 'function') {
    initMyBrainChat(cmid, brainId, apiKey);
  } else {
    console.error('Hugo script not loaded properly');
  }
});
</script>
HTML;

echo $OUTPUT->footer();
