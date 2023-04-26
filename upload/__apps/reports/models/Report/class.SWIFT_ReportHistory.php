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

use Tickets\Library\Ticket\SWIFT_Ticket_Exception;

/**
 * The Report History Model
 *
 * @author Varun Shoor
 */
class SWIFT_ReportHistory extends SWIFT_Model
{
    const TABLE_NAME        =    'reporthistory';
    const PRIMARY_KEY        =    'reporthistoryid';

    const TABLE_STRUCTURE    =    "reporthistoryid I PRIMARY AUTO NOTNULL,
                                reportid I DEFAULT '0' NOTNULL,
                                dateline I DEFAULT '0' NOTNULL,
                                creatorstaffid I DEFAULT '0' NOTNULL,
                                creatorstaffname C(255) DEFAULT '' NOTNULL,
                                kql X NOTNULL";

    const INDEX_1            =    'reportid';


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
            throw new SWIFT_Exception('Failed to load ReportHistory Object');
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

        $this->Database->AutoExecute(TABLE_PREFIX . 'reporthistory', $this->GetUpdatePool(), 'UPDATE', "reporthistoryid = '" . (int) ($this->GetReportHistoryID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Report History ID
     *
     * @author Varun Shoor
     * @return mixed "reporthistoryid" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetReportHistoryID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['reporthistoryid'];
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
            $_dataStore = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "reporthistory WHERE reporthistoryid = '" . (int) ($_SWIFT_DataObject->GetDataID()) . "'");
            if (isset($_dataStore['reporthistoryid']) && !empty($_dataStore['reporthistoryid'])) {
                $this->_dataStore = $_dataStore;

                return true;
            }

            // Is it a Store?
        } else if ($_SWIFT_DataObject instanceof SWIFT_DataStore && $_SWIFT_DataObject->GetIsClassLoaded()) {
            $this->_dataStore = $_SWIFT_DataObject->GetDataStore();

            if (!isset($this->_dataStore['reporthistoryid']) || empty($this->_dataStore['reporthistoryid'])) {
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
     * Create a new report history entry
     *
     * @author Varun Shoor
     * @param SWIFT_Report $_SWIFT_ReportObject
     * @return int Report History ID
     * @throws SWIFT_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function Create(SWIFT_Report $_SWIFT_ReportObject)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_SWIFT_ReportObject instanceof SWIFT_Report || !$_SWIFT_ReportObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        /**
         * BUG FIX - Ashish Kataria
         *
         * SWIFT-2673 Staff shown under History tab of report displays the original creator of the report even if some other staff edits the report query.
         */
        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'reporthistory', array(
                                       'reportid'       => $_SWIFT_ReportObject->GetID(), 'dateline' => DATENOW,
                                       'creatorstaffid' => $_SWIFT->Staff->GetID(), 'creatorstaffname' => $_SWIFT->Staff->Get('fullname'), 'kql' => $_SWIFT_ReportObject->Get('kql')
                                       ), 'INSERT');
        $_reportHistoryID = $_SWIFT->Database->Insert_ID();

        if (!$_reportHistoryID) {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        return $_reportHistoryID;
    }

    /**
     * Delete the Report History record
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

        self::DeleteList(array($this->GetReportHistoryID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of Report Histories
     *
     * @author Varun Shoor
     * @param array $_reportHistoryIDList
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_reportHistoryIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_reportHistoryIDList)) {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "reporthistory WHERE reporthistoryid IN (" . BuildIN($_reportHistoryIDList) . ")");

        return true;
    }

    /**
     * Delete the histories based on a list of report ids
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

        $_reportHistoryIDList = array();
        $_SWIFT->Database->Query("SELECT reporthistoryid FROM " . TABLE_PREFIX . "reports WHERE reportid IN (" . BuildIN($_reportIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_reportHistoryIDList[] = $_SWIFT->Database->Record['reporthistoryid'];
        }

        if (!count($_reportHistoryIDList)) {
            return false;
        }

        self::DeleteList($_reportHistoryIDList);

        return true;
    }

    /**
     * Retrieve the total history count
     *
     * @author Varun Shoor
     * @param SWIFT_Report $_SWIFT_ReportObject
     * @return int The Total Count
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function GetTotalCount(SWIFT_Report $_SWIFT_ReportObject)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_SWIFT_ReportObject instanceof SWIFT_Report || !$_SWIFT_ReportObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_totalItems = 0;

        $_reportCountContainer = $_SWIFT->Database->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "reporthistory WHERE reportid = '" . (int) ($_SWIFT_ReportObject->GetReportID()) . "'");
        if (isset($_reportCountContainer['totalitems'])) {
            $_totalItems = (int) ($_reportCountContainer['totalitems']);
        }

        return $_totalItems;
    }

    /**
     * Retrieve the entire report history
     *
     * @author Varun Shoor
     * @param SWIFT_Report $_SWIFT_ReportObject
     * @return array History Container
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveOnReport(SWIFT_Report $_SWIFT_ReportObject)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_SWIFT_ReportObject instanceof SWIFT_Report || !$_SWIFT_ReportObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_historyContainer = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "reporthistory WHERE reportid = '" . (int) ($_SWIFT_ReportObject->GetReportID()) . "' ORDER BY reporthistoryid DESC");
        while ($_SWIFT->Database->NextRecord()) {
            $_historyContainer[$_SWIFT->Database->Record['reporthistoryid']] = $_SWIFT->Database->Record;
        }

        return $_historyContainer;
    }
}
