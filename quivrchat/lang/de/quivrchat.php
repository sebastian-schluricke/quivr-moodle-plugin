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
 * German language strings for mod_quivrchat.
 *
 * @package     mod_quivrchat
 * @category    string
 * @copyright   2024 Sebastian Schluricke <schluricke@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Allgemein.
$string['pluginname'] = 'Quivr Chat';
$string['modulename'] = 'Quivr Chat';
$string['modulenameplural'] = 'Quivr Chats';
$string['modulename_help'] = 'Das Quivr Chat Modul ermöglicht es Schüler:innen, mit einem KI-gestützten Wissens-Brain zu interagieren. Lehrkräfte können die Brain-Verbindung konfigurieren und Schüler:innen können Fragen stellen, um KI-generierte Antworten basierend auf der Wissensbasis des Brains zu erhalten.';
$string['quivrchatname'] = 'Chat-Titel';
$string['quivrchatname_help'] = 'Der Name dieser Chat-Aktivität.';
$string['pluginadministration'] = 'Quivr Chat Verwaltung';
$string['view'] = 'Chat anzeigen';
$string['quivrchat:view'] = 'Quivr Chat Aktivität anzeigen';
$string['quivrchat:addinstance'] = 'Neue Quivr Chat Aktivität hinzufügen';
$string['noquivrchatinstances'] = 'Es gibt keine Quivr Chat Aktivitäten in diesem Kurs.';

// Einstellungen.
$string['quivr_api_url'] = 'Quivr API URL';
$string['quivr_api_url_desc'] = 'Die URL des Quivr Backend-Servers (z.B. http://localhost:5050).';

// Formularfelder.
$string['brainid'] = 'Brain ID';
$string['brainid_help'] = 'Die UUID des Quivr Brains, mit dem gechattet werden soll.';
$string['apikey'] = 'API-Schlüssel';
$string['apikey_help'] = 'Der API-Schlüssel für den Zugriff auf das Quivr Brain. Wird in Ihrem Profil gespeichert.';
$string['apikey_from_profile'] = 'API-Schlüssel aus Profil verwenden';
$string['apikey_from_profile_desc'] = 'Ihr gespeicherter API-Schlüssel wird verwendet. Lassen Sie das Feld leer, um den gespeicherten Schlüssel zu verwenden, oder geben Sie einen neuen ein.';
$string['quivrchatfieldset'] = 'Brain-Einstellungen';
$string['quivrchatsettings'] = 'Konfigurieren Sie die Verbindung zum Quivr Brain.';

// Brain-Auswahl.
$string['loadbrains'] = 'Brains laden';
$string['selectbrain'] = '-- Brain auswählen --';
$string['loadingbrains'] = 'Brains werden geladen...';
$string['brainsloaded'] = 'Brains erfolgreich geladen';
$string['nobrainsfound'] = 'Keine Brains gefunden. Bitte prüfen Sie Ihren API-Schlüssel.';
$string['errorloadingbrains'] = 'Fehler beim Laden der Brains';

// Popup-Einstellungen.
$string['popupsettings'] = 'Popup-Einstellungen';
$string['use_for_popup'] = 'Für Kurs-Popup verwenden';
$string['use_for_popup_desc'] = 'Diese Aktivität für den kursweiten Popup-Chat-Button verwenden';
$string['use_for_popup_help'] = 'Wenn aktiviert, wird diese Aktivität verwendet, wenn Schüler:innen auf den "Quivr Chat" Popup-Button auf Kursseiten klicken. Nur eine Aktivität pro Kurs kann für das Popup verwendet werden - die Aktivierung deaktiviert es automatisch bei anderen Aktivitäten im selben Kurs.';

// Chat UI Strings.
$string['connecting'] = 'Verbindung zum Brain wird hergestellt...';
$string['newchat'] = 'Neuer Chat';
$string['newchat_title'] = 'Neuen Chat starten';
$string['inputplaceholder'] = 'Was möchtest du wissen?';
$string['chat_restored'] = 'Chat wird fortgesetzt. Stelle eine Frage an Quivr Chat.';
$string['chat_welcome'] = 'Willkommen! Stelle eine Frage an Quivr Chat.';
$string['chat_new_started'] = 'Neuer Chat gestartet. Stelle eine Frage an Quivr Chat.';
$string['error_prefix'] = 'Fehler: ';
$string['error_unexpected'] = 'Unerwartete Antwort vom Server.';
$string['followup_questions'] = 'Weitere Fragen:';
$string['feedback_not_helpful'] = 'Antwort ist nicht hilfreich!';
$string['send'] = 'Senden';
$string['avatar_alt'] = 'Chat-Avatar';

// Privacy API.
$string['privacy:metadata'] = 'Das Quivr Chat Plugin sendet Benutzerfragen an einen externen Quivr API-Dienst für KI-gestützte Antworten. Es werden keine personenbezogenen Daten übertragen.';
$string['privacy:metadata:quivr_api'] = 'Benutzerfragen werden an den konfigurierten Quivr API-Dienst gesendet, um KI-gestützte Antworten zu generieren. Es wird nur der Fragetext übertragen; keine Benutzernamen, E-Mail-Adressen oder andere personenbezogene Daten werden gesendet.';
$string['privacy:metadata:quivr_api:question'] = 'Der vom Benutzer eingegebene Fragetext wird an die Quivr API gesendet, um eine Antwort zu generieren.';
$string['privacy:metadata:preference:apikey'] = 'Lehrkräfte können ihren Quivr API-Schlüssel in ihren Benutzereinstellungen speichern, um bei der Erstellung von Chat-Aktivitäten bequem darauf zugreifen zu können.';
