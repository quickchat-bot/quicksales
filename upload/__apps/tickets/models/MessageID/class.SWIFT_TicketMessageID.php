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

namespace Tickets\Models\MessageID;

use SWIFT;
use SWIFT_Exception;
use SWIFT_Model;
use Tickets\Models\Ticket\SWIFT_Ticket;
use Tickets\Models\Ticket\SWIFT_TicketPost;

/**
 * The Ticket Message ID Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_TicketMessageID extends SWIFT_Model {
    const TABLE_NAME        =    'ticketmessageids';
    const PRIMARY_KEY        =    'ticketmessageid';

    const TABLE_STRUCTURE    =    "ticketmessageid I PRIMARY AUTO NOTNULL,
                                messageid C(17) DEFAULT '' NOTNULL,
                                ticketid I DEFAULT '0' NOTNULL,
                                ticketpostid I DEFAULT '0' NOTNULL,
                                dateline I DEFAULT '0' NOTNULL";

    const INDEX_1            =    'messageid, ticketid';
    const INDEX_2            =    'dateline';
    const INDEX_3            =    'ticketid, messageid';


    protected $_dataStore = array();

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param int $_ticketMessageID The Ticket Message ID
     * @throws SWIFT_MessageID_Exception If the Record could not be loaded
     */
    public function __construct($_ticketMessageID) {
        parent::__construct();

        if (!$this->LoadData($_ticketMessageID)) {
            throw new SWIFT_MessageID_Exception('Failed to load Ticket Message ID: ' . $_ticketMessageID);
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
     * @throws SWIFT_MessageID_Exception If the Class is not Loaded
     */
    public function ProcessUpdatePool() {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'ticketmessageids', $this->GetUpdatePool(), 'UPDATE',
                "ticketmessageid = '" . (int) ($this->GetTicketMessageID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Ticket Message ID
     *
     * @author Varun Shoor
     * @return mixed "ticketmessageid" on Success, "false" otherwise
     * @throws SWIFT_MessageID_Exception If the Class is not Loaded
     */
    public function GetTicketMessageID() {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_MessageID_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['ticketmessageid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param int $_ticketMessageID The Ticket Message ID
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_ticketMessageID) {
        $_dataStore = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "ticketmessageids WHERE ticketmessageids = '"
                . $_ticketMessageID . "'");
        if (isset($_dataStore['ticketmessageid']) && !empty($_dataStore['ticketmessageid'])) {
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
     * @throws SWIFT_MessageID_Exception If the Class is not Loaded
     */
    public function GetDataStore() {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_MessageID_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore;
    }

    /**
     * Retrieves a Property Value from Data Store
     *
     * @author Varun Shoor
     * @param string $_key The Key Identifier
     * @return mixed Property Data on Success, "false" otherwise
     * @throws SWIFT_MessageID_Exception If the Class is not Loaded
     */
    public function GetProperty($_key) {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_MessageID_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_MessageID_Exception(SWIFT_INVALIDDATA);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Retrieve the Message ID based on Ticket ID
     *
     * @author Varun Shoor
     * @param SWIFT_Ticket $_SWIFT_TicketObject The SWIFT_Ticket Object Pointer
     * @return string "Ticket Message ID" (STRING) on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided or If Invalid Data is Provided
     */
    public static function RetrieveMessageIDOnTicketID(SWIFT_Ticket $_SWIFT_TicketObject) {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded()) {
            throw new SWIFT_MessageID_Exception(SWIFT_INVALIDDATA);
        }

        $_ticketMessageIDContainer = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "ticketmessageids WHERE ticketid = '" .
                (int) ($_SWIFT_TicketObject->GetTicketID()) . "'");
        if (isset($_ticketMessageIDContainer['ticketid']) && !empty($_ticketMessageIDContainer['ticketid'])) {
            return $_ticketMessageIDContainer['messageid'];
        }

        return '0';
    }

    /**
     * Create a new Ticket Message ID
     *
     * @author Varun Shoor
     * @param SWIFT_Ticket $_SWIFT_TicketObject The SWIFT_Ticket Object Pointer
     * @param SWIFT_TicketPost $_SWIFT_TicketPostObject (OPTIONAL) The SWIFT_TicketPost Object Pointer
     * @return string "Message ID" (STRING) on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function Create(SWIFT_Ticket $_SWIFT_TicketObject, SWIFT_TicketPost $_SWIFT_TicketPostObject = null) {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_SWIFT_TicketObject instanceof SWIFT_TIcket || !$_SWIFT_TicketObject->GetIsClassLoaded()) {
            throw new SWIFT_MessageID_Exception(SWIFT_INVALIDDATA);
        }

        $_ticketPostID = false;
        if ($_SWIFT_TicketPostObject instanceof SWIFT_TicketPost && $_SWIFT_TicketPostObject->GetIsClassLoaded()) {
            $_ticketPostID = (int) ($_SWIFT_TicketPostObject->GetTicketPostID());
        }

        $_currentTicketMessageID = self::RetrieveMessageIDOnTicketID($_SWIFT_TicketObject);
        if ($_currentTicketMessageID && empty($_ticketPostID)) {
            return $_currentTicketMessageID;
        }

        $_messageID = substr(BuildHash(), 0, 10) . '.' . substr(BuildHash(), 0, 5);
        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'ticketmessageids', array('messageid' => $_messageID,
            'ticketid' => (int) ($_SWIFT_TicketObject->GetTicketID()), 'ticketpostid' => $_ticketPostID, 'dateline' => DATENOW), 'INSERT');
        $_ticketMessageID = $_SWIFT->Database->Insert_ID();

        if (!$_ticketMessageID) {
            throw new SWIFT_MessageID_Exception(SWIFT_CREATEFAILED);
        }

        return $_messageID;
    }

    /**
     * Delete the ticket message id record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_MessageID_Exception If the Class is not Loaded
     */
    public function Delete() {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_MessageID_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($this->GetTicketMessageID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of Ticket Message ID's
     *
     * @author Varun Shoor
     * @param array $_ticketMessageIDList The Ticket Message ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_ticketMessageIDList) {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_ticketMessageIDList)) {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "ticketmessageids WHERE ticketmessageid IN (" .
                BuildIN($_ticketMessageIDList) . ")");

        return true;
    }

    /**
     * Delete on a list of ticket ids
     *
     * @author Varun Shoor
     * @param array $_ticketIDList The Ticket ID List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_MessageID_Exception If Invalid Data is Provided
     */
    public static function DeleteOnTicket($_ticketIDList) {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_ticketIDList)) {
            return false;
        }

        $_ticketMessageIDList = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketmessageids WHERE ticketid IN (" . BuildIN($_ticketIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_ticketMessageIDList[] = (int) ($_SWIFT->Database->Record['ticketmessageid']);
        }

        if (!count($_ticketMessageIDList)) {
            return false;
        }

        self::DeleteList($_ticketMessageIDList);

        return true;
    }
}
?>
