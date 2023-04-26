<?php
/**
 * ###############################################
 *
 * SWIFT Framework
 * _______________________________________________
 *
 * @author         Varun Shoor
 *
 * @package        SWIFT
 * @copyright      Copyright (c) 2001-2014, Kayako
 * @license        http://www.kayako.com/license
 * @link           http://www.kayako.com
 *
 * ###############################################
 */

$__LANG = array(
    'ratings'                     => 'Bewertungen',
    'tabgeneral'                  => 'Allgemeines',
    'tabpermissionsstaff'         => 'Berechtigungen (Personal)',
    'tabpermissionsuser'          => 'Berechtigungen (Benutzer)',
    'staffgroups'                 => 'Personalteams',
    'usergroups'                  => 'Benutzergruppen',
    'ratingticket'                => 'Ticket',
    'ratingticketpost'            => 'Ticket-Beiträge',
    'ratingchatsurvey'            => 'Chat-Umfrage',
    'ratingchathistory'           => 'Chat-Verlauf',

    'staffvisibilitycustom'       => 'Bewertungsänderung bestimmter Personalteams einschränken',
    'desc_staffvisibilitycustom'  => 'Ist dies aktivier, sind nur die unten genannten Teams fähig, Werte für diese Bewertung zu verändern und einzustellen.',

    'buservisibilitycustom'       => 'Bewertung für besimmte Benutzergruppen einschränken',
    'desc_buservisibilitycustom'  => 'Ist dies aktivier, sind nur die unten genannten Benutzergruppen fähig, Werte für diese Bewertung zu verändern und einzustellen.',

    'bstaffvisibilitycustom'      => 'Bewertung auf bestimmte Personalteams einschränken',
    'desc_bstaffvisibilitycustom' => 'Wenn dies aktiviert ist, ist diese Bewertung nur für die unten genannten Teams sichtbar.',

    'isclientonly'                => 'Personal verhindern, Werte für die Bewertung einzustellen',
    'desc_isclientonly'           => 'Ist dies aktiviert, können nur Endbenutzer Werte für diese Bewertung eingeben.',

    'ratingscale'                 => 'Bewertungsskala',
    'desc_ratingscale'            => 'Die höchste Bewertung, die abgegeben werden kann. Wird beispielsweise <em>5</em> gewählt, ist die niedrigste Bewertung 1 und die höchste 10.',

    'ratingtype'                  => 'Helpdesk-Bereich',
    'desc_ratingtype'             => 'Der Bereich des Helpdesks, auf den sich diese Bewertung bezieht. <br /><br /><strong>Ticket:</strong> Für die Bewertung ganzer Tickets.<br /><strong>Ticket-Beitrag:</strong> Für die Bewertung einzelner Antworten auf Tickets.<br /><strong>Chat-Umfrage:</strong> Werden zu den Post-Chat-Umfragen hinzugefügt.<br /><strong>Chat-Verlauf:</strong> Für die Bewertung von Chat-Verläufen in der Personal-Systemsteuerung (für interne Bewertungen und Audits gedacht).',

    'ratingtitle'                 => 'Bewertungsname',
    'desc_ratingtitle'            => 'Z.B. <em>Allgemeine Zufriedenheit</em> oder <em>Lösungszeit</em>.',

    'displayorder'                => 'Anzeigereihenfolge',
    'desc_displayorder'           => 'Es können mehrere Bewertungen an der gleichen Stelle angezeigt werden. Sie werden nach Ihrer Zahl der Anzeigereihenfolge, kleinste zur größten, angezeigt.',

    'ratingdep'                   => 'Bewertung auf eine Abteilung einschränken',
    'desc_ratingdep'              => 'Wählen Sie eine Abteilung, um diese Bewertung von Tickets oder Chats auf eine bestimmte Abteilung einzuschränken. Wenn keine Abteilung ausgewählt wird, erscheint die Bewertung bei allen Abteilungen.',
    'ratingalldep'                => '--Alle Abteilungen--',

    'iseditable'                  => 'Änderung nach erster Übermittlung ermöglichen',
    'desc_iseditable'             => 'Wenn dies aktiviert ist, kann der Wert für die Bewertung geändert werden.',

    'ratingvisibility'            => 'Wer kann eine Bewertung abgeben?',
    'desc_ratingvisibility'       => '<strong>Öffentliche</strong> Bewertungen sind für alle Benutzer verfügbar. Wählen Sie diese Option aus, um Feedback von Ihren Endbenutzern einzuholen und dem Personal Einsicht zu ermöglichen.<br /><strong>Privat</strong> Bewertungen sind nur für das Personal, für interne Zwecke, verfügbar.',

    'ratingvis'                   => 'Sichtbarkeit',
    'insertrating'                => 'Bewertung einfügen',
    'desc_insertrating'           => '',
    'manageratings'               => 'Verwalten',
    'desc_manageratings'          => '',
    'wineditrating'               => 'Bewertung bearbeiten : %s',
    'editrating'                  => 'Bewertung bearbeiten',
    'desc_editrating'             => '',
    'titledelrating'              => 'Gelöschte Bewertungen (%d)',
    'msgdelrating'                => 'Folgende Bewertungen und ihre Daten wurden gelöscht:',
    'titleratinginsert'           => 'Bewertung erstellt',
    'msgratinginsert'             => 'Die Bewertung (%s) wurde erfolgreich erstellt.',
    'titleratingupdate'           => 'Bewertung wurde aktualisiert',
    'msgratingupdate'             => 'Die Bewertung (%s) wurde erfolgreich aktualisiert.',

    // Potentialy unused phrases in admin_ratings.php
    'ratingknowledgebase'         => 'Wissensdatenbank',
    'ratingtroubleshooter'        => 'Fehlersuche',
    'ratingnews'                  => 'News',
);


return $__LANG;
