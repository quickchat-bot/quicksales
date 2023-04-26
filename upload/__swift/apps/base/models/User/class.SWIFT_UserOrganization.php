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

namespace Base\Models\User;

use Base\Models\Tag\SWIFT_TagLink;
use phpDocumentor\Reflection\Types\Array_;
use SWIFT;
use SWIFT_DataID;
use SWIFT_Exception;
use SWIFT_Model;

/**
 * User Organization Manager Class
 *
 * @author Varun Shoor
 */
class SWIFT_UserOrganization extends SWIFT_Model
{
    const TABLE_NAME = 'userorganizations';
    const PRIMARY_KEY = 'userorganizationid';

    const TABLE_STRUCTURE = "userorganizationid I PRIMARY AUTO NOTNULL,
                                organizationname C(100) DEFAULT '' NOTNULL,
                                organizationtype I2 DEFAULT '0' NOTNULL,
                                address C(200) DEFAULT '' NOTNULL,
                                city C(255) DEFAULT '' NOTNULL,
                                state C(255) DEFAULT '' NOTNULL,
                                postalcode C(100) DEFAULT '' NOTNULL,
                                country C(255) DEFAULT '' NOTNULL,
                                phone C(25) DEFAULT '' NOTNULL,
                                fax C(200) DEFAULT '' NOTNULL,
                                website C(255) DEFAULT '' NOTNULL,
                                dateline I DEFAULT '0' NOTNULL,
                                lastupdate I DEFAULT '0' NOTNULL,
                                languageid I DEFAULT '0' NOTNULL,
                                slaplanid I DEFAULT '0' NOTNULL,
                                slaexpirytimeline I DEFAULT '0' NOTNULL,
                                usergroupid I DEFAULT '0' NOTNULL";

    const INDEX_1 = 'organizationname, address, phone';


    protected $_dataStore = array();

    // Core Constants
    const TYPE_RESTRICTED = 1;
    const TYPE_SHARED = 2;

    const ALLOWED_CHARACTERS = "'!@#$%^&*\(\)-_=+\[\{\]\}|?\/,.";

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param SWIFT_DataID|int $_userOrganizationID The User Organization ID
     * @throws SWIFT_Exception If Unable to Load User Organization Record
     */
    public function __construct($_userOrganizationID)
    {
        parent::__construct();

        if ($_userOrganizationID instanceof SWIFT_DataID) {
            $_userOrganizationID = $_userOrganizationID->GetDataID();
        }

        if (!$this->LoadData($_userOrganizationID)) {
            throw new SWIFT_Exception('Unable to Load User Organization ID: ' .$_userOrganizationID);
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
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ProcessUpdatePool()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        } else if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'userorganizations', $this->GetUpdatePool(), 'UPDATE', "userorganizationid = '" . (int)($this->GetUserOrganizationID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the User Organization ID
     *
     * @author Varun Shoor
     * @return mixed "userorganizationid" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetUserOrganizationID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['userorganizationid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param int $_userOrganizationID The User Organization ID
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_userOrganizationID)
    {
        $_dataStore = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "userorganizations WHERE userorganizationid = '" .$_userOrganizationID . "'");
        if (isset($_dataStore['userorganizationid']) && !empty($_dataStore['userorganizationid'])) {
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
    public function GetDataStore()
    {
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
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function GetProperty($_key)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA . ': ' . $_key);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Check to see if it is a valid organization type
     *
     * @author Varun Shoor
     * @param mixed $_organizationType The Organization Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidOrganizationType($_organizationType)
    {
        if ($_organizationType == self::TYPE_RESTRICTED || $_organizationType == self::TYPE_SHARED) {
            return true;
        }

        return false;
    }

    /**
     * Retrieve the Display Label for the Organization Type
     *
     * @author Varun Shoor
     * @param mixed $_organizationType The Organization Type
     * @return string
     * @throws SWIFT_Exception
     */
    public static function GetOrganizationTypeLabel($_organizationType)
    {
        $_SWIFT = SWIFT::GetInstance();

        if ($_organizationType == self::TYPE_RESTRICTED) {
            return $_SWIFT->Language->Get('userorganizationrestricted');
        } elseif ($_organizationType == self::TYPE_SHARED) {
            return $_SWIFT->Language->Get('userorganizationshared');
        }

        return '';
    }

    /**
     * Create a new user organization record
     *
     * @author Varun Shoor
     * @param string $_organizationName (REQUIRED) The Organization Name
     * @param mixed $_organizationType (REQUIRED) The Organization Type
     * @param array $_emailContainer (OPTIONAL) The Email Container Array
     * @param string $_address (OPTIONAL) Address 1
     * @param string $_city (OPTIONAL) The City
     * @param string $_state (OPTIONAL) State
     * @param string $_postalCode (OPTIONAL) The Postal Code
     * @param string $_country (OPTIONAL) Organization Country
     * @param string $_phone (OPTIONAL) Organization Phone
     * @param string $_fax (OPTIONAL) Organization Fax
     * @param string $_website (OPTIONAL) The Organization Website
     * @param int $_slaPlanID (OPTIONAL) The Custom SLA Plan For Users of this Organization
     * @param int $_slaPlanExpiry (OPTIONAL) The SLA Plan Expiry
     * @return mixed "SWIFT_UserOrganization" Object on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided or If Creation Fails
     */
    public static function Create($_organizationName, $_organizationType, $_emailContainer = array(), $_address = '', $_city = '', $_state = '',
                                  $_postalCode = '', $_country = '', $_phone = '', $_fax = '', $_website = '', $_slaPlanID = 0, $_slaPlanExpiry = 0)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (trim($_organizationName) == '' || !self::IsValidOrganizationType($_organizationType)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if (empty($_slaPlanID)) {
            $_slaPlanExpiry = '0';
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'userorganizations', array('organizationname' => $_organizationName,
            'organizationtype' => (int)($_organizationType), 'address' => $_address, 'city' => $_city, 'state' => $_state,
            'postalcode' => $_postalCode, 'country' => $_country, 'phone' => $_phone, 'fax' => $_fax, 'website' => $_website,
            'dateline' => DATENOW, 'lastupdate' => DATENOW, 'slaplanid' =>$_slaPlanID, 'slaexpirytimeline' => (int)($_slaPlanExpiry)), 'INSERT');
        $_userOrganizationID = $_SWIFT->Database->Insert_ID();
        if (!$_userOrganizationID) {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        $_SWIFT_UserOrganizationObject = new SWIFT_UserOrganization($_userOrganizationID);
        if (!$_SWIFT_UserOrganizationObject instanceof SWIFT_UserOrganization || !$_SWIFT_UserOrganizationObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        // Email Filter Processing
        $_SWIFT_UserOrganizationObject->ProcessEmailContainer($_emailContainer);

        return $_SWIFT_UserOrganizationObject;
    }

    /**
     * Update the Organization Record
     *
     * @author Varun Shoor
     * @param string $_organizationName (REQUIRED) The Organization Name
     * @param mixed $_organizationType (REQUIRED) The Organization Type
     * @param array $_emailContainer (OPTIONAL) The Email Container Array
     * @param string $_address (OPTIONAL) Address
     * @param string $_city (OPTIONAL) The City
     * @param string $_state (OPTIONAL) State
     * @param string $_postalCode (OPTIONAL) The Postal Code
     * @param string $_country (OPTIONAL) Organization Country
     * @param string $_phone (OPTIONAL) Organization Phone
     * @param string $_fax (OPTIONAL) Organization Fax
     * @param string $_website (OPTIONAL) The Organization Website
     * @param int $_slaPlanID (OPTIONAL) The Custom SLA Plan For Users of this Organization
     * @param int $_slaPlanExpiry (OPTIONAL) The SLA Plan Expiry
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function Update($_organizationName, $_organizationType, $_emailContainer = array(), $_address = '', $_city = '', $_state = '',
                           $_postalCode = '', $_country = '', $_phone = '', $_fax = '', $_website = '', $_slaPlanID = 0, $_slaPlanExpiry = 0)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (trim($_organizationName) == '' || !self::IsValidOrganizationType($_organizationType)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if (empty($_slaPlanID)) {
            $_slaPlanExpiry = '0';
        }

        $this->UpdatePool('organizationname', $_organizationName);
        $this->UpdatePool('organizationtype', (int)($_organizationType));
        $this->UpdatePool('address', $_address);
        $this->UpdatePool('city', $_city);
        $this->UpdatePool('state', $_state);
        $this->UpdatePool('postalcode', $_postalCode);
        $this->UpdatePool('country', $_country);
        $this->UpdatePool('phone', $_phone);
        $this->UpdatePool('fax', $_fax);
        $this->UpdatePool('website', $_website);
        $this->UpdatePool('slaplanid',$_slaPlanID);
        $this->UpdatePool('slaexpirytimeline', (int)($_slaPlanExpiry));
        $this->UpdatePool('lastupdate', DATENOW);
        $this->ProcessUpdatePool();

        // Email Filter Processing
        $this->ProcessEmailContainer($_emailContainer);

        return true;
    }

    /**
     * Update the Organization Profile
     *
     * @author Varun Shoor
     * @param string $_address (OPTIONAL) Address
     * @param string $_city (OPTIONAL) The City
     * @param string $_state (OPTIONAL) State
     * @param string $_postalCode (OPTIONAL) The Postal Code
     * @param string $_country (OPTIONAL) Organization Country
     * @param string $_phone (OPTIONAL) Organization Phone
     * @param string $_fax (OPTIONAL) Organization Fax
     * @param string $_website (OPTIONAL) The Organization Website
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function UpdateProfile($_address = '', $_city = '', $_state = '', $_postalCode = '', $_country = '', $_phone = '', $_fax = '', $_website = '')
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UpdatePool('address', $_address);
        $this->UpdatePool('city', $_city);
        $this->UpdatePool('state', $_state);
        $this->UpdatePool('postalcode', $_postalCode);
        $this->UpdatePool('country', $_country);
        $this->UpdatePool('phone', $_phone);
        $this->UpdatePool('fax', $_fax);
        $this->UpdatePool('website', $_website);
        $this->UpdatePool('lastupdate', DATENOW);
        $this->ProcessUpdatePool();

        return true;
    }

    /**
     * Processes the Email Container
     *
     * @author Varun Shoor
     * @param array $_emailContainer The Email Container
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function ProcessEmailContainer($_emailContainer)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        SWIFT_UserOrganizationEmail::DeleteOnUserOrganization(array($this->GetUserOrganizationID()));

        if (_is_array($_emailContainer)) {
            foreach ($_emailContainer as $_key => $_val) {
                // Do we need to process it?
                if (strrpos($_val, '@') !== false) {
                    $_val = substr($_val, (strrpos($_val, '@') + 1));
                }

                SWIFT_UserOrganizationEmail::Create($this, $_val, false);
            }
        }

        return true;
    }

    /**
     * Delete the organization record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($this->GetUserOrganizationID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete the list of user organizations
     *
     * @author Varun Shoor
     * @param array $_userOrganizationIDList The User Organization ID List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function DeleteList($_userOrganizationIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_userOrganizationIDList)) {
            return false;
        }

        $_finalUserOrganizationIDList = array();
        $_index = 1;
        $_finalText = '';

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "userorganizations WHERE userorganizationid IN (" . BuildIN($_userOrganizationIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_finalUserOrganizationIDList[] = $_SWIFT->Database->Record['userorganizationid'];

            $_finalText .= $_index . '. ' . htmlspecialchars($_SWIFT->Database->Record['organizationname']) . ' (' . self::GetOrganizationTypeLabel($_SWIFT->Database->Record['organizationtype']) . ')<BR />';

            $_index++;
        }

        if (!count($_finalUserOrganizationIDList)) {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "userorganizations WHERE userorganizationid IN (" . BuildIN($_finalUserOrganizationIDList) . ")");

        SWIFT_UserOrganizationLink::DeleteOnOrganization($_finalUserOrganizationIDList);

        SWIFT_User::RemoveGlobalUserOrganizationAssociation($_finalUserOrganizationIDList);

        SWIFT_UserOrganizationEmail::DeleteOnUserOrganization($_finalUserOrganizationIDList);

        SWIFT_UserOrganizationNote::DeleteOnUserOrganization($_finalUserOrganizationIDList);

        SWIFT_TagLink::DeleteOnLinkList(SWIFT_TagLink::TYPE_USERORGANIZATION, $_finalUserOrganizationIDList);

        SWIFT::Info(sprintf($_SWIFT->Language->Get('titledeleteduserorganization'), count($_finalUserOrganizationIDList)), $_SWIFT->Language->Get('msgdeleteduserorganization') . '<BR />' . $_finalText);

        return true;
    }

    /**
     * Does this organization has a user as a manager?
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function HasUserManager()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->Database->Query("SELECT userrole FROM " . TABLE_PREFIX . "users WHERE userorganizationid = '" . (int)($this->GetUserOrganizationID()) . "'");
        while ($this->Database->NextRecord()) {
            if ($this->Database->Record['userrole'] == SWIFT_User::ROLE_MANAGER) {
                return true;
            }
        }

        return false;
    }

    /**
     * Retrieve a user organization based on its name
     *
     * @author Varun Shoor
     * @param string $_userOrganizationName The Organization Name
     * @return array The Organization Container
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveOnName($_userOrganizationName)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_userOrganizationName)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_userOrganizationContainer = array();

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "userorganizations WHERE organizationname = '" . $_SWIFT->Database->Escape($_userOrganizationName) . "'");
        while ($_SWIFT->Database->NextRecord()) {
            $_userOrganizationContainer[$_SWIFT->Database->Record['userorganizationid']] = $_SWIFT->Database->Record;
        }

        return $_userOrganizationContainer;
    }

    /**
     * Merge User Organizations
     *
     * @author Varun Shoor
     * @param int $_primaryOrganizationID The Primary Organization ID to Preserve
     * @param array $_userOrganizationIDList The User Organization ID List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function MergeList($_primaryOrganizationID, $_userOrganizationIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_userOrganizationIDList) || empty($_primaryOrganizationID) || count($_userOrganizationIDList) == 1) {
            return false;
        }

        $_userOrganizationIDList_Delete = $_userIDList = array();
        
        foreach ($_userOrganizationIDList as $_userOrganizationID) {
            if ($_userOrganizationID != $_primaryOrganizationID) {
                $_userOrganizationIDList_Delete[] = $_userOrganizationID;
            }
        }

        $_SWIFT->Database->Query("SELECT DISTINCT userorganizationid, userid FROM " . TABLE_PREFIX . "userorganizationlinks WHERE userorganizationid IN (" . BuildIN($_userOrganizationIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            if ($_SWIFT->Database->Record['userorganizationid'] != $_primaryOrganizationID) {
                if (!isset($_userIDList[$_SWIFT->Database->Record['userid']])) {
                    $_userIDList[] = $_SWIFT->Database->Record['userid'];
                }
            }
        }

        // Merge Users
        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'users', array('userorganizationid' =>$_primaryOrganizationID), 'UPDATE',
            "userorganizationid IN (" . BuildIN($_userOrganizationIDList_Delete) . ")");

        if (!count($_userOrganizationIDList_Delete)) {
            return false;
        }

        if (count($_userIDList)) {
            $_query = 'INSERT IGNORE INTO ' . TABLE_PREFIX . 'userorganizationlinks (userorganizationid, userid) VALUES ';
            foreach ($_userIDList as $_idx => $_uid) {
                if ($_idx > 0) {
                    $_query .= ',';
                }
                $_query .= sprintf('(%d, %d)', $_primaryOrganizationID, $_uid);
            }
            $_SWIFT->Database->Query($_query);
        }

        SWIFT_UserOrganizationLink::DeleteOnOrganization($_userOrganizationIDList_Delete);

        self::DeleteList($_userOrganizationIDList_Delete);

        return true;
    }
}

