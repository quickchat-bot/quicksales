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

namespace Base\Models\Staff;

use SWIFT;
use SWIFT_DataID;
use SWIFT_Exception;
use SWIFT_Model;
use Base\Library\Staff\SWIFT_Staff_Exception;
use SWIFT_XML;

/**
 * The Staff Profile Image Manager
 *
 * @author Varun Shoor
 */
class SWIFT_StaffProfileImage extends SWIFT_Model
{
    const TABLE_NAME = 'staffprofileimages';
    const PRIMARY_KEY = 'staffprofileimageid';

    const TABLE_STRUCTURE = "staffprofileimageid I PRIMARY AUTO NOTNULL,
                                type I2 DEFAULT '0' NOTNULL,
                                dateline I DEFAULT '0' NOTNULL,
                                staffid I DEFAULT '0' NOTNULL,
                                extension C(255) DEFAULT '' NOTNULL,
                                imagedata X2";

    const INDEX_1 = 'staffid, type';


    protected $_dataStore = array();

    // Core Constants
    const TYPE_PUBLIC = 1;
    const TYPE_PRIVATE = 2;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param int $_staffProfileImageID The Staff Profile Image ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Staff_Exception If the Record could not be loaded
     */
    public function __construct($_staffProfileImageID)
    {
        parent::__construct();

        if (!$this->LoadData($_staffProfileImageID)) {
            throw new SWIFT_Staff_Exception('Failed to load Staff Profile Image ID: ' . $_staffProfileImageID);
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
     * @throws SWIFT_Staff_Exception If the Class is not Loaded
     */
    public function ProcessUpdatePool()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        } else if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'staffprofileimages', $this->GetUpdatePool(), 'UPDATE', "staffprofileimageid = '" . (int)($this->GetStaffProfileImageID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Staff Profile Image ID
     *
     * @author Varun Shoor
     * @return mixed "staffprofileimageid" on Success, "false" otherwise
     * @throws SWIFT_Staff_Exception If the Class is not Loaded
     */
    public function GetStaffProfileImageID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Staff_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['staffprofileimageid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param int $_staffProfileImageID The Staff Profile Image ID
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_staffProfileImageID)
    {
        $_dataStore = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "staffprofileimages WHERE staffprofileimageid = '" . $_staffProfileImageID . "'");
        if (isset($_dataStore['staffprofileimageid']) && !empty($_dataStore['staffprofileimageid'])) {
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
     * @throws SWIFT_Staff_Exception If the Class is not Loaded
     */
    public function GetDataStore()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Staff_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore;
    }

    /**
     * Retrieves a Property Value from Data Store
     *
     * @author Varun Shoor
     * @param string $_key The Key Identifier
     * @return mixed Property Data on Success, "false" otherwise
     * @throws SWIFT_Staff_Exception If the Class is not Loaded
     */
    public function GetProperty($_key)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Staff_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_Staff_Exception(SWIFT_INVALIDDATA);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Check to see if its a valid Image Type
     *
     * @author Varun Shoor
     * @param mixed $_imageType The Image Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidType($_imageType)
    {
        if ($_imageType == self::TYPE_PUBLIC || $_imageType == self::TYPE_PRIVATE) {
            return true;
        }

        return false;
    }

    /**
     * Retrieve the Profile Images based on given Staff ID's
     *
     * @author Varun Shoor
     * @param array $_staffIDList The Staff ID List
     * @param mixed $_imageType The Image Type
     * @return mixed "_profileImageContainer" (ARRAY) on Success, "false" otherwise
     * @throws SWIFT_Staff_Exception If Invalid Data is Provided
     */
    public static function RetrieveList($_staffIDList, $_imageType = self::TYPE_PUBLIC)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!self::IsValidType($_imageType)) {
            throw new SWIFT_Staff_Exception(SWIFT_INVALIDDATA);
        } else if (!_is_array($_staffIDList)) {
            return false;
        }

        $_profileImageContainer = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "staffprofileimages WHERE staffid IN (" . BuildIN($_staffIDList) . ") AND type = '" . (int)($_imageType) . "'");
        while ($_SWIFT->Database->NextRecord()) {
            $_profileImageContainer[$_SWIFT->Database->Record['staffid']] = $_SWIFT->Database->Record['imagedata'];
        }

        return $_profileImageContainer;
    }

    /**
     * Check to see if there is a Profile Object data on Staff ID
     *
     * @author Varun Shoor
     * @param int $_staffID The Staff ID
     * @param mixed $_imageType The Image Type
     * @return mixed "SWIFT_StaffProfileImage" (OBJECT) on Success, "false" otherwise
     * @throws SWIFT_Staff_Exception If Invalid Data is Provided
     */
    public static function StaffHasProfileImage($_staffID, $_imageType = self::TYPE_PUBLIC)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!self::IsValidType($_imageType)) {
            throw new SWIFT_Staff_Exception(SWIFT_INVALIDDATA);
        }

        $_staffProfileImageContainer = $_SWIFT->Database->QueryFetch("SELECT staffprofileimageid FROM " . TABLE_PREFIX . "staffprofileimages WHERE staffid = '" . $_staffID . "' AND type = '" . (int)($_imageType) . "'");
        if ($_staffProfileImageContainer && isset($_staffProfileImageContainer['staffprofileimageid']) && !empty($_staffProfileImageContainer['staffprofileimageid'])) {
            return true;
        }

        return false;
    }

    /**
     * Retrieve the Profile Object data on Staff ID
     *
     * @author Varun Shoor
     * @param int $_staffID The Staff ID
     * @param mixed $_imageType The Image Type
     * @return mixed "SWIFT_StaffProfileImage" (OBJECT) on Success, "false" otherwise
     * @throws SWIFT_Staff_Exception If Invalid Data is Provided
     */
    public static function RetrieveOnStaff($_staffID, $_imageType = self::TYPE_PUBLIC)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!self::IsValidType($_imageType)) {
            throw new SWIFT_Staff_Exception(SWIFT_INVALIDDATA);
        }

        $_staffProfileImageContainer = $_SWIFT->Database->QueryFetch("SELECT staffprofileimageid FROM " . TABLE_PREFIX . "staffprofileimages WHERE staffid = '" . $_staffID . "' AND type = '" . (int)($_imageType) . "'");
        if ($_staffProfileImageContainer && isset($_staffProfileImageContainer['staffprofileimageid']) && !empty($_staffProfileImageContainer['staffprofileimageid'])) {
            return new SWIFT_StaffProfileImage($_staffProfileImageContainer['staffprofileimageid']);
        }

        return false;
    }

    /**
     * Retrieve on basis of staff id list
     *
     * @author Varun Shoor
     * @param array $_staffIDList The STaff ID List
     * @param mixed $_imageType (OPTIONAL) The Image Type
     * @return array|bool $_staffProfileImageObjectContainer
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveOnStaffList($_staffIDList, $_imageType = self::TYPE_PUBLIC)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_staffIDList)) {
            return false;
        }

        $_staffProfileImageObjectContainer = array();

        $_SWIFT->Database->Query("SELECT staffid, staffprofileimageid FROM " . TABLE_PREFIX . "staffprofileimages WHERE staffid IN (" . BuildIN($_staffIDList) . ") AND type = '" . (int)($_imageType) . "'");
        while ($_SWIFT->Database->NextRecord()) {
            $_staffProfileImageObjectContainer[$_SWIFT->Database->Record['staffid']] = $_SWIFT->Database->Record['staffprofileimageid'];
        }

        return $_staffProfileImageObjectContainer;
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
            throw new SWIFT_Staff_Exception(SWIFT_CLASSNOTLOADED);
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
                throw new SWIFT_Staff_Exception(SWIFT_INVALIDDATA);
                break;
        }

        return true;
    }

    /**
     * Create a new Staff Profile Image
     *
     * @author Varun Shoor
     * @param int $_staffID The Staff ID
     * @param mixed $_imageType The Image Type
     * @param string $_imageExtension The Image Extension
     * @param string $_imageData The Base64 Encoded Image Data
     * @return mixed "_staffProfileImageID" (INT) on Success, "false" otherwise
     * @throws SWIFT_Staff_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function Create($_staffID, $_imageType, $_imageExtension, $_imageData)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_staffID) || !self::IsValidType($_imageType) || empty($_imageData) || empty($_imageExtension)) {
            throw new SWIFT_Staff_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'staffprofileimages', array('staffid' => $_staffID, 'type' => (int)($_imageType), 'extension' => strtolower($_imageExtension), 'imagedata' => $_imageData, 'dateline' => DATENOW), 'INSERT');
        $_staffProfileImageID = $_SWIFT->Database->Insert_ID();

        if (!$_staffProfileImageID) {
            throw new SWIFT_Staff_Exception(SWIFT_CREATEFAILED);
        }

        $_SWIFT_StaffObject = new SWIFT_Staff(new SWIFT_DataID($_staffID));
        if (!$_SWIFT_StaffObject instanceof SWIFT_Staff || !$_SWIFT_StaffObject->GetIsClassLoaded()) {
            throw new SWIFT_Staff_Exception(SWIFT_CREATEFAILED);
        }

        $_SWIFT_StaffObject->UpdateProfileTimeline();

        return $_staffProfileImageID;
    }

    /**
     * Delete the Staff Profile Image record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Staff_Exception If the Class is not Loaded
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Staff_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($this->GetStaffProfileImageID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of Staff Profile Images
     *
     * @author Varun Shoor
     * @param array $_staffProfileImageIDList The Staff Profile Image ID Container Array
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_staffProfileImageIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_staffProfileImageIDList)) {
            return false;
        }

        $_finalStaffProfileImageIDList = array();

        $_SWIFT->Database->Query("SELECT staffprofileimageid FROM " . TABLE_PREFIX . "staffprofileimages WHERE staffprofileimageid IN (" . BuildIN($_staffProfileImageIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_finalStaffProfileImageIDList[] = $_SWIFT->Database->Record['staffprofileimageid'];
        }

        if (!count($_finalStaffProfileImageIDList)) {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "staffprofileimages WHERE staffprofileimageid IN (" . BuildIN($_finalStaffProfileImageIDList) . ")");

        return true;
    }

    /**
     * Delete the Staff Profile Images based on Staff ID's
     *
     * @author Varun Shoor
     * @param array $_staffIDList The Staff ID List
     * @param mixed $_imageType (OPTIONAL) The Image Type to Delete
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Staff_Exception If Invalid Data is Provided
     */
    public static function DeleteOnStaff($_staffIDList, $_imageType = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_staffIDList)) {
            return false;
        }

        $_sqlExtended = '';

        if (!empty($_imageType) && !self::IsValidType($_imageType)) {
            throw new SWIFT_Staff_Exception(SWIFT_INVALIDDATA);
        } else if (!empty($_imageType)) {
            $_sqlExtended = " AND type = '" . (int)($_imageType) . "'";
        }

        $_staffProfileImageIDList = array();

        $_SWIFT->Database->Query("SELECT staffprofileimageid FROM " . TABLE_PREFIX . "staffprofileimages WHERE staffid IN (" . BuildIN($_staffIDList) . ")" . $_sqlExtended);
        while ($_SWIFT->Database->NextRecord()) {
            $_staffProfileImageIDList[] = $_SWIFT->Database->Record['staffprofileimageid'];
        }

        if (!count($_staffProfileImageIDList)) {
            return false;
        }

        self::DeleteList($_staffProfileImageIDList);

        return true;
    }

    /**
     * Retrieve the XML based Data
     *
     * @author Varun Shoor
     * @param SWIFT_XML $_XMLObject The SWIFT_XML Pointer
     * @param mixed $_imageType (OPTIONAL) The Image Type to Delete
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Staff_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public static function DispatchXML(SWIFT_XML $_XMLObject, $_imageType = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_XMLObject instanceof SWIFT_XML || !$_XMLObject->GetIsClassLoaded()) {
            throw new SWIFT_Staff_Exception(SWIFT_INVALIDDATA);
        } else if (!empty($_imageType) && !self::IsValidType($_imageType)) {
            throw new SWIFT_Staff_Exception(SWIFT_INVALIDDATA);
        }

        $_sqlExtended = '';
        if (!empty($_imageType)) {
            $_sqlExtended = " WHERE type = '" . (int)($_imageType) . "'";
        }

        $_staffProfileImageContainer = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "staffprofileimages" . $_sqlExtended);
        while ($_SWIFT->Database->NextRecord()) {
            $_staffProfileImageContainer[$_SWIFT->Database->Record['staffprofileimageid']] = $_SWIFT->Database->Record;
        }

        foreach ($_staffProfileImageContainer as $_key => $_val) {
            $_XMLObject->AddTag('avatar', $_val['imagedata'], array('staffid' => (int)($_val['staffid']), 'type' => $_val['extension']));
        }

        return true;
    }
}

?>
