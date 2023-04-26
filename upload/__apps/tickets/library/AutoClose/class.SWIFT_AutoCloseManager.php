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

namespace Tickets\Library\AutoClose;

use SWIFT;
use Tickets\Models\AutoClose\SWIFT_AutoCloseRule;
use SWIFT_DataStore;
use SWIFT_Exception;
use SWIFT_Library;
use Base\Library\Rules\SWIFT_Rules;
use Tickets\Models\Ticket\SWIFT_Ticket;

/**
 * The Auto Close Manager Class
 *
 * This class handles all the lookups, notifications and status changes
 *
 * @author Varun Shoor
 */
class SWIFT_AutoCloseManager extends SWIFT_Library {
    const FIELD_TICKETSTATUSID = 1;
    const FIELD_DEPARTMENTID = 2;
    const FIELD_TICKETPRIORITYID = 3;
    const FIELD_TICKETTYPEID = 4;
    const FIELD_OTHER = 5;

    const AUTOCLOSE_ITEMS_LIMIT = 1000;

    /**
     * Execute the Ticket Auto Close Routines
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function ExecutePending()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_autoCloseRuleCache = (array) $_SWIFT->Cache->Get('autocloserulecache');
        if (!_is_array($_autoCloseRuleCache)) {
            return false;
        }

        $_ticketStatusCache = (array) $_SWIFT->Cache->Get('statuscache');

        foreach ($_autoCloseRuleCache as $_autoCloseRuleID => $_autoCloseRuleContainer) {
            $_SWIFT_AutoCloseRuleObject = new SWIFT_AutoCloseRule(new SWIFT_DataStore($_autoCloseRuleContainer));
            if (!$_SWIFT_AutoCloseRuleObject instanceof SWIFT_AutoCloseRule || !$_SWIFT_AutoCloseRuleObject->GetIsClassLoaded()) {
                // @codeCoverageIgnoreStart
                // this code will never be executed
                continue;
                // @codeCoverageIgnoreEnd
            }

            // Is the rule enabled?
            if ($_SWIFT_AutoCloseRuleObject->GetProperty('isenabled') == '0') {
                continue;
            }

            // If this rule has invalid status or the target status isnt set to mark as resolved then we bail out
            if (!isset($_ticketStatusCache[$_SWIFT_AutoCloseRuleObject->GetProperty('targetticketstatusid')])
                    || $_ticketStatusCache[$_SWIFT_AutoCloseRuleObject->GetProperty('targetticketstatusid')]['markasresolved'] == '0') {
                continue;
            }

            $_andContainer = $_orContainer = array();

            $_fieldList = array(self::FIELD_TICKETSTATUSID, self::FIELD_DEPARTMENTID, self::FIELD_TICKETPRIORITYID, self::FIELD_TICKETTYPEID, self::FIELD_OTHER);
            foreach ($_fieldList as $_fieldType) {
                $_andContainer[$_fieldType] = array();
                $_orContainer[$_fieldType] = array();
            }

            // Build the SQL statements for each field, we follow a specific order to ensure that the index gets utilized
            // Index Order: ticketstatusid, departmentid, priorityid, tickettypeid
            $criteria = (array) $_autoCloseRuleContainer['_criteria'];
            foreach ($criteria as $_criteriaContainer) {
                if ($_criteriaContainer[0] == SWIFT_AutoCloseRule::AUTOCLOSE_TICKETSTATUS) {
                    if ($_criteriaContainer[3] == SWIFT_Rules::RULE_MATCHALL) {
                        $_andContainer[self::FIELD_TICKETSTATUSID][] = SWIFT_Rules::BuildSQL(Clean($_criteriaContainer[0]), $_criteriaContainer[1], $_criteriaContainer[2]);
                    } else if ($_criteriaContainer[3] == SWIFT_Rules::RULE_MATCHANY) {
                        $_orContainer[self::FIELD_TICKETSTATUSID][] = SWIFT_Rules::BuildSQL(Clean($_criteriaContainer[0]), $_criteriaContainer[1], $_criteriaContainer[2]);
                    }
                } else if ($_criteriaContainer[0] == SWIFT_AutoCloseRule::AUTOCLOSE_TICKETDEPARTMENT) {
                    if ($_criteriaContainer[3] == SWIFT_Rules::RULE_MATCHALL) {
                        $_andContainer[self::FIELD_DEPARTMENTID][] = SWIFT_Rules::BuildSQL(Clean($_criteriaContainer[0]), $_criteriaContainer[1], $_criteriaContainer[2]);
                    } else if ($_criteriaContainer[3] == SWIFT_Rules::RULE_MATCHANY) {
                        $_orContainer[self::FIELD_DEPARTMENTID][] = SWIFT_Rules::BuildSQL(Clean($_criteriaContainer[0]), $_criteriaContainer[1], $_criteriaContainer[2]);
                    }
                } else if ($_criteriaContainer[0] == SWIFT_AutoCloseRule::AUTOCLOSE_TICKETPRIORITY) {
                    if ($_criteriaContainer[3] == SWIFT_Rules::RULE_MATCHALL) {
                        $_andContainer[self::FIELD_TICKETPRIORITYID][] = SWIFT_Rules::BuildSQL(Clean($_criteriaContainer[0]), $_criteriaContainer[1], $_criteriaContainer[2]);
                    } else if ($_criteriaContainer[3] == SWIFT_Rules::RULE_MATCHANY) {
                        $_orContainer[self::FIELD_TICKETPRIORITYID][] = SWIFT_Rules::BuildSQL(Clean($_criteriaContainer[0]), $_criteriaContainer[1], $_criteriaContainer[2]);
                    }
                } else if ($_criteriaContainer[0] == SWIFT_AutoCloseRule::AUTOCLOSE_TICKETTYPE) {
                    if ($_criteriaContainer[3] == SWIFT_Rules::RULE_MATCHALL) {
                        $_andContainer[self::FIELD_TICKETTYPEID][] = SWIFT_Rules::BuildSQL(Clean($_criteriaContainer[0]), $_criteriaContainer[1], $_criteriaContainer[2]);
                    } else if ($_criteriaContainer[3] == SWIFT_Rules::RULE_MATCHANY) {
                        $_orContainer[self::FIELD_TICKETTYPEID][] = SWIFT_Rules::BuildSQL(Clean($_criteriaContainer[0]), $_criteriaContainer[1], $_criteriaContainer[2]);
                    }
                } else {
                    if ($_criteriaContainer[3] == SWIFT_Rules::RULE_MATCHALL) {
                        $_andContainer[self::FIELD_OTHER][] = SWIFT_Rules::BuildSQL(Clean($_criteriaContainer[0]), $_criteriaContainer[1], $_criteriaContainer[2]);
                    } else if ($_criteriaContainer[3] == SWIFT_Rules::RULE_MATCHANY) {
                        $_orContainer[self::FIELD_OTHER][] = SWIFT_Rules::BuildSQL(Clean($_criteriaContainer[0]), $_criteriaContainer[1], $_criteriaContainer[2]);
                    }
                }
            }

            // Optimization
            if (count($_andContainer)) {
                if (!count($_andContainer[self::FIELD_TICKETSTATUSID])) {
                    $_andContainer[self::FIELD_TICKETSTATUSID][] = 'ticketstatusid > 0';
                }

                if (!count($_andContainer[self::FIELD_DEPARTMENTID])) {
                    $_andContainer[self::FIELD_DEPARTMENTID][] = 'departmentid > 0';
                }

                if (!count($_andContainer[self::FIELD_TICKETPRIORITYID])) {
                    $_andContainer[self::FIELD_TICKETPRIORITYID][] = 'priorityid > 0';
                }
            }

            // Sort the arrays to ensure index utilization
            ksort($_andContainer);
            ksort($_orContainer);

            // Build up the final SQL query list
            $_finalAndContainer = $_finalOrContainer = array();
            foreach ($_andContainer as $_fieldType => $_queryList) {
                $_finalAndContainer = array_merge($_finalAndContainer, $_queryList);
            }

            foreach ($_orContainer as $_fieldType => $_queryList) {
                $_finalOrContainer = array_merge($_finalOrContainer, $_queryList);
            }

            $_inactivityThreshold = DATENOW - (floatval($_SWIFT_AutoCloseRuleObject->GetProperty('inactivitythreshold')) * 60 * 60);

            $_ticketIDList = array();
            $_SWIFT->Database->QueryLimit("SELECT tickets.ticketid FROM " . TABLE_PREFIX . "tickets AS tickets
                LEFT JOIN " . TABLE_PREFIX . "users AS users ON (tickets.userid = users.userid)
                 WHERE (tickets.isresolved = '0' AND tickets.autoclosestatus = '" . SWIFT_Ticket::AUTOCLOSESTATUS_NONE . "' AND tickets.lastactivity <= '" . (int) ($_inactivityThreshold) . "')
                   AND (" . implode(' AND ', $_finalAndContainer) . ")" . IIF(count($_finalOrContainer), " AND (" . implode(' OR ', $_finalOrContainer) . ")"), self::AUTOCLOSE_ITEMS_LIMIT);
            while ($_SWIFT->Database->NextRecord()) {
                $_ticketIDList[] = $_SWIFT->Database->Record['ticketid'];
            }

            if (!count($_ticketIDList)) {
                continue;
            }

            if (!SWIFT::Get('iscron')) {
                echo 'Pending (' . $_SWIFT_AutoCloseRuleObject->GetProperty('title') . '): ' . print_r($_ticketIDList, true) . SWIFT_CRLF;
            }

            // Now that we have the final ticket list, we need to mark their status as pending and update the timeline
            SWIFT_Ticket::MarkAsAutoClosePending($_ticketIDList, $_SWIFT_AutoCloseRuleObject);
        }

        return true;
    }

    /**
     * Close all the pending auto close tickets
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function ExecuteClosure()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_autoCloseRuleCache = (array) $_SWIFT->Cache->Get('autocloserulecache');
        if (!_is_array($_autoCloseRuleCache)) {
            return false;
        }

        $_ticketStatusCache = (array) $_SWIFT->Cache->Get('statuscache');

        foreach ($_autoCloseRuleCache as $_autoCloseRuleID => $_autoCloseRuleContainer) {
            $_SWIFT_AutoCloseRuleObject = new SWIFT_AutoCloseRule(new SWIFT_DataStore($_autoCloseRuleContainer));
            if (!$_SWIFT_AutoCloseRuleObject instanceof SWIFT_AutoCloseRule || !$_SWIFT_AutoCloseRuleObject->GetIsClassLoaded()) {
                // @codeCoverageIgnoreStart
                // this code will never be executed
                continue;
                // @codeCoverageIgnoreEnd
            }

            // Is the rule enabled?
            if ($_SWIFT_AutoCloseRuleObject->GetProperty('isenabled') == '0') {
                continue;
            }

            // If this rule has invalid status or the target status isnt set to mark as resolved then we bail out
            if (!isset($_ticketStatusCache[$_SWIFT_AutoCloseRuleObject->GetProperty('targetticketstatusid')])
                    || $_ticketStatusCache[$_SWIFT_AutoCloseRuleObject->GetProperty('targetticketstatusid')]['markasresolved'] == '0') {
                continue;
            }

            $_closureThreshold = DATENOW - (floatval($_SWIFT_AutoCloseRuleObject->GetProperty('closurethreshold'))*60*60);

            // Retrieve all tickets that are pending closure
            $_ticketIDList = array();
            $_SWIFT->Database->QueryLimit("SELECT ticketid FROM " . TABLE_PREFIX . "tickets t
                left join " . TABLE_PREFIX . "ticketstatus ts on t.ticketstatusid = ts.ticketstatusid 
                WHERE t.autoclosestatus = " . SWIFT_Ticket::AUTOCLOSESTATUS_PENDING . " 
                AND t.autocloseruleid = " .  ($_autoCloseRuleID) . " 
                AND t.autoclosetimeline <= " . (int) ($_closureThreshold). " 
                AND ts.markasresolved != 1 ", self::AUTOCLOSE_ITEMS_LIMIT);
            while ($_SWIFT->Database->NextRecord()) {
                $_ticketIDList[] = $_SWIFT->Database->Record['ticketid'];
            }

            if (!count($_ticketIDList)) {
                continue;
            }

            if (!SWIFT::Get('iscron')) {
                echo 'Closed (' . $_SWIFT_AutoCloseRuleObject->GetProperty('title') . '): ' . print_r($_ticketIDList, true) . SWIFT_CRLF;
            }

            // Now that we have the tickets, we have to mark em as closed
            SWIFT_Ticket::MarkAsAutoClosed($_ticketIDList, $_SWIFT_AutoCloseRuleObject);
        }

        return true;
    }
}
