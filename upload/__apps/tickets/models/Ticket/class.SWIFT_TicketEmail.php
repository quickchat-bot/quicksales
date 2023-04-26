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

namespace Tickets\Models\Ticket;

use SWIFT;
use SWIFT_Exception;
use SWIFT_Model;
use Tickets\Library\Ticket\SWIFT_Ticket_Exception;

/**
 * The Ticket Email Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_TicketEmail extends SWIFT_Model
{
    const TABLE_NAME        =    'ticketemails';
    const PRIMARY_KEY        =    'ticketemailid';

    const TABLE_STRUCTURE    =    "ticketemailid I PRIMARY AUTO NOTNULL,
                                 email C(255) DEFAULT '' NOTNULL,
                                 issearchable I2 DEFAULT '1' NOTNULL
                                ";

    const INDEX_1            =    'issearchable, email';
    const INDEXTYPE_1        =    'UNIQUE';

    protected $_dataStore = array();

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param int $_ticketEmailID The Ticket Email ID
     * @throws SWIFT_Ticket_Exception If the Record could not be loaded
     */
    public function __construct($_ticketEmailID)
    {
        parent::__construct();

        if (!$this->LoadData($_ticketEmailID)) {
            throw new SWIFT_Ticket_Exception('Failed to load Ticket Email ID: ' . $_ticketEmailID);
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
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded
     */
    public function ProcessUpdatePool()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        } else if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'ticketemails', $this->GetUpdatePool(), 'UPDATE', "ticketemailid = '" . (int) ($this->GetTicketEmailID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Ticket Email ID
     *
     * @author Varun Shoor
     * @return mixed "ticketemailid" on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded
     */
    public function GetTicketEmailID()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['ticketemailid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param int $_ticketEmailID The Ticket Email ID
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_ticketEmailID)
    {
        $_dataStore = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "ticketemails WHERE ticketemailid = '" . $_ticketEmailID . "'");
        if (isset($_dataStore['ticketemailid']) && !empty($_dataStore['ticketemailid']))
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
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded
     */
    public function GetDataStore()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore;
    }

    /**
     * Retrieves a Property Value from Data Store
     *
     * @author Varun Shoor
     * @param string $_key The Key Identifier
     * @return mixed Property Data on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded
     */
    public function GetProperty($_key)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_Ticket_Exception(SWIFT_INVALIDDATA);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Create a new Ticket Email record
     *
     * @author   Varun Shoor
     *
     * @param string $_emailAddress The Email Address
     * @param bool   $_isSearchable
     *
     * @throws SWIFT_Ticket_Exception
     * @return mixed "_ticketEmailID" (INT) on Success, "false" otherwise
     */
    public static function Create($_emailAddress, $_isSearchable = true)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_emailAddress))
        {
            throw new SWIFT_Ticket_Exception(SWIFT_INVALIDDATA);
        }

        try {
            $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'ticketemails', array('email' => mb_strtolower($_emailAddress), 'issearchable' => (int) ($_isSearchable)), 'INSERT');
            $_ticketEmailID = $_SWIFT->Database->Insert_ID();

        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            return false;
        }

        if (!$_ticketEmailID)
        {
            throw new SWIFT_Ticket_Exception(SWIFT_CREATEFAILED);
        }

        return $_ticketEmailID;
    }

    /**
     * Delete the Ticket Email record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($this->GetTicketEmailID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of Ticket Emails
     *
     * @author Varun Shoor
     * @param array $_ticketEmailIDList The Ticket Email ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_ticketEmailIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_ticketEmailIDList))
        {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "ticketemails WHERE ticketemailid IN (" . BuildIN($_ticketEmailIDList) . ")");

        return true;
    }

    /**
     * Retrieve the Ticket Email IDs for the given email list
     *
     * @author Varun Shoor
     * @param array $_emailList The Email List
     * @return mixed "_ticketEmailIDList" (ARRAY) on Success, "false" otherwise
     */
    public static function RetrieveIDListOnEmailList($_emailList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_emailList))
        {
            return array();
        }

        foreach ($_emailList as $_index => $_emailAddress) {
            $_emailList[$_index] = mb_strtolower($_emailAddress);
        }

        $_ticketEmailIDList = array();
        $_SWIFT->Database->Query("SELECT ticketemailid FROM " . TABLE_PREFIX . "ticketemails WHERE email IN (" . BuildIN($_emailList) . ")");
        while ($_SWIFT->Database->NextRecord())
        {
            if (!in_array($_SWIFT->Database->Record['ticketemailid'], $_ticketEmailIDList))
            {
                $_ticketEmailIDList[$_SWIFT->Database->Record['ticketemailid']] = (int) ($_SWIFT->Database->Record['ticketemailid']);
            }
        }

        return $_ticketEmailIDList;
    }

    /**
     * Retrieve the Ticket Email IDs for the given email list and create if not exists
     *
     * @author Varun Shoor
     * @param array $_emailList The Email List
     * @return mixed "_ticketEmailIDList" (ARRAY) on Success, "false" otherwise
     */
    public static function RetrieveIDListOnEmailListAndCreateIfNotExists($_emailList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_emailList))
        {
            return array();
        }

        foreach ($_emailList as $_index => $_emailAddress) {
            $_emailList[$_index] = mb_strtolower($_emailAddress);
        }

        $_ticketEmailIDList = $_emailMap = array();
        $_SWIFT->Database->Query("SELECT ticketemailid, email FROM " . TABLE_PREFIX . "ticketemails WHERE email IN (" . BuildIN($_emailList) . ")");
        while ($_SWIFT->Database->NextRecord())
        {
            if (!in_array($_SWIFT->Database->Record['ticketemailid'], $_ticketEmailIDList))
            {
                $_ticketEmailIDList[$_SWIFT->Database->Record['ticketemailid']] = (int) ($_SWIFT->Database->Record['ticketemailid']);
                $_emailMap[] = mb_strtolower($_SWIFT->Database->Record['email']);
            }
        }

        // Updating searchable bit for existing items, incase they have been set to off previously with account deletion
        self::MaskSearchableOnIDList($_ticketEmailIDList, true);

        // Create if it doesnt exist..
        foreach ($_emailList as $_key => $_email) {
            $_email = mb_strtolower($_email);

            if (!in_array($_email, $_emailMap)) {
                $_ticketEmailID = self::Create($_email);

                $_ticketEmailIDList[$_ticketEmailID] = $_ticketEmailID;
            }
        }

        return $_ticketEmailIDList;
    }

    /**
     * Retrieve the Ticket Emails for the given email id list
     *
     * @author Varun Shoor
     * @param array $_ticketEmailIDList The Ticket Email ID List
     * @return mixed "_emailList" (ARRAY) on Success, "false" otherwise
     */
    public static function RetrieveEmailListOnIDList($_ticketEmailIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_ticketEmailIDList))
        {
            return array();
        }

        $_emailList = array();
        $_SWIFT->Database->Query("SELECT ticketemailid, email FROM " . TABLE_PREFIX . "ticketemails WHERE ticketemailid IN (" . BuildIN($_ticketEmailIDList) . ")");
        while ($_SWIFT->Database->NextRecord())
        {
            if (!in_array($_SWIFT->Database->Record['email'], $_emailList))
            {
                $_emailList[$_SWIFT->Database->Record['ticketemailid']] = $_SWIFT->Database->Record['email'];
            }
        }

        return $_emailList;
    }

    /**
     * Delete for Recipients, attempts a cleanup
     *
     * Bug Fix: SWIFT-1186 While replying to a ticket in Staff CP, 'To' and 'CC' fields are also showing those emails addresses of the users which were already deleted
     *
     * @author Varun Shoor
     * @param array $_ticketEmailIDList
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function DeleteForRecipient($_ticketEmailIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_ticketEmailIDList)) {
            return false;
        }

        $_recipientCountList = array();

        $_SWIFT->Database->Query("SELECT COUNT(*) AS totalitems, ticketemailid FROM " . TABLE_PREFIX . "ticketrecipients
                WHERE ticketemailid IN (" . BuildIN($_ticketEmailIDList) . ")
                GROUP BY ticketemailid");
        while ($_SWIFT->Database->NextRecord()) {
            $_recipientCountList[$_SWIFT->Database->Record['ticketemailid']] = (int) ($_SWIFT->Database->Record['totalitems']);
        }

        $_finalTicketEmailIDList = array();

        foreach ($_ticketEmailIDList as $_ticketEmailID) {
            // Is it not set or empty in count list?
            if (!isset($_recipientCountList[$_ticketEmailID]) || empty($_recipientCountList[$_ticketEmailID])) {
                $_finalTicketEmailIDList[] = $_ticketEmailID;
            }
        }

        if (!count($_finalTicketEmailIDList)) {
            return false;
        }

        self::DeleteList($_finalTicketEmailIDList);

        return true;
    }

    /**
     * @author Utsav Handa
     *
     * @param array $_ticketEmailIDList
     * @param bool $_isSearchable
     *
     * @return bool
     */
    public static function MaskSearchableOnIDList(array $_ticketEmailIDList, $_isSearchable = false) {

        // Mask searchable information
        foreach($_ticketEmailIDList as $_ticketEmailID) {
            try {
                $_TicketEmail = new SWIFT_TicketEmail($_ticketEmailID);
                $_TicketEmail->UpdatePool('issearchable', (int) ($_isSearchable));
                $_TicketEmail->ProcessUpdatePool();
            } catch(SWIFT_Exception $_Exception) {
                // Exception
            }
        }

        return true;
    }
}
?>
