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
 * Backup steps for mod_quivrchat.
 *
 * @package     mod_quivrchat
 * @category    backup
 * @copyright   2024 Sebastian Schluricke <schluricke@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Define the complete quivrchat structure for backup.
 */
class backup_quivrchat_activity_structure_step extends backup_activity_structure_step {

    /**
     * Defines the structure of the quivrchat element inside the backup.xml file.
     *
     * @return backup_nested_element
     */
    protected function define_structure() {

        // Define each element separated.
        $quivrchat = new backup_nested_element('quivrchat', ['id'], [
            'name',
            'intro',
            'introformat',
            'brainid',
            'apikey',
            'use_for_popup',
            'timecreated',
            'timemodified',
        ]);

        // Define sources.
        $quivrchat->set_source_table('quivrchat', ['id' => backup::VAR_ACTIVITYID]);

        // Define annotations (none needed for this simple module).

        // Define file annotations.
        $quivrchat->annotate_files('mod_quivrchat', 'intro', null);

        // Return the root element (quivrchat), wrapped into standard activity structure.
        return $this->prepare_activity_structure($quivrchat);
    }
}
