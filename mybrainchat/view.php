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

// Output start
echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($instance->name));

// Chat UI HTML
$cmid = $cm->id;
echo <<<HTML
<div style="margin-top: 20px;">
  <label for="chat-question"><strong>Deine Frage an das Brain:</strong></label><br>
  <input type="text" id="chat-question" placeholder="Was möchtest du wissen?" style="width: 70%; padding: 8px;" />
  <button onclick="sendQuestion()" style="padding: 8px 16px; margin-left: 10px;">Frage senden</button>
</div>

<div id="chat-answer" style="margin-top: 20px; background: #f7f7f7; border: 1px solid #ddd; padding: 15px; min-height: 100px;">
  ⏳ Noch keine Frage gestellt.
</div>

<script>
function sendQuestion() {
  const question = document.getElementById("chat-question").value.trim();
  const answerDiv = document.getElementById("chat-answer");
  if (!question) {
    answerDiv.innerText = "Bitte gib eine Frage ein.";
    return;
  }

  answerDiv.innerText = "⏳ Die Antwort wird geladen...";

  fetch("{$CFG->wwwroot}/mod/mybrainchat/api/chat.php?cmid={$cmid}&question=" + encodeURIComponent(question))
    .then(res => res.json())
    .then(data => {
      if (data.answer) {
        answerDiv.innerText = data.answer;
      } else if (data.error) {
        answerDiv.innerText = "❌ Fehler: " + data.error;
      } else {
        answerDiv.innerText = "❌ Unerwartete Antwort vom Server.";
      }
    })
    .catch(err => {
      answerDiv.innerText = "❌ Netzwerkfehler: " + err;
    });
}
</script>
HTML;

echo $OUTPUT->footer();
