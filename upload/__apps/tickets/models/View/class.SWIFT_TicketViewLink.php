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

namespace Tickets\Models\View;

use SWIFT;
use SWIFT_Data;
use SWIFT_DataID;
use SWIFT_DataStore;
use SWIFT_Exception;
use SWIFT_Model;

/**
 * The Ticket View Link Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_TicketViewLink extends SWIFT_Model {
    const TABLE_NAME        =    'ticketviewlinks';
    const PRIMARY_KEY        =    'ticketviewlinkid';

    const TABLE_STRUCTURE    =    "ticketviewlinkid I PRIMARY AUTO NOTNULL,
                                ticketviewid I DEFAULT '0' NOTNULL,
                                linktype I2 DEFAULT '0' NOTNULL,
                                linktypeid I DEFAULT '0' NOTNULL";

    const INDEX_1            =    'ticketviewid';


    protected $_dataStore = array();

    // Core Constants
    const LINK_DEPARTMENT = '1';
    const LINK_FILTERDEPARTMENT = '2';
    const LINK_FILTERTYPE = '3';
    const LINK_FILTERPRIORITY = '4';
    const LINK_FILTERSTATUS = '5';

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param SWIFT_Data $_SWIFT_DataObject The SWIFT_Data Object
     * @throws SWIFT_View_Exception If the Record could not be loaded
     */
    public function __construct(SWIFT_Data $_SWIFT_DataObject) {
        parent::__construct();

        if (!$_SWIFT_DataObject instanceof SWIFT_Data || !$_SWIFT_DataObject->GetIsClassLoaded() || !$this->LoadData($_SWIFT_DataObject)) {
            throw new SWIFT_View_Exception('Failed to load Ticket Object');
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
     * @throws SWIFT_View_Exception If the Class is not Loaded
     */
    public function ProcessUpdatePool() {
        if (!$this->GetIsClassLoaded()) {
            return false;
        } else if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'ticketviewlinks', $this->GetUpdatePool(), 'UPDATE', "ticketviewlinkid = '" .
                (int) ($this->GetTicketViewLinkID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Ticket View Link ID
     *
     * @author Varun Shoor
     * @return mixed "ticketviewlinkid" on Success, "false" otherwise
     * @throws SWIFT_View_Exception If the Class is not Loaded
     */
    public function GetTicketViewLinkID() {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_View_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['ticketviewlinkid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param SWIFT_Data $_SWIFT_DataObject The SWIFT_Data Object
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    protected function LoadData($_SWIFT_DataObject) {
        $_SWIFT = SWIFT::GetInstance();

        // Is it a ID?
        if ($_SWIFT_DataObject instanceof SWIFT_DataID && $_SWIFT_DataObject->GetIsClassLoaded()) {
            $_dataStore = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "ticketviewlinks WHERE ticketviewlinkid = '" .
                    (int) ($_SWIFT_DataObject->GetDataID()) . "'");
            if (isset($_dataStore['ticketviewlinkid']) && !empty($_dataStore['ticketviewlinkid'])) {
                $this->_dataStore = $_dataStore;

                return true;
            }

            // Is it a Store?
        } else if ($_SWIFT_DataObject instanceof SWIFT_DataStore && $_SWIFT_DataObject->GetIsClassLoaded()) {
            $this->_dataStore = $_SWIFT_DataObject->GetDataStore();

            if (!isset($this->_dataStore['ticketviewlinkid']) || empty($this->_dataStore['ticketviewlinkid'])) {
                throw new SWIFT_View_Exception(SWIFT_INVALIDDATA);
            }

            return true;
        }

        throw new SWIFT_View_Exception(SWIFT_INVALIDDATA);
    }

    /**
     * Returns the Data Store Array
     *
     * @author Varun Shoor
     * @return mixed "_dataStore" Array on Success, "false" otherwise
     * @throws SWIFT_View_Exception If the Class is not Loaded
     */
    public function GetDataStore() {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_View_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore;
    }

    /**
     * Retrieves a Property Value from Data Store
     *
     * @author Varun Shoor
     * @param string $_key The Key Identifier
     * @return mixed Property Data on Success, "false" otherwise
     * @throws SWIFT_View_Exception If the Class is not Loaded
     */
    public function GetProperty($_key) {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_View_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_View_Exception(SWIFT_INVALIDDATA);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Check to see if its a valid type
     *
     * @author Varun Shoor
     * @param mixed $_linkType The Link Type
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_View_Exception If Invalid Data is Provided
     */
    public static function IsValidType($_linkType) {
        $_SWIFT = SWIFT::GetInstance();

        if ($_linkType == self::LINK_DEPARTMENT || $_linkType == self::LINK_FILTERDEPARTMENT || $_linkType == self::LINK_FILTERTYPE ||
                $_linkType == self::LINK_FILTERPRIORITY || $_linkType == self::LINK_FILTERSTATUS)
        {
            return true;
        }

        return false;
    }

    /**
     * Create a new Ticket View Link
     *
     * @author Varun Shoor
     * @param SWIFT_TicketView $_SWIFT_TicketViewObject The Ticket View Object Pointer
     * @param mixed $_linkType The Link Type
     * @param int $_linkTypeID The Link Type ID
     * @return int Ticket View Link ID on Success, "false" otherwise
     * @throws SWIFT_View_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function Create(SWIFT_TicketView $_SWIFT_TicketViewObject, $_linkType, $_linkTypeID) {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_SWIFT_TicketViewObject instanceof SWIFT_TicketView || !$_SWIFT_TicketViewObject->GetIsClassLoaded() ||
                !self::IsValidType($_linkType) || empty($_linkTypeID)) {
            throw new SWIFT_View_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'ticketviewlinks', array('ticketviewid' => (int) ($_SWIFT_TicketViewObject->GetTicketViewID()),
                'linktype' => (int) ($_linkType), 'linktypeid' => $_linkTypeID));
        $_ticketViewLinkID = $_SWIFT->Database->Insert_ID();

        if (!$_ticketViewLinkID) {
            throw new SWIFT_View_Exception(SWIFT_CREATEFAILED);
        }

        // Rebuild cache
        SWIFT_TicketView::RebuildCache();

        return $_ticketViewLinkID;
    }

    /**
     * Delete Ticket View Link record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_View_Exception If the Class is not Loaded
     */
    public function Delete() {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_View_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($this->GetTicketViewLinkID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of Ticket View Link IDs
     *
     * @author Varun Shoor
     * @param array $_ticketViewLinkIDList The Ticket View Link ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_ticketViewLinkIDList) {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_ticketViewLinkIDList)) {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "ticketviewlinks WHERE ticketviewlinkid IN (" . BuildIN($_ticketViewLinkIDList) .
                ")");

        return true;
    }

    /**
     * Retrieve the links based on ticket view id
     *
     * @author Varun Shoor
     * @param int $_ticketViewID The Ticket View ID
     * @return array The Ticket View Links Container
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveOnTicketView($_ticketViewID) {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_ticketViewID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_ticketViewLinksContainer = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketviewlinks WHERE ticketviewid = '" . $_ticketViewID . "'");
        while ($_SWIFT->Database->NextRecord()) {
            $_ticketViewLinksContainer[$_SWIFT->Database->Record['linktype']][$_SWIFT->Database->Record['ticketviewlinkid']] =
                    $_SWIFT->Database->Record;
        }

        return $_ticketViewLinksContainer;
    }

    /**
     * Delete the links based on a list of ticket view id's
     *
     * @author Varun Shoor
     * @param array $_ticketViewIDList The Ticket View ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteOnTicketView($_ticketViewIDList) {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_ticketViewIDList)) {
            return false;
        }

        $_ticketViewLinkIDList = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketviewlinks WHERE ticketviewid IN (" . BuildIN($_ticketViewIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_ticketViewLinkIDList[] = (int) ($_SWIFT->Database->Record['ticketviewlinkid']);
        }

        if (!count($_ticketViewLinkIDList)) {
            return false;
        }

        self::DeleteList($_ticketViewLinkIDList);

        return true;
    }
}
?>
