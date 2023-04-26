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
 * @license        http://www.opencart.com.vn/license
 * @link           http://www.opencart.com.vn
 *
 * ###############################################
 */

$__LANG = array(
    'notifications'                  => 'Notifications',
    'manage'                         => 'Manage',
    'ruletitle'                      => 'Rule title',
    'desc_ruletitle'                 => 'Enter a title for the notification rule.',
    'isenabled'                      => 'Notification rule enabled',
    'desc_isenabled'                 => 'Toggle whether this rule is enabled or disabled.',
    'ruletype'                       => 'Type',
    'desc_ruletype'                  => 'The notification rule can be associated with one of the following items.',
    'next'                           => 'Next',
    'creator'                        => 'Creator',
    'wininsertnotification'          => 'Insert Notification',

    'unassigned'                     => '-- Unassigned --',

    'tabgeneral'                     => 'General',
    'tabactions'                     => 'Actions',
    'insertcriteria'                 => 'Insert Criteria',

    'na_email'                       => 'Email',
    'na_sms'                         => 'SMS',
    'na_pool'                        => 'Desktop application',
    'na_staff'                       => 'Own staff user account',
    'na_staffgroup'                  => 'Team',
    'na_department'                  => 'Department',
    'na_user'                        => 'User',
    'nactionemaildispatch'           => 'Send email to the ticket owner\'s...',
    'desc_nactionemaildispatch'      => '',
    'nadispatchemail'                => 'Send email to other email addresses',
    'desc_nadispatchemail'           => '',
    'nadispatchemailstaff'           => 'Send email to other staff',
    'desc_nadispatchemailstaff'      => '',
    'nactionpooldispatch'            => 'Send notification to the active ticket owner\'s...',
    'desc_nactionpooldispatch'       => '',
    'nactionpoolcustomdispatch'      => 'Staff',
    'desc_nactionpoolcustomdispatch' => '',
    'notificationruledesc'           => 'Select a notification rule type.',
    'titlenocriteria'                => 'No criteria specified',
    'msgnocriteria'                  => 'At least one criteria is needed to create a notification rule.',
    'titlenotificationinsert'        => 'Notification rule (%s) created',
    'msgnotificationinsert'          => 'The notification rule (%s) was created successfully.',
    'titlenotificationupdate'        => 'Notification rule (%s) updated',
    'msgnotificationupdate'          => 'The notification rule (%s) was updated successfully.',
    'tabnotifyapp'                   => 'Notify App',
    'tabemail'                       => 'Email',
    'titleenablenotification'        => 'Notification rules enabled (%d)',
    'msgenablenotification'          => 'The following notification rules have been enabled:<br />%s',
    'titledisablenotification'       => 'Notification rules disabled (%d)',
    'msgdisablenotification'         => 'The following notification rules have been disabled:<br />%s',

    'emailprefix'                    => 'Email subject prefix',
    'desc_emailprefix'               => 'Specify an email subject prefix to easily identify the emails sent by this notification rule.',

    /**
     * ---------------------------------------------
     * RULE TYPES
     * ---------------------------------------------
     */
    'nrule_chat'                     => 'Chat',
    'nrule_message'                  => 'Offline Message',
    'nrule_survey'                   => 'Chat Survey',
    'nrule_ticket'                   => 'Ticket',
    'nrule_user'                     => 'User',

    /**
     * ---------------------------------------------
     * RULE CRITERIA: TICKETS
     * ---------------------------------------------
     */

    'ntickettype'                    => 'Ticket type',
    'desc_ntickettype'               => '',
    'nbayesian'                      => 'Bayesian category',
    'desc_nbayesian'                 => 'Tickets that have been matched to a specific Bayesian category.',
    'notapplicable'                  => '-- Not Available --',
    'nticketstatus'                  => 'Ticket status',
    'desc_nticketstatus'             => '',
    'nticketpriority'                => 'Ticket priority',
    'desc_nticketpriority'           => '',
    'nusergroup'                     => 'User group',
    'desc_nusergroup'                => 'Searches for tickets that have recipient\'s belonging to a particular user group.',
    'ndepartment'                    => 'Ticket department',
    'desc_ndepartment'               => 'Tickets that belong to a department.',
    'nowner'                         => 'Ticket owner',
    'desc_nowner'                    => 'Tickets assigned to a particular staff user.',
    'nunassigned'                    => '-- Unassigned --',
    'nactivestaff'                   => '-- Active Staff --',
    'nemailqueue'                    => 'Email queue',
    'desc_nemailqueue'               => 'Tickets that were created or replied to by email via a specific email queue.',
    'nflagtype'                      => 'Ticket flag',
    'desc_nflagtype'                 => '',
    'ncreator'                       => 'Ticket created by',
    'desc_ncreator'                  => '',
    'creatorstaff'                   => 'Staff',
    'creatorclient'                  => 'User',
    'nslaplan'                       => 'SLA plan',
    'desc_nslaplan'                  => 'Tickets that are currently assigned to a particular SLA plan.',
    'ndaterange'                     => 'Ticket creation date <range>',
    'desc_ndaterange'                => 'Tickets that were created within this time frame.',
    'nlastactivityrange'             => 'Ticket last updated <range>',
    'desc_nlastactivityrange'        => 'Tickets that were updated (i.e. replied to by anyone, or any other update event) within this time frame.',
    'nlaststaffreplyrange'           => 'Last reply from staff <range>',
    'desc_nlaststaffreplyrange'      => 'Tickets that received a reply from a staff user within this time frame.',
    'nlastuserreplyrange'            => 'Last reply from user <range>',
    'desc_nlastuserreplyrange'       => 'Tickets that received a reply from a user within this time frame.',
    'nduerange'                      => 'Reply deadline <range>',
    'desc_nduerange'                 => 'Tickets that have a reply deadline within this time frame.',
    'nisoverdue'                     => 'Overdue: Reply overdue',
    'desc_nisoverdue'                => 'Tickets that are overdue (because they were not replied to before the the reply deadline).',
    'nresolutionduerange'            => 'Resolution deadline <range>',
    'desc_nresolutionduerange'       => 'Tickets that have a resolution deadline within this time frame.',
    'nisresolutionoverdue'           => 'Overdue: Resolution overdue',
    'desc_nisresolutionoverdue'      => 'Tickets that are overdue (because they were not resolved before the resolution deadline).',
    'ntemplategroup'                 => 'Template group',
    'desc_ntemplategroup'            => 'Tickets that belong to a particular template group.',
    'ntimeworked'                    => 'Time worked (in minutes)',
    'desc_ntimeworked'               => 'Tickets that match this <strong>time worked</strong> value (as part of ticket billing and time tracking entries).',
    'ntotalreplies'                  => 'Total replies',
    'desc_ntotalreplies'             => 'Tickets that have this many replies.',
    'npendingfollowups'              => 'Pending follow-ups',
    'desc_npendingfollowups'         => 'Tickets that have ticket follow-ups scheduled.',
    'nipaddress'                     => 'IP address',
    'desc_nipaddress'                => 'If a ticket reply has been made from the <strong>support enter</strong>, the helpdesk may have logged an IP address for the user.',
    'nisemailed'                     => 'Created by email',
    'desc_nisemailed'                => 'Tickets which were created via email.',
    'nisedited'                      => 'Has been edited',
    'desc_nisedited'                 => 'Tickets that have been edited.',
    'nhasnotes'                      => 'Has notes',
    'desc_nhasnotes'                 => 'Tickets that have ticket notes.',
    'nhasattachments'                => 'Has attachments',
    'desc_nhasattachments'           => 'Tickets that have file attachments.',
    'nisescalated'                   => 'Has been escalated',
    'desc_nisescalated'              => 'Tickets that have been escalated by at least one escalation rule.',
    'nhasdraft'                      => 'Has a draft',
    'desc_nhasdraft'                 => 'Tickets that have a draft reply saved to them.',
    'nhasbilling'                    => 'Has billing and time tracking entries',
    'desc_nhasbilling'               => 'Tickets that have billing and time tracking entries.',
    'nisphonecall'                   => 'Is a phone ticket',
    'desc_nisphonecall'              => 'Tickets that has been marked as a <em>phone call</em> type ticket.',
    'nislabeled'                     => 'Is tagged',
    'desc_nislabeled'                => 'Tickets that have been tagged.',

    'nticketevent'                   => 'Event',
    'desc_nticketevent'              => '',
    'tevent_newticket'               => 'New ticket created',
    'tevent_newticketnotes'          => 'New ticket note added',
    'tevent_newclientreply'          => 'New reply from end user',
    'tevent_newclientsurvey'         => 'New survey submission',
    'tevent_newstaffreply'           => 'New reply from staff',
    'tevent_ticketassigned'          => 'Ticket assigned',
    'tevent_newuser'                 => 'New user registration',

    'nuserevent'                     => 'Event',
    'desc_nuserevent'                => 'This rule will trigger on this event.',
);

return $__LANG;
