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

namespace LiveChat\Models\Chat;

use SWIFT;
use SWIFT_Data;
use SWIFT_DataID;
use SWIFT_DataStore;
use SWIFT_Exception;
use SWIFT_Model;
use Tickets\Library\Ticket\SWIFT_Ticket_Exception;

/**
 * The Chat Text Data Manager
 *
 * @author Varun Shoor
 */
class SWIFT_ChatTextData extends SWIFT_Model
{
    const TABLE_NAME = 'chattextdata';
    const PRIMARY_KEY = 'chattextdataid';

    const TABLE_STRUCTURE = "chattextdataid I PRIMARY AUTO NOTNULL,
                                chatobjectid I DEFAULT '0' NOTNULL,
                                contents X2";

    const INDEX_1 = 'chatobjectid';


    protected $_dataStore = array();

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
            throw new SWIFT_Exception('Failed to load Chat Text Data Object');
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
        } else if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'chattextdata', $this->GetUpdatePool(), 'UPDATE', "chattextdataid = '" . (int)($this->GetChatTextDataID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Chat Text Data ID
     *
     * @author Varun Shoor
     * @return mixed "chattextdataid" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetChatTextDataID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        return $this->_dataStore['chattextdataid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param SWIFT_Data $_SWIFT_DataObject The SWIFT_Data Object
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If Invalid Data is Provided
     */
    protected function LoadData($_SWIFT_DataObject)
    {
        $_SWIFT = SWIFT::GetInstance();

        // Is it a ID?
        if ($_SWIFT_DataObject instanceof SWIFT_DataID && $_SWIFT_DataObject->GetIsClassLoaded()) {
            $_dataStore = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "chattextdata WHERE chattextdataid = '" . (int)($_SWIFT_DataObject->GetDataID()) . "'");
            if (isset($_dataStore['chattextdataid']) && !empty($_dataStore['chattextdataid'])) {
                $this->_dataStore = $_dataStore;

                return true;
            }

            // Is it a Store?
        } else if ($_SWIFT_DataObject instanceof SWIFT_DataStore && $_SWIFT_DataObject->GetIsClassLoaded()) {
            $this->_dataStore = $_SWIFT_DataObject->GetDataStore();

            if (!isset($this->_dataStore['chattextdataid']) || empty($this->_dataStore['chattextdataid'])) {
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            }

            return true;
        }

        throw new SWIFT_Exception(SWIFT_INVALIDDATA);

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
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetProperty($_key)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Create a new Chat Text Data Record
     *
     * @author Varun Shoor
     * @param int $_chatObjectID
     * @param string $_contents
     * @return int Chat Text Data ID
     * @throws SWIFT_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function Create($_chatObjectID, $_contents)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_chatObjectID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'chattextdata', array('chatobjectid' => $_chatObjectID, 'contents' => $_contents), 'INSERT');
        $_chatTextDataID = $_SWIFT->Database->Insert_ID();

        if (!$_chatTextDataID) {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        return $_chatTextDataID;
    }

    /**
     * Delete the Chat Text Data record
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

        self::DeleteList(array($this->GetChatTextDataID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of Chat Text Datas
     *
     * @author Varun Shoor
     * @param array $_chatTextDataIDList
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_chatTextDataIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_chatTextDataIDList)) {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "chattextdata WHERE chattextdataid IN (" . BuildIN($_chatTextDataIDList) . ")");

        return true;
    }

    /**
     * Delete on a list of chat objects
     *
     * @author Varun Shoor
     * @param array $_chatObjectIDList The Chat Object ID List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function DeleteOnChat($_chatObjectIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_chatObjectIDList)) {
            return false;
        }

        $_chatTextDataIDList = array();
        $_SWIFT->Database->Query("SELECT chattextdataid FROM " . TABLE_PREFIX . "chattextdata WHERE chatobjectid IN (" . BuildIN($_chatObjectIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_chatTextDataIDList[] = $_SWIFT->Database->Record['chattextdataid'];
        }

        self::DeleteList($_chatTextDataIDList);

        return true;
    }
}

