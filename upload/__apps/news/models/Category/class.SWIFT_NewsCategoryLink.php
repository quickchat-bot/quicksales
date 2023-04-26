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
 * The News Category Link Manager
 *
 * @author Varun Shoor
 */
class SWIFT_NewsCategoryLink extends SWIFT_Model
{
    const TABLE_NAME        =    'newscategorylinks';
    const PRIMARY_KEY        =    'newscategorylinkid';

    const TABLE_STRUCTURE    =    "newscategorylinkid I PRIMARY AUTO NOTNULL,
                                newsitemid I DEFAULT '0' NOTNULL,
                                newscategoryid I DEFAULT '0' NOTNULL";

    const INDEX_1            =    'newsitemid, newscategoryid';
    const INDEX_2            =    'newscategoryid, newsitemid';


    protected $_dataStore = array();

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param int $_newsCategoryLinkID The News Category Link ID
     * @throws SWIFT_Exception If the Record could not be loaded
     */
    public function __construct($_newsCategoryLinkID)
    {
        parent::__construct();

        if (!$this->LoadData($_newsCategoryLinkID)) {
            throw new SWIFT_Category_Exception('Failed to load News Category Link ID: ' .  ($_newsCategoryLinkID));
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

        $this->Database->AutoExecute(TABLE_PREFIX . 'newscategorylinks', $this->GetUpdatePool(), 'UPDATE', "newscategorylinkid = '" . (int) ($this->GetNewsCategoryLinkID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the News Category Link ID
     *
     * @author Varun Shoor
     * @return mixed "newscategorylinkid" on Success, "false" otherwise
     * @throws SWIFT_Category_Exception If the Class is not Loaded
     */
    public function GetNewsCategoryLinkID()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Category_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['newscategorylinkid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param \SWIFT_Data|int $_newsCategoryLinkID The News Category Link ID
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_newsCategoryLinkID)
    {
        $_dataStore = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "newscategorylinks WHERE newscategorylinkid = '" .  ($_newsCategoryLinkID) . "'");
        if (isset($_dataStore['newscategorylinkid']) && !empty($_dataStore['newscategorylinkid']))
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
        } else if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_Category_Exception(SWIFT_INVALIDDATA);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Create a new Category <> Item Link
     *
     * @author Varun Shoor
     * @param int $_newsCategoryID The News Category ID
     * @param int $_newsItemID The News Item ID
     * @return mixed "_newsCategoryLinkID" (INT) on Success, "false" otherwise
     * @throws SWIFT_Category_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function Create($_newsCategoryID, $_newsItemID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_newsCategoryID) || empty($_newsItemID))
        {
            throw new SWIFT_Category_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'newscategorylinks', array('newscategoryid' =>  ($_newsCategoryID), 'newsitemid' =>  ($_newsItemID)), 'INSERT');
        $_newsCategoryLinkID = $_SWIFT->Database->Insert_ID();

        if (!$_newsCategoryLinkID)
        {
            throw new SWIFT_Category_Exception(SWIFT_CREATEFAILED);
        }

        return $_newsCategoryLinkID;
    }

    /**
     * Delete the News Category Link record
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
        }

        self::DeleteList(array($this->GetNewsCategoryLinkID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of News Category Links
     *
     * @author Varun Shoor
     * @param array $_newsCategoryLinkIDList The News Category Link ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_newsCategoryLinkIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_newsCategoryLinkIDList))
        {
            return false;
        }

        $_finalNewsCategoryLinkIDList = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "newscategorylinks WHERE newscategorylinkid IN (" . BuildIN($_newsCategoryLinkIDList) . ")");
        while ($_SWIFT->Database->NextRecord())
        {
            $_finalNewsCategoryLinkIDList[] = $_SWIFT->Database->Record['newscategorylinkid'];
        }

        if (!count($_finalNewsCategoryLinkIDList))
        {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "newscategorylinks WHERE newscategorylinkid IN (" . BuildIN($_finalNewsCategoryLinkIDList) . ")");

        return true;
    }

    /**
     * Delete the links on news category id list
     *
     * @author Varun Shoor
     * @param array $_newsCategoryIDList The News Category ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteOnNewsCategory($_newsCategoryIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_newsCategoryIDList))
        {
            return false;
        }

        $_newsCategoryLinkIDList = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "newscategorylinks WHERE newscategoryid IN (" . BuildIN($_newsCategoryIDList) . ")");
        while ($_SWIFT->Database->NextRecord())
        {
            $_newsCategoryLinkIDList[] = $_SWIFT->Database->Record['newscategorylinkid'];
        }

        if (!count($_newsCategoryLinkIDList))
        {
            return false;
        }

        self::DeleteList($_newsCategoryLinkIDList);

        return false;
    }

    /**
     * Delete the links on news item id list
     *
     * @author Varun Shoor
     * @param array $_newsItemIDList The News Item ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteOnNewsItem($_newsItemIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_newsItemIDList))
        {
            return false;
        }

        $_newsCategoryLinkIDList = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "newscategorylinks WHERE newsitemid IN (" . BuildIN($_newsItemIDList) . ")");
        while ($_SWIFT->Database->NextRecord())
        {
            $_newsCategoryLinkIDList[] = $_SWIFT->Database->Record['newscategorylinkid'];
        }

        if (!count($_newsCategoryLinkIDList))
        {
            return false;
        }

        self::DeleteList($_newsCategoryLinkIDList);

        return false;
    }

    /**
     * Retrieve the News Category ID List based on News Item ID List
     *
     * @author Varun Shoor
     * @param array $_newsItemIDList The News Item ID List
     * @return array The News Category ID List
     */
    public static function RetrieveOnNewsItem($_newsItemIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_newsItemIDList))
        {
            return array();
        }

        $_newsCategoryIDList = array();

        $_SWIFT->Database->Query("SELECT newscategoryid FROM " . TABLE_PREFIX . "newscategorylinks WHERE newsitemid IN (" . BuildIN($_newsItemIDList) . ")");
        while ($_SWIFT->Database->NextRecord())
        {
            $_newsCategoryIDList[] = $_SWIFT->Database->Record['newscategoryid'];
        }

        return $_newsCategoryIDList;
    }

    /**
     * Retrieve the News Item ID List based on News Category ID List
     *
     * @author Varun Shoor
     * @param array $_newsCategoryIDList The News Category ID List
     * @return array The News Item ID List
     */
    public static function RetrieveOnNewsCategory($_newsCategoryIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_newsCategoryIDList))
        {
            return array();
        }

        $_newsItemIDList = array();

        $_SWIFT->Database->Query("SELECT newsitemid FROM " . TABLE_PREFIX . "newscategorylinks WHERE newscategoryid IN (" . BuildIN($_newsCategoryIDList) . ")");
        while ($_SWIFT->Database->NextRecord())
        {
            $_newsItemIDList[] = $_SWIFT->Database->Record['newsitemid'];
        }

        return $_newsItemIDList;
    }
}
