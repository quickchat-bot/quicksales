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

namespace Base\Admin;

use Controller_admin;
use SWIFT;
use SWIFT_Cron;
use SWIFT_CronLog;
use SWIFT_Exception;
use SWIFT_Session;
use Base\Models\Staff\SWIFT_StaffActivityLog;

/**
 * The Scheduled Tasks Controller
 *
 * @property \Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel $UserInterface
 * @property View_ScheduledTasks $View
 * @author Varun Shoor
 */
class Controller_ScheduledTasks extends Controller_admin
{
    // Core Constants
    const MENU_ID = 1;
    const NAVIGATION_ID = 10;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct()
    {
        parent::__construct();

        $this->Language->Load('tasks');
    }

    /**
     * Retrieve the Appropriate Cron Title
     *
     * @author Varun Shoor
     * @param string $_cronName The Cron Name
     * @return string The Cron Name or the Corresponding Cron Title (From Languages)
     */
    public static function _GetCronTitle($_cronName)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_cronName)) {
            return $_SWIFT->Language->Get('na');
        }

        $_languageTitle = $_SWIFT->Language->Get($_cronName);

        if (empty($_languageTitle)) {
            return $_cronName;
        }

        return $_languageTitle;
    }

    /**
     * Delete the Cron Logs from Mass Action
     *
     * @author Varun Shoor
     * @param mixed $_cronLogIDList The Cron Log ID List Container Array
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteLogList($_cronLogIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('admin_candeletescheduledtasklogs') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        if (_is_array($_cronLogIDList)) {
            SWIFT_CronLog::DeleteList($_cronLogIDList);
        }

        return true;
    }

    /**
     * Disable Cron Tasks from Mass Action
     *
     * @author Varun Shoor
     * @param mixed $_cronIDList The Cron ID List Container Array
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DisableList($_cronIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('admin_canupdatescheduledtasks') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        if (_is_array($_cronIDList)) {
            $_SWIFT->Database->Query("SELECT name FROM " . TABLE_PREFIX . "cron WHERE cronid IN (" . BuildIN($_cronIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activitydisablescheduledtask'), htmlspecialchars(self::_GetCronTitle($_SWIFT->Database->Record['name']))), SWIFT_StaffActivityLog::ACTION_UPDATE, SWIFT_StaffActivityLog::SECTION_SCHEDULEDTASKS, SWIFT_StaffActivityLog::INTERFACE_ADMIN);
            }

            SWIFT_Cron::DisableList($_cronIDList);
        }

        return true;
    }

    /**
     * Enable Cron Tasks from Mass Action
     *
     * @author Varun Shoor
     * @param mixed $_cronIDList The Cron ID List Container Array
     * @return bool "true" on Success, "false" otherwise
     */
    public static function EnableList($_cronIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('admin_canupdatescheduledtasks') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        if (_is_array($_cronIDList)) {
            $_SWIFT->Database->Query("SELECT name FROM " . TABLE_PREFIX . "cron WHERE cronid IN (" . BuildIN($_cronIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activityenablescheduledtask'), htmlspecialchars(self::_GetCronTitle($_SWIFT->Database->Record['name']))), SWIFT_StaffActivityLog::ACTION_UPDATE, SWIFT_StaffActivityLog::SECTION_SCHEDULEDTASKS, SWIFT_StaffActivityLog::INTERFACE_ADMIN);
            }

            SWIFT_Cron::EnableList($_cronIDList);
        }

        return true;
    }

    /**
     * Execute Cron Tasks from Mass Action
     *
     * @author Varun Shoor
     * @param mixed $_cronIDList The Cron ID List Container Array
     * @return bool "true" on Success, "false" otherwise
     */
    public static function ExecuteList($_cronIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('admin_canupdatescheduledtasks') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        if (_is_array($_cronIDList)) {
            $_SWIFT->Database->Query("SELECT name FROM " . TABLE_PREFIX . "cron WHERE cronid IN (" . BuildIN($_cronIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activityexecutescheduledtask'), htmlspecialchars(self::_GetCronTitle($_SWIFT->Database->Record['name']))), SWIFT_StaffActivityLog::ACTION_UPDATE, SWIFT_StaffActivityLog::SECTION_SCHEDULEDTASKS, SWIFT_StaffActivityLog::INTERFACE_ADMIN);
            }

            SWIFT_Cron::ExecuteList($_cronIDList);
        }

        return true;
    }

    /**
     * Displays the Scheduled Tasks Grid
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Manage()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UserInterface->Header($this->Language->Get('scheduledtasks') . ' > ' . $this->Language->Get('manage'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_canviewscheduledtasks') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->RenderGrid();
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Displays the Scheduled Tasks Log Grid
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function TaskLog()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UserInterface->Header($this->Language->Get('scheduledtasks') . ' > ' . $this->Language->Get('tasklog'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_canviewscheduledtasks') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->RenderTaskLogGrid();
        }

        $this->UserInterface->Footer();

        return true;
    }
}

?>
