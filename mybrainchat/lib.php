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
 * Library of interface functions and constants.
 *
 * @package     mod_mybrainchat
 * @copyright   
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Return if the plugin supports $feature.
 *
 * @param string $feature Constant representing the feature.
 * @return true | null True if the feature is supported, null otherwise.
 */
function mybrainchat_supports($feature) {
    switch ($feature) {
        case FEATURE_MOD_INTRO:
            return true;
        default:
            return null;
    }
}

/**
 * Saves a new instance of the mod_mybrainchat into the database.
 *
 * Given an object containing all the necessary data, (defined by the form
 * in mod_form.php) this function will create a new instance and return the id
 * number of the instance.
 *
 * @param object $moduleinstance An object from the form.
 * @param mod_mybrainchat_mod_form $mform The form.
 * @return int The id of the newly inserted record.
 */
function mybrainchat_add_instance($moduleinstance, $mform = null) {
    global $DB;

    $moduleinstance->timecreated = time();

    $id = $DB->insert_record('mybrainchat', $moduleinstance);

    return $id;
}

/**
 * Updates an instance of the mod_mybrainchat in the database.
 *
 * Given an object containing all the necessary data (defined in mod_form.php),
 * this function will update an existing instance with new data.
 *
 * @param object $moduleinstance An object from the form in mod_form.php.
 * @param mod_mybrainchat_mod_form $mform The form.
 * @return bool True if successful, false otherwise.
 */
function mybrainchat_update_instance($moduleinstance, $mform = null) {
    global $DB;

    $moduleinstance->timemodified = time();
    $moduleinstance->id = $moduleinstance->instance;

    return $DB->update_record('mybrainchat', $moduleinstance);
}

/**
 * Removes an instance of the mod_mybrainchat from the database.
 *
 * @param int $id Id of the module instance.
 * @return bool True if successful, false on failure.
 */
function mybrainchat_delete_instance($id) {
    global $DB;

    $exists = $DB->get_record('mybrainchat', ['id' => $id]);
    if (!$exists) {
        return false;
    }

    $DB->delete_records('mybrainchat', ['id' => $id]);

    return true;
}

/**
 * Add required CSS files before the HTML head is printed.
 *
 * This function is called before the standard HTML head is output.
 */
function mod_mybrainchat_before_standard_html_head() {
    global $PAGE, $COURSE;

    // Only add CSS on course pages
    if ($PAGE->context->contextlevel != CONTEXT_COURSE && $PAGE->context->contextlevel != CONTEXT_MODULE) {
        return;
    }

    // Get the course ID
    $courseid = ($PAGE->context->contextlevel == CONTEXT_COURSE) ? $PAGE->context->instanceid : $COURSE->id;
    
    // Skip if we're not in a real course (e.g., site home)
    if ($courseid <= 1) {
        return;
    }

    // Check if there are any mybrainchat instances in this course
    $modinfo = get_fast_modinfo($COURSE);
    $hasmybrainchat = false;

    if (isset($modinfo->instances['mybrainchat'])) {
        foreach ($modinfo->instances['mybrainchat'] as $cm) {
            if ($cm->uservisible) {
                $hasmybrainchat = true;
                break;
            }
        }
    }

    // Only add CSS if there are mybrainchat instances in this course
    if (!$hasmybrainchat) {
        return;
    }

    // Add the required CSS - this must be done before the head is printed
    $PAGE->requires->css(new moodle_url('/mod/mybrainchat/styles/popup-chat.css'));
}

/**
 * Inject the chat button into course pages.
 *
 * This function is called before the page footer is output.
 */
function mybrainchat_before_footer() {
    global $PAGE, $COURSE, $DB;

    // Only show the chat button on course pages
    if ($PAGE->context->contextlevel != CONTEXT_COURSE && $PAGE->context->contextlevel != CONTEXT_MODULE) {
        return;
    }

    // Don't show the button if we're in popup mode
    // Note: $PAGE->bodyclasses can be either an array or a string depending on the context
    // We need to check both types to avoid the error: "in_array(): Argument #2 ($haystack) must be of type array, string given"
    if ($PAGE->pagelayout === 'popup' || 
        (is_array($PAGE->bodyclasses) && in_array('mybrainchat-popup', $PAGE->bodyclasses)) || 
        (is_string($PAGE->bodyclasses) && strpos($PAGE->bodyclasses, 'mybrainchat-popup') !== false)) {
        return;
    }

    // Get the course ID
    $courseid = ($PAGE->context->contextlevel == CONTEXT_COURSE) ? $PAGE->context->instanceid : $COURSE->id;
    
    // Skip if we're not in a real course (e.g., site home)
    if ($courseid <= 1) {
        return;
    }

    // Check if there are any mybrainchat instances in this course
    $modinfo = get_fast_modinfo($COURSE);
    $hasmybrainchat = false;

    if (isset($modinfo->instances['mybrainchat'])) {
        foreach ($modinfo->instances['mybrainchat'] as $cm) {
            if ($cm->uservisible) {
                $hasmybrainchat = true;
                break;
            }
        }
    }

    // Only show the button if there are mybrainchat instances in this course
    if (!$hasmybrainchat) {
        return;
    }

    // Add the required JavaScript - this can be done before the footer
    $PAGE->requires->js(new moodle_url('/mod/mybrainchat/js/popup-chat.js'));

    // Output the chat button HTML
    echo '<div class="mybrainchat-button-container">';
    echo '<button id="mybrainchat-open-button" class="mybrainchat-open-button">';
    echo '<i class="fa fa-comments"></i> Frag Hugo';
    echo '</button>';
    echo '</div>';

    // Initialize the popup chat JavaScript
    echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            if (typeof MyBrainChatPopup !== "undefined" && typeof MyBrainChatPopup.init === "function") {
                MyBrainChatPopup.init(' . $courseid . ');
            }
        });
    </script>';
}
