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

namespace Troubleshooter\Staff;

use Base\Admin\Controller_Staff;
use Base\Library\Comment\SWIFT_CommentManager;
use SWIFT;
use SWIFT_App;
use Base\Models\Attachment\SWIFT_Attachment;
use Base\Models\Comment\SWIFT_Comment;
use SWIFT_DataID;
use SWIFT_Exception;
use SWIFT_Loader;
use SWIFT_MIME_Exception;
use SWIFT_MIMEList;
use Base\Library\Rules\SWIFT_Rules;
use Base\Models\SearchStore\SWIFT_SearchStore;
use SWIFT_Session;
use Base\Models\Staff\SWIFT_StaffActivityLog;
use SWIFT_StringHTMLToText;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel;
use Base\Library\UserInterface\SWIFT_UserInterfaceGrid;
use Troubleshooter\Models\Category\SWIFT_TroubleshooterCategory;
use Troubleshooter\Models\Step\SWIFT_TroubleshooterStep;

/**
 * The Troubleshooter Step Controller
 *
 * @author Varun Shoor
 * @method Library($_libraryName, $_arguments, $_initiateInstance, $_customAppName, $_appName)
 * @method Controller($_libraryName, $_arguments)
 * @property Controller_Step $Load
 * @property SWIFT_CommentManager $CommentManager
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @property View_Step $View
 */
class Controller_Step extends Controller_Staff
{
    // Core Constants
    const MENU_ID = 6;
    const NAVIGATION_ID = 1;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @throws SWIFT_Exception
     */
    public function __construct()
    {
        parent::__construct();

        $this->Load->Library('Comment:CommentManager', [], true, false, 'base');

        if (SWIFT_App::IsInstalled(APP_TICKETS))
        {
            SWIFT_Loader::LoadModel('Ticket:Ticket', APP_TICKETS);
            SWIFT_Loader::LoadModel('Ticket:TicketPost', APP_TICKETS);
        }

        $this->Language->Load('staff_troubleshooter');
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

        $this->UserInterface->AddNavigationBox($this->Language->Get('quickfilter'), $this->View->RenderQuickFilterTree());

        return true;
    }

    /**
     * Delete the Troubleshooter Steps from Mass Action
     *
     * @author Varun Shoor
     * @param mixed $_troubleshooterStepIDList The Troubleshooter Step ID List Container Array
     * @param bool $_byPassCSRF Whether to bypass the CSRF check
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function DeleteList($_troubleshooterStepIDList, $_byPassCSRF = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash']))
        {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('staff_trcandeletestep') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        if (_is_array($_troubleshooterStepIDList)) {
            $_SWIFT->Database->Query("SELECT subject, staffname FROM " . TABLE_PREFIX . "troubleshootersteps WHERE troubleshooterstepid IN (" . BuildIN($_troubleshooterStepIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activitydeletetrstep'),
                        htmlspecialchars(StripName($_SWIFT->Database->Record['subject'], 30)), htmlspecialchars($_SWIFT->Database->Record['staffname'])),
                        SWIFT_StaffActivityLog::ACTION_DELETE, SWIFT_StaffActivityLog::SECTION_TROUBLESHOOTER, SWIFT_StaffActivityLog::INTERFACE_STAFF);
            }

            SWIFT_TroubleshooterStep::DeleteList($_troubleshooterStepIDList);
        }

        return true;
    }

    /**
     * Delete the Given Troubleshooter Step ID
     *
     * @author Varun Shoor
     * @param int $_troubleshooterStepID The Troubleshooter Step ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete($_troubleshooterStepID)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($_troubleshooterStepID), true);

        $this->Load->Manage();

        return true;
    }

    /**
     * Displays the Troubleshooter Tree & Grid
     *
     * @author Varun Shoor
     * @param bool|int $_isGridTabSelected Whether the grid tab is selected by default
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Index($_isGridTabSelected = false)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->Load->Manage(false, (int) $_isGridTabSelected);
    }

    /**
     * Displays the Troubleshooter Tree & Grid
     *
     * @author Varun Shoor
     * @param int|bool $_searchStoreID (OPTIONAL) The Search Store ID
     * @param mixed $_isGridTabSelected (OPTIONAL) Whether the grid tab is selected by default
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Manage($_searchStoreID = false, $_isGridTabSelected = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (is_numeric($_isGridTabSelected) || is_bool($_isGridTabSelected))
        {
            $_isGridTabSelected = (int) ($_isGridTabSelected);
        } else {
            $_isGridTabSelected = false;
        }

        if (is_numeric($_searchStoreID))
        {
            $_searchStoreID = ($_searchStoreID);
        } else {
            $_searchStoreID = false;
        }

        if (isset($_POST['itemid']) || SWIFT::Get('displaygridtab') == true)
        {
            $_isGridTabSelected = true;
        }

        $this->_LoadDisplayData();

        $this->UserInterface->Header($this->Language->Get('troubleshooter') . ' > ' . $this->Language->Get('manage'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('staff_trcanmanagesteps') == '0')
        {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->RenderTabs((bool) $_isGridTabSelected, (int) $_searchStoreID);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Runs the Checks for Insertion/Editing
     *
     * @author Varun Shoor
     * @param int $_mode The User Interface Mode
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    private function RunChecks($_mode)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!SWIFT_Session::CheckCSRFHash($_POST['csrfhash']))
        {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (trim($_POST['subject']) == '' || trim($_POST['stepcontents_htmlcontents']) == '' || !isset($_POST['parentstepidlist']) || !_is_array($_POST['parentstepidlist']))
        {
            $this->UserInterface->CheckFields('subject', 'stepcontents', 'parentstepidlist');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            return false;
        }

        if (SWIFT::Get('isdemo') == true) {
            $this->UserInterface->Error($this->Language->Get('titledemomode'), $this->Language->Get('msgdemomode'));

            return false;
        }

        if (($_mode == SWIFT_UserInterface::MODE_INSERT && $_SWIFT->Staff->GetPermission('staff_trcaninsertstep') == '0') ||
                ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT->Staff->GetPermission('staff_trcanupdatestep') == '0')) {
            $this->UserInterface->Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));

            return false;
        }

        return true;
    }

    /**
     * Render New Step Dialog
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function InsertDialog() {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        // Check permission
        if ($_SWIFT->Staff->GetPermission('staff_trcaninsertstep') == '0') {
            $this->UserInterface->Header($this->Language->Get('troubleshooter') . ' > ' . $this->Language->Get('insert'), self::MENU_ID, self::NAVIGATION_ID);
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
            $this->UserInterface->Footer();

            return false;

        }

        $this->UserInterface->Header($this->Language->Get('troubleshooter') . ' > ' . $this->Language->Get('insert'), self::MENU_ID, self::NAVIGATION_ID);
        $this->View->RenderNewStepDialog();
        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Insert a Troubleshooter Step
     *
     * @author Varun Shoor
     * @param int|bool $_troubleshooterStepID (OPTIONAL) The Troubleshooter Step ID
     * @param int|bool $_troubleshooterCategoryID (OPTIONAL) The Troubleshooter Category ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function Insert($_troubleshooterStepID = false, $_troubleshooterCategoryID = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (isset($_POST['troubleshootercategoryid']))
        {
            $_troubleshooterCategoryID = (int) ($_POST['troubleshootercategoryid']);
        }

        $_troubleshooterStepID = (int) ($_troubleshooterStepID);

        if (empty($_troubleshooterCategoryID))
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->_LoadDisplayData();

        $this->UserInterface->Header($this->Language->Get('troubleshooter') . ' > ' . $this->Language->Get('insertstep'), self::MENU_ID, 4);

        if ($_SWIFT->Staff->GetPermission('staff_trcaninsertstep') == '0')
        {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render(SWIFT_UserInterface::MODE_INSERT, null, (int)$_troubleshooterCategoryID, $_troubleshooterStepID);
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

        $_type = 'insert';
        if ($_mode == SWIFT_UserInterface::MODE_EDIT)
        {
            $_type = 'update';
        }

        SWIFT::Info(sprintf($this->Language->Get('titletrstep' . $_type), htmlspecialchars($_POST['subject'])),
                sprintf($this->Language->Get('msgtrstep' . $_type), htmlspecialchars($_POST['subject'])));

        return true;
    }

    /**
     * Insert Submission Processor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function InsertSubmit($_isDraft = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->RunChecks(SWIFT_UserInterface::MODE_INSERT))
        {
            $_stepStatus = SWIFT_TroubleshooterStep::STATUS_DRAFT;
            if ($_SWIFT->Staff->GetPermission('staff_trcaninsertpublishedsteps') != '0' && $_isDraft == false)
            {
                $_stepStatus = SWIFT_TroubleshooterStep::STATUS_PUBLISHED;
            }

            $_parentTroubleshooterStepIDList = $this->_GetTroubleshooterStepIDList();

            if (!isset($_POST['troubleshootercategoryid']) || empty($_POST['troubleshootercategoryid']))
            {
                throw new SWIFT_Exception(SWIFT_INVALIDDATA . ': Invalid category');
            }

            $_redirectTickets = false;
            $_redirectDepartmentID = $_ticketTypeID = $_ticketPriorityID = 0;
            $_ticketSubject = '';
            if (isset($_POST['redirecttickets']))
            {
                $_redirectTickets = (int) ($_POST['redirecttickets']);

                if ($_redirectTickets == '1')
                {
                    $_ticketSubject = $_POST['ticketsubject'];
                    $_redirectDepartmentID = (int) ($_POST['redirectdepartmentid']);
                    $_ticketTypeID = (int) ($_POST['tickettypeid']);
                    $_ticketPriorityID = (int) ($_POST['ticketpriorityid']);
                }
            }

            $_troubleshooterStepID = SWIFT_TroubleshooterStep::Create($_POST['troubleshootercategoryid'], $_stepStatus, $_POST['subject'], $_POST['stepcontents_htmlcontents'],
                    (int) ($_POST['displayorder']), $_POST['allowcomments'], (bool) $_redirectTickets, $_ticketSubject, $_redirectDepartmentID,
                    $_ticketTypeID, $_ticketPriorityID, $_parentTroubleshooterStepIDList, $_SWIFT->Staff);

            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityinserttrstep'), htmlspecialchars(StripName($_POST['subject'], 25))),
                    SWIFT_StaffActivityLog::ACTION_INSERT, SWIFT_StaffActivityLog::SECTION_TROUBLESHOOTER, SWIFT_StaffActivityLog::INTERFACE_STAFF);

            $_SWIFT_TroubleshooterStepObject = new SWIFT_TroubleshooterStep(new SWIFT_DataID($_troubleshooterStepID));
            $_SWIFT_TroubleshooterStepObject->ProcessPostAttachments();
            $this->_RenderConfirmation(SWIFT_UserInterface::MODE_INSERT);

            $this->Load->Manage();

            return true;
        }

        $this->Load->Insert();

        return false;
    }

    /**
     * Edit the Troubleshooter Step
     *
     * @author Varun Shoor
     * @param int $_troubleshooterStepID The Troubleshooter Step ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Edit($_troubleshooterStepID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_troubleshooterStepID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_TroubleshooterStepObject = new SWIFT_TroubleshooterStep(new SWIFT_DataID($_troubleshooterStepID));
        if (!$_SWIFT_TroubleshooterStepObject instanceof SWIFT_TroubleshooterStep || !$_SWIFT_TroubleshooterStepObject->GetIsClassLoaded())
        {
            // @codeCoverageIgnoreStart
            // This code will never be executed
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }

        $this->View->RenderInfoBox($_SWIFT_TroubleshooterStepObject);
        $this->_LoadDisplayData();

        $this->UserInterface->Header($this->Language->Get('troubleshooter') . ' > ' . htmlspecialchars($_SWIFT_TroubleshooterStepObject->GetProperty('subject')), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('staff_trcanupdatestep') == '0')
        {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render(SWIFT_UserInterface::MODE_EDIT, $_SWIFT_TroubleshooterStepObject);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Edit Submission Processor
     *
     * @author Varun Shoor
     * @param int $_troubleshooterStepID The Troubleshooter Step ID
     * @param bool $_markAsPublished (OPTIONAL) Whether to mark article as published
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function EditSubmit($_troubleshooterStepID, $_markAsPublished = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_troubleshooterStepID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_TroubleshooterStepObject = new SWIFT_TroubleshooterStep(new SWIFT_DataID($_troubleshooterStepID));
        if (!$_SWIFT_TroubleshooterStepObject instanceof SWIFT_TroubleshooterStep || !$_SWIFT_TroubleshooterStepObject->GetIsClassLoaded())
        {
            // @codeCoverageIgnoreStart
            // This code will never be executed
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }

        if ($this->RunChecks(SWIFT_UserInterface::MODE_EDIT))
        {
            if ($_markAsPublished == '1')
            {
                $_SWIFT_TroubleshooterStepObject->UpdateStatus(SWIFT_TroubleshooterStep::STATUS_PUBLISHED);
            } else if ($_SWIFT_TroubleshooterStepObject->GetProperty('stepstatus') == SWIFT_TroubleshooterStep::STATUS_PUBLISHED && $_markAsPublished == '-1') {
                $_SWIFT_TroubleshooterStepObject->UpdateStatus(SWIFT_TroubleshooterStep::STATUS_DRAFT);
            }

            $_redirectTickets = false;
            $_redirectDepartmentID = $_ticketTypeID = $_ticketPriorityID = 0;
            $_ticketSubject = '';
            if (isset($_POST['redirecttickets']))
            {
                $_redirectTickets = (int) ($_POST['redirecttickets']);

                if ($_redirectTickets == '1')
                {
                    $_ticketSubject = $_POST['ticketsubject'];
                    $_redirectDepartmentID = (int) ($_POST['redirectdepartmentid']);
                    $_ticketTypeID = (int) ($_POST['tickettypeid']);
                    $_ticketPriorityID = (int) ($_POST['ticketpriorityid']);
                }
            }

            $_updateResult = $_SWIFT_TroubleshooterStepObject->Update($_POST['subject'], $_POST['stepcontents_htmlcontents'], $_POST['displayorder'], $_POST['allowcomments'],
                (bool)$_redirectTickets, $_ticketSubject, $_redirectDepartmentID, $_ticketTypeID, $_ticketPriorityID, $this->_GetTroubleshooterStepIDList(), $_SWIFT->Staff);

            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityupdatetrstep'), htmlspecialchars(StripName($_POST['subject'], 25))),
                    SWIFT_StaffActivityLog::ACTION_UPDATE, SWIFT_StaffActivityLog::SECTION_TROUBLESHOOTER, SWIFT_StaffActivityLog::INTERFACE_STAFF);

            if (!$_updateResult)
            {
                // @codeCoverageIgnoreStart
                // This code will never be executed
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
                // @codeCoverageIgnoreEnd
            }

            $_SWIFT_TroubleshooterStepObject->ProcessPostAttachments();

            $this->_RenderConfirmation(SWIFT_UserInterface::MODE_EDIT);

            $this->Load->Manage();

            return true;
        }

        $this->Load->Edit($_troubleshooterStepID);

        return false;
    }

    /**
     * Retrieve the Troubleshooter Step ID List
     *
     * @author Varun Shoor
     * @return array The Troubleshooter Step ID List
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    private function _GetTroubleshooterStepIDList()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($_POST['parentstepidlist']) || !_is_array($_POST['parentstepidlist']))
        {
            return array();
        }

        /*
         * BUG FIX - Simaranjit Singh
         *
         * SWIFT-853  Troubleshooter Steps get duplicated on updating, if Category Root is selected in the 'Parent Steps' option
         *
         * Comments: Need to return unique parent step ID List
         */
        return array_unique($_POST['parentstepidlist']);
    }

    /**
     * Quick Filter Options
     *
     * @author Varun Shoor
     * @param string $_filterType The Filter Type
     * @param string $_filterValue The Filter Value
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function QuickFilter($_filterType, $_filterValue)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_searchStoreID = -1;

        $_troubleshooterStepIDList = array();

        $_gridSortContainer = SWIFT_UserInterfaceGrid::GetGridSortField('trstepgrid', 'troubleshootersteps.subject', 'asc');

        switch ($_filterType)
        {
            case 'category': {
                $_finalKnowledgebaseCategoryIDList = array();
                $_finalKnowledgebaseCategoryIDList[] = (int) ($_filterValue);

                $this->Database->QueryLimit("SELECT troubleshootersteps.troubleshooterstepid FROM " . TABLE_PREFIX . "troubleshootersteps AS troubleshootersteps
                    WHERE troubleshootersteps.troubleshootercategoryid IN (" . BuildIN($_finalKnowledgebaseCategoryIDList) . ")
                    ORDER BY " . $_gridSortContainer[0] . ' ' . $_gridSortContainer[1], 100);
                while ($this->Database->NextRecord())
                {
                    $_troubleshooterStepIDList[] = $this->Database->Record['troubleshooterstepid'];
                }

            }
            break;


            case 'date': {
                $_extendedSQL = false;

                if ($_filterValue == 'today')
                {
                    $_extendedSQL = SWIFT_Rules::BuildSQLDateRange('troubleshootersteps.dateline', SWIFT_Rules::DATERANGE_TODAY);
                } else if ($_filterValue == 'yesterday') {
                    $_extendedSQL = SWIFT_Rules::BuildSQLDateRange('troubleshootersteps.dateline', SWIFT_Rules::DATERANGE_YESTERDAY);
                } else if ($_filterValue == 'l7') {
                    $_extendedSQL = SWIFT_Rules::BuildSQLDateRange('troubleshootersteps.dateline', SWIFT_Rules::DATERANGE_LAST7DAYS);
                } else if ($_filterValue == 'l30') {
                    $_extendedSQL = SWIFT_Rules::BuildSQLDateRange('troubleshootersteps.dateline', SWIFT_Rules::DATERANGE_LAST30DAYS);
                } else if ($_filterValue == 'l180') {
                    $_extendedSQL = SWIFT_Rules::BuildSQLDateRange('troubleshootersteps.dateline', SWIFT_Rules::DATERANGE_LAST180DAYS);
                } else if ($_filterValue == 'l365') {
                    $_extendedSQL = SWIFT_Rules::BuildSQLDateRange('troubleshootersteps.dateline', SWIFT_Rules::DATERANGE_LAST365DAYS);
                } else {
                    throw new SWIFT_Exception(SWIFT_INVALIDDATA);
                }

                if (!empty($_extendedSQL))
                {
                    $this->Database->QueryLimit("SELECT troubleshootersteps.troubleshooterstepid FROM " . TABLE_PREFIX . "troubleshootersteps AS troubleshootersteps
                        WHERE " . $_extendedSQL . "
                        ORDER BY " . $_gridSortContainer[0] . ' ' . $_gridSortContainer[1], 100);
                    while ($this->Database->NextRecord())
                    {
                        $_troubleshooterStepIDList[] = $this->Database->Record['troubleshooterstepid'];
                    }
                }

            }
            break;

            default:
                break;
        }

        $_searchStoreID = SWIFT_SearchStore::Create($_SWIFT->Session->GetSessionID(), SWIFT_SearchStore::TYPE_TROUBLESHOOTER, $_troubleshooterStepIDList,
                $_SWIFT->Staff->GetStaffID());

        if (!_is_array($_troubleshooterStepIDList))
        {
            SWIFT::Alert($this->Language->Get('titlesearchfailed'), $this->Language->Get('msgsearchfailed'));
        }

        $this->Load->Manage($_searchStoreID, true);

        return true;
    }

    /**
     * The Troubleshooter Category Rendering Function
     *
     * @author Varun Shoor
     * @param int $_troubleshooterCategoryID The Troubleshooter Category ID
     * @param int $_activeTroubleshooterStepID (OPTIONAL) The Currently Active Troubleshooter Step ID
     * @param string $_troubleshooterStepHistory (OPTIONAL) The Troubleshooter Step History
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ViewSteps($_troubleshooterCategoryID, $_activeTroubleshooterStepID = 0, $_troubleshooterStepHistory = '')
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        /**
         * ---------------------------------------------
         * REPLICA EXISTS IN CLIENT!
         * ---------------------------------------------
         */

        if (empty($_activeTroubleshooterStepID) && isset($_POST['nexttroubleshooterstepid']))
        {
            $_activeTroubleshooterStepID = $_POST['nexttroubleshooterstepid'];
        }

        if (empty($_troubleshooterStepHistory) && isset($_POST['troubleshooterstephistory']))
        {
            $_troubleshooterStepHistory = $_POST['troubleshooterstephistory'];
        }

        // Was back button triggered?
        $length = strrpos($_POST['troubleshooterstephistory']??'', ':');
        if ($length === false) {
            $length = 0;
        }
        $_history = isset($_POST['troubleshooterstephistory']) && strpos($_POST['troubleshooterstephistory'], ':') != false;
        if (isset($_POST['isback']) && $_POST['isback'] == '1' && $_history)
        {
            $_troubleshooterStepHistory = substr($_POST['troubleshooterstephistory'], 0, $length);
            $_activeTroubleshooterStepID = substr($_POST['troubleshooterstephistory'], $length +1);

            // We need to move one step back
            if (strpos($_troubleshooterStepHistory, ':') != false)
            {
                $length1 = strrpos($_troubleshooterStepHistory, ':');
                if ($length1 === false) {
                    $length1 = 0;
                }
                $_activeTroubleshooterStepID = substr($_troubleshooterStepHistory, $length1 +1);
                $_troubleshooterStepHistory = substr($_troubleshooterStepHistory, 0, $length1);
            } else {
                $_activeTroubleshooterStepID = 0;
            }
        } else if ((!isset($_POST['isback']) || $_POST['isback'] == '0') && (!isset($_POST['nexttroubleshooterstepid']) || empty($_POST['nexttroubleshooterstepid'])) && $_history) {
            $_troubleshooterStepHistory = substr($_POST['troubleshooterstephistory'], 0, $length);
            $_activeTroubleshooterStepID = substr($_POST['troubleshooterstephistory'], $length +1);
        }

        $_SWIFT_TroubleshooterCategoryObject = false;

        try {
            $_SWIFT_TroubleshooterCategoryObject = new SWIFT_TroubleshooterCategory(new SWIFT_DataID($_troubleshooterCategoryID));
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {


            return false;
        }

        if (!$_SWIFT_TroubleshooterCategoryObject instanceof SWIFT_TroubleshooterCategory || !$_SWIFT_TroubleshooterCategoryObject->GetIsClassLoaded())
        {
            // @codeCoverageIgnoreStart
            // This code will never be executed
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }

        if (!$_SWIFT_TroubleshooterCategoryObject->CanAccess(array(SWIFT_TroubleshooterCategory::TYPE_GLOBAL, SWIFT_TroubleshooterCategory::TYPE_PRIVATE), $_SWIFT->Staff->GetProperty('staffgroupid'), 0))
        {
            throw new SWIFT_Exception('Access Denied');
        }

        if (empty($_troubleshooterStepHistory))
        {
            $_troubleshooterStepHistory = '0';
        }

        $_troubleshooterStepSubject = htmlspecialchars($_SWIFT_TroubleshooterCategoryObject->GetProperty('title'));
        $_troubleshooterStepContents = nl2br(htmlspecialchars($_SWIFT_TroubleshooterCategoryObject->GetProperty('description')));
        $_troubleshooterStepHasAttachments = '0';
        $_extendedTitle = '';
        $_attachmentContainer = array();
        $_troubleshooterStepCount = 0;

        $_troubleshooterStepContainer = array();

        $_troubleshooterStepAllowComments = false;

        if (!empty($_activeTroubleshooterStepID))
        {
            $_SWIFT_TroubleshooterStepObject = false;

            try {
                $_SWIFT_TroubleshooterStepObject = new SWIFT_TroubleshooterStep(new SWIFT_DataID($_activeTroubleshooterStepID));
            } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {


                return false;
            }

            if ($_SWIFT_TroubleshooterStepObject->GetProperty('troubleshootercategoryid') != $_troubleshooterCategoryID)
            {
                throw new SWIFT_Exception('Invalid Step Category');
            }

            $_troubleshooterStepHistory .= ':' . $_SWIFT_TroubleshooterStepObject->GetTroubleshooterStepID();

            /**
             * ---------------------------------------------
             * Ticket Redirection Logic
             * ---------------------------------------------
             */
            if (SWIFT_App::IsInstalled(APP_TICKETS) && $_SWIFT_TroubleshooterStepObject->GetProperty('redirecttickets') == '1')
            {
                $_departmentCache = $this->Cache->Get('departmentcache');

                // Redirect to department
                if (isset($_departmentCache[$_SWIFT_TroubleshooterStepObject->GetProperty('redirectdepartmentid')]))
                {
                    $_POST['newticketsubject'] = $_SWIFT_TroubleshooterStepObject->GetProperty('subject');

                    if ($_SWIFT_TroubleshooterStepObject->GetProperty('ticketsubject') != '')
                    {
                        $_POST['newticketsubject'] = $_SWIFT_TroubleshooterStepObject->GetProperty('ticketsubject');
                    }

                    $_SWIFT_StringHTMLToTextObject = new SWIFT_StringHTMLToText();

                    $_POST['newticketcontents'] = $_SWIFT_StringHTMLToTextObject->Convert($_SWIFT_TroubleshooterStepObject->GetProperty('contents'), false);

                    $_POST['tickettype'] = 'user';
                    $_POST['departmentid'] = $_SWIFT_TroubleshooterStepObject->GetProperty('redirectdepartmentid');

                    $this->Load->Controller('Ticket', 'Tickets')->Load->NewTicketForm();


                // Redirect to ticket submission
                } else {
                    $this->Load->Controller('Ticket', 'Tickets')->Load->NewTicket();

                }

                return true;
            }

            /**
             * BUG FIX : Saloni Dhall <saloni.dhall@opencart.com.vn>
             *
             * SWIFT-3987 : Security issue (medium)
             *
             * Comments : Sanitizing the content of subject field to prevent vulnerability.
             */
            $_troubleshooterStepSubject = $_SWIFT->Input->SanitizeForXSS($_SWIFT_TroubleshooterStepObject->GetProperty('subject'));
            $_troubleshooterStepContents = $_SWIFT_TroubleshooterStepObject->GetProperty('contents');
            $_troubleshooterStepHasAttachments = $_SWIFT_TroubleshooterStepObject->GetProperty('hasattachments');
            $_troubleshooterStepAllowComments = $_SWIFT_TroubleshooterStepObject->GetProperty('allowcomments');

            // Attachment Logic
            $_attachmentContainer = array();
            if ($_SWIFT_TroubleshooterStepObject->GetProperty('hasattachments') == '1')
            {
                $_attachmentContainer = SWIFT_Attachment::Retrieve(SWIFT_Attachment::LINKTYPE_TROUBLESHOOTERSTEP, $_SWIFT_TroubleshooterStepObject->GetTroubleshooterStepID());

                foreach ($_attachmentContainer as $_attachmentID => $_attachment)
                {
                    $_mimeDataContainer = array();
                    try
                    {
                        $_fileExtension = mb_strtolower(substr($_attachment['filename'], (strrpos($_attachment['filename'], '.')+1)));

                        $_MIMEListObject = new SWIFT_MIMEList();
                        $_mimeDataContainer = $_MIMEListObject->Get($_fileExtension);
                    } catch (SWIFT_MIME_Exception $_SWIFT_MIME_ExceptionObject) {
                        // Do nothing
                    }

                    $_attachmentIcon = 'icon_file.gif';
                    if (isset($_mimeDataContainer[1]))
                    {
                        $_attachmentIcon = $_mimeDataContainer[1];
                    }

                    $_attachmentContainer[$_attachmentID] = array();
                    $_attachmentContainer[$_attachmentID]['icon'] = $_attachmentIcon;
                    $_attachmentContainer[$_attachmentID]['link'] = SWIFT::Get('basename') . '/Troubleshooter/Step/GetAttachment/' . $_troubleshooterCategoryID . '/' . $_SWIFT_TroubleshooterStepObject->GetTroubleshooterStepID() . '/' . $_attachment['attachmentid'];
                    $_attachmentContainer[$_attachmentID]['name'] = htmlspecialchars($_attachment['filename']);
                    $_attachmentContainer[$_attachmentID]['size'] = FormattedSize($_attachment['filesize']);
                }
            }

            $_extendedTitle = $_SWIFT_TroubleshooterCategoryObject->GetProperty('title');

            $this->View->RenderInfoBox($_SWIFT_TroubleshooterStepObject);

            if (isset($_POST['comments']) && !empty($_POST['comments']))
            {
                $this->CommentManager->ProcessPOSTStaff($_SWIFT->Staff, SWIFT_Comment::TYPE_TROUBLESHOOTER, $_SWIFT_TroubleshooterStepObject->GetTroubleshooterStepID(), SWIFT::Get('basename') . '/Troubleshooter/Step/ViewSteps/' . $_troubleshooterCategoryID . '/' . $_activeTroubleshooterStepID . '/' . $_troubleshooterStepHistory);
            }
        } else {
            $_SWIFT_TroubleshooterCategoryObject->IncrementViews();
        }

        $_troubleshooterStepContainer = SWIFT_TroubleshooterStep::RetrieveSubSteps($_troubleshooterCategoryID, $_activeTroubleshooterStepID);
        $_troubleshooterStepCount = count($_troubleshooterStepContainer);

        $_showBackButton = false;
        if (!empty($_troubleshooterStepHistory))
        {
            $_showBackButton = true;
        }

        $_dataContainer = array();
        $_dataContainer['_extendedTitle'] = $_extendedTitle;
        $_dataContainer['_troubleshooterCategoryID'] = $_troubleshooterCategoryID;
        $_dataContainer['_troubleshooterStepSubject'] = $_troubleshooterStepSubject;
        $_dataContainer['_troubleshooterStepContents'] = $_troubleshooterStepContents;
        $_dataContainer['_troubleshooterStepHasAttachments'] = $_troubleshooterStepHasAttachments;
        $_dataContainer['_troubleshooterStepAllowComments'] = $_troubleshooterStepAllowComments;
        $_dataContainer['_troubleshooterStepContainer'] = $_troubleshooterStepContainer;
        $_dataContainer['_troubleshooterStepHistory'] = $_troubleshooterStepHistory;
        $_dataContainer['_troubleshooterStepCount'] = $_troubleshooterStepCount;
        $_dataContainer['_attachmentContainer'] = $_attachmentContainer;
        $_dataContainer['_activeTroubleshooterStepID'] = $_activeTroubleshooterStepID;
        $_dataContainer['_showBackButton'] = $_showBackButton;

        $this->UserInterface->Header($this->Language->Get('troubleshooter') . ' > ' . $this->Language->Get('steps'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('staff_trcanviewsteps') == '0')
        {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->RenderViewSteps($_dataContainer);
        }

        $this->UserInterface->Footer();


        return true;
    }

    /**
     * Dispatch the Attachment
     *
     * @author Varun Shoor
     * @param int $_troubleshooterCategoryID The Troubleshooter Category ID
     * @param int $_troubleshooterStepID The Troubleshooter Step ID
     * @param int $_attachmentID The Attachment ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetAttachment($_troubleshooterCategoryID, $_troubleshooterStepID, $_attachmentID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }


        $_SWIFT_TroubleshooterCategoryObject = false;

        try {
            $_SWIFT_TroubleshooterCategoryObject = new SWIFT_TroubleshooterCategory(new SWIFT_DataID($_troubleshooterCategoryID));
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {


            return false;
        }

        if (!$_SWIFT_TroubleshooterCategoryObject instanceof SWIFT_TroubleshooterCategory || !$_SWIFT_TroubleshooterCategoryObject->GetIsClassLoaded())
        {
            // @codeCoverageIgnoreStart
            // This code will never be executed
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }

        if (!$_SWIFT_TroubleshooterCategoryObject->CanAccess(array(SWIFT_TroubleshooterCategory::TYPE_GLOBAL, SWIFT_TroubleshooterCategory::TYPE_PRIVATE), $_SWIFT->Staff->GetProperty('staffgroupid'), 0))
        {
            throw new SWIFT_Exception('Access Denied');
        }

        $_SWIFT_TroubleshooterStepObject = false;

        try {
            $_SWIFT_TroubleshooterStepObject = new SWIFT_TroubleshooterStep(new SWIFT_DataID($_troubleshooterStepID));
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {


            return false;
        }

        if ($_SWIFT_TroubleshooterStepObject->GetProperty('troubleshootercategoryid') != $_troubleshooterCategoryID)
        {
            // @codeCoverageIgnoreStart
            // This code will never be executed
            throw new SWIFT_Exception('Invalid Step Category');
            // @codeCoverageIgnoreEnd
        }

        $_SWIFT_AttachmentObject = new SWIFT_Attachment($_attachmentID);
        // Did the object load up?
        if (!$_SWIFT_AttachmentObject instanceof SWIFT_Attachment || !$_SWIFT_AttachmentObject->GetIsClassLoaded()) {
            // @codeCoverageIgnoreStart
            // This code will never be executed
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }

        if ($_SWIFT_AttachmentObject->GetProperty('linktype') != SWIFT_Attachment::LINKTYPE_TROUBLESHOOTERSTEP || $_SWIFT_AttachmentObject->GetProperty('linktypeid') != $_SWIFT_TroubleshooterStepObject->GetTroubleshooterStepID()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $intName = \SWIFT::GetInstance()->Interface->GetName()?:SWIFT_INTERFACE;
        if ($intName === 'tests' || $intName === 'console') {
            return true;
        }
        // @codeCoverageIgnoreStart
        // This code will never be executed in tests
        $_SWIFT_AttachmentObject->Dispatch();

        return true;
        // @codeCoverageIgnoreEnd
    }
}
