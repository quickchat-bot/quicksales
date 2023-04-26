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

use Base\Models\Staff\SWIFT_Staff;
use Tickets\Library\Ticket\SWIFT_Ticket_Exception;

/**
 * The Report Usage Log
 *
 * @author Varun Shoor
 */
class SWIFT_ReportUsageLog extends SWIFT_Model
{
    const TABLE_NAME        =    'reportusagelogs';
    const PRIMARY_KEY        =    'reportusagelogid';

    const TABLE_STRUCTURE    =    "reportusagelogid I PRIMARY AUTO NOTNULL,
                                reportid I DEFAULT '0' NOTNULL,
                                dateline I DEFAULT '0' NOTNULL,
                                staffid I DEFAULT '0' NOTNULL,
                                staffname C(255) DEFAULT '' NOTNULL,
                                timetaken F DEFAULT '0' NOTNULL";

    const INDEX_1            =    'reportid';
    const INDEX_2            =    'staffid, reportid';


    protected $_dataStore = array();

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param SWIFT_Data $_SWIFT_DataObject The SWIFT_Data Object
     * @throws SWIFT_Exception If the Record could not be loaded
     */
    public function __construct(SWIFT_Data $_SWIFT_DataObject)
    {
        parent::__construct();

        if (!$_SWIFT_DataObject instanceof SWIFT_Data || !$_SWIFT_DataObject->GetIsClassLoaded() || !$this->LoadData($_SWIFT_DataObject)) {
            throw new SWIFT_Exception('Failed to load ReportUsageLog Object');
        }
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
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ProcessUpdatePool()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'reportusagelogs', $this->GetUpdatePool(), 'UPDATE', "reportusagelogid = '" . (int) ($this->GetReportUsageLogID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Report Usage Log ID
     *
     * @author Varun Shoor
     * @return mixed "reportusagelogid" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetReportUsageLogID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['reportusagelogid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param SWIFT_Data $_SWIFT_DataObject The SWIFT_Data Object
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If Invalid Data is Provided
     */
    protected function LoadData($_SWIFT_DataObject)
    {
        $_SWIFT = SWIFT::GetInstance();


        // Is it a ID?
        if ($_SWIFT_DataObject instanceof SWIFT_DataID && $_SWIFT_DataObject->GetIsClassLoaded()) {
            $_dataStore = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "reportusagelogs WHERE reportusagelogid = '" . (int) ($_SWIFT_DataObject->GetDataID()) . "'");
            if (isset($_dataStore['reportusagelogid']) && !empty($_dataStore['reportusagelogid'])) {
                $this->_dataStore = $_dataStore;

                return true;
            }

            // Is it a Store?
        } else if ($_SWIFT_DataObject instanceof SWIFT_DataStore && $_SWIFT_DataObject->GetIsClassLoaded()) {
            $this->_dataStore = $_SWIFT_DataObject->GetDataStore();

            if (!isset($this->_dataStore['reportusagelogid']) || empty($this->_dataStore['reportusagelogid'])) {
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            }

            return true;
        }

        throw new SWIFT_Exception(SWIFT_INVALIDDATA);
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
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
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
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Create a new Report Usage Log
     *
     * @author Varun Shoor
     * @param SWIFT_Report $_SWIFT_ReportObject
     * @param SWIFT_Staff $_SWIFT_StaffObject
     * @param float $_timeTaken
     * @return int Report Usage Log ID
     * @throws SWIFT_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function Create(SWIFT_Report $_SWIFT_ReportObject, SWIFT_Staff $_SWIFT_StaffObject, $_timeTaken)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_SWIFT_ReportObject instanceof SWIFT_Report || !$_SWIFT_ReportObject->GetIsClassLoaded() || !$_SWIFT_StaffObject instanceof SWIFT_Staff || !$_SWIFT_StaffObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'reportusagelogs', array('reportid' => (int) ($_SWIFT_ReportObject->GetReportID()), 'staffid' => (int) ($_SWIFT_StaffObject->GetStaffID()),
            'staffname' => $_SWIFT_StaffObject->GetProperty('fullname'), 'timetaken' => floatval($_timeTaken)), 'INSERT');
        $_reportUsageLogID = $_SWIFT->Database->Insert_ID();

        if (!$_reportUsageLogID) {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        return $_reportUsageLogID;
    }

    /**
     * Delete Report Usage Log record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($this->GetReportUsageLogID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of Report Usage Logs
     *
     * @author Varun Shoor
     * @param array $_reportUsageLogIDList
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_reportUsageLogIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "reportusagelogs WHERE reportusagelogid IN (" . BuildIN($_reportUsageLogIDList) . ")");

        return true;
    }

    /**
     * Delete usage logs on report id's
     *
     * @author Varun Shoor
     * @param array $_reportIDList
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteOnReport($_reportIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_reportIDList)) {
            return false;
        }

        $_reportUsageLogIDList = array();
        $_SWIFT->Database->Query("SELECT reportusagelogid FROM " . TABLE_PREFIX . "reportusagelogs WHERE reportid IN (" . BuildIN($_reportIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_reportUsageLogIDList[] = $_SWIFT->Database->Record['reportusagelogid'];
        }

        if (!count($_reportUsageLogIDList)) {
            return false;
        }

        self::DeleteList($_reportUsageLogIDList);

        return true;
    }

}
