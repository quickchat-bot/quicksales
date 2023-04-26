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
use Base\Library\Rules\SWIFT_Rules;
use SWIFT_Session;
use Base\Models\Staff\SWIFT_StaffActivityLog;
use Tickets\Models\Ticket\SWIFT_Ticket;
use Tickets\Models\Filter\SWIFT_TicketFilter;
use Tickets\Library\Search\SWIFT_TicketSearch;
use Tickets\Library\View\SWIFT_TicketViewRenderer;
use Base\Library\UserInterface\SWIFT_UserInterface;

/**
 * The Ticket Filter Controller
 *
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @property View_Filter $View
 * @property Controller_Filter $Load
 * @author Varun Shoor
 */
class Controller_Filter extends Controller_StaffBase
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

        $this->Language->Load('staff_ticketfilters');
        $this->Language->Load('staff_ticketsmain');
        $this->Language->Load('staff_ticketsmanage');
        $this->Language->Load('staff_ticketssearch');

        SWIFT_Ticket::LoadLanguageTable();
    }

    /**
     * Delete the Ticket Filters from Mass Action
     *
     * @author Varun Shoor
     * @param mixed $_ticketFilterIDList The Ticket Filter ID List Container Array
     * @param bool $_byPassCSRF Whether to bypass the CSRF check
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function DeleteList($_ticketFilterIDList, $_byPassCSRF = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash']))
        {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('staff_tcandeletefilters') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        if (_is_array($_ticketFilterIDList)) {
            $_SWIFT->Database->Query("SELECT title FROM " . TABLE_PREFIX . "ticketfilters WHERE ticketfilterid IN (" .
                    BuildIN($_ticketFilterIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activitydeleteticketfilters'),
                        htmlspecialchars($_SWIFT->Database->Record['title'])), SWIFT_StaffActivityLog::ACTION_DELETE,
                        SWIFT_StaffActivityLog::SECTION_TICKETS, SWIFT_StaffActivityLog::INTERFACE_STAFF);
            }

            SWIFT_TicketFilter::DeleteList($_ticketFilterIDList);
        }

        return true;
    }

    /**
     * Delete the Given Ticket Filter ID
     *
     * @author Varun Shoor
     * @param int $_ticketFilterID The Ticket Filter ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete($_ticketFilterID)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($_ticketFilterID), true);

        $this->Load->Manage();

        return true;
    }

    /**
     * Displays the Ticket Filter Grid
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Manage()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_LoadDisplayData();

        $this->UserInterface->Header($this->Language->Get('tickets') . ' > ' . $this->Language->Get('managefilters'), self::MENU_ID,
                self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('staff_tcanviewfilters') == '0')
        {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->RenderGrid();
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Runs the Checks for Insertion/Editing
     *
     * @author Varun Shoor
     * @param int $_mode The User Interface Mode
     * @param int $_ticketFileTypeID The Ticket File Type ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function RunChecks($_mode, $_ticketFileTypeID = 0)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        // BEGIN CSRF HASH CHECK

        if (!SWIFT_Session::CheckCSRFHash($_POST['csrfhash']))
        {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if (trim($_POST['title']) == '' || !isset($_POST['rulecriteria']) || !_is_array($_POST['rulecriteria']))
        {
            $this->UserInterface->CheckFields('title');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            return false;
        }

        if (SWIFT::Get('isdemo') == true) {
            $this->UserInterface->Error($this->Language->Get('titledemomode'), $this->Language->Get('msgdemomode'));

            return false;
        }

        if (($_mode == SWIFT_UserInterface::MODE_INSERT && $_SWIFT->Staff->GetPermission('staff_tcaninsertfilter') == '0') ||
                ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT->Staff->GetPermission('staff_tcanupdatefilter') == '0')) {
            $this->UserInterface->Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));

            return false;
        }

        return true;
    }

    /**
     * Insert a new Ticket Filter
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Insert()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_LoadDisplayData();

        $this->UserInterface->Header($this->Language->Get('tickets') . ' > ' . $this->Language->Get('insertfilter'), self::MENU_ID,
                self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('staff_tcaninsertfilter') == '0')
        {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render(SWIFT_UserInterface::MODE_INSERT);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Render the Confirmation for InsertSubmit/EditSubmit
     *
     * @author Varun Shoor
     * @param mixed $_mode The User Interface Mode
     * @param SWIFT_TicketFilter $_SWIFT_TicketFilterObject
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    private function _RenderConfirmation($_mode, SWIFT_TicketFilter $_SWIFT_TicketFilterObject)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($_mode == SWIFT_UserInterface::MODE_EDIT)
        {
            $_type = 'update';
        } else {
            $_type = 'insert';
        }

        $_finalText = '<b>' . $this->Language->Get('filtertitle') . ':</b> ' . htmlspecialchars($_POST['title']) . '<br />';
        $_finalText .= '<b>' . $this->Language->Get('filtertype') . ':</b> ' . IIF($_POST['filtertype'] == SWIFT_TicketFilter::TYPE_PUBLIC, $this->Language->Get('public'), $this->Language->Get('private')) . '<br />';

        $_criteriaPointer = SWIFT_TicketSearch::GetCriteriaPointer();
        SWIFT_TicketSearch::ExtendCustomCriteria($_criteriaPointer);

        // Get all the criterias
        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketfilterfields WHERE ticketfilterid = '" .
                (int) ($_SWIFT_TicketFilterObject->GetTicketFilterID()) . "'");
        while ($this->Database->NextRecord())
        {
            $_criteriaName = 'ts' . $this->Database->Record['fieldtitle'];
            $_finalText .= ' <img src="' . SWIFT::Get('themepathimages') . 'linkdownarrow.gif' . '" align="absmiddle" border="0" /> ' . $this->Language->Get('if') . ' <b>"' . $this->Language->Get($_criteriaName) . '"</b> ' .
                    SWIFT_Rules::GetOperText($this->Database->Record['fieldoper']) . ' <b>"';

            $_extendedName = '';
            if (isset($_criteriaPointer[$this->Database->Record['fieldtitle']]['fieldcontents']) &&
                    _is_array($_criteriaPointer[$this->Database->Record['fieldtitle']]['fieldcontents']) &&
                            $_criteriaPointer[$this->Database->Record['fieldtitle']]['field'] === 'custom')
            {
                foreach ($_criteriaPointer[$this->Database->Record['fieldtitle']]['fieldcontents'] as $_key => $_val)
                {
                    if ($_val['contents'] == $this->Database->Record['fieldvalue'])
                    {
                        $_extendedName = $_val['title'];

                        break;
                    }
                }
            }

            $_finalText .= htmlspecialchars(IIF(!empty($_extendedName), $_extendedName, $this->Database->Record['fieldvalue'])) . '"</b><BR />';
        }

        $_finalText .= '<BR />';

        SWIFT::Info(sprintf($this->Language->Get('titlefilter' . $_type), htmlspecialchars($_POST['title'])),
                sprintf($this->Language->Get('msgfilter' . $_type), htmlspecialchars($_POST['title'])) . '<br />' . $_finalText);

        return true;
    }

    /**
     * Loads the Rule Criteria into $_POST
     *
     * @author Varun Shoor
     * @param int $_ticketFilterID The Ticket Filter ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    private function _LoadPOSTVariables($_ticketFilterID) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($_POST['rulecriteria']))
        {
            $_POST['rulecriteria'] = array();
            $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketfilterfields WHERE ticketfilterid = '" .
                     ($_ticketFilterID) . "' ORDER BY ticketfilterfieldid ASC");
            while ($this->Database->NextRecord())
            {
                $_POST['rulecriteria'][] = array($this->Database->Record['fieldtitle'], $this->Database->Record['fieldoper'], $this->Database->Record['fieldvalue']);
            }
        }

        return true;
    }

    /**
     * Retrieve the ticket field container from POST
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function GetTicketFieldContainer()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $_POST['rulecriteria'];
    }

    /**
     * Insert Submission Processor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function InsertSubmit()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->RunChecks(SWIFT_UserInterface::MODE_INSERT))
        {
            $_restrictStaffGroupID = 0;
            if (isset($_POST['restrictstaffgroupid']))
            {
                $_restrictStaffGroupID = $_POST['restrictstaffgroupid'];
            }

            $_ticketFilterID = SWIFT_TicketFilter::Create($_POST['title'], $_POST['filtertype'], $_restrictStaffGroupID,
                    $_POST['criteriaoptions'], $_SWIFT->Staff->GetStaffID(), $this->GetTicketFieldContainer());

            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityinsertticketfilter'), htmlspecialchars($_POST['title'])),
                    SWIFT_StaffActivityLog::ACTION_INSERT, SWIFT_StaffActivityLog::SECTION_TICKETS, SWIFT_StaffActivityLog::INTERFACE_STAFF);

            if (!$_ticketFilterID)
            {
                // @codeCoverageIgnoreStart
                // this code will never be executed
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
                // @codeCoverageIgnoreEnd
            }

            $this->_RenderConfirmation(SWIFT_UserInterface::MODE_INSERT, new SWIFT_TicketFilter(new SWIFT_DataID($_ticketFilterID)));

            $this->Load->Manage();

            return true;
        }

        $this->Load->Insert();

        return false;
    }

    /**
     * Edit the Ticket Filter ID
     *
     * @author Varun Shoor
     * @param int $_ticketFilterID The Ticket Filter ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Edit($_ticketFilterID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_ticketFilterID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_TicketFilterObject = new SWIFT_TicketFilter(new SWIFT_DataID($_ticketFilterID));
        if (!$_SWIFT_TicketFilterObject instanceof SWIFT_TicketFilter || !$_SWIFT_TicketFilterObject->GetIsClassLoaded())
        {
            // @codeCoverageIgnoreStart
            // this code will never be executed
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }

        $this->_LoadDisplayData();
        $this->_LoadPOSTVariables($_ticketFilterID);

        $this->UserInterface->Header($this->Language->Get('tickets') . ' > ' . $this->Language->Get('editfilter'), self::MENU_ID,
                self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('staff_tcanupdatefilter') == '0')
        {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render(SWIFT_UserInterface::MODE_EDIT, $_SWIFT_TicketFilterObject);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Edit Submission Processor
     *
     * @author Varun Shoor
     * @param int $_ticketFilterID The Ticket Filter ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function EditSubmit($_ticketFilterID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_ticketFilterID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_TicketFilterObject = new SWIFT_TicketFilter(new SWIFT_DataID($_ticketFilterID));
        if (!$_SWIFT_TicketFilterObject instanceof SWIFT_TicketFilter || !$_SWIFT_TicketFilterObject->GetIsClassLoaded())
        {
            // @codeCoverageIgnoreStart
            // this code will never be executed
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }

        if ($this->RunChecks(SWIFT_UserInterface::MODE_EDIT, $_SWIFT_TicketFilterObject->GetTicketFilterID()))
        {
            $_restrictStaffGroupID = 0;
            if (isset($_POST['restrictstaffgroupid']))
            {
                $_restrictStaffGroupID = $_POST['restrictstaffgroupid'];
            }

            $_updateResult = $_SWIFT_TicketFilterObject->Update($_POST['title'], $_POST['filtertype'], $_restrictStaffGroupID, $_POST['criteriaoptions'], $_SWIFT->Staff->GetStaffID(), $this->GetTicketFieldContainer());

            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityupdateticketfilter'), htmlspecialchars($_POST['title'])),
                    SWIFT_StaffActivityLog::ACTION_UPDATE, SWIFT_StaffActivityLog::SECTION_TICKETS, SWIFT_StaffActivityLog::INTERFACE_STAFF);

            if (!$_updateResult)
            {
                // @codeCoverageIgnoreStart
                // this code will never be executed
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
                // @codeCoverageIgnoreEnd
            }

            $this->_RenderConfirmation(SWIFT_UserInterface::MODE_EDIT, $_SWIFT_TicketFilterObject);

            $this->Load->Manage();

            return true;
        }

        $this->Load->Edit($_ticketFilterID);

        return false;
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
     * Retrieve the Ticket Filter Menu
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetMenu()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_finalTicketFilterContainer = SWIFT_TicketFilter::RetrieveMenu();
        echo $this->View->RenderMenu($_finalTicketFilterContainer);

        return true;
    }
}
