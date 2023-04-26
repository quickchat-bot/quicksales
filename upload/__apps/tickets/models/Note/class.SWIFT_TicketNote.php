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

namespace Tickets\Models\Note;

use SWIFT;
use SWIFT_Exception;
use Tickets\Models\Ticket\SWIFT_Ticket;
use Tickets\Models\Note\SWIFT_TicketNoteManager;

/**
 * The Ticket Note Class
 *
 * @author Varun Shoor
 */
class   SWIFT_TicketNote extends SWIFT_TicketNoteManager
{
    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param int $_ticketNoteID The Ticket Note ID
     */
    public function __construct($_ticketNoteID)
    {
        parent::__construct($_ticketNoteID);

        if ($this->GetProperty('linktype') != self::LINKTYPE_TICKET)
        {
            throw new SWIFT_Note_Exception(SWIFT_INVALIDDATA);
        }
    }

    /**
     * Create a new Ticket Note
     *
     * @author Varun Shoor
     * @param SWIFT_Ticket $_SWIFT_TicketObject The SWIFT_Ticket Object pointer
     * @param int $_forStaffID The Staff ID for which this note is for (0 = Visible to all)
     * @param int $_staffID The Staff ID
     * @param string $_staffName The Staff Name
     * @param string $_noteContents The Note Contents
     * @param int $_noteColor (OPTIONAL) The Note Color
     * @return mixed "_ticketNoteID" on Success, "false" otherwise
     * @throws SWIFT_Note_Exception If Invalid Data is Provided
     */
    public static function Create($_SWIFT_TicketObject, $_forStaffID, $_staffID, $_staffName, $_noteContents, $_noteColor = 1, $_ = null)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded())
        {
            throw new SWIFT_Note_Exception(SWIFT_INVALIDDATA);
        }

        $_ticketNoteID = parent::Create(self::LINKTYPE_TICKET, $_SWIFT_TicketObject->GetTicketID(), $_forStaffID, $_staffID, $_staffName, $_noteContents, $_noteColor);

        $_SWIFT_TicketObject->MarkHasNotes();

        return $_ticketNoteID;
    }

    /**
     * Delete notes on Ticket ID list
     *
     * @author Varun Shoor
     * @param array $_ticketIDList The Ticket ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteOnTicket($_ticketIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_ticketIDList))
        {
            return false;
        }

        $_ticketNoteIDList = array();
        $_SWIFT->Database->Query("SELECT ticketnoteid FROM " . TABLE_PREFIX . "ticketnotes WHERE linktype = '" .  (self::LINKTYPE_TICKET) .
                "' AND linktypeid IN (" . BuildIN($_ticketIDList) . ")");
        while ($_SWIFT->Database->NextRecord())
        {
            $_ticketNoteIDList[] = (int) ($_SWIFT->Database->Record['ticketnoteid']);
        }

        if (!count($_ticketNoteIDList))
        {
            return false;
        }

        self::DeleteList($_ticketNoteIDList);

        return true;
    }
}
?>
