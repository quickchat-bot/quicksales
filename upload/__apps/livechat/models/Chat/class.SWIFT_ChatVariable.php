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

namespace LiveChat\Models\Chat;

use LiveChat\Library\Chat\SWIFT_Chat_Exception;
use SWIFT;
use SWIFT_Exception;
use SWIFT_Model;

/**
 * The Chat Variable Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_ChatVariable extends SWIFT_Model
{
    const TABLE_NAME = 'chatvariables';
    const PRIMARY_KEY = 'chatvariableid';

    const TABLE_STRUCTURE = "chatvariableid I PRIMARY AUTO NOTNULL,
                                variabletype I2 DEFAULT '0' NOTNULL,
                                chatobjectid I DEFAULT '0' NOTNULL,
                                variablevalue C(255) DEFAULT '' NOTNULL";

    const INDEX_1 = 'chatobjectid, variabletype';


    protected $_dataStore = array();

    // Core Constants
    const TYPE_ROUNDROBINIGNORE = 1;
    const TYPE_SKILL = 2;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param int $_chatVariableID The Chat Variable ID
     * @throws \LiveChat\Models\Chat\SWIFT_Chat_Exception If the Record could not be loaded
     */
    public function __construct($_chatVariableID)
    {
        parent::__construct();

        if (!$this->LoadData($_chatVariableID)) {
            throw new SWIFT_Chat_Exception('Failed to load Chat Variable ID: ' . $_chatVariableID);
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
     * @throws \LiveChat\Models\Chat\SWIFT_Chat_Exception If the Class is not Loaded
     */
    public function ProcessUpdatePool()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        } else if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'chatvariables', $this->GetUpdatePool(), 'UPDATE', "chatvariableid = '" . (int)($this->GetChatVariableID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Chat Variable ID
     *
     * @author Varun Shoor
     * @return mixed "chatvariableid" on Success, "false" otherwise
     * @throws \LiveChat\Models\Chat\SWIFT_Chat_Exception If the Class is not Loaded
     */
    public function GetChatVariableID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Chat_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        return $this->_dataStore['chatvariableid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param \SWIFT_Data|int $_chatVariableID The Chat Variable ID
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_chatVariableID)
    {
        $_dataStore = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "chatvariables WHERE chatvariableid = '" . $_chatVariableID . "'");
        if (isset($_dataStore['chatvariableid']) && !empty($_dataStore['chatvariableid'])) {
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
     * @throws \LiveChat\Models\Chat\SWIFT_Chat_Exception If the Class is not Loaded
     */
    public function GetDataStore()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Chat_Exception(SWIFT_CLASSNOTLOADED);

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
     * @throws \LiveChat\Models\Chat\SWIFT_Chat_Exception If the Class is not Loaded
     */
    public function GetProperty($_key)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Chat_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_Chat_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Check to see if its a valid variable type
     *
     * @author Varun Shoor
     * @param int $_variableType The Variable Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidVariableType($_variableType)
    {
        if ($_variableType == self::TYPE_ROUNDROBINIGNORE ||
            $_variableType == self::TYPE_SKILL) {
            return true;
        }

        return false;
    }

    /**
     * Create a new Chat Object
     *
     * @author Varun Shoor
     * @param int $_chatObjectID The Chat Object ID
     * @param mixed $_variableType The Variable Type
     * @param string $_variableValue The Variable Value
     * @return int "_chatVariableID" (INT) on Success, "false" otherwise
     * @throws \LiveChat\Models\Chat\SWIFT_Chat_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function Create($_chatObjectID, $_variableType, $_variableValue)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_chatObjectID) || !self::IsValidVariableType($_variableType)) {
            throw new SWIFT_Chat_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'chatvariables', array('chatobjectid' => $_chatObjectID, 'variabletype' => (int)($_variableType), 'variablevalue' => $_variableValue), 'INSERT');
        $_chatVariableID = $_SWIFT->Database->Insert_ID();

        if (!$_chatVariableID) {
            throw new SWIFT_Chat_Exception(SWIFT_CREATEFAILED);
        }

        return $_chatVariableID;
    }

    /**
     * Delete the Chat Variable record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws \LiveChat\Models\Chat\SWIFT_Chat_Exception If the Class is not Loaded
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Chat_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($this->GetChatVariableID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of chat variables
     *
     * @author Varun Shoor
     * @param array $_chatVariableIDList The Chat Variable ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_chatVariableIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_chatVariableIDList)) {
            return false;
        }

        $_finalChatVariableIDList = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "chatvariables WHERE chatvariableid IN (" . BuildIN($_chatVariableIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_finalChatVariableIDList[] = $_SWIFT->Database->Record['chatvariableid'];
        }

        if (!count($_finalChatVariableIDList)) {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "chatvariables WHERE chatvariableid IN (" . BuildIN($_finalChatVariableIDList) . ")");

        return true;
    }

    /**
     * Delete the chat variables based on chat object id's
     *
     * @author Varun Shoor
     * @param array $_chatObjectIDList The Chat Object ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteListOnChat($_chatObjectIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_chatObjectIDList)) {
            return false;
        }

        $_chatVariableIDList = array();
        $_SWIFT->Database->Query("SELECT chatvariableid FROM " . TABLE_PREFIX . "chatvariables WHERE chatobjectid IN (" . BuildIN($_chatObjectIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_chatVariableIDList[] = $_SWIFT->Database->Record['chatvariableid'];
        }

        if (!count($_chatVariableIDList)) {
            return false;
        }

        self::DeleteList($_chatVariableIDList);

        return true;
    }

    /**
     * Delete List on Type
     *
     * @author Varun Shoor
     * @param array $_chatObjectIDList
     * @param array $_variableTypeList
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function DeleteOnType($_chatObjectIDList, $_variableTypeList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_chatObjectIDList)) {
            return false;
        }

        $_chatVariableIDList = array();

        $_SWIFT->Database->Query("SELECT chatvariableid FROM " . TABLE_PREFIX . "chatvariables
            WHERE chatobjectid IN (" . BuildIN($_chatObjectIDList) . ") AND variabletype IN (" . BuildIN($_variableTypeList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_chatVariableIDList[] = $_SWIFT->Database->Record['chatvariableid'];
        }

        if (!count($_chatVariableIDList)) {
            return false;
        }

        self::DeleteList($_chatVariableIDList);

        return true;
    }

    /**
     * Retrieve Variable values based on chat object id and possibly variable type
     *
     * @author Varun Shoor
     * @param int $_chatObjectID The Chat Object ID
     * @param mixed $_variableType (OPTIONAL) The Variable Type
     * @return array "_chatVariableContainer" (ARRAY) on Success, "false" otherwise
     */
    public static function RetrieveVariableValues($_chatObjectID, $_variableType = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_chatObjectID)) {
            throw new SWIFT_Chat_Exception(SWIFT_INVALIDDATA);
        }

        $_extendedSQL = '';

        // Check variable type
        if (!empty($_variableType) && self::IsValidVariableType($_variableType)) {
            $_extendedSQL = " AND variabletype = '" . (int)($_variableType) . "'";
        }

        $_chatVariableContainer = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "chatvariables WHERE chatobjectid = '" . $_chatObjectID . "'" . $_extendedSQL);
        while ($_SWIFT->Database->NextRecord()) {
            $_chatVariableContainer[] = $_SWIFT->Database->Record['variablevalue'];
        }

        return $_chatVariableContainer;
    }
}
