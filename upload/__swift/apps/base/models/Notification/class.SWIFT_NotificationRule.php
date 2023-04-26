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

namespace Base\Models\Notification;

use Base\Models\Department\SWIFT_Department;
use SWIFT;
use SWIFT_App;
use SWIFT_Base;
use SWIFT_Data;
use SWIFT_DataID;
use SWIFT_DataStore;
use SWIFT_Exception;
use SWIFT_Loader;
use Base\Library\Rules\SWIFT_Rules;
use Base\Models\User\SWIFT_User;
use Tickets\Library\Flag\SWIFT_TicketFlag;
use Tickets\Library\Ticket\SWIFT_Ticket_Exception;
use Tickets\Models\Ticket\SWIFT_Ticket;

/**
 * The Notification Rule Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_NotificationRule extends SWIFT_Rules
{
    const TABLE_NAME = 'notificationrules';
    const PRIMARY_KEY = 'notificationruleid';

    const TABLE_STRUCTURE = "notificationruleid I PRIMARY AUTO NOTNULL,
                                title C(255) NOTNULL,
                                ruletype I2 DEFAULT '0' NOTNULL,
                                staffid I DEFAULT '0' NOTNULL,
                                emailprefix C(255) DEFAULT '' NOTNULL,
                                isenabled I2 DEFAULT '0' NOTNULL,
                                dateline I DEFAULT '0' NOTNULL";

    const INDEX_1 = 'ruletype, isenabled';
    const INDEX_2 = 'isenabled';


    protected $_dataStore = array();

    protected $_propertiesContainer = array();
    protected $_changeContainer = array();

    // Core Constants
    const TYPE_TICKET = 1;
    const TYPE_CHAT = 2;
    const TYPE_MESSAGE = 3;
    const TYPE_SURVEY = 4;
    const TYPE_USER = 5;

    /**
     * ---------------------------------------------
     * BEGIN TICKET CRITERIA TAGS
     * ---------------------------------------------
     */

    // Custom
    const CRITERIA_TICKETEVENT = 'ticketevent';
    const CRITERIA_TICKETSTATUS = 'ticketstatus';
    const CRITERIA_TICKETPRIORITY = 'ticketpriority';
    const CRITERIA_TICKETUSERGROUP = 'usergroup';
    const CRITERIA_TICKETTYPE = 'tickettype';
    const CRITERIA_BAYESIAN = 'bayesian';
    const CRITERIA_DEPARTMENT = 'department';
    const CRITERIA_OWNER = 'owner';
    const CRITERIA_EMAILQUEUE = 'emailqueue';
    const CRITERIA_FLAGTYPE = 'flagtype';
    const CRITERIA_SLAPLAN = 'slaplan';
    const CRITERIA_TEMPLATEGROUP = 'templategroup';
    const CRITERIA_CREATOR = 'creator';

    // Calendar
    const CRITERIA_DATERANGE = 'daterange';
    const CRITERIA_LASTACTIVITYRANGE = 'lastactivityrange';
    const CRITERIA_LASTSTAFFREPLYRANGE = 'laststaffreplyrange';
    const CRITERIA_LASTUSERREPLYRANGE = 'lastuserreplyrange';
    const CRITERIA_DUERANGE = 'duerange';
    const CRITERIA_RESOLUTIONDUERANGE = 'resolutionduerange';

    // Integer
    const CRITERIA_TIMEWORKED = 'timeworked';
    const CRITERIA_TOTALREPLIES = 'totalreplies';
    const CRITERIA_PENDINGFOLLOWUPS = 'pendingfollowups';

    // Boolean
    const CRITERIA_ISEMAILED = 'isemailed';
    const CRITERIA_ISEDITED = 'isedited';
    const CRITERIA_HASNOTES = 'hasnotes';
    const CRITERIA_HASATTACHMENTS = 'hasattachments';
    const CRITERIA_ISESCALATED = 'isescalated';
    const CRITERIA_HASDRAFT = 'hasdraft';
    const CRITERIA_HASBILLING = 'hasbilling';
    const CRITERIA_ISPHONECALL = 'isphonecall';
    const CRITERIA_ISOVERDUE = 'isoverdue';
    const CRITERIA_ISRESOLUTIONOVERDUE = 'isresolutionoverdue';

    /**
     * ---------------------------------------------
     * END TICKET CRITERIA TAGS
     * ---------------------------------------------
     */

    /**
     * ---------------------------------------------
     * BEGIN USER CRITERIA TAGS
     * ---------------------------------------------
     */

    // Custom
    const CRITERIA_USEREVENT = 'userevent';
    const CRITERIA_USERGROUP = 'usergroup';

    /**
     * ---------------------------------------------
     * END USER CRITERIA TAGS
     * ---------------------------------------------
     */

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param SWIFT_Data $_SWIFT_DataObject The SWIFT_Data Object
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Record could not be loaded
     */
    public function __construct(SWIFT_Data $_SWIFT_DataObject)
    {
        if (!$_SWIFT_DataObject instanceof SWIFT_Data || !$_SWIFT_DataObject->GetIsClassLoaded() || !$this->LoadData($_SWIFT_DataObject)) {
            throw new SWIFT_Exception('Failed to load Notification Object');
        }

        $this->SetIsClassLoaded(true);
        parent::__construct($this->GetProperty('_criteria'), SWIFT_Rules::RULE_MATCHEXTENDED);
    }

    /**
     * Destructor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
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

        $this->Database->AutoExecute(TABLE_PREFIX . 'notificationrules', $this->GetUpdatePool(), 'UPDATE', "notificationruleid = '" . (int)($this->GetNotificationRuleID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Notification Rule ID
     *
     * @author Varun Shoor
     * @return mixed "notificationruleid" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetNotificationRuleID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['notificationruleid'];
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
            $_dataStore = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "notificationrules WHERE notificationruleid = '" . (int)($_SWIFT_DataObject->GetDataID()) . "'");
            if (isset($_dataStore['notificationruleid']) && !empty($_dataStore['notificationruleid'])) {
                $_dataStore['_criteria'] = array();

                $_criteriaPointer = self::GetCriteriaPointer($_dataStore['ruletype']);

                $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "notificationcriteria WHERE notificationruleid = '" .
                    (int)($_SWIFT_DataObject->GetDataID()) . "'");
                while ($_SWIFT->Database->NextRecord()) {
                    $_dataStore['_criteria'][$_SWIFT->Database->Record['notificationcriteriaid']] = array($_SWIFT->Database->Record['name'],
                        $_SWIFT->Database->Record['ruleop'], $_SWIFT->Database->Record['rulematch'], $_criteriaPointer[$_SWIFT->Database->Record['name']]['field'],
                        $_SWIFT->Database->Record['rulematchtype']);
                }
                $this->_dataStore = $_dataStore;

                return true;
            }

            // Is it a Store?
        } else if ($_SWIFT_DataObject instanceof SWIFT_DataStore && $_SWIFT_DataObject->GetIsClassLoaded()) {
            $this->_dataStore = $_SWIFT_DataObject->GetDataStore();

            if (!isset($this->_dataStore['notificationruleid']) || empty($this->_dataStore['notificationruleid'])) {
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
        if (!$this->GetIsClassLoaded()) {
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
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetProperty($_key)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
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
    public static function IsValidRuleType($_ruleType)
    {
        if ($_ruleType == self::TYPE_CHAT || $_ruleType == self::TYPE_MESSAGE || $_ruleType == self::TYPE_SURVEY || $_ruleType == self::TYPE_TICKET || $_ruleType == self::TYPE_USER) {
            return true;
        }

        return false;
    }

    /**
     * Create a new Notification Rule
     *
     * @author Varun Shoor
     * @param string $_ruleTitle The Rule Title
     * @param mixed $_ruleType The Rule Type
     * @param bool $_isEnabled Whether this rule is enabled
     * @param array $_criteriaContainer The Criteria Container
     * @param array $_actionsContainer The Actions Container
     * @param int $_staffID (OPTIONAL) The Staff ID
     * @param string $_emailPrefix (OPTIONAL) The Email Prefix
     * @return int The Notification Rule ID
     * @throws SWIFT_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function Create($_ruleTitle, $_ruleType, $_isEnabled, $_criteriaContainer, $_actionsContainer, $_staffID = 0, $_emailPrefix = '')
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_ruleTitle) || !self::IsValidRuleType($_ruleType) || !_is_array($_criteriaContainer)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'notificationrules', array('title' => $_ruleTitle, 'ruletype' => (int)($_ruleType), 'isenabled' => (int)($_isEnabled),
            'staffid' => $_staffID, 'dateline' => DATENOW, 'emailprefix' => $_emailPrefix), 'INSERT');
        $_notificationRuleID = $_SWIFT->Database->Insert_ID();

        if (!$_notificationRuleID) {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        foreach ($_criteriaContainer as $_criteria) {
            if (!isset($_criteria[0], $_criteria[1], $_criteria[2], $_criteria[3])) {
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            }

            if ($_criteria[1] == SWIFT_Rules::OP_CHANGED || $_criteria[1] == SWIFT_Rules::OP_NOTCHANGED) {
                $_criteria[2] = 0;
            }

            SWIFT_NotificationCriteria::Create($_notificationRuleID, $_criteria[0], $_criteria[1], $_criteria[2], $_criteria[3]);
        }

        foreach ($_actionsContainer as $_action) {
            if (!isset($_action[0], $_action[1])) {
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            }

            SWIFT_NotificationAction::Create($_notificationRuleID, $_action[0], $_action[1]);
        }

        self::RebuildCache();

        return $_notificationRuleID;
    }

    /**
     * Update the Notification Rule Record
     *
     * @author Varun Shoor
     * @param string $_ruleTitle The Rule Title
     * @param bool $_isEnabled Whether this rule is enabled
     * @param array $_criteriaContainer The Criteria Container
     * @param array $_actionsContainer The Actions Container
     * @param string $_emailPrefix (OPTIONAL) The Email Prefix
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function Update($_ruleTitle, $_isEnabled, $_criteriaContainer, $_actionsContainer, $_emailPrefix = '')
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_ruleTitle) || !_is_array($_criteriaContainer)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->UpdatePool('title', $_ruleTitle);
        $this->UpdatePool('emailprefix', $_emailPrefix);
        $this->UpdatePool('isenabled', (int)($_isEnabled));
        $this->ProcessUpdatePool();

        SWIFT_NotificationCriteria::DeleteOnNotificationRule(array($this->GetNotificationRuleID()));

        foreach ($_criteriaContainer as $_criteria) {
            if (!isset($_criteria[0], $_criteria[1], $_criteria[2], $_criteria[3])) {
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            }

            if ($_criteria[1] == SWIFT_Rules::OP_CHANGED || $_criteria[1] == SWIFT_Rules::OP_NOTCHANGED) {
                $_criteria[2] = 0;
            }

            SWIFT_NotificationCriteria::Create($this->GetNotificationRuleID(), $_criteria[0], $_criteria[1], $_criteria[2], $_criteria[3]);
        }

        SWIFT_NotificationAction::DeleteOnNotificationRule(array($this->GetNotificationRuleID()));

        foreach ($_actionsContainer as $_action) {
            if (!isset($_action[0], $_action[1])) {
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            }

            SWIFT_NotificationAction::Create($this->GetNotificationRuleID(), $_action[0], $_action[1]);
        }

        self::RebuildCache();

        return true;
    }

    /**
     * Delete the Notification Rule record
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

        self::DeleteList(array($this->GetNotificationRuleID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of Notification Rules
     *
     * @author Varun Shoor
     * @param array $_notificationRuleIDList
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_notificationRuleIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_notificationRuleIDList)) {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "notificationrules WHERE notificationruleid IN (" . BuildIN($_notificationRuleIDList) . ")");

        // Delete the linked criteria
        SWIFT_NotificationCriteria::DeleteOnNotificationRule($_notificationRuleIDList);

        // Delete the linked actions
        SWIFT_NotificationAction::DeleteOnNotificationRule($_notificationRuleIDList);

        // Delete the linked pool records
        SWIFT_NotificationPool::DeleteOnNotificationRule($_notificationRuleIDList);

        self::RebuildCache();

        return true;
    }

    /**
     * Retrieve the Rule Type Label
     *
     * @author Varun Shoor
     * @param mixed $_ruleType The Rule Type
     * @return string The Rule Type Label
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveTypeLabel($_ruleType)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!self::IsValidRuleType($_ruleType)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        switch ($_ruleType) {
            case self::TYPE_CHAT:
                return $_SWIFT->Language->Get('nrule_chat');

                break;

            case self::TYPE_MESSAGE:
                return $_SWIFT->Language->Get('nrule_message');

                break;

            case self::TYPE_SURVEY:
                return $_SWIFT->Language->Get('nrule_survey');

                break;

            case self::TYPE_TICKET:
                return $_SWIFT->Language->Get('nrule_ticket');

                break;

            case self::TYPE_USER:
                return $_SWIFT->Language->Get('nrule_user');

                break;

            default:
                break;
        }

        throw new SWIFT_Exception(SWIFT_INVALIDDATA);
    }

    /**
     * Retrieve the Rule Type List
     *
     * @author Varun Shoor
     * @return array The Rule Type List
     */
    public static function RetrieveRuleTypeList()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_ruleTypeList = array();
        if (SWIFT_App::IsInstalled(APP_TICKETS)) {
            $_ruleTypeList[] = self::TYPE_TICKET;
        }

        if (SWIFT_App::IsInstalled(APP_LIVECHAT)) {
//            $_ruleTypeList[] = self::TYPE_CHAT;
//            $_ruleTypeList[] = self::TYPE_MESSAGE;
//            $_ruleTypeList[] = self::TYPE_SURVEY;
        }

        $_ruleTypeList[] = self::TYPE_USER;

        return $_ruleTypeList;
    }

    /**
     * Get the Criteria Pointer
     *
     * @author Varun Shoor
     * @param mixed $_ruleType The Rule
     * @return array The Criteria Pointer
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function GetCriteriaPointer($_ruleType)
    {
        switch ($_ruleType) {
            case self::TYPE_TICKET:
                return self::GetCriteriaPointer_Tickets();
                break;

            case self::TYPE_USER:
                return self::GetCriteriaPointer_User();
                break;

            default:
                break;
        }

        throw new SWIFT_Exception(SWIFT_INVALIDDATA);
    }

    /**
     * Return the Criteria for this Rule
     *
     * @author Varun Shoor
     * @return mixed "_criteriaPointer" (ARRAY) on Success, "false" otherwise
     */
    protected static function GetCriteriaPointer_User()
    {
        $_SWIFT = SWIFT::GetInstance();
        $_criteriaPointer = array();

        $_criteriaPointer[self::CRITERIA_USEREVENT]['title'] = $_SWIFT->Language->Get('n' . self::CRITERIA_USEREVENT);
        $_criteriaPointer[self::CRITERIA_USEREVENT]['desc'] = $_SWIFT->Language->Get('desc_n' . self::CRITERIA_USEREVENT);
        $_criteriaPointer[self::CRITERIA_USEREVENT]['op'] = 'bool';
        $_criteriaPointer[self::CRITERIA_USEREVENT]['field'] = 'custom';

        $_criteriaPointer[self::CRITERIA_USERGROUP]['title'] = $_SWIFT->Language->Get('n' . self::CRITERIA_USERGROUP);
        $_criteriaPointer[self::CRITERIA_USERGROUP]['desc'] = $_SWIFT->Language->Get('desc_n' . self::CRITERIA_USERGROUP);
        $_criteriaPointer[self::CRITERIA_USERGROUP]['op'] = 'bool';
        $_criteriaPointer[self::CRITERIA_USERGROUP]['field'] = 'custom';

        return $_criteriaPointer;
    }

    /**
     * Return the Criteria for this Rule
     *
     * @author Varun Shoor
     * @return mixed "_criteriaPointer" (ARRAY) on Success, "false" otherwise
     */
    protected static function GetCriteriaPointer_Tickets()
    {
        $_SWIFT = SWIFT::GetInstance();
        $_criteriaPointer = array();

        $_criteriaPointer[self::CRITERIA_TICKETEVENT]['title'] = $_SWIFT->Language->Get('n' . self::CRITERIA_TICKETEVENT);
        $_criteriaPointer[self::CRITERIA_TICKETEVENT]['desc'] = $_SWIFT->Language->Get('desc_n' . self::CRITERIA_TICKETEVENT);
        $_criteriaPointer[self::CRITERIA_TICKETEVENT]['op'] = 'bool';
        $_criteriaPointer[self::CRITERIA_TICKETEVENT]['field'] = 'custom';

        $_criteriaPointer[self::CRITERIA_TICKETTYPE]['title'] = $_SWIFT->Language->Get('n' . self::CRITERIA_TICKETTYPE);
        $_criteriaPointer[self::CRITERIA_TICKETTYPE]['desc'] = $_SWIFT->Language->Get('desc_n' . self::CRITERIA_TICKETTYPE);
        $_criteriaPointer[self::CRITERIA_TICKETTYPE]['op'] = 'extendedcustom';
        $_criteriaPointer[self::CRITERIA_TICKETTYPE]['field'] = 'custom';

        $_criteriaPointer[self::CRITERIA_TICKETSTATUS]['title'] = $_SWIFT->Language->Get('n' . self::CRITERIA_TICKETSTATUS);
        $_criteriaPointer[self::CRITERIA_TICKETSTATUS]['desc'] = $_SWIFT->Language->Get('desc_n' . self::CRITERIA_TICKETSTATUS);
        $_criteriaPointer[self::CRITERIA_TICKETSTATUS]['op'] = 'extendedcustom';
        $_criteriaPointer[self::CRITERIA_TICKETSTATUS]['field'] = 'custom';

        $_criteriaPointer[self::CRITERIA_TICKETPRIORITY]['title'] = $_SWIFT->Language->Get('n' . self::CRITERIA_TICKETPRIORITY);
        $_criteriaPointer[self::CRITERIA_TICKETPRIORITY]['desc'] = $_SWIFT->Language->Get('desc_n' . self::CRITERIA_TICKETPRIORITY);
        $_criteriaPointer[self::CRITERIA_TICKETPRIORITY]['op'] = 'extendedcustom';
        $_criteriaPointer[self::CRITERIA_TICKETPRIORITY]['field'] = 'custom';

        $_criteriaPointer[self::CRITERIA_TICKETUSERGROUP]['title'] = $_SWIFT->Language->Get('n' . self::CRITERIA_TICKETUSERGROUP);
        $_criteriaPointer[self::CRITERIA_TICKETUSERGROUP]['desc'] = $_SWIFT->Language->Get('desc_n' . self::CRITERIA_TICKETUSERGROUP);
        $_criteriaPointer[self::CRITERIA_TICKETUSERGROUP]['op'] = 'bool';
        $_criteriaPointer[self::CRITERIA_TICKETUSERGROUP]['field'] = 'custom';

        $_criteriaPointer[self::CRITERIA_DEPARTMENT]['title'] = $_SWIFT->Language->Get('n' . self::CRITERIA_DEPARTMENT);
        $_criteriaPointer[self::CRITERIA_DEPARTMENT]['desc'] = $_SWIFT->Language->Get('desc_n' . self::CRITERIA_DEPARTMENT);
        $_criteriaPointer[self::CRITERIA_DEPARTMENT]['op'] = 'extendedcustom';
        $_criteriaPointer[self::CRITERIA_DEPARTMENT]['field'] = 'custom';

        $_criteriaPointer[self::CRITERIA_OWNER]['title'] = $_SWIFT->Language->Get('n' . self::CRITERIA_OWNER);
        $_criteriaPointer[self::CRITERIA_OWNER]['desc'] = $_SWIFT->Language->Get('desc_n' . self::CRITERIA_OWNER);
        $_criteriaPointer[self::CRITERIA_OWNER]['op'] = 'extendedcustom';
        $_criteriaPointer[self::CRITERIA_OWNER]['field'] = 'custom';

        $_criteriaPointer[self::CRITERIA_EMAILQUEUE]['title'] = $_SWIFT->Language->Get('n' . self::CRITERIA_EMAILQUEUE);
        $_criteriaPointer[self::CRITERIA_EMAILQUEUE]['desc'] = $_SWIFT->Language->Get('desc_n' . self::CRITERIA_EMAILQUEUE);
        $_criteriaPointer[self::CRITERIA_EMAILQUEUE]['op'] = 'bool';
        $_criteriaPointer[self::CRITERIA_EMAILQUEUE]['field'] = 'custom';

        $_criteriaPointer[self::CRITERIA_FLAGTYPE]['title'] = $_SWIFT->Language->Get('n' . self::CRITERIA_FLAGTYPE);
        $_criteriaPointer[self::CRITERIA_FLAGTYPE]['desc'] = $_SWIFT->Language->Get('desc_n' . self::CRITERIA_FLAGTYPE);
        $_criteriaPointer[self::CRITERIA_FLAGTYPE]['op'] = 'extendedcustom';
        $_criteriaPointer[self::CRITERIA_FLAGTYPE]['field'] = 'custom';

        $_criteriaPointer[self::CRITERIA_SLAPLAN]['title'] = $_SWIFT->Language->Get('n' . self::CRITERIA_SLAPLAN);
        $_criteriaPointer[self::CRITERIA_SLAPLAN]['desc'] = $_SWIFT->Language->Get('desc_n' . self::CRITERIA_SLAPLAN);
        $_criteriaPointer[self::CRITERIA_SLAPLAN]['op'] = 'extendedcustom';
        $_criteriaPointer[self::CRITERIA_SLAPLAN]['field'] = 'custom';

        $_criteriaPointer[self::CRITERIA_TEMPLATEGROUP]['title'] = $_SWIFT->Language->Get('n' . self::CRITERIA_TEMPLATEGROUP);
        $_criteriaPointer[self::CRITERIA_TEMPLATEGROUP]['desc'] = $_SWIFT->Language->Get('desc_n' . self::CRITERIA_TEMPLATEGROUP);
        $_criteriaPointer[self::CRITERIA_TEMPLATEGROUP]['op'] = 'bool';
        $_criteriaPointer[self::CRITERIA_TEMPLATEGROUP]['field'] = 'custom';

        $_criteriaPointer[self::CRITERIA_BAYESIAN]['title'] = $_SWIFT->Language->Get('n' . self::CRITERIA_BAYESIAN);
        $_criteriaPointer[self::CRITERIA_BAYESIAN]['desc'] = $_SWIFT->Language->Get('desc_n' . self::CRITERIA_BAYESIAN);
        $_criteriaPointer[self::CRITERIA_BAYESIAN]['op'] = 'bool';
        $_criteriaPointer[self::CRITERIA_BAYESIAN]['field'] = 'custom';

        $_criteriaPointer[self::CRITERIA_CREATOR]['title'] = $_SWIFT->Language->Get('n' . self::CRITERIA_CREATOR);
        $_criteriaPointer[self::CRITERIA_CREATOR]['desc'] = $_SWIFT->Language->Get('desc_n' . self::CRITERIA_CREATOR);
        $_criteriaPointer[self::CRITERIA_CREATOR]['op'] = 'bool';
        $_criteriaPointer[self::CRITERIA_CREATOR]['field'] = 'custom';

        $_criteriaPointer[self::CRITERIA_DATERANGE]['title'] = $_SWIFT->Language->Get('n' . self::CRITERIA_DATERANGE);
        $_criteriaPointer[self::CRITERIA_DATERANGE]['desc'] = $_SWIFT->Language->Get('desc_n' . self::CRITERIA_DATERANGE);
        $_criteriaPointer[self::CRITERIA_DATERANGE]['op'] = 'bool';
        $_criteriaPointer[self::CRITERIA_DATERANGE]['field'] = 'daterange';

        $_criteriaPointer[self::CRITERIA_LASTACTIVITYRANGE]['title'] = $_SWIFT->Language->Get('n' . self::CRITERIA_LASTACTIVITYRANGE);
        $_criteriaPointer[self::CRITERIA_LASTACTIVITYRANGE]['desc'] = $_SWIFT->Language->Get('desc_n' . self::CRITERIA_LASTACTIVITYRANGE);
        $_criteriaPointer[self::CRITERIA_LASTACTIVITYRANGE]['op'] = 'bool';
        $_criteriaPointer[self::CRITERIA_LASTACTIVITYRANGE]['field'] = 'daterange';

        $_criteriaPointer[self::CRITERIA_LASTSTAFFREPLYRANGE]['title'] = $_SWIFT->Language->Get('n' . self::CRITERIA_LASTSTAFFREPLYRANGE);
        $_criteriaPointer[self::CRITERIA_LASTSTAFFREPLYRANGE]['desc'] = $_SWIFT->Language->Get('desc_n' . self::CRITERIA_LASTSTAFFREPLYRANGE);
        $_criteriaPointer[self::CRITERIA_LASTSTAFFREPLYRANGE]['op'] = 'bool';
        $_criteriaPointer[self::CRITERIA_LASTSTAFFREPLYRANGE]['field'] = 'daterange';

        $_criteriaPointer[self::CRITERIA_LASTUSERREPLYRANGE]['title'] = $_SWIFT->Language->Get('n' . self::CRITERIA_LASTUSERREPLYRANGE);
        $_criteriaPointer[self::CRITERIA_LASTUSERREPLYRANGE]['desc'] = $_SWIFT->Language->Get('desc_n' . self::CRITERIA_LASTUSERREPLYRANGE);
        $_criteriaPointer[self::CRITERIA_LASTUSERREPLYRANGE]['op'] = 'bool';
        $_criteriaPointer[self::CRITERIA_LASTUSERREPLYRANGE]['field'] = 'daterange';

        $_criteriaPointer[self::CRITERIA_DUERANGE]['title'] = $_SWIFT->Language->Get('n' . self::CRITERIA_DUERANGE);
        $_criteriaPointer[self::CRITERIA_DUERANGE]['desc'] = $_SWIFT->Language->Get('desc_n' . self::CRITERIA_DUERANGE);
        $_criteriaPointer[self::CRITERIA_DUERANGE]['op'] = 'bool';
        $_criteriaPointer[self::CRITERIA_DUERANGE]['field'] = 'daterangeforward';

        $_criteriaPointer[self::CRITERIA_ISOVERDUE]['title'] = $_SWIFT->Language->Get('n' . self::CRITERIA_ISOVERDUE);
        $_criteriaPointer[self::CRITERIA_ISOVERDUE]['desc'] = $_SWIFT->Language->Get('desc_n' . self::CRITERIA_ISOVERDUE);
        $_criteriaPointer[self::CRITERIA_ISOVERDUE]['op'] = 'bool';
        $_criteriaPointer[self::CRITERIA_ISOVERDUE]['field'] = 'bool';

        $_criteriaPointer[self::CRITERIA_RESOLUTIONDUERANGE]['title'] = $_SWIFT->Language->Get('n' . self::CRITERIA_RESOLUTIONDUERANGE);
        $_criteriaPointer[self::CRITERIA_RESOLUTIONDUERANGE]['desc'] = $_SWIFT->Language->Get('desc_n' . self::CRITERIA_RESOLUTIONDUERANGE);
        $_criteriaPointer[self::CRITERIA_RESOLUTIONDUERANGE]['op'] = 'bool';
        $_criteriaPointer[self::CRITERIA_RESOLUTIONDUERANGE]['field'] = 'daterangeforward';

        $_criteriaPointer[self::CRITERIA_ISRESOLUTIONOVERDUE]['title'] = $_SWIFT->Language->Get('n' . self::CRITERIA_ISRESOLUTIONOVERDUE);
        $_criteriaPointer[self::CRITERIA_ISRESOLUTIONOVERDUE]['desc'] = $_SWIFT->Language->Get('desc_n' . self::CRITERIA_ISRESOLUTIONOVERDUE);
        $_criteriaPointer[self::CRITERIA_ISRESOLUTIONOVERDUE]['op'] = 'bool';
        $_criteriaPointer[self::CRITERIA_ISRESOLUTIONOVERDUE]['field'] = 'bool';

        $_criteriaPointer[self::CRITERIA_TIMEWORKED]['title'] = $_SWIFT->Language->Get('n' . self::CRITERIA_TIMEWORKED);
        $_criteriaPointer[self::CRITERIA_TIMEWORKED]['desc'] = $_SWIFT->Language->Get('desc_n' . self::CRITERIA_TIMEWORKED);
        $_criteriaPointer[self::CRITERIA_TIMEWORKED]['op'] = 'int';
        $_criteriaPointer[self::CRITERIA_TIMEWORKED]['field'] = 'int';

        $_criteriaPointer[self::CRITERIA_TOTALREPLIES]['title'] = $_SWIFT->Language->Get('n' . self::CRITERIA_TOTALREPLIES);
        $_criteriaPointer[self::CRITERIA_TOTALREPLIES]['desc'] = $_SWIFT->Language->Get('desc_n' . self::CRITERIA_TOTALREPLIES);
        $_criteriaPointer[self::CRITERIA_TOTALREPLIES]['op'] = 'int';
        $_criteriaPointer[self::CRITERIA_TOTALREPLIES]['field'] = 'int';

        $_criteriaPointer[self::CRITERIA_PENDINGFOLLOWUPS]['title'] = $_SWIFT->Language->Get('n' . self::CRITERIA_PENDINGFOLLOWUPS);
        $_criteriaPointer[self::CRITERIA_PENDINGFOLLOWUPS]['desc'] = $_SWIFT->Language->Get('desc_n' . self::CRITERIA_PENDINGFOLLOWUPS);
        $_criteriaPointer[self::CRITERIA_PENDINGFOLLOWUPS]['op'] = 'int';
        $_criteriaPointer[self::CRITERIA_PENDINGFOLLOWUPS]['field'] = 'int';

        $_criteriaPointer[self::CRITERIA_ISEMAILED]['title'] = $_SWIFT->Language->Get('n' . self::CRITERIA_ISEMAILED);
        $_criteriaPointer[self::CRITERIA_ISEMAILED]['desc'] = $_SWIFT->Language->Get('desc_n' . self::CRITERIA_ISEMAILED);
        $_criteriaPointer[self::CRITERIA_ISEMAILED]['op'] = 'bool';
        $_criteriaPointer[self::CRITERIA_ISEMAILED]['field'] = 'bool';

        $_criteriaPointer[self::CRITERIA_ISEDITED]['title'] = $_SWIFT->Language->Get('n' . self::CRITERIA_ISEDITED);
        $_criteriaPointer[self::CRITERIA_ISEDITED]['desc'] = $_SWIFT->Language->Get('desc_n' . self::CRITERIA_ISEDITED);
        $_criteriaPointer[self::CRITERIA_ISEDITED]['op'] = 'bool';
        $_criteriaPointer[self::CRITERIA_ISEDITED]['field'] = 'bool';

        $_criteriaPointer[self::CRITERIA_HASNOTES]['title'] = $_SWIFT->Language->Get('n' . self::CRITERIA_HASNOTES);
        $_criteriaPointer[self::CRITERIA_HASNOTES]['desc'] = $_SWIFT->Language->Get('desc_n' . self::CRITERIA_HASNOTES);
        $_criteriaPointer[self::CRITERIA_HASNOTES]['op'] = 'bool';
        $_criteriaPointer[self::CRITERIA_HASNOTES]['field'] = 'bool';

        $_criteriaPointer[self::CRITERIA_HASATTACHMENTS]['title'] = $_SWIFT->Language->Get('n' . self::CRITERIA_HASATTACHMENTS);
        $_criteriaPointer[self::CRITERIA_HASATTACHMENTS]['desc'] = $_SWIFT->Language->Get('desc_n' . self::CRITERIA_HASATTACHMENTS);
        $_criteriaPointer[self::CRITERIA_HASATTACHMENTS]['op'] = 'bool';
        $_criteriaPointer[self::CRITERIA_HASATTACHMENTS]['field'] = 'bool';

        $_criteriaPointer[self::CRITERIA_ISESCALATED]['title'] = $_SWIFT->Language->Get('n' . self::CRITERIA_ISESCALATED);
        $_criteriaPointer[self::CRITERIA_ISESCALATED]['desc'] = $_SWIFT->Language->Get('desc_n' . self::CRITERIA_ISESCALATED);
        $_criteriaPointer[self::CRITERIA_ISESCALATED]['op'] = 'bool';
        $_criteriaPointer[self::CRITERIA_ISESCALATED]['field'] = 'bool';

        $_criteriaPointer[self::CRITERIA_HASDRAFT]['title'] = $_SWIFT->Language->Get('n' . self::CRITERIA_HASDRAFT);
        $_criteriaPointer[self::CRITERIA_HASDRAFT]['desc'] = $_SWIFT->Language->Get('desc_n' . self::CRITERIA_HASDRAFT);
        $_criteriaPointer[self::CRITERIA_HASDRAFT]['op'] = 'bool';
        $_criteriaPointer[self::CRITERIA_HASDRAFT]['field'] = 'bool';

        $_criteriaPointer[self::CRITERIA_HASBILLING]['title'] = $_SWIFT->Language->Get('n' . self::CRITERIA_HASBILLING);
        $_criteriaPointer[self::CRITERIA_HASBILLING]['desc'] = $_SWIFT->Language->Get('desc_n' . self::CRITERIA_HASBILLING);
        $_criteriaPointer[self::CRITERIA_HASBILLING]['op'] = 'bool';
        $_criteriaPointer[self::CRITERIA_HASBILLING]['field'] = 'bool';

        $_criteriaPointer[self::CRITERIA_ISPHONECALL]['title'] = $_SWIFT->Language->Get('n' . self::CRITERIA_ISPHONECALL);
        $_criteriaPointer[self::CRITERIA_ISPHONECALL]['desc'] = $_SWIFT->Language->Get('desc_n' . self::CRITERIA_ISPHONECALL);
        $_criteriaPointer[self::CRITERIA_ISPHONECALL]['op'] = 'bool';
        $_criteriaPointer[self::CRITERIA_ISPHONECALL]['field'] = 'bool';

        return $_criteriaPointer;
    }

    /**
     * Parse the Criteria and Extend It
     *
     * @author Varun Shoor
     * @param mixed $_ruleType The Rule Type
     * @param array $_criteriaPointer
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function ExtendCustomCriteria($_ruleType, &$_criteriaPointer)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!self::IsValidRuleType($_ruleType)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        switch ($_ruleType) {
            case self::TYPE_TICKET:
                return self::ExtendCustomCriteria_Tickets($_criteriaPointer);
                break;

            case self::TYPE_USER:
                return self::ExtendCustomCriteria_User($_criteriaPointer);
                break;

            default:
                break;
        }

        throw new SWIFT_Exception(SWIFT_INVALIDDATA);
    }

    /**
     * Parse the Criteria Pointer and extend it
     *
     * @author Varun Shoor
     * @param array $_criteriaPointer The Criteria Pointer Container
     * @return bool "true" on Success, "false" otherwise
     */
    protected static function ExtendCustomCriteria_Tickets(&$_criteriaPointer)
    {
        $_SWIFT = SWIFT::GetInstance();

        SWIFT_Loader::LoadModel('Ticket:Ticket', APP_TICKETS);
        SWIFT_Loader::LoadLibrary('Flag:TicketFlag', APP_TICKETS);

        // ======= TICKET EVENT =======
        $_fieldContainer = array();
        $_fieldContainer[] = array('title' => $_SWIFT->Language->Get('tevent_newticket'), 'contents' => 'newticket');
        $_fieldContainer[] = array('title' => $_SWIFT->Language->Get('tevent_newclientreply'), 'contents' => 'newclientreply');
        $_fieldContainer[] = array('title' => $_SWIFT->Language->Get('tevent_newclientsurvey'), 'contents' => 'newclientsurvey');
        $_fieldContainer[] = array('title' => $_SWIFT->Language->Get('tevent_newstaffreply'), 'contents' => 'newstaffreply');
        $_fieldContainer[] = array('title' => $_SWIFT->Language->Get('tevent_ticketassigned'), 'contents' => 'ticketassigned');
        $_fieldContainer[] = array('title' => $_SWIFT->Language->Get('tevent_newticketnotes'), 'contents' => 'newticketnotes');

        $_criteriaPointer[self::CRITERIA_TICKETEVENT]['fieldcontents'] = $_fieldContainer;

        // ======= TICKET TYPE =======
        $_fieldContainer = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "tickettypes ORDER BY displayorder ASC");
        while ($_SWIFT->Database->nextRecord()) {
            $_fieldContainer[] = array('title' => $_SWIFT->Database->Record['title'], 'contents' => $_SWIFT->Database->Record['tickettypeid']);
        }

        if (!count($_fieldContainer)) {
            $_fieldContainer[] = array('title' => $_SWIFT->Language->Get('notapplicable'), 'contents' => '0');
        }

        $_criteriaPointer[self::CRITERIA_TICKETTYPE]['fieldcontents'] = $_fieldContainer;

        // ======= BAYESIAN CATEGORIES =======
        $_fieldContainer = array();
        $_fieldContainer[] = array('title' => $_SWIFT->Language->Get('notset'), 'contents' => '0');
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "bayescategories ORDER BY category ASC");
        while ($_SWIFT->Database->nextRecord()) {
            $_fieldContainer[] = array('title' => $_SWIFT->Database->Record['category'], 'contents' => $_SWIFT->Database->Record['bayescategoryid']);
        }

        $_criteriaPointer[self::CRITERIA_BAYESIAN]['fieldcontents'] = $_fieldContainer;

        // ======= TICKET STATUS =======
        $_fieldContainer = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketstatus ORDER BY displayorder ASC");
        while ($_SWIFT->Database->nextRecord()) {
            $_fieldContainer[] = array('title' => $_SWIFT->Database->Record['title'], 'contents' => $_SWIFT->Database->Record['ticketstatusid']);
        }

        if (!count($_fieldContainer)) {
            $_fieldContainer[] = array('title' => $_SWIFT->Language->Get('notapplicable'), 'contents' => '0');
        }

        $_criteriaPointer[self::CRITERIA_TICKETSTATUS]['fieldcontents'] = $_fieldContainer;

        // ======= TICKET PRIORITY =======
        $_fieldContainer = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketpriorities ORDER BY displayorder ASC");
        while ($_SWIFT->Database->NextRecord()) {
            $_fieldContainer[] = array('title' => $_SWIFT->Database->Record['title'], 'contents' => $_SWIFT->Database->Record['priorityid']);
        }

        if (!count($_fieldContainer)) {
            $_fieldContainer[] = array('title' => $_SWIFT->Language->Get('notapplicable'), 'contents' => '0');
        }

        $_criteriaPointer[self::CRITERIA_TICKETPRIORITY]['fieldcontents'] = $_fieldContainer;

        // ======= USER GROUPS =======
        $_fieldContainer = array();
        $_fieldContainer[] = array('title' => $_SWIFT->Language->Get('notset'), 'contents' => '0');
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "usergroups ORDER BY title ASC");
        while ($_SWIFT->Database->NextRecord()) {
            $_fieldContainer[] = array('title' => $_SWIFT->Database->Record['title'], 'contents' => $_SWIFT->Database->Record['usergroupid']);
        }

        $_criteriaPointer[self::CRITERIA_TICKETUSERGROUP]['fieldcontents'] = $_fieldContainer;

        // ======= DEPARTMENT =======
        $_departmentMapContainer = SWIFT_Department::GetDepartmentMap(APP_TICKETS);
        $_fieldContainer = array();

        foreach ($_departmentMapContainer as $_key => $_val) {
            $_fieldContainer[] = array('title' => $_val['title'], 'contents' => $_val['departmentid']);

            if (_is_array($_val['subdepartments'])) {
                foreach ($_val['subdepartments'] as $_subKey => $_subVal) {
                    $_fieldContainer[] = array('title' => ' |- ' . $_subVal['title'], 'contents' => $_subVal['departmentid']);
                }
            }
        }

        if (!count($_fieldContainer)) {
            $_fieldContainer[] = array('title' => $_SWIFT->Language->Get('notapplicable'), 'contents' => '0');
        }

        $_criteriaPointer[self::CRITERIA_DEPARTMENT]['fieldcontents'] = $_fieldContainer;

        // ======= TICKET OWNER =======
        $_fieldContainer = array();
        $_fieldContainer[] = array('title' => $_SWIFT->Language->Get('unassigned'), 'contents' => '0');
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "staff ORDER BY fullname ASC");
        while ($_SWIFT->Database->NextRecord()) {
            $_fieldContainer[] = array('title' => $_SWIFT->Database->Record['fullname'], 'contents' => $_SWIFT->Database->Record['staffid']);
        }

        if (!count($_fieldContainer)) {
            $_fieldContainer[] = array('title' => $_SWIFT->Language->Get('notapplicable'), 'contents' => '0');
        }

        $_criteriaPointer[self::CRITERIA_OWNER]['fieldcontents'] = $_fieldContainer;

        // ======= EMAIL QUEUE =======
        $_fieldContainer = array();
        $_fieldContainer[] = array('title' => $_SWIFT->Language->Get('notset'), 'contents' => '0');
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "emailqueues ORDER BY email ASC");
        while ($_SWIFT->Database->NextRecord()) {
            $_fieldContainer[] = array('title' => $_SWIFT->Database->Record['email'], 'contents' => $_SWIFT->Database->Record['emailqueueid']);
        }

        $_criteriaPointer[self::CRITERIA_EMAILQUEUE]['fieldcontents'] = $_fieldContainer;

        // ======= SLA PLAN =======
        $_fieldContainer = array();
        $_fieldContainer[] = array('title' => $_SWIFT->Language->Get('notset'), 'contents' => '0');
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "slaplans ORDER BY title ASC");
        while ($_SWIFT->Database->NextRecord()) {
            $_fieldContainer[] = array('title' => $_SWIFT->Database->Record['title'], 'contents' => $_SWIFT->Database->Record['slaplanid']);
        }

        $_criteriaPointer[self::CRITERIA_SLAPLAN]['fieldcontents'] = $_fieldContainer;

        // ======= TEMPLATE GROUPS =======
        $_fieldContainer = array();
        $_fieldContainer[] = array('title' => $_SWIFT->Language->Get('notset'), 'contents' => '0');
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "templategroups ORDER BY title ASC");
        while ($_SWIFT->Database->NextRecord()) {
            $_fieldContainer[] = array('title' => $_SWIFT->Database->Record['title'], 'contents' => $_SWIFT->Database->Record['tgroupid']);
        }


        $_criteriaPointer[self::CRITERIA_TEMPLATEGROUP]['fieldcontents'] = $_fieldContainer;

        // ======= FLAG TYPE =======
        $_SWIFT_TicketFlagObject = new SWIFT_TicketFlag();

        $_fieldContainer = array();
        $_fieldContainer[] = array('title' => $_SWIFT->Language->Get('notset'), 'contents' => '0');
        foreach ($_SWIFT_TicketFlagObject->GetFlagList() as $_key => $_val) {
            $_fieldContainer[] = array('title' => $_val, 'contents' => $_key);
        }

        $_criteriaPointer[self::CRITERIA_FLAGTYPE]['fieldcontents'] = $_fieldContainer;

        // ======= CREATOR =======
        $_fieldContainer = array();
        $_fieldContainer[] = array('title' => $_SWIFT->Language->Get('creatorstaff'), 'contents' => SWIFT_Ticket::CREATOR_STAFF);
        $_fieldContainer[] = array('title' => $_SWIFT->Language->Get('creatorclient'), 'contents' => SWIFT_Ticket::CREATOR_CLIENT);

        $_criteriaPointer[self::CRITERIA_CREATOR]['fieldcontents'] = $_fieldContainer;

        return true;
    }

    /**
     * Parse the Criteria Pointer and extend it
     *
     * @author Varun Shoor
     * @param array $_criteriaPointer The Criteria Pointer Container
     * @return bool "true" on Success, "false" otherwise
     */
    protected static function ExtendCustomCriteria_User(&$_criteriaPointer)
    {
        $_SWIFT = SWIFT::GetInstance();

        // ======= TICKET EVENT =======
        $_fieldContainer = array();
        $_fieldContainer[] = array('title' => $_SWIFT->Language->Get('tevent_newuser'), 'contents' => 'newuser');

        $_criteriaPointer[self::CRITERIA_USEREVENT]['fieldcontents'] = $_fieldContainer;

        // ======= USER GROUPS =======
        $_fieldContainer = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "usergroups ORDER BY title ASC");
        while ($_SWIFT->Database->NextRecord()) {
            $_fieldContainer[] = array('title' => $_SWIFT->Database->Record['title'], 'contents' => $_SWIFT->Database->Record['usergroupid']);
        }

        $_criteriaPointer[self::CRITERIA_USERGROUP]['fieldcontents'] = $_fieldContainer;

        return true;
    }

    /**
     * Enable a List of Notification Rules
     *
     * @author Varun Shoor
     * @param array $_notificationRuleIDList The Notification Rule ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function EnableList($_notificationRuleIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_notificationRuleIDList)) {
            return false;
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'notificationrules', array('isenabled' => '1'), 'UPDATE', "notificationruleid IN (" . BuildIN($_notificationRuleIDList) . ")");

        self::RebuildCache();

        return true;
    }

    /**
     * Disable a List of Notification Rules
     *
     * @author Varun Shoor
     * @param array $_notificationRuleIDList The Notification Rule ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DisableList($_notificationRuleIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_notificationRuleIDList)) {
            return false;
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'notificationrules', array('isenabled' => '0'), 'UPDATE', "notificationruleid IN (" . BuildIN($_notificationRuleIDList) . ")");

        self::RebuildCache();

        return true;
    }

    /**
     * Rebuild the Notification Rule Cache
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public static function RebuildCache()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_cacheContainer = array();

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "notificationrules ORDER BY notificationruleid");
        while ($_SWIFT->Database->NextRecord()) {
            $_cacheContainer[$_SWIFT->Database->Record['notificationruleid']] = $_SWIFT->Database->Record;
            $_cacheContainer[$_SWIFT->Database->Record['notificationruleid']]['_criteria'] = array();
            $_cacheContainer[$_SWIFT->Database->Record['notificationruleid']]['_actions'] = array();
        }

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "notificationcriteria ORDER BY notificationcriteriaid");
        while ($_SWIFT->Database->NextRecord()) {
            if (!isset($_cacheContainer[$_SWIFT->Database->Record['notificationruleid']])) {
                continue;
            }

            $_ruleType = $_cacheContainer[$_SWIFT->Database->Record['notificationruleid']]['ruletype'];

            $_criteriaContainer = array($_SWIFT->Database->Record['name'], $_SWIFT->Database->Record['ruleop'], $_SWIFT->Database->Record['rulematch'],
                $_SWIFT->Database->Record['rulematchtype']);

            $_cacheContainer[$_SWIFT->Database->Record['notificationruleid']]['_criteria'][$_SWIFT->Database->Record['notificationcriteriaid']] = $_criteriaContainer;
        }

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "notificationactions ORDER BY notificationactionid");
        while ($_SWIFT->Database->NextRecord()) {
            if (!isset($_cacheContainer[$_SWIFT->Database->Record['notificationruleid']])) {
                continue;
            }

            $_cacheContainer[$_SWIFT->Database->Record['notificationruleid']]['_actions'][$_SWIFT->Database->Record['notificationactionid']] = $_SWIFT->Database->Record;
        }

        $_SWIFT->Cache->Update('notificationrulescache', $_cacheContainer);

        return true;
    }

    /**
     * Process the Notification Rule Properties
     *
     * @author Varun Shoor
     * @param SWIFT_Base $_SWIFT_BaseObject The Base Object
     * @param string $_event The Execution Event
     * @param array $_changeContainer The Change Container
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ProcessProperties($_SWIFT_BaseObject, $_event, $_changeContainer)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_propertiesContainer = array();
        if ($_SWIFT_BaseObject instanceof SWIFT_Ticket) {
            $_propertiesContainer = $this->ProcessTicketProperties($_SWIFT_BaseObject, $_event, $_changeContainer);
        } else if ($_SWIFT_BaseObject instanceof SWIFT_User) {
            $_propertiesContainer = $this->ProcessUserProperties($_SWIFT_BaseObject, $_event, $_changeContainer);
        } else {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->_propertiesContainer = $_propertiesContainer;
        $this->_changeContainer = $_changeContainer;

        return true;
    }

    /**
     * Process the Notification Rule Properties for TICKETS
     *
     * @author Varun Shoor
     * @param SWIFT_Ticket $_SWIFT_TicketObject The SWIFT_Ticket Object
     * @param string $_event The Execution Event
     * @param array $_changeContainer The Change Container
     * @return array The Processed Properties Container
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ProcessTicketProperties(SWIFT_Ticket $_SWIFT_TicketObject, $_event, $_changeContainer)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_propertiesContainer = array();

        // Custom Properties
        $_propertiesContainer[self::CRITERIA_TICKETEVENT] = $_event;
        $_propertiesContainer[self::CRITERIA_TICKETSTATUS] = $_SWIFT_TicketObject->GetProperty('ticketstatusid');
        $_propertiesContainer[self::CRITERIA_TICKETPRIORITY] = $_SWIFT_TicketObject->GetProperty('priorityid');

        $_SWIFT_UserObject = $_SWIFT_TicketObject->GetUserObject();
        if ($_SWIFT_UserObject instanceof SWIFT_User && $_SWIFT_UserObject->GetIsClassLoaded()) {
            $_propertiesContainer[self::CRITERIA_USERGROUP] = $_SWIFT_UserObject->GetProperty('usergroupid');
        } else {
            $_propertiesContainer[self::CRITERIA_USERGROUP] = 0;
        }

        $_propertiesContainer[self::CRITERIA_TICKETTYPE] = $_SWIFT_TicketObject->GetProperty('tickettypeid');
        $_propertiesContainer[self::CRITERIA_BAYESIAN] = $_SWIFT_TicketObject->GetProperty('bayescategoryid');
        $_propertiesContainer[self::CRITERIA_DEPARTMENT] = $_SWIFT_TicketObject->GetProperty('departmentid');
        $_propertiesContainer[self::CRITERIA_OWNER] = $_SWIFT_TicketObject->GetProperty('ownerstaffid');
        $_propertiesContainer[self::CRITERIA_EMAILQUEUE] = $_SWIFT_TicketObject->GetProperty('emailqueueid');
        $_propertiesContainer[self::CRITERIA_FLAGTYPE] = $_SWIFT_TicketObject->GetProperty('flagtype');
        $_propertiesContainer[self::CRITERIA_SLAPLAN] = $_SWIFT_TicketObject->GetProperty('slaplanid');
        $_propertiesContainer[self::CRITERIA_TEMPLATEGROUP] = $_SWIFT_TicketObject->GetProperty('tgroupid');
        $_propertiesContainer[self::CRITERIA_CREATOR] = $_SWIFT_TicketObject->GetProperty('creator');

        // Calendar
        $_propertiesContainer[self::CRITERIA_DATERANGE] = $_SWIFT_TicketObject->GetProperty('dateline');
        $_propertiesContainer[self::CRITERIA_LASTACTIVITYRANGE] = $_SWIFT_TicketObject->GetProperty('lastactivity');
        $_propertiesContainer[self::CRITERIA_LASTSTAFFREPLYRANGE] = $_SWIFT_TicketObject->GetProperty('laststaffreplytime');
        $_propertiesContainer[self::CRITERIA_LASTUSERREPLYRANGE] = $_SWIFT_TicketObject->GetProperty('lastuserreplytime');
        $_propertiesContainer[self::CRITERIA_DUERANGE] = $_SWIFT_TicketObject->GetProperty('duetime');
        $_propertiesContainer[self::CRITERIA_RESOLUTIONDUERANGE] = $_SWIFT_TicketObject->GetProperty('resolutionduedateline');

        // Integer
        $_propertiesContainer[self::CRITERIA_TIMEWORKED] = $_SWIFT_TicketObject->GetProperty('timeworked');
        $_propertiesContainer[self::CRITERIA_TOTALREPLIES] = $_SWIFT_TicketObject->GetProperty('totalreplies');
        $_propertiesContainer[self::CRITERIA_PENDINGFOLLOWUPS] = $_SWIFT_TicketObject->GetProperty('followupcount');

        // Boolean
        $_propertiesContainer[self::CRITERIA_ISEMAILED] = $_SWIFT_TicketObject->GetProperty('isemailed');
        $_propertiesContainer[self::CRITERIA_ISEDITED] = $_SWIFT_TicketObject->GetProperty('edited');
        $_propertiesContainer[self::CRITERIA_HASNOTES] = $_SWIFT_TicketObject->GetProperty('hasnotes');
        $_propertiesContainer[self::CRITERIA_HASATTACHMENTS] = $_SWIFT_TicketObject->GetProperty('hasattachments');
        $_propertiesContainer[self::CRITERIA_ISESCALATED] = $_SWIFT_TicketObject->GetProperty('isescalated');
        $_propertiesContainer[self::CRITERIA_HASDRAFT] = $_SWIFT_TicketObject->GetProperty('hasdraft');
        $_propertiesContainer[self::CRITERIA_HASBILLING] = $_SWIFT_TicketObject->GetProperty('hasbilling');

        if ($_SWIFT_TicketObject->GetProperty('tickettype') == SWIFT_Ticket::TYPE_PHONE) {
            $_propertiesContainer[self::CRITERIA_ISPHONECALL] = '1';
        } else {
            $_propertiesContainer[self::CRITERIA_ISPHONECALL] = '0';
        }

        if ($_SWIFT_TicketObject->GetProperty('duetime') < DATENOW && $_SWIFT_TicketObject->GetProperty('duetime') != '0') {
            $_propertiesContainer[self::CRITERIA_ISOVERDUE] = '1';
        } else {
            $_propertiesContainer[self::CRITERIA_ISOVERDUE] = '0';
        }

        if ($_SWIFT_TicketObject->GetProperty('resolutionduedateline') < DATENOW && $_SWIFT_TicketObject->GetProperty('resolutionduedateline') != '0') {
            $_propertiesContainer[self::CRITERIA_ISRESOLUTIONOVERDUE] = '1';
        } else {
            $_propertiesContainer[self::CRITERIA_ISRESOLUTIONOVERDUE] = '0';
        }

        return $_propertiesContainer;
    }

    /**
     * Process the Notification Rule Properties for USERS
     *
     * @author Varun Shoor
     * @param SWIFT_User $_SWIFT_UserObject The SWIFT_User Object
     * @param string $_event The Execution Event
     * @param array $_changeContainer The Change Container
     * @return array The Processed Properties Container
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ProcessUserProperties(SWIFT_User $_SWIFT_UserObject, $_event, $_changeContainer)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!$_SWIFT_UserObject instanceof SWIFT_User || !$_SWIFT_UserObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_propertiesContainer = array();
        $_propertiesContainer[self::CRITERIA_USEREVENT] = $_event;
        $_propertiesContainer[self::CRITERIA_USERGROUP] = $_SWIFT_UserObject->GetProperty('usergroupid');

        return $_propertiesContainer;
    }

    /**
     * Retrieves the Criteria Value
     *
     * @author Varun Shoor
     * @param string $_criteriaName The Criteria Name Pointer
     * @return bool "true" on Success, "false" otherwise
     */
    public function GetCriteriaValue($_criteriaName)
    {
        if (isset($this->_propertiesContainer[$_criteriaName])) {
            return $this->_propertiesContainer[$_criteriaName];
        }

        return false;
    }

    /**
     * Retrieve the Change Container
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetChangeContainer($_criteriaName)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (isset($this->_changeContainer[$_criteriaName])) {
            return $this->_changeContainer[$_criteriaName];
        }

        return false;
    }

    /**
     * Delete Notification Rules based on list of Rule Types
     *
     * @author Varun Shoor
     * @param array $_ruleTypeList The Rule Type List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function DeleteOnType($_ruleTypeList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_ruleTypeList)) {
            return false;
        }

        $_notificationRuleIDList = array();

        $_SWIFT->Database->Query("SELECT notificationruleid FROM " . TABLE_PREFIX . "notificationrules WHERE ruletype IN (" . BuildIN($_ruleTypeList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_notificationRuleIDList[] = $_SWIFT->Database->Record['notificationruleid'];
        }

        if (!count($_notificationRuleIDList)) {
            return false;
        }

        self::DeleteList($_notificationRuleIDList);

        return true;
    }
}

?>
