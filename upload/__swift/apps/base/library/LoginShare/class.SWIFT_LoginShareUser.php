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

namespace Base\Library\LoginShare;

use Base\Library\LoginShare\SWIFT_LoginShare;
use Base\Models\User\SWIFT_User;
use Base\Models\User\SWIFT_UserEmail;
use Base\Models\User\SWIFT_UserGroup;
use Base\Models\User\SWIFT_UserOrganization;
use SWIFT;
use SWIFT_DataID;
use SWIFT_ErrorLog;
use SWIFT_Exception;

/**
 * The User LoginShare Class
 *
 * @author Varun Shoor
 */
class SWIFT_LoginShareUser extends SWIFT_LoginShare
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

        if ($_SWIFT->Settings->Get('loginshare_userenable') == '1' && $_SWIFT->Settings->Get('loginshare_userurl') != '') {
            return true;
        }

        return false;
    }

    /**
     * Attempt to Authenticate the user against the LoginShare plugin
     *
     * @author Varun Shoor
     * @param string $_username The Username
     * @param string $_password The Clear Text Password
     * @param string $_ipAddress The IP Address
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Class is not Loaded or If Invalid Data is Provided
     */
    public function Authenticate($_username, $_password, $_ipAddress)
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

        $_xmlResult = $this->DispatchPOST($_SWIFT->Settings->Get('loginshare_userurl'), $_variableContainer);
        if (empty($_xmlResult)) {
            SWIFT_ErrorLog::Create(SWIFT_ErrorLog::TYPE_LOGINSHARE, 'Empty data received for user loginshare plugin');
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
            SWIFT_ErrorLog::Create(SWIFT_ErrorLog::TYPE_LOGINSHARE, 'Invalid XML Received for User LoginShare Plugin', $_xmlResult);
            SWIFT::Set('errorstring', SWIFT_INVALIDDATA . ': 2');

            return false;
        }

        $_loginShareResult = (int)$_XMLObject->result;
        $_loginShareMessage = (string)$_XMLObject->message;

        if (empty($_loginShareResult)) {
            // Authentication failed
            SWIFT::Set('errorstring', $_loginShareMessage);
            return false;

        } elseif ($_loginShareResult == '1' && isset($_XMLObject->user->usergroup) && isset($_XMLObject->user->fullname) && isset($_XMLObject->user->emails)) {

            $_userGroupTitle = (string)$_XMLObject->user->usergroup;
            $_userFullName = (string)$_XMLObject->user->fullname;

            $_userDesignation = '';
            $_userPhone = '';
            $_userOrganizationTitle = '';
            $_userOrganizationID = 0;

            // Check to see if a phone number has been provided
            if (isset($_XMLObject->user->phone)) {
                $_userPhone = (string)$_XMLObject->user->phone;
            }

            // Check to see if a user designation has been provided
            if (isset($_XMLObject->user->designation)) {
                $_userDesignation = (string)$_XMLObject->user->designation;
            }

            $_userEmailList = array();
            foreach ($_XMLObject->user->emails->email as $_EmailObject) {
                $_emailAddress = (string)$_EmailObject;

                if (!IsEmailValid($_emailAddress)) {
                    continue;
                }

                $_userEmailList[] = mb_strtolower($_emailAddress);
            }

            // First we check that we received atleast one valid email address
            if (!count($_userEmailList)) {
                SWIFT_ErrorLog::Create(SWIFT_ErrorLog::TYPE_LOGINSHARE, 'Invalid XML Received for User LoginShare Plugin (No Emails)', $_xmlResult);
                SWIFT::Set('errorstring', SWIFT_INVALIDDATA . ': No Emails');

                return false;
            }

            // Now we attempt to retrieve the user group
            $_userGroupID = SWIFT_UserGroup::RetrieveOnTitle($_userGroupTitle);

            // Attempt to retrieve the user organization
            if (isset($_XMLObject->user->organization) && !empty($_XMLObject->user->organization)) {
                $_userOrganizationTitle = (string)$_XMLObject->user->organization;

                // First check existing title..
                $_userOrganizationContainer = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "userorganizations WHERE organizationname LIKE '" . $this->Database->Escape($_userOrganizationTitle) . "'");
                if (isset($_userOrganizationContainer['userorganizationid']) && !empty($_userOrganizationContainer['userorganizationid'])) {
                    $_userOrganizationID = $_userOrganizationContainer['userorganizationid'];
                } else {
                    // If no match.. then only create a new user organization
                    $_userOrganizationType = SWIFT_UserOrganization::TYPE_RESTRICTED;

                    if (isset($_XMLObject->user->organizationtype) && strtolower($_XMLObject->user->organizationtype) == 'shared') {
                        $_userOrganizationType = SWIFT_UserOrganization::TYPE_SHARED;
                    }

                    $_SWIFT_UserOrganizationObject = SWIFT_UserOrganization::Create($_userOrganizationTitle, $_userOrganizationType);
                    if ($_SWIFT_UserOrganizationObject instanceof SWIFT_UserOrganization && $_SWIFT_UserOrganizationObject->GetIsClassLoaded()) {
                        $_userOrganizationID = $_SWIFT_UserOrganizationObject->GetUserOrganizationID();
                    }
                }
            }

            // We now need to identify the user
            $_userID = false;

            foreach ($_userEmailList as $_emailAddress) {
                $_emailUserID = SWIFT_UserEmail::RetrieveUserIDOnUserEmail($_emailAddress);

                // User found!
                if (!empty($_emailUserID)) {
                    $_userID = $_emailUserID;
                }
            }

            // If we found the user then attempt to proceed
            if (!empty($_userID)) {
                $_SWIFT_UserObject = false;

                try {
                    $_SWIFT_UserObject = new SWIFT_User(new SWIFT_DataID($_userID));
                } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
                    SWIFT_ErrorLog::Create(SWIFT_ErrorLog::TYPE_LOGINSHARE, 'Invalid XML Received for User LoginShare Plugin (Invalid User: ' . $_userID . ')', $_xmlResult);
                    SWIFT::Set('errorstring', SWIFT_INVALIDDATA . ': Invalid User (' . $_userID . ')');

                    return false;
                }

                if (!$_SWIFT_UserObject instanceof SWIFT_User || !$_SWIFT_UserObject->GetIsClassLoaded()) {
                    SWIFT_ErrorLog::Create(SWIFT_ErrorLog::TYPE_LOGINSHARE, 'Invalid XML Received for User LoginShare Plugin (Invalid User 2: ' . $_userID . ')', $_xmlResult);
                    SWIFT::Set('errorstring', SWIFT_INVALIDDATA . ': Invalid User 2 (' . $_userID . ')');

                    return false;
                }

                // By now we will have the User object.. attempt to update it
                $_existingUserEmailList = $_SWIFT_UserObject->GetEmailList();
                $_finalUserEmailList = $_userEmailList;

                // Create an email address for this user if it doesnt exist
                foreach ($_userEmailList as $_emailAddress) {
                    if (!in_array($_emailAddress, $_existingUserEmailList)) {
                        $_userIDForEmail = SWIFT_UserEmail::RetrieveUserIDOnUserEmail($_emailAddress);
                        if (empty($_userIDForEmail)) {
                            SWIFT_UserEmail::Create($_SWIFT_UserObject, $_emailAddress);

                            $_finalUserEmailList[] = $_emailAddress;
                        }
                    }
                }

                // Update the user record
                if (!empty($_userGroupID)) {
                    $_SWIFT_UserObject->UpdateLoginShare($_userGroupID, $_userFullName);
                }

                if (!empty($_userDesignation)) {
                    $_SWIFT_UserObject->UpdateUserDesignation($_userDesignation);
                }

                if (!empty($_userPhone)) {
                    $_SWIFT_UserObject->UpdateUserPhoneNumber($_userPhone);
                }

                // User authenticated
                $_SWIFT_UserObject->LoadIntoSWIFTNameSpace();
                $_SWIFT_UserObject->UpdateLastVisit();

                return true;
            }

            // Cant proceed with empty user group
            if (empty($_userGroupID)) {
                SWIFT_ErrorLog::Create(SWIFT_ErrorLog::TYPE_LOGINSHARE, 'Invalid XML Received for User LoginShare Plugin (Invalid Group)', $_xmlResult);
                SWIFT::Set('errorstring', SWIFT_INVALIDDATA . ': Invalid Group');

                return false;
            }

            // No user found, create one
            $_SWIFT_UserObject = SWIFT_User::Create($_userGroupID, $_userOrganizationID, SWIFT_User::SALUTATION_NONE, $_userFullName, $_userDesignation, $_userPhone, true, SWIFT_User::ROLE_USER,
                $_userEmailList, substr(BuildHash(), 0, 14), 0, '', false, 0, 0, 0, false, true, false);

            // User should be authenticated by now..
            $_SWIFT_UserObject->LoadIntoSWIFTNameSpace();

            $_SWIFT_UserObject->UpdateLastVisit();

            return true;
        }

        SWIFT_ErrorLog::Create(SWIFT_ErrorLog::TYPE_LOGINSHARE, 'Invalid XML Received for User LoginShare Plugin', $_xmlResult);
        SWIFT::Set('errorstring', SWIFT_INVALIDDATA . ': 5');

        return false;
    }
}

?>
