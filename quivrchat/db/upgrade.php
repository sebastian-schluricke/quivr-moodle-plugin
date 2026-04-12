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
 * Plugin upgrade steps are defined here.
 *
 * @package     mod_quivrchat
 * @copyright   2024 Sebastian Schluricke <schluricke@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Execute mod_quivrchat upgrade from the given old version.
 *
 * @param int $oldversion The old version number.
 * @return bool True on success.
 */
function xmldb_quivrchat_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    // Add use_for_popup field.
    if ($oldversion < 2025112802) {
        // Define field use_for_popup to be added to quivrchat.
        $table = new xmldb_table('quivrchat');
        $field = new xmldb_field('use_for_popup', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'apikey');

        // Conditionally launch add field use_for_popup.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Quivrchat savepoint reached.
        upgrade_mod_savepoint(true, 2025112802, 'quivrchat');
    }

    // Add custom_instructions field.
    if ($oldversion < 2026020200) {
        // Define field custom_instructions to be added to quivrchat.
        $table = new xmldb_table('quivrchat');
        $field = new xmldb_field('custom_instructions', XMLDB_TYPE_TEXT, null, null, null, null, null, 'use_for_popup');

        // Conditionally launch add field custom_instructions.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Quivrchat savepoint reached.
        upgrade_mod_savepoint(true, 2026020200, 'quivrchat');
    }

    return true;
}
