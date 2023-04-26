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

namespace Tickets\Models\AuditLog;

use SWIFT;
use SWIFT_Exception;
use SWIFT_Interface;
use SWIFT_Model;
use SWIFT_Date;
use Tickets\Models\Ticket\SWIFT_Ticket;
use Tickets\Models\Ticket\SWIFT_TicketPost;
use Base\Models\User\SWIFT_User;

/**
 * The Ticket Audit Log Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_TicketAuditLog extends SWIFT_Model
{
    const TABLE_NAME        =    'ticketauditlogs';
    const PRIMARY_KEY        =    'ticketauditlogid';

    const TABLE_STRUCTURE    =    "ticketauditlogid I PRIMARY AUTO NOTNULL,
                                ticketid I DEFAULT '0' NOTNULL,
                                ticketpostid I DEFAULT '0' NOTNULL,
                                departmentid I DEFAULT '0' NOTNULL,
                                departmenttitle C(255) DEFAULT '' NOTNULL,
                                dateline I DEFAULT '0' NOTNULL,
                                creatortype I2 DEFAULT '0' NOTNULL,
                                creatorid I DEFAULT '0' NOTNULL,
                                creatorfullname C(255) DEFAULT '' NOTNULL,
                                actiontype I2 DEFAULT '0' NOTNULL,
                                actionmsg C(255) DEFAULT '' NOTNULL,
                                valuetype I DEFAULT '0' NOTNULL,
                                oldvalueid I DEFAULT '0' NOTNULL,
                                oldvaluestring C(255) DEFAULT '' NOTNULL,
                                newvalueid I DEFAULT '0' NOTNULL,
                                newvaluestring C(255) DEFAULT '' NOTNULL,
                                actionhash C(50) DEFAULT '' NOTNULL,
                                actionmsgparams C(1000) DEFAULT ''";

    const INDEX_1            =    'ticketid, actiontype';
    const INDEX_2            =    'dateline, creatortype, creatorid';
    const INDEX_3            =    'actionhash';
    const INDEX_4            =    'ticketid, ticketpostid, valuetype';
    const INDEX_5            =    'ticketpostid, ticketid, valuetype';

    protected $_dataStore = array();

    // Core Constants
    const CREATOR_STAFF = 1;
    const CREATOR_USER = 2;
    const CREATOR_SYSTEM = 3;
    const CREATOR_PARSER = 4;

    const ACTION_NEWTICKET = 1;
    const ACTION_NEWTICKETPOST = 2;
    const ACTION_UPDATEOWNER = 3;
    const ACTION_UPDATESTATUS = 4;
    const ACTION_UPDATEPRIORITY = 5;
    const ACTION_UPDATETYPE = 6;
    const ACTION_UPDATEDEPARTMENT = 7;
    const ACTION_UPDATEUSER = 19;
    const ACTION_UPDATESLA = 20;
    const ACTION_UPDATETICKETPOST = 8;
    const ACTION_DELETETICKETPOST = 9;
    const ACTION_DELETETICKET = 10;
    const ACTION_UPDATEFLAG = 11;
    const ACTION_WATCH = 12;
    const ACTION_TRASHTICKET = 13;
    const ACTION_UPDATETAGS = 14;
    const ACTION_LINKTICKET = 15;
    const ACTION_MERGETICKET = 16;
    const ACTION_BAN = 17;
    const ACTION_UPDATETICKET = 18;
    const ACTION_NEWNOTE = 21;
    const ACTION_DELETENOTE = 22;
    const ACTION_SLA = 23;
    const ACTION_RATING= 24;

    const VALUE_STATUS = 1;
    const VALUE_PRIORITY = 2;
    const VALUE_TYPE = 3;
    const VALUE_DEPARTMENT = 4;
    const VALUE_OWNER = 5;
    const VALUE_USER = 6;
    const VALUE_SLA = 7;
    const VALUE_NONE = 8;

	const PHRASE_PARAM = 'phrase:';
	const DATETIME_PARAM = 'datetime:';

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param int $_ticketAuditLogID The Ticket Audit Log ID
     * @throws SWIFT_Exception If the Record could not be loaded
     */
    public function __construct($_ticketAuditLogID)
    {
        parent::__construct();

        if (!$this->LoadData($_ticketAuditLogID)) {
            throw new SWIFT_AuditLog_Exception('Failed to load Ticket Audit Log ID: ' .  ($_ticketAuditLogID));
        }
    }

    /**
     * Destructor
     *
     * @author Varun Shoor
     * @throws SWIFT_Exception
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
        }

        if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'ticketauditlogs', $this->GetUpdatePool(), 'UPDATE', "ticketauditlogid = '" . (int) ($this->GetTicketAuditLogID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Ticket Audit Log ID
     *
     * @author Varun Shoor
     * @return mixed "ticketauditlogid" on Success, "false" otherwise
     * @throws SWIFT_AuditLog_Exception If the Class is not Loaded
     */
    public function GetTicketAuditLogID()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AuditLog_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['ticketauditlogid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param int $_ticketAuditLogID The Ticket Audit LOg ID
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_ticketAuditLogID)
    {
        $_dataStore = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "ticketauditlogs WHERE ticketauditlogid = '" . ($_ticketAuditLogID) . "'");
        if (isset($_dataStore['ticketauditlogid']) && !empty($_dataStore['ticketauditlogid']))
        {
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
     * @throws SWIFT_AuditLog_Exception If the Class is not Loaded
     */
    public function GetDataStore()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AuditLog_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore;
    }

    /**
     * Retrieves a Property Value from Data Store
     *
     * @author Varun Shoor
     * @param string $_key The Key Identifier
     * @return mixed Property Data on Success, "false" otherwise
     * @throws SWIFT_AuditLog_Exception If the Class is not Loaded
     */
    public function GetProperty($_key)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AuditLog_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_AuditLog_Exception(SWIFT_INVALIDDATA);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Check to see if its a valid creator type
     *
     * @author Varun Shoor
     * @param mixed $_creatorType The Creator Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidCreator($_creatorType)
    {
        if ($_creatorType == self::CREATOR_STAFF || $_creatorType == self::CREATOR_USER || $_creatorType == self::CREATOR_SYSTEM || $_creatorType == self::CREATOR_PARSER)
        {
            return true;
        }

        return false;
    }

    /**
     * Check to see if its a valid action type
     *
     * @author Varun Shoor
     * @param mixed $_actionType The Action Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidAction($_actionType)
    {
        return ($_actionType == self::ACTION_NEWTICKET || $_actionType == self::ACTION_NEWTICKETPOST || $_actionType == self::ACTION_UPDATEOWNER ||
                $_actionType == self::ACTION_UPDATESTATUS || $_actionType == self::ACTION_UPDATEPRIORITY ||
                $_actionType == self::ACTION_UPDATETYPE || $_actionType == self::ACTION_UPDATEDEPARTMENT ||
                $_actionType == self::ACTION_UPDATEUSER || $_actionType == self::ACTION_UPDATESLA ||
                $_actionType == self::ACTION_UPDATETICKETPOST || $_actionType == self::ACTION_DELETETICKETPOST ||
                $_actionType == self::ACTION_DELETETICKET || $_actionType == self::ACTION_UPDATEFLAG ||
                $_actionType == self::ACTION_WATCH || $_actionType == self::ACTION_TRASHTICKET ||
                $_actionType == self::ACTION_UPDATETAGS || $_actionType == self::ACTION_LINKTICKET ||
                $_actionType == self::ACTION_MERGETICKET || $_actionType == self::ACTION_BAN ||
                $_actionType == self::ACTION_UPDATETICKET || $_actionType == self::ACTION_NEWNOTE || $_actionType == self::ACTION_DELETENOTE || $_actionType == self::ACTION_SLA || $_actionType == self::ACTION_RATING);
    }

    /**
     * Check to see if its a valid Value type
     *
     * @author Varun Shoor
     * @param mixed $_valueType The Value Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidValue($_valueType)
    {
        return ($_valueType == self::VALUE_STATUS || $_valueType == self::VALUE_PRIORITY || $_valueType == self::VALUE_TYPE ||
                $_valueType == self::VALUE_DEPARTMENT || $_valueType == self::VALUE_OWNER ||
                $_valueType == self::VALUE_USER || $_valueType == self::VALUE_SLA ||
                $_valueType == self::VALUE_NONE);
    }

    /**
     * Create a new Audit log entry
     *
     * @author Varun Shoor
     * @param SWIFT_Ticket $_SWIFT_TicketObject The SWIFT_Ticket Object Pointer
     * @param SWIFT_TicketPost $_SWIFT_TicketPostObject The SWIFT_TicketPost Object Pointer
     * @param mixed $_creatorType The Creator Type (Staff/Client/System)
     * @param int $_creatorID The Creator ID
     * @param string $_creatorFullName The Creator Full Name
     * @param mixed $_actionType The Action Type
     * @param string $_actionMessage The Action Message
     * @param mixed $_valueType The Value Type (for IDs)
     * @param string $_actionHash (OPTIONAL) A unique hash to group the actions
     * @param int $_oldValueID (OPTIONAL) The Old Value ID
     * @param string $_oldValueString (OPTIONAL) The Old Value String (status title etc.)
     * @param int $_newValueID (OPTIONAL) The New Value ID
     * @param string $_newValueString (OPTIONAL) The New Value String
     * @return mixed "_ticketAuditLogID" (INT) on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function Create(SWIFT_Ticket $_SWIFT_TicketObject, $_SWIFT_TicketPostObject, $_creatorType, $_creatorID,
            $_creatorFullName, $_actionType, $_actionMessage, $_valueType, $_actionHash = '', $_oldValueID = 0, $_oldValueString = '',
            $_newValueID = 0, $_newValueString = '', $msgparams = [])
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!self::IsValidCreator($_creatorType) || !self::IsValidAction($_actionType) || !self::IsValidValue($_valueType) ||
                !$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded())
        {
            throw new SWIFT_AuditLog_Exception(SWIFT_INVALIDDATA);
        }

        $_ticketPostID = 0;
        if ($_SWIFT_TicketPostObject instanceof SWIFT_TicketPost && $_SWIFT_TicketPostObject->GetIsClassLoaded()) {
            $_ticketPostID = $_SWIFT_TicketPostObject->GetTicketPostID();
        }

        $_departmentCache = $_SWIFT->Cache->Get('departmentcache');
        $_departmentTitle = false;
        $_departmentID = (int) ($_SWIFT_TicketObject->GetProperty('departmentid'));

        if (isset($_departmentCache[$_departmentID]))
        {
            $_departmentTitle = $_departmentCache[$_departmentID]['title'];
        } else if ($_departmentID == '0') {
            $_departmentTitle = $_SWIFT->Language->Get('trash');
        } else {
            $_departmentTitle = $_SWIFT->Language->Get('na');
        }

        /**
         * BUGFIX: Parminder Singh
         *
         * SWIFT-1998: ActionMsg too long
         *
         * Comments: Strip text to accomodate within storage
         */
        $_actionMessage = StripName($_actionMessage, 252);

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'ticketauditlogs', array('ticketid' => (int) ($_SWIFT_TicketObject->GetTicketID()),
            'ticketpostid' =>  ($_ticketPostID), 'departmentid' =>  ($_departmentID),
            'departmenttitle' => $_departmentTitle, 'dateline' => DATENOW, 'creatortype' => (int) ($_creatorType),
            'creatorid' =>  ($_creatorID), 'creatorfullname' => $_creatorFullName, 'actiontype' => (int) ($_actionType),
            'actionmsg' => $_actionMessage, 'valuetype' => (int) ($_valueType), 'oldvalueid' =>  ($_oldValueID),
            'oldvaluestring' => $_oldValueString, 'newvalueid' =>  ($_newValueID), 'newvaluestring' => $_newValueString,
            'actionhash' => $_actionHash, 'actionmsgparams' => json_encode($msgparams)), 'INSERT');
        $_ticketAuditLogID = $_SWIFT->Database->Insert_ID();

        if (!$_ticketAuditLogID && SWIFT_INTERFACE !== 'tests')
        {
            throw new SWIFT_AuditLog_Exception(SWIFT_CREATEFAILED);
        }

        return $_ticketAuditLogID;
    }

    /**
     * Retrieve on Ticket
     *
     * @author Varun Shoor
     * @param SWIFT_Ticket $_SWIFT_TicketObject The SWIFT_Ticket Object
     * @return array "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveOnTicket(SWIFT_Ticket $_SWIFT_TicketObject) {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_ticketAuditLogContainer = array();

        $_SWIFT->Database->Query("SELECT actionhash, dateline FROM " . TABLE_PREFIX . "ticketauditlogs WHERE ticketid = '" .
                (int) ($_SWIFT_TicketObject->GetTicketID()) . "' GROUP BY actionhash ORDER BY dateline");
        while ($_SWIFT->Database->NextRecord()) {
            $_ticketAuditLogContainer[$_SWIFT->Database->Record['actionhash']] = array();
            $_ticketAuditLogContainer[$_SWIFT->Database->Record['actionhash']]['dateline'] = $_SWIFT->Database->Record['dateline'];
            $_ticketAuditLogContainer[$_SWIFT->Database->Record['actionhash']]['items'] = array();
        }

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketauditlogs WHERE ticketid = '" .
                (int) ($_SWIFT_TicketObject->GetTicketID()) . "' ORDER BY dateline");
        while ($_SWIFT->Database->NextRecord()) {
            $_ticketAuditLogContainer[$_SWIFT->Database->Record['actionhash']]['items'][$_SWIFT->Database->Record['ticketauditlogid']] = $_SWIFT->Database->Record;
        }

        return self::ConvertAuditLogMsgLanguage($_ticketAuditLogContainer, $_SWIFT);
    }


    protected static function ConvertAuditLogMsgLanguage($auditLogContainer, $_SWIFT) {
        foreach ($auditLogContainer as $auditKey => $hashContainer) {
            foreach ($hashContainer['items'] as $key => $logContainer) {
                if (array_key_exists('actionmsgparams', $logContainer) && !empty($logContainer['actionmsgparams'])) {
                    $logContainer['actionmsg'] = self::ConvertMsgLanguage($logContainer['actionmsgparams'], $_SWIFT);
                    $hashContainer['items'][$key] = $logContainer;
                }
            }
            $auditLogContainer[$auditKey] = $hashContainer;
        }
        return $auditLogContainer;
    }

    protected static function ConvertMsgLanguage(string $msgParams, $_SWIFT) {
        try {
            $params = json_decode($msgParams);
            $rawMsg = $_SWIFT->Language->Get($params[0]);
            if (count($params) > 1) {
                $msgParams = self::ConvertMsgParams(array_slice($params, 1), $_SWIFT);
                return vsprintf($rawMsg, $msgParams);
            } else {
                return $rawMsg;
            }
        } catch (\Exception $e) {
            $_SWIFT->Log->Log("unable to parse audit log params: ".$msgParams, 0, 'SWIFT_TicketAuditLog');
        }
    }

    protected static function ConvertMsgParams($msgParams, $_SWIFT) {
        $convertedParams = [];
        foreach ($msgParams as $param) {
            if (is_string($param) && substr($param, 0, strlen(self::DATETIME_PARAM)) == self::DATETIME_PARAM) {
                $rawParam = substr($param, strlen(self::DATETIME_PARAM) + 1);
                $convertedParams[] = SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, intval($rawParam));
            } elseif (is_string($param) && substr($param, 0, strlen(self::PHRASE_PARAM)) == self::PHRASE_PARAM) {
                $rawParam = substr($param, strlen(self::PHRASE_PARAM) + 1);
                $convertedParams[] = $_SWIFT->Language->Get($rawParam);
            } else {
                $convertedParams[] = $param;
            }
        }
        return $convertedParams;
    }

    /**
     * Log a new action
     *
     * @author Varun Shoor
     * @param SWIFT_Ticket $_SWIFT_TicketObject The SWIFT_Ticket Object Pointer
     * @param SWIFT_TicketPost $_SWIFT_TicketPostObject The SWIFT_TicketPost Object Pointer
     * @param mixed $_actionType The Action Type
     * @param string $_actionMessage The Action Message
     * @param mixed $_valueType The Value Type (for IDs)
     * @param int $_oldValueID (OPTIONAL) The Old Value ID
     * @param string $_oldValueString (OPTIONAL) The Old Value String (status title etc.)
     * @param int $_newValueID (OPTIONAL) The New Value ID
     * @param string $_newValueString (OPTIONAL) The New Value String
     * @return mixed "_ticketAuditLogID" (INT) on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function AddToLog(SWIFT_Ticket $_SWIFT_TicketObject, $_SWIFT_TicketPostObject, $_actionType, $_actionMessage,
            $_valueType = self::VALUE_NONE, $_oldValueID = 0, $_oldValueString = '', $_newValueID = 0, $_newValueString = '', $msgparams = []) {
        $_SWIFT = SWIFT::GetInstance();

        $_auditLogCreator = static::CREATOR_SYSTEM;
        $_auditLogCreatorID = 0;
        $_auditLogCreatorFullName = '';

        if (SWIFT::Get('isparser') === true) {
            $_auditLogCreator = static::CREATOR_PARSER;
        } else if ($_SWIFT->Interface->GetInterface() == SWIFT_Interface::INTERFACE_STAFF ||
                $_SWIFT->Interface->GetInterface() == SWIFT_Interface::INTERFACE_ADMIN ||
                $_SWIFT->Interface->GetInterface() == SWIFT_Interface::INTERFACE_WINAPP ||
                $_SWIFT->Interface->GetInterface() == SWIFT_Interface::INTERFACE_MOBILE ||
                $_SWIFT->Interface->GetInterface() == SWIFT_Interface::INTERFACE_STAFFAPI) {
            $_auditLogCreator = static::CREATOR_STAFF;
            $_auditLogCreatorID = $_SWIFT->Staff->GetStaffID();
            $_auditLogCreatorFullName = $_SWIFT->Staff->GetProperty('fullname');
        } else if ($_SWIFT->Interface->GetInterface() == SWIFT_Interface::INTERFACE_CLIENT ||
                $_SWIFT->Interface->GetInterface() == SWIFT_Interface::INTERFACE_CHAT ||
                $_SWIFT->Interface->GetInterface() == SWIFT_Interface::INTERFACE_VISITOR) {
            $_auditLogCreator = static::CREATOR_USER;

            if (isset($_SWIFT->User) && $_SWIFT->User instanceof SWIFT_User && $_SWIFT->User->GetIsClassLoaded()) {
                $_auditLogCreatorID = $_SWIFT->User->GetUserID();
                $_auditLogCreatorFullName = $_SWIFT->User->GetProperty('fullname');
            }
        } else if ($_SWIFT->Interface->GetInterface() == SWIFT_Interface::INTERFACE_CONSOLE || $_SWIFT->Interface->GetInterface() == SWIFT_Interface::INTERFACE_CRON) {
            $_auditLogCreator = SWIFT_TicketAuditLog::CREATOR_SYSTEM;
        }

        static::Create($_SWIFT_TicketObject, $_SWIFT_TicketPostObject, $_auditLogCreator, $_auditLogCreatorID,
            $_auditLogCreatorFullName, $_actionType, $_actionMessage,
            $_valueType, SWIFT::Get('ActionHash'), $_oldValueID, $_oldValueString,
            $_newValueID, $_newValueString, $msgparams);

        return true;
    }

    /**
     * Delete the audit log record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AuditLog_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($this->GetTicketAuditLogID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of Ticket Audit logs
     *
     * @author Varun Shoor
     * @param array $_ticketAuditLogIDList The Ticket Audit Log ID List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function DeleteList($_ticketAuditLogIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_ticketAuditLogIDList))
        {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "ticketauditlogs WHERE ticketauditlogid IN (" . BuildIN($_ticketAuditLogIDList) .
                ")");

        return true;
    }

    /**
     * Delete the audit logs based on ticket id list
     *
     * @author Varun Shoor
     * @param array $_ticketIDList The Ticket ID List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function DeleteOnTicket($_ticketIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_ticketIDList))
        {
            return false;
        }

        $_ticketAuditLogIDList = array();
        $_SWIFT->Database->Query("SELECT ticketauditlogid, actiontype FROM " . TABLE_PREFIX . "ticketauditlogs
            WHERE ticketid IN (" . BuildIN($_ticketIDList) . ") AND actiontype NOT IN ('" . self::ACTION_DELETETICKET . "', '" . self::ACTION_TRASHTICKET . "', '" . self::ACTION_DELETETICKETPOST . "')");
        while ($_SWIFT->Database->NextRecord())
        {
            // Ignore the delete logs
            if ($_SWIFT->Database->Record['actiontype'] != self::ACTION_DELETETICKET ||
                    $_SWIFT->Database->Record['actiontype'] != self::ACTION_DELETETICKETPOST)
            {
                $_ticketAuditLogIDList[] = (int) ($_SWIFT->Database->Record['ticketauditlogid']);
            }
        }

        if (!count($_ticketAuditLogIDList))
        {
            return false;
        }

        self::DeleteList($_ticketAuditLogIDList);

        return true;
    }

    /**
     * Delete the audit logs based on ticket post id list
     *
     * @author Varun Shoor
     * @param array $_ticketPostIDList The Ticket Post ID List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function DeleteOnTicketPost($_ticketPostIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_ticketPostIDList))
        {
            return false;
        }

        $_ticketAuditLogIDList = array();
        $_SWIFT->Database->Query("SELECT ticketauditlogid, actiontype FROM " . TABLE_PREFIX . "ticketauditlogs WHERE ticketpostid IN (" .
                BuildIN($_ticketPostIDList) . ")");
        while ($_SWIFT->Database->NextRecord())
        {
            // Ignore the delete logs
            if ($_SWIFT->Database->Record['actiontype'] != self::ACTION_DELETETICKETPOST)
            {
                $_ticketAuditLogIDList[] = (int) ($_SWIFT->Database->Record['ticketauditlogid']);
            }
        }

        if (!count($_ticketAuditLogIDList))
        {
            return false;
        }

        self::DeleteList($_ticketAuditLogIDList);

        return true;
    }

    /**
     * Replace the current ticket id all tickets with the new one
     *
     * @author Varun Shoor
     * @param array $_ticketIDList The Old Ticket ID List
     * @param SWIFT_Ticket $_SWIFT_ParentTicketObject The Parent Ticket Object
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function ReplaceTicket($_ticketIDList, SWIFT_Ticket $_SWIFT_ParentTicketObject) {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_SWIFT_ParentTicketObject instanceof SWIFT_Ticket || !$_SWIFT_ParentTicketObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if (!_is_array($_ticketIDList)) {
            return false;
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'ticketauditlogs', array('ticketid' => (int) ($_SWIFT_ParentTicketObject->GetTicketID())),
                'UPDATE', "ticketid IN (" . BuildIN($_ticketIDList) . ")");

        return true;
    }

    /**
     * Update the properties from rebuild properties option in tickets
     *
     * @author Varun Shoor
     * @param int $_ticketAuditLogID
     * @param string $_departmentTitle
     * @param string $_creatorName
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function UpdateProperties($_ticketAuditLogID, $_departmentTitle, $_creatorName)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'ticketauditlogs', array('departmenttitle' => $_departmentTitle, 'creatorfullname' => $_creatorName), 'UPDATE', "ticketauditlogid = '" .  ($_ticketAuditLogID) . "'");

        return true;
    }

    /**
     * Update the global property on all ticket audit logs, used to update stuff like departmentname etc.
     *
     * @author Varun Shoor
     * @param string $_updateFieldName
     * @param string $_updateFieldValue
     * @param string $_whereFieldName
     * @param string $_whereFieldValue
     * @param string $_extendedUpdateStatement (OPTIONAL)
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function UpdateGlobalProperty($_updateFieldName, $_updateFieldValue, $_whereFieldName, $_whereFieldValue, $_extendedUpdateStatement = '')
    {
        $_SWIFT = SWIFT::GetInstance();

        $_updateFieldName = $_SWIFT->Database->Escape($_updateFieldName);
        $_whereFieldName = $_SWIFT->Database->Escape($_whereFieldName);
        $_whereFieldValue = (int) ($_whereFieldValue); // Expected to be always int

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'ticketauditlogs', array($_updateFieldName => $_updateFieldValue), 'UPDATE', $_whereFieldName . " = '" . $_SWIFT->Database->Escape($_whereFieldValue) . "'" . $_extendedUpdateStatement);

        return true;
    }
}
