<?php
namespace mod_quivrchat\event;

defined('MOODLE_INTERNAL') || die();

/**
 * Event for when the activity is viewed.
 */
class course_module_viewed extends \core\event\course_module_viewed {

    protected function init(): void {
        $this->data['objecttable'] = 'quivrchat'; // entspricht Tabellenname in install.xml
        $this->data['crud'] = 'r'; // read
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
    }
}
