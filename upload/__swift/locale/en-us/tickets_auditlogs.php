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
    'duetimecleared' => '- CLEARED -',

    // Activity Log
    'log_newreply' => 'Replied to ticket: #%s',
    'log_forward' => 'Forwarded ticket: #%s to: %s',
    'log_newticket' => 'Created ticket: #%s: %s',

    // Audit Log
    'al_newticket' => 'New ticket created by %s (%s): subject: %s', // DONE
    'al_newticket_queue' => 'New ticket created by %s (%s): subject: %s, email queue: %s', // DONE
    'al_newreply' => 'New reply created by %s (%s)', // DONE
    'al_watch' => 'Ticket watch activated: #%s by: %s', // DONE
    'al_owner' => 'Ticket owner changed from: %s to: %s', // DONE
    'al_priority' => 'Ticket priority changed from: %s to: %s', // DONE
    'al_rating' => 'Rating %d added by: %s',
    'al_status' => 'Ticket status changed from: %s to: %s', // DONE
    'al_statusautoclose' => 'Ticket status changed from: %s to: %s by auto close', // DONE
    'al_type' => 'Ticket type changed from: %s to: %s', // DONE
    'al_department' => 'Ticket department changed from: %s to: %s', // DONE
    'al_due' => 'Setting due time to: %s', // DONE
    'al_resolutiondue' => 'Setting resolution due time to: %s', // DONE
    'al_sla' => 'SLA plan set to: %s', // DONE
    'al_slaclear' => 'SLA plan associated deleted', // DONE
    'al_flag' => 'Ticket flagged: %s', // DONE
    'al_flagclear' => 'Ticket flag cleared', // DONE
    'al_duestaffoverdue' => 'Ticket marked as overdue', // DONE
    'al_duestaffclear' => 'Ticket due time cleared', // DONE
    'al_resduestaffclear' => 'Ticket resolution due time cleared', // DONE
    'al_ban' => 'Ticket creator banned: ticket ID: #%s, email address: %s', // DONE
    'al_merge' => 'Ticket merged: ticket ID: #%s, subject: %s, name: %s, email adress: %s', // DONE
    'al_untrashticket' => 'Ticket moved back from trash: ticket ID: #%s, subject: %s, name: %s, email address: %s', // DONE
    'al_deleteticket' => 'Deleted ticket: ticket ID: #%s, subject: %s, name: %s, email address: %s', // DONE
    'al_trashticket' => 'Ticket moved to trash: ticket ID: #%s, subject: %s, name: %s, email address: %s', // DONE
    'al_updateticketpost' => 'Ticket post updated: creator: %s (%s)', // DONE
    'al_deleteticketpost' => 'Ticket post deleted:  creator: %s (%s)', // DONE
    'al_ticketnote' => 'Ticket note added to ticket', // DONE
    'al_usernote' => 'User note added to ticket', // DONE
    'al_deletenote' => 'Ticket note deleted', // DONE
    'al_updatenote' => 'Ticket note updated', // DONE
    'al_delbilling' => 'Ticket billing entry deleted', // DONE
    'al_ticketbilling' => 'Ticket billing entry added to ticket', // DONE
    'al_updticketbilling' => 'Updated ticket billing entry', // DONE
    'al_delfollowup' => 'Ticket follow-up deleted', // DONE
    'al_createfollowup' => 'Created ticket follow-up', // DONE
    'al_updateproperties' => 'Updated ticket properties: subject: %s > %s, name: %s > %s, email address: %s > %s',

    'al_newforward' => 'New forward message created by %s (%s)',
    'al_escalated' => 'Ticket escalated under rule: %s',
    'al_print' => 'Ticket printed by: %s',
    'al_cleardraft' => 'Ticket draft cleared by: %s',
    'al_savedraft' => 'Ticket draft saved by: %s',
    'al_forward' => 'Ticket forwarded to: %s by: %s',
    'al_recipientdel' => 'Ticket recipient: %s deleted by: %s',
    'al_timetrackdel' => 'Time tracking entry #%s deleted by: %s',
    'al_timetrack' => 'Time tracking entry added for: %s by: %s (time worked: %s billable time: %s)',
    'al_prule' => 'Processed under email rule: %s',
    'al_statusac' => 'Ticket status changed from: %s to: %s by auto close',
    'al_xmlexport' => 'Ticket exported as XML by: %s',
    'al_pdfexport' => 'Ticket exported as PDF by: %s',
);

return $__LANG;
