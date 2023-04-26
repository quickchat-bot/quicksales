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

namespace Knowledgebase\Models\Article;

use SWIFT;
use SWIFT_Data;
use SWIFT_DataID;
use SWIFT_DataStore;
use SWIFT_Exception;
use SWIFT_Model;

/**
 * The Knowledgebase Article Link Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_KnowledgebaseArticleLink extends SWIFT_Model
{
    const TABLE_NAME        =    'kbarticlelinks';
    const PRIMARY_KEY        =    'kbarticlelinkid';

    const TABLE_STRUCTURE    =    "kbarticlelinkid I PRIMARY AUTO NOTNULL,
                                kbarticleid I DEFAULT '0' NOTNULL,
                                linktype I2 DEFAULT '0' NOTNULL,
                                linktypeid I DEFAULT '0' NOTNULL";

    const INDEX_1            =    'kbarticleid';
    const INDEX_2            =    'linktype, linktypeid, kbarticleid';


    protected $_dataStore = array();

    // Core Constants
    const LINKTYPE_ARTICLE = 1;
    const LINKTYPE_CATEGORY = 2;

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
            throw new SWIFT_Exception('Failed to load Knowledgebase Article Link Object');
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
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ProcessUpdatePool()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'kbarticlelinks', $this->GetUpdatePool(), 'UPDATE', "kbarticlelinkid = '" . ($this->GetKnowledgebaseArticleLinkID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Knowledgebase Article Link ID
     *
     * @author Varun Shoor
     * @return mixed "kbarticlelinkid" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetKnowledgebaseArticleLinkID()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['kbarticlelinkid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param SWIFT_Data $_SWIFT_DataObject The SWIFT_Data Object
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    protected function LoadData($_SWIFT_DataObject)
    {
        $_SWIFT = SWIFT::GetInstance();

        // Is it a ID?
        $isClassLoaded = $_SWIFT_DataObject->GetIsClassLoaded();
        if ($_SWIFT_DataObject instanceof SWIFT_DataID && $isClassLoaded)
        {
            $_dataStore = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "kbarticlelinks WHERE kbarticlelinkid = '" . ($_SWIFT_DataObject->GetDataID()) . "'");
            if (isset($_dataStore['kbarticlelinkid']) && !empty($_dataStore['kbarticlelinkid']))
            {
                $this->_dataStore = $_dataStore;

                return true;
            }

            // Is it a Store?
        } else if ($_SWIFT_DataObject instanceof SWIFT_DataStore && $isClassLoaded) {
            $this->_dataStore = $_SWIFT_DataObject->GetDataStore();

            if (!isset($this->_dataStore['kbarticlelinkid']) || empty($this->_dataStore['kbarticlelinkid']))
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
     * Check to see if its a valid link type
     *
     * @author Varun Shoor
     * @param mixed $_linkType The Link Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidLinkType($_linkType)
    {
        return $_linkType == self::LINKTYPE_ARTICLE || $_linkType == self::LINKTYPE_CATEGORY;
    }

    /**
     * Create a new Knowledgebase Article Link
     *
     * @author Varun Shoor
     * @param int $_knowledgebaseArticleID The Knowledgebase Article ID
     * @param mixed $_linkType The Link Type
     * @param int $_linkTypeID The Link Type ID
     * @return int The Knowledgebase Article Link ID
     * @throws SWIFT_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function Create($_knowledgebaseArticleID, $_linkType, $_linkTypeID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_knowledgebaseArticleID) || !self::IsValidLinkType($_linkType))
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'kbarticlelinks', array('kbarticleid' => ($_knowledgebaseArticleID), 'linktype' => ($_linkType),
            'linktypeid' => ($_linkTypeID)), 'INSERT');
        $_knowledgebaseArticleLinkID = $_SWIFT->Database->Insert_ID();

        if (!$_knowledgebaseArticleLinkID)
        {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        return $_knowledgebaseArticleLinkID;
    }

    /**
     * Delete the Knowledgebase Article Link record
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

        self::DeleteList(array($this->GetKnowledgebaseArticleLinkID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of Knowledgebase Article Link ID
     *
     * @author Varun Shoor
     * @param array $_knowledgebaseArticleLinkIDList
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function DeleteList($_knowledgebaseArticleLinkIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_knowledgebaseArticleLinkIDList))
        {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "kbarticlelinks WHERE kbarticlelinkid IN (" . BuildIN($_knowledgebaseArticleLinkIDList) . ")");

        return true;
    }

    /**
     * Delete the Knowledgebase Article Links based on Knowledgebase Article ID List
     *
     * @author Varun Shoor
     * @param array $_knowledgebaseArticleIDList The Knowledgebase Article ID List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function DeleteOnKnowledgebaseArticle($_knowledgebaseArticleIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_knowledgebaseArticleIDList))
        {
            return false;
        }

        $_knowledgebaseArticleLinkIDList = array();
        $_SWIFT->Database->Query("SELECT kbarticlelinkid FROM " . TABLE_PREFIX . "kbarticlelinks WHERE kbarticleid IN (" . BuildIN($_knowledgebaseArticleIDList) . ")");
        while ($_SWIFT->Database->NextRecord())
        {
            $_knowledgebaseArticleLinkIDList[] = $_SWIFT->Database->Record['kbarticlelinkid'];
        }

        if (!count($_knowledgebaseArticleLinkIDList))
        {
            return false;
        }

        self::DeleteList($_knowledgebaseArticleLinkIDList);

        return true;
    }

    /**
     * Delete the Knowledgebase Article Links based on Link Type ID List
     *
     * @author Varun Shoor
     * @param mixed $_linkType The Link Type
     * @param array $_linkTypeIDList The Link Type ID List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function DeleteOnLinkType($_linkType, $_linkTypeIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!self::IsValidLinkType($_linkType))
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if (!_is_array($_linkTypeIDList))
        {
            return false;
        }

        $_knowledgebaseArticleLinkIDList = array();
        $_SWIFT->Database->Query("SELECT kbarticlelinkid FROM " . TABLE_PREFIX . "kbarticlelinks WHERE linktype = '" . ($_linkType) . "' AND linktypeid IN (" . BuildIN($_linkTypeIDList) . ")");
        while ($_SWIFT->Database->NextRecord())
        {
            $_knowledgebaseArticleLinkIDList[] = $_SWIFT->Database->Record['kbarticlelinkid'];
        }

        if (!count($_knowledgebaseArticleLinkIDList))
        {
            return false;
        }

        self::DeleteList($_knowledgebaseArticleLinkIDList);

        return true;
    }

    /**
     * Retrieve the Link ID List based on Knowledgebase Article ID
     *
     * @author Varun Shoor
     * @param int $_knowledgebaseArticleID The Knowledgebase Article ID
     * @param mixed $_linkType The Link TYpe
     * @return array
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveLinkIDListOnArticle($_knowledgebaseArticleID, $_linkType = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!empty($_linkType) && !self::IsValidLinkType($_linkType))
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_extendedSQL = '';
        if (!empty($_linkType))
        {
            $_extendedSQL = " AND linktype = '" . ($_linkType) . "'";
        }

        $_linkTypeIDList = array();

        $_SWIFT->Database->Query("SELECT linktypeid FROM " . TABLE_PREFIX . "kbarticlelinks WHERE kbarticleid = '" . ($_knowledgebaseArticleID) . "'" . $_extendedSQL);
        while ($_SWIFT->Database->NextRecord())
        {
            $_linkTypeIDList[] = $_SWIFT->Database->Record['linktypeid'];
        }

        return $_linkTypeIDList;
    }
}
