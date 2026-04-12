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
 * External service to obtain a scoped chat token from the Quivr backend.
 *
 * @package     mod_quivrchat
 * @copyright   2024 Sebastian Schluricke <schluricke@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_token extends external_api {
    /**
     * Describes the parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'cmid' => new external_value(PARAM_INT, 'Course module ID'),
        ]);
    }

    /**
     * Obtain a scoped chat token.
     *
     * @param int $cmid Course module ID.
     * @return array
     */
    public static function execute(int $cmid): array {
        global $DB;

        $params = self::validate_parameters(self::execute_parameters(), ['cmid' => $cmid]);
        $cmid = $params['cmid'];

        $cm = get_coursemodule_from_id('quivrchat', $cmid, 0, false, MUST_EXIST);
        $context = \context_module::instance($cm->id);
        self::validate_context($context);
        require_capability('mod/quivrchat:view', $context);

        $instance = $DB->get_record('quivrchat', ['id' => $cm->instance], '*', MUST_EXIST);

        $apiurl = get_config('mod_quivrchat', 'quivr_api_url');
        if (empty($apiurl)) {
            $apiurl = getenv('QUIVR_API_URL') ?: 'http://localhost:5050';
        }

        $curl = new \curl();
        $curl->setHeader([
            'Authorization: Bearer ' . $instance->apikey,
            'Content-Type: application/json',
        ]);

        $postdata = json_encode([
            'brain_id' => $instance->brainid,
            'ttl_minutes' => 10,
        ]);

        $response = $curl->post("$apiurl/chat/token", $postdata);
        $httpcode = $curl->get_info()['http_code'] ?? 0;
        $data = json_decode($response, true);

        if ($curl->get_errno() !== 0 || $httpcode != 200) {
            throw new \moodle_exception('error_obtain_token', 'mod_quivrchat');
        }

        return [
            'success' => true,
            'token' => $data['token'],
            'expires_at' => $data['expires_at'],
            'brain_id' => $data['brain_id'],
        ];
    }

    /**
     * Describes the return value.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'Whether the request succeeded'),
            'token' => new external_value(PARAM_RAW, 'The scoped chat token'),
            'expires_at' => new external_value(PARAM_RAW, 'Token expiry timestamp'),
            'brain_id' => new external_value(PARAM_RAW, 'Brain ID for the token'),
        ]);
    }
}
