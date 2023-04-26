<?php
/**
 * ###############################################
 *
 * SWIFT Framework
 * _______________________________________________
 *
 * @author        Werner Garcia
 *
 * @package        SWIFT
 * @copyright    Copyright (c) 2001-2012, Kayako
 * @license        http://www.kayako.com/license
 * @link        http://www.kayako.com
 *
 * ###############################################
 */

namespace Base\Models\User;

use SWIFT;
use SWIFT_Data;
use SWIFT_DataID;
use SWIFT_DataStore;
use SWIFT_Exception;
use SWIFT_Model;

/**
 * The User Organization Link Management Class
 *
 * @author Werner Garcia
 */
class SWIFT_UserOrganizationLink extends SWIFT_Model {
    const TABLE_NAME         =    'userorganizationlinks';
    const PRIMARY_KEY        =    'userorganizationlinkid';

    const TABLE_STRUCTURE    =    "userorganizationlinkid I PRIMARY AUTO NOTNULL,
                                   userorganizationid I DEFAULT '0' NOTNULL,
                                   userid I DEFAULT '0' NOTNULL";

    const INDEX_1            =    'userid, userorganizationid';
    const INDEX_2            =    'userid';
    const INDEX_3            =    'userorganizationid';

    const INDEXTYPE_1        =    'UNIQUE';

    protected $_dataStore = [];

    /**
     * Constructor
     *
     * @author Werner Garcia
     * @param SWIFT_Data $_SWIFT_DataObject The SWIFT_Data Object
     * @throws SWIFT_Exception If the Record could not be loaded
     */
    public function __construct(SWIFT_Data $_SWIFT_DataObject) {
        parent::__construct();

        if (!$_SWIFT_DataObject instanceof SWIFT_Data || !$_SWIFT_DataObject->GetIsClassLoaded() || !$this->LoadData($_SWIFT_DataObject)) {
            throw new SWIFT_Exception('Failed to load UserOrganizationLink Object');
        }
    }

    /**
     * Destructor
     *
     * @author Werner Garcia
     * @throws SWIFT_Exception
     */
    public function __destruct() {
        $this->ProcessUpdatePool();

        parent::__destruct();
    }

    /**
     * Processes the Update Pool Data
     *
     * @author Werner Garcia
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

        $this->Database->AutoExecute(TABLE_PREFIX . 'userorganizationlinks', $this->GetUpdatePool(), 'UPDATE', "userorganizationlinkid = '" .
            (int)$this->GetUserOrganizationLinkID() . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the User Organization Link ID
     *
     * @author Werner Garcia
     * @return mixed "userorganizationlinkid" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetUserOrganizationLinkID() {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['userorganizationlinkid'];
    }

    /**
     * Load the Data
     *
     * @author Werner Garcia
     * @param SWIFT_Data $_SWIFT_DataObject The SWIFT_Data Object
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    protected function LoadData($_SWIFT_DataObject) {
        $_SWIFT = SWIFT::GetInstance();

        // Is it a ID?
        $isClassLoaded = $_SWIFT_DataObject->GetIsClassLoaded();
        if ($_SWIFT_DataObject instanceof SWIFT_DataID && $isClassLoaded) {
            $_dataStore = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "userorganizationlinks WHERE userorganizationlinkid = '" .
                (int)$_SWIFT_DataObject->GetDataID() . "'");
            if (isset($_dataStore['userorganizationlinkid']) && !empty($_dataStore['userorganizationlinkid'])) {
                $this->_dataStore = $_dataStore;

                return true;
            }

            // Is it a Store?
        } else if ($_SWIFT_DataObject instanceof SWIFT_DataStore && $isClassLoaded) {
            $this->_dataStore = $_SWIFT_DataObject->GetDataStore();

            if (!isset($this->_dataStore['userorganizationlinkid']) || empty($this->_dataStore['userorganizationlinkid'])) {
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            }

            return true;
        }

        throw new SWIFT_Exception(SWIFT_INVALIDDATA);
    }

    /**
     * Returns the Data Store Array
     *
     * @author Werner Garcia
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
     * @author Werner Garcia
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
     * Create a new User Organization Link
     *
     * @author Werner Garcia
     * @param SWIFT_User $_SWIFT_UserObject The Ticket View Object Pointer
     * @param int $_userOrganizationId The Organization ID
     * @return int UserOrganizationLink ID on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function Create(SWIFT_User $_SWIFT_UserObject, $_userOrganizationId) {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_userOrganizationId) || !$_SWIFT_UserObject instanceof SWIFT_User || !$_SWIFT_UserObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if (static::LinkExists($_userOrganizationId, $_SWIFT_UserObject->GetID())) {
            $_dataStore = $_SWIFT->Database->QueryFetch('SELECT * FROM ' . TABLE_PREFIX . 'userorganizationlinks WHERE userorganizationid = ' . $_userOrganizationId . ' AND userid = ' . $_SWIFT_UserObject->GetID());
            return $_dataStore['userorganizationlinkid'];
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'userorganizationlinks', [
            'userid' => $_SWIFT_UserObject->GetUserID(),
            'userorganizationid' => $_userOrganizationId
        ]);
        $_userOrganizationLinkID = $_SWIFT->Database->Insert_ID();

        if (!$_userOrganizationLinkID) {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        return $_userOrganizationLinkID;
    }

    /**
     * Delete User Organization Link record
     *
     * @author Werner Garcia
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete() {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($this->GetUserOrganizationLinkID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of User Organization Link IDs
     *
     * @author Werner Garcia
     * @param array $_userOrganizationLinkIDList The User Organization Link ID List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function DeleteList($_userOrganizationLinkIDList) {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_userOrganizationLinkIDList)) {
            return false;
        }

        $_SWIFT->Database->Query('DELETE FROM ' . TABLE_PREFIX . 'userorganizationlinks WHERE userorganizationlinkid IN (' . BuildIN($_userOrganizationLinkIDList) . ')');

        return true;
    }

    /**
     * Retrieve the links based on ticket view id
     *
     * @author Werner Garcia
     * @param int $_userID The User ID
     * @return array The User Organization Links Container
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveOnUser($_userID) {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_userID)) {
            return [];
        }

        $_userOrganizationLinksContainer = array();
        $_SWIFT->Database->Query('SELECT * FROM ' . TABLE_PREFIX . "userorganizationlinks WHERE userid = '" . $_userID . "'");
        while ($_SWIFT->Database->NextRecord()) {
            if (isset($_SWIFT->Database->Record['userorganizationlinkid'])) {
                $_userOrganizationLinksContainer[$_SWIFT->Database->Record['userorganizationlinkid']] =
                    $_SWIFT->Database->Record;
            }
        }

        return $_userOrganizationLinksContainer;
    }

    /**
     * Retrieve the links based on ticket view id
     *
     * @author Werner Garcia
     * @param int $_userID The User ID
     * @return array The User Organization Links Container
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveListOnUser($_userID) {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_userID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_userOrganizationLinksContainer = array();
        $_SWIFT->Database->Query('SELECT uo.* FROM ' . TABLE_PREFIX . 'userorganizationlinks uol JOIN ' . TABLE_PREFIX . 'userorganizations uo USING (userorganizationid) WHERE uol.userid = ' . $_userID);
        while ($_SWIFT->Database->NextRecord()) {
            $_userOrganizationLinksContainer[$_SWIFT->Database->Record['userorganizationid']] = $_SWIFT->Database->Record['organizationname'];
        }

        return $_userOrganizationLinksContainer;
    }

    /**
     * Retrieve the links based on ticket view id
     *
     * @author Werner Garcia
     * @param int $_userOrganizationID The User ID
     * @return array The User Organization Links Container
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveOnOrganization($_userOrganizationID) {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_userOrganizationID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_userOrganizationLinksContainer = array();
        $_SWIFT->Database->Query('SELECT * FROM ' . TABLE_PREFIX . "userorganizationlinks WHERE userorganizationid = '" . $_userOrganizationID . "'");
        while ($_SWIFT->Database->NextRecord()) {
            $_userOrganizationLinksContainer[$_SWIFT->Database->Record['userorganizationlinkid']] =
                $_SWIFT->Database->Record;
        }

        return $_userOrganizationLinksContainer;
    }

    /**
     * Returns count
     *
     * @author Werner Garcia
     * @param int $_userOrganizationID The User Organization ID
     * @param int $_userID The User ID
     * @return int
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function LinkExists($_userOrganizationID, $_userID) {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_userOrganizationID) || empty($_userID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_countContainer = $_SWIFT->Database->QueryFetch('SELECT count(*) as totalitems FROM ' . TABLE_PREFIX . 'userorganizationlinks WHERE userorganizationid = ' . $_userOrganizationID . ' AND userid = ' . $_userID);
        if (isset($_countContainer['totalitems'])) {
            return $_countContainer['totalitems'];
        }

        return 0;
    }

    /**
     * Delete the links based on a list of user id's
     *
     * @author Werner Garcia
     * @param array $_userIDList The User ID List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function DeleteOnUser($_userIDList) {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_userIDList)) {
            return false;
        }

        $_userOrganizationLinkIDList = array();
        $_SWIFT->Database->Query('SELECT * FROM ' . TABLE_PREFIX . 'userorganizationlinks WHERE userid IN (' . BuildIN($_userIDList) . ')');
        while ($_SWIFT->Database->NextRecord()) {
            $_userOrganizationLinkIDList[] = $_SWIFT->Database->Record['userorganizationlinkid'];
        }

        if (!count($_userOrganizationLinkIDList)) {
            return false;
        }

        self::DeleteList($_userOrganizationLinkIDList);

        return true;
    }

    /**
     * Delete the links based on a list of user organization id's
     *
     * @author Werner Garcia
     * @param array $_userOrganizationIDList The User organization ID List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function DeleteOnOrganization($_userOrganizationIDList) {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_userOrganizationIDList)) {
            return false;
        }

        $_userOrganizationLinkIDList = array();
        $_SWIFT->Database->Query('SELECT * FROM ' . TABLE_PREFIX . 'userorganizationlinks WHERE userorganizationid IN (' . BuildIN($_userOrganizationIDList) . ')');
        while ($_SWIFT->Database->NextRecord()) {
            $_userOrganizationLinkIDList[] = $_SWIFT->Database->Record['userorganizationlinkid'];
        }

        if (!count($_userOrganizationLinkIDList)) {
            return false;
        }

        self::DeleteList($_userOrganizationLinkIDList);

        return true;
    }
}
