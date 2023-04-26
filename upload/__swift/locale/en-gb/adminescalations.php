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
    // ======= BEGIN V4 LOCALES =======
    'escalations'                 => 'Escalations',
    'insertrule'                  => 'Insert Rule',
    'ruletitle'                   => 'Rule title',
    'desc_ruletitle'              => 'Enter a title for the escalation rule, for example: <em>Overdue tickets</em>.',
    'escalationruledetails'       => 'Escalation Rule Details',
    'nochange'                    => '-- No Change --',
    'escalationplan'              => 'SLA plan',
    'desc_escalationplan'         => 'This escalation rule will trigger if a ticket goes overdue according to the SLA plan specified here.',
    'escalationaction'            => 'Actions',
    'escalationstaff'             => 'Assign ticket to a staff user',
    'desc_escalationstaff'        => '',
    'escalationpriority'          => 'Change ticket priority',
    'desc_escalationpriority'     => '',
    'escalationticketstatus'      => 'Change ticket status',
    'desc_escalationticketstatus' => '',
    'escalationdepartment'        => 'Move ticket to a department',
    'desc_escalationdepartment'   => '',
    'escalationslaplan'           => 'Change the ticket\'s SLA plan',
    'desc_escalationslaplan'      => '',
    'escalationflagtype'          => 'Flag the ticket',
    'desc_escalationflagtype'     => '',
    'noplanavailable'             => '-- Not Available --',
    'sendnotification'            => 'Send an email notification to the user',
    'desc_sendnotification'       => '',
    'titlenoplanavail'            => 'No SLA plans found',
    'msgnoplanavail'              => 'There needs to be at least one SLA plan before you can create an escalation rule, because an escalation rule determines what happens to a ticket which violates an SLA plan.',
    'creationdate'                => 'Creation Date',
    'wineditescalationrule'       => 'Edit Escalation Rule: %s',
    'invalidescalationrule'       => 'A problem was encountered (invalid escalation rule - make sure that the rule exists in the database)',
    'editrule'                    => 'Edit Rule',
    'desc_editrule'               => '',
    'titledelescalationrule'      => 'Escalation rules deleted (%d)',
    'msgdelescalationrule'        => 'The following escalation rules were deleted:',
    'titleinsertescalationrule'   => 'Escalation rule created',
    'msginsertescalationrule'     => 'The escalation rule (%s) was created successfully.',
    'titleupdateescalationrule'   => 'Escalation rule updated',
    'msgupdateescalationrule'     => 'The escalation rule (%s) was updated successfully.',
    'manageescalations'           => 'Manage',
    'desc_manageescalations'      => '',
    'tabnotifications'            => 'Notifications',
    'insertnotification'          => 'Insert Notification',
    'escalationtickettype'        => 'Change ticket type',
    'desc_escalationtickettype'   => '',
    'escalationaddtags'           => 'Tag the ticket',
    'desc_escalationaddtags'      => '',
    'escalationremovetags'        => 'Remove tags from the ticket (if set)',
    'desc_escalationremovetags'   => '',
    'eruletype'                   => 'Escalation type',
    'desc_eruletype'              => 'This escalation rule will trigger when a ticket goes overdue according to the SLA plan specified above.<br /><br /><strong>Reply overdue</strong> This escalation rule will trigger when a ticket\'s reply deadline lapses.<br /><strong>Resolution overdue</strong> Triggers when the resolution deadline lapses.<br /><strong>Either</strong> Triggers when either the reply or resolution deadlines lapse.',
    'eruletype_due'               => 'Reply overdue',
    'eruletype_resolutiondue'     => 'Resolution overdue',
    'eruletype_both'              => 'Either reply or resolution overdue',
    // ======= END V4 LOCALES =======
);

return $__LANG;
