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
use SWIFT_Exception;
use SWIFT_Model;

/**
 * The Ticket Workflow Notification Handling Class
 *
 * @author Varun Shoor
 */
class SWIFT_TicketWorkflowNotification extends SWIFT_Model
{
    const TABLE_NAME        =    'ticketworkflownotifications';
    const PRIMARY_KEY        =    'ticketworkflownotificationid';

    const TABLE_STRUCTURE    =    "ticketworkflownotificationid I PRIMARY AUTO NOTNULL,
                                dateline I DEFAULT '0' NOTNULL,
                                ticketworkflowid I DEFAULT '0' NOTNULL,
                                notificationtype C(100) DEFAULT '' NOTNULL,
                                subject C(255) DEFAULT '' NOTNULL,
                                notificationcontents X NOTNULL";

    const INDEX_1            =    'ticketworkflowid';


    protected $_dataStore = array();

    // Core Constants
    const TYPE_USER = 'user';
    const TYPE_USERORGANIZATION = 'userorganization';
    const TYPE_STAFF = 'staff';
    const TYPE_TEAM = 'team';
    const TYPE_DEPARTMENT = 'department';

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param int $_ticketWorkflowNotificationID The Ticket Workflow Notification ID
     * @throws SWIFT_Workflow_Exception If the Record could not be loaded
     */
    public function __construct($_ticketWorkflowNotificationID)
    {
        parent::__construct();

        if (!$this->LoadData($_ticketWorkflowNotificationID)) {
            throw new SWIFT_Workflow_Exception('Failed to load Ticket Workflow Notification ID: ' . $_ticketWorkflowNotificationID);
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
     * @throws SWIFT_Workflow_Exception If the Class is not Loaded
     */
    public function ProcessUpdatePool()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        } else if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'ticketworkflownotifications', $this->GetUpdatePool(), 'UPDATE',
                "ticketworkflownotificationid = '" . (int) ($this->GetTicketWorkflowNotificationID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Ticket Workflow Notification ID
     *
     * @author Varun Shoor
     * @return mixed "ticketworkflownotificationid" on Success, "false" otherwise
     * @throws SWIFT_Workflow_Exception If the Class is not Loaded
     */
    public function GetTicketWorkflowNotificationID()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Workflow_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['ticketworkflownotificationid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param int $_ticketWorkflowNotificationID The Ticket Workflow Notification ID
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_ticketWorkflowNotificationID)
    {
        $_dataStore = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "ticketworkflownotifications WHERE
            ticketworkflownotificationid = '" . $_ticketWorkflowNotificationID . "'");
        if (isset($_dataStore['ticketworkflownotificationid']) && !empty($_dataStore['ticketworkflownotificationid']))
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
     * @throws SWIFT_Workflow_Exception If the Class is not Loaded
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
     * Check to see if its a Valid Notification Type
     *
     * @author Varun Shoor
     * @param mixed $_notificationType The Notification Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidType($_notificationType)
    {
        if ($_notificationType == self::TYPE_USER || $_notificationType == self::TYPE_USERORGANIZATION || $_notificationType == self::TYPE_STAFF ||
                $_notificationType == self::TYPE_TEAM || $_notificationType == self::TYPE_DEPARTMENT)
        {
            return true;
        }

        return false;
    }

    /**
     * Retrieve a list of Workflow Notification Types
     *
     * @author Varun Shoor
     * @return array List of Ticket Workflow Notification Types
     */
    public static function GetTypeList()
    {
        $_SWIFT = SWIFT::GetInstance();

        return array(self::TYPE_USER => $_SWIFT->Language->Get('notificationuser'),
            self::TYPE_USERORGANIZATION => $_SWIFT->Language->Get('notificationuserorganization'),
            self::TYPE_STAFF => $_SWIFT->Language->Get('notificationstaff'), self::TYPE_TEAM => $_SWIFT->Language->Get('notificationteam'),
            self::TYPE_DEPARTMENT => $_SWIFT->Language->Get('notificationdepartment'));
    }

    /**
     * Retrieve the Notifications Based on Ticket Workflow Rule ID
     *
     * @author Varun Shoor
     * @param int $_ticketWorkflowID The Ticket Workflow Rule ID
     * @return array
     * @throws SWIFT_Exception
     */
    public static function RetrieveOnWorkflow($_ticketWorkflowID)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_ticketWorkflowNotificationContainer = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketworkflownotifications WHERE ticketworkflowid = '" .
                $_ticketWorkflowID . "' ORDER BY ticketworkflownotificationid");
        while ($_SWIFT->Database->NextRecord())
        {
            $_ticketWorkflowNotificationContainer[$_SWIFT->Database->Record['ticketworkflownotificationid']] = $_SWIFT->Database->Record;
        }

        return $_ticketWorkflowNotificationContainer;
    }

    /**
     * Create a new Ticket Workflow Notification
     *
     * @author Varun Shoor
     * @param mixed $_notificationType The Notification Type
     * @param int $_ticketWorkflowID The Ticket Workflow Rule ID
     * @param string $_subject The Notification Subject
     * @param string $_contents The Notification Contents
     * @return bool "_ticketWorkflowNotificationID" (INT) on Success, "false" otherwise
     * @throws SWIFT_Workflow_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function Create($_notificationType, $_ticketWorkflowID, $_subject, $_contents)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_ticketWorkflowID) || empty($_contents) || !self::IsValidType($_notificationType))
        {
            throw new SWIFT_Workflow_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'ticketworkflownotifications', array('notificationtype' => $_notificationType,
            'ticketworkflowid' => $_ticketWorkflowID, 'subject' => $_subject, 'notificationcontents' => $_contents,
            'dateline' => DATENOW), 'INSERT');
        $_ticketWorkflowNotificationID = $_SWIFT->Database->Insert_ID();

        if (!$_ticketWorkflowNotificationID)
        {
            throw new SWIFT_Workflow_Exception(SWIFT_CREATEFAILED);
        }

        return $_ticketWorkflowNotificationID;
    }

    /**
     * Delete Ticket Workflow Notification record
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

        self::DeleteList(array($this->GetTicketWorkflowNotificationID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of Ticket Workflow Notifications
     *
     * @author Varun Shoor
     * @param array $_ticketWorkflowNotificationIDList The Ticket Workflow Notification ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_ticketWorkflowNotificationIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_ticketWorkflowNotificationIDList))
        {
            return false;
        }

        $_finalTicketWorkflowNotificationIDList = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketworkflownotifications WHERE ticketworkflownotificationid IN (" .
                BuildIN($_ticketWorkflowNotificationIDList) . ")");
        while ($_SWIFT->Database->NextRecord())
        {
            $_finalTicketWorkflowNotificationIDList[] = $_SWIFT->Database->Record['ticketworkflownotificationid'];
        }

        if (!count($_finalTicketWorkflowNotificationIDList))
        {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "ticketworkflownotifications WHERE ticketworkflownotificationid IN (" .
                BuildIN($_finalTicketWorkflowNotificationIDList) . ")");

        return true;
    }

    /**
     * Delete the Notifications based on Ticket Workflow Rule ID List
     *
     * @author Varun Shoor
     * @param array $_ticketWorkflowIDList The Ticket Workflow Rule ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteOnWorkflow($_ticketWorkflowIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_ticketWorkflowIDList))
        {
            return false;
        }

        $_finalTicketWorkflowNotificationIDList = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketworkflownotifications WHERE ticketworkflowid IN (" .
                BuildIN($_ticketWorkflowIDList) . ")");
        while ($_SWIFT->Database->NextRecord())
        {
            $_finalTicketWorkflowNotificationIDList[] = $_SWIFT->Database->Record['ticketworkflownotificationid'];
        }

        if (!count($_finalTicketWorkflowNotificationIDList))
        {
            return false;
        }

        self::DeleteList($_finalTicketWorkflowNotificationIDList);

        return true;
    }
}
?>
