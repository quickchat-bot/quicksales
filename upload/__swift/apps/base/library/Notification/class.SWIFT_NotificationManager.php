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

namespace Base\Library\Notification;

use Base\Models\Notification\SWIFT_NotificationAction;
use Base\Models\Notification\SWIFT_NotificationRule;
use Base\Models\User\SWIFT_User;
use SWIFT_Base;
use SWIFT_DataStore;
use SWIFT_Exception;
use SWIFT_Library;
use Base\Library\User\SWIFT_UserNotification;
use Tickets\Library\Notification\SWIFT_TicketNotification;
use Tickets\Models\Ticket\SWIFT_Ticket;

/**
 * The Notification Manager
 *
 * Handles the execution of notification rules
 *
 * @author Varun Shoor
 */
class SWIFT_NotificationManager extends SWIFT_Library
{
    protected $_ruleType = false;
    protected $_changeContainer = array();
    protected $_event = array();
    protected $_isPrivate = false;

    /**
     * Core Classes
     */
    protected $Ticket = false;
    protected $User = false;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param SWIFT_Base $_SWIFT_BaseObject
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct($_SWIFT_BaseObject)
    {
        parent::__construct();

        if ($_SWIFT_BaseObject instanceof SWIFT_Ticket && $_SWIFT_BaseObject->GetIsClassLoaded()) {
            $this->LoadTicket($_SWIFT_BaseObject);
        } elseif ($_SWIFT_BaseObject instanceof SWIFT_User && $_SWIFT_BaseObject->GetIsClassLoaded()) {
            $this->LoadUser($_SWIFT_BaseObject);
        } else {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }
    }

    /**
     * Set the Rule Type
     *
     * @author Varun Shoor
     * @param mixed $_ruleType The Notification Rule Type
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    protected function SetRuleType($_ruleType)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } elseif (!SWIFT_NotificationRule::IsValidRuleType($_ruleType)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->_ruleType = $_ruleType;

        return true;
    }

    /**
     * Retrieve the notification rule type
     *
     * @author Varun Shoor
     * @return mixed The Notification Rule Type
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetRuleType()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_ruleType;
    }

    /**
     * Set the Email Type
     *
     * @author Mansi Wason
     * @param mixed $_isPrivate
     * @return bool
     * @throws SWIFT_Exception
     */
    public function SetPrivate($_isPrivate)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_isPrivate = $_isPrivate;

        return true;
    }

    /**
     * Retrieve the Email Type
     *
     * @author Mansi Wason
     * @return mixed $_isPrivate
     * @throws SWIFT_Exception
     */
    public function GetPrivate()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_isPrivate;
    }

    /**
     * Load the Ticket Object
     *
     * @author Varun Shoor
     * @param SWIFT_Ticket $_SWIFT_TicketObject
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function LoadTicket(SWIFT_Ticket $_SWIFT_TicketObject)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } elseif (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->Ticket = $_SWIFT_TicketObject;
        $this->SetRuleType(SWIFT_NotificationRule::TYPE_TICKET);

        return true;
    }

    /**
     * Load the User Object
     *
     * @author Varun Shoor
     * @param SWIFT_User $_SWIFT_UserObject
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function LoadUser(SWIFT_User $_SWIFT_UserObject)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } elseif (!$_SWIFT_UserObject instanceof SWIFT_User || !$_SWIFT_UserObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->User = $_SWIFT_UserObject;
        $this->SetRuleType(SWIFT_NotificationRule::TYPE_USER);

        return true;
    }

    /**
     * Triggered when a property is changed
     *
     * @author Varun Shoor
     * @param mixed $_fieldName The Field Name
     * @param string $_oldValue The Old Value
     * @param string $_newValue The New Value
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Changed($_fieldName, $_oldValue, $_newValue)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_changeContainer[$_fieldName] = array($_oldValue, $_newValue);

        return true;
    }

    /**
     * Set an event for action
     *
     * @author Varun Shoor
     * @param string $_event The Event
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function SetEvent($_event)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_event[] = $_event;

        return true;
    }

    /**
     * Retrieve the active event
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetEvent()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_event;
    }

    /**
     * Trigger the parsing of notification rules
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Trigger()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            // If nothing changed and the event is invalid then bail out
        } elseif (count($this->_changeContainer) == 0 && !_is_array($this->_event)) {
            return false;
        }
        $_staffCache = $this->Cache->Get('staffcache');
        $_notificationRuleCache = $this->Cache->Get('notificationrulescache');
        if (!_is_array($_notificationRuleCache)) {
            return false;
        }

        // By now something either changed or an event was triggered..
        $_finalRulesContainer = array();
        foreach ($_notificationRuleCache as $_notificationRuleID => $_notificationRuleContainer) {
            /*
             * BUG FIX - Varun Shoor
             *
             * SWIFT-2145 Staff receives two notification emails for a single notification event
             *
             */
            if (isset($_notificationRuleContainer['ruletype']) && $_notificationRuleContainer['ruletype'] == $this->GetRuleType() && $_notificationRuleContainer['isenabled'] == '1') {
                if (!isset($this->_event) || !_is_array($this->_event)) {
                    $_finalRulesContainer[$_notificationRuleID] = $_notificationRuleContainer;
                    $_finalRulesContainer[$_notificationRuleID]['event'] = '0';
                } else {

                    foreach ($_notificationRuleContainer['_criteria'] as $_criteriaID => $_criteriaContainer) {
                        foreach ($this->_event as $_event) {
                            if ($_criteriaContainer[2] == $_event) {
                                $_finalRulesContainer[$_notificationRuleID] = $_notificationRuleContainer;
                                $_finalRulesContainer[$_notificationRuleID]['event'] = $_event;
                            }
                        }
                        if (!isset($_finalRulesContainer[$_notificationRuleID]) && in_array($_criteriaContainer[0], array_keys($this->_changeContainer))) {  // Check in ChangeContainer
                            $_finalRulesContainer[$_notificationRuleID] = $_notificationRuleContainer;
                            $_finalRulesContainer[$_notificationRuleID]['event'] = '0';
                        }
                    }
                }
            }
        }

        if (!count($_finalRulesContainer)) {
            return false;
        }

        $_finalActionsContainer = array();

        foreach ($_finalRulesContainer as $_notificationRuleID => $_notificationRuleContainer) {

            $_SWIFT_NotificationRuleObject = new SWIFT_NotificationRule(new SWIFT_DataStore($_notificationRuleContainer));
            if (!$_SWIFT_NotificationRuleObject instanceof SWIFT_NotificationRule || !$_SWIFT_NotificationRuleObject->GetIsClassLoaded()) {
                continue;
            }

            if ($this->GetRuleType() == SWIFT_NotificationRule::TYPE_TICKET) {
                $_SWIFT_NotificationRuleObject->ProcessProperties($this->Ticket, $_notificationRuleContainer['event'], $this->_changeContainer);
            } elseif ($this->GetRuleType() == SWIFT_NotificationRule::TYPE_USER) {
                $_SWIFT_NotificationRuleObject->ProcessProperties($this->User, $_notificationRuleContainer['event'], $this->_changeContainer);
            }

            $_ruleResult = $_SWIFT_NotificationRuleObject->Execute(SWIFT_NotificationRule::GetCriteriaPointer($_notificationRuleContainer['ruletype']));
            if ($_ruleResult) {
                $_finalActionsContainer[$_notificationRuleID] = array();
                $_finalActionsContainer[$_notificationRuleID][SWIFT_NotificationAction::ACTION_EMAILSTAFF] = false;
                $_finalActionsContainer[$_notificationRuleID][SWIFT_NotificationAction::ACTION_EMAILSTAFFGROUP] = false;
                $_finalActionsContainer[$_notificationRuleID][SWIFT_NotificationAction::ACTION_EMAILDEPARTMENT] = false;
                $_finalActionsContainer[$_notificationRuleID][SWIFT_NotificationAction::ACTION_EMAILUSER] = false;
                $_finalActionsContainer[$_notificationRuleID][SWIFT_NotificationAction::ACTION_EMAILCUSTOM] = array();
                $_finalActionsContainer[$_notificationRuleID][SWIFT_NotificationAction::ACTION_EMAILSTAFFCUSTOM] = array();
                $_finalActionsContainer[$_notificationRuleID][SWIFT_NotificationAction::ACTION_POOLSTAFF] = false;
                $_finalActionsContainer[$_notificationRuleID][SWIFT_NotificationAction::ACTION_POOLSTAFFGROUP] = false;
                $_finalActionsContainer[$_notificationRuleID][SWIFT_NotificationAction::ACTION_POOLDEPARTMENT] = false;
                $_finalActionsContainer[$_notificationRuleID][SWIFT_NotificationAction::ACTION_POOLCUSTOM] = array();

                $_finalActionsContainer[$_notificationRuleID]['event'] = $_notificationRuleContainer['event'];

                // Execute All Actions Here
                foreach ($_notificationRuleContainer['_actions'] as $_actionContainer) {
                    if ($_actionContainer['actiontype'] == SWIFT_NotificationAction::ACTION_EMAILSTAFF) {
                        $_finalActionsContainer[$_notificationRuleID][SWIFT_NotificationAction::ACTION_EMAILSTAFF] = true;
                    } elseif ($_actionContainer['actiontype'] == SWIFT_NotificationAction::ACTION_EMAILUSER) {
                        $_finalActionsContainer[$_notificationRuleID][SWIFT_NotificationAction::ACTION_EMAILUSER] = true;
                    } elseif ($_actionContainer['actiontype'] == SWIFT_NotificationAction::ACTION_EMAILSTAFFGROUP) {
                        $_finalActionsContainer[$_notificationRuleID][SWIFT_NotificationAction::ACTION_EMAILSTAFFGROUP] = true;
                    } elseif ($_actionContainer['actiontype'] == SWIFT_NotificationAction::ACTION_EMAILDEPARTMENT) {
                        $_finalActionsContainer[$_notificationRuleID][SWIFT_NotificationAction::ACTION_EMAILDEPARTMENT] = true;
                    } elseif ($_actionContainer['actiontype'] == SWIFT_NotificationAction::ACTION_POOLSTAFF) {
                        $_finalActionsContainer[$_notificationRuleID][SWIFT_NotificationAction::ACTION_POOLSTAFF] = true;
                    } elseif ($_actionContainer['actiontype'] == SWIFT_NotificationAction::ACTION_POOLSTAFFGROUP) {
                        $_finalActionsContainer[$_notificationRuleID][SWIFT_NotificationAction::ACTION_POOLSTAFFGROUP] = true;
                    } elseif ($_actionContainer['actiontype'] == SWIFT_NotificationAction::ACTION_POOLDEPARTMENT) {
                        $_finalActionsContainer[$_notificationRuleID][SWIFT_NotificationAction::ACTION_POOLDEPARTMENT] = true;
                    } elseif ($_actionContainer['actiontype'] == SWIFT_NotificationAction::ACTION_EMAILCUSTOM) {
                        if (!in_array($_actionContainer['contents'], $_finalActionsContainer[$_notificationRuleID][SWIFT_NotificationAction::ACTION_EMAILCUSTOM])) {
                            $_finalActionsContainer[$_notificationRuleID][SWIFT_NotificationAction::ACTION_EMAILCUSTOM][] = $_actionContainer['contents'];
                        }
                    } elseif ($_actionContainer['actiontype'] == SWIFT_NotificationAction::ACTION_EMAILSTAFFCUSTOM && isset($_staffCache[$_actionContainer['contents']]) && $_staffCache[$_actionContainer['contents']]['isenabled'] == '1') {
                        $_emailAddress = $_staffCache[$_actionContainer['contents']]['email'];
                        if (!in_array($_emailAddress, $_finalActionsContainer[$_notificationRuleID][SWIFT_NotificationAction::ACTION_EMAILCUSTOM])) {
                            $_finalActionsContainer[$_notificationRuleID][SWIFT_NotificationAction::ACTION_EMAILCUSTOM][] = $_emailAddress;
                        }
                    } elseif ($_actionContainer['actiontype'] == SWIFT_NotificationAction::ACTION_POOLCUSTOM) {
                        if (!in_array($_actionContainer['contents'], $_finalActionsContainer[$_notificationRuleID][SWIFT_NotificationAction::ACTION_POOLCUSTOM])) {
                            $_finalActionsContainer[$_notificationRuleID][SWIFT_NotificationAction::ACTION_POOLCUSTOM][] = $_actionContainer['contents'];
                        }
                    }
                }

//                echo $_SWIFT_NotificationRuleObject->GetProperty('title') . ': SUCCESS<br />';
            } else {
//                echo $_SWIFT_NotificationRuleObject->GetProperty('title') . ': FAILURE<br />';
            }
        }

        if (!count($_finalActionsContainer)) {
            return false;
        }

        /**
         * ---------------------------------------------
         * EXECUTE ACTIONS HERE
         * ---------------------------------------------
         */
        foreach ($_finalActionsContainer as $_notificationRuleID => $_actionsContainer) {
            $_emailPrefix = $_finalRulesContainer[$_notificationRuleID]['emailprefix'];
            foreach ($_actionsContainer as $_actionType => $_actionValue) {
                // Ticket
                if ($this->GetRuleType() == SWIFT_NotificationRule::TYPE_TICKET) {
                    /**
                     * BUG FIX - Mansi Wason<mansi.wason@kayako.com>
                     *
                     * SWIFT-4233 Private replies are sent to users, if 'User' is selected in 'New reply from staff' notifications.
                     *
                     * Comments: Users will not receive notification for private replies.
                     */
                    if ($_actionType == SWIFT_NotificationAction::ACTION_EMAILSTAFF && $_actionValue == true) {
                        $this->Ticket->DispatchNotification(SWIFT_TicketNotification::TYPE_STAFF, array(), $_emailPrefix, $_actionsContainer['event']);
                    } elseif ($_actionType == SWIFT_NotificationAction::ACTION_EMAILUSER && $_actionValue == true && $this->GetPrivate() == false) {
                        $this->Ticket->DispatchNotification(SWIFT_TicketNotification::TYPE_USER, array(), $_emailPrefix, $_actionsContainer['event']);
                    } elseif ($_actionType == SWIFT_NotificationAction::ACTION_EMAILSTAFFGROUP && $_actionValue == true) {
                        $this->Ticket->DispatchNotification(SWIFT_TicketNotification::TYPE_TEAM, array(), $_emailPrefix, $_actionsContainer['event']);
                    } elseif ($_actionType == SWIFT_NotificationAction::ACTION_EMAILDEPARTMENT && $_actionValue == true) {
                        $this->Ticket->DispatchNotification(SWIFT_TicketNotification::TYPE_DEPARTMENT, array(), $_emailPrefix, $_actionsContainer['event']);
                    } elseif ($_actionType == SWIFT_NotificationAction::ACTION_EMAILCUSTOM && _is_array($_actionValue)) {
                        $this->Ticket->DispatchNotification(SWIFT_TicketNotification::TYPE_CUSTOM, $_actionValue, $_emailPrefix, $_actionsContainer['event']);

                    } elseif ($_actionType == SWIFT_NotificationAction::ACTION_POOLSTAFF && $_actionValue == true) {
                        $this->Ticket->DispatchNotificationPool(SWIFT_TicketNotification::TYPE_STAFF, array(), $_actionsContainer['event']);
                    } elseif ($_actionType == SWIFT_NotificationAction::ACTION_POOLSTAFFGROUP && $_actionValue == true) {
                        $this->Ticket->DispatchNotificationPool(SWIFT_TicketNotification::TYPE_TEAM, array(), $_actionsContainer['event']);
                    } elseif ($_actionType == SWIFT_NotificationAction::ACTION_POOLDEPARTMENT && $_actionValue == true) {
                        $this->Ticket->DispatchNotificationPool(SWIFT_TicketNotification::TYPE_DEPARTMENT, array(), $_actionsContainer['event']);
                    } elseif ($_actionType == SWIFT_NotificationAction::ACTION_POOLCUSTOM && _is_array($_actionValue)) {
                        $this->Ticket->DispatchNotificationPool(SWIFT_TicketNotification::TYPE_CUSTOM, $_actionValue, $_actionsContainer['event']);
                    }

                    // Users
                } elseif ($this->GetRuleType() == SWIFT_NotificationRule::TYPE_USER) {
                    if ($_actionType == SWIFT_NotificationAction::ACTION_EMAILCUSTOM && _is_array($_actionValue)) {
                        $this->User->DispatchNotification(SWIFT_UserNotification::TYPE_CUSTOM, $_actionValue, $_emailPrefix);
                    } elseif ($_actionType == SWIFT_NotificationAction::ACTION_POOLCUSTOM && _is_array($_actionValue)) {
                        $this->User->DispatchNotificationPool(SWIFT_UserNotification::TYPE_CUSTOM, $_actionValue);
                    }

                }
            }
        }

        return true;
    }
}

?>
