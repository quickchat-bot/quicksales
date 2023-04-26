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
 * @copyright      Copyright (c) 2001-2014, QuickSupport
 * @license        http://www.kayako.com/license
 * @link           http://www.kayako.com
 *
 * ###############################################
 */

$__LANG = array(
    'autoclose'                    => 'Automatische Schließung',
    'manage'                       => 'Verwalten',
    'targetstatus'                 => 'Status verändern auf',
    'tabgeneral'                   => 'Allgemeines',

    'insertrule'                   => 'Regel einfügen',
    'editrule'                     => 'Regel bearbeiten',

    'ruletitle'                    => 'Regeltitel',
    'desc_ruletitle'               => 'Geben Sie einen Titel für diese Regel zur automatischen Schließung ein.',
    'targetticketstatus'           => 'Status ändern zu',
    'desc_targetticketstatus'      => 'Wenn das Zeitlimit für Inaktivität überschritten wird (siehe unten), wird der Status der Tickets, die diese Regel erfüllen, zu folgenden gerändert (das sollte der Status <strong>erledigt</strong> sein).',
    'inactivitythreshold'          => 'Inaktivitätsschwelle',
    'desc_inactivitythreshold'     => 'Wird das Ticket innerhalb der angegebenen Stunden nicht aktualisiert, wird es als inaktiv betrachtet. Dies ist der erste Schritt zur automatischen Schließung eines Tickets.',
    'closurethreshold'             => 'Schließungsschwelle',
    'desc_closurethreshold'        => 'Wird ein als inaktiv markiertes Ticket innerhalb der angegebenen Stunden nicht aktualisiert, wird dessen Status auf den oben angegebenen geändert.',
    'isenabled'                    => 'Regel ist aktiviert',
    'desc_isenabled'               => 'Bestimmen Sie, ob die Regel aktiviert oder deaktiviert ist.',
    'sortorder'                    => 'Ausführungsreihenfolge',
    'desc_sortorder'               => 'Es ist möglich, mehrere Regeln zur automatischen Schließung zu erstellen. Die Reihenfolge der Ausführung bestimmt, welche Regeln zuerst ausgeführt werden, kleinste zur größten.',
    'sendpendingnotification'      => 'Benachrichtigungs-E-Mail zur Inaktivität schicken',
    'desc_sendpendingnotification' => 'Das Helpdesk kann den Benutzer über die Markierung seines Tickets als inaktiv benachrichtigen und das Ticket schließen, falls keine Antwort erhalten wird.',
    'sendfinalnotification'        => 'Finale Benachrichtigungs-E-Mail zur Ticketschließung versenden',
    'desc_sendfinalnotification'   => 'Das Helpdesk kann den Benutzer auch über die Schließung seines Tickets benachrichtigen.',
    'suppresssurveyemail'          => 'Kundenumfrage-E-Mail abstellen',
    'desc_suppresssurveyemail'     => 'Bei der Aktivierung von Einladungen zu einer Kundenzufriedenheitsumfrage für Tickets in einem bestimmten Status, möchten Sie vielleicht verhindern, dass das Helpdesk Einladungen zur Umfrage an automatisch geschlossene Tickets versendet.',

    'insertcriteria'               => 'Kriterien einfügen',

    'titleautocloseruledel'        => 'Regel zur automatischen Schließung (%d)',
    'msgautocloseruledel'          => 'Folgende Regeln zur automatischen Schließung wurden gelöscht:',
    'titleautocloseruleenable'     => 'Aktivierte Regeln zur automatischen Schließung (%d)',
    'msgautocloseruleenable'       => 'Folgende Regeln zur automatischen Schließung wurden aktiviert:',
    'titleautocloseruledisable'    => 'Deaktivierte Regeln zur automatischen Schließung (%d)',
    'msgautocloseruledisable'      => 'Folgende Regeln zur automatischen Schließung wurden deaktiviert:',
    'titleautocloseruleinsert'     => 'Regel zur automatischen Schließung wurde erstellt',
    'msgautocloseruleinsert'       => 'Die Regel zur automatischen Schließung (%s) wurde erfolgreich erstellt.',
    'titleautocloseruleupdate'     => 'Regel zur automatischen Schließung wurde aktualisiert',
    'msgautocloseruleupdate'       => 'Die Regel zur automatischen Schließung (%s) wurde erfolgreich aktualisiert.',

    'titlenocriteriaadded'         => 'Es wurden keine Kriterien angegeben',
    'msgnocriteriaadded'           => 'Sie müssen mindestens ein Kriterium angeben, um eine Regel zur automatischen Schließung zu erstellen (ansonsten erkennt das Helpdesk nicht, welches Ticket automatisch geschlossen werden soll).',

    /**
     * ---------------------------------------------
     * Rule Criterias
     * ---------------------------------------------
     */
    'notapplicable'          => '--NA--',
    'articketstatusid'       => 'Status',
    'desc_articketstatusid'  => '',
    'arpriorityid'           => 'Priorität',
    'desc_arpriorityid'      => '',
    'ardepartmentid'         => 'Abteilung',
    'desc_ardepartmentid'    => '',
    'arownerstaffid'         => 'Eigentümer',
    'desc_arownerstaffid'    => '',
    'aremailqueueid'         => 'E-Mail-Warteschlange',
    'arusergroupid'          => 'Benutzergruppe',
    'desc_arusergroupid'     => '',
    'desc_aremailqueueid'    => '',
    'arflagtype'             => 'Markieren',
    'desc_arflagtype'        => '',
    'arbayescategoryid'      => 'Bayes-Kategorie',
    'desc_arbayescategoryid' => 'Tickets, die mit einer bestimmten Bayes-Kategorie übereinstimmen.',
    'arcreator'              => 'Verfasser',
    'desc_arcreator'         => '',
    'creatorstaff'           => 'Personal',
    'creatorclient'          => 'Benutzer',
    'arunassigned'           => '--Nicht zugewiesen--',
    'artemplategroupid'      => 'Vorlagengruppe',
    'desc_artemplategroupid' => '',
    'artickettypeid'         => 'Typ',
    'desc_rtickettypeid'     => '',
);

return $__LANG;
