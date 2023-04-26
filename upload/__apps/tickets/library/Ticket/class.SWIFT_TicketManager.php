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

namespace Tickets\Library\Ticket;

use SWIFT;
use SWIFT_Exception;
use SWIFT_Library;
use Base\Models\Staff\SWIFT_Staff;
use Tickets\Models\Ticket\SWIFT_Ticket;

/**
 * The Ticket Manager Class
 *
 * @author Varun Shoor
 */
class SWIFT_TicketManager extends SWIFT_Library {
    static protected $_recountDepartmentIDList = array();

    static protected $_shutdownQueued = false;
    static protected $_rebuildCacheExecuted = false;

    /**
     * Rebuild the cache on shutdown
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function RebuildCacheOnShutdown() {
        if (!count(static::$_recountDepartmentIDList)) {
            static::RebuildCache(false);
        } else {
            static::RebuildCache(static::$_recountDepartmentIDList);
        }

        return true;
    }

    /**
     * Add the specified department to recount queue
     *
     * @author Varun Shoor
     * @param int $_departmentID The Department ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function Recount($_departmentID) {
        if (!in_array($_departmentID, static::$_recountDepartmentIDList)) {
            static::$_recountDepartmentIDList[] = $_departmentID;
        }

        if ($_departmentID == false) {
            static::$_recountDepartmentIDList = array();
        }

        if (static::$_shutdownQueued == true) {
            return true;
        }

        SWIFT::Shutdown('Tickets\Library\Ticket\SWIFT_TicketManager', 'RebuildCacheOnShutdown', 10, false);

        static::$_shutdownQueued = true;

        return true;
    }

    /**
     * Rebuild the Ticket Cache
     *
     * @author Varun Shoor
     * @param array $_departmentIDList (OPTIONAL) The department id list. If specified, the results will be filtered to the specified departments.
     * @param bool $_forceRebuild
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RebuildCache($_departmentIDList = array(), $_forceRebuild = false) {
        $_SWIFT = SWIFT::GetInstance();

        if (static::$_rebuildCacheExecuted == true && !$_forceRebuild) {
            return true;
        }

        $_staffCache = (array) $_SWIFT->Cache->Get('staffcache');
        $_assignedDepartmentContainer = array();
        foreach ($_staffCache as $_staffID => $_staffContainer) {
            $_assignedDepartmentContainer[$_staffID] = SWIFT_Staff::GetAssignedDepartmentsOnStaffID($_staffID);
        }

        $_ticketCountCache = $_SWIFT->Cache->Get('ticketcountcache');
        $_ticketStatusCache = (array) $_SWIFT->Cache->Get('statuscache');
        if (!_is_array($_ticketCountCache)) {
            $_ticketCountCache = array();
        }

        $_resetDepartmentCounters = false;
        if (!_is_array($_departmentIDList)) {
            $_departmentIDList = array();

            $_resetDepartmentCounters = true;
        }

        $_unresolvedTicketStatusIDList = array();
        // Process the ticket status cache
        foreach ($_ticketStatusCache as $_key => $_val) {
            if ($_val['markasresolved'] == '0') {
                $_unresolvedTicketStatusIDList[] = $_val['ticketstatusid'];
            }
        }

        $_countSelectQuery = "SELECT COUNT(*) AS totalitems, departmentid, ticketstatusid, ownerstaffid, tickettypeid, MAX(lastactivity) AS lastactivity FROM " . TABLE_PREFIX . "tickets
			GROUP BY departmentid, ticketstatusid, ownerstaffid, tickettypeid";

        if (_is_array($_departmentIDList)) {
            $_SWIFT->Database->Query($_countSelectQuery . " HAVING departmentid IN (" . BuildIN($_departmentIDList) . ")");
        } else {
            $_SWIFT->Database->Query($_countSelectQuery);
        }

        // Process Parent Departments
        if (!isset($_ticketCountCache['departments']) || $_resetDepartmentCounters) {
            $_ticketCountCache['departments'] = array();
        }

        $_parentDepartmentContainer = &$_ticketCountCache['departments'];

        // Process Parent Ticket Status
        if (!isset($_ticketCountCache['ticketstatus']) || $_resetDepartmentCounters) {
            $_ticketCountCache['ticketstatus'] = array();
        }

        $_parentTicketStatusContainer = &$_ticketCountCache['ticketstatus'];

        // Process Parent Ticket Owner
        if (!isset($_ticketCountCache['ownerstaff']) || $_resetDepartmentCounters) {
            $_ticketCountCache['ownerstaff'] = array();
        }

        $_parentOwnerStaffContainer = &$_ticketCountCache['ownerstaff'];

        // Process Parent Unassigned
        if (!isset($_ticketCountCache['unassigned']) || $_resetDepartmentCounters) {
            $_ticketCountCache['unassigned'] = array();
        }

        $_parentUnassignedContainer = &$_ticketCountCache['unassigned'];

        // Process Parent Ticket Type
        if (!isset($_ticketCountCache['tickettypes']) || $_resetDepartmentCounters) {
            $_ticketCountCache['tickettypes'] = array();
        }

        $_parentTicketTypeContainer = &$_ticketCountCache['tickettypes'];

        while ($_SWIFT->Database->NextRecord()) {
            $_departmentID = (int) ($_SWIFT->Database->Record['departmentid']);
            $_ticketStatusID = (int) ($_SWIFT->Database->Record['ticketstatusid']);
            $_ownerStaffID = (int) ($_SWIFT->Database->Record['ownerstaffid']);
            $_ticketTypeID = (int) ($_SWIFT->Database->Record['tickettypeid']);
            $_totalItems = (int) ($_SWIFT->Database->Record['totalitems']);
            $_lastActivity = (int) ($_SWIFT->Database->Record['lastactivity']);

            $_isUnresolvedTicketStatus = false;
            if (in_array($_ticketStatusID, $_unresolvedTicketStatusIDList) || $_ticketStatusID == '0') {
                $_isUnresolvedTicketStatus = true;
            }

            /*
             * ###############################################
             * PROCESS DEPARTMENTS
             * ###############################################
             */
            if (!isset($_parentDepartmentContainer[$_departmentID]) || in_array($_departmentID, $_departmentIDList)) {
                $_parentDepartmentContainer[$_departmentID] = array();
                $_parentDepartmentContainer[$_departmentID]['lastactivity'] = $_parentDepartmentContainer[$_departmentID]['totalitems'] = 0;
                $_parentDepartmentContainer[$_departmentID]['totalunresolveditems'] = 0;

                $_parentDepartmentContainer[$_departmentID]['ticketstatus'] = array();
                $_parentDepartmentContainer[$_departmentID]['tickettypes'] = array();
                $_parentDepartmentContainer[$_departmentID]['ownerstaff'] = array();
            }

            $_departmentTicketStatusContainer = &$_parentDepartmentContainer[$_departmentID]['ticketstatus'];
            $_departmentTicketTypeContainer = &$_parentDepartmentContainer[$_departmentID]['tickettypes'];
            $_departmentOwnerStaffContainer = &$_parentDepartmentContainer[$_departmentID]['ownerstaff'];

            // First add to department count
            $_parentDepartmentContainer[$_departmentID]['totalitems'] += $_totalItems;

            if ($_isUnresolvedTicketStatus) {
                $_parentDepartmentContainer[$_departmentID]['totalunresolveditems'] += $_totalItems;
            }

            // Compare last activity
            $_departmentLastActivity = $_parentDepartmentContainer[$_departmentID]['lastactivity'];
            if ($_lastActivity > $_departmentLastActivity) {
                // Update because something changed since last refresh
                $_parentDepartmentContainer[$_departmentID]['lastactivity'] = $_lastActivity;
            }

            /*
             * ###############################################
             * PROCESS TICKET STATUS
             * ###############################################
             */
            // Check if the record exists in parent ticket status container
            if (!isset($_parentTicketStatusContainer[$_ticketStatusID])) {
                $_parentTicketStatusContainer[$_ticketStatusID] = array();
                $_parentTicketStatusContainer[$_ticketStatusID]['lastactivity'] = $_parentTicketStatusContainer[$_ticketStatusID]['totalitems'] = 0;
            }

            if (!isset($_departmentTicketStatusContainer[$_ticketStatusID])) {
                $_departmentTicketStatusContainer[$_ticketStatusID] = array();
                $_departmentTicketStatusContainer[$_ticketStatusID]['lastactivity'] = $_departmentTicketStatusContainer[$_ticketStatusID]['totalitems'] = 0;
                $_departmentTicketStatusContainer[$_ticketStatusID]['ownerstaff'] = array();
            }

            $_ticketStatusOwnerContainer = &$_parentTicketStatusContainer[$_ticketStatusID]['ownerstaff'];

            // Compare last activity for the ticket status (both parent container and department container)
            foreach (array('_parentTicketStatusContainer', '_departmentTicketStatusContainer') as $_key => $_val) {
                $_containerArray = &${$_val};
                $_ticketStatusLastActivity = $_containerArray[$_ticketStatusID]['lastactivity'];
                if ($_lastActivity > $_ticketStatusLastActivity) {
                    $_containerArray[$_ticketStatusID]['lastactivity'] = $_lastActivity;
                }

                $_containerArray[$_ticketStatusID]['totalitems'] += $_totalItems;
            }

            /*
             * ###############################################
             * PROCESS OWNER STAFF
             * ###############################################
             */
            if (!isset($_parentOwnerStaffContainer[$_ownerStaffID])) {
                $_parentOwnerStaffContainer[$_ownerStaffID] = array();
                $_parentOwnerStaffContainer[$_ownerStaffID]['lastactivity'] = $_parentOwnerStaffContainer[$_ownerStaffID]['totalitems'] = 0;
                $_parentOwnerStaffContainer[$_ownerStaffID]['totalunresolveditems'] = 0;
            }

            if (!isset($_ticketStatusOwnerContainer[$_ownerStaffID])) {
                $_ticketStatusOwnerContainer[$_ownerStaffID] = array();
                $_ticketStatusOwnerContainer[$_ownerStaffID]['lastactivity'] = $_ticketStatusOwnerContainer[$_ownerStaffID]['totalitems'] = 0;
                $_ticketStatusOwnerContainer[$_ownerStaffID]['totalunresolveditems'] = 0;
            }

            if (!isset($_departmentOwnerStaffContainer[$_ownerStaffID])) {
                $_departmentOwnerStaffContainer[$_ownerStaffID] = array();
                $_departmentOwnerStaffContainer[$_ownerStaffID]['lastactivity'] = $_departmentOwnerStaffContainer[$_ownerStaffID]['totalitems'] = 0;
                $_departmentOwnerStaffContainer[$_ownerStaffID]['totalunresolveditems'] = 0;
            }

            // Compare last activity for the owner
            foreach (array('_parentOwnerStaffContainer', '_ticketStatusOwnerContainer', '_departmentOwnerStaffContainer') as $_key => $_val) {
                if (($_departmentID == '0' || (isset($_assignedDepartmentContainer[$_ownerStaffID]) && !in_array($_departmentID, $_assignedDepartmentContainer[$_ownerStaffID]))) && $_val === '_parentOwnerStaffContainer') {
                    continue;
                }

                $_ownerStaffLastActivity = ${$_val}[$_ownerStaffID]['lastactivity'];
                if ($_lastActivity > $_ownerStaffLastActivity) {
                    ${$_val}[$_ownerStaffID]['lastactivity'] = $_lastActivity;
                }

                ${$_val}[$_ownerStaffID]['totalitems'] += $_totalItems;

                if ($_isUnresolvedTicketStatus) {
                    ${$_val}[$_ownerStaffID]['totalunresolveditems'] += $_totalItems;
                }
            }

            // Time to set unassigned property (depending upon assigned departments)
            if ($_ownerStaffID == '0') {
                foreach ($_staffCache as $_staffID => $_staffContainer) {
                    // If staff isnt assigned to any department or if it isnt assigned to this department then move on..
                    if (!isset($_assignedDepartmentContainer[$_staffID]) || (isset($_assignedDepartmentContainer[$_staffID]) && !in_array($_departmentID, $_assignedDepartmentContainer[$_staffID]))) {
                        continue;
                    }

                    if (!isset($_parentUnassignedContainer[$_staffID])) {
                        $_parentUnassignedContainer[$_staffID] = array();
                        $_parentUnassignedContainer[$_staffID]['lastactivity'] = $_parentUnassignedContainer[$_staffID]['totalitems'] = 0;
                        $_parentUnassignedContainer[$_staffID]['totalunresolveditems'] = 0;
                    }

                    $_ownerStaffLastActivity = $_parentUnassignedContainer[$_staffID]['lastactivity'];
                    if ($_lastActivity > $_ownerStaffLastActivity) {
                        $_parentUnassignedContainer[$_staffID]['lastactivity'] = $_lastActivity;
                    }

                    $_parentUnassignedContainer[$_staffID]['totalitems'] += $_totalItems;

                    if ($_isUnresolvedTicketStatus) {
                        $_parentUnassignedContainer[$_staffID]['totalunresolveditems'] += $_totalItems;
                    }
                }
            }

            /*
             * ###############################################
             * PROCESS TICKET TYPE
             * ###############################################
             */
            if (!isset($_parentTicketTypeContainer[$_ticketTypeID])) {
                $_parentTicketTypeContainer[$_ticketTypeID] = array();
                $_parentTicketTypeContainer[$_ticketTypeID]['lastactivity'] = $_parentTicketTypeContainer[$_ticketTypeID]['totalitems'] = 0;
                $_parentTicketTypeContainer[$_ticketTypeID]['totalunresolveditems'] = 0;
            }

            if (!isset($_departmentTicketTypeContainer[$_ticketTypeID])) {
                $_departmentTicketTypeContainer[$_ticketTypeID] = array();
                $_departmentTicketTypeContainer[$_ticketTypeID]['lastactivity'] = $_departmentTicketTypeContainer[$_ticketTypeID]['totalitems'] = 0;
                $_departmentTicketTypeContainer[$_ticketTypeID]['totalunresolveditems'] = 0;
            }

            foreach (array('_parentTicketTypeContainer', '_departmentTicketTypeContainer') as $_key => $_val) {
                $_ticketTypeLastActivity = ${$_val}[$_ticketTypeID]['lastactivity'];
                if ($_lastActivity > $_ticketTypeLastActivity) {
                    ${$_val}[$_ticketTypeID]['lastactivity'] = $_lastActivity;
                }

                ${$_val}[$_ticketTypeID]['totalitems'] += $_totalItems;

                if ($_isUnresolvedTicketStatus) {
                    ${$_val}[$_ticketTypeID]['totalunresolveditems'] += $_totalItems;
                }
            }
        }

        $_SWIFT->Cache->Update('ticketcountcache', $_ticketCountCache);
        static::$_rebuildCacheExecuted = true;

        return true;
    }

    /**
     * Export the Ticket as XML
     *
     * @author Varun Shoor
     * @param SWIFT_Ticket $_SWIFT_TicketObject The Ticket Object Pointer
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function ExportXML(SWIFT_Ticket $_SWIFT_TicketObject) {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        return true;
    }

    /**
     * Export the Ticket as PDF
     *
     * @author Varun Shoor
     * @param SWIFT_Ticket $_SWIFT_TicketObject The Ticket Object Pointer
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function ExportPDF(SWIFT_Ticket $_SWIFT_TicketObject) {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        return true;
    }
}
