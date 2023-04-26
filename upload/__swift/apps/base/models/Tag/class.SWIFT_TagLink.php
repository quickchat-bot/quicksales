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

namespace Base\Models\Tag;

use SWIFT;
use SWIFT_Exception;
use SWIFT_Model;
use Base\Library\Tag\SWIFT_Tag_Exception;

/**
 * The Tag Link Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_TagLink extends SWIFT_Model
{
    const TABLE_NAME = 'taglinks';
    const PRIMARY_KEY = 'taglinkid';

    const TABLE_STRUCTURE = "taglinkid I PRIMARY AUTO NOTNULL,
                                staffid I DEFAULT '0' NOTNULL,
                                dateline I DEFAULT '0' NOTNULL,
                                tagid I DEFAULT '0' NOTNULL,
                                linktype I2 DEFAULT '0' NOTNULL,
                                linkid I DEFAULT '0' NOTNULL";

    const INDEX_1 = 'tagid, linktype';
    const INDEX_2 = 'linktype, linkid';


    static protected $_tagCache = array();
    protected $_dataStore = array();

    // Core Constants
    const TYPE_TICKET = 1;
    const TYPE_KNOWLEDGEBASE = 2;
    const TYPE_TROUBLESHOOTER = 3;
    const TYPE_DOWNLOAD = 4;
    const TYPE_USER = 5;
    const TYPE_USERORGANIZATION = 6;
    const TYPE_CHAT = 7;
    const TYPE_CHATMESSAGE = 8;
    const TYPE_REPORT = 9;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param int $_tagLinkID The Tag Link ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Record could not be loaded
     */
    public function __construct($_tagLinkID)
    {
        parent::__construct();

        if (!$this->LoadData($_tagLinkID)) {
            throw new SWIFT_Exception('Failed to load Tag Link ID: ' . $_tagLinkID);
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
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ProcessUpdatePool()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        } else if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'taglinks', $this->GetUpdatePool(), 'UPDATE', "taglinkid = '" . (int)($this->GetTagLinkID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Tag Link ID
     *
     * @author Varun Shoor
     * @return mixed "taglinkid" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetTagLinkID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['taglinkid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param int $_tagLinkID The Tag Link ID
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_tagLinkID)
    {
        $_dataStore = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "taglinks WHERE taglinkid = '" . $_tagLinkID . "'");
        if (isset($_dataStore['taglinkid']) && !empty($_dataStore['taglinkid'])) {
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
     * Check to see if it is a valid link type
     *
     * @author Varun Shoor
     * @param mixed $_linkType The Tag Link Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidType($_linkType)
    {
        if ($_linkType == self::TYPE_TICKET || $_linkType == self::TYPE_KNOWLEDGEBASE || $_linkType == self::TYPE_TROUBLESHOOTER ||
            $_linkType == self::TYPE_DOWNLOAD || $_linkType == self::TYPE_USER || $_linkType == self::TYPE_USERORGANIZATION ||
            $_linkType == self::TYPE_CHAT || $_linkType == self::TYPE_CHATMESSAGE || $_linkType == self::TYPE_REPORT) {
            return true;
        }

        return false;
    }

    /**
     * Create a new Tag Link
     *
     * @author Varun Shoor
     * @param int $_tagID The Tag ID
     * @param mixed $_linkType The Link Type
     * @param int $_linkID The Link ID
     * @param int $_staffID (OPTIONAL) The Staff ID
     * @return mixed "_tagLinkID" (INT) on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function Create($_tagID, $_linkType, $_linkID, $_staffID = 0)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_tagID) || !self::IsValidType($_linkType) || empty($_linkID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'taglinks', array('staffid' => $_staffID, 'dateline' => DATENOW, 'tagid' => $_tagID, 'linktype' => (int)($_linkType), 'linkid' => $_linkID), 'INSERT');
        $_tagLinkID = $_SWIFT->Database->Insert_ID();

        if (!$_tagLinkID) {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        self::$_tagCache = array();

        return $_tagLinkID;
    }

    /**
     * Delete the Tag Link record
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

        self::DeleteList(array($this->GetTagLinkID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of Tag Link ID's
     *
     * @author Varun Shoor
     * @param array $_tagLinkIDList The Tag Link ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_tagLinkIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_tagLinkIDList)) {
            return false;
        }

        $_finalTagLinkIDList = array();

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "taglinks WHERE taglinkid IN (" . BuildIN($_tagLinkIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_finalTagLinkIDList[] = $_SWIFT->Database->Record['taglinkid'];
        }

        if (!count($_finalTagLinkIDList)) {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "taglinks WHERE taglinkid IN (" . BuildIN($_tagLinkIDList) . ")");

        self::$_tagCache = array();

        return true;
    }

    /**
     * Delete on the given Tag ID List
     *
     * @author Varun Shoor
     * @param array $_tagIDList The Tag ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteOnTag($_tagIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_tagIDList)) {
            return false;
        }

        $_tagLinkIDList = array();

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "taglinks WHERE tagid IN (" . BuildIN($_tagIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_tagLinkIDList[] = $_SWIFT->Database->Record['taglinkid'];
        }

        if (!count($_tagLinkIDList)) {
            return false;
        }

        self::DeleteList($_tagLinkIDList);

        return false;
    }

    /**
     * Delete on a list of link id's
     *
     * @author Varun Shoor
     * @param array $_tagIDList The Tag ID List
     * @param mixed $_linkType The Link Type
     * @param array $_linkIDList The Link ID List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Tag_Exception If Invalid Data is Provided
     */
    public static function DeleteOnTagAndLinkList($_tagIDList, $_linkType, $_linkIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!self::IsValidType($_linkType)) {
            throw new SWIFT_Tag_Exception(SWIFT_INVALIDDATA);
        } else if (!_is_array($_linkIDList) || !_is_array($_tagIDList)) {
            return false;
        }

        $_tagLinkIDList = array();

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "taglinks WHERE tagid IN (" . BuildIN($_tagIDList) . ") AND linktype = '" . (int)($_linkType) . "' AND linkid IN (" . BuildIN($_linkIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_tagLinkIDList[] = $_SWIFT->Database->Record['taglinkid'];
        }

        if (!count($_tagLinkIDList)) {
            return false;
        }

        self::DeleteList($_tagLinkIDList);

        return false;
    }

    /**
     * Delete on a list of link id's
     *
     * @author Varun Shoor
     * @param mixed $_linkType The Link Type
     * @param array $_linkIDList The Link ID List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Tag_Exception If Invalid Data is Provided
     */
    public static function DeleteOnLinkList($_linkType, $_linkIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!self::IsValidType($_linkType)) {
            throw new SWIFT_Tag_Exception(SWIFT_INVALIDDATA);
        } else if (!_is_array($_linkIDList)) {
            return false;
        }

        $_tagLinkIDList = array();

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "taglinks WHERE linktype = '" . (int)($_linkType) . "' AND linkid IN (" . BuildIN($_linkIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_tagLinkIDList[] = $_SWIFT->Database->Record['taglinkid'];
        }

        if (!count($_tagLinkIDList)) {
            return false;
        }

        self::DeleteList($_tagLinkIDList);

        return false;
    }

    /**
     * Retrieve the Tag ID List
     *
     * @author Varun Shoor
     * @param mixed $_linkType The Link Type
     * @param int $_linkID The Link ID
     * @return mixed "_tagIDList" (ARRAY) on Success, "false" otherwise
     */
    public static function RetrieveTagIDList($_linkType, $_linkID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!SWIFT_TagLink::IsValidType($_linkType) || empty($_linkID)) {
            throw new SWIFT_Tag_Exception(SWIFT_INVALIDDATA);
        }

        $_cacheKey = $_linkType . '-' . $_linkID;
        if (isset(self::$_tagCache[$_cacheKey])) {
            return self::$_tagCache[$_cacheKey];
        }

        $_tagIDList = array();

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "taglinks WHERE linktype = '" . (int)($_linkType) . "' AND linkid = '" . $_linkID . "'");
        while ($_SWIFT->Database->NextRecord()) {
            if (!in_array($_SWIFT->Database->Record['tagid'], $_tagIDList)) {
                $_tagIDList[] = $_SWIFT->Database->Record['tagid'];
            }
        }

        self::$_tagCache[$_cacheKey] = $_tagIDList;

        return $_tagIDList;
    }

    /**
     * Retrieve the relevant link id list which contains all the specified tags
     *
     * @author Varun Shoor
     * @param mixed $_linkType The Link Type
     * @param array $_tagIDList The Tag ID List
     * @return array
     * @throws SWIFT_Tag_Exception If Invalid Data is Provided
     */
    public static function RetrieveLinkIDListOnTagList($_linkType, $_tagIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!SWIFT_TagLink::IsValidType($_linkType)) {
            throw new SWIFT_Tag_Exception(SWIFT_INVALIDDATA);
        } else if (!_is_array($_tagIDList)) {
            return array();
        }

        $_tagLinkContainer = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "taglinks WHERE tagid IN (" . BuildIN($_tagIDList) . ") and linktype = '" . (int)($_linkType) . "'");
        while ($_SWIFT->Database->NextRecord()) {
            $_tagLinkContainer[$_SWIFT->Database->Record['linkid']][] = $_SWIFT->Database->Record['tagid'];
        }

        $_finalLinkIDList = array();
        foreach ($_tagLinkContainer as $_key => $_val) {
            // Contains all tags?
            if (!count(array_diff($_tagIDList, $_val))) {
                $_finalLinkIDList[] = $_key;
            }
        }

        return $_finalLinkIDList;
    }

    /**
     * Retrieve the cloud container array for the link type
     *
     * @author Varun Shoor
     * @param mixed $_linkType The Link Type
     * @return mixed "_finalTagCloudContainer" (ARRAY) on Success, "false" otherwise
     */
    public static function RetrieveCloudContainer($_linkType)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!SWIFT_TagLink::IsValidType($_linkType)) {
            throw new SWIFT_Tag_Exception(SWIFT_INVALIDDATA);
        }

        $_tagLinkContainer = array();

        $_tagIDList = array();
        $_SWIFT->Database->QueryLimit("SELECT COUNT(*) AS totalitems, tagid FROM " . TABLE_PREFIX . "taglinks WHERE linktype = '" . (int)($_linkType) . "' GROUP BY tagid ORDER BY totalitems DESC", $_SWIFT->Settings->Get('g_maxtagcloud'));
        while ($_SWIFT->Database->NextRecord()) {
            $_tagLinkContainer[$_SWIFT->Database->Record['tagid']] = $_SWIFT->Database->Record['totalitems'];

            $_tagIDList[] = $_SWIFT->Database->Record['tagid'];
        }

        $_tagContainer = SWIFT_Tag::GetTagListOnTagID($_tagIDList);
        $_finalTagCloudContainer = array();
        foreach ($_tagContainer as $_key => $_val) {
            if (isset($_tagLinkContainer[$_key])) {
                $_finalTagCloudContainer[$_val] = $_tagLinkContainer[$_key];
            }
        }

        return $_finalTagCloudContainer;
    }
}

?>
