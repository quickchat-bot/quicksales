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
 * @license        http://www.opencart.com.vn/license
 * @link        http://www.opencart.com.vn
 *
 * ###############################################
 */

use Base\Models\Staff\SWIFT_Staff;

/**
 * The Report Schedule Model
 *
 * @author Andriy Lesyuk
 */
class SWIFT_ReportSchedule extends SWIFT_Model
{
    const TABLE_NAME        =    'reportschedules';
    const PRIMARY_KEY        =    'scheduleid';

    const TABLE_STRUCTURE    =    "scheduleid I PRIMARY AUTO NOTNULL,
                                 staffid I DEFAULT '0' NOTNULL,
                                 reportid I DEFAULT '0' NOTNULL,
                                 isexecuted I2 DEFAULT '0' NOTNULL,
                                 lastrun I DEFAULT '0',
                                 nextrun I DEFAULT '0' NOTNULL,
                                 cday I DEFAULT '0',
                                 format C(10) DEFAULT 'Excel' NOTNULL,
                                 recurrencetype I2 DEFAULT '0' NOTNULL,
                                 ccemails X";

    const INDEX_1 = 'staffid';
    const INDEX_2 = 'reportid';

    const REPORT_ITEMS_LIMIT = 1000;

    protected $_dataStore = array();

    // Core Constants
    const TYPE_ONCE = 0;
    const TYPE_HOURLY = 1;
    const TYPE_DAILY = 2;
    const TYPE_WEEKLY = 3;
    const TYPE_BIMONTHLY = 4;
    const TYPE_MONTHLY = 5;
    const TYPE_QUARTERLY = 6;
    const TYPE_YEARLY = 7;

    static protected $_recurrenceTypes = array(
        self::TYPE_ONCE => 'once',
        self::TYPE_HOURLY => 'hourly',
        self::TYPE_DAILY => 'daily',
        self::TYPE_WEEKLY => 'weekly',
        self::TYPE_BIMONTHLY => 'bimonthly',
        self::TYPE_MONTHLY => 'monthly',
        self::TYPE_QUARTERLY => 'quarterly',
        self::TYPE_YEARLY => 'yearly',
    );

    /**
     * Constructor
     *
     * @author Andriy Lesyuk
     * @param SWIFT_Data $_SWIFT_DataObject The SWIFT_Data Object
     * @throws SWIFT_Exception If the Record could not be loaded
     */
    public function __construct(SWIFT_Data $_SWIFT_DataObject)
    {
        parent::__construct();

        if (!$_SWIFT_DataObject instanceof SWIFT_Data || !$_SWIFT_DataObject->GetIsClassLoaded() || !$this->LoadData($_SWIFT_DataObject)) {
            throw new SWIFT_Exception('Failed to load ReportSchedule Object');
        }
    }

    /**
     * Destructor
     *
     * @author Andriy Lesyu
     */
    public function __destruct()
    {
        $this->ProcessUpdatePool();

        parent::__destruct();
    }

    /**
     * Retrieves the Report Schedule ID
     *
     * @author Andriy Lesyuk
     * @return mixed "reportcategoryid" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetScheduleID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore[self::GetPrimaryKeyName()];
    }

    /**
     * Update the Report Schedule Record
     *
     * @author Andriy Lesyuk
     * @param string $_exportFormat The Export Format
     * @param mixed $_recurrenceType The Recurrence Type
     * @param int $_firstExecutionDate The First Execution Date
     * @param SWIFT_Staff $_SWIFT_StaffObject
     * @param array $_ccEmails (OPTIONAL) CC Email Addresses
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function Update($_exportFormat, $_recurrenceType, $_firstExecutionDate, SWIFT_Staff $_SWIFT_StaffObject, $_ccEmails = array())
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } elseif (!SWIFT_ReportBase::IsValidExportFormat($_exportFormat) || !self::IsValidRecurrenceType($_recurrenceType) || empty($_firstExecutionDate) ||
            !$_SWIFT_StaffObject instanceof SWIFT_Staff || !$_SWIFT_StaffObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->UpdatePool('nextrun', ($_firstExecutionDate));
        $this->UpdatePool('format', $_exportFormat);
        $this->UpdatePool('recurrencetype', ($_recurrenceType));
        $this->UpdatePool('ccemails', implode(', ', $_ccEmails));

        if (($_recurrenceType == self::TYPE_MONTHLY) || ($_recurrenceType == self::TYPE_QUARTERLY) || ($_recurrenceType == self::TYPE_YEARLY)) {
            $_time = localtime(($_firstExecutionDate), true);

            $this->UpdatePool('cday', $_time['tm_mday']);
        }

        $this->ProcessUpdatePool();

        return true;
    }

    /**
     * Execute Report and Send it by Email
     *
     * @author Andriy Lesyuk
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function Execute()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_errorMessage = '';
        $_SWIFT_ReportExportObject = null;

        $_staffID = $this->GetProperty('staffid');
        $_reportID = $this->GetProperty('reportid');
        $_nextRun = $this->GetProperty('nextrun');
        $_exportFormat = $this->GetProperty('format');
        $_recurrenceType = $this->GetProperty('recurrencetype');
        $_ccEmails = $this->GetProperty('ccemails');

        $_SWIFT_StaffObject = new SWIFT_Staff(new SWIFT_DataID($_staffID));
        if (!$_SWIFT_StaffObject instanceof SWIFT_Staff || !$_SWIFT_StaffObject->GetIsClassLoaded()) {
            return false;
        }

        $_SWIFT_ReportObject = new SWIFT_Report(new SWIFT_DataID($_reportID));
        if (!$_SWIFT_ReportObject instanceof SWIFT_Report || !$_SWIFT_ReportObject->GetIsClassLoaded()) {
            return false;
        }

        $_ccEmailList = array();
        if (!empty($_ccEmails)) {
            $_ccEmailList = explode(', ', $_ccEmails);
        }

        if ($this->IsExecuting()) {
            if ((DATENOW - $_nextRun) > 7200) {
                $this->Log->Log('Assiming the execution has timed out: ' . $_SWIFT_ReportObject->GetProperty('title'));

                $this->StopExecuting();
            } else {
                $this->Log->Log('Skipping execution of report (it seems to be still executing): ' . $_SWIFT_ReportObject->GetProperty('title'));

                return false;
            }
        }

        $this->Log->Log('Executing report: ' . $_SWIFT_ReportObject->GetProperty('title'));

        $this->Language->Queue('default', SWIFT_LanguageEngine::TYPE_DB);
        $this->Language->LoadQueue(SWIFT_LanguageEngine::TYPE_DB);

        $this->Language->Load('reports_main', SWIFT_LanguageEngine::TYPE_FILE);
        $this->Language->Load('reports_schedule', SWIFT_LanguageEngine::TYPE_FILE);

        $_startTime = GetMicroTime();

        $this->StartExecuting();

        $_fileContainer = array();

        if (($_exportFormat != SWIFT_ReportExport::EXPORT_CSV) &&
            ($_exportFormat != SWIFT_ReportExport::EXPORT_HTML)) {

            /**
             * ---------------------------------------------
             * Export in Excel etc.
             * ---------------------------------------------
             */

            try {
                $_SWIFT_ReportExportObject = SWIFT_ReportExport::Process($_SWIFT_ReportObject, $_exportFormat);
            } catch (Exception $_ExceptionObject) {
                $_errorMessage = $_ExceptionObject->getMessage();
            }

            if (is_null($_SWIFT_ReportExportObject) || ($_SWIFT_ReportExportObject->GetRecordCount() == 0)) {
                $_errorMessage = $this->Language->Get('noexportresultfound');
            }

            if ($_SWIFT_ReportExportObject) {
                try {
                    $_fileContainer = $_SWIFT_ReportExportObject->GetFile($_exportFormat);
                } catch (Exception $_ExceptionObject) {
                    $_errorMessage = $_ExceptionObject->getMessage();
                }
            }
        } else {

            /**
             * ---------------------------------------------
             * Export in CSV/HTML
             * ---------------------------------------------
             */

            try {
                $_SWIFT_ReportExportObject = SWIFT_ReportRender::Process($_SWIFT_ReportObject, true);
            } catch (Exception $_ExceptionObject) {
                $_errorMessage = $_ExceptionObject->getMessage();
            }

            if (is_null($_SWIFT_ReportExportObject) || ($_SWIFT_ReportExportObject->GetRecordCount() == 0)) {
                $_errorMessage = $this->Language->Get('noexportresultfound');
            }

            if (empty($_errorMessage)) {

                $_exportFormatMap = $_SWIFT_ReportExportObject->GetExportFormatMap();

                if (isset($_exportFormatMap[$_exportFormat])) {
                    $_fileName = $_SWIFT_ReportExportObject->GetFilename();

                    $_fileContainer['mime-type'] = $_exportFormatMap[$_exportFormat][1];
                    $_fileContainer['filename'] = $_fileName . '.' . $_exportFormatMap[$_exportFormat][2];

                    $_tempFileName = SWIFT_FileManager::DEFAULT_TEMP_PREFIX . BuildHash();
                    $_tempFilePath = './' . SWIFT_BASEDIRECTORY . '/' . SWIFT_FILESDIRECTORY . '/' . $_tempFileName;

                    if ($_exportFormat == SWIFT_ReportExport::EXPORT_CSV) {
                        $_SWIFT_ReportExportObject->DispatchCSV($_tempFilePath);
                        $_fileContainer['content'] = file_get_contents($_tempFilePath);
                    } else {
                        ob_start();
                        $_SWIFT_ReportExportObject->DispatchHTML();
                        $_fileContainer['content'] = ob_get_contents();
                        ob_end_clean();
                    }

                    @unlink($_tempFilePath);
                } else {
                    $_errorMessage = SWIFT_INVALIDDATA;
                }
            }
        }

        $this->ReSchedule();
        $this->StopExecuting();

        $_endTime = GetMicroTime();

        $this->Log->Log('Report execution time: ' . number_format($_endTime - $_startTime, 5));

        $this->Load->Library('Mail:Mail');

        $_reportSendIntro = sprintf($this->Language->Get('reportsendintro'), $_SWIFT_StaffObject->GetProperty('fullname'));

        $this->Template->Assign('_reportSendIntro', $_reportSendIntro);
        $this->Template->Assign('_errorMessage', $_errorMessage);
        $this->Template->Assign('_hasCCEmails', (count($_ccEmailList) > 0));

        $_emailSubject = sprintf($this->Language->Get('reportsend' . self::$_recurrenceTypes[$_recurrenceType] . 'sub'), $_SWIFT_ReportObject->GetProperty('title'));
        if (!empty($_errorMessage)) {
            $_emailSubject .= ' (' . $this->Language->Get('reportnoattachment') . ')';
        }

        $_textEmailContents = $this->Template->Get('email_reportsend_text', SWIFT_TemplateEngine::TYPE_DB);
        $_htmlEmailContents = $this->Template->Get('email_reportsend_html', SWIFT_TemplateEngine::TYPE_DB);

        $_SWIFT_MailObject = new SWIFT_Mail();
        $_SWIFT_MailObject->SetFromField($this->Settings->Get('general_returnemail'), SWIFT::Get('companyname'));
        $_SWIFT_MailObject->SetToField($_SWIFT_StaffObject->GetProperty('email'), $_SWIFT_StaffObject->GetProperty('fullname'));

        if (empty($_errorMessage)) {
            foreach ($_ccEmailList as $_ccEmail) {
                $_SWIFT_MailObject->AddCC($_ccEmail);
            }
        }

        $_SWIFT_MailObject->SetSubjectField($_emailSubject);
        $_SWIFT_MailObject->SetDataText($_textEmailContents);
        $_SWIFT_MailObject->SetDataHTML($_htmlEmailContents);

        if (empty($_errorMessage)) {
            $_SWIFT_MailObject->Attach($_fileContainer['content'], $_fileContainer['mime-type'], $_fileContainer['filename']);
        }

        $_SWIFT_MailObject->SendMail();

        return true;
    }

    /**
     * Save Last Run and Calculate the Next Run Date
     *
     * @author Andriy Lesyuk
     * @return int Next Run Date or False
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    protected function ReSchedule()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_newNextRun = false;

        $_nextRun = $this->GetProperty('nextrun');
        $_cDay = $this->GetProperty('cday');
        $_recurrenceType = $this->GetProperty('recurrencetype');

        $_SWIFT_ReportObject = new SWIFT_Report(new SWIFT_DataID($this->GetProperty('reportid')));

        switch ($_recurrenceType)
        {
            case self::TYPE_HOURLY:
                $_newNextRun = SWIFT_CronManager::AddHour($_nextRun);
                break;

            case self::TYPE_DAILY:
                $_newNextRun = SWIFT_CronManager::AddDay($_nextRun);
                break;

            case self::TYPE_WEEKLY:
                $_newNextRun = SWIFT_CronManager::AddDay($_nextRun, 7);
                break;

            case self::TYPE_BIMONTHLY:
                $_newNextRun = SWIFT_CronManager::AddDay($_nextRun, 14);
                break;

            case self::TYPE_MONTHLY:
                $_newNextRun = SWIFT_CronManager::AddMonth($_nextRun, $_cDay);
                break;

            case self::TYPE_QUARTERLY:
                $_newNextRun = SWIFT_CronManager::AddMonth($_nextRun, $_cDay, 3);
                break;

            case self::TYPE_YEARLY:
                $_newNextRun = SWIFT_CronManager::AddYear($_nextRun, $_cDay);
                break;

            default: // self::TYPE_ONCE
                break;
        }

        if ($_newNextRun &&
            $_SWIFT_ReportObject instanceof SWIFT_Report && $_SWIFT_ReportObject->GetIsClassLoaded()) {

            $_lastRun = DATENOW;

            $this->UpdatePool('lastrun', $_lastRun);
            $this->UpdatePool('nextrun', $_newNextRun);

            $this->ProcessUpdatePool();

            $this->Log->Log('Scheduling execution for report (' . $_SWIFT_ReportObject->GetProperty('title') . ') to: ' . date('d M Y h:i:s A', $_newNextRun));
        } else {
            self::DeleteList(array($this->GetScheduleID()));
        }

        return $_newNextRun;
    }

    /**
     * Returns Whether the Report Schedule is Executing
     *
     * @author Andriy Lesyuk
     * @return bool True if Executing, False Otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    private function IsExecuting()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return ($this->_dataStore['isexecuted'] > 0);
    }

    /**
     * Marks the Report Schedule as Executing
     *
     * @author Andriy Lesyuk
     * @return bool Always True
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    private function StartExecuting()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UpdatePool('isexecuted', 1);

        $this->ProcessUpdatePool();

        return true;
    }

    /**
     * Marks the Report Schedule as Not Executing
     *
     * @author Andriy Lesyuk
     * @return bool Always True
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    private function StopExecuting()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UpdatePool('isexecuted', 0);

        $this->ProcessUpdatePool();

        return true;
    }

    /**
     * Create a New Report Schedule
     *
     * @author Andriy Lesyuk
     * @param string $_exportFormat The Export Format
     * @param mixed $_recurrenceType The Recurrence Type
     * @param int $_firstExecutionDate The First Execution Date
     * @param int $_reportID The Report ID
     * @param SWIFT_Staff $_SWIFT_StaffObject
     * @param array $_ccEmails (OPTIONAL) CC Email Addresses
     * @return int Report Schedule ID
     * @throws SWIFT_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function Create($_exportFormat, $_recurrenceType, $_firstExecutionDate, $_reportID, SWIFT_Staff $_SWIFT_StaffObject, $_ccEmails = array())
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!SWIFT_ReportBase::IsValidExportFormat($_exportFormat) || !self::IsValidRecurrenceType($_recurrenceType) || empty($_firstExecutionDate) || empty($_reportID) ||
            !$_SWIFT_StaffObject instanceof SWIFT_Staff || !$_SWIFT_StaffObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_staffID = $_SWIFT_StaffObject->GetStaffID();

        $_cDay = 0;
        $_recurrenceType = ($_recurrenceType);
        if (($_recurrenceType == self::TYPE_MONTHLY) || ($_recurrenceType == self::TYPE_QUARTERLY) || ($_recurrenceType == self::TYPE_YEARLY)) {
            $_time = localtime(($_firstExecutionDate), true);
            $_cDay = $_time['tm_mday'];
        }

        $_queryResult = $_SWIFT->Database->AutoExecute(TABLE_PREFIX . self::GetTableName(), array(
            'staffid' => ($_staffID),
            'reportid' => ($_reportID),
            'nextrun' => ($_firstExecutionDate),
            'cday' => $_cDay,
            'format' => $_exportFormat,
            'recurrencetype' => $_recurrenceType,
            'ccemails' => implode(', ', $_ccEmails)
         ), 'INSERT');

        $_scheduleID = $_SWIFT->Database->Insert_ID();
        if (!$_scheduleID) {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        return $_scheduleID;
    }

    /**
     * Delete Schedules Based on a List of Report IDs
     *
     * @author Andriy Lesyuk
     * @param array $_reportIDList
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteOnReport($_reportIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_reportIDList)) {
            return false;
        }

        $_reportSchedulesIDList = array();

        $_SWIFT->Database->Query("SELECT " . self::GetPrimaryKeyName() . " FROM " . TABLE_PREFIX . self::GetTableName() . " WHERE reportid IN (" . BuildIN($_reportIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_reportSchedulesIDList[] = $_SWIFT->Database->Record[self::GetPrimaryKeyName()];
        }

        if (!count($_reportSchedulesIDList)) {
            return false;
        }

        self::DeleteList($_reportSchedulesIDList);

        return true;
    }

    /**
     * Delete Schedules Based on a List of Staff IDs
     *
     * @author Andriy Lesyuk
     * @param array $_staffIDList
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteOnStaff($_staffIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_staffIDList)) {
            return false;
        }

        $_reportSchedulesIDList = array();

        $_SWIFT->Database->Query("SELECT " . self::GetPrimaryKeyName() . " FROM " . TABLE_PREFIX . self::GetTableName() . " WHERE staffid IN (" . BuildIN($_staffIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_reportSchedulesIDList[] = $_SWIFT->Database->Record[self::GetPrimaryKeyName()];
        }

        if (!count($_reportSchedulesIDList)) {
            return false;
        }

        self::DeleteList($_reportSchedulesIDList);

        return true;
    }

    /**
     * Retrieve Schedules for the Given Report and User
     *
     * @author Andriy Lesyuk
     * @param SWIFT_Report $_SWIFT_ReportObject
     * @param SWIFT_Staff $_SWIFT_StaffObject
     * @return array Schedules
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveOnReportAndStaff(SWIFT_Report $_SWIFT_ReportObject, SWIFT_Staff $_SWIFT_StaffObject)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_SWIFT_ReportObject instanceof SWIFT_Report || !$_SWIFT_ReportObject->GetIsClassLoaded() ||
            !$_SWIFT_StaffObject instanceof SWIFT_Staff || !$_SWIFT_StaffObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_reportSchedulesContainer = array();

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . self::GetTableName() . " WHERE reportid = '" . ($_SWIFT_ReportObject->GetReportID()) . "' AND staffid = '" . ($_SWIFT_StaffObject->GetStaffID()) . "' ORDER BY nextrun ASC");
        while ($_SWIFT->Database->NextRecord()) {
            $_reportSchedulesContainer[$_SWIFT->Database->Record[self::GetPrimaryKeyName()]] = new SWIFT_ReportSchedule(new SWIFT_DataStore($_SWIFT->Database->Record));
        }

        return $_reportSchedulesContainer;
    }

    /**
     * Retrieves Pending Schedules
     *
     * @author Andriy Lesyuk
     * @return array
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrievePendingSchedules()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_pendingSchedulesContainer = array();

        $_SWIFT->Database->QueryLimit("SELECT * FROM ". TABLE_PREFIX . self::GetTableName() . " WHERE nextrun <= '" . DATENOW . "' ORDER BY isexecuted ASC, nextrun ASC", self::REPORT_ITEMS_LIMIT);
        while ($_SWIFT->Database->NextRecord()) {
            $_pendingSchedulesContainer[] = new SWIFT_ReportSchedule(new SWIFT_DataStore($_SWIFT->Database->Record));
        }

        return $_pendingSchedulesContainer;
    }

    /**
     * Execute Pending Schedules
     *
     * @author Andriy Lesyuk
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function ExecutePendingSchedules()
    {
        $_pendingSchedulesContainer = self::RetrievePendingSchedules();

        if (!_is_array($_pendingSchedulesContainer)) {
            return false;
        }

        foreach ($_pendingSchedulesContainer as $_SWIFT_ReportScheduleObject) {
            if (!($_SWIFT_ReportScheduleObject instanceof SWIFT_ReportSchedule) || !$_SWIFT_ReportScheduleObject->GetIsClassLoaded()) {
                continue;
            }

            SWIFT::Set('schedulestaffid', $_SWIFT_ReportScheduleObject->GetProperty('staffid'));

            $_SWIFT_ReportScheduleObject->Execute();

            // Execute only one report at a time
            if (SWIFT_INTERFACE != 'console') {
                break;
            }
        }

        return true;
    }

    /**
     * Queries for Short Summary of Schedules
     *
     * @author Andriy Lesyuk
     * @param array $_scheduleIDList
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function QuerySummaryList($_scheduleIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_scheduleIDList)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT->Database->Query(
            "SELECT " . self::GetTableName() . ".*, " . SWIFT_Report::GetTableName() . ".title " .
            "FROM " . TABLE_PREFIX . self::GetTableName() . " " . self::GetTableName() . " " .
            "LEFT JOIN " . TABLE_PREFIX . SWIFT_Report::GetTableName() . " " . SWIFT_Report::GetTableName() . " ON " . self::GetTableName() . ".reportid = " . SWIFT_Report::GetTableName() . ".reportid " .
            "WHERE " . self::GetPrimaryKeyName() . " IN (" . BuildIN($_scheduleIDList) . ")"
        );

        return true;
    }

    /**
     * Check if the Recurrence Type is Valid
     *
     * @author Andriy Lesyuk
     * @return array
     */
    public static function GetSupportedRecurrenceTypes()
    {
        return self::$_recurrenceTypes;
    }

    /**
     * Check if the Recurrence Type is Valid
     *
     * @author Andriy Lesyuk
     * @param mixed $_recurrenceType
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidRecurrenceType($_recurrenceType)
    {
        return isset(self::$_recurrenceTypes[$_recurrenceType]);
    }

}
?>
