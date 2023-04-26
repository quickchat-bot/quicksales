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

namespace Tickets\Models\Ticket;

use SWIFT;
use SWIFT_Exception;
use SWIFT_Model;

/**
 * The Linked Table Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_TicketLinkedTable extends SWIFT_Model {
    const TABLE_NAME        =    'ticketlinkedtables';
    const PRIMARY_KEY        =    'ticketlinkedtableid';

    const TABLE_STRUCTURE    =    "ticketlinkedtableid I PRIMARY AUTO NOTNULL,
                                dateline I DEFAULT '0' NOTNULL,
                                linktype I2 DEFAULT '0' NOTNULL,
                                linktypeid I DEFAULT '0' NOTNULL,
                                ticketid I DEFAULT '0' NOTNULL";

    const INDEX_1            =    'ticketid, linktype';
    const INDEX_2            =    'linktype, linktypeid';


    protected $_dataStore = array();

    // Core Constants
    const LINKTYPE_WORKFLOW = 1;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param int $_ticketLinkedTableID The Ticket Linked Table ID (PRIMARY KEY)
     * @throws SWIFT_Exception If the Record could not be loaded
     */
    public function __construct($_ticketLinkedTableID) {
        parent::__construct();

        if (!$this->LoadData($_ticketLinkedTableID)) {
            throw new SWIFT_Exception('Failed to load Ticket Linked Table ID: ' . $_ticketLinkedTableID);
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

        $this->Database->AutoExecute(TABLE_PREFIX . 'ticketlinkedtables', $this->GetUpdatePool(), 'UPDATE', "ticketlinkedtableid = '" .
                (int) ($this->GetTicketLinkedTableID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Ticket Linked Table ID
     *
     * @author Varun Shoor
     * @return mixed "ticketlinkedtableid" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetTicketLinkedTableID() {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['ticketlinkedtableid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param int $_ticketLinkedTableID The Ticket Linked Table ID
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_ticketLinkedTableID) {
        $_dataStore = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "ticketlinkedtables WHERE ticketlinkedtableid = '" .
                $_ticketLinkedTableID . "'");
        if (isset($_dataStore['ticketlinkedtableid']) && !empty($_dataStore['ticketlinkedtableid'])) {
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
     * Check to see if its a valid link type
     *
     * @author Varun Shoor
     * @param mixed $_linkType The Link Type
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function IsValidLinkType($_linkType) {
        $_SWIFT = SWIFT::GetInstance();

        if ($_linkType == self::LINKTYPE_WORKFLOW) {
            return true;
        }

        return false;
    }

    /**
     * Create a new Ticket Table Link
     *
     * @author Varun Shoor
     * @param SWIFT_Ticket $_SWIFT_TicketObject The SWIFT_Ticket object pointer
     * @param mixed $_linkType The Link Type
     * @param int $_linkTypeID The Link Type ID
     * @return int "$_ticketLinkedTableID" (INT) on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function Create(SWIFT_Ticket $_SWIFT_TicketObject, $_linkType, $_linkTypeID) {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded() || !self::IsValidLinkType($_linkType) ||
                empty($_linkTypeID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'ticketlinkedtables', array('ticketid' => (int) ($_SWIFT_TicketObject->GetTicketID()),
                'dateline' => DATENOW, 'linktype' => (int) ($_linkType), 'linktypeid' => $_linkTypeID), 'INSERT');
        $_ticketLinkedTableID = $_SWIFT->Database->Insert_ID();

        if (!$_ticketLinkedTableID) {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        return $_ticketLinkedTableID;
    }

    /**
     * Create the record if it doesnt exist
     *
     * @author Varun Shoor
     * @param SWIFT_Ticket $_SWIFT_TicketObject The SWIFT_Ticket object pointer
     * @param array $_linkTypeContainer The Link Type Container. array(linktype => array(linktypeid, ...))
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function CreateIfNotExists(SWIFT_Ticket $_SWIFT_TicketObject, $_linkTypeContainer) {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded() || !_is_array($_linkTypeContainer)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        // First retrieve the existing ticket links..
        $_ticketLinkTypeContainer = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketlinkedtables WHERE ticketid = '" .
                (int) ($_SWIFT_TicketObject->GetTicketID()) . "'");
        while ($_SWIFT->Database->NextRecord()) {
            $_ticketLinkTypeContainer[$_SWIFT->Database->Record['linktype']][] = $_SWIFT->Database->Record['linktypeid'];
        }

        // Itterate through the links provided and see if they already exists.. if they do, then dont create the link
        $_finalCreateLinkTypeContainer = array();
        foreach ($_linkTypeContainer as $_linkType => $_linkTypeIDList) {
            foreach ($_linkTypeIDList as $_key => $_linkTypeID) {
                if (isset($_ticketLinkTypeContainer[$_linkType]) && in_array($_linkTypeID, $_ticketLinkTypeContainer[$_linkType])) {
                    // Record exists!
                } else {
                    self::Create($_SWIFT_TicketObject, $_linkType, $_linkTypeID);
                }
            }
        }

        return true;
    }

    /**
     * Delete ticket linked table record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete() {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($this->GetTicketLinkedTableID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of Ticket Linked Table's
     *
     * @author Varun Shoor
     * @param array $_ticketLinkedTableIDList The Ticket Linked Table ID List Array
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_ticketLinkedTableIDList) {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_ticketLinkedTableIDList)) {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "ticketlinkedtables WHERE ticketlinkedtableid IN (" .
                BuildIN($_ticketLinkedTableIDList) . ")");

        return true;
    }

    /**
     * Retrieve the linked table values based on the given ticket object
     *
     * @author Varun Shoor
     * @param SWIFT_Ticket $_SWIFT_TicketObject The SWIFT_Ticket Object Pointer
     * @return array The Link Container
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveOnTicket(SWIFT_Ticket $_SWIFT_TicketObject) {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_resultContainer = array();

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketlinkedtables WHERE ticketid = '" . (int) ($_SWIFT_TicketObject->GetTicketID()) . "' ORDER BY ticketlinkedtableid ASC");
        while ($_SWIFT->Database->NextRecord()) {
            $_resultContainer[$_SWIFT->Database->Record['linktype']][$_SWIFT->Database->Record['ticketlinkedtableid']] = $_SWIFT->Database->Record;
        }

        return $_resultContainer;
    }

    /**
     * Delete the records based on list of ticket ids
     *
     * @author Varun Shoor
     * @param array $_ticketIDList The Ticket ID List
     * @param mixed $_linkType (OPTIONAL) The Link Type
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function DeleteOnTicket($_ticketIDList, $_linkType = false) {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_ticketIDList)) {
            return false;
        }

        $_sqlExtended = '';
        if (!empty($_linkType) && self::IsValidLinkType($_linkType)) {
            $_sqlExtended = " AND linktype = '" . (int) ($_linkType) . "'";
        }

        $_ticketLinkedTableIDList = array();
        $_SWIFT->Database->Query("SELECT ticketlinkedtableid FROM " . TABLE_PREFIX . "ticketlinkedtables WHERE ticketid IN (" .
                BuildIN($_ticketIDList) . ")" . $_sqlExtended);
        while ($_SWIFT->Database->NextRecord()) {
            $_ticketLinkedTableIDList[] = $_SWIFT->Database->Record['ticketlinkedtableid'];
        }

        if (!count($_ticketLinkedTableIDList)) {
            return false;
        }

        self::DeleteList($_ticketLinkedTableIDList);

        return true;
    }

    /**
     * Delete the records based on list of linktype ids
     *
     * @author Varun Shoor
     * @param mixed $_linkType The Link Type
     * @param array $_linkTypeIDList The Link Type ID List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function DeleteOnLinkList($_linkType, $_linkTypeIDList) {
        $_SWIFT = SWIFT::GetInstance();

        if (!self::IsValidLinktype($_linkType)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if (!_is_array($_linkTypeIDList)) {
            return false;
        }

        $_ticketLinkedTableIDList = array();
        $_SWIFT->Database->Query("SELECT ticketlinkedtableid FROM " . TABLE_PREFIX . "ticketlinkedtables WHERE linktype = '" . (int) ($_linkType) .
                "' AND linktypeid IN (" . BuildIN($_linkTypeIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_ticketLinkedTableIDList[] = $_SWIFT->Database->Record['ticketlinkedtableid'];
        }

        if (!count($_ticketLinkedTableIDList)) {
            return false;
        }

        self::DeleteList($_ticketLinkedTableIDList);

        return true;
    }
}
?>
