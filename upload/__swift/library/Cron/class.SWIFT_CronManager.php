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
 * @license        http://www.kayako.com/license
 * @link        http://www.kayako.com
 *
 * ###############################################
 */

/**
 * The Cron Manager Class. Responsible for execution and rescheduling of tasks.
 *
 * @author Varun Shoor
 */
class SWIFT_CronManager extends SWIFT_Library
{
    private $Cron = false;

    const CRON_CACHE = 'SWIFT_CronManager.cache';

    const TYPE_MINUTE = 1;
    const TYPE_HOURLY = 2;
    const TYPE_DAILY = 3;
    const TYPE_WEEKLY = 4;
    const TYPE_MONTHLY = 5;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param SWIFT_Cron $_SWIFT_CronObject The SWIFT_Cron Object Pointere
     */
    public function __construct(SWIFT_Cron $_SWIFT_CronObject)
    {
        $this->SetCronObject($_SWIFT_CronObject);

        parent::__construct();

        $this->Log = new SWIFT_Log('cron');
        $this->Language->Load('tasks');
    }

    /**
     * Destructor
     *
     * @author Varun Shoore
     */
    public function __destruct()
    {
        parent::__destruct();
    }

    /**
     * Set the Cron Object
     *
     * @author Varun Shoor
     * @param SWIFT_Cron $_SWIFT_CronObject The SWIFT_Cron Object Pointer
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public function SetCronObject(SWIFT_Cron $_SWIFT_CronObject)
    {
        if (!$_SWIFT_CronObject instanceof SWIFT_Cron || !$_SWIFT_CronObject->GetIsClassLoaded())
        {
            throw new SWIFT_Cron_Exception(SWIFT_INVALIDDATA);
        }

        $this->Cron = $_SWIFT_CronObject;

        return true;
    }

    /**
     * Get the Cron Object
     *
     * @author Varun Shoor
     * @return mixed "this->Cron" (OBJECT) on Success, "false" otherwise
     * @throws SWIFT_Cron_Exception If the Class is not Loaded
     */
    public function GetCronObject()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Cron_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        return $this->Cron;
    }

    /**
     * Runs the given task
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Cron_Exception If the Class is not Loaded
     */
    public function Run()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Cron_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        // We now update the next run for this cron job
        $_nextRunTimeline = $this->SaveNextRun();

        // Execute the cron task
        $_SWIFT_InterfaceObject = new SWIFT_Interface(SWIFT_Interface::INTERFACE_CRON);
        $_SWIFT_AppObject = SWIFT_App::Get($this->GetCronObject()->GetProperty('app'));
        $_SWIFT_RouterObject = new SWIFT_Router($this->GetCronObject()->GetProperty('app'), $this->GetCronObject()->GetProperty('controller'), $this->GetCronObject()->GetProperty('action'));

        SWIFT_CronLog::Create($this->Cron, '');

        SWIFT::Set('iscron', true);

        $this->Log->Log('Executing: ' . '/' . $this->GetCronObject()->GetProperty('app') . '/' . $this->GetCronObject()->GetProperty('controller') . '/' . $this->GetCronObject()->GetProperty('action'));
        SWIFT_Controller::Load($_SWIFT_InterfaceObject, $_SWIFT_AppObject, $_SWIFT_RouterObject);

        return true;
    }

    /**
     * Runs a Specific Task
     *
     * @author Varun Shoor
     * @param string $_taskName The Task Name
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Cron_Exception If Invalid Data Provided
     */
    public static function RunTask($_taskName)
    {
        $_SWIFT_CronObject = SWIFT_Cron::Retrieve($_taskName);
        if (!$_SWIFT_CronObject instanceof SWIFT_Cron || !$_SWIFT_CronObject->GetIsClassLoaded())
        {
            return false;
        }

        $_SWIFT_CronManagerObject = new SWIFT_CronManager($_SWIFT_CronObject);
        if (!$_SWIFT_CronManagerObject instanceof SWIFT_CronManager || !$_SWIFT_CronManagerObject->GetIsClassLoaded())
        {
            throw new SWIFT_Cron_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $_SWIFT_LogObject = new SWIFT_Log('cron');
        $_SWIFT_LogObject->Log('Running Task: ' . $_taskName);

        $_SWIFT_CronManagerObject->Run();

        return true;
    }

    /**
     * Runs the pending task
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Cron_Exception If Invalid Data Provided
     */
    public static function RunPendingTasks()
    {
        $_SWIFT_CronObject = SWIFT_Cron::RetrievePendingTask();
        if (!$_SWIFT_CronObject instanceof SWIFT_Cron || !$_SWIFT_CronObject->GetIsClassLoaded())
        {
            return false;
        }

        $_SWIFT_CronManagerObject = new SWIFT_CronManager($_SWIFT_CronObject);
        if (!$_SWIFT_CronManagerObject instanceof SWIFT_CronManager || !$_SWIFT_CronManagerObject->GetIsClassLoaded())
        {
            throw new SWIFT_Cron_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $_SWIFT_LogObject = new SWIFT_Log('cron');
        $_SWIFT_LogObject->Log('Running Pending Tasks');

        $_SWIFT_CronManagerObject->Run();

        return true;
    }

    /**
     * This function calculates the next run time for a particular cron task
     *
     * @author Varun Shoor
     * @return mixed "_saveNextRunValue" (INT) on Success, "false" otherwise
     * @throws SWIFT_Cron_Exception If the Class is not Loaded
     */
    protected function SaveNextRun()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Cron_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        // This is a very basic cron function, we wont need scripts to run at specific time of day so I didnt add specific time processing to this
        $_nextRun = $this->Cron->GetProperty('nextrun');
        $_saveNextRunValue = false;

        // We always add the next time from the time of execution
        $_nextRun = DATENOW;

        // Append one minute to this and save. We are supposed to run this every one minute
        if ($this->Cron->GetProperty('cminute') == -1 && $this->Cron->GetProperty('cday') == 0 && $this->Cron->GetProperty('chour') == 0)
        {
            $_saveNextRunValue = self::AddMinute($_nextRun);

        // Append cminute to this and save, this means we run it every x minutes
        } else if ($this->Cron->GetProperty('cminute') > 0 && $this->Cron->GetProperty('cday') == 0 && $this->Cron->GetProperty('chour')== 0) {
            $_saveNextRunValue = self::AddMinute($_nextRun, $this->Cron->GetProperty('cminute'));

        // We have to run this task every 1 hour
        } else if ($this->Cron->GetProperty('chour') == -1 && $this->Cron->GetProperty('cday')== 0 && $this->Cron->GetProperty('cminute') == 0) {
            $_saveNextRunValue = self::AddHour($_nextRun);

        // We have to run this task every X hours
        } else if ($this->Cron->GetProperty('chour') > 0 && $this->Cron->GetProperty('cday') == 0 && $this->Cron->GetProperty('cminute') == 0) {
            $_saveNextRunValue = self::AddHour($_nextRun, $this->Cron->GetProperty('chour'));

        // We have to run this task every 1 day
        } else if ($this->Cron->GetProperty('cday') == -1 && $this->Cron->GetProperty('cminute') == 0 && $this->Cron->GetProperty('chour') == 0) {
            $_saveNextRunValue = self::AddDay($_nextRun);

        // We have to run this task every X days
        } else if ($this->Cron->GetProperty('cday') > 0 && $this->Cron->GetProperty('chour') == 0 && $this->Cron->GetProperty('cminute') == 0) {
            $_saveNextRunValue = self::AddDay($_nextRun, $this->Cron->GetProperty('cday'));
        }

        // Seems like there was some clocking resetting here, time to set the date to a future one
        if ($_saveNextRunValue < DATENOW)
        {
            $_saveNextRunValue = DATENOW+1;
        }

        // We have save next run now, save it over
        if (!empty($_saveNextRunValue))
        {
            $this->Log->Log('Scheduling execution for: ' . $this->GetCronObject()->GetProperty('name') . ' (' . $this->GetCronObject()->GetProperty('app') . '/' . $this->GetCronObject()->GetProperty('controller') . '/' . $this->GetCronObject()->GetProperty('action') . ') to: ' . date('d M Y h:i:s A', $_saveNextRunValue));
            $this->Cron->UpdateNextRun($_saveNextRunValue);
        }

        return $_saveNextRunValue;
    }

    /**
     * Adds a Year (Tick by 1 or Append X)
     *
     * @author Andriy Lesyuk
     * @param int $_dateline The Dateline to Add to
     * @param int $_monthDay The Day of the Month
     * @param int $_yearRecord The Year Record Value
     * @return int Next Run Dateline
     */
    public static function AddYear($_dateline, $_monthDay, $_yearRecord = 0)
    {
        $_yearRecord = $_yearRecord;

        // If Its empty, we simply tick it by 1 day
        if (empty($_yearRecord)) {
            $_yearRecord = 1;
        }

        $_time = localtime($_dateline, true);

        $_modifiedDateline = mktime($_time['tm_hour'], $_time['tm_min'], $_time['tm_sec'], $_time['tm_mon'] + 1, $_monthDay, $_time['tm_year'] + $_yearRecord + 1900);

        // Fix the month day
        $_time = localtime($_modifiedDateline, true);
        if ($_time['tm_mday'] != $_monthDay) {
            // Assuming the last day should be used
            $_modifiedDateline = mktime($_time['tm_hour'], $_time['tm_min'], $_time['tm_sec'], $_time['tm_mon'] + 1, 0, $_time['tm_year'] + 1900);
        }

        return $_modifiedDateline;
    }

    /**
     * Adds a Month (Tick by 1 or Append X)
     *
     * @author Andriy Lesyuk
     * @param int $_dateline The Dateline to Add to
     * @param int $_monthDay The Day of the Month
     * @param int $_monthRecord The Month Record Value
     * @return int Next Run Dateline
     */
    public static function AddMonth($_dateline, $_monthDay, $_monthRecord = 0)
    {
        $_monthRecord = $_monthRecord;

        // If Its empty, we simply tick it by 1 day
        if (empty($_monthRecord)) {
            $_monthRecord = 1;
        }

        $_time = localtime($_dateline, true);

        $_modifiedDateline = mktime($_time['tm_hour'], $_time['tm_min'], $_time['tm_sec'], $_time['tm_mon'] + $_monthRecord + 1, $_monthDay, $_time['tm_year'] + 1900);

        // Fix the month day
        $_time = localtime($_modifiedDateline, true);
        if ($_time['tm_mday'] != $_monthDay) {
            // Assuming the last day should be used
            $_modifiedDateline = mktime($_time['tm_hour'], $_time['tm_min'], $_time['tm_sec'], $_time['tm_mon'] + 1, 0, $_time['tm_year'] + 1900);
        }

        return $_modifiedDateline;
    }

    /**
     * Adds a Day (Tick by 1 or Append X)
     *
     * @author Varun Shoor
     * @param int $_dateline The Dateline to Add to
     * @param int $_dayRecord The Day Record Value
     * @return int
     */
    public static function AddDay($_dateline, $_dayRecord = 0)
    {
        $_dayRecord = $_dayRecord;

        // If Its empty, we simply tick it by 1 day
        if (empty($_dayRecord))
        {
            return $_dateline+(1*60*60*24);

        // Otherwise, we add it to the record.. this means we run it every X days
        } else {
            return $_dateline+($_dayRecord*60*60*24);
        }
    }

    /**
     * Adds a Hour (Tick by 1 or Append X)
     *
     * @author Varun Shoor
     * @param int $_dateline The Dateline to Add to
     * @param int $_hourRecord The Hour Record Value
     * @return int
     */
    public static function AddHour($_dateline, $_hourRecord = 0)
    {
        $_hourRecord = $_hourRecord;

        // If Its empty, we simply tick it by 1 hour
        if (empty($_hourRecord))
        {
            return $_dateline+(1*60*60);

        // Otherwise, we add it to the record.. this means we run it every X hours
        } else {
            return $_dateline+($_hourRecord*60*60);
        }
    }

    /**
     * Adds a Minute (Tick by 1 or Append X)
     *
     * @author Varun Shoor
     * @param int $_dateline The Dateline to Add to
     * @param int $_minuteRecord The Minute Record Value
     * @return int
     */
    public static function AddMinute($_dateline, $_minuteRecord = 0)
    {
        $_minuteRecord = $_minuteRecord;

        // If Its empty, we simply tick it by 1 hour
        if (empty($_minuteRecord))
        {
            return $_dateline+(60);

        // Otherwise, we add it to the record.. this means we run it every X minutes
        } else {
            return $_dateline+($_minuteRecord*60);
        }
    }

    /**
     * Check to see if its a valid cron type
     *
     * @author Varun Shoor
     * @param mixed $_cronType
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidCronType($_cronType)
    {
        return ($_cronType == self::TYPE_MINUTE || $_cronType == self::TYPE_HOURLY || $_cronType == self::TYPE_DAILY || $_cronType == self::TYPE_WEEKLY || $_cronType == self::TYPE_MONTHLY);
    }

    /**
     * Run the cron on all available models
     *
     * @author Varun Shoor
     * @param mixed $_cronType
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RunModelCron($_cronType)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!self::IsValidCronType($_cronType)) {
            throw new SWIFT_Exception(__CLASS__ . ': ' . SWIFT_INVALIDDATA);
        }

        $_modelList = self::RetrieveModelList($_cronType);

        $_functionName = self::RetrieveCronFunctionName($_cronType);

        $_SWIFT_LogObject = new SWIFT_Log('cron');

        foreach ($_modelList as $_modelContainer) {
            $_modelLoadName = $_modelContainer[0];
            $_modelName = $_modelContainer[1];
            $_modelFilePath = $_modelContainer[2];
            $_appName = $_modelContainer[3];

            SWIFT_Loader::LoadModel($_modelLoadName, $_appName);

            $_SWIFT_LogObject->Log('Running Model Cron: SWIFT_' . $_modelName . '::' . $_functionName . '()');
            call_user_func_array(array('SWIFT_' . $_modelName, $_functionName), array());
        }

        return true;
    }

    /**
     * Retrieve the Cron Function Name
     *
     * @author Varun Shoor
     * @param mixed $_cronType
     * @return string
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveCronFunctionName($_cronType)
    {
        if (!self::IsValidCronType($_cronType)) {
            throw new SWIFT_Exception(__CLASS__ . ': ' . SWIFT_INVALIDDATA);
        }

        switch ($_cronType) {
            case self::TYPE_MINUTE:
                return 'Minute';

                break;

            case self::TYPE_HOURLY:
                return 'Hourly';

                break;

            case self::TYPE_DAILY:
                return 'Daily';

                break;

            case self::TYPE_WEEKLY:
                return 'Weekly';

                break;

            case self::TYPE_MONTHLY:
                return 'Monthly';

                break;

            default:
                break;
        }

        throw new SWIFT_Exception(__CLASS__ . ': ' . SWIFT_INVALIDDATA);
    }

    /**
     * Retrieve the list of models based on type
     *
     * @author Varun Shoor
     * @param mixed $_cronType
     * @return array (modelLoadString, modelName, modelFilePath, appName)
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    protected static function RetrieveModelList($_cronType)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!self::IsValidCronType($_cronType)) {
            throw new SWIFT_Exception(__CLASS__ . ': ' . SWIFT_INVALIDDATA);
        }

        chdir(SWIFT_BASEPATH);

        $_functionName = self::RetrieveCronFunctionName($_cronType);

        $_cronCachePath = './' . SWIFT_BASE_DIRECTORY . '/' . SWIFT_CACHE_DIRECTORY . '/' . self::CRON_CACHE;

        $_cronCache = array();
        if (file_exists($_cronCachePath)) {
            $_cronCache = unserialize(file_get_contents($_cronCachePath));
            if (!SWIFT::IsDebug() && _is_array($_cronCache) && isset($_cronCache[$_cronType])) {
                return $_cronCache[$_cronType];
            }
        }

        $_returnContainer = array();

        $_appList = SWIFT_App::GetInstalledApps();
        foreach ($_appList as $_appName) {
            $_SWIFT_AppObject = false;

            try {
                $_SWIFT_AppObject = SWIFT_App::Get($_appName);
            } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            }

            if (!$_SWIFT_AppObject instanceof SWIFT_App || !$_SWIFT_AppObject->GetIsClassLoaded()) {
                continue;
            }

            $_returnContainer = array_merge($_returnContainer, $_SWIFT_AppObject->RetrieveFileList(SWIFT_App::FILETYPE_MODEL, $_functionName));
        }

        $_cronCache[$_cronType] = $_returnContainer;

        file_put_contents($_cronCachePath, serialize($_cronCache));
        @chmod($_cronCachePath, 0666);

        return $_returnContainer;
    }
}
?>