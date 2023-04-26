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

namespace Tickets\Models\Merge;

use SWIFT;
use SWIFT_Exception;
use SWIFT_Model;
use Tickets\Models\Ticket\SWIFT_Ticket;

/**
 * The Ticket Merge Log Model
 *
 * @author Varun Shoor
 */
class SWIFT_TicketMergeLog extends SWIFT_Model {
    const TABLE_NAME        =    'ticketmergelog';
    const PRIMARY_KEY        =    'ticketmergelogid';

    const TABLE_STRUCTURE    =    "ticketmergelogid I PRIMARY AUTO NOTNULL,
                                oldticketid I DEFAULT '0' NOTNULL,
                                oldticketmaskid C(20) DEFAULT '' NOTNULL,
                                ticketid I DEFAULT '0' NOTNULL,
                                staffid I DEFAULT '0' NOTNULL,
                                dateline I DEFAULT '0' NOTNULL";

    const INDEX_1            =    'oldticketid';
    const INDEX_2            =    'oldticketmaskid';
    const INDEX_3            =    'ticketid';


    protected $_dataStore = array();

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param int $_ticketMergeLogID The Ticket Merge Log ID
     * @throws SWIFT_Exception If the Record could not be loaded
     */
    public function __construct($_ticketMergeLogID) {
        parent::__construct();

        if (!$this->LoadData($_ticketMergeLogID)) {
            throw new SWIFT_Exception('Failed to load Ticket Merge Log ID: ' . $_ticketMergeLogID);
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
        }

        if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'ticketmergelog', $this->GetUpdatePool(), 'UPDATE', "ticketmergelogid = '" .
                (int) ($this->GetTicketMergeLogID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Ticket Merge Log ID
     *
     * @author Varun Shoor
     * @return mixed "ticketmergelogid" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetTicketMergeLogID() {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['ticketmergelogid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param int $_ticketMergeLogID The Ticket Merge Log ID
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_ticketMergeLogID) {
        $_dataStore = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "ticketmergelog WHERE ticketmergelogid = '" .
                $_ticketMergeLogID . "'");
        if (isset($_dataStore['ticketmergelogid']) && !empty($_dataStore['ticketmergelogid'])) {
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
     * Create a new Ticket Merge Log
     *
     * @author Varun Shoor
     * @param SWIFT_Ticket $_SWIFT_ParentTicketObject The Parent Ticket Object
     * @param int $_oldTicketID The Old Numerical Ticket ID
     * @param string $_oldTicketMaskID The Old Ticket Mask ID
     * @param int $_staffID The Staff ID
     * @return int Ticket Merge Log ID
     * @throws SWIFT_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function Create(SWIFT_Ticket $_SWIFT_ParentTicketObject, $_oldTicketID, $_oldTicketMaskID, $_staffID = 0) {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_SWIFT_ParentTicketObject instanceof SWIFT_Ticket || !$_SWIFT_ParentTicketObject->GetIsClassLoaded() || empty($_oldTicketID) ||
                empty($_oldTicketMaskID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'ticketmergelog', array('ticketid' => (int) ($_SWIFT_ParentTicketObject->GetTicketID()),
            'oldticketid' => $_oldTicketID, 'oldticketmaskid' => $_oldTicketMaskID, 'staffid' => $_staffID, 'dateline' => DATENOW),
                'INSERT');
        $_ticketMergeLogID = $_SWIFT->Database->Insert_ID();

        if (!$_ticketMergeLogID) {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        return $_ticketMergeLogID;
    }

    /**
     * Delete Ticket Merge Log record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete() {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($this->GetTicketMergeLogID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of Ticket Merge Log's
     *
     * @author Varun Shoor
     * @param array $_ticketMergeLogIDList The Ticket Merge Log ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_ticketMergeLogIDList) {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_ticketMergeLogIDList)) {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "ticketmergelog WHERE ticketmergelogid IN (" . BuildIN($_ticketMergeLogIDList) .
                ")");

        return true;
    }

    /**
     * Delete the merge log on the ticket id
     *
     * @author Varun Shoor
     * @param array $_ticketIDList The Ticket ID List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function DeleteOnTicket($_ticketIDList) {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_ticketIDList)) {
            return false;
        }

        $_ticketMergeLogIDList = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketmergelog WHERE ticketid IN (" . BuildIN($_ticketIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_ticketMergeLogIDList[] = (int) ($_SWIFT->Database->Record['ticketmergelogid']);
        }

        if (!count($_ticketMergeLogIDList)) {
            return false;
        }

        self::DeleteList($_ticketMergeLogIDList);

        return true;
    }

    /**
     * Retrieve the new ticket id from old merged ticket mask id
     *
     * @author Varun Shoor
     * @param string $_ticketMaskID The Ticket MAsk ID
     * @return int "Ticket ID" on Success, "0" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function GetTicketIDFromMergedTicketMaskID($_ticketMaskID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_ticketMaskID))
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }
        /**
         * BUG FIX : Ashish Kataria <ashish.kataria@opencart.com.vn>
         *
         * SWIFT-2677 : Staff reply not updated on merged ticket
         */
        $_mergeLogContainer = $_SWIFT->Database->QueryFetch("SELECT ticketid FROM " . TABLE_PREFIX . "ticketmergelog WHERE oldticketmaskid = '" . $_ticketMaskID . "'");
        if (isset($_mergeLogContainer['ticketid']) && !empty($_mergeLogContainer['ticketid']))
        {
            $_newMergeLogTicketID = self::GetTicketIDFromMergedTicketID($_mergeLogContainer['ticketid']);
            if (!empty($_newMergeLogTicketID)) {
                return $_newMergeLogTicketID;
            }

            return $_mergeLogContainer['ticketid'];
        }

        return 0;
    }

    /**
     * Retrieve the new ticket id from the old merged ticket id
     *
     * @author Varun Shoor
     * @param int $_ticketID The Ticket ID
     * @return int "Ticket ID" on Success, "0" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function GetTicketIDFromMergedTicketID($_ticketID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_ticketID))
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_mergeLogContainer = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "ticketmergelog WHERE oldticketid = '" . $_ticketID . "'");
        if (isset($_mergeLogContainer['ticketid']) && !empty($_mergeLogContainer['ticketid']))
        {
            $_newMergeLogTicketID = self::GetTicketIDFromMergedTicketID($_mergeLogContainer['ticketid']);
            if (!empty($_newMergeLogTicketID)) {
                return $_newMergeLogTicketID;
            }

            return $_mergeLogContainer['ticketid'];
        }

        return 0;
    }

    /**
     * Retrieve the old merged ticket id from the new ticket id
     *
     * @author Parminder Singh
     * @param int $_ticketID The Ticket ID
     * @return array $_ticketIDList The Ticket ID List
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function GetMergedTicketIDFromTicketID($_ticketID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_ticketID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_ticketMergeLogIDList = array();
        $_SWIFT->Database->Query("SELECT oldticketid FROM " . TABLE_PREFIX . "ticketmergelog WHERE ticketid = " . $_ticketID);
        while ($_SWIFT->Database->NextRecord()) {
            $_ticketMergeLogIDList[] = (int) ($_SWIFT->Database->Record['oldticketid']);
        }

        if (!count($_ticketMergeLogIDList)) {
            return [];
        }

        return $_ticketMergeLogIDList;
    }
}

