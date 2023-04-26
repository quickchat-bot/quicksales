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

namespace LiveChat\Models\Call;

use Base\Models\User\SWIFT_User;
use Base\Models\User\SWIFT_UserEmail;
use SWIFT;
use LiveChat\Models\Chat\SWIFT_Chat;
use SWIFT_Data;
use SWIFT_DataID;
use SWIFT_DataStore;
use SWIFT_Exception;
use SWIFT_FileManager;
use SWIFT_Model;
use Tickets\Library\Ticket\SWIFT_Ticket_Exception;

/**
 * The Call Log Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_Call extends SWIFT_Model
{
    const TABLE_NAME = 'calls';
    const PRIMARY_KEY = 'callid';

    const TABLE_STRUCTURE = "callid I PRIMARY AUTO NOTNULL,
                                phonenumber C(255) DEFAULT '' NOTNULL,
                                callguid C(255) DEFAULT '' NOTNULL,
                                userid I DEFAULT '0' NOTNULL,
                                userfullname C(255) DEFAULT '' NOTNULL,
                                useremail C(255) DEFAULT '' NOTNULL,
                                staffid I DEFAULT '0' NOTNULL,
                                stafffullname C(255) DEFAULT '' NOTNULL,
                                chatobjectid I DEFAULT '0' NOTNULL,
                                departmentid I DEFAULT '0' NOTNULL,
                                dateline I DEFAULT '0' NOTNULL,
                                enddateline I DEFAULT '0' NOTNULL,
                                lastactivity I DEFAULT '0' NOTNULL,
                                duration I DEFAULT '0' NOTNULL,
                                isclicktocall I2 DEFAULT '0' NOTNULL,
                                callstatus I2 DEFAULT '0' NOTNULL,
                                calltype I2 DEFAULT '0' NOTNULL,
                                fileid BIGINT(11) DEFAULT '0' NOTNULL";

    const INDEX_1 = 'phonenumber, userid';
    const INDEX_2 = 'userid';
    const INDEX_3 = 'staffid';
    const INDEX_4 = 'dateline';
    const INDEX_5 = 'callstatus';
    const INDEX_6 = 'departmentid';
    const INDEX_7 = 'chatobjectid';
    const INDEX_8 = 'callguid';
    const INDEX_9 = 'calltype, callstatus';
    const INDEX_10 = 'phonenumber(15), userfullname(30), useremail(40)'; // Unified Search


    protected $_dataStore = array();

    // Core Constants
    const STATUS_PENDING = 1;
    const STATUS_ACCEPTED = 2;
    const STATUS_ENDED = 3;
    const STATUS_REJECTED = 4;
    const STATUS_UNANSWERED = 5;

    const TYPE_INBOUND = 1;
    const TYPE_OUTBOUND = 0;

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
            throw new SWIFT_Exception('Failed to load Call Object');
        }
    }

    /**
     * Returns query for phone numbers
     *
     * @param string $_phoneNumber
     * @return string
     * @author Werner Garcia
     */
    public static function getPhoneNumberSQL(string $_phoneNumber): string
    {
        $_phoneSQLExtended = '';
        $_SWIFT = SWIFT::GetInstance();
        if (!empty($_phoneNumber)) {
            $countryCode = $_SWIFT->Settings->Get('ls_activecountrycode');
            // first, match exact phone number
            $_phoneSQLContainer = [
                "phonenumber = '" . $_SWIFT->Database->Escape($_phoneNumber) . "'",
            ];
            foreach ([$countryCode, '+' . $countryCode] as $cc) {
                // add phone number with country code prefix
                $_phoneSQLContainer[] = "phonenumber = '" . $_SWIFT->Database->Escape($cc . $_phoneNumber) . "'";
                if (stripos($_phoneNumber, $cc) === 0) {
                    // add phone number without country code prefix
                    $_phoneSQLContainer[] = "phonenumber = '" . $_SWIFT->Database->Escape(substr($_phoneNumber,
                            strlen($cc))) . "'";
                }
            }

            $_phoneSQLExtended = " OR (" . implode(' OR ', $_phoneSQLContainer) . ") ";
        }

        return $_phoneSQLExtended;
    }

    /**
     * Destructor
     *
     * @author Varun Shoor
     * @throws SWIFT_Exception If the Record could not be loaded
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

        $this->Database->AutoExecute(TABLE_PREFIX . 'calls', $this->GetUpdatePool(), 'UPDATE', "callid = '" . (int)($this->GetCallID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Call ID
     *
     * @author Varun Shoor
     * @return mixed "callid" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetCallID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        return $this->_dataStore['callid'];
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
            $_dataStore = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "calls WHERE callid = '" . (int)($_SWIFT_DataObject->GetDataID()) . "'");
            if (isset($_dataStore['callid']) && !empty($_dataStore['callid'])) {
                $this->_dataStore = $_dataStore;

                return true;
            }

            // Is it a Store?
        } else if ($_SWIFT_DataObject instanceof SWIFT_DataStore && $_SWIFT_DataObject->GetIsClassLoaded()) {
            $this->_dataStore = $_SWIFT_DataObject->GetDataStore();

            if (!isset($this->_dataStore['callid']) || empty($this->_dataStore['callid'])) {
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
     * Check to see if its a valid call status
     *
     * @author Varun Shoor
     * @param mixed $_callStatus The Call Status
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidStatus($_callStatus)
    {
        if ($_callStatus == self::STATUS_ACCEPTED || $_callStatus == self::STATUS_ENDED || $_callStatus == self::STATUS_PENDING ||
            $_callStatus == self::STATUS_REJECTED || $_callStatus == self::STATUS_UNANSWERED) {
            return true;
        }

        return false;
    }

    /**
     * Check to see if its a valid call type
     *
     * @author Varun Shoor
     * @param mixed $_callType The Call Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidType($_callType)
    {
        if ($_callType == self::TYPE_INBOUND || $_callType == self::TYPE_OUTBOUND) {
            return true;
        }

        return true;
    }

    /**
     * Create a new Call record
     *
     * @author Varun Shoor
     * @param string $_phoneNumber The Phone Number
     * @param string $_callGUID The Call GUID
     * @param int $_userID The User ID
     * @param string $_userFullName The User Fullname
     * @param string $_userEmail The User Email Address
     * @param int $_staffID The Staff ID
     * @param string $_staffFullName The Staff Full Name
     * @param int $_chatObjectID The Chat Object ID
     * @param int $_departmentID The Department ID
     * @param bool $_isClickToCall
     * @param mixed $_callStatus The Call Status
     * @param mixed $_callType The Call Type
     * @return int The Call ID
     * @throws SWIFT_Exception If Invalid Data is Provided or If the Object could not be created
     * @Param bool $_isClickToCall Whether its a click to call
     */
    public static function Create($_phoneNumber, $_callGUID, $_userID, $_userFullName, $_userEmail, $_staffID, $_staffFullName, $_chatObjectID, $_departmentID,
                                  $_isClickToCall, $_callStatus, $_callType)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_phoneNumber) || !self::IsValidStatus($_callStatus) || !self::IsValidType($_callType)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'calls', array('phonenumber' => $_phoneNumber, 'callguid' => $_callGUID, 'userid' => $_userID,
            'userfullname' => $_userFullName, 'useremail' => $_userEmail, 'staffid' => $_staffID, 'stafffullname' => $_staffFullName,
            'chatobjectid' => $_chatObjectID, 'departmentid' => $_departmentID, 'isclicktocall' => (int)($_isClickToCall),
            'callstatus' => (int)($_callStatus), 'calltype' => (int)($_callType), 'dateline' => DATENOW), 'INSERT');
        $_callID = $_SWIFT->Database->Insert_ID();

        if (!$_callID) {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        return $_callID;
    }

    /**
     * Delete the Call record
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

        self::DeleteList(array($this->GetCallID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of Call Records
     *
     * @author Varun Shoor
     * @param array $_callIDList
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_callIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_callIDList)) {
            return false;
        }

        // Delete the call recording
        $_fileIDList = array();
        $_SWIFT->Database->Query("SELECT fileid FROM " . TABLE_PREFIX . "calls WHERE callid IN (" . BuildIN($_callIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_fileIDList[] = $_SWIFT->Database->Record['fileid'];
        }

        if (_is_array($_fileIDList)) {
            SWIFT_FileManager::DeleteList($_fileIDList);
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "calls WHERE callid IN (" . BuildIN($_callIDList) . ")");


        return true;
    }

    /**
     * Retrieve it on Chat Object
     *
     * @author Varun Shoor
     * @param SWIFT_Chat $_SWIFT_ChatObject
     * @return mixed "SWIFT_Call" (OBJECT) on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveOnChat(SWIFT_Chat $_SWIFT_ChatObject)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_SWIFT_ChatObject instanceof SWIFT_Chat || !$_SWIFT_ChatObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_callContainer = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "calls WHERE chatobjectid = '" . $_SWIFT_ChatObject->GetChatObjectID() . "'");
        if (isset($_callContainer['callid']) && !empty($_callContainer['callid'])) {
            return new SWIFT_Call(new SWIFT_DataStore($_callContainer));
        }

        return false;
    }

    /**
     * Retrieve on GUID
     *
     * @author Varun Shoor
     * @param string $_callGUID The Call GUID
     * @return mixed "SWIFT_Call" (OBJECT) on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveOnGUID($_callGUID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_callGUID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_callContainer = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "calls WHERE callguid = '" . $_SWIFT->Database->Escape($_callGUID) . "'");
        if (isset($_callContainer['callid']) && !empty($_callContainer['callid'])) {
            return new SWIFT_Call(new SWIFT_DataStore($_callContainer));
        }

        return false;
    }

    /**
     * Update the Call Status
     *
     * @author Varun Shoor
     * @param mixed $_callStatus The Call Status
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function UpdateStatus($_callStatus)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (!self::IsValidStatus($_callStatus)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if ($this->GetProperty('callstatus') == $_callStatus) {
            return true;
        } /*
        * BUG FIX - Rajat Garg
        *
        * SWIFT-1747 All calls are signed as "ended" in Chat Logs at Staff CP, even those that have not been answered
        *
        * Comments: Corresponding changes are also included in this commit, please refer the full commit for complete fix.
        *
        */
        else if ($this->GetProperty('callstatus') == SWIFT_Call::STATUS_PENDING && $_callStatus == SWIFT_Call::STATUS_ENDED) {
            $_callStatus = SWIFT_Call::STATUS_UNANSWERED;
        }

        $this->UpdatePool('callstatus', (int)($_callStatus));
        $this->ProcessUpdatePool();

        return true;
    }

    /**
     * Update the Call GUID
     *
     * @author Varun Shoor
     * @param mixed $_callGUID The Call GUID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function UpdateGUID($_callGUID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        if ($this->GetProperty('callguid') == $_callGUID) {
            return true;
        }

        $this->UpdatePool('callguid', $_callGUID);
        $this->ProcessUpdatePool();

        return true;
    }

    /**
     * Update the Call Activity
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function UpdateActivity()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $this->UpdatePool('enddateline', DATENOW);
        $this->UpdatePool('lastactivity', DATENOW);
        $this->UpdatePool('duration', DATENOW - $this->GetProperty('dateline'));
        $this->ProcessUpdatePool();

        return true;
    }

    /**
     * Get the Call Status Label
     *
     * @author Varun Shoor
     * @param mixed $_callStatus The Call Status
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function GetStatusLabel($_callStatus)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!self::IsValidStatus($_callStatus)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        switch ($_callStatus) {
            case self::STATUS_ACCEPTED:
                return $_SWIFT->Language->Get('status_accepted');
                break;

            case self::STATUS_ENDED:
                return $_SWIFT->Language->Get('status_ended');
                break;

            case self::STATUS_PENDING:
                return $_SWIFT->Language->Get('status_pending');
                break;

            case self::STATUS_REJECTED:
                return $_SWIFT->Language->Get('status_rejected');
                break;
            case self::STATUS_UNANSWERED:
                return $_SWIFT->Language->Get('status_unanswered');
                break;

            default:
                break;
        }

        return false;
    }

    /**
     * Get the Call Type Label
     *
     * @author Varun Shoor
     * @param mixed $_callType The Call Type
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function GetTypeLabel($_callType)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!self::IsValidType($_callType)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        switch ($_callType) {
            case self::TYPE_INBOUND:
                return $_SWIFT->Language->Get('type_inbound');
                break;

            case self::TYPE_OUTBOUND:
                return $_SWIFT->Language->Get('type_outbound');
                break;

            default:
                break;
        }

        return false;
    }

    /**
     * Retrieve the history count for this user based on his userid & email addresses
     *
     * @author Varun Shoor
     * @param SWIFT_User $_SWIFT_UserObject
     * @param array $_userEmailList
     * @return int The History Count
     */
    public static function GetHistoryCountOnUser($_SWIFT_UserObject, $_userEmailList = array())
    {
        $_SWIFT = SWIFT::GetInstance();

        $_userID = '-1';
        $_phoneNumber = '';
        if ($_SWIFT_UserObject instanceof SWIFT_User && $_SWIFT_UserObject->GetIsClassLoaded()) {
            $_userID = $_SWIFT_UserObject->GetUserID();
            $_phoneNumber = $_SWIFT_UserObject->GetProperty('phone');
        } else {
            if (!is_array($_userEmailList)) {
                $_userEmailList = [$_userEmailList];
            }
            $_SWIFT_UserObject = SWIFT_User::RetrieveOnEmailList($_userEmailList);
            if ($_SWIFT_UserObject instanceof SWIFT_User && $_SWIFT_UserObject->GetIsClassLoaded()) {
                $_userID = $_SWIFT_UserObject->GetUserID();

                $_phoneNumber = $_SWIFT_UserObject->GetProperty('phone');
            }
        }

        if (!_is_array($_userEmailList) && $_SWIFT_UserObject instanceof SWIFT_User && $_SWIFT_UserObject->GetIsClassLoaded()) {
            $_userEmailList = $_SWIFT_UserObject->GetEmailList();
        }

        $_phoneSQLExtended = self::getPhoneNumberSQL($_phoneNumber);

        $_countContainer = $_SWIFT->Database->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "calls WHERE userid = '" .
            $_userID . "' OR useremail IN (" . BuildIN($_userEmailList) . ")" . $_phoneSQLExtended);

        if (isset($_countContainer['totalitems']) && (int)($_countContainer['totalitems']) > 0) {
            return $_countContainer['totalitems'];
        }

        return 0;
    }


    /**
     * Retrieve the history for this chat
     *
     * @author Varun Shoor
     * @return array The History Container
     */
    public static function RetrieveHistoryExtended($_userID, $_incomingEmailList)
    {
        $_SWIFT = SWIFT::GetInstance();

        // Retrieve all email addresses of user
        $_userEmailList = array();
        $_emailAddressList = $_incomingEmailList;

        $_userEmailList = SWIFT_UserEmail::RetrieveList($_userID);
        foreach ($_userEmailList as $_emailAddress) {
            if (!in_array($_emailAddress, $_emailAddressList)) {
                $_emailAddressList[] = $_emailAddress;
            }
        }

        $_SWIFT_UserObject = false;
        if (empty($_userID)) {
            $_userID = '-1';
        } else {
            try {
                $_SWIFT_UserObject = new SWIFT_User(new SWIFT_DataID($_userID));
            } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            }
        }

        $_phoneNumber = '';
        if ($_SWIFT_UserObject instanceof SWIFT_User && $_SWIFT_UserObject->GetIsClassLoaded()) {
            $_phoneNumber = $_SWIFT_UserObject->GetProperty('phone');
        } else {
            $_SWIFT_UserObject = SWIFT_User::RetrieveOnEmailList($_emailAddressList);
            if ($_SWIFT_UserObject instanceof SWIFT_User && $_SWIFT_UserObject->GetIsClassLoaded()) {
                $_userID = $_SWIFT_UserObject->GetUserID();

                $_phoneNumber = $_SWIFT_UserObject->GetProperty('phone');
            }
        }

        $_phoneSQLExtended = self::getPhoneNumberSQL($_phoneNumber);

        $_historyContainer = array();

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "calls WHERE userid = '" . $_userID .
            "' OR useremail IN (" . BuildIN($_emailAddressList) . ")" . $_phoneSQLExtended . " ORDER BY dateline DESC");
        while ($_SWIFT->Database->NextRecord()) {
            $_historyContainer[$_SWIFT->Database->Record['callid']] = $_SWIFT->Database->Record;
        }

        return $_historyContainer;
    }


    /**
     * Update secondary user IDs with merged primary user ID
     *
     * @author Abhishek Mittal
     *
     * @param int $_primaryUserID
     * @param array $_secondaryUserIDList
     *
     * @return bool
     */
    public static function UpdateUserIDOnMerge($_primaryUserID, $_secondaryUserIDList)
    {
        $_SWIFT = SWIFT::GetInstance();
        if (!_is_array($_secondaryUserIDList)) {
            return false;
        }
        $_callContainer = array();
        $_SWIFT->Database->Query("SELECT callid FROM " . TABLE_PREFIX . self::TABLE_NAME . "
                                  WHERE userid IN ( " . BuildIN($_secondaryUserIDList, true) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_callContainer[$_SWIFT->Database->Record['callid']] = $_SWIFT->Database->Record;
        }
        foreach ($_callContainer as $_call) {
            $_Call = new SWIFT_Call(new SWIFT_DataID($_call['callid']));
            $_Call->UpdateUser($_primaryUserID);
        }
        return true;
    }

    /**
     * Updates the User with which the call is linked
     *
     * @author Abhishek Mittal
     *
     * @param int $_userID
     *
     * @return SWIFT_Call
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function UpdateUser($_userID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        }
        $this->UpdatePool('userid', $_userID);
        $this->ProcessUpdatePool();
        return $this;
    }
}


