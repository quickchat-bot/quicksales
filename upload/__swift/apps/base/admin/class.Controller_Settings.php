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

namespace Base\Admin;

use Controller_admin;
use SWIFT;
use SWIFT_Exception;
use SWIFT_SettingsManager;
use Base\Library\UserInterface\SWIFT_UserInterface;

/**
 * The Settings Controller
 *
 * @property \Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel $UserInterface
 * @property SWIFT_SettingsManager $SettingsManager
 * @property View_Settings $View
 * @author Varun Shoor
 */
class Controller_Settings extends Controller_admin
{
    // Core Constants
    const MENU_ID = 1;
    const NAVIGATION_ID = 14;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct()
    {
        parent::__construct();

        $this->Load->Library('Settings:SettingsManager');

        $this->Language->Load('settings');
        $this->Language->LoadNonCoreApps('settings');
    }

    /**
     * Render the Settings List
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function Index()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->UserInterface->Header($this->Language->Get('settings'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_canupdatesettings') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->RenderList();
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Render a Setting Group
     *
     * @author Varun Shoor
     * @param int $_settingGroupID The Setting Group ID
     * @return bool "true" on Success, "false" otherwise
     */
    public function View($_settingGroupID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded() || empty($_settingGroupID)) {
            return false;
        }

        $_settingGroupContainer = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "settingsgroups WHERE sgroupid = '" . $_settingGroupID . "'");
        if (!isset($_settingGroupContainer['sgroupid']) || empty($_settingGroupContainer['sgroupid'])) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->UserInterface->Header($this->Language->Get('settings') . ' > ' . $this->Language->Get($_settingGroupContainer['name']), self::MENU_ID, self::NAVIGATION_ID);

        $return_bytes = return_bytes(ini_get('memory_limit'));
        if ($_SWIFT->Staff->GetPermission('admin_canupdatesettings') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } elseif (isset($_POST['pr_sizelimit']) && $return_bytes != -1 && $_POST['pr_sizelimit'] > $return_bytes) {
            $this->UserInterface->DisplayError($this->Language->Get('titlephpinierror'), sprintf($this->Language->Get('msgphpinierror'), ini_get('memory_limit')));
        } else {
            $this->UserInterface->Start(get_short_class($this), '/Base/Settings/View/' . $_settingGroupID, SWIFT_UserInterface::MODE_INSERT, false);
            $this->SettingsManager->Render($this->UserInterface, SWIFT_SettingsManager::FILTER_ID, array($_settingGroupID));
            $this->UserInterface->End();
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * The REST API Settings Manager
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RESTAPI()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UserInterface->Header($this->Language->Get('restapi') . ' > ' . $this->Language->Get('settings'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_canupdatesettings') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->UserInterface->Start(get_short_class($this), '/Base/Settings/RESTAPI', SWIFT_UserInterface::MODE_INSERT, false);
            $this->SettingsManager->Render($this->UserInterface, SWIFT_SettingsManager::FILTER_NAME, array('settings_general'), array('g_api'));
            $this->UserInterface->End();
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * The Staff Settings Manager
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Staff()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UserInterface->Header($this->Language->Get('staff') . ' > ' . $this->Language->Get('settings'), 2, 1);

        if ($_SWIFT->Staff->GetPermission('admin_canupdatesettings') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->UserInterface->Start(get_short_class($this), '/Base/Settings/Staff', SWIFT_UserInterface::MODE_INSERT, false);
            $this->SettingsManager->Render($this->UserInterface, SWIFT_SettingsManager::FILTER_NAME, array('settings_security'), array('security_staffpasswordpolicy'));
            $this->UserInterface->End();
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * The User Settings Manager
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

        $this->UserInterface->Header($this->Language->Get('users') . ' > ' . $this->Language->Get('settings'), 4, 1);

        if ($_SWIFT->Staff->GetPermission('admin_canupdatesettings') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->UserInterface->Start(get_short_class($this), '/Base/Settings/User', SWIFT_UserInterface::MODE_INSERT, false);
            $this->SettingsManager->Render($this->UserInterface, SWIFT_SettingsManager::FILTER_NAME, array('settings_users'));
            $this->UserInterface->End();
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * The Staff LoginShare Settings Manager
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function StaffLoginShare()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UserInterface->Header($this->Language->Get('staff') . ' > ' . $this->Language->Get('loginsharesettings'), 2, 1);

        if ($_SWIFT->Staff->GetPermission('admin_canupdatesettings') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->UserInterface->Start(get_short_class($this), '/Base/Settings/StaffLoginShare', SWIFT_UserInterface::MODE_INSERT, false);
            $this->SettingsManager->Render($this->UserInterface, SWIFT_SettingsManager::FILTER_NAME, array('settings_loginshare'), array('loginshare_staff'));
            $this->UserInterface->End();
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * The User LoginShare Settings Manager
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function UserLoginShare()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UserInterface->Header($this->Language->Get('users') . ' > ' . $this->Language->Get('loginsharesettings'), 4, 1);

        if ($_SWIFT->Staff->GetPermission('admin_canupdatesettings') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->UserInterface->Start(get_short_class($this), '/Base/Settings/UserLoginShare', SWIFT_UserInterface::MODE_INSERT, false);
            $this->SettingsManager->Render($this->UserInterface, SWIFT_SettingsManager::FILTER_NAME, array('settings_loginshare'), array('loginshare_user'));
            $this->UserInterface->End();
        }

        $this->UserInterface->Footer();

        return true;
    }
}

?>
