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

namespace Tickets\Admin;

use Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel;
use Controller_admin;
use SWIFT;
use SWIFT_Exception;
use SWIFT_SettingsManager;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Tickets\Models\View\SWIFT_TicketView;

/**
 * The Tickets Settings Manager Class
 *
 * @property SWIFT_SettingsManager $SettingsManager
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @author Varun Shoor
 */
class Controller_SettingsManager extends Controller_admin
{
    // Core Constants
    const MENU_ID = 1;
    const NAVIGATION_ID = 5;

    /**
     * Constructor
     *
     * @author Varun Shoor
     */
    public function __construct()
    {
        parent::__construct();

        $this->Load->Library('Settings:SettingsManager');

        $this->Language->Load('tickets');
        $this->Language->Load('settings');
    }

    /**
     * Render the Tickets Settings
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Index()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }
        /**
         * BUG FIX - Ravi Sharma <ravi.sharma@opencart.com.vn>
         *
         * SWIFT-5061 Option to define 'Tickets Per Page' allowed for Ticket Views.
         */
        if (isset($_POST['t_ticketview']) && $_POST['t_ticketview'] >= SWIFT_TicketView::VIEW_MAX_PER_PAGE) {
            $_POST['t_ticketview'] = SWIFT_TicketView::VIEW_MAX_PER_PAGE;
        }

        $this->UserInterface->Header($this->Language->Get('tickets') . ' > ' . $this->Language->Get('settings'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_canupdatesettings') == '0')
        {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->UserInterface->Start(get_short_class($this),'/Tickets/SettingsManager/Index', SWIFT_UserInterface::MODE_INSERT, false);
            $this->SettingsManager->Render($this->UserInterface, SWIFT_SettingsManager::FILTER_NAME, array('settings_tickets'));
            $this->UserInterface->End();
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * The Bayesian Settings Manager
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Bayesian()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UserInterface->Header($this->Language->Get('bayesian') . ' > ' . $this->Language->Get('settings'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_canupdatesettings') == '0')
        {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->UserInterface->Start(get_short_class($this),'/Tickets/SettingsManager/Bayesian', SWIFT_UserInterface::MODE_INSERT, false);
            $this->SettingsManager->Render($this->UserInterface, SWIFT_SettingsManager::FILTER_NAME, array('settings_tickets'), array('tickets_bayesian'));
            $this->UserInterface->End();
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * The SLA Settings Manager
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function SLA()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UserInterface->Header($this->Language->Get('sla') . ' > ' . $this->Language->Get('settings'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_canupdatesettings') == '0')
        {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->UserInterface->Start(get_short_class($this),'/Tickets/SettingsManager/SLA', SWIFT_UserInterface::MODE_INSERT, false);
            $this->SettingsManager->Render($this->UserInterface, SWIFT_SettingsManager::FILTER_NAME, array('settings_tickets'), array('t_sla'));
            $this->UserInterface->End();
        }

        $this->UserInterface->Footer();

        return true;
    }
}
