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
use SWIFT_App;
use SWIFT_AppManager;
use SWIFT_Exception;

/**
 * The App Management Controller
 *
 * @property \Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel $UserInterface
 * @property SWIFT_AppManager $AppManager
 * @property View_App $View
 * @author Varun Shoor
 */
class Controller_App extends Controller_admin
{
    // Core Constants
    const MENU_ID = 1;
    const NAVIGATION_ID = 32;

    /**
     * Constructor
     *
     * @author Varun Shoor
     */
    public function __construct()
    {
        parent::__construct();

        $this->Load->Library('App:AppManager');
        $this->Load->Library('Setup:Setup');

        $this->Language->Load('admin_apps');
        $this->Language->Load('setup');
    }

    /**
     * Displays the App Grid
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Manage()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UserInterface->Header($this->Language->Get('apps') . ' > ' . $this->Language->Get('manage'), self::MENU_ID,
            self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_canmanageapps') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $_availableAppContainer = $this->AppManager->RetrieveAvailableApps();
            $this->View->RenderAppList($_availableAppContainer);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Uninstall an App
     *
     * @author Varun Shoor
     * @param string $_appName The App Name
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Uninstall($_appName)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($_SWIFT->Staff->GetPermission('admin_canmanageapps') == '0') {
            return false;
        }

        SWIFT_AppManager::Uninstall($_appName);

        $this->Load->Method('View', $_appName);

        return true;
    }

    /**
     * Install a app
     *
     * @author Varun Shoor
     * @param string $_appName The App Name
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Install($_appName)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($_SWIFT->Staff->GetPermission('admin_canmanageapps') == '0') {
            return false;
        }

        SWIFT_AppManager::Install($_appName);

        $this->Load->Method('View', $_appName);

        return true;
    }

    /**
     * Upgrade an App
     *
     * @author Varun Shoor
     * @param string $_appName The App Name
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Upgrade($_appName)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($_SWIFT->Staff->GetPermission('admin_canmanageapps') == '0') {
            return false;
        }

        SWIFT_AppManager::Upgrade($_appName);

        $this->Load->Method('View', $_appName);

        return true;
    }

    /**
     * Upgrade All Apps
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function UpgradeAll()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($_SWIFT->Staff->GetPermission('admin_canmanageapps') == '0') {
            return false;
        }

        SWIFT_AppManager::UpgradeAll();

        $this->Load->Manage();

        return true;
    }

    /**
     * View an App
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or if Invalid Data is Provided
     */
    public function View($_appName)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($_SWIFT->Staff->GetPermission('admin_canmanageapps') == '0') {
            return false;
        }

        $_appDirectory = SWIFT_App::GetAppDirectory($_appName);
        if (empty($_appDirectory)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->UserInterface->Header($this->Language->Get('apps') . ' > ' . $this->Language->Get('viewapp'), self::MENU_ID, self::NAVIGATION_ID);
        $this->View->Render($_appName);
        $this->UserInterface->Footer();

        return true;
    }
}
