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

namespace News\Models\Category;

use SWIFT;
use SWIFT_Exception;
use SWIFT_Model;

/**
 * The News Category Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_NewsCategory extends SWIFT_Model
{
    const TABLE_NAME        =    'newscategories';
    const PRIMARY_KEY        =    'newscategoryid';

    const TABLE_STRUCTURE    =    "newscategoryid I PRIMARY AUTO NOTNULL,
                                categorytitle C(255) DEFAULT '' NOTNULL,
                                newsitemcount I DEFAULT '0' NOTNULL,
                                visibilitytype C(20) DEFAULT '' NOTNULL,
                                dateline I DEFAULT '0' NOTNULL,
                                lastupdate I DEFAULT '0' NOTNULL,
                                titlehash C(50) DEFAULT '' NOTNULL";

    const INDEX_1            =    'visibilitytype';
    const INDEX_2            =    'titlehash, visibilitytype';

    protected $_dataStore = array();

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param int $_newsCategoryID The News Category ID
     * @throws SWIFT_Exception
     */
    public function __construct($_newsCategoryID)
    {
        parent::__construct();

        if (!$this->LoadData($_newsCategoryID)) {
            throw new SWIFT_Category_Exception('Failed to load News Category ID: ' .  ($_newsCategoryID));
        }
    }

    /**
     * Destructor
     *
     * @author Varun Shoor
     * @throws SWIFT_Exception
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
     * @throws SWIFT_Category_Exception If the Class is not Loaded
     * @throws SWIFT_Exception
     */
    public function ProcessUpdatePool()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        } else if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'newscategories', $this->GetUpdatePool(), 'UPDATE', "newscategoryid = '" . (int) ($this->GetNewsCategoryID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the News Category ID
     *
     * @author Varun Shoor
     * @return mixed "newscategoryid" on Success, "false" otherwise
     * @throws SWIFT_Category_Exception If the Class is not Loaded
     */
    public function GetNewsCategoryID()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Category_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        return $this->_dataStore['newscategoryid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param \SWIFT_Data|int $_newsCategoryID The News Category ID
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_newsCategoryID)
    {
        $_dataStore = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "newscategories WHERE newscategoryid = '" .  ($_newsCategoryID) . "'");
        if (isset($_dataStore['newscategoryid']) && !empty($_dataStore['newscategoryid']))
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
     * @throws SWIFT_Category_Exception If the Class is not Loaded
     */
    public function GetDataStore()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Category_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore;
    }

    /**
     * Retrieves a Property Value from Data Store
     *
     * @author Varun Shoor
     * @param string $_key The Key Identifier
     * @return mixed Property Data on Success, "false" otherwise
     * @throws SWIFT_Category_Exception If the Class is not Loaded
     */
    public function GetProperty($_key)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Category_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_Category_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Check to see if its a valid visibility type
     *
     * @author Varun Shoor
     * @param mixed $_visibilityType The Category Visibility Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidVisibilityType($_visibilityType)
    {
        if ($_visibilityType == SWIFT_PUBLIC || $_visibilityType == SWIFT_PRIVATE)
        {
            return true;
        }

        return false;
    }

    /**
     * Create a new News Category
     *
     * @author Varun Shoor
     * @param string $_categoryTitle The Category Title
     * @param mixed $_visibilityType The Category Visibility Type
     * @return mixed "_newsCategoryID" (INT) on Success, "false" otherwise
     * @throws SWIFT_Category_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function Create($_categoryTitle, $_visibilityType)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_categoryTitle) || !self::IsValidVisibilityType($_visibilityType))
        {
            throw new SWIFT_Category_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'newscategories', array('categorytitle' => $_categoryTitle, 'newsitemcount' => 0, 'dateline' => DATENOW,
            'visibilitytype' => $_visibilityType, 'titlehash' => md5($_categoryTitle)), 'INSERT');
        $_newsCategoryID = $_SWIFT->Database->Insert_ID();

        if (!$_newsCategoryID)
        {
            throw new SWIFT_Category_Exception(SWIFT_CREATEFAILED);

            return false;
        }

        self::RebuildCache();

        return $_newsCategoryID;
    }

    /**
     * Update News Category Record
     *
     * @author Varun Shoor
     * @param string $_categoryTitle The Category Title
     * @param mixed $_visibilityType The Category Visibility Type
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Category_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function Update($_categoryTitle, $_visibilityType)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Category_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_categoryTitle) || !self::IsValidVisibilityType($_visibilityType)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->UpdatePool('categorytitle', $_categoryTitle);
        $this->UpdatePool('visibilitytype', $_visibilityType);
        $this->UpdatePool('titlehash', md5($_categoryTitle));
        $this->UpdatePool('lastupdate', DATENOW);
        $this->ProcessUpdatePool();

        self::RebuildCache();

        return true;
    }

    /**
     * Delete the News Category record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Category_Exception If the Class is not Loaded
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Category_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        self::DeleteList(array($this->GetNewsCategoryID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of News Categories
     *
     * @author Varun Shoor
     * @param array $_newsCategoryIDList The News Category ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_newsCategoryIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_newsCategoryIDList))
        {
            return false;
        }

        $_finalNewsCategoryIDList = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "newscategories WHERE newscategoryid IN (" . BuildIN($_newsCategoryIDList) . ")");
        while ($_SWIFT->Database->NextRecord())
        {
            $_finalNewsCategoryIDList[] = $_SWIFT->Database->Record['newscategoryid'];
        }

        if (!count($_finalNewsCategoryIDList))
        {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "newscategories WHERE newscategoryid IN (" . BuildIN($_finalNewsCategoryIDList) . ")");

        SWIFT_NewsCategoryLink::DeleteOnNewsCategory($_finalNewsCategoryIDList);

        return true;
    }

    /**
     * Rebuild the News Category Cache
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public static function RebuildCache()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_newsCategoryCountContainer = array();
        $_SWIFT->Database->Query("SELECT COUNT(*) AS totalitems, newscategoryid FROM " . TABLE_PREFIX . "newscategorylinks GROUP BY newscategoryid");
        while ($_SWIFT->Database->NextRecord())
        {
            $_newsCategoryCountContainer[$_SWIFT->Database->Record['newscategoryid']] = (int) ($_SWIFT->Database->Record['totalitems']);
        }

        foreach ($_newsCategoryCountContainer as $_key => $_val)
        {
            $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'newscategories', array('newsitemcount' =>  ($_val)), 'UPDATE', "newscategoryid = '" . (int) ($_key) . "'");
        }

        $_newsCategoryContainer = array();

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "newscategories ORDER BY newscategoryid ASC");
        while ($_SWIFT->Database->NextRecord())
        {
            $_newsCategoryContainer[$_SWIFT->Database->Record['newscategoryid']] = $_SWIFT->Database->Record;
        }

        $_SWIFT->Cache->Update('newscategorycache', $_newsCategoryContainer);

        return true;
    }

    /**
     * Create or Update categories from sync and create the necessary linkages
     *
     * @author Varun Shoor
     * @param array $_categoryList The Category List
     * @param int $_newsItemID The News Item ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function CreateOrUpdateFromSync($_categoryList, $_newsItemID, $_visibilityType)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_categoryMD5List = $_categoryMD5Pointer = $_newsCategoryIDList = array();
        foreach ($_categoryList as $_categoryTitle)
        {
            $_titleHash = md5($_categoryTitle);

            $_categoryMD5List[] = $_titleHash;
            $_categoryMD5Pointer[$_titleHash] = $_categoryTitle;
        }

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "newscategories
            WHERE titlehash IN (" . BuildIN($_categoryMD5List) . ")
                AND visibilitytype = '" . $_SWIFT->Database->Escape($_visibilityType) . "'");
        while ($_SWIFT->Database->NextRecord())
        {
            if (!isset($_categoryMD5Pointer[$_SWIFT->Database->Record['titlehash']]))
            {
                continue;
            }

            $_newsCategoryIDList[] = $_SWIFT->Database->Record['newscategoryid'];

            unset($_categoryMD5Pointer[$_SWIFT->Database->Record['titlehash']]);
        }

        if (count($_categoryMD5Pointer))
        {
            foreach ($_categoryMD5Pointer as $_categoryTitle)
            {
                $_newsCategoryIDList[] = SWIFT_NewsCategory::Create($_categoryTitle, $_visibilityType);
            }
        }

        if (!count($_newsCategoryIDList))
        {
            return false;
        }

        // Retrieve all the links for the news item
        $_ignoreNewsCategoryIDList = array();
        $_SWIFT->Database->Query("SELECT newscategoryid FROM " . TABLE_PREFIX . "newscategorylinks WHERE newsitemid = '" .  ($_newsItemID) . "'");
        while ($_SWIFT->Database->NextRecord())
        {
            $_ignoreNewsCategoryIDList[] = $_SWIFT->Database->Record['newscategoryid'];
        }

        foreach ($_newsCategoryIDList as $_newsCategoryID)
        {
            if (in_array($_newsCategoryID, $_ignoreNewsCategoryIDList))
            {
                continue;
            }

            SWIFT_NewsCategoryLink::Create($_newsCategoryID, $_newsItemID);
        }

        return true;
    }
}
?>
