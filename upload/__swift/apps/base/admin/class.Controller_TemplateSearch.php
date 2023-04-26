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
use SWIFT;
use SWIFT_Exception;

/**
 * The Template Search Controller
 *
 * @property \Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel $UserInterface
 * @property View_TemplateSearch $View
 * @property \SWIFT_StringHighlighter $StringHighlighter
 * @author Varun Shoor
 */
class Controller_TemplateSearch extends Controller_admin
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

        $this->Load->Library('String:StringHighlighter');

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

        $this->UserInterface->Header($this->Language->Get('templates') . ' > ' . $this->Language->Get('search'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_tmpcansearchtemplates') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render();
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Run the Search on Template Group
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RunSearch()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_templateSearchCache = $this->Cache->Get('templatesearchcache');

        $_propertiesContainer = array();
        if (isset($_POST['searchquery']) && $_POST['searchquery'] != '' && isset($_POST['tgroupid'])) {
            $_propertiesContainer['searchquery'] = $_POST['searchquery'];
            $_propertiesContainer['tgroupid'] = $_POST['tgroupid'];
            $_propertiesContainer['modified'] = $_POST['modified'];

            $this->Cache->Update('templatesearchcache', $_propertiesContainer);

            // Direct call?
        } elseif (!isset($_POST['searchquery']) && !isset($_POST['tgroupid']) && !isset($_POST['modified'])) {
            $_propertiesContainer = $_templateSearchCache;
        }

        // Check for incoming data
        if (!isset($_propertiesContainer['searchquery']) || trim($_propertiesContainer['searchquery']) == '' || !isset($_propertiesContainer['tgroupid']) || trim($_propertiesContainer['tgroupid']) == '') {
            $this->UserInterface->CheckFields('searchquery', 'tgroupid');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            $this->Load->Index();

            return false;
        }


        $this->UserInterface->Header($this->Language->Get('templates') . ' > ' . $this->Language->Get('search'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_tmpcansearchtemplates') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $_templateContainer = array();
            $_modifyList = array();
            if (isset($_propertiesContainer['modified']) && _is_array($_propertiesContainer['modified'])) {
                foreach ($_propertiesContainer['modified'] as $_key => $_val) {
                    if ($_val == 1) {
                        $_modifyList[] = $_key;
                    }
                }
            }


            $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "templates AS templates
                LEFT JOIN " . TABLE_PREFIX . "templatedata AS templatedata ON (templates.templateid = templatedata.templateid)
                WHERE templates.tgroupid = '" . (int)($_propertiesContainer["tgroupid"]) . "'
                    AND modified IN (" . BuildIN($_modifyList) . ")
                    AND ((" . BuildSQLSearch('templatedata.contents', $_propertiesContainer['searchquery']) . ") OR (" . BuildSQLSearch('templates.name', $_propertiesContainer['searchquery']) . "))
                ORDER BY templates.templateid ASC");
            while ($this->Database->NextRecord()) {
                $_templateContainer[$this->Database->Record['templateid']] = $this->Database->Record;
            }

            $this->View->RenderResult($_templateContainer, $_propertiesContainer);
        }

        $this->UserInterface->Footer();

        return true;
    }
}

?>
