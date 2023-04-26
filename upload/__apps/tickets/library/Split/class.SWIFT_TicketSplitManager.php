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
 * @copyright      Copyright (c) 2001-2012, QuickSupport
 * @license        http://www.opencart.com.vn/license
 * @link           http://www.opencart.com.vn
 *
 * ###############################################
 */

namespace Tickets\Library\Split;

use Base\Models\Attachment\SWIFT_Attachment;
use SWIFT_Exception;
use SWIFT_Library;
use Tickets\Models\Draft\SWIFT_TicketDraft;
use Tickets\Models\FollowUp\SWIFT_TicketFollowUp;
use Tickets\Models\Note\SWIFT_TicketNote;
use Tickets\Models\Note\SWIFT_TicketNoteManager;
use Tickets\Models\Ticket\SWIFT_TicketPost;
use Tickets\Models\TimeTrack\SWIFT_TicketTimeTrack;

/**
 * The Ticket Split Library
 *
 * @author Simaranjit Singh
 */
class SWIFT_TicketSplitManager extends SWIFT_Library
{
    private $_fromTicketID;
    private $_toTicketID;
    private $_startDateline;

    /**
     * @author Simaranjit Singh
     *
     * @param int $_from
     *
     * @return $this
     * @throws SWIFT_Exception
     */
    public function SetFrom($_from)
    {
        if (!is_numeric($_from)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->_fromTicketID = $_from;

        return $this;
    }

    /**
     * @author Simaranjit Singh
     *
     * @param int $_to
     *
     * @return $this
     * @throws SWIFT_Exception
     */
    public function SetTo($_to)
    {
        if (!is_numeric($_to)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->_toTicketID = $_to;

        return $this;
    }

    /**
     * @author Simaranjit Singh
     *
     * @param int $_startFrom
     *
     * @return $this
     * @throws SWIFT_Exception
     */
    public function SetStartDateLine($_startFrom)
    {
        if (!is_numeric($_startFrom)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->_startDateline = $_startFrom;

        return $this;
    }

    /**
     * @author Simaranjit Singh
     *
     * @return $this
     * @throws SWIFT_Exception
     */
    private function SplitPosts()
    {
        $this->Database->AutoExecute(TABLE_PREFIX . SWIFT_TicketPost::TABLE_NAME, array('ticketid' => (int) ($this->_toTicketID)),
            'UPDATE', "ticketid = " . (int) ($this->_fromTicketID) . " AND dateline >= " . (int) ($this->_startDateline));

        return $this;
    }


    /**
     * @author Simaranjit Singh
     *
     * @return $this
     * @throws SWIFT_Exception
     */
    private function SplitAttachments()
    {
        $this->Database->AutoExecute(TABLE_PREFIX . SWIFT_Attachment::TABLE_NAME, array('ticketid' => (int) ($this->_toTicketID)),
            'UPDATE', "ticketid = " . (int) ($this->_fromTicketID) . " AND dateline >= " . (int) ($this->_startDateline));

        return $this;
    }

    /**
     * @author Simaranjit Singh
     *
     * @return $this
     * @throws SWIFT_Exception
     */
    private function SplitTimeTrack()
    {
        $this->Database->AutoExecute(TABLE_PREFIX . SWIFT_TicketTimeTrack::TABLE_NAME, array('ticketid' => (int) ($this->_toTicketID)),
            'UPDATE', "ticketid = " . (int) ($this->_fromTicketID) . " AND dateline >= " . (int) ($this->_startDateline));

        return $this;
    }


    /**
     * @author Simaranjit Singh
     *
     * @return $this
     * @throws SWIFT_Exception
     */
    private function SplitNotes()
    {
        $this->Database->AutoExecute(TABLE_PREFIX . SWIFT_TicketNote::TABLE_NAME, array('linktypeid' => (int) ($this->_toTicketID)),
            'UPDATE', "linktypeid = " . (int) ($this->_fromTicketID) . " AND linktype = " . SWIFT_TicketNoteManager::LINKTYPE_TICKET . " AND dateline >=" . (int) ($this->_startDateline));

        return $this;
    }


    /**
     * @author Simaranjit Singh
     *
     * @return $this
     * @throws SWIFT_Exception
     */
    private function SplitDraft()
    {
        $this->Database->AutoExecute(TABLE_PREFIX . SWIFT_TicketDraft::TABLE_NAME, array('ticketid' => (int) ($this->_toTicketID)),
            'UPDATE', "ticketid = " . (int) ($this->_fromTicketID) . " AND dateline >= " . (int) ($this->_startDateline));

        return $this;
    }

    /**
     * @author Simaranjit Singh
     *
     * @return $this
     * @throws SWIFT_Exception
     */
    private function SplitFollowUp()
    {
        $this->Database->AutoExecute(TABLE_PREFIX . SWIFT_TicketFollowUp::TABLE_NAME, array('ticketid' => (int) ($this->_toTicketID)),
            'UPDATE', "ticketid = " . (int) ($this->_fromTicketID) . " AND dateline >= " . (int) ($this->_startDateline));

        return $this;
    }

    /**
     * @author Simaranjit Singh
     *
     * @return bool
     * @throws SWIFT_Exception
     */
    public function Split()
    {
        if (!is_numeric($this->_fromTicketID) || !is_numeric($this->_toTicketID) || !is_numeric($this->_startDateline)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->SplitPosts()
             ->SplitAttachments()
             ->SplitTimeTrack()
             ->SplitNotes()
             ->SplitDraft()
             ->SplitFollowUp();

        return true;
    }
}
