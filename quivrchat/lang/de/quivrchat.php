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

// Instruktions-Einstellungen.
$string['instructionssettings'] = 'Instruktions-Einstellungen';
$string['custom_instructions'] = 'Eigene Instruktionen';
$string['custom_instructions_help'] = 'Eigene Instruktionen, die mit jeder Chat-Nachricht gesendet werden, um die KI-Antworten für diese Aktivität zu steuern. Diese Instruktionen überschreiben die Standardeinstellungen des Brains. Nutzen Sie dies, um das Verhalten der KI für verschiedene Anwendungsfälle anzupassen (z.B. Quiz-Modus, Fragen & Antworten, Erläuterungen). Lassen Sie das Feld leer, um die Standard-Instruktionen des Brains zu verwenden.';

// Beispiel-Prompts.
$string['example_prompts_label'] = 'Klicken Sie auf ein Beispiel, um es als Vorlage einzufügen:';
$string['example_socratic_label'] = 'Sokratischer Tutor';
$string['example_socratic'] = 'Gib niemals die direkte Lösung. Stelle stattdessen gezielte Rückfragen, die den Schüler selbst zum Ergebnis führen. Erst wenn der Schüler dreimal nach der Lösung gefragt hat, gib sie preis – und zwar mit vollständigem Rechenweg.';
$string['example_quiz_label'] = 'Quiz-Modus';
$string['example_quiz'] = 'Du bist ein Quizmaster. Stelle immer zuerst eine Frage zum Thema, bevor du Inhalte erklärst. Gib niemals mehr als eine Frage pro Antwort. Bewerte die Antwort des Schülers und stelle dann die nächste Frage.';
$string['example_simple_label'] = 'Einfache Sprache';
$string['example_simple'] = 'Erkläre alles so, wie du es einem Grundschüler der 4. Klasse erklären würdest. Verwende nur kurze Sätze (max. 12 Wörter) und konkrete Alltagsbeispiele. Keine Fachbegriffe ohne Erklärung.';
$string['example_math_label'] = 'Mathe-Tutor';
$string['example_math'] = 'Du bist Mathe-Tutor. Formatiere alle Gleichungen mit AsciiMath in Backticks (z.B. `x^2 + 2x - 3 = 0`). Bei Aufgaben zeige immer drei Dinge in dieser Reihenfolge: (1) die Formel, (2) die Schritte, (3) das Ergebnis. Runde niemals – gib Brüche exakt an.';
$string['example_short_label'] = 'Kurzantworten';
$string['example_short'] = 'Antworte in maximal 3 Sätzen. Verwende keine Listen und keine Überschriften. Wenn die Antwort länger werden müsste, frage stattdessen: „Welcher Teil interessiert dich am meisten?"';

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

// Popup-Chat.
$string['popup_button_label'] = 'Quivr Chat';
$string['error_opening_chat'] = 'Fehler beim Öffnen des Chats.';
$string['error_no_chat_available'] = 'Kein Chat für diesen Kurs verfügbar.';
$string['error_loading_chat'] = 'Fehler beim Laden des Chats.';

// API-Fehlermeldungen.
$string['error_chat_creation_failed'] = 'Chat konnte nicht erstellt werden';
$string['error_no_response'] = 'Keine Antwort.';
$string['error_course_not_found'] = 'Kurs nicht gefunden';
$string['error_access_denied'] = 'Zugriff verweigert';
$string['error_no_instances_in_course'] = 'Keine Quivr Chat Aktivitäten in diesem Kurs gefunden';
$string['error_invalid_chatid_format'] = 'Ungültiges Chat-ID-Format';
$string['error_invalid_message_format'] = 'Ungültiges Nachrichtenformat (role und content erforderlich)';
$string['error_method_not_allowed'] = 'Methode nicht erlaubt';
$string['chat_session_cleared'] = 'Chat-Sitzung gelöscht';
$string['error_no_apikey'] = 'Kein API-Schlüssel verfügbar. Bitte geben Sie zuerst einen API-Schlüssel ein.';
$string['error_connect_backend'] = 'Verbindung zum Quivr-Backend fehlgeschlagen';
$string['error_fetch_brains'] = 'Fehler beim Laden der Brains';
$string['error_obtain_token'] = 'Chat-Token konnte nicht bezogen werden';

// Privacy API.
$string['privacy:metadata'] = 'Das Quivr Chat Plugin sendet Benutzerfragen an einen externen Quivr API-Dienst für KI-gestützte Antworten. Es werden keine personenbezogenen Daten übertragen.';
$string['privacy:metadata:quivr_api'] = 'Benutzerfragen werden an den konfigurierten Quivr API-Dienst gesendet, um KI-gestützte Antworten zu generieren. Es wird nur der Fragetext übertragen; keine Benutzernamen, E-Mail-Adressen oder andere personenbezogene Daten werden gesendet.';
$string['privacy:metadata:quivr_api:question'] = 'Der vom Benutzer eingegebene Fragetext wird an die Quivr API gesendet, um eine Antwort zu generieren.';
$string['privacy:metadata:preference:apikey'] = 'Lehrkräfte können ihren Quivr API-Schlüssel in ihren Benutzereinstellungen speichern, um bei der Erstellung von Chat-Aktivitäten bequem darauf zugreifen zu können.';
