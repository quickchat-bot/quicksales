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

namespace Tickets\Admin;

use Base\Library\CustomField\SWIFT_CustomFieldManager;
use Base\Library\CustomField\SWIFT_CustomFieldRendererWorkflow;
use Base\Library\UserInterface\SWIFT_UserInterfaceClient;
use Base\Models\CustomField\SWIFT_CustomFieldGroup;
use Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel;
use Controller_admin;
use SWIFT;
use SWIFT_DataID;
use SWIFT_Exception;
use Base\Library\Rules\SWIFT_Rules;
use SWIFT_Session;
use Base\Models\Staff\SWIFT_StaffActivityLog;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Tickets\Library\Flag\SWIFT_TicketFlag;
use Tickets\Models\Ticket\SWIFT_Ticket;
use Tickets\Models\Workflow\SWIFT_TicketWorkflow;
use Tickets\Models\Workflow\SWIFT_TicketWorkflowNotification;

/**
 * The Ticket Workflow Controller
 *
 * @method Library($_libraryName, $_arguments = [], $_initiateInstance = false, $_customAppName = '', $_appName = '')
 * @property SWIFT_UserInterfaceClient $UserInterface
 * @property SWIFT_CustomFieldManager $CustomFieldManager
 * @property Controller_Workflow $Load
 * @property View_Workflow $View
 * @property SWIFT_TicketFlag $TicketFlag
 * @property SWIFT_CustomFieldRendererWorkflow $CustomFieldRendererWorkflow
 * @author Varun Shoor
 */
class Controller_Workflow extends Controller_admin
{
    // Core Constants
    const MENU_ID = 1;
    const NAVIGATION_ID = 5;

    /**
     * Constructor
     *
     * @author Varun Shoor
     */
    public function __construct()
    {
        parent::__construct();

        $this->Load->Library('Flag:TicketFlag', [], true, false, 'tickets');

        $this->Load->Library('CustomField:CustomFieldRendererWorkflow', [], true, false, 'base');
        $this->Load->Library('CustomField:CustomFieldManager', [], true, false, 'base');

        $this->Language->Load('admin_workflows');
        $this->Language->Load('staff_ticketsmain');

        SWIFT_Ticket::LoadLanguageTable();
    }

    /**
     * Resort the Ticket Workflows
     *
     * @author Varun Shoor
     * @param mixed $_ticketWorkflowIDSortList The Ticket Workflow ID Sort List Container Array
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function SortList($_ticketWorkflowIDSortList)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!SWIFT_Session::CheckCSRFHash($_POST['csrfhash']))
        {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('admin_tcanupdateworkflow') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        SWIFT_TicketWorkflow::UpdateDisplayOrderList($_ticketWorkflowIDSortList);

        return true;
    }

    /**
     * Delete the Ticket Workflows from Mass Action
     *
     * @author Varun Shoor
     * @param mixed $_ticketWorkflowIDList The Ticket Workflow ID List Container Array
     * @param bool $_byPassCSRF Whether to bypass the CSRF check
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function DeleteList($_ticketWorkflowIDList, $_byPassCSRF = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash']))
        {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('admin_tcandeleteworkflows') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        if (_is_array($_ticketWorkflowIDList)) {
            $_SWIFT->Database->Query("SELECT title FROM " . TABLE_PREFIX . "ticketworkflows WHERE ticketworkflowid IN (" .
                    BuildIN($_ticketWorkflowIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activitydeleteticketworkflow'), $_SWIFT->Database->Record['title']),
                        SWIFT_StaffActivityLog::ACTION_DELETE, SWIFT_StaffActivityLog::SECTION_TICKETS, SWIFT_StaffActivityLog::INTERFACE_ADMIN);
            }

            SWIFT_TicketWorkflow::DeleteList($_ticketWorkflowIDList);
        }

        return true;
    }

    /**
     * Enable the Ticket Workflows from Mass Action
     *
     * @author Varun Shoor
     * @param mixed $_ticketWorkflowIDList The Ticket Workflow ID List Container Array
     * @param bool $_byPassCSRF Whether to bypass the CSRF check
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function EnableList($_ticketWorkflowIDList, $_byPassCSRF = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash']))
        {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('admin_tcanupdateworkflow') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        if (_is_array($_ticketWorkflowIDList)) {
            $_SWIFT->Database->Query("SELECT title FROM " . TABLE_PREFIX . "ticketworkflows WHERE ticketworkflowid IN (" .
                    BuildIN($_ticketWorkflowIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activityenableticketworkflow'), $_SWIFT->Database->Record['title']),
                        SWIFT_StaffActivityLog::ACTION_UPDATE, SWIFT_StaffActivityLog::SECTION_TICKETS, SWIFT_StaffActivityLog::INTERFACE_ADMIN);
            }

            SWIFT_TicketWorkflow::EnableList($_ticketWorkflowIDList);
        }

        return true;
    }

    /**
     * Disable the Ticket Workflows from Mass Action
     *
     * @author Varun Shoor
     * @param mixed $_ticketWorkflowIDList The Ticket Workflow ID List Container Array
     * @param bool $_byPassCSRF Whether to bypass the CSRF check
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function DisableList($_ticketWorkflowIDList, $_byPassCSRF = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash']))
        {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('admin_tcanupdateworkflow') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        if (_is_array($_ticketWorkflowIDList)) {
            $_SWIFT->Database->Query("SELECT title FROM " . TABLE_PREFIX . "ticketworkflows WHERE ticketworkflowid IN (" .
                    BuildIN($_ticketWorkflowIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activitydisableticketworkflow'), $_SWIFT->Database->Record['title']),
                        SWIFT_StaffActivityLog::ACTION_UPDATE, SWIFT_StaffActivityLog::SECTION_TICKETS, SWIFT_StaffActivityLog::INTERFACE_ADMIN);
            }

            SWIFT_TicketWorkflow::DisableList($_ticketWorkflowIDList);
        }

        return true;
    }

    /**
     * Delete the Given Ticket Workflow ID
     *
     * @author Varun Shoor
     * @param int $_ticketWorkflowID The Ticket Workflow ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete($_ticketWorkflowID)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($_ticketWorkflowID), true);

        $this->Load->Manage();

        return true;
    }

    /**
     * Displays the Ticket Workflow Grid
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

        $this->UserInterface->Header($this->Language->Get('tickets') . ' > ' . $this->Language->Get('manageworkflows'),
                self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_tcanviewworkflows') == '0')
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
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function _RunChecks($_mode)
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

        $_actionContainer = self::_ProcessFormRuleActions();

        if (trim($_POST['title']) == '' || trim($_POST['sortorder']) == '' || trim($_POST['ruleoptions']) == '' ||
                !isset($_POST['rulecriteria']) || !_is_array($_POST['rulecriteria']))
        {
            $this->UserInterface->CheckFields('title', 'sortorder', 'ruleoptions');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            return false;
        }

        if (!count($_actionContainer)) {
            SWIFT::ErrorField('staffid', 'departmentid', 'priorityid', 'ticketstatusid', 'newslaplanid', 'flagtype', 'notes');

            $this->UserInterface->Error($this->Language->Get('titlenoaction'), $this->Language->Get('msgnoaction'));

            return false;
        }

        if (SWIFT::Get('isdemo') == true) {
            $this->UserInterface->Error($this->Language->Get('titledemomode'), $this->Language->Get('msgdemomode'));

            return false;
        }

        if (($_mode == SWIFT_UserInterface::MODE_INSERT && $_SWIFT->Staff->GetPermission('admin_tcaninsertworkflow') == '0') ||
                ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT->Staff->GetPermission('admin_tcanupdateworkflow') == '0')) {
            $this->UserInterface->Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));

            return false;
        }

        return true;
    }

    /**
     * Render the Insert/Edit Confirmation
     *
     * @author Varun Shoor
     * @param SWIFT_TicketWorkflow $_SWIFT_TicketWorkflowObject The Ticket Workflow Object
     * @param mixed $_mode The UI Mode (INSERT/EDIT)
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function _RenderConfirmation(SWIFT_TicketWorkflow $_SWIFT_TicketWorkflowObject, $_mode)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!$_SWIFT_TicketWorkflowObject instanceof SWIFT_TicketWorkflow || !$_SWIFT_TicketWorkflowObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if ($_mode == SWIFT_UserInterface::MODE_INSERT)
        {
            $_type = 'insert';
        } else {
            $_type = 'update';
        }

        $_criteriaPointer = SWIFT_TicketWorkflow::GetCriteriaPointer();
        SWIFT_TicketWorkflow::ExtendCustomCriteria($_criteriaPointer);

        // Get all the criterias
        $_finalText = '';
        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketworkflowcriteria WHERE ticketworkflowid = '" .
                (int) ($_SWIFT_TicketWorkflowObject->GetTicketWorkflowID()) . "'");
        while ($this->Database->NextRecord())
        {
            $_criteriaName = 'wf' . $this->Database->Record['name'];
            $_finalText .= $this->Language->Get('if') . ' <b>"' . $this->Language->Get($_criteriaName) . '"</b> ' .
                    SWIFT_Rules::GetOperText($this->Database->Record['ruleop']) . ' <b>"';

            $_extendedName = '';
            if (isset($_criteriaPointer[$this->Database->Record['name']]['fieldcontents']) &&
                    _is_array($_criteriaPointer[$this->Database->Record['name']]['fieldcontents']) &&
                            $_criteriaPointer[$this->Database->Record['name']]['field'] === 'custom')
            {
                foreach ($_criteriaPointer[$this->Database->Record['name']]['fieldcontents'] as $_key => $_val)
                {
                    if ($_val['contents'] == $this->Database->Record['rulematch'])
                    {
                        $_extendedName = $_val['title'];

                        break;
                    }
                }
            }

            $_finalText .= htmlspecialchars(IIF(!empty($_extendedName), $_extendedName, $this->Database->Record['rulematch'])) . '"</b><BR />';
        }

        $_finalText .= '<BR />';

        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketworkflowactions WHERE ticketworkflowid = '" .
                (int) ($_SWIFT_TicketWorkflowObject->GetTicketWorkflowID()) . "'");
        while ($this->Database->NextRecord())
        {
            $_finalText .= $this->View->ReturnRuleActionString($this->Database->Record);
        }

        SWIFT::Info(sprintf($this->Language->Get('title' . $_type . 'workflow'),
                htmlspecialchars($_SWIFT_TicketWorkflowObject->GetProperty('title'))), sprintf($this->Language->Get('msg' . $_type . 'workflow'),
                        htmlspecialchars($_SWIFT_TicketWorkflowObject->GetProperty('title'))) . '<BR />' . $_finalText);

        return true;
    }

    /**
     * Processes the form and returns the unified Action Array
     *
     * @author Varun Shoor
     * @return array "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function _ProcessFormRuleActions()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_actionsContainer = array();
        $_index = 0;

        if (!empty($_POST['departmentid']))
        {
            $_actionsContainer[$_index]['name'] = SWIFT_TicketWorkflow::ACTION_DEPARTMENT;
            $_actionsContainer[$_index]['typeid'] = (int) ($_POST['departmentid']);
            $_index++;
        }

        if (!empty($_POST['ticketstatusid']))
        {
            $_actionsContainer[$_index]['name'] = SWIFT_TicketWorkflow::ACTION_STATUS;
            $_actionsContainer[$_index]['typeid'] = (int) ($_POST['ticketstatusid']);
            $_index++;
        }

        if (!empty($_POST['priorityid']))
        {
            $_actionsContainer[$_index]['name'] = SWIFT_TicketWorkflow::ACTION_PRIORITY;
            $_actionsContainer[$_index]['typeid'] = (int) ($_POST['priorityid']);
            $_index++;
        }

        if ($_POST['staffid'] != '-2')
        {
            $_actionsContainer[$_index]['name'] = SWIFT_TicketWorkflow::ACTION_OWNER;
            $_actionsContainer[$_index]['typeid'] = (int) ($_POST['staffid']);
            $_index++;
        }

        if (!empty($_POST['flagtype']))
        {
            $_actionsContainer[$_index]['name'] = SWIFT_TicketWorkflow::ACTION_FLAGTICKET;
            $_actionsContainer[$_index]['typeid'] = (int) ($_POST['flagtype']);
            $_index++;
        }

        if (!empty($_POST['newslaplanid']))
        {
            $_actionsContainer[$_index]['name'] = SWIFT_TicketWorkflow::ACTION_SLAPLAN;
            $_actionsContainer[$_index]['typeid'] = (int) ($_POST['newslaplanid']);
            $_index++;
        }

        if (!empty($_POST['tickettypeid']))
        {
            $_actionsContainer[$_index]['name'] = SWIFT_TicketWorkflow::ACTION_TICKETTYPE;
            $_actionsContainer[$_index]['typeid'] = (int) ($_POST['tickettypeid']);
            $_index++;
        }

        if (!empty($_POST['bayescategoryid']))
        {
            $_actionsContainer[$_index]['name'] = SWIFT_TicketWorkflow::ACTION_BAYESIAN;
            $_actionsContainer[$_index]['typeid'] = (int) ($_POST['bayescategoryid']);
            $_index++;
        }

        if (!empty($_POST['trashticket']))
        {
            $_actionsContainer[$_index]['name'] = SWIFT_TicketWorkflow::ACTION_TRASH;
            $_actionsContainer[$_index]['typeid'] = '1';
            $_index++;
        }

        $_addTagsList = SWIFT_UserInterface::GetMultipleInputValues('addtags');
        $_removeTagsList = SWIFT_UserInterface::GetMultipleInputValues('removetags');

        if (_is_array($_addTagsList)) {
            $_actionsContainer[$_index]['name'] = SWIFT_TicketWorkflow::ACTION_ADDTAGS;
            $_actionsContainer[$_index]['typedata'] = json_encode($_addTagsList);
            $_index++;
        }

        if (_is_array($_removeTagsList)) {
            $_actionsContainer[$_index]['name'] = SWIFT_TicketWorkflow::ACTION_REMOVETAGS;
            $_actionsContainer[$_index]['typedata'] = json_encode($_removeTagsList);
            $_index++;
        }

        if (trim($_POST['notes']) !== '')
        {
            $_actionsContainer[$_index]['name'] = SWIFT_TicketWorkflow::ACTION_ADDNOTE;
            $_actionsContainer[$_index]['typedata'] = $_POST['notes'];
            $_index++;
        }

        return $_actionsContainer;
    }

    /**
     * Retrieves the Notification Container
     *
     * @author Varun Shoor
     * @return array "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    private function _GetNotificationContainer()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_notificationContainer = array();

        if (isset($_POST['notifications']) && _is_array($_POST['notifications']))
        {
            foreach ($_POST['notifications'] as $_key => $_val)
            {
                if (isset($_val[0]) && isset($_val[1]) && isset($_val[2]) && !empty($_val[0]) && !empty($_val[2]) &&
                        SWIFT_TicketWorkflowNotification::IsValidType($_val[0]))
                {
                    $_notificationContainer[] = array($_val[0], $_val[1], $_val[2]);
                }
            }
        }

        return $_notificationContainer;
    }

    /**
     * Insert a new Ticket Workflow Rule
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

        $this->UserInterface->Header($this->Language->Get('tickets') . ' > ' . $this->Language->Get('insertworkflow'), self::MENU_ID,
                self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_tcaninsertworkflow') == '0')
        {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render(SWIFT_UserInterface::MODE_INSERT);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Insert Submission Processor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Creation Fails
     */
    public function InsertSubmit()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->_RunChecks(SWIFT_UserInterface::MODE_INSERT))
        {
            $_actionContainer = self::_ProcessFormRuleActions();
            $_SWIFT_TicketWorkflowObject = SWIFT_TicketWorkflow::Create($_POST['title'], $_POST['isenabled'], $_POST['sortorder'],
                    $_POST['ruleoptions'], $_POST['rulecriteria'], $_actionContainer, $this->_GetNotificationContainer(),
                    $_POST['staffvisibilitycustom'], $this->_GetAssignedStaffGroupIDList(), (int) $_POST['uservisibility']);

            if ($_SWIFT_TicketWorkflowObject instanceof SWIFT_TicketWorkflow && $_SWIFT_TicketWorkflowObject->GetIsClassLoaded()) {
                /**
                 * New Feature - Werner Garcia <werner.garcia@crossover.com>
                 *
                 * KAYAKOC-4974 Ability to modify custom fields when triggering a workflow.
                 *
                 * Update Custom Field Values
                 * These are the only custom field groups that make sense to update
                 * when triggering a workflow. Livechat, News, KB, etc. need an ID
                 * that is not available from a ticket
                 */
                $_customFieldCheckResult = $this->CustomFieldManager->Check(SWIFT_CustomFieldManager::MODE_POST, SWIFT_UserInterface::MODE_INSERT,
                    [
                        SWIFT_CustomFieldGroup::GROUP_USER,
                        SWIFT_CustomFieldGroup::GROUP_STAFFTICKET,
                        SWIFT_CustomFieldGroup::GROUP_USERTICKET,
                        SWIFT_CustomFieldGroup::GROUP_STAFFUSERTICKET,
                    ], SWIFT_CustomFieldManager::CHECKMODE_WORKFLOW, 0, $_SWIFT_TicketWorkflowObject->GetTicketWorkflowID());
                if ($_customFieldCheckResult[0] == false)
                {
                    SWIFT::Alert($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

                    $this->Load->Edit($_SWIFT_TicketWorkflowObject->GetTicketWorkflowID());

                    return false;
                }

                $this->CustomFieldManager->Update(SWIFT_CustomFieldManager::MODE_POST, SWIFT_UserInterface::MODE_INSERT,
                    [
                        SWIFT_CustomFieldGroup::GROUP_USER,
                        SWIFT_CustomFieldGroup::GROUP_STAFFTICKET,
                        SWIFT_CustomFieldGroup::GROUP_USERTICKET,
                        SWIFT_CustomFieldGroup::GROUP_STAFFUSERTICKET,
                    ], SWIFT_CustomFieldManager::CHECKMODE_WORKFLOW, $_SWIFT_TicketWorkflowObject->GetTicketWorkflowID());

                SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityinsertticketworkflow'), htmlspecialchars($_POST['title'])),
                        SWIFT_StaffActivityLog::ACTION_INSERT, SWIFT_StaffActivityLog::SECTION_TICKETS, SWIFT_StaffActivityLog::INTERFACE_ADMIN);

                $this->_RenderConfirmation($_SWIFT_TicketWorkflowObject, SWIFT_UserInterface::MODE_INSERT);

                $this->Load->Manage();

                return true;
            }

            // @codeCoverageIgnoreStart
            // this code will never be executed
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
            // @codeCoverageIgnoreEnd
        }

        $this->Load->Insert();

        return false;
    }

    /**
     * Edit the Ticket Workflow
     *
     * @author Varun Shoor
     * @param int $_ticketWorkflowID The Ticket Workflow ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data Provided
     */
    public function Edit($_ticketWorkflowID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_ticketWorkflowID))
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_TicketWorkflowObject = new SWIFT_TicketWorkflow(new SWIFT_DataID($_ticketWorkflowID));
        if (!$_SWIFT_TicketWorkflowObject instanceof SWIFT_TicketWorkflow || !$_SWIFT_TicketWorkflowObject->GetIsClassLoaded())
        {
            // @codeCoverageIgnoreStart
            // this code will never be executed
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }

        $this->_LoadPOSTVariables($_ticketWorkflowID);

        $this->UserInterface->Header($this->Language->Get('tickets') . ' > ' . $this->Language->Get('editworkflow'), self::MENU_ID,
                self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_tcanupdateworkflow') == '0')
        {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render(SWIFT_UserInterface::MODE_EDIT, $_SWIFT_TicketWorkflowObject);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Loads the Rule Criteria into $_POST
     *
     * @author Varun Shoor
     * @param int $_ticketWorkflowID The Ticket Workflow ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    private function _LoadPOSTVariables($_ticketWorkflowID) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($_POST['rulecriteria']))
        {
            $_POST['rulecriteria'] = array();
            $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketworkflowcriteria WHERE ticketworkflowid = '" .
                     ($_ticketWorkflowID) . "' ORDER BY ticketworkflowcriteriaid ASC");
            while ($this->Database->NextRecord())
            {
                $_POST['rulecriteria'][] = array($this->Database->Record['name'], $this->Database->Record['ruleop'], $this->Database->Record['rulematch'], $this->Database->Record['rulematchtype']);
            }
        }

        return true;
    }

    /**
     * Edit Submission Processor
     *
     * @author Varun Shoor
     * @param int $_ticketWorkflowID The Ticket Workflow ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data Provided
     */
    public function EditSubmit($_ticketWorkflowID)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_ticketWorkflowID))
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_TicketWorkflowObject = new SWIFT_TicketWorkflow(new SWIFT_DataID($_ticketWorkflowID));
        if (!$_SWIFT_TicketWorkflowObject instanceof SWIFT_TicketWorkflow || !$_SWIFT_TicketWorkflowObject->GetIsClassLoaded())
        {
            // @codeCoverageIgnoreStart
            // this code will never be executed
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }

        if ($this->_RunChecks(SWIFT_UserInterface::MODE_EDIT))
        {
            $_actionContainer = self::_ProcessFormRuleActions();
            $_SWIFT_TicketWorkflowObject->Update($_POST['title'], $_POST['isenabled'], $_POST['sortorder'], $_POST['ruleoptions'],
                    $_POST['rulecriteria'], $_actionContainer, $this->_GetNotificationContainer(), $_POST['staffvisibilitycustom'],
                    $this->_GetAssignedStaffGroupIDList(), $_POST['uservisibility']);

            /**
             * New Feature - Werner Garcia <werner.garcia@crossover.com>
             *
             * KAYAKOC-4974 Ability to modify custom fields when triggering a workflow.
             *
             * Update Custom Field Values
             * These are the only custom field groups that make sense to update
             * when triggering a workflow. Livechat, News, KB, etc. need an ID
             * that is not available from a ticket
             */
            $_customFieldCheckResult = $this->CustomFieldManager->Check(SWIFT_CustomFieldManager::MODE_POST, SWIFT_UserInterface::MODE_EDIT,
                [
                    SWIFT_CustomFieldGroup::GROUP_USER,
                    SWIFT_CustomFieldGroup::GROUP_STAFFTICKET,
                    SWIFT_CustomFieldGroup::GROUP_USERTICKET,
                    SWIFT_CustomFieldGroup::GROUP_STAFFUSERTICKET,
                ], SWIFT_CustomFieldManager::CHECKMODE_WORKFLOW, 0, $_ticketWorkflowID);
            if ($_customFieldCheckResult[0] == false)
            {
                SWIFT::Alert($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

                $this->Load->Edit($_ticketWorkflowID);

                return false;
            }

            $this->CustomFieldManager->Update(SWIFT_CustomFieldManager::MODE_POST, SWIFT_UserInterface::MODE_EDIT,
                [
                    SWIFT_CustomFieldGroup::GROUP_USER,
                    SWIFT_CustomFieldGroup::GROUP_STAFFTICKET,
                    SWIFT_CustomFieldGroup::GROUP_USERTICKET,
                    SWIFT_CustomFieldGroup::GROUP_STAFFUSERTICKET,
                ], SWIFT_CustomFieldManager::CHECKMODE_WORKFLOW, $_ticketWorkflowID);

            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityupdateticketworkflow'), htmlspecialchars($_POST['title'])),
                    SWIFT_StaffActivityLog::ACTION_UPDATE, SWIFT_StaffActivityLog::SECTION_TICKETS, SWIFT_StaffActivityLog::INTERFACE_ADMIN);

            $this->_RenderConfirmation($_SWIFT_TicketWorkflowObject, SWIFT_UserInterface::MODE_EDIT);

            $this->Load->Manage();

            return true;
        }

        $this->Load->Edit($_ticketWorkflowID);

        return false;
    }

    /**
     * Retrieve the Assigned Staff Group ID list for Insert/Edit Processing
     *
     * @author Varun Shoor
     * @return mixed "_assignedStaffGroupIDList" (ARRAY) on Success, "false" otherwise
     */
    private function _GetAssignedStaffGroupIDList()
    {
        if (!$this->GetIsClassLoaded() || !isset($_POST['staffgroupidlist']) || !_is_array($_POST['staffgroupidlist']))
        {
            return array();
        }

        $_assignedStaffGroupIDList = array();
        foreach ($_POST['staffgroupidlist'] as $_key => $_val)
        {
            if ($_val == '1')
            {
                $_assignedStaffGroupIDList[] = (int) ($_key);
            }
        }

        return $_assignedStaffGroupIDList;
    }
}
