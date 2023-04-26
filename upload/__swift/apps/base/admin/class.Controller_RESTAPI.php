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

namespace Base\Admin;

use Controller_admin;
use SWIFT;
use SWIFT_Exception;
use SWIFT_Session;

/**
 * The REST API Controller
 *
 * @property \Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel $UserInterface
 * @property \SWIFT_RESTManager $RESTManager
 * @property View_RESTAPI $View
 * @author Varun Shoor
 */
class Controller_RESTAPI extends Controller_admin
{
    // Core Constants
    const MENU_ID = 1;
    const NAVIGATION_ID = 26;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct()
    {
        parent::__construct();

        $this->Load->Library('REST:RESTManager');

        $this->Language->Load('admin_restapi');
    }

    /**
     * Render the API Tab
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

        if ($this->Settings->Get('g_enableapiinterface') == '0') {
            SWIFT::Alert($this->Language->Get('titleapidisabled'), $this->Language->Get('msgapidisabled'));
        }

        $this->UserInterface->Header($this->Language->Get('restapi') . " > " . $this->Language->Get('apiinformation'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_canmanagerestapi') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render();
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Regenerate the API Authentication Data
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ReGenerate()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        // BEGIN CSRF HASH CHECK

        if (!SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            $this->Load->Index();

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('admin_canmanagerestapi') == '0') {
            $this->Load->Index();

            return false;
        }

        $this->RESTManager->ReGenerateAuthenticationData();

        SWIFT::Info($this->Language->Get('titleapiregenerate'), $this->Language->Get('msgapiregenerate'));

        $this->Load->Index();
    }
}

?>
