<?php
/**
 * ###############################################
 *
 * SWIFT Framework
 * _______________________________________________
 *
 * @author         Varun Shoor
 *
 * @package        SWIFT
 * @copyright      Copyright (c) 2001-2012, Kayako
 * @license        http://www.kayako.com/license
 * @link           http://www.kayako.com
 *
 * ###############################################
 */

namespace Parser\Admin;

use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel;
use Controller_admin;
use SWIFT;
use SWIFT_Exception;
use SWIFT_SettingsManager;

/**
 * The Parser Settings Manager Class
 *
 * @property SWIFT_SettingsManager $SettingsManager
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @author Varun Shoor
 */
class Controller_SettingsManager extends Controller_admin
{
    // Core Constants
    const MENU_ID = 1;
    const NAVIGATION_ID = 4;

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

        $this->Language->Load('mailparser_misc');
        $this->Language->Load('settings');
    }

    /**
     * Render the Parser Settings
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Index()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UserInterface->Header($this->Language->Get('mailparser') . ' > ' . $this->Language->Get('mpsettings'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_canupdatesettings') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));

            /**
             * BUG FIX - Ravi Sharma <ravi.sharma@kayako.com>
             *
             * SWIFT-5138 Check and alert if `Email size limit` is more then the PHP memory_limit
             */
        } else {
            $return_bytes = return_bytes(ini_get('memory_limit'));
            if (isset($_POST['pr_sizelimit']) && $return_bytes != -1 && $_POST['pr_sizelimit'] > $return_bytes) {
                $this->UserInterface->DisplayError($this->Language->Get('titlephpinierror'), sprintf($this->Language->Get('msgphpinierror'), ini_get('memory_limit')));
            } else {
                $this->UserInterface->Start(get_short_class($this), '/Parser/SettingsManager/Index', SWIFT_UserInterface::MODE_INSERT, false);
                $this->SettingsManager->Render($this->UserInterface, SWIFT_SettingsManager::FILTER_NAME, array('settings_parser'));
                $this->UserInterface->End();
            }
        }

        $this->UserInterface->Footer();

        return true;
    }
}

?>
