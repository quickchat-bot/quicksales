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

namespace Tickets\Models\Note;

use SWIFT;
use SWIFT_Exception;
use SWIFT_Model;
use Tickets\Models\Ticket\SWIFT_Ticket;
use Tickets\Models\Ticket\SWIFT_TicketPost;

/**
 * The Ticket Post Note Class
 *
 * @author Varun Shoor
 */
class SWIFT_TicketPostNote extends SWIFT_TicketNoteManager
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

        if ($this->GetProperty('linktype') != self::LINKTYPE_TICKETPOST)
        {
            throw new SWIFT_Note_Exception(SWIFT_INVALIDDATA);
        }
    }

    /**
     * Create a new Ticket Post Note
     *
     * @author Varun Shoor
     * @param SWIFT_TicketPost $_SWIFT_TicketPostObject The SWIFT_TicketPost Object pointer
     * @param int $_forStaffID The Staff ID for which this note is for (0 = Visible to all)
     * @param int $_staffID The Staff ID
     * @param string $_staffName The Staff Name
     * @param string $_noteContents The Note Contents
     * @param int $_noteColor (OPTIONAL) The Note Color
     * @return mixed "_ticketNoteID" on Success, "false" otherwise
     * @throws SWIFT_Note_Exception If Invalid Data is Provided
     */
    public static function Create($_SWIFT_TicketPostObject, $_forStaffID, $_staffID, $_staffName, $_noteContents, $_noteColor = 1, $_ = null)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_SWIFT_TicketPostObject instanceof SWIFT_TicketPost || !$_SWIFT_TicketPostObject->GetIsClassLoaded())
        {
            throw new SWIFT_Note_Exception(SWIFT_INVALIDDATA);
        }

        $_ticketNoteID = parent::Create(self::LINKTYPE_TICKETPOST, $_SWIFT_TicketPostObject->GetTicketPostID(), $_forStaffID, $_staffID, $_staffName, $_noteContents, $_noteColor);

        return $_ticketNoteID;
    }

    /**
     * Delete notes on Ticket Post ID list
     *
     * @author Varun Shoor
     * @param array $_ticketPostIDList The Ticket Post ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteOnTicketPost($_ticketPostIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_ticketPostIDList))
        {
            return false;
        }

        $_ticketNoteIDList = array();
        $_SWIFT->Database->Query("SELECT ticketnoteid FROM " . TABLE_PREFIX . "ticketnotes WHERE linktype = '" .  (self::LINKTYPE_TICKETPOST) . "' AND linktypeid IN (" . BuildIN($_ticketPostIDList) . ")");
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
