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

namespace Base\Models\Tag;

use SWIFT;
use SWIFT_Model;
use Base\Library\Tag\SWIFT_Tag_Exception;

/**
 * The Tag Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_Tag extends SWIFT_Model
{
    const TABLE_NAME = 'tags';
    const PRIMARY_KEY = 'tagid';

    const TABLE_STRUCTURE = "tagid I PRIMARY AUTO NOTNULL,
                                staffid I DEFAULT '0' NOTNULL,
                                dateline I DEFAULT '0' NOTNULL,
                                tagname C(100) DEFAULT '' NOTNULL,
                                linkcount I DEFAULT '0' NOTNULL";

    const INDEX_1 = 'tagname';


    protected $_dataStore = array();

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param int $_tagID The Tag ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Tag_Exception If the Record could not be loaded
     */
    public function __construct($_tagID)
    {
        parent::__construct();

        if (!$this->LoadData($_tagID)) {
            throw new SWIFT_Tag_Exception('Failed to load Tag ID: ' . $_tagID);
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
     * @throws SWIFT_Tag_Exception If the Class is not Loaded
     */
    public function ProcessUpdatePool()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        } else if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'tags', $this->GetUpdatePool(), 'UPDATE', "tagid = '" . (int)($this->GetTagID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Tag ID
     *
     * @author Varun Shoor
     * @return mixed "tagid" on Success, "false" otherwise
     * @throws SWIFT_Tag_Exception If the Class is not Loaded
     */
    public function GetTagID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Tag_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['tagid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param int $_tagID The Tag ID
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_tagID)
    {
        $_dataStore = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "tags WHERE tagid = '" . $_tagID . "'");
        if (isset($_dataStore['tagid']) && !empty($_dataStore['tagid'])) {
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
     * @throws SWIFT_Tag_Exception If the Class is not Loaded
     */
    public function GetDataStore()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Tag_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore;
    }

    /**
     * Retrieves a Property Value from Data Store
     *
     * @author Varun Shoor
     * @param string $_key The Key Identifier
     * @return mixed Property Data on Success, "false" otherwise
     * @throws SWIFT_Tag_Exception If the Class is not Loaded
     */
    public function GetProperty($_key)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Tag_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_Tag_Exception(SWIFT_INVALIDDATA);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Create a new Tag
     *
     * @author Varun Shoor
     * @param string $_tagName The Tag Name
     * @param int $_staffID (OPTIONAL) The Staff ID who created this tag
     * @return mixed "_tagID" (INT) on Success, "false" otherwise
     * @throws SWIFT_Tag_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function Create($_tagName, $_staffID = 0)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_tagName)) {
            throw new SWIFT_Tag_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'tags', array('staffid' => $_staffID, 'dateline' => DATENOW, 'tagname' => CleanTag($_tagName), 'linkcount' => 0), 'INSERT');
        $_tagID = $_SWIFT->Database->Insert_ID();

        if (!$_tagID) {
            throw new SWIFT_Tag_Exception(SWIFT_CREATEFAILED);
        }

        return $_tagID;
    }

    /**
     * Update the Tag Record
     *
     * @author Varun Shoor
     * @param string $_tagName The Tag Name
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Tag_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function Update($_tagName)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Tag_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_tagName)) {
            throw new SWIFT_Tag_Exception(SWIFT_INVALIDDATA);
        }

        $this->UpdatePool('tagname', CleanTag($_tagName));

        $this->ProcessUpdatePool();

        return true;
    }

    /**
     * Delete the Tag record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Tag_Exception If the Class is not Loaded
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Tag_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($this->GetTagID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of Tags
     *
     * @author Varun Shoor
     * @param array $_tagIDList The Tag ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_tagIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_tagIDList)) {
            return false;
        }

        $_finalTagIDList = array();

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "tags WHERE tagid IN (" . BuildIN($_tagIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_finalTagIDList[] = $_SWIFT->Database->Record['tagid'];
        }

        if (!count($_finalTagIDList)) {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "tags WHERE tagid IN (" . BuildIN($_tagIDList) . ")");

        SWIFT_TagLink::DeleteOnTag($_tagIDList);

        return true;
    }

    /**
     * Get the Tag ID List
     *
     * @author Varun Shoor
     * @param array $_tagList The Tag List
     * @return array "_tagIDContainer" (ARRAY) on Success, array() otherwise
     */
    public static function GetTagIDList($_tagList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_tagList)) {
            return array();
        }

        $_tagIDContainer = array();

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "tags WHERE tagname IN (" . BuildIN($_tagList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_tagIDContainer[$_SWIFT->Database->Record['tagname']] = $_SWIFT->Database->Record['tagid'];
        }

        return $_tagIDContainer;
    }

    /**
     * Retrieve the tag id on tag name
     *
     * @author Varun Shoor
     * @param string $_tagName The Tag Name
     * @return mixed "tagid" (INT) on Success, "false" otherwise
     */
    public static function GetTagIDOnName($_tagName)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_tagContainer = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "tags WHERE tagname = '" . $_SWIFT->Database->Escape($_tagName) . "'");

        if (isset($_tagContainer['tagid']) && !empty($_tagContainer['tagid'])) {
            return $_tagContainer['tagid'];
        }

        return false;
    }

    /**
     * Process the tags in form post and link accordingly
     *
     * @author Varun Shoor
     * @param mixed $_linkType The Tag Link Type
     * @param int $_linkID The Link Type ID
     * @param array $_tagList The Tag List
     * @param int $_staffID (OPTIONAL) The Staff ID who created this tag
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Tag_Exception If Invalid Data is Provided
     */
    public static function Process($_linkType, $_linkID, $_tagList, $_staffID = 0)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!SWIFT_TagLink::IsValidType($_linkType)) {
            throw new SWIFT_Tag_Exception(SWIFT_INVALIDDATA);
        }

        // If no tags assigned.. make sure we delink any existing tags assigned to this item.. user probably deleted the tags?
        if (!_is_array($_tagList)) {
            $_tagList = self::GetTagList($_linkType, $_linkID);

            SWIFT_TagLink::DeleteOnLinkList($_linkType, array($_linkID));

            SWIFT_Tag::Cleanup($_tagList);

            return true;
        }

        // Convert all to lowercase
        foreach ($_tagList as $_key => $_val) {
            $_tagList[$_key] = mb_strtolower($_val);
        }

        // Retrieve the TAG ID's
        $_tagIDContainer = self::GetTagIDList($_tagList);

        // Check for tags that are supposed to be removed.. to do that.. first fetch current list
        $_currentTagIDList = SWIFT_TagLink::RetrieveTagIDList($_linkType, $_linkID);
        $_unlinkTagIDList = $_unlinkTagsList = array();
        if (_is_array($_currentTagIDList)) {
            foreach ($_currentTagIDList as $_key => $_val) {
                if (!in_array($_val, $_tagIDContainer) && !in_array($_val, $_unlinkTagIDList)) {
                    $_unlinkTagIDList[] = $_val;
                }
            }

            if (count($_unlinkTagIDList)) {
                SWIFT_TagLink::DeleteOnTagAndLinkList($_unlinkTagIDList, $_linkType, array($_linkID));

                $_unlinkTagsList = self::GetTagListOnTagID($_unlinkTagIDList);
            }
        }

        $_creationTagList = array();
        foreach ($_tagList as $_key => $_val) {
            if (trim($_val) == '') {
                continue;
            }

            if (!isset($_tagIDContainer[$_val])) {
                $_creationTagList[] = $_val;

                $_tagID = self::Create($_val, $_staffID);

                $_tagIDContainer[$_val] = $_tagID;
            } else {
                $_tagID = (int)($_tagIDContainer[$_val]);
            }

            // Now that we have a proper tag id.. we need to link em up
            if (!in_array($_tagID, $_currentTagIDList)) {
                SWIFT_TagLink::Create($_tagID, $_linkType, $_linkID, $_staffID);
            }
        }

        $_tagList = array_merge($_tagList, $_unlinkTagsList);

        SWIFT_Tag::Cleanup($_tagList);

        return true;
    }

    /**
     * Add the tags in form post and link accordingly
     *
     * @author Varun Shoor
     * @param mixed $_linkType The Tag Link Type
     * @param int $_linkID The Link Type ID
     * @param array $_tagList The Tag List
     * @param int|mixed $_staffID (OPTIONAL) The Staff ID who created this tag
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Tag_Exception If Invalid Data is Provided
     */
    public static function AddTags($_linkType, $_linkID, $_tagList, $_staffID = 0)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_tagList)) {
            return false;
        }

        if (!SWIFT_TagLink::IsValidType($_linkType)) {
            throw new SWIFT_Tag_Exception(SWIFT_INVALIDDATA);
        }

        // Convert all to lowercase
        foreach ($_tagList as $_key => $_val) {
            $_tagList[$_key] = mb_strtolower($_val);
        }

        // Retrieve the TAG ID's
        $_tagIDContainer = self::GetTagIDList($_tagList);

        // Check for tags that are supposed to be removed.. to do that.. first fetch current list
        $_currentTagIDList = SWIFT_TagLink::RetrieveTagIDList($_linkType, $_linkID);

        $_creationTagList = array();
        foreach ($_tagList as $_key => $_val) {
            if (!isset($_tagIDContainer[$_val])) {
                $_creationTagList[] = $_val;

                $_tagID = self::Create($_val, $_staffID);

                $_tagIDContainer[$_val] = $_tagID;
            } else {
                $_tagID = (int)($_tagIDContainer[$_val]);
            }

            // Now that we have a proper tag id.. we need to link em up
            if (!in_array($_tagID, $_currentTagIDList)) {
                SWIFT_TagLink::Create($_tagID, $_linkType, $_linkID, $_staffID);
            }
        }

        return true;
    }

    /**
     * Remove the tags in form post and delink accordingly
     *
     * @author Varun Shoor
     * @param mixed $_linkType The Tag Link Type
     * @param array $_linkIDList The Link Type ID List
     * @param array $_tagList The Tag List
     * @param int|mixed $_staffID (OPTIONAL) The Staff ID who created this tag
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Tag_Exception If Invalid Data is Provided
     */
    public static function RemoveTags($_linkType, $_linkIDList, $_tagList, $_staffID = 0)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_tagList)) {
            return false;
        }

        if (!SWIFT_TagLink::IsValidType($_linkType)) {
            throw new SWIFT_Tag_Exception(SWIFT_INVALIDDATA);
        }

        // Convert all to lowercase
        foreach ($_tagList as $_key => $_val) {
            $_tagList[$_key] = mb_strtolower($_val);
        }

        // Retrieve the TAG ID's
        $_tagIDContainer = self::GetTagIDList($_tagList);

        $_finalTagIDList = array();
        foreach ($_tagList as $_key => $_val) {
            if (isset($_tagIDContainer[$_val])) {
                $_tagID = (int)($_tagIDContainer[$_val]);

                $_finalTagIDList[] = $_tagID;
            }
        }

        if (count($_finalTagIDList)) {
            SWIFT_TagLink::DeleteOnTagAndLinkList($_finalTagIDList, $_linkType, $_linkIDList);
        }

        SWIFT_Tag::Cleanup($_tagList);

        return true;
    }

    /**
     * Retrieve the Tag List
     *
     * @author Varun Shoor
     * @param mixed $_linkType The Tag Link Type
     * @param int $_linkID The Link Type ID
     * @return mixed "_tagList" (ARRAY) on Success, "false" otherwise
     */
    public static function GetTagList($_linkType, $_linkID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!SWIFT_TagLink::IsValidType($_linkType)) {
            throw new SWIFT_Tag_Exception(SWIFT_INVALIDDATA);
        }

        $_tagIDList = SWIFT_TagLink::RetrieveTagIDList($_linkType, $_linkID);
        if (!_is_array($_tagIDList)) {
            return array();
        }

        $_tagList = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "tags WHERE tagid IN (" . BuildIN($_tagIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_tagList[] = mb_strtolower($_SWIFT->Database->Record['tagname']);
        }

        return $_tagList;
    }

    /**
     * Retrieve a list of tags on the Tag ID
     *
     * @author Varun Shoor
     * @param array $_tagIDList The Tag ID List
     * @return mixed "_tagContainer" (ARRAY) on Success, "false" otherwise
     */
    public static function GetTagListOnTagID($_tagIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_tagIDList)) {
            return array();
        }

        $_tagContainer = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "tags WHERE tagid IN (" . BuildIN($_tagIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_tagContainer[$_SWIFT->Database->Record['tagid']] = $_SWIFT->Database->Record['tagname'];
        }

        return $_tagContainer;
    }

    /**
     * Cleans up the tags that are not used anymore
     *
     * @author Varun Shoor
     * @param array $_tagList
     * @return bool "true" on Success, "false" otherwise
     */
    public static function Cleanup($_tagList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_tagList)) {
            return false;
        }

        foreach ($_tagList as $_index => $_tagName) {
            $_tagList[$_index] = trim(mb_strtolower($_tagName));
        }

        $_tagIDContainer = self::GetTagIDList($_tagList);

        $_tagCountContainer = array();

        $_SWIFT->Database->Query("SELECT COUNT(*) AS totalitems, tagid FROM " . TABLE_PREFIX . "taglinks
            WHERE tagid IN (" . BuildIN($_tagIDContainer) . ")
            GROUP BY tagid");
        while ($_SWIFT->Database->NextRecord()) {
            $_tagCountContainer[$_SWIFT->Database->Record['tagid']] = $_SWIFT->Database->Record['totalitems'];
        }

        $_deleteTagIDList = array();

        foreach ($_tagList as $_tagName) {
            // No record found for the tag? continue..
            if (!isset($_tagIDContainer[$_tagName])) {
                continue;
            }

            $_tagID = $_tagIDContainer[$_tagName];

            if (!isset($_tagCountContainer[$_tagID])
                || (isset($_tagCountContainer[$_tagID]) && $_tagCountContainer[$_tagID] == 0)) {
                $_deleteTagIDList[] = $_tagID;
            }
        }

        if (!count($_deleteTagIDList)) {
            return false;
        }

        SWIFT_Tag::DeleteList($_deleteTagIDList);

        return true;
    }
}

?>
