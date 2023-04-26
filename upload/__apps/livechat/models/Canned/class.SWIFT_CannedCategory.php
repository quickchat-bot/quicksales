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

namespace LiveChat\Models\Canned;

use LiveChat\Library\Canned\SWIFT_Canned_Exception;
use SWIFT;
use LiveChat\Models\Canned\SWIFT_CannedResponse;
use SWIFT_Exception;
use SWIFT_Model;

/**
 * The Canned Category Class
 *
 * @author Varun Shoor
 */
class SWIFT_CannedCategory extends SWIFT_Model
{
    const TABLE_NAME = 'cannedcategories';
    const PRIMARY_KEY = 'cannedcategoryid';

    const TABLE_STRUCTURE = "cannedcategoryid I PRIMARY AUTO NOTNULL,
                                parentcategoryid I DEFAULT '0' NOTNULL,
                                categorytype I2 DEFAULT '0' NOTNULL,
                                staffid I DEFAULT '0' NOTNULL,
                                title C(255) DEFAULT '' NOTNULL";

    const INDEX_1 = 'parentcategoryid';
    const INDEX_2 = 'categorytype, staffid';


    protected $_dataStore = array();

    static protected $_cannedCategoryCache = false;

    // Core Constants
    const TYPE_PUBLIC = 1;
    const TYPE_PRIVATE = 0;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param int $_cannedCategoryID The Canned Category ID
     * @throws SWIFT_Exception If the Record could not be loaded
     */
    public function __construct($_cannedCategoryID)
    {
        parent::__construct();

        if (!$this->LoadData($_cannedCategoryID)) {
            throw new SWIFT_Canned_Exception('Failed to load Canned Category ID: ' . ($_cannedCategoryID));
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
     * @throws \LiveChat\Models\Canned\SWIFT_Canned_Exception If the Class is not Loaded
     */
    public function ProcessUpdatePool()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        } else if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'cannedcategories', $this->GetUpdatePool(), 'UPDATE', "cannedcategoryid = '" . (int)($this->GetCannedCategoryID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Canned Category ID
     *
     * @author Varun Shoor
     * @return mixed "cannedcategoryid" on Success, "false" otherwise
     * @throws \LiveChat\Models\Canned\SWIFT_Canned_Exception If the Class is not Loaded
     */
    public function GetCannedCategoryID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Canned_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        return $this->_dataStore['cannedcategoryid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param \SWIFT_Data|int $_cannedCategoryID The Canned Category ID
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_cannedCategoryID)
    {
        $_dataStore = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "cannedcategories WHERE cannedcategoryid = '" . $_cannedCategoryID . "'");
        if (isset($_dataStore['cannedcategoryid']) && !empty($_dataStore['cannedcategoryid'])) {
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
     * @throws \LiveChat\Models\Canned\SWIFT_Canned_Exception If the Class is not Loaded
     */
    public function GetDataStore()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Canned_Exception(SWIFT_CLASSNOTLOADED);

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
     * @throws \LiveChat\Models\Canned\SWIFT_Canned_Exception If the Class is not Loaded
     */
    public function GetProperty($_key)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Canned_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_Canned_Exception(SWIFT_INVALIDDATA);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * The Canned Category Type
     *
     * @author Varun Shoor
     * @param mixed $_categoryType The Canned Category Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidType($_categoryType)
    {
        if ($_categoryType == self::TYPE_PUBLIC || $_categoryType == self::TYPE_PRIVATE) {
            return true;
        }

        return false;
    }

    /**
     * Create a new Canned Category
     *
     * @author Varun Shoor
     * @param mixed $_categoryType The Canned Category Type
     * @param string $_categoryTitle The Category Title
     * @param int $_parentCategoryID The Parent Category ID
     * @param int $_staffID The Staff ID
     * @return mixed "_cannedCategoryID" (INT) on Success, "false" otherwise
     * @throws \LiveChat\Models\Canned\SWIFT_Canned_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function Create($_categoryType, $_categoryTitle, $_parentCategoryID, $_staffID = 0)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_parentCategoryID = $_parentCategoryID;

        if (empty($_categoryTitle) || !self::IsValidType($_categoryType)) {
            throw new SWIFT_Canned_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'cannedcategories', array('categorytype' => (int)($_categoryType), 'title' => $_categoryTitle, 'parentcategoryid' => $_parentCategoryID, 'staffid' => $_staffID), 'INSERT');
        $_cannedCategoryID = $_SWIFT->Database->Insert_ID();

        if (!$_cannedCategoryID) {
            throw new SWIFT_Canned_Exception(SWIFT_CREATEFAILED);

            return false;
        }

        return $_cannedCategoryID;
    }

    /**
     * Update the Category Record
     *
     * @author Varun Shoor
     * @param mixed $_categoryType The Canned Category Type
     * @param string $_categoryTitle The Category Title
     * @param int $_parentCategoryID The Parent Category ID
     * @param int $_staffID The Staff ID
     * @return bool "true" on Success, "false" otherwise
     * @throws \LiveChat\Models\Canned\SWIFT_Canned_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function Update($_categoryType, $_categoryTitle, $_parentCategoryID, $_staffID = 0)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Canned_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_categoryTitle) || !self::IsValidType($_categoryType)) {
            throw new SWIFT_Canned_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $this->UpdatePool('categorytype', (int)($_categoryType));
        $this->UpdatePool('title', $_categoryTitle);
        $this->UpdatePool('parentcategoryid', $_parentCategoryID);
        $this->UpdatePool('staffid', $_staffID);

        $this->ProcessUpdatePool();

        return true;
    }

    /**
     * Delete the Canned Category record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws \LiveChat\Models\Canned\SWIFT_Canned_Exception If the Class is not Loaded
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Canned_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        self::DeleteList(array($this->GetCannedCategoryID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of Canned Categories
     *
     * @author Varun Shoor
     * @param array $_cannedCategoryIDList The Canned Category ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_cannedCategoryIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_cannedCategoryIDList)) {
            return false;
        }

        // Merge any sub categories...
        $_cannedCategoryIDList = array_merge($_cannedCategoryIDList, self::RetrieveSubCategoryIDList($_cannedCategoryIDList));

        $_index = 1;
        $_finalText = '';

        $_finalCannedCategoryIDList = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "cannedcategories WHERE cannedcategoryid IN (" . BuildIN($_cannedCategoryIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_finalCannedCategoryIDList[] = $_SWIFT->Database->Record['cannedcategoryid'];

            $_finalText .= $_index . '. ' . htmlspecialchars($_SWIFT->Database->Record['title']) . '<BR />';

            $_index++;
        }

        if (!_is_array($_finalCannedCategoryIDList)) {
            return false;
        }

        SWIFT::Info(sprintf($_SWIFT->Language->Get('titledelcannedcat'), count($_finalCannedCategoryIDList)), $_SWIFT->Language->Get('msgdelcannedcat') . '<BR />' . $_finalText);

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "cannedcategories WHERE cannedcategoryid IN (" . BuildIN($_finalCannedCategoryIDList) . ")");

        SWIFT_CannedResponse::DeleteOnCannedCategory($_finalCannedCategoryIDList);

        return true;
    }

    /**
     * Retrieve list of sub categories
     *
     * @author Varun Shoor
     * @param array $_parentCannedCategoryIDList The Sub Canned Category ID List
     * @return array
     */
    public static function RetrieveSubCategoryIDList($_parentCannedCategoryIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_parentCannedCategoryIDList)) {
            return array();
        }

        if (!self::$_cannedCategoryCache) {
            self::$_cannedCategoryCache = self::RetrieveCannedCategories();
        }

        // this will be overwritten by extract
        $_cannedParentMap = [];
        extract(self::$_cannedCategoryCache, EXTR_OVERWRITE);

        $_subCategoryIDList = $_fetchParentCategoryIDList = array();

        foreach ($_parentCannedCategoryIDList as $_key => $_val) {
            if (isset($_cannedParentMap[$_val])) {
                foreach ($_cannedParentMap[$_val] as $_subKey => $_subVal) {
                    $_subCategoryIDList[] = $_subVal['cannedcategoryid'];
                    $_fetchParentCategoryIDList[] = $_subVal['cannedcategoryid'];
                }
            }
        }

        if (_is_array($_fetchParentCategoryIDList)) {
            $_subCategoryIDList = array_merge($_subCategoryIDList, self::RetrieveSubCategoryIDList($_fetchParentCategoryIDList));
        }

        return $_subCategoryIDList;
    }

    /**
     * Retrieve the canned categories in one go..
     *
     * @author Varun Shoor
     * @return array An array containig the categories and the parent <> child relationship map
     */
    public static function RetrieveCannedCategories()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_cannedCategoryContainer = $_cannedParentMap = array();

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "cannedcategories ORDER BY title ASC");
        while ($_SWIFT->Database->NextRecord()) {
            $_cannedCategoryContainer[$_SWIFT->Database->Record['cannedcategoryid']] = $_SWIFT->Database->Record;

            if (!isset($_cannedParentMap[$_SWIFT->Database->Record['parentcategoryid']])) {
                $_cannedParentMap[$_SWIFT->Database->Record['parentcategoryid']] = array();
            }

            $_cannedParentMap[$_SWIFT->Database->Record['parentcategoryid']][] = $_SWIFT->Database->Record;
        }

        return array('_cannedCategoryContainer' => $_cannedCategoryContainer, '_cannedParentMap' => $_cannedParentMap);
    }
}
