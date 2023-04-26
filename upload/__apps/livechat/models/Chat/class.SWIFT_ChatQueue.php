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
 * @license        http://www.opencart.com.vn/license
 * @link        http://www.opencart.com.vn
 *
 * ###############################################
 */

namespace LiveChat\Models\Chat;

use Base\Models\Staff\SWIFT_Staff;
use LiveChat\Library\Chat\SWIFT_Chat_Exception;
use LiveChat\Models\Chat\SWIFT_Chat;
use SWIFT;
use SWIFT_Exception;
use SWIFT_Model;

/**
 * The Chat Queue Management Class. Handles dispatch and retrieval of queue related messages and actions
 *
 * @author Varun Shoor
 */
class SWIFT_ChatQueue extends SWIFT_Model
{
    private $_SWIFT_ChatObject;

    // Message Types
    const MESSAGE_CLIENT = 1;
    const MESSAGE_STAFF = 2;
    const MESSAGE_SYSTEM = 3;

    // Submit Types
    const SUBMIT_STAFF = 1;
    const SUBMIT_CLIENT = 2;
    const SUBMIT_SYSTEM = 3;

    // Chat Actions
    const CHATACTION_SYSTEM = 'systemmsg';
    const CHATACTION_SYSTEMMESSAGE = 'systemmsg';
    const CHATACTION_MESSAGE = 'message';
    const CHATACTION_TEXT = 'text';
    const CHATACTION_URL = 'url';
    const CHATACTION_IMAGE = 'image';
    const CHATACTION_CODE = 'code';
    const CHATACTION_CHATLEAVE = 'leave';
    const CHATACTION_CHATENTER = 'enter';
    const CHATACTION_CHATJOIN = 'join';
    const CHATACTION_CHATREFUSE = 'refuse';
    const CHATACTION_TYPING = 'typing';
    const CHATACTION_CALL = 'call';
    const CHATACTION_UPLOADEDIMAGE = 'uploadedimage';
    const CHATACTION_FILE = 'file';
    const CHATACTION_OBSERVE = 'observe';

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param SWIFT_Chat $_SWIFT_ChatObject The Live Chat Object Pointer
     * @throws \LiveChat\Models\Chat\SWIFT_Chat_Exception If the Class is not Loaded
     */
    public function __construct(SWIFT_Chat $_SWIFT_ChatObject)
    {
        if (!$_SWIFT_ChatObject->GetIsClassLoaded()) {
            throw new SWIFT_Chat_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_SWIFT_ChatObject = $_SWIFT_ChatObject;

        parent::__construct();
    }

    /**
     * Generate a Unique GUID
     *
     * @author Varun Shoor
     * @return string Unique GUID on Success, "false" otherwise
     */
    public static function GenerateGUID()
    {
        return strtoupper(substr(BuildHash(), 0, 8) . '-' . substr(BuildHash(), 0, 4) . '-' . substr(BuildHash(), 0, 4) . '-' . substr(BuildHash(), 0, 4) . '-' . substr(BuildHash(), 0, 12));
    }

    /**
     * Checks to see if Message Type is Valid
     *
     * @author Varun Shoor
     * @param int $_msgType The Message Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidMessageType($_msgType)
    {
        if ($_msgType != self::MESSAGE_CLIENT && $_msgType != self::MESSAGE_STAFF) {
            return false;
        }

        return true;
    }

    /**
     * Checks to see if chat object is valid
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws \LiveChat\Models\Chat\SWIFT_Chat_Exception If the Class is not Loaded or If Invalid Data Provided
     */
    public function IsValidChatObject()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Chat_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (!$this->_SWIFT_ChatObject instanceof SWIFT_Chat || !$this->_SWIFT_ChatObject->GetIsClassLoaded()) {
            throw new SWIFT_Chat_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        return true;
    }

    /**
     * Add a Message to Chat Queue
     *
     * @author Varun Shoor
     * @param int $_msgType The Message Type (STAFF/CLIENT)
     * @param int $_submitType The Submitter Type (STAFF/CLIENT)
     * @param string $_message The Actual Message
     * @param bool $_doBase64 Whether the message is base64 encoded or not
     * @return bool "true" on Success, "false" otherwise
     * @throws \LiveChat\Models\Chat\SWIFT_Chat_Exception If the Class is not Loaded
     */
    public function AddMessageToQueue($_msgType, $_submitType, $_message, $_doBase64 = false, $_msgTimestamp = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded() || !$this->IsValidChatObject()) {
            throw new SWIFT_Chat_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_finalTimestamp = DATENOW;
        if (!empty($_msgTimestamp)) {
            $_finalTimestamp = $_msgTimestamp;
        }

        $_msgArray = array('type' => self::CHATACTION_MESSAGE, 'contents' => $_message, "base64" => $_doBase64, 'timestamp' => $_finalTimestamp);

        if ($_msgType == self::MESSAGE_STAFF) {
            $_fullName = $_SWIFT->Staff->GetProperty('fullname');
        } else {
            $_fullName = $this->_SWIFT_ChatObject->GetProperty('userfullname');
        }

        $_chatChildList = $this->_SWIFT_ChatObject->GetChatChildList();
        if (_is_array($_chatChildList)) {
            foreach ($_chatChildList as $_key => $_val) {
                if ($_msgType == self::MESSAGE_STAFF && $_val['staffid'] == $_SWIFT->Staff->GetStaffID()) {
                    // Seems like this is the same chat child for the staff that is submitting the msg, dont do anything because the msg display for staff will be carried out by the winapp
                } else if (isset($_val['staffid']) && isset($_val['chatchildid']) && !empty($_val['chatchildid']) && !empty($_val['staffid'])) {
                    $this->Insert($_val['chatchildid'], $_val['staffid'], $_fullName, serialize($_msgArray), self::CHATACTION_MESSAGE, self::GenerateGUID(), $_submitType);
                }
            }
        }

        // If the message is from the staff, dispatch it to '0' chatchildid (the global client id)...
        if ($_msgType == self::MESSAGE_STAFF && $this->_SWIFT_ChatObject->GetProperty('chattype') == SWIFT_Chat::CHATTYPE_CLIENT) {
            $this->Insert('0', $_SWIFT->Staff->GetStaffID(), $_fullName, serialize($_msgArray), self::CHATACTION_MESSAGE, self::GenerateGUID(), $_submitType);
        }

        $this->_SWIFT_ChatObject->UpdateLastPostActivity($_submitType);

        $this->_SWIFT_ChatObject->AppendChatData($_msgType, $_submitType, $_fullName, $_message, $_doBase64, self::CHATACTION_MESSAGE);

        return true;
    }

    /**
     * Add a new action to the Chat Queue
     *
     * @author Varun Shoor
     * @param int $_submitType The Submitter Type (CLIENT/STAFF)
     * @param string $_actionType The Action Type
     * @param string $_data The data for the action
     * @param bool $_ignoreCustomerAddition (OPTIONAL) Whether to add the action for the customer orn ot
     * @param bool $_onlyForObservers (OPTIONAL) Whether to add the action only for observers
     * @return bool "true" on Success, "false" otherwise
     * @throws \LiveChat\Models\Chat\SWIFT_Chat_Exception If the Class is not Loaded
     */
    public function AddActionToQueue($_submitType, $_actionType, $_data, $_ignoreCustomerAddition = false, $_onlyForObservers = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded() || !$this->IsValidChatObject()) {
            throw new SWIFT_Chat_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_msgArray = array('type' => $_actionType, 'contents' => $_data);

        $_fullName = '';

        $_chatChildList = $this->_SWIFT_ChatObject->GetChatChildList();
        if (!$_chatChildList) {
            return false;
        }

        $_staffID = 0;
        if (isset($_SWIFT->Staff) && $_SWIFT->Staff instanceof SWIFT_Staff && $_SWIFT->Staff->GetIsClassLoaded()) {
            $_staffID = $_SWIFT->Staff->GetStaffID();

            // Default the full name to that of current staff (if any)
            $_fullName = $_SWIFT->Staff->GetProperty('fullname');
        }

        foreach ($_chatChildList as $_key => $_val) {
            // Ignore LEAVE, TYPING and URL actions for the same staff user, they get sent to everyone else BUT the dispatcher
            if ($_val['staffid'] == $_staffID && ($_actionType == self::CHATACTION_CHATLEAVE || $_actionType == self::CHATACTION_CHATENTER || $_actionType == self::CHATACTION_TYPING ||
                    $_actionType == self::CHATACTION_URL || $_actionType == self::CHATACTION_CODE || $_actionType == self::CHATACTION_IMAGE || $_actionType == self::CHATACTION_UPLOADEDIMAGE)) {
                // Ignored
                continue;

                // Ignore ALL actions for the initiator staff for Staff <> Staff chat IF the initiator of the chat is the same staff we are looking up in list
            } else if ($this->_SWIFT_ChatObject->GetProperty('chattype') == SWIFT_Chat::CHATTYPE_STAFF && $_val['staffid'] == $_staffID && $this->_SWIFT_ChatObject->GetProperty('creatorstaffid') == $_staffID) {
                // Ignored
            } else {
                if ($this->_SWIFT_ChatObject->GetProperty('chattype') == SWIFT_Chat::CHATTYPE_CLIENT && $_submitType == self::SUBMIT_CLIENT) {
                    $_fullName = $this->_SWIFT_ChatObject->GetProperty('userfullname');
                }

                if (!$_onlyForObservers || ($_onlyForObservers == true && $_val['isobserver'] == '1')) {
                    $this->Insert($_val['chatchildid'], $_staffID, $_fullName, serialize($_msgArray), $_actionType, self::GenerateGUID(), $_submitType);
                }
            }
        }

        // Do a separate submit if its a client chat and the submitter was staff, we have to ALWAYS send staff > client packets
        if ($this->_SWIFT_ChatObject->GetProperty('chattype') == SWIFT_Chat::CHATTYPE_CLIENT && $_submitType == self::SUBMIT_STAFF &&
            !$_ignoreCustomerAddition && !$_onlyForObservers) {
            $this->Insert('0', $_staffID, $_fullName, serialize($_msgArray), $_actionType, self::GenerateGUID(), $_submitType);
        }

        $this->_SWIFT_ChatObject->UpdateLastPostActivity($_submitType);

        $this->_SWIFT_ChatObject->AppendChatData(IIF($_submitType == self::SUBMIT_STAFF, self::MESSAGE_STAFF, self::MESSAGE_CLIENT), $_submitType, $_fullName, $_data, false, $_actionType);

        return true;
    }

    /**
     * Add a new action to a specific chat child entry
     *
     * @author Varun Shoor
     * @param string $_chatChildID The Chat Child ID associated with this chat object
     * @param string $_actionType The Action Type
     * @param string $_data The data for the action
     * @return bool "true" on Success, "false" otherwise
     * @throws \LiveChat\Models\Chat\SWIFT_Chat_Exception If the Class is not Loaded
     */
    public function AddChatChildActionToQueue($_chatChildID, $_actionType, $_data)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded() || !$this->IsValidChatObject()) {
            throw new SWIFT_Chat_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_msgArray = array('type' => $_actionType, 'contents' => $_data);

        $_fullName = $_SWIFT->Staff->GetProperty('fullname');

        $this->Insert($_chatChildID, $_SWIFT->Staff->GetStaffID(), $_fullName, serialize($_msgArray), $_actionType, self::GenerateGUID(), self::SUBMIT_STAFF);
    }

    /**
     * Inserts a new "messagequeue" record. This function is supposed to be called by the message and action handlers
     *
     * @author Varun Shoor
     * @param string $_chatChildID The Chat Child ID
     * @param int $_staffID The Staff ID
     * @param string $_name The Name of submitter
     * @param string $_contents The contents of the messaage
     * @param int $_msgType The type of message
     * @param string $_guid The Unique GUID
     * @param int $_submitType The Submit Type
     * @return int
     * @throws \LiveChat\Models\Chat\SWIFT_Chat_Exception If the Class is not Loaded or If Invalid Data Provided
     */
    public function Insert($_chatChildID, $_staffID, $_name, $_contents, $_msgType, $_guid, $_submitType)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Chat_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!self::IsValidMessageType($_submitType)) {
            throw new SWIFT_Chat_Exception(SWIFT_INVALIDDATA);
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'messagequeue', array('chatobjectid' => $this->_SWIFT_ChatObject->GetChatObjectID(), 'chatchildid' => $_chatChildID,
            'staffid' => $_staffID, 'dateline' => DATENOW, 'name' => $_name, 'contents' => $_contents, 'msgtype' => $_msgType,
            'guid' => $_guid, 'submittype' => $_submitType), 'INSERT');
        $_messageQueueID = $this->Database->Insert_ID();

        return $_messageQueueID;
    }

    /**
     * Delete Entries from Message Queue based on a list of GUIDs
     *
     * @author Varun Shoor
     * @param array $_guidList The GUID List Container
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteGUID($_guidList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_guidList)) {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "messagequeue WHERE guid IN (" . BuildIN($_guidList) . ")");

        return true;
    }

    /**
     * Retrieve the Message Queue based on ChatChildID
     *
     * @author Varun Shoor
     * @param int $_chatChildID The Chat Child ID
     * @return array
     * @throws \LiveChat\Models\Chat\SWIFT_Chat_Exception If the Class is not Loaded
     */
    public function GetMessageQueue($_chatChildID = 0)
    {
        if (!$this->GetIsClassLoaded() || !$this->IsValidChatObject()) {
            throw new SWIFT_Chat_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_messageQueueContainer = array();
        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "messagequeue WHERE chatchildid = '" . $_chatChildID . "' AND chatobjectid = '" . $this->_SWIFT_ChatObject->GetChatObjectID() . "' ORDER BY dateline ASC");
        while ($this->Database->NextRecord()) {
            $_messageQueueContainer[$this->Database->Record['messagequeueid']] = $this->Database->Record;
        }

        return $_messageQueueContainer;
    }

    /**
     * Delete Message Queue based on Message Queue ID List
     *
     * @author Varun Shoor
     * @param array $_messageQueueIDList The Message Queue ID List
     * @return bool "true" on Success, "false" otherwise
     * @throws \LiveChat\Models\Chat\SWIFT_Chat_Exception If the Class is not Loaded
     */
    public function DeleteMessageQueue($_messageQueueIDList)
    {
        if (!$this->GetIsClassLoaded() || !$this->IsValidChatObject()) {
            throw new SWIFT_Chat_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!_is_array($_messageQueueIDList)) {
            return false;
        }

        $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "messagequeue WHERE messagequeueid IN (" . BuildIN($_messageQueueIDList) . ")");

        return true;
    }

    /**
     * Cleanup messages older than 30 minutes
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function Cleanup()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_threshold = DATENOW - 1800;

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "messagequeue WHERE dateline < '" . ($_threshold) . "'");

        return true;
    }
}
