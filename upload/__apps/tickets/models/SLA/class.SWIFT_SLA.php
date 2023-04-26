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

namespace Tickets\Models\SLA;

use SWIFT;
use SWIFT_App;
use SWIFT_Data;
use SWIFT_DataID;
use SWIFT_DataStore;
use Base\Models\Department\SWIFT_Department;
use SWIFT_Exception;
use Base\Library\Rules\SWIFT_Rules;
use Tickets\Models\Ticket\SWIFT_Ticket;
use Tickets\Library\Flag\SWIFT_TicketFlag;
use Tickets\Models\Escalation\SWIFT_EscalationRule;

/**
 * The SLA Plan Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_SLA extends SWIFT_Rules
{
    const TABLE_NAME        =    'slaplans';
    const PRIMARY_KEY        =    'slaplanid';

    const TABLE_STRUCTURE    =    "slaplanid I PRIMARY AUTO NOTNULL,
                                slascheduleid I DEFAULT '0' NOTNULL,
                                title C(255) DEFAULT '' NOTNULL,
                                overduehrs F DEFAULT '0.0' NOTNULL,
                                resolutionduehrs F DEFAULT '0.0' NOTNULL,
                                isenabled I2 DEFAULT '0' NOTNULL,
                                sortorder I DEFAULT '0' NOTNULL,
                                ruletype I2 DEFAULT '0' NOTNULL,
                                dateline I DEFAULT '0' NOTNULL";

    const INDEX_1            =    'slascheduleid';
    const INDEX_2            =    'title'; // Unified Search


    protected $_dataStore = array();
    protected $_slaProperties = array();
    protected $_SWIFT_SLAScheduleObject = false;

    // Criteria
    const SLA_TICKETSTATUS = 'ticketstatus';
    const SLA_TICKETPRIORITY = 'ticketpriority';
    const SLA_TICKETDEPARTMENT = 'departmentid';
    const SLA_TICKETOWNER = 'ownerstaffid';
    const SLA_TICKETEMAILQUEUE = 'emailqueueid';
    const SLA_TICKETFLAGTYPE = 'flagtype';
    const SLA_TICKETCREATOR = 'creator';
    const SLA_TICKETUSERGROUP = 'usergroupid';

    const SLA_TICKETFULLNAME = 'fullname';
    const SLA_TICKETEMAIL = 'email';
    const SLA_TICKETLASTREPLIER = 'lastreplier';
    const SLA_TICKETSUBJECT = 'subject';
    const SLA_TICKETCHARSET = 'charset';

    const SLA_TICKETTEMPLATEGROUP = 'templategroup';
    const SLA_TICKETISRESOLVED = 'isresolved';
    const SLA_TICKETTYPE = 'tickettype';
    const SLA_TICKETWASREOPENED = 'wasreopened';
    const SLA_TICKETTOTALREPLIES = 'totalreplies';
    const SLA_TICKETBAYESCATEGORY = 'bayescategory';

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param SWIFT_Data $_SWIFT_DataObject The SWIFT_Data Object
     * @throws SWIFT_Exception If the Record could not be loaded
     */
    public function __construct(SWIFT_Data $_SWIFT_DataObject)
    {
        parent::__construct(array(), self::RULE_MATCHALL);

        if (!$_SWIFT_DataObject instanceof SWIFT_Data || !$_SWIFT_DataObject->GetIsClassLoaded() || !$this->LoadData($_SWIFT_DataObject)) {
            throw new SWIFT_SLA_Exception('Failed to load SLA Plan Object');
        }

        $this->SetIsClassLoaded(true);

        if (!$this->SetCriteria($this->GetProperty('_criteria')) || !$this->SetMatchType($this->GetProperty('ruletype'))) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
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
     * @throws SWIFT_SLA_Exception If the Class is not Loaded
     */
    public function ProcessUpdatePool()
    {
        if (!$this->GetIsClassLoaded())
        {
            return false;
        } else if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'slaplans', $this->GetUpdatePool(), 'UPDATE', "slaplanid = '" . (int) ($this->GetSLAPlanID()) .
                "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the SLA Plan ID
     *
     * @author Varun Shoor
     * @return mixed "slaplanid" on Success, "false" otherwise
     * @throws SWIFT_SLA_Exception If the Class is not Loaded
     */
    public function GetSLAPlanID()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_SLA_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['slaplanid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param SWIFT_Data $_SWIFT_DataObject The SWIFT_Data Object
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_SLA_Exception If Invalid Data is Provided
     */
    protected function LoadData($_SWIFT_DataObject)
    {
        $_SWIFT = SWIFT::GetInstance();

        // Is it a ID?
        if ($_SWIFT_DataObject instanceof SWIFT_DataID && $_SWIFT_DataObject->GetIsClassLoaded())
        {
            $_dataStore = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "slaplans WHERE slaplanid = '" .
                    (int) ($_SWIFT_DataObject->GetDataID()) . "'");
            if (isset($_dataStore['slaplanid']) && !empty($_dataStore['slaplanid']))
            {
                $_dataStore['_criteria'] = array();

                $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "slarulecriteria WHERE slaplanid = '" .
                        (int) ($_SWIFT_DataObject->GetDataID()) . "'");
                while ($_SWIFT->Database->NextRecord()) {
                    $_dataStore['_criteria'][$_SWIFT->Database->Record['slarulecriteriaid']] = array($_SWIFT->Database->Record['name'],
                        $_SWIFT->Database->Record['ruleop'], $_SWIFT->Database->Record['rulematch'], $_SWIFT->Database->Record['rulematchtype']);
                }

                $this->_dataStore = $_dataStore;

                return true;
            }

        // Is it a Store?
        } else if ($_SWIFT_DataObject instanceof SWIFT_DataStore && $_SWIFT_DataObject->GetIsClassLoaded()) {
            $this->_dataStore = $_SWIFT_DataObject->GetDataStore();

            if (!isset($this->_dataStore['slaplanid']) || empty($this->_dataStore['slaplanid']))
            {
                throw new SWIFT_SLA_Exception(SWIFT_INVALIDDATA);
            }

            return true;
        }

        throw new SWIFT_SLA_Exception(SWIFT_INVALIDDATA);
    }

    /**
     * Returns the Data Store Array
     *
     * @author Varun Shoor
     * @return mixed "_dataStore" Array on Success, "false" otherwise
     * @throws SWIFT_SLA_Exception If the Class is not Loaded
     */
    public function GetDataStore()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_SLA_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore;
    }

    /**
     * Retrieves a Property Value from Data Store
     *
     * @author Varun Shoor
     * @param string $_key The Key Identifier
     * @return mixed Property Data on Success, "false" otherwise
     * @throws SWIFT_SLA_Exception If the Class is not Loaded or If Invalid Data Provided
     */
    public function GetProperty($_key)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_SLA_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_SLA_Exception(SWIFT_INVALIDDATA . ': ' . $_key);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Retrieve the SLA Days Container
     *
     * @author Varun Shoor
     * @return mixed "SLA Days" (ARRAY) on Success, "false" otherwise
     */
    public static function GetDays()
    {
        return array('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday');
    }

    /**
     * Create a new SLA Plan
     *
     * @author Varun Shoor
     * @param string $_title The SLA Plan Title
     * @param float $_overDueHours The Overdue Hours to check against
     * @param float $_resolutionDueHours The Resolution Due Hours
     * @param int $_slaScheduleID The SLA Schedule ID
     * @param bool $_isEnabled Whether the SLA Plan is Enabled/Disabled
     * @param int $_sortOrder The SLA Plan Sort Order
     * @param int $_ruleType The Rule Type
     * @param array $_criteriaContainer The Array Containing the Criterias for this SLA Plan
     * @param array $_slaHolidayIDList The SLA Holiday this plan is linked to
     * @return mixed "_SWIFT_SLAObject" (OBJECT) on Success, "false" otherwise
     * @throws SWIFT_SLA_Exception If Invalid Data Provided or If Creation Fails
     */
    public static function Create($_title, $_overDueHours, $_resolutionDueHours, $_slaScheduleID, $_isEnabled, $_sortOrder, $_ruleType,
            $_criteriaContainer, $_slaHolidayIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_title) || !_is_array($_criteriaContainer))
        {
            throw new SWIFT_SLA_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'slaplans', array('title' => $_title, 'overduehrs' => floatval($_overDueHours),
            'resolutionduehrs' => floatval($_resolutionDueHours), 'slascheduleid' => $_slaScheduleID, 'isenabled' => (int) ($_isEnabled),
            'sortorder' =>  ($_sortOrder), 'ruletype' =>  ($_ruleType), 'dateline' => DATENOW), 'INSERT');
        $_slaPlanID = $_SWIFT->Database->Insert_ID();

        if (!$_slaPlanID)
        {
            throw new SWIFT_SLA_Exception(SWIFT_CREATEFAILED);
        }

        $_SWIFT_SLAObject = new SWIFT_SLA(new SWIFT_DataID($_slaPlanID));
        if (!$_SWIFT_SLAObject instanceof SWIFT_SLA || !$_SWIFT_SLAObject->GetIsClassLoaded())
        {
            throw new SWIFT_SLA_Exception(SWIFT_CREATEFAILED);
        }

        $_SWIFT_SLAObject->InsertRuleCriteria($_criteriaContainer);

        if (_is_array($_slaHolidayIDList))
        {
            SWIFT_SLAHoliday::LinkSLAHolidayIDList($_slaPlanID, $_slaHolidayIDList);
        }

        // Rebuild the SLA Plan Cache
        self::RebuildCache();

        return $_SWIFT_SLAObject;
    }

    /**
     * Update the SLA Plan Record
     *
     * @author Varun Shoor
     * @param string $_title The SLA Plan Title
     * @param float $_overDueHours The Overdue Hours to check against
     * @param float $_resolutionDueHours The Resolution Due Hours
     * @param int $_slaScheduleID The SLA Schedule ID
     * @param bool $_isEnabled Whether the SLA Plan is Enabled/Disabled
     * @param int $_sortOrder The SLA Plan Sort Order
     * @param int $_ruleType The Rule Type
     * @param array $_criteriaContainer The Array Containing the Criterias for this SLA Plan
     * @param array $_slaHolidayIDList The SLA Holiday this plan is linked to
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_SLA_Exception If the Class is not Loaded or If Invalid Data Provided
     */
    public function Update($_title, $_overDueHours, $_resolutionDueHours, $_slaScheduleID, $_isEnabled, $_sortOrder, $_ruleType,
            $_criteriaContainer, $_slaHolidayIDList)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_SLA_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_title) || !_is_array($_criteriaContainer)) {
            throw new SWIFT_SLA_Exception(SWIFT_INVALIDDATA);
        }

        $this->UpdatePool('title', $_title);
        $this->UpdatePool('overduehrs', floatval($_overDueHours));
        $this->UpdatePool('resolutionduehrs', floatval($_resolutionDueHours));
        $this->UpdatePool('slascheduleid', $_slaScheduleID);
        $this->UpdatePool('isenabled', (int) ($_isEnabled));
        $this->UpdatePool('sortorder',  ($_sortOrder));
        $this->UpdatePool('ruletype',  ($_ruleType));

        $this->ProcessUpdatePool();

        $this->ClearRuleCriteria();
        $this->InsertRuleCriteria($_criteriaContainer);

        SWIFT_SLAHoliday::DeleteOnSLAPlanIDList(array($this->GetSLAPlanID()));

        if (_is_array($_slaHolidayIDList))
        {
            SWIFT_SLAHoliday::LinkSLAHolidayIDList($this->GetSLAPlanID(), $_slaHolidayIDList);
        }

        // Rebuild the SLA Plan Cache
        self::RebuildCache();

        return true;
    }

    /**
     * Clears the Rule Criteria's for this SLA Plan
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_SLA_Exception If the Class is not Loaded
     */
    public function ClearRuleCriteria()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_SLA_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "slarulecriteria WHERE slaplanid = '". (int) ($this->GetSLAPlanID()) ."'");

        return true;
    }

    /**
     * Insert Criteria from a Container
     *
     * @author Varun Shoor
     * @param array $_criteriaContainer The SLA Rule Criteria Container
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_SLA_Exception If the Class is not Loaded
     */
    public function InsertRuleCriteria($_criteriaContainer)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_SLA_Exception(SWIFT_CLASSNOTLOADED);
        }

        // Insert the Criterias
        foreach ($_criteriaContainer as $_key => $_val)
        {
            if (!isset($_val[0], $_val[1], $_val[2]))
            {
                continue;
            }

            $this->Database->AutoExecute(TABLE_PREFIX . 'slarulecriteria', array('slaplanid' => (int) ($this->GetSLAPlanID()), 'name' => $_val[0],
                'ruleop' => (int) ($_val[1]), 'rulematch' => strval($_val[2]), 'rulematchtype' => (int) ($_val[3])), 'INSERT');
        }

        return true;
    }

    /**
     * Delete the SLA Plan Record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_SLA_Exception If the Class is not Loaded
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_SLA_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($this->GetSLAPlanID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a List of SLA Plans
     *
     * @author Varun Shoor
     * @param array $_slaPlanIDList The SLA Plan ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_slaPlanIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_slaPlanIDList))
        {
            return false;
        }

        $_finalSLAPlanIDList = array();
        $_index = 1;

        $_finalText = '';
        $_SWIFT->Database->Query("SELECT * FROM ". TABLE_PREFIX ."slaplans WHERE slaplanid IN (". BuildIN($_slaPlanIDList) .")");
        while ($_SWIFT->Database->NextRecord())
        {
            $_finalSLAPlanIDList[] = $_SWIFT->Database->Record['slaplanid'];
            $_finalText .= $_index . '. ' . htmlspecialchars($_SWIFT->Database->Record['title']) . '<br />';

            $_index++;
        }

        SWIFT::Info(sprintf($_SWIFT->Language->Get('titleslaplandel'), count($_finalSLAPlanIDList)), $_SWIFT->Language->Get('msgslaplandel') .
                '<br />' . $_finalText);

        if (!count($_finalSLAPlanIDList))
        {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM ". TABLE_PREFIX ."slaplans WHERE slaplanid IN (". BuildIN($_finalSLAPlanIDList) .")");
        $_SWIFT->Database->Query("DELETE FROM ". TABLE_PREFIX ."slarulecriteria WHERE slaplanid IN (". BuildIN($_finalSLAPlanIDList) .")");

        SWIFT_SLAHoliday::DeleteOnSLAPlanIDList($_finalSLAPlanIDList);
        SWIFT_EscalationRule::DeleteOnSLAPlanIDList($_finalSLAPlanIDList);

        self::RebuildCache();

        return true;
    }

    /**
     * Enable a List of SLA Plans
     *
     * @author Varun Shoor
     * @param array $_slaPlanIDList The SLA Plan ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function EnableList($_slaPlanIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_slaPlanIDList))
        {
            return false;
        }

        $_finalSLAPlanIDList = array();
        $_index = 1;

        $_finalText = '';
        $_SWIFT->Database->Query("SELECT * FROM ". TABLE_PREFIX ."slaplans WHERE slaplanid IN (". BuildIN($_slaPlanIDList) .")");
        while ($_SWIFT->Database->NextRecord())
        {
            $_finalSLAPlanIDList[] = $_SWIFT->Database->Record['slaplanid'];
            $_finalText .= $_index . '. ' . htmlspecialchars($_SWIFT->Database->Record['title']) . '<br />';

            $_index++;
        }

        SWIFT::Info(sprintf($_SWIFT->Language->Get('titleslaplanenable'), count($_finalSLAPlanIDList)), $_SWIFT->Language->Get('msgslaplanenable')
                . '<br />' . $_finalText);

        if (!count($_finalSLAPlanIDList))
        {
            return false;
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'slaplans', array('isenabled' => '1'), 'UPDATE', "slaplanid IN (" .
                BuildIN($_slaPlanIDList) . ")");

        self::RebuildCache();

        return true;
    }

    /**
     * Disable a List of SLA Plans
     *
     * @author Varun Shoor
     * @param array $_slaPlanIDList The SLA Plan ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DisableList($_slaPlanIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_slaPlanIDList))
        {
            return false;
        }

        $_finalSLAPlanIDList = array();
        $_index = 1;

        $_finalText = '';
        $_SWIFT->Database->Query("SELECT * FROM ". TABLE_PREFIX ."slaplans WHERE slaplanid IN (". BuildIN($_slaPlanIDList) .")");
        while ($_SWIFT->Database->NextRecord())
        {
            $_finalSLAPlanIDList[] = $_SWIFT->Database->Record['slaplanid'];
            $_finalText .= $_index . '. ' . htmlspecialchars($_SWIFT->Database->Record['title']) . '<br />';

            $_index++;
        }

        SWIFT::Info(sprintf($_SWIFT->Language->Get('titleslaplandisable'), count($_finalSLAPlanIDList)), $_SWIFT->Language->Get('msgslaplandisable')
                . '<br />' . $_finalText);

        if (!count($_finalSLAPlanIDList))
        {
            return false;
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'slaplans', array('isenabled' => '0'), 'UPDATE', "slaplanid IN (" .
                BuildIN($_slaPlanIDList) . ")");

        self::RebuildCache();

        return true;
    }

    /**
     * Rebuild the SLA Plan Cache
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public static function RebuildCache()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_cache = $_slaPlanIDList = array();

        $_index = 0;
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "slaplans ORDER BY sortorder ASC", 3);
        while ($_SWIFT->Database->NextRecord(3))
        {
            $_index++;

            $_cache[$_SWIFT->Database->Record3['slaplanid']] = $_SWIFT->Database->Record3;
            $_cache[$_SWIFT->Database->Record3['slaplanid']]['index'] = $_index;
            $_cache[$_SWIFT->Database->Record3['slaplanid']]['_criteria'] = array();

            $_slaPlanIDList[] = $_SWIFT->Database->Record3['slaplanid'];
        }

        if (count($_slaPlanIDList))
        {
            $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "slarulecriteria
                WHERE slaplanid IN (". BuildIN($_slaPlanIDList) . ") ORDER BY slaplanid ASC", 3);
            while ($_SWIFT->Database->NextRecord(3))
            {
                $_cache[$_SWIFT->Database->Record3['slaplanid']]['_criteria'][] = array($_SWIFT->Database->Record3['name'],
                    $_SWIFT->Database->Record3['ruleop'], $_SWIFT->Database->Record3['rulematch'], $_SWIFT->Database->Record3['rulematchtype']);
            }
        }

        $_SWIFT->Cache->Update('slaplancache', $_cache);

        return true;
    }

    /**
    * Extends the $_criteria array with custom field data (like departments etc.)
    *
    * @author Varun Shoor
    * @param array $_criteriaPointer The Criteria Pointer
    * @return bool "true" on Success, "false" otherwise
    */
    public static function ExtendCustomCriteria(&$_criteriaPointer) {
        $_SWIFT = SWIFT::GetInstance();

        // ======= BAYESIAN CATEGORIES =======
        $_field = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "bayescategories ORDER BY bayescategoryid ASC");
        while ($_SWIFT->Database->NextRecord())
        {
            $_field[] = array('title' => $_SWIFT->Database->Record['category'], 'contents' => $_SWIFT->Database->Record['bayescategoryid']);
        }

        if (!count($_field))
        {
            $_field[] = array('title' => $_SWIFT->Language->Get('notapplicable'), 'contents' => '0');
        }

        $_criteriaPointer[self::SLA_TICKETBAYESCATEGORY]['fieldcontents'] = $_field;

        // ======= TEMPLATE GROUPS =======
        $_field = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "templategroups ORDER BY tgroupid ASC");
        while ($_SWIFT->Database->NextRecord())
        {
            $_field[] = array('title' => $_SWIFT->Database->Record['title'], 'contents' => $_SWIFT->Database->Record['tgroupid']);
        }

        if (!count($_field))
        {
            $_field[] = array('title' => $_SWIFT->Language->Get('notapplicable'), 'contents' => '0');
        }

        $_criteriaPointer[self::SLA_TICKETTEMPLATEGROUP]['fieldcontents'] = $_field;

        // ======= TICKET TYPES =======
        $_field = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "tickettypes ORDER BY tickettypeid ASC");
        while ($_SWIFT->Database->NextRecord())
        {
            $_field[] = array('title' => $_SWIFT->Database->Record['title'], 'contents' => $_SWIFT->Database->Record['tickettypeid']);
        }

        if (!count($_field))
        {
            $_field[] = array('title' => $_SWIFT->Language->Get('notapplicable'), 'contents' => '0');
        }

        $_criteriaPointer[self::SLA_TICKETTYPE]['fieldcontents'] = $_field;

        // ======= TICKET STATUS =======
        $_field = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketstatus ORDER BY displayorder ASC");
        while ($_SWIFT->Database->NextRecord())
        {
            $_field[] = array('title' => $_SWIFT->Database->Record['title'], 'contents' => $_SWIFT->Database->Record['ticketstatusid']);
        }

        if (!count($_field))
        {
            $_field[] = array('title' => $_SWIFT->Language->Get('notapplicable'), 'contents' => '0');
        }

        $_criteriaPointer[self::SLA_TICKETSTATUS]['fieldcontents'] = $_field;

        // ======= TICKET PRIORITY =======
        $_field = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketpriorities ORDER BY displayorder ASC");
        while ($_SWIFT->Database->NextRecord())
        {
            $_field[] = array('title' => $_SWIFT->Database->Record['title'], 'contents' => $_SWIFT->Database->Record['priorityid']);
        }

        if (!count($_field))
        {
            $_field[] = array('title' => $_SWIFT->Language->Get('notapplicable'), 'contents' => '0');
        }

        $_criteriaPointer[self::SLA_TICKETPRIORITY]['fieldcontents'] = $_field;

        // ======= USER GROUPS =======
        $_field = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "usergroups ORDER BY title ASC");
        while ($_SWIFT->Database->NextRecord())
        {
            $_field[] = array('title' => $_SWIFT->Database->Record['title'], 'contents' => $_SWIFT->Database->Record['usergroupid']);
        }

        if (!count($_field))
        {
            $_field[] = array('title' => $_SWIFT->Language->Get('notapplicable'), 'contents' => '0');
        }

        $_criteriaPointer[self::SLA_TICKETUSERGROUP]['fieldcontents'] = $_field;

        // ======= DEPARTMENT =======
        $_field = array();

        $_departmentMapOptions = SWIFT_Department::GetDepartmentMapOptions(false, APP_TICKETS);

        foreach ($_departmentMapOptions as $_key => $_val)
        {
            $_field[] = array('title' => $_val['title'], 'contents' => $_val['value']);
        }

        if (!count($_field))
        {
            $_field[] = array('title' => $_SWIFT->Language->Get('language'), 'contents' => '0');
        }

        $_criteriaPointer[self::SLA_TICKETDEPARTMENT]['fieldcontents'] = $_field;

        // ======= TICKET OWNER =======
        $_field = array();
        $_field[] = array('title' => $_SWIFT->Language->Get('srunassigned'), 'contents' => '0');
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "staff ORDER BY fullname ASC");
        while ($_SWIFT->Database->NextRecord())
        {
            $_field[] = array('title' => $_SWIFT->Database->Record['fullname'], 'contents' => $_SWIFT->Database->Record['staffid']);
        }

        if (!count($_field))
        {
            $_field[] = array('title' => $_SWIFT->Language->Get('notapplicable'), 'contents' => '0');
        }

        $_criteriaPointer[self::SLA_TICKETOWNER]['fieldcontents'] = $_field;

        // ======= EMAIL QUEUE ID =======
        if (SWIFT_App::IsInstalled(APP_PARSER))
        {
            $_field = array();
            $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "emailqueues ORDER BY email ASC");
            while ($_SWIFT->Database->NextRecord())
            {
                $_field[] = array('title' => $_SWIFT->Database->Record['email'], 'contents' => $_SWIFT->Database->Record['emailqueueid']);
            }

            if (!count($_field))
            {
                $_field[] = array('title' => $_SWIFT->Language->Get('notapplicable'), 'contents' => '0');
            }

            $_criteriaPointer[self::SLA_TICKETEMAILQUEUE]['fieldcontents'] = $_field;
        }

        $_TicketFlagObject = new SWIFT_TicketFlag();

        // ======= FLAG TYPE =======
        $_field = array();

        $_flagContainer = $_TicketFlagObject->GetFlagList();
        foreach ($_flagContainer as $_key => $_val)
        {
            $_field[] = array('title' => $_val, 'contents' => $_key);
        }

        $_criteriaPointer[self::SLA_TICKETFLAGTYPE]['fieldcontents'] = $_field;

        // ======= CREATOR =======
        $_field = array();
        $_field[] = array('title' => $_SWIFT->Language->Get('creatorstaff'), 'contents' => SWIFT_Ticket::CREATOR_STAFF);
        $_field[] = array('title' => $_SWIFT->Language->Get('creatorclient'), 'contents' => SWIFT_Ticket::CREATOR_USER);

        $_criteriaPointer[self::SLA_TICKETCREATOR]["fieldcontents"] = $_field;

        return true;
    }

    /**
     * Return the Criteria for this Rule
     *
     * @author Varun Shoor
     * @return mixed "_criteriaPointer" (ARRAY) on Success, "false" otherwise
     */
    public static function GetCriteriaPointer()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_criteriaPointer = array();

        $_criteriaPointer[self::SLA_TICKETSTATUS]['title'] = $_SWIFT->Language->Get('sr' . self::SLA_TICKETSTATUS);
        $_criteriaPointer[self::SLA_TICKETSTATUS]['desc'] = $_SWIFT->Language->Get('desc_sr' . self::SLA_TICKETSTATUS);
        $_criteriaPointer[self::SLA_TICKETSTATUS]['op'] = 'bool';
        $_criteriaPointer[self::SLA_TICKETSTATUS]['field'] = 'custom';

        $_criteriaPointer[self::SLA_TICKETPRIORITY]['title'] = $_SWIFT->Language->Get('sr' . self::SLA_TICKETPRIORITY);
        $_criteriaPointer[self::SLA_TICKETPRIORITY]['desc'] = $_SWIFT->Language->Get('desc_sr' . self::SLA_TICKETPRIORITY);
        $_criteriaPointer[self::SLA_TICKETPRIORITY]['op'] = 'bool';
        $_criteriaPointer[self::SLA_TICKETPRIORITY]['field'] = 'custom';

        $_criteriaPointer[self::SLA_TICKETDEPARTMENT]['title'] = $_SWIFT->Language->Get('sr' . self::SLA_TICKETDEPARTMENT);
        $_criteriaPointer[self::SLA_TICKETDEPARTMENT]['desc'] = $_SWIFT->Language->Get('desc_sr' . self::SLA_TICKETDEPARTMENT);
        $_criteriaPointer[self::SLA_TICKETDEPARTMENT]['op'] = 'bool';
        $_criteriaPointer[self::SLA_TICKETDEPARTMENT]['field'] = 'custom';

        $_criteriaPointer[self::SLA_TICKETOWNER]['title'] = $_SWIFT->Language->Get('sr' . self::SLA_TICKETOWNER);
        $_criteriaPointer[self::SLA_TICKETOWNER]['desc'] = $_SWIFT->Language->Get('desc_sr' . self::SLA_TICKETOWNER);
        $_criteriaPointer[self::SLA_TICKETOWNER]['op'] = 'bool';
        $_criteriaPointer[self::SLA_TICKETOWNER]['field'] = 'custom';

        $_criteriaPointer[self::SLA_TICKETUSERGROUP]['title'] = $_SWIFT->Language->Get('sr' . self::SLA_TICKETUSERGROUP);
        $_criteriaPointer[self::SLA_TICKETUSERGROUP]['desc'] = $_SWIFT->Language->Get('desc_sr' . self::SLA_TICKETUSERGROUP);
        $_criteriaPointer[self::SLA_TICKETUSERGROUP]['op'] = 'bool';
        $_criteriaPointer[self::SLA_TICKETUSERGROUP]['field'] = 'custom';

        $_criteriaPointer[self::SLA_TICKETTEMPLATEGROUP]['title'] = $_SWIFT->Language->Get('sr' . self::SLA_TICKETTEMPLATEGROUP);
        $_criteriaPointer[self::SLA_TICKETTEMPLATEGROUP]['desc'] = $_SWIFT->Language->Get('desc_sr' . self::SLA_TICKETTEMPLATEGROUP);
        $_criteriaPointer[self::SLA_TICKETTEMPLATEGROUP]['op'] = 'bool';
        $_criteriaPointer[self::SLA_TICKETTEMPLATEGROUP]['field'] = 'custom';

        $_criteriaPointer[self::SLA_TICKETTYPE]['title'] = $_SWIFT->Language->Get('sr' . self::SLA_TICKETTYPE);
        $_criteriaPointer[self::SLA_TICKETTYPE]['desc'] = $_SWIFT->Language->Get('desc_sr' . self::SLA_TICKETTYPE);
        $_criteriaPointer[self::SLA_TICKETTYPE]['op'] = 'bool';
        $_criteriaPointer[self::SLA_TICKETTYPE]['field'] = 'custom';

        $_criteriaPointer[self::SLA_TICKETBAYESCATEGORY]['title'] = $_SWIFT->Language->Get('sr' . self::SLA_TICKETBAYESCATEGORY);
        $_criteriaPointer[self::SLA_TICKETBAYESCATEGORY]['desc'] = $_SWIFT->Language->Get('desc_sr' . self::SLA_TICKETBAYESCATEGORY);
        $_criteriaPointer[self::SLA_TICKETBAYESCATEGORY]['op'] = 'bool';
        $_criteriaPointer[self::SLA_TICKETBAYESCATEGORY]['field'] = 'custom';

        $_criteriaPointer[self::SLA_TICKETEMAILQUEUE]['title'] = $_SWIFT->Language->Get('sr' . self::SLA_TICKETEMAILQUEUE);
        $_criteriaPointer[self::SLA_TICKETEMAILQUEUE]['desc'] = $_SWIFT->Language->Get('desc_sr' . self::SLA_TICKETEMAILQUEUE);
        $_criteriaPointer[self::SLA_TICKETEMAILQUEUE]['op'] = 'bool';
        $_criteriaPointer[self::SLA_TICKETEMAILQUEUE]['field'] = 'custom';

        $_criteriaPointer[self::SLA_TICKETFLAGTYPE]['title'] = $_SWIFT->Language->Get('sr' . self::SLA_TICKETFLAGTYPE);
        $_criteriaPointer[self::SLA_TICKETFLAGTYPE]['desc'] = $_SWIFT->Language->Get('desc_sr' . self::SLA_TICKETFLAGTYPE);
        $_criteriaPointer[self::SLA_TICKETFLAGTYPE]['op'] = 'bool';
        $_criteriaPointer[self::SLA_TICKETFLAGTYPE]['field'] = 'custom';

        $_criteriaPointer[self::SLA_TICKETCREATOR]['title'] = $_SWIFT->Language->Get('sr' . self::SLA_TICKETCREATOR);
        $_criteriaPointer[self::SLA_TICKETCREATOR]['desc'] = $_SWIFT->Language->Get('desc_sr' . self::SLA_TICKETCREATOR);
        $_criteriaPointer[self::SLA_TICKETCREATOR]['op'] = 'bool';
        $_criteriaPointer[self::SLA_TICKETCREATOR]['field'] = 'custom';


        $_criteriaPointer[self::SLA_TICKETFULLNAME]['title'] = $_SWIFT->Language->Get('sr' . self::SLA_TICKETFULLNAME);
        $_criteriaPointer[self::SLA_TICKETFULLNAME]['desc'] = $_SWIFT->Language->Get('desc_sr' . self::SLA_TICKETFULLNAME);
        $_criteriaPointer[self::SLA_TICKETFULLNAME]['op'] = 'string';
        $_criteriaPointer[self::SLA_TICKETFULLNAME]['field'] = 'text';

        $_criteriaPointer[self::SLA_TICKETEMAIL]['title'] = $_SWIFT->Language->Get('sr' . self::SLA_TICKETEMAIL);
        $_criteriaPointer[self::SLA_TICKETEMAIL]['desc'] = $_SWIFT->Language->Get('desc_sr' . self::SLA_TICKETEMAIL);
        $_criteriaPointer[self::SLA_TICKETEMAIL]['op'] = 'string';
        $_criteriaPointer[self::SLA_TICKETEMAIL]['field'] = 'text';

        $_criteriaPointer[self::SLA_TICKETLASTREPLIER]['title'] = $_SWIFT->Language->Get('sr' . self::SLA_TICKETLASTREPLIER);
        $_criteriaPointer[self::SLA_TICKETLASTREPLIER]['desc'] = $_SWIFT->Language->Get('desc_sr' . self::SLA_TICKETLASTREPLIER);
        $_criteriaPointer[self::SLA_TICKETLASTREPLIER]['op'] = 'string';
        $_criteriaPointer[self::SLA_TICKETLASTREPLIER]['field'] = 'text';

        $_criteriaPointer[self::SLA_TICKETSUBJECT]['title'] = $_SWIFT->Language->Get('sr' . self::SLA_TICKETSUBJECT);
        $_criteriaPointer[self::SLA_TICKETSUBJECT]['desc'] = $_SWIFT->Language->Get('desc_sr' . self::SLA_TICKETSUBJECT);
        $_criteriaPointer[self::SLA_TICKETSUBJECT]['op'] = 'string';
        $_criteriaPointer[self::SLA_TICKETSUBJECT]['field'] = 'text';

        $_criteriaPointer[self::SLA_TICKETCHARSET]['title'] = $_SWIFT->Language->Get('sr' . self::SLA_TICKETCHARSET);
        $_criteriaPointer[self::SLA_TICKETCHARSET]['desc'] = $_SWIFT->Language->Get('desc_sr' . self::SLA_TICKETCHARSET);
        $_criteriaPointer[self::SLA_TICKETCHARSET]['op'] = 'string';
        $_criteriaPointer[self::SLA_TICKETCHARSET]['field'] = 'text';

        $_criteriaPointer[self::SLA_TICKETISRESOLVED]['title'] = $_SWIFT->Language->Get('sr' . self::SLA_TICKETISRESOLVED);
        $_criteriaPointer[self::SLA_TICKETISRESOLVED]['desc'] = $_SWIFT->Language->Get('desc_sr' . self::SLA_TICKETISRESOLVED);
        $_criteriaPointer[self::SLA_TICKETISRESOLVED]['op'] = 'bool';
        $_criteriaPointer[self::SLA_TICKETISRESOLVED]['field'] = 'bool';

        $_criteriaPointer[self::SLA_TICKETWASREOPENED]['title'] = $_SWIFT->Language->Get('sr' . self::SLA_TICKETWASREOPENED);
        $_criteriaPointer[self::SLA_TICKETWASREOPENED]['desc'] = $_SWIFT->Language->Get('desc_sr' . self::SLA_TICKETWASREOPENED);
        $_criteriaPointer[self::SLA_TICKETWASREOPENED]['op'] = 'bool';
        $_criteriaPointer[self::SLA_TICKETWASREOPENED]['field'] = 'bool';

        $_criteriaPointer[self::SLA_TICKETTOTALREPLIES]['title'] = $_SWIFT->Language->Get('sr' . self::SLA_TICKETTOTALREPLIES);
        $_criteriaPointer[self::SLA_TICKETTOTALREPLIES]['desc'] = $_SWIFT->Language->Get('desc_sr' . self::SLA_TICKETTOTALREPLIES);
        $_criteriaPointer[self::SLA_TICKETTOTALREPLIES]['op'] = 'int';
        $_criteriaPointer[self::SLA_TICKETTOTALREPLIES]['field'] = 'int';

        return $_criteriaPointer;
    }

    /**
    * Retrieves the Criteria Value
    *
    * @author Varun Shoor
    * @param string $_criteriaName The Criteria Name Pointer
    * @return bool "true" on Success, "false" otherwise
    */
    public function GetCriteriaValue($_criteriaName) {
        if (isset($this->_slaProperties[$_criteriaName])) {
            return $this->_slaProperties[$_criteriaName];
        }

        return false;
    }

    /**
     * Set the SLA Properties
     *
     * @author Varun Shoor
     * @param array $_slaProperties The SLA Properties
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function SetSLAProperties(array $_slaProperties) {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_slaProperties = $_slaProperties;

        return true;
    }

    /**
     * Retrieve the SWIFT_SLASchedule Object associated with this SLA Plan
     *
     * @author Varun Shoor
     * @return SWIFT_SLASchedule
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetScheduleObject() {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->_SWIFT_SLAScheduleObject instanceof SWIFT_SLASchedule && $this->_SWIFT_SLAScheduleObject->GetIsClassLoaded()) {
            return $this->_SWIFT_SLAScheduleObject;
        }

        $this->_SWIFT_SLAScheduleObject = new SWIFT_SLASchedule($this->GetProperty('slascheduleid'));

        return $this->_SWIFT_SLAScheduleObject;
    }
}
