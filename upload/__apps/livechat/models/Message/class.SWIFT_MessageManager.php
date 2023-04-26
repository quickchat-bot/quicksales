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

namespace LiveChat\Models\Message;

use Base\Library\Staff\SWIFT_Staff_Exception;
use Base\Models\GeoIP\SWIFT_GeoIP;
use Base\Models\Staff\SWIFT_Staff;
use LiveChat\Models\Message\SWIFT_Message;
use LiveChat\Models\Message\SWIFT_Message_Exception;
use SWIFT;
use SWIFT_Exception;
use SWIFT_LanguageEngine;
use LiveChat\Models\Message\SWIFT_MessageSurvey;
use SWIFT_Mail;
use SWIFT_Model;
use SWIFT_TemplateEngine;
use Tickets\Library\Ticket\SWIFT_Ticket_Exception;

/**
 * The Live Chat Offline Message Management Class
 *
 * @author Varun Shoor
 * @property SWIFT_Mail $Mail
 */
abstract class SWIFT_MessageManager extends SWIFT_Model
{
    const TABLE_NAME = 'messages';
    const PRIMARY_KEY = 'messageid';

    const TABLE_STRUCTURE = "messageid I PRIMARY AUTO NOTNULL,
                                messagemaskid C(50) DEFAULT '' NOTNULL,
                                dateline I DEFAULT '0' NOTNULL,
                                replydateline I DEFAULT '0' NOTNULL,
                                fullname C(150) DEFAULT '' NOTNULL,
                                email C(150) DEFAULT '' NOTNULL,
                                subject C(255) DEFAULT '' NOTNULL,
                                departmentid I DEFAULT '0' NOTNULL,
                                parentmessageid I DEFAULT '0' NOTNULL,
                                messagestatus I2 DEFAULT '0' NOTNULL,
                                messagetype I2 DEFAULT '0' NOTNULL,
                                messagerating F DEFAULT '0' NOTNULL,
                                chatobjectid I DEFAULT '0' NOTNULL,
                                staffid I DEFAULT '0' NOTNULL,
                                staffname C(255) DEFAULT '' NOTNULL,

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

    const INDEX_1 = 'departmentid, messagestatus';
    const INDEX_2 = 'staffid';
    const INDEX_3 = 'messagestatus, dateline';
    const INDEX_4 = 'messagetype, messagerating';
    const INDEX_5 = 'messagerating';
    const INDEX_6 = 'messagetype, messagestatus, messagerating';
    const INDEX_7 = 'messagemaskid';
    const INDEX_8 = 'dateline';
    const INDEX_9 = 'chatobjectid';
    const INDEX_10 = 'subject(250), fullname(30), email(40)'; // Unified Search
    const INDEX_11 = 'departmentid'; // Unified Search
    const INDEX_12 = 'messagemaskid, departmentid'; // Unified Search


    protected $_dataStore = array();

    static protected $_rebuildCacheExecuted = false;

    // Core Constants
    const MESSAGE_CLIENT = 1;
    const MESSAGE_CLIENTSURVEY = 2;
    const MESSAGE_STAFF = 3;

    const CONTENT_CLIENT = 1;
    const CONTENT_STAFF = 2;

    const STATUS_NEW = 1;
    const STATUS_REPLIED = 2;
    const STATUS_READ = 3;

    public $Mail;


    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param int $_messageID The Message ID
     * @throws SWIFT_Message_Exception If the Record could not be loaded
     */
    public function __construct($_messageID)
    {
        parent::__construct();

        if (!$this->LoadData($_messageID)) {
            throw new SWIFT_Message_Exception('Failed to load Message ID: ' . $_messageID);
        }
    }

    /**
     * Processes the Update Pool Data
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Message_Exception If the Class is not Loaded
     */
    public function ProcessUpdatePool()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        } else if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'messages', $this->GetUpdatePool(), 'UPDATE', "messageid = '" . $this->GetMessageID() . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Message ID
     *
     * @author Varun Shoor
     * @return int
     * @throws SWIFT_Message_Exception If the Class is not Loaded
     */
    public function GetMessageID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Message_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['messageid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param \SWIFT_Data|int $_messageID The Message ID
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_messageID)
    {
        $_dataStore = $this->Database->QueryFetch("SELECT messages.*, messagedata.contents AS contents FROM " . TABLE_PREFIX . "messages AS messages LEFT JOIN " . TABLE_PREFIX . "messagedata AS messagedata ON (messages.messageid = messagedata.messageid AND messagedata.contenttype = '" . (self::CONTENT_CLIENT) . "') WHERE messages.messageid = '" . $_messageID . "'");
        if (isset($_dataStore['messageid']) && !empty($_dataStore['messageid'])) {
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
     * @throws SWIFT_Message_Exception If the Class is not Loaded
     */
    public function GetDataStore()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Message_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore;
    }

    /**
     * Retrieves a Property Value from Data Store
     *
     * @author Varun Shoor
     * @param string $_key The Key Identifier
     * @return mixed Property Data on Success, "false" otherwise
     * @throws SWIFT_Message_Exception If the Class is not Loaded
     */
    public function GetProperty($_key)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Message_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_Message_Exception(SWIFT_INVALIDDATA . ': ' . $_key);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Check to see if its a valid message type
     *
     * @author Varun Shoor
     * @param mixed $_messageType The Message Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidType($_messageType)
    {
        if ($_messageType == self::MESSAGE_CLIENT || $_messageType == self::MESSAGE_CLIENTSURVEY || $_messageType == self::MESSAGE_STAFF) {
            return true;
        }

        return false;
    }

    /**
     * Retrieve the valid content type
     *
     * @author Varun Shoor
     * @param string $_messageType The Message Type
     * @return mixed "CONTENTTYPE" (CONSTANT) on Success, "false" otherwise
     * @throws SWIFT_Message_Exception If Invalid Data is Provided
     */
    public static function GetContentType($_messageType)
    {
        if (!self::IsValidType($_messageType)) {
            throw new SWIFT_Message_Exception(SWIFT_INVALIDDATA);
        }

        switch ($_messageType) {
            case self::MESSAGE_STAFF:
                return self::CONTENT_STAFF;
                break;

            case self::MESSAGE_CLIENT:
                return self::CONTENT_CLIENT;
                break;

            case self::MESSAGE_CLIENTSURVEY:
                return self::CONTENT_CLIENT;
                break;

            default:
                break;
        }

        return false;
    }

    /**
     * Sanitize the Message Rating
     *
     * @author Varun Shoor
     * @param float $_messageRating The Message Rating
     * @return float The Sanitized Message Rating
     */
    public static function SanitizeMessageRating($_messageRating)
    {
        $_messageRating = floatval($_messageRating);

        if ($_messageRating < 0) {
            $_messageRating = 0;
        }

        if ($_messageRating > 5) {
            $_messageRating = 5;
        }

        return floatval($_messageRating);
    }

    /**
     * Retrieve a unique message mask id
     *
     * @author Varun Shoor
     * @return string The Unique Message Mask ID
     */
    public static function GetMessageMaskID()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_uniqueMaskID = GenerateUniqueMask();

        do {
            $_messageMaskID = false;
            $_uniqueMaskID = GenerateUniqueMask();

            $_maskCheckContainer = $_SWIFT->Database->QueryFetch("SELECT messagemaskid FROM " . TABLE_PREFIX . "messages WHERE messagemaskid = '" . $_SWIFT->Database->Escape($_uniqueMaskID) . "'");
            if (!$_maskCheckContainer || !isset($_maskCheckContainer['messagemaskid'])) {
                $_messageMaskID = $_uniqueMaskID;
            }

        } while (empty($_messageMaskID));

        return $_messageMaskID;
    }

    /**
     * Create a New Message
     *
     * @author Varun Shoor
     * @param string $_fullName The Full Name of message creator
     * @param string $_email The Email Address of message creator
     * @param string $_subject The Message Subject
     * @param int $_departmentID The Department ID
     * @param string $_messageContents The Message Contents
     * @param mixed $_messageType The Message Type
     * @param int $_parentMessageID (OPTIONAL) The Parent Message ID
     * @param int $_chatObjectID (OPTIONAL) The Chat Object ID for Survey Message
     * @param int $_messageRating (OPTIONAL) The Message Rating
     * @return mixed "_messageID" (INT) on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function Create($_fullName, $_email, $_subject, $_departmentID, $_messageContents, $_messageType, $_parentMessageID = 0, $_chatObjectID = 0, $_messageRating = 0)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!self::IsValidType($_messageType) || ($_messageType == self::MESSAGE_CLIENTSURVEY && empty($_chatObjectID))) {
            throw new SWIFT_Message_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'messages', array('messagemaskid' => self::GetMessageMaskID(), 'dateline' => DATENOW, 'replydateline' => '0', 'fullname' => $_fullName, 'email' => $_email, 'subject' => $_subject, 'departmentid' => $_departmentID, 'parentmessageid' => $_parentMessageID, 'messagestatus' => self::STATUS_NEW, 'messagetype' => $_messageType, 'staffid' => '0', 'messagerating' => self::SanitizeMessageRating($_messageRating), 'chatobjectid' => $_chatObjectID), 'INSERT');
        $_messageID = $_SWIFT->Database->Insert_ID();
        if (!$_messageID) {
            throw new SWIFT_Message_Exception(SWIFT_CREATEFAILED);
        }

        $_contentType = self::GetContentType($_messageType);

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'messagedata', array('messageid' => $_messageID, 'contenttype' => $_contentType, 'contents' => $_messageContents), 'INSERT');

        self::RebuildCache(array($_departmentID));

        return $_messageID;
    }

    /**
     * Update the Message Record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Message_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function Update()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Message_Exception(SWIFT_CLASSNOTLOADED);
        }

        return true;
    }

    /**
     * Delete the Message record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Message_Exception If the Class is not Loaded
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Message_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($this->GetMessageID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Mark this Message as Read
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function MarkAsRead()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::MarkAsReadList(array($this->GetMessageID()));

        return true;
    }

    /**
     * Delete a list of Message IDs
     *
     * @author Varun Shoor
     * @param array $_messageIDList The Message ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_messageIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_messageIDList)) {
            return false;
        }

        $_finalMessageIDList = array();
        $_index = 1;
        $_finalText = '';

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "messages WHERE messageid IN (" . BuildIN($_messageIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_finalMessageIDList[] = $_SWIFT->Database->Record['messageid'];

            $_finalText .= $_index . '. ' . htmlspecialchars($_SWIFT->Database->Record['subject']) . '<BR />';
            $_index++;
        }

        if (!count($_finalMessageIDList)) {
            return false;
        }

        SWIFT::Info(sprintf($_SWIFT->Language->Get('titlemessagedel'), count($_finalMessageIDList)), $_SWIFT->Language->Get('msgmessagedel') . '<BR />' . $_finalText);

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "messages WHERE messageid IN (" . BuildIN($_finalMessageIDList) . ")");
        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "messagedata WHERE messageid IN (" . BuildIN($_finalMessageIDList) . ")");

        self::RebuildCache();

        return true;
    }

    /**
     * Mark a list of Message IDs as Read
     *
     * @author Varun Shoor
     * @param array $_messageIDList The Message ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function MarkAsReadList($_messageIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_messageIDList)) {
            return false;
        }

        $_finalMessageIDList = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "messages WHERE messageid IN (" . BuildIN($_messageIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_finalMessageIDList[] = $_SWIFT->Database->Record['messageid'];
        }

        if (!count($_finalMessageIDList)) {
            return false;
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'messages', array('messagestatus' => self::STATUS_READ), 'UPDATE', "messageid IN (" . BuildIN($_finalMessageIDList) . ")");

        self::RebuildCache();

        return true;
    }

    /**
     * Rebuild the message counter cache
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

        $_chatMessageCountCache = $_SWIFT->Cache->Get('chatmessagecountcache');

        if (!_is_array($_chatMessageCountCache)) {
            $_chatMessageCountCache = array();
        }

        /*
         * ###############################################
         * DEPARTMENT + MESSAGE TYPE CACHE REBUILD
         * ###############################################
         */
        if (_is_array($_departmentIDList)) {
            $_SWIFT->Database->Query("SELECT departmentid, messagestatus, COUNT(*) AS totalitems, MAX(dateline) AS dateline FROM " . TABLE_PREFIX . "messages GROUP BY departmentid, messagestatus HAVING departmentid IN (" . BuildIN($_departmentIDList) . ")");
        } else {
            $_chatMessageCountCache = array();
            $_SWIFT->Database->Query("SELECT departmentid, messagestatus, COUNT(*) AS totalitems, MAX(dateline) AS dateline FROM " . TABLE_PREFIX . "messages GROUP BY departmentid, messagestatus");
        }

        if (!isset($_chatMessageCountCache['status'])) {
            $_chatMessageCountCache['status'] = array();
        }

        foreach (array(self::STATUS_NEW, self::STATUS_REPLIED, self::STATUS_READ) as $_key => $_val) {
            if (!isset($_chatMessageCountCache['status'][$_val])) {
                $_chatMessageCountCache['status'][$_val] = array();
                $_chatMessageCountCache['status'][$_val]['totalitems'] = 0;
                $_chatMessageCountCache['status'][$_val]['dateline'] = 0;
            }
        }

        while ($_SWIFT->Database->NextRecord()) {
            if (!isset($_chatMessageCountCache[$_SWIFT->Database->Record['departmentid']])) {
                $_chatMessageCountCache[$_SWIFT->Database->Record['departmentid']] = array();
            }

            if (!isset($_chatMessageCountCache[$_SWIFT->Database->Record['departmentid']]['totalitems'])) {
                $_chatMessageCountCache[$_SWIFT->Database->Record['departmentid']]['totalitems'] = 0;
            }

            if (!isset($_chatMessageCountCache[$_SWIFT->Database->Record['departmentid']]['dateline'])) {
                $_chatMessageCountCache[$_SWIFT->Database->Record['departmentid']]['dateline'] = 0;
            }

            $_chatMessageCountCache[$_SWIFT->Database->Record['departmentid']]['totalitems'] += (int)($_SWIFT->Database->Record['totalitems']);
            if ($_SWIFT->Database->Record['dateline'] > $_chatMessageCountCache[$_SWIFT->Database->Record['departmentid']]['dateline']) {
                $_chatMessageCountCache[$_SWIFT->Database->Record['departmentid']]['dateline'] = (int)($_SWIFT->Database->Record['dateline']);
            }

            $_chatMessageCountCache[$_SWIFT->Database->Record['departmentid']][$_SWIFT->Database->Record['messagestatus']] = array();
            $_chatMessageCountCache[$_SWIFT->Database->Record['departmentid']][$_SWIFT->Database->Record['messagestatus']]['totalitems'] = (int)($_SWIFT->Database->Record['totalitems']);
            $_chatMessageCountCache[$_SWIFT->Database->Record['departmentid']][$_SWIFT->Database->Record['messagestatus']]['dateline'] = (int)($_SWIFT->Database->Record['dateline']);

            // Individual Status Records
            $_chatMessageCountCache['status'][$_SWIFT->Database->Record['messagestatus']]['totalitems'] += (int)($_SWIFT->Database->Record['totalitems']);

            if ($_SWIFT->Database->Record['dateline'] > $_chatMessageCountCache['status'][$_SWIFT->Database->Record['messagestatus']]['dateline']) {
                $_chatMessageCountCache['status'][$_SWIFT->Database->Record['messagestatus']]['dateline'] = (int)($_SWIFT->Database->Record['dateline']);
            }
        }

        /*
         * ###############################################
         * SURVEY RATINGS CACHE REBUILD
         * ###############################################
         */

        if (!isset($_chatMessageCountCache['surveys'])) {
            $_chatMessageCountCache['surveys'] = array();
        }

        foreach (array('0.5', '1', '1.5', '2', '2.5', '3', '3.5', '4', '4.5', '5') as $_key => $_val) {
            if (!isset($_chatMessageCountCache['surveys'][$_val])) {
                $_chatMessageCountCache['surveys'][$_val] = array();
                $_chatMessageCountCache['surveys'][$_val]['totalitems'] = 0;
                $_chatMessageCountCache['surveys'][$_val]['dateline'] = 0;
            }
        }

        $_SWIFT->Database->Query("SELECT messagerating, COUNT(*) AS totalitems, MAX(dateline) AS dateline FROM " . TABLE_PREFIX . "messages WHERE messagetype = '" . (self::MESSAGE_CLIENTSURVEY) . "' AND messagestatus = '" . (self::STATUS_NEW) . "' GROUP BY messagerating");
        while ($_SWIFT->Database->NextRecord()) {
            $_chatMessageCountCache['surveys'][$_SWIFT->Database->Record['messagerating']] = array();
            $_chatMessageCountCache['surveys'][$_SWIFT->Database->Record['messagerating']]['totalitems'] = (int)($_SWIFT->Database->Record['totalitems']);
            $_chatMessageCountCache['surveys'][$_SWIFT->Database->Record['messagerating']]['dateline'] = (int)($_SWIFT->Database->Record['dateline']);
        }

        $_SWIFT->Cache->Update('chatmessagecountcache', $_chatMessageCountCache);

        return true;
    }

    /**
     * Retrieve the relevant message object
     *
     * @author Varun Shoor
     * @param int $_messageID The Message ID
     * @return mixed Message/MessageSurvey Object on Success, "false" otherwise
     */
    public static function RetrieveMessageObject($_messageID)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_messageContainer = $_SWIFT->Database->QueryFetch("SELECT messagetype FROM " . TABLE_PREFIX . "messages WHERE messageid = '" . $_messageID . "'");
        if (!$_messageContainer || !isset($_messageContainer['messagetype']) || !self::IsValidType($_messageContainer['messagetype'])) {
            return false;
        }

        switch ($_messageContainer['messagetype']) {
            case self::MESSAGE_CLIENT:
                return new SWIFT_Message($_messageID);
                break;

            case self::MESSAGE_STAFF:
                return new SWIFT_Message($_messageID);
                break;

            case self::MESSAGE_CLIENTSURVEY:
                return new SWIFT_MessageSurvey($_messageID);
                break;

            default:
                break;
        }

        return false;
    }

    /**
     * Retrieve the Message Data Contents
     *
     * @author Varun Shoor
     * @return bool The Message Contents on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetContents()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->GetProperty('contents');
    }

    /**
     * Retrieve the Message Data Contents for staff reply
     *
     * @author Varun Shoor
     * @return bool The Message Contents on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetStaffReplyContents()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_messageDataContainer = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "messagedata WHERE messageid = '" . $this->GetMessageID() . "' AND contenttype = '" . (self::CONTENT_STAFF) . "'");
        if ($_messageDataContainer && isset($_messageDataContainer['contents']) && !empty($_messageDataContainer['contents'])) {
            return $_messageDataContainer['contents'];
        }

        return false;
    }

    /**
     * Dispatch the reply to the message and mark it as replied
     *
     * @author Varun Shoor
     * @param int $_staffID The Staff ID of replier
     * @param string $_staffName The Staff Name of replier
     * @param string $_fromEmail The From Email Address
     * @param string $_subject The Message Subject
     * @param string $_replyContents The Reply Contents
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function Reply($_staffID, $_staffName, $_fromEmail, $_subject, $_replyContents)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_staffID) || empty($_staffName) || empty($_fromEmail) || empty($_subject) || empty($_replyContents)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->UpdatePool('staffid', $_staffID);
        $this->UpdatePool('staffname', $_staffName);
        $this->UpdatePool('messagestatus', self::STATUS_REPLIED);
        $this->UpdatePool('replydateline', DATENOW);
        $this->ProcessUpdatePool();

        // Add to Message Data
        $this->Database->AutoExecute(TABLE_PREFIX . 'messagedata', array('messageid' => $this->GetMessageID(), 'contenttype' => self::CONTENT_STAFF, 'contents' => $_replyContents), 'INSERT');

        /*
         * ###############################################
         * MAIL DISPATCHING ROUTINES
         * ###############################################
         */
        $this->Load->Library('Mail:Mail');

        // Load the phrases from the database..
        $this->Language->Queue('default', SWIFT_LanguageEngine::TYPE_DB);
        $this->Language->LoadQueue(SWIFT_LanguageEngine::TYPE_DB);

        $this->Template->Assign('_contentsText', $_replyContents);
        $this->Template->Assign('_contentsHTML', nl2br($_replyContents));

        $_textEmailContents = $this->Template->Get('email_text', SWIFT_TemplateEngine::TYPE_DB);
        $_htmlEmailContents = $this->Template->Get('email_html', SWIFT_TemplateEngine::TYPE_DB);

        $this->Mail->SetFromField($_fromEmail, $_staffName);

        $this->Mail->SetToField($this->GetProperty('email'));

        $this->Mail->SetSubjectField($_subject);

        $this->Mail->SetDataText($_textEmailContents);
        $this->Mail->SetDataHTML($_htmlEmailContents);

        $this->Mail->SendMail();

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
        $_countContainer = $_SWIFT->Database->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "messages
            WHERE dateline > '" . (int)($_timeLine) . "' ORDER BY messageid DESC", 30);
        $_totalRecordCount = 0;
        if (isset($_countContainer['totalitems'])) {
            $_totalRecordCount = (int)($_countContainer['totalitems']);
        }

        $_messageContainer = array();
        $_SWIFT->Database->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "messages WHERE dateline > '" . (int)($_timeLine) . "' ORDER BY messageid DESC", 30);
        while ($_SWIFT->Database->NextRecord()) {
            $_messageContainer[$_SWIFT->Database->Record['messageid']] = $_SWIFT->Database->Record;
        }

        return array($_totalRecordCount, $_messageContainer);
    }

    /**
     * Update the GeoIP details for this chat message
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
     * Check to see if the given staff can access the chat message
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
     * Retrieve the Message Status Label
     *
     * @author Varun Shoor
     * @return string The Label
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetMessageStatusLabel()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_messageStatus = $this->Language->Get('na');
        switch ($this->GetProperty('messagestatus')) {
            case SWIFT_Message::STATUS_NEW:
                $_messageStatus = $this->Language->Get('mh_msgnew');
                break;

            case SWIFT_Message::STATUS_REPLIED:
                $_messageStatus = $this->Language->Get('mh_msgreplied');
                break;

            case SWIFT_Message::STATUS_READ:
                $_messageStatus = $this->Language->Get('mh_msgread');
                break;

            default:
                break;
        }

        return $_messageStatus;
    }

    /**
     * Retrieve the Message Type Label
     *
     * @author Varun Shoor
     * @return string The Message Type Label
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetMessageTypeLabel()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_messageType = $this->Language->Get('na');
        switch ($this->GetProperty('messagetype')) {
            case SWIFT_Message::MESSAGE_CLIENT:
                $_messageType = $this->Language->Get('mh_msgclient');
                break;

            case SWIFT_Message::MESSAGE_STAFF:
                $_messageType = $this->Language->Get('mh_msgstaff');
                break;

            case SWIFT_Message::MESSAGE_CLIENTSURVEY:
                $_messageType = $this->Language->Get('mh_msgclientsurvey');
                break;

            default:
                break;
        }

        return $_messageType;
    }
}
