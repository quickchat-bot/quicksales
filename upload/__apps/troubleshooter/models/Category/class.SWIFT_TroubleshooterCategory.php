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

namespace Troubleshooter\Models\Category;

use SWIFT;
use SWIFT_Data;
use SWIFT_DataID;
use SWIFT_DataStore;
use SWIFT_Exception;
use SWIFT_Model;
use Base\Models\Staff\SWIFT_Staff;
use Base\Models\Staff\SWIFT_StaffGroupLink;
use Troubleshooter\Models\Link\SWIFT_TroubleshooterLink;
use Base\Models\User\SWIFT_UserGroupAssign;

/**
 * The Troubleshooter Category Model
 *
 * @author Varun Shoor
 */
class SWIFT_TroubleshooterCategory extends SWIFT_Model {
    const TABLE_NAME        =    'troubleshootercategories';
    const PRIMARY_KEY        =    'troubleshootercategoryid';

    const TABLE_STRUCTURE    =    "troubleshootercategoryid I PRIMARY AUTO NOTNULL,
                                staffid I DEFAULT '0' NOTNULL,
                                staffname C(255) DEFAULT '' NOTNULL,
                                title C(255) DEFAULT '' NOTNULL,
                                description X NOTNULL,
                                dateline I DEFAULT '0' NOTNULL,
                                categorytype I2 DEFAULT '0' NOTNULL,
                                displayorder I DEFAULT '0' NOTNULL,
                                views I DEFAULT '0' NOTNULL,

                                uservisibilitycustom I2 DEFAULT '0' NOTNULL,
                                staffvisibilitycustom I2 DEFAULT '0' NOTNULL";

    const INDEX_1            =    'categorytype';
    const INDEX_2            =    'staffvisibilitycustom, troubleshootercategoryid';
    const INDEX_3            =    'title';


    protected $_dataStore = array();

    // Core Constants
    const TYPE_GLOBAL = 1;
    const TYPE_PUBLIC = 2;
    const TYPE_PRIVATE = 3;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param SWIFT_Data $_SWIFT_DataObject The SWIFT_Data Object
     * @throws SWIFT_Exception If the Record could not be loaded
     */
    public function __construct(SWIFT_Data $_SWIFT_DataObject) {
        parent::__construct();

        if (!$_SWIFT_DataObject instanceof SWIFT_Data || !$_SWIFT_DataObject->GetIsClassLoaded() || !$this->LoadData($_SWIFT_DataObject)) {
            throw new SWIFT_Exception('Failed to load Troubleshooter Object');
        }
    }

    /**
     * Destructor
     *
     * @author Varun Shoor
     * @throws SWIFT_Exception
     */
    public function __destruct() {
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
    public function ProcessUpdatePool() {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'troubleshootercategories', $this->GetUpdatePool(), 'UPDATE', "troubleshootercategoryid = '" . (int) ($this->GetTroubleshooterCategoryID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Troubleshooter Category ID
     *
     * @author Varun Shoor
     * @return mixed "troubleshootercategoryid" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetTroubleshooterCategoryID() {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['troubleshootercategoryid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param SWIFT_Data $_SWIFT_DataObject The SWIFT_Data Object
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    protected function LoadData($_SWIFT_DataObject) {
        $_SWIFT = SWIFT::GetInstance();

        // Is it a ID?
        if ($_SWIFT_DataObject instanceof SWIFT_DataID && $_SWIFT_DataObject->GetIsClassLoaded()) {
            $_dataStore = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "troubleshootercategories WHERE troubleshootercategoryid = '" . (int) ($_SWIFT_DataObject->GetDataID()) . "'");
            if (isset($_dataStore['troubleshootercategoryid']) && !empty($_dataStore['troubleshootercategoryid'])) {
                $this->_dataStore = $_dataStore;

                return true;
            }

            // Is it a Store?
        } else if ($_SWIFT_DataObject instanceof SWIFT_DataStore && $_SWIFT_DataObject->GetIsClassLoaded()) {
            $this->_dataStore = $_SWIFT_DataObject->GetDataStore();

            if (!isset($this->_dataStore['troubleshootercategoryid']) || empty($this->_dataStore['troubleshootercategoryid'])) {
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
    public function GetDataStore() {
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
    public function GetProperty($_key) {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA . ': ' . $_key);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Check to see if its a valid category type
     *
     * @author Varun Shoor
     * @param mixed $_categoryType The Category Types
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidType($_categoryType) {
        if ($_categoryType == self::TYPE_GLOBAL || $_categoryType == self::TYPE_PRIVATE || $_categoryType == self::TYPE_PUBLIC) {
            return true;
        }

        return false;
    }

    /**
     * Create a new Troubleshooter Category Record
     *
     * @author Varun Shoor
     * @param string $_categoryTitle The Category Title
     * @param string $_description The Category Description
     * @param mixed $_categoryType The Category Type
     * @param int $_displayOrder The Category Display Order
     * @param bool|int $_userVisibilityCustom (OPTIONAL) The User Visibility Custom Flag
     * @param array $_userGroupIDList (OPTIONAL) The User Group ID List
     * @param bool|int $_staffVisibilityCustom (OPTIONAL) The Staff Visibility Custom Flag
     * @param array $_staffGroupIDList (OPTIONAL) The Staff Group ID List
     * @param bool|SWIFT_Model $_SWIFT_StaffObject
     * @return int The Troubleshooter Category ID
     * @throws SWIFT_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function Create($_categoryTitle, $_description, $_categoryType, $_displayOrder, $_userVisibilityCustom = false, $_userGroupIDList = array(),
            $_staffVisibilityCustom = false, $_staffGroupIDList = array(), $_SWIFT_StaffObject = false) {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_categoryTitle) || !self::IsValidType($_categoryType)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_staffID = 0;
        $_staffName = '';

        if ($_SWIFT_StaffObject instanceof SWIFT_Staff && $_SWIFT_StaffObject->GetIsClassLoaded()) {
            $_staffID = $_SWIFT_StaffObject->GetStaffID();
            $_staffName = $_SWIFT_StaffObject->GetProperty('fullname');
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'troubleshootercategories', array('title' => $_categoryTitle, 'description' => $_description, 'categorytype' => (int)$_categoryType,
                'displayorder' => $_displayOrder, 'views' => '0', 'dateline' => DATENOW, 'uservisibilitycustom' => (int)$_userVisibilityCustom,
                'staffvisibilitycustom' => (int)$_staffVisibilityCustom, 'staffid' => (int)$_staffID, 'staffname' => $_staffName), 'INSERT');
        $_troubleshooterCategoryID = $_SWIFT->Database->Insert_ID();

        if (!$_troubleshooterCategoryID) {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        if ($_userVisibilityCustom == true)
        {
            if (_is_array($_userGroupIDList))
            {
                foreach ($_userGroupIDList as $_userGroupID)
                {
                    SWIFT_UserGroupAssign::Insert($_troubleshooterCategoryID, SWIFT_UserGroupAssign::TYPE_TROUBLESHOOTERCATEGORY, $_userGroupID);
                }
            }
        }

        if ($_staffVisibilityCustom == true)
        {
            if (_is_array($_staffGroupIDList))
            {
                foreach ($_staffGroupIDList as $_staffGroupID)
                {
                    SWIFT_StaffGroupLink::Create($_staffGroupID, SWIFT_StaffGroupLink::TYPE_TROUBLESHOOTERCATEGORY, $_troubleshooterCategoryID);
                }
            }
        }

        self::RebuildCache();

        return $_troubleshooterCategoryID;
    }

    /**
     * Update the Troubleshooter Category Record
     *
     * @author Varun Shoor
     * @param string $_categoryTitle The Category Title
     * @param string $_description The Category Description
     * @param mixed $_categoryType The Category Type
     * @param int $_displayOrder The Category Display Order
     * @param bool|int $_userVisibilityCustom (OPTIONAL) The User Visibility Custom Flag
     * @param array $_userGroupIDList (OPTIONAL) The User Group ID List
     * @param bool|int $_staffVisibilityCustom (OPTIONAL) The Staff Visibility Custom Flag
     * @param array $_staffGroupIDList (OPTIONAL) The Staff Group ID List
     * @param bool|SWIFT_Model $_SWIFT_StaffObject (OPTIONAL) The Staff Creating this Category
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function Update($_categoryTitle, $_description, $_categoryType, $_displayOrder, $_userVisibilityCustom = false, $_userGroupIDList = array(),
            $_staffVisibilityCustom = false, $_staffGroupIDList = array(), $_SWIFT_StaffObject = false) {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_categoryTitle) || !self::IsValidType($_categoryType)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_staffID = 0;
        $_staffName = '';

        if ($_SWIFT_StaffObject instanceof SWIFT_Staff && $_SWIFT_StaffObject->GetIsClassLoaded()) {
            $_staffID = $_SWIFT_StaffObject->GetStaffID();
            $_staffName = $_SWIFT_StaffObject->GetProperty('fullname');
        }

        $this->UpdatePool('title', $_categoryTitle);
        $this->UpdatePool('description', $_description);
        $this->UpdatePool('staffid', (int)$_staffID);
        $this->UpdatePool('staffname', $_staffName);
        $this->UpdatePool('dateline', DATENOW);

        $this->UpdatePool('categorytype', (int)$_categoryType);
        $this->UpdatePool('displayorder', $_displayOrder);
        $this->UpdatePool('uservisibilitycustom', (int)$_userVisibilityCustom);
        $this->UpdatePool('staffvisibilitycustom', (int)$_staffVisibilityCustom);

        $this->ProcessUpdatePool();

        SWIFT_UserGroupAssign::DeleteList(array($this->GetTroubleshooterCategoryID()), SWIFT_UserGroupAssign::TYPE_TROUBLESHOOTERCATEGORY);
        if ($_userVisibilityCustom == true)
        {
            if (_is_array($_userGroupIDList))
            {
                foreach ($_userGroupIDList as $_userGroupID)
                {
                    SWIFT_UserGroupAssign::Insert($this->GetTroubleshooterCategoryID(), SWIFT_UserGroupAssign::TYPE_TROUBLESHOOTERCATEGORY, $_userGroupID);
                }
            }
        }

        SWIFT_StaffGroupLink::DeleteOnLinkList(SWIFT_StaffGroupLink::TYPE_TROUBLESHOOTERCATEGORY, array($this->GetTroubleshooterCategoryID()));
        if ($_staffVisibilityCustom == true)
        {
            if (_is_array($_staffGroupIDList))
            {
                foreach ($_staffGroupIDList as $_staffGroupID)
                {
                    SWIFT_StaffGroupLink::Create($_staffGroupID, SWIFT_StaffGroupLink::TYPE_TROUBLESHOOTERCATEGORY, $this->GetTroubleshooterCategoryID());
                }
            }
        }

        self::RebuildCache();

        return true;
    }

    /**
     * Delete the Troubleshooter Category record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete() {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($this->GetTroubleshooterCategoryID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of Troubleshooter Categories
     *
     * @author Varun Shoor
     * @param array $_troubleshooterCategoryIDList The Troubleshooter Category ID List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function DeleteList($_troubleshooterCategoryIDList) {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_troubleshooterCategoryIDList)) {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "troubleshootercategories WHERE troubleshootercategoryid IN (" . BuildIN($_troubleshooterCategoryIDList) . ")");

        SWIFT_TroubleshooterLink::DeleteOnTroubleshooterCategory($_troubleshooterCategoryIDList);

        self::RebuildCache();

        return true;
    }

    /**
     * Retrieve the label for category type
     *
     * @author Varun Shoor
     * @param mixed $_categoryType The Category Type
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function GetCategoryTypeLabel($_categoryType) {
        $_SWIFT = SWIFT::GetInstance();

        if ($_categoryType == self::TYPE_GLOBAL) {
            return $_SWIFT->Language->Get('global');
        }

        if ($_categoryType == self::TYPE_PUBLIC) {
            return $_SWIFT->Language->Get('public');
        }

        if ($_categoryType == self::TYPE_PRIVATE) {
            return $_SWIFT->Language->Get('private');
        }

        throw new SWIFT_Exception(SWIFT_INVALIDDATA);
    }

    /**
     * Rebuild the Troubleshooter Category Cache
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RebuildCache()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_cacheContainer = array();

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "troubleshootercategories");
        while ($_SWIFT->Database->NextRecord())
        {
            $_cacheContainer[$_SWIFT->Database->Record['troubleshootercategoryid']] = $_SWIFT->Database->Record;
        }

        $_SWIFT->Cache->Update('troubleshootercategorycache', $_cacheContainer);

        return true;
    }

    /**
     * Retrieve the Last Possible Display Order for a Troubleshooter Category
     *
     * @author Varun Shoor
     * @return int The Last Possible Display Order
     * @throws SWIFT_Exception
     */
    public static function GetLastDisplayOrder()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_SWIFT->Cache->Load('troubleshootercategorycache');

        $_troubleshooterCategoryCache = (array) $_SWIFT->Cache->Get('troubleshootercategorycache');

        if (!_is_array($_troubleshooterCategoryCache))
        {
            return 1;
        }

        // Get Last Insert ID
        $_lastInsertID = max(array_keys($_troubleshooterCategoryCache));

        $_displayOrder = ($_troubleshooterCategoryCache[$_lastInsertID]['displayorder'] + 1);

        return $_displayOrder;
    }

    /**
     * Retrieve the User Group ID's linked with this Troubleshooter Category
     *
     * @author Varun Shoor
     * @return mixed "_userGroupIDList" (ARRAY) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetLinkedUserGroupIDList()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return SWIFT_UserGroupAssign::RetrieveList(SWIFT_UserGroupAssign::TYPE_TROUBLESHOOTERCATEGORY, $this->GetTroubleshooterCategoryID());
    }

    /**
     * Retrieve the Staff Group ID's linked with this Troubleshooter Category
     *
     * @author Varun Shoor
     * @return mixed "_staffGroupIDList" (ARRAY) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetLinkedStaffGroupIDList()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return SWIFT_StaffGroupLink::RetrieveList(SWIFT_StaffGroupLink::TYPE_TROUBLESHOOTERCATEGORY, $this->GetTroubleshooterCategoryID());
    }

    /**
     * Retrieve the Troubleshooter Categories
     *
     * @author Varun Shoor
     * @param array $_categoryTypeList The Category Type List
     * @param int $_staffGroupID (OPTIONAL) The Staff Group to restrict results to
     * @param int $_userGroupID (OPTIONAL) The User Group to restrict results to
     * @return array The Troubleshooter Category Container
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function Retrieve($_categoryTypeList, $_staffGroupID = 0, $_userGroupID = 0)
    {
        $_SWIFT = SWIFT::GetInstance();


        $_filterUserCategoryIDList = $_filterStaffCategoryIDList = [];
        if (!empty($_userGroupID))
        {
            $_filterUserCategoryIDList = SWIFT_UserGroupAssign::RetrieveListOnUserGroup($_userGroupID, SWIFT_UserGroupAssign::TYPE_TROUBLESHOOTERCATEGORY);
        }

        if (!empty($_staffGroupID))
        {
            $_filterStaffCategoryIDList = SWIFT_StaffGroupLink::RetrieveListFromCacheOnStaffGroup(SWIFT_StaffGroupLink::TYPE_TROUBLESHOOTERCATEGORY, $_staffGroupID);
        }

        $_troubleshooterCategoryContainer = array();

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "troubleshootercategories AS troubleshootercategories
            WHERE troubleshootercategories.categorytype IN (" . BuildIN($_categoryTypeList) . ")
                " . IIF(is_array($_filterUserCategoryIDList), "AND (troubleshootercategories.uservisibilitycustom = '0' OR (troubleshootercategories.uservisibilitycustom = '1' AND troubleshootercategories.troubleshootercategoryid IN (" . BuildIN($_filterUserCategoryIDList) . ")))") . "
                " . IIF(is_array($_filterStaffCategoryIDList), "AND (troubleshootercategories.staffvisibilitycustom = '0' OR (troubleshootercategories.staffvisibilitycustom = '1' AND troubleshootercategories.troubleshootercategoryid IN (" . BuildIN($_filterStaffCategoryIDList) . ")))") . "
            ORDER BY troubleshootercategories.displayorder ASC");
        while ($_SWIFT->Database->NextRecord())
        {
            $_troubleshooterCategoryContainer[$_SWIFT->Database->Record['troubleshootercategoryid']] = $_SWIFT->Database->Record;
        }

        return $_troubleshooterCategoryContainer;
    }

    /**
     * Confirm the access for the currently loaded category
     *
     * @author Varun Shoor
     * @param array $_categoryTypeList The Category Tyep List
     * @param int $_staffGroupID (OPTIONAL) The Staff Group ID
     * @param int $_userGroupID (OPTIONAL) The User Group ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function CanAccess($_categoryTypeList, $_staffGroupID = 0, $_userGroupID = 0)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!_is_array($_categoryTypeList)) {
            return false;
        }

        if (!in_array($this->GetProperty('categorytype'), $_categoryTypeList))
        {
            return false;
        }

        if ($this->GetProperty('staffvisibilitycustom') == '1' && !empty($_staffGroupID)) {
            $_filterStaffCategoryIDList = SWIFT_StaffGroupLink::RetrieveListFromCacheOnStaffGroup(SWIFT_StaffGroupLink::TYPE_TROUBLESHOOTERCATEGORY, $_staffGroupID);

            if (!in_array($this->GetTroubleshooterCategoryID(), $_filterStaffCategoryIDList))
            {
                return false;
            }
        } else if ($this->GetProperty('uservisibilitycustom') == '1' && !empty($_userGroupID)) {
            $_filterUserCategoryIDList = SWIFT_UserGroupAssign::RetrieveListOnUserGroup($_userGroupID, SWIFT_UserGroupAssign::TYPE_TROUBLESHOOTERCATEGORY);

            if (!in_array($this->GetTroubleshooterCategoryID(), $_filterUserCategoryIDList))
            {
                return false;
            }
        }

        return true;
    }

    /**
     * Increment the Category Views
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function IncrementViews()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UpdatePool('views', $this->GetProperty('views')+1);
        $this->ProcessUpdatePool();

        return true;
    }
}
