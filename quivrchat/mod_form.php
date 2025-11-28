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
 * The main mod_quivrchat configuration form.
 *
 * @package     mod_quivrchat
 * @copyright   2024 ESFL
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

/**
 * Module instance settings form.
 *
 * @package     mod_quivrchat
 * @copyright   2024 ESFL
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_quivrchat_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $CFG, $USER, $PAGE;

        $mform = $this->_form;

        // Adding the "general" fieldset, where all the common settings are shown.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field.
        $mform->addElement('text', 'name', get_string('quivrchatname', 'mod_quivrchat'), ['size' => '64']);

        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }

        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('name', 'quivrchatname', 'mod_quivrchat');

        // Adding the standard "intro" and "introformat" fields.
        if ($CFG->branch >= 29) {
            $this->standard_intro_elements();
        } else {
            $this->add_intro_editor();
        }

        // Adding the rest of mod_quivrchat settings, spreading all them into this fieldset
        $mform->addElement('static', 'label1', 'quivrchatsettings', get_string('quivrchatsettings', 'mod_quivrchat'));
        $mform->addElement('header', 'quivrchatfieldset', get_string('quivrchatfieldset', 'mod_quivrchat'));

        // Check if user has a saved API key
        $saved_apikey = get_user_preferences('quivrchat_apikey', '', $USER->id);
        $has_saved_key = !empty($saved_apikey);

        // API Key field
        if ($has_saved_key) {
            // Show info that API key is saved
            $mform->addElement('static', 'apikey_info', '',
                '<div class="alert alert-info">' .
                get_string('apikey_from_profile_desc', 'mod_quivrchat') .
                '</div>'
            );
        }

        // API Key (password field) - optional if user has saved key
        $mform->addElement('passwordunmask', 'apikey', get_string('apikey', 'mod_quivrchat'));
        $mform->setType('apikey', PARAM_RAW);

        // Only require API key if no saved key exists
        if (!$has_saved_key) {
            $mform->addRule('apikey', null, 'required', null, 'client');
        }

        $mform->addHelpButton('apikey', 'apikey', 'mod_quivrchat');

        // Button to load brains
        $mform->addElement('button', 'loadbrains', get_string('loadbrains', 'mod_quivrchat'), [
            'id' => 'id_loadbrains'
        ]);

        // Hidden field for actual brainid value (bypasses Moodle's select validation)
        $mform->addElement('hidden', 'brainid', '');
        $mform->setType('brainid', PARAM_RAW);

        // Visual select for brain selection (not submitted directly)
        $brainoptions = ['' => get_string('selectbrain', 'mod_quivrchat')];

        // Try to load brains if we have an API key
        if ($has_saved_key) {
            $brains = $this->fetch_brains($saved_apikey);
            if (!empty($brains)) {
                foreach ($brains as $brain) {
                    $brainoptions[$brain['id']] = $brain['name'];
                }
            }
        }

        $mform->addElement('select', 'brainid_select', get_string('brainid', 'mod_quivrchat'), $brainoptions, [
            'id' => 'id_brainid_select'
        ]);
        $mform->addHelpButton('brainid_select', 'brainid', 'mod_quivrchat');

        // Status message area
        $mform->addElement('static', 'brains_status', '', '<div id="brains_status"></div>');

        // Popup settings header
        $mform->addElement('header', 'popupsettings', get_string('popupsettings', 'mod_quivrchat'));

        // Use for popup checkbox
        $mform->addElement('advcheckbox', 'use_for_popup', get_string('use_for_popup', 'mod_quivrchat'),
            get_string('use_for_popup_desc', 'mod_quivrchat'), [], [0, 1]);
        $mform->setDefault('use_for_popup', 0);
        $mform->addHelpButton('use_for_popup', 'use_for_popup', 'mod_quivrchat');

        // Add standard elements.
        $this->standard_coursemodule_elements();

        // Add standard buttons.
        $this->add_action_buttons();

        // Add inline JavaScript for loading brains
        $PAGE->requires->js_amd_inline("
            require(['jquery'], function(\$) {
                var wwwroot = M.cfg.wwwroot;
                var brainSelect = \$('#id_brainid_select');
                var brainHidden = \$('#id_brainid');

                // Sync select to hidden field on change
                brainSelect.on('change', function() {
                    brainHidden.val(\$(this).val());
                });

                // Initialize hidden field from select (in case of pre-selected value)
                if (brainSelect.val()) {
                    brainHidden.val(brainSelect.val());
                }

                // Also sync before form submit to ensure value is captured
                \$('form').on('submit', function() {
                    brainHidden.val(brainSelect.val());
                });

                \$('#id_loadbrains').on('click', function() {
                    var apikey = \$('#id_apikey').val();
                    var statusDiv = \$('#brains_status');
                    var currentBrain = brainHidden.val() || brainSelect.val();

                    statusDiv.html('<div class=\"alert alert-info\">" . get_string('loadingbrains', 'mod_quivrchat') . "</div>');

                    \$.ajax({
                        url: wwwroot + '/mod/quivrchat/api/get_brains.php',
                        method: 'GET',
                        data: { apikey: apikey },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success && response.brains.length > 0) {
                                brainSelect.empty();
                                brainSelect.append('<option value=\"\">" . get_string('selectbrain', 'mod_quivrchat') . "</option>');

                                response.brains.forEach(function(brain) {
                                    var selected = (brain.id === currentBrain) ? 'selected' : '';
                                    brainSelect.append('<option value=\"' + brain.id + '\" ' + selected + '>' + brain.name + '</option>');
                                });

                                // If we had a current brain, re-select it and sync to hidden field
                                if (currentBrain) {
                                    brainSelect.val(currentBrain);
                                }
                                // Always sync to hidden field after loading
                                brainHidden.val(brainSelect.val());

                                statusDiv.html('<div class=\"alert alert-success\">" . get_string('brainsloaded', 'mod_quivrchat') . " (' + response.brains.length + ')</div>');
                            } else {
                                statusDiv.html('<div class=\"alert alert-warning\">' + (response.error || '" . get_string('nobrainsfound', 'mod_quivrchat') . "') + '</div>');
                            }
                        },
                        error: function(xhr, status, error) {
                            statusDiv.html('<div class=\"alert alert-danger\">" . get_string('errorloadingbrains', 'mod_quivrchat') . ": ' + error + '</div>');
                        }
                    });
                });

                // Auto-load brains when page opens
                setTimeout(function() {
                    \$('#id_loadbrains').trigger('click');
                }, 500);
            });
        ");
    }

    /**
     * Fetch brains from Quivr API
     *
     * @param string $apikey The API key to use
     * @return array List of brains
     */
    private function fetch_brains($apikey) {
        if (empty($apikey)) {
            return [];
        }

        // Get Quivr API URL
        // Use host.docker.internal for Docker environments to reach the host machine
        $api_url = get_config('mod_quivrchat', 'quivr_api_url');
        if (empty($api_url)) {
            $api_url = getenv('QUIVR_API_URL') ?: 'http://host.docker.internal:5050';
        }

        // Fetch brains
        $ch = curl_init("$api_url/brains/");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $apikey,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpcode !== 200) {
            return [];
        }

        $data = json_decode($response, true);
        $brains = [];

        // API returns {"brains": [...]} so we need to access the brains key
        $brainsList = $data['brains'] ?? $data;
        if (is_array($brainsList)) {
            foreach ($brainsList as $brain) {
                // Skip model entries (brain_type = "model")
                if (isset($brain['brain_type']) && $brain['brain_type'] === 'model') {
                    continue;
                }
                $brains[] = [
                    'id' => (string)($brain['id'] ?? $brain['brain_id'] ?? ''),
                    'name' => $brain['name'] ?? 'Unnamed Brain'
                ];
            }
        }

        return $brains;
    }

    /**
     * Pre-process form data before setting defaults
     *
     * @param array $defaultvalues
     */
    public function data_preprocessing(&$defaultvalues) {
        global $USER;

        parent::data_preprocessing($defaultvalues);

        // Copy brainid to brainid_select for display
        if (!empty($defaultvalues['brainid'])) {
            $defaultvalues['brainid_select'] = $defaultvalues['brainid'];
        }
    }

    /**
     * Validate form data
     *
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {
        global $USER;

        $errors = parent::validation($data, $files);

        // Check if API key is provided or saved
        $saved_apikey = get_user_preferences('quivrchat_apikey', '', $USER->id);

        if (empty($data['apikey']) && empty($saved_apikey)) {
            $errors['apikey'] = get_string('required');
        }

        // Check if brain is selected
        if (empty($data['brainid'])) {
            $errors['brainid'] = get_string('required');
        }

        return $errors;
    }
}
