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

namespace Base\Models\User;

use SWIFT;
use SWIFT_Data;
use SWIFT_DataID;
use SWIFT_DataStore;
use SWIFT_Exception;
use SWIFT_Model;

/**
 * The User Profile Image Manager
 *
 * @author Varun Shoor
 */
class SWIFT_UserProfileImage extends SWIFT_Model
{
    const TABLE_NAME = 'userprofileimages';
    const PRIMARY_KEY = 'userprofileimageid';

    const TABLE_STRUCTURE = "userprofileimageid I PRIMARY AUTO NOTNULL,
                                dateline I DEFAULT '0' NOTNULL,
                                userid I DEFAULT '0' NOTNULL,
                                extension C(255) DEFAULT '' NOTNULL,
                                imagedata X2";

    const INDEX_1 = 'userid';


    protected $_dataStore = array();

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param SWIFT_Data $_SWIFT_DataObject The SWIFT_Data Object
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Record could not be loaded
     */
    public function __construct(SWIFT_Data $_SWIFT_DataObject)
    {
        parent::__construct();

        if (!$_SWIFT_DataObject instanceof SWIFT_Data || !$_SWIFT_DataObject->GetIsClassLoaded() || !$this->LoadData($_SWIFT_DataObject)) {
            throw new SWIFT_Exception('Failed to load User Profile Image Object');
        }
    }

    /**
     * Destructor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
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

        $this->Database->AutoExecute(TABLE_PREFIX . 'userprofileimages', $this->GetUpdatePool(), 'UPDATE', "userprofileimageid = '" .
            (int)($this->GetUserProfileImageID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the User Profile Image ID
     *
     * @author Varun Shoor
     * @return mixed "userprofileimageid" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetUserProfileImageID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['userprofileimageid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param SWIFT_Data $_SWIFT_DataObject The SWIFT_Data Object
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    protected function LoadData($_SWIFT_DataObject)
    {
        // Is it a ID?
        if ($_SWIFT_DataObject instanceof SWIFT_DataID && $_SWIFT_DataObject->GetIsClassLoaded()) {
            $_dataStore = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "userprofileimages WHERE userprofileimageid = '" .
                (int)($_SWIFT_DataObject->GetDataID()) . "'");
            if (isset($_dataStore['userprofileimageid']) && !empty($_dataStore['userprofileimageid'])) {
                $this->_dataStore = $_dataStore;

                return true;
            }

            // Is it a Store?
        } else if ($_SWIFT_DataObject instanceof SWIFT_DataStore && $_SWIFT_DataObject->GetIsClassLoaded()) {
            $this->_dataStore = $_SWIFT_DataObject->GetDataStore();

            if (!isset($this->_dataStore['userprofileimageid']) || empty($this->_dataStore['userprofileimageid'])) {
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            }

            return true;
        }

        throw new SWIFT_Exception(SWIFT_INVALIDDATA);
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
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetProperty($_key)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Retrieve the Profile Images based on given User ID's
     *
     * @author Varun Shoor
     * @param array $_userIDList The User ID List
     * @return mixed "_profileImageContainer" (ARRAY) on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveList($_userIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_userIDList)) {
            return false;
        }

        $_profileImageContainer = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "userprofileimages WHERE userid IN (" . BuildIN($_userIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_profileImageContainer[$_SWIFT->Database->Record['userid']] = $_SWIFT->Database->Record['imagedata'];
        }

        return $_profileImageContainer;
    }

    /**
     * Check to see if there is a Profile Object data on User ID
     *
     * @author Varun Shoor
     * @param int $_userID The User ID
     * @return mixed "SWIFT_UserProfileImage" (OBJECT) on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function UserHasProfileImage($_userID)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_userProfileImageContainer = $_SWIFT->Database->QueryFetch("SELECT userprofileimageid FROM " . TABLE_PREFIX . "userprofileimages WHERE userid = '" .$_userID . "'");
        if ($_userProfileImageContainer && isset($_userProfileImageContainer['userprofileimageid']) && !empty($_userProfileImageContainer['userprofileimageid'])) {
            return true;
        }

        return false;
    }

    /**
     * Retrieve the Profile Object data on User ID
     *
     * @author Varun Shoor
     * @param int $_userID The User ID
     * @return mixed "SWIFT_UserProfileImage" (OBJECT) on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveOnUser($_userID)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_userProfileImageContainer = $_SWIFT->Database->QueryFetch("SELECT userprofileimageid FROM " . TABLE_PREFIX .
            "userprofileimages WHERE userid = '" .$_userID . "'");
        if ($_userProfileImageContainer && isset($_userProfileImageContainer['userprofileimageid']) && !empty($_userProfileImageContainer['userprofileimageid'])) {
            return new SWIFT_UserProfileImage(new SWIFT_DataID($_userProfileImageContainer['userprofileimageid']));
        }

        return false;
    }

    /**
     * Retrieve on basis of user id list
     *
     * @author Varun Shoor
     * @param array $_userIDList The User ID List
     * @return array|bool $_userProfileImageObjectContainer
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveOnUserList($_userIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_userIDList)) {
            return false;
        }

        $_userProfileImageObjectContainer = array();

        $_SWIFT->Database->Query("SELECT userid, userprofileimageid FROM " . TABLE_PREFIX . "userprofileimages WHERE userid IN (" . BuildIN($_userIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_userProfileImageObjectContainer[$_SWIFT->Database->Record['userid']] = $_SWIFT->Database->Record['userprofileimageid'];
        }

        return $_userProfileImageObjectContainer;
    }

    /**
     * Output the Image
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Output()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        HeaderNoCache();

        switch ($this->GetProperty('extension')) {
            case 'png':
                header('Content-type: image/png');

                echo base64_decode($this->GetProperty('imagedata'));

                break;

            case 'gif';
                header('Content-type: image/gif');

                echo base64_decode($this->GetProperty('imagedata'));

                break;

            case 'jpeg';
            case 'jpg';
                header('Content-type: image/jpeg');

                echo base64_decode($this->GetProperty('imagedata'));

                break;

            default:
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
                break;
        }

        return true;
    }

    /**
     * Create a new User Profile Image
     *
     * @author Varun Shoor
     * @param int $_userID The User ID
     * @param string $_imageExtension The Image Extension
     * @param string $_imageData The Base64 Encoded Image Data
     * @return mixed "_userProfileImageID" (INT) on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function Create($_userID, $_imageExtension, $_imageData)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_userID) || empty($_imageData) || empty($_imageExtension)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'userprofileimages', array('userid' =>$_userID, 'extension' => strtolower($_imageExtension), 'imagedata' => $_imageData, 'dateline' => DATENOW), 'INSERT');
        $_userProfileImageID = $_SWIFT->Database->Insert_ID();

        if (!$_userProfileImageID) {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        return $_userProfileImageID;
    }

    /**
     * Delete the User Profile Image record
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

        self::DeleteList(array($this->GetUserProfileImageID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of User Profile Images
     *
     * @author Varun Shoor
     * @param array $_userProfileImageIDList The User Profile Image ID Container Array
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_userProfileImageIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_userProfileImageIDList)) {
            return false;
        }

        $_finalUserProfileImageIDList = array();

        $_SWIFT->Database->Query("SELECT userprofileimageid FROM " . TABLE_PREFIX . "userprofileimages WHERE userprofileimageid IN (" . BuildIN($_userProfileImageIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_finalUserProfileImageIDList[] = $_SWIFT->Database->Record['userprofileimageid'];
        }

        if (!count($_finalUserProfileImageIDList)) {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "userprofileimages WHERE userprofileimageid IN (" . BuildIN($_finalUserProfileImageIDList) . ")");

        return true;
    }

    /**
     * Delete the User Profile Images based on User ID's
     *
     * @author Varun Shoor
     * @param array $_userIDList The User ID List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function DeleteOnUser($_userIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_userIDList)) {
            return false;
        }

        $_userProfileImageIDList = array();

        $_SWIFT->Database->Query("SELECT userprofileimageid FROM " . TABLE_PREFIX . "userprofileimages WHERE userid IN (" . BuildIN($_userIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_userProfileImageIDList[] = $_SWIFT->Database->Record['userprofileimageid'];
        }

        if (!count($_userProfileImageIDList)) {
            return false;
        }

        self::DeleteList($_userProfileImageIDList);

        return true;
    }
}

?>
