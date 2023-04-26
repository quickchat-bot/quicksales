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

namespace LiveChat\Admin;

use Base\Library\UserInterface\SWIFT_UserInterface;
use Controller_admin;
use SWIFT;
use SWIFT_SettingsManager;

/**
 * The Live Chat Settings Manager Class
 *
 * @author Varun Shoor
 */
class Controller_SettingsManager extends Controller_admin
{
    // Core Constants
    const MENU_ID = 1;
    const NAVIGATION_ID = 13;

    public $SettingsManager;

    /**
     * Constructor
     *
     * @author Varun Shoor
     */
    public function __construct()
    {
        parent::__construct();

        $this->Load->Library('Settings:SettingsManager');

        $this->Language->Load('admin_livesupport');
        $this->Language->Load('settings');
    }

    /**
     * Render the Live Chat Settings
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

        $this->UserInterface->Header($this->Language->Get('livechat') . ' > ' . $this->Language->Get('settings'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_canupdatesettings') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->UserInterface->Start(get_short_class($this), '/LiveChat/SettingsManager/Index', SWIFT_UserInterface::MODE_INSERT, false);
            $this->SettingsManager->Render($this->UserInterface, SWIFT_SettingsManager::FILTER_NAME, array('settings_livechat'));
            $this->UserInterface->End();
        }

        $this->UserInterface->Footer();

        return true;
    }
}
