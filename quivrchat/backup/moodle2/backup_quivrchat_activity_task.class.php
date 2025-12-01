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
 * Backup task for mod_quivrchat.
 *
 * @package     mod_quivrchat
 * @category    backup
 * @copyright   2024 Sebastian Schluricke <schluricke@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/quivrchat/backup/moodle2/backup_quivrchat_stepslib.php');

/**
 * Provides the steps to perform one complete backup of the quivrchat instance.
 */
class backup_quivrchat_activity_task extends backup_activity_task {
    /**
     * No specific settings for this activity.
     */
    protected function define_my_settings() {
        // No particular settings for this activity.
    }

    /**
     * Defines a backup step to store the instance data in the quivrchat.xml file.
     */
    protected function define_my_steps() {
        $this->add_step(new backup_quivrchat_activity_structure_step('quivrchat_structure', 'quivrchat.xml'));
    }

    /**
     * Encodes URLs to the index.php and view.php scripts.
     *
     * @param string $content Some HTML text that eventually contains URLs to the activity instance scripts.
     * @return string The content with the URLs encoded.
     */
    public static function encode_content_links($content) {
        global $CFG;

        $base = preg_quote($CFG->wwwroot, '/');

        // Link to the list of quivrchat instances.
        $search = '/(' . $base . '\/mod\/quivrchat\/index\.php\?id=)([0-9]+)/';
        $content = preg_replace($search, '$@QUIVRCHATINDEX*$2@$', $content);

        // Link to quivrchat view by module id.
        $search = '/(' . $base . '\/mod\/quivrchat\/view\.php\?id=)([0-9]+)/';
        $content = preg_replace($search, '$@QUIVRCHATVIEWBYID*$2@$', $content);

        return $content;
    }
}
