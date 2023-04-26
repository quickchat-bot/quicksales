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
 * @license        http://www.opencart.com.vn/license
 * @link        http://www.opencart.com.vn
 *
 * ###############################################
 */

$__LANG = array (
    // ======= BEGIN v4 LOCALES =======
    'tickets' => 'Tickets',
    'tabgeneral' => 'General',
    'tabpermissions' => 'Permissions',
    'staffvisibilitycustom' => 'Restrict changing tickets to this status',
    'desc_staffvisibilitycustom' =>  'If enabled, only the teams below will be set tickets to this status.',
    'staffgroups' => 'Staff Teams',
    'triggersurvey' => 'Send satisfaction survey email',
    'desc_triggersurvey' => 'When a ticket is changed to this status, an email will be sent to the user asking for their rating and feedback.',
    'statustitle' => 'Status name',
    'desc_statustitle' => 'Enter a name for this status.',
    'statuscolor' => 'Status color',
    'desc_statuscolor' => 'We recommend green for open statuses and dark colors for resolved statuses.',
    'statusbgcolor' => 'Status background color',
    'desc_statusbgcolor' => 'In the staff control panel, the entire ticket properties bar is filled in this color (depending on the ticket status). You can\'t miss it!',
    'displayorder' => 'Display order',
    'desc_displayorder' => 'Ticket statuses are displayed according to their display order, smallest to largest.',
    'displayiconstatus' => 'Icon',
    'desc_displayiconstatus' => 'Upload or link to an icon for this status. {$themepath} can be used to point to the helpdesk images directory, for example: <em>{$themepath}icon_statusopen.gif</em>.',
    'statusdep' => 'Associate status with department',
    'desc_statusdep' => 'Associate this status with a specific department. If you don\'t, then the status will be available to all ticket departments.',
    'statusalldep' => '-- Not Linked --',
    'markasresolved' => 'Is this a <em>resolved</em> ticket status?',
    'desc_markasresolved' => 'If tickets set to this status are resolved or closed (i.e. fixed, resolved, finished), enable this option. It will hide them from open ticket lists.',
    'displayinmainlist' => 'Display in main ticket listing',
    'desc_displayinmainlist' => 'Tickets set to this status will be listed in the main ticket listing.',
    'displaycount' => 'Display ticket count in the ticket navigation tree',
    'desc_displaycount' => 'When enabled, the count of tickets set to this status will be displayed in the staff control panel ticket navigation tree. We recommend that you disable this setting for any resolved statuses.',
    'statustype2' => 'Status type',
    'desc_statustype2' => '<strong>Public</strong> Statuses are visible everywhere and to your end users.<br /><strong>Private</strong> Statuses are visible only to your staff users.',
    'resetduetime' => 'Clear ticket reply deadline when set to this status',
    'desc_resetduetime' => 'When changing tickets to this status, the ticket\'s due time will be automatically cleared.',
    'dispatchnotification' => 'Dispatch Notification to User',
    'desc_dispatchnotification' => 'If Enabled, the system will dispatch a notification to the user/creator whenever this status becomes active for a ticket.',
    'insertstatus' => 'Insert Status',
    'desc_insertstatus' => '',
    'invalidticketstatus' => 'Couldn\'t load ticket status.',
    'managestatus' => 'Statuses',
    'desc_managestatus' => '',
    'wineditticketstatus' => 'Edit Status: %s',
    'editstatus' => 'Edit Status',
    'desc_editstatus' => '',
    'taboptions' => 'Options',
    'titledelticketstatus' => 'Ticket statuses deleted (%d)',
    'msgdelticketstatus' => 'The following ticket statuses were deleted:',
    'titleticketstatusinsert' => 'Ticket status %s created',
    'msgticketstatusinsert' => 'The ticket status %s was created successfully.',
    'titleticketstatusupdate' => 'Ticket status %s updated',
    'msgticketstatusupdate' => 'The ticket status %s was updated successfully.',
    'titlemasterstatusdelete' => 'Unable to delete ticket statuses (%d)',
    'msgmasterstatusdelete' => 'The following master ticket statuses could not be deleted:',
    'tablanguages' => 'Languages: Translation',
    'title_statusinuse'                      => 'Status In Use',
    'message_statusinuse_editassociation'    => 'There are tickets set to this status which belong to the existing linked department. You need to change the status of the tickets before you can update the linked department.',
    'message_statusinuse_createassociation'  => 'There are tickets set to this status which belong to the existing linked department. You need to change the status of the tickets before you can update the linked department.',
    // ======= END v4 LOCALES =======
);


return $__LANG;
