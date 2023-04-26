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

namespace Tickets\Models\AutoClose;

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

/**
 * The Auto Close Rule Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_AutoCloseRule extends SWIFT_Rules
{
    const TABLE_NAME        =    'autocloserules';
    const PRIMARY_KEY        =    'autocloseruleid';

    const TABLE_STRUCTURE    =    "autocloseruleid I PRIMARY AUTO NOTNULL,
                                dateline I DEFAULT '0' NOTNULL,
                                title C(100) DEFAULT '' NOTNULL,
                                targetticketstatusid I DEFAULT '0' NOTNULL,
                                inactivitythreshold F DEFAULT '0' NOTNULL,
                                closurethreshold F DEFAULT '0' NOTNULL,
                                sendpendingnotification I DEFAULT '0' NOTNULL,
                                sendfinalnotification I DEFAULT '0' NOTNULL,
                                suppresssurveyemail I DEFAULT '0' NOTNULL,
                                isenabled I DEFAULT '0' NOTNULL,
                                sortorder I DEFAULT '0' NOTNULL";

    const INDEX_1            =    'isenabled, sortorder';
    const INDEX_2            =    'title'; // Unified Search


    protected $_dataStore = array();
    protected $_autoCloseRuleProperties = array();

    // Criteria
    const AUTOCLOSE_TICKETSTATUS = 'ticketstatusid';
    const AUTOCLOSE_TICKETTYPE = 'tickettypeid';
    const AUTOCLOSE_TICKETPRIORITY = 'priorityid';
    const AUTOCLOSE_TICKETDEPARTMENT = 'departmentid';
    const AUTOCLOSE_TICKETOWNER = 'ownerstaffid';
    const AUTOCLOSE_TICKETEMAILQUEUE = 'emailqueueid';
    const AUTOCLOSE_TICKETFLAGTYPE = 'flagtype';
    const AUTOCLOSE_TICKETCREATOR = 'creator';
    const AUTOCLOSE_TICKETUSERGROUP = 'usergroupid';
    const AUTOCLOSE_TICKETTEMPLATEGROUP = 'templategroupid';
    const AUTOCLOSE_TICKETBAYESCATEGORY = 'bayescategoryid';

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
            throw new SWIFT_Exception('Failed to load Auto Close Object');
        }

        $this->SetIsClassLoaded(true);

        if (!$this->SetCriteria($this->GetProperty('_criteria')) || !$this->SetMatchType(SWIFT_Rules::RULE_MATCHEXTENDED)) {
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
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ProcessUpdatePool()
    {
        if (!$this->GetIsClassLoaded())
        {
            return false;
        } else if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'autocloserules', $this->GetUpdatePool(), 'UPDATE', "autocloseruleid = '" . (int) ($this->GetAutoCloseRuleID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Auto Close Rule ID
     *
     * @author Varun Shoor
     * @return mixed "autocloseruleid" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetAutoCloseRuleID()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['autocloseruleid'];
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
            $_dataStore = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "autocloserules WHERE autocloseruleid = '" . (int) ($_SWIFT_DataObject->GetDataID()) . "'");
            if (isset($_dataStore['autocloseruleid']) && !empty($_dataStore['autocloseruleid']))
            {
                $_dataStore['_criteria'] = array();

                $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "autoclosecriteria WHERE autocloseruleid = '" . (int) ($_SWIFT_DataObject->GetDataID()) . "'");
                while ($_SWIFT->Database->NextRecord()) {
                    $_dataStore['_criteria'][$_SWIFT->Database->Record['autoclosecriteriaid']] = array($_SWIFT->Database->Record['name'],
                        $_SWIFT->Database->Record['ruleop'], $_SWIFT->Database->Record['rulematch'], $_SWIFT->Database->Record['rulematchtype']);
                }

                $this->_dataStore = $_dataStore;

                return true;
            }

        // Is it a Store?
        } else if ($_SWIFT_DataObject instanceof SWIFT_DataStore && $_SWIFT_DataObject->GetIsClassLoaded()) {
            $this->_dataStore = $_SWIFT_DataObject->GetDataStore();

            if (!isset($this->_dataStore['autocloseruleid']) || empty($this->_dataStore['autocloseruleid']))
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
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetDataStore()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore;
    }

    /**
     * Retrieves a Property Value from Data Store
     *
     * @author Varun Shoor
     * @param string $_key The Key Identifier
     * @return mixed Property Data on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data Provided
     */
    public function GetProperty($_key)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA . ': ' . $_key);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Create a new auto close rule
     *
     * @author Varun Shoor
     * @param string $_title The Auto Close Rule Title
     * @param int $_targetTicketStatusID The Ticket Status ID to change the ticket to on closure
     * @param float $_inactivityThreshold The Initial Warning Threshold when the Pending Notification is sent to client (In Hours)
     * @param float $_closureThreshold The Closure Threshold when the Final Notification is sent to client and ticket status is set to $_targetTicketStatusID (In Hours)
     * @param bool $_sendPendingNotification Whether to send the pending notification
     * @param bool $_sendFinalNotification Whether to send the final notification
     * @param bool $_isEnabled Whether the Auto Close Rule is Enabled/Disabled
     * @param int $_sortOrder The Rule Sort Order
     * @param array $_criteriaContainer The Array Containing the Criterias for this Auto Close Rule
     * @param bool $_suppressSurveyEmail (OPTIONAL) Whether to suppress survey email
     * @return SWIFT_AutoCloseRule "_SWIFT_AutoCloseRuleObject" (OBJECT) on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data Provided or If Creation Fails
     */
    public static function Create($_title, $_targetTicketStatusID, $_inactivityThreshold, $_closureThreshold, $_sendPendingNotification, $_sendFinalNotification, $_isEnabled,
            $_sortOrder, $_criteriaContainer, $_suppressSurveyEmail = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_ticketStatusCache = $_SWIFT->Cache->Get('statuscache');

        if (empty($_title) || !_is_array($_criteriaContainer) || empty ($_targetTicketStatusID) || !isset($_ticketStatusCache[$_targetTicketStatusID]))
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'autocloserules', array('title' => $_title, 'targetticketstatusid' =>  ($_targetTicketStatusID),
            'inactivitythreshold' => floatval($_inactivityThreshold), 'closurethreshold' => floatval($_closureThreshold), 'sendpendingnotification' => (int) ($_sendPendingNotification),
            'sendfinalnotification' => (int) ($_sendFinalNotification), 'isenabled' => (int) ($_isEnabled),
            'sortorder' =>  ($_sortOrder), 'dateline' => DATENOW, 'suppresssurveyemail' => (int) ($_suppressSurveyEmail)), 'INSERT');
        $_autoCloseRuleID = $_SWIFT->Database->Insert_ID();

        if (!$_autoCloseRuleID)
        {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        $_SWIFT_AutoCloseRuleObject = new SWIFT_AutoCloseRule(new SWIFT_DataID($_autoCloseRuleID));
        if (!$_SWIFT_AutoCloseRuleObject instanceof SWIFT_AutoCloseRule || !$_SWIFT_AutoCloseRuleObject->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        $_SWIFT_AutoCloseRuleObject->InsertRuleCriteria($_criteriaContainer);

        // Rebuild the Auto Close Rule Cache
        self::RebuildCache();

        return $_SWIFT_AutoCloseRuleObject;
    }

    /**
     * Update the Auto Close Rule Record
     *
     * @author Varun Shoor
     * @param string $_title The Auto Close Rule Title
     * @param int $_targetTicketStatusID The Ticket Status ID to change the ticket to on closure
     * @param float $_inactivityThreshold The Initial Warning Threshold when the Pending Notification is sent to client (In Hours)
     * @param float $_closureThreshold The Closure Threshold when the Final Notification is sent to client and ticket status is set to $_targetTicketStatusID (In Hours)
     * @param bool $_sendPendingNotification Whether to send the pending notification
     * @param bool $_sendFinalNotification Whether to send the final notification
     * @param bool $_isEnabled Whether the Auto Close Rule is Enabled/Disabled
     * @param int $_sortOrder The Rule Sort Order
     * @param array $_criteriaContainer The Array Containing the Criterias for this Auto Close Rule
     * @param bool $_suppressSurveyEmail (OPTIONAL) Whether to suppress survey email
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data Provided
     */
    public function Update($_title, $_targetTicketStatusID, $_inactivityThreshold, $_closureThreshold, $_sendPendingNotification, $_sendFinalNotification, $_isEnabled,
            $_sortOrder, $_criteriaContainer, $_suppressSurveyEmail = false)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_title) || !_is_array($_criteriaContainer)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->UpdatePool('title', $_title);
        $this->UpdatePool('targetticketstatusid',  ($_targetTicketStatusID));
        $this->UpdatePool('inactivitythreshold', floatval($_inactivityThreshold));
        $this->UpdatePool('closurethreshold', floatval($_closureThreshold));
        $this->UpdatePool('sendpendingnotification', (int) ($_sendPendingNotification));
        $this->UpdatePool('sendfinalnotification', (int) ($_sendFinalNotification));
        $this->UpdatePool('isenabled', (int) ($_isEnabled));
        $this->UpdatePool('sortorder',  ($_sortOrder));
        $this->UpdatePool('suppresssurveyemail', (int) ($_suppressSurveyEmail));

        $this->ProcessUpdatePool();

        $this->ClearRuleCriteria();
        $this->InsertRuleCriteria($_criteriaContainer);

        // Rebuild the Auto Close Rule Cache
        self::RebuildCache();

        return true;
    }

    /**
     * Clears the Rule Criteria's for this Auto Close Rule
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ClearRuleCriteria()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "autoclosecriteria WHERE autocloseruleid = '" . (int) ($this->GetAutoCloseRuleID()) . "'");

        return true;
    }

    /**
     * Insert Criteria from a Container
     *
     * @author Varun Shoor
     * @param array $_criteriaContainer The Auto Close Rule Criteria Container
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function InsertRuleCriteria($_criteriaContainer)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        // Insert the Criterias
        foreach ($_criteriaContainer as $_val)
        {
            if (!isset($_val[0], $_val[1], $_val[2]))
            {
                continue;
            }

            $this->Database->AutoExecute(TABLE_PREFIX . 'autoclosecriteria', array('autocloseruleid' => (int) ($this->GetAutoCloseRuleID()), 'name' => $_val[0],
                'ruleop' => (int) ($_val[1]), 'rulematch' => strval($_val[2]), 'rulematchtype' => (int) ($_val[3])), 'INSERT');
        }

        return true;
    }

    /**
     * Delete the Auto Close Rule Record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($this->GetAutoCloseRuleID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a List of Auto Close Rules
     *
     * @author Varun Shoor
     * @param array $_autoCloseRuleIDList The Auto Close Rule ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_autoCloseRuleIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_autoCloseRuleIDList))
        {
            return false;
        }

        $_finalAutoCloseRuleIDList = array();
        $_index = 1;

        $_finalText = '';
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "autocloserules WHERE autocloseruleid IN (". BuildIN($_autoCloseRuleIDList) .")");
        while ($_SWIFT->Database->NextRecord())
        {
            $_finalAutoCloseRuleIDList[] = $_SWIFT->Database->Record['autocloseruleid'];
            $_finalText .= $_index . '. ' . htmlspecialchars($_SWIFT->Database->Record['title']) . '<br />';

            $_index++;
        }

        SWIFT::Info(sprintf($_SWIFT->Language->Get('titleautocloseruledel'), count($_finalAutoCloseRuleIDList)), $_SWIFT->Language->Get('msgautocloseruledel') .
                '<br />' . $_finalText);

        if (!count($_finalAutoCloseRuleIDList))
        {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "autocloserules WHERE autocloseruleid IN (" . BuildIN($_finalAutoCloseRuleIDList) . ")");
        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "autoclosecriteria WHERE autocloseruleid IN (" . BuildIN($_finalAutoCloseRuleIDList) . ")");

        /**
         * BUG FIX - Nidhi Gupta
         *
         * SWIFT-3504 Tickets should be unlinked from autoclose rule in database, if autoclose is deleted in the help desk
         *
         * Comments: Code to unlink autoclose rule
         **/
        $_ticketIDList = array();
        $_SWIFT->Database->Query("Select ticketid FROM " . TABLE_PREFIX . "tickets WHERE autocloseruleid IN (" . BuildIN($_finalAutoCloseRuleIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_ticketIDList[] = $_SWIFT->Database->Record['ticketid'];
        }
        if (count($_ticketIDList) != 0) {
            $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'tickets', array('autocloseruleid' => '0', 'autoclosestatus' => '0', 'autoclosetimeline' => '0'), 'UPDATE', "autocloseruleid  IN (" . BuildIN($_finalAutoCloseRuleIDList) . ")");
        }
        self::RebuildCache();
        return true;
    }

    /**
     * Enable a List of Auto Close Rules
     *
     * @author Varun Shoor
     * @param array $_autoCloseRuleIDList The Auto Close Rule ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function EnableList($_autoCloseRuleIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_autoCloseRuleIDList))
        {
            return false;
        }

        $_finalAutoCloseRuleIDList = array();
        $_index = 1;

        $_finalText = '';
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "autocloserules WHERE autocloseruleid IN (". BuildIN($_autoCloseRuleIDList) .")");
        while ($_SWIFT->Database->NextRecord())
        {
            $_finalAutoCloseRuleIDList[] = $_SWIFT->Database->Record['autocloseruleid'];
            $_finalText .= $_index . '. ' . htmlspecialchars($_SWIFT->Database->Record['title']) . '<br />';

            $_index++;
        }

        SWIFT::Info(sprintf($_SWIFT->Language->Get('titleautocloseruleenable'), count($_finalAutoCloseRuleIDList)), $_SWIFT->Language->Get('msgautocloseruleenable')
                . '<br />' . $_finalText);

        if (!count($_finalAutoCloseRuleIDList))
        {
            return false;
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'autocloserules', array('isenabled' => '1'), 'UPDATE', "autocloseruleid IN (" . BuildIN($_finalAutoCloseRuleIDList) . ")");

        self::RebuildCache();

        return true;
    }

    /**
     * Disable a List of Auto Close Rules
     *
     * @author Varun Shoor
     * @param array $_autoCloseRuleIDList The Auto Close Rule ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DisableList($_autoCloseRuleIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_autoCloseRuleIDList))
        {
            return false;
        }

        $_finalAutoCloseRuleIDList = array();
        $_index = 1;

        $_finalText = '';
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "autocloserules WHERE autocloseruleid IN (" . BuildIN($_autoCloseRuleIDList) . ")");
        while ($_SWIFT->Database->NextRecord())
        {
            $_finalAutoCloseRuleIDList[] = $_SWIFT->Database->Record['autocloseruleid'];
            $_finalText .= $_index . '. ' . htmlspecialchars($_SWIFT->Database->Record['title']) . '<br />';

            $_index++;
        }

        SWIFT::Info(sprintf($_SWIFT->Language->Get('titleautocloseruledisable'), count($_finalAutoCloseRuleIDList)), $_SWIFT->Language->Get('msgautocloseruledisable')
                . '<br />' . $_finalText);

        if (!count($_finalAutoCloseRuleIDList))
        {
            return false;
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'autocloserules', array('isenabled' => '0'), 'UPDATE', "autocloseruleid IN (" . BuildIN($_finalAutoCloseRuleIDList) . ")");

        self::RebuildCache();

        return true;
    }

    /**
     * Rebuild the Auto Close Rules Cache
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public static function RebuildCache()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_cache = $_autoCloseRuleIDList = array();

        $_index = 0;
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "autocloserules
            ORDER BY autocloseruleid ASC", 3);
        while ($_SWIFT->Database->NextRecord(3))
        {
            $_index++;

            $_cache[$_SWIFT->Database->Record3['autocloseruleid']] = $_SWIFT->Database->Record3;
            $_cache[$_SWIFT->Database->Record3['autocloseruleid']]['index'] = $_index;
            $_cache[$_SWIFT->Database->Record3['autocloseruleid']]['_criteria'] = array();

            $_autoCloseRuleIDList[] = $_SWIFT->Database->Record3['autocloseruleid'];
        }

        if (count($_autoCloseRuleIDList))
        {
            $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "autoclosecriteria
                WHERE autocloseruleid IN (" . BuildIN($_autoCloseRuleIDList) . ")
                    ORDER BY autocloseruleid ASC", 3);
            while ($_SWIFT->Database->NextRecord(3))
            {
                $_cache[$_SWIFT->Database->Record3['autocloseruleid']]['_criteria'][] = array($_SWIFT->Database->Record3['name'],
                    $_SWIFT->Database->Record3['ruleop'], $_SWIFT->Database->Record3['rulematch'], $_SWIFT->Database->Record3['rulematchtype']);
            }
        }

        $_SWIFT->Cache->Update('autocloserulecache', $_cache);

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

        $_criteriaPointer[self::AUTOCLOSE_TICKETBAYESCATEGORY]['fieldcontents'] = $_field;

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

        $_criteriaPointer[self::AUTOCLOSE_TICKETTEMPLATEGROUP]['fieldcontents'] = $_field;

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

        $_criteriaPointer[self::AUTOCLOSE_TICKETTYPE]['fieldcontents'] = $_field;

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

        $_criteriaPointer[self::AUTOCLOSE_TICKETSTATUS]['fieldcontents'] = $_field;

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

        $_criteriaPointer[self::AUTOCLOSE_TICKETPRIORITY]['fieldcontents'] = $_field;

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

        $_criteriaPointer[self::AUTOCLOSE_TICKETUSERGROUP]['fieldcontents'] = $_field;

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

        $_criteriaPointer[self::AUTOCLOSE_TICKETDEPARTMENT]['fieldcontents'] = $_field;

        // ======= TICKET OWNER =======
        $_field = array();
        $_field[] = array('title' => $_SWIFT->Language->Get('arunassigned'), 'contents' => '0');
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "staff ORDER BY fullname ASC");
        while ($_SWIFT->Database->NextRecord())
        {
            $_field[] = array('title' => $_SWIFT->Database->Record['fullname'], 'contents' => $_SWIFT->Database->Record['staffid']);
        }

        if (!count($_field))
        {
            $_field[] = array('title' => $_SWIFT->Language->Get('notapplicable'), 'contents' => '0');
        }

        $_criteriaPointer[self::AUTOCLOSE_TICKETOWNER]['fieldcontents'] = $_field;

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

            $_criteriaPointer[self::AUTOCLOSE_TICKETEMAILQUEUE]['fieldcontents'] = $_field;
        }

        $_TicketFlagObject = new SWIFT_TicketFlag();

        // ======= FLAG TYPE =======
        $_field = array();

        $_flagContainer = $_TicketFlagObject->GetFlagList();
        foreach ($_flagContainer as $_key => $_val)
        {
            $_field[] = array('title' => $_val, 'contents' => $_key);
        }

        $_criteriaPointer[self::AUTOCLOSE_TICKETFLAGTYPE]['fieldcontents'] = $_field;

        // ======= CREATOR =======
        $_field = array();
        $_field[] = array('title' => $_SWIFT->Language->Get('creatorstaff'), 'contents' => SWIFT_Ticket::CREATOR_STAFF);
        $_field[] = array('title' => $_SWIFT->Language->Get('creatorclient'), 'contents' => SWIFT_Ticket::CREATOR_USER);

        $_criteriaPointer[self::AUTOCLOSE_TICKETCREATOR]["fieldcontents"] = $_field;

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

        $_criteriaPointer[self::AUTOCLOSE_TICKETSTATUS]['title'] = $_SWIFT->Language->Get('ar' . self::AUTOCLOSE_TICKETSTATUS);
        $_criteriaPointer[self::AUTOCLOSE_TICKETSTATUS]['desc'] = $_SWIFT->Language->Get('desc_ar' . self::AUTOCLOSE_TICKETSTATUS);
        $_criteriaPointer[self::AUTOCLOSE_TICKETSTATUS]['op'] = 'bool';
        $_criteriaPointer[self::AUTOCLOSE_TICKETSTATUS]['field'] = 'custom';

        $_criteriaPointer[self::AUTOCLOSE_TICKETTYPE]['title'] = $_SWIFT->Language->Get('ar' . self::AUTOCLOSE_TICKETTYPE);
        $_criteriaPointer[self::AUTOCLOSE_TICKETTYPE]['desc'] = $_SWIFT->Language->Get('desc_r' . self::AUTOCLOSE_TICKETTYPE);
        $_criteriaPointer[self::AUTOCLOSE_TICKETTYPE]['op'] = 'bool';
        $_criteriaPointer[self::AUTOCLOSE_TICKETTYPE]['field'] = 'custom';

        $_criteriaPointer[self::AUTOCLOSE_TICKETPRIORITY]['title'] = $_SWIFT->Language->Get('ar' . self::AUTOCLOSE_TICKETPRIORITY);
        $_criteriaPointer[self::AUTOCLOSE_TICKETPRIORITY]['desc'] = $_SWIFT->Language->Get('desc_ar' . self::AUTOCLOSE_TICKETPRIORITY);
        $_criteriaPointer[self::AUTOCLOSE_TICKETPRIORITY]['op'] = 'bool';
        $_criteriaPointer[self::AUTOCLOSE_TICKETPRIORITY]['field'] = 'custom';

        $_criteriaPointer[self::AUTOCLOSE_TICKETDEPARTMENT]['title'] = $_SWIFT->Language->Get('ar' . self::AUTOCLOSE_TICKETDEPARTMENT);
        $_criteriaPointer[self::AUTOCLOSE_TICKETDEPARTMENT]['desc'] = $_SWIFT->Language->Get('desc_ar' . self::AUTOCLOSE_TICKETDEPARTMENT);
        $_criteriaPointer[self::AUTOCLOSE_TICKETDEPARTMENT]['op'] = 'bool';
        $_criteriaPointer[self::AUTOCLOSE_TICKETDEPARTMENT]['field'] = 'custom';

        $_criteriaPointer[self::AUTOCLOSE_TICKETOWNER]['title'] = $_SWIFT->Language->Get('ar' . self::AUTOCLOSE_TICKETOWNER);
        $_criteriaPointer[self::AUTOCLOSE_TICKETOWNER]['desc'] = $_SWIFT->Language->Get('desc_ar' . self::AUTOCLOSE_TICKETOWNER);
        $_criteriaPointer[self::AUTOCLOSE_TICKETOWNER]['op'] = 'bool';
        $_criteriaPointer[self::AUTOCLOSE_TICKETOWNER]['field'] = 'custom';

        $_criteriaPointer[self::AUTOCLOSE_TICKETUSERGROUP]['title'] = $_SWIFT->Language->Get('ar' . self::AUTOCLOSE_TICKETUSERGROUP);
        $_criteriaPointer[self::AUTOCLOSE_TICKETUSERGROUP]['desc'] = $_SWIFT->Language->Get('desc_ar' . self::AUTOCLOSE_TICKETUSERGROUP);
        $_criteriaPointer[self::AUTOCLOSE_TICKETUSERGROUP]['op'] = 'bool';
        $_criteriaPointer[self::AUTOCLOSE_TICKETUSERGROUP]['field'] = 'custom';

        $_criteriaPointer[self::AUTOCLOSE_TICKETTEMPLATEGROUP]['title'] = $_SWIFT->Language->Get('ar' . self::AUTOCLOSE_TICKETTEMPLATEGROUP);
        $_criteriaPointer[self::AUTOCLOSE_TICKETTEMPLATEGROUP]['desc'] = $_SWIFT->Language->Get('desc_ar' . self::AUTOCLOSE_TICKETTEMPLATEGROUP);
        $_criteriaPointer[self::AUTOCLOSE_TICKETTEMPLATEGROUP]['op'] = 'bool';
        $_criteriaPointer[self::AUTOCLOSE_TICKETTEMPLATEGROUP]['field'] = 'custom';

        $_criteriaPointer[self::AUTOCLOSE_TICKETBAYESCATEGORY]['title'] = $_SWIFT->Language->Get('ar' . self::AUTOCLOSE_TICKETBAYESCATEGORY);
        $_criteriaPointer[self::AUTOCLOSE_TICKETBAYESCATEGORY]['desc'] = $_SWIFT->Language->Get('desc_ar' . self::AUTOCLOSE_TICKETBAYESCATEGORY);
        $_criteriaPointer[self::AUTOCLOSE_TICKETBAYESCATEGORY]['op'] = 'bool';
        $_criteriaPointer[self::AUTOCLOSE_TICKETBAYESCATEGORY]['field'] = 'custom';

        $_criteriaPointer[self::AUTOCLOSE_TICKETEMAILQUEUE]['title'] = $_SWIFT->Language->Get('ar' . self::AUTOCLOSE_TICKETEMAILQUEUE);
        $_criteriaPointer[self::AUTOCLOSE_TICKETEMAILQUEUE]['desc'] = $_SWIFT->Language->Get('desc_ar' . self::AUTOCLOSE_TICKETEMAILQUEUE);
        $_criteriaPointer[self::AUTOCLOSE_TICKETEMAILQUEUE]['op'] = 'bool';
        $_criteriaPointer[self::AUTOCLOSE_TICKETEMAILQUEUE]['field'] = 'custom';

        $_criteriaPointer[self::AUTOCLOSE_TICKETFLAGTYPE]['title'] = $_SWIFT->Language->Get('ar' . self::AUTOCLOSE_TICKETFLAGTYPE);
        $_criteriaPointer[self::AUTOCLOSE_TICKETFLAGTYPE]['desc'] = $_SWIFT->Language->Get('desc_ar' . self::AUTOCLOSE_TICKETFLAGTYPE);
        $_criteriaPointer[self::AUTOCLOSE_TICKETFLAGTYPE]['op'] = 'bool';
        $_criteriaPointer[self::AUTOCLOSE_TICKETFLAGTYPE]['field'] = 'custom';

        $_criteriaPointer[self::AUTOCLOSE_TICKETCREATOR]['title'] = $_SWIFT->Language->Get('ar' . self::AUTOCLOSE_TICKETCREATOR);
        $_criteriaPointer[self::AUTOCLOSE_TICKETCREATOR]['desc'] = $_SWIFT->Language->Get('desc_ar' . self::AUTOCLOSE_TICKETCREATOR);
        $_criteriaPointer[self::AUTOCLOSE_TICKETCREATOR]['op'] = 'bool';
        $_criteriaPointer[self::AUTOCLOSE_TICKETCREATOR]['field'] = 'custom';


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
        if (isset($this->_autoCloseRuleProperties[$_criteriaName])) {
            return $this->_autoCloseRuleProperties[$_criteriaName];
        }

        return false;
    }

    /**
     * Set the Auto Close Rule Properties
     *
     * @author Varun Shoor
     * @param array $_autoCloseRuleProperties The Auto Close Rule Properties
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function SetRuleProperties(array $_autoCloseRuleProperties) {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_autoCloseRuleProperties = $_autoCloseRuleProperties;

        return true;
    }

}
