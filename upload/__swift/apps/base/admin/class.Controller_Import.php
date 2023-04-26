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
use Base\Library\Import\SWIFT_ImportManager;
use Base\Models\Staff\SWIFT_StaffActivityLog;

/**
 * The Import Controller
 *
 * @property \Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel $UserInterface
 * @property \SWIFT_Loader $Load
 * @property View_Import $View
 * @author Varun Shoor
 */
class Controller_Import extends Controller_admin
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

        $this->Language->Load('admin_import');
    }

    /**
     * Show the Import options
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Manage()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UserInterface->Header($this->Language->Get('import') . " > " . $this->Language->Get('manage'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_canrunimport') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render();
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Show the Import options for a Product
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RenderForm()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_productName = $_POST['productname'];
        if (!SWIFT_ImportManager::IsValidProduct($_productName)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_ImportManagerObject = SWIFT_ImportManager::GetImportManagerObject($_productName);

        $this->UserInterface->Header($this->Language->Get('import') . " > " . $_SWIFT_ImportManagerObject->GetProductTitle(), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_canrunimport') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->RenderForm($_SWIFT_ImportManagerObject);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Process the Form Data and start the Import Process
     *
     * @author Varun Shoor
     * @param string $_productName The Product Name
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ProcessForm($_productName)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!SWIFT_ImportManager::IsValidProduct($_productName)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_ImportManagerObject = SWIFT_ImportManager::GetImportManagerObject($_productName);

        if ($_SWIFT->Staff->GetPermission('admin_canrunimport') == '0') {
            return false;
        } else {
//            $_SWIFT_ImportManagerObject->GetImportRegistry()->DeleteAll();

            if (!$_SWIFT_ImportManagerObject->ProcessForm()) {
                $_POST['productname'] = $_productName;
                $this->Load->RenderForm();

                return false;
            }

            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityimportexec'), htmlspecialchars($_SWIFT_ImportManagerObject->GetProductTitle())), SWIFT_StaffActivityLog::ACTION_UPDATE, SWIFT_StaffActivityLog::SECTION_GENERAL, SWIFT_StaffActivityLog::INTERFACE_ADMIN);

            $_importResult = $_SWIFT_ImportManagerObject->ImportPre();

            $this->UserInterface->Header($this->Language->Get('import') . " > " . $_SWIFT_ImportManagerObject->GetProductTitle(), self::MENU_ID, self::NAVIGATION_ID);
            if ($_importResult) {
                $_SWIFT_ImportManagerObject->StartImport();
                $_SWIFT_ImportTableObject = $_SWIFT_ImportManagerObject->Import();
                $this->View->RenderProgress($_SWIFT_ImportManagerObject, $_SWIFT_ImportTableObject);
            }

            $this->UserInterface->Footer();
        }

        return true;
    }

    /**
     * The Import Process
     *
     * @author Varun Shoor
     * @param string $_productName The Product Name
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ImportProcess($_productName)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!SWIFT_ImportManager::IsValidProduct($_productName)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_ImportManagerObject = SWIFT_ImportManager::GetImportManagerObject($_productName);

        if ($_SWIFT->Staff->GetPermission('admin_canrunimport') == '0') {
            return false;
        } else {

            $_importResult = $_SWIFT_ImportManagerObject->ImportPre();


            $this->UserInterface->Header($this->Language->Get('import') . " > " . $_SWIFT_ImportManagerObject->GetProductTitle(), self::MENU_ID, self::NAVIGATION_ID);
            if ($_importResult) {
                $_SWIFT_ImportTableObject = $_SWIFT_ImportManagerObject->Import();
                $this->View->RenderProgress($_SWIFT_ImportManagerObject, $_SWIFT_ImportTableObject);
            }

            $this->UserInterface->Footer();
        }

        return true;
    }


}

?>
