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
 * @copyright    Copyright (c) 2001-2012, Kayako
 * @license        http://www.kayako.com/license
 * @link        http://www.kayako.com
 *
 * ###############################################
 */

$__LANG = array(
    'titlemasterviewdel' => 'Unable to Delete "%d" View(s)',
    'msgmasterviewdel' => 'Kayako is unable to delete the following master ticket view(s):',
    'manageviews' => 'Views',
    'insertview' => 'Insert View',
    'viewtitle' => 'Ticket view title',
    'desc_viewtitle' => 'Enter a title for the view (numbers, letters and spaces only).',
    'viewscope' => 'View Scope',
    'createdby' => 'Creator',
    'editview' => 'Edit View',
    'titleticketviewinsert' => 'Ticket view (%s) created',
    'msgticketviewinsert' => 'The ticket view (%s) was created successfully.',
    'titleticketviewupdate' => 'Ticket view (%s) updated',
    'msgticketviewupdate' => 'The ticket view (%s) was upated successfully.',
    'titleviewfieldempty' => 'No ticket view columns were specified',
    'msgviewfieldempty' => 'You need to specify at least one column for a ticke view (otherwise there wouldn\'t be anything to display!).',

    'viewlinkdepartment' => 'Link this view with departments',
    'desc_viewlinkdepartment' => 'This view can be automatically loaded when browsing tickets within one of the departments selected here.',

    'taboptions' => 'Options',
    'tabcolumns' => 'Columns',

    'viewscope_global' => 'All staff',
    'viewscope_private' => 'Just me',
    'viewscope_team' => 'My team',

    'viewunassigned' => 'Unassigned',
    'viewassigned' => 'Assigned To Me',
    'viewalltickets' => 'All Tickets',

    'viewassignedfield' => 'Display tickets that are assigned to',
    'desc_viewassignedfield' => '<strong>Unassigned</strong> Tickets that are not assigned to anyone.<br /><strong>Assigned to me</strong> Tickets that are assigned to you.<br /><strong>All tickets</strong> All tickets, whether assigned to you, someone else or not assigned.',
    'viewscope' => 'Visibility',
    'desc_viewscope' => '<strong>All staff</strong> All staff will be able to use this view.<br /><strong>Just me</strong> The view will only be available to you.<br /><strong>My team</strong> The view will be available to you and others in your staff team.',

    'viewselectfields' => 'Select columns (drag, drop and organize into the yellow box)',
    'viewsortoptions' => 'Sort Options',
    'viewsortby' => 'Sort by',
    'desc_viewsortby' => 'How will tickets initially be sorted when this view is in use?',
    'viewsortorder' => 'Sort order',
    'desc_viewsortorder' => 'Tickets can be sorted ascending (smallest or largest, most recent to least recent, etc) or descending.',
    'adminviewticket'         => 'The number of tickets per page is too high.',
    'desc_adminviewticket'    => 'Staff ticket views are currently limited to showing %s tickets per page. Contact your administrator to increase this limit.',
    'viewticketsperpage'      => 'Tickets to display per page',
    'desc_viewticketsperpage' => 'Specify the number of tickets to display before breaking the view into multiple pages. Note: This value cannot exceed the default maximum your administrator has set for staff views.',
    'generaloptions' => 'General Options',
    'viewautorefresh' => 'Automatically refresh the ticket listing',
    'desc_viewautorefresh' => 'When this view is loaded, the ticket listing can be automatically refreshed. Useful for wallboard type uses.',
    'autorefresh_disable' => '-- Disable Auto Refresh --',
    'autorefresh_30s' => 'Refresh every 30 seconds',
    'autorefresh_1m' => 'Refresh every 1 minute',
    'autorefresh_5m' => 'Refresh every 5 minutes',
    'autorefresh_15m' => 'Refresh every 15 minutes',
    'autorefresh_30m' => 'Refresh every 30 minutes',
    'autorefresh_1h' => 'Refresh every 1 hour',
    'viewsetasowner' => 'Automatically set ticket owner to active staff user',
    'desc_viewsetasowner' => 'When replying or forwarding a ticket, the ticket owner will be automatically set to the active staff user if this view is in use.',
    'viewdefaultstatusonreply' => 'Default ticket status when replying to a ticket',
    'desc_viewdefaultstatusonreply' => 'Specify which status a ticket will be automatically set to when a staff user who is using this view replies to or forwards a ticket.',
    'defaultstatus_unspecified' => '-- Unspecified --',

    'afterreply_topticketlist' => 'Top level ticket list (view all tickets)',
    'afterreply_activeticketlist' => 'Active ticket list (within the current department or search results)',
    'afterreply_ticket' => 'The active ticket',
    'afterreply_nextticket' => 'Next available ticket',
    'viewafterreplyaction' => 'After a ticket has been replied to, take me to',
    'desc_viewafterreplyaction' => 'Select where Kayako should take you (or whomever is using this view) after replying to or forwarding a ticket.',

    'sort_asc' => 'Ascending',
    'sort_desc' => 'Descending',

    // Potentialy unused phrases in staff_ticketview.php
    'viewfilteroptions' => 'Filter Options',
    'viewfilterdepartments' => 'Filter Department(s)',
    'desc_viewfilterdepartments' => 'Kayako by default will display tickets from all/selected department. However, you can use this option to filter the tickets based on the selected departments.',
    'viewfiltertype' => 'Filter Ticket Type(s)',
    'desc_viewfiltertype' => 'You can use this option to filter the tickets based on the selected ticket type(s).',
    'viewfilterstatus' => 'Filter Ticket Status(es)',
    'desc_viewfilterstatus' => 'You can use this option to filter the tickets based on the selected ticket status(es).',
);


return $__LANG;
