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

use Base\Library\KQL\SWIFT_KQLSchema;
use Base\Models\Staff\SWIFT_Staff;
use Tickets\Library\Ticket\SWIFT_Ticket_Exception;

/**
 * The Base Report Model Class
 *
 * @author Varun Shoor
 */
class SWIFT_Report extends SWIFT_Model
{
    const TABLE_NAME        =    'reports';
    const PRIMARY_KEY        =    'reportid';

    const TABLE_STRUCTURE    =    "reportid I PRIMARY AUTO NOTNULL,
                                reportcategoryid I DEFAULT '0' NOTNULL,
                                title C(255) DEFAULT '' NOTNULL,
                                basetablename C(255) DEFAULT '' NOTNULL,
                                basetablenametext C(255) DEFAULT '' NOTNULL,

                                dateline I DEFAULT '0' NOTNULL,
                                creatorstaffid I DEFAULT '0' NOTNULL,
                                creatorstaffname C(255) DEFAULT '' NOTNULL,

                                visibilitytype C(100) DEFAULT 'public' NOTNULL,

                                updatedateline I DEFAULT '0' NOTNULL,
                                updatestaffid I DEFAULT '0' NOTNULL,
                                updatestaffname C(255) DEFAULT '' NOTNULL,

                                executedateline I DEFAULT '0' NOTNULL,
                                executestaffid I DEFAULT '0' NOTNULL,
                                executestaffname C(255) DEFAULT '' NOTNULL,
                                executetimetaken I DEFAULT '0' NOTNULL,

                                chartsenabled I2 DEFAULT '1' NOTNULL,

                                kql X NOTNULL";

    const INDEX_1            =    'dateline';
    const INDEX_2            =    'title';


    const VISIBLE_PUBLIC = 'public';
    const VISIBLE_PRIVATE = 'private';

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
            throw new SWIFT_Exception('Failed to load Report Object');
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

        $this->Database->AutoExecute(TABLE_PREFIX . 'reports', $this->GetUpdatePool(), 'UPDATE', "reportid = '" . (int) ($this->GetReportID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Report ID
     *
     * @author Varun Shoor
     * @return mixed "reportid" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetReportID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['reportid'];
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
            $_dataStore = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "reports WHERE reportid = '" . (int) ($_SWIFT_DataObject->GetDataID()) . "'");
            if (isset($_dataStore['reportid']) && !empty($_dataStore['reportid'])) {
                $this->_dataStore = $_dataStore;

                return true;
            }

            // Is it a Store?
        } else if ($_SWIFT_DataObject instanceof SWIFT_DataStore && $_SWIFT_DataObject->GetIsClassLoaded()) {
            $this->_dataStore = $_SWIFT_DataObject->GetDataStore();

            if (!isset($this->_dataStore['reportid']) || empty($this->_dataStore['reportid'])) {
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
     * Check to see if its a valid visibility type
     *
     * @author Varun Shoor
     * @param mixed $_visibilityType
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidVisibilityType($_visibilityType)
    {
        return ($_visibilityType == self::VISIBLE_PRIVATE || $_visibilityType == self::VISIBLE_PUBLIC);
    }

    /**
     * Create a new report
     *
     * @author Varun Shoor
     * @param string $_reportCategoryID
     * @param string $_title
     * @param string $_baseTableName
     * @param string $_kqlStatement
     * @param SWIFT_Staff $_SWIFT_StaffObject
     * @param mixed $_visibilityType
     * @return int Report ID
     * @throws SWIFT_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function Create($_reportCategoryID, $_title, $_baseTableName, $_kqlStatement, SWIFT_Staff $_SWIFT_StaffObject, $_visibilityType)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_reportCategoryID = (int) ($_reportCategoryID);

        if (empty($_reportCategoryID) || empty($_title) || empty($_kqlStatement) || !$_SWIFT_StaffObject instanceof SWIFT_Staff || !$_SWIFT_StaffObject->GetIsClassLoaded()
                || !self::IsValidVisibilityType($_visibilityType) || empty($_baseTableName)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_schemaContainer = SWIFT_KQLSchema::GetCombinedSchema();

        $_baseTableNameText = $_baseTableName;
        if (isset($_schemaContainer[$_baseTableName][SWIFT_KQLSchema::SCHEMA_TABLELABEL])) {
            $_baseTableNameText = SWIFT_KQLSchema::GetLabel($_schemaContainer[$_baseTableName][SWIFT_KQLSchema::SCHEMA_TABLELABEL]);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'reports', array('title' => $_title, 'dateline' => DATENOW, 'creatorstaffid' => $_SWIFT_StaffObject->GetStaffID(),
            'creatorstaffname' => $_SWIFT_StaffObject->GetProperty('fullname'), 'kql' => $_kqlStatement, 'reportcategoryid' => $_reportCategoryID,
            'visibilitytype' => $_visibilityType, 'basetablename' => $_baseTableName, 'basetablenametext' => $_baseTableNameText), 'INSERT');
        $_reportID = $_SWIFT->Database->Insert_ID();

        if (!$_reportID) {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        return $_reportID;
    }

    /**
     * Update the Report Record
     *
     * @author Varun Shoor
     * @param string $_title
     * @param string $_kqlStatement
     * @param bool $_enableCharts
     * @param SWIFT_Staff $_SWIFT_StaffObject
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function Update($_title, $_kqlStatement, $_enableCharts, SWIFT_Staff $_SWIFT_StaffObject)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_title) || empty($_kqlStatement) || !$_SWIFT_StaffObject instanceof SWIFT_Staff || !$_SWIFT_StaffObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if (mb_strtolower($_kqlStatement) != mb_strtolower($this->GetProperty('kql'))) {
            SWIFT_ReportHistory::Create($this);
        }

        $this->UpdatePool('title', $_title);
        $this->UpdatePool('kql', $_kqlStatement);
        $this->UpdatePool('chartsenabled', (int) ($_enableCharts));

        $this->UpdatePool('updatedateline', DATENOW);
        $this->UpdatePool('updatestaffid', $_SWIFT_StaffObject->GetStaffID());
        $this->UpdatePool('updatestaffname', $_SWIFT_StaffObject->GetProperty('fullname'));
        $this->ProcessUpdatePool();

        return true;
    }

    /**
     * Update the Report Record
     *
     * @author Varun Shoor
     * @param string $_title
     * @param int $_reportCategoryID
     * @param string $_baseTableName
     * @param mixed $_visibilityType
     * @param SWIFT_Staff $_SWIFT_StaffObject
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function UpdateProperties($_title, $_reportCategoryID, $_baseTableName, $_visibilityType, SWIFT_Staff $_SWIFT_StaffObject)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_title) || empty($_reportCategoryID) || empty($_baseTableName) || !$_SWIFT_StaffObject instanceof SWIFT_Staff || !$_SWIFT_StaffObject->GetIsClassLoaded() || !self::IsValidVisibilityType($_visibilityType)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }


        $this->UpdatePool('title', $_title);
        $this->UpdatePool('basetablename', $_baseTableName);
        $this->UpdatePool('reportcategoryid',  ($_reportCategoryID));
        $this->UpdatePool('visibilitytype', $_visibilityType);

        $this->UpdatePool('updatedateline', DATENOW);
        $this->UpdatePool('updatestaffid', $_SWIFT_StaffObject->GetStaffID());
        $this->UpdatePool('updatestaffname', $_SWIFT_StaffObject->GetProperty('fullname'));

        /*
         * BUG FIX - Varun Shoor
         *
         * SWIFT-1886 Marking a default report as private causes it to disappear
         *
         */
        if ($_visibilityType == self::VISIBLE_PRIVATE) {
            $this->UpdatePool('creatorstaffid', $_SWIFT_StaffObject->GetStaffID());
            $this->UpdatePool('creatorstaffname', $_SWIFT_StaffObject->GetProperty('fullname'));
        }

        $this->ProcessUpdatePool();

        return true;
    }

    /**
     * Delete the report record
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

        self::DeleteList(array($this->GetReportID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of Reports
     *
     * @author Varun Shoor
     * @param array $_reportIDList
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_reportIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_reportIDList)) {
            return false;
        }

        // Clear the history entries
        SWIFT_ReportHistory::DeleteOnReport($_reportIDList);

        // Clear the usage logs
        SWIFT_ReportUsageLog::DeleteOnReport($_reportIDList);

        // Clear the schedules
        SWIFT_ReportSchedule::DeleteOnReport($_reportIDList);

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "reports WHERE reportid IN (" . BuildIN($_reportIDList) . ")");

        return true;
    }

    /**
     * Delete on a list of report categories
     *
     * @author Varun Shoor
     * @param array $_reportCategoryIDList
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteOnCategory($_reportCategoryIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_reportCategoryIDList)) {
            return false;
        }

        $_reportIDList = array();

        $_SWIFT->Database->Query("SELECT reportid FROM " . TABLE_PREFIX . "reports WHERE reportcategoryid IN (" . BuildIN($_reportCategoryIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_reportIDList[] = $_SWIFT->Database->Record['reportid'];
        }

        if (!count($_reportIDList)) {
            return false;
        }

        self::DeleteList($_reportIDList);

        return true;
    }

    /**
     * Retrieve the visibilty label
     *
     * @author Varun Shoor
     * @param mixed $_visibilityType
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function GetVisibilityLabel($_visibilityType)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!self::IsValidVisibilityType($_visibilityType)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        switch ($_visibilityType) {
            case self::VISIBLE_PUBLIC:
                return $_SWIFT->Language->Get('visible_public');

                break;

            case self::VISIBLE_PRIVATE:
                return $_SWIFT->Language->Get('visible_private');

                break;

            default:
                break;
        }

        throw new SWIFT_Exception(SWIFT_INVALIDDATA);
    }

    /**
     * Retrieve the base table list
     *
     * @author Varun Shoor
     * @return array The Base Table List
     */
    public static function GetBaseTableList()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_baseTableList = array();

        $_schemaContainer = SWIFT_KQLSchema::GetCombinedSchema();

        foreach ($_schemaContainer as $_tableName => $_schema) {
            if (isset($_schema[SWIFT_KQLSchema::SCHEMA_ISVISIBLE]) && $_schema[SWIFT_KQLSchema::SCHEMA_ISVISIBLE] == true) {
                $_tableLabel = $_tableName;
                if (isset($_schema[SWIFT_KQLSchema::SCHEMA_TABLELABEL])) {
                    $_tableLabel = $_schema[SWIFT_KQLSchema::SCHEMA_TABLELABEL];
                }

                $_baseTableList[$_tableName] = SWIFT_KQLSchema::GetLabel($_tableLabel);
            }
        }

        asort($_baseTableList);

        return $_baseTableList;
    }

    /**
     * Update Execution Data
     *
     * @author Varun Shoor
     * @param SWIFT_Staff $_SWIFT_StaffObject
     * @param float $_timeTaken
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function UpdateExecution(SWIFT_Staff $_SWIFT_StaffObject, $_timeTaken)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UpdatePool('executedateline', DATENOW);
        $this->UpdatePool('executestaffid', $_SWIFT_StaffObject->GetStaffID());
        $this->UpdatePool('executestaffname', $_SWIFT_StaffObject->GetProperty('fullname'));
        $this->UpdatePool('executetimetaken', floatval($_timeTaken));
        $this->ProcessUpdatePool();

        return true;
    }
}
