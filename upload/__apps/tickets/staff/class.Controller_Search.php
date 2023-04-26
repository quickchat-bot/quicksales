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

namespace Tickets\Staff;

use Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel;
use Controller_StaffBase;
use SWIFT;
use SWIFT_DataID;
use SWIFT_Exception;
use Base\Models\SearchStore\SWIFT_SearchStore;
use Tickets\Models\Ticket\SWIFT_Ticket;
use Tickets\Models\Filter\SWIFT_TicketFilter;
use Tickets\Library\Search\SWIFT_TicketSearchManager;
use Tickets\Library\View\SWIFT_TicketViewRenderer;

/**
 * The Search Controller
 *
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @property View_Search $View
 * @author Varun Shoor
 */
class Controller_Search extends Controller_StaffBase
{
    // Core Constants
    const MENU_ID = 2;
    const NAVIGATION_ID = 1;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @throws SWIFT_Exception
     */
    public function __construct()
    {
        parent::__construct(self::TYPE_STAFF);

        $this->Language->Load('staff_ticketsmain');
        $this->Language->Load('staff_ticketsmanage');
        $this->Language->Load('staff_ticketssearch');

        SWIFT_Ticket::LoadLanguageTable();
    }

    /**
     * Loads the Display Data
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function _LoadDisplayData()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UserInterface->AddNavigationBox($this->Language->Get('quickfilter'), SWIFT_TicketViewRenderer::RenderTree('none', -1, -1, -1));

        return true;
    }

    /**
     * Load the Search Data and Show the Ticket List
     *
     * @author Varun Shoor
     * @param array $_ticketIDList The Ticket ID List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function LoadSearch(array $_ticketIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        // If theres only one ticket to load then open it up
        if (count($_ticketIDList) === 1)
        {
            /**
             * BUG FIX: Parminder Singh
             *
             * SWIFT-1444: "Controller_Ticket" Controller has no function declaration for "Index" Action in SWIFT App "tickets" staff (library/class.SWIFT.php:756)
             *
             */
            $this->Load->Controller('Ticket')->Load->Method('View', $_ticketIDList[0]);

            return true;
        }

        SWIFT_SearchStore::DeleteOnType(SWIFT_SearchStore::TYPE_TICKETS, $_SWIFT->Staff->GetStaffID());

        $_searchStoreID = SWIFT_SearchStore::Create($_SWIFT->Session->GetSessionID(), SWIFT_SearchStore::TYPE_TICKETS, $_ticketIDList, $_SWIFT->Staff->GetStaffID());
        if (!_is_array($_ticketIDList))
        {
            SWIFT::Alert($this->Language->Get('titlesearchfailed'), $this->Language->Get('msgsearchfailed'));
        }

        $this->Load->Controller('Manage')->Search($_searchStoreID);

        return true;
    }

    /**
     * Searches for all new tickets of a staff since last visit
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function NewTickets()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($_SWIFT->Staff->GetPermission('staff_tcansearch') == '0')
        {
            SWIFT::Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
            $this->Load->Controller('Manage')->Index();

        } else {
            $_newTicketIDList = SWIFT_TicketSearchManager::RetrieveNewUpdatedTickets($_SWIFT->Staff);

            $this->LoadSearch($_newTicketIDList);
        }

        return true;
    }

    /**
     * Searches for all unresolved tickets for the given owner
     *
     * @author Varun Shoor
     * @param int $_ownerStaffID The Owner Staff ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function UnresolvedOwner($_ownerStaffID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($_SWIFT->Staff->GetPermission('staff_tcansearch') == '0')
        {
            SWIFT::Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
            $this->Load->Controller('Manage')->Index();

        } else {
            $_ticketIDList = SWIFT_TicketSearchManager::RetrieveOnOwner($_SWIFT->Staff, $_ownerStaffID);

            $this->LoadSearch($_ticketIDList);
        }

        return true;
    }

    /**
     * Searches for all unresolved tickets for the given ticket status
     *
     * @author Varun Shoor
     * @param int $_ticketStatusID The Ticket Status ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function UnresolvedStatus($_ticketStatusID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($_SWIFT->Staff->GetPermission('staff_tcansearch') == '0')
        {
            SWIFT::Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
            $this->Load->Controller('Manage')->Index();

        } else {
            $_ticketIDList = SWIFT_TicketSearchManager::RetrieveOnStatus($_SWIFT->Staff, $_ticketStatusID);

            $this->LoadSearch($_ticketIDList);
        }

        return true;
    }

    /**
     * Searches for all unresolved tickets for the given ticket type
     *
     * @author Varun Shoor
     * @param int $_ticketTypeID The Ticket Type ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function UnresolvedType($_ticketTypeID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($_SWIFT->Staff->GetPermission('staff_tcansearch') == '0')
        {
            SWIFT::Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
            $this->Load->Controller('Manage')->Index();

        } else {
            $_ticketIDList = SWIFT_TicketSearchManager::RetrieveOnType($_SWIFT->Staff, $_ticketTypeID);

            $this->LoadSearch($_ticketIDList);
        }

        return true;
    }

    /**
     * Searches for all unresolved tickets for the given ticket priority
     *
     * @author Varun Shoor
     * @param int $_ticketPriorityID The Ticket Priority ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function UnresolvedPriority($_ticketPriorityID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($_SWIFT->Staff->GetPermission('staff_tcansearch') == '0')
        {
            SWIFT::Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
            $this->Load->Controller('Manage')->Index();

        } else {
            $_ticketIDList = SWIFT_TicketSearchManager::RetrieveOnPriority($_SWIFT->Staff, $_ticketPriorityID);

            $this->LoadSearch($_ticketIDList);
        }

        return true;
    }

    /**
     * Searches for all overdue tickets of a staff
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Overdue()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($_SWIFT->Staff->GetPermission('staff_tcansearch') == '0')
        {
            SWIFT::Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
            $this->Load->Controller('Manage')->Index();

        } else {
            $_overdueTicketIDList = SWIFT_TicketSearchManager::RetrieveOverdueTickets($_SWIFT->Staff);

            $this->LoadSearch($_overdueTicketIDList);
        }

        return true;
    }

    /**
     * Lookup the Ticket ID
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function TicketID()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($_SWIFT->Staff->GetPermission('staff_tcansearch') == '0')
        {
            SWIFT::Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
            $this->Load->Controller('Manage')->Index();

        } else {
            $_ticketIDList = array();

            if (!empty($_POST['query'])) {
                $_ticketIDList = SWIFT_TicketSearchManager::SearchTicketID(trim($_POST['query']), $_SWIFT->Staff);
            }

            $this->LoadSearch($_ticketIDList);
        }

        return true;
    }

    /**
     * Lookup the Creator
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Creator()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($_SWIFT->Staff->GetPermission('staff_tcansearch') == '0')
        {
            SWIFT::Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
            $this->Load->Controller('Manage')->Index();

        } else {
            $_ticketIDList = array();

            if (!empty($_POST['query'])) {
                $_ticketIDList = SWIFT_TicketSearchManager::SearchCreator(trim($_POST['query']), $_SWIFT->Staff);
            }

            $this->LoadSearch($_ticketIDList);
        }

        return true;
    }

    /**
     * Do a quick search
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function QuickSearch()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($_SWIFT->Staff->GetPermission('staff_tcansearch') == '0')
        {
            SWIFT::Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
            $this->Load->Controller('Manage')->Index();

        } else {

            $_ticketIDList = array();

            if (!empty($_POST['query'])) {
                $_ticketIDList = SWIFT_TicketSearchManager::QuickSearch(trim($_POST['query']), $_SWIFT->Staff);
            }

            $this->LoadSearch($_ticketIDList);
        }

        return true;
    }

    /**
     * The Search Form Renderer
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Advanced()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_LoadDisplayData();

        $this->UserInterface->Header($this->Language->Get('tickets') . ' > ' . $this->Language->Get('search'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('staff_tcansearch') == '0')
        {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render();
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Search processor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function SearchSubmit()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($_SWIFT->Staff->GetPermission('staff_tcansearch') == '0')
        {
            SWIFT::Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
            $this->Load->Controller('Manage')->Index();

        } else {
            $_ticketIDList = SWIFT_TicketSearchManager::SearchRules($_POST['rulecriteria'], $_POST['criteriaoptions'], $_SWIFT->Staff);

            $this->LoadSearch($_ticketIDList);
        }

        return true;
    }

    /**
     * Search processor
     *
     * @author Varun Shoor
     * @param int $_ticketFilterID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Filter($_ticketFilterID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_TicketFilterObject = new SWIFT_TicketFilter(new SWIFT_DataID($_ticketFilterID));
        if (!$_SWIFT_TicketFilterObject instanceof SWIFT_TicketFilter || !$_SWIFT_TicketFilterObject->GetIsClassLoaded())
        {
            // @codeCoverageIgnoreStart
            // this code will never be executed
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }

        $_SWIFT_TicketFilterObject->UpdateLastActivity();

        $_ruleCriteria = array();
        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketfilterfields WHERE ticketfilterid = '" .
                 ($_ticketFilterID) . "' ORDER BY ticketfilterfieldid ASC");
        while ($this->Database->NextRecord())
        {
            $_ruleCriteria[] = array($this->Database->Record['fieldtitle'], $this->Database->Record['fieldoper'], $this->Database->Record['fieldvalue']);
        }

        $_ticketIDList = SWIFT_TicketSearchManager::SearchRules($_ruleCriteria, $_SWIFT_TicketFilterObject->GetProperty('criteriaoptions'), $_SWIFT->Staff);

        $this->LoadSearch($_ticketIDList);

        return true;
    }
}
