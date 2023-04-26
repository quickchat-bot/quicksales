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

namespace Base\Admin;

use Controller_admin;
use Dwoo\Exception;
use SWIFT;
use SWIFT_Exception;
use SWIFT_Session;

/**
 * The Template Diagnostics Controller
 *
 * @property \Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel $UserInterface
 * @property View_TemplateDiagnostics $View
 * @author Varun Shoor
 */
class Controller_TemplateDiagnostics extends Controller_admin
{
    // Core Constants
    const MENU_ID = 1;
    const NAVIGATION_ID = 0;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct()
    {
        parent::__construct();

        $this->Language->Load('templates');
    }

    /**
     * Displays the Diagnostics Selection Form
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

        $this->UserInterface->Header($this->Language->Get('templates') . ' > ' . $this->Language->Get('diagnostics'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_tmpcanrundiagnostics') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render();
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Displays the Diagnostics Results
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RunDiagnostics()
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

        $this->UserInterface->Header($this->Language->Get('templates') . ' > ' . $this->Language->Get('diagnostics'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_tmpcanrundiagnostics') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {

            $_templateContainer = array();
            $_modifyList = array();
            if (isset($_POST['modified']) && _is_array($_POST['modified'])) {
                foreach ($_POST['modified'] as $_key => $_val) {
                    if ($_val == 1) {
                        $_modifyList[] = $_key;
                    }
                }
            }

            // Diagnose smarty engine errors
            $this->Template->SetTemplateGroupID($_POST['tgroupid']);

            $this->Database->Query("SELECT templateid, name, modified FROM " . TABLE_PREFIX . "templates WHERE tgroupid = '" . (int)($_POST['tgroupid']) . "' AND modified IN (" . BuildIN($_modifyList) . ") ORDER BY templateid ASC");
            while ($this->Database->NextRecord()) {
                $_templateContainer[$this->Database->Record['templateid']] = $this->Database->Record;

                $_templateContainer[$this->Database->Record['templateid']]['_compileResult'] = true;

                $_startTime = GetMicroTime();
                try {
                    $this->Template->CompileCheck($this->Database->Record['name']);
                } catch (Exception $Dwoo_ExceptionObject) {
                    $_templateContainer[$this->Database->Record['templateid']]['_compileResult'] = false;
                }

                $_endTime = GetMicroTime();
                $_templateContainer[$this->Database->Record['templateid']]['_compileTime'] = ($_endTime - $_startTime);
            }

            $this->View->RenderResult($_templateContainer);
        }

        $this->UserInterface->Footer();

        return true;
    }
}

?>
