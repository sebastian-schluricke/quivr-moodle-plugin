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
 * Plugin strings are defined here.
 *
 * @package     mod_quivrchat
 * @category    string
 * @copyright
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Quivr Chat';
$string['privacy:metadata'] = 'Quivr Chat speichert keine personenbezogenen Daten.';
$string['modulename'] = 'Quivr Chat';
$string['modulenameplural'] = 'Quivr Chats';
$string['modulename_help'] = 'Dieses Modul ermöglicht es Schüler:innen, mit einem KI-gestützten Wissens-Brain zu chatten.';
$string['quivrchatname'] = 'Chat-Titel';
$string['quivrchatname_help'] = 'Der Name dieser Chat-Aktivität.';
$string['pluginadministration'] = 'Quivr Chat Verwaltung';
$string['view'] = 'Chat anzeigen';
$string['quivrchat:view'] = 'Quivr Chat Aktivität anzeigen';
$string['quivrchat:addinstance'] = 'Neue Quivr Chat Aktivität hinzufügen';

// Settings
$string['quivr_api_url'] = 'Quivr API URL';
$string['quivr_api_url_desc'] = 'Die URL des Quivr Backend-Servers (z.B. http://localhost:5050).';

// Form fields
$string['brainid'] = 'Brain ID';
$string['brainid_help'] = 'Die UUID des Quivr Brains, mit dem gechattet werden soll.';
$string['apikey'] = 'API-Schlüssel';
$string['apikey_help'] = 'Der API-Schlüssel für den Zugriff auf das Quivr Brain. Wird in Ihrem Profil gespeichert.';
$string['apikey_from_profile'] = 'API-Schlüssel aus Profil verwenden';
$string['apikey_from_profile_desc'] = 'Ihr gespeicherter API-Schlüssel wird verwendet. Lassen Sie das Feld leer, um den gespeicherten Schlüssel zu verwenden, oder geben Sie einen neuen ein.';
$string['quivrchatfieldset'] = 'Brain-Einstellungen';
$string['quivrchatsettings'] = 'Konfigurieren Sie die Verbindung zum Quivr Brain.';

// Brain selection
$string['loadbrains'] = 'Brains laden';
$string['selectbrain'] = '-- Brain auswählen --';
$string['loadingbrains'] = 'Brains werden geladen...';
$string['brainsloaded'] = 'Brains erfolgreich geladen';
$string['nobrainsfound'] = 'Keine Brains gefunden. Bitte prüfen Sie Ihren API-Schlüssel.';
$string['errorloadingbrains'] = 'Fehler beim Laden der Brains';

// Popup settings
$string['popupsettings'] = 'Popup-Einstellungen';
$string['use_for_popup'] = 'Für Kurs-Popup verwenden';
$string['use_for_popup_desc'] = 'Diese Aktivität für den kursweiten Popup-Chat-Button verwenden';
$string['use_for_popup_help'] = 'Wenn aktiviert, wird diese Aktivität verwendet, wenn Schüler:innen auf den "Quivr Chat" Popup-Button auf Kursseiten klicken. Nur eine Aktivität pro Kurs kann für das Popup verwendet werden - die Aktivierung deaktiviert es automatisch bei anderen Aktivitäten im selben Kurs.';
