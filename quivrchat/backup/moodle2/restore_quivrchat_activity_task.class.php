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
 * Restore task for mod_quivrchat.
 *
 * @package     mod_quivrchat
 * @category    backup
 * @copyright   2024 Sebastian Schluricke <schluricke@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/quivrchat/backup/moodle2/restore_quivrchat_stepslib.php');

/**
 * Restore task for the quivrchat activity module.
 */
class restore_quivrchat_activity_task extends restore_activity_task {
    /**
     * Define (add) particular settings this activity can have.
     */
    protected function define_my_settings() {
        // No particular settings for this activity.
    }

    /**
     * Define (add) particular steps this activity can have.
     */
    protected function define_my_steps() {
        // Quivrchat only has one structure step.
        $this->add_step(new restore_quivrchat_activity_structure_step('quivrchat_structure', 'quivrchat.xml'));
    }

    /**
     * Define the contents in the activity that must be processed by the link decoder.
     *
     * @return restore_decode_content[] Array of restore decode content objects.
     */
    public static function define_decode_contents() {
        $contents = [];

        $contents[] = new restore_decode_content('quivrchat', ['intro'], 'quivrchat');

        return $contents;
    }

    /**
     * Define the decoding rules for links belonging to the activity to be executed by the link decoder.
     *
     * @return restore_decode_rule[] Array of restore decode rule objects.
     */
    public static function define_decode_rules() {
        $rules = [];

        $rules[] = new restore_decode_rule('QUIVRCHATVIEWBYID', '/mod/quivrchat/view.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('QUIVRCHATINDEX', '/mod/quivrchat/index.php?id=$1', 'course');

        return $rules;
    }

    /**
     * Define the restore log rules that will be applied when restoring quivrchat logs.
     *
     * @return restore_log_rule[] Array of restore log rule objects.
     */
    public static function define_restore_log_rules() {
        $rules = [];

        $rules[] = new restore_log_rule('quivrchat', 'add', 'view.php?id={course_module}', '{quivrchat}');
        $rules[] = new restore_log_rule('quivrchat', 'update', 'view.php?id={course_module}', '{quivrchat}');
        $rules[] = new restore_log_rule('quivrchat', 'view', 'view.php?id={course_module}', '{quivrchat}');

        return $rules;
    }

    /**
     * Define the restore log rules that will be applied when restoring course logs.
     *
     * @return restore_log_rule[] Array of restore log rule objects.
     */
    public static function define_restore_log_rules_for_course() {
        $rules = [];

        $rules[] = new restore_log_rule('quivrchat', 'view all', 'index.php?id={course}', null);

        return $rules;
    }
}
