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
 * @copyright    Copyright (c) 2001-2012, Kayako
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
use SWIFT_TemplateEngine;
use Base\Models\Template\SWIFT_TemplateGroup;

/**
 * The Template Manager (ImpEx/Diagnostics/Restore) Controller
 *
 * @property \Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel $UserInterface
 * @property \Base\Library\Template\SWIFT_TemplateManager $TemplateManager
 * @property View_TemplateManager $View
 * @author Varun Shoor
 */
class Controller_TemplateManager extends Controller_admin
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
        $this->Load->Library('Template:TemplateManager', [], true, false, 'base');

        $this->Language->Load('templates');
    }

    /**
     * Displays the Import/Export Form
     *
     * @author Varun Shoor
     * @param mixed $_isImpexTabSelected
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ImpEx($_isImpexTabSelected = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!is_numeric($_isImpexTabSelected) && !is_bool($_isImpexTabSelected)) {
            $_isImpexTabSelected = false;
        }

        $this->UserInterface->Header($this->Language->Get('templates') . ' > ' . $this->Language->Get('importexport'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_tmpcanrunimportexport') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->RenderImpEx($_isImpexTabSelected);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Export a given template group
     *
     * @author Varun Shoor
     * @param int $_templateGroupID The Template Group ID
     * @param mixed $_exportOptions The Export Options
     * @param bool $_exportHistory Whether to Export History
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Export($_templateGroupID, $_exportOptions, $_exportHistory)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_templateGroupCache = $this->Cache->Get('templategroupcache');
        if (!isset($_templateGroupCache[$_templateGroupID])) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        } elseif ($_SWIFT->Staff->GetPermission('admin_tmpcanrunimportexport') == '0') {
            $this->Load->ImpEx();

            return false;
        }

        $this->TemplateManager->Export($_templateGroupID, (int)($_exportOptions), '', $_exportHistory);

        return true;
    }

    /**
     * Import a Template XML File
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Import()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        // BEGIN CSRF HASH CHECK
        if (!isset($_POST['csrfhash'])) {
            $this->Load->ImpEx(true);

            return false;
        }

        if (!SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
            SWIFT::Error($this->Language->Get('titlecsrfhash'), $this->Language->Get('msgcsrfhash'));

            $this->Load->ImpEx(true);

            return false;
        }

        // END CSRF HASH CHECK

        if (!isset($_FILES['templatefile']) || !file_exists($_FILES['templatefile']['tmp_name']) || !is_readable($_FILES['templatefile']['tmp_name'])) {
            SWIFT::Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            SWIFT::ErrorField('templatefile');

            $this->Load->ImpEx(true);

            return false;
        }

        if (SWIFT::Get('isdemo') == true) {
            $this->UserInterface->Error($this->Language->Get('titledemomode'), $this->Language->Get('msgdemomode'));

            $this->Load->ImpEx(true);

            return false;
        }

        $_templateGroupCache = $this->Cache->Get('templategroupcache');

        // Success.. are we supposed to insert a new template group or merge changes with existing one..?
        if (!empty($_POST['tgroupidimport']) && isset($_templateGroupCache[$_POST['tgroupidimport']])) {
            // Merge
            $_result = $this->TemplateManager->Merge($_FILES['templatefile']['tmp_name'], IIF($_POST['ignoreversion'] == '1', false, true), $_POST['tgroupidimport'], $_POST['addtohistory']);
            if ($_result == -1) {
                SWIFT::Error($this->Language->Get('titleversioncheckfail'), $this->Language->Get('msgversioncheckfail'));
            } elseif ($_result == -2 || !$_result) {
                SWIFT::Error($this->Language->Get('titletemplateimportfailed'), sprintf($this->Language->Get('msgtemplateimportfailed'), htmlspecialchars($_FILES['templatefile']['name'])));
            } else {
                SWIFT::Info(sprintf($this->Language->Get('titletgroupmerge'), htmlspecialchars($_templateGroupCache[$_POST['tgroupidimport']]['title'])), sprintf($this->Language->Get('msgtgroupmerge'), htmlspecialchars($_FILES['templatefile']['name']), htmlspecialchars($_templateGroupCache[$_POST['tgroupidimport']]['title'])));
            }
        } else {
            // Create New
            $_result = $this->TemplateManager->ImportCreateGroup($_FILES['templatefile']['tmp_name'], IIF($_POST['ignoreversion'] == '1', false, true));

            if ($_result instanceof SWIFT_TemplateGroup && $_result->GetIsClassLoaded()) {
                SWIFT::Info(sprintf($this->Language->Get('titletgroupimport'), htmlspecialchars($_result->GetProperty('title'))), sprintf($this->Language->Get('msgtgroupimport'), htmlspecialchars($_FILES['templatefile']['name']), htmlspecialchars($_result->GetProperty('title'))));
            } elseif ($_result == -1) {
                SWIFT::Error($this->Language->Get('titleversioncheckfail'), $this->Language->Get('msgversioncheckfail'));
            } elseif ($_result == -2 || !$_result) {
                SWIFT::Error($this->Language->Get('titletemplateimportfailed'), sprintf($this->Language->Get('msgtemplateimportfailed'), htmlspecialchars($_FILES['templatefile']['name'])));
            }
        }

        SWIFT_CacheManager::EmptyCacheDirectory();

        $this->Load->ImpEx(true);

        return true;
    }

    /**
     * Render the Personlization Form
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Personalize()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UserInterface->Header($this->Language->Get('templates') . ' > ' . $this->Language->Get('personalize'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_tmpcanpersonalize') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->RenderPersonalize();
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * The Personalize Submission Processor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function PersonalizeSubmit()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        // BEGIN CSRF HASH CHECK

        if (!SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            $this->Load->Personalize();

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('admin_tmpcanpersonalize') == '0') {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        // Check for Invalid Image Extensions
        $_allowedImageExtensions = array('gif', 'png', 'jpg', 'jpeg');
        $_fileFieldList = array('supportcenterlogo', 'stafflogo');

        if (!isset($_FILES['supportcenterlogo']['name']) && !isset($_FILES['stafflogo']['name'])) {
            $this->UserInterface->CheckFields('supportcenterlogo', 'stafflogo');
            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('personalizationerrmsg'));
            $this->Load->Personalize();

            return false;
        }

        foreach ($_fileFieldList as $_key => $_val) {
            if (isset($_FILES[$_val]['name']) && isset($_FILES[$_val]['tmp_name'])) {
                $_pathContainer = pathinfo($_FILES[$_val]['name']);

                if (!isset($_pathContainer['extension'])) {
                    unset($_FILES[$_val]);
                } elseif (!in_array(mb_strtolower($_pathContainer['extension']), $_allowedImageExtensions)) {
                    SWIFT::ErrorField($_val);

                    SWIFT::Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

                    $this->Load->Personalize();

                    return false;
                }
            }
        }

        $_informationUpdated = false;
        // Now update the header images if needed
        if (isset($_FILES['supportcenterlogo']) && file_exists($_FILES['supportcenterlogo']['tmp_name'])) {
            $this->TemplateManager->UpdateHeaderImage(SWIFT_TemplateEngine::HEADERIMAGE_SUPPORTCENTER, $_FILES['supportcenterlogo']['name'], $_FILES['supportcenterlogo']['tmp_name']);
            $_informationUpdated = true;
        }

        if (isset($_FILES['stafflogo']) && file_exists($_FILES['stafflogo']['tmp_name'])) {
            $this->TemplateManager->UpdateHeaderImage(SWIFT_TemplateEngine::HEADERIMAGE_CONTROLPANEL, $_FILES['stafflogo']['name'], $_FILES['stafflogo']['tmp_name']);
            $_informationUpdated = true;
        }

        // Validating the information update
        if ($_informationUpdated) {
            SWIFT::Info($this->Language->Get('titlepersonalization'), $this->Language->Get('msgpersonalization'));
            SWIFT_CacheManager::EmptyCacheDirectory();
        }

        return $this->Load->Personalize();
    }
}

?>
