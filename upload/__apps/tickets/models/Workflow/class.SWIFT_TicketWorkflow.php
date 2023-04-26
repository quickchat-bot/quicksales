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

namespace Tickets\Models\Workflow;

use SWIFT;
use SWIFT_Data;
use SWIFT_DataID;
use SWIFT_DataStore;
use Base\Models\Department\SWIFT_Department;
use SWIFT_Exception;
use Base\Library\Rules\SWIFT_Rules;
use Base\Models\Staff\SWIFT_Staff;
use Base\Models\Staff\SWIFT_StaffGroupLink;
use Base\Models\Tag\SWIFT_Tag;
use Base\Models\Tag\SWIFT_TagLink;
use Base\Models\User\SWIFT_User;
use Tickets\Library\Bayesian\SWIFT_Bayesian;
use Tickets\Library\Flag\SWIFT_TicketFlag;
use Tickets\Library\Notification\SWIFT_TicketNotification;
use Tickets\Library\Ticket\SWIFT_TicketManager;
use Tickets\Models\AuditLog\SWIFT_TicketAuditLog;
use Tickets\Models\Note\SWIFT_TicketNote;
use Tickets\Models\SLA\SWIFT_SLA;
use Tickets\Models\Ticket\SWIFT_Ticket;
use Tickets\Models\Ticket\SWIFT_TicketLinkedTable;

/**
 * The Ticket Workflow Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_TicketWorkflow extends SWIFT_Rules
{
    const TABLE_NAME        =    'ticketworkflows';
    const PRIMARY_KEY        =    'ticketworkflowid';

    const TABLE_STRUCTURE    =    "ticketworkflowid I PRIMARY AUTO NOTNULL,
                                title C(255) DEFAULT '' NOTNULL,
                                isenabled I2 DEFAULT '0' NOTNULL,
                                sortorder I DEFAULT '0' NOTNULL,
                                ruletype I2 DEFAULT '0' NOTNULL,
                                staffvisibilitycustom I2 DEFAULT '0' NOTNULL,
                                uservisibility I2 DEFAULT '0' NOTNULL,
                                dateline I DEFAULT '0' NOTNULL";

    const INDEX_2            =    'title';


    protected $_dataStore = array();

    protected $_workflowProperties = array();
    static protected $_workflowPropertiesCache = array();

    // Core Constants
    const ACTION_DEPARTMENT = 'department';
    const ACTION_OWNER = 'owner';
    const ACTION_STATUS = 'status';
    const ACTION_PRIORITY = 'priority';
    const ACTION_ADDNOTE = 'addnote';
    const ACTION_FLAGTICKET = 'flagticket';
    const ACTION_SLAPLAN = 'slaplan';
    const ACTION_TICKETTYPE = 'tickettype';
    const ACTION_ADDTAGS = 'addtags';
    const ACTION_REMOVETAGS = 'removetags';
    const ACTION_BAYESIAN = 'bayesian';
    const ACTION_TRASH = 'trash';

    // ======= BEGIN CRITERIA TAGS =======
    // Custom
    const CRITERIA_TICKETSTATUS = 'ticketstatus';
    const CRITERIA_TICKETPRIORITY = 'ticketpriority';
    const CRITERIA_USERGROUP = 'usergroup';
    const CRITERIA_TICKETTYPE = 'tickettype';
    const CRITERIA_BAYESIAN = 'bayesian';
    const CRITERIA_DEPARTMENT = 'department';
    const CRITERIA_OWNER = 'owner';
    const CRITERIA_EMAILQUEUE = 'emailqueue';
    const CRITERIA_FLAGTYPE = 'flagtype';
    const CRITERIA_SLAPLAN = 'slaplan';
    const CRITERIA_TEMPLATEGROUP = 'templategroup';
    const CRITERIA_CREATOR = 'creator';

    // String
    const CRITERIA_SUBJECT = 'subject';
    const CRITERIA_FULLNAME = 'fullname';
    const CRITERIA_EMAIL = 'email';
    const CRITERIA_LASTREPLIER = 'lastreplier';
    const CRITERIA_CHARSET = 'charset';
    const CRITERIA_IPADDRESS = 'ipaddress';

    // Calendar
    const CRITERIA_DATE = 'date';
    const CRITERIA_DATERANGE = 'daterange';
    const CRITERIA_LASTACTIVITY = 'lastactivity';
    const CRITERIA_LASTACTIVITYRANGE = 'lastactivityrange';
    const CRITERIA_LASTSTAFFREPLY = 'laststaffreply';
    const CRITERIA_LASTSTAFFREPLYRANGE = 'laststaffreplyrange';
    const CRITERIA_LASTUSERREPLY = 'lastuserreply';
    const CRITERIA_LASTUSERREPLYRANGE = 'lastuserreplyrange';
    const CRITERIA_DUE = 'due';
    const CRITERIA_DUERANGE = 'duerange';
    const CRITERIA_RESOLUTIONDUE = 'resolutiondue';
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
     * Constructor
     *
     * @author Varun Shoor
     * @param SWIFT_Data $_SWIFT_DataObject The SWIFT_Data Object
     * @throws SWIFT_Workflow_Exception If the Record could not be loaded
     */
    public function __construct(SWIFT_Data $_SWIFT_DataObject) {
        if (!$_SWIFT_DataObject instanceof SWIFT_Data || !$_SWIFT_DataObject->GetIsClassLoaded() || !$this->LoadData($_SWIFT_DataObject)) {
            throw new SWIFT_Workflow_Exception('Failed to load Ticket Object');
        }

        $this->SetIsClassLoaded(true);

        parent::__construct($this->GetProperty('_criteria'), $this->GetProperty('ruletype'));
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
     * @throws SWIFT_Workflow_Exception If the Class is not Loaded
     */
    public function ProcessUpdatePool()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        } else if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . "ticketworkflows", $this->GetUpdatePool(), 'UPDATE', "ticketworkflowid = '" .
                (int) ($this->GetTicketWorkflowID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Ticket Workflow ID
     *
     * @author Varun Shoor
     * @return mixed "ticketworkflowid" on Success, "false" otherwise
     * @throws SWIFT_Workflow_Exception If the Class is not Loaded
     */
    public function GetTicketWorkflowID()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Workflow_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['ticketworkflowid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param SWIFT_Data $_SWIFT_DataObject The SWIFT_Data Object
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    protected function LoadData($_SWIFT_DataObject) {
        $_SWIFT = SWIFT::GetInstance();

        // Is it a ID?
        if ($_SWIFT_DataObject instanceof SWIFT_DataID && $_SWIFT_DataObject->GetIsClassLoaded()) {
            $_dataStore = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "ticketworkflows WHERE ticketworkflowid = '" .
                    (int) ($_SWIFT_DataObject->GetDataID()) . "'");
            if (isset($_dataStore['ticketworkflowid']) && !empty($_dataStore['ticketworkflowid'])) {
                $_dataStore['_criteria'] = array();

                $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketworkflowcriteria WHERE ticketworkflowid = '" .
                        (int) ($_SWIFT_DataObject->GetDataID()) . "'");
                while ($_SWIFT->Database->NextRecord()) {
                    $_dataStore['_criteria'][$_SWIFT->Database->Record['ticketworkflowcriteriaid']] = array($_SWIFT->Database->Record['name'],
                        $_SWIFT->Database->Record['ruleop'], $_SWIFT->Database->Record['rulematch'], $_SWIFT->Database->Record['rulematchtype']);
                }

                $this->_dataStore = $_dataStore;

                return true;
            }

            // Is it a Store?
        } else if ($_SWIFT_DataObject instanceof SWIFT_DataStore && $_SWIFT_DataObject->GetIsClassLoaded()) {
            $this->_dataStore = $_SWIFT_DataObject->GetDataStore();

            if (!isset($this->_dataStore['ticketworkflowid']) || empty($this->_dataStore['ticketworkflowid'])) {
                throw new SWIFT_Workflow_Exception(SWIFT_INVALIDDATA);
            }

            return true;
        }

        throw new SWIFT_Workflow_Exception(SWIFT_INVALIDDATA);
    }

    /**
     * Returns the Data Store Array
     *
     * @author Varun Shoor
     * @return mixed "_dataStore" Array on Success, "false" otherwise
     * @throws SWIFT_Workflow_Exception If the Class is not Loaded
     */
    public function GetDataStore()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Workflow_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore;
    }

    /**
     * Retrieves a Property Value from Data Store
     *
     * @author Varun Shoor
     * @param string $_key The Key Identifier
     * @return mixed Property Data on Success, "false" otherwise
     * @throws SWIFT_Workflow_Exception If the Class is not Loaded or If Invalid Data Provided
     */
    public function GetProperty($_key)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Workflow_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_Workflow_Exception(SWIFT_INVALIDDATA);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Check to see if it is a valid action
     *
     * @author Varun Shoor
     * @param string $_workflowAction The Workflow Action
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidAction($_workflowAction)
    {
        if ($_workflowAction == self::ACTION_DEPARTMENT || $_workflowAction == self::ACTION_OWNER || $_workflowAction == self::ACTION_STATUS ||
                $_workflowAction == self::ACTION_PRIORITY || $_workflowAction == self::ACTION_ADDNOTE ||
                $_workflowAction == self::ACTION_FLAGTICKET || $_workflowAction == self::ACTION_SLAPLAN ||
                $_workflowAction == self::ACTION_TICKETTYPE || $_workflowAction == self::ACTION_ADDTAGS ||
                $_workflowAction == self::ACTION_REMOVETAGS || $_workflowAction == self::ACTION_BAYESIAN || $_workflowAction == self::ACTION_TRASH)
        {
            return true;
        }

        return false;
    }

    /**
     * Clear the Workflow Criteria
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Workflow_Exception If the Class is not Loaded
     */
    public function ClearCriteria()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Workflow_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "ticketworkflowcriteria WHERE ticketworkflowid = '" .
                (int) ($this->GetTicketWorkflowID()) . "'");

        return true;
    }

    /**
     * Clear the Workflow Actions
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Workflow_Exception If the Class is not Loaded
     */
    public function ClearActions()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Workflow_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "ticketworkflowactions WHERE ticketworkflowid = '" .
                (int) ($this->GetTicketWorkflowID()) . "'");

        return true;
    }

    /**
     * Get the Workflow Actions
     *
     * @author Varun Shoor
     * @return mixed "_actionsContainer" (ARRAY) on Success, "false" otherwise
     * @throws SWIFT_Workflow_Exception If the Class is not Loaded
     */
    public function GetActions()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Workflow_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_actionsContainer = array();

        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketworkflowactions WHERE ticketworkflowid = '" .
                (int) ($this->GetTicketworkflowID()) . "'");
        while ($this->Database->NextRecord())
        {
            $_actionsContainer[$this->Database->Record['ticketworkflowactionid']] = $this->Database->Record;
        }

        return $_actionsContainer;
    }

    /**
     * Insert Workflow Criteria
     *
     * @author Varun Shoor
     * @param array $_criteriaContainer The Criteria Container
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data Provided
     */
    public function InsertCriteria($_criteriaContainer)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Workflow_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!_is_array($_criteriaContainer)) {
            throw new SWIFT_Workflow_Exception(SWIFT_CLASSNOTLOADED);
        }

        foreach ($_criteriaContainer as $_key => $_val)
        {
            if (!isset($_val[0], $_val[1], $_val[2]))
            {
                continue;
            }

            $this->Database->AutoExecute(TABLE_PREFIX . "ticketworkflowcriteria", array('ticketworkflowid' => (int) ($this->GetTicketWorkflowID()),
                'name' => $_val[0], 'ruleop' => (int) ($_val[1]), 'rulematch' => (string)$_val[2], 'rulematchtype' => (int) ($_val[3])), 'INSERT');
        }

        return true;
    }

    /**
     * Insert Workflow Actions
     *
     * @author Varun Shoor
     * @param array $_actionContainer The Workflow Action Container
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data Provided
     */
    public function InsertAction($_actionContainer)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Workflow_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!_is_array($_actionContainer)) {
            throw new SWIFT_Workflow_Exception(SWIFT_INVALIDDATA);
        }

        foreach ($_actionContainer as $_key => $_val)
        {
            if (!isset($_val['name']) || !self::IsValidAction($_val['name']) || (!isset($_val['typeid']) && !isset($_val['typedata']) &&
                    !isset($_val['typechar'])))
            {
                continue;
            }

            if (!isset($_val['typeid']))
            {
                $_val['typeid'] = 0;
            }

            if (!isset($_val['typedata']))
            {
                $_val['typedata'] = '';
            }

            if (!isset($_val['typechar']))
            {
                $_val['typechar'] = '';
            }

            $this->Database->AutoExecute(TABLE_PREFIX . 'ticketworkflowactions', array('ticketworkflowid' => (int) ($this->GetTicketWorkflowID()),
                'name' => $_val['name'], 'typeid' => (int) ($_val['typeid']), 'typedata' => strval($_val['typedata']),
                'typechar' => strval($_val['typechar'])), 'INSERT');
        }
    }

    /**
     * Create a new Ticket Workflow Record
     *
     * @author Varun Shoor
     * @param string $_title The Workflow Title
     * @param bool $_isEnabled Whether the Workflow is Enabled or not
     * @param int $_sortOrder The Ticket Workflow Sort Order
     * @param int $_ruleType The Ticket Workflow Rule Type
     * @param array $_criteriaContainer The Criteria Container Array
     * @param array $_actionContainer The Actions Container Array
     * @param array $_notificationContainer The Notification Container
     * @param bool $_staffVisibilityCustom Whether the workflow should be visible to only select staff groups
     * @param array $_staffGroupIDList (OPTIONAL) The Staff Groups the workflow should be Linked With
     * @return mixed "_SWIFT_TicketWorkflowObject" (OBJECT) on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data Provided or If Creation Fails
     */
    public static function Create($_title, $_isEnabled, $_sortOrder, $_ruleType, $_criteriaContainer, $_actionContainer, $_notificationContainer,
            $_staffVisibilityCustom, $_staffGroupIDList = array(), $_userVisibility = 0)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_title) || !_is_array($_criteriaContainer) || !_is_array($_actionContainer))
        {
            throw new SWIFT_Workflow_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'ticketworkflows', array('title' => $_title, 'isenabled' => (int) ($_isEnabled),
            'sortorder' =>  ($_sortOrder), 'ruletype' =>  ($_ruleType), 'dateline' => DATENOW,
            'staffvisibilitycustom' => (int) ($_staffVisibilityCustom), 'uservisibility' => $_userVisibility), 'INSERT');
        $_ticketWorkflowID = $_SWIFT->Database->Insert_ID();

        if (!$_ticketWorkflowID)
        {
            throw new SWIFT_Workflow_Exception(SWIFT_CREATEFAILED);
        }

        $_SWIFT_TicketWorkflowObject = new SWIFT_TicketWorkflow(new SWIFT_DataID($_ticketWorkflowID));
        if (!$_SWIFT_TicketWorkflowObject instanceof SWIFT_TicketWorkflow || !$_SWIFT_TicketWorkflowObject->GetIsClassLoaded())
        {
            throw new SWIFT_Workflow_Exception(SWIFT_CREATEFAILED);
        }

        $_SWIFT_TicketWorkflowObject->InsertCriteria($_criteriaContainer);

        $_SWIFT_TicketWorkflowObject->InsertAction($_actionContainer);

        if (_is_array($_notificationContainer))
        {
            foreach ($_notificationContainer as $_key => $_val)
            {
                SWIFT_TicketWorkflowNotification::Create($_val[0], $_SWIFT_TicketWorkflowObject->GetTicketWorkflowID(), $_val[1], $_val[2]);
            }
        }

        // Process Staff Group Links
        if (_is_array($_staffGroupIDList) && $_staffVisibilityCustom == 1)
        {
            foreach ($_staffGroupIDList as $_key => $_val)
            {
                SWIFT_StaffGroupLink::Create($_val, SWIFT_StaffGroupLink::TYPE_WORKFLOW, $_ticketWorkflowID, false);
            }
        }

        SWIFT_StaffGroupLink::RebuildCache();

        self::RebuildCache();

        return $_SWIFT_TicketWorkflowObject;
    }

    /**
     * Update the Ticket Workflow Record
     *
     * @author Varun Shoor
     * @param string $_title The Workflow Title
     * @param bool $_isEnabled Whether the Workflow is Enabled or not
     * @param int $_sortOrder The Ticket Workflow Sort Order
     * @param int $_ruleType The Ticket Workflow Rule Type
     * @param array $_criteriaContainer The Criteria Container Array
     * @param array $_actionContainer The Actions Container Array
     * @param array $_notificationContainer The Notification Container
     * @param array $_staffGroupIDList (OPTIONAL) The Staff Groups the workflow should be Linked With
     * @return mixed "_SWIFT_TicketWorkflowObject" (OBJECT) on Success, "false" otherwise
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Workflow_Exception If the Class is not Loaded or If Invalid Data Provided
     */
    public function Update($_title, $_isEnabled, $_sortOrder, $_ruleType, $_criteriaContainer, $_actionContainer, $_notificationContainer,
            $_staffVisibilityCustom, $_staffGroupIDList = array(), $_userVisibility = 0)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Workflow_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_title) || !_is_array($_criteriaContainer) || !_is_array($_actionContainer)) {
            throw new SWIFT_Workflow_Exception(SWIFT_INVALIDDATA);
        }

        $this->UpdatePool('title', $_title);
        $this->UpdatePool('isenabled', (int) ($_isEnabled));
        $this->UpdatePool('sortorder',  ($_sortOrder));
        $this->UpdatePool('ruletype',  ($_ruleType));
        $this->UpdatePool('dateline', DATENOW);
        $this->UpdatePool('staffvisibilitycustom', (int) ($_staffVisibilityCustom));
        $this->UpdatePool('uservisibility', (int) ($_userVisibility));

        $this->ProcessUpdatePool();

        $this->ClearCriteria();
        $this->ClearActions();

        $this->InsertCriteria($_criteriaContainer);

        $this->InsertAction($_actionContainer);

        SWIFT_TicketWorkflowNotification::DeleteOnWorkflow(array($this->GetTicketWorkflowID()));
        if (_is_array($_notificationContainer))
        {
            foreach ($_notificationContainer as $_key => $_val)
            {
                SWIFT_TicketWorkflowNotification::Create($_val[0], $this->GetTicketWorkflowID(), $_val[1], $_val[2]);
            }
        }

        // Process the Staff Groups
        SWIFT_StaffGroupLink::DeleteOnLink(SWIFT_StaffGroupLink::TYPE_WORKFLOW, $this->GetTicketWorkflowID());
        if (_is_array($_staffGroupIDList) && $_staffVisibilityCustom == 1)
        {
            foreach ($_staffGroupIDList as $_key => $_val)
            {
                SWIFT_StaffGroupLink::Create($_val, SWIFT_StaffGroupLink::TYPE_WORKFLOW, $this->GetTicketWorkflowID(), false);
            }
        }

        SWIFT_StaffGroupLink::RebuildCache();

        self::RebuildCache();

        return true;
    }

    /**
     * Delete the Ticket Workflow record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Workflow_Exception If the Class is not Loaded
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Workflow_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($this->GetTicketWorkflowID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of Ticket Workflows
     *
     * @author Varun Shoor
     * @param array $_ticketWorkflowIDList The Ticket Workflow ID list
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_ticketWorkflowIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_ticketWorkflowIDList))
        {
            return false;
        }

        // First get all the workflows specified
        $_finalTicketWorkflowIDList = array();
        $_index = 1;

        $_finalText = "";
        $_SWIFT->Database->Query("SELECT * FROM ". TABLE_PREFIX ."ticketworkflows WHERE ticketworkflowid IN (" .
                BuildIN($_ticketWorkflowIDList) . ")");
        while ($_SWIFT->Database->NextRecord())
        {
            $_finalTicketWorkflowIDList[] = $_SWIFT->Database->Record['ticketworkflowid'];

            $_finalText .= $_index . ". " . htmlspecialchars($_SWIFT->Database->Record['title']) . "<br />";
            $_index++;
        }

        SWIFT::Info(sprintf($_SWIFT->Language->Get('titleworkflowdel'), count($_finalTicketWorkflowIDList)),
                $_SWIFT->Language->Get('msgworkflowdel') . "<br />" . $_finalText);

        if (!count($_finalTicketWorkflowIDList))
        {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM ". TABLE_PREFIX ."ticketworkflows WHERE ticketworkflowid IN (" .
                BuildIN($_finalTicketWorkflowIDList) . ")");
        $_SWIFT->Database->Query("DELETE FROM ". TABLE_PREFIX ."ticketworkflowcriteria WHERE ticketworkflowid IN (" .
                BuildIN($_finalTicketWorkflowIDList) . ")");
        $_SWIFT->Database->Query("DELETE FROM ". TABLE_PREFIX ."ticketworkflowactions WHERE ticketworkflowid IN (" .
                BuildIN($_finalTicketWorkflowIDList) . ")");

        // Clear the Notification Pool
        SWIFT_TicketWorkflowNotification::DeleteOnWorkflow($_finalTicketWorkflowIDList);

        // Clear the Staff Group Links
        SWIFT_StaffGroupLink::DeleteOnLinkList(SWIFT_StaffGroupLink::TYPE_WORKFLOW, $_finalTicketWorkflowIDList);

        // Clear the Workflow <> Ticket Links
        SWIFT_TicketLinkedTable::DeleteOnLinkList(SWIFT_TicketLinkedTable::LINKTYPE_WORKFLOW, $_finalTicketWorkflowIDList);

        self::RebuildCache();

        return true;
    }

    /**
     * Enable a list of Ticket Workflows
     *
     * @author Varun Shoor
     * @param array $_ticketWorkflowIDList The Ticket Workflow ID list
     * @return bool "true" on Success, "false" otherwise
     */
    public static function EnableList($_ticketWorkflowIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_ticketWorkflowIDList))
        {
            return false;
        }

        // First get all the workflows specified
        $_finalTicketWorkflowIDList = array();
        $_index = 1;

        $_finalText = "";
        $_SWIFT->Database->Query("SELECT * FROM ". TABLE_PREFIX ."ticketworkflows WHERE ticketworkflowid IN (" .
                BuildIN($_ticketWorkflowIDList) . ")");
        while ($_SWIFT->Database->NextRecord())
        {
            if ($_SWIFT->Database->Record['isenabled'] == '1') {
                continue;
            }

            $_finalTicketWorkflowIDList[] = $_SWIFT->Database->Record['ticketworkflowid'];

            $_finalText .= $_index . ". " . htmlspecialchars($_SWIFT->Database->Record['title']) . "<br />";
            $_index++;
        }

        if (!count($_finalTicketWorkflowIDList))
        {
            return false;
        }

        SWIFT::Info(sprintf($_SWIFT->Language->Get('titleworkflowenable'), count($_finalTicketWorkflowIDList)),
                $_SWIFT->Language->Get('msgworkflowenable') . "<br />" . $_finalText);

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'ticketworkflows', array('isenabled' => '1'), 'UPDATE', "ticketworkflowid IN (" .
                BuildIN($_finalTicketWorkflowIDList) . ")");

        self::RebuildCache();

        return true;
    }

    /**
     * Disable a list of Ticket Workflows
     *
     * @author Varun Shoor
     * @param array $_ticketWorkflowIDList The Ticket Workflow ID list
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DisableList($_ticketWorkflowIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_ticketWorkflowIDList))
        {
            return false;
        }

        // First get all the workflows specified
        $_finalTicketWorkflowIDList = array();
        $_index = 1;

        $_finalText = "";
        $_SWIFT->Database->Query("SELECT * FROM ". TABLE_PREFIX ."ticketworkflows WHERE ticketworkflowid IN (" .
                BuildIN($_ticketWorkflowIDList) . ")");
        while ($_SWIFT->Database->NextRecord())
        {
            if ($_SWIFT->Database->Record['isenabled'] == '0') {
                continue;
            }

            $_finalTicketWorkflowIDList[] = $_SWIFT->Database->Record['ticketworkflowid'];

            $_finalText .= $_index . ". " . htmlspecialchars($_SWIFT->Database->Record['title']) . "<br />";
            $_index++;
        }

        if (!count($_finalTicketWorkflowIDList))
        {
            return false;
        }

        SWIFT::Info(sprintf($_SWIFT->Language->Get('titleworkflowdisable'), count($_finalTicketWorkflowIDList)),
                $_SWIFT->Language->Get('msgworkflowdisable') . "<br />" . $_finalText);

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'ticketworkflows', array('isenabled' => '0'), 'UPDATE', "ticketworkflowid IN (" .
                BuildIN($_finalTicketWorkflowIDList) . ")");

        self::RebuildCache();

        return true;
    }

    /**
     * Rebuild the Ticket Workflow Cache
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public static function RebuildCache()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_cache = array();

        $_ticketWorkflowIDList = array();

        $_index = 0;
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketworkflows ORDER BY sortorder ASC", 3);
        while ($_SWIFT->Database->NextRecord(3))
        {
            $_index++;

            $_cache[$_SWIFT->Database->Record3['ticketworkflowid']] = $_SWIFT->Database->Record3;
            $_cache[$_SWIFT->Database->Record3['ticketworkflowid']]['index'] = $_index;
            $_cache[$_SWIFT->Database->Record3['ticketworkflowid']]['_criteria'] = array();

            $_ticketWorkflowIDList[] = $_SWIFT->Database->Record3['ticketworkflowid'];
        }

        if (count($_ticketWorkflowIDList)) {
            $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketworkflowcriteria WHERE ticketworkflowid IN (" .
                    BuildIN($_ticketWorkflowIDList) . ")", 3);
            while ($_SWIFT->Database->NextRecord(3)) {
                    $_cache[$_SWIFT->Database->Record3['ticketworkflowid']]['_criteria'][$_SWIFT->Database->Record3['ticketworkflowcriteriaid']] =
                            array($_SWIFT->Database->Record3['name'], $_SWIFT->Database->Record3['ruleop'], $_SWIFT->Database->Record3['rulematch'], $_SWIFT->Database->Record3['rulematchtype']);
                }
        }

        $_SWIFT->Cache->Update('ticketworkflowcache', $_cache);

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

        $_criteriaPointer[self::CRITERIA_TICKETTYPE]['title'] = $_SWIFT->Language->Get('wf' . self::CRITERIA_TICKETTYPE);
        $_criteriaPointer[self::CRITERIA_TICKETTYPE]['desc'] = $_SWIFT->Language->Get('desc_wf' . self::CRITERIA_TICKETTYPE);
        $_criteriaPointer[self::CRITERIA_TICKETTYPE]['op'] = 'bool';
        $_criteriaPointer[self::CRITERIA_TICKETTYPE]['field'] = 'custom';

        $_criteriaPointer[self::CRITERIA_TICKETSTATUS]['title'] = $_SWIFT->Language->Get('wf' . self::CRITERIA_TICKETSTATUS);
        $_criteriaPointer[self::CRITERIA_TICKETSTATUS]['desc'] = $_SWIFT->Language->Get('desc_wf' . self::CRITERIA_TICKETSTATUS);
        $_criteriaPointer[self::CRITERIA_TICKETSTATUS]['op'] = 'bool';
        $_criteriaPointer[self::CRITERIA_TICKETSTATUS]['field'] = 'custom';

        $_criteriaPointer[self::CRITERIA_TICKETPRIORITY]['title'] = $_SWIFT->Language->Get('wf' . self::CRITERIA_TICKETPRIORITY);
        $_criteriaPointer[self::CRITERIA_TICKETPRIORITY]['desc'] = $_SWIFT->Language->Get('desc_wf' . self::CRITERIA_TICKETPRIORITY);
        $_criteriaPointer[self::CRITERIA_TICKETPRIORITY]['op'] = 'bool';
        $_criteriaPointer[self::CRITERIA_TICKETPRIORITY]['field'] = 'custom';

        $_criteriaPointer[self::CRITERIA_USERGROUP]['title'] = $_SWIFT->Language->Get('wf' . self::CRITERIA_USERGROUP);
        $_criteriaPointer[self::CRITERIA_USERGROUP]['desc'] = $_SWIFT->Language->Get('desc_wf' . self::CRITERIA_USERGROUP);
        $_criteriaPointer[self::CRITERIA_USERGROUP]['op'] = 'bool';
        $_criteriaPointer[self::CRITERIA_USERGROUP]['field'] = 'custom';

        $_criteriaPointer[self::CRITERIA_DEPARTMENT]['title'] = $_SWIFT->Language->Get('wf' . self::CRITERIA_DEPARTMENT);
        $_criteriaPointer[self::CRITERIA_DEPARTMENT]['desc'] = $_SWIFT->Language->Get('desc_wf' . self::CRITERIA_DEPARTMENT);
        $_criteriaPointer[self::CRITERIA_DEPARTMENT]['op'] = 'bool';
        $_criteriaPointer[self::CRITERIA_DEPARTMENT]['field'] = 'custom';

        $_criteriaPointer[self::CRITERIA_OWNER]['title'] = $_SWIFT->Language->Get('wf' . self::CRITERIA_OWNER);
        $_criteriaPointer[self::CRITERIA_OWNER]['desc'] = $_SWIFT->Language->Get('desc_wf' . self::CRITERIA_OWNER);
        $_criteriaPointer[self::CRITERIA_OWNER]['op'] = 'bool';
        $_criteriaPointer[self::CRITERIA_OWNER]['field'] = 'custom';

        $_criteriaPointer[self::CRITERIA_EMAILQUEUE]['title'] = $_SWIFT->Language->Get('wf' . self::CRITERIA_EMAILQUEUE);
        $_criteriaPointer[self::CRITERIA_EMAILQUEUE]['desc'] = $_SWIFT->Language->Get('desc_wf' . self::CRITERIA_EMAILQUEUE);
        $_criteriaPointer[self::CRITERIA_EMAILQUEUE]['op'] = 'bool';
        $_criteriaPointer[self::CRITERIA_EMAILQUEUE]['field'] = 'custom';

        $_criteriaPointer[self::CRITERIA_FLAGTYPE]['title'] = $_SWIFT->Language->Get('wf' . self::CRITERIA_FLAGTYPE);
        $_criteriaPointer[self::CRITERIA_FLAGTYPE]['desc'] = $_SWIFT->Language->Get('desc_wf' . self::CRITERIA_FLAGTYPE);
        $_criteriaPointer[self::CRITERIA_FLAGTYPE]['op'] = 'bool';
        $_criteriaPointer[self::CRITERIA_FLAGTYPE]['field'] = 'custom';

        $_criteriaPointer[self::CRITERIA_SLAPLAN]['title'] = $_SWIFT->Language->Get('wf' . self::CRITERIA_SLAPLAN);
        $_criteriaPointer[self::CRITERIA_SLAPLAN]['desc'] = $_SWIFT->Language->Get('desc_wf' . self::CRITERIA_SLAPLAN);
        $_criteriaPointer[self::CRITERIA_SLAPLAN]['op'] = 'bool';
        $_criteriaPointer[self::CRITERIA_SLAPLAN]['field'] = 'custom';

        $_criteriaPointer[self::CRITERIA_TEMPLATEGROUP]['title'] = $_SWIFT->Language->Get('wf' . self::CRITERIA_TEMPLATEGROUP);
        $_criteriaPointer[self::CRITERIA_TEMPLATEGROUP]['desc'] = $_SWIFT->Language->Get('desc_wf' . self::CRITERIA_TEMPLATEGROUP);
        $_criteriaPointer[self::CRITERIA_TEMPLATEGROUP]['op'] = 'bool';
        $_criteriaPointer[self::CRITERIA_TEMPLATEGROUP]['field'] = 'custom';

        $_criteriaPointer[self::CRITERIA_BAYESIAN]['title'] = $_SWIFT->Language->Get('wf' . self::CRITERIA_BAYESIAN);
        $_criteriaPointer[self::CRITERIA_BAYESIAN]['desc'] = $_SWIFT->Language->Get('desc_wf' . self::CRITERIA_BAYESIAN);
        $_criteriaPointer[self::CRITERIA_BAYESIAN]['op'] = 'bool';
        $_criteriaPointer[self::CRITERIA_BAYESIAN]['field'] = 'custom';

        $_criteriaPointer[self::CRITERIA_CREATOR]['title'] = $_SWIFT->Language->Get('wf' . self::CRITERIA_CREATOR);
        $_criteriaPointer[self::CRITERIA_CREATOR]['desc'] = $_SWIFT->Language->Get('desc_wf' . self::CRITERIA_CREATOR);
        $_criteriaPointer[self::CRITERIA_CREATOR]['op'] = 'bool';
        $_criteriaPointer[self::CRITERIA_CREATOR]['field'] = 'custom';

        $_criteriaPointer[self::CRITERIA_SUBJECT]['title'] = $_SWIFT->Language->Get('wf' . self::CRITERIA_SUBJECT);
        $_criteriaPointer[self::CRITERIA_SUBJECT]['desc'] = $_SWIFT->Language->Get('desc_wf' . self::CRITERIA_SUBJECT);
        $_criteriaPointer[self::CRITERIA_SUBJECT]['op'] = 'string';
        $_criteriaPointer[self::CRITERIA_SUBJECT]['field'] = 'text';

        $_criteriaPointer[self::CRITERIA_FULLNAME]['title'] = $_SWIFT->Language->Get('wf' . self::CRITERIA_FULLNAME);
        $_criteriaPointer[self::CRITERIA_FULLNAME]['desc'] = $_SWIFT->Language->Get('desc_wf' . self::CRITERIA_FULLNAME);
        $_criteriaPointer[self::CRITERIA_FULLNAME]['op'] = 'string';
        $_criteriaPointer[self::CRITERIA_FULLNAME]['field'] = 'text';

        $_criteriaPointer[self::CRITERIA_EMAIL]['title'] = $_SWIFT->Language->Get('wf' . self::CRITERIA_EMAIL);
        $_criteriaPointer[self::CRITERIA_EMAIL]['desc'] = $_SWIFT->Language->Get('desc_wf' . self::CRITERIA_EMAIL);
        $_criteriaPointer[self::CRITERIA_EMAIL]['op'] = 'string';
        $_criteriaPointer[self::CRITERIA_EMAIL]['field'] = 'text';

        $_criteriaPointer[self::CRITERIA_LASTREPLIER]['title'] = $_SWIFT->Language->Get('wf' . self::CRITERIA_LASTREPLIER);
        $_criteriaPointer[self::CRITERIA_LASTREPLIER]['desc'] = $_SWIFT->Language->Get('desc_wf' . self::CRITERIA_LASTREPLIER);
        $_criteriaPointer[self::CRITERIA_LASTREPLIER]['op'] = 'string';
        $_criteriaPointer[self::CRITERIA_LASTREPLIER]['field'] = 'text';

        $_criteriaPointer[self::CRITERIA_CHARSET]['title'] = $_SWIFT->Language->Get('wf' . self::CRITERIA_CHARSET);
        $_criteriaPointer[self::CRITERIA_CHARSET]['desc'] = $_SWIFT->Language->Get('desc_wf' . self::CRITERIA_CHARSET);
        $_criteriaPointer[self::CRITERIA_CHARSET]['op'] = 'string';
        $_criteriaPointer[self::CRITERIA_CHARSET]['field'] = 'text';

        $_criteriaPointer[self::CRITERIA_IPADDRESS]['title'] = $_SWIFT->Language->Get('wf' . self::CRITERIA_IPADDRESS);
        $_criteriaPointer[self::CRITERIA_IPADDRESS]['desc'] = $_SWIFT->Language->Get('desc_wf' . self::CRITERIA_IPADDRESS);
        $_criteriaPointer[self::CRITERIA_IPADDRESS]['op'] = 'string';
        $_criteriaPointer[self::CRITERIA_IPADDRESS]['field'] = 'text';

        $_criteriaPointer[self::CRITERIA_DATE]['title'] = $_SWIFT->Language->Get('wf' . self::CRITERIA_DATE);
        $_criteriaPointer[self::CRITERIA_DATE]['desc'] = $_SWIFT->Language->Get('desc_wf' . self::CRITERIA_DATE);
        $_criteriaPointer[self::CRITERIA_DATE]['op'] = 'cal';
        $_criteriaPointer[self::CRITERIA_DATE]['field'] = 'cal';

        $_criteriaPointer[self::CRITERIA_DATERANGE]['title'] = $_SWIFT->Language->Get('wf' . self::CRITERIA_DATERANGE);
        $_criteriaPointer[self::CRITERIA_DATERANGE]['desc'] = $_SWIFT->Language->Get('desc_wf' . self::CRITERIA_DATERANGE);
        $_criteriaPointer[self::CRITERIA_DATERANGE]['op'] = 'bool';
        $_criteriaPointer[self::CRITERIA_DATERANGE]['field'] = 'daterange';

        $_criteriaPointer[self::CRITERIA_LASTACTIVITY]['title'] = $_SWIFT->Language->Get('wf' . self::CRITERIA_LASTACTIVITY);
        $_criteriaPointer[self::CRITERIA_LASTACTIVITY]['desc'] = $_SWIFT->Language->Get('desc_wf' . self::CRITERIA_LASTACTIVITY);
        $_criteriaPointer[self::CRITERIA_LASTACTIVITY]['op'] = 'cal';
        $_criteriaPointer[self::CRITERIA_LASTACTIVITY]['field'] = 'cal';

        $_criteriaPointer[self::CRITERIA_LASTACTIVITYRANGE]['title'] = $_SWIFT->Language->Get('wf' . self::CRITERIA_LASTACTIVITYRANGE);
        $_criteriaPointer[self::CRITERIA_LASTACTIVITYRANGE]['desc'] = $_SWIFT->Language->Get('desc_wf' . self::CRITERIA_LASTACTIVITYRANGE);
        $_criteriaPointer[self::CRITERIA_LASTACTIVITYRANGE]['op'] = 'bool';
        $_criteriaPointer[self::CRITERIA_LASTACTIVITYRANGE]['field'] = 'daterange';

        $_criteriaPointer[self::CRITERIA_LASTSTAFFREPLY]['title'] = $_SWIFT->Language->Get('wf' . self::CRITERIA_LASTSTAFFREPLY);
        $_criteriaPointer[self::CRITERIA_LASTSTAFFREPLY]['desc'] = $_SWIFT->Language->Get('desc_wf' . self::CRITERIA_LASTSTAFFREPLY);
        $_criteriaPointer[self::CRITERIA_LASTSTAFFREPLY]['op'] = 'cal';
        $_criteriaPointer[self::CRITERIA_LASTSTAFFREPLY]['field'] = 'cal';

        $_criteriaPointer[self::CRITERIA_LASTSTAFFREPLYRANGE]['title'] = $_SWIFT->Language->Get('wf' . self::CRITERIA_LASTSTAFFREPLYRANGE);
        $_criteriaPointer[self::CRITERIA_LASTSTAFFREPLYRANGE]['desc'] = $_SWIFT->Language->Get('desc_wf' . self::CRITERIA_LASTSTAFFREPLYRANGE);
        $_criteriaPointer[self::CRITERIA_LASTSTAFFREPLYRANGE]['op'] = 'bool';
        $_criteriaPointer[self::CRITERIA_LASTSTAFFREPLYRANGE]['field'] = 'daterange';

        $_criteriaPointer[self::CRITERIA_LASTUSERREPLY]['title'] = $_SWIFT->Language->Get('wf' . self::CRITERIA_LASTUSERREPLY);
        $_criteriaPointer[self::CRITERIA_LASTUSERREPLY]['desc'] = $_SWIFT->Language->Get('desc_wf' . self::CRITERIA_LASTUSERREPLY);
        $_criteriaPointer[self::CRITERIA_LASTUSERREPLY]['op'] = 'cal';
        $_criteriaPointer[self::CRITERIA_LASTUSERREPLY]['field'] = 'cal';

        $_criteriaPointer[self::CRITERIA_LASTUSERREPLYRANGE]['title'] = $_SWIFT->Language->Get('wf' . self::CRITERIA_LASTUSERREPLYRANGE);
        $_criteriaPointer[self::CRITERIA_LASTUSERREPLYRANGE]['desc'] = $_SWIFT->Language->Get('desc_wf' . self::CRITERIA_LASTUSERREPLYRANGE);
        $_criteriaPointer[self::CRITERIA_LASTUSERREPLYRANGE]['op'] = 'bool';
        $_criteriaPointer[self::CRITERIA_LASTUSERREPLYRANGE]['field'] = 'daterange';

        $_criteriaPointer[self::CRITERIA_DUE]['title'] = $_SWIFT->Language->Get('wf' . self::CRITERIA_DUE);
        $_criteriaPointer[self::CRITERIA_DUE]['desc'] = $_SWIFT->Language->Get('desc_wf' . self::CRITERIA_DUE);
        $_criteriaPointer[self::CRITERIA_DUE]['op'] = 'cal';
        $_criteriaPointer[self::CRITERIA_DUE]['field'] = 'cal';

        $_criteriaPointer[self::CRITERIA_DUERANGE]['title'] = $_SWIFT->Language->Get('wf' . self::CRITERIA_DUERANGE);
        $_criteriaPointer[self::CRITERIA_DUERANGE]['desc'] = $_SWIFT->Language->Get('desc_wf' . self::CRITERIA_DUERANGE);
        $_criteriaPointer[self::CRITERIA_DUERANGE]['op'] = 'bool';
        $_criteriaPointer[self::CRITERIA_DUERANGE]['field'] = 'daterangeforward';

        $_criteriaPointer[self::CRITERIA_ISOVERDUE]['title'] = $_SWIFT->Language->Get('wf' . self::CRITERIA_ISOVERDUE);
        $_criteriaPointer[self::CRITERIA_ISOVERDUE]['desc'] = $_SWIFT->Language->Get('desc_wf' . self::CRITERIA_ISOVERDUE);
        $_criteriaPointer[self::CRITERIA_ISOVERDUE]['op'] = 'bool';
        $_criteriaPointer[self::CRITERIA_ISOVERDUE]['field'] = 'bool';

        $_criteriaPointer[self::CRITERIA_RESOLUTIONDUE]['title'] = $_SWIFT->Language->Get('wf' . self::CRITERIA_RESOLUTIONDUE);
        $_criteriaPointer[self::CRITERIA_RESOLUTIONDUE]['desc'] = $_SWIFT->Language->Get('desc_wf' . self::CRITERIA_RESOLUTIONDUE);
        $_criteriaPointer[self::CRITERIA_RESOLUTIONDUE]['op'] = 'cal';
        $_criteriaPointer[self::CRITERIA_RESOLUTIONDUE]['field'] = 'cal';

        $_criteriaPointer[self::CRITERIA_RESOLUTIONDUERANGE]['title'] = $_SWIFT->Language->Get('wf' . self::CRITERIA_RESOLUTIONDUERANGE);
        $_criteriaPointer[self::CRITERIA_RESOLUTIONDUERANGE]['desc'] = $_SWIFT->Language->Get('desc_wf' . self::CRITERIA_RESOLUTIONDUERANGE);
        $_criteriaPointer[self::CRITERIA_RESOLUTIONDUERANGE]['op'] = 'bool';
        $_criteriaPointer[self::CRITERIA_RESOLUTIONDUERANGE]['field'] = 'daterangeforward';

        $_criteriaPointer[self::CRITERIA_ISRESOLUTIONOVERDUE]['title'] = $_SWIFT->Language->Get('wf' . self::CRITERIA_ISRESOLUTIONOVERDUE);
        $_criteriaPointer[self::CRITERIA_ISRESOLUTIONOVERDUE]['desc'] = $_SWIFT->Language->Get('desc_wf' . self::CRITERIA_ISRESOLUTIONOVERDUE);
        $_criteriaPointer[self::CRITERIA_ISRESOLUTIONOVERDUE]['op'] = 'bool';
        $_criteriaPointer[self::CRITERIA_ISRESOLUTIONOVERDUE]['field'] = 'bool';

        $_criteriaPointer[self::CRITERIA_TIMEWORKED]['title'] = $_SWIFT->Language->Get('wf' . self::CRITERIA_TIMEWORKED);
        $_criteriaPointer[self::CRITERIA_TIMEWORKED]['desc'] = $_SWIFT->Language->Get('desc_wf' . self::CRITERIA_TIMEWORKED);
        $_criteriaPointer[self::CRITERIA_TIMEWORKED]['op'] = 'int';
        $_criteriaPointer[self::CRITERIA_TIMEWORKED]['field'] = 'int';

        $_criteriaPointer[self::CRITERIA_TOTALREPLIES]['title'] = $_SWIFT->Language->Get('wf' . self::CRITERIA_TOTALREPLIES);
        $_criteriaPointer[self::CRITERIA_TOTALREPLIES]['desc'] = $_SWIFT->Language->Get('desc_wf' . self::CRITERIA_TOTALREPLIES);
        $_criteriaPointer[self::CRITERIA_TOTALREPLIES]['op'] = 'int';
        $_criteriaPointer[self::CRITERIA_TOTALREPLIES]['field'] = 'int';

        $_criteriaPointer[self::CRITERIA_PENDINGFOLLOWUPS]['title'] = $_SWIFT->Language->Get('wf' . self::CRITERIA_PENDINGFOLLOWUPS);
        $_criteriaPointer[self::CRITERIA_PENDINGFOLLOWUPS]['desc'] = $_SWIFT->Language->Get('desc_wf' . self::CRITERIA_PENDINGFOLLOWUPS);
        $_criteriaPointer[self::CRITERIA_PENDINGFOLLOWUPS]['op'] = 'int';
        $_criteriaPointer[self::CRITERIA_PENDINGFOLLOWUPS]['field'] = 'int';

        $_criteriaPointer[self::CRITERIA_ISEMAILED]['title'] = $_SWIFT->Language->Get('wf' . self::CRITERIA_ISEMAILED);
        $_criteriaPointer[self::CRITERIA_ISEMAILED]['desc'] = $_SWIFT->Language->Get('desc_wf' . self::CRITERIA_ISEMAILED);
        $_criteriaPointer[self::CRITERIA_ISEMAILED]['op'] = 'bool';
        $_criteriaPointer[self::CRITERIA_ISEMAILED]['field'] = 'bool';

        $_criteriaPointer[self::CRITERIA_ISEDITED]['title'] = $_SWIFT->Language->Get('wf' . self::CRITERIA_ISEDITED);
        $_criteriaPointer[self::CRITERIA_ISEDITED]['desc'] = $_SWIFT->Language->Get('desc_wf' . self::CRITERIA_ISEDITED);
        $_criteriaPointer[self::CRITERIA_ISEDITED]['op'] = 'bool';
        $_criteriaPointer[self::CRITERIA_ISEDITED]['field'] = 'bool';

        $_criteriaPointer[self::CRITERIA_HASNOTES]['title'] = $_SWIFT->Language->Get('wf' . self::CRITERIA_HASNOTES);
        $_criteriaPointer[self::CRITERIA_HASNOTES]['desc'] = $_SWIFT->Language->Get('desc_wf' . self::CRITERIA_HASNOTES);
        $_criteriaPointer[self::CRITERIA_HASNOTES]['op'] = 'bool';
        $_criteriaPointer[self::CRITERIA_HASNOTES]['field'] = 'bool';

        $_criteriaPointer[self::CRITERIA_HASATTACHMENTS]['title'] = $_SWIFT->Language->Get('wf' . self::CRITERIA_HASATTACHMENTS);
        $_criteriaPointer[self::CRITERIA_HASATTACHMENTS]['desc'] = $_SWIFT->Language->Get('desc_wf' . self::CRITERIA_HASATTACHMENTS);
        $_criteriaPointer[self::CRITERIA_HASATTACHMENTS]['op'] = 'bool';
        $_criteriaPointer[self::CRITERIA_HASATTACHMENTS]['field'] = 'bool';

        $_criteriaPointer[self::CRITERIA_ISESCALATED]['title'] = $_SWIFT->Language->Get('wf' . self::CRITERIA_ISESCALATED);
        $_criteriaPointer[self::CRITERIA_ISESCALATED]['desc'] = $_SWIFT->Language->Get('desc_wf' . self::CRITERIA_ISESCALATED);
        $_criteriaPointer[self::CRITERIA_ISESCALATED]['op'] = 'bool';
        $_criteriaPointer[self::CRITERIA_ISESCALATED]['field'] = 'bool';

        $_criteriaPointer[self::CRITERIA_HASDRAFT]['title'] = $_SWIFT->Language->Get('wf' . self::CRITERIA_HASDRAFT);
        $_criteriaPointer[self::CRITERIA_HASDRAFT]['desc'] = $_SWIFT->Language->Get('desc_wf' . self::CRITERIA_HASDRAFT);
        $_criteriaPointer[self::CRITERIA_HASDRAFT]['op'] = 'bool';
        $_criteriaPointer[self::CRITERIA_HASDRAFT]['field'] = 'bool';

        $_criteriaPointer[self::CRITERIA_HASBILLING]['title'] = $_SWIFT->Language->Get('wf' . self::CRITERIA_HASBILLING);
        $_criteriaPointer[self::CRITERIA_HASBILLING]['desc'] = $_SWIFT->Language->Get('desc_wf' . self::CRITERIA_HASBILLING);
        $_criteriaPointer[self::CRITERIA_HASBILLING]['op'] = 'bool';
        $_criteriaPointer[self::CRITERIA_HASBILLING]['field'] = 'bool';

        $_criteriaPointer[self::CRITERIA_ISPHONECALL]['title'] = $_SWIFT->Language->Get('wf' . self::CRITERIA_ISPHONECALL);
        $_criteriaPointer[self::CRITERIA_ISPHONECALL]['desc'] = $_SWIFT->Language->Get('desc_wf' . self::CRITERIA_ISPHONECALL);
        $_criteriaPointer[self::CRITERIA_ISPHONECALL]['op'] = 'bool';
        $_criteriaPointer[self::CRITERIA_ISPHONECALL]['field'] = 'bool';

        return $_criteriaPointer;
    }

    /**
     * Parse the Criteria Pointer and extend it
     *
     * @author Varun Shoor
     * @param array $_criteriaPointer The Criteria Pointer Container
     * @return bool "true" on Success, "false" otherwise
     */
    public static function ExtendCustomCriteria(&$_criteriaPointer)
    {
        $_SWIFT = SWIFT::GetInstance();

        // ======= TICKET TYPE =======
        $_fieldContainer = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "tickettypes ORDER BY displayorder ASC");
        while ($_SWIFT->Database->nextRecord())
        {
            $_fieldContainer[] = array('title' => $_SWIFT->Database->Record['title'], 'contents' => $_SWIFT->Database->Record['tickettypeid']);
        }

        if (!count($_fieldContainer))
        {
            $_fieldContainer[] = array('title' => $_SWIFT->Language->Get('notapplicable'), 'contents' => '0');
        }

        $_criteriaPointer[self::CRITERIA_TICKETTYPE]['fieldcontents'] = $_fieldContainer;

        // ======= BAYESIAN CATEGORIES =======
        $_fieldContainer = array();
        $_fieldContainer[] = array('title' => $_SWIFT->Language->Get('notset'), 'contents' => '0');
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "bayescategories ORDER BY category ASC");
        while ($_SWIFT->Database->nextRecord())
        {
            $_fieldContainer[] = array('title' => $_SWIFT->Database->Record['category'], 'contents' => $_SWIFT->Database->Record['bayescategoryid']);
        }

        $_criteriaPointer[self::CRITERIA_BAYESIAN]['fieldcontents'] = $_fieldContainer;

        // ======= TICKET STATUS =======
        $_fieldContainer = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketstatus ORDER BY displayorder ASC");
        while ($_SWIFT->Database->nextRecord())
        {
            $_fieldContainer[] = array('title' => $_SWIFT->Database->Record['title'], 'contents' => $_SWIFT->Database->Record['ticketstatusid']);
        }

        if (!count($_fieldContainer))
        {
            $_fieldContainer[] = array('title' => $_SWIFT->Language->Get('notapplicable'), 'contents' => '0');
        }

        $_criteriaPointer[self::CRITERIA_TICKETSTATUS]['fieldcontents'] = $_fieldContainer;

        // ======= TICKET PRIORITY =======
        $_fieldContainer = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketpriorities ORDER BY displayorder ASC");
        while ($_SWIFT->Database->NextRecord())
        {
            $_fieldContainer[] = array('title' => $_SWIFT->Database->Record['title'], 'contents' => $_SWIFT->Database->Record['priorityid']);
        }

        if (!count($_fieldContainer))
        {
            $_fieldContainer[] = array('title' => $_SWIFT->Language->Get('notapplicable'), 'contents' => '0');
        }

        $_criteriaPointer[self::CRITERIA_TICKETPRIORITY]['fieldcontents'] = $_fieldContainer;

        // ======= USER GROUPS =======
        $_fieldContainer = array();
        $_fieldContainer[] = array('title' => $_SWIFT->Language->Get('notset'), 'contents' => '0');
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "usergroups ORDER BY title ASC");
        while ($_SWIFT->Database->NextRecord())
        {
            $_fieldContainer[] = array('title' => $_SWIFT->Database->Record['title'], 'contents' => $_SWIFT->Database->Record['usergroupid']);
        }

        $_criteriaPointer[self::CRITERIA_USERGROUP]['fieldcontents'] = $_fieldContainer;

        // ======= DEPARTMENT =======
        $_departmentMapContainer = SWIFT_Department::GetDepartmentMap(APP_TICKETS);
        $_fieldContainer = array();

        foreach ($_departmentMapContainer as $_key => $_val)
        {
            $_fieldContainer[] = array('title' => $_val['title'], 'contents' => $_val['departmentid']);

            if (_is_array($_val['subdepartments']))
            {
                foreach ($_val['subdepartments'] as $_subKey => $_subVal)
                {
                    $_fieldContainer[] = array('title' => ' |- ' . $_subVal['title'], 'contents' => $_subVal['departmentid']);
                }
            }
        }

        if (!count($_fieldContainer))
        {
            $_fieldContainer[] = array('title' => $_SWIFT->Language->Get('notapplicable'), 'contents' => '0');
        }

        $_criteriaPointer[self::CRITERIA_DEPARTMENT]['fieldcontents'] = $_fieldContainer;

        // ======= TICKET OWNER =======
        $_fieldContainer = array();
        $_fieldContainer[] = array('title' => $_SWIFT->Language->Get('wfunassigned'), 'contents' => '0');
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "staff ORDER BY fullname ASC");
        while ($_SWIFT->Database->NextRecord())
        {
            $_fieldContainer[] = array('title' => $_SWIFT->Database->Record['fullname'], 'contents' => $_SWIFT->Database->Record['staffid']);
        }

        if (!count($_fieldContainer))
        {
            $_fieldContainer[] = array('title' => $_SWIFT->Language->Get('notapplicable'), 'contents' => '0');
        }

        $_criteriaPointer[self::CRITERIA_OWNER]['fieldcontents'] = $_fieldContainer;

        // ======= EMAIL QUEUE =======
        $_fieldContainer = array();
        $_fieldContainer[] = array('title' => $_SWIFT->Language->Get('notset'), 'contents' => '0');
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "emailqueues ORDER BY email ASC");
        while ($_SWIFT->Database->NextRecord())
        {
            $_fieldContainer[] = array('title' => $_SWIFT->Database->Record['email'], 'contents' => $_SWIFT->Database->Record['emailqueueid']);
        }

        $_criteriaPointer[self::CRITERIA_EMAILQUEUE]['fieldcontents'] = $_fieldContainer;

        // ======= SLA PLAN =======
        $_fieldContainer = array();
        $_fieldContainer[] = array('title' => $_SWIFT->Language->Get('notset'), 'contents' => '0');
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "slaplans ORDER BY title ASC");
        while ($_SWIFT->Database->NextRecord())
        {
            $_fieldContainer[] = array('title' => $_SWIFT->Database->Record['title'], 'contents' => $_SWIFT->Database->Record['slaplanid']);
        }

        $_criteriaPointer[self::CRITERIA_SLAPLAN]['fieldcontents'] = $_fieldContainer;

        // ======= TEMPLATE GROUPS =======
        $_fieldContainer = array();
        $_fieldContainer[] = array('title' => $_SWIFT->Language->Get('notset'), 'contents' => '0');
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "templategroups ORDER BY title ASC");
        while ($_SWIFT->Database->NextRecord())
        {
            $_fieldContainer[] = array('title' => $_SWIFT->Database->Record['title'], 'contents' => $_SWIFT->Database->Record['tgroupid']);
        }


        $_criteriaPointer[self::CRITERIA_TEMPLATEGROUP]['fieldcontents'] = $_fieldContainer;

        // ======= FLAG TYPE =======
        $_SWIFT_TicketFlagObject = new SWIFT_TicketFlag();

        $_fieldContainer = array();
        $_fieldContainer[] = array('title' => $_SWIFT->Language->Get('notset'), 'contents' => '0');
        foreach ($_SWIFT_TicketFlagObject->GetFlagList() as $_key => $_val)
        {
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
     * Retrieve the Staff Group ID's linked with this Ticket Workflow
     *
     * @author Varun Shoor
     * @return mixed "_staffGroupIDList" (ARRAY) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetLinkedStaffGroupIDList()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return SWIFT_StaffGroupLink::RetrieveList(SWIFT_StaffGroupLink::TYPE_WORKFLOW, $this->GetTicketWorkflowID());
    }

    /**
     * Executes all workflow rules against a ticket object and returns a list of valid workflow ids
     *
     * @author Varun Shoor
     * @param SWIFT_Ticket $_SWIFT_TicketObject The SWIFT_Ticket Object Pointer
     * @return array
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function ExecuteAll(SWIFT_Ticket $_SWIFT_TicketObject)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_ticketWorkflowCache = $_SWIFT->Cache->Get('ticketworkflowcache');

        // If there are no workflows added then return without any result
        if (!_is_array($_ticketWorkflowCache)) {
            return array();
        }

        $_validTicketWorkflowIDList = $_ticketWorkflowObjectContainer = array();

        // Load a list of workflow objects and execute against the provided ticket object
        //$_ticketWorkflowObjectContainer = array();
        foreach ($_ticketWorkflowCache as $_ticketWorkflowID => $_ticketWorkflowContainer) {
            $_SWIFT_TicketWorkflowObject = new SWIFT_TicketWorkflow(new SWIFT_DataStore($_ticketWorkflowContainer));
            if (!$_SWIFT_TicketWorkflowObject instanceof SWIFT_TicketWorkflow || !$_SWIFT_TicketWorkflowObject->GetIsClassLoaded()) {
                throw new SWIFT_Exception('Unable to load ticket workflow: ' . $_ticketWorkflowID);
            }

            if ($_ticketWorkflowContainer['isenabled'] != '1') {
                continue;
            }

            $_ticketWorkflowObjectContainer[$_ticketWorkflowID] = $_SWIFT_TicketWorkflowObject;

            $_executionResult = $_SWIFT_TicketWorkflowObject->ExecuteWorkflow($_SWIFT_TicketObject);
            if ($_executionResult == true) {
                $_validTicketWorkflowIDList[] = $_ticketWorkflowID;
            }
        }

        return $_validTicketWorkflowIDList;
    }

    /**
     * Execute the given workflow
     *
     * @author Varun Shoor
     * @param SWIFT_Ticket $_SWIFT_TicketObject The SWIFT_Ticket Object Pointer
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ExecuteWorkflow(SWIFT_Ticket $_SWIFT_TicketObject) {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->LoadWorkflowProperties($_SWIFT_TicketObject);

        $_criteriaPointer = self::GetCriteriaPointer();

        if ($this->Execute($_criteriaPointer)) {
            return true;
        }

        return false;
    }

    /**
    * Retrieves the Criteria Value
    *
    * @author Varun Shoor
    * @param string $_criteriaName The Criteria Name Pointer
    * @return bool "true" on Success, "false" otherwise
    */
    public function GetCriteriaValue($_criteriaName) {
        if (isset($this->_workflowProperties[$_criteriaName])) {
            return $this->_workflowProperties[$_criteriaName];
        }

        return false;
    }


    /**
     * Load the Workflow Properties
     *
     * @author Varun Shoor
     *
     * @param SWIFT_Ticket $_SWIFT_TicketObject
     *
     * @return bool
     *
     * @throws SWIFT_Exception
     */
    protected function LoadWorkflowProperties(SWIFT_Ticket $_SWIFT_TicketObject)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if (!isset(self::$_workflowPropertiesCache[$_SWIFT_TicketObject->GetID()])) {
            self::$_workflowPropertiesCache[$_SWIFT_TicketObject->GetID()] = array();
        }

        $_propertiesCacheContainer = & self::$_workflowPropertiesCache[$_SWIFT_TicketObject->GetID()];

        $this->_workflowProperties[self::CRITERIA_TICKETSTATUS]   = $_SWIFT_TicketObject->GetProperty('ticketstatusid');
        $this->_workflowProperties[self::CRITERIA_TICKETPRIORITY] = $_SWIFT_TicketObject->GetProperty('priorityid');
        $this->_workflowProperties[self::CRITERIA_USERGROUP]      = false;
        if (isset($_propertiesCacheContainer[self::CRITERIA_USERGROUP])) {
            $this->_workflowProperties[self::CRITERIA_USERGROUP] = $_propertiesCacheContainer[self::CRITERIA_USERGROUP];
        } else {
            $_userGroupID = false;
            if ($_SWIFT_TicketObject->GetProperty('userid') != '0') {
                $_activeUserID = $_SWIFT_TicketObject->GetProperty('userid');
                try {
                    $_SWIFT_UserObject = new SWIFT_User(new SWIFT_DataID($_activeUserID));
                    if ($_SWIFT_UserObject instanceof SWIFT_User && $_SWIFT_UserObject->GetIsClassLoaded()) {
                        $_userGroupID = $_SWIFT_UserObject->GetProperty('usergroupid');
                    }
                } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
                }
            }

            $_propertiesCacheContainer[self::CRITERIA_USERGROUP] = $_userGroupID;
            $this->_workflowProperties[self::CRITERIA_USERGROUP] = $_userGroupID;
        }


        $this->_workflowProperties[self::CRITERIA_TICKETTYPE] = $_SWIFT_TicketObject->GetProperty('tickettypeid');
        $this->_workflowProperties[self::CRITERIA_BAYESIAN] = $_SWIFT_TicketObject->GetProperty('bayescategoryid');
        $this->_workflowProperties[self::CRITERIA_DEPARTMENT] = $_SWIFT_TicketObject->GetProperty('departmentid');
        $this->_workflowProperties[self::CRITERIA_OWNER] = $_SWIFT_TicketObject->GetProperty('ownerstaffid');
        $this->_workflowProperties[self::CRITERIA_EMAILQUEUE] = $_SWIFT_TicketObject->GetProperty('emailqueueid');
        $this->_workflowProperties[self::CRITERIA_FLAGTYPE] = $_SWIFT_TicketObject->GetProperty('flagtype');
        $this->_workflowProperties[self::CRITERIA_SLAPLAN] = $_SWIFT_TicketObject->GetProperty('slaplanid');
        $this->_workflowProperties[self::CRITERIA_TEMPLATEGROUP] = $_SWIFT_TicketObject->GetProperty('tgroupid');
        $this->_workflowProperties[self::CRITERIA_CREATOR] = $_SWIFT_TicketObject->GetProperty('creator');


        $this->_workflowProperties[self::CRITERIA_SUBJECT] = $_SWIFT_TicketObject->GetProperty('subject');
        $this->_workflowProperties[self::CRITERIA_FULLNAME] = $_SWIFT_TicketObject->GetProperty('fullname');
        $this->_workflowProperties[self::CRITERIA_EMAIL] = $_SWIFT_TicketObject->GetProperty('email');
        $this->_workflowProperties[self::CRITERIA_LASTREPLIER] = $_SWIFT_TicketObject->GetProperty('lastreplier');
        $this->_workflowProperties[self::CRITERIA_CHARSET] = $_SWIFT_TicketObject->GetProperty('charset');
        $this->_workflowProperties[self::CRITERIA_IPADDRESS] = $_SWIFT_TicketObject->GetProperty('ipaddress');


        $this->_workflowProperties[self::CRITERIA_DATE] = $_SWIFT_TicketObject->GetProperty('dateline');
        $this->_workflowProperties[self::CRITERIA_DATERANGE] = $_SWIFT_TicketObject->GetProperty('dateline');
        $this->_workflowProperties[self::CRITERIA_LASTACTIVITY] = $_SWIFT_TicketObject->GetProperty('lastactivity');
        $this->_workflowProperties[self::CRITERIA_LASTACTIVITYRANGE] = $_SWIFT_TicketObject->GetProperty('lastactivity');
        $this->_workflowProperties[self::CRITERIA_LASTSTAFFREPLY] = $_SWIFT_TicketObject->GetProperty('laststaffreplytime');
        $this->_workflowProperties[self::CRITERIA_LASTSTAFFREPLYRANGE] = $_SWIFT_TicketObject->GetProperty('laststaffreplytime');
        $this->_workflowProperties[self::CRITERIA_LASTUSERREPLY] = $_SWIFT_TicketObject->GetProperty('lastuserreplytime');
        $this->_workflowProperties[self::CRITERIA_LASTUSERREPLYRANGE] = $_SWIFT_TicketObject->GetProperty('lastuserreplytime');
        $this->_workflowProperties[self::CRITERIA_DUE] = $_SWIFT_TicketObject->GetProperty('duetime');
        $this->_workflowProperties[self::CRITERIA_DUERANGE] = $_SWIFT_TicketObject->GetProperty('duetime');
        $this->_workflowProperties[self::CRITERIA_RESOLUTIONDUE] = $_SWIFT_TicketObject->GetProperty('resolutionduedateline');
        $this->_workflowProperties[self::CRITERIA_RESOLUTIONDUERANGE] = $_SWIFT_TicketObject->GetProperty('resolutionduedateline');


        $this->_workflowProperties[self::CRITERIA_TIMEWORKED] = $_SWIFT_TicketObject->GetProperty('timeworked');
        $this->_workflowProperties[self::CRITERIA_TOTALREPLIES] = $_SWIFT_TicketObject->GetProperty('totalreplies');
        $this->_workflowProperties[self::CRITERIA_PENDINGFOLLOWUPS] = $_SWIFT_TicketObject->GetProperty('followupcount');


        $this->_workflowProperties[self::CRITERIA_ISEMAILED] = $_SWIFT_TicketObject->GetProperty('isemailed');
        $this->_workflowProperties[self::CRITERIA_ISEDITED] = $_SWIFT_TicketObject->GetProperty('edited');
        $this->_workflowProperties[self::CRITERIA_HASNOTES] = $_SWIFT_TicketObject->GetProperty('hasnotes');
        $this->_workflowProperties[self::CRITERIA_HASATTACHMENTS] = $_SWIFT_TicketObject->GetProperty('hasattachments');
        $this->_workflowProperties[self::CRITERIA_HASDRAFT] = $_SWIFT_TicketObject->GetProperty('hasdraft');
        $this->_workflowProperties[self::CRITERIA_HASBILLING] = $_SWIFT_TicketObject->GetProperty('hasbilling');
        $this->_workflowProperties[self::CRITERIA_ISPHONECALL] = $_SWIFT_TicketObject->GetProperty('isphonecall');
        $this->_workflowProperties[self::CRITERIA_ISOVERDUE] = IIF($_SWIFT_TicketObject->GetProperty('duetime')<DATENOW, 1, 0);
        $this->_workflowProperties[self::CRITERIA_ISRESOLUTIONOVERDUE] = IIF($_SWIFT_TicketObject->GetProperty('resolutionduedateline')<DATENOW, 1, 0);

        return true;
    }

    /**
     * Execute the workflow actions
     *
     * @author Varun Shoor
     * @param SWIFT_Ticket $_SWIFT_TicketObject The SWIFT_Ticket Object Pointer
     * @param SWIFT_Staff $_SWIFT_StaffObject The SWIFT_Staff Object
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function ExecuteActions(SWIFT_Ticket $_SWIFT_TicketObject, SWIFT_Staff $_SWIFT_StaffObject = null) {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_activeStaffID = 0;
        $_activeStaffName = '';

        if (isset($_SWIFT->Staff) && $_SWIFT->Staff instanceof SWIFT_Staff && $_SWIFT->Staff->GetIsClassLoaded()) {
            $_activeStaffID = $_SWIFT->Staff->GetStaffID();
            $_activeStaffName = $_SWIFT->Staff->GetProperty('fullname');
        } else if ($_SWIFT_StaffObject instanceof SWIFT_Staff && $_SWIFT_StaffObject->GetIsClassLoaded()) {
            $_activeStaffID = $_SWIFT_StaffObject->GetStaffID();
            $_activeStaffName = $_SWIFT_StaffObject->GetProperty('fullname');
        }

        $_ticketWorkflowActionContainer = array();
        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketworkflowactions WHERE ticketworkflowid = '" .
                (int) ($this->GetTicketWorkflowID()) . "'");
        while ($this->Database->NextRecord()) {
            $_ticketWorkflowActionContainer[$this->Database->Record['ticketworkflowactionid']] = $this->Database->Record;
        }

        foreach ($_ticketWorkflowActionContainer as $_ticketWorkflowActionID => $_ticketWorkflowAction) {
            switch ($_ticketWorkflowAction['name']) {
                case self::ACTION_DEPARTMENT:
                    $_SWIFT_TicketObject->SetDepartment($_ticketWorkflowAction['typeid']);
                    break;

                case self::ACTION_OWNER:
                    $_ownerStaffID = $_ticketWorkflowAction['typeid'];

                    // Active Staff?
                    if ($_ownerStaffID == -1) {
                        $_ownerStaffID = 0;

                        if ($_activeStaffID) {
                            $_ownerStaffID = $_activeStaffID;
                        }
                    }

                    $_SWIFT_TicketObject->SetOwner($_ownerStaffID);
                    break;

                case self::ACTION_STATUS:
                    $_SWIFT_TicketObject->SetStatus($_ticketWorkflowAction['typeid']);
                    break;

                case self::ACTION_PRIORITY:
                    $_SWIFT_TicketObject->SetPriority($_ticketWorkflowAction['typeid']);
                    break;

                case self::ACTION_TICKETTYPE:
                    $_SWIFT_TicketObject->SetType($_ticketWorkflowAction['typeid']);
                    break;

                case self::ACTION_FLAGTICKET:
                    $_SWIFT_TicketObject->SetFlag($_ticketWorkflowAction['typeid']);
                    break;

                case self::ACTION_TRASH:
                    $_SWIFT_TicketObject->Trash();
                    break;

                case self::ACTION_ADDTAGS:
                    {
                        $_tagContainer = json_decode($_ticketWorkflowAction['typedata']);
                        if (!_is_array($_tagContainer)) {
                            continue 2;
                        }

                        SWIFT_Tag::AddTags(SWIFT_TagLink::TYPE_TICKET, $_SWIFT_TicketObject->GetTicketID(),
                                $_tagContainer, $_activeStaffID);
                    }
                    break;

                case self::ACTION_REMOVETAGS:
                    {
                        $_tagContainer = json_decode($_ticketWorkflowAction['typedata']);
                        if (!_is_array($_tagContainer)) {
                            continue 2;
                        }

                        SWIFT_Tag::RemoveTags(SWIFT_TagLink::TYPE_TICKET, array($_SWIFT_TicketObject->GetTicketID()),
                                $_tagContainer, $_activeStaffID);
                    }
                    break;

                case self::ACTION_ADDNOTE:
                    {
                        SWIFT_TicketNote::Create($_SWIFT_TicketObject, 0, $_activeStaffID,
                            $_activeStaffName, $_ticketWorkflowAction['typedata'], 1);

                        SWIFT_TicketAuditLog::AddToLog($_SWIFT_TicketObject, null, SWIFT_TicketAuditLog::ACTION_NEWNOTE,
                            $_SWIFT->Language->Get('al_ticketnote'),
                            SWIFT_TicketAuditLog::VALUE_NONE, 0, '', 0, '', ['al_ticketnote']);
                    }
                    break;

                case self::ACTION_BAYESIAN:
                    {
                        $_bayesianCategoryCache = $this->Cache->Get('bayesiancategorycache');
                        $_bayesCategoryID = (int) ($_ticketWorkflowAction['typeid']);
                        if (!isset($_bayesianCategoryCache[$_bayesCategoryID])) {
                            continue 2;
                        }

                        $_SWIFT_BayesianObject = new SWIFT_Bayesian();
                        if (!$_SWIFT_BayesianObject instanceof SWIFT_Bayesian || !$_SWIFT_BayesianObject->GetIsClassLoaded()) {
                            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
                        }

                        $_ticketPostContainer = $_SWIFT_TicketObject->GetTicketPosts(0, false, 'ASC', SWIFT_Ticket::CREATOR_CLIENT);
                        if (!_is_array($_ticketPostContainer)) {
                            continue 2;
                        }

                        $_finalTicketPostText = $_SWIFT_TicketObject->GetProperty('subject');
                        foreach ($_ticketPostContainer as $_ticketPostID => $_SWIFT_TicketPostObject) {
                            $_finalTicketPostText .= $_SWIFT_TicketPostObject->GetProperty('contents') . SWIFT_CRLF;
                        }

                        $_SWIFT_BayesianObject->Train($_SWIFT_TicketObject->GetTicketID(), $_bayesCategoryID, $_finalTicketPostText);
                    }
                    break;


                case self::ACTION_SLAPLAN:
                    {
                        $_SWIFT_SLAObject = false;
                        try {
                            $_SWIFT_SLAObject = new SWIFT_SLA(new SWIFT_DataID($_ticketWorkflowAction['typeid']));
                        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {

                            return false;
                        }

                        if ($_SWIFT_SLAObject instanceof SWIFT_SLA && $_SWIFT_SLAObject->GetIsClassLoaded()) {
                            $_SWIFT_TicketObject->SetSLA($_SWIFT_SLAObject);
                        }
                    }
                    break;

                default:
                    break;
            }
        }

        return true;
    }

    /**
     * Update the Display Order List
     *
     * @author Varun Shoor
     * @param array $_ticketWorkflowIDSortList The Ticket Workflow ID Sort List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function UpdateDisplayOrderList($_ticketWorkflowIDSortList) {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_ticketWorkflowIDSortList)) {
            return false;
        }

        foreach ($_ticketWorkflowIDSortList as $_ticketWorkflowID => $_displayOrder) {
            $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'ticketworkflows', array('sortorder' => (int) ($_displayOrder)), 'UPDATE',
                    "ticketworkflowid = '" . $_ticketWorkflowID . "'");
        }

        self::RebuildCache();

        return true;
    }

    /**
     * @author Abdulrahman Suleiman <abdulrahman.suleiman@crossover.com>
     *
     * @param SWIFT_Ticket $_SWIFT_TicketObject
     * @param int $_ticketWorkflowID
     * @param bool $isClient
     *
     * @return bool
     *
     * @throws SWIFT_Exception
     */
    public static function ProcessWorkflow(SWIFT_Ticket $_SWIFT_TicketObject, $_ticketWorkflowID, $_requireChanges,  $isClient = false){

        // Attempt to load the workflow object
        $_SWIFT_TicketWorkflowObject = new SWIFT_TicketWorkflow(new SWIFT_DataID($_ticketWorkflowID));

        $_ticketLinkedTableContainer = SWIFT_TicketLinkedTable::RetrieveOnTicket($_SWIFT_TicketObject);

        // Make sure this workflow is available to the current ticket
        $_proceedWithWorkflow = false;
        if (isset($_ticketLinkedTableContainer[SWIFT_TicketLinkedTable::LINKTYPE_WORKFLOW])) {
            foreach ($_ticketLinkedTableContainer[SWIFT_TicketLinkedTable::LINKTYPE_WORKFLOW] as $_ticketLinkedTableID => $_ticketLinkedTableValue) {
                if ($_ticketLinkedTableValue['linktypeid'] == $_ticketWorkflowID) {
                    $_proceedWithWorkflow = true;
                    break;
                }
            }
        }

        if (!$_proceedWithWorkflow ||
            ($isClient && $_SWIFT_TicketWorkflowObject->GetProperty('uservisibility') != '1')) {
            return false;
        }

        $_SWIFT_TicketWorkflowObject->ExecuteActions($_SWIFT_TicketObject);

        $_SWIFT_TicketObject->ProcessUpdatePool();
        SWIFT_TicketManager::RebuildCache();
        $_SWIFT_TicketObject->ExecuteSLA(false, true, false);

        // Execute notifications
        $_workflowNotificationContainer = SWIFT_TicketWorkflowNotification::RetrieveOnWorkflow($_ticketWorkflowID);
        foreach ($_workflowNotificationContainer as $_ticketWorkflowNotificationID => $_ticketWorkflowNotificationContainer) {
            $_notificationSubject = $_ticketWorkflowNotificationContainer['subject'];
            $_notificationContents = $_ticketWorkflowNotificationContainer['notificationcontents'];

            $_notificationType = false;
            switch ($_ticketWorkflowNotificationContainer['notificationtype']) {
                case SWIFT_TicketWorkflowNotification::TYPE_USER:
                    $_notificationType = SWIFT_TicketNotification::TYPE_USER;
                    break;

                case SWIFT_TicketWorkflowNotification::TYPE_USERORGANIZATION:
                    $_notificationType = SWIFT_TicketNotification::TYPE_USERORGANIZATION;
                    break;

                case SWIFT_TicketWorkflowNotification::TYPE_STAFF:
                    $_notificationType = SWIFT_TicketNotification::TYPE_STAFF;
                    break;

                case SWIFT_TicketWorkflowNotification::TYPE_TEAM:
                    $_notificationType = SWIFT_TicketNotification::TYPE_TEAM;
                    break;

                case SWIFT_TicketWorkflowNotification::TYPE_DEPARTMENT:
                    $_notificationType = SWIFT_TicketNotification::TYPE_DEPARTMENT;
                    break;

                default:
                    $_notificationType = SWIFT_TicketNotification::TYPE_CUSTOM;
                    break;
            }

            $_SWIFT_TicketObject->Notification->Dispatch($_notificationType, array(), $_notificationSubject, $_notificationContents, '', '', $_requireChanges);
        }

        return true;
    }
}
?>
