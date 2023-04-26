<?php
/**
 * ###############################################
 *
 * SWIFT Framework
 * _______________________________________________
 *
 * @author         Varun Shoor
 *
 * @package        SWIFT
 * @copyright      Copyright (c) 2001-2012, Kayako
 * @license        http://www.kayako.com/license
 * @link           http://www.kayako.com
 *
 * ###############################################
 */

namespace Parser\Models\EmailQueue;

use Parser\Library\EmailQueue\SWIFT_EmailQueueType;
use Parser\Models\CatchAll\SWIFT_CatchAllRule;
use SWIFT;
use SWIFT_Data;
use SWIFT_DataID;
use SWIFT_DataStore;
use Parser\Models\EmailQueue\SWIFT_EmailQueueMailbox;
use Parser\Models\EmailQueue\SWIFT_EmailQueuePipe;
use Parser\Models\EmailQueue\SWIFT_EmailQueueSignature;
use SWIFT_Exception;
use SWIFT_Model;
use Tickets\Library\Ticket\SWIFT_Ticket_Exception;

/**
 * Email Queue Management Class
 *
 * @property SWIFT_EmailQueueSignature $EmailQueueSignature
 *
 * @author Varun Shoor
 */
abstract class SWIFT_EmailQueue extends SWIFT_Model
{
    const TABLE_NAME = 'emailqueues';
    const PRIMARY_KEY = 'emailqueueid';

    const TABLE_STRUCTURE = "emailqueueid I PRIMARY AUTO NOTNULL,
                                email C(255) DEFAULT '' NOTNULL,
                                type C(30) DEFAULT 'tickets' NOTNULL,
                                fetchtype C(30) DEFAULT 'pipe' NOTNULL,
                                host C(255) DEFAULT '' NOTNULL,
                                port C(10) DEFAULT '' NOTNULL,
                                username C(255) DEFAULT '' NOTNULL,
                                userpassword C(255) DEFAULT '' NOTNULL,
                                customfromname C(200) DEFAULT '' NOTNULL,
                                customfromemail C(255) DEFAULT '' NOTNULL,
                                tickettypeid I DEFAULT '0' NOTNULL,
                                priorityid I DEFAULT '0' NOTNULL,
                                ticketstatusid I DEFAULT '0' NOTNULL,
                                departmentid I DEFAULT '0' NOTNULL,
                                prefix C(30) DEFAULT '' NOTNULL,
                                ticketautoresponder I2 DEFAULT '0' NOTNULL,
                                replyautoresponder I2 DEFAULT '0' NOTNULL,
                                registrationrequired I2 DEFAULT '0' NOTNULL,
                                tgroupid I DEFAULT '0' NOTNULL,
                                forcequeue I2 DEFAULT '1' NOTNULL,
                                leavecopyonserver I2 DEFAULT '0' NOTNULL,
                                usequeuesmtp I2 DEFAULT '0' NOTNULL,
                                smtptype C(20) DEFAULT '' NOTNULL,
                                isenabled I2 DEFAULT '0' NOTNULL,
                                authtype C(20) DEFAULT '' NOTNULL,
                                clientid C(255) DEFAULT '' NOTNULL,
                                clientsecret C(255) DEFAULT '' NOTNULL,
                                authendpoint C(255) DEFAULT '' NOTNULL,
                                tokenendpoint C(255) DEFAULT '' NOTNULL,
                                authscopes C(255) DEFAULT '' NOTNULL,
                                accesstoken C(2000) DEFAULT '' NOTNULL,
                                refreshtoken C(2000) DEFAULT '' NOTNULL,
                                tokenexpiry I DEFAULT '0' NOTNULL,
                                smtphost C(255) DEFAULT '' NOTNULL,
                                smtpport C(255) DEFAULT '' NOTNULL";

    const INDEX_1 = 'email';
    const INDEX_2 = 'email(100), customfromname(100), customfromemail(100)';


    protected $_dataStore = array();

    public $EmailQueueType = false;

    // Core Constants
    const FETCH_PIPE = 'pipe';
    const FETCH_POP3 = 'pop3';
    const FETCH_POP3SSL = 'pop3ssl';
    const FETCH_POP3TLS = 'pop3tls';
    const FETCH_IMAP = 'imap';
    const FETCH_IMAPSSL = 'imapssl';
    const FETCH_IMAPTLS = 'imaptls';

    /**
     * Constructor
     *
     * @author Varun Shoor
     *
     * @param SWIFT_Data $_SWIFT_DataObject The SWIFT_Data Object
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Record could not be loaded
     */
    public function __construct(SWIFT_Data $_SWIFT_DataObject)
    {
        parent::__construct();

        // @codeCoverageIgnoreStart
        if (!$_SWIFT_DataObject instanceof SWIFT_Data || !$_SWIFT_DataObject->GetIsClassLoaded() || !$this->LoadData($_SWIFT_DataObject)) {
            throw new SWIFT_Exception('Failed to load Email Queue Object');
        }
        // @codeCoverageIgnoreEnd

        $this->Load->Model('EmailQueue:EmailQueueSignature', [$this->GetProperty('emailqueueid')], true, false, APP_PARSER);

        $this->EmailQueueType = SWIFT_EmailQueueType::GetFromEmailQueueObject($this);
    }

    /**
     * Destructor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function __destruct()
    {
        // @codeCoverageIgnoreStart
        $this->ProcessUpdatePool();

        parent::__destruct();
        // @codeCoverageIgnoreEnd
    }

    /**
     * Processes the Update Pool Data
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_EmailQueue_Exception If the Class is not Loaded
     */
    public function ProcessUpdatePool()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        } else if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'emailqueues', $this->GetUpdatePool(), 'UPDATE', "emailqueueid = '" .
            (int)($this->GetEmailQueueID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Email Queue ID
     *
     * @author Varun Shoor
     * @return mixed "emailqueueid" on Success, "false" otherwise
     * @throws SWIFT_EmailQueue_Exception If the Class is not Loaded
     */
    public function GetEmailQueueID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_EmailQueue_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['emailqueueid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     *
     * @param SWIFT_Data $_SWIFT_DataObject The SWIFT_Data Object
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If Invalid Data is Provided
     */
    protected function LoadData($_SWIFT_DataObject)
    {
        $_SWIFT = SWIFT::GetInstance();

        // Is it a ID?
        if ($_SWIFT_DataObject instanceof SWIFT_DataID && $_SWIFT_DataObject->GetIsClassLoaded()) {
            $_dataStore = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "emailqueues WHERE emailqueueid = '" . (int)($_SWIFT_DataObject->GetDataID()) . "'");
            if (isset($_dataStore['emailqueueid']) && !empty($_dataStore['emailqueueid'])) {
                $this->_dataStore = $_dataStore;

                return true;
            }

            // Is it a Store?
        } else if ($_SWIFT_DataObject instanceof SWIFT_DataStore && $_SWIFT_DataObject->GetIsClassLoaded()) {
            $this->_dataStore = $_SWIFT_DataObject->GetDataStore();

            if (!isset($this->_dataStore['emailqueueid']) || empty($this->_dataStore['emailqueueid'])) {
                // @codeCoverageIgnoreStart
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
                // @codeCoverageIgnoreEnd
            }

            return true;
        }

        // @codeCoverageIgnoreStart
        throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        // @codeCoverageIgnoreEnd
    }

    /**
     * Returns the Data Store Array
     *
     * @author Varun Shoor
     * @return mixed "_dataStore" Array on Success, "false" otherwise
     * @throws SWIFT_EmailQueue_Exception If the Class is not Loaded
     */
    public function GetDataStore()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_EmailQueue_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore;
    }

    /**
     * Retrieves a Property Value from Data Store
     *
     * @author Varun Shoor
     *
     * @param string $_key The Key Identifier
     *
     * @return mixed Property Data on Success, "false" otherwise
     * @throws SWIFT_EmailQueue_Exception If the Class is not Loaded
     */
    public function GetProperty($_key)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_EmailQueue_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_EmailQueue_Exception(SWIFT_INVALIDDATA);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Check to see if its a valid fetch type
     *
     * @author Varun Shoor
     *
     * @param string $_fetchType The Queue Fetch Type
     *
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidFetchType($_fetchType)
    {
        if ($_fetchType == self::FETCH_PIPE || $_fetchType == self::FETCH_POP3 || $_fetchType == self::FETCH_POP3SSL ||
            $_fetchType == self::FETCH_POP3TLS || $_fetchType == self::FETCH_IMAP || $_fetchType == self::FETCH_IMAPSSL ||
            $_fetchType == self::FETCH_IMAPTLS) {
            return true;
        }

        return false;
    }

    /**
     * Check to see whether email queue exists with the given email address
     *
     * @author Varun Shoor
     *
     * @param string $_email The Email Address to Check
     *
     * @return bool "true" on Success, "false" otherwise
     */
    public static function EmailQueueExistsWithEmail($_email)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_emailQueueContainer = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "emailqueues WHERE email = '" .
            $_SWIFT->Database->Escape($_email) . "'");
        if (!isset($_emailQueueContainer['emailqueueid']) || empty($_emailQueueContainer['emailqueueid'])) {
            // @codeCoverageIgnoreStart
            return false;
            // @codeCoverageIgnoreEnd
        }

        return true;
    }

    /**
     * Retrieve the Email Queue Object based on Email Queue ID
     *
     * @author Varun Shoor
     *
     * @param int $_emailQueueID The Email Queue ID
     *
     * @return SWIFT_EmailQueuePipe|SWIFT_EmailQueueMailbox|bool "true" on Success, "false" otherwise
     */
    public static function Retrieve($_emailQueueID)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_emailQueueContainer = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "emailqueues WHERE emailqueueid = '" .
            $_emailQueueID . "'");
        if (!isset($_emailQueueContainer['emailqueueid']) || empty($_emailQueueContainer['emailqueueid']) ||
            !isset($_emailQueueContainer['fetchtype']) || empty($_emailQueueContainer['fetchtype'])) {
            return false;
        }

        switch ($_emailQueueContainer['fetchtype']) {
            case SWIFT_EmailQueue::FETCH_PIPE:
                return new SWIFT_EmailQueuePipe(new SWIFT_DataStore($_emailQueueContainer));

                break;

            default:
                return new SWIFT_EmailQueueMailbox(new SWIFT_DataStore($_emailQueueContainer));

                break;
        }
    }

    /**
     * Retrieve the Email Queue Object based on Email Queue Data Store
     *
     * @author Varun Shoor
     *
     * @param array $_emailQueueContainer The Email Queue Container
     * @return SWIFT_EmailQueuePipe|SWIFT_EmailQueueMailbox|bool "true" on Success, "false" otherwise
     */
    public static function RetrieveStore($_emailQueueContainer)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!isset($_emailQueueContainer['emailqueueid']) || empty($_emailQueueContainer['emailqueueid']) ||
            !isset($_emailQueueContainer['fetchtype']) || empty($_emailQueueContainer['fetchtype'])) {
            return false;
        }

        switch ($_emailQueueContainer['fetchtype']) {
            case SWIFT_EmailQueue::FETCH_PIPE:
                return new SWIFT_EmailQueuePipe(new SWIFT_DataStore($_emailQueueContainer));

                break;

            default:
                return new SWIFT_EmailQueueMailbox(new SWIFT_DataStore($_emailQueueContainer));

                break;
        }

        return false;
    }

    /**
     * Create a new Email Queue
     *
     * @author Varun Shoor
     *
     * @param string $_queueEmail           The Queue Email Address
     * @param string $_fetchType            The Queue Fetch Type
     * @param SWIFT_EmailQueueType $_EmailQueueTypeObject The Email Queue Type Object Pointer (NEWS/TICKETS)
     * @param string $_queuePrefix          The Queue Prefix
     * @param string $_customFromName       The Custom From Name
     * @param string $_customFromEmail      The Custom From Email
     * @param string $_queueSignature       The Queue Signature
     * @param bool   $_registrationRequired Whether the user should be registered for message acceptance to work
     * @param bool   $_isEnabled            Whether this Queue is Enabled
     * @param bool   $_rebuildCache         Whether the cache should be rebuilt at the end of creation
     *
     * @return mixed "_emailQueueID" (INT) on Success, "false" otherwise
     * @throws SWIFT_EmailQueue_Exception When Invalid Data is Specified or the Object couldnt be created
     */
    protected static function Create($_queueEmail, $_fetchType, $_EmailQueueTypeObject, $_queuePrefix, $_customFromName,
                                     $_customFromEmail, $_queueSignature, $_registrationRequired, $_isEnabled, $_rebuildCache = true)
    {
        $_SWIFT = SWIFT::GetInstance();

        // @codeCoverageIgnoreStart
        if (empty($_queueEmail) || !self::IsValidFetchType($_fetchType) || !$_EmailQueueTypeObject->GetIsClassLoaded()) {
            throw new SWIFT_EmailQueue_Exception('Invalid Data Specified');
        }

        $_valueContainer = $_EmailQueueTypeObject->GetValueContainer();
        if (!is_array($_valueContainer)) {
            throw new SWIFT_EmailQueue_Exception('Invalid Value Container for Email Queue Type');
        }
        // @codeCoverageIgnoreEnd

        $_fieldsContainer = array('email' => $_queueEmail, 'fetchtype' => $_fetchType, 'customfromname' => $_customFromName,
            'customfromemail' => $_customFromEmail, 'type' => $_EmailQueueTypeObject->GetQueueType(), 'prefix' => $_queuePrefix,
            'registrationrequired' => (int)($_registrationRequired), 'isenabled' => (int)($_isEnabled));

        foreach ($_valueContainer as $_key => $_val) {
            // @codeCoverageIgnoreStart
            $_fieldsContainer[$_key] = $_val;
            // @codeCoverageIgnoreEnd
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'emailqueues', $_fieldsContainer, 'INSERT');
        $_emailQueueID = $_SWIFT->Database->Insert_ID();
        if (!$_emailQueueID) {
            // @codeCoverageIgnoreStart
            throw new SWIFT_EmailQueue_Exception('Unable to create Email Queue');
            // @codeCoverageIgnoreEnd
        }

        SWIFT_EmailQueueSignature::Create($_emailQueueID, $_queueSignature);

        if ($_rebuildCache) {
            self::RebuildCache();
        }

        return $_emailQueueID;
    }

    /**
     * Update the Email Queue Record
     *
     * @author Varun Shoor
     *
     * @param string $_queueEmail The Queue Email Address
     * @param SWIFT_EmailQueueType $_EmailQueueTypeObject The Email Queue Type Object Pointer (NEWS/TICKETS)
     * @param string $_queuePrefix The Queue Prefix
     * @param string $_customFromName The Custom From Name
     * @param string $_customFromEmail The Custom From Email
     * @param string $_queueSignature The Queue Signature
     * @param bool $_registrationRequired Whether the user should be registered for message acceptance to work
     * @param bool $_isEnabled Whether this Queue is Enabled
     * @param bool $_rebuildCache Whether the cache should be rebuilt at the end of update
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_EmailQueue_Exception If the Class is not Loaded or if Invalid Data is Specified
     * @throws \Parser\Library\EmailQueue\SWIFT_EmailQueue_Exception
     */
    protected function Update($_queueEmail, $_EmailQueueTypeObject, $_queuePrefix, $_customFromName, $_customFromEmail,
                              $_queueSignature, $_registrationRequired, $_isEnabled, $_rebuildCache = true)
    {
        // @codeCoverageIgnoreStart
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_EmailQueue_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_queueEmail) || !$_EmailQueueTypeObject->GetIsClassLoaded()) {
            throw new SWIFT_EmailQueue_Exception('Invalid Data Specified');
        }

        $_valueContainer = $_EmailQueueTypeObject->GetValueContainer();
        if (!is_array($_valueContainer)) {
            throw new SWIFT_EmailQueue_Exception('Invalid Value Container for Email Queue Type');
        }

        foreach ($_valueContainer as $_key => $_val) {
            $this->UpdatePool($_key, $_val);
        }
        // @codeCoverageIgnoreEnd

        $this->UpdatePool('email', $_queueEmail);
        $this->UpdatePool('prefix', $_queuePrefix);
        $this->UpdatePool('registrationrequired', (int)($_registrationRequired));
        $this->UpdatePool('isenabled', $_isEnabled);
        $this->UpdatePool('customfromname', $_customFromName);
        $this->UpdatePool('customfromemail', $_customFromEmail);

        $this->ProcessUpdatePool();

        $this->EmailQueueSignature->Update($_queueSignature);

        if ($_rebuildCache) {
            self::RebuildCache();
        }

        return true;
    }

    /**
     * Update the Fetch Type Value
     *
     * @author Varun Shoor
     *
     * @param string $_fetchType The Fetch Type
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function UpdateFetchType($_fetchType)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!self::IsValidFetchType($_fetchType)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->UpdatePool('fetchtype', $_fetchType);
        $this->ProcessUpdatePool();

        return true;
    }

    /**
     * Delete the Email Queue record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_EmailQueue_Exception If the Class is not Loaded
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_EmailQueue_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($this->GetEmailQueueID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Retrieve the Email Queue Signature
     *
     * @author Varun Shoor
     * @return bool|string "_emailQueueSignature['contents']" (STRING) on Success, "" OR "false" otherwise
     * @throws SWIFT_EmailQueue_Exception If the Class is not Loaded
     */
    public function GetSignature()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_EmailQueue_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_emailQueueSignature = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "queuesignatures WHERE emailqueueid = '" .
            (int)($this->GetEmailQueueID()) . "'");
        if (!$_emailQueueSignature || !isset($_emailQueueSignature['contents']) || empty($_emailQueueSignature['contents'])) {
            return '';
        }

        return $_emailQueueSignature['contents'];
    }

    /**
     * Delete a list of Email Queue's
     *
     * @author Varun Shoor
     *
     * @param array $_emailQueueIDList The Email Queue ID List
     *
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_emailQueueIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_emailQueueIDList)) {
            return false;
        }

        $_finalEmailQueueIDList = array();
        $_index = 1;

        $_finalText = '';
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "emailqueues WHERE emailqueueid IN (" . BuildIN($_emailQueueIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_finalText .= $_index . '. ' . htmlspecialchars($_SWIFT->Database->Record['email']) . ' (' .
                htmlspecialchars(strtoupper($_SWIFT->Database->Record['fetchtype'])) . ')<br />';
            $_index++;

            $_finalEmailQueueIDList[] = $_SWIFT->Database->Record['emailqueueid'];
        }

        SWIFT::Info(sprintf($_SWIFT->Language->Get('titledelqueues'), count($_finalEmailQueueIDList)), $_SWIFT->Language->Get('msgdelqueues') .
            '<br />' . $_finalText);

        if (!count($_finalEmailQueueIDList)) {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "emailqueues WHERE emailqueueid IN (" . BuildIN($_finalEmailQueueIDList) . ")");

        SWIFT_EmailQueueSignature::DeleteOnEmailQueue($_finalEmailQueueIDList);
        SWIFT_CatchAllRule::DeleteOnEmailQueue($_finalEmailQueueIDList);

        /*
         * ==============================
         * TODO: Update when Tickets Class is done
         * ==============================
         */
        //$ADODB->AutoExecute(TABLE_PREFIX.'tickets', array('emailqueueid' => '0'), 'UPDATE', "emailqueueid IN (". buildIN($emailqueueidarray) .")");

        self::RebuildCache();

        return true;
    }

    /**
     * Enable a list of Email Queue's
     *
     * @author Varun Shoor
     *
     * @param array $_emailQueueIDList The Email Queue ID List
     *
     * @return bool "true" on Success, "false" otherwise
     */
    public static function EnableList($_emailQueueIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_emailQueueIDList)) {
            return false;
        }

        $_finalEmailQueueIDList = array();
        $_index = 1;

        $_finalText = '';
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "emailqueues WHERE emailqueueid IN (" . BuildIN($_emailQueueIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            if ($_SWIFT->Database->Record['isenabled'] == '1') {
                continue;
            }

            $_finalText .= $_index . '. ' . htmlspecialchars($_SWIFT->Database->Record['email']) . ' (' .
                htmlspecialchars(strtoupper($_SWIFT->Database->Record['fetchtype'])) . ')<br />';
            $_index++;

            $_finalEmailQueueIDList[] = $_SWIFT->Database->Record['emailqueueid'];
        }

        if (!count($_finalEmailQueueIDList)) {
            return false;
        }

        SWIFT::Info(sprintf($_SWIFT->Language->Get('titleenablequeues'), count($_finalEmailQueueIDList)), $_SWIFT->Language->Get('msgenablequeues') .
            '<br />' . $_finalText);

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'emailqueues', array('isenabled' => '1'), 'UPDATE', "emailqueueid IN (" .
            BuildIN($_finalEmailQueueIDList) . ")");

        self::RebuildCache();

        return true;
    }

    /**
     * Disable a list of Email Queue's
     *
     * @author Varun Shoor
     *
     * @param array $_emailQueueIDList The Email Queue ID List
     *
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DisableList($_emailQueueIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_emailQueueIDList)) {
            return false;
        }

        $_finalEmailQueueIDList = array();
        $_index = 1;

        $_finalText = '';
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "emailqueues WHERE emailqueueid IN (" . BuildIN($_emailQueueIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            if ($_SWIFT->Database->Record['isenabled'] == '0') {
                continue;
            }

            $_finalText .= $_index . '. ' . htmlspecialchars($_SWIFT->Database->Record['email']) . ' (' .
                htmlspecialchars(strtoupper($_SWIFT->Database->Record['fetchtype'])) . ')<br />';
            $_index++;

            $_finalEmailQueueIDList[] = $_SWIFT->Database->Record['emailqueueid'];
        }

        if (!count($_finalEmailQueueIDList)) {
            return false;
        }

        SWIFT::Info(sprintf($_SWIFT->Language->Get('titledisablequeues'), count($_finalEmailQueueIDList)), $_SWIFT->Language->Get('msgdisablequeues') .
            '<br />' . $_finalText);

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'emailqueues', array('isenabled' => '0'), 'UPDATE', "emailqueueid IN (" .
            BuildIN($_finalEmailQueueIDList) . ")");

        self::RebuildCache();

        return true;
    }

    /**
     * Rebuild the Email Queue Cache
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public static function RebuildCache()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_cache = array();

        $_cache['pointer'] = array();
        $_cache['list'] = array();
        $_cache['pipe'] = array();

        $_index = 0;
        $_SWIFT->Database->Query("SELECT *, emailqueues.emailqueueid AS demailqueueid FROM " . TABLE_PREFIX .
            "emailqueues AS emailqueues LEFT JOIN " . TABLE_PREFIX . "queuesignatures AS queuesignatures ON
                    (emailqueues.emailqueueid = queuesignatures.emailqueueid) ORDER BY emailqueues.emailqueueid ASC", 3);
        while ($_SWIFT->Database->NextRecord(3)) {
            $_index++;

            $_cache['pointer'][strtolower($_SWIFT->Database->Record3['email'])] = $_SWIFT->Database->Record3['emailqueueid'];

            $_cache['list'][$_SWIFT->Database->Record3['demailqueueid']] = $_SWIFT->Database->Record3;
            $_cache['list'][$_SWIFT->Database->Record3['demailqueueid']]['emailqueueid'] = $_SWIFT->Database->Record3['demailqueueid'];

            if ($_SWIFT->Database->Record3['fetchtype'] == 'pipe' && $_SWIFT->Database->Record3['isenabled'] == '1') {
                $_cache['pipe'][$_SWIFT->Database->Record3['demailqueueid']] = $_SWIFT->Database->Record3;
            }
        }

        $_SWIFT->Cache->Update('queuecache', $_cache);

        return true;
    }

    /**
     * Returns a boolean indicating whether or not the prefix passed is acceptable for use.
     *
     * @author Varun Shoor
     *
     * @param string $_queuePrefix The Queue Prefix
     *
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidQueuePrefix($_queuePrefix)
    {
        return preg_match('@^[\w ]+$@i', $_queuePrefix);
    }

    /**
     * Retrieve the IMAP Function Argument based on Fetch Type
     *
     * @author Varun Shoor
     *
     * @param mixed $_fetchType The Fetch Type
     *
     * @return mixed "_fetchTypeArgument" (STRING) on Success, "false" otherwise
     * @throws SWIFT_EmailQueue_Exception If Invalid Data is Provided
     */
    public static function GetIMAPArgument($_fetchType)
    {
        if (!self::IsValidFetchType($_fetchType)) {
            throw new SWIFT_EmailQueue_Exception(SWIFT_INVALIDDATA);
        }

        switch ($_fetchType) {
            case self::FETCH_IMAP:
                $_fetchTypeArgument = 'imap/notls';
                break;
            case self::FETCH_IMAPSSL:
                $_fetchTypeArgument = 'imap/ssl/novalidate-cert/notls';
                break;
            case self::FETCH_IMAPTLS:
                $_fetchTypeArgument = 'imap/ssl/novalidate-cert';
                break;
            case self::FETCH_POP3SSL:
                $_fetchTypeArgument = 'pop3/ssl/novalidate-cert/notls';
                break;
            case self::FETCH_POP3TLS:
                $_fetchTypeArgument = 'pop3/ssl/novalidate-cert';
                break;
            default:
                $_fetchTypeArgument = "pop3/notls";
                break;
        }

        return $_fetchTypeArgument;
    }

    /**
     * Retrieve emails of all the Email Queues
     *
     * @author Mansi Wason <mansi.wason@kayako.com>
     *
     * @return mixed "_emailList"
     */
    public static function RetrieveEmailofAllEmailQueues()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_emailList = [];

        $_SWIFT->Database->Query("SELECT email FROM " . TABLE_PREFIX . "emailqueues");

        while ($_SWIFT->Database->NextRecord()) {
            $_emailList[] = $_SWIFT->Database->Record['email'];
        }

        return $_emailList;
    }
}

?>
