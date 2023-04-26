<?php
/**
 * ###############################################
 *
 * SWIFT Framework
 * _______________________________________________
 *
 * @author        Andriy Lesyuk
 *
 * @package        SWIFT
 * @copyright    Copyright (c) 2001-2012, QuickSupport
 * @license        http://www.kayako.com/license
 * @link        http://www.kayako.com
 *
 * ###############################################
 */

use Base\Admin\Controller_Staff;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel;
use Base\Models\Staff\SWIFT_StaffActivityLog;

/**
 * The Report Schedules Controller
 *
 * @author Andriy Lesyuk
 *
 * @method Controller_Schedule Method($param1 = '', $param2 = '', $param3 = '')
 * @method Controller_Schedule Controller($param1 = '', $param2 = '')
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @property Controller_Schedule $Load
 * @property View_Schedule $View
 */
class Controller_Schedule extends Controller_staff
{
    // Core Constants
    const MENU_ID = 9;
    const NAVIGATION_ID = 1;

    /**
     * Constructor
     *
     * @author Andriy Lesyuk
     */
    public function __construct()
    {
        parent::__construct();

        $this->Language->Load('reports_main');
        $this->Language->Load('reports_schedule');
    }

    /**
     * Loads the Display Data
     *
     * @author Andriy Lesyuk
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function _LoadDisplayData()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $this->UserInterface->AddNavigationBox($this->Language->Get('quickfilter'), SWIFT_ReportTreeRender::Render());

        return true;
    }

    /**
     * Displays Report Schedules
     *
     * @author Andriy Lesyuk
     * @param int $_reportID  The Report ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Manage($_reportID = 0)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_SWIFT_ReportObject = new SWIFT_Report(new SWIFT_DataID($_reportID));
        if (!$_SWIFT_ReportObject instanceof SWIFT_Report || !$_SWIFT_ReportObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        if ($_SWIFT->Staff->GetPermission('staff_rcanviewschedules') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->RenderReport($_SWIFT_ReportObject);
        }

        return true;
    }

    /**
     * Render the Schedule Dialog
     *
     * @author Andriy Lesyuk
     * @param int $_reportID The Report ID
     * @param int|false $_scheduleID The Report Schedule ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Dialog($_reportID, $_scheduleID = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_reportID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $_SWIFT_ReportScheduleObject = null;

        if (is_numeric($_scheduleID)) {
            $_SWIFT_ReportScheduleObject = new SWIFT_ReportSchedule(new SWIFT_DataID( ($_scheduleID)));
            if (!$_SWIFT_ReportScheduleObject instanceof SWIFT_ReportSchedule || !$_SWIFT_ReportScheduleObject->GetIsClassLoaded()) {
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            }

            $_reportID = $_SWIFT_ReportScheduleObject->GetProperty('reportid');
        }

        $_SWIFT_ReportObject = new SWIFT_Report(new SWIFT_DataID($_reportID));
        if (!$_SWIFT_ReportObject instanceof SWIFT_Report || !$_SWIFT_ReportObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->_LoadDisplayData();

        $this->UserInterface->Header($this->Language->Get('reports') . ' > ' . $this->Language->Get('schedule'), self::MENU_ID, self::NAVIGATION_ID);

        if ((!$_SWIFT_ReportScheduleObject && ($_SWIFT->Staff->GetPermission('staff_rcaninsertschedule') == '0')) ||
            ($_SWIFT_ReportScheduleObject && ($_SWIFT->Staff->GetPermission('staff_rcanupdateschedule') == '0'))) {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->RenderScheduleDialog($_SWIFT_ReportObject, $_SWIFT_ReportScheduleObject);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Schedule Submission Processor
     *
     * @author Andriy Lesyuk
     * @param int $_reportID The Report ID
     * @param int|bool $_scheduleID The Report Schedule ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Submit($_reportID, $_scheduleID = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_reportID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $_SWIFT_ReportScheduleObject = null;
        if (is_numeric($_scheduleID)) {
            $_SWIFT_ReportScheduleObject = new SWIFT_ReportSchedule(new SWIFT_DataID($_scheduleID));
            if (!$_SWIFT_ReportScheduleObject instanceof SWIFT_ReportSchedule || !$_SWIFT_ReportScheduleObject->GetIsClassLoaded()) {
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            }

            $_reportID = $_SWIFT_ReportScheduleObject->GetProperty('reportid');
            $_mode = SWIFT_UserInterface::MODE_EDIT;
        } else {
            $_mode = SWIFT_UserInterface::MODE_INSERT;
        }

        $_SWIFT_ReportObject = new SWIFT_Report(new SWIFT_DataID($_reportID));
        if (!$_SWIFT_ReportObject instanceof SWIFT_Report || !$_SWIFT_ReportObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if ($this->RunChecks($_mode)) {
            $_executionDate = GetDateFieldTimestamp('executiondate');
            $_recurrenceType = $this->GetRecurrenceTypeFromString($_POST['recurrence']);
            $_ccEmails = SWIFT_UserInterface::GetMultipleInputValues('schedulecc');

            if ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT_ReportScheduleObject !== null) {
                $_updateResult = $_SWIFT_ReportScheduleObject->Update($_POST['exportformat'], $_recurrenceType, $_executionDate, $_SWIFT->Staff, $_ccEmails);

                SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityupdatereportschedule'), text_to_html_entities($_SWIFT->Staff->GetProperty('fullname')), htmlspecialchars($_SWIFT_ReportObject->GetProperty('title'))),
                    SWIFT_StaffActivityLog::ACTION_UPDATE, SWIFT_StaffActivityLog::SECTION_REPORTS, SWIFT_StaffActivityLog::INTERFACE_STAFF);

                if (!$_updateResult) {
                    throw new SWIFT_Exception(SWIFT_INVALIDDATA);
                }

                $this->_RenderConfirmation($_mode);

                $this->Load->Controller('Report', 'Reports')->Load->Method('Edit', $_reportID, true);
            } else {
                $_scheduleID = SWIFT_ReportSchedule::Create($_POST['exportformat'], $_recurrenceType, $_executionDate, $_reportID, $_SWIFT->Staff, $_ccEmails);

                SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityinsertreportschedule'), text_to_html_entities($_SWIFT->Staff->GetProperty('fullname')), htmlspecialchars($_SWIFT_ReportObject->GetProperty('title'))),
                    SWIFT_StaffActivityLog::ACTION_INSERT, SWIFT_StaffActivityLog::SECTION_REPORTS, SWIFT_StaffActivityLog::INTERFACE_STAFF);

                if (!$_scheduleID) {
                    throw new SWIFT_Exception(SWIFT_INVALIDDATA);
                }

                $this->_RenderConfirmation($_mode);

                $this->Load->Controller('Report', 'Reports')->Load->Method('Edit', $_reportID);
            }

            return true;
        }

        $this->Load->Dialog($_reportID, $_scheduleID);

        return true;
    }

    /**
     * Delete the Report Schedules from Mass Action
     *
     * @author Andriy Lesyuk
     * @param array $_reportScheduleIDList The Report Schedule ID List Container Array
     * @param bool $_byPassCSRF Whether to bypass the CSRF check
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_reportScheduleIDList, $_byPassCSRF = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK
        if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
            SWIFT::Notify(SWIFT::NOTIFICATION_ERROR, $_SWIFT->Language->Get('notifycsrfhash'));

            return false;
        }
        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('staff_rcandeleteschedule') == '0') {
            SWIFT::Notify(SWIFT::NOTIFICATION_ERROR, $_SWIFT->Language->Get('notifynoperm'));

            return false;
        }

        if (_is_array($_reportScheduleIDList)) {
            SWIFT_ReportSchedule::QuerySummaryList($_reportScheduleIDList);
            while ($_SWIFT->Database->NextRecord()) {
                if ($_SWIFT->Database->Record['staffid'] != $_SWIFT->Staff->GetStaffID()) {
                    throw new SWIFT_Exception('You dont have permission to delete this report schedule');
                }

                SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activitydeletereportschedule'), text_to_html_entities($_SWIFT->Staff->GetProperty('fullname')), htmlspecialchars($_SWIFT->Database->Record['title'])),
                    SWIFT_StaffActivityLog::ACTION_DELETE, SWIFT_StaffActivityLog::SECTION_REPORTS, SWIFT_StaffActivityLog::INTERFACE_STAFF);
            }

            SWIFT_ReportSchedule::DeleteList($_reportScheduleIDList);
        }

        return true;
    }

    /**
     * Delete the Report Schedule
     *
     * @author Andriy Lesyuk
     * @param int $_scheduleID The Report Schedule ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete($_scheduleID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_ReportScheduleObject = new SWIFT_ReportSchedule(new SWIFT_DataID($_scheduleID));
        if (!$_SWIFT_ReportScheduleObject instanceof SWIFT_ReportSchedule || !$_SWIFT_ReportScheduleObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_ReportObject = new SWIFT_Report(new SWIFT_DataID($_SWIFT_ReportScheduleObject->GetProperty('reportid')));
        if (!$_SWIFT_ReportObject instanceof SWIFT_Report || !$_SWIFT_ReportObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        self::DeleteList(array($_scheduleID), true);

        $_schedulesContainer = SWIFT_ReportSchedule::RetrieveOnReportAndStaff($_SWIFT_ReportObject, $_SWIFT->Staff);

        $this->Load->Controller('Report', 'Reports')->Load->Method('Edit', $_SWIFT_ReportObject->GetReportID(), count($_schedulesContainer) > 0);

        return true;
    }

    /**
     * Runs the Checks for Insertion/Editing
     *
     * @author Andriy Lesyuk
     * @param int $_mode The User Interface Mode
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    private function RunChecks($_mode)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
            SWIFT::Notify(SWIFT::NOTIFICATION_ERROR, $_SWIFT->Language->Get('notifycsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_executionDate = GetDateFieldTimestamp('executiondate');

        if (!isset($_POST['exportformat']) || empty($_POST['exportformat']) || !SWIFT_ReportBase::IsValidExportFormat($_POST['exportformat'])) {
            $this->UserInterface->Error($this->Language->Get('titleinvalidformat'), $this->Language->Get('msginvalidformat'));

            return false;
        } elseif (!isset($_POST['recurrence']) || empty($_POST['recurrence']) || !$this->IsValidRecurrenceType($_POST['recurrence'])) {
            $this->UserInterface->Error($this->Language->Get('titleinvalidrecurrence'), $this->Language->Get('msginvalidrecurrence'));

            return false;
        } elseif (empty($_executionDate)) {
            SWIFT::ErrorField('executiondate');

            $this->UserInterface->Error($this->Language->Get('titleinvalidexecutiondate'), $this->Language->Get('msginvalidexecutiondate'));

            return false;
        } elseif ((DATENOW - $_executionDate) > 1800) {
            SWIFT::ErrorField('executiondate');

            $this->UserInterface->Error($this->Language->Get('titlepastexecutiondate'), $this->Language->Get('msgpastexecutiondate'));

            return false;
        } elseif (SWIFT_UserInterface::GetMultipleInputValues('schedulecc') && _is_array(SWIFT_UserInterface::GetMultipleInputValues('schedulecc')) && !$this->_CheckPOSTEmailContainer('schedulecc')) {
            SWIFT::ErrorField('schedulecc');

            $this->UserInterface->Error($this->Language->Get('titleinvalidcc'), $this->Language->Get('msginvalidcc'));

            return false;
        }

        if ((($_mode == SWIFT_UserInterface::MODE_INSERT) && ($_SWIFT->Staff->GetPermission('staff_rcaninsertschedule') == '0')) ||
            (($_mode == SWIFT_UserInterface::MODE_EDIT) && ($_SWIFT->Staff->GetPermission('staff_rcanupdateschedule') == '0'))) {
            SWIFT::Notify(SWIFT::NOTIFICATION_ERROR, $_SWIFT->Language->Get('notifynoperm'));

            return false;
        }

        return true;
    }

    /**
     * Checks if the Recurrence Type is Valid
     *
     * @author Andriy Lesyuk
     * @param string $_recurrenceType The Recurrence Type
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    private function IsValidRecurrenceType($_recurrenceType)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_recurrenceTypesContainer = SWIFT_ReportSchedule::GetSupportedRecurrenceTypes();

        return in_array($_recurrenceType, array_values($_recurrenceTypesContainer));
    }

    /**
     * Returns Recurrence Type ID by Name
     *
     * @author Andriy Lesyuk
     * @param string $_recurrenceType The Recurrence Type
     * @return int The Recurrence Type Numeric ID
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    private function GetRecurrenceTypeFromString($_recurrenceType)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_recurrenceTypesContainer = array_flip(SWIFT_ReportSchedule::GetSupportedRecurrenceTypes());
        if (isset($_recurrenceTypesContainer[$_recurrenceType])) {
            return $_recurrenceTypesContainer[$_recurrenceType];
        }

        return SWIFT_ReportSchedule::TYPE_ONCE;
    }

    /**
     * Check Validity of Emails in the Input Box
     *
     * @author Andriy Lesyuk
     * @param string $_fieldName The Field Name to Check On
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function _CheckPOSTEmailContainer($_fieldName)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_postEmailValues = SWIFT_UserInterface::GetMultipleInputValues($_fieldName);
        if (_is_array($_postEmailValues)) {
            foreach ($_postEmailValues as $_key => $_val) {
                if (!IsEmailValid($_val)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Render the Confirmation for Submit/Update
     *
     * @author Andriy Lesyuk
     * @param mixed $_mode The User Interface Mode
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    private function _RenderConfirmation($_mode)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_type = 'insert';
        if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            $_type = 'update';
        }

        SWIFT::Notify(SWIFT::NOTIFICATION_INFO, $this->Language->Get('notifyreportschedule' . $_type));

        return true;
    }

}
?>
