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

namespace Base\Models\Notification;

use SWIFT;
use SWIFT_Data;
use SWIFT_DataID;
use SWIFT_DataStore;
use SWIFT_Exception;
use SWIFT_Model;
use Tickets\Library\Ticket\SWIFT_Ticket_Exception;

/**
 * The Notification Action Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_NotificationAction extends SWIFT_Model
{
    protected $_dataStore = array();

    // Core Constants
    const ACTION_EMAILSTAFF = 1; // Active Staff
    const ACTION_EMAILSTAFFGROUP = 2; // Active Staff's Group
    const ACTION_EMAILDEPARTMENT = 3; // Active Tickets Department
    const ACTION_EMAILCUSTOM = 4; // Custom Email
    const ACTION_EMAILSTAFFCUSTOM = 5; // Staff Email

    const ACTION_POOLSTAFF = 6;
    const ACTION_POOLSTAFFGROUP = 7;
    const ACTION_POOLDEPARTMENT = 8;
    const ACTION_POOLCUSTOM = 9;
    const ACTION_EMAILUSER = 10; // Active User

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
        parent::__construct();

        if (!$_SWIFT_DataObject instanceof SWIFT_Data || !$_SWIFT_DataObject->GetIsClassLoaded() || !$this->LoadData($_SWIFT_DataObject)) {
            throw new SWIFT_Exception('Failed to load Notification Action Object');
        }
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

        $this->Database->AutoExecute(TABLE_PREFIX . 'notificationactions', $this->GetUpdatePool(), 'UPDATE', "notificationactionid = '" . (int)($this->GetNotificationActionID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Notification Action ID
     *
     * @author Varun Shoor
     * @return mixed "notificationactionid" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetNotificationActionID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['notificationactionid'];
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
            $_dataStore = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "notificationactions WHERE notificationactionid = '" . (int)($_SWIFT_DataObject->GetDataID()) . "'");
            if (isset($_dataStore['notificationactionid']) && !empty($_dataStore['notificationactionid'])) {
                $this->_dataStore = $_dataStore;

                return true;
            }

            // Is it a Store?
        } else if ($_SWIFT_DataObject instanceof SWIFT_DataStore && $_SWIFT_DataObject->GetIsClassLoaded()) {
            $this->_dataStore = $_SWIFT_DataObject->GetDataStore();

            if (!isset($this->_dataStore['notificationactionid']) || empty($this->_dataStore['notificationactionid'])) {
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
     * Check to see if its a valid action type
     *
     * @author Varun Shoor
     * @param mixed $_actionType The Action Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidActionType($_actionType)
    {
        if ($_actionType == self::ACTION_EMAILCUSTOM || $_actionType == self::ACTION_EMAILDEPARTMENT || $_actionType == self::ACTION_EMAILSTAFF || $_actionType == self::ACTION_EMAILSTAFFGROUP ||
            $_actionType == self::ACTION_POOLCUSTOM || $_actionType == self::ACTION_POOLDEPARTMENT || $_actionType == self::ACTION_POOLSTAFF || $_actionType == self::ACTION_POOLSTAFFGROUP ||
            $_actionType == self::ACTION_EMAILSTAFFCUSTOM || $_actionType == self::ACTION_EMAILUSER) {
            return true;
        }

        return false;
    }

    /**
     * Create a new Notification Action
     *
     * @author Varun Shoor
     * @param int $_notificationRuleID The Notification Rule ID
     * @param mixed $_actionType The Action Type
     * @param string $_contents The Notification Contents
     * @return int The Notification Action ID
     * @throws SWIFT_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function Create($_notificationRuleID, $_actionType, $_contents)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_notificationRuleID) || !self::IsValidActionType($_actionType)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'notificationactions', array('notificationruleid' => $_notificationRuleID, 'actiontype' => (int)($_actionType),
            'contents' => $_contents), 'INSERT');
        $_notificationActionID = $_SWIFT->Database->Insert_ID();

        if (!$_notificationActionID) {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        return $_notificationActionID;
    }

    /**
     * Delete the Notification Action record
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

        self::DeleteList(array($this->GetNotificationActionID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of Notification Actions
     *
     * @author Varun Shoor
     * @param array $_notificationActionIDList
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_notificationActionIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_notificationActionIDList)) {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "notificationactions WHERE notificationactionid IN (" . BuildIN($_notificationActionIDList) . ")");

        return true;
    }

    /**
     * Delete the Notification Actions on Notification Rules
     *
     * @author Varun Shoor
     * @param array $_notificationRuleIDList The Notification Rule ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteOnNotificationRule($_notificationRuleIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_notificationRuleIDList)) {
            return false;
        }

        $_notificationActionIDList = array();

        $_SWIFT->Database->Query("SELECT notificationactionid FROM " . TABLE_PREFIX . "notificationactions WHERE notificationruleid IN (" . BuildIN($_notificationRuleIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_notificationActionIDList[] = (int)($_SWIFT->Database->Record['notificationactionid']);
        }

        if (!count($_notificationActionIDList)) {
            return false;
        }

        self::DeleteList($_notificationActionIDList);

        return true;
    }

    /**
     * Retrieve the Actions on Notification Rule
     *
     * @author Varun Shoor
     * @param int $_notificationRuleID The Notification Rule ID
     * @return array The Actions Container
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveOnNotificationRule($_notificationRuleID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_notificationRuleID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_actionsContainer = array();

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "notificationactions WHERE notificationruleid = '" . $_notificationRuleID . "'");
        while ($_SWIFT->Database->NextRecord()) {
            $_actionsContainer[$_SWIFT->Database->Record['actiontype']][] = $_SWIFT->Database->Record['contents'];
        }

        return $_actionsContainer;
    }
}

?>
