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

namespace Tickets\Models\Escalation;

use SWIFT;
use SWIFT_Data;
use SWIFT_DataID;
use SWIFT_DataStore;
use SWIFT_Exception;
use SWIFT_Model;
use Tickets\Models\Ticket\SWIFT_Ticket;

/**
 * The Escalation Rule Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_EscalationRule extends SWIFT_Model
{
    const TABLE_NAME        =    'escalationrules';
    const PRIMARY_KEY        =    'escalationruleid';

    const TABLE_STRUCTURE    =    "escalationruleid I PRIMARY AUTO NOTNULL,
                                slaplanid I DEFAULT '0' NOTNULL,
                                staffid I DEFAULT '0' NOTNULL,
                                priorityid I DEFAULT '0' NOTNULL,
                                ticketstatusid I DEFAULT '0' NOTNULL,
                                departmentid I DEFAULT '0' NOTNULL,
                                title C(255) DEFAULT '' NOTNULL,
                                dateline I DEFAULT '0' NOTNULL,
                                ruletype I2 DEFAULT '0' NOTNULL,

                                flagtype I2 DEFAULT '0' NOTNULL,
                                newslaplanid I DEFAULT '0' NOTNULL,
                                addtags X2 NOTNULL,
                                removetags X2 NOTNULL,
                                tickettypeid I DEFAULT '0' NOTNULL";

    const INDEX_1            =    'slaplanid';
    const INDEX_2            =    'title'; // Unified Search


    protected $_dataStore = array();

    // Core Constants
    const TYPE_DUE = 1;
    const TYPE_RESOLUTIONDUE = 2;
    const TYPE_BOTH = 3;

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
            throw new SWIFT_Exception('Failed to load Escalation Rule Object');
        }
    }

    /**
     * Destructor
     *
     * @author Varun Shoor
     */
    public function __destruct()
    {
        $intName = \SWIFT::GetInstance()->Interface->GetName()?: SWIFT_INTERFACE;
        if ($intName === 'tests') {
            return;
        }

        $this->ProcessUpdatePool();

        parent::__destruct();
    }

    /**
     * Processes the Update Pool Data
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Escalation_Exception If the Class is not Loaded
     */
    public function ProcessUpdatePool()
    {
        if (!$this->GetIsClassLoaded())
        {
            return false;
        } else if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'escalationrules', $this->GetUpdatePool(), 'UPDATE', "escalationruleid = '" .
                (int) ($this->GetEscalationRuleID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Escalation Rule ID
     *
     * @author Varun Shoor
     * @return mixed "escalationruleid" on Success, "false" otherwise
     * @throws SWIFT_Escalation_Exception If the Class is not Loaded
     */
    public function GetEscalationRuleID()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Escalation_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['escalationruleid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param SWIFT_Data $_SWIFT_DataObject The SWIFT_Data Object
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    protected function LoadData($_SWIFT_DataObject)
    {
        $_SWIFT = SWIFT::GetInstance();

        // Is it a ID?
        if ($_SWIFT_DataObject instanceof SWIFT_DataID && $_SWIFT_DataObject->GetIsClassLoaded())
        {
            $_dataStore = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "escalationrules WHERE escalationruleid = '" . (int) ($_SWIFT_DataObject->GetDataID()) . "'");
            if (isset($_dataStore['escalationruleid']) && !empty($_dataStore['escalationruleid']))
            {
                $this->_dataStore = $_dataStore;

                return true;
            }

            // Is it a Store?
        } else if ($_SWIFT_DataObject instanceof SWIFT_DataStore && $_SWIFT_DataObject->GetIsClassLoaded()) {
            $this->_dataStore = $_SWIFT_DataObject->GetDataStore();

            if (!isset($this->_dataStore['escalationruleid']) || empty($this->_dataStore['escalationruleid']))
            {
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            }

            return true;
        }

        throw new SWIFT_Exception(SWIFT_INVALIDDATA);
    }

    /**
     * Returns the Data Store Array
     *
     * @author Varun Shoor
     * @return mixed "_dataStore" Array on Success, "false" otherwise
     * @throws SWIFT_Escalation_Exception If the Class is not Loaded
     */
    public function GetDataStore()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Escalation_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore;
    }

    /**
     * Retrieves a Property Value from Data Store
     *
     * @author Varun Shoor
     * @param string $_key The Key Identifier
     * @return mixed Property Data on Success, "false" otherwise
     * @throws SWIFT_Escalation_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function GetProperty($_key)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Escalation_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_Escalation_Exception(SWIFT_INVALIDDATA . ': ' . $_key);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Check to see if its a valid rule type
     *
     * @author Varun Shoor
     * @param mixed $_ruleType The Rule Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidType($_ruleType)
    {
        if ($_ruleType == self::TYPE_DUE || $_ruleType == self::TYPE_RESOLUTIONDUE || $_ruleType == self::TYPE_BOTH)
        {
            return true;
        }

        return false;
    }

    /**
     * Process the Tag List
     *
     * @author Varun Shoor
     * @param array $_tagList The Tag List
     * @return string The Processed Tag List (JSON'ed Array)
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function ProcessTagList($_tagList) {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_tagList)) {
            return json_encode(array());
        }

        $_finalTagList = array();
        foreach ($_tagList as $_key => $_val) {
            $_finalTagList[] = Clean($_val);
        }

        return json_encode($_finalTagList);
    }

    /**
     * Create a new Escalation Rule
     *
     * @author Varun Shoor
     * @param string $_title The Escalation Rule Title
     * @param int $_slaPlanID The SLA Plan ID
     * @param int $_staffID The Staff ID
     * @param mixed $_ruleType The Rule Type
     * @param int $_ticketTypeID The Ticket Type ID
     * @param int $_ticketPriorityID The Ticket Priority ID
     * @param int $_ticketStatusID The Ticket Status ID
     * @param int $_departmentID The Ticket Department ID
     * @param int $_flagType The Flag Type
     * @param int $_newSLAPlanID The new SLA Plan ID
     * @param array $_notificationContainer The Notification Container
     * @param array $_addTagList The List of Tags to Add to Ticket
     * @param array $_removeTagList The List of Tags to Remove from Ticket
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Escalation_Exception If Invalid Data is Provided or If Creation Fails
     */
    public static function Create($_title, $_slaPlanID, $_staffID, $_ruleType, $_ticketTypeID, $_ticketPriorityID, $_ticketStatusID, $_departmentID, $_flagType,
            $_newSLAPlanID, $_notificationContainer, $_addTagList, $_removeTagList)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_SWIFT->Cache->Load('slaplancache');

        $_slaPlanCache = $_SWIFT->Cache->Get('slaplancache');

        if (empty($_title) || !isset($_slaPlanCache[$_slaPlanID]) || (!empty($_newSLAPlanID) && !isset($_slaPlanCache[$_newSLAPlanID])) ||
                !self::IsValidType($_ruleType))
        {
            throw new SWIFT_Escalation_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'escalationrules', array('slaplanid' => $_slaPlanID, 'staffid' => $_staffID,
            'priorityid' => $_ticketPriorityID, 'ticketstatusid' => $_ticketStatusID, 'departmentid' => $_departmentID,
            'title' => $_title, 'dateline' => DATENOW, 'flagtype' => ($_flagType), 'newslaplanid' => $_newSLAPlanID,
            'tickettypeid' => $_ticketTypeID, 'addtags' => self::ProcessTagList($_addTagList),
            'removetags' => self::ProcessTagList($_removeTagList), 'ruletype' => (int) ($_ruleType)), 'INSERT');

        $_escalationRuleID = $_SWIFT->Database->Insert_ID();
        if (!$_escalationRuleID)
        {
            throw new SWIFT_Escalation_Exception(SWIFT_CREATEFAILED);
        }

        if (_is_array($_notificationContainer))
        {
            foreach ($_notificationContainer as $_key => $_val)
            {
                SWIFT_EscalationNotification::Create($_val[0], $_escalationRuleID, $_val[1], $_val[2]);
            }
        }

        self::RebuildCache();

        return $_escalationRuleID;
    }

    /**
     * Update an Escalation Rule Record
     *
     * @author Varun Shoor
     * @param string $_title The Escalation Rule Title
     * @param int $_slaPlanID The SLA Plan ID
     * @param int $_staffID The Staff ID
     * @param mixed $_ruleType The Rule Type
     * @param int $_ticketTypeID The Ticket Type ID
     * @param int $_ticketPriorityID The Ticket Priority ID
     * @param int $_ticketStatusID The Ticket Status ID
     * @param int $_departmentID The Ticket Department ID
     * @param int $_flagType The Flag Type
     * @param int $_newSLAPlanID The new SLA Plan ID
     * @param array $_notificationContainer The Notification Container
     * @param array $_addTagList The List of Tags to Add to Ticket
     * @param array $_removeTagList The List of Tags to Remove from Ticket
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Escalation_Exception If Invalid Data is Provided or If the Class is not Loaded
     */
    public function Update($_title, $_slaPlanID, $_staffID, $_ruleType, $_ticketTypeID, $_ticketPriorityID, $_ticketStatusID, $_departmentID, $_flagType,
            $_newSLAPlanID, $_notificationContainer, $_addTagList, $_removeTagList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Escalation_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->Cache->Load('slaplancache');
        $_slaPlanCache = $this->Cache->Get('slaplancache');

        if (empty($_title) || !isset($_slaPlanCache[$_slaPlanID]) || (!empty($_newSLAPlanID) && !isset($_slaPlanCache[$_newSLAPlanID])) ||
                !self::IsValidType($_ruleType))
        {
            throw new SWIFT_Escalation_Exception(SWIFT_INVALIDDATA);
        }

        $this->UpdatePool('slaplanid', $_slaPlanID);
        $this->UpdatePool('priorityid', $_ticketPriorityID);
        $this->UpdatePool('ticketstatusid', $_ticketStatusID);
        $this->UpdatePool('title', $_title);
        $this->UpdatePool('dateline', DATENOW);
        $this->UpdatePool('flagtype', ($_flagType));
        $this->UpdatePool('staffid', $_staffID);
        $this->UpdatePool('departmentid', $_departmentID);
        $this->UpdatePool('newslaplanid', $_newSLAPlanID);
        $this->UpdatePool('tickettypeid', $_ticketTypeID);
        $this->UpdatePool('ruletype', (int) ($_ruleType));

        $this->UpdatePool('addtags', self::ProcessTagList($_addTagList));
        $this->UpdatePool('removetags', self::ProcessTagList($_removeTagList));

        $this->ProcessUpdatePool();

        SWIFT_EscalationNotification::DeleteOnEscalationRule(array($this->GetEscalationRuleID()));
        if (_is_array($_notificationContainer))
        {
            foreach ($_notificationContainer as $_key => $_val)
            {
                SWIFT_EscalationNotification::Create($_val[0], $this->GetEscalationRuleID(), $_val[1], $_val[2]);
            }
        }

        self::RebuildCache();

        return true;
    }

    /**
     * Delete the Escalation Rule record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Escalation_Exception If the Class is not Loaded
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Escalation_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($this->GetEscalationRuleID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a List of Escalation Rule ID's
     *
     * @author Varun Shoor
     * @param array $_escalationRuleIDList The List of Escalation Rule IDs
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_escalationRuleIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_escalationRuleIDList))
        {
            return false;
        }

        $_finalEscalationRuleIDList = array();
        $_index = 1;

        $_finalText = '';
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "escalationrules WHERE escalationruleid IN (" .
                BuildIN($_escalationRuleIDList) . ")");
        while ($_SWIFT->Database->NextRecord())
        {
            $_finalText .= $_index . '. ' . htmlspecialchars($_SWIFT->Database->Record['title']) . '<br />';

            $_finalEscalationRuleIDList[] = $_SWIFT->Database->Record['escalationruleid'];
        }

        SWIFT::Info(sprintf($_SWIFT->Language->Get('titledelescalationrule'), count($_finalEscalationRuleIDList)),
                $_SWIFT->Language->Get('msgdelescalationrule') . '<br />' . $_finalText);

        if (!count($_finalEscalationRuleIDList))
        {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "escalationrules WHERE escalationruleid IN (" .
                BuildIN($_finalEscalationRuleIDList) . ")");

        SWIFT_EscalationNotification::DeleteOnEscalationRule($_finalEscalationRuleIDList);

        self::RebuildCache();

        return true;
    }

    /**
     * Delete the Escalation Rules based on SLA Plan ID's
     *
     * @author Varun Shoor
     * @param string $_slaPlanIDList The SLA Plan ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteOnSLAPlanIDList($_slaPlanIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_slaPlanIDList))
        {
            return false;
        }

        $_escalationRuleIDList = array();
        $_SWIFT->Database->Query("SELECT escalationruleid FROM " . TABLE_PREFIX . "escalationrules WHERE slaplanid IN (" .
                BuildIN($_slaPlanIDList) . ")");
        while ($_SWIFT->Database->NextRecord())
        {
            $_escalationRuleIDList[] = $_SWIFT->Database->Record['escalationruleid'];
        }

        if (count($_escalationRuleIDList))
        {
            self::DeleteList($_escalationRuleIDList);
        }

        return true;
    }

    /**
     * Rebuild the Escalation Rule Cache
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public static function RebuildCache()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_cache = array();

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "escalationrules ORDER BY title ASC", 3);
        while ($_SWIFT->Database->NextRecord(3))
        {
            $_cache[$_SWIFT->Database->Record3['escalationruleid']] = $_SWIFT->Database->Record3;
        }

        $_SWIFT->Cache->Update('escalationrulecache', $_cache);

        return true;
    }
}
?>
