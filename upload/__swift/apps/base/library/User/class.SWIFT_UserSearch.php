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

namespace Base\Library\User;

use Base\Library\Rules\SWIFT_Rules;
use Base\Models\User\SWIFT_User;
use Base\Models\User\SWIFT_UserGroup;
use SWIFT;
use SWIFT_App;

/**
 * The User Search Management Lib
 *
 * @author Varun Shoor
 */
class SWIFT_UserSearch extends SWIFT_Rules
{
    // Criteria
    const USERSEARCH_FULLNAME = 'fullname';
    const USERSEARCH_EMAIL = 'email';
    const USERSEARCH_DESIGNATION = 'designation';
    const USERSEARCH_PHONE = 'phone';
    const USERSEARCH_SALUTATION = 'salutation';

    const USERSEARCH_GROUP = 'usergroup';
    const USERSEARCH_ROLE = 'userrole';
    const USERSEARCH_ORGANIZATION = 'organization';
    const USERSEARCH_DATEREGISTERED = 'dateregistered';
    const USERSEARCH_DATEREGISTEREDRANGE = 'dateregisteredrange';
    const USERSEARCH_DATELASTUPDATE = 'datelastupdate';
    const USERSEARCH_DATELASTUPDATERANGE = 'datelastupdaterange';
    const USERSEARCH_DATELASTVISIT = 'datelastvisit';
    const USERSEARCH_DATELASTVISITRANGE = 'datelastvisitrange';
    const USERSEARCH_ISENABLED = 'isenabled';
    const USERSEARCH_SLAPLAN = 'slaplan';
    const USERSEARCH_USEREXPIRY = 'userexpiry';
    const USERSEARCH_SLAEXPIRY = 'slaexpiry';

    /**
     * Extends the $_criteria array with custom field data (like departments etc.)
     *
     * @author Varun Shoor
     * @param array $_criteriaPointer The Criteria Pointer
     * @return bool "true" on Success, "false" otherwise
     */
    public static function ExtendCustomCriteria(&$_criteriaPointer)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (SWIFT_App::IsInstalled(APP_TICKETS)) {
            // ======= SLA PLAN =======
            $_field = array();
            $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "slaplans ORDER BY title ASC");
            while ($_SWIFT->Database->NextRecord()) {
                $_field[] = array('title' => $_SWIFT->Database->Record['title'], 'contents' => $_SWIFT->Database->Record['slaplanid']);
            }

            if (!count($_field)) {
                $_field[] = array('title' => $_SWIFT->Language->Get('notapplicable'), 'contents' => '0');
            }

            $_criteriaPointer[self::USERSEARCH_SLAPLAN]['fieldcontents'] = $_field;
        }

        // ======= USER GROUPS =======
        $_field = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "usergroups ORDER BY title ASC");
        while ($_SWIFT->Database->NextRecord()) {
            if ($_SWIFT->Database->Record['grouptype'] != SWIFT_UserGroup::TYPE_REGISTERED) {
                continue;
            }

            $_field[] = array('title' => $_SWIFT->Database->Record['title'], 'contents' => $_SWIFT->Database->Record['usergroupid']);
        }

        if (!count($_field)) {
            $_field[] = array('title' => $_SWIFT->Language->Get('notapplicable'), 'contents' => '0');
        }

        $_criteriaPointer[self::USERSEARCH_GROUP]['fieldcontents'] = $_field;

        // ======= SALUTATION =======
        $_field = array();

        foreach (SWIFT_User::RetrieveSalutationList() as $_key => $_val) {
            $_field[] = array('title' => $_val, 'contents' => $_key);
        }

        $_criteriaPointer[self::USERSEARCH_SALUTATION]["fieldcontents"] = $_field;

        // ======= USER ROLE =======
        $_field = array();
        $_field[] = array('title' => $_SWIFT->Language->Get('umanager'), 'contents' => SWIFT_User::ROLE_MANAGER);
        $_field[] = array('title' => $_SWIFT->Language->Get('uuser'), 'contents' => SWIFT_User::ROLE_USER);

        $_criteriaPointer[self::USERSEARCH_ROLE]["fieldcontents"] = $_field;

        return true;
    }

    /**
     * Return the Criteria for the User Search
     *
     * @author Varun Shoor
     * @return mixed "_criteriaPointer" (ARRAY) on Success, "false" otherwise
     */
    public static function GetCriteriaPointer()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_criteriaPointer = array();

        $_criteriaPointer[self::USERSEARCH_FULLNAME]['title'] = $_SWIFT->Language->Get('us' . self::USERSEARCH_FULLNAME);
        $_criteriaPointer[self::USERSEARCH_FULLNAME]['desc'] = $_SWIFT->Language->Get('desc_us' . self::USERSEARCH_FULLNAME);
        $_criteriaPointer[self::USERSEARCH_FULLNAME]['op'] = 'string';
        $_criteriaPointer[self::USERSEARCH_FULLNAME]['field'] = 'text';

        $_criteriaPointer[self::USERSEARCH_EMAIL]['title'] = $_SWIFT->Language->Get('us' . self::USERSEARCH_EMAIL);
        $_criteriaPointer[self::USERSEARCH_EMAIL]['desc'] = $_SWIFT->Language->Get('desc_us' . self::USERSEARCH_EMAIL);
        $_criteriaPointer[self::USERSEARCH_EMAIL]['op'] = 'string';
        $_criteriaPointer[self::USERSEARCH_EMAIL]['field'] = 'text';

        $_criteriaPointer[self::USERSEARCH_DESIGNATION]['title'] = $_SWIFT->Language->Get('us' . self::USERSEARCH_DESIGNATION);
        $_criteriaPointer[self::USERSEARCH_DESIGNATION]['desc'] = $_SWIFT->Language->Get('desc_us' . self::USERSEARCH_DESIGNATION);
        $_criteriaPointer[self::USERSEARCH_DESIGNATION]['op'] = 'string';
        $_criteriaPointer[self::USERSEARCH_DESIGNATION]['field'] = 'text';

        $_criteriaPointer[self::USERSEARCH_PHONE]['title'] = $_SWIFT->Language->Get('us' . self::USERSEARCH_PHONE);
        $_criteriaPointer[self::USERSEARCH_PHONE]['desc'] = $_SWIFT->Language->Get('desc_us' . self::USERSEARCH_PHONE);
        $_criteriaPointer[self::USERSEARCH_PHONE]['op'] = 'string';
        $_criteriaPointer[self::USERSEARCH_PHONE]['field'] = 'text';

        $_criteriaPointer[self::USERSEARCH_SALUTATION]['title'] = $_SWIFT->Language->Get('us' . self::USERSEARCH_SALUTATION);
        $_criteriaPointer[self::USERSEARCH_SALUTATION]['desc'] = $_SWIFT->Language->Get('desc_us' . self::USERSEARCH_SALUTATION);
        $_criteriaPointer[self::USERSEARCH_SALUTATION]['op'] = 'bool';
        $_criteriaPointer[self::USERSEARCH_SALUTATION]['field'] = 'custom';

        $_criteriaPointer[self::USERSEARCH_GROUP]['title'] = $_SWIFT->Language->Get('us' . self::USERSEARCH_GROUP);
        $_criteriaPointer[self::USERSEARCH_GROUP]['desc'] = $_SWIFT->Language->Get('desc_us' . self::USERSEARCH_GROUP);
        $_criteriaPointer[self::USERSEARCH_GROUP]['op'] = 'bool';
        $_criteriaPointer[self::USERSEARCH_GROUP]['field'] = 'custom';

        $_criteriaPointer[self::USERSEARCH_ROLE]['title'] = $_SWIFT->Language->Get('us' . self::USERSEARCH_ROLE);
        $_criteriaPointer[self::USERSEARCH_ROLE]['desc'] = $_SWIFT->Language->Get('desc_us' . self::USERSEARCH_ROLE);
        $_criteriaPointer[self::USERSEARCH_ROLE]['op'] = 'bool';
        $_criteriaPointer[self::USERSEARCH_ROLE]['field'] = 'custom';

        $_criteriaPointer[self::USERSEARCH_ORGANIZATION]['title'] = $_SWIFT->Language->Get('us' . self::USERSEARCH_ORGANIZATION);
        $_criteriaPointer[self::USERSEARCH_ORGANIZATION]['desc'] = $_SWIFT->Language->Get('desc_us' . self::USERSEARCH_ORGANIZATION);
        $_criteriaPointer[self::USERSEARCH_ORGANIZATION]['op'] = 'string';
        $_criteriaPointer[self::USERSEARCH_ORGANIZATION]['field'] = 'text';

        $_criteriaPointer[self::USERSEARCH_ISENABLED]['title'] = $_SWIFT->Language->Get('us' . self::USERSEARCH_ISENABLED);
        $_criteriaPointer[self::USERSEARCH_ISENABLED]['desc'] = $_SWIFT->Language->Get('desc_us' . self::USERSEARCH_ISENABLED);
        $_criteriaPointer[self::USERSEARCH_ISENABLED]['op'] = 'bool';
        $_criteriaPointer[self::USERSEARCH_ISENABLED]['field'] = 'bool';

        if (SWIFT_App::IsInstalled(APP_TICKETS)) {
            $_criteriaPointer[self::USERSEARCH_SLAPLAN]['title'] = $_SWIFT->Language->Get('us' . self::USERSEARCH_SLAPLAN);
            $_criteriaPointer[self::USERSEARCH_SLAPLAN]['desc'] = $_SWIFT->Language->Get('desc_us' . self::USERSEARCH_SLAPLAN);
            $_criteriaPointer[self::USERSEARCH_SLAPLAN]['op'] = 'bool';
            $_criteriaPointer[self::USERSEARCH_SLAPLAN]['field'] = 'custom';
        }

        $_criteriaPointer[self::USERSEARCH_DATEREGISTERED]['title'] = $_SWIFT->Language->Get('us' . self::USERSEARCH_DATEREGISTERED);
        $_criteriaPointer[self::USERSEARCH_DATEREGISTERED]['desc'] = $_SWIFT->Language->Get('desc_us' . self::USERSEARCH_DATEREGISTERED);
        $_criteriaPointer[self::USERSEARCH_DATEREGISTERED]['op'] = 'int';
        $_criteriaPointer[self::USERSEARCH_DATEREGISTERED]['field'] = 'cal';

        $_criteriaPointer[self::USERSEARCH_DATEREGISTEREDRANGE]['title'] = $_SWIFT->Language->Get('us' . self::USERSEARCH_DATEREGISTEREDRANGE);
        $_criteriaPointer[self::USERSEARCH_DATEREGISTEREDRANGE]['desc'] = $_SWIFT->Language->Get('desc_us' . self::USERSEARCH_DATEREGISTEREDRANGE);
        $_criteriaPointer[self::USERSEARCH_DATEREGISTEREDRANGE]['op'] = 'resbool';
        $_criteriaPointer[self::USERSEARCH_DATEREGISTEREDRANGE]['field'] = 'daterange';

        $_criteriaPointer[self::USERSEARCH_DATELASTUPDATE]['title'] = $_SWIFT->Language->Get('us' . self::USERSEARCH_DATELASTUPDATE);
        $_criteriaPointer[self::USERSEARCH_DATELASTUPDATE]['desc'] = $_SWIFT->Language->Get('desc_us' . self::USERSEARCH_DATELASTUPDATE);
        $_criteriaPointer[self::USERSEARCH_DATELASTUPDATE]['op'] = 'int';
        $_criteriaPointer[self::USERSEARCH_DATELASTUPDATE]['field'] = 'cal';

        $_criteriaPointer[self::USERSEARCH_DATELASTUPDATERANGE]['title'] = $_SWIFT->Language->Get('us' . self::USERSEARCH_DATELASTUPDATERANGE);
        $_criteriaPointer[self::USERSEARCH_DATELASTUPDATERANGE]['desc'] = $_SWIFT->Language->Get('desc_us' . self::USERSEARCH_DATELASTUPDATERANGE);
        $_criteriaPointer[self::USERSEARCH_DATELASTUPDATERANGE]['op'] = 'resbool';
        $_criteriaPointer[self::USERSEARCH_DATELASTUPDATERANGE]['field'] = 'daterange';

        $_criteriaPointer[self::USERSEARCH_DATELASTVISIT]['title'] = $_SWIFT->Language->Get('us' . self::USERSEARCH_DATELASTVISIT);
        $_criteriaPointer[self::USERSEARCH_DATELASTVISIT]['desc'] = $_SWIFT->Language->Get('desc_us' . self::USERSEARCH_DATELASTVISIT);
        $_criteriaPointer[self::USERSEARCH_DATELASTVISIT]['op'] = 'int';
        $_criteriaPointer[self::USERSEARCH_DATELASTVISIT]['field'] = 'cal';

        $_criteriaPointer[self::USERSEARCH_DATELASTVISITRANGE]['title'] = $_SWIFT->Language->Get('us' . self::USERSEARCH_DATELASTVISITRANGE);
        $_criteriaPointer[self::USERSEARCH_DATELASTVISITRANGE]['desc'] = $_SWIFT->Language->Get('desc_us' . self::USERSEARCH_DATELASTVISITRANGE);
        $_criteriaPointer[self::USERSEARCH_DATELASTVISITRANGE]['op'] = 'resbool';
        $_criteriaPointer[self::USERSEARCH_DATELASTVISITRANGE]['field'] = 'daterange';

        $_criteriaPointer[self::USERSEARCH_USEREXPIRY]['title'] = $_SWIFT->Language->Get('us' . self::USERSEARCH_USEREXPIRY);
        $_criteriaPointer[self::USERSEARCH_USEREXPIRY]['desc'] = $_SWIFT->Language->Get('desc_us' . self::USERSEARCH_USEREXPIRY);
        $_criteriaPointer[self::USERSEARCH_USEREXPIRY]['op'] = 'int';
        $_criteriaPointer[self::USERSEARCH_USEREXPIRY]['field'] = 'cal';

        if (SWIFT_App::IsInstalled(APP_TICKETS)) {
            $_criteriaPointer[self::USERSEARCH_SLAEXPIRY]['title'] = $_SWIFT->Language->Get('us' . self::USERSEARCH_SLAEXPIRY);
            $_criteriaPointer[self::USERSEARCH_SLAEXPIRY]['desc'] = $_SWIFT->Language->Get('desc_us' . self::USERSEARCH_SLAEXPIRY);
            $_criteriaPointer[self::USERSEARCH_SLAEXPIRY]['op'] = 'int';
            $_criteriaPointer[self::USERSEARCH_SLAEXPIRY]['field'] = 'cal';
        }

        return $_criteriaPointer;
    }

    /**
     * Returns the field pointer
     *
     * @author Varun Shoor
     * @return array
     */
    public static function GetFieldPointer()
    {
        $_fieldPointer = array();
        $_fieldPointer[self::USERSEARCH_FULLNAME] = 'users.fullname';
        $_fieldPointer[self::USERSEARCH_EMAIL] = 'useremails.email';
        $_fieldPointer[self::USERSEARCH_DESIGNATION] = 'users.userdesignation';
        $_fieldPointer[self::USERSEARCH_PHONE] = 'users.phone';
        $_fieldPointer[self::USERSEARCH_SALUTATION] = 'users.salutation';
        $_fieldPointer[self::USERSEARCH_GROUP] = 'users.usergroupid';
        $_fieldPointer[self::USERSEARCH_ROLE] = 'users.userrole';
        $_fieldPointer[self::USERSEARCH_ORGANIZATION] = 'userorganizations.organizationname';
        $_fieldPointer[self::USERSEARCH_ISENABLED] = 'users.isenabled';
        $_fieldPointer[self::USERSEARCH_SLAPLAN] = 'users.slaplanid';

        $_fieldPointer[self::USERSEARCH_DATEREGISTERED] = 'users.dateline';
        $_fieldPointer[self::USERSEARCH_DATEREGISTEREDRANGE] = 'users.dateline';
        $_fieldPointer[self::USERSEARCH_DATELASTUPDATE] = 'users.lastupdate';
        $_fieldPointer[self::USERSEARCH_DATELASTUPDATERANGE] = 'users.lastupdate';
        $_fieldPointer[self::USERSEARCH_DATELASTVISIT] = 'users.lastvisit';
        $_fieldPointer[self::USERSEARCH_DATELASTVISITRANGE] = 'users.lastvisit';
        $_fieldPointer[self::USERSEARCH_USEREXPIRY] = 'users.userexpirytimeline';
        $_fieldPointer[self::USERSEARCH_SLAEXPIRY] = 'users.slaexpirytimeline';

        return $_fieldPointer;
    }
}

?>
