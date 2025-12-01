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
 * @package     mod_quivrchat
 * @copyright   2024 Sebastian Schluricke <schluricke@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Return if the plugin supports $feature.
 *
 * @param string $feature Constant representing the feature.
 * @return mixed True if the feature is supported, null otherwise.
 */
function quivrchat_supports($feature) {
    switch ($feature) {
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        case FEATURE_MOD_PURPOSE:
            return MOD_PURPOSE_COMMUNICATION;
        default:
            return null;
    }
}

/**
 * Saves a new instance of the mod_quivrchat into the database.
 *
 * Given an object containing all the necessary data, (defined by the form
 * in mod_form.php) this function will create a new instance and return the id
 * number of the instance.
 *
 * @param object $moduleinstance An object from the form.
 * @param mod_quivrchat_mod_form $mform The form.
 * @return int The id of the newly inserted record.
 */
function quivrchat_add_instance($moduleinstance, $mform = null) {
    global $DB, $USER;

    $moduleinstance->timecreated = time();

    // Handle API key: save to user preferences and use saved key if empty
    if (!empty($moduleinstance->apikey)) {
        // Save new API key to user preferences
        set_user_preference('quivrchat_apikey', $moduleinstance->apikey, $USER->id);
    } else {
        // Use saved API key from user preferences
        $saved_apikey = get_user_preferences('quivrchat_apikey', '', $USER->id);
        if (!empty($saved_apikey)) {
            $moduleinstance->apikey = $saved_apikey;
        }
    }

    $id = $DB->insert_record('quivrchat', $moduleinstance);

    // If this instance is set as the popup instance, deactivate all others in the same course
    if (!empty($moduleinstance->use_for_popup)) {
        $DB->set_field_select('quivrchat', 'use_for_popup', 0,
            'course = ? AND id != ?', [$moduleinstance->course, $id]);
    }

    return $id;
}

/**
 * Updates an instance of the mod_quivrchat in the database.
 *
 * Given an object containing all the necessary data (defined in mod_form.php),
 * this function will update an existing instance with new data.
 *
 * @param object $moduleinstance An object from the form in mod_form.php.
 * @param mod_quivrchat_mod_form $mform The form.
 * @return bool True if successful, false otherwise.
 */
function quivrchat_update_instance($moduleinstance, $mform = null) {
    global $DB, $USER;

    $moduleinstance->timemodified = time();
    $moduleinstance->id = $moduleinstance->instance;

    // Handle API key: save to user preferences and use saved key if empty
    if (!empty($moduleinstance->apikey)) {
        // Save new API key to user preferences
        set_user_preference('quivrchat_apikey', $moduleinstance->apikey, $USER->id);
    } else {
        // Use saved API key from user preferences
        $saved_apikey = get_user_preferences('quivrchat_apikey', '', $USER->id);
        if (!empty($saved_apikey)) {
            $moduleinstance->apikey = $saved_apikey;
        } else {
            // Keep the existing API key from database
            $existing = $DB->get_record('quivrchat', ['id' => $moduleinstance->id]);
            if ($existing && !empty($existing->apikey)) {
                $moduleinstance->apikey = $existing->apikey;
            }
        }
    }

    // If this instance is set as the popup instance, deactivate all others in the same course
    if (!empty($moduleinstance->use_for_popup)) {
        $DB->set_field_select('quivrchat', 'use_for_popup', 0,
            'course = ? AND id != ?', [$moduleinstance->course, $moduleinstance->id]);
    }

    return $DB->update_record('quivrchat', $moduleinstance);
}

/**
 * Removes an instance of the mod_quivrchat from the database.
 *
 * @param int $id Id of the module instance.
 * @return bool True if successful, false on failure.
 */
function quivrchat_delete_instance($id) {
    global $DB;

    $exists = $DB->get_record('quivrchat', ['id' => $id]);
    if (!$exists) {
        return false;
    }

    $DB->delete_records('quivrchat', ['id' => $id]);

    return true;
}

/**
 * Add required CSS files before the HTML head is printed.
 *
 * This function is called before the standard HTML head is output.
 */
function mod_quivrchat_before_standard_html_head() {
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

    // Check if there are any quivrchat instances in this course
    $modinfo = get_fast_modinfo($COURSE);
    $hasquivrchat = false;

    if (isset($modinfo->instances['quivrchat'])) {
        foreach ($modinfo->instances['quivrchat'] as $cm) {
            if ($cm->uservisible) {
                $hasquivrchat = true;
                break;
            }
        }
    }

    // Only add CSS if there are quivrchat instances in this course
    if (!$hasquivrchat) {
        return;
    }

    // Add the required CSS - this must be done before the head is printed
    $PAGE->requires->css(new moodle_url('/mod/quivrchat/styles/popup-chat.css'));
}

/**
 * Inject the chat button into course pages.
 *
 * This function is called before the page footer is output.
 */
function quivrchat_before_footer() {
    global $PAGE, $COURSE, $DB;

    // Only show the chat button on course pages
    if ($PAGE->context->contextlevel != CONTEXT_COURSE && $PAGE->context->contextlevel != CONTEXT_MODULE) {
        return;
    }

    // Don't show the button if we're in popup mode
    // Note: $PAGE->bodyclasses can be either an array or a string depending on the context
    // We need to check both types to avoid the error: "in_array(): Argument #2 ($haystack) must be of type array, string given"
    if ($PAGE->pagelayout === 'popup' || 
        (is_array($PAGE->bodyclasses) && in_array('quivrchat-popup', $PAGE->bodyclasses)) || 
        (is_string($PAGE->bodyclasses) && strpos($PAGE->bodyclasses, 'quivrchat-popup') !== false)) {
        return;
    }

    // Get the course ID
    $courseid = ($PAGE->context->contextlevel == CONTEXT_COURSE) ? $PAGE->context->instanceid : $COURSE->id;
    
    // Skip if we're not in a real course (e.g., site home)
    if ($courseid <= 1) {
        return;
    }

    // Check if there are any quivrchat instances in this course
    $modinfo = get_fast_modinfo($COURSE);
    $hasquivrchat = false;

    if (isset($modinfo->instances['quivrchat'])) {
        foreach ($modinfo->instances['quivrchat'] as $cm) {
            if ($cm->uservisible) {
                $hasquivrchat = true;
                break;
            }
        }
    }

    // Only show the button if there are quivrchat instances in this course
    if (!$hasquivrchat) {
        return;
    }

    // Add the required JavaScript - this can be done before the footer
    $PAGE->requires->js(new moodle_url('/mod/quivrchat/js/popup-chat.js'));

    // Output the chat button HTML
    echo '<div class="quivrchat-button-container">';
    echo '<button id="quivrchat-open-button" class="quivrchat-open-button">';
    echo '<i class="fa fa-comments"></i> Quivr Chat';
    echo '</button>';
    echo '</div>';

    // Initialize the popup chat JavaScript
    echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            if (typeof QuivrChatPopup !== "undefined" && typeof QuivrChatPopup.init === "function") {
                QuivrChatPopup.init(' . $courseid . ');
            }
        });
    </script>';
}
