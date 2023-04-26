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

namespace Base\Staff;

use Base\Admin\Controller_Staff;
use Base\Library\User\SWIFT_UserRenderManager;
use Base\Models\SearchStore\SWIFT_SearchStore;
use Base\Models\User\SWIFT_UserEmail;
use SWIFT;
use SWIFT_Exception;

/**
 * The User Search Controller
 *
 * @author Varun Shoor
 * @property SWIFT_UserRenderManager $UserRenderManager
 * @property View_UserSearch $View
 */
class Controller_UserSearch extends Controller_staff
{
    // Core Constants
    const MENU_ID = 8;
    const NAVIGATION_ID = 1;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @throws SWIFT_Exception
     */
    public function __construct()
    {
        $_SWIFT = SWIFT::GetInstance();

        parent::__construct();

        $this->Load->Library('User:UserRenderManager', [], true, false, 'base');

        $this->Language->Load('staff_users');

        if ($_SWIFT->Staff->GetPermission('cu_entab') == '0') {
            throw new SWIFT_Exception(SWIFT_NOPERMISSION);
        }
    }

    /**
     * The Search Form Renderer
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Advanced()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UserInterface->AddNavigationBox($this->Language->Get('quickfilter'), $this->UserRenderManager->RenderTree());

        $this->UserInterface->Header($this->Language->Get('users') . ' > ' . $this->Language->Get('search'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('staff_canviewusers') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render();
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Search a User
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function User()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_userIDList = array();

        $this->Database->Query("SELECT users.userid FROM " . TABLE_PREFIX . "useremails AS useremails
            LEFT JOIN " . TABLE_PREFIX . "users AS users ON (useremails.linktypeid = users.userid)
            LEFT JOIN " . TABLE_PREFIX . "userorganizations AS userorganizations ON (users.userorganizationid = userorganizations.userorganizationid)
            LEFT JOIN " . TABLE_PREFIX . "usergroups AS usergroups ON (users.usergroupid = usergroups.usergroupid)
            WHERE useremails.linktype = '" . SWIFT_UserEmail::LINKTYPE_USER . "' AND
                ((" . BuildSQLSearch('useremails.email', $_POST['query']) . ")
                    OR (" . BuildSQLSearch('users.fullname', $_POST['query']) . ")
                    OR (" . BuildSQLSearch('users.phone', $_POST['query']) . ")
                    OR (" . BuildSQLSearch('userorganizations.organizationname', $_POST['query']) . ")
                    OR (" . BuildSQLSearch('usergroups.title', $_POST['query']) . "))");
        while ($this->Database->NextRecord()) {
            $_userIDList[] = $this->Database->Record['userid'];
        }

        // If theres only one ticket to load then open it up
        if (count($_userIDList) == 1) {
            $this->Load->Controller('User')->Edit($_userIDList[0]);

            return true;
        }

        $_userEmailIDList = array();
        $this->Database->Query("SELECT useremails.useremailid FROM " . TABLE_PREFIX . "useremails AS useremails
            WHERE useremails.linktype = '" . SWIFT_UserEmail::LINKTYPE_USER . "'
                AND useremails.linktypeid IN (" . BuildIN($_userIDList) . ")");
        while ($this->Database->NextRecord()) {
            $_userEmailIDList[] = $this->Database->Record['useremailid'];
        }

        SWIFT_SearchStore::DeleteOnType(SWIFT_SearchStore::TYPE_USERS, $_SWIFT->Staff->GetStaffID());

        $_searchStoreID = SWIFT_SearchStore::Create($_SWIFT->Session->GetSessionID(), SWIFT_SearchStore::TYPE_USERS, $_userEmailIDList, $_SWIFT->Staff->GetStaffID());
        if (!_is_array($_userEmailIDList)) {
            SWIFT::Alert($this->Language->Get('titlesearchfailed'), $this->Language->Get('msgsearchfailed'));
        }

        $this->Load->Controller('User')->Manage($_searchStoreID);

        return true;
    }

    /**
     * Search for Organizations
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function UserOrganization()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_userOrganizationIDList = array();

        $this->Database->Query('SELECT userorganizationid FROM ' . TABLE_PREFIX . 'userorganizations
            WHERE ((' . BuildSQLSearch('organizationname', $_POST['query']) . ')
                OR (' . BuildSQLSearch('city', $_POST['query']) . ')
                OR (' . BuildSQLSearch('state', $_POST['query']) . ')
                OR (' . BuildSQLSearch('country', $_POST['query']) . ')
                OR (' . BuildSQLSearch('address', $_POST['query']) . ')
                OR (' . BuildSQLSearch('phone', $_POST['query']) . ')
                OR (' . BuildSQLSearch('website', $_POST['query']) . ')
                OR (' . BuildSQLSearch('postalcode', $_POST['query']) . ')
                OR (' . BuildSQLSearch('fax', $_POST['query']) . '))');
        while ($this->Database->NextRecord()) {
            $_userOrganizationIDList[] = $this->Database->Record['userorganizationid'];
        }

        // If theres only one ticket to load then open it up
        if (count($_userOrganizationIDList) == 1) {
            $this->Load->Controller('UserOrganization')->Edit($_userOrganizationIDList[0]);

            return true;
        }

        SWIFT_SearchStore::DeleteOnType(SWIFT_SearchStore::TYPE_USERORGANIZATIONS, $_SWIFT->Staff->GetStaffID());

        $_searchStoreID = SWIFT_SearchStore::Create($_SWIFT->Session->GetSessionID(), SWIFT_SearchStore::TYPE_USERORGANIZATIONS, $_userOrganizationIDList, $_SWIFT->Staff->GetStaffID());
        if (!_is_array($_userOrganizationIDList)) {
            SWIFT::Alert($this->Language->Get('titlesearchfailed'), $this->Language->Get('msgsearchfailed'));
        }

        $this->Load->Controller('UserOrganization')->Load->Method('Manage', $_searchStoreID);

        return true;
    }
}
