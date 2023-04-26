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
    'titlemasterviewdel'            => 'Kan "%d" weergave(n) niet verwijderen',
    'msgmasterviewdel'              => 'Kayako kan de volgende hoofdticketweergave(n) niet verwijderen:',
    'manageviews'                   => 'Weergaven',
    'insertview'                    => 'Weergave toevoegen',
    'viewtitle'                     => 'Ticket weergavetitel',
    'desc_viewtitle'                => 'Voer een titel in voor de weergave (alleen cijfers, letters en spaties).',
    'viewscope'                     => 'Weergavetoepassingsgebied',
    'createdby'                     => 'Auteur',
    'editview'                      => 'Weergave bewerken',
    'titleticketviewinsert'         => 'Ticketweergave (%s) gemaakt',
    'msgticketviewinsert'           => 'De ticketweergave (%s) is succesvol gemaakt.',
    'titleticketviewupdate'         => 'Ticketweergave (%s) bijgewerkt',
    'msgticketviewupdate'           => 'De ticketweergave (%s) is succesvol bijgewerkt.',
    'titleviewfieldempty'           => 'Geen ticketweergavekolommen opgegeven',
    'msgviewfieldempty'             => 'Je moet tenminste één kolom opgegeven voor een ticketweergave (anders is er niets om weer te geven!).',

    'viewlinkdepartment'            => 'Deze weergave aan afdelingen koppelen',
    'desc_viewlinkdepartment'       => 'Deze weergave kan automatisch worden gelaten bij het bekijken van tickets binnen een van de hier geselecteerde afdelingen.',

    'taboptions'                    => 'Opties',
    'tabcolumns'                    => 'Kolommen',

    'viewscope_global'              => 'Alle medewerkers',
    'viewscope_private'             => 'Alleen ik',
    'viewscope_team'                => 'Mijn team',

    'viewunassigned'                => 'Niet toegewezen',
    'viewassigned'                  => 'Aan mij toegewezen',
    'viewalltickets'                => 'Alle tickets',

    'viewassignedfield'             => 'Tickets weergeven die zijn toegewezen aan',
    'desc_viewassignedfield'        => '<strong>Niet toegewezen</strong> Tickets die aan niemand zijn toegewezen.<br /><strong>Aan mij toegewezen</strong> Tickets die aan jou zijn toegewezen.<br /><strong>Alle tickets</strong> Alle tickets, of ze nu aan jou, aan iemand anders, of niet zijn toegewezen.',
    'viewscope'                     => 'Weergavetoepassingsgebied',
    'desc_viewscope'                => '<strong>Alle medewerkers</strong> Alle medewerkers kunnen deze weergave gebruiken.<br /><strong>Alleen ik</strong> De weergave is alleen beschikbaar voor jou.<br /><strong>Mijn team</strong> De weergave is beschikbaar voor jou en anderen in je team.',

    'viewselectfields'              => 'Selecteer kolommen (slepen en ordenen in het gele vak)',
    'viewsortoptions'               => 'Sorteeropties',
    'viewsortby'                    => 'Sorteren op',
    'desc_viewsortby'               => 'Hoe worden tickets in eerste instantie gesorteerd wanneer deze weergave wordt gebruikt?',
    'viewsortorder'                 => 'Sorteervolgorde',
    'desc_viewsortorder'            => 'Tickets kunnen oplopend (van laag naar hoog, minst recent to meest recent, etc) worden gesorteerd of aflopend.',
    'viewticketsperpage'            => 'Aantal per pagina weer te geven tickets',
    'adminviewticket'         => 'The number of tickets per page is too high.',
    'desc_adminviewticket'    => 'Staff ticket views are currently limited to showing %s tickets per page. Contact your administrator to increase this limit.',
    'desc_viewticketsperpage' => 'Specify the number of tickets to display before breaking the view into multiple pages. Note: This value cannot exceed the default maximum your administrator has set for staff views.',    'generaloptions'                => 'Algemene opties',
    'viewautorefresh'               => 'Ticketlijst automatisch vernieuwen',
    'desc_viewautorefresh'          => 'Wanneer deze weergave wordt geladen, kan de ticketlijst automatisch worden vernieuwd. Nuttig voor wandbordgebruik.',
    'autorefresh_disable'           => '-- Automatisch vernieuwen uitschakelen --',
    'autorefresh_30s'               => 'Elke 30 seconden vernieuwen',
    'autorefresh_1m'                => 'Elke minuut vernieuwen',
    'autorefresh_5m'                => 'Elke 5 minuten vernieuwen',
    'autorefresh_15m'               => 'Elke 15 minuten vernieuwen',
    'autorefresh_30m'               => 'Elke 30 minuten vernieuwen',
    'autorefresh_1h'                => 'Elk uur vernieuwen',
    'viewsetasowner'                => 'Ticketeigenaar automatisch op actieve medewerker zetten',
    'desc_viewsetasowner'           => 'Bij het antwoorden op of doorsturen van een ticket wordt de ticketeigenaar automatisch op de actieve medewerker gezet, als deze weergave wordt gebruikt.',
    'viewdefaultstatusonreply'      => 'Standaard ticketstatus bij antwoorden op een ticket',
    'desc_viewdefaultstatusonreply' => 'Bepaal welke status een ticket automatisch krijgt wanneer een medewerker die deze weergave gebruikt een ticket beantwoordt of doorstuurt.',
    'defaultstatus_unspecified'     => '-- Onbepaald --',

    'afterreply_topticketlist'      => 'Ticketlijst topniveau (alle tickets weergeven)',
    'afterreply_activeticketlist'   => 'Lijst van actieve tickets (binnen de huidige afdeling of zoekresultaten)',
    'afterreply_ticket'             => 'De actieve ticket',
    'afterreply_nextticket'         => 'Volgende beschikbare ticket',
    'viewafterreplyaction'          => 'Nadat een ticket is beantwoord, ga naar',
    'desc_viewafterreplyaction'     => 'Bepaal waar Kayako je (of wie deze weergave ook gebruikt) heen moet brengen na het beantwoorden of doorsturen van een ticket.',

    'sort_asc'                      => 'Oplopend',
    'sort_desc'                     => 'Aflopend',

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
