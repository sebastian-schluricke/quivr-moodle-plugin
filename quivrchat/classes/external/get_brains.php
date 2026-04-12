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
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;

/**
 * External service to fetch available brains from the Quivr backend.
 *
 * @package     mod_quivrchat
 * @copyright   2024 Sebastian Schluricke <schluricke@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_brains extends external_api {
    /**
     * Describes the parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'apikey' => new external_value(PARAM_RAW, 'API key for Quivr', VALUE_DEFAULT, ''),
        ]);
    }

    /**
     * Fetch brains from the Quivr API.
     *
     * @param string $apikey The API key.
     * @return array
     */
    public static function execute(string $apikey = ''): array {
        global $USER;

        $params = self::validate_parameters(self::execute_parameters(), ['apikey' => $apikey]);
        $apikey = $params['apikey'];

        $context = \context_system::instance();
        self::validate_context($context);

        if (empty($apikey)) {
            $apikey = get_user_preferences('quivrchat_apikey', '', $USER->id);
        }

        if (empty($apikey)) {
            return ['success' => false, 'brains' => []];
        }

        $apiurl = get_config('mod_quivrchat', 'quivr_api_url');
        if (empty($apiurl)) {
            $apiurl = getenv('QUIVR_API_URL') ?: 'http://host.docker.internal:5050';
        }

        $curl = new \curl();
        $curl->setHeader([
            'Authorization: Bearer ' . $apikey,
            'Content-Type: application/json',
        ]);

        $response = $curl->get("$apiurl/brains/");
        $httpcode = $curl->get_info()['http_code'] ?? 0;

        if ($httpcode != 200) {
            return ['success' => false, 'brains' => []];
        }

        $data = json_decode($response, true);
        $brains = [];
        $brainslist = $data['brains'] ?? $data;
        if (is_array($brainslist)) {
            foreach ($brainslist as $brain) {
                if (isset($brain['brain_type']) && $brain['brain_type'] === 'model') {
                    continue;
                }
                $brains[] = [
                    'id' => (string)($brain['id'] ?? $brain['brain_id'] ?? ''),
                    'name' => $brain['name'] ?? 'Unnamed Brain',
                    'description' => $brain['description'] ?? '',
                ];
            }
        }

        return ['success' => true, 'brains' => $brains];
    }

    /**
     * Describes the return value.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'Whether the request succeeded'),
            'brains' => new external_multiple_structure(
                new external_single_structure([
                    'id' => new external_value(PARAM_RAW, 'Brain ID'),
                    'name' => new external_value(PARAM_RAW, 'Brain name'),
                    'description' => new external_value(PARAM_RAW, 'Brain description'),
                ])
            ),
        ]);
    }
}
