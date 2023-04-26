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

use Base\Library\Diagnostics\SWIFT_DiagnosticsPHPInfo;
use Controller_admin;
use SWIFT;
use SWIFT_CacheManager;
use SWIFT_Exception;
use SWIFT_Loader;
use SWIFT_Mail;
use SWIFT_Session;
use Base\Models\Staff\SWIFT_StaffActivityLog;

/**
 * The Diagnostics Controller
 *
 * @property \Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel $UserInterface
 * @property SWIFT_Loader $Load
 * @property SWIFT_Mail $Mail
 * @property View_Diagnostics $View
 * @property SWIFT_DiagnosticsPHPInfo $DiagnosticsPHPInfo
 * @author Varun Shoor
 */
class Controller_Diagnostics extends Controller_admin
{
    // Core Constants
    const MENU_ID = 1;
    const NAVIGATION_ID = 12;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct()
    {
        parent::__construct();

        $this->Load->Library('Diagnostics:DiagnosticsPHPInfo', [], true, false, 'base');

        $this->Language->Load('diagnostics');
    }

    /**
     * The PHP Info Handler
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function PHPInfo()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UserInterface->Header($this->Language->Get('diagnostics') . ' > ' . $this->Language->Get('phpinfo'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_canrundiagnostics') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->RenderPHPInfo();
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Kill the Sessions's from Mass Action
     *
     * @author Varun Shoor
     * @param mixed $_sessionIDList The Session ID List Container Array
     * @return bool "true" on Success, "false" otherwise
     */
    public static function KillSessions($_sessionIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('admin_canrundiagnostics') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        $_finalSessionIDList = array();
        $_index = 1;
        $_finalText = '';

        if (_is_array($_sessionIDList)) {
            $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "sessions WHERE sessionid IN (" . BuildIN($_sessionIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                $_sessionKey = 'sess' . $_SWIFT->Database->Record['sessiontype'];

                $_finalSessionIDList[] = $_SWIFT->Database->Record['sessionid'];

                $_finalText .= $_index . '. ' . htmlspecialchars($_SWIFT->Database->Record['ipaddress']) . ' (' . htmlspecialchars($_SWIFT->Language->Get($_sessionKey)) . ')<BR />';

                SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activitykillsession'), htmlspecialchars($_SWIFT->Database->Record['ipaddress']), htmlspecialchars($_SWIFT->Language->Get($_sessionKey))), SWIFT_StaffActivityLog::ACTION_DELETE, SWIFT_StaffActivityLog::SECTION_DIAGNOSTICS, SWIFT_StaffActivityLog::INTERFACE_ADMIN);

                $_index++;
            }

            if (!count($_finalSessionIDList)) {
                return false;
            }

            SWIFT_Session::KillSessionList($_finalSessionIDList);

            SWIFT::Info(sprintf($_SWIFT->Language->Get('titlekillsession'), count($_finalSessionIDList)), $_SWIFT->Language->Get('msgkillsession') . '<BR />' . $_finalText);
        }

        return true;
    }

    /**
     * Displays the Active Sessions Grid
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ActiveSessions()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UserInterface->Header($this->Language->Get('diagnostics') . ' > ' . $this->Language->Get('activesessions'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_canrundiagnostics') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->RenderActiveSessionsGrid();
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Displays the Cache Grid
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function CacheInformation()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UserInterface->Header($this->Language->Get('diagnostics') . ' > ' . $this->Language->Get('cacheinfo'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_canrundiagnostics') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->RenderCacheGrid();
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Rebuild all the core caches
     *
     * @author Varun Shoor
     *
     * @return bool
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RebuildCache()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UserInterface->Header($this->Language->Get('diagnostics') . ' > ' . $this->Language->Get('rebuildcache'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_canrundiagnostics') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        }

        /**
         * BUG FIX: Mansi Wason
         *
         * SWIFT-3461 "Rebuild Cache option under Admin CP should redirect to direct URL used for rebuilding cache"
         *
         * Comments: None
         **/
        SWIFT_CacheManager::EmptyCacheDirectory();
        $_cacheList = SWIFT_CacheManager::RetrieveCacheList();

        $_cacheContainer = [];
        foreach ($_cacheList as $_section => $_classContainer) {
            foreach ($_classContainer as $_classList) {
                list($_classLoadName, $_className, $_classFilePath, $_appName) = $_classList;
                $_dir = '';
                if ($_section == 'model') {
                    $_dir = 'Models';
                    SWIFT_Loader::LoadModel($_classLoadName, $_appName);
                } else if ($_section == 'lib') {
                    $_dir = 'Library';
                    SWIFT_Loader::LoadLibrary($_classLoadName, $_appName);
                }

                $_rebuiltCacheList[] = $_className;

                $__className = prepend_library_namespace(explode(':', $_classLoadName),$_className,'SWIFT_' . $_className, $_dir, $_appName);

                call_user_func_array(array($__className, 'RebuildCache'), array());
                $_cacheContainer[] = array(strtolower(($_className)), true);
            }
        }

        $this->View->RenderRebuildCache($_cacheContainer);

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * View the Cache
     *
     * @author Varun Shoor
     * @param string $_cacheKey The Cache Key
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ViewCache($_cacheKey)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_cache = $this->Cache->Get($_cacheKey);

        $_cacheData = var_export($_cache, true);

        $this->UserInterface->Header($this->Language->Get('diagnostics') . ' > ' . $this->Language->Get('cacheinfo'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_canrundiagnostics') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->RenderCacheDialog($_cacheKey, $_cacheData);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Report a Bug to QuickSupport
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ReportBug()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UserInterface->Header($this->Language->Get('diagnostics') . ' > ' . $this->Language->Get('reportbug'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_canrundiagnostics') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->RenderBugDialog();
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Dispatch the Bug report to QuickSupport
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function SendBug()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        // BEGIN CSRF HASH CHECK

        if (!SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            $this->Load->ReportBug();

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('admin_canrundiagnostics') == '0') {
            $this->UserInterface->Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));

            $this->Load->ReportBug();

            return false;
        } elseif (trim($_POST['subject']) == '' || trim($_POST['fromname']) == '' || trim($_POST['fromemail']) == '' || trim($_POST['contents']) == '') {
            $this->UserInterface->CheckFields('subject', 'fromname', 'fromemail', 'contents');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            $this->Load->ReportBug();

            return false;
        } elseif (!IsEmailValid($_POST['fromemail'])) {
            SWIFT::ErrorField('fromemail');

            $this->UserInterface->Error($this->Language->Get('titlespecifyvalidemail'), $this->Language->Get('msgspecifyvalidemail'));

            $this->Load->ReportBug();

            return false;
        } elseif (SWIFT::Get('isdemo') == true) {
            $this->UserInterface->Error($this->Language->Get('titledemomode'), $this->Language->Get('msgdemomode'));

            $this->Load->ReportBug();

            return false;
        }

        $this->Load->Library('Mail:Mail');

        $this->Mail->SetFromField($_POST['fromemail'], $_POST['fromname']);
        $this->Mail->SetToField($_POST['fromemail']);
        $this->Mail->SetSubjectField($_POST['subject']);

        $this->Mail->SetDataText($_POST['contents']);

        $this->Mail->SendMail();

        SWIFT::Info($this->Language->Get('titlebugreportdispatched'), $this->Language->Get('msgbugreportdispatched'));

        $this->Load->ReportBug();

        return true;
    }

    /**
     * Display the License Information
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function LicenseInformation()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UserInterface->Header($this->Language->Get('diagnostics') . ' > ' . $this->Language->Get('licenseinfo'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_canrundiagnostics') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->RenderLicenseDialog();
        }

        $this->UserInterface->Footer();

        return true;
    }
}

?>
