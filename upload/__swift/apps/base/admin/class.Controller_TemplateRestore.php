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
use SWIFT_CacheManager;
use SWIFT_Exception;
use SWIFT_Session;
use Base\Models\Template\SWIFT_Template;

/**
 * The Template Restore Management System
 *
 * @property \Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel $UserInterface
 * @property View_TemplateRestore $View
 * @author Varun Shoor
 */
class Controller_TemplateRestore extends Controller_admin
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

        $this->Load->Library('Cache:CacheManager');

        $this->Language->Load('templates');
    }

    /**
     * Displays the Search Form
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

        $this->UserInterface->Header($this->Language->Get('templates') . ' > ' . $this->Language->Get('restoretemplates'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_tmpcanrestoretemplates') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render();
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * List Templates for Restoration
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ListTemplates()
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

        // Check for incoming data
        if (!isset($_POST['tgroupid']) || trim($_POST['tgroupid']) == '') {
            $this->UserInterface->CheckFields('tgroupid');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            $this->Load->Index();

            return false;
        }

        $this->UserInterface->Header($this->Language->Get('templates') . ' > ' . $this->Language->Get('restoretemplates'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_tmpcanrestoretemplates') == '0') {
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

            $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "templates WHERE tgroupid = '" . (int)($_POST["tgroupid"]) . "' AND modified IN (" . BuildIN($_modifyList) . ") ORDER BY templateid ASC");
            while ($this->Database->NextRecord()) {
                $_templateContainer[$this->Database->Record['templateid']] = $this->Database->Record;
            }

            $this->View->RenderResult($_templateContainer);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Restore a list of Templates
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Restore()
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

        $_templateGroupCache = $this->Cache->Get('templategroupcache');

        if (!isset($_templateGroupCache[$_POST['tgroupid']])) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if ($_SWIFT->Staff->GetPermission('admin_tmpcanrestoretemplates') == '0') {
            SWIFT::Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));

            $this->Load->Index();

            return false;
        } elseif (isset($_POST['itemid']) && _is_array($_POST['itemid'])) {
            $_restoredTemplateContainer = SWIFT_Template::RestoreList($_POST['itemid'], true, $_SWIFT->Staff->GetStaffID());

            $_finalText = sprintf($this->Language->Get('restoretgroup'), htmlspecialchars($_templateGroupCache[$_POST['tgroupid']]['title'])) . '<br />';
            if (_is_array($_restoredTemplateContainer)) {
                foreach ($_restoredTemplateContainer as $_key => $_val) {
                    $_modifiedContainer = SWIFT_Template::GetModifiedHTML($_val['modified']);
                    if (!$_modifiedContainer) {
                        continue;
                    }

                    $_modifiedStatus = $_modifiedContainer[0];
                    $_modifiedText = $_modifiedContainer[1];

                    $_finalText .= '&nbsp;&nbsp;<img src="' . SWIFT::Get('themepath') . 'images/linkdownarrow_blue.gif" align="absmiddle" border="0" /> ' . htmlspecialchars($_val['name']) . ' (' . '<img src="' . SWIFT::Get('themepath') . $_modifiedStatus . '" border="0" align="absmiddle" />&nbsp;' . $_modifiedText . ')<br />';
                }
            }

            SWIFT::Info(sprintf($this->Language->Get('titlerestoretemplates'), count($_restoredTemplateContainer)), $this->Language->Get('msgrestoretemplates') . '<br />' . $_finalText);
        }

        SWIFT_CacheManager::EmptyCacheDirectory();

        $this->Load->Index();

        return true;
    }
}

?>
