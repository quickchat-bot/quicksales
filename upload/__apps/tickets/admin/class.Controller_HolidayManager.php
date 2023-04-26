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

namespace Tickets\Admin;

use Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel;
use Controller_admin;
use SWIFT;
use SWIFT_Exception;
use SWIFT_Session;
use Base\Models\Staff\SWIFT_StaffActivityLog;
use Tickets\Library\SLA\SWIFT_SLAHolidayManager;

/**
 * The Holiday Manager Import/Export Controller
 *
 * @method Library($_libraryName, $_arguments, $_initiateInstance, $_customAppName, $_appName)
 * @property Controller_HolidayManager $Load
 * @property SWIFT_SLAHolidayManager $SLAHolidayManager
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @property View_HolidayManager $View
 * @author Varun Shoor
 */
class Controller_HolidayManager extends Controller_admin
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

        $this->Load->Library('SLA:SLAHolidayManager', [], true, false, 'tickets');

        $this->Language->Load('tickets');
        $this->Language->Load('adminsla');
    }

    /**
     * The ImpEx Form Renderer
     *
     * @author Varun Shoor
     * @param bool $_isImportTabActivated Whether the Import Tab is activated by default
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Index($_isImportTabActivated = false)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT = SWIFT::GetInstance();

        $this->UserInterface->Header($this->Language->Get('sla') . ' > ' . $this->Language->Get('impex'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_tcanimpexslaholidays') == '0')
        {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render($_isImportTabActivated);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Export the Holiday Pack File
     *
     * @author Varun Shoor
     * @param array $_argumentContainer The Argument Container
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Export($_argumentContainer)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT = SWIFT::GetInstance();

        if (SWIFT::Get('isdemo') == true) {
            $this->UserInterface->Error($this->Language->Get('titledemomode'), $this->Language->Get('msgdemomode'));

            $this->Load->Index();

            return false;
        }

        if ($_SWIFT->Staff->GetPermission('admin_tcanimpexslaholidays') == '0') {
            $this->UserInterface->Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));

            $this->Load->Index();

            return false;
        }

        $_holidayPackAuthor = $this->Settings->Get('general_companyname');

        if (!isset($_argumentContainer['_exportFileName']) || empty($_argumentContainer['_exportFileName']))
        {
            $_holidayPackFileName = SWIFT_SLAHolidayManager::GenerateFileName('sla');
        } else {
            $_holidayPackFileName = $_argumentContainer['_exportFileName'];
        }

        $_holidayPackTitle = $this->Language->Get('slaholidaypack');

        $this->SLAHolidayManager->Export($_holidayPackTitle, $_holidayPackAuthor, $_holidayPackFileName);

        return true;
    }

    /**
     * Import the Holiday Pack File
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Import()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!SWIFT_Session::CheckCSRFHash($_POST['csrfhash']))
        {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            $this->Load->Index(true);

            return false;
        }

        // END CSRF HASH CHECK

        if (SWIFT::Get('isdemo') == true) {
            $this->UserInterface->Error($this->Language->Get('titledemomode'), $this->Language->Get('msgdemomode'));

            $this->Load->Index(true);

            return false;
        }

        if ($_SWIFT->Staff->GetPermission('admin_tcanimpexslaholidays') == '0') {
            $this->UserInterface->Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));

            $this->Load->Index(true);

            return false;
        }

        if (isset($_FILES['slaholidayfile']) && file_exists($_FILES['slaholidayfile']['tmp_name']))
        {
            $_result = $this->SLAHolidayManager->Import($_FILES['slaholidayfile']['tmp_name']);
            SWIFT_StaffActivityLog::AddToLog($this->Language->Get('activityimportslaholiday'), SWIFT_StaffActivityLog::ACTION_INSERT, SWIFT_StaffActivityLog::SECTION_TICKETS, SWIFT_StaffActivityLog::INTERFACE_ADMIN);

            if (!$_result)
            {
                $_importResult = '-1';
            } else {
                $_importResult = count($_result);
            }

            if (!empty($_importResult)) {
                if ($_importResult == '-1')
                {
                    SWIFT::Error($this->Language->Get('titleslaholidayimpexfailed'), $this->Language->Get('msgslaholidayimpexfailed'));
                } else if ((int) ($_importResult) > '0') {
                    SWIFT::Info($this->Language->Get('titleslaholidayimpex'), sprintf($this->Language->Get('msgslaholidayimpex'), (int) ($_importResult)));
                }
            }
        } else {
            SWIFT::Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));
        }

        $this->Load->Index(true);

        return true;
    }
}
