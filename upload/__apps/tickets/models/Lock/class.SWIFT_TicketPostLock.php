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

namespace Tickets\Models\Lock;

use SWIFT;
use SWIFT_Data;
use SWIFT_DataID;
use SWIFT_DataStore;
use SWIFT_Exception;
use SWIFT_Model;
use Base\Models\Staff\SWIFT_Staff;
use Tickets\Models\Ticket\SWIFT_Ticket;

/**
 * The Ticket Post Lock Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_TicketPostLock extends SWIFT_Model
{
    const TABLE_NAME  = 'ticketpostlocks';
    const PRIMARY_KEY = 'ticketpostlockid';

    const TABLE_STRUCTURE = "ticketpostlockid I PRIMARY AUTO NOTNULL,
                                ticketid I DEFAULT '0' NOTNULL,
                                staffid I DEFAULT '0' NOTNULL,
                                dateline I DEFAULT '0' NOTNULL,
                                contents X";

    const INDEX_1 = 'ticketid, staffid';
    const INDEX_2 = 'dateline';

    const TICKETPOSTLOCK_ITEMS_LIMIT = 1000;


    protected $_dataStore = array();

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param int $_ticketPostLockID The Ticket Post Lock ID
     * @throws SWIFT_Lock_Exception If the Record could not be loaded
     */
    public function __construct($_ticketPostLockID)
    {
        parent::__construct();

        if (!$this->LoadData($_ticketPostLockID)) {
            throw new SWIFT_Lock_Exception('Failed to load Ticket Post Lock ID: ' . $_ticketPostLockID);
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
     * @throws SWIFT_Lock_Exception If the Class is not Loaded
     */
    public function ProcessUpdatePool()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'ticketpostlocks', $this->GetUpdatePool(), 'UPDATE', "ticketpostlockid = '" . (int) ($this->GetTicketPostLockID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Ticket Post Lock ID
     *
     * @author Varun Shoor
     * @return mixed "ticketpostlockid" on Success, "false" otherwise
     * @throws SWIFT_Lock_Exception If the Class is not Loaded
     */
    public function GetTicketPostLockID()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Lock_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['ticketpostlockid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param int $_ticketPostLockID The Ticket Post Lock ID
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_ticketPostLockID)
    {
        $_dataStore = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "ticketpostlocks WHERE ticketpostlockid = '" . $_ticketPostLockID . "'");
        if (isset($_dataStore['ticketpostlockid']) && !empty($_dataStore['ticketpostlockid']))
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
     * @throws SWIFT_Lock_Exception If the Class is not Loaded
     */
    public function GetDataStore()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Lock_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore;
    }

    /**
     * Retrieves a Property Value from Data Store
     *
     * @author Varun Shoor
     * @param string $_key The Key Identifier
     * @return mixed Property Data on Success, "false" otherwise
     * @throws SWIFT_Lock_Exception If the Class is not Loaded
     */
    public function GetProperty($_key)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Lock_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_Lock_Exception(SWIFT_INVALIDDATA);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Create a new Ticket Post Lock
     *
     * @author Varun Shoor
     * @param SWIFT_Ticket $_SWIFT_TicketObject
     * @param SWIFT_Staff $_SWIFT_StaffObject
     * @param string $_postContents
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function Replace(SWIFT_Ticket $_SWIFT_TicketObject, SWIFT_Staff $_SWIFT_StaffObject, $_postContents)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded() || !$_SWIFT_StaffObject instanceof SWIFT_Staff || !$_SWIFT_StaffObject->GetIsClassLoaded())
        {
            throw new SWIFT_Lock_Exception(SWIFT_INVALIDDATA);
        }

        $_ticketPostLockContainer = $_SWIFT->Database->QueryFetch("SELECT ticketpostlockid, contents FROM " . TABLE_PREFIX . "ticketpostlocks WHERE ticketid = '" . (int) ($_SWIFT_TicketObject->GetTicketID()) . "' AND staffid = '" . (int) ($_SWIFT_StaffObject->GetStaffID()) . "'");
        if (isset($_ticketPostLockContainer['ticketpostlockid']))
        {
            // If we post contents are empty, we override it with old value
            if (trim($_postContents) == '') {
                $_postContents = $_ticketPostLockContainer['contents'];
            }

            $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'ticketpostlocks', ['ticketid' => (int) ($_SWIFT_TicketObject->GetTicketID()),
                'staffid' => (int) ($_SWIFT_StaffObject->GetStaffID()), 'dateline' => DATENOW, 'contents' => $_postContents],
                    'UPDATE', "ticketpostlockid = '" . (int) ($_ticketPostLockContainer['ticketpostlockid']) . "'");
        } else {
            // Before inserting a lock, we need to check if a reply was made recently.. its possibility its a stale loop
            $_threshold = DATENOW - 120;

            $_ticketPostContainer = $_SWIFT->Database->QueryFetch("SELECT dateline FROM " . TABLE_PREFIX . "ticketposts
                WHERE ticketid = '" . $_SWIFT_TicketObject->GetTicketID() . "' AND staffid = '" . $_SWIFT_StaffObject->GetStaffID() . "' ORDER BY ticketpostid DESC");
            if (isset($_ticketPostContainer['dateline']) && $_ticketPostContainer['dateline'] > $_threshold) {
                return true;
            }

            $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'ticketpostlocks', ['ticketid' => (int) ($_SWIFT_TicketObject->GetTicketID()),
                'staffid' => (int) ($_SWIFT_StaffObject->GetStaffID()), 'dateline' => DATENOW, 'contents' => $_postContents], 'INSERT');
        }

        return true;
    }

    /**
     * Delete the Ticket Post Lock records
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Lock_Exception If the Class is not Loaded
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Lock_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($this->GetTicketPostLockID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of Ticket Post Locks
     *
     * @author Varun Shoor
     * @param array $_ticketPostLockIDList The List of Ticket Post locks
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_ticketPostLockIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_ticketPostLockIDList))
        {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "ticketpostlocks WHERE ticketpostlockid IN (" . BuildIN($_ticketPostLockIDList) . ")");

        return true;
    }

    /**
     * Delete on a list of ticket ids
     *
     * @author Jamie Edwards
     * @param array $_ticketIDList List of ticket IDs to purge locks for
     * @param int $_staffID Optional ID of staff user to purge locks for (selective purge)
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteOnTicket($_ticketIDList, $_staffID = null)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_ticketIDList))
        {
            return false;
        }

        $_ticketPostLockIDList = array();

        if ($_staffID)
        {
            $_SWIFT->Database->Query("SELECT ticketpostlockid FROM " . TABLE_PREFIX . "ticketpostlocks WHERE ticketid IN (" . BuildIN($_ticketIDList) . ") AND staffid = '" . $_staffID . "'");
        }
        else
        {
            $_SWIFT->Database->Query("SELECT ticketpostlockid FROM " . TABLE_PREFIX . "ticketpostlocks WHERE ticketid IN (" . BuildIN($_ticketIDList) . ")");
        }

        while ($_SWIFT->Database->NextRecord())
        {
            $_ticketPostLockIDList[] = (int) ($_SWIFT->Database->Record['ticketpostlockid']);
        }

        if (!count($_ticketPostLockIDList))
        {
            return false;
        }

        self::DeleteList($_ticketPostLockIDList);

        return true;
    }

    /**
     * Retrieve the Ticket Post Locks on a Ticket
     *
     * @author Varun Shoor
     * @param SWIFT_Ticket $_SWIFT_TicketObject SWIFT_Ticket Object Pointer
     * @return array The Ticket Post Lock Container
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveOnTicket(SWIFT_Ticket $_SWIFT_TicketObject)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_ticketPostLockContainer = array();

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketpostlocks WHERE ticketid = '" . (int) ($_SWIFT_TicketObject->GetTicketID()) . "'");
        while ($_SWIFT->Database->NextRecord())
        {
            $_dateThreshold = DATENOW - $_SWIFT->Settings->Get('t_plockthreshold');
            if ($_SWIFT->Database->Record['dateline'] < $_dateThreshold)
            {
                continue;
            }

            $_ticketPostLockContainer[$_SWIFT->Database->Record['ticketpostlockid']] = $_SWIFT->Database->Record;
        }

        return $_ticketPostLockContainer;
    }

    /**
     * Retrieve the contents on ticket and staff
     *
     * @author Varun Shoor
     * @param SWIFT_Ticket $_SWIFT_TicketObject
     * @param SWIFT_Staff $_SWIFT_StaffObject
     * @return string Lock Contents
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveOnTicketAndStaff(SWIFT_Ticket $_SWIFT_TicketObject, SWIFT_Staff $_SWIFT_StaffObject)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_ticketPostLockContainer = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "ticketpostlocks
            WHERE ticketid = '" . (int) ($_SWIFT_TicketObject->GetTicketID()) . "' AND staffid = '" . (int) ($_SWIFT_StaffObject->GetStaffID()) . "'
            ORDER BY ticketpostlockid DESC");

        if (isset($_ticketPostLockContainer['contents'])) {
            return $_ticketPostLockContainer['contents'];
        }

        return '';
    }

    /**
     * Cleanup locks older than 1 day
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function Cleanup() {
        $_SWIFT = SWIFT::GetInstance();

        $_threshold = DATENOW - 86400;

        $_ticketPostLockIDList = array();
        $_SWIFT->Database->QueryLimit("SELECT ticketpostlockid FROM " . TABLE_PREFIX . "ticketpostlocks WHERE dateline < '" .  ($_threshold) . "'", self::TICKETPOSTLOCK_ITEMS_LIMIT);
        while ($_SWIFT->Database->NextRecord()) {
            $_ticketPostLockIDList[] = $_SWIFT->Database->Record['ticketpostlockid'];
        }

        if (!count($_ticketPostLockIDList)) {
            return false;
        }

        self::DeleteList($_ticketPostLockIDList);

        return true;
    }
}
?>
