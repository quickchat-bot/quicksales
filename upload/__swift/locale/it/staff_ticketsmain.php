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
    'tickets'                   => 'Ticket',
    'overdue'                   => 'In Ritardo',
    'unassigned'                => '-- Non Assegnato/i --',
    'unassigned2'               => 'Non Assegnato',
    'menuviews'                 => 'Visualizza: %s',
    'merge'                     => 'Unisci',
    'trash'                     => 'Cestino',
    'spam'                      => 'Spam',
    'watch'                     => 'Osserva',
    /*
    * BUG FIX - Saloni Dhall
    *
    * SWIFT-3091 Staff CP: laguage display issue (only button reply)
    *
    * Comments: None
    */
    'reply'                     => 'Rispondi',
    'qticketpost'               => 'Cita questo messaggio',

    // Tree
    'treeinbox'                 => 'Posta in arrivo',
    'treetrash'                 => 'Cestino',
    'treewatched'               => 'Osservato',
    'treeflagged'               => 'Contrassegnato',
    'treemytickets'             => 'I Miei Ticket',
    'treeunassigned'            => 'Non Assegnato',

    // Grid Titles
    'f_ticketid'                => 'ID Ticket',
    'f_subject'                 => 'Oggetto',
    'f_queue'                   => 'Coda Email',
    'f_department'              => 'Dipartimento',
    'f_ticketstatus'            => 'Stato',
    'f_duedate'                 => 'Risposta Entro',
    'f_lastactivity'            => 'Ultima Attività',
    'f_date'                    => 'Data',
    'f_owner'                   => 'Proprietario',
    'f_priority'                => 'Priorità',
    'f_lastreplier'             => 'Ultimo che ha Risposto',
    'f_fullname'                => 'Nome',
    'f_timeworked'              => 'Tempo Lavorato',
    'f_email'                   => 'Email',
    'f_totalreplies'            => 'Risposte',
    'f_assignstatus'            => 'Assegnato',
    'f_flagtype'                => 'Contrassegno',
    'f_laststaffreply'          => 'Aggiornamento del Personale',
    'f_lastuserreply'           => 'Aggiornamento Utente',
    'f_tgroup'                  => 'Gruppo di Modelli',
    'f_slaplan'                 => 'Piano di SLA',
    'f_usergroup'               => 'Gruppo di Utenti',
    'f_userorganization'        => 'Organizzazione',
    'f_escalationrule'          => 'Regola di Escalation',
    'f_escalatedtime'           => 'Tempo in Escalation',
    'f_resolutiondue'           => 'Scadenza Risoluzione',
    'f_type'                    => 'Tipo',
    'f_typeicon'                => 'Tipo (Icona)',

    // Creation Modes
    'cm_api'                    => 'API',
    'cm_email'                  => 'Email',
    'cm_sitebadge'              => 'Badge del Sito',
    'cm_staffcp'                => 'CP Personale',
    'cm_supportcenter'          => 'Centro di Supporto',

    // Ticket listing icon description tags
    'alt_hasattachments'        => 'Contiene Allegati',
    'alt_isescalated'           => 'Questo ticket è in ritardo ed è in escalation',
    'alt_raisedbyemail'         => 'Generato via email',
    'alt_linkedticket'          => 'Questo ticket è collegato ad un altro',
    'alt_followupset'           => 'È stato impostato un elemento di follow-up per questo ticket',
    'alt_assignedotyou'         => 'Assegnato a te',
    'alt_watchingticket'        => 'Stai osservando questo ticket',
    'alt_ticketlocked'          => 'Questo ticket è visualizzato da un altro membro del personale',
    'alt_ticketphonetype'       => 'Questo ticket è stato contrassegnato come ticket di tipo telefonico',
    'alt_ticketunread'          => 'È stato fatto un aggiornamento a questo ticket dall\'ultima tua visita',
    'alt_tickethastimetracking' => 'Questo ticket ha una voce di rilevamento del tempo',
    'alt_tickethasnote'         => 'Questo ticket ha una o più note',
    'alt_tickethasbilling'      => 'Questo ticket ha una voce di rilevamento del tempo e fatturazione',

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
