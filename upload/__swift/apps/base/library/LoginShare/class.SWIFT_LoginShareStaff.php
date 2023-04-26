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

namespace Base\Library\LoginShare;

use Base\Library\LoginShare\SWIFT_LoginShare;
use Base\Models\Staff\SWIFT_Staff;
use Base\Models\Staff\SWIFT_StaffGroup;
use SWIFT;
use SWIFT_DataID;
use SWIFT_ErrorLog;
use SWIFT_Exception;

/**
 * The Staff LoginShare Class
 *
 * @author Varun Shoor
 */
class SWIFT_LoginShareStaff extends SWIFT_LoginShare
{

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Check to see if LoginShare is active
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsActive()
    {
        $_SWIFT = SWIFT::GetInstance();

        if ($_SWIFT->Settings->Get('loginshare_staffenable') == '1' && $_SWIFT->Settings->Get('loginshare_staffurl') != '') {
            return true;
        }

        return false;
    }

    /**
     * Attempt to Authenticate the Staff against the LoginShare plugin
     *
     * @author Varun Shoor
     * @param string $_username The Username
     * @param string $_password The Clear Text Password
     * @param string $_ipAddress The IP Address
     * @param bool $_shouldBeAdmin Whether its a login from Admin CP
     * @return bool|SWIFT_Staff
     * @throws SWIFT_Exception If Class is not Loaded or If Invalid Data is Provided
     */
    public function Authenticate($_username, $_password, $_ipAddress, $_shouldBeAdmin)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!self::IsActive()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_variableContainer = array();
        $_variableContainer['username'] = $_username;
        $_variableContainer['password'] = $_password;
        $_variableContainer['ipaddress'] = $_ipAddress;
        $_variableContainer['interface'] = SWIFT_INTERFACE;

        $_xmlResult = $this->DispatchPOST($_SWIFT->Settings->Get('loginshare_staffurl'), $_variableContainer);
        if (empty($_xmlResult)) {
            SWIFT_ErrorLog::Create(SWIFT_ErrorLog::TYPE_LOGINSHARE, 'Empty data received for Staff loginshare plugin');
            SWIFT::Set('errorstring', SWIFT_INVALIDDATA . ': 1');

            return false;
        }

        /*
         * BUG FIX - Varun Shoor
         *
         * SWIFT-943 If LoginShare is not properly implemented (and thus fails), SWIFT does not trigger fallback
         *
         * Comments: None
         */
        $_XMLObject = @simplexml_load_string(trim($_xmlResult));
        if (!isset($_XMLObject->result)) {
            SWIFT_ErrorLog::Create(SWIFT_ErrorLog::TYPE_LOGINSHARE, 'Invalid XML Received for Staff LoginShare Plugin', $_xmlResult);
            SWIFT::Set('errorstring', SWIFT_INVALIDDATA . ': 2');

            return false;
        }

        $_loginShareResult = (int)$_XMLObject->result;
        $_loginShareMessage = (string)$_XMLObject->message;

        // Login Failed?
        if (empty($_loginShareResult)) {
            SWIFT::Set('errorstring', $_loginShareMessage);

            return false;
        } elseif ($_loginShareResult == '1' && isset($_XMLObject->staff->firstname) && isset($_XMLObject->staff->lastname) && isset($_XMLObject->staff->designation)
            && isset($_XMLObject->staff->email) && isset($_XMLObject->staff->mobilenumber) && isset($_XMLObject->staff->signature)
            && isset($_XMLObject->staff->team)) {
            $_staffGroupTitle = (string)$_XMLObject->staff->team;
            $_staffFirstName = (string)$_XMLObject->staff->firstname;
            $_staffLastName = (string)$_XMLObject->staff->lastname;
            $_staffDesignation = (string)$_XMLObject->staff->designation;
            $_staffEmail = (string)$_XMLObject->staff->email;
            $_staffMobileNumber = (string)$_XMLObject->staff->mobilenumber;
            $_staffSignature = (string)$_XMLObject->staff->signature;

            // We now need to identify the staff
            $_staffContainer = SWIFT_Staff::RetrieveOnUsername($_username);

            // Now we attempt to retrieve the staff group
            if (isset($_staffContainer['staffgroupid'])) {
                $_staffGroupID = $_staffContainer['staffgroupid'];
            } else {
                $_staffGroupID = SWIFT_StaffGroup::RetrieveOnTitle($_staffGroupTitle);
            }

            $_SWIFT_StaffGroupObject = false;
            try {
                $_SWIFT_StaffGroupObject = new SWIFT_StaffGroup($_staffGroupID);
            } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            }
            if (empty($_staffGroupID) || !$_SWIFT_StaffGroupObject instanceof SWIFT_StaffGroup) {
                SWIFT_ErrorLog::Create(SWIFT_ErrorLog::TYPE_LOGINSHARE, 'Invalid XML Received for Staff LoginShare Plugin (Invalid Group)', $_xmlResult);
                SWIFT::Set('errorstring', SWIFT_INVALIDDATA . ': Invalid Group');

                return false;
            }

            // Admin Checks
            if ($_shouldBeAdmin == true && $_SWIFT_StaffGroupObject->GetProperty('isadmin') != '1') {
                SWIFT_ErrorLog::Create(SWIFT_ErrorLog::TYPE_LOGINSHARE, 'Invalid Group Attempt. Staff Group is not admin and staff is trying to login to Admin CP', $_xmlResult);
                SWIFT::Set('errorstring', 'Staff Group is not Admin');

                return false;
            }

            $_staffID = false;
            if (isset($_staffContainer['staffid'])) {
                $_staffID = $_staffContainer['staffid'];
                $_staffCache = $_SWIFT->Cache->Get('staffcache');
                if (isset($_staffCache[$_staffID]['signature']) && !empty($_staffCache[$_staffID]['signature'])) {
                    $_staffSignature = $_staffCache[$_staffID]['signature'];
                }
                if (isset($_staffContainer['designation']) && !empty($_staffContainer['designation'])) {
                    $_staffDesignation = $_staffContainer['designation'];
                }
                if (isset($_staffContainer['mobilenumber']) && !empty($_staffContainer['mobilenumber'])) {
                    $_staffMobileNumber = $_staffContainer['mobilenumber'];
                }
            }

            // If we found the staff then attempt to proceed
            if (!empty($_staffID)) {
                $_SWIFT_StaffObject = false;

                try {
                    $_SWIFT_StaffObject = new SWIFT_Staff(new SWIFT_DataID($_staffID));
                } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
                    SWIFT_ErrorLog::Create(SWIFT_ErrorLog::TYPE_LOGINSHARE, 'Invalid XML Received for Staff LoginShare Plugin (Invalid Staff: ' . $_staffID . ')', $_xmlResult);
                    SWIFT::Set('errorstring', SWIFT_INVALIDDATA . ': Invalid Staff (' . $_staffID . ')');

                    return false;
                }

                if (!$_SWIFT_StaffObject instanceof SWIFT_Staff || !$_SWIFT_StaffObject->GetIsClassLoaded()) {
                    SWIFT_ErrorLog::Create(SWIFT_ErrorLog::TYPE_LOGINSHARE, 'Invalid XML Received for Staff LoginShare Plugin (Invalid Staff 2: ' . $_staffID . ')', $_xmlResult);
                    SWIFT::Set('errorstring', SWIFT_INVALIDDATA . ': Invalid Staff 2 (' . $_staffID . ')');

                    return false;
                }

                // We also update the staff
                $_SWIFT_StaffObject->UpdateLoginShare($_staffFirstName, $_staffLastName, $_staffDesignation, $_username, $_staffGroupID, $_staffEmail, $_staffMobileNumber,
                    $_staffSignature);

                $_SWIFT_StaffObject->LoadIntoSWIFTNameSpace();

                $_SWIFT_StaffObject->UpdateLastVisit();

                return $_SWIFT_StaffObject;
            }

            // License limit check. Circumventing this will cause the helpesk to lock up
            $_activeStaffCount = SWIFT_Staff::ActiveStaffCount();
            if (SWIFT::Get('licensedstaff') != false && $_activeStaffCount >= SWIFT::Get('licensedstaff')) {
                SWIFT::Set('errorstring', $_SWIFT->Language->Get('licenselimit_unabletocreate'));
                return false;
            }

            // No staff found, create one
            $_SWIFT_StaffObject = SWIFT_Staff::Create($_staffFirstName, $_staffLastName, $_staffDesignation, $_username, substr(BuildHash(), 0, 14), $_staffGroupID,
                $_staffEmail, $_staffMobileNumber, $_staffSignature);
            SWIFT_Staff::RebuildCache();

            $_SWIFT_StaffObject->LoadIntoSWIFTNameSpace();

            $_SWIFT_StaffObject->UpdateLastVisit();

            return $_SWIFT_StaffObject;
        }

        SWIFT_ErrorLog::Create(SWIFT_ErrorLog::TYPE_LOGINSHARE, 'Invalid XML Received for Staff LoginShare Plugin', $_xmlResult);
        SWIFT::Set('errorstring', SWIFT_INVALIDDATA . ': 5');

        return false;
    }
}

?>
