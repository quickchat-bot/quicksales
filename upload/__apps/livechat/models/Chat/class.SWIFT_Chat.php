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

use Base\Library\Language\SWIFT_LanguagePhraseLinked;
use Base\Library\SearchEngine\SWIFT_SearchEngine;
use Base\Library\Staff\SWIFT_Staff_Exception;
use Base\Models\Department\SWIFT_Department;
use Base\Models\GeoIP\SWIFT_GeoIP;
use Base\Models\Staff\SWIFT_Staff;
use Base\Models\User\SWIFT_User;
use Base\Models\User\SWIFT_UserEmail;
use Base\Models\User\SWIFT_UserGroupAssign;
use LiveChat\Library\Chat\SWIFT_Chat_Exception;
use SWIFT;
use LiveChat\Models\Chat\SWIFT_ChatChild;
use LiveChat\Models\Chat\SWIFT_ChatHits;
use LiveChat\Models\Note\SWIFT_ChatNote;
use LiveChat\Models\Chat\SWIFT_ChatQueue;
use LiveChat\Models\Skill\SWIFT_ChatSkill;
use LiveChat\Models\Chat\SWIFT_ChatTextData;
use LiveChat\Models\Chat\SWIFT_ChatVariable;
use SWIFT_Date;
use SWIFT_Exception;
use SWIFT_Interface;
use SWIFT_LanguageEngine;
use SWIFT_Model;
use SWIFT_Session;
use SWIFT_TemplateEngine;
use Tickets\Library\Ticket\SWIFT_Ticket_Exception;

/**
 * The Chat Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_Chat extends SWIFT_Model
{
    const TABLE_NAME = 'chatobjects';
    const PRIMARY_KEY = 'chatobjectid';

    const TABLE_STRUCTURE = "chatobjectid I PRIMARY AUTO NOTNULL,
                                chatobjectmaskid C(50) DEFAULT '' NOTNULL,
                                visitorsessionid C(255) DEFAULT '' NOTNULL,
                                chatsessionid C(255) DEFAULT '' NOTNULL,
                                dateline I DEFAULT '0' NOTNULL,
                                lastpostactivity I DEFAULT '0' NOTNULL,
                                userpostactivity I DEFAULT '0' NOTNULL,
                                staffpostactivity I DEFAULT '0' NOTNULL,
                                userid I DEFAULT '0' NOTNULL,
                                userfullname C(150) DEFAULT '' NOTNULL,
                                useremail C(255) DEFAULT '' NOTNULL,
                                subject C(255) DEFAULT '' NOTNULL,
                                staffid I DEFAULT '0' NOTNULL,
                                staffname C(150) DEFAULT '' NOTNULL,
                                chatstatus I2 DEFAULT '0' NOTNULL,
                                transferfromid I DEFAULT '0' NOTNULL,
                                transfertoid I DEFAULT '0' NOTNULL,
                                transferstatus I2 DEFAULT '0' NOTNULL,
                                transfertimeline I DEFAULT '0' NOTNULL,
                                roundrobintimeline I DEFAULT '0' NOTNULL,
                                roundrobinhits I DEFAULT '0' NOTNULL,
                                departmentid I DEFAULT '0' NOTNULL,
                                departmenttitle C(100) DEFAULT '' NOTNULL,
                                chattype I2 DEFAULT '0' NOTNULL,
                                ipaddress C(50) DEFAULT '' NOTNULL,
                                waittime I DEFAULT '0' NOTNULL,
                                chatskillid I DEFAULT '0' NOTNULL,
                                isproactive I2 DEFAULT '0' NOTNULL,
                                creatorstaffid I DEFAULT '0' NOTNULL,
                                tgroupid I DEFAULT '0' NOTNULL,
                                isphone I2 DEFAULT '0' NOTNULL,
                                phonenumber C(255) DEFAULT '' NOTNULL,
                                callstatus I2 DEFAULT '0' NOTNULL,
                                isindexed I2 DEFAULT '0' NOTNULL,

                                hasgeoip I2 DEFAULT '0' NOTNULL,
                                geoiptimezone C(255) DEFAULT '' NOTNULL,
                                geoipisp C(255) DEFAULT '' NOTNULL,
                                geoiporganization C(255) DEFAULT '' NOTNULL,
                                geoipnetspeed C(255) DEFAULT '' NOTNULL,
                                geoipcountry C(10) DEFAULT '' NOTNULL,
                                geoipcountrydesc C(255) DEFAULT '' NOTNULL,
                                geoipregion C(255) DEFAULT '' NOTNULL,
                                geoipcity C(255) DEFAULT '' NOTNULL,
                                geoippostalcode C(255) DEFAULT '' NOTNULL,
                                geoiplatitude C(255) DEFAULT '' NOTNULL,
                                geoiplongitude C(255) DEFAULT '' NOTNULL,
                                geoipmetrocode C(255) DEFAULT '' NOTNULL,
                                geoipareacode C(255) DEFAULT '' NOTNULL";

    const INDEX_1 = 'chatstatus, staffid, lastpostactivity';
    const INDEX_2 = 'chatstatus, chattype';
    const INDEX_3 = 'visitorsessionid';
    const INDEX_4 = 'staffid';
    const INDEX_5 = 'userid, useremail';
    const INDEX_6 = 'ipaddress';
    const INDEX_7 = 'departmentid, dateline';
    const INDEX_8 = 'useremail';
    const INDEX_9 = 'chatstatus, staffid, dateline';
    const INDEX_10 = 'chatstatus, chatobjectid, staffid';
    const INDEX_11 = 'chatstatus, dateline, lastpostactivity';
    const INDEX_12 = 'departmentid, chatstatus';
    const INDEX_13 = 'dateline';
    const INDEX_14 = 'chatobjectmaskid, departmentid';
    const INDEX_15 = 'chatstatus, staffid, isphone';
    const INDEX_16 = 'isindexed, chatstatus';
    const INDEX_17 = 'subject(220), userfullname(30), useremail(40), phonenumber(15)'; // Unified Search


    // Chat Status
    const CHAT_INCOMING = 1;
    const CHAT_INCHAT = 2;
    const CHAT_ENDED = 3;
    const CHAT_NOANSWER = 4;
    const CHAT_TIMEOUT = 5;

    // Types
    const CHATTYPE_STAFF = 6;
    const CHATTYPE_CLIENT = 7;

    // Transfer Status
    const TRANSFER_PENDING = 1;
    const TRANSFER_ACCEPTED = 2;
    const TRANSFER_REJECTED = 3;

    // Chat End Types
    const CHATEND_STAFF = 1;
    const CHATEND_CLIENT = 2;

    // Staff <> Staff Chat
    const STAFFCHAT_DEFAULT = 'chat';
    const STAFFCHAT_INVITE = 'invite';

    // Variables
    private $_chatObject = array();
    public $_SWIFT_ChatQueueObject = false;
    static protected $_rebuildCacheExecuted = false;
    public $Mail;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param int $_chatObjectID The Chat Object ID to load data from
     * @throws SWIFT_Chat_Exception If the Class is not Loaded
     */
    public function __construct($_chatObjectID)
    {
        parent::__construct();

        if (!$this->LoadChatData($_chatObjectID)) {
            throw new SWIFT_Chat_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_SWIFT_ChatQueueObject = new SWIFT_ChatQueue($this);
        if (!$this->_SWIFT_ChatQueueObject instanceof SWIFT_ChatQueue || !$this->_SWIFT_ChatQueueObject->GetIsClassLoaded()) {
            throw new SWIFT_Chat_Exception(SWIFT_CLASSNOTLOADED . 'Chat Queue');
        }
    }

    /**
     * Processes the Update Pool Data
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Chat_Exception If the Class is not Loaded
     */
    public function ProcessUpdatePool()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        } else if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'chatobjects', $this->GetUpdatePool(), 'UPDATE', "chatobjectid = '" . ($this->GetChatObjectID()) . "'");

        return true;
    }

    /**
     * Retrieves the Chat Object ID for the given object
     *
     * @author Varun Shoor
     * @return int "chatobjectid" on Success, "false" otherwise
     * @throws SWIFT_Chat_Exception If the Class is not Loaded
     */
    public function GetChatObjectID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Chat_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_chatObject['chatobjectid'];
    }

    /**
     * Update the transfer fields for this chat object
     *
     * @author Varun Shoor
     * @param int $_transferFromID The Staff ID to Transfer From
     * @param int $_transferToID The Staff ID to Transfer to
     * @param mixed $_transferStatus The Transfer Status
     * @param int $_transferTimeline The Timline when the transfer was initiated
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Chat_Exception If the Class is not Loaded
     */
    public function UpdateTransfer($_transferFromID, $_transferToID, $_transferStatus, $_transferTimeline)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Chat_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        /*
         * BUG FIX - Ravi Sharma
         *
         * SWIFT-2885 Chat shows ‘Pending’ in QuickSupport Desktop to all staff members, if staff declines a transfer chat
         *
         * Comments: When transfer request has been accepted, it should update the chat status and corresponding data aswell.
         */
        if ($_transferStatus == self::TRANSFER_ACCEPTED) {
            $_staffCache = $this->Cache->Get('staffcache');
            $this->UpdatePool('chatstatus', SWIFT_Chat::CHAT_INCHAT);
            $this->UpdatePool('staffid', $_transferToID);
            $this->UpdatePool('staffname', $_staffCache[$_transferToID]['fullname']);
        }

        $this->UpdatePool('transferfromid', $_transferFromID);
        $this->UpdatePool('transfertoid', $_transferToID);
        $this->UpdatePool('transferstatus', (int)($_transferStatus));
        $this->UpdatePool('transfertimeline', ($_transferTimeline));
        $this->ProcessUpdatePool();

        return true;
    }

    /**
     * Updates the last post activity for this chat object
     *
     * @author Varun Shoor
     * @param string $_submitType The Message Type
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Chat_Exception If the Class is not Loaded
     */
    public function UpdateLastPostActivity($_submitType)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Chat_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        if ($_submitType == SWIFT_ChatQueue::SUBMIT_STAFF) {
            $this->UpdatePool('lastpostactivity', DATENOW);
            $this->UpdatePool('staffpostactivity', DATENOW);
        } else {
            $this->UpdatePool('lastpostactivity', DATENOW);
            $this->UpdatePool('userpostactivity', DATENOW);
        }

        $this->ProcessUpdatePool();

        return true;
    }

    /**
     * Appends the Chat Data serialized Array
     *
     * @author Varun Shoor
     * @param int $_msgType The Message Type
     * @param int $_submitType The Submitter Type
     * @param string $_fullName The Full Name of person submitting chat
     * @param string $_message The Message to dispatch
     * @param bool $_doBase64 Whether the message is base64 encoded
     * @param string $_actionType The Action Type
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Chat_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function AppendChatData($_msgType, $_submitType, $_fullName, $_message, $_doBase64, $_actionType)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Chat_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        // Get the data for this from chatdata table now..
        $_chatDataContainer = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "chatdata WHERE chatobjectid = '" . $this->GetChatObjectID() . "'");
        if (!$_chatDataContainer || !isset($_chatDataContainer['chatdataid']) || empty($_chatDataContainer['chatdataid'])) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        if ($_actionType == SWIFT_ChatQueue::CHATACTION_TYPING) {
            return true;
        }

        $_chatDataArray = mb_unserialize($_chatDataContainer['contents']);

        $_chatDataArray[] = array('type' => $_msgType, 'name' => $_fullName, 'message' => $_message, 'base64' => $_doBase64, 'submittype'
        => $_submitType, 'actiontype' => $_actionType, 'dateline' => DATENOW);

        $_serializedChatData = serialize($_chatDataArray);

        $this->Database->AutoExecute(TABLE_PREFIX . 'chatdata', array('contents' => $_serializedChatData), 'UPDATE', "chatobjectid = '" . $this->GetChatObjectID() . "'");

        return true;
    }

    /**
     * Loads the chat data for the given chat object id
     *
     * @author Varun Shoor
     * @param int $_chatObjectID The Chat Object ID to load data from
     * @return bool "true" on Success, "false" otherwise
     */
    private function LoadChatData($_chatObjectID)
    {
        $_chatObject = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "chatobjects WHERE chatobjectid = '" . $_chatObjectID . "'");
        if (!isset($_chatObject['chatobjectid']) || empty($_chatObject['chatobjectid'])) {
            return false;
        }

        $this->_chatObject = $_chatObject;

        return true;
    }

    /**
     * Check to see If it is a Valid Chat Type
     *
     * @author Varun Shoor
     * @param mixed $_chatType The Chat Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidChatType($_chatType)
    {
        if ($_chatType == self::CHATTYPE_STAFF || $_chatType == self::CHATTYPE_CLIENT) {
            return true;
        }

        return false;
    }

    /**
     * Retrieve a unique chat object mask id
     *
     * @author Varun Shoor
     * @return string The Unique Chat Object Mask ID
     */
    public static function GetChatObjectMaskID()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_uniqueMaskID = GenerateUniqueMask();

        do {
            $_chatObjectMaskID = false;
            $_uniqueMaskID = GenerateUniqueMask();

            $_maskCheckContainer = $_SWIFT->Database->QueryFetch("SELECT chatobjectmaskid FROM " . TABLE_PREFIX . "chatobjects WHERE chatobjectmaskid = '" . $_SWIFT->Database->Escape($_uniqueMaskID) . "'");
            if (!$_maskCheckContainer || !isset($_maskCheckContainer['chatobjectmaskid'])) {
                $_chatObjectMaskID = $_uniqueMaskID;
            }

        } while (empty($_chatObjectMaskID));

        return $_chatObjectMaskID;
    }

    /**
     * Insert a new Chat Object
     *
     * @author Varun Shoor
     * @param string $_visitorSessionID The Visitor Session ID
     * @param int $_userID The User ID
     * @param string $_userFullName The User Full Name
     * @param string $_userEmail The User Email
     * @param string $_subject The Chat Subject
     * @param int $_staffID The PRIMARY Staff ID taking the chat
     * @param string $_staffName The Staff Name
     * @param int $_departmentID The Department ID under which chat is initiated
     * @param string $_departmentTitle The Department Title
     * @param mixed $_chatType The Chat Type
     * @param string $_ipAddress The IP Address of User
     * @param bool $_isProactive Whether the chat is proactive
     * @param int $_chatSkillID (OPTIONAL) The Chat Skill ID
     * @param int $_creatorStaffID (OPTIONAL) In case of staff chats
     * @param int $_templateGroupID (OPTIONAL) The Template Group ID
     * @param bool $_isPhone (OPTIONAL) Whether its a phone call request
     * @param string $_phoneNumber (OPTIONAL) The Phone Number
     * @return mixed "_SWIFT_ChatObject" (OBJECT) on Success, "false" otherwise
     * @throws SWIFT_Chat_Exception If Invalid Data is Provided or If Creation Fails
     */
    public static function Insert($_visitorSessionID, $_userID, $_userFullName, $_userEmail, $_subject, $_staffID, $_staffName, $_departmentID,
                                  $_departmentTitle, $_chatType, $_ipAddress, $_isProactive, $_chatSkillID = 0, $_creatorStaffID = 0, $_templateGroupID = 0,
                                  $_isPhone = false, $_phoneNumber = '')
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_userFullName) || !self::IsValidChatType($_chatType)) {
            throw new SWIFT_Chat_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'chatobjects', array('visitorsessionid' => ReturnNone($_visitorSessionID),
            'dateline' => DATENOW, 'lastpostactivity' => '0', 'userpostactivity' => '0', 'staffpostactivity' => '0',
            'userid' => $_userID, 'userfullname' => ReturnNone($_userFullName), 'useremail' => ReturnNone($_userEmail),
            'subject' => ReturnNone($_subject), 'staffid' => $_staffID, 'staffname' => ReturnNone($_staffName),
            'chatstatus' => self::CHAT_INCOMING, 'transferfromid' => '0', 'transfertoid' => '0', 'transferstatus' => '0', 'transfertimeline' => '0',
            'roundrobintimeline' => DATENOW, 'roundrobinhits' => '0', 'departmentid' => $_departmentID,
            'departmenttitle' => ReturnNone($_departmentTitle), 'chattype' => $_chatType, 'ipaddress' => ReturnNone(SWIFT::Get('IP')),
            'isproactive' => (int)($_isProactive), 'chatobjectmaskid' => self::GetChatObjectMaskID(),
            'chatskillid' => $_chatSkillID, 'creatorstaffid' => $_creatorStaffID, 'tgroupid' => $_templateGroupID,
            'isphone' => (int)($_isPhone), 'phonenumber' => $_phoneNumber), 'INSERT');

        $_chatObjectID = $_SWIFT->Database->Insert_ID();
        if (!$_chatObjectID) {
            throw new SWIFT_Chat_Exception(SWIFT_CREATEFAILED);

            return false;
        }

        $_SWIFT_ChatObject = new SWIFT_Chat($_chatObjectID);
        if (!$_SWIFT_ChatObject instanceof SWIFT_Chat || !$_SWIFT_ChatObject->GetIsClassLoaded()) {
            throw new SWIFT_Chat_Exception(SWIFT_CREATEFAILED);

            return false;
        }

        $_chatSessionID = SWIFT_Session::Insert(SWIFT_Interface::INTERFACE_CHAT, $_chatObjectID);

        $_SWIFT_ChatObject->SetChatSessionID($_chatSessionID);
        $_SWIFT_ChatObject->CreateChatDataRecord();

        // GeoIP
        if ($_chatType == self::CHATTYPE_CLIENT) {
            $_SWIFT_ChatObject->UpdateGeoIP();
        }

        if ($_staffID != '0') {
            $_chatHitID = SWIFT_ChatHits::Insert($_SWIFT_ChatObject, $_staffID, $_userFullName, $_userEmail, false);
        }

        return $_SWIFT_ChatObject;
    }

    /**
     * Sets the Chat Session ID for the given Chat Object
     *
     * @author Varun Shoor
     * @param string $_chatSessionID The Chat Session ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Chat_Exception If the Class is not Loaded
     */
    public function SetChatSessionID($_chatSessionID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Chat_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $this->_chatObject['chatsessionid'] = $_chatSessionID;
        $this->UpdatePool('chatsessionid', $_chatSessionID);

        $this->ProcessUpdatePool();

        return true;
    }

    /**
     * Retrieves the Chat Session ID
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Chat_Exception If the Class is not Loaded
     */
    public function GetChatSessionID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Chat_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        return $this->_chatObject['chatsessionid'];
    }

    /**
     * Creates the linked chat data record
     *
     * @author Varun Shoor
     * @return int
     * @throws SWIFT_Chat_Exception If the Class is not Loaded
     */
    private function CreateChatDataRecord()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Chat_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'chatdata', array('chatobjectid' => $this->GetChatObjectID(), 'contents' => serialize(array())), 'INSERT');
        $_chatDataID = $this->Database->Insert_ID();

        return $_chatDataID;
    }

    /**
     * Used to Retrieve Details about the chat object
     *
     * @author Varun Shoor
     * @param string $_key The Key to fetch data for
     * @return mixed Property Value on Success, "false" otherwise
     * @throws SWIFT_Chat_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function GetProperty($_key)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Chat_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!isset($this->_chatObject[$_key])) {
            throw new SWIFT_Chat_Exception(SWIFT_INVALIDDATA);
        }

        return $this->_chatObject[$_key];
    }

    /**
     * Retrieves the count of Chat Childs associated with this Chat Object
     *
     * @author Varun Shoor
     * @return int Chat Child Count on Success, "0" otherwise
     * @throws SWIFT_Chat_Exception If the Class is not Loaded
     */
    public function GetChatChildCount()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Chat_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_chatChildCount = array();
        $_chatChildCountContainer = $this->Database->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "chatchilds WHERE chatobjectid = '" . $this->GetChatObjectID() . "'");

        return (int)($_chatChildCountContainer['totalitems']);
    }

    /**
     * Retrieves the list of Chat Childs associated with this Chat Object
     *
     * @author Varun Shoor
     * @return array Chat Child List on Success, "false" otherwise
     * @throws SWIFT_Chat_Exception If the Class is not Loaded
     */
    public function GetChatChildList()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Chat_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_chatChildList = array();
        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "chatchilds WHERE chatobjectid = '" . $this->GetChatObjectID() . "'");
        while ($this->Database->NextRecord()) {
            $_chatChildList[$this->Database->Record['chatchildid']] = $this->Database->Record;
        }

        return $_chatChildList;
    }

    /**
     * Retrieves all staff members for a given department id
     *
     * @author Varun Shoor
     * @param int $_departmentID The Department ID
     * @return array
     */
    public static function GetStaffMembersByDepartment($_departmentID)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_staffIDList = array();

        $_SWIFT->Database->Query("SELECT staff.staffid AS staffid, staff.groupassigns AS groupassigns, groupassigns.groupassignid AS groupassignid FROM " . TABLE_PREFIX . "staff AS staff LEFT JOIN " . TABLE_PREFIX . "staffassigns AS staffassigns ON (staffassigns.staffid = staff.staffid) LEFT JOIN " . TABLE_PREFIX . "groupassigns AS groupassigns ON (groupassigns.staffgroupid = staff.staffgroupid) WHERE staffassigns.departmentid = '" . $_departmentID . "' OR groupassigns.departmentid = IF(staff.groupassigns='1','" . $_departmentID . "', '0')");
        while ($_SWIFT->Database->NextRecord()) {
            $_staffIDList[] = $_SWIFT->Database->Record["staffid"];
        }

        return $_staffIDList;

    }

    /**
     * Check whether a staff is online
     *
     * @author Varun Shoor
     * @param int $_staffID The Staff ID
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsStaffOnline($_staffID)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_sessionList = array();
        $_timeFetch = DATENOW - 60;

        $_staffSession = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "sessions WHERE sessiontype IN ('" . SWIFT_Interface::INTERFACE_WINAPP . "') AND lastactivity >= '" . ($_timeFetch) . "' AND typeid = '" . $_staffID . "'");
        if (isset($_staffSession['sessionid']) && !empty($_staffSession['sessionid']) && $_staffSession['typeid'] == $_staffID && !empty($_staffID)) {
            return true;
        }

        return false;
    }

    /**
     * This retrieves all active sessions in a given staff list
     *
     * @author Varun Shoor
     * @param array $_staffIDList The Staff ID List
     * @param array $_staffIDIgnoreList The list of staff ids to ignore
     * @param bool $_isPhone (OPTIONAL) Whether this is a phone call request
     * @return array
     */
    public static function GetSessionsForStaffList($_staffIDList, $_staffIDIgnoreList, $_isPhone = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_sessionList = array();
        $_timeFetch = DATENOW - 60;

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "sessions
            WHERE sessiontype IN ('" . SWIFT_Interface::INTERFACE_WINAPP . "')
                AND lastactivity >= '" . ($_timeFetch) . "'
                AND typeid IN (" . BuildIN($_staffIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            if (!in_array($_SWIFT->Database->Record["typeid"], $_staffIDIgnoreList)) {
                $_sessionList[$_SWIFT->Database->Record["sessionid"]] = $_SWIFT->Database->Record;
            }
        }

        return $_sessionList;
    }

    /**
     * Returns the staff ID of the staff member whose most recent chat is the oldest.
     * Assumes that every staff member in the input has at least one active chat.
     *
     * @author Varun Shoor
     * @param array $_staffContainer The Staff Container Array
     * @return int
     */
    public static function ReturnStaffWithMostTimeOnHisHands($_staffContainer)
    {
        if (!_is_array($_staffContainer)) {
            return 0;
        }

        array_sort_by_column($_staffContainer, 'dateline');

        $_oldestMostRecent = array('dateline' => 0, 'staffid' => 0);

        foreach ($_staffContainer as $_staff) {
            $_oldestMostRecent = array('dateline' => $_staff['dateline'], 'staffid' => $_staff['staffid']);

            foreach ($_staffContainer as $_staffMember) {
                $_myNewest = 0;

                foreach ($_staffMember['chats'] as $_activeChat) {
                    if (!$_myNewest || $_activeChat > $_myNewest) {
                        $_myNewest = $_activeChat;
                    }
                }

                if (!$_oldestMostRecent['dateline'] || $_myNewest < $_oldestMostRecent['dateline']) {
                    $_oldestMostRecent['dateline'] = $_myNewest;
                    $_oldestMostRecent['staffid'] = $_staffMember['staffid'];
                }
            }
        }

        // Return the staff ID of the "oldest most recent" chat.
        return $_oldestMostRecent['staffid'];
    }

    /**
     * Retrieves the available staff member for chat from a given department
     *
     * @author Varun Shoor
     * @param int $_departmentID The Department ID
     * @param array|bool $_staffIDIgnoreList (OPTIONAL) The Ignore list of staff ids
     * @param array|bool $_customStaffIDList (OPTIONAL) A custom staff id list if this shouldnt be processed according to department
     * @param array $_requiredChatSkillIDList (OPTIONAL) A list of required skills that should be looked up in a staff
     * @param bool $_isPhone (OPTIONAL) Whether this is a phone call request
     * @return mixed "_roundRobinStaffID" (INT) on Success, "false" otherwise
     */
    public static function GetRoundRobinStaff($_departmentID, $_staffIDIgnoreList = false, $_customStaffIDList = false, $_requiredChatSkillIDList = array(), $_isPhone = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_staffIDIgnoreList)) {
            $_staffIDIgnoreList = array();
        }

        // If we dont need to fetch the staff on basis of any skills.. we simply use the non skill based function
        if (!_is_array($_requiredChatSkillIDList)) {
            return self::GetRoundRobinStaffNonSkill($_departmentID, $_staffIDIgnoreList, $_customStaffIDList, $_isPhone);
        }

        // We have skills to process!
        $_roundRobinStaffID = (int) self::GetRoundRobinStaffFromSkill($_departmentID, $_staffIDIgnoreList, $_customStaffIDList, $_requiredChatSkillIDList, $_isPhone);

        // If we didnt receive a round robin staff on skills.. we attempt it without skills..
        if (!$_roundRobinStaffID) {
            return self::GetRoundRobinStaffNonSkill($_departmentID, $_staffIDIgnoreList, $_customStaffIDList, $_isPhone);
        }

        return $_roundRobinStaffID;
    }

    /**
     * Retreieve the round robin staff based on department id or customstaffidlist
     *
     * @author Varun Shoor
     * @param int $_departmentID The Department ID
     * @param array|false $_staffIDIgnoreList (OPTIONAL) The Ignore list of staff ids
     * @param array|false $_customStaffIDList (OPTIONAL) A custom staff id list if this shouldnt be processed according to department
     * @param bool $_isPhone (OPTIONAL) Whether this is a phone call request
     * @return mixed "_roundRobinStaffID" (INT) on Success, "false" otherwise
     */
    protected static function GetRoundRobinStaffNonSkill($_departmentID, $_staffIDIgnoreList = false, $_customStaffIDList = false, $_isPhone = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_staffIDIgnoreList)) {
            $_staffIDIgnoreList = array();
        }

        if (_is_array($_customStaffIDList)) {
            $_staffIDList = $_customStaffIDList;
        } else {
            $_staffIDList = array_unique(self::GetStaffMembersByDepartment($_departmentID));
        }
        $_sessionsContainer = self::GetSessionsForStaffList($_staffIDList, $_staffIDIgnoreList, $_isPhone);

        /**
         * IMPROVEMENT: Mansi Wason <mansi.wason@kayako.com>
         *
         * SWIFT-4918: There should be a defined process for routing chat requests using Round-Robin mode
         *
         * Comments: We have introduced ActiveChatCount per staff.
         */
        $_staffContainer = $_datelineContainer = $_activeChatContainer = $_processedStaffIDList = array();
        foreach ($_sessionsContainer as $_key => $_val) {
            $_processedStaffIDList[] = $_val['typeid'];
        }

        $_sqlExtended = '';
        if ($_isPhone == true) {
            $_sqlExtended = " AND isphone = '1'";
        }

        if (count($_processedStaffIDList)) {
            $_SWIFT->Database->Query("SELECT count(*) as activechats, staffid, min(dateline) as dateline FROM " . TABLE_PREFIX . "chatobjects
               WHERE chatstatus IN('" . self::CHAT_INCHAT . "', '" . self::CHAT_INCOMING . "') AND staffid IN (" . BuildIN($_processedStaffIDList) . ")" . $_sqlExtended . "group by staffid order by dateline");
            while ($_SWIFT->Database->NextRecord()) {
                $_datelineContainer[$_SWIFT->Database->Record['staffid']][] = $_SWIFT->Database->Record['dateline'];
                $_activeChatContainer[$_SWIFT->Database->Record['staffid']][] = $_SWIFT->Database->Record['activechats'];
            }
        }

        foreach ($_sessionsContainer as $_key => $_val) {
            if ($_isPhone == false && $_val["status"] != SWIFT_Session::STATUS_ONLINE) {
                continue;

            } else if ($_isPhone == true && $_val['phonestatus'] != SWIFT_Session::PHONESTATUS_AVAILABLE) {
                continue;
            }

            // How many chats is this staff member currently handling?
            $_thisStaff = array('staffid' => $_val["typeid"]);

            if (isset($_activeChatContainer[$_val['typeid']])) {
                $_thisStaff['chats'] = $_activeChatContainer[$_val['typeid']];
                $_thisStaff['dateline'] = $_datelineContainer[$_val['typeid']];
            }

            if (isset($_thisStaff['chats'])) {
                $_thisStaffChatCount = count($_thisStaff['chats']);
            } else {
                $_thisStaffChatCount = 0;
            }

            // Sort the array of staff members grouped together based on how many chats they're handling.
            $_staffContainer[$_thisStaffChatCount][] = $_thisStaff;
        }

        ksort($_staffContainer);

        // arrNumChats is now sorted according to groups of staff members with the same number of active chats.

        // Are there people handling no chats currently?
        if (!isset($_staffContainer[0])) {
            $_noChats = 0;
        } else {
            $_noChats = count($_staffContainer[0]);
        }

        if ($_noChats > 0) {
            // Chose someone randomly to take the chat who isn't handling any chats.
            if ($_noChats == 1) {
                $_randomStaffID = $_staffContainer[0][0]['staffid'];
            } else {
                $_randomStaffID = $_staffContainer[0][mt_rand(0, $_noChats - 1)]['staffid'];
            }

            return $_randomStaffID;

        } else {
            // Everyone is handling at least one chat, so pick someone who has the least amount of active chats, and also
            // has not accepted a new chat in the longest amount of time.
            $_staffID = self::ReturnStaffWithMostTimeOnHisHands(reset($_staffContainer));

            return $_staffID;
        }

        return false;
    }

    /**
     * Get the round robin staff based on a skill set...
     *
     * @author Varun Shoor
     * @param int $_departmentID The Department ID
     * @param array|false $_staffIDIgnoreList (OPTIONAL) The Ignore list of staff ids
     * @param array|false $_customStaffIDList (OPTIONAL) A custom staff id list if this shouldnt be processed according to department
     * @param array $_requiredChatSkillIDList (OPTIONAL) A list of required skills that should be looked up in a staff
     * @param bool $_isPhone (OPTIONAL) Whether this is a phone call request
     * @return mixed "_roundRobinStaffID" (INT) on Success, "false" otherwise
     */
    protected static function GetRoundRobinstaffFromSkill($_departmentID, $_staffIDIgnoreList = false, $_customStaffIDList = false, $_requiredChatSkillIDList = array(), $_isPhone = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_staffIDIgnoreList)) {
            $_staffIDIgnoreList = array();
        }

        if (_is_array($_customStaffIDList)) {
            $_staffIDList = $_customStaffIDList;
        } else {
            $_staffIDList = array_unique(self::GetStaffMembersByDepartment($_departmentID));
        }
        $_sessionsContainer = self::GetSessionsForStaffList($_staffIDList, $_staffIDIgnoreList);

        $_staffContainer = $_datelineContainer = $_processedStaffIDList = array();
        foreach ($_sessionsContainer as $_key => $_val) {
            if ($_isPhone == false && $_val["status"] != SWIFT_Session::STATUS_ONLINE) {
                continue;

            } else if ($_isPhone == true && $_val['phonestatus'] != SWIFT_Session::PHONESTATUS_AVAILABLE) {
                continue;
            }

            // This array contains all the online staff ids..
            $_processedStaffIDList[] = $_val['typeid'];
        }

        // By now we have the online staff ids and the staff id list that we needed to process.. we now attempt to retrieve the skill id's of online users
        $_chatSkillIDContainer = SWIFT_ChatSkill::RetrieveSkillListOnStaffList($_processedStaffIDList);

        // The staff we have online have no skills? what?
        if (!count($_chatSkillIDContainer)) {
            return false;
        }

        // By now we have staff which have some skills.. but not necessarily the same skills we need..
        $_staffSkillMap = $_staffSkillCountMap = array();
        foreach ($_chatSkillIDContainer as $_key => $_val) {
            if (!count($_val)) {
                continue;
            }

            $_commonSkills = array_intersect($_requiredChatSkillIDList, $_val);
            if (!count($_commonSkills)) {
                continue;
            }

            // By now we have some of the skills this staff might have from our required skill set
            $_staffSkillMap[$_key] = $_commonSkills;
            $_staffSkillCountMap[$_key] = count($_commonSkills);
        }

        // No staff with skills?
        if (!count($_staffSkillMap)) {
            return false;
        }

        asort($_staffSkillCountMap);

        $_finalStaffContainer = array();

        // Return the staff with maximum number of required skills...
        foreach ($_staffSkillCountMap as $_key => $_val) {
            // Check that this staff is ignored..
            if (!in_array($_key, $_staffIDIgnoreList)) {
                $_finalStaffContainer[] = $_key;
            }
        }

        /*
         * BUG FIX - Varun Shoor
         *
         * SWIFT-1568 Round-robin algorithm in chats does not works as expected
         *
         * Comments: None
         */
        // Count the qualified staff members
        $_staffCount = count($_finalStaffContainer);
        if ($_staffCount >= 1) {
            // Return a random staff from the list
            return $_finalStaffContainer[mt_rand(0, $_staffCount - 1)];
        }

        return false;
    }

    /**
     * Update the Chat Status
     *
     * @author Varun Shoor
     * @param int $_chatSkillID The Chat Skill ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Chat_Exception If the Class is not Loaded
     */
    public function UpdateChatSkill($_chatSkillID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Chat_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $this->UpdatePool('chatskillid', $_chatSkillID);

        $this->ProcessUpdatePool();

        self::RebuildCache();

        return true;
    }

    /**
     * Update the Chat Status
     *
     * @author Varun Shoor
     * @param int $_chatStatus The Chat Status
     * @param int $_staffID The Staff ID
     * @param string $_staffName The Staff Name
     * @param bool $_resetStaff
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public function UpdateChatStatus($_chatStatus, $_staffID = 0, $_staffName = '', $_resetStaff = false)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Chat_Exception(SWIFT_CLASSNOTLOADED);
        }

        /*
         * BUG FIX - Parminder Singh
         *
         * SWIFT-2872: Wait Time : 0 second
         *
         * Comments: It should update wait time if current status is incoming
         */
        if ($this->GetProperty('chatstatus') == self::CHAT_INCOMING) {
            $_waitTime = DATENOW - $this->GetProperty('dateline');
            $this->UpdatePool('waittime', (int)($_waitTime));
        }

        $this->UpdatePool('chatstatus', $_chatStatus);
        if (trim($_staffID) != '' && !empty($_staffID) && trim($_staffName) != '') {

            if ($_chatStatus == self::CHAT_INCHAT) {
                // Mark as accepted
                SWIFT_ChatHits::MarkAsAccepted($this, $_staffID);
            } else if ($_chatStatus == self::CHAT_INCOMING) {
                $this->UpdatePool('transfertoid', $_staffID);
            }

            $this->UpdatePool('staffid', $_staffID);
            $this->UpdatePool('staffname', $_staffName);
        }

        if ($_resetStaff == true) {
            $this->UpdatePool('staffid', '0');
            $this->UpdatePool('staffname', '');
        }

        $this->ProcessUpdatePool();

        self::RebuildCache();

        return true;
    }

    /**
     * Bulk Update the Chat Status
     *
     * @author Varun Shoor
     * @param array $_chatObjectIDList
     * @param mixed $_chatStatus
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function BulkUpdateChatStatus($_chatObjectIDList, $_chatStatus)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_chatObjectIDList)) {
            return false;
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'chatobjects', array('chatstatus' => (int)($_chatStatus)), 'UPDATE', "chatobjectid IN (" . BuildIN($_chatObjectIDList) . ")");

        return true;
    }

    /**
     * Deletes all the Chat Childs for the current chat object
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Chat_Exception If the Class is not Loaded
     */
    private function DeleteAllChatChilds()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Chat_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "chatchilds WHERE chatobjectid = '" . $this->GetChatObjectID() . "'");

        return true;
    }

    /**
     * Forces Complete Closure of Chat
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Chat_Exception If the Class is not Loaded
     */
    private function ForcedEndChat()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Chat_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $this->UpdateChatStatus(self::CHAT_ENDED);

        $this->DeleteAllChatChilds();

        // TODO: Update Chat Hits Here

        self::RebuildCache();

        return true;
    }

    /**
     * Ends the given chat
     *
     * @author Varun Shoor
     * @param int $_chatEndType The Source of End Request (staff/client)
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Chat_Exception If the Class is not Loaded
     */
    public function EndChat($_chatEndType)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Chat_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_staffCache = $this->Cache->Get('staffcache');
        $_departmentCache = $this->Cache->Get('departmentcache');

        $_currentChatChildID = $_processedChatChildCount = $_backupStaffID = 0;
        $_isConference = false;
        $_haveChatChild = false;

        $_dispatchNotification = true;

        if ($_chatEndType == self::CHATEND_CLIENT) {
            $this->ForcedEndChat();

            return true;
        } else if ($_chatEndType == self::CHATEND_STAFF) {

            // If the chat status isnt marked as INCHAT then we simply end
            if ($this->GetProperty('chatstatus') != self::CHAT_INCHAT) {
                return false;
            }

            $_chatChildList = $this->GetChatChildList();
            $_backupStaffName = false;
            if (_is_array($_chatChildList)) {
                foreach ($_chatChildList as $_key => $_val) {
                    // We have another staff in conference, so we need to use him as a backup if the main staff leaves
                    if ($_val['staffid'] != '0' && $_val['staffid'] != $_SWIFT->Staff->GetStaffID() && $_val['isobserver'] != '1') {
                        $_backupStaffID = $_val['staffid'];
                        $_backupStaffName = $_staffCache[$_val['staffid']]['fullname'];

                        // The current chat child id for this staff is..
                    } else if ($_val['staffid'] == $_SWIFT->Staff->GetStaffID()) {
                        $_currentChatChildID = $_val['chatchildid'];

                        if ($_val['isobserver'] == '1') {
                            $_dispatchNotification = false;
                        }
                    }

                    if ($_val['staffid'] == $_SWIFT->Staff->GetStaffID()) {
                        $_haveChatChild = true;
                    }

                    $_processedChatChildCount++;
                }
            }

            if (!$_haveChatChild) {
                return true;
            }
            // If this is a conference chat then we dont end it when leaving..
            if (($_processedChatChildCount > 1 && $this->GetProperty('chattype') == self::CHATTYPE_CLIENT) || ($_processedChatChildCount > 2 && $this->GetProperty('chattype') == self::CHATTYPE_STAFF)) {
                $_isConference = true;
            }

            if ($_isConference && !empty($_backupStaffID) && !empty($_backupStaffName)) {
                $this->UpdateChatStatus(self::CHAT_INCHAT, $_backupStaffID, $_backupStaffName);
            } else {
                $this->ForcedEndChat();
                unset($_currentChatChildID); // Need to unset $_currentChatChildID because all chat childs has already been removed in ForcedEndChat() function
            }

            if ($_isConference) {
                if ($_SWIFT->Settings->Get('ls_depname') == 1 && !empty($_departmentCache[$this->GetProperty('departmentid')]["title"])) {
                    $_chatDisplayName = $_departmentCache[$this->GetProperty('departmentid')]["title"];
                } else {
                    $_chatDisplayName = $_SWIFT->Staff->GetProperty('fullname');
                }

                if ($_dispatchNotification) {
                    $this->_SWIFT_ChatQueueObject->AddActionToQueue(SWIFT_ChatQueue::SUBMIT_STAFF, SWIFT_ChatQueue::CHATACTION_CHATLEAVE, $_chatDisplayName);
                }

                if (isset($_currentChatChildID)) {
                    // Delete the child because the chat wasnt nuked
                    $_SWIFT_ChatChildObject = false;
                    try {
                        $_SWIFT_ChatChildObject = new SWIFT_ChatChild($_currentChatChildID);
                    } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
                    }

                    if ($_SWIFT_ChatChildObject instanceof SWIFT_ChatChild && $_SWIFT_ChatChildObject->GetIsClassLoaded()) {
                        $_SWIFT_ChatChildObject->Delete();
                    }
                }
            }
        }

        self::RebuildCache();

        return true;
    }

    /**
     * Ends all chats being handled by the specified staff member
     *
     * @author Varun Shoor
     * @param int $_staffID The Staff ID to clear chats for
     * @return bool "true" on Success, "false" otherwise
     */
    public static function EndAllChatsByStaff($_staffID)
    {
        $_SWIFT = SWIFT::GetInstance();

        // Get a list of all the ACTIVE chats being handled by this staff member
        $_chatObjectIDList = array();
        $_SWIFT->Database->Query("SELECT chatobjectid FROM " . TABLE_PREFIX . "chatobjects WHERE chatstatus IN('" . self::CHAT_INCHAT . "', '" . self::CHAT_INCOMING . "') AND staffid = '" . $_staffID . "'");
        while ($_SWIFT->Database->NextRecord()) {
            $_chatObjectIDList[] = $_SWIFT->Database->Record["chatobjectid"];
        }

        // Now fetch a list of ACTIVE staff<>staff chats
        $_SWIFT->Database->Query("SELECT chatobjectid, staffid, userid FROM " . TABLE_PREFIX . "chatobjects WHERE chatstatus IN('" . self::CHAT_INCHAT . "', '" . self::CHAT_INCOMING . "') AND chattype = '" . self::CHATTYPE_STAFF . "'");
        while ($_SWIFT->Database->NextRecord()) {
            if (($_SWIFT->Database->Record["staffid"] == $_staffID || $_SWIFT->Database->Record["userid"] == $_staffID) && !in_array($_SWIFT->Database->Record["chatobjectid"], $_chatObjectIDList)) {
                $_chatObjectIDList[] = $_SWIFT->Database->Record["chatobjectid"];
            }
        }

        foreach ($_chatObjectIDList as $_key => $_val) {
            $_SWIFT_ChatObject = new SWIFT_Chat($_val);
            if ($_SWIFT_ChatObject instanceof SWIFT_Chat && $_SWIFT_ChatObject->GetIsClassLoaded() && $_SWIFT_ChatObject->_SWIFT_ChatQueueObject instanceof SWIFT_ChatQueue && $_SWIFT_ChatObject->_SWIFT_ChatQueueObject->GetIsClassLoaded()) {
                $_SWIFT_ChatObject->EndChat(self::CHATEND_STAFF);
            }
        }

        return true;
    }

    /**
     * Processes the department data and returns a presentable data
     *
     * @author Varun Shoor
     * @param array $_departmentArray The raw department array
     * @param array $_staffIDList The Staff ID List
     * @param array $_staffGroupIDList The Staff Group ID List
     * @return array|bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    static private function ProcessDepartmentStatusData($_departmentArray, $_staffIDList, $_staffGroupIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_processedDepartmentArray = $_departmentIDList = array();

        if ($_departmentArray['departmenttype'] != 'public') {
            return false;
        }

        $_staffAssignCache = $_SWIFT->Cache->Get('staffassigncache');
        $_groupAssignCache = $_SWIFT->Cache->Get('groupassigncache');

        $_processedDepartmentArray[$_departmentArray['departmentid']]['departmentid'] = $_departmentArray['departmentid'];
        $_processedDepartmentArray[$_departmentArray['departmentid']]['title'] = $_departmentArray['title'];
        $_processedDepartmentArray[$_departmentArray['departmentid']]['displaytitle'] = $_departmentArray['title'];
        $_processedDepartmentArray[$_departmentArray['departmentid']]['isonline'] = false;
        $_processedDepartmentArray[$_departmentArray['departmentid']]['issub'] = false;

        if (_is_array($_departmentArray['subdepartments'])) {
            foreach ($_departmentArray['subdepartments'] as $_key => $_val) {
                if ($_val['departmenttype'] == SWIFT_Department::DEPARTMENT_PRIVATE) {
                    continue;
                }

                $_processedDepartmentArray[$_val['departmentid']]['departmentid'] = $_val['departmentid'];
                $_processedDepartmentArray[$_val['departmentid']]['title'] = $_val['title'];
                $_processedDepartmentArray[$_val['departmentid']]['displaytitle'] = '  |- ' . $_val['title'];
                $_processedDepartmentArray[$_val['departmentid']]['isonline'] = false;
                $_processedDepartmentArray[$_val['departmentid']]['issub'] = true;
            }
        }

        foreach ($_processedDepartmentArray as $_key => $_val) {
            if (_is_array($_staffIDList)) {
                foreach ($_staffIDList as $_staffID) {
                    if (!isset($_staffAssignCache[$_staffID]) || !_is_array($_staffAssignCache[$_staffID])) {
                        continue;
                    }

                    if (in_array($_key, $_staffAssignCache[$_staffID])) {
                        $_processedDepartmentArray[$_key]['isonline'] = true;

                        // Mark parent department as online too, if this is a child department
                        if (!empty($_val['parentdepartmentid'])) {
                            $_processedDepartmentArray[$_val['parentdepartmentid']]['isonline'] = true;
                        }
                    }
                }
            }

            if (_is_array($_staffGroupIDList)) {
                foreach ($_staffGroupIDList as $_staffGroupID) {
                    if (!isset($_groupAssignCache[$_staffGroupID]) || !_is_array($_groupAssignCache[$_staffGroupID])) {
                        continue;
                    }

                    if (in_array($_key, $_groupAssignCache[$_staffGroupID])) {
                        $_processedDepartmentArray[$_key]['isonline'] = true;

                        // Mark parent department as online too, if this is a child department
                        if (!empty($_val['parentdepartmentid'])) {
                            $_processedDepartmentArray[$_val['parentdepartmentid']]['isonline'] = true;
                        }
                    }
                }
            }
        }

        return $_processedDepartmentArray;
    }

    /**
     * Returns an array of online/offline departments
     *
     * @author Varun Shoor
     * @param array $_filterDepartmentIDList The Filter Department ID
     * @param bool $_isPhone (OPTIONAL) Whether its a phone request
     * @param int $_userGroupID (OPTIONAL) The User Group ID to filter by
     * @return array array(online, offline) Container Array on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function GetDepartmentStatus($_filterDepartmentIDList, $_isPhone = false, $_userGroupID = 0)
    {
        $_SWIFT = SWIFT::GetInstance();

        /*
         * BUG FIX - Abhishek Mittal
         *
         * SWIFT-692: Restricting Chat Departments to a User Group does not work
         *
         * Comments: Set logged in user group ID
         */
        $_clientSessionID = $_SWIFT->Cookie->Get('sessionid' . SWIFT_Interface::INTERFACE_CLIENT);

        if (!empty($_clientSessionID)) {
            $_Session = SWIFT_Session::RetrieveSession($_clientSessionID, new SWIFT_Interface(SWIFT_Interface::INTERFACE_CLIENT));

            if ($_Session instanceof SWIFT_Session && $_Session->GetProperty('typeid') && $_Session->GetProperty('sessiontype') == SWIFT_Interface::INTERFACE_CLIENT) {
                $_User = new SWIFT_User($_Session->GetProperty('typeid'));
                $_userGroupID = $_User->GetProperty('usergroupid');
            }
        }

        $_userGroupDepartmentIDList = array();
        if (!empty($_userGroupID)) {
            $_userGroupDepartmentIDList = SWIFT_UserGroupAssign::RetrieveListOnUserGroup($_userGroupID, SWIFT_UserGroupAssign::TYPE_DEPARTMENT);
        }

        // First get the online departments
        $_staffIDList = $_staffGroupIDList = array();

        // Get the id of staff members and their groups that are online first
        $_SWIFT->Database->Query("SELECT staff.staffid, staff.groupassigns, staffgroup.staffgroupid, sessions.status, sessions.phonestatus FROM " . TABLE_PREFIX . "sessions AS sessions
            LEFT JOIN " . TABLE_PREFIX . "staff AS staff ON (sessions.typeid = staff.staffid)
            LEFT JOIN " . TABLE_PREFIX . "staffgroup AS staffgroup ON (staff.staffgroupid = staffgroup.staffgroupid)
            WHERE sessions.sessiontype IN ('" . SWIFT_Interface::INTERFACE_WINAPP . "') AND sessions.lastactivity >= '" . (DATENOW - 180) . "';");
        while ($_SWIFT->Database->NextRecord()) {
            if (($_isPhone == false && $_SWIFT->Database->Record["status"] != SWIFT_Session::STATUS_ONLINE) || ($_isPhone == true && $_SWIFT->Database->Record["phonestatus"] != SWIFT_Session::PHONESTATUS_AVAILABLE)) {
                continue;
            }

            $_staffIDList[] = $_SWIFT->Database->Record['staffid'];
            if ($_SWIFT->Database->Record['groupassigns'] == '1') {
                $_staffGroupIDList[] = $_SWIFT->Database->Record['staffgroupid'];
            }
        }

        /**
         * ###############################################
         * TODO: IMPLEMENT ADDITION OF $_DEPARTMENTID WHEN USERGROUP FILTERING IS IMPLEMENTED
         * ###############################################
         */

        // Now get all the departments that are assigned to either the given staffgroups or directly to staff member
        $_parentDepartmentIDList = $_departmentContainer = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "departments
            WHERE departmentapp = '" . APP_LIVECHAT . "' AND parentdepartmentid = '0' ORDER BY displayorder ASC");
        while ($_SWIFT->Database->NextRecord()) {
            if (!empty($_userGroupID) && $_SWIFT->Database->Record['uservisibilitycustom'] == '1' && !in_array($_SWIFT->Database->Record['departmentid'], $_userGroupDepartmentIDList)) {
                continue;
            }

            $_departmentContainer[$_SWIFT->Database->Record['departmentid']] = $_SWIFT->Database->Record;
            $_liveChatDepartmentTitleLanguage = "";
            try {
                $_liveChatDepartmentTitleLanguage = $_SWIFT->Language->GetLinked(SWIFT_LanguagePhraseLinked::TYPE_DEPARTMENT, $_SWIFT->Database->Record['departmentid']);
            } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {

            }

            if (!empty($_liveChatDepartmentTitleLanguage)) {
                $_departmentContainer[$_SWIFT->Database->Record['departmentid']]['title'] = text_to_html_entities($_liveChatDepartmentTitleLanguage);
            }

            $_departmentContainer[$_SWIFT->Database->Record['departmentid']]['subids'] = array();
            $_departmentContainer[$_SWIFT->Database->Record['departmentid']]['subdepartments'] = array();
            $_parentDepartmentIDList[] = $_SWIFT->Database->Record['departmentid'];
        }

        if (_is_array($_parentDepartmentIDList)) {
            $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "departments
                WHERE parentdepartmentid IN(" . BuildIN($_parentDepartmentIDList) . ") ORDER BY displayorder ASC", 2);
            $_subDepartmentIndex = 0;
            while ($_SWIFT->Database->NextRecord(2)) {
                if (!empty($_userGroupID) && $_SWIFT->Database->Record['uservisibilitycustom'] == '1' && !in_array($_SWIFT->Database->Record['departmentid'], $_userGroupDepartmentIDList)) {
                    continue;
                }

                $_departmentContainer[$_SWIFT->Database->Record2['parentdepartmentid']]['subids'][$_subDepartmentIndex] = $_SWIFT->Database->Record2['departmentid'];
                $_departmentContainer[$_SWIFT->Database->Record2['parentdepartmentid']]['subdepartments'][$_subDepartmentIndex] = $_SWIFT->Database->Record2;
                $_liveChatSubDepartmentTitleLanguage = "";
                try {
                    $_liveChatSubDepartmentTitleLanguage = $_SWIFT->Language->GetLinked(SWIFT_LanguagePhraseLinked::TYPE_DEPARTMENT, $_SWIFT->Database->Record2['departmentid']);
                } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {

                }
                if (!empty($_liveChatSubDepartmentTitleLanguage)) {
                    $_departmentContainer[$_SWIFT->Database->Record2['parentdepartmentid']]['subdepartments'][$_subDepartmentIndex]['title'] = text_to_html_entities($_liveChatSubDepartmentTitleLanguage);
                }

                $_subDepartmentIndex++;
            }
        }

        // Process the array and create presentable output
        $_onlineDepartments = $_offlineDepartments = array();

        /**
         * BUG FIX - Saurabh Sinha
         *
         * SWIFT-1222: Department is showing 'online', even it is not assigned to staff
         *
         * Comments: The inner foreach block has been added in the parent foreach. Previously the online condition was only checked for the parent department and if the condition was satisfied then all the sub-departments
         * in the array were assigned in the Online array even if they were actually supposed to be sent to the Offline array.
         */

        foreach ($_departmentContainer as $_departmentKey => $_departmentVal) {
            $_processedDepartmentData = self::ProcessDepartmentStatusData($_departmentVal, $_staffIDList, $_staffGroupIDList);

            if (_is_array($_processedDepartmentData)) {
                foreach ($_processedDepartmentData as $_processedDepartmentKey => $_processedDepartmentValue) {
                    if (_is_array($_processedDepartmentValue)) {
                        if ($_processedDepartmentValue['isonline']) {
                            $_onlineDepartments[] = $_processedDepartmentValue;
                        } else {
                            $_offlineDepartments[] = $_processedDepartmentValue;
                        }
                    }
                }
            }
        }

        // If we are supposed to filter the departments then we do that now..
        if (_is_array($_filterDepartmentIDList)) {
            foreach ($_onlineDepartments as $_key => $_val) {
                if (!in_array($_val['departmentid'], $_filterDepartmentIDList)) {
                    unset($_onlineDepartments[$_key]);
                }
            }

            foreach ($_offlineDepartments as $_key => $_val) {
                if (!in_array($_val['departmentid'], $_filterDepartmentIDList)) {
                    unset($_offlineDepartments[$_key]);
                }
            }
        }

        return array('online' => $_onlineDepartments, 'offline' => $_offlineDepartments);
    }

    /**
     * Retrieves the Chat Object from a chatsessionid
     *
     * @author Varun Shoor
     * @param string $_chatSessionID The Chat Session ID
     * @return mixed "SWIFT_Chat" object on Success, "false" otherwise
     */
    public static function GetChatObjectFromSession($_chatSessionID)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_chatSession = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "sessions AS sessions WHERE sessions.sessionid = '" . $_SWIFT->Database->Escape($_chatSessionID) . "'");
        if (empty($_chatSession['sessionid'])) {
            return false;
        }

        $_SWIFT_ChatObject = false;
        try {
            $_SWIFT_ChatObject = new SWIFT_Chat($_chatSession['typeid']);
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
        }
        if (!$_SWIFT_ChatObject instanceof SWIFT_Chat || !$_SWIFT_ChatObject->GetIsClassLoaded()) {
            return false;
        }

        return $_SWIFT_ChatObject;
    }

    /**
     * Get Chat Status
     *
     * @author Varun Shoor
     * @return mixed "chatstatus" on Success, "false" otherwise
     * @throws SWIFT_Chat_Exception If the Class is not Loaded
     */
    public function GetChatStatus()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Chat_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        return $this->_chatObject['chatstatus'];
    }

    /**
     * Updates the Round Robin Timeline
     *
     * @author Varun Shoor
     * @param int $_staffID The Staff ID
     * @param string $_staffName The Staff Name
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Chat_Exception If the Class is not Loaded
     */
    public function UpdateRoundRobinTimeline($_staffID = null, $_staffName = '')
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Chat_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        if (!empty($_staffID) && !empty($_staffName)) {
            SWIFT_ChatHits::Insert($this, $_staffID, $this->GetProperty('userfullname'), $this->GetProperty('useremail'), false);

            $this->UpdatePool('staffid', $_staffID);
            $this->UpdatePool('staffname', $_staffName);
        }

        $this->UpdatePool('roundrobinhits', ((int)$this->GetProperty('roundrobinhits') + 1));
        $this->UpdatePool('roundrobintimeline', DATENOW);

        $this->ProcessUpdatePool();

        return true;
    }

    /**
     * Returns the URL prefix for chatting after respecting user's http/https chat preferences
     * this actually only provides the protocol, domain and base of the url; for http://foo/support/admin/index.php you'd get http://foo/support/
     *
     * @author Varun Shoor
     * @return string Processed Chat URL
     */
    public static function GetChatURL()
    {
        $_SWIFT = SWIFT::GetInstance();

        return $_SWIFT->Settings->Get('livesupport_usehttps') ? (strpos(SWIFT::Get('swiftpath'), 'http:') === 0 ? 'https:' . substr(SWIFT::Get('swiftpath'), 5) : SWIFT::Get('swiftpath')) : SWIFT::Get('swiftpath');
    }

    /**
     * Returns the number of chat objects that are pending in queue
     *
     * @author Varun Shoor
     * @param array $_ignoreVisitorSessionIDList The Visitor Session ID List to Ignore
     * @return mixed Pending Chat Count on Success, "false" otherwise
     */
    public static function GetTotalPendingChatQueue($_ignoreVisitorSessionIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_timeLine = DATENOW - 60;

        $_totalQueue = $_SWIFT->Database->QueryFetch("SELECT COUNT(*) as totalitems FROM " . TABLE_PREFIX . "sessions AS sessions
            LEFT JOIN " . TABLE_PREFIX . "chatobjects AS chatobjects ON (sessions.typeid = chatobjects.chatobjectid)
            WHERE chatobjects.chatstatus = '" . self::CHAT_INCOMING . "' AND chatobjects.visitorsessionid NOT IN(" . BuildIN($_ignoreVisitorSessionIDList) . ")
            AND sessions.sessiontype = '" . SWIFT_Interface::INTERFACE_CHAT . "' AND sessions.lastactivity >= '" . ($_timeLine) . "'");

        return (int)($_totalQueue["totalitems"]);
    }

    /**
     * Returns the number of pending chat objects started before the given time frame (that are 'ahead of me' in the queue)
     *
     * @author Jamie Edwards
     * @param array $_ignoreVisitorSessionIDList The Visitor Session ID List to Ignore
     * @param int $_chatStartedTime Time the chat request was made (time to count before)
     * @param int $_departmentId ID of the department the chat request was made under
     * @return mixed Chat count on success, "false" on failure
     */
    public static function GetTotalChatsAhead($_ignoreVisitorSessionIDList, $_chatStartedTime, $_departmentId)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_totalQueue = $_SWIFT->Database->QueryFetch("
            SELECT COUNT(*) as totalitems FROM " . TABLE_PREFIX . "sessions AS sessions
            LEFT JOIN " . TABLE_PREFIX . "chatobjects AS chatobjects ON (sessions.typeid = chatobjects.chatobjectid)
            WHERE chatobjects.chatstatus = '" . self::CHAT_INCOMING . "'
            AND chatobjects.visitorsessionid NOT IN(" . BuildIN($_ignoreVisitorSessionIDList) . ")
            AND sessions.sessiontype = '" . SWIFT_Interface::INTERFACE_CHAT . "'
            AND chatobjects.dateline <= '" . ($_chatStartedTime) . "'
            AND chatobjects.departmentid = '" . $_departmentId . "'
        ");

        return (int)($_totalQueue["totalitems"]);
    }

    /**
     * Flush Inactive Chats
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public static function FlushInactive()
    {
        $_SWIFT = SWIFT::GetInstance();

        //chatstatus, dateline, lastpostactivity

        // We wait for 10 minutes since chat created.. AND/OR no lastpostactivity since 10 minutes.. AND those sessions have been inactive for more than 10 minutes..
        $_flushDateline = DATENOW - 1800;
        $_flushLastPostActivity = DATENOW - 1800;
        $_sessionThreshold = DATENOW - 120;

        $_inChatInactiveChatObjectIDList = array();

        $_SWIFT->Database->Query("SELECT chatobjects.chatobjectid AS chatobjectid, sessions.lastactivity AS lastactivity
            FROM " . TABLE_PREFIX . "chatobjects AS chatobjects
            LEFT JOIN " . TABLE_PREFIX . "sessions AS sessions ON (chatobjects.chatsessionid = sessions.sessionid)

            WHERE chatobjects.chatstatus IN('" . self::CHAT_INCHAT . "', '" . self::CHAT_INCOMING . "')
                AND chatobjects.dateline < '" . ($_flushDateline) . "'
                AND (chatobjects.lastpostactivity = '0' OR chatobjects.lastpostactivity < '" . ($_flushLastPostActivity) . "')");
        while ($_SWIFT->Database->NextRecord()) {
            if ($_SWIFT->Database->Record['lastactivity'] < $_sessionThreshold) {
                $_inChatInactiveChatObjectIDList[] = $_SWIFT->Database->Record['chatobjectid'];
            }
        }

        if (!count($_inChatInactiveChatObjectIDList)) {
            return false;
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'chatobjects', array('chatstatus' => self::CHAT_TIMEOUT), 'UPDATE', "chatobjectid IN (" . BuildIN($_inChatInactiveChatObjectIDList) . ")");

        return true;
    }

    /**
     * Email this chat conversation
     *
     * @author Varun Shoor
     * @param array $_emailList The list of emails to send this chat conversation to
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Chat_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function Email($_emailList, $_emailSubject = '', $_emailNotes = '', $_overrideDepartmentName = false)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Chat_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (!_is_array($_emailList)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $_departmentCache = $this->Cache->Get('departmentcache');
        $_staffCache = $this->Cache->Get('staffcache');

        $this->Language->Queue('default', SWIFT_LanguageEngine::TYPE_DB);
        $this->Language->Queue('livechatclient', SWIFT_LanguageEngine::TYPE_DB);
        $this->Language->LoadQueue(SWIFT_LanguageEngine::TYPE_DB);

        $_chatDataArray = $this->GetConversationArray($_overrideDepartmentName);

        $this->Load->Library('Mail:Mail');
        $_chatDepartment = $this->Language->Get('na');
        if (isset($_departmentCache[$this->GetProperty('departmentid')])) {
            $_chatDepartment = $_departmentCache[$this->GetProperty('departmentid')]['title'];
        }

        $this->Template->Assign('_chatObject', $this->_chatObject);
        $this->Template->Assign('_chatDepartment', $_chatDepartment);
        $this->Template->Assign('_chatFullName', $this->GetProperty('userfullname'));
        $this->Template->Assign('_chatEmail', $this->GetProperty('useremail'));
        $this->Template->Assign('_chatSubject', $this->GetProperty('subject'));
        $this->Template->Assign('_chatStaff', $_staffCache[$this->GetProperty('staffid')]['fullname']);
        $this->Template->Assign('_chatConversation', $_chatDataArray);

        $this->Template->Assign('_emailNotes', $_emailNotes);
        $this->Template->Assign('_emailNotesHTML', nl2br($_emailNotes));

        $_textEmailContents = $this->Template->Get('livechat_email_text', SWIFT_TemplateEngine::TYPE_DB);
        $_htmlEmailContents = $this->Template->Get('livechat_email_html', SWIFT_TemplateEngine::TYPE_DB);

        $this->Mail->SetFromField($this->Settings->Get('general_returnemail'), SWIFT::Get('companyname'));

        foreach ($_emailList as $_key => $_val) {
            if ($_key == 0) {
                $this->Mail->SetToField($_val);
            } else {
                $this->Mail->AddCC($_val);
            }
        }

        if (empty($_emailSubject)) {
            $_emailSubject = sprintf($this->Language->Get('livechat_emailsubject'), SWIFT::Get('companyname'), SWIFT_Date::Get(SWIFT_Date::TYPE_DATE));
        }

        $this->Mail->SetSubjectField($_emailSubject);

        $this->Mail->SetDataText($_textEmailContents);
        $this->Mail->SetDataHTML($_htmlEmailContents);

        $this->Mail->SendMail();

        return true;
    }

    /**
     * Retrieve the conversation array
     *
     * @author Varun Shoor
     * @return mixed "_chatDataArray" (ARRAY) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetConversationArray($_overrideDepartmentName = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_departmentCache = $this->Cache->Get('departmentcache');

        $_chatDataContainer = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "chatdata WHERE chatobjectid = '" . $this->GetChatObjectID() . "'");
        if (!$_chatDataContainer || !isset($_chatDataContainer['chatdataid']) || empty($_chatDataContainer['chatdataid'])) {
            throw new SWIFT_Chat_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $_chatDataArray = mb_unserialize($_chatDataContainer['contents']);
        foreach ($_chatDataArray as $_key => $_val) {
            if ($_val['actiontype'] != SWIFT_ChatQueue::CHATACTION_SYSTEM && $_val['actiontype'] != SWIFT_ChatQueue::CHATACTION_SYSTEMMESSAGE &&
                $_val['actiontype'] != SWIFT_ChatQueue::CHATACTION_MESSAGE && $_val['actiontype'] != SWIFT_ChatQueue::CHATACTION_CODE) {
                unset($_chatDataArray[$_key]);

                continue;
            }

            // Process the message type for templates
            if ($_val['type'] == SWIFT_ChatQueue::MESSAGE_CLIENT) {
                $_chatDataArray[$_key]['msgtype'] = 'client';
            } else if ($_val['type'] == SWIFT_ChatQueue::MESSAGE_STAFF) {
                $_chatDataArray[$_key]['msgtype'] = 'staff';
            } else if ($_val['type'] == SWIFT_ChatQueue::MESSAGE_SYSTEM) {
                $_chatDataArray[$_key]['msgtype'] = 'system';

                $_val['message'] = strip_tags($_val['message']);
            }
            //Do not override the client's name, should hide only the staff names.
            if ($_overrideDepartmentName == true && $this->Settings->Get('ls_depname') == '1' && $_val['type'] == SWIFT_ChatQueue::MESSAGE_STAFF) {
                $_chatDataArray[$_key]['name'] = '';
                if (isset($_departmentCache[$this->GetProperty('departmentid')])) {
                    $_chatDataArray[$_key]['name'] = $_departmentCache[$this->GetProperty('departmentid')]['title'];
                }
            } else {
                /* Bug Fix : Saloni Dhall
                 *
                 * SWIFT-4232 : Security issue (medium)
                 *
                 * Comments : Item 'name' being used in templates cause vulnerability.
                 */
                $_chatDataArray[$_key]['name'] = $_SWIFT->Input->SanitizeForXSS($_chatDataArray[$_key]['name']);
            }

            $_chatDataArray[$_key]['timestamp'] = SWIFT_Date::Get(SWIFT_Date::TYPE_CUSTOM, $_val['dateline'], $this->Settings->Get('livechat_timestampformat'));
            $_chatDataArray[$_key]['messageoriginal'] = $_val['message'];

            if ($_val['base64'] == '1') {
                $_chatDataArray[$_key]['message'] = base64_decode($_val['message']);
                $_chatDataArray[$_key]['messagehtml'] = str_replace("\n", '<BR />', preg_replace('#(\r\n|\r|\n)#s', "\n", htmlspecialchars(base64_decode($_val['message']))));
            } else {
                if (_is_array($_chatDataArray[$_key]['message'])) {
                    $_val['message'] = $_chatDataArray[$_key]['message'][0];
                }
                $_chatDataArray[$_key]['message'] = $_val['message'];
                $_chatDataArray[$_key]['messagehtml'] = str_replace("\n", '<BR />', preg_replace('#(\r\n|\r|\n)#s', "\n", htmlspecialchars($_val['message'])));
            }
        }

        return $_chatDataArray;
    }

    /**
     * Rebuild the chat counter cache
     *
     * @author Varun Shoor
     * @param array|false $_departmentIDList (OPTIONAL) The custom departmentid list to restrict counting to
     * @return bool "true" on Success, "false" otherwise
     */
    public static function RebuildCache($_departmentIDList = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (self::$_rebuildCacheExecuted) {
            return true;
        }

        self::$_rebuildCacheExecuted = true;

        $_chatCountCache = $_SWIFT->Cache->Get('chatcountcache');

        if (!_is_array($_chatCountCache)) {
            $_chatCountCache = array();
        }

        if (_is_array($_departmentIDList)) {
            $_SWIFT->Database->Query("SELECT departmentid, chatstatus, COUNT(*) AS totalitems, MAX(dateline) AS dateline FROM " . TABLE_PREFIX . "chatobjects GROUP BY departmentid, chatstatus HAVING departmentid IN (" . BuildIN($_departmentIDList) . ")");
        } else {
            $_chatCountCache = array();
            $_SWIFT->Database->Query("SELECT departmentid, chatstatus, COUNT(*) AS totalitems, MAX(dateline) AS dateline FROM " . TABLE_PREFIX . "chatobjects GROUP BY departmentid, chatstatus");
        }

        if (!isset($_chatCountCache['status'])) {
            $_chatCountCache['status'] = array();
        }

        foreach (array(self::CHAT_NOANSWER, self::CHAT_TIMEOUT, self::CHAT_INCHAT, self::CHAT_INCOMING, self::CHAT_ENDED) as $_key => $_val) {
            if (!isset($_chatCountCache['status'][$_val])) {
                $_chatCountCache['status'][$_val] = array();
                $_chatCountCache['status'][$_val]['totalitems'] = 0;
                $_chatCountCache['status'][$_val]['dateline'] = 0;
            }
        }

        while ($_SWIFT->Database->NextRecord()) {
            if (!isset($_chatCountCache[$_SWIFT->Database->Record['departmentid']])) {
                $_chatCountCache[$_SWIFT->Database->Record['departmentid']] = array();
            }

            if (!isset($_chatCountCache[$_SWIFT->Database->Record['departmentid']]['totalitems'])) {
                $_chatCountCache[$_SWIFT->Database->Record['departmentid']]['totalitems'] = 0;
            }

            if (!isset($_chatCountCache[$_SWIFT->Database->Record['departmentid']]['dateline'])) {
                $_chatCountCache[$_SWIFT->Database->Record['departmentid']]['dateline'] = 0;
            }

            $_chatCountCache[$_SWIFT->Database->Record['departmentid']]['totalitems'] += (int)($_SWIFT->Database->Record['totalitems']);
            if ($_SWIFT->Database->Record['dateline'] > $_chatCountCache[$_SWIFT->Database->Record['departmentid']]['dateline']) {
                $_chatCountCache[$_SWIFT->Database->Record['departmentid']]['dateline'] = (int)($_SWIFT->Database->Record['dateline']);
            }

            $_chatCountCache[$_SWIFT->Database->Record['departmentid']][$_SWIFT->Database->Record['chatstatus']] = array();
            $_chatCountCache[$_SWIFT->Database->Record['departmentid']][$_SWIFT->Database->Record['chatstatus']]['totalitems'] = (int)($_SWIFT->Database->Record['totalitems']);
            $_chatCountCache[$_SWIFT->Database->Record['departmentid']][$_SWIFT->Database->Record['chatstatus']]['dateline'] = (int)($_SWIFT->Database->Record['dateline']);

            // Individual Status Records
            $_chatCountCache['status'][$_SWIFT->Database->Record['chatstatus']]['totalitems'] += (int)($_SWIFT->Database->Record['totalitems']);

            if ($_SWIFT->Database->Record['dateline'] > $_chatCountCache['status'][$_SWIFT->Database->Record['chatstatus']]['dateline']) {
                $_chatCountCache['status'][$_SWIFT->Database->Record['chatstatus']]['dateline'] = (int)($_SWIFT->Database->Record['dateline']);
            }
        }

        $_SWIFT->Cache->Update('chatcountcache', $_chatCountCache);

        return true;
    }

    /**
     * Retrieve the processed chat id (RANDOM/INT)
     *
     * @author Varun Shoor
     * @return mixed "_processedChatID" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetProcessedChatID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        /*
         * ###############################################
         * TODO: Add the relevant code to return integer/mask depending upons etting
         * ###############################################
         */

        $_processedChatID = $this->GetProperty('chatobjectmaskid');

        return $_processedChatID;
    }

    /**
     * Retrieve the chat object history
     *
     * @author Varun Shoor
     * @return mixed "_chatHistoryContainer" (ARRAY) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RetrieveHistory()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_userID = -1;
        if ($this->GetProperty('userid') != '0') {
            $_userID = $this->GetProperty('userid');
        }

        $_userEmail = $this->GetProperty('useremail');

        $_chatHistoryContainer = array();

        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "chatobjects
            WHERE userid = '" . $_userID . "'" . IIF(!empty($_userEmail), " OR useremail = '" . $this->Database->Escape($_userEmail) . "'"));
        while ($this->Database->NextRecord()) {
            // We dont dispatch the current chat object in history
            if ($this->Database->Record['chatobjectid'] == $this->GetChatObjectID()) {
                continue;
            }

            $_chatHistoryContainer[$this->Database->Record['chatobjectid']] = $this->Database->Record;
        }

        return $_chatHistoryContainer;
    }

    /**
     * Delete a list of chat objects
     *
     * @author Varun Shoor
     * @param array $_chatObjectIDList The Chat Object ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_chatObjectIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_chatObjectIDList)) {
            return false;
        }

        $_finalChatObjectIDList = array();
        $_finalText = '';
        $_index = 1;

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "chatobjects WHERE chatobjectid IN (" . BuildIN($_chatObjectIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_finalChatObjectIDList[] = $_SWIFT->Database->Record['chatobjectid'];

            $_finalText .= $_index . '. ' . text_to_html_entities($_SWIFT->Database->Record['userfullname']) . IIF(!empty($_SWIFT->Database->Record['subject']), ' (' . htmlspecialchars($_SWIFT->Database->Record['subject']) . ')') . '<BR />';

            $_index++;
        }

        if (!count($_finalChatObjectIDList)) {
            return false;
        }

        SWIFT::Info(sprintf($_SWIFT->Language->Get('titledelchat'), count($_finalChatObjectIDList)), $_SWIFT->Language->Get('msgdelchat') . '<BR />' . $_finalText);

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "chatobjects WHERE chatobjectid IN (" . BuildIN($_finalChatObjectIDList) . ")");
        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "chatdata WHERE chatobjectid IN (" . BuildIN($_finalChatObjectIDList) . ")");

        // Search engine index
        $eng = new SWIFT_SearchEngine();
        $eng->DeleteList($_finalChatObjectIDList, SWIFT_SearchEngine::TYPE_CHAT);

        SWIFT_ChatNote::DeleteOnChat($_finalChatObjectIDList);

        SWIFT_ChatVariable::DeleteListOnChat($_finalChatObjectIDList);

        SWIFT_ChatTextData::DeleteOnChat($_finalChatObjectIDList);

        self::RebuildCache();

        return true;
    }

    /**
     * Retrieve the Dashboard Container Array
     *
     * @author Varun Shoor
     * @return mixed The Dashboard Container Array on Success, "false" otherwise
     */
    public static function RetrieveDashboardContainer()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_SWIFT_StaffObject = $_SWIFT->Staff;
        if (!$_SWIFT_StaffObject instanceof SWIFT_Staff || !$_SWIFT_StaffObject->GetIsClassLoaded()) {
            throw new SWIFT_Staff_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_timeLine = $_SWIFT_StaffObject->GetProperty('lastvisit');
        $_countContainer = $_SWIFT->Database->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "chatobjects
            WHERE dateline > '" . (int)($_timeLine) . "' ORDER BY chatobjectid DESC");
        $_totalRecordCount = 0;
        if (isset($_countContainer['totalitems'])) {
            $_totalRecordCount = (int)($_countContainer['totalitems']);
        }

        $_chatObjectContainer = array();
        $_SWIFT->Database->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "chatobjects WHERE dateline > '" . (int)($_timeLine) . "' ORDER BY chatobjectid DESC", 30);
        while ($_SWIFT->Database->NextRecord()) {
            $_chatObjectContainer[$_SWIFT->Database->Record['chatobjectid']] = $_SWIFT->Database->Record;
        }

        return array($_totalRecordCount, $_chatObjectContainer);
    }

    /**
     * Retrieve the history count for this user based on his userid & email address
     *
     * @author Varun Shoor
     * @return int The History Count
     */
    public static function GetHistoryCount($_userID, $_emailAddress)
    {
        $_SWIFT = SWIFT::GetInstance();

        // Retrieve all email addresses of user
        $_userEmailList = $_emailAddressList = array();

        if (!empty($_emailAddress)) {
            $_emailAddressList[] = $_emailAddress;
        }

        $_userEmailList = SWIFT_UserEmail::RetrieveList($_userID);
        foreach ($_userEmailList as $_emailAddress) {
            if (!in_array($_emailAddress, $_emailAddressList)) {
                $_emailAddressList[] = $_emailAddress;
            }
        }

        // Retrieve count
        $_countContainer = $_SWIFT->Database->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "chatobjects WHERE userid = '" .
            $_userID . "' OR useremail IN (" . BuildIN($_emailAddressList) . ")");

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
    public static function RetrieveHistoryExtended($_userID, $_emailAddress)
    {
        $_SWIFT = SWIFT::GetInstance();

        // Retrieve all email addresses of user
        $_userEmailList = $_emailAddressList = array();

        /*
         * BUG FIX - Ravi Sharma
         *
         * SWIFT-3300 Incorrect chat count in case of Proactive chat or Generate tag with skip user details.
         *
         * Comments: None
         */
        if (!empty($_emailAddress) && IsEmailValid($_emailAddress)) {
            $_emailAddressList[] = $_emailAddress;
        }

        $_userEmailList = SWIFT_UserEmail::RetrieveList($_userID);
        foreach ($_userEmailList as $_emailAddress) {
            if (!in_array($_emailAddress, $_emailAddressList)) {
                $_emailAddressList[] = $_emailAddress;
            }
        }

        if (empty($_userID)) {
            $_userID = '-1';
        }

        $_historyContainer = array();

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "chatobjects WHERE userid = '" . $_userID .
            "' OR useremail IN (" . BuildIN($_emailAddressList) . ") ORDER BY dateline DESC");
        while ($_SWIFT->Database->NextRecord()) {
            $_historyContainer[$_SWIFT->Database->Record['chatobjectid']] = $_SWIFT->Database->Record;
        }

        return $_historyContainer;
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
        if ($_SWIFT_UserObject instanceof SWIFT_User && $_SWIFT_UserObject->GetIsClassLoaded()) {
            $_userID = $_SWIFT_UserObject->GetUserID();
        }

        if (!_is_array($_userEmailList)) {
            $_userEmailList = $_SWIFT_UserObject->GetEmailList();
        }

        $_countContainer = $_SWIFT->Database->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "chatobjects WHERE userid = '" .
            $_userID . "' OR useremail IN (" . BuildIN($_userEmailList) . ")");

        if (isset($_countContainer['totalitems']) && (int)($_countContainer['totalitems']) > 0) {
            return $_countContainer['totalitems'];
        }

        return 0;
    }

    /**
     * Update the GeoIP details for this chat
     *
     * @author Varun Shoor
     * @param string $_ipAddress (OPTIONAL) The IP Address to use, if empty, uses the SWIFT::IP
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function UpdateGeoIP($_ipAddress = null)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_finalIPAddress = SWIFT::Get('IP');
        if (!empty($_ipAddress)) {
            $_finalIPAddress = $_ipAddress;
        }

        $_geoIPContainer = SWIFT_GeoIP::GetIPDetails($_finalIPAddress, array(SWIFT_GeoIP::GEOIP_ISP, SWIFT_GeoIP::GEOIP_ORGANIZATION,
            SWIFT_GeoIP::GEOIP_NETSPEED, SWIFT_GeoIP::GEOIP_CITY));

        if (isset($_geoIPContainer[SWIFT_GeoIP::GEOIP_ISP])) {
            $this->UpdatePool('geoipisp', $_geoIPContainer[SWIFT_GeoIP::GEOIP_ISP]);
        }

        if (isset($_geoIPContainer[SWIFT_GeoIP::GEOIP_ORGANIZATION])) {
            $this->UpdatePool('geoiporganization', $_geoIPContainer[SWIFT_GeoIP::GEOIP_ORGANIZATION]);
        }

        if (isset($_geoIPContainer[SWIFT_GeoIP::GEOIP_NETSPEED])) {
            $this->UpdatePool('geoipnetspeed', $_geoIPContainer[SWIFT_GeoIP::GEOIP_NETSPEED]);
        }

        if (isset($_geoIPContainer[SWIFT_GeoIP::GEOIP_CITY])) {
            $this->UpdatePool('geoipcountry', $_geoIPContainer[SWIFT_GeoIP::GEOIP_CITY]['country']);
            $this->UpdatePool('geoipregion', $_geoIPContainer[SWIFT_GeoIP::GEOIP_CITY]['region']);
            $this->UpdatePool('geoipcity', $_geoIPContainer[SWIFT_GeoIP::GEOIP_CITY]['city']);
            $this->UpdatePool('geoippostalcode', $_geoIPContainer[SWIFT_GeoIP::GEOIP_CITY]['postalcode']);
            $this->UpdatePool('geoiplatitude', $_geoIPContainer[SWIFT_GeoIP::GEOIP_CITY]['latitude']);
            $this->UpdatePool('geoiplongitude', $_geoIPContainer[SWIFT_GeoIP::GEOIP_CITY]['longitude']);
            $this->UpdatePool('geoipmetrocode', $_geoIPContainer[SWIFT_GeoIP::GEOIP_CITY]['metrocode']);
            $this->UpdatePool('geoipareacode', $_geoIPContainer[SWIFT_GeoIP::GEOIP_CITY]['areacode']);
        }

        $this->UpdatePool('hasgeoip', '1');
        $this->ProcessUpdatePool();

        return true;
    }

    /**
     * Retrieve the chat status label
     *
     * @author Varun Shoor
     * @return string The Chat Status Label
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetChatStatusLabel()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_chatStatus = '';
        switch ($this->GetProperty('chatstatus')) {
            case SWIFT_Chat::CHAT_INCOMING:
                $_chatStatus = $this->Language->Get('chatstatusincoming');
                break;

            case SWIFT_Chat::CHAT_INCHAT:
                $_chatStatus = $this->Language->Get('chatstatusinchat');
                break;

            case SWIFT_Chat::CHAT_ENDED:
                $_chatStatus = $this->Language->Get('chatstatusended');
                break;

            case SWIFT_Chat::CHAT_NOANSWER:
                $_chatStatus = $this->Language->Get('chatstatusnoanswer');
                break;

            case SWIFT_Chat::CHAT_TIMEOUT:
                $_chatStatus = $this->Language->Get('chatstatustimeout');
                break;

            default:
                $_chatStatus = $this->Language->Get('na');
                break;
        }

        return $_chatStatus;
    }

    /**
     * Check to see if the given staff can access the chat
     *
     * @author Varun Shoor
     * @param SWIFT_Staff $_SWIFT_StaffObject The SWIFT_Staff Object Pointer
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function CanAccess(SWIFT_Staff $_SWIFT_StaffObject)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (!$_SWIFT_StaffObject instanceof SWIFT_Staff || !$_SWIFT_StaffObject->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_INVALIDDATA);
        }

        $_assignedDepartmentIDList = $_SWIFT_StaffObject->GetAssignedDepartments(APP_LIVECHAT);

        if (in_array($this->GetProperty('departmentid'), $_assignedDepartmentIDList)) {
            return true;
        } else if ($this->GetProperty('departmentid') == '0') {
            return true;
        }

        return false;
    }

    /**
     * Get the processed phone number based on the settings
     *
     * @author Varun Shoor
     * @param int $_countryCode The Country Code
     * @param int $_phoneNumber The Phone Number
     * @return string
     */
    public static function GetProcessedPhoneNumber($_countryCode, $_phoneNumber)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_countryCode = CleanInt($_countryCode);
        $_phoneNumber = CleanInt($_phoneNumber);

        if ($_SWIFT->Settings->Get('ls_activecountrycode') == $_countryCode && $_SWIFT->Settings->Get('ls_ignoreactivecountrycode') == '1') {
            return $_phoneNumber;
        } else if ($_SWIFT->Settings->Get('ls_activecountrycode') == $_countryCode && $_SWIFT->Settings->Get('ls_ignoreactivecountrycode') == '0') {
            return $_countryCode . $_phoneNumber;

        }

        return $_SWIFT->Settings->Get('ls_internationalcallprefix') . $_countryCode . $_phoneNumber;
    }

    /**
     * Update the Call Status
     *
     * @author Varun Shoor
     * @param mixed $_callStatus The Call Status
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function UpdateCallStatus($_callStatus)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UpdatePool('callstatus', (int)($_callStatus));
        $this->ProcessUpdatePool();

        return true;
    }

    /**
     * Get or Create a User ID based on given info
     *
     * @author Varun Shoor
     * @param string $_fullName The User Full Name
     * @param string $_email the User Email
     * @param int $_userGroupID The User Group ID
     * @param int $_languageID (OPTIONAL) The Language ID
     * @param bool $_checkGeoIP (OPTIONAL) Check GeoIP for User
     * @return int The User ID on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If Invalid Data is Provided
     */
    public static function GetOrCreateUserID($_fullName, $_email, $_userGroupID, $_languageID = 0, $_checkGeoIP = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        // User processing.. no user specified?
        $_userIDFromEmail = SWIFT_UserEmail::RetrieveUserIDOnUserEmail($_email);

        $_userID = false;
        if (!empty($_userIDFromEmail)) {
            $_userID = $_userIDFromEmail;
        } else {
            $_SWIFT_UserObject = SWIFT_User::Create($_userGroupID, false, SWIFT_User::SALUTATION_NONE, $_fullName, '', '', true,
                false, array($_email), false, $_languageID, false, false, false, false, false, true, true, $_checkGeoIP);

            $_userID = $_SWIFT_UserObject->GetUserID();
        }

        return $_userID;
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
        $_chatContainer = array();
        $_SWIFT->Database->Query("SELECT chatobjectid FROM " . TABLE_PREFIX . self::TABLE_NAME . "
                                  WHERE userid IN ( " . BuildIN($_secondaryUserIDList, true) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_chatContainer[$_SWIFT->Database->Record['chatobjectid']] = $_SWIFT->Database->Record;
        }
        foreach ($_chatContainer as $_chat) {
            $_Chat = new SWIFT_Chat($_chat['chatobjectid']);
            $_Chat->UpdateUser($_primaryUserID);
        }
        return true;
    }

    /**
     * Updates the User with which the chat is linked
     *
     * @author Abhishek Mittal
     *
     * @param int $_userID
     *
     * @return SWIFT_Chat
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

    /**
     * @author Ravi Sharma <ravi.sharma@kayako.com>
     *
     * @param int $_departmentID
     *
     * @return bool True if success, False otherwise
     */
    public static function IsDepartmentOnline($_departmentID)
    {
        $_departmentStatusContainer = self::GetDepartmentStatus(array($_departmentID));

        foreach ($_departmentStatusContainer['online'] as $_department) {
            if ($_departmentID == $_department['departmentid']) {
                return true;
            }
        }

        return false;
    }
}

