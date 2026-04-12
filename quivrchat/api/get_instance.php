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
 * API endpoint to get a quivrchat instance for a course.
 *
 * @package     mod_quivrchat
 * @copyright   2024 Sebastian Schluricke <schluricke@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->dirroot . '/mod/quivrchat/lib.php');

// Parameters.
$courseid = required_param('courseid', PARAM_INT);

// Set up page.
$PAGE->set_context(context_system::instance());
$PAGE->set_url('/mod/quivrchat/api/get_instance.php', ['courseid' => $courseid]);

// Check if user is logged in.
require_login(null, false);

// Prepare response.
$response = [
    'success' => false,
    'cmid' => null,
    'message' => '',
];

// Check if course exists.
$course = $DB->get_record('course', ['id' => $courseid], '*', IGNORE_MISSING);
if (!$course) {
    $response['message'] = get_string('error_course_not_found', 'mod_quivrchat');
    echo json_encode($response);
    die();
}

// Check if user has access to the course.
$coursecontext = context_course::instance($courseid);
if (!has_capability('moodle/course:view', $coursecontext)) {
    $response['message'] = get_string('error_access_denied', 'mod_quivrchat');
    echo json_encode($response);
    die();
}

// Find all quivrchat instances in the course.
$modinfo = get_fast_modinfo($course);
$instances = [];
$primaryinstance = null;

if (isset($modinfo->instances['quivrchat'])) {
    foreach ($modinfo->instances['quivrchat'] as $cm) {
        if ($cm->uservisible) {
            // Get the instance data to check use_for_popup.
            $instancedata = $DB->get_record('quivrchat', ['id' => $cm->instance]);
            $isprimary = $instancedata && !empty($instancedata->use_for_popup);

            $instance = [
                'cmid' => $cm->id,
                'name' => $cm->name,
                'use_for_popup' => $isprimary,
            ];

            $instances[] = $instance;

            // Remember the primary instance.
            if ($isprimary) {
                $primaryinstance = $instance;
            }
        }
    }
}

// Prefer the primary instance (use_for_popup=1), otherwise return the first available.
if ($primaryinstance) {
    $response['success'] = true;
    $response['cmid'] = $primaryinstance['cmid'];
    $response['name'] = $primaryinstance['name'];
    $response['is_primary'] = true;
} else if (!empty($instances)) {
    $response['success'] = true;
    $response['cmid'] = $instances[0]['cmid'];
    $response['name'] = $instances[0]['name'];
    $response['is_primary'] = false;
} else {
    $response['message'] = get_string('error_no_instances_in_course', 'mod_quivrchat');
}

// Output JSON response.
header('Content-Type: application/json');
echo json_encode($response);
