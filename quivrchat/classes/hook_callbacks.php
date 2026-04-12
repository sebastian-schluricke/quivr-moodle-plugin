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

namespace mod_quivrchat;

use core\hook\output\before_standard_head_html_generation;
use core\hook\output\before_footer_html_generation;

/**
 * Hook callbacks for mod_quivrchat.
 *
 * Replaces the legacy lib.php callbacks:
 * - mod_quivrchat_before_standard_html_head() → before_head_generation()
 * - quivrchat_before_footer() → before_footer_generation()
 *
 * @package     mod_quivrchat
 * @copyright   2024 Sebastian Schluricke <schluricke@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class hook_callbacks {
    /**
     * Add popup chat CSS on course pages that have quivrchat instances.
     *
     * @param before_standard_head_html_generation $hook
     */
    public static function before_head_generation(before_standard_head_html_generation $hook): void {
        global $PAGE, $COURSE;

        // Only add CSS on course pages.
        if ($PAGE->context->contextlevel != CONTEXT_COURSE && $PAGE->context->contextlevel != CONTEXT_MODULE) {
            return;
        }

        // Get the course ID.
        $courseid = ($PAGE->context->contextlevel == CONTEXT_COURSE) ? $PAGE->context->instanceid : $COURSE->id;

        // Skip if we're not in a real course (e.g., site home).
        if ($courseid <= 1) {
            return;
        }

        // Check if there are any quivrchat instances in this course.
        $modinfo = get_fast_modinfo($COURSE);
        if (!isset($modinfo->instances['quivrchat'])) {
            return;
        }

        $hasquivrchat = false;
        foreach ($modinfo->instances['quivrchat'] as $cm) {
            if ($cm->uservisible) {
                $hasquivrchat = true;
                break;
            }
        }

        if (!$hasquivrchat) {
            return;
        }

        // Add the popup chat CSS via $PAGE (side-effect, no HTML to add).
        $PAGE->requires->css(new \moodle_url('/mod/quivrchat/styles/popup-chat.css'));
    }

    /**
     * Inject the chat button into course pages that have quivrchat instances.
     *
     * @param before_footer_html_generation $hook
     */
    public static function before_footer_generation(before_footer_html_generation $hook): void {
        global $PAGE, $COURSE;

        // Only show the chat button on course pages.
        if ($PAGE->context->contextlevel != CONTEXT_COURSE && $PAGE->context->contextlevel != CONTEXT_MODULE) {
            return;
        }

        // Don't show the button if we're in popup mode.
        if (
            $PAGE->pagelayout === 'popup' ||
            (is_array($PAGE->bodyclasses) && in_array('quivrchat-popup', $PAGE->bodyclasses)) ||
            (is_string($PAGE->bodyclasses) && strpos($PAGE->bodyclasses, 'quivrchat-popup') !== false)
        ) {
            return;
        }

        // Get the course ID.
        $courseid = ($PAGE->context->contextlevel == CONTEXT_COURSE) ? $PAGE->context->instanceid : $COURSE->id;

        // Skip if we're not in a real course (e.g., site home).
        if ($courseid <= 1) {
            return;
        }

        // Check if there are any quivrchat instances in this course.
        $modinfo = get_fast_modinfo($COURSE);
        if (!isset($modinfo->instances['quivrchat'])) {
            return;
        }

        $hasquivrchat = false;
        foreach ($modinfo->instances['quivrchat'] as $cm) {
            if ($cm->uservisible) {
                $hasquivrchat = true;
                break;
            }
        }

        if (!$hasquivrchat) {
            return;
        }

        // Build the chat button HTML (returned via hook, not echoed).
        $buttonlabel = get_string('popup_button_label', 'mod_quivrchat');

        $html = '<div class="quivrchat-button-container">';
        $html .= '<button id="quivrchat-open-button" class="quivrchat-open-button">';
        $html .= '<i class="fa fa-comments"></i> ' . $buttonlabel;
        $html .= '</button>';
        $html .= '</div>';

        $hook->add_html($html);

        // Initialize popup via AMD module (no inline script needed).
        $popupstrings = [
            'error_opening_chat' => get_string('error_opening_chat', 'mod_quivrchat'),
            'error_no_chat_available' => get_string('error_no_chat_available', 'mod_quivrchat'),
            'error_loading_chat' => get_string('error_loading_chat', 'mod_quivrchat'),
        ];
        $PAGE->requires->js_call_amd('mod_quivrchat/popup', 'init', [$courseid, $popupstrings]);
    }
}
