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
 * Privacy API implementation for the Quivr Chat plugin.
 *
 * This plugin connects to an external Quivr API service to provide AI-powered
 * chat functionality. While user questions are sent to the external service,
 * no personally identifiable information (PII) is transmitted or stored.
 *
 * @package     mod_quivrchat
 * @category    privacy
 * @copyright   2024 Sebastian Schluricke <schluricke@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_quivrchat\privacy;

use core_privacy\local\metadata\collection;

/**
 * Privacy provider for mod_quivrchat.
 *
 * This plugin sends user questions to an external Quivr API service but does not
 * transmit any personally identifiable information. The questions are processed
 * by the AI service and responses are returned to the user.
 *
 * Data flow:
 * - User questions (text only) are sent to the configured Quivr API endpoint
 * - A scoped authentication token (not linked to user identity) is used
 * - No usernames, email addresses, or other PII are transmitted
 * - Chat history is stored only in the user's PHP session (not in database)
 *
 * @package     mod_quivrchat
 * @copyright   2024 Sebastian Schluricke <schluricke@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    \core_privacy\local\metadata\provider {

    /**
     * Returns metadata about this plugin's data practices.
     *
     * @param collection $collection The initialised collection to add items to.
     * @return collection A listing of user data stored through this system.
     */
    public static function get_metadata(collection $collection): collection {
        // Document the external system connection.
        // Note: Only the question text is sent, no PII is transmitted.
        $collection->add_external_location_link(
            'quivr_api',
            [
                'question' => 'privacy:metadata:quivr_api:question',
            ],
            'privacy:metadata:quivr_api'
        );

        // Document user preferences (API key stored per teacher).
        $collection->add_user_preference(
            'mod_quivrchat_apikey',
            'privacy:metadata:preference:apikey'
        );

        return $collection;
    }
}
