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

namespace Base\Models\User;

use Base\Models\Staff\SWIFT_Staff;
use InvalidArgumentException;
use SWIFT;
use SWIFT_Exception;
use SWIFT_Model;

/**
 * The User Note Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_UserNote extends SWIFT_UserNoteManager {
    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param int $_userNoteID The User Note ID
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct($_userNoteID) {
        parent::__construct($_userNoteID);
    }

    /**
     * Create a new User Note
     *
     * @author Varun Shoor
     * @param SWIFT_User $_SWIFT_UserObject The User Object
     * @param string $_noteContents The Note Contents
     * @param int $_noteColor The Note Color
     * @return mixed "SWIFT_UserNote" Object on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided or If Creation Fails
     */
    public static function Create($_SWIFT_UserObject, $_noteContents, $_noteColor = 1, $_SWIFT_StaffObject = false, $_ = null, $__ = null) {
        if (!$_SWIFT_UserObject instanceof SWIFT_User) {
            throw new InvalidArgumentException();
        }

        $_SWIFT = SWIFT::GetInstance();

        /**
         * BUG FIX - Mansi Wason <mansi.wason@kayako.com>
         *
         * SWIFT-2103 - Adding User Note via Ticket Followup results [User Error] : Invalid data provided
         */
        if ($_SWIFT_StaffObject == false) {
            $_SWIFT_StaffObject = $_SWIFT->Staff;
        }

        if (!$_SWIFT_UserObject instanceof SWIFT_User || !$_SWIFT_UserObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        } else if (!$_SWIFT_StaffObject instanceof SWIFT_Staff || !$_SWIFT_StaffObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_userNoteID = parent::Create(self::LINKTYPE_USER, $_SWIFT_UserObject->GetUserID(), $_SWIFT_StaffObject->GetStaffID(), $_SWIFT_StaffObject->GetProperty('fullname'), $_noteContents, $_noteColor);
        if (!$_userNoteID) {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        return new SWIFT_UserNote($_userNoteID);
    }

    /**
     * Delete User Notes based on User ID List
     *
     * @author Varun Shoor
     * @param array $_userIDList The User ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteOnUser($_userIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_userIDList))
        {
            return false;
        }

        $_userNoteIDList = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "usernotes WHERE linktype = '" . self::LINKTYPE_USER . "' AND linktypeid IN (" . BuildIN($_userIDList) . ")");
        while ($_SWIFT->Database->NextRecord())
        {
            $_userNoteIDList[] = $_SWIFT->Database->Record['usernoteid'];
        }

        if (!count($_userNoteIDList))
        {
            return false;
        }

        self::DeleteList($_userNoteIDList);

        return true;
    }
}
?>
