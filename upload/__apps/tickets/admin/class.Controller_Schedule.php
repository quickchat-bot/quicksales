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
use SWIFT_Exception;
use SWIFT_Session;
use Base\Models\Staff\SWIFT_StaffActivityLog;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Tickets\Models\SLA\SWIFT_SLA;
use Tickets\Models\SLA\SWIFT_SLASchedule;

/**
 * The Schedule Controller
 *
 * @method Library($_libraryName, $_arguments = [], $_initiateInstance = false, $_customAppName = '', $_appName = '')
 * @property Controller_Schedule $Load
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @property View_Schedule $View
 * @author Varun Shoor
 */
class Controller_Schedule extends Controller_admin
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
     * Delete the SLA Schedules from Mass Action
     *
     * @author Varun Shoor
     * @param mixed $_slaScheduleIDList The SLA Schedule ID List Container Array
     * @param bool $_byPassCSRF Whether to bypass the CSRF check
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function DeleteList($_slaScheduleIDList, $_byPassCSRF = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash']))
        {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('admin_tcandeleteslaschedules') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        if (_is_array($_slaScheduleIDList)) {
            $_SWIFT->Database->Query("SELECT title FROM ". TABLE_PREFIX ."slaschedules WHERE slascheduleid IN (" . BuildIN($_slaScheduleIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activitydeleteslaschedule'), htmlspecialchars($_SWIFT->Database->Record['title'])), SWIFT_StaffActivityLog::ACTION_DELETE, SWIFT_StaffActivityLog::SECTION_TICKETS, SWIFT_StaffActivityLog::INTERFACE_ADMIN);
            }

            SWIFT_SLASchedule::DeleteList($_slaScheduleIDList);
        }

        return true;
    }

    /**
     * Delete the Given SLA Schedule ID
     *
     * @author Varun Shoor
     * @param int $_slaScheduleID The SLA Schedule ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete($_slaScheduleID)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($_slaScheduleID), true);

        $this->Load->Manage();

        return true;
    }

    /**
     * Displays the SLA Schedule Grid
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

        $this->UserInterface->Header($this->Language->Get('sla') . ' > ' . $this->Language->Get('schedules'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_tcanviewslaschedules') == '0')
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
     * @return mixed "_days" (ARRAY) on Success, "false" otherwise
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

        $_continue = true;
        $_errorHours = array();

        $_days = array();

        if (trim($_POST['title']) == '')
        {
            $this->UserInterface->CheckFields('title');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            return false;
        }

        if (SWIFT::Get('isdemo') == true) {
            $this->UserInterface->Error($this->Language->Get('titledemomode'), $this->Language->Get('msgdemomode'));

            return false;
        }

        if (($_mode == SWIFT_UserInterface::MODE_INSERT && $_SWIFT->Staff->GetPermission('admin_tcaninsertslaschedules') == '0') || ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT->Staff->GetPermission('admin_tcanupdateslaschedules') == '0')) {
            $this->UserInterface->Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));

            return false;
        }

        if (isset($_POST['sladay']) && !count($_POST['sladay']))
        {
            return false;
        }

        foreach ($_POST['sladay'] as $_key => $_val)
        {
            $_days[$_key]['type'] = $_val;
            $_days[$_key]['hours'] = array();

            // Custom?
            if ($_val == SWIFT_SLASchedule::SCHEDULE_DAYOPEN)
            {
                if (isset($_POST['dayHourOpen'][$_key]) && _is_array($_POST['dayHourOpen'][$_key]))
                {
                    foreach ($_POST['dayHourOpen'][$_key] as $_hourKey => $_hourVal)
                    {
                        $_openFloat = (float)($_hourVal . '.' . $_POST['dayMinuteOpen'][$_key][$_hourKey]);
                        $_closeFloat = (float)($_POST['dayHourClose'][$_key][$_hourKey] . '.' . $_POST['dayMinuteClose'][$_key][$_hourKey]);

                        $_days[$_key]['hours'][] = array($_hourVal . ':' . $_POST['dayMinuteOpen'][$_key][$_hourKey], $_POST['dayHourClose'][$_key][$_hourKey] . ':' . $_POST['dayMinuteClose'][$_key][$_hourKey]);

                        if ($_closeFloat < $_openFloat)
                        {
                            $_errorHours[$_key][] = $_hourVal . ':' . $_POST['dayMinuteOpen'][$_key][$_hourKey] . ' => ' . $_POST['dayHourClose'][$_key][$_hourKey] . ':' . $_POST['dayMinuteClose'][$_key][$_hourKey];
                            $_continue = false;
                        }
                    }
                }
            }
        }

        if (!$_continue)
        {
            $_index = 1;
            $_finalText = '';
            foreach ($_errorHours as $_key => $_val)
            {
                $_finalText .= $_index . '. ' . ucfirst($_key) . '<br />';
                foreach ($_val as $_hourKey => $_hourVal)
                {
                    $_finalText .= '&nbsp;&nbsp;<img src="' . SWIFT::Get('themepath') . 'images/linkdownarrow_blue.gif" align="absmiddle" border="0" /> ' . $_hourVal . '<br />';
                }

                $_index++;
            }

            $this->UserInterface->Error($this->Language->Get('titleinvalidhrange'), $this->Language->Get('msginvalidhrange') . '<br />' . $_finalText);

            return false;
        }

        return $_days;
    }

    /**
     * Insert a new SLA Schedule
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

        $this->UserInterface->Header($this->Language->Get('sla') . ' > ' . $this->Language->Get('insertschedule'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_tcaninsertslaschedules') == '0')
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
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function InsertSubmit()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($_daysContainer = $this->RunChecks(SWIFT_UserInterface::MODE_INSERT))
        {
            $_slaScheduleID = SWIFT_SLASchedule::Create($_POST['title'], $_daysContainer);

            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityslascheduleinsert'), htmlspecialchars($_POST['title'])), SWIFT_StaffActivityLog::ACTION_INSERT, SWIFT_StaffActivityLog::SECTION_TICKETS, SWIFT_StaffActivityLog::INTERFACE_ADMIN);

            if (!$_slaScheduleID)
            {
                // @codeCoverageIgnoreStart
                // this code will never be executed
                return false;
                // @codeCoverageIgnoreEnd
            }

            $this->_RenderConfirmation(SWIFT_UserInterface::MODE_INSERT, $_slaScheduleID);

            $this->Load->Manage();

            return true;
        }

        $this->Load->Insert();

        return false;
    }

    /**
     * Edit the SLA Schedule
     *
     * @author Varun Shoor
     * @param int $_slaScheduleID The SLA Schedule ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Edit($_slaScheduleID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_slaScheduleID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_SLAScheduleObject = new SWIFT_SLASchedule($_slaScheduleID);
        if (!$_SWIFT_SLAScheduleObject instanceof SWIFT_SLASchedule || !$_SWIFT_SLAScheduleObject->GetIsClassLoaded())
        {
            // @codeCoverageIgnoreStart
            // this code will never be executed
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }

        foreach (SWIFT_SLA::GetDays() as $_key => $_val)
        {
            if (!isset($_POST['sladay'][$_val]))
            {
                $_POST['sladay'][$_val] = $_SWIFT_SLAScheduleObject->GetProperty($_val . '_open');
                $_POST['rowId'][$_val] = array();
                $_POST['dayHourOpen'][$_val] = array();
                $_POST['dayMinuteOpen'][$_val] = array();
                $_POST['dayHourClose'][$_val] = array();
                $_POST['dayMinuteClose'][$_val] = array();
            }
        }

        $_slaScheduleTable = array();
        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "slascheduletable WHERE slascheduleid = '" .  ($_slaScheduleID) . "'");
        while ($this->Database->NextRecord())
        {
            //check if the record already exists in post data (i.e user entered invalid data and re-rendering happens)
            if(isset($_POST['rowId'][$this->Database->Record['sladay']]) &&
               in_array($this->Database->Record['slascheduletableid'], $_POST['rowId'][$this->Database->Record['sladay']])) {
                continue;
            }

            $_slaScheduleTable[$this->Database->Record['sladay']][] = $this->Database->Record;

            $_openArray = explode(':', $this->Database->Record['opentimeline']);
            $_closeArray = explode(':', $this->Database->Record['closetimeline']);

            $_POST['rowId'][$this->Database->Record['sladay']][] = $this->Database->Record['slascheduletableid'];
            $_POST['dayHourOpen'][$this->Database->Record['sladay']][] = $_openArray[0];
            $_POST['dayMinuteOpen'][$this->Database->Record['sladay']][] = $_openArray[1];
            $_POST['dayHourClose'][$this->Database->Record['sladay']][] = $_closeArray[0];
            $_POST['dayMinuteClose'][$this->Database->Record['sladay']][] = $_closeArray[1];
        }

        $this->UserInterface->Header($this->Language->Get('sla') . ' > ' . $this->Language->Get('editschedule'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_tcanupdateslaschedules') == '0')
        {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render(SWIFT_UserInterface::MODE_EDIT, $_SWIFT_SLAScheduleObject);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Edit Submission Processor
     *
     * @author Varun Shoor
     * @param int $_slaScheduleID The SLA Schedule ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function EditSubmit($_slaScheduleID)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_slaScheduleID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_SLAScheduleObject = new SWIFT_SLASchedule($_slaScheduleID);
        if (!$_SWIFT_SLAScheduleObject instanceof SWIFT_SLASchedule || !$_SWIFT_SLAScheduleObject->GetIsClassLoaded())
        {
            // @codeCoverageIgnoreStart
            // this code will never be executed
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }

        if ($_daysContainer = $this->RunChecks(SWIFT_UserInterface::MODE_EDIT))
        {
            $_updateResult = $_SWIFT_SLAScheduleObject->Update($_POST['title'], $_daysContainer);

            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityslascheduleupdate'), htmlspecialchars($_POST['title'])), SWIFT_StaffActivityLog::ACTION_UPDATE, SWIFT_StaffActivityLog::SECTION_TICKETS, SWIFT_StaffActivityLog::INTERFACE_ADMIN);

            if (!$_updateResult)
            {
                // @codeCoverageIgnoreStart
                // this code will never be executed
                return false;
                // @codeCoverageIgnoreEnd
            }

            $this->_RenderConfirmation(SWIFT_UserInterface::MODE_EDIT, $_slaScheduleID);

            $this->Load->Manage();

            return true;
        }

        $this->Load->Edit($_slaScheduleID);

        return false;
    }

    /**
     * Render the User Interface Confirmation
     *
     * @author Varun Shoor
     * @param mixed $_mode The UI Mode
     * @param int $_slaScheduleID The SLA Schedule ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    private function _RenderConfirmation($_mode, $_slaScheduleID)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_slaSchedule = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "slaschedules WHERE slascheduleid = '" .  ($_slaScheduleID) . "'");
        if (!isset($_slaSchedule['slascheduleid']) || empty($_slaSchedule['slascheduleid']))
        {
            return false;
        }

        $_slaScheduleTable = array();

        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "slascheduletable WHERE slascheduleid = '" . (int) ($_slaSchedule['slascheduleid']) . "'");
        while ($this->Database->NextRecord())
        {
            $_slaScheduleTable[$this->Database->Record['sladay']][] = $this->Database->Record;
        }

        $_finalText = '';

        $_index = 1;
        foreach (SWIFT_SLA::GetDays() as $_key => $_val)
        {
            $str = $_val . '_open';
            if (isset($_slaSchedule[$str]) && $_slaSchedule[$str] == SWIFT_SLASchedule::SCHEDULE_DAYOPEN)
            {
                $_dayType = $this->Language->Get('sladayopencustom');
            } else if (isset($_slaSchedule[$str]) && $_slaSchedule[$str] == SWIFT_SLASchedule::SCHEDULE_DAYOPEN24) {
                $_dayType = $this->Language->Get('sladayopen24');
            } else {
                $_dayType = $this->Language->Get('sladayclosed');
            }

            $_finalText .= $_index . '. <b>' . ucfirst($this->Language->Get($_val)) . '</b>: ' . $_dayType . '<br />';
            if (isset($_slaScheduleTable[$_val]) && _is_array($_slaScheduleTable[$_val]))
            {
                foreach ($_slaScheduleTable[$_val] as $_hourKey => $_hourVal)
                {
                    $_finalText .= '&nbsp;&nbsp;<img src="' . SWIFT::Get('themepath') . 'images/linkdownarrow_blue.gif" border="0" align="absmiddle" /> ' . htmlspecialchars($_hourVal['opentimeline']) . ' => ' . htmlspecialchars($_hourVal['closetimeline']) . '<br />';
                }
            }
            $_index++;
        }

        /** Bug FIX : Nidhi Gupta <nidhi.gupta@opencart.com.vn>
         *
         * SWIFT-4449 : Wrong message after creating/updating SLA Schedule
         */
        if ($_mode == SWIFT_UserInterface::MODE_INSERT) {
            SWIFT::Info($this->Language->Get('titleinsertslasched'), sprintf($this->Language->Get('msginsertslasched'), htmlspecialchars($_POST['title'])) . '<br />' . $_finalText);
        } else if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            SWIFT::Info($this->Language->Get('titleupdateslasched'), sprintf($this->Language->Get('msgupdateslasched'), htmlspecialchars($_POST['title'])) . '<br />' . $_finalText);
        }

        return true;
    }
}
