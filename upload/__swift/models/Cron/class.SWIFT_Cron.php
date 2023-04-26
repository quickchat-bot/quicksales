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
 * @copyright    Copyright (c) 2001-2012, Kayako
 * @license        http://www.kayako.com/license
 * @link        http://www.kayako.com
 *
 * ###############################################
 */

/**
 * The Cron Record Class
 *
 * @author Varun Shoor
 */
class SWIFT_Cron extends SWIFT_Model
{
    const TABLE_NAME        =    'cron';
    const PRIMARY_KEY        =    'cronid';

    const TABLE_STRUCTURE    =    "cronid I PRIMARY AUTO NOTNULL,
                                nextrun I DEFAULT '0' NOTNULL,
                                lastrun I DEFAULT '0' NOTNULL,
                                chour I DEFAULT '0' NOTNULL,
                                cminute I DEFAULT '0' NOTNULL,
                                cday I DEFAULT '0' NOTNULL,
                                app C(255) DEFAULT '' NOTNULL,
                                controller C(255) DEFAULT '' NOTNULL,
                                action C(255) DEFAULT '' NOTNULL,
                                autorun I2 DEFAULT '0' NOTNULL,
                                name C(150) DEFAULT '' NOTNULL";

    const COLUMN_RENAME_MODULE    = 'app';

    protected $_dataStore = array();

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param int $_cronID The Cron ID
     * @throws SWIFT_Exception If the Record could not be loaded
     */
    public function __construct($_cronID)
    {
        parent::__construct();

        if (!$this->LoadData($_cronID))
        {
            throw new SWIFT_Cron_Exception('Failed to load Cron ID: ' . ($_cronID));
        }
    }

    /**
     * Destructor
     *
     * @author Varun Shoor
     * @throws SWIFT_Exception If the Record could not be loaded
     */
    public function __destruct()
    {
        $this->ProcessUpdatePool();

        parent::__destruct();
    }

    /**
     * Processes the Update Pool Data
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ProcessUpdatePool()
    {
        if (!_is_array($this->GetUpdatePool()))
        {
            return false;
        } else if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'cron', $this->GetUpdatePool(), 'UPDATE', "cronid = '". ($this->GetCronID()) ."'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Cron ID
     *
     * @author Varun Shoor
     * @return mixed "cronid" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetCronID()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Cron_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['cronid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param int $_cronID The Cron ID
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_cronID)
    {
        $_dataStore = $this->Database->QueryFetch("SELECT * FROM ". TABLE_PREFIX ."cron WHERE cronid = '". ($_cronID) ."'");
        if (isset($_dataStore['cronid']) && !empty($_dataStore['cronid']))
        {
            $this->_dataStore = $_dataStore;

            return true;
        }

        return false;
    }

    /**
     * Returns the Data Store Array
     *
     * @author Varun Shoor
     * @return mixed "_dataStore" Array on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetDataStore()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Cron_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore;
    }

    /**
     * Retrieves a Property Value from Data Store
     *
     * @author Varun Shoor
     * @param string $_key The Key Identifier
     * @return mixed Property Data on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetProperty($_key)
    {
        if (!$this->GetIsClassLoaded() || !isset($this->_dataStore[$_key]))
        {
            throw new SWIFT_Cron_Exception(SWIFT_INVALIDDATA);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Updates the Next Run Timeline for this Cron Task
     *
     * @author Varun Shoor
     * @param int $_nextRun The Next Run Timeline
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function UpdateNextRun($_nextRun)
    {
        $_nextRun = ($_nextRun);

        if (!$this->GetIsClassLoaded() || empty($_nextRun))
        {
            throw new SWIFT_Cron_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UpdatePool('nextrun', $_nextRun);
        $this->UpdatePool('lastrun', DATENOW);

        $this->ProcessUpdatePool();

        return true;
    }

    /**
     * Retrieve a new Cron record
     *
     * @author Varun Shoor
     * @param string $_taskName The Cron Task Name
     * @return mixed "SWIFT_Cron" (Object) on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function Retrieve($_taskName)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_taskName))
        {
            throw new SWIFT_Cron_Exception(SWIFT_INVALIDDATA);
        }

        $_cronContainer = $_SWIFT->Database->QueryFetch("SELECT cronid FROM " . TABLE_PREFIX . "cron WHERE name = '" .
                $_SWIFT->Database->Escape($_taskName) . "'");
        if (!isset($_cronContainer['cronid']) || empty($_cronContainer['cronid']))
        {
            return false;
        }

        return new SWIFT_Cron($_cronContainer['cronid']);
    }

    /**
     * Retrieves a Pending Task
     *
     * @author Varun Shoor
     * @return SWIFT_Cron|bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function RetrievePendingTask()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_cronContainer = $_SWIFT->Database->QueryFetch("SELECT cronid FROM ". TABLE_PREFIX ."cron WHERE nextrun <= '" . DATENOW .
                "' AND autorun = '1' ORDER BY nextrun ASC");
        if (!isset($_cronContainer['cronid']) || empty($_cronContainer['cronid']))
        {
            return false;
        }

        return new SWIFT_Cron($_cronContainer['cronid']);
    }

    /**
     * Create a new Cron Record
     *
     * @author Varun Shoor
     * @param string $_taskName The Task Name
     * @param string $_app The App under which the action is to be executed
     * @param string $_controller The Contorller to Load
     * @param string $_action The Action to Execute
     * @param int $_hour Hour (-1 for every 1 Hour, >0 for every X hours)
     * @param int $_minute Minute (-1 for every 1 Minute, >0 for every X Minutes)
     * @param int $_day Day (-1 for every 1 Day, >0 for every X Days)
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function Create($_taskName, $_app, $_controller, $_action, $_hour, $_minute, $_day, $_autoRun = true)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_taskName) || empty($_app) || empty($_action))
        {
            throw new SWIFT_Cron_Exception(SWIFT_INVALIDDATA);
        }

        $_taskName = Clean($_taskName);

        $_hour = ($_hour);
        $_minute = ($_minute);
        $_day = ($_day);

        if ($_hour < -1)
        {
            $_hour = -1;
        }

        if ($_minute < -1)
        {
            $_minute = -1;
        }

        if ($_day < -1)
        {
            $_day = -1;
        }

        $_queryResult = $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'cron', array('nextrun' => DATENOW, 'lastrun' => DATENOW, 'chour' => $_hour,
            'cminute' => $_minute, 'cday' => $_day, 'app' => $_app, 'controller' => $_controller, 'action' => $_action,
            'autorun' => ($_autoRun), 'name' => $_taskName), 'INSERT');
        if (!$_queryResult)
        {
            return false;
        }

        $_cronID = $_SWIFT->Database->Insert_ID();

        return $_cronID;
    }

    /**
     * Delete the cron record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Cron_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($this->GetCronID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Disable the cron record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Disable()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Cron_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DisableList(array($this->GetCronID()));

        return true;
    }

    /**
     * Enable the cron record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Enable()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Cron_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::EnableList(array($this->GetCronID()));

        return true;
    }

    /**
     * Execute the cron record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Execute()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Cron_Exception(SWIFT_CLASSNOTLOADED);
        }

        SWIFT_CronManager::RunTask($this->GetProperty('name'));

        return true;
    }

    /**
     * Delete the given Cron Tasks
     *
     * @author Varun Shoor
     * @param array $_cronIDList The Cron ID Container
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_cronIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_cronIDList))
        {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM ". TABLE_PREFIX ."cron WHERE cronid IN (". BuildIN($_cronIDList) .")");

        SWIFT_CronLog::DeleteOnCronID($_cronIDList);

        return true;
    }

    /**
     * Delete on Cron Name
     *
     * @author Varun Shoor
     * @param array|string $_nameList The Name List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function DeleteOnName($_nameList) {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_nameList)) {
            return false;
        }

        $_cronIDList = array();

        $_SWIFT->Database->Query("SELECT cronid FROM " . TABLE_PREFIX . "cron WHERE name IN (" . BuildIN($_nameList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_cronIDList[] = $_SWIFT->Database->Record['cronid'];
        }

        if (!_is_array($_cronIDList)) {
            return false;
        }

        self::DeleteList($_cronIDList);

        return true;
    }

    /**
     * Disable the given Cron Tasks
     *
     * @author Varun Shoor
     * @param array $_cronIDList The Cron ID Container
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function DisableList($_cronIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_cronIDList))
        {
            return false;
        }

        $_finalCronIDList = array();
        $_index = 1;
        $_finalText = '';

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "cron WHERE cronid IN (" . BuildIN($_cronIDList) . ")");
        while ($_SWIFT->Database->NextRecord())
        {
            $_finalCronIDList[] = $_SWIFT->Database->Record['cronid'];

            $_cronTitle = $_SWIFT->Database->Record['name'];

            if ($_SWIFT->Language->Get($_SWIFT->Database->Record['name']))
            {
                $_cronTitle = $_SWIFT->Language->Get($_SWIFT->Database->Record['name']);
            }

            $_finalText .= $_index . '. ' . htmlspecialchars($_cronTitle) . '<BR />';

            $_index++;
        }

        if (!count($_finalCronIDList))
        {
            return false;
        }

        SWIFT::Info(sprintf($_SWIFT->Language->Get('titledisablecron'), count($_finalCronIDList)), $_SWIFT->Language->Get('msgdisablecron') .
                '<BR />' . $_finalText);

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'cron', array('autorun' => '0'), 'UPDATE', "cronid IN (" . BuildIN($_finalCronIDList) . ")");

        return true;
    }

    /**
     * Enable the given Cron Tasks
     *
     * @author Varun Shoor
     * @param array $_cronIDList The Cron ID Container
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function EnableList($_cronIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_cronIDList))
        {
            return false;
        }

        $_finalCronIDList = array();
        $_index = 1;
        $_finalText = '';

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "cron WHERE cronid IN (" . BuildIN($_cronIDList) . ")");
        while ($_SWIFT->Database->NextRecord())
        {
            $_finalCronIDList[] = $_SWIFT->Database->Record['cronid'];

            $_cronTitle = $_SWIFT->Database->Record['name'];

            if ($_SWIFT->Language->Get($_SWIFT->Database->Record['name']))
            {
                $_cronTitle = $_SWIFT->Language->Get($_SWIFT->Database->Record['name']);
            }

            $_finalText .= $_index . '. ' . htmlspecialchars($_cronTitle) . '<BR />';

            $_index++;
        }

        if (!count($_finalCronIDList))
        {
            return false;
        }

        SWIFT::Info(sprintf($_SWIFT->Language->Get('titleenablecron'), count($_finalCronIDList)), $_SWIFT->Language->Get('msgenablecron') .
                '<BR />' . $_finalText);

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'cron', array('autorun' => '1'), 'UPDATE', "cronid IN (" . BuildIN($_finalCronIDList) . ")");

        return true;
    }

    /**
     * Execute the given Cron Tasks
     *
     * @author Varun Shoor
     * @param array $_cronIDList The Cron ID Container
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function ExecuteList($_cronIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_cronIDList))
        {
            return false;
        }

        $_finalCronIDList = array();
        $_index = 1;
        $_finalText = '';

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "cron WHERE cronid IN (" . BuildIN($_cronIDList) . ")");
        while ($_SWIFT->Database->NextRecord())
        {
            $_finalCronIDList[] = $_SWIFT->Database->Record['cronid'];

            $_cronTitle = $_SWIFT->Database->Record['name'];

            if ($_SWIFT->Language->Get($_SWIFT->Database->Record['name']))
            {
                $_cronTitle = $_SWIFT->Language->Get($_SWIFT->Database->Record['name']);
            }

            $_finalText .= $_index . '. ' . htmlspecialchars($_cronTitle) . '<BR />';

            $_index++;
        }

        if (!count($_finalCronIDList))
        {
            return false;
        }

        SWIFT::Info(sprintf($_SWIFT->Language->Get('titleexecutecron'), count($_finalCronIDList)), $_SWIFT->Language->Get('msgexecutecron') . '<BR />' . $_finalText);

        foreach ($_finalCronIDList as $_key => $_val)
        {
            $_CronObject = new SWIFT_Cron($_val);
            if ($_CronObject instanceof SWIFT_Cron && $_CronObject->GetIsClassLoaded())
            {
                $_CronObject->Execute();
            }
        }

        return true;
    }
}
