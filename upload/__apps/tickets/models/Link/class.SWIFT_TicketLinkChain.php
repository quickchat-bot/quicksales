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

namespace Tickets\Models\Link;

use SWIFT;
use SWIFT_Exception;
use SWIFT_Model;
use Tickets\Models\Ticket\SWIFT_Ticket;

/**
 * The Ticket Link Chain Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_TicketLinkChain extends SWIFT_Model {
    const TABLE_NAME        =    'ticketlinkchains';
    const PRIMARY_KEY        =    'ticketlinkchainid';

    const TABLE_STRUCTURE    =    "ticketlinkchainid I PRIMARY AUTO NOTNULL,
                                dateline I DEFAULT '0' NOTNULL,
                                ticketid I DEFAULT '0' NOTNULL,
                                ticketlinktypeid I DEFAULT '0' NOTNULL,
                                chainhash C(50) DEFAULT '' NOTNULL";

    const INDEX_1            =    'chainhash';
    const INDEX_2            =    'ticketid';
    const INDEX_3            =    'ticketlinktypeid';


    protected $_dataStore = array();

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param int $_ticketLinkChainID Ticket Link Chain ID
     * @throws SWIFT_Exception If the Record could not be loaded
     */
    public function __construct($_ticketLinkChainID) {
        parent::__construct();

        if (!$this->LoadData($_ticketLinkChainID)) {
            throw new SWIFT_Exception('Failed to load Ticket Link Chain ID: ' . $_ticketLinkChainID);
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

        $this->Database->AutoExecute(TABLE_PREFIX . 'ticketlinkchains', $this->GetUpdatePool(), 'UPDATE', "ticketlinkchainid = '" .
                (int) ($this->GetTicketLinkChainID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Ticket Link Chain ID
     *
     * @author Varun Shoor
     * @return mixed "ticketlinkchainid" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetTicketLinkChainID() {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['ticketlinkchainid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param int $_ticketLinkChainID The Ticket Link Chain ID
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_ticketLinkChainID) {
        $_dataStore = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "ticketlinkchains WHERE ticketlinkchainid = '" .
                $_ticketLinkChainID . "'");
        if (isset($_dataStore['ticketlinkchainid']) && !empty($_dataStore['ticketlinkchainid'])) {
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
     * Create a new Ticket Link Chain
     *
     * @author Varun Shoor
     * @param int $_ticketID The Ticket ID to Link
     * @param int $_ticketLinkTypeID The Ticket Link Type ID
     * @param string $_chainHash The Chain Hash String
     * @return int Ticket Link Chain ID
     * @throws SWIFT_Exception If Invalid Data is Provided or If the Object could not be created
     */
    protected static function Create($_ticketID, $_ticketLinkTypeID, $_chainHash) {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_ticketID) || empty($_ticketLinkTypeID) || empty($_chainHash)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'ticketlinkchains', array('ticketid' => $_ticketID,
            'ticketlinktypeid' => $_ticketLinkTypeID, 'chainhash' => $_chainHash, 'dateline' => DATENOW), 'INSERT');
        $_ticketLinkChainID = $_SWIFT->Database->Insert_ID();

        if (!$_ticketLinkChainID) {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        return $_ticketLinkChainID;
    }

    /**
     * Create a linked chain
     *
     * @author Varun Shoor
     * @param int $_ticketLinkTypeID The Ticket Link Type ID
     * @param array $_ticketIDList The Ticket ID List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function CreateChain($_ticketLinkTypeID, $_ticketIDList) {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_ticketIDList)) {
            return false;
        }

        $_chainHash = BuildHash();

        foreach ($_ticketIDList as $_key => $_ticketID) {
            self::Create($_ticketID, $_ticketLinkTypeID, $_chainHash);
        }

        return true;
    }

    /**
     * Get the linked ticket id list
     *
     * @author Varun Shoor
     * @param int $_ticketID The Ticket ID
     * @return array
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function GetLinkedTicketIDContainer($_ticketID) {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_ticketID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        // First see if theres an entry
        $_returnChainContainer = $_chainHashList = array();
        $_SWIFT->Database->Query("SELECT chainhash FROM " . TABLE_PREFIX . "ticketlinkchains WHERE ticketid  = '" . $_ticketID . "'");
        while ($_SWIFT->Database->NextRecord()) {
            $_chainHashList[] = $_SWIFT->Database->Record['chainhash'];
        }

        if (!count($_chainHashList)) {
            return array();
        }

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketlinkchains WHERE chainhash IN (" . BuildIN($_chainHashList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_returnChainContainer[$_SWIFT->Database->Record['ticketlinktypeid']][] = $_SWIFT->Database->Record['ticketid'];
        }

        return $_returnChainContainer;
    }

    /**
     * Delete the link chain record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete() {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($this->GetTicketLinkChainID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of Ticket Link Chain ID's
     *
     * @author Varun Shoor
     * @param array $_ticketLinkChainIDList The Ticket Link Chain ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_ticketLinkChainIDList) {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_ticketLinkChainIDList)) {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "ticketlinkchains WHERE ticketlinkchainid IN (" . BuildIN($_ticketLinkChainIDList) .
                ")");

        return true;
    }

    /**
     * Delete the chains on a ticket id list.
     *
     * @author Varun Shoor
     * @param array $_ticketIDList The Ticket ID List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function DeleteOnTicket($_ticketIDList) {
        $_SWIFT = SWIFT::GetInstance();

        $_finalTicketIDList = $_ticketIDList;

        // First get all the chains for the given ticket ids
        $_finalTicketLinkChainIDList = $_chainHashList = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketlinkchains WHERE ticketid IN (" . BuildIN($_ticketIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_finalTicketLinkChainIDList[] = (int) ($_SWIFT->Database->Record['ticketlinkchainid']);
            $_chainHashList[] = $_SWIFT->Database->Record['chainhash'];
        }

        if (!count($_finalTicketLinkChainIDList)) {
            return false;
        }

        // Delete the links
        self::DeleteList($_finalTicketLinkChainIDList);

        // Now get the chain hashes and see which ones have 1 item
        $_chainHashDeleteList = array();
        $_SWIFT->Database->Query("SELECT COUNT(*) AS totalitems, chainhash FROM " . TABLE_PREFIX . "ticketlinkchains WHERE chainhash IN (" .
                BuildIN($_chainHashList) . ") GROUP BY chainhash");
        while ($_SWIFT->Database->NextRecord()) {
            if ($_SWIFT->Database->Record['totalitems'] <= 1) {
                $_chainHashDeleteList[] = $_SWIFT->Database->Record['chainhash'];
            }
        }

        if (!count($_chainHashDeleteList)) {
            SWIFT_Ticket::RecalculateTicketLinkProperty($_finalTicketIDList);

            return false;
        }

        // Get all associated ticket link chains for the given chain hashes
        $_otherTicketLinkChainIDList = array();
        $_SWIFT->Database->Query("SELECT ticketlinkchainid, ticketid FROM " . TABLE_PREFIX . "ticketlinkchains WHERE chainhash IN (" .
                BuildIN($_chainHashDeleteList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_otherTicketLinkChainIDList[] = $_SWIFT->Database->Record['ticketlinkchainid'];

            if (!in_array($_SWIFT->Database->Record['ticketid'], $_finalTicketIDList)) {
                $_finalTicketIDList[] = $_SWIFT->Database->Record['ticketid'];
            }
        }

        if (!count($_otherTicketLinkChainIDList)) {
            SWIFT_Ticket::RecalculateTicketLinkProperty($_finalTicketIDList);

            return false;
        }

        self::DeleteList($_otherTicketLinkChainIDList);

        SWIFT_Ticket::RecalculateTicketLinkProperty($_finalTicketIDList);

        return true;
    }

    /**
     * Delete all items associated with the given ticket link type ids
     *
     * @author Varun Shoor
     * @param array $_ticketLinkTypeIDList The Ticket Link Type ID List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function DeleteOnTicketLinkType($_ticketLinkTypeIDList) {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_ticketLinkTypeIDList)) {
            return false;
        }

        $_finalTicketLinkChainIDList = $_finalTicketIDList = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketlinkchains WHERE ticketlinktypeid IN (" . BuildIN($_ticketLinkTypeIDList) .
                ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_finalTicketLinkChainIDList[] = $_SWIFT->Database->Record['ticketlinkchainid'];

            $_finalTicketIDList[] = $_SWIFT->Database->Record['ticketid'];
        }

        if (!count($_finalTicketLinkChainIDList)) {
            return false;
        }

        self::DeleteList($_finalTicketLinkChainIDList);

        SWIFT_Ticket::RecalculateTicketLinkProperty($_finalTicketIDList);

        return true;
    }

}
?>
