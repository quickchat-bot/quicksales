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

namespace Tickets\Models\Watcher;

use SWIFT;
use SWIFT_Exception;
use SWIFT_Model;
use Base\Models\Staff\SWIFT_Staff;
use Tickets\Models\Ticket\SWIFT_Ticket;

/**
 * The Ticket Watcher Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_TicketWatcher extends SWIFT_Model {
    const TABLE_NAME        =    'ticketwatchers';
    const PRIMARY_KEY        =    'ticketwatcherid';

    const TABLE_STRUCTURE    =    "ticketwatcherid I PRIMARY AUTO NOTNULL,
                                dateline I DEFAULT '0' NOTNULL,
                                ticketid I DEFAULT '0' NOTNULL,
                                staffid I DEFAULT '0' NOTNULL";

    const INDEX_1            =    'ticketid, staffid';
    const INDEXTYPE_1        =    'UNIQUE';


    protected $_dataStore = array();

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param int $_ticketWatcherID The Ticket Watcher ID
     * @throws SWIFT_Exception If the Record could not be loaded
     */
    public function __construct($_ticketWatcherID) {
        parent::__construct();

        if (!$this->LoadData($_ticketWatcherID)) {
            throw new SWIFT_Exception('Failed to load Ticket Watcher ID: ' . $_ticketWatcherID);
        }
    }

    /**
     * Destructor
     *
     * @author Varun Shoor
     */
    public function __destruct() {
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
    public function ProcessUpdatePool() {
        if (!$this->GetIsClassLoaded()) {
            return false;
        } else if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'ticketwatchers', $this->GetUpdatePool(), 'UPDATE', "ticketwatcherid = '" .
                (int) ($this->GetTicketWatcherID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Ticket Watcher ID
     *
     * @author Varun Shoor
     * @return mixed "ticketwatcherid" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetTicketWatcherID() {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['ticketwatcherid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param int $_ticketWatcherID The Ticket Watcher ID
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_ticketWatcherID) {
        $_dataStore = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "ticketwatchers WHERE ticketwatcherid = '" .
                $_ticketWatcherID . "'");
        if (isset($_dataStore['ticketwatcherid']) && !empty($_dataStore['ticketwatcherid'])) {
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
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetDataStore() {
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
    public function GetProperty($_key) {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Retrieve the watcher list on a given ticket id
     *
     * @author Varun Shoor
     * @param SWIFT_Ticket $_SWIFT_TicketObject The SWIFT_Ticket Object Pointer
     * @return array Ticket Watch Container
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveOnTicket(SWIFT_Ticket $_SWIFT_TicketObject) {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_ticketWatchContainer = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketwatchers WHERE ticketid = '" . (int) ($_SWIFT_TicketObject->GetTicketID()) . "'");
        while ($_SWIFT->Database->NextRecord()) {
            if (isset($_SWIFT->Database->Record['staffid'])) {
                $_ticketWatchContainer[$_SWIFT->Database->Record['staffid']] = $_SWIFT->Database->Record;
            }
        }

        return $_ticketWatchContainer;
    }

    /**
     * Create a new Ticket Watcher entry
     *
     * @author Varun Shoor
     * @param SWIFT_Ticket $_SWIFT_TicketObject The SWIFT_Ticket Object Pointer
     * @param SWIFT_Staff $_SWIFT_StaffObject The SWIFT_Staff Object Pointer
     * @return mixed "Ticket Watcher ID" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function Create(SWIFT_Ticket $_SWIFT_TicketObject, SWIFT_Staff $_SWIFT_StaffObject) {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded() ||
                !$_SWIFT_StaffObject instanceof SWIFT_Staff || !$_SWIFT_StaffObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }


        $_SWIFT->Database->Replace(TABLE_PREFIX . 'ticketwatchers', array('ticketid' => (int) ($_SWIFT_TicketObject->GetTicketID()),
            'staffid' => (int) ($_SWIFT_StaffObject->GetStaffID()), 'dateline' => DATENOW), array('ticketid', 'staffid'));
        $_ticketWatcherID = $_SWIFT->Database->Insert_ID();

        if (!$_ticketWatcherID) {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        $_SWIFT_TicketObject->MarkAsWatched();

        return $_ticketWatcherID;
    }

    /**
     * Delete the Ticket Watcher record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete() {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($this->GetTicketWatcherID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of Ticket Watchers
     *
     * @author Varun Shoor
     * @param array $_ticketWatcherIDList
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_ticketWatcherIDList) {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_ticketWatcherIDList)) {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "ticketwatchers WHERE ticketwatcherid IN (" . BuildIN($_ticketWatcherIDList) . ")");

        return true;
    }

    /**
     * Delete the ticket watchers based on ticket id list
     *
     * @author Varun Shoor
     * @param array $_ticketIDList The Ticket ID List
     * @param array $_staffIDList (OPTIONAL) The Staff ID List to Filter by
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function DeleteOnTicket($_ticketIDList, $_staffIDList = array()) {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_ticketIDList)) {
            return false;
        }

        $_staffFilter = '';
        if (_is_array($_staffIDList)) {
            $_staffFilter = " AND staffid IN (" . BuildIN($_staffIDList) . ")";
        }

        $_ticketWatcherIDList = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketwatchers WHERE ticketid IN (" . BuildIN($_ticketIDList) . ")" . $_staffFilter);
        while ($_SWIFT->Database->NextRecord()) {
            $_ticketWatcherIDList[] = (int) ($_SWIFT->Database->Record['ticketwatcherid']);
        }

        if (!count($_ticketWatcherIDList)) {
            return false;
        }

        self::DeleteList($_ticketWatcherIDList);

        return true;
    }
}
?>
