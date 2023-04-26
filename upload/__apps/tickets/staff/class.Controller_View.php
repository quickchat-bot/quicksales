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
use SWIFT_Session;
use Base\Models\Staff\SWIFT_StaffActivityLog;
use Tickets\Models\Ticket\SWIFT_Ticket;
use Tickets\Models\View\SWIFT_TicketView;
use Tickets\Models\View\SWIFT_TicketViewLink;
use Base\Library\UserInterface\SWIFT_UserInterface;

/**
 * The Ticket View Controller
 *
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @property Controller_View $Load
 * @property View_View $View
 * @author Varun Shoor
 */
class Controller_View extends Controller_StaffBase {
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
        $this->Language->Load('staff_ticketview');

        SWIFT_Ticket::LoadLanguageTable();
    }

    /**
     * Delete the Ticket Views from Mass Action
     *
     * @author Varun Shoor
     * @param mixed $_ticketViewIDList The Ticket View ID List Container Array
     * @param bool $_byPassCSRF Whether to bypass the CSRF check
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function DeleteList($_ticketViewIDList, $_byPassCSRF = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash']))
        {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('staff_tcandeleteview') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        $_masterFinalText = '';
        $_masterIndex = 1;
        $_masterTicketViewIDList = array();

        if (_is_array($_ticketViewIDList)) {
            $_finalTicketViewIDList = array();

            $_SWIFT->Database->Query("SELECT ticketviewid, title, ismaster FROM " . TABLE_PREFIX . "ticketviews WHERE ticketviewid IN (" .
                    BuildIN($_ticketViewIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                if ($_SWIFT->Database->Record['ismaster'] == '1') {
                    $_masterTicketViewIDList[] = (int) ($_SWIFT->Database->Record['ticketviewid']);
                    $_masterFinalText .= $_masterIndex . '. ' . htmlspecialchars($_SWIFT->Database->Record['title']) . '<br />';

                    $_masterIndex++;
                } else {
                    SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activitydeleteticketview'),
                            htmlspecialchars($_SWIFT->Database->Record['title'])), SWIFT_StaffActivityLog::ACTION_DELETE,
                            SWIFT_StaffActivityLog::SECTION_TICKETS, SWIFT_StaffActivityLog::INTERFACE_STAFF);

                    $_finalTicketViewIDList[] = $_SWIFT->Database->Record['ticketviewid'];
                }
            }

            if (count($_finalTicketViewIDList)) {
                SWIFT_TicketView::DeleteList($_finalTicketViewIDList);
            }
        }

        if (!empty($_masterFinalText)) {
            SWIFT::Alert(sprintf($_SWIFT->Language->Get('titlemasterviewdel'), count($_masterTicketViewIDList)),
                    $_SWIFT->Language->Get('msgmasterviewdel') . '<br />' . $_masterFinalText);
        }

        return true;
    }

    /**
     * Delete the Given Ticket View ID
     *
     * @author Varun Shoor
     * @param int $_ticketViewID The Ticket View ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete($_ticketViewID)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($_ticketViewID), true);

        $this->Load->Manage();

        return true;
    }

    /**
     * Displays the Ticket View Grid
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

        $this->UserInterface->Header($this->Language->Get('tickets') . ' > ' . $this->Language->Get('manageviews'), self::MENU_ID,
                self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('staff_tcanview_views') == '0')
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

        if (trim($_POST['title']) == '' || trim($_POST['ticketsperpage']) == '')
        {
            $this->UserInterface->CheckFields('title', 'ticketsperpage');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            return false;
        }
        /**
         * FEATURE- Mansi Wason <mansi.wason@opencart.com.vn>
         *
         * SWIFT-5061 Option to define 'Tickets Per Page' allowed for Ticket Views
         *
         * Comment - Added a setting in Admin CP Tickets settings, where Administrator can define the limit for Tickets Per Page option which will be applicable for Ticket Views created by any staff.
         */

        if ($_POST['ticketsperpage'] > $_SWIFT->Settings->Get('t_ticketview') || $_POST['ticketsperpage'] <= 0)
        {
            $this->UserInterface->CheckFields('ticketsperpage');

            $this->UserInterface->Error($this->Language->Get('adminviewticket'), sprintf($this->Language->Get('desc_adminviewticket'), $_SWIFT->Settings->Get('t_ticketview')));

            return false;

        }

        if (!isset($_POST['viewtype']) || !_is_array($_POST['viewtype'])) {
            $this->UserInterface->CheckFields('viewtype');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            return false;
        }

        if (!isset($_POST['viewfields']) || !_is_array($_POST['viewfields'])) {
            $this->UserInterface->Error($this->Language->Get('titleviewfieldempty'), $this->Language->Get('msgviewfieldempty'));

            return false;

        }

        if (SWIFT::Get('isdemo') == true) {
            $this->UserInterface->Error($this->Language->Get('titledemomode'), $this->Language->Get('msgdemomode'));

            return false;
        }

        if (($_mode == SWIFT_UserInterface::MODE_INSERT && $_SWIFT->Staff->GetPermission('staff_tcaninsertview') == '0') ||
                ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT->Staff->GetPermission('staff_tcanupdateview') == '0')) {
            $this->UserInterface->Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));

            return false;
        }

        return true;
    }

    /**
     * Insert a new Ticket View
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

        $this->UserInterface->Header($this->Language->Get('tickets') . ' > ' . $this->Language->Get('insertview'), self::MENU_ID,
                self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('staff_tcaninsertview') == '0')
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
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    private function _RenderConfirmation($_mode)
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

        $_finalText = '<b>' . $this->Language->Get('title') . ':</b> ' . htmlspecialchars($_POST['title']) . '<br />';

        $_viewAssignedArray = array();
        if (in_array(SWIFT_TicketView::VIEW_ALLTICKETS, $_POST['viewtype'])) {
            $_viewAssignedArray[] = $this->Language->Get('viewalltickets');
        }

        if (in_array(SWIFT_TicketView::VIEW_UNASSIGNED, $_POST['viewtype'])) {
            $_viewAssignedArray[] = $this->Language->Get('viewunassigned');
        }

        if (in_array(SWIFT_TicketView::VIEW_ASSIGNED, $_POST['viewtype'])) {
            $_viewAssignedArray[] = $this->Language->Get('viewassigned');
        }

        $_finalText .= '<b>' . $this->Language->Get('viewassignedfield') . ':</b> ' . implode(', ', $_viewAssignedArray) . '<br />';
        $_finalText .= '<b>' . $this->Language->Get('viewscope') . ':</b> ' . SWIFT_TicketView::GetViewScopeLabel($_POST['viewscope']) . '<br />';

        SWIFT::Info(sprintf($this->Language->Get('titleticketview' . $_type), htmlspecialchars($_POST['title'])),
                sprintf($this->Language->Get('msgticketview' . $_type), htmlspecialchars($_POST['title'])) . '<br />' . $_finalText);

        return true;
    }

    /**
     * Process the POST Variables
     *
     * @author Varun Shoor
     * @return array
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function _ProcessPOSTVariables() {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_containerArray = array();

        // First process the view types
        $_containerArray['_viewAllTickets'] = $_containerArray['_viewUnassigned'] = $_containerArray['_viewAssigned'] = false;
        if (in_array(SWIFT_TicketView::VIEW_ALLTICKETS, $_POST['viewtype'])) {
            $_containerArray['_viewAllTickets'] = true;
        }

        if (in_array(SWIFT_TicketView::VIEW_ASSIGNED, $_POST['viewtype'])) {
            $_containerArray['_viewAssigned'] = true;
        }

        if (in_array(SWIFT_TicketView::VIEW_UNASSIGNED, $_POST['viewtype'])) {
            $_containerArray['_viewUnassigned'] = true;
        }

        // Process the fields/columns
        $_containerArray['_ticketViewFieldsContainer'] = array();
        foreach ($_POST['viewfields'] as $_key => $_val) {
            $_containerArray['_ticketViewFieldsContainer'][] = $_val;
        }

        // Process the links
        $_containerArray['_ticketViewLinkContainer'] = array();
        $_containerArray['_ticketViewLinkContainer'][SWIFT_TicketViewLink::LINK_DEPARTMENT] = array();
        $_containerArray['_ticketViewLinkContainer'][SWIFT_TicketViewLink::LINK_FILTERDEPARTMENT] = array();
        $_containerArray['_ticketViewLinkContainer'][SWIFT_TicketViewLink::LINK_FILTERPRIORITY] = array();
        $_containerArray['_ticketViewLinkContainer'][SWIFT_TicketViewLink::LINK_FILTERSTATUS] = array();
        $_containerArray['_ticketViewLinkContainer'][SWIFT_TicketViewLink::LINK_FILTERTYPE] = array();

        if (isset($_POST['linkdepartmentid']) && _is_array($_POST['linkdepartmentid'])) {
            foreach ($_POST['linkdepartmentid'] as $_key => $_val) {
                $_containerArray['_ticketViewLinkContainer'][SWIFT_TicketViewLink::LINK_DEPARTMENT][] = $_val;
            }
        }

        if (isset($_POST['filterdepartmentid']) && _is_array($_POST['filterdepartmentid'])) {
            foreach ($_POST['filterdepartmentid'] as $_key => $_val) {
                $_containerArray['_ticketViewLinkContainer'][SWIFT_TicketViewLink::LINK_FILTERDEPARTMENT][] = $_val;
            }
        }

        if (isset($_POST['filterticketpriorityid']) && _is_array($_POST['filterticketpriorityid'])) {
            foreach ($_POST['filterticketpriorityid'] as $_key => $_val) {
                $_containerArray['_ticketViewLinkContainer'][SWIFT_TicketViewLink::LINK_FILTERPRIORITY][] = $_val;
            }
        }

        if (isset($_POST['filterticketstatusid']) && _is_array($_POST['filterticketstatusid'])) {
            foreach ($_POST['filterticketstatusid'] as $_key => $_val) {
                $_containerArray['_ticketViewLinkContainer'][SWIFT_TicketViewLink::LINK_FILTERSTATUS][] = $_val;
            }
        }

        if (isset($_POST['filtertickettypeid']) && _is_array($_POST['filtertickettypeid'])) {
            foreach ($_POST['filtertickettypeid'] as $_key => $_val) {
                $_containerArray['_ticketViewLinkContainer'][SWIFT_TicketViewLink::LINK_FILTERTYPE][] = $_val;
            }
        }

        return $_containerArray;
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
            $_variableContainer = $this->_ProcessPOSTVariables();

            // these will be overwritten by extract
            $_viewAllTickets = $_viewUnassigned = $_viewAssigned = $_ticketViewFieldsContainer = $_ticketViewLinkContainer = false;
            extract($_variableContainer, EXTR_OVERWRITE);

            $_ticketViewID = SWIFT_TicketView::Create($_POST['title'], $_POST['viewscope'], $_SWIFT->Staff, $_viewAllTickets, $_viewUnassigned,
                    $_viewAssigned, $_POST['sortby'], $_POST['sortorder'], $_POST['ticketsperpage'], $_POST['autorefresh'], $_POST['setasowner'],
                    $_POST['defaultstatusonreply'], $_POST['afterreplyaction'], $_ticketViewFieldsContainer, $_ticketViewLinkContainer);

            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityinsertticketview'), htmlspecialchars($_POST['title'])),
                    SWIFT_StaffActivityLog::ACTION_INSERT, SWIFT_StaffActivityLog::SECTION_TICKETS, SWIFT_StaffActivityLog::INTERFACE_STAFF);

            if (!$_ticketViewID)
            {
                // @codeCoverageIgnoreStart
                // this code will never be executed
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
                // @codeCoverageIgnoreEnd
            }

            $this->_RenderConfirmation(SWIFT_UserInterface::MODE_INSERT);

            $this->Load->Manage();

            return true;
        }

        $this->Load->Insert();

        return false;
    }

    /**
     * Edit the Ticket View ID
     *
     * @author Varun Shoor
     * @param int $_ticketViewID The Ticket View ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Edit($_ticketViewID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_ticketViewID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_TicketViewObject = false;
        try {
            $_SWIFT_TicketViewObject = new SWIFT_TicketView(new SWIFT_DataID($_ticketViewID));
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {

        }

        $this->UserInterface->Header($this->Language->Get('tickets') . ' > ' . $this->Language->Get('editview'), self::MENU_ID,
                self::NAVIGATION_ID);

        if (!$_SWIFT_TicketViewObject instanceof SWIFT_TicketView || !$_SWIFT_TicketViewObject->GetIsClassLoaded() ||
                !$_SWIFT_TicketViewObject->CanStaffView() || $_SWIFT->Staff->GetPermission('staff_tcanupdateview') == '0')
        {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render(SWIFT_UserInterface::MODE_EDIT, $_SWIFT_TicketViewObject);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Edit Submission Processor
     *
     * @author Varun Shoor
     * @param int $_ticketViewID The Ticket View ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function EditSubmit($_ticketViewID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_ticketViewID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_TicketViewObject = new SWIFT_TicketView(new SWIFT_DataID($_ticketViewID));
        if (!$_SWIFT_TicketViewObject instanceof SWIFT_TicketView || !$_SWIFT_TicketViewObject->GetIsClassLoaded() ||
                !$_SWIFT_TicketViewObject->CanStaffView())
        {
            // @codeCoverageIgnoreStart
            // this code will never be executed
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }

        if ($this->RunChecks(SWIFT_UserInterface::MODE_EDIT, $_SWIFT_TicketViewObject->GetTicketViewID()))
        {
            $_variableContainer = $this->_ProcessPOSTVariables();

            // these will be overwritten by extract
            $_viewAllTickets = $_viewUnassigned = $_viewAssigned = $_ticketViewFieldsContainer = $_ticketViewLinkContainer = false;
            extract($_variableContainer);

            $_updateResult = $_SWIFT_TicketViewObject->Update($_POST['title'], $_POST['viewscope'], $_SWIFT->Staff, $_viewAllTickets,
                    $_viewUnassigned, $_viewAssigned, $_POST['sortby'], $_POST['sortorder'], $_POST['ticketsperpage'], $_POST['autorefresh'],
                    $_POST['setasowner'], $_POST['defaultstatusonreply'], $_POST['afterreplyaction'], $_ticketViewFieldsContainer,
                    $_ticketViewLinkContainer);

            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityupdateticketview'), htmlspecialchars($_POST['title'])),
                    SWIFT_StaffActivityLog::ACTION_UPDATE, SWIFT_StaffActivityLog::SECTION_TICKETS, SWIFT_StaffActivityLog::INTERFACE_STAFF);

            if (!$_updateResult)
            {
                // @codeCoverageIgnoreStart
                // this code will never be executed
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
                // @codeCoverageIgnoreEnd
            }

            $this->_RenderConfirmation(SWIFT_UserInterface::MODE_EDIT);

            $this->Load->Manage();

            return true;
        }

        $this->Load->Edit($_ticketViewID);

        return false;
    }
}
