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

/**
 * The Cron Log Manager
 *
 * @author Varun Shoor
 */
class SWIFT_CronLog extends SWIFT_Model
{
    const TABLE_NAME        =    'cronlogs';
    const PRIMARY_KEY        =    'cronlogid';

    const TABLE_STRUCTURE    =    "cronlogid I PRIMARY AUTO NOTNULL,
                                cronid I DEFAULT '0' NOTNULL,
                                crontitle C(255) DEFAULT '' NOTNULL,
                                dateline I DEFAULT '0' NOTNULL,
                                description C(255) DEFAULT '' NOTNULL";


    protected $_dataStore = array();

    /**
     * Constructor
     *
     * @author Varun Shoor
     */
    public function __construct()
    {
        parent::__construct();

        self::LoadLanguageSection();
    }

    /**
     * Destructor
     *
     * @author Varun Shoor
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
     * @throws SWIFT_Cron_Exception If the Class is not Loaded
     */
    public function ProcessUpdatePool()
    {
        if (!_is_array($this->GetUpdatePool()))
        {
            return false;
        } else if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Cron_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'cronlogs', $this->GetUpdatePool(), 'UPDATE', "cronlogid = '". (int) ($this->GetCronLogID()) ."'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Cron Log ID
     *
     * @author Varun Shoor
     * @return mixed "cronlogid" on Success, "false" otherwise
     * @throws SWIFT_Cron_Exception If the Class is not Loaded
     */
    public function GetCronLogID()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Cron_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['cronlogid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param int $_cronLogID The Cron Log ID
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_cronLogID)
    {
        $_dataStore = $this->Database->QueryFetch("SELECT * FROM ". TABLE_PREFIX ."cronlogs WHERE cronlogid = '".  ($_cronLogID) ."'");
        if (isset($_dataStore['cronlogid']) && !empty($_dataStore['cronlogid']))
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
     * @throws SWIFT_Cron_Exception If the Class is not Loaded
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
     * @throws SWIFT_Cron_Exception If the Class is not Loaded
     */
    public function GetProperty($_key)
    {
        if (!$this->GetIsClassLoaded() || !isset($this->_dataStore[$_key]))
        {
            throw new SWIFT_Cron_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Load the Task Language Section
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public static function LoadLanguageSection()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_SWIFT->Language->Load('tasks');

        return true;
    }

    /**
     * Create a new Cron Log entry
     *
     * @author Varun Shoor
     * @param SWIFT_Cron $_SWIFT_CronObject The SWIFT_Cron Object Pointer
     * @param string $_description The Task Description
     * @return mixed "cronlogid" (INT) on Success, "false" otherwise
     * @throws SWIFT_Cron_Exception If Invalid Data Provided or if Object Creation Fails
     */
    public static function Create(SWIFT_Cron $_SWIFT_CronObject, $_description)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_SWIFT_CronObject instanceof SWIFT_Cron || !$_SWIFT_CronObject->GetIsClassLoaded())
        {
            throw new SWIFT_Cron_Exception(SWIFT_INVALIDDATA);
        }

        self::LoadLanguageSection();

        if ($_SWIFT->Language->Get($_SWIFT_CronObject->GetProperty('name')))
        {
            $_cronTitle = $_SWIFT->Language->Get($_SWIFT_CronObject->GetProperty('name'));
        } else {
            $_cronTitle = $_SWIFT_CronObject->GetProperty('name');
        }

        $_queryResult = $_SWIFT->Database->AutoExecute(TABLE_PREFIX.'cronlogs', array('cronid' => (int) ($_SWIFT_CronObject->GetCronID()), 'crontitle' => $_cronTitle, 'dateline' => DATENOW, 'description' => $_description), 'INSERT');
        if (!$_queryResult)
        {
            throw new SWIFT_Cron_Exception(SWIFT_CREATEFAILED);
        }

        $_cronLogID = $_SWIFT->Database->Insert_ID();

        return $_cronLogID;
    }

    /**
     * Delete the Cron Log ID record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Cron_Exception If the Class is not Loaded
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Cron_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($this->GetCronLogID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of cron log records
     *
     * @author Varun Shoor
     * @param array $_cronLogIDList The Cron Log ID Container
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_cronLogIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_cronLogIDList))
        {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "cronlogs WHERE cronlogid IN (" . BuildIN($_cronLogIDList) . ")");

        return true;
    }

    /**
     * Delete a list of cron log records
     *
     * @author Varun Shoor
     * @param array $_cronIDList The Cron ID Container
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteOnCronID($_cronIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_cronIDList))
        {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM ". TABLE_PREFIX ."cronlogs WHERE cronid IN (". BuildIN($_cronIDList) .")");

        return true;
    }

    /**
     * Cleanup all old logs according to settings
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function CleanUp()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_dateThreshold = DATENOW - (86400 * $_SWIFT->Settings->Get('cpu_logcleardays'));

        $_cronLogIDList = array();

        $_SWIFT->Database->Query("SELECT cronlogid FROM " . TABLE_PREFIX . "cronlogs WHERE dateline < '" . (int) ($_dateThreshold) . "'");
        while ($_SWIFT->Database->NextRecord())
        {
            $_cronLogIDList[] = (int) ($_SWIFT->Database->Record['cronlogid']);
        }

        if (!count($_cronLogIDList))
        {
            return false;
        }

        self::DeleteList($_cronLogIDList);

        return true;
    }
}
