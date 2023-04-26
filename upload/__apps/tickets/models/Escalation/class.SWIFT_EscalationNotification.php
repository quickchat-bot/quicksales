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

namespace Tickets\Models\Escalation;

use SWIFT;
use SWIFT_Exception;
use SWIFT_Model;

/**
 * The Escalation Notification Handling Class
 *
 * @author Varun Shoor
 */
class SWIFT_EscalationNotification extends SWIFT_Model
{
    const TABLE_NAME        =    'escalationnotifications';
    const PRIMARY_KEY        =    'escalationnotificationid';

    const TABLE_STRUCTURE    =    "escalationnotificationid I PRIMARY AUTO NOTNULL,
                                dateline I DEFAULT '0' NOTNULL,
                                escalationruleid I DEFAULT '0' NOTNULL,
                                notificationtype C(100) DEFAULT '' NOTNULL,
                                subject C(255) DEFAULT '' NOTNULL,
                                notificationcontents X NOTNULL";

    const INDEX_1            =    'escalationruleid';


    protected $_dataStore = array();

    static protected $_cache = array();

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
     * @param int $_escalationNotificationID The Escalation Notification ID
     * @throws SWIFT_Escalation_Exception If the Record could not be loaded
     */
    public function __construct($_escalationNotificationID)
    {
        parent::__construct();

        if (!$this->LoadData($_escalationNotificationID)) {
            throw new SWIFT_Escalation_Exception('Failed to load Escalation Notification ID: ' . $_escalationNotificationID);
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
        if (!$this->GetIsClassLoaded()) {
            return false;
        } else if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'escalationnotifications', $this->GetUpdatePool(), 'UPDATE', "escalationnotificationid = '" .
                (int) ($this->GetEscalationNotificationID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Escalation Notification ID
     *
     * @author Varun Shoor
     * @return mixed "escalationnotificationid" on Success, "false" otherwise
     * @throws SWIFT_Escalation_Exception If the Class is not Loaded
     */
    public function GetEscalationNotificationID()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Escalation_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['escalationnotificationid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param int $_escalationNotificationID The Escalation Notification ID
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_escalationNotificationID)
    {
        $_dataStore = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "escalationnotifications WHERE escalationnotificationid = '" .
                $_escalationNotificationID . "'");
        if (isset($_dataStore['escalationnotificationid']) && !empty($_dataStore['escalationnotificationid']))
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
     * @throws SWIFT_Escalation_Exception If the Class is not Loaded
     */
    public function GetProperty($_key)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Escalation_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_Escalation_Exception(SWIFT_INVALIDDATA);
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
     * Retrieve a list of Notification Types
     *
     * @author Varun Shoor
     * @return array List of Ticket Notification Types
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
     * Retrieve the Notifications Based on Escalation Rule ID
     *
     * @author Varun Shoor
     * @param int $_escalationRuleID The Escalation Rule ID
     * @return array
     */
    public static function RetrieveOnEscalationRule($_escalationRuleID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (isset(self::$_cache[$_escalationRuleID]))
        {
            return self::$_cache[$_escalationRuleID];
        }

        $_escalationNotificationContainer = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "escalationnotifications WHERE escalationruleid = '" .
                $_escalationRuleID . "' ORDER BY escalationnotificationid");
        while ($_SWIFT->Database->NextRecord())
        {
            $_escalationNotificationContainer[$_SWIFT->Database->Record['escalationnotificationid']] = $_SWIFT->Database->Record;
        }

        self::$_cache[$_escalationRuleID] = $_escalationNotificationContainer;

        return $_escalationNotificationContainer;
    }

    /**
     * Create a new Escalation Notification
     *
     * @author Varun Shoor
     * @param mixed $_notificationType The Notification Type
     * @param int $_escalationRuleID The Escalation Rule ID
     * @param string $_subject The Notification Subject
     * @param string $_contents The Notification Contents
     * @return bool "_escalationNotificationID" (INT) on Success, "false" otherwise
     * @throws SWIFT_Escalation_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function Create($_notificationType, $_escalationRuleID, $_subject, $_contents)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_escalationRuleID) || empty($_subject) || empty($_contents) || !self::IsValidType($_notificationType))
        {
            throw new SWIFT_Escalation_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'escalationnotifications', array('notificationtype' => $_notificationType,
            'escalationruleid' => $_escalationRuleID, 'subject' => $_subject, 'notificationcontents' => $_contents,
            'dateline' => DATENOW), 'INSERT');
        $_escalationNotificationID = $_SWIFT->Database->Insert_ID();

        if (!$_escalationNotificationID)
        {
            throw new SWIFT_Escalation_Exception(SWIFT_CREATEFAILED);
        }

        return $_escalationNotificationID;
    }

    /**
     * Delete Escalation Notification record
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

        self::DeleteList(array($this->GetEscalationNotificationID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of Escalation Notifications
     *
     * @author Varun Shoor
     * @param array $_escalationNotificationIDList The Escalation Notification ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_escalationNotificationIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_escalationNotificationIDList))
        {
            return false;
        }

        $_finalEscalationNotificationIDList = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "escalationnotifications WHERE escalationnotificationid IN (" .
                BuildIN($_escalationNotificationIDList) . ")");
        while ($_SWIFT->Database->NextRecord())
        {
            $_finalEscalationNotificationIDList[] = $_SWIFT->Database->Record['escalationnotificationid'];
        }

        if (!count($_finalEscalationNotificationIDList))
        {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "escalationnotifications WHERE escalationnotificationid IN (" .
                BuildIN($_finalEscalationNotificationIDList) . ")");

        return true;
    }

    /**
     * Delete the Notifications based on Escalation Rule ID List
     *
     * @author Varun Shoor
     * @param array $_escalationRuleIDList The Escalation Rule ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteOnEscalationRule($_escalationRuleIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_escalationRuleIDList))
        {
            return false;
        }

        $_finalEscalationNotificationIDList = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "escalationnotifications WHERE escalationruleid IN (" .
                BuildIN($_escalationRuleIDList) . ")");
        while ($_SWIFT->Database->NextRecord())
        {
            $_finalEscalationNotificationIDList[] = $_SWIFT->Database->Record['escalationnotificationid'];
        }

        if (!count($_finalEscalationNotificationIDList))
        {
            return false;
        }

        self::DeleteList($_finalEscalationNotificationIDList);

        return true;
    }
}
?>
