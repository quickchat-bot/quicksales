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
    'insertpriority'            => 'Prioriteit toevoegen',
    'tabgeneral'                => 'Algemeen',
    'tabpermissions'            => 'Rechten',
    'tablanguages'              => 'Talen',
    'prioritytitle'             => 'Prioriteittitel',
    'desc_prioritytitle'        => 'Bijvoorbeeld <em>Hoog</em> of <em>Niveau 1</em>.',
    'prioritytype'              => 'Prioriteittype',
    'desc_prioritytype'         => '<strong>Privé</strong> Prioriteiten zijn niet zichtbaar voor eindgebruikers, alleen voor medewerkers.<br /><strong>Publiek</strong> Prioriteiten zijn voor beide zichtbaar.',
    'forecolor'                 => 'Voorgrondkleur',
    'desc_forecolor'            => 'Geef optioneel een voorgrondkleur (tekst) aan voor de prioriteit.',
    'bgcolor'                   => 'Achtergrondkleur',
    'desc_bgcolor'              => 'Geef optioneel een achtergrondkleur (schaduw) aan voor de prioriteit (zorg dat de voorgrondkleur hierboven zichtbaar is tegen de achtergrondkleur!).',
    'displayorder'              => 'Weergavevolgorde',
    'desc_displayorder'         => 'Prioriteiten worden weergegeven volgens hun weergavevolgorde, van laag naar hoog.',
    'displayiconpriority'       => 'Pictogram',
    'desc_displayiconpriority'  => 'Upload of link een pictogram voor deze prioriteit. {$themepath} kan worden gebruikt om naar de afbeeldingendirectory van de helpdesk te verwijderen, bijvoorbeeld <em>{$themepath}pictogram_prioriteitgemiddeld.gif</em>.',
    'uservisibilitycustom'      => 'Prioriteit beperken tot bepaalde gebruikersgroepen',
    'desc_uservisibilitycustom' => 'Als dit is ingeschakeld kunnen alleen onderstaande gebruikersgroepen deze prioriteit zien (en selecteren, indien van toepassing).',
    'usergroups'                => 'Gebruikersgroepen',
    'invalidticketpriority'     => 'Er is een probleem opgetreden (ongeldige ticketprioriteit - zorg dat de prioriteit bestaat in de database)',
    'editpriority'              => 'Prioriteit bewerken',
    'desc_editpriority'         => '',
    'managepriorities'          => 'Prioriteiten',
    'desc_managepriorities'     => '',
    'wineditticketpriority'     => 'Ticketprioriteit bewerken: %s',
    'titledelticketpriority'    => 'Ticketprioriteiten verwijderd (%d)',
    'msgdelticketpriority'      => 'De volgende ticketprioriteiten zijn verwijderd:',
    'titleticketpriorityinsert' => 'Ticketprioriteit %s gemaakt',
    'msgticketpriorityinsert'   => 'De prioriteit (%s) is succesvol gemaakt.',
    'titleticketpriorityupdate' => 'Ticketprioriteit %s bijgewerkt',
    'msgticketpriorityupdate'   => 'De prioriteit (%s) is succesvol bijgewekt.',
    'titlemasterprioritydelete' => 'Ticketprioriteiten konden niet worden verwijderd',
    'msgmasterprioritydelete'   => 'Kon de volgende hoofdticketprioriteiten niet verwijderen:',

    // Potentialy unused phrases in admin_ticketspriority.php
    'desc_insertpriority'       => '',
    'prioritylist'              => 'Priority List',
    'prioritydetails'           => 'Priority Details',
    'registrationrequired'      => 'Registration Required?',
    'desc_registrationrequired' => 'If enabled, only registered clients will be able to create Tickets with this Priority.',
);

return $__LANG;
