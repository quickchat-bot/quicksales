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

use Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel;
use Controller_admin;
use SWIFT;
use SWIFT_DataID;
use SWIFT_Exception;
use Base\Library\Rules\SWIFT_Rules;
use SWIFT_Session;
use Base\Models\Staff\SWIFT_StaffActivityLog;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Tickets\Models\SLA\SWIFT_SLA;

/**
 * The SLA Controller
 *
 * @method Library($_libraryName, $_arguments, $_initiateInstance, $_customAppName, $_appName)
 * @property Controller_SLA $Load
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @property View_SLA $View
 * @author Varun Shoor
 */
class Controller_SLA extends Controller_admin
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

        $this->Language->Load('tickets');
        $this->Language->Load('adminsla');
    }

    /**
     * Delete the SLA Plans from Mass Action
     *
     * @author Varun Shoor
     * @param mixed $_slaPlanIDList The SLA Plan ID List Container Array
     * @param bool $_byPassCSRF Whether to bypass the CSRF check
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function DeleteList($_slaPlanIDList, $_byPassCSRF = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash']))
        {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('admin_tcandeleteslaplans') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        if (_is_array($_slaPlanIDList)) {
            $_SWIFT->Database->Query("SELECT title FROM ". TABLE_PREFIX ."slaplans WHERE slaplanid IN (". BuildIN($_slaPlanIDList) .")");
            while ($_SWIFT->Database->NextRecord()) {
                SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activityslaplandelete'), htmlspecialchars($_SWIFT->Database->Record['title'])), SWIFT_StaffActivityLog::ACTION_DELETE, SWIFT_StaffActivityLog::SECTION_TICKETS, SWIFT_StaffActivityLog::INTERFACE_ADMIN);
            }

            SWIFT_SLA::DeleteList($_slaPlanIDList);
        }

        return true;
    }

    /**
     * Enable the SLA Plans from Mass Action
     *
     * @author Varun Shoor
     * @param mixed $_slaPlanIDList The SLA Plan ID List Container Array
     * @param bool $_byPassCSRF Whether to bypass the CSRF check
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function EnableList($_slaPlanIDList, $_byPassCSRF = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash']))
        {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('admin_tcanupdateslaplan') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        if (_is_array($_slaPlanIDList)) {
            $_SWIFT->Database->Query("SELECT title FROM ". TABLE_PREFIX ."slaplans WHERE slaplanid IN (". BuildIN($_slaPlanIDList) .")");
            while ($_SWIFT->Database->NextRecord()) {
                SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activityslaplanenable'),
                        htmlspecialchars($_SWIFT->Database->Record['title'])), SWIFT_StaffActivityLog::ACTION_UPDATE,
                        SWIFT_StaffActivityLog::SECTION_TICKETS, SWIFT_StaffActivityLog::INTERFACE_ADMIN);
            }

            SWIFT_SLA::EnableList($_slaPlanIDList);
        }

        return true;
    }

    /**
     * Disable the SLA Plans from Mass Action
     *
     * @author Varun Shoor
     * @param mixed $_slaPlanIDList The SLA Plan ID List Container Array
     * @param bool $_byPassCSRF Whether to bypass the CSRF check
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function DisableList($_slaPlanIDList, $_byPassCSRF = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash']))
        {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('admin_tcanupdateslaplan') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        if (_is_array($_slaPlanIDList)) {
            $_SWIFT->Database->Query("SELECT title FROM ". TABLE_PREFIX ."slaplans WHERE slaplanid IN (". BuildIN($_slaPlanIDList) .")");
            while ($_SWIFT->Database->NextRecord()) {
                SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activityslaplandisable'),
                        htmlspecialchars($_SWIFT->Database->Record['title'])), SWIFT_StaffActivityLog::ACTION_UPDATE,
                        SWIFT_StaffActivityLog::SECTION_TICKETS, SWIFT_StaffActivityLog::INTERFACE_ADMIN);
            }

            SWIFT_SLA::DisableList($_slaPlanIDList);
        }

        return true;
    }

    /**
     * Delete the Given SLA Plan ID
     *
     * @author Varun Shoor
     * @param int $_slaPlanID The SLA Plan ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete($_slaPlanID)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($_slaPlanID), true);

        $this->Load->Manage();

        return true;
    }

    /**
     * Displays the SLA Plan Grid
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

        $this->UserInterface->Header($this->Language->Get('sla') . ' > ' . $this->Language->Get('manageplans'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_tcanviewslaplans') == '0')
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
    protected function RunChecks($_mode)
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

        if (trim($_POST['title']) == '' || trim($_POST['overduehrs']) == '' || trim($_POST['slascheduleid']) == ''
                || trim($_POST['resolutionduehrs']) == '' || !(is_numeric($_POST['overduehrs']) && $_POST['overduehrs'] > 0)
                || !(is_numeric($_POST['resolutionduehrs']) && $_POST['resolutionduehrs'] > 0))
        {
            $this->UserInterface->CheckFields('title', 'overduehrs', 'slascheduleid', 'resolutionduehrs');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            return false;
        }

        if (SWIFT::Get('isdemo') == true) {
            $this->UserInterface->Error($this->Language->Get('titledemomode'), $this->Language->Get('msgdemomode'));

            return false;
        }

        if (!isset($_POST['rulecriteria']) || !count($_POST['rulecriteria'])) {
            $this->UserInterface->Error($this->Language->Get('titlenocriteriaadded'), $this->Language->Get('msgnocriteriaadded'));

            return false;
        }

        if (($_mode == SWIFT_UserInterface::MODE_INSERT && $_SWIFT->Staff->GetPermission('admin_tcaninsertslaplan') == '0') ||
                ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT->Staff->GetPermission('admin_tcanupdateslaplan') == '0')) {
            $this->UserInterface->Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));

            return false;
        }

        return true;
    }

    /**
     * Loads the Rule Criteria into $_POST
     *
     * @author Varun Shoor
     * @param int $_slaPlanID The SLA Plan ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    private function _LoadPOSTVariables($_slaPlanID) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($_POST['rulecriteria']))
        {
            $_POST['rulecriteria'] = array();
            $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "slarulecriteria WHERE slaplanid = '" .  ($_slaPlanID) .
                    "' ORDER BY slarulecriteriaid ASC");
            while ($this->Database->NextRecord())
            {
                $_POST['rulecriteria'][] = array($this->Database->Record['name'], $this->Database->Record['ruleop'],
                    $this->Database->Record['rulematch'], $this->Database->Record['rulematchtype']);
            }
        }

        return true;
    }

    /**
     * Insert a new SLA Plan
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

        $_slaScheduleCache = $this->Cache->Get('slaschedulecache');
        if (!_is_array($_slaScheduleCache))
        {
            SWIFT::Alert($this->Language->Get('titlenoslasched'), $this->Language->Get('msgnoslasched'));
        }

        $this->UserInterface->Header($this->Language->Get('sla') . ' > ' . $this->Language->Get('insertplan'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_tcaninsertslaplan') == '0')
        {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render(SWIFT_UserInterface::MODE_INSERT);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Retrieve the Assigned SLA Holiday ID list for Insert/Edit Processing
     *
     * @author Varun Shoor
     * @return mixed "_assignedSLAHolidayIDList" (ARRAY) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    private function _GetAssignedSLAHolidayIDList()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($_POST['slaholidays']) || !_is_array($_POST['slaholidays'])) {
            return array();
        }

        $_assignedSLAHolidayIDList = array();
        foreach ($_POST['slaholidays'] as $_key => $_val)
        {
            if ($_val == '1')
            {
                $_assignedSLAHolidayIDList[] = (int) ($_key);
            }
        }

        return $_assignedSLAHolidayIDList;
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
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->RunChecks(SWIFT_UserInterface::MODE_INSERT))
        {
            $_SLAPlanObject = SWIFT_SLA::Create($_POST['title'], $_POST['overduehrs'], $_POST['resolutionduehrs'], $_POST['slascheduleid'],
                    $_POST['isenabled'], $_POST['sortorder'], $_POST['ruleoptions'], $_POST['rulecriteria'], $this->_GetAssignedSLAHolidayIDList());

            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityslaplaninsert'), htmlspecialchars($_POST['title'])),
                    SWIFT_StaffActivityLog::ACTION_INSERT, SWIFT_StaffActivityLog::SECTION_TICKETS, SWIFT_StaffActivityLog::INTERFACE_ADMIN);

            if (!$_SLAPlanObject instanceof SWIFT_SLA || !$_SLAPlanObject->GetIsClassLoaded())
            {
                // @codeCoverageIgnoreStart
                // this code will never be executed
                return false;
                // @codeCoverageIgnoreEnd
            }

            $this->_RenderConfirmation(SWIFT_UserInterface::MODE_INSERT, $_SLAPlanObject->GetSLAPlanID());

            $this->Load->Manage();

            return true;
        }

        $this->Load->Insert();

        return false;
    }

    /**
     * Edit the SLA Plan
     *
     * @author Varun Shoor
     * @param int $_slaPlanID The SLA Plan ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Edit($_slaPlanID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_slaPlanID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_SLAObject = new SWIFT_SLA(new SWIFT_DataID($_slaPlanID));
        if (!$_SWIFT_SLAObject instanceof SWIFT_SLA || !$_SWIFT_SLAObject->GetIsClassLoaded())
        {
            // @codeCoverageIgnoreStart
            // this code will never be executed
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }

        $this->_LoadPOSTVariables($_slaPlanID);

        $this->UserInterface->Header($this->Language->Get('sla') . ' > ' . $this->Language->Get('editplan'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_tcanupdateslaplan') == '0')
        {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render(SWIFT_UserInterface::MODE_EDIT, $_SWIFT_SLAObject);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Edit Submission Processor
     *
     * @author Varun Shoor
     * @param int $_slaPlanID The SLA Plan ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function EditSubmit($_slaPlanID)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_slaPlanID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_SLAObject = new SWIFT_SLA(new SWIFT_DataID($_slaPlanID));
        if (!$_SWIFT_SLAObject instanceof SWIFT_SLA || !$_SWIFT_SLAObject->GetIsClassLoaded())
        {
            // @codeCoverageIgnoreStart
            // this code will never be executed
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }

        if ($this->RunChecks(SWIFT_UserInterface::MODE_EDIT))
        {
            $_updateResult = $_SWIFT_SLAObject->Update($_POST['title'], $_POST['overduehrs'], $_POST['resolutionduehrs'], $_POST['slascheduleid'],
                    $_POST['isenabled'], $_POST['sortorder'], $_POST['ruleoptions'], $_POST['rulecriteria'], $this->_GetAssignedSLAHolidayIDList());

            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityslaplanupdate'), htmlspecialchars($_POST['title'])),
                    SWIFT_StaffActivityLog::ACTION_UPDATE, SWIFT_StaffActivityLog::SECTION_TICKETS, SWIFT_StaffActivityLog::INTERFACE_ADMIN);

            if (!$_updateResult)
            {
                // @codeCoverageIgnoreStart
                // this code will never be executed
                return false;
                // @codeCoverageIgnoreEnd
            }

            $this->_RenderConfirmation(SWIFT_UserInterface::MODE_EDIT, $_slaPlanID);

            $this->Load->Manage();

            return true;
        }

        $this->Load->Edit($_slaPlanID);

        return false;
    }

    /**
     * Render the User Interface Confirmation
     *
     * @author Varun Shoor
     * @param mixed $_mode The UI Mode
     * @param int $_slaPlanID The SLA Plan ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    private function _RenderConfirmation($_mode, $_slaPlanID)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_criteriaPointer = SWIFT_SLA::GetCriteriaPointer();
        SWIFT_SLA::ExtendCustomCriteria($_criteriaPointer);

        $_slaScheduleCache = $this->Cache->Get('slaschedulecache');
        $__type = IIF($_mode == SWIFT_UserInterface::MODE_INSERT, 'insert', 'update');
        $_slaPlan = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "slaplans WHERE slaplanid = '" .  ($_slaPlanID) . "'");

        $_finalText = '<b>' . $this->Language->Get('plantitle') . ':</b> ' . htmlspecialchars($_slaPlan['title']) . '<br />';
        $_finalText .= '<b>' . $this->Language->Get('overduehrs') . ':</b> ' . htmlspecialchars($_slaPlan['overduehrs']) . '<br />';
        $_finalText .= '<b>' . $this->Language->Get('resolutionduehrs') . ':</b> ' . htmlspecialchars($_slaPlan['resolutionduehrs']) . '<br />';

        if (isset($_slaScheduleCache[$_slaPlan['slascheduleid']]))
        {
            $_finalText .= '<b>' . $this->Language->Get('planschedule') . ':</b> ' . htmlspecialchars($_slaScheduleCache[$_slaPlan['slascheduleid']]['title']) . '<br />';
        }
        $_finalText .= '<b>' . $this->Language->Get('isenabled') . ':</b> ' . IIF($_slaPlan['isenabled'] == 1, $this->Language->Get('yes'), $this->Language->Get('no')) . '<br />';
        $_finalText .= '<b>' . $this->Language->Get('sortorder') . ':</b> ' . (int) ($_slaPlan['sortorder']) . '<br />';

        $_index = 1;
        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "slarulecriteria WHERE slaplanid = '" .  ($_slaPlanID) . "'");
        while ($this->Database->NextRecord())
        {
            $_finalText .= '<img src="' . SWIFT::Get('themepath') . 'images/linkdownarrow_blue.gif' . '" border="0" align="absmiddle" /> ' . $this->Language->Get('if') . ' <b>"' . $this->Language->Get('sr' . $this->Database->Record['name']) . '"</b> ' . SWIFT_Rules::GetOperText($this->Database->Record['ruleop']) . ' <b>"';

            $_extendedName = '';
            if (isset($_criteriaPointer[$this->Database->Record['name']]['fieldcontents']) && _is_array($_criteriaPointer[$this->Database->Record['name']]['fieldcontents']) && $_criteriaPointer[$this->Database->Record['name']]['field'] === 'custom')
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

            $_finalText .= htmlspecialchars(IIF(!empty($_extendedName), $_extendedName, $this->Database->Record['rulematch'])) . '"</b><br>';
            $_index++;
        }

        $_slaPlan['holidaylinks'] = array();
        $this->Database->Query("SELECT slaholidays.* FROM " . TABLE_PREFIX . "slaholidaylinks AS slaholidaylinks LEFT JOIN " . TABLE_PREFIX . "slaholidays AS slaholidays ON (slaholidaylinks.slaholidayid = slaholidays.slaholidayid) WHERE slaholidaylinks.slaplanid = '" .  ($_slaPlanID) . "'");
        while ($this->Database->NextRecord())
        {
            $_slaPlan['holidaylinks'][] = $this->Database->Record;
        }

        if (count($_slaPlan['holidaylinks']))
        {
            $_finalText .= '<br /><b>' . $this->Language->Get('linkedholidays') . '</b><br />';
            $_index = 1;
            foreach ($_slaPlan['holidaylinks'] as $_key => $_val)
            {
                $_finalText .= $_index . '. ' . IIF(!empty($_val['flagicon']), '<img src="' . str_replace('{$themepath}', SWIFT::Get('swiftpath') . SWIFT_BASEDIRECTORY . '/' . SWIFT_THEMESDIRECTORY . '/' . SWIFT_THEMEGLOBALDIRECTORY . '/flags/', $_val['flagicon']) . '" align="absmiddle" border="0" /> ') . htmlspecialchars($_val['title']) . '<br />';
                $_index++;
            }
        }

        SWIFT::Info($this->Language->Get('titleslaplan' . $__type), sprintf($this->Language->Get('msgslaplan' . $__type), htmlspecialchars($_slaPlan['title'])) . '<br />' . $_finalText);

        return true;
    }
}
