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
 * Restore steps for mod_quivrchat.
 *
 * @package     mod_quivrchat
 * @category    backup
 * @copyright   2024 Sebastian Schluricke <schluricke@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Structure step to restore one quivrchat activity.
 */
class restore_quivrchat_activity_structure_step extends restore_activity_structure_step {

    /**
     * Defines the structure to be restored.
     *
     * @return restore_path_element[]
     */
    protected function define_structure() {
        $paths = [];
        $paths[] = new restore_path_element('quivrchat', '/activity/quivrchat');

        // Return the paths wrapped into standard activity structure.
        return $this->prepare_activity_structure($paths);
    }

    /**
     * Process the quivrchat element.
     *
     * @param array $data The data from the backup file.
     */
    protected function process_quivrchat($data) {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        // Insert the quivrchat record.
        $newitemid = $DB->insert_record('quivrchat', $data);

        // Immediately after inserting "activity" record, call this.
        $this->apply_activity_instance($newitemid);
    }

    /**
     * Post-execution actions.
     */
    protected function after_execute() {
        // Add quivrchat related files, no need to match by itemname (just internally handled context).
        $this->add_related_files('mod_quivrchat', 'intro', null);
    }
}
