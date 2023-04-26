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

namespace Base\Models\Template;

use SWIFT;
use SWIFT_App;
use SWIFT_CacheManager;
use SWIFT_Exception;
use SWIFT_Model;
use Base\Library\Template\SWIFT_Template_Exception;

/**
 * The Template Category Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_TemplateCategory extends SWIFT_Model
{
    const TABLE_NAME = 'templatecategories';
    const PRIMARY_KEY = 'tcategoryid';

    const TABLE_STRUCTURE = "tcategoryid I PRIMARY AUTO NOTNULL,
                                tgroupid I DEFAULT '0' NOTNULL,
                                name C(60) DEFAULT '' NOTNULL,
                                description C(255) DEFAULT '' NOTNULL,
                                icon C(255) DEFAULT '' NOTNULL,
                                app C(100) DEFAULT '' NOTNULL";

    const INDEX_1 = 'tgroupid';

    const COLUMN_RENAME_MODULE = 'app';

    protected $_dataStore = array();

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param int $_templateCategoryID The Template Category ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Template_Exception If the Record could not be loaded
     */
    public function __construct($_templateCategoryID)
    {
        parent::__construct();

        if (!$this->LoadData($_templateCategoryID)) {
            throw new SWIFT_Template_Exception('Failed to load Template Category ID: ' . $_templateCategoryID);
        }
    }

    /**
     * Destructor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
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
     * @throws SWIFT_Template_Exception If the Class is not Loaded
     */
    public function ProcessUpdatePool()
    {
        if (!_is_array($this->GetUpdatePool())) {
            return false;
        } else if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'templatecategories', $this->GetUpdatePool(), 'UPDATE', "tcategoryid = '" . (int)($this->GetTemplateCategoryID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Template Category ID
     *
     * @author Varun Shoor
     * @return mixed "templatecategoryid" on Success, "false" otherwise
     * @throws SWIFT_Template_Exception If the Class is not Loaded
     */
    public function GetTemplateCategoryID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Template_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['tcategoryid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param int $_templateCategoryID The Template Category ID
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_templateCategoryID)
    {
        $_dataStore = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "templatecategories WHERE tcategoryid = '" . $_templateCategoryID . "'");
        if (isset($_dataStore['tcategoryid']) && !empty($_dataStore['tcategoryid'])) {
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
     * @throws SWIFT_Template_Exception If the Class is not Loaded
     */
    public function GetDataStore()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Template_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore;
    }

    /**
     * Retrieves a Property Value from Data Store
     *
     * @author Varun Shoor
     * @param string $_key The Key Identifier
     * @return mixed Property Data on Success, "false" otherwise
     * @throws SWIFT_Template_Exception If the Class is not Loaded
     */
    public function GetProperty($_key)
    {
        if (!$this->GetIsClassLoaded() || !isset($this->_dataStore[$_key])) {
            throw new SWIFT_Template_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Create a new Template Category
     *
     * @author Varun Shoor
     * @param int $_templateGroupID The Template Group ID
     * @param string $_categoryName The Category Name
     * @param string $_app The Category App
     * @param string $_categoryDescription (OPTIONAL) The Category Description
     * @param string $_icon (OPTIONAL) The Category Icon
     * @param bool $_ignoreAppCheck (OPTIONAL) Whether to ignore the app registered check or not
     * @return mixed "templateCategoryID" (INT) on Success, "false" otherwise
     * @throws SWIFT_Template_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function Create($_templateGroupID, $_categoryName, $_app, $_categoryDescription = '', $_icon = '', $_ignoreAppCheck = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_templateGroupID) || empty($_categoryName) || empty($_app) || (!$_ignoreAppCheck && !SWIFT_App::IsInstalled($_app))) {
            throw new SWIFT_Template_Exception(SWIFT_INVALIDDATA);
        }

        $_queryResult = $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'templatecategories', array('tgroupid' => $_templateGroupID, 'name' => $_categoryName, 'description' => ReturnNone($_categoryDescription), 'app' => $_app, 'icon' => ReturnNone($_icon)), 'INSERT');
        if (!$_queryResult) {
            throw new SWIFT_Template_Exception(SWIFT_CREATEFAILED);
        }

        $_templateCategoryID = $_SWIFT->Database->Insert_ID();
        if (!$_templateCategoryID) {
            throw new SWIFT_Template_Exception(SWIFT_CREATEFAILED);
        }

        return $_templateCategoryID;
    }

    /**
     * Retrieve all the Template IDs under this Category
     *
     * @author Varun Shoor
     * @return mixed "_templateIDList" (ARRAY) on Success, "false" otherwise
     * @throws SWIFT_Template_Exception If the Class is not Loaded
     */
    public function GetTemplateIDList()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Template_Exception(SWIFT_CLASSNOTLOADED);
        }

        return SWIFT_Template::GetTemplateIDListFromTemplateCategory($this->GetTemplateCategoryID());
    }

    /**
     * Restore all Templates under this category to their default counter parts
     *
     * @author Varun Shoor
     * @param int $_staffID (OPTIONAL) The Staff ID of the Staff Making the Change
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Template_Exception If the Class is not Loaded
     */
    public function Restore($_staffID = 0)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Template_Exception(SWIFT_CLASSNOTLOADED);
        }

        // First get the template ids..
        $_templateIDList = $this->GetTemplateIDList();
        if (!count($_templateIDList)) {
            return false;
        }

        foreach ($_templateIDList as $_key => $_val) {
            $_SWIFT_TemplateObject = new SWIFT_Template($_val);
            if ($_SWIFT_TemplateObject instanceof SWIFT_Template && $_SWIFT_TemplateObject->GetIsClassLoaded()) {
                $_SWIFT_TemplateObject->Restore(true, $_staffID);
            }
        }

        SWIFT_CacheManager::EmptyCacheDirectory();

        return true;
    }

    /**
     * Retrieve the appropriate label for this category
     *
     * @author Varun Shoor
     * @return mixed "The Category Label/Name" (STRING) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetLabel()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->Language->Get($this->GetProperty('name'))) {
            return $this->Language->Get($this->GetProperty('name'));
        }

        return $this->GetProperty('name');
    }

    /**
     * Delete the Template Category record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Template_Exception If the Class is not Loaded
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Template_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($this->GetTemplateCategoryID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of Template Categories
     *
     * @author Varun Shoor
     * @param array $_templateCategoryIDList The Template Category ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_templateCategoryIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_templateCategoryIDList)) {
            return false;
        }

        $_finalTemplateCategoryIDList = array();

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "templatecategories WHERE tcategoryid IN (" . BuildIN($_templateCategoryIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_finalTemplateCategoryIDList[] = $_SWIFT->Database->Record['tcategoryid'];
        }

        if (!count($_finalTemplateCategoryIDList)) {
            return false;
        }

        SWIFT_Template::DeleteOnTemplateCategory($_finalTemplateCategoryIDList);

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "templatecategories WHERE tcategoryid IN (" . BuildIN($_finalTemplateCategoryIDList) . ")");

        return true;
    }

    /**
     * Delete the Template Categories based on Template Group IDs
     *
     * @author Varun Shoor
     * @param array $_templateGroupIDList The Template Group ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteOnTemplateGroup($_templateGroupIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_templateGroupIDList)) {
            return false;
        }

        $_templateCategoryIDList = array();

        $_SWIFT->Database->Query("SELECT tcategoryid FROM " . TABLE_PREFIX . "templatecategories WHERE tgroupid IN (" . BuildIN($_templateGroupIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_templateCategoryIDList[] = $_SWIFT->Database->Record['tcategoryid'];
        }

        if (!count($_templateCategoryIDList)) {
            return false;
        }

        self::DeleteList($_templateCategoryIDList);

        return true;
    }

    /**
     * Retrieve the Categories as an array based on the specified template group
     *
     * @author Varun Shoor
     * @param int $_templateGroupID The Template Group ID
     * @return mixed "_templateCategoryContainer" (ARRAY) on Success, "false" otherwise
     */
    public static function GetCategoryData($_templateGroupID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_templateGroupID)) {
            return false;
        }

        $_templateCategoryContainer = array();

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "templatecategories WHERE tgroupid = '" . $_templateGroupID . "'");
        while ($_SWIFT->Database->NextRecord()) {
            $_templateCategoryContainer[$_SWIFT->Database->Record['tcategoryid']] = $_SWIFT->Database->Record;
        }

        if (!count($_templateCategoryContainer)) {
            return false;
        }

        return $_templateCategoryContainer;
    }

    /**
     * Delete on a list of App Names
     *
     * @author Varun Shoor
     * @param array $_appNameList
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function DeleteOnApp($_appNameList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_appNameList)) {
            return false;
        }

        $_templateCategoryIDList = array();
        $_SWIFT->Database->Query("SELECT tcategoryid FROM " . TABLE_PREFIX . "templatecategories
            WHERE app IN (" . BuildIN($_appNameList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_templateCategoryIDList[] = $_SWIFT->Database->Record['tcategoryid'];
        }

        self::DeleteList($_templateCategoryIDList);

        return true;
    }
}

?>
