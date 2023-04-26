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

use SWIFT;
use SWIFT_Data;
use SWIFT_DataID;
use SWIFT_DataStore;
use SWIFT_Exception;
use SWIFT_Model;
use Tickets\Library\Ticket\SWIFT_Ticket_Exception;

/**
 * The Notification Criteria Model
 *
 * @author Varun Shoor
 */
class SWIFT_NotificationCriteria extends SWIFT_Model
{
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
            throw new SWIFT_Exception('Failed to load Notification Criteria Object');
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

        $this->Database->AutoExecute(TABLE_PREFIX . 'notificationcriteria', $this->GetUpdatePool(), 'UPDATE', "notificationcriteriaid = '" . (int)($this->GetNotificationCriteriaID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Notification Criteria ID
     *
     * @author Varun Shoor
     * @return mixed "notificationcriteriaid" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetNotificationCriteriaID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['notificationcriteriaid'];
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
            $_dataStore = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "notificationcriteria WHERE notificationcriteriaid = '" . (int)($_SWIFT_DataObject->GetDataID()) . "'");
            if (isset($_dataStore['notificationcriteriaid']) && !empty($_dataStore['notificationcriteriaid'])) {
                $this->_dataStore = $_dataStore;

                return true;
            }

            // Is it a Store?
        } else if ($_SWIFT_DataObject instanceof SWIFT_DataStore && $_SWIFT_DataObject->GetIsClassLoaded()) {
            $this->_dataStore = $_SWIFT_DataObject->GetDataStore();

            if (!isset($this->_dataStore['notificationcriteriaid']) || empty($this->_dataStore['notificationcriteriaid'])) {
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
     * Create a new Notification Criteria
     *
     * @author Varun Shoor
     * @param int $_notificationRuleID The Notification Rule ID
     * @param string $_ruleName The Rule Name
     * @param string $_ruleOP The Rule OP
     * @param string $_ruleMatch The Rule Match Value
     * @param int $_ruleMatchType The Rule Match Type (AND/OR)
     * @return int The Notification Criteria ID
     * @throws SWIFT_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function Create($_notificationRuleID, $_ruleName, $_ruleOP, $_ruleMatch, $_ruleMatchType)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_notificationRuleID) || empty($_ruleName) || empty($_ruleOP)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'notificationcriteria', array('notificationruleid' => $_notificationRuleID, 'name' => $_ruleName, 'ruleop' => $_ruleOP,
            'rulematch' => $_ruleMatch, 'rulematchtype' => $_ruleMatchType), 'INSERT');
        $_notificationCriteriaID = $_SWIFT->Database->Insert_ID();

        if (!$_notificationCriteriaID) {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        return $_notificationCriteriaID;
    }

    /**
     * Delete the Notification Criteria record
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

        self::DeleteList(array($this->GetNotificationCriteriaID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of Notification Criterias
     *
     * @author Varun Shoor
     * @param array $_notificationCriteriaIDList
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_notificationCriteriaIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_notificationCriteriaIDList)) {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "notificationcriteria WHERE notificationcriteriaid IN (" . BuildIN($_notificationCriteriaIDList) . ")");

        return true;
    }

    /**
     * Delete the Criteria on a list of notification rules
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

        $_notificationCriteriaIDList = array();
        $_SWIFT->Database->Query("SELECT notificationcriteriaid FROM " . TABLE_PREFIX . "notificationcriteria WHERE notificationruleid IN (" . BuildIN($_notificationRuleIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_notificationCriteriaIDList[] = $_SWIFT->Database->Record['notificationcriteriaid'];
        }

        if (!count($_notificationCriteriaIDList)) {
            return false;
        }

        self::DeleteList($_notificationCriteriaIDList);

        return true;
    }
}

?>
