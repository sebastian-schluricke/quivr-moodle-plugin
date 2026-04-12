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

namespace mod_quivrchat\external;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;

/**
 * External service to get the primary quivrchat instance for a course (for popup).
 *
 * @package     mod_quivrchat
 * @copyright   2024 Sebastian Schluricke <schluricke@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_instance extends external_api {
    /**
     * Describes the parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course ID'),
        ]);
    }

    /**
     * Get the primary quivrchat instance for a course.
     *
     * @param int $courseid Course ID.
     * @return array
     */
    public static function execute(int $courseid): array {
        global $DB;

        $params = self::validate_parameters(self::execute_parameters(), ['courseid' => $courseid]);
        $courseid = $params['courseid'];

        $course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
        $context = \context_course::instance($courseid);
        self::validate_context($context);

        $modinfo = get_fast_modinfo($course);
        $instances = [];
        $primaryinstance = null;

        if (isset($modinfo->instances['quivrchat'])) {
            foreach ($modinfo->instances['quivrchat'] as $cm) {
                if ($cm->uservisible) {
                    $instancedata = $DB->get_record('quivrchat', ['id' => $cm->instance]);
                    $isprimary = $instancedata && !empty($instancedata->use_for_popup);
                    $instance = [
                        'cmid' => $cm->id,
                        'name' => $cm->name,
                        'is_primary' => $isprimary,
                    ];
                    $instances[] = $instance;
                    if ($isprimary) {
                        $primaryinstance = $instance;
                    }
                }
            }
        }

        if ($primaryinstance) {
            return ['success' => true, 'cmid' => $primaryinstance['cmid'],
                    'name' => $primaryinstance['name'], 'is_primary' => true];
        } else if (!empty($instances)) {
            return ['success' => true, 'cmid' => $instances[0]['cmid'],
                    'name' => $instances[0]['name'], 'is_primary' => false];
        }
        return ['success' => false, 'cmid' => 0, 'name' => '', 'is_primary' => false];
    }

    /**
     * Describes the return value.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'Whether an instance was found'),
            'cmid' => new external_value(PARAM_INT, 'Course module ID'),
            'name' => new external_value(PARAM_RAW, 'Instance name'),
            'is_primary' => new external_value(PARAM_BOOL, 'Whether this is the primary popup instance'),
        ]);
    }
}
