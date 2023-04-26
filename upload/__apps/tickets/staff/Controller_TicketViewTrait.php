<?php
/**
 *  *
 * QuickSupport Classic
 * _______________________________________________
 *
 * @author        Werner Garcia <werner.garcia@crossover.com>
 *
 * @copyright     Copyright (c) 2001-2018, Trilogy
 * @license       http://opencart.com.vn/license
 * @link          http://opencart.com.vn
 *
 *  */

namespace Tickets\Staff;

use SWIFT;
use SWIFT_Exception;
use Base\Models\Rating\SWIFT_Rating;
use Base\Library\Rating\SWIFT_RatingRenderer;
use Tickets\Models\Ticket\SWIFT_Ticket;
use Tickets\Models\Ticket\SWIFT_TicketLinkedTable;
use Tickets\Models\Ticket\SWIFT_TicketPost;
use Tickets\Library\View\SWIFT_TicketViewRenderer;

trait Controller_TicketViewTrait {
    /**
     * View a ticket
     *
     * @author Varun Shoor
     * @param int|string $_ticketID The Ticket ID
     * @param string $_listType (OPTIONAL) The List Type
     * @param int $_departmentID (OPTIONAL) The Department ID
     * @param int $_ticketStatusID (OPTIONAL) The Ticket Status ID
     * @param int $_ticketTypeID (OPTIONAL) The Ticket Type ID
     * @param int $_ticketLimitOffset (OPTIONAL) The offset to display ticket posts on
     * @param int $_ticketLimitOffsetIncoming (OPTIONAL) The Incoming offset
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function View($_ticketID = '', $_listType = 'inbox', $_departmentID = -1, $_ticketStatusID = -1, $_ticketTypeID = -1, $_ticketLimitOffset = null, $_ticketLimitOffsetIncoming = -2) {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_ticketID)) {
            $this->Load->Controller('Manage')->Index();

            return false;
        }

        $_ticketLimitOffsetIncoming =  ($_ticketLimitOffsetIncoming);
        if ($_ticketLimitOffsetIncoming !== -2)
        {
            $_ticketLimitOffset = $_ticketLimitOffsetIncoming;
        }

        $_SWIFT_TicketObject = SWIFT_Ticket::GetObjectOnID($_ticketID);

        // Did the object load up?
        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded()) {
            $this->UserInterface->Header($this->Language->Get('tickets') . ' > ' . $this->Language->Get('viewticket'), self::MENU_ID,
                self::NAVIGATION_ID);
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
            $this->UserInterface->Footer();

            return false;
        }

        // Check permission
        /*
         * Improvement - Bishwanath Jha
         * Improvement - Mansi Wason <mansi.wason@opencart.com.vn>
         *
         * SWIFT-1339 Restrict staff to be able to view assigned tickets ONLY
         * SWIFT-5157 Improving error message when creating ticket from Staff CP in a department not assigned to staff
         *
         * Comments : Check if restriction for Group (Can View unassigned Tickets, Can View All Tickets) is set?
         */
        if (!$_SWIFT_TicketObject->CanAccess($_SWIFT->Staff) || $_SWIFT->Staff->GetPermission('staff_tcanviewtickets') == '0' ||
            ($_SWIFT_TicketObject->GetProperty('ownerstaffid') == 0 && $_SWIFT->Staff->GetPermission('staff_tcanviewunassign') == '0') ||
            ($_SWIFT_TicketObject->GetProperty('ownerstaffid') != 0 && $_SWIFT_TicketObject->GetProperty('ownerstaffid') != $_SWIFT->Staff->GetStaffID() && $_SWIFT->Staff->GetPermission('staff_tcanviewall') == '0')
        ) {
            $this->UserInterface->Header($this->Language->Get('tickets') . ' > ' . $this->Language->Get('viewticket'), self::MENU_ID,
                self::NAVIGATION_ID);
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm1'));
            $this->UserInterface->Footer();

            return false;

        }

        if (is_numeric($_ticketLimitOffset)) {
            $_ticketLimitOffset =  ($_ticketLimitOffset);
        } else {
            $_ticketLimitOffset = 0;
        }

        $_SWIFT_UserObject = $_SWIFT_TicketObject->GetUserObject();
        $_SWIFT_UserOrganizationObject = $_SWIFT_TicketObject->GetUserOrganizationObject();

        $_ticketLinkedTableContainer = SWIFT_TicketLinkedTable::RetrieveOnTicket($_SWIFT_TicketObject);

        SWIFT::Set('ticketurlsuffix', $_listType . '/' . $_departmentID . '/' . $_ticketStatusID . '/' . $_ticketTypeID . '/' . $_ticketLimitOffset);

        // Ticket Post Processing
        $_variableContainer = array();
        $_ticketPostCount = $_SWIFT_TicketObject->GetTicketPostCount();

        $_ticketPostOffset =  ($_ticketLimitOffset);
        $_ticketPostLimitCount = false;
        if ($this->Settings->Get('t_enpagin') == '1') {
            $_ticketPostLimitCount = (int) ($this->Settings->Get('t_postlimit'));
        }

        $_ticketPostOrder = 'ASC';
        if ($this->Settings->Get('t_postorder') === 'desc') {
            $_ticketPostOrder = 'DESC';
        }

        if ($_ticketPostOffset < 0) {
            $_ticketPostOffset = 0;

            $_ticketPostLimitCount = $_ticketPostCount;
        }

        $_ticketPostContainer = $_SWIFT_TicketObject->GetTicketPosts($_ticketPostOffset, $_ticketPostLimitCount, $_ticketPostOrder);

        $_participantsContainer = $_participantHTMLContainer = array();
        $_userImageUserIDList = $_staffImageUserIDList = array();
        foreach ($_ticketPostContainer as $_ticketPostID => $_SWIFT_TicketPostObject) {
            if ($_SWIFT->Staff->GetPermission('staff_canviewusers') != '0' && $_SWIFT_TicketPostObject->GetProperty('creator') == SWIFT_Ticket::CREATOR_USER &&
                $_SWIFT_TicketPostObject->GetProperty('userid') != '0' && !in_array($_SWIFT_TicketPostObject->GetProperty('userid'), $_userImageUserIDList)) {
                $_userImageUserIDList[] = $_SWIFT_TicketPostObject->GetProperty('userid');
            } else if ($_SWIFT_TicketPostObject->GetProperty('creator') == SWIFT_Ticket::CREATOR_STAFF &&
                $_SWIFT_TicketPostObject->GetProperty('staffid') != '0' && !in_array($_SWIFT_TicketPostObject->GetProperty('staffid'), $_staffImageUserIDList)) {
                $_staffImageUserIDList[] = $_SWIFT_TicketPostObject->GetProperty('staffid');
            }

            // Participants
            $_userHash = md5($_SWIFT_TicketPostObject->GetProperty('creator') . ':' . $_SWIFT_TicketPostObject->GetProperty('userid') . ':' . $_SWIFT_TicketPostObject->GetProperty('fullname'));
            $_staffHash = md5($_SWIFT_TicketPostObject->GetProperty('creator') . ':' . $_SWIFT_TicketPostObject->GetProperty('staffid') . ':' . $_SWIFT_TicketPostObject->GetProperty('fullname'));
            if ($_SWIFT_TicketPostObject->GetProperty('creator') == SWIFT_TicketPost::CREATOR_USER)
            {
                if (in_array($_userHash, $_participantsContainer))
                {
                    continue;
                }

                $_participantHTMLContainer[] = array($this->Language->Get('badgeuser'), $_SWIFT_TicketPostObject->GetProperty('fullname'));

                $_participantsContainer[] = $_userHash;
            } else if ($_SWIFT_TicketPostObject->GetProperty('creator') == SWIFT_TicketPost::CREATOR_STAFF) {
                if (in_array($_staffHash, $_participantsContainer))
                {
                    continue;
                }

                $_participantHTMLContainer[] = array($this->Language->Get('badgestaff'), $_SWIFT_TicketPostObject->GetProperty('fullname'));
                $_participantsContainer[] = $_staffHash;

            } else if ($_SWIFT_TicketPostObject->GetProperty('creator') == SWIFT_TicketPost::CREATOR_THIRDPARTY) {
                if (in_array($_userHash, $_participantsContainer))
                {
                    continue;
                }

                $_participantHTMLContainer[] = array($this->Language->Get('badgethirdparty'), $_SWIFT_TicketPostObject->GetProperty('fullname'));
                $_participantsContainer[] = $_userHash;

            } else if ($_SWIFT_TicketPostObject->GetProperty('creator') == SWIFT_TicketPost::CREATOR_CC || $_SWIFT_TicketPostObject->GetProperty('creator') == SWIFT_TicketPost::CREATOR_BCC) {
                if (in_array($_userHash, $_participantsContainer))
                {
                    continue;
                }

                $_participantHTMLContainer[] = array($this->Language->Get('badgecc'), $_SWIFT_TicketPostObject->GetProperty('fullname'));
                $_participantsContainer[] = $_userHash;

            }
        }

        $_variableContainer = array('_ticketPostOffset' => $_ticketPostOffset, '_ticketPostLimitCount' => $_ticketPostLimitCount, '_ticketPostContainer' => $_ticketPostContainer,
            '_userImageUserIDList' => $_userImageUserIDList, '_staffImageUserIDList' => $_staffImageUserIDList, '_ticketPostCount' => $_ticketPostCount);


        // Ratings
        $_ratingContainer = SWIFT_Rating::Retrieve(array(SWIFT_Rating::TYPE_TICKET), $_SWIFT->Staff, false, $_SWIFT_TicketObject->GetProperty('departmentid'));

        $this->View->RenderInfoBox($_SWIFT_TicketObject, $_SWIFT_UserObject, $_SWIFT_UserOrganizationObject);
        $this->View->RenderParticipantBox($_SWIFT_TicketObject, $_participantHTMLContainer);
        $this->View->RenderWorkflowBox($_SWIFT_TicketObject, $_ticketLinkedTableContainer);

        if ($_SWIFT->Staff->GetPermission('staff_canviewratings') != '0')
        {
            SWIFT_RatingRenderer::RenderNavigationBox(array(SWIFT_Rating::TYPE_TICKET), $_SWIFT_TicketObject->GetTicketID(), '/Tickets/Ticket/Rating/' . $_SWIFT_TicketObject->GetTicketID(), $_ratingContainer);
        }

        $this->UserInterface->AddNavigationBox($this->Language->Get('quickfilter'), SWIFT_TicketViewRenderer::RenderTree($_listType, $_departmentID,
            $_ticketStatusID, $_ticketTypeID));
        $this->UserInterface->Header(sprintf($this->Language->Get('viewticketext'), $_SWIFT_TicketObject->GetTicketDisplayID(), $this->Emoji->Decode($_SWIFT_TicketObject->GetProperty('subject'))),
            self::MENU_ID, self::NAVIGATION_ID);
        $this->View->RenderTicket($_SWIFT_TicketObject, $_SWIFT_UserObject, $_listType, $_departmentID, $_ticketStatusID, $_ticketTypeID, $_ticketLimitOffset, $_variableContainer);
        $this->UserInterface->Footer();
        return true;
    }
}
