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
    'tickets'                   => 'Tickets',
    'overdue'                   => 'Over tijd',
    'unassigned'                => '-- Niet toegewezen --',
    'unassigned2'               => 'Niet toegewezen',
    'menuviews'                 => 'Bekijken: %s',
    'merge'                     => 'Samenvoegen',
    'trash'                     => 'Prullenbak',
    'spam'                      => 'Spam',
    'watch'                     => 'Volgen',
    /*
    * BUG FIX - Saloni Dhall
    *
    * SWIFT-3091 Staff CP: laguage display issue (only button reply)
    *
    * Comments: None
    */
    'reply'                     => 'Antwoorden',
    'qticketpost'               => 'Bericht citeren',

    // Tree
    'treeinbox'                 => 'Postvak IN',
    'treetrash'                 => 'Prullenbak',
    'treewatched'               => 'Gevolgd',
    'treeflagged'               => 'Gevlagd',
    'treemytickets'             => 'Mijn tickets',
    'treeunassigned'            => 'Niet toegewezen',

    // Grid Titles
    'f_ticketid'                => 'Ticket-ID',
    'f_subject'                 => 'Onderwerp',
    'f_queue'                   => 'Emailwachtrij',
    'f_department'              => 'Afdeling',
    'f_ticketstatus'            => 'Status',
    'f_duedate'                 => 'Antwoord vereist',
    'f_lastactivity'            => 'Laatste activiteit',
    'f_date'                    => 'Datum',
    'f_owner'                   => 'Eigenaar',
    'f_priority'                => 'Prioriteit',
    'f_lastreplier'             => 'Laatste antwoorder',
    'f_fullname'                => 'Naam',
    'f_timeworked'              => 'Gewerkte tijd',
    'f_email'                   => 'Email',
    'f_totalreplies'            => 'Antwoorden',
    'f_assignstatus'            => 'Toegewezen',
    'f_flagtype'                => 'Vlag',
    'f_laststaffreply'          => 'Bijwerking door medewerker',
    'f_lastuserreply'           => 'Bijwerking door gebruiker',
    'f_tgroup'                  => 'Sjabloongroep',
    'f_slaplan'                 => 'SLA-plan',
    'f_usergroup'               => 'Gebruikersgroep',
    'f_userorganization'        => 'Organisatie',
    'f_escalationrule'          => 'Escalatieregel',
    'f_escalatedtime'           => 'Escalatietijd',
    'f_resolutiondue'           => 'Afhandeling nodig',
    'f_type'                    => 'Type',
    'f_typeicon'                => 'Type (pictogram)',

    // Creation Modes
    'cm_api'                    => 'API',
    'cm_email'                  => 'Email',
    'cm_sitebadge'              => 'Sitebadge',
    'cm_staffcp'                => 'Medewerkerbeheerscherm',
    'cm_supportcenter'          => 'Support Center',

    // Ticket listing icon description tags
    'alt_hasattachments'        => 'Heeft bijlagen',
    'alt_isescalated'           => 'De ticket is over tijd geraakt en is geëscaleerd',
    'alt_raisedbyemail'         => 'Gesteld via email',
    'alt_linkedticket'          => 'Deze ticket is aan een andere gekoppeld',
    'alt_followupset'           => 'Er is een follow-up ingesteld voor deze ticket',
    'alt_assignedotyou'         => 'Aan jou toegewezen',
    'alt_watchingticket'        => 'Je volgt deze ticket',
    'alt_ticketlocked'          => 'Deze ticket wordt door een andere medewerker bekeken',
    'alt_ticketphonetype'       => 'Deze ticket is gemarkeerd als telefoonticket',
    'alt_ticketunread'          => 'Deze ticket is bijgewerkt sinds je laatste bezoek',
    'alt_tickethastimetracking' => 'Deze ticket heeft een tijdregistratievermelding',
    'alt_tickethasnote'         => 'Deze ticket heeft een of meer ticketnotities',
    'alt_tickethasbilling'      => 'Deze ticket heeft een tijdregistratie- en factureringsvermelding',

    // Potentialy unused phrases in staff_ticketsmain.php
    'filtertickets'             => 'Filter Tickets',

    // Ticket Copy/Split
    'closeold'                  => 'Close old ticket',
    'closeold_d'                => 'Close the old ticket (split from) after splitting this ticket.',
    'splitat'                   => 'Split at reply',
    'splitat_d'                 => 'This reply, and all replies made after it, will belong to the new ticket.',
    'split_into'                => 'Split into ticket',
    'split_from'                => 'Split from ticket',
    'duplicateat'               => 'Duplicate at reply',
    'duplicateat_d'             => 'This reply, and all replies made after it, will be duplicated to the new ticket.',
    'duplicate_into'            => 'Duplicate into ticket',
    'duplicate_from'            => 'Duplicate from ticket',
    'ticketsplitter'            => 'Ticket Splitter',
    'no_such_post'              => 'Couldn\'t identify the post at which to split.',
    'oldticket'                 => 'Old Ticket',
    'newticket'                 => 'New Ticket',
    'split'                     => 'Split',
    'duplicate'                 => 'Duplicate',
    'splitticket'               => 'Split Ticket',
    'duplicateticket'           => 'Duplicate Ticket'
);


return $__LANG;
