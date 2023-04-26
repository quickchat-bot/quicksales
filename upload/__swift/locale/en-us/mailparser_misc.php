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


$__LANG = array (
    // ======= BEGIN v4 LOCALES =======
    'mpsettings' => 'Settings',
    'desc_mpsettings' => '',
    'tabsettings' => 'Settings',
    'mailparser' => 'Email Parser',
    


    'titleinsertban' => 'Email address banned',
    'msginsertban' => 'The email address %s was banned and the email parser will no longer process email from this address.',
    'titleupdateban' => 'Email address ban updated',
    'msgupdateban' => 'The email address ban for %s was updated successfully.',
    'wineditban' => 'Edit Parser Ban',
    'titledelbans' => 'Email address bans lifted (%d)',
    'msgdelbans' => 'The following email address bans were deleted:',

    // Email Bans
    'emailbans' => 'Email Bans',
    'emailbanlist' => 'Email Ban List',
    'bannedemail' => 'Email address',
    'bannedby' => 'Banned by',
    'insertban' => 'Insert Ban',
    'insertnewban' => 'Insert New Ban',
    'desc_bannedemail' => 'Enter the email address to ban. Any incoming email messages from this address will be ignored by the helpdesk. To ban an entire domain name, enter <em>@thedomainname.com</em>.',
    'parserbaninsertconfirm' => 'Email ban "%s" inserted successfully',
    'parserbansdelconfirm' => 'Email ban(s) deleted successfully',
    'invalidemailban' => 'Invalid email address ban ID specified',
    'editban' => 'Edit Ban',
    'bandelconfirmmsg' => 'Are you sure you wish to delete this email ban entry?  This action is irreversible!',
    'parserbanupdconfirm' => 'Email address ban for %s updated',
    'desc_mailbans' => 'You can prevent problem users from submitting tickets by adding an email ban for their address.  Whenever Kayako receives an email message, it will check the author\'s address against the ban list, and if the address is banned, the message will be ignored without notice.',

    // Catch-All Rules
    'insertcatchallrule' => 'Insert Catch-all Rule',
    'titlenqcatchall' => 'Configure email queues before creating catch-all rules',
    'msgnqcatchall' => 'Catch-all rules determine what happens to email coming into the helpdesk. Without any email queues configured, email cannot come into the helpdesk.',
    'wineditcatchall' => 'Edit Catch-all Rule',
    'titledelcatchall' => 'Catch-all rules deleted (%d)',
    'msgdelcatchall' => 'The following catch-all rules were deleted:<br>',
    'titleupdatecatchall' => 'Catch-all rule (%s) updated',
    'msgupdatecatchall' => 'The catch-all rule (%s) was updated successfully.',
    'titleinsertcatchall' => 'Catch-all rule created',
    'msginsertcatchall' => 'The catch-all rule (%s) was created successfully.',
    'catchallrules' => 'Catch-all Rules',
    'desc_catchallrules' => 'Catch-all rules use regular expressions to match the addresses of incoming email messages.  The messages can then be routed to whichever email queue you wish. For more information on regular expressions, see <a href="http://www.php.net/preg_match" target="_blank" rel="noopener noreferrer" id="red">preg_match()</a>.',
    'insertrule' => 'Insert Rule',
    'insertnewrule' => 'Insert New Rule',
    'editrule' => 'Edit Rule',
    'rregexp' => 'Pattern match',
    'desc_rregexp' => 'Enter a regular expression to match against the sender\'s email address. If it matches, this catch-all rule will trigger. For example:<br /><br /><strong>/^(.*)(\@yourdomain\.com)$/i</strong> Matches any email address that has a domain of yourdomain.com (*@yourdomain.com)<br /><strong>/^(support\@)(.*)$/i</strong> Matches support@*',
    'sortorder' => 'Execution order',
    'desc_sortorder' => 'Catch-all rules will be executed according to their execution order number, smallest to largest (1 will execute before 5).',
    'emailqueue' => 'Route to email queue',
    'desc_emailqueue' => 'Incoming email that is caught by this catch-all rule will be routed to this email queue.',
    'ruletitle' => 'Rule title',
    'desc_ruletitle' => 'For example, <em>Rule to catch anything @acme.com</em>.',
    'noemailqueueadd' => '-- No email queues configured --',
    'editcatchallrule' => 'Edit Catch-All Rule',
    'emailqueue' => 'Destination email queue',
    
    // Potentialy unused phrases in mailparser_misc.php
    'invalidemailaddress' => 'Invalid email address specified',

    'errnoqueue' => 'ERROR: You do not have any email queues in the database. You need to have at least one email queue to utilize catch-all rules.',
    'catchruleinsertconfirm' => 'Catch-all rule "%s" inserted successfully',
    'catchruleupdconfirm' => 'Catch-all rule "%s" updated successfully',
    'catchallruledelconfirm' => 'Catch-all rule(s) deleted successfully',
    'invalidcatchalrule' => 'Invalid catch all rule specified',
    'cruledelconfirmmsg' => 'Are you sure you wish to delete this catch-all rule?  This action is irreversible!',
);

return $__LANG;
