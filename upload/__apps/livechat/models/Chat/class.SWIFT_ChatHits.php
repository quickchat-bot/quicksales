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

use LiveChat\Library\Chat\SWIFT_Chat_Exception;
use LiveChat\Models\Chat\SWIFT_Chat;
use SWIFT;
use SWIFT_Exception;
use SWIFT_Model;

/**
 * The Chat Hits Record Manager
 *
 * @author Varun Shoor
 */
class SWIFT_ChatHits extends SWIFT_Model
{
    const TABLE_NAME = 'chathits';
    const PRIMARY_KEY = 'chathitid';

    const TABLE_STRUCTURE = "chathitid I PRIMARY AUTO NOTNULL,
                                staffid I DEFAULT '0' NOTNULL,
                                chatobjectid I DEFAULT '0' NOTNULL,
                                dateline I DEFAULT '0' NOTNULL,
                                fullname C(255) DEFAULT '' NOTNULL,
                                email C(255) DEFAULT '' NOTNULL,
                                isaccepted I2 DEFAULT '0' NOTNULL";

    const INDEX_1 = 'chatobjectid, staffid';


    private $_chatHitData = array();

    // Constants
    const CHATHIT_NOTPICKED = 1;
    const CHATHIT_ACCEPTED = 2;
    const CHATHIT_REFUSED = 3;
    const CHATHIT_ENDED = 4;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param SWIFT_Chat $_SWIFT_ChatObject The SWIFT_Chat Object Pointer
     * @param int $_staffID The Staff ID associated with this chat hit
     * @throws \LiveChat\Models\Chat\SWIFT_Chat_Exception If the Class is not Loaded
     */
    public function __construct(SWIFT_Chat $_SWIFT_ChatObject, $_staffID)
    {
        parent::__construct();

        if (!$this->LoadData($_SWIFT_ChatObject, $_staffID)) {
            throw new SWIFT_Chat_Exception(SWIFT_CLASSNOTLOADED);
        }
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

        $this->Database->AutoExecute(TABLE_PREFIX . 'chathits', $this->GetUpdatePool(), 'UPDATE', "chathitid = '" . ($this->GetChatHitID()) . "'");

        return true;
    }

    /**
     * Loads the Chat Hit Data into array
     *
     * @author Varun Shoor
     * @param SWIFT_Chat|\SWIFT_Data $_SWIFT_ChatObject The SWIFT_Chat Object Pointer
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Chat_Exception
     */
    public function LoadData($_SWIFT_ChatObject)
    {
        $_staffID = func_get_arg(1);
        if (!$_SWIFT_ChatObject instanceof SWIFT_Chat || !$_SWIFT_ChatObject->GetIsClassLoaded() || empty($_staffID)) {
            throw new SWIFT_Chat_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_chatHitData = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "chathits WHERE chatobjectid = '" . ($_SWIFT_ChatObject->GetChatObjectID()) . "' AND staffid = '" . $_staffID . "'");
        if (!isset($_chatHitData['chathitid']) || empty($_chatHitData['chathitid'])) {
            return false;
        }

        $this->_chatHitData = $_chatHitData;

        return true;
    }

    /**
     * Retrieves the Chat Hit ID
     *
     * @author Varun Shoor
     * @return int "chathitid" on Success, "false" otherwise
     * @throws \LiveChat\Models\Chat\SWIFT_Chat_Exception If the Class is not Loaded
     */
    public function GetChatHitID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Chat_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_chatHitData['chathitid'];
    }

    /**
     * Sets the Is Accepted flag
     *
     * @author Varun Shoor
     * @param int $_isAccepted One of the basic class constnats (notpicked, accepted, refused, ended)
     * @return bool "true" on Success, "false" otherwise
     * @throws \LiveChat\Models\Chat\SWIFT_Chat_Exception If the Class is not Loaded
     */
    public function SetIsAccepted($_isAccepted)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Chat_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UpdatePool('isaccepted', ($_isAccepted));

        $this->ProcessUpdatePool();

        return true;
    }

    /**
     * Retrieves the Is Accepted flag
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws \LiveChat\Models\Chat\SWIFT_Chat_Exception If the Class is not Loaded
     */
    public function GetIsAccepted()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Chat_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_chatHitData['isaccepted'];
    }

    /**
     * Inserts a new Chat Hit record. This is used to monitor whether a staff accepted a chat request or not.
     *
     * @author Varun Shoor
     * @param SWIFT_Chat $_SWIFT_ChatObject The Live Chat Object
     * @param int $_staffID The Staff ID
     * @param string $_fullName The Fullname for entry
     * @param string $_email The Email Address
     * @param bool $_isAccepted Is Chat Accepted
     * @return int
     * @throws SWIFT_Exception
     */
    public static function Insert(SWIFT_Chat $_SWIFT_ChatObject, $_staffID, $_fullName, $_email, $_isAccepted = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_SWIFT_ChatObject instanceof SWIFT_Chat || !$_SWIFT_ChatObject->GetIsClassLoaded()) {
            throw new SWIFT_Chat_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'chathits', array('staffid' => $_staffID, 'chatobjectid' => $_SWIFT_ChatObject->GetChatObjectID(), 'dateline' => DATENOW,
            'fullname' => ReturnNone($_fullName), 'email' => ReturnNone($_email), 'isaccepted' => (int)($_isAccepted)), 'INSERT');
        return $_SWIFT->Database->Insert_ID();
    }

    /**
     * Mark the chat hit as accepted
     *
     * @author Varun Shoor
     * @param SWIFT_Chat $_SWIFT_ChatObject
     * @param int $_staffID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function MarkAsAccepted(SWIFT_Chat $_SWIFT_ChatObject, $_staffID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_staffID) || !$_SWIFT_ChatObject instanceof SWIFT_Chat || !$_SWIFT_ChatObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'chathits', array('isaccepted' => '1'), 'UPDATE', "chatobjectid = '" . ($_SWIFT_ChatObject->GetChatObjectID()) . "' AND staffid = '" . ($_staffID) . "'");

        return true;
    }
}

