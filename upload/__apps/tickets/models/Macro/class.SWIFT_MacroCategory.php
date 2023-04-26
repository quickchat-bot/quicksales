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

namespace Tickets\Models\Macro;

use SWIFT;
use SWIFT_Data;
use SWIFT_DataID;
use SWIFT_DataStore;
use SWIFT_Exception;
use SWIFT_Model;
use Base\Models\Staff\SWIFT_Staff;

/**
 * The Macro Category Model
 *
 * @author Varun Shoor
 */
class SWIFT_MacroCategory extends SWIFT_Model
{
    const TABLE_NAME        =    'macrocategories';
    const PRIMARY_KEY        =    'macrocategoryid';

    const TABLE_STRUCTURE    =    "macrocategoryid I PRIMARY AUTO NOTNULL,
                                parentcategoryid I DEFAULT '0' NOTNULL,
                                categorytype I2 DEFAULT '0' NOTNULL,
                                restrictstaffgroupid I DEFAULT '0' NOTNULL,
                                staffid I DEFAULT '0' NOTNULL,
                                title C(255) DEFAULT '' NOTNULL";

    const INDEX_1            =    'parentcategoryid';
    const INDEX_2            =    'categorytype, staffid';


    protected $_dataStore = array();

    static protected $_macroCategoryCache = false;

    // Core Constants
    const TYPE_PUBLIC = 1;
    const TYPE_PRIVATE = 0;

    const CATEGORY_ROOT = 0;

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
            throw new SWIFT_Exception('Failed to load Macro Category Object');
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
            return false;
        }

        if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'macrocategories', $this->GetUpdatePool(), 'UPDATE', "macrocategoryid = '" . (int) ($this->GetMacroCategoryID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Macro Category ID
     *
     * @author Varun Shoor
     * @return mixed "macrocategoryid" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetMacroCategoryID()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['macrocategoryid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param SWIFT_Data $_SWIFT_DataObject The SWIFT_Data Object
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    protected function LoadData($_SWIFT_DataObject)
    {
        $_SWIFT = SWIFT::GetInstance();

        // Is it a ID?
        if ($_SWIFT_DataObject instanceof SWIFT_DataID && $_SWIFT_DataObject->GetIsClassLoaded())
        {
            $_dataStore = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "macrocategories WHERE macrocategoryid = '" . (int) ($_SWIFT_DataObject->GetDataID()) . "'");
            if (isset($_dataStore['macrocategoryid']) && !empty($_dataStore['macrocategoryid']))
            {
                $this->_dataStore = $_dataStore;

                return true;
            }

            // Is it a Store?
        } else if ($_SWIFT_DataObject instanceof SWIFT_DataStore && $_SWIFT_DataObject->GetIsClassLoaded()) {
            $this->_dataStore = $_SWIFT_DataObject->GetDataStore();

            if (!isset($this->_dataStore['macrocategoryid']) || empty($this->_dataStore['macrocategoryid']))
            {
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
        if (!$this->GetIsClassLoaded())
        {
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
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Check to see if its a valid category type
     *
     * @author Varun Shoor
     * @param mixed $_categoryType The Category Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidCategoryType($_categoryType)
    {
        if ($_categoryType == self::TYPE_PUBLIC || $_categoryType == self::TYPE_PRIVATE)
        {
            return true;
        }

        return false;
    }

    /**
     * Create a new Macro Category Record
     *
     * @author Varun Shoor
     * @param string $_categoryTitle The Category Title
     * @param mixed $_categoryType The Category Type
     * @param int $_parentCategoryID The Parent Category ID
     * @param int $_restrictStaffGroupID Restrict the category visibility to the given staff group
     * @param SWIFT_Staff $_SWIFT_StaffObject (OPTIONAL) The SWIFT_Staff Object of Creator
     * @return int The Macro Category ID
     * @throws SWIFT_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function Create($_categoryTitle, $_categoryType, $_parentCategoryID = 0, $_restrictStaffGroupID = 0, SWIFT_Staff $_SWIFT_StaffObject = null)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_categoryTitle) || !self::IsValidCategoryType($_categoryType))
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_staffID = 0;
        if ($_SWIFT_StaffObject instanceof SWIFT_Staff && $_SWIFT_StaffObject->GetIsClassLoaded())
        {
            $_staffID = $_SWIFT_StaffObject->GetStaffID();
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'macrocategories', array('title' => $_categoryTitle, 'categorytype' => (int) ($_categoryType), 'parentcategoryid' => $_parentCategoryID,
            'staffid' => $_staffID, 'restrictstaffgroupid' => $_restrictStaffGroupID), 'INSERT');
        $_macroCategoryID = $_SWIFT->Database->Insert_ID();

        if (!$_macroCategoryID)
        {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        return $_macroCategoryID;
    }

    /**
     * Update the Macro Category Record
     *
     * @author Varun Shoor
     * @param string $_categoryTitle The Category Title
     * @param mixed $_categoryType The Category Type
     * @param int $_parentCategoryID The Parent Category ID
     * @param int $_restrictStaffGroupID Restrict the category visibility to the given staff group
     * @param SWIFT_Staff $_SWIFT_StaffObject (OPTIONAL) The SWIFT_Staff Object of Creator
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function Update($_categoryTitle, $_categoryType, $_parentCategoryID = 0, $_restrictStaffGroupID = 0, SWIFT_Staff $_SWIFT_StaffObject = null)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_categoryTitle) || !self::IsValidCategoryType($_categoryType))
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_staffID = 0;
        if ($_SWIFT_StaffObject instanceof SWIFT_Staff && $_SWIFT_StaffObject->GetIsClassLoaded())
        {
            $_staffID = $_SWIFT_StaffObject->GetStaffID();
        }

        $this->UpdatePool('title', $_categoryTitle);
        $this->UpdatePool('categorytype', (int) ($_categoryType));
        $this->UpdatePool('parentcategoryid', $_parentCategoryID);
        $this->UpdatePool('staffid', $_staffID);
        $this->UpdatePool('restrictstaffgroupid', $_restrictStaffGroupID);
        $this->ProcessUpdatePool();

        return true;
    }

    /**
     * Delete a Macro Category record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($this->GetMacroCategoryID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of Macro Categories
     *
     * @author Varun Shoor
     * @param array $_macroCategoryIDList
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_macroCategoryIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_macroCategoryIDList))
        {
            return false;
        }

        // Merge any sub categories...
        $_macroCategoryIDList = array_merge($_macroCategoryIDList, self::RetrieveSubCategoryIDList($_macroCategoryIDList));

        $_index = 1;
        $_finalText = '';

        $_finalMacroCategoryIDList = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "macrocategories WHERE macrocategoryid IN (" . BuildIN($_macroCategoryIDList) . ")");
        while ($_SWIFT->Database->NextRecord())
        {
            $_finalMacroCategoryIDList[] = $_SWIFT->Database->Record['macrocategoryid'];

            $_finalText .= $_index . '. ' . htmlspecialchars($_SWIFT->Database->Record['title']) . '<BR />';

            $_index++;
        }

        if (!_is_array($_finalMacroCategoryIDList))
        {
            return false;
        }

        SWIFT::Info(sprintf($_SWIFT->Language->Get('titledelmacrocat'), count($_finalMacroCategoryIDList)), $_SWIFT->Language->Get('msgdelmacrocat') . '<BR />' . $_finalText);

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "macrocategories WHERE macrocategoryid IN (" . BuildIN($_macroCategoryIDList) . ")");

        SWIFT_MacroReply::DeleteOnCategory($_macroCategoryIDList);

        return true;
    }

    /**
     * Retrieve list of sub categories
     *
     * @author Varun Shoor
     * @param array $_parentMacroCategoryIDList
     * @return array
     */
    public static function RetrieveSubCategoryIDList($_parentMacroCategoryIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_parentMacroCategoryIDList))
        {
            return array();
        }

        if (!self::$_macroCategoryCache)
        {
            self::$_macroCategoryCache = self::RetrieveMacroCategories();
        }

        extract(self::$_macroCategoryCache);

        $_subCategoryIDList = $_fetchParentCategoryIDList = array();

        foreach ($_parentMacroCategoryIDList as $_key => $_val)
        {
            if (isset($_macroCategoryParentMap[$_val]))
            {
                foreach ($_macroCategoryParentMap[$_val] as $_subKey => $_subVal)
                {
                    $_subCategoryIDList[] = $_subVal['macrocategoryid'];
                    $_fetchParentCategoryIDList[] = $_subVal['macrocategoryid'];
                }
            }
        }

        if (count($_fetchParentCategoryIDList))
        {
            $_subCategoryIDList = array_merge($_subCategoryIDList, self::RetrieveSubCategoryIDList($_fetchParentCategoryIDList));
        }

        return $_subCategoryIDList;
    }

    /**
     * Retrieve the macro categories in one go..
     *
     * @author Varun Shoor
     * @return array An array containig the categories and the parent <> child relationship map
     */
    public static function RetrieveMacroCategories()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_macroCategoryContainer = $_macroCategoryParentMap = array();

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "macrocategories ORDER BY title ASC");
        while ($_SWIFT->Database->NextRecord())
        {
            $_macroCategoryContainer[$_SWIFT->Database->Record['macrocategoryid']] = $_SWIFT->Database->Record;

            if (!isset($_macroCategoryParentMap[$_SWIFT->Database->Record['parentcategoryid']]))
            {
                $_macroCategoryParentMap[$_SWIFT->Database->Record['parentcategoryid']] = array();
            }

            $_macroCategoryParentMap[$_SWIFT->Database->Record['parentcategoryid']][] = $_SWIFT->Database->Record;
        }

        return array('_macroCategoryContainer' => $_macroCategoryContainer, '_macroCategoryParentMap' => $_macroCategoryParentMap);
    }
}
?>
