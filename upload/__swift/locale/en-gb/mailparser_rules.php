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
    // ======= BEGIN v4 LOCALES =======
    'isenabled'                 => 'Email parser rule enabled',
    'desc_isenabled'            => 'Email rules will be ignored if disabled.',
    'insertcriteria'            => 'Insert Criteria',
    'tabactions'                => 'Actions',
    'titlenoaction'             => 'No actions specified',
    'msgnoaction'               => 'At least one action that the email rule will perform (if it matches) needs to be specified.',
    'if'                        => 'If',
    'titleinsertrule'           => 'Email rule (%s) created',
    'msginsertrule'             => 'Email rule (%s) created successfully.',
    'titleupdaterule'           => 'Email rule (%s) updated',
    'msgupdaterule'             => 'Successfully updated email parser rule "%s".',
    'titledelprules'            => 'Email rules deleted (%d)',
    'msgdelprules'              => 'The following email rules were deleted:',
    'titleenableprules'         => 'Email rules enabled (%d)',
    'msgenableprules'           => 'The following email rules were enabled:',
    'titledisableprules'        => 'Email rules disabled (%d)',
    'msgdisableprules'          => 'The following email rules were disabled:',
    // ======= END v4 LOCALES =======

    'mailparser'                => 'Email Parser',

    'desc_parserrules'          => '',

    // Operators
    'opcontains'                => 'Contains',
    'opnotcontains'             => 'Doesn\'t contain',
    'opequal'                   => 'Equal',
    'opnotequal'                => 'Not equal',
    'opgreater'                 => 'Greater than',
    'opless'                    => 'Less than',
    'opregexp'                  => 'Regular expression',
    'strue'                     => 'True',
    'sfalse'                    => 'False',

    // Criteria
    'psendername'               => 'Sender name',
    'desc_psendername'          => 'The sender name is parsed from the "From:" header of the email message.',

    'psenderemail'              => 'Sender email address',
    'desc_psenderemail'         => 'The sender email address is parsed from the "From:" header of the email message. Example: <em>From: <span class="tabletitle">Sender Name</span> <senderemail@domain.com></em>',

    'pdestinationname'          => 'Destination (recipient) name',
    'desc_pdestinationname'     => 'The destination name is parsed from the "To:" header of the email. The destination name is usually the name of the staff user that has replied to the ticket (unless configured otherwise).',

    'pdestinationemail'         => 'Destination (recipient) email address',
    'desc_pdestinationemail'    => 'The destination email address is parsed from the "To:" header of the email message. This is usually the address belonging to the email queue that the reply is being sent to.',

    'preplytoname'              => 'Reply-to name',
    'desc_preplytoname'         => 'The reply-to name is parsed from the "Reply-To:" header of the email message.',

    'preplytoemail'             => 'Reply-to email address',
    'desc_preplytoemail'        => 'The reply-to email address is parsed from the "Reply-To:" header of the email message.',

    'psubject'                  => 'Subject',
    'desc_psubject'             => 'Matches the email subject.',

    'precipients'               => 'Recipients',
    'desc_precipients'          => 'Matches the email recipients. There can be multiple recipients to which an incoming email was sent, including in the "To:" and "CC:" headers of the email message.',

    'pbody'                     => 'Body',
    'desc_pbody'                => 'Matches the remaining contents of the email body after HTML (unless configured otherwise) and breaklines have been stripped out.',

    'pbodysize'                 => 'Body size',
    'desc_pbodysize'            => 'The total size of the body content of the email in bytes.',

    'ptextbody'                 => 'Plain-text body',
    'desc_ptextbody'            => 'An email can have two content types; <em>plain-text</em>, <em>HTML</em> or <em>both</em>. This rule searches the <em>plain-text</em> version of the body only. Note that not all emails are sent in <em>both</em> formats.',

    'phtmlbody'                 => 'HTML body',
    'desc_phtmlbody'            => 'An email can have two content types; <em>plain-text</em>, <em>HTML</em> or <em>both</em>. This rule searches the <em>HTML</em> version of the body only. Note that not all emails are sent in <em>both</em> formats.',

    'ptextbodysize'             => 'Text body size',
    'desc_ptextbodysize'        => 'An email can have two content types; <em>plain-text</em>, <em>HTML</em> or <em>both</em>. This rule matches the total size of the <em>plain-text</em> body content of the email in bytes.',

    'phtmlbodysize'             => 'HTML body size',
    'desc_phtmlbodysize'        => 'An email can have two content types; <em>plain-text</em>, <em>HTML</em> or <em>both</em>. This rule matches the total size of the <em>HTML</em> body content of the email in bytes.',

    'pattachmentname'           => 'Attachment names',
    'desc_pattachmentname'      => 'Matches attachment names of attachments that have been accepted into the helpdesk (as defined in the admin control panel).',

    'pattachmentsize'           => 'Attachment size',
    'desc_pattachmentsize'      => 'Matches attachment sizes of attachments that have been accepted into the helpdesk (as defined in the admin control panel).',

    'ptotalattachmentsize'      => 'Total attachment size',
    'desc_ptotalattachmentsize' => 'Matches the <em>total size</em> (in bytes) of attachments that have been accepted into the helpdesk (as defined in the admin control panel).',

    'pisreply'                  => 'Is a user reply',
    'desc_pisreply'             => 'If the incoming email is a reply to an an existing ticket by an end user, this rule will match.',

    'pisthirdparty'             => 'Is third party recipient reply',
    'desc_pisthirdparty'        => 'If the incoming email is a reply to an existing ticket from an address that has been added to the ticket as a third-party recipient, this rule will match.',

    'pfloodprotection'          => 'Flood protection triggered',
    'desc_pfloodprotection'     => 'This rule will match if the helpdesk\'s flood protection has been triggered.',

    'pisstaffreply'             => 'Is a staff reply',
    'desc_pisstaffreply'        => 'If the incoming email is a reply to an existing ticket from a staff user\'s email address, this rule will match.',

    'pticketstatus'             => 'Ticket status (post-parse)',
    'desc_pticketstatus'        => 'If the incoming email is a reply to an existing ticket, the ticket\'s status will be matched by this rule.',

    'pticketemailqueue'         => 'Email queue',
    'desc_pticketemailqueue'    => 'If the incoming email is retrieved from the specified queue, this rule will match.',

    'ptickettype'               => 'Ticket type (post-parse)',
    'desc_ptickettype'          => 'If the incoming email is a reply to an existing ticket, the ticket\'s type will be matched by this rule.',

    'pticketpriority'           => 'Ticket priority (post-parse)',
    'desc_pticketpriority'      => 'If the incoming email is a reply to an existing ticket, the ticket\'s priority will be matched by this rule.',

    'pticketusergroup'          => 'Ticket user group (post-parse)',
    'desc_pticketusergroup'     => 'If the incoming email is a reply to an existing ticket, the ticket\'s user group will be matched by this rule.',

    'pticketdepartment'         => 'Ticket department (post-parse)',
    'desc_pticketdepartment'    => 'If the incoming email is a reply to an existing ticket, the ticket\'s department will be matched by this rule.',

    'pticketowner'              => 'Ticket owner (post-parse)',
    'desc_pticketowner'         => 'If the incoming email is a reply to an existing ticket, the ticket\'s owner will be matched by this rule.',
    'prunassigned'              => '-- Unassigned --',

    'pticketflagtype'           => 'Ticket flag (post-parse)',
    'desc_pticketflagtype'      => 'If the incoming email is a reply to an existing ticket, the ticket\'s flag will be matched by this rule.',

    'pbayescategory'            => 'Bayesian category',
    'desc_pbayescategory'       => 'The helpdesk runs all incoming email through the Bayesian filter, and assigns the ticket a Bayesian category with the highest score. You can use this to filter spam email or train the system to automatically route tickets based on its contents, using custom Bayesian categories.',

    // Insert Rule
    'insertrule'                => 'Insert Rule',
    'iruletype'                 => 'Email rule type: ',
    'ipreparse'                 => 'Before the email is processed (pre-parse)',
    'ipostparse'                => 'After the email is processed (post-parse)',

    'paignore'                  => 'Ignore email',
    'desc_paignore'             => 'The email will be ignored by the helpdesk and will not be processed into a ticket or a reply.',
    'panoautoresp'              => 'Do not send autoresponder',
    'desc_panoautoresp'         => 'An autoresponder confirming receipt of new email (as a new ticket or reply) will not be sent.',
    'panoalerts'                => 'Do not process notification rules',
    'desc_panoalerts'           => 'Helpdesk notification rules will not be processed if enabled.',
    'pnochange'                 => '-- No Change --',
    'pcstaff'                   => 'Assign the ticket',
    'desc_pcstaff'              => 'The ticket (whether a new one or a reply to an existing one) will be assigned to this staff user.',
    'pcstatus'                  => 'Change ticket status',
    'desc_pcstatus'             => 'The ticket will be changed to this status.',
    'pcpriority'                => 'Change ticket priority',
    'desc_pcpriority'           => 'The ticket will be changed to this priority.',
    'pcdepartment'              => 'Move ticket to department',
    'desc_pcdepartment'         => 'The ticket will be moved to this department.',
    'pcslaplan'                 => 'Change ticket SLA plan',
    'desc_pcslaplan'            => 'The ticket will be assigned this SLA plan.',
    'pcmovetotrash'             => 'Move to trash',
    'desc_pcmovetotrash'        => 'The ticket will be moved to Trash',
    'pcflag'                    => 'Flag ticket',
    'desc_pcflag'               => 'The ticket will be flagged this color.',
    'paddnotes'                 => 'Add a note to the ticket',
    'desc_paddnotes'            => 'A note will be added to the ticket.',
    'pcforward'                 => 'Forward the email',
    'desc_pcforward'            => 'The email will be forwarded to this address.',
    'preply'                    => 'Reply to the email',
    'desc_preply'               => 'The email will be automatically responded to with this message.',
    'panoticket'                => 'Do not process as a ticket reply',
    'desc_panoticket'           => 'Even if the email is a reply to an existing ticket, the email will not be added to the existing ticket and a new ticket will be created.',
    'pctickettype'              => 'Change ticket type',
    'desc_pctickettype'         => 'The ticket type will be changed to the one specified here.',
    'pcaddtags'                 => 'Add tags to ticket',
    'desc_pcaddtags'            => 'These tags will be added to the ticket.',
    'pcremovetags'              => 'Remove tags from ticket',
    'desc_pcremovetags'         => 'If the ticket has any of these tags, they will be removed.',
    'pcprivate'                 => 'Make the ticket reply private',
    'desc_pcprivate'            => 'This ticket reply will be marked as private and visible only to other staff users (this applies to ticket replies from staff only).',

    // Edit Rules
    'editrule'                  => 'Edit Rule',
    'ptitle'                    => 'Email rule title',
    'desc_ptitle'               => 'For example, <em>PayPal receipts</em>.',
    'pstop'                     => 'Stop processing other rules',
    'desc_pstop'                => 'If this rule is matched first, no other email rules will be processed after this one.',

    // Manage Rules
    'managerules'               => 'Rules',
    'ruletitle'                 => 'Rule title',
    'sortorder'                 => 'Execution order',
    'desc_sortorder'            => 'It is possible that multiple email rules can match for the same email. The helpdesk will process the rules according to the execution order number, smallest to largest.',
    'ruletype'                  => 'Rule type',
    'creationdate'              => 'Created',

    // Potentialy unused phrases in mailparser_rules.php
    'smatchtype'                => 'Match Type',
    'matchtype'                 => 'Criteria Options',
    'desc_matchtype'            => 'Select the grouping method for the criteria fields.',
    'smatchall'                 => 'Match <b>All</b> Criteria (AND)',
    'smatchany'                 => 'Match <b>Any</b> Criteria (OR)',
    'criteria'                  => 'Criteria',
    'newcriteria'               => 'New Criteria',
    'ruleinsertconfirm'         => 'Parser rule "%s" inserted successfully',
    'ruleupdateconfirm'         => 'Parser rule "%s" updated successfully',
    'invalidrule'               => 'ERROR: Invalid email parser rule',
    'ruledelconfirm'            => 'Parser rule deleted successfully',
    'rulelist'                  => 'Rule List',
    'notapplicable'             => '-- NA --',

);

return $__LANG;
