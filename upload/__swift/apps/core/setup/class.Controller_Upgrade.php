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

use Base\Library\Language\SWIFT_LanguageManager;
use Base\Library\Template\SWIFT_TemplateManager;

/**
 * The Upgrade Controller
 *
 * @property SWIFT_Setup $Setup
 * @property SWIFT_TemplateManager $TemplateManager
 * @property SWIFT_SettingsManager $SettingsManager
 * @property SWIFT_LanguageManager $LanguageManager
 * @property View_Upgrade $View
 * @author Varun Shoor
 */
class Controller_Upgrade extends SWIFT_Controller
{
    public $Console;

    /**
     * Constructor
     *
     * @author Varun Shoor
     */
    public function __construct()
    {
        parent::__construct();

        $this->Language->Load('setup');

        $this->Load->Library('Cache:CacheManager');

        $this->Load->Library('Setup:Setup');
    }

    /**
     * Retrieve the Setup Object
     *
     * @author Varun Shoor
     * @return object The SWIFT_Setup Object Pointer
     */
    public function _GetSetupObject()
    {
        return $this->Setup;
    }

    /**
     * The Index Function, Display the License Agreement
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function Index()
    {
        $this->View->Header();
        $this->View->DisplayLicenseAgreement();
        $this->View->Footer();

        SWIFT_CacheManager::EmptyCacheDirectory();

        return true;
    }

    /**
     * Run the Console Upgrade
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Console()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!defined('SETUP_CONSOLE')) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_ConsoleObject = new SWIFT_Console();
        $_SWIFT_ConsoleObject->WriteLine($_SWIFT_ConsoleObject->Green('======================='));
        $_SWIFT_ConsoleObject->WriteLine($_SWIFT_ConsoleObject->Yellow('KAYAKO CONSOLE UPGRADE'));
        $_SWIFT_ConsoleObject->WriteLine($_SWIFT_ConsoleObject->Green('======================='));

        $_argumentsContainer = func_get_args();

        $_isForced = false;
        $_forceVersion = '';
        while ($arg = array_shift($_argumentsContainer)) {
            if ($arg === '--force') {
                $_isForced = true;
                $_SWIFT_ConsoleObject->WriteLine(
                    $_SWIFT_ConsoleObject->Yellow('Using force upgrade: ') .
                    $_SWIFT_ConsoleObject->Green('YES'));
            }
            if ($arg === '--version') {
                $_forceVersion = array_shift($_argumentsContainer);
                if ($_forceVersion) {
                    $_SWIFT_ConsoleObject->WriteLine(
                        $_SWIFT_ConsoleObject->Yellow('Using forced version: ') .
                        $_SWIFT_ConsoleObject->Green($_forceVersion));
                }
            }
        }

        $_productURL = isset($_argumentsContainer[0]) ? urldecode($_argumentsContainer[0]) : '';

        $this->Load->Library('Console:Console');

        $this->Setup->SetMode(SWIFT_Setup::MODE_CLI);

        $_appList = array_merge(array('core'), SWIFT_App::ListApps());

        // If anything related to progress changes, please inform platform team. Because it affects how overall progress is displayed
        $_totalProgressCount   = count(array_unique($_appList)) * 2 + 6; // 2 weight for apps, 2 for system checks, 1 for legacy data reset, 1 for settings import, 1 for rebuild cache, 1 for empty cache directory

        if (in_array(APP_BASE, $_appList)) {
            $_totalProgressCount += 2; // 1 for template import, 1 for language import
        }

        $_currentProgressIndex = 0;

        // Run System Checks
        $this->Setup->RunSystemChecks();

        $this->ShowProgress($_currentProgressIndex, $_totalProgressCount);

        $_currentProgressIndex += 2;

        if (!$this->Setup->GetStatus())
        {
            $this->Console->Message('Setup System Checks Failed', SWIFT_Console::CONSOLE_ERROR);

            return false;
        }

        // Reset the legacy data. registeredmodules > installedapps & moduleversions > appversions
        $this->ResetLegacyData();

        $this->ShowProgress($_currentProgressIndex, $_totalProgressCount);

        $_currentProgressIndex++;

        // Install all apps
        $_upgradedApps = array();

        foreach ($_appList as $_appName)
        {
            if (in_array($_appName, $_upgradedApps))
            {
                continue;
            }

            /** @var SWIFT_App $_SWIFT_AppObject */
            $_SWIFT_AppObject = SWIFT_App::Get($_appName);

            $_SWIFT_SetupDatabaseObject = $this->Setup->LoadAppSetupObject($_appName);
            if (!$_SWIFT_SetupDatabaseObject)
            {
                $this->Setup->Status(sprintf($this->Language->Get('scmodsetfailed'), $_appName), false);

                $this->Setup->SetStatus(false);

                break;

            // Only upgrade if the app is installed
            } else if (SWIFT_App::IsInstalled($_appName)) {
                $this->Setup->Message('Upgrading App: ' . $_appName);

                $_SWIFT_SetupDatabaseObject->Upgrade($_isForced, $_forceVersion);

                $_upgradedApps[] = $_appName;

            // This app isnt installed right now.. wasnt installed ever and is a core app.. seems like we introduced new functionality.. install it!
            } else if (!SWIFT_App::IsInstalled($_appName) && !$_SWIFT_AppObject->WasInstalled() && $_SWIFT_AppObject->IsCoreApp()) {
                $this->Setup->Message('Installing App: ' . $_appName);

                $_SWIFT_SetupDatabaseObject->InstallApp();

                $_upgradedApps[] = $_appName;
            }

            $this->ShowProgress($_currentProgressIndex, $_totalProgressCount);

            $_currentProgressIndex += 2;
        }

        if (!$this->Setup->GetStatus())
        {
            $this->Console->Message('Setup App Upgrade Failed', SWIFT_Console::CONSOLE_ERROR);

            return false;
        }

        // Import all settings
        $this->Load->Library('Settings:SettingsManager');

        $_companyName = $_defaultReturnEmail = '';

        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "settings WHERE vkey IN ('general_companyname', 'general_producturl', 'general_returnemail')");
        while ($this->Database->NextRecord())
        {
            if ($this->Database->Record['vkey'] == 'general_companyname')
            {
                $_companyName = $this->Database->Record['data'];
            } else if (empty($_productURL) && $this->Database->Record['vkey'] == 'general_producturl') {
                $_productURL = $this->Database->Record['data'];
            } else if ($this->Database->Record['vkey'] == 'general_returnemail') {
                $_defaultReturnEmail = $this->Database->Record['data'];
            }
        }

        $_statusListContainer = $this->SettingsManager->ImportAll(true, $_companyName, $_productURL, $_defaultReturnEmail);
        $this->Setup->StatusList($_statusListContainer);
        if (!$this->Setup->GetStatus())
        {
            $this->Console->Message('Settings Import Failed', SWIFT_Console::CONSOLE_ERROR);

            return false;
        } else {
            $this->ShowProgress($_currentProgressIndex, $_totalProgressCount);

            $_currentProgressIndex++;
        }

        // Import all Templates
        if (in_array(APP_BASE, $_appList)) {
            $this->Load->Library('Template:TemplateManager', [], true, false, 'base');

            $_templateGroupContainer = $this->Database->QueryFetch("SELECT tgroupid AS tgroupid FROM ". TABLE_PREFIX . "templategroups ORDER BY tgroupid ASC");
            $_statusListContainer = array();

            if (!isset($_templateGroupContainer['tgroupid']) || empty($_templateGroupContainer['tgroupid']))
            {
                $this->Setup->SetStatus(false);
            } else {
                $_templateResultContainer = $this->TemplateManager->ImportAll(true);

                foreach ($_templateResultContainer['categorylist'] as $_categoryName) {
                    $_statusListContainer[] = array('statusText' => 'Creating Category: ' . $_categoryName, 'result' => true, 'reasonFailure' => '');
                }

                foreach ($_templateResultContainer['templatelist'] as $_templateName) {
                    $_statusListContainer[] = array('statusText' => 'Updating Template: ' . $_templateName, 'result' => true, 'reasonFailure' => '');
                }
            }

            $this->Setup->StatusList($_statusListContainer);
            if (!$this->Setup->GetStatus())
            {
                $this->Console->Message('Templates Import Failed', SWIFT_Console::CONSOLE_ERROR);

                return false;
            } else {
                $this->ShowProgress($_currentProgressIndex, $_totalProgressCount);

                $_currentProgressIndex++;
            }

            // Import the Languages
            $this->Load->Library('Language:LanguageManager', [], true, false, 'base');
            $_statusListContainer = array();

            $_languageList = $this->LanguageManager->ImportAll(true);
            foreach ($_languageList as $_languageTitle) {
                $_statusListContainer[] = array('statusText' => 'Updating Language: ' . htmlspecialchars($_languageTitle), 'result' => true, 'reasonFailure' => '');
            }

            $this->Setup->StatusList($_statusListContainer);
            if (!$this->Setup->GetStatus())
            {
                $this->Console->Message('Language Import Failed', SWIFT_Console::CONSOLE_ERROR);

                return false;
            } else {
                $this->ShowProgress($_currentProgressIndex, $_totalProgressCount);

                $_currentProgressIndex++;
            }
        }

        // Rebuild the Caches
        $this->Setup->RebuildAllCaches($_appList, true);

        $this->ShowProgress($_currentProgressIndex, $_totalProgressCount);

        $_currentProgressIndex++;

        // Clear Cache Directory
        SWIFT_CacheManager::EmptyCacheDirectory();

        $this->ShowProgress($_currentProgressIndex, $_totalProgressCount);

        $this->Console->Message('Upgrade Process Completed!');

        return true;
    }

    /**
     * Display progress message
     *
     * @author Atul Atri
     *
     * @param int $_currentProgressIndex
     * @param int $_totalProgressCount
     *
     * @return void
     */
    private function ShowProgress($_currentProgressIndex, $_totalProgressCount) {
        $_completed = ($_currentProgressIndex + 1 == $_totalProgressCount) ? 100 : floor(100 * ($_currentProgressIndex + 1)/ $_totalProgressCount);

        $this->Console->Message('Upgrade Progress ' . ' [' . $_completed . '%]', SWIFT_Console::CONSOLE_INFO);
    }

    /**
     * Processes the Steps and Displays results accordingly
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function StepProcessor()
    {
        if (!$this->GetIsClassLoaded())
        {
            return false;
        }

        switch ($this->View->GetCurrentStep())
        {
            // License Agreement
            case 1:
                $this->_RunStep1();
            break;

            // System Checks
            case 2:
                $this->_RunStep2();
            break;

            // Upgrade Apps
            case 3:
                $this->_RunStep3();
            break;

            // Import Settings
            case 4:
                $this->_RunStep4();
            break;

            // Import Templates
            case 5:
                $this->_RunStep5();
            break;

            // Import Languages
            case 6:
                $this->_RunStep6();
            break;

            // Finalize
            case 7:
                $this->_RunStep7();
            break;

            default:
            break;
        }

        return true;
    }

    /**
     * Runs the Step 1: System Checks
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    protected function _RunStep1()
    {
        if (isset($_POST['submitbutton']) && $_POST['submitbutton'] == $this->Language->Get('sccontinue'))
        {
            return $this->_RunStep2();
        }

        $this->View->SetCurrentStep(1);
        $this->View->Header();

        $this->Setup->Message($this->Language->Get('stproduct'), SWIFT_PRODUCT);
        $this->Setup->Message($this->Language->Get('stversion'), SWIFT_VERSION);
        $this->Setup->Message($this->Language->Get('stbuilddate'), SWIFT::Get('builddate'));
        $this->Setup->Message($this->Language->Get('stbuildtype'), SWIFT::Get('buildtype'));
        $this->Setup->Message($this->Language->Get('stsourcetype'), SWIFT::Get('sourcetype'));
        $this->Setup->Status($this->Language->Get('stverifyingconnection'), $this->Database->IsConnected(), $this->Database->FetchLastError());

        if ($this->Database->IsConnected())
        {
            $this->Setup->RunSystemChecks();

            // ======= Continue With Other Stuff =======
            if ($this->Setup->GetStatus())
            {
            } else {
                $this->Setup->SetStatus(false);
            }
        } else {
            $this->Setup->SetStatus(false);
        }

        $this->View->Footer();
    }

    /**
     * Run the Step 2 (App Upgrade)
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    protected function _RunStep2()
    {
        $this->View->SetCurrentStep(2);
        $this->View->Header();

        // Does a app exist? If not, we start with core
        if (!isset($_POST['setupapp']) || empty($_POST['setupapp']))
        {
            $_POST['setupapp'] = 'core';
        }

        if (!isset($_POST['page']) || empty($_POST['page']))
        {
            $_POST['page'] = 1;
        } else {
            $_POST['page'] = (int) ($_POST['page']);
        }

        $_POST["setupapp"] = Clean($_POST["setupapp"]);

        $_executeApp = $_executePage = 0;

        // Reset the legacy data. registeredmodules > installedapps & moduleversions > appversions
        $this->ResetLegacyData();

        // We have the app list
        $_appList = SWIFT_App::ListApps();

        if (in_array($_POST['setupapp'], $_appList) && is_numeric($_POST['page']))
        {
            $_appName = $_POST['setupapp'];
            /** @var SWIFT_App $_SWIFT_AppObject */
            $_SWIFT_AppObject = SWIFT_App::Get($_appName);

            $_SWIFT_SetupDatabaseObject = SWIFT_App::RetrieveSetupDatabaseObject($_appName);
            $_appTotalPages = 1;

            // Display Current Page etc
            $this->Setup->Message('App: ' . $_appName . ' (Page ' . $_POST['page'] . ' of ' . $_appTotalPages . ')');

            $_statusList = array();

            // Upgrade only if the app is installed..
            if (SWIFT_App::IsInstalled($_appName)) {
                $_statusList = $_SWIFT_SetupDatabaseObject->Upgrade();

                // This app isnt installed right now.. wasnt installed ever and is a core app.. seems like we introduced new functionality.. install it!
            } else if (!SWIFT_App::IsInstalled($_appName) && !$_SWIFT_AppObject->WasInstalled() && $_SWIFT_AppObject->IsCoreApp()) {
                $this->Setup->Message('Installing App: ' . $_appName);

                $_statusList = $_SWIFT_SetupDatabaseObject->InstallApp();
            }

            if (!_is_array($_statusList))
            {
                $_statusList = array(array('Processing App: ' . $_appName));
            }

            $this->Setup->StatusListBasic($_statusList);

            // Calculate next step page
            if ($_POST["page"] >= $_appTotalPages)
            {
                // We have crossed the page or equalled our page, we need to move onto the first index of next app
                $_currentKey = array_search($_appName, $_appList);
                for ($_ii=$_currentKey+1; $_ii<count($_appList); $_ii++)
                {
                    // Does this app exist?
                    if (SWIFT_App::GetAppDirectory($_appList[$_ii]))
                    {
                        // Set this as next app and break
                        $this->View->_executeApp = $_appList[$_ii];
                        $this->View->_executePage = 1;
                        break;
                    }
                }
            } else {
                // We have not executed more than the totalpage count yet, increment it
                $this->View->_executeApp = $_appName;
                $this->View->_executePage = $_POST['page']+1;
            }
        }

        $this->View->Footer();

        return true;
    }

    /**
     * Step 4: Import Settings
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    protected function _RunStep3()
    {
        $this->View->SetCurrentStep(3);

        $this->Load->Library('Settings:SettingsManager');

        $_companyName = $_productURL = $_defaultReturnEmail = '';

        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "settings WHERE vkey IN ('general_companyname', 'general_producturl', 'general_returnemail')");
        while ($this->Database->NextRecord())
        {
            if ($this->Database->Record['vkey'] == 'general_companyname')
            {
                $_companyName = $this->Database->Record['data'];
            } else if ($this->Database->Record['vkey'] == 'general_producturl') {
                $_productURL = $this->Database->Record['data'];
            } else if ($this->Database->Record['vkey'] == 'general_returnemail') {
                $_defaultReturnEmail = $this->Database->Record['data'];
            }
        }

        $_statusListContainer = $this->SettingsManager->ImportAll(true, $_companyName, $_productURL, $_defaultReturnEmail);

        $this->View->Header();
        $this->Setup->StatusList($_statusListContainer);
        $this->View->Footer();

        return true;
    }

    /**
     * Step 5: Import Templates
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    protected function _RunStep4()
    {
        $this->View->SetCurrentStep(4);

        $this->Load->Library('Template:TemplateManager', [], true, false, 'base');

        $_templateGroupContainer = $this->Database->QueryFetch("SELECT tgroupid AS tgroupid FROM ". TABLE_PREFIX . "templategroups ORDER BY tgroupid ASC");
        $_statusListContainer = array();

        if (!isset($_templateGroupContainer['tgroupid']) || empty($_templateGroupContainer['tgroupid']))
        {
            $this->Setup->SetStatus(false);
        } else {
            $_templateResultContainer = $this->TemplateManager->ImportAll(true);

            foreach ($_templateResultContainer['categorylist'] as $_categoryName) {
                $_statusListContainer[] = array('statusText' => 'Creating Category: ' . $_categoryName, 'result' => true, 'reasonFailure' => '');
            }

            foreach ($_templateResultContainer['templatelist'] as $_templateName) {
                $_statusListContainer[] = array('statusText' => 'Updating Template: ' . $_templateName, 'result' => true, 'reasonFailure' => '');
            }
        }

        $this->View->Header();
        $this->Setup->StatusList($_statusListContainer);
        $this->View->Footer();

        return true;
    }

    /**
     * Step 6: Import Languages
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    protected function _RunStep5()
    {
        $this->View->SetCurrentStep(5);

        $this->Load->Library('Language:LanguageManager', [], true, false, 'base');
        $_statusListContainer = array();

        $_languageList = $this->LanguageManager->ImportAll(true);
        foreach ($_languageList as $_languageTitle) {
            $_statusListContainer[] = array('statusText' => 'Updating Language: ' . htmlspecialchars($_languageTitle), 'result' => true, 'reasonFailure' => '');
        }

        $this->View->Header();
        $this->Setup->StatusList($_statusListContainer);
        $this->View->Footer();

        return true;
    }

    /**
     * Step 7: Final Step (Rebuild Caches, Import LoginShare files)
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    protected function _RunStep6()
    {
        $this->View->SetCurrentStep(6);

        $_appList = array_merge(array('core'), SWIFT_App::ListApps(true));
        $this->Setup->RebuildAllCaches($_appList, true);

        SWIFT_CacheManager::EmptyCacheDirectory();

        $this->View->Header();
        $this->View->DisplayUpgradeConfirmation();
        $this->View->Footer();

        return true;
    }

    protected function _RunStep7()
    {

    }

    /**
     *     Reset the legacy data. registeredmodules > installedapps & moduleversions > appversions
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function ResetLegacyData()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        }

        $_registeredModules = $_moduleVersions = array();

        $this->Database->Query("SELECT vkey, data FROM " . TABLE_PREFIX . "settings WHERE section = 'registeredmodules'");
        while ($this->Database->NextRecord()) {
            $_registeredModules[$this->Database->Record['vkey']] = $this->Database->Record['data'];
        }

        if (_is_array($_registeredModules)) {
            foreach ($_registeredModules as $_keyName => $_keyValue) {
                $this->Settings->UpdateKey('installedapps', $_keyName, $_keyValue);
                if ($_keyValue == '1') {
                    SWIFT_App::AddToInstalledApps($_keyName);
                }
            }

            $this->Settings->DeleteSection('registeredmodules');
        }

        $this->Database->Query("SELECT vkey, data FROM " . TABLE_PREFIX . "settings WHERE section = 'moduleversions'");
        while ($this->Database->NextRecord()) {
            $_moduleVersions[$this->Database->Record['vkey']] = $this->Database->Record['data'];
        }

        if (_is_array($_moduleVersions)) {
            foreach ($_moduleVersions as $_keyName => $_keyValue) {
                $this->Settings->UpdateKey('appversions', $_keyName, $_keyValue);
            }

            $this->Settings->DeleteSection('moduleversions');
        }

        return true;
    }
}
?>
