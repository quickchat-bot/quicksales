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

use SWIFT;
use SWIFT_Exception;
use SWIFT_Model;

/**
 * The Orgnization Email Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_UserOrganizationEmail extends SWIFT_UserEmailManager {
    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param int $_userEmailID The User Email ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public function __construct($_userEmailID) {
        parent::__construct($_userEmailID);

        if (SWIFT_INTERFACE !== 'tests' && $this->GetProperty('linktype') != self::LINKTYPE_ORGANIZATION)
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }
    }

    /**
     * Create a new User Email Record
     *
     * @author Varun Shoor
     * @param SWIFT_UserOrganization $_SWIFT_UserOrganizationObject The User Organization Object
     * @param string $_email The User Email
     * @param bool $_isPrimary Whether the Email is Primary one for this user
     * @return mixed "SWIFT_UserNote" Object on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided or If Creation Fails
     */
    public static function Create($_SWIFT_UserOrganizationObject, $_email, $_isPrimary = false, $_ = null) {
        if (!$_SWIFT_UserOrganizationObject instanceof SWIFT_UserOrganization || !$_SWIFT_UserOrganizationObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_userEmailID = parent::Create(self::LINKTYPE_ORGANIZATION, $_SWIFT_UserOrganizationObject->GetUserOrganizationID(), $_email, $_isPrimary);
        if (!$_userEmailID) {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        return new SWIFT_UserOrganizationEmail($_userEmailID);
    }

    /**
     * Delete the User Emails on the User Organization ID List
     *
     * @author Varun Shoor
     * @param array $_userOrganizationIDList The User Organization ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteOnUserOrganization($_userOrganizationIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_userOrganizationIDList))
        {
            return false;
        }

        $_userEmailIDList = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "useremails WHERE linktype = '" . self::LINKTYPE_ORGANIZATION . "' AND linktypeid IN (" . BuildIN($_userOrganizationIDList) . ")");
        while ($_SWIFT->Database->NextRecord())
        {
            $_userEmailIDList[] = $_SWIFT->Database->Record['useremailid'];
        }

        if (!count($_userEmailIDList))
        {
            return false;
        }

        self::DeleteList($_userEmailIDList);

        return true;
    }

    /**
     * Check to see if a given email already exists.. if it does, return the relevant user id
     *
     * @author Varun Shoor
     * @param array $_emailList The Email List
     * @param int $_currentUserOrganizationID (OPTIONAL) The Current User Organization ID to ignore
     * @return mixed array(email, linktypeid) on Success, "false" otherwise
     */
    public static function CheckEmailRecordExists($_emailList, $_currentUserOrganizationID = null)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_emailList))
        {
            return false;
        }

        foreach ($_emailList as $_key => $_val)
        {
            if (substr($_val, 0, 1) != '@' && !in_array('@' . $_val, $_emailList))
            {
                $_emailList[] = '@' . $_val;
            }
        }

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "useremails WHERE linktype = '" . self::LINKTYPE_ORGANIZATION . "' AND email IN (" . BuildIN($_emailList) . ")");
        while ($_SWIFT->Database->NextRecord())
        {
            if (!empty($_currentUserOrganizationID) && $_SWIFT->Database->Record['linktypeid'] == $_currentUserOrganizationID)
            {
                // Belongs to the current user ignore...
            } else {
                return array($_SWIFT->Database->Record['email'], $_SWIFT->Database->Record['linktypeid']);
            }
        }

        return false;
    }

    /**
     * Retrieve all emails for the given user organization id
     *
     * @author Varun Shoor
     * @param int $_userOrganizationID The User Organization ID
     * @return mixed "_emailList" (ARRAY) on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveList($_userOrganizationID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_userOrganizationID))
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_emailList = array();

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "useremails WHERE linktype = '" . self::LINKTYPE_ORGANIZATION . "' AND linktypeid = '" . $_userOrganizationID . "'");
        while ($_SWIFT->Database->NextRecord())
        {
            $_emailList[] = $_SWIFT->Database->Record['email'];
        }

        return $_emailList;
    }

    /**
     * Try to retrieve the Organization from a list of emails
     *
     * @author Varun Shoor
     * @param array $_emailList The Email List
     * @return int
     */
    public static function GetOrganizationFromEmailList($_emailList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_emailList))
        {
            return 0;
        }

        $_finalDomainList = array();
        foreach ($_emailList as $_key => $_val)
        {
            if (!IsEmailValid($_val))
            {
                continue;
            }

            $_symbolPosition = strrpos($_val, '@');
            if ($_symbolPosition === false)
            {
                continue;
            }

            $_finalDomainList[] = substr($_val, $_symbolPosition); // @kayako.com
            $_finalDomainList[] = substr($_val, $_symbolPosition + 1); // kayako.com
        }

        if (!count($_finalDomainList))
        {
            return 0;
        }

        $_userOrganizationID = false;

        $_userEmailContainer = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "useremails WHERE linktype = '" . self::LINKTYPE_ORGANIZATION . "' AND email IN (" . BuildIN($_finalDomainList) . ")");
        if (isset($_userEmailContainer['useremailid']))
        {
            $_userOrganizationID = (int) ($_userEmailContainer['linktypeid']);
        }

        if ($_userOrganizationID)
        {
            try
            {
                $_SWIFT_UserOrganizationObject = new SWIFT_UserOrganization($_userOrganizationID);
                if ($_SWIFT_UserOrganizationObject instanceof SWIFT_UserOrganization && $_SWIFT_UserOrganizationObject->GetIsClassLoaded())
                {
                    return $_SWIFT_UserOrganizationObject->GetUserOrganizationID();
                }
            } catch (SWIFT_Exception $_SWIFT_UserExceptionObject) {

            }
        }

        return 0;
    }
}
?>
