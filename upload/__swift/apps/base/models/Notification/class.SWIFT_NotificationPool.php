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
 * The Notification Pool Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_NotificationPool extends SWIFT_Model
{
    const TABLE_NAME = 'notificationpool';
    const PRIMARY_KEY = 'notificationpoolid';

    const TABLE_STRUCTURE = "notificationpoolid I PRIMARY AUTO NOTNULL,
                                notificationruleid I DEFAULT '0' NOTNULL,
                                staffid I DEFAULT '0' NOTNULL,
                                contents X NOTNULL,
                                dateline I DEFAULT '0' NOTNULL";

    const INDEX_1 = 'staffid, dateline';
    const INDEX_2 = 'dateline';


    protected $_dataStore = array();

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
            throw new SWIFT_Exception('Failed to load Notification Pool Object');
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

        $this->Database->AutoExecute(TABLE_PREFIX . 'notificationpool', $this->GetUpdatePool(), 'UPDATE', "notificationpoolid = '" . (int)($this->GetNotificationPoolID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Notification Pool ID
     *
     * @author Varun Shoor
     * @return mixed "notificationpoolid" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetNotificationPoolID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['notificationpoolid'];
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
            $_dataStore = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "notificationpool WHERE notificationpoolid = '" . (int)($_SWIFT_DataObject->GetDataID()) . "'");
            if (isset($_dataStore['notificationpoolid']) && !empty($_dataStore['notificationpoolid'])) {
                $this->_dataStore = $_dataStore;

                return true;
            }

            // Is it a Store?
        } else if ($_SWIFT_DataObject instanceof SWIFT_DataStore && $_SWIFT_DataObject->GetIsClassLoaded()) {
            $this->_dataStore = $_SWIFT_DataObject->GetDataStore();

            if (!isset($this->_dataStore['notificationpoolid']) || empty($this->_dataStore['notificationpoolid'])) {
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
     * Create a new Notification Pool
     *
     * @author Varun Shoor
     * @param int $_notificationRuleID
     * @param int $_staffID
     * @param string $_contents
     * @return int The Notification Pool ID
     * @throws SWIFT_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function Create($_notificationRuleID, $_staffID, $_contents)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_notificationRuleID) || empty($_staffID) || empty($_contents)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'notificationpool', array('notificationruleid' => $_notificationRuleID, 'staffid' => $_staffID, 'contents' => $_contents), 'INSERT');
        $_notificationPoolID = $_SWIFT->Database->Insert_ID();

        if (!$_notificationPoolID) {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        return $_notificationPoolID;
    }

    /**
     * Delete the Notification Pool record
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

        self::DeleteList(array($this->GetNotificationPoolID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of Notification Pool Records
     *
     * @author Varun Shoor
     * @param array $_notificationPoolIDList The Notification Pool ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_notificationPoolIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_notificationPoolIDList)) {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "notificationpool WHERE notificationpoolid IN (" . BuildIN($_notificationPoolIDList) . ")");

        return true;
    }

    /**
     * Delete the Notification Pool based on Notification Rule ID List
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

        $_notificationPoolIDList = array();
        $_SWIFT->Database->Query("SELECT notificationpoolid FROM " . TABLE_PREFIX . "notificationpool WHERE notificationruleid IN (" . BuildIN($_notificationRuleIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_notificationPoolIDList[] = $_SWIFT->Database->Record['notificationpoolid'];
        }

        if (!count($_notificationPoolIDList)) {
            return false;
        }

        self::DeleteList($_notificationPoolIDList);

        return true;
    }
}

?>
