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
 * @copyright      Copyright (c) 2001-2014, QuickSupport
 * @license        http://www.kayako.com/license
 * @link           http://www.kayako.com
 *
 * ###############################################
 */

$__LANG = array(
    'tsticketid'                    => 'Ticket ID',
    'desc_tsticketid'               => 'The numeric ID of a ticket (this is used internally by the helpdesk - you may be looking for <strong>Ticket mask ID</strong> instead).',
    'tsticketmaskid'                => 'Ticket mask ID',
    'desc_tsticketmaskid'           => 'This is the regular, external ticket ID (ABC-123-4567).',
    'tsfullname'                    => 'Recipient\'s name',
    'desc_tsfullname'               => 'Search the names of recipients participating in a ticket (searches tickets and individual ticket replies).',
    'tsemail'                       => 'Recipient\'s email address',
    'desc_tsemail'                  => 'Search tickets by the recipient\'s email addresses.',
    'tslastreplier'                 => 'Last replier\'s name',
    'desc_tslastreplier'            => 'Search the name of the last person to reply to a ticket.',
    'tsreplyto'                     => 'Reply-to',
    'desc_tsreplyto'                => 'The reply-to email as specified in the email header. This is only valid for tickets created by email.',
    'tssubject'                     => 'Ticket subject',
    'desc_tssubject'                => 'Searches the subject of a ticket.',
    'tsmessage'                     => 'Ticket contents',
    'desc_tsmessage'                => 'Searches the contents of ticket replies.',
    'tsmessagelike'                 => 'Ticket reply contents (using SQL LIKE)',
    'desc_tsmessagelike'            => 'Searches message contents using the SQL LIKE search method.',
    'tsuser'                        => 'User account name or email addresses',
    'desc_tsuser'                   => 'Search for tickets created by users (with names or email addresses that match this criteria).',
    'tsuserorganization'            => 'User organisation',
    'desc_tsuserorganization'       => 'Search the organisation name of users and recipients participating in tickets.',
    'tsipaddress'                   => 'IP address',
    'desc_tsipaddress'              => 'If a ticket reply has been made from the <strong>support enter</strong>, the helpdesk may have logged an IP address for the user.',
    'tscharset'                     => 'Character set',
    'desc_tscharset'                => 'Tickets of a particular character set.',
    'tsphone'                       => 'Phone number',
    'desc_tsphone'                  => 'Searches the phone number of users and recipients participating in tickets.',
    'tstimeworked'                  => 'Time worked',
    'desc_tstimeworked'             => 'Search tickets by the time worked in seconds (in ticket billing and time tracking entries).',
    'tstimebilled'                  => 'Time billable',
    'desc_tstimebilled'             => 'Search tickets by the time billable in seconds (in ticket billing and time tracking entries).',
    'tsdepartment'                  => 'Department',
    'desc_tsdepartment'             => 'Tickets that belong to a department.',
    'tsowner'                       => 'Owner',
    'desc_tsowner'                  => 'Tickets assigned to a particular staff user.',
    'tstype'                        => 'Type',
    'desc_tstype'                   => '',
    'tsstatus'                      => 'Status',
    'desc_tsstatus'                 => '',
    'tspriority'                    => 'Priority',
    'desc_tspriority'               => '',
    'tsemailqueue'                  => 'Email queue',
    'desc_tsemailqueue'             => 'Tickets that were created or replied to by email via a specific email queue.',
    'tsslaplan'                     => 'SLA plan',
    'desc_tsslaplan'                => 'Tickets that are currently assigned to a particular SLA plan.',
    'tsflag'                        => 'Flag',
    'desc_tsflag'                   => '',
    'tstemplategroup'               => 'Template group',
    'desc_tstemplategroup'          => 'Tickets that belong to a particular template group.',
    'tsescalation'                  => 'Escalated by rule',
    'desc_tsescalation'             => 'Search for tickets that have been escalated by a specific escalation rule.',
    'tsbayesian'                    => 'Bayesian category',
    'desc_tsbayesian'               => 'Tickets that have been matched to a specific Bayesian category.',
    'tsusergroup'                   => 'User group',
    'desc_tsusergroup'              => 'Searches for tickets that have recipient\'s belonging to a particular user group.',
    'tscreator'                     => 'Ticket created by',
    'desc_tscreator'                => '',
    'tscreationmode'                => 'Creation mode',
    'desc_tscreationmode'           => 'Search for tickets by how the ticket was created.',
    'tsdue'                         => 'Reply deadline',
    'desc_tsdue'                    => 'Tickets that have a reply deadline before or after this time.',
    'tsduerange'                    => 'Reply deadline <range>',
    'desc_tsduerange'               => 'Tickets that have a reply deadline within this time frame.',
    'tsresolutiondue'               => 'Resolution deadline',
    'desc_tsresolutiondue'          => 'Tickets that have a resolution deadline before or after this time.',
    'tsresolutionduerange'          => 'Resolution deadline <range>',
    'desc_tsresolutionduerange'     => 'Tickets that have a resolution deadline within this time frame.',
    'tscreationdate'                => 'Creation date',
    'desc_tscreationdate'           => 'Tickets that were created before or after this time.',
    'tscreationdaterange'           => 'Creation date <range>',
    'desc_tscreationdaterange'      => 'Tickets that were created within this time frame.',
    'tslastactivity'                => 'Last updated',
    'desc_tslastactivity'           => 'Tickets that were updated (i.e. replied to by anyone, or any other update event) before or after this time.',
    'tslastactivityrange'           => 'Last updated <range>',
    'desc_tslastactivityrange'      => 'Tickets that were updated (i.e. replied to by anyone, or any other update event) within this time frame.',
    'tslaststaffreply'              => 'Last reply from staff',
    'desc_tslaststaffreply'         => 'Tickets that received a reply from a staff user within this time frame.',
    'tslaststaffreplyrange'         => 'Last reply from staff <range>',
    'desc_tslaststaffreplyrange'    => 'Tickets that received a reply from a staff user within this time frame.',
    'tslastuserreply'               => 'Last reply from user',
    'desc_tslastuserreply'          => 'Tickets that received a reply from a user before or after this time.',
    'tslastuserreplyrange'          => 'Last reply from user <range>',
    'desc_tslastuserreplyrange'     => 'Tickets that received a reply from a user within this time frame.',
    'tsescalateddate'               => 'Escalated date',
    'desc_tsescalateddate'          => 'Tickets that have been escalated (went overdue) before or after this time.',
    'tsescalateddaterange'          => 'Escalated date <range>',
    'desc_tsescalateddaterange'     => 'Tickets that have been escalated (went overdue) within this time frame.',
    'tsresolutiondate'              => 'Resolution deadline',
    'desc_tsresolutiondate'         => 'Tickets that have a resolution deadline before or after this time.',
    'tsresolutiondaterange'         => 'Resolution deadline <range>',
    'desc_tsresolutiondaterange'    => 'Tickets that have a resolution deadline within this time frame.',
    'tsreopendate'                  => 'Reopen date',
    'desc_tsreopendate'             => 'Search for tickets by when they were reopened (changed from a <strong>resolved</strong> to an <strong>open</strong> status) before or after this time.',
    'tsreopendaterange'             => 'Reopen Date <range>',
    'desc_tsreopendaterange'        => 'Search for tickets by when they were reopened (changed from a <strong>resolved</strong> to an <strong>open</strong> status) within this time frame.',
    'tsedited'                      => 'Has been edited',
    'desc_tsedited'                 => 'Tickets that have been edited.',
    'tseditedby'                    => 'Edited by',
    'desc_tseditedby'               => 'Search for tickets that have been edited by a particular staff user.',
    'tsediteddate'                  => 'Edited date',
    'desc_tsediteddate'             => 'Tickets that have been edited before or after this time.',
    'tsediteddaterange'             => 'Edited date <range>',
    'desc_tsediteddaterange'        => 'Tickets that have been edited within this time frame.',
    'tstotalreplies'                => 'Total replies',
    'desc_tstotalreplies'           => 'Tickets that have this many replies.',
    'tshasnotes'                    => 'Ticket has notes',
    'desc_tshasnotes'               => '',
    'tshasattachments'              => 'Ticket has attachments',
    'desc_tshasattachments'         => '',
    'tsisemailed'                   => 'Created by email',
    'desc_tsisemailed'              => 'Tickets which were created via email.',
    'tshasdraft'                    => 'Ticket has a draft',
    'desc_tshasdraft'               => 'Tickets that have a draft reply saved to them.',
    'tshasfollowup'                 => 'Pending follow-ups',
    'desc_tshasfollowup'            => 'Tickets that have ticket follow-ups scheduled.',
    'tsislinked'                    => 'Ticket is linked to another',
    'desc_tsislinked'               => 'Search for tickets that have been linked to another',
    'tsisfirstcontactresolved'      => 'Resolved on first contact',
    'desc_tsisfirstcontactresolved' => 'Search for tickets that were resolved on first reply (by a staff user).',
    'tsaverageresponsetime'         => 'Average response time',
    'desc_tsaverageresponsetime'    => 'Search for tickets which have a particular average response time (between user and staff replies).',
    'tsescalationlevelcount'        => 'Number of escalations',
    'desc_tsescalationlevelcount'   => 'Search for tickets by how many times they have been escalated (gone overdue).',
    'tswasreopened'                 => 'Ticket has been reopened',
    'desc_tswasreopened'            => 'Search for tickets that have been reopened (changed from a <strong>resolved</strong> to an <strong>open</strong> status).',
    'tsisresolved'                  => 'Ticket is resolved',
    'desc_tsisresolved'             => 'Search for tickets that have been resolved (set to a <strong>resolved</strong> status).',
    'tsresolutionlevel'             => 'Number of ticket owners before resolution',
    'desc_tsresolutionlevel'        => 'Search for tickets by how many owners the ticket had before it was resolved.',
    'tsticketnotes'                 => 'Ticket note contents',
    'desc_tsticketnotes'            => 'Search the contents of ticket notes.',
    'tsgeneraloptions'              => 'General ticket criteria',
    'tsdateoptions'                 => 'Date criteria',
    'tsmiscellaneous'               => 'Misc criteria',
    'tstag' => 'Tag',
    'desc_tstag' => 'Tickets that belong to a particular tag.',

    /**
     * ---------------------------------------------
     * OTHER LOCALES
     * ---------------------------------------------
     */
    'notapplicable'                 => '-- Not Applicable --',
    'lookup'                        => 'Lookup',
);


return $__LANG;
