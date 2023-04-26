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

namespace Tickets\Library\View;

use SWIFT;
use SWIFT_Exception;
use SWIFT_Library;
use Base\Models\Staff\SWIFT_Staff;

/**
 * The Ticket View Property Manager
 *
 * @author Varun Shoor
 */
class SWIFT_TicketViewPropertyManager extends SWIFT_Library {
    static protected $_rebuildCacheQueued = false;

    static protected $_ticketPropertyCache = array();

    // Core Constants
    const PROPERTY_STATUS = 1;
    const PROPERTY_PRIORITY = 2;
    const PROPERTY_TYPE = 3;
    const PROPERTY_DEPARTMENT = 4;
    const PROPERTY_STAFF = 5;
    const PROPERTY_LINK = 6;
    const PROPERTY_FLAG = 7;
    const PROPERTY_BAYES = 8;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @throws SWIFT_Exception
     */
    public function __construct() {
        parent::__construct();

        $_SWIFT = SWIFT::GetInstance();

        $_staffTicketPropertiesCache = $this->Cache->Get('staffticketpropertiescache');

        $_ticketPropertiesCache = array();

        if ($_staffTicketPropertiesCache && isset($_staffTicketPropertiesCache[$_SWIFT->Staff->GetStaffID()])) {
            $_ticketPropertiesCache = $_staffTicketPropertiesCache[$_SWIFT->Staff->GetStaffID()];
        }

        self::$_ticketPropertyCache = $_ticketPropertiesCache;
    }

    /**
     * Increment the Ticket Statuses
     *
     * @author Varun Shoor
     * @param int $_ticketStatusID The Ticket Status ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_View_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function IncrementTicketStatus($_ticketStatusID) {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_View_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_ticketStatusID)) {
            throw new SWIFT_View_Exception(SWIFT_INVALIDDATA);
        }

        if (!isset(self::$_ticketPropertyCache[self::PROPERTY_STATUS][$_ticketStatusID])) {
            self::$_ticketPropertyCache[self::PROPERTY_STATUS][$_ticketStatusID] = 0;
        }

        self::$_ticketPropertyCache[self::PROPERTY_STATUS][$_ticketStatusID]++;

        self::QueueRebuildCache();

        return true;
    }

    /**
     * Increment the Ticket Type Usage
     *
     * @author Varun Shoor
     * @param int $_ticketTypeID The Ticket Type ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_View_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function IncrementTicketType($_ticketTypeID) {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_View_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_ticketTypeID)) {
            throw new SWIFT_View_Exception(SWIFT_INVALIDDATA);
        }

        if (!isset(self::$_ticketPropertyCache[self::PROPERTY_TYPE][$_ticketTypeID])) {
            self::$_ticketPropertyCache[self::PROPERTY_TYPE][$_ticketTypeID] = 0;
        }

        self::$_ticketPropertyCache[self::PROPERTY_TYPE][$_ticketTypeID]++;

        self::QueueRebuildCache();

        return true;
    }

    /**
     * Increment the Ticket Priority Usage
     *
     * @author Varun Shoor
     * @param int $_ticketPriorityID The Ticket Priority ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_View_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function IncrementTicketPriority($_ticketPriorityID) {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_View_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_ticketPriorityID)) {
            throw new SWIFT_View_Exception(SWIFT_INVALIDDATA);
        }

        if (!isset(self::$_ticketPropertyCache[self::PROPERTY_PRIORITY][$_ticketPriorityID])) {
            self::$_ticketPropertyCache[self::PROPERTY_PRIORITY][$_ticketPriorityID] = 0;
        }

        self::$_ticketPropertyCache[self::PROPERTY_PRIORITY][$_ticketPriorityID]++;

        self::QueueRebuildCache();

        return true;
    }

    /**
     * Increment the Department Usage
     *
     * @author Varun Shoor
     * @param int $_departmentID The Department ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_View_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function IncrementDepartment($_departmentID) {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_View_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_departmentID)) {
            throw new SWIFT_View_Exception(SWIFT_INVALIDDATA);
        }

        if (!isset(self::$_ticketPropertyCache[self::PROPERTY_DEPARTMENT][$_departmentID])) {
            self::$_ticketPropertyCache[self::PROPERTY_DEPARTMENT][$_departmentID] = 0;
        }

        self::$_ticketPropertyCache[self::PROPERTY_DEPARTMENT][$_departmentID]++;

        self::QueueRebuildCache();

        return true;
    }

    /**
     * Increment the Staff Usage
     *
     * @author Varun Shoor
     * @param int $_staffID The Staff ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_View_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function IncrementStaff($_staffID) {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_View_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_staffID)) {
            throw new SWIFT_View_Exception(SWIFT_INVALIDDATA);
        }

        if (!isset(self::$_ticketPropertyCache[self::PROPERTY_STAFF][$_staffID])) {
            self::$_ticketPropertyCache[self::PROPERTY_STAFF][$_staffID] = 0;
        }

        self::$_ticketPropertyCache[self::PROPERTY_STAFF][$_staffID]++;

        self::QueueRebuildCache();

        return true;
    }

    /**
     * Increment the Ticket Link
     *
     * @author Varun Shoor
     * @param int $_ticketLinkID The Ticket Link ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_View_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function IncrementTicketLink($_ticketLinkID) {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_View_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_ticketLinkID)) {
            throw new SWIFT_View_Exception(SWIFT_INVALIDDATA);
        }

        if (!isset(self::$_ticketPropertyCache[self::PROPERTY_LINK][$_ticketLinkID])) {
            self::$_ticketPropertyCache[self::PROPERTY_LINK][$_ticketLinkID] = 0;
        }

        self::$_ticketPropertyCache[self::PROPERTY_LINK][$_ticketLinkID]++;

        self::QueueRebuildCache();

        return true;
    }

    /**
     * Increment the Ticket Flag
     *
     * @author Varun Shoor
     * @param int $_ticketFlagID The Ticket Flag ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_View_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function IncrementTicketFlag($_ticketFlagID) {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_View_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_ticketFlagID)) {
            throw new SWIFT_View_Exception(SWIFT_INVALIDDATA);
        }

        if (!isset(self::$_ticketPropertyCache[self::PROPERTY_FLAG][$_ticketFlagID])) {
            self::$_ticketPropertyCache[self::PROPERTY_FLAG][$_ticketFlagID] = 0;
        }

        self::$_ticketPropertyCache[self::PROPERTY_FLAG][$_ticketFlagID]++;

        self::QueueRebuildCache();

        return true;
    }

    /**
     * Increment the Bayesian Category
     *
     * @author Varun Shoor
     * @param int $_bayesCategoryID The Bayesian Category ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_View_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function IncrementBayesian($_bayesCategoryID) {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_View_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_bayesCategoryID)) {
            throw new SWIFT_View_Exception(SWIFT_INVALIDDATA);
        }

        if (!isset(self::$_ticketPropertyCache[self::PROPERTY_BAYES][$_bayesCategoryID])) {
            self::$_ticketPropertyCache[self::PROPERTY_BAYES][$_bayesCategoryID] = 0;
        }

        self::$_ticketPropertyCache[self::PROPERTY_BAYES][$_bayesCategoryID]++;

        self::QueueRebuildCache();

        return true;
    }

    /**
     * Get top ticket type id list
     *
     * @author Varun Shoor
     * @param mixed $_itemType The Item Type
     * @param int $_length The # of Items to Return
     * @return array The Ticket ID List
     * @throws SWIFT_View_Exception If the Class is not Loaded
     */
    public function GetTopTicketItems($_itemType, $_length = 3) {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_View_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset(self::$_ticketPropertyCache[$_itemType]) || !_is_array(self::$_ticketPropertyCache[$_itemType])) {
            return array();
        }

        arsort(self::$_ticketPropertyCache[$_itemType]);

        return array_keys(array_slice(self::$_ticketPropertyCache[$_itemType], 0, $_length, true));
    }

    /**
     * Queue the Rebuild Cache function
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function QueueRebuildCache() {
        if (self::$_rebuildCacheQueued) {
            return true;
        }

        self::$_rebuildCacheQueued = true;

        SWIFT::Shutdown('Tickets\Library\View\SWIFT_TicketViewPropertyManager', 'RebuildCache', -1, false);

        return true;
    }

    /**
     * Rebuild the Ticket Property Cache
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RebuildCache() {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_SWIFT->Staff instanceof SWIFT_Staff) {
            return false;
        }

        $_staffTicketPropertiesCache = $_SWIFT->Cache->Get('staffticketpropertiescache');
        if (!$_staffTicketPropertiesCache) {
            $_staffTicketPropertiesCache = array();
        }

        if (!isset($_staffTicketPropertiesCache[$_SWIFT->Staff->GetStaffID()])) {
            $_staffTicketPropertiesCache[$_SWIFT->Staff->GetStaffID()] = array();
        }

        $_staffTicketPropertiesCache[$_SWIFT->Staff->GetStaffID()] = self::$_ticketPropertyCache;

        $_SWIFT->Cache->Update('staffticketpropertiescache', $_staffTicketPropertiesCache);

        return true;
    }
}
