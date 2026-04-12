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

// Get Quivr API URL from plugin settings.
$quivrapiurl = get_config('mod_quivrchat', 'quivr_api_url');
if (empty($quivrapiurl)) {
    $quivrapiurl = getenv('QUIVR_API_URL') ?: 'http://localhost:5050';
}

// Localized strings for JavaScript.
$jsstrings = [
    'connecting' => get_string('connecting', 'mod_quivrchat'),
    'chat_restored' => get_string('chat_restored', 'mod_quivrchat'),
    'chat_welcome' => get_string('chat_welcome', 'mod_quivrchat'),
    'chat_new_started' => get_string('chat_new_started', 'mod_quivrchat'),
    'error_prefix' => get_string('error_prefix', 'mod_quivrchat'),
    'error_unexpected' => get_string('error_unexpected', 'mod_quivrchat'),
    'followup_questions' => get_string('followup_questions', 'mod_quivrchat'),
    'feedback_not_helpful' => get_string('feedback_not_helpful', 'mod_quivrchat'),
];

// Add CSS.
$PAGE->requires->css(new moodle_url('/mod/quivrchat/styles/quivr-chat.css'));
$PAGE->requires->css(new moodle_url('/mod/quivrchat/styles/vendor/highlight-github.css'));

// Add vendor JS libraries (loaded in head, before AMD modules).
$PAGE->requires->js(new moodle_url('/mod/quivrchat/js/vendor/marked.min.js'), true);
$PAGE->requires->js(new moodle_url('/mod/quivrchat/js/vendor/purify.min.js'), true);
$PAGE->requires->js(new moodle_url('/mod/quivrchat/js/vendor/highlight.min.js'), true);

// Popup-specific adjustments.
if ($popup) {
    $PAGE->add_body_class('quivrchat-popup');
    $PAGE->set_pagelayout('popup');
}

// Build template context.
$templatecontext = [
    'heading' => $OUTPUT->heading(format_string($instance->name)),
    'str_connecting' => get_string('connecting', 'mod_quivrchat'),
    'str_newchat' => get_string('newchat', 'mod_quivrchat'),
    'str_newchat_title' => get_string('newchat_title', 'mod_quivrchat'),
    'str_placeholder' => get_string('inputplaceholder', 'mod_quivrchat'),
    'str_avatar_alt' => get_string('avatar_alt', 'mod_quivrchat'),
    'str_send' => get_string('send', 'mod_quivrchat'),
    'avatar_url' => $CFG->wwwroot . '/mod/quivrchat/pix/avatar.svg',
    'send_url' => $CFG->wwwroot . '/mod/quivrchat/pix/send.svg',
];

// Initialize chat via AMD module.
$PAGE->requires->js_call_amd('mod_quivrchat/chat', 'init', [
    $cm->id,
    $brainid,
    $quivrapiurl,
    $jsstrings,
    $custominstructions,
]);

// Render page.
echo $OUTPUT->header();
echo $OUTPUT->render_from_template('mod_quivrchat/view', $templatecontext);
echo $OUTPUT->footer();
