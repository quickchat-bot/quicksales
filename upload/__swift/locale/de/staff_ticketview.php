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
    'titlemasterviewdel'            => '"%d" Ansicht(en) konnten nicht gelöscht werden',
    'msgmasterviewdel'              => 'Kayako konnte folgende Ansicht(en) von Master-Tickets nicht löschen:',
    'manageviews'                   => 'Ansichten',
    'insertview'                    => 'Ansicht eingeben',
    'viewtitle'                     => 'Ticket-Ansicht-Titel',
    'desc_viewtitle'                => 'Geben Sie einen Titel für die Aufrufe ein (nur Zahlen, Buchstaben und Leerzeichen).',
    'viewscope'                     => 'Ansichtsbereich',
    'createdby'                     => 'Verfasser',
    'editview'                      => 'Ansicht bearbeiten',
    'titleticketviewinsert'         => 'Ticket-Ansicht (%s) erstellt',
    'msgticketviewinsert'           => 'Die Ticket-Ansicht (%s) wurde erfolgreich erstellt.',
    'titleticketviewupdate'         => 'Ticket-Ansicht (%s) aktualisiert',
    'msgticketviewupdate'           => 'Die Ticket-Ansicht (%s) wurde erfolgreich aktualisiert.',
    'titleviewfieldempty'           => 'Es wurden keine Spalten für die Ticket-Ansicht angegeben',
    'msgviewfieldempty'             => 'Sie müssen mindestens eine Spalte für eine Ticket-Ansicht angeben (ansonsten gäbe es nicht zum Anzeigen!).',

    'viewlinkdepartment'            => 'Diese Ansicht mit Abteilungen verknüpfen',
    'desc_viewlinkdepartment'       => 'Diese Ansicht kann beim Durchsuchen von Tickets in einer der hier ausgewählten Abteilungen automatisch geladen werden.',

    'taboptions'                    => 'Optionen',
    'tabcolumns'                    => 'Spalten',

    'viewscope_global'              => 'Alle Mitarbeiter',
    'viewscope_private'             => 'Nur ich',
    'viewscope_team'                => 'Mein Team',

    'viewunassigned'                => 'Nicht zugewiesen',
    'viewassigned'                  => 'Mir zugewiesen',
    'viewalltickets'                => 'Alle Tickets',

    'viewassignedfield'             => 'Tickets anzeigen, die zugewiesen sind an',
    'desc_viewassignedfield'        => '<strong>Nicht zugewiesen</strong> Tickets, die niemandem zugewiesen wurden.<br /><strong>Mir zugewiesen</strong> Tickets, die Ihnen zugewiesen wurden.<br /><strong>Alle Tickets</strong> All Tickets, unabängig davon, ob Sie Ihnen, jemand anderes oder niemanden zugewiesen wurden.',
    'desc_viewscope'                => '<strong>Alle Mitarbeiter</strong> Das gesamente Personal kann diese Ansicht verwenden.<br /><strong>Nur ich</strong> Diese Ansicht ist nur für Sie verfügbar.<br /><strong>Mein Team</strong> Die Ansicht ist für Sie und für anderes Personal aus Ihrem Team verfügbar.',

    'viewselectfields'              => 'Wählen Sie Spalten (ziehen, ablegen und organisieren Sie alles im gelbe Kasten)',
    'viewsortoptions'               => 'Sortieroptionen',
    'viewsortby'                    => 'Sortieren nach',
    'desc_viewsortby'               => 'Wie werden Tickets zunächst sortiert, wenn diese Ansicht verwendet wird?',
    'viewsortorder'                 => 'Sortierreihenfolge',
    'desc_viewsortorder'            => 'Tickets können aufsteigend (kleinstes oder größtes, neuestes oder ältestes, etc.) oder absteigend sortiert werden.',
    'viewticketsperpage'            => 'Pro Seite anzuzeigende Tickets',
    'adminviewticket'         => 'The number of tickets per page is too high.',
    'desc_adminviewticket'    => 'Staff ticket views are currently limited to showing %s tickets per page. Contact your administrator to increase this limit.',
    'desc_viewticketsperpage' => 'Specify the number of tickets to display before breaking the view into multiple pages. Note: This value cannot exceed the default maximum your administrator has set for staff views.',
    'generaloptions'                => 'Allgemeine Optionen',
    'viewautorefresh'               => 'Ticket-Liste automatisch aktualisieren',
    'desc_viewautorefresh'          => 'Wenn diese Ansicht geladen wird, kann die Ticketliste automatisch aktualisiert werden. Nützlich für Wallboard-Benutzer.',
    'autorefresh_disable'           => '-- Automatische Aktualisierung deaktivieren --',
    'autorefresh_30s'               => 'Alle 30 Sekunden aktualisieren',
    'autorefresh_1m'                => 'Jede Minute aktualisieren',
    'autorefresh_5m'                => 'Alle 5 Minuten aktualisieren',
    'autorefresh_15m'               => 'Alle 15 Minuten aktualisieren',
    'autorefresh_30m'               => 'Alle 30 Minuten aktualisieren',
    'autorefresh_1h'                => 'Jede Stunde aktualisiert',
    'viewsetasowner'                => 'Ticket-Eigentümer automatisch auf aktiven Personalbenutzer einstellen',
    'desc_viewsetasowner'           => 'Beim Antworten oder Weiterleiten eines Tickets, wird der Ticket-Eigentümer automatisch auf den aktiven Personalbenutzer eingestellt, wenn diese Ansicht verwendet wird.',
    'viewdefaultstatusonreply'      => 'Standard Ticket-Status beim Antworten auf ein Ticket',
    'desc_viewdefaultstatusonreply' => 'Geben Sie an, welcher Status für ein Ticket automatisch festgelegt wird, wenn ein Personalbenutzer, der diese Ansicht verwendet, das Ticket beantwortet oder weiterleitet.',
    'defaultstatus_unspecified'     => '-- Keine Angabe --',

    'afterreply_topticketlist'      => 'Liste der Top-Level-Tickets (alle Tickets anzeigen)',
    'afterreply_activeticketlist'   => 'Liste der aktiven Tickets (in der aktuellen Abteilung oder Suchergebnissen)',
    'afterreply_ticket'             => 'Das aktive Ticket',
    'afterreply_nextticket'         => 'Nächstes verfügbares Ticket',
    'viewafterreplyaction'          => 'Nachdem ein Ticket beantwortet wurde, weiterführen auf',
    'desc_viewafterreplyaction'     => 'Wählen Sie aus, wohin Kayako Sie (oder wen auch immer, der diese Ansicht verwendet) nach dem Beantworten oder Weiterleiten eines Tickets führen soll.',

    'sort_asc'                      => 'Aufsteigend',
    'sort_desc'                     => 'Absteigend',

    // Potentialy unused phrases in staff_ticketview.php
    'viewfilteroptions'             => 'Filter Options',
    'viewfilterdepartments'         => 'Filter Department(s)',
    'desc_viewfilterdepartments'    => 'Kayako by default will display tickets from all/selected department. However, you can use this option to filter the tickets based on the selected departments.',
    'viewfiltertype'                => 'Filter Ticket Type(s)',
    'desc_viewfiltertype'           => 'You can use this option to filter the tickets based on the selected ticket type(s).',
    'viewfilterstatus'              => 'Filter Ticket Status(es)',
    'desc_viewfilterstatus'         => 'You can use this option to filter the tickets based on the selected ticket status(es).',
);


return $__LANG;
