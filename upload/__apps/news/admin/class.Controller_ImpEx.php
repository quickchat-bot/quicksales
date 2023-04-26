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

namespace News\Admin;

use Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel;
use Controller_admin;
use News\Models\Subscriber\SWIFT_NewsSubscriber;
use SWIFT;
use SWIFT_Session;
use Base\Models\Staff\SWIFT_StaffActivityLog;

/**
 * The News ImpEx Controller
 *
 * @author Varun Shoor
 *
 * @property Controller_ImpEx $Load
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @property View_ImpEx $View
 */
class Controller_ImpEx extends Controller_admin
{
    // Core Constants
    const MENU_ID = 1;
    const NAVIGATION_ID = 1;

    /**
     * Constructor
     *
     * @author Varun Shoor
     */
    public function __construct()
    {
        parent::__construct();

        $this->Language->Load('admin_news');
    }

    /**
     * News ImpEx Manager
     *
     * @author Varun Shoor
     * @param bool $_isImportTabSelected (OPTIONAL)
     * @return bool "true" on Success, "false" otherwise
     * @throws \SWIFT_Exception
     */
    public function Manage($_isImportTabSelected = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            return false;
        }

        $this->UserInterface->Header($this->Language->Get('news') . ' > ' . $this->Language->Get('importexport'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_nwcanupdatesubscriber') == '0')
        {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->RenderImpEx($_isImportTabSelected);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Import the Subscribers
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws \Base\Library\Staff\SWIFT_Staff_Exception
     * @throws \SWIFT_Exception
     */
    public function Import()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            return false;
        }

        // BEGIN CSRF HASH CHECK

        if (!SWIFT_Session::CheckCSRFHash($_POST['csrfhash']))
        {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            $this->Load->Manage();

            return false;
        }

        // END CSRF HASH CHECK

        if (SWIFT::Get('isdemo') == true) {
            $this->UserInterface->Error($this->Language->Get('titledemomode'), $this->Language->Get('msgdemomode'));

            $this->Load->Manage();

            return false;
        }

        if ($_SWIFT->Staff->GetPermission('admin_nwcanupdatesubscriber') == '0') {
            $this->UserInterface->Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));

            $this->Load->Manage();

            return false;
        }

        $_importCount = SWIFT_NewsSubscriber::Import($_POST['emails']);

        if ($_importCount)
        {
            SWIFT::Info(sprintf($this->Language->Get('titlesubscriberimport'), $_importCount), sprintf($this->Language->Get('msgsubscriberimport'), $_importCount));
        }

        SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityimportedsubscribers'), $_importCount),
                SWIFT_StaffActivityLog::ACTION_INSERT, SWIFT_StaffActivityLog::SECTION_NEWS, SWIFT_StaffActivityLog::INTERFACE_ADMIN);

        $this->Load->Manage(true);

        return true;
    }

    /**
     * Export the Subscribers
     *
     * @author Varun Shoor
     * @param int $_templateGroupID The Template Group ID
     * @return bool "true" on Success, "false" otherwise
     * @throws \Base\Library\Staff\SWIFT_Staff_Exception
     * @throws \SWIFT_Exception
     */
    public function Export($_templateGroupID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            return false;
        }

        if ($_SWIFT->Staff->GetPermission('admin_nwcanupdatesubscriber') == '0') {
            $this->UserInterface->Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));

            return false;
        }

        SWIFT_NewsSubscriber::Export($_templateGroupID);

        SWIFT_StaffActivityLog::AddToLog($this->Language->Get('activityexportedsubscribers'),
                SWIFT_StaffActivityLog::ACTION_UPDATE, SWIFT_StaffActivityLog::SECTION_NEWS, SWIFT_StaffActivityLog::INTERFACE_ADMIN);

        return true;
    }
}
