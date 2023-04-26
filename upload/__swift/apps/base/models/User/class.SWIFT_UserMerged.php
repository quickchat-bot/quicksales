<?php

/**
 *
 * SWIFT Framework
 * _______________________________________________
 *
 * @author         Varun Shoor
 *
 * @package        SWIFT
 * @copyright      Copyright (c) 2001-2012, QuickSupport
 * @license        http://www.kayako.com/license
 * @link           http://www.kayako.com
 *
 *  */

namespace Base\Models\User;

use SWIFT;
use SWIFT_Exception;
use SWIFT_Model;

/**
 * User Merged Model
 *
 * This model contains all the historical changes that happen on user.
 *
 * ###################################################
 * Database Structure
 * ###################################################
 * usersmergedid - Primary Key
 * primaryuserid - The active user id
 * primaryusername - The active user name
 * secondaryuserid - The user id which merged with primary user
 * secondaryusername - The secondary user name
 * dateline - The date of action
 *
 * @author Rajat Garg
 */
class SWIFT_UserMerged extends SWIFT_Model
{
    const TABLE_NAME = 'usersmerged';
    const PRIMARY_KEY = 'usersmergedid';
    const TABLE_STRUCTURE = "usersmergedid I PRIMARY AUTO NOTNULL,
                                primaryuserid I DEFAULT '0' NOTNULL,
                                primaryusername C(200) DEFAULT '' NOTNULL,
                                secondaryuserid I DEFAULT '0' NOTNULL,
                                secondaryusername C(200) DEFAULT '' NOTNULL,
                                dateline I DEFAULT '0' NOTNULL";
    const INDEX_1 = 'secondaryuserid';

    /**
     * @author Rajat Garg
     *
     * @param int $_primaryUserID
     * @param array $_secondaryUserIDList
     *
     * @return bool
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function Insert($_primaryUserID, $_secondaryUserIDList)
    {
        $_SWIFT = SWIFT::GetInstance();
        if (!_is_array($_secondaryUserIDList)) {
            return false;
        }
        $_UserPrimary = new SWIFT_User($_primaryUserID);
        if (!$_UserPrimary instanceof SWIFT_User) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }
        $_primaryUserName = $_UserPrimary->GetProperty('fullname');
        foreach ($_secondaryUserIDList as $_secondaryUserID) {
            $_UserSecondary = new SWIFT_User($_secondaryUserID);
            if (!$_UserSecondary instanceof SWIFT_User) {
                continue;
            }
            $_secondaryUserName = $_UserSecondary->GetProperty('fullname');
            $_SWIFT->Database->AutoExecute(TABLE_PREFIX . self::TABLE_NAME, array(
                'primaryuserid' => $_primaryUserID,
                'primaryusername' => $_primaryUserName,
                'secondaryuserid' => $_secondaryUserID,
                'secondaryusername' => $_secondaryUserName,
                'dateline' => DATENOW
            ), 'INSERT');
        }
        return true;
    }
}
