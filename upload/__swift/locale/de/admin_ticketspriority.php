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

$__LANG = array (
    'tickets'                   => 'Tickets',
    'insertpriority'            => 'Priorität eingeben',
    'tabgeneral'                => 'Allgemeines',
    'tabpermissions'            => 'Berechtigungen',
    'tablanguages'              => 'Sprachen',
    'prioritytitle'             => 'Prioritätstitel',
    'desc_prioritytitle'        => 'Zum Beispiel, <em>hoch</em> oder <em>Level 1</em>.',
    'prioritytype'              => 'Prioritätstyp',
    'desc_prioritytype'         => '<strong>Private</strong> Prioritäten sind für Endnutzer nicht sichtbar, nur für Personalnutzer.<br /><strong>Öffentliche</strong> Prioritäten sind für beide sichtbar.',
    'forecolor'                 => 'Vordergrundfarbe',
    'desc_forecolor'            => 'Geben Sie optional eine Vordergrundfarbe (Text) für die Priorität an.',
    'bgcolor'                   => 'Hintergrundfarbe',
    'desc_bgcolor'              => 'Geben Sie optional eine Hintergrundfarbe (Schattierung) für die Priorität an (vergewissern Sie sich, dass die oben bestimmte Vordergrundfarbe über der Hintergrundfarbe sichtbar ist!).',
    'displayorder'              => 'Anzeigereihenfolge',
    'desc_displayorder'         => 'Prioritäten werden in dieser Reihenfolge angezeigt, die kleinsten zuerst.',
    'displayiconpriority'       => 'Symbol',
    'desc_displayiconpriority'  => 'Verlinken Sie oder landen Sie ein Symbol für diese Priorität hoch. {$themepath} kann dazu benutzt werden, das Helpdesk-Bilderverzeichnis anzuzeigen, z.B.: <em>{$themepath}icon_prioritymedium.gif</em>.',
    'uservisibilitycustom'      => 'Priorität für bestimmte Benutzergruppen beschränken',
    'desc_uservisibilitycustom' => 'Ist dies aktiviert, kann diese Priorität nur von folgende Benutzergruppen gesehen (und ggf. ausgewählt) werden.',
    'usergroups'                => 'Benutzergruppen',
    'invalidticketpriority'     => 'Ein Problem ist aufgetreten (ungültige Ticket-Priorität - Vergewissern Sie sich, dass die Priorität in der Datenbank vorhanden ist)',
    'editpriority'              => 'Priorität bearbeiten',
    'desc_editpriority'         => '',
    'managepriorities'          => 'Prioritäten',
    'desc_managepriorities'     => '',
    'wineditticketpriority'     => 'Ticket-Priorität bearbeiten: %s',
    'titledelticketpriority'    => 'Ticket-Prioritäten gelöscht (%d)',
    'msgdelticketpriority'      => 'Folgenden Ticket-Prioritäten wurden gelöscht:',
    'titleticketpriorityinsert' => 'Ticket-Priorität %s erstellt',
    'msgticketpriorityinsert'   => 'Die Priorität (%s) wurde erfolgreich erstellt.',
    'titleticketpriorityupdate' => 'Ticket-Priorität %s aktualisiert',
    'msgticketpriorityupdate'   => 'Die Priorität (%s) wurde erfolgreich aktualisiert.',
    'titlemasterprioritydelete' => 'Ticket-Prioritäten konnten nicht gelöscht werden',
    'msgmasterprioritydelete'   => 'Folgende Master-Ticket-Prioritäten konnten nicht gelöscht werden:',

    // Potentialy unused phrases in admin_ticketspriority.php
    'desc_insertpriority'       => '',
    'prioritylist'              => 'Priority List',
    'prioritydetails'           => 'Priority Details',
    'registrationrequired'      => 'Registration Required?',
    'desc_registrationrequired' => 'If enabled, only registered clients will be able to create Tickets with this Priority.',
);

return $__LANG;
