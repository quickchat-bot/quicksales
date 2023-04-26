<?php
/**
 * ###############################################
 *
 * SWIFT Framework
 * _______________________________________________
 *
 * @author        Varun Shoor
 *
 * @package        SWIFT
 * @copyright    Copyright (c) 2001-2012, QuickSupport
 * @license        http://www.kayako.com/license
 * @link        http://www.kayako.com
 *
 * ###############################################
 */

$__LANG = array (
    'tickets' => 'Tickets',
    'insertpriority' => 'Insert Priority',
    'tabgeneral' => 'General',
    'tabpermissions' => 'Permissions',
    'tablanguages' => 'Languages',
    'prioritytitle' => 'Priority title',
    'desc_prioritytitle' => 'For example, <em>High</em> or <em>Level 1</em>.',
    'prioritytype' => 'Priority type',
    'desc_prioritytype' => '<strong>Private</strong> Priorities are not visible to end users and only to staff users.<br /><strong>Public</strong> Priorities are visible to both.',
    'forecolor' => 'Foreground color',
    'desc_forecolor' => 'Optionally specify a foreground (text) color for the priority.',
    'bgcolor' => 'Background color',
    'desc_bgcolor' => 'Optionally specify a background (shading) color for the priority (make sure that the foreground color above will be visible over the background color!).',
    'displayorder' => 'Display order',
    'desc_displayorder' => 'Priorities are listed according to their display order, smallest to largest.',
    'displayiconpriority' => 'Icon',
    'desc_displayiconpriority' => 'Upload or link to an icon for this priority. {$themepath} can be used to point to the helpdesk images directory, for example: <em>{$themepath}icon_prioritymedium.gif</em>.',
    'uservisibilitycustom' => 'Restrict priority to specific user groups',
    'desc_uservisibilitycustom' =>  'If enabled, only the user groups below will be able to see (and if applicable, select) this priority.',
    'usergroups' => 'User Groups',
    'invalidticketpriority' => 'A problem was encountered (invalid ticket priority - make sure that the priority exists in database)',
    'editpriority' => 'Edit Priority',
    'desc_editpriority' => '',
    'managepriorities' => 'Priorities',
    'desc_managepriorities' => '',
    'wineditticketpriority' => 'Edit Ticket Priority: %s',
    'titledelticketpriority' => 'Ticket priorities deleted (%d)',
    'msgdelticketpriority' => 'The following ticket priorities were deleted:',
    'titleticketpriorityinsert' => 'Ticket priority %s created',
    'msgticketpriorityinsert' => 'The priority (%s) was created successfully.',
    'titleticketpriorityupdate' => 'Ticket priority %s updated',
    'msgticketpriorityupdate' => 'The priority (%s) was updated successfully.',
    'titlemasterprioritydelete' => 'Ticket priorities couldn\'t be deleted',
    'msgmasterprioritydelete' => 'Unable to delete the following master ticket priorities:',
    
    // Potentialy unused phrases in admin_ticketspriority.php
    'desc_insertpriority' => '',
    'prioritylist' => 'Priority List',
    'prioritydetails' => 'Priority Details',
    'registrationrequired' => 'Registration Required?',
    'desc_registrationrequired' => 'If enabled, only registered clients will be able to create Tickets with this Priority.',
);

return $__LANG;
