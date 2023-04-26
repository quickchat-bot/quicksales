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
    'autoclose'                    => 'Auto Close',
    'manage'                       => 'Manage',
    'targetstatus'                 => 'Status to change to',
    'tabgeneral'                   => 'General',

    'insertrule'                   => 'Insert Rule',
    'editrule'                     => 'Edit Rule',

    'ruletitle'                    => 'Rule title',
    'desc_ruletitle'               => 'Enter a title for this auto close rule.',
    'targetticketstatus'           => 'Change status to',
    'desc_targetticketstatus'      => 'Once the inactivity time has passed (see below), tickets matching this rule will be set to this status (this should be a <strong>resolved</strong> status).',
    'inactivitythreshold'          => 'Inactivity threshold',
    'desc_inactivitythreshold'     => 'If no update is made to a ticket within this number of hours, the ticket will be considered to be inactive. This is the first step towards automatically closing a ticket.',
    'closurethreshold'             => 'Closure threshold',
    'desc_closurethreshold'        => 'If a ticket has been marked as inactive and still no update is received within this number of hours, the ticket will be automatically set to the status you specified above.',
    'isenabled'                    => 'Rule is enabled',
    'desc_isenabled'               => 'Toggle whether this rule is enabled or not.',
    'sortorder'                    => 'Execution order',
    'desc_sortorder'               => 'It is possible to create multiple auto close rules. The execution order determines which rules are executed first, smallest to largest.',
    'sendpendingnotification'      => 'Send inactivity notification email',
    'desc_sendpendingnotification' => 'The helpdesk can notify the user that their ticket has been marked as inactive and if no reply is received, will be closed.',
    'sendfinalnotification'        => 'Send final ticket closed notification email',
    'desc_sendfinalnotification'   => 'The helpdesk can also notify the user that their ticket has been closed.',
    'suppresssurveyemail'          => 'Suppress customer survey email',
    'desc_suppresssurveyemail'     => 'If you have enabled customer satisfaction survey invitations when a ticket is set to the status specified above, you may wish to prevent the helpdesk from sending a survey invitation for automatically closed tickets.',

    'insertcriteria'               => 'Insert Criteria',


    'titleautocloseruledel'        => 'Auto close rules deleted (%d)',
    'msgautocloseruledel'          => 'The following auto close rules were deleted:',
    'titleautocloseruleenable'     => 'Auto close rules enabled (%d)',
    'msgautocloseruleenable'       => 'The following auto close rules were enabled:',
    'titleautocloseruledisable'    => 'Auto close rules disabled (%d)',
    'msgautocloseruledisable'      => 'The following auto close rules were disabled:',
    'titleautocloseruleinsert'     => 'Auto close rule created',
    'msgautocloseruleinsert'       => 'The auto close rule (%s) was created successfully.',
    'titleautocloseruleupdate'     => 'Auto close rule updated',
    'msgautocloseruleupdate'       => 'The auto close rule (%s) was updated successfully.',

    'titlenocriteriaadded'         => 'No criteria specified',
    'msgnocriteriaadded'           => 'You need to specify at least one criteria to create an auto close rule (otherwise, the helpdesk won\'t know which tickets to auto close).',

    /**
     * ---------------------------------------------
     * Rule Criterias
     * ---------------------------------------------
     */
    'notapplicable'                => '-- NA --',
    'articketstatusid'             => 'Status',
    'desc_articketstatusid'        => '',
    'arpriorityid'                 => 'Priority',
    'desc_arpriorityid'            => '',
    'ardepartmentid'               => 'Department',
    'desc_ardepartmentid'          => '',
    'arownerstaffid'               => 'Owner',
    'desc_arownerstaffid'          => '',
    'aremailqueueid'               => 'Email queue',
    'arusergroupid'                => 'User group',
    'desc_arusergroupid'           => '',
    'desc_aremailqueueid'          => '',
    'arflagtype'                   => 'Flag',
    'desc_arflagtype'              => '',
    'arbayescategoryid'            => 'Bayesian category',
    'desc_arbayescategoryid'       => 'Tickets that have been matched to a specific Bayesian category.',
    'arcreator'                    => 'Creator',
    'desc_arcreator'               => '',
    'creatorstaff'                 => 'Staff',
    'creatorclient'                => 'User',
    'arunassigned'                 => '-- Unassigned --',
    'artemplategroupid'            => 'Template group',
    'desc_artemplategroupid'       => '',
    'artickettypeid'               => 'Type',
    'desc_rtickettypeid'           => '',
);

return $__LANG;
