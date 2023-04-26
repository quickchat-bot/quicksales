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

namespace Troubleshooter\Admin;

use Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel;
use SWIFT;
use SWIFT_Exception;
use SWIFT_SettingsManager;
use Base\Library\UserInterface\SWIFT_UserInterface;

/**
 * The Troubleshooter Settings Manager Class
 *
 * @property SWIFT_SettingsManager $SettingsManager
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @author Varun Shoor
 */
class Controller_SettingsManager extends \Controller_admin
{
    // Core Constants
    const MENU_ID = 1;
    const NAVIGATION_ID = 5;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @throws \SWIFT_Exception
     */
    public function __construct()
    {
        parent::__construct();

        $this->Load->Library('Settings:SettingsManager');

        $this->Language->Load('admintroubleshooter');
        $this->Language->Load('settings');
    }

    /**
     * Render the Troubleshooter Settings
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     * @throws \SWIFT_Exception
     */
    public function Index()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UserInterface->Header($this->Language->Get('troubleshooter') . ' > ' . $this->Language->Get('trsettings'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_canupdatesettings') == '0')
        {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->UserInterface->Start(get_short_class($this),'/Troubleshooter/SettingsManager/Index', SWIFT_UserInterface::MODE_INSERT, false);
            $this->SettingsManager->Render($this->UserInterface, SWIFT_SettingsManager::FILTER_NAME, array('settings_tr'));
            $this->UserInterface->End();
        }

        $this->UserInterface->Footer();

        return true;
    }
}
?>
