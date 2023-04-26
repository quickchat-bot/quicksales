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

use Base\Models\Staff\SWIFT_Staff;
use Tickets\Library\Ticket\SWIFT_Ticket_Exception;

/**
 * The Report Category Model
 *
 * @author Varun Shoor
 */
class SWIFT_ReportCategory extends SWIFT_Model
{
    const TABLE_NAME        =    'reportcategories';
    const PRIMARY_KEY        =    'reportcategoryid';

    const TABLE_STRUCTURE    =    "reportcategoryid I PRIMARY AUTO NOTNULL,
                                visibilitytype C(100) DEFAULT 'public' NOTNULL,
                                staffid I DEFAULT '0' NOTNULL,
                                title C(255) DEFAULT '' NOTNULL";

    const INDEX_1            =    'visibilitytype';


    const VISIBLE_PUBLIC = 'public';
    const VISIBLE_PRIVATE = 'private';
    const VISIBLE_TEAM = 'team';

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
            throw new SWIFT_Exception('Failed to load Report Category Object');
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

            return false;
        } else if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'reportcategories', $this->GetUpdatePool(), 'UPDATE', "reportcategoryid = '" . (int) ($this->GetReportCategoryID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Report Category ID
     *
     * @author Varun Shoor
     * @return mixed "reportcategoryid" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetReportCategoryID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['reportcategoryid'];
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
            $_dataStore = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "reportcategories WHERE reportcategoryid = '" . (int) ($_SWIFT_DataObject->GetDataID()) . "'");
            if (isset($_dataStore['reportcategoryid']) && !empty($_dataStore['reportcategoryid'])) {
                $this->_dataStore = $_dataStore;

                return true;
            }

        // Is it a Store?
        } else if ($_SWIFT_DataObject instanceof SWIFT_DataStore && $_SWIFT_DataObject->GetIsClassLoaded()) {
            $this->_dataStore = $_SWIFT_DataObject->GetDataStore();

            if (!isset($this->_dataStore['reportcategoryid']) || empty($this->_dataStore['reportcategoryid'])) {
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

            return false;
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

            return false;
        } else if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);

            return false;
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
        return ($_visibilityType == self::VISIBLE_PRIVATE || $_visibilityType == self::VISIBLE_PUBLIC || $_visibilityType == self::VISIBLE_TEAM);
    }

    /**
     * Create a new Report Category
     *
     * @author Varun Shoor
     * @param string $_title
     * @param mixed $_visibilityType
     * @param SWIFT_Staff $_SWIFT_StaffObject
     * @return int Report Category ID
     * @throws SWIFT_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function Create($_title, $_visibilityType, SWIFT_Staff $_SWIFT_StaffObject)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_title) || !self::IsValidVisibilityType($_visibilityType) || !$_SWIFT_StaffObject instanceof SWIFT_Staff || !$_SWIFT_StaffObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'reportcategories', array('title' => $_title, 'visibilitytype' => $_visibilityType, 'staffid' => $_SWIFT_StaffObject->GetStaffID()), 'INSERT');
        $_reportCategoryID = $_SWIFT->Database->Insert_ID();

        if (!$_reportCategoryID) {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        self::RebuildCache();

        return $_reportCategoryID;
    }

    /**
     * Update the Report Record
     *
     * @author Varun Shoor
     * @param string $_title
     * @param mixed $_visibilityType
     * @param SWIFT_Staff $_SWIFT_StaffObject
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function Update($_title, $_visibilityType, SWIFT_Staff $_SWIFT_StaffObject)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_title) || !self::IsValidVisibilityType($_visibilityType) || !$_SWIFT_StaffObject instanceof SWIFT_Staff || !$_SWIFT_StaffObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->UpdatePool('title', $_title);
        $this->UpdatePool('visibilitytype', $_visibilityType);
        $this->UpdatePool('staffid', (int) ($_SWIFT_StaffObject->GetStaffID()));
        $this->ProcessUpdatePool();

        self::RebuildCache();

        return true;
    }

    /**
     * Delete the Report Category record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        self::DeleteList(array($this->GetReportCategoryID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of Report Categories
     *
     * @author Varun Shoor
     * @param array $_reportCategoryIDList
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_reportCategoryIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_reportCategoryIDList)) {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "reportcategories WHERE reportcategoryid IN (" . BuildIN($_reportCategoryIDList) . ")");

        SWIFT_Report::DeleteOnCategory($_reportCategoryIDList);

        self::RebuildCache();

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

            case self::VISIBLE_TEAM:
                return $_SWIFT->Language->Get('visible_team');

                break;

            default:
                break;
        }

        throw new SWIFT_Exception(SWIFT_INVALIDDATA);

        return false;
    }

    /**
     * Rebuild the Reports Category Cache
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public static function RebuildCache()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_reportCategoryContainer = array();

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "reportcategories ORDER BY reportcategoryid ASC");
        while ($_SWIFT->Database->NextRecord())
        {
            $_reportCategoryContainer[$_SWIFT->Database->Record['reportcategoryid']] = $_SWIFT->Database->Record;
        }

        $_SWIFT->Cache->Update('reportcategorycache', $_reportCategoryContainer);

        return true;
    }
}

?>
