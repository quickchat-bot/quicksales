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
    'insertpriority'            => 'Inserisci Priorità',
    'tabgeneral'                => 'Generale',
    'tabpermissions'            => 'Autorizzazioni',
    'tablanguages'              => 'Lingue',
    'prioritytitle'             => 'Titolo della priorità',
    'desc_prioritytitle'        => 'Ad esempio, <em>Alta</em> o <em>Livello 1</em>.',
    'prioritytype'              => 'Tipo di priorità',
    'desc_prioritytype'         => '<strong>Privato</strong> Le priorità non sono visibili agli utenti finali ma solo agli utenti del personale. <br /><strong>Pubblico</strong> Le priorità sono visibili ad entrambi.',
    'forecolor'                 => 'Colore del primo piano',
    'desc_forecolor'            => 'Specifica, opzionalmente, un colore di primo piano (testo) per la priorità.',
    'bgcolor'                   => 'Colore di sfondo',
    'desc_bgcolor'              => 'Specifica, opzionalmente, un colore di sfondo (ombreggiatura) per la priorità (accertati prima che il colore di primo piano sia visibile sul colore di fondo!).',
    'displayorder'              => 'Ordine di visualizzazione',
    'desc_displayorder'         => 'Le priorità sono elencate secondo l\'ordine di visualizzazione, dal più piccolo al più grande.',
    'displayiconpriority'       => 'Icona',
    'desc_displayiconpriority'  => 'Collega o carica un\'icona per questa priorità. {$themepath} può essere utilizzato per puntare alla directory immagini dell\'helpdesk, per esempio: <em>{$themepath}icon_prioritymedium.gif</em>.',
    'uservisibilitycustom'      => 'Limita la priorità a gruppi di utenti specifici',
    'desc_uservisibilitycustom' => 'Se abilitato, solo i gruppi di utenti qui sotto saranno in grado di vedere (e se il caso, selezionare) questa priorità.',
    'usergroups'                => 'Gruppi di Utenti',
    'invalidticketpriority'     => 'È stato riscontrato un problema (priorità del ticket non valida - assicurarsi che la priorità sia presente nel database)',
    'editpriority'              => 'Modifica Priorità',
    'desc_editpriority'         => '',
    'managepriorities'          => 'Priorità',
    'desc_managepriorities'     => '',
    'wineditticketpriority'     => 'Modifica la Priorità Ticket: %s',
    'titledelticketpriority'    => 'Eliminate le priorità ticket (%d)',
    'msgdelticketpriority'      => 'Sono state eliminate le seguenti priorità ticket:',
    'titleticketpriorityinsert' => 'Creata la priorità ticket %s',
    'msgticketpriorityinsert'   => 'La priorità (%s) è stata creata con successo.',
    'titleticketpriorityupdate' => 'Aggiornata la priorità ticket %s',
    'msgticketpriorityupdate'   => 'La priorità (%s) è stata aggiornata con successo.',
    'titlemasterprioritydelete' => 'Non è stato possibile eliminare le priorità ticket',
    'msgmasterprioritydelete'   => 'Impossibile eliminare le seguenti priorità master del ticket:',

    // Potentialy unused phrases in admin_ticketspriority.php
    'desc_insertpriority'       => '',
    'prioritylist'              => 'Priority List',
    'prioritydetails'           => 'Priority Details',
    'registrationrequired'      => 'Registration Required?',
    'desc_registrationrequired' => 'If enabled, only registered clients will be able to create Tickets with this Priority.',
);

return $__LANG;
