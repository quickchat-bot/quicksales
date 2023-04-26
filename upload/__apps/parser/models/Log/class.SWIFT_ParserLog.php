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

namespace Parser\Models\Log;

use Base\Library\Staff\SWIFT_Staff_Exception;
use Base\Models\Staff\SWIFT_Staff;
use Parser\Models\Log\SWIFT_Log_Exception;
use SWIFT;
use SWIFT_Date;
use SWIFT_Exception;
use SWIFT_Mail;
use SWIFT_Model;

/**
 * The Parser Log Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_ParserLog extends SWIFT_Model
{
    const TABLE_NAME = 'parserlogs';
    const PRIMARY_KEY = 'parserlogid';

    const TABLE_STRUCTURE = "parserlogid I PRIMARY AUTO NOTNULL,
                                typeid I DEFAULT '0' NOTNULL,
                                dateline I DEFAULT '0' NOTNULL,
                                emailqueueid I DEFAULT '0' NOTNULL,
                                logtype C(20) DEFAULT 'failure' NOTNULL,
                                subject C(255) DEFAULT '' NOTNULL,
                                fromemail C(255) DEFAULT '' NOTNULL,
                                toemail C(255) DEFAULT '' NOTNULL,
                                size I DEFAULT '0' NOTNULL,
                                description X NOTNULL,
                                parsetimetaken F DEFAULT '0.0' NOTNULL,

                                responsetype C(20) DEFAULT 'ticket' NOTNULL,
                                ticketpostid I DEFAULT '0' NOTNULL,
                                ticketmaskid C(20) DEFAULT '' NOTNULL,
                                messageid C(100) DEFAULT '' NOTNULL";

    const INDEX_1 = 'ticketpostid';
    const INDEX_2 = 'dateline';
    const INDEX_3 = 'emailqueueid';
    const INDEX_4 = 'logtype, dateline';


    protected $_dataStore = array();

    // Core Constants
    const TYPE_SUCCESS = 'success';
    const TYPE_FAILURE = 'failure';

    const TYPE_ID_BAN_EMAIL = 1;

    /**
     * Constructor
     *
     * @author Varun Shoor
     *
     * @param int $_parserLogID The Parser Log ID
     *
     * @throws SWIFT_Exception
     * @throws \Parser\Models\Log\SWIFT_Log_Exception If the Record could not be loaded
     */
    public function __construct($_parserLogID)
    {
        parent::__construct();

        if (!$this->LoadData($_parserLogID)) {
            // @codeCoverageIgnoreStart
            throw new SWIFT_Log_Exception('Failed to load Parser Log ID: ' . $_parserLogID);
            // @codeCoverageIgnoreEnd
        }
    }

    /**
     * Processes the Update Pool Data
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     * @throws \Parser\Models\Log\SWIFT_Log_Exception If the Class is not Loaded
     */
    public function ProcessUpdatePool()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        } else if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'parserlogs', $this->GetUpdatePool(), 'UPDATE', "parserlogid = '" .
            (int)($this->GetParserLogID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Parser Log ID
     *
     * @author Varun Shoor
     * @return mixed "parserlogid" on Success, "false" otherwise
     * @throws SWIFT_Log_Exception If the Class is not Loaded
     */
    public function GetParserLogID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Log_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['parserlogid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     *
     * @param \SWIFT_Data|int $_parserLogID The Parser Log ID
     *
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_parserLogID)
    {
        $_dataStore = $this->Database->QueryFetch("SELECT parserlogs.*, parserlogdata.contents AS contents FROM " . TABLE_PREFIX .
            "parserlogs AS parserlogs LEFT JOIN " . TABLE_PREFIX . "parserlogdata AS parserlogdata ON
                    (parserlogs.parserlogid = parserlogdata.parserlogid) WHERE parserlogs.parserlogid = '" . $_parserLogID . "'");
        if (isset($_dataStore['parserlogid']) && !empty($_dataStore['parserlogid'])) {
            if (!isset($_dataStore['contents'])) {
                $_dataStore['contents'] = '';
            }

            $this->_dataStore = $_dataStore;

            return true;
        }

        // @codeCoverageIgnoreStart
        return false;
        // @codeCoverageIgnoreEnd
    }

    /**
     * Returns the Data Store Array
     *
     * @author Varun Shoor
     * @return mixed "_dataStore" Array on Success, "false" otherwise
     * @throws SWIFT_Log_Exception If the Class is not Loaded
     */
    public function GetDataStore()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Log_Exception(SWIFT_CLASSNOTLOADED);
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
     * @throws SWIFT_Log_Exception If the Class is not Loaded
     */
    public function GetProperty($_key)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Log_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_Log_Exception(SWIFT_INVALIDDATA);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Check to see if its a valid log type
     *
     * @author Varun Shoor
     *
     * @param string $_logType The Log Type
     *
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidLogType($_logType)
    {
        if ($_logType == self::TYPE_SUCCESS || $_logType == self::TYPE_FAILURE) {
            return true;
        }

        return false;
    }

    /**
     * Create a new Parser Log
     *
     * @author Varun Shoor
     *
     * @param mixed $_logType The Log Type Result
     * @param int $_emailQueueID The Email Queue ID
     * @param int $_typeID The Type ID
     * @param string $_subject The Email Subject
     * @param string $_fromEmail The From Email Address
     * @param string $_toEmail The Destination Email Address
     * @param int $_size The Data Size
     * @param string $_description The Description for this Entry
     * @param string $_logContents
     * @param float $_parseTimeTaken The Time Taken to Parse this Email
     * @param array $_extendedArguments The Extended Arguments for this Log Entry
     * @param string $_messageID The email Message ID
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     * @throws \Parser\Models\Log\SWIFT_Log_Exception If Invalid Data is Provided
     */
    public static function Create($_logType, $_emailQueueID, $_typeID, $_subject, $_fromEmail, $_toEmail, $_size, $_description, $_logContents,
                                  $_parseTimeTaken, $_extendedArguments = array(), $_messageID = '')
    {
        $_SWIFT = SWIFT::GetInstance();

        //if ($_SWIFT->Settings->Get('pr_enablelog') != '1')
        //{
        //    continue;
        //}

        if (!self::IsValidLogType($_logType) || !is_array($_extendedArguments)) {
            throw new SWIFT_Log_Exception('Invalid Data Provided');
        }

        $_fieldsContainer = array('emailqueueid' => $_emailQueueID, 'typeid' => $_typeID, 'dateline' => DATENOW,
            'logtype' => $_logType, 'subject' => $_subject, 'fromemail' => $_fromEmail, 'toemail' => $_toEmail, 'size' => floatval($_size),
            'description' => $_description, 'parsetimetaken' => floatval($_parseTimeTaken), 'messageid' => $_messageID);

        $_fieldsContainer = array_merge($_fieldsContainer, $_extendedArguments);


        /**
         * BUG FIX - Ravi Sharma <ravi.sharma@kayako.com>
         *
         * SWIFT-1175 Admin notification if an email cannot be fetched from an email queue
         */
        // Email notification on failure
        if ($_logType == self::TYPE_FAILURE && $_SWIFT->Settings->Get('pr_enablelog_notification') == '1' && $_typeID != self::TYPE_ID_BAN_EMAIL) {
            self::DispatchNotfication($_fieldsContainer);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'parserlogs', $_fieldsContainer, 'INSERT');
        $_parserLogID = $_SWIFT->Database->Insert_ID();
        if (!$_parserLogID) {
            throw new SWIFT_Log_Exception('Unable to Load Parser Log Record');
        }

        $_logLength = mb_strlen($_logContents);
        $_maxSize = $_SWIFT->Settings->Get('pr_maxlogsize') * 1024;
        if ($_logLength > $_maxSize) {
            $_logContents = '';
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'parserlogdata', array('parserlogid' => (int)($_parserLogID),
            'contents' => $_logContents), 'INSERT');

        return $_parserLogID;
    }

    /**
     * Delete the Parser Log record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     * @throws \Parser\Models\Log\SWIFT_Log_Exception If the Class is not Loaded
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Log_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($this->GetParserLogID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of Parser Logs
     *
     * @author Varun Shoor
     *
     * @param array $_parserLogIDList The Parser Log ID List
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function DeleteList($_parserLogIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_parserLogIDList)) {
            return false;
        }

        $_finalParserLogIDList = array();
        $_index = 1;

        $_finalText = '';
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "parserlogs WHERE parserlogid IN (" . BuildIN($_parserLogIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_finalParserLogIDList[] = $_SWIFT->Database->Record['parserlogid'];

            $_finalText .= $_index . '. ' . htmlspecialchars($_SWIFT->Database->Record['subject']) . ' (' .
                $_SWIFT->Language->Get('queuefromemail') . ': ' . htmlspecialchars($_SWIFT->Database->Record['fromemail']) . ') <br />';
            $_index++;
        }

        SWIFT::Info(sprintf($_SWIFT->Language->Get('titleparserlogdel'), count($_finalParserLogIDList)), $_SWIFT->Language->Get('msgparserlogdel') .
            '<br />' . $_finalText);

        if (!count($_finalParserLogIDList)) {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "parserlogs WHERE parserlogid IN (" . BuildIN($_finalParserLogIDList) . ")");
        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "parserlogdata WHERE parserlogid IN (" . BuildIN($_finalParserLogIDList) . ")");

        return true;
    }

    /**
     * Cleanup all old logs according to settings
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function CleanUp()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_dateThreshold = DATENOW - (86400 * $_SWIFT->Settings->Get('pr_logchurndays'));

        $_parserLogIDList = array();

        $_SWIFT->Database->Query("SELECT parserlogid FROM " . TABLE_PREFIX . "parserlogs WHERE dateline < '" . (int)($_dateThreshold) . "'");
        while ($_SWIFT->Database->NextRecord()) {
            $_parserLogIDList[] = (int)($_SWIFT->Database->Record['parserlogid']);
        }

        if (!count($_parserLogIDList)) {
            return false;
        }

        self::DeleteList($_parserLogIDList);

        return true;
    }

    /**
     * Displays the Dashboard Widget
     *
     * @author Varun Shoor
     * @return mixed "_parserLogContainer" (ARRAY) on Success, "false" otherwise
     * @throws SWIFT_Exception
     * @throws SWIFT_Staff_Exception
     */
    public static function GetDashboardContainer()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_SWIFT_StaffObject = $_SWIFT->Staff;
        if (!$_SWIFT_StaffObject instanceof SWIFT_Staff || !$_SWIFT_StaffObject->GetIsClassLoaded()) {
            // @codeCoverageIgnoreStart
            throw new SWIFT_Staff_Exception(SWIFT_CLASSNOTLOADED);
            // @codeCoverageIgnoreEnd
        }

        $_emailQueueCache = $_SWIFT->Cache->Get('queuecache');

        $_parserLogContainer = array();

        $_timeLine = $_SWIFT_StaffObject->GetProperty('lastvisit');

        $_countContainer = $_SWIFT->Database->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "parserlogs
            WHERE logtype = '" . self::TYPE_FAILURE . "' AND dateline > '" . (int)($_timeLine) . "'");
        $_totalRecordCount = 0;
        if (isset($_countContainer['totalitems'])) {
            $_totalRecordCount = (int)($_countContainer['totalitems']);
        }

        $_parserLogsContainer = array();
        $_SWIFT->Database->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "parserlogs
            WHERE logtype = '" . self::TYPE_FAILURE . "' AND dateline > '" . (int)($_timeLine) . "'
            ORDER BY parserlogid DESC", 7);
        while ($_SWIFT->Database->NextRecord()) {
            $_parserLogsContainer[$_SWIFT->Database->Record['parserlogid']] = $_SWIFT->Database->Record;
        }

        if (count($_parserLogsContainer)) {
            foreach ($_parserLogsContainer as $_parserLogID => $_parserLog) {
                $_displayText = $_SWIFT->Language->Get('logfailure');
                $_displayClass = 'blocknotecounterred';

                $_titleSuffix = '';
                if (isset($_emailQueueCache['list'][$_parserLog['emailqueueid']])) {
                    // @codeCoverageIgnoreStart
                    $_titleSuffix = ' (' . htmlspecialchars($_emailQueueCache['list'][$_parserLog['emailqueueid']]['email']) . ')';
                    // @codeCoverageIgnoreEnd
                }

                $_finalText = '<b>' . $_SWIFT->Language->Get('plogfrom') . ' ' . htmlspecialchars($_parserLog['fromemail']) . '<br />';
                $_finalText .= '<b>' . $_SWIFT->Language->Get('plogto') . ' ' . htmlspecialchars($_parserLog['toemail']) . '<br />';
                $_finalText .= '<b>' . $_SWIFT->Language->Get('plogsize') . ' ' . FormattedSize($_parserLog['size']) . '<br />';

                $_logSubject = htmlspecialchars($_parserLog['subject']);
                if (empty($_logSubject)) {
                    $_logSubject = $_SWIFT->Language->Get('plognosubject');
                }

                $_parserLogContainer[] = array('title' => htmlspecialchars($_logSubject) . $_titleSuffix, 'date' => SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_parserLog['dateline']) . ' (' . SWIFT_Date::ColorTime(DATENOW - $_parserLog['dateline']) . ')', 'contents' => $_finalText . '<br /><div class="' . $_displayClass . '">' . $_displayText . '</div> ' . htmlspecialchars($_parserLog['description']));
            }
        }

        return array($_totalRecordCount, $_parserLogContainer);
    }

    /**
     * Check if Message ID Exist
     *
     * @author Ravi Sharma <ravi.sharma@kayako.com>
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsMessageIDExist($_messageID)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_dataContaner = $_SWIFT->Database->QueryFetch("SELECT parserlogid FROM " . TABLE_PREFIX . "parserlogs WHERE messageid = '" . $_messageID . "'");

        if (isset($_dataContaner['parserlogid'])) {
            return $_dataContaner['parserlogid'];
        }

        // @codeCoverageIgnoreStart
        return false;
        // @codeCoverageIgnoreEnd
    }

    /**
     * @author Mansi Wason<mansi.wason@kayako.com>
     *
     * @return array
     */
    public static function RetrieveMessageID($_ticketID)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_dataContainer = $_SWIFT->Database->QueryFetchAll("SELECT messageid FROM " . TABLE_PREFIX . "parserlogs WHERE typeid = '" . $_ticketID . "'");

        $messageIDs = array();
        foreach ($_dataContainer as $_data) {
            // @codeCoverageIgnoreStart
            $messageIDs[] = $_data['messageid'];
            // @codeCoverageIgnoreEnd
        }

        return $messageIDs;
    }

    /**
     * Dispatch failure notifications to admin
     *
     * @author Ravi Sharma <ravi.sharma@kayako.com>
     *
     * @param array $_fieldsContainer the log data
     *
     * @return bool
     * @throws SWIFT_Exception
     */
    public static function DispatchNotfication($_fieldsContainer)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_emailQueueCache = $_SWIFT->Cache->Get('queuecache');

        // Customize notification message
        $_content = '<font color="red">' . $_fieldsContainer['description'] . '</font>';
        $_content .= '<br><br>' . $_SWIFT->Language->Get('ppemailqueue') . isset($_emailQueueCache['list'][$_fieldsContainer['emailqueueid']]) ? '' : $_emailQueueCache['list'][$_fieldsContainer['emailqueueid']]['email'];
        $_content .= '<br><br>' . $_SWIFT->Language->Get('notificationsubject') . $_fieldsContainer['subject'];
        $_content .= '<br><br>' . $_SWIFT->Language->Get('ppfromemail') . $_fieldsContainer['fromemail'];
        $_content .= '<br><br>' . $_SWIFT->Language->Get('pptoemail') . $_fieldsContainer['toemail'];
        $_content .= '<br><br>' . $_SWIFT->Language->Get('ppsize') . FormattedSize($_fieldsContainer['size']);
        $_content .= '<br><br>' . 'Log in to your account at ' . $_SWIFT->Settings->Get('general_producturl') . ' to see further details and take the necessary actions.';

        $_mailObject = new SWIFT_Mail();

        $_mailObject->SetFromField($_SWIFT->Settings->Get('general_returnemail'), "Parser Failure");
        $_mailObject->SetSubjectField('Mail Processing Error at ' . date("F j, Y, g:i a"));
        $_mailObject->SetDataText($_content);
        $_mailObject->SetDataHTML($_content);

        // Get all the admin email addresses
        $_SWIFT->Database->Query("SELECT email, fullname FROM " . TABLE_PREFIX . "staff WHERE isenabled = 1 AND staffgroupid IN (SELECT staffgroupid FROM " . TABLE_PREFIX . "staffgroup WHERE isadmin = 1)");

        while ($_SWIFT->Database->NextRecord()) {
            $_mailObject->OverrideToField($_SWIFT->Database->Record['email'], $_SWIFT->Database->Record['fullname']);
            $_mailObject->SendMail();
        }

        return true;
    }
}
