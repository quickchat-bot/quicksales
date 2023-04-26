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

/**
 * The Setup Controller
 *
 * @author Varun Shoor
 *
 * @property View_Setup $View
 * @property SWIFT_Setup $Setup
 * @property SWIFT_SettingsManager $SettingsManager
 * @property \Base\Library\Template\SWIFT_TemplateManager $TemplateManager
 * @property \Base\Library\Language\SWIFT_LanguageManager $LanguageManager
 */
class Controller_Setup extends SWIFT_Controller
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
     * Run the Console Setup
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

            return false;
        } else if (!defined('SETUP_CONSOLE')) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_ConsoleObject = new SWIFT_Console();
        $_SWIFT_ConsoleObject->WriteLine($_SWIFT_ConsoleObject->Green('====================='));
        $_SWIFT_ConsoleObject->WriteLine($_SWIFT_ConsoleObject->Yellow('KAYAKO CONSOLE SETUP'));
        $_SWIFT_ConsoleObject->WriteLine($_SWIFT_ConsoleObject->Green('====================='));

        $_argumentsContainer = func_get_args();

        if (count($_argumentsContainer) != 7) {
            throw new SWIFT_Exception('Please check input. Need data in format of "Company Name" "Product URL" "First Name" "Last Name" "Username" "Password" "Email"');
        }

        $_POST['companyname'] = $_argumentsContainer[0];
        $_POST['producturl'] = $_argumentsContainer[1];
        $_POST['firstname'] = $_argumentsContainer[2];
        $_POST['lastname'] = $_argumentsContainer[3];
        $_POST['username'] = $_argumentsContainer[4];
        $_POST['password'] = $_argumentsContainer[5];
        $_POST['email'] = $_argumentsContainer[6];

        $this->Load->Library('Console:Console');

        $this->Setup->SetMode(SWIFT_Setup::MODE_CLI);

        // Install all apps
        $_installedApps        = array();
        $_currentProgressIndex = 0;

        $_appList = array_merge(array('core'), SWIFT_App::ListApps());

        // If anything related to progress changes, please inform platform team. Because it affects how overall progress is displayed
        $_totalProgressCount   = count(array_unique($_appList)) * 2 + 6; // 2 weight for each app installation, 2 for system checks, 1 for empty database, 1 for settings import, 1 for rebuild cache, 1 for final steps


        if (in_array(APP_BASE, $_appList)) {
            $_totalProgressCount += 2; // 1 for template import, 1 for language import
        }

        // Run System Checks
        $this->Setup->RunSystemChecks();

        if (!$this->Setup->GetStatus())
        {
            $this->Console->Message('Setup System Checks Failed', SWIFT_Console::CONSOLE_ERROR);

            return false;
        } else {
            $this->ShowProgress($_currentProgressIndex, $_totalProgressCount);

            $_currentProgressIndex += 2;
        }

        // Attempt to clear the database
        $this->Setup->StatusList(SWIFT_SetupDatabase::EmptyDatabase());

        $this->ShowProgress($_currentProgressIndex, $_totalProgressCount);

        $_currentProgressIndex += 1;

        foreach ($_appList as $_key => $_appName)
        {
            if (in_array($_appName, $_installedApps))
            {
                continue;
            }

            $_SWIFT_SetupDatabaseObject = $this->Setup->LoadAppSetupObject($_appName);
            if (!$_SWIFT_SetupDatabaseObject)
            {
                $this->Setup->Status(sprintf($this->Language->Get('scmodsetfailed'), $_appName), false);

                $this->Setup->SetStatus(false);

                break;
            } else {
                $_appTotalPages = (int) ($_SWIFT_SetupDatabaseObject->GetPageCount());
                if (!$_appTotalPages)
                {
                    $_appTotalPages = 1;
                }

                for ($_ii=1; $_ii<=$_appTotalPages; $_ii++)
                {
                    // Display Current Page etc
                    $this->Setup->Message("App: ". $_appName . " (Page ". $_ii ." of " . $_appTotalPages . ")");

                    $this->Setup->InstallApp($_appName, $_SWIFT_SetupDatabaseObject, $_ii);
                }

                $_installedApps[] = $_appName;
            }

            $this->ShowProgress($_currentProgressIndex, $_totalProgressCount);

            $_currentProgressIndex += 2;
        }

        if (!$this->Setup->GetStatus())
        {
            $this->Console->Message('Setup App Installation Failed', SWIFT_Console::CONSOLE_ERROR);

            return false;
        }

        // Import all settings
        $this->Load->Library('Settings:SettingsManager');
        $_statusListContainer = $this->SettingsManager->ImportAll(false, $_POST['companyname'], $_POST['producturl'], $_POST['email']);
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
            $_templateGroupContainer = $this->Database->QueryFetch("SELECT tgroupid AS tgroupid FROM " . TABLE_PREFIX . "templategroups ORDER BY tgroupid ASC");
            if (!isset($_templateGroupContainer['tgroupid']) || empty($_templateGroupContainer['tgroupid']))
            {
                $this->Setup->SetStatus(false);
            } else {
                $_statusListContainer = $this->TemplateManager->ImportAll(false);
                if (!$_statusListContainer)
                {
                    $this->Setup->SetStatus(false);
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
            $_statusListContainer = $this->LanguageManager->ImportAll(false);
            if (!$_statusListContainer)
            {
                $this->Setup->SetStatus(false);
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
        $this->Setup->RebuildAllCaches($_appList);

        $this->ShowProgress($_currentProgressIndex, $_totalProgressCount);

        $_currentProgressIndex++;

        $this->Setup->RunFinalSteps();

        $this->ShowProgress($_currentProgressIndex, $_totalProgressCount);

        $this->Console->Message('Setup Process Completed!');

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
        $_completed = ($_currentProgressIndex + 1 == $_totalProgressCount) ? 100 : floor(100 * ($_currentProgressIndex + 1) / $_totalProgressCount);

        $this->Console->Message('[STATUS] Setup Progress ' . ' [' . $_completed . '%]', SWIFT_Console::CONSOLE_INFO);
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

            // Ask for Input
            case 3:
                $this->_RunStep3();
                break;

            // Install Apps
            case 4:
                $this->_RunStep4();
                break;

            // Import Settings
            case 5:
                $this->_RunStep5();
                break;

            // Import Templates
            case 6:
                $this->_RunStep6();
                break;

            // Import Languages
            case 7:
                $this->_RunStep7();
                break;

            // Finalize
            case 8:
                $this->_RunStep8();
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
                // ok db was also selected, lets see if it is empty?
                $_isCleared = false;
                if (isset($_POST['docleardb']) && $_POST["docleardb"] == '1')
                {
                    $this->Setup->StatusList(SWIFT_SetupDatabase::EmptyDatabase());

                    $_isCleared = true;
                }

                $this->Setup->CheckDatabaseIsEmpty($_isCleared);

            } else {
                $this->Setup->SetStatus(false);
            }
        } else {
            $this->Setup->SetStatus(false);
        }

        $this->View->Footer();
    }

    /**
     * Run the Step 2: Ask for User Input
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    protected function _RunStep2($_errorString = '')
    {
        $this->View->SetCurrentStep(2);
        $this->View->Header();

        if (isset($_POST['username']))
        {
            $_userName = $_POST['username'];
        } else {
            $_userName = 'admin';
        }

        $_firstName = $_lastName = $_email = $_companyName = '';
        if (isset($_POST['firstname']))
        {
            $_firstName = $_POST['firstname'];
        }

        if (isset($_POST['lastname']))
        {
            $_lastName = $_POST['lastname'];
        }

        if (isset($_POST['email']))
        {
            $_email = $_POST['email'];
        }

        if (isset($_POST['companyname']))
        {
            $_companyName = $_POST['companyname'];
        }

        $this->View->DisplayInput('Admin username', 'username', $_userName, 'text', false);
        $this->View->DisplayInput('Password', 'password', '', 'password', false);
        $this->View->DisplayInput('Password (repeat)', 'password2', '', 'password', false);
        $this->View->DisplayInput('Admin first name', 'firstname', $_firstName, 'text', false);
        $this->View->DisplayInput('Admin last name', 'lastname', $_lastName, 'text', false);
        $this->View->DisplayInput('Admin e-mail address', 'email', $_email, 'text', false);
        $this->View->DisplayInput('Product URL', 'producturl', SWIFT_Setup::GetProductURL(), 'text', false);
        $this->View->DisplayInput('Organization name', 'companyname', $_companyName, 'text', false);

        if (!empty($_errorString))
        {
            $this->View->DisplayError($_errorString);
        }

        $this->View->Footer();

        return true;
    }

    /**
     * Run the Step 3 (App Setup)
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    protected function _RunStep3()
    {
        if ($_POST['password'] != $_POST['password2'])
        {
            return $this->_RunStep2($this->Language->Get('scpassnomatch'));
        } else if (trim($_POST['producturl']) == '' || trim($_POST['companyname']) == '' || trim($_POST['password']) == '' || trim($_POST['firstname']) == '' || trim($_POST['lastname']) == '' || trim($_POST['email']) == '') {
            return $this->_RunStep2($this->Language->Get('scfieldempty'));
        } else if (!IsEmailValid($_POST['email'])) {
            return $this->_RunStep2($this->Language->Get('scinvalidemail'));
        } else if (strlen($_POST['username']) >= 100){
            return $this->_RunStep2($this->Language->Get('scusernamelong'));
        } else if (strlen($_POST['firstname']) >= 100 ||  strlen($_POST['lastname']) >= 100 || strlen($_POST['email']) >= 255){
            return $this->_RunStep2($this->Language->Get('scfieldlong'));
        }

        $this->View->SetCurrentStep(3);
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

        // We have the app list
        $_appList = SWIFT_App::ListApps();
//        print_r($_appList);
//        error_log('SWIFT: Hard exiting at ' . __METHOD__); exit;
        if (in_array($_POST["setupapp"], $_appList) && is_numeric($_POST["page"]))
        {
            $_appName = $_POST["setupapp"];
            $_SWIFT_SetupDatabaseObject = $this->Setup->LoadAppSetupObject($_appName);
            if (!$_SWIFT_SetupDatabaseObject)
            {
                $this->Setup->Status(sprintf($this->Language->Get('scmodsetfailed'), $_appName), false);

                $this->Setup->SetStatus(false);
            } else {
                $_appTotalPages = (int) ($_SWIFT_SetupDatabaseObject->GetPageCount());
                if (!$_appTotalPages)
                {
                    $_appTotalPages = 1;
                }

                // Display Current Page etc
                $this->Setup->Message("App: ". $_appName . " (Page ". $_POST["page"] ." of " . $_appTotalPages . ")");

                $this->Setup->InstallApp($_appName, $_SWIFT_SetupDatabaseObject, $_POST['page']);

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
    protected function _RunStep4()
    {
        $this->View->SetCurrentStep(4);

        $this->Load->Library('Settings:SettingsManager');

        $_statusListContainer = $this->SettingsManager->ImportAll(false, $_POST['companyname'], $_POST['producturl'], $_POST['email']);

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
    protected function _RunStep5()
    {
        $this->View->SetCurrentStep(5);

        $this->Load->Library('Template:TemplateManager', [], true, false, 'base');

        $_templateGroupContainer = $this->Database->QueryFetch("SELECT tgroupid AS tgroupid FROM ". TABLE_PREFIX . "templategroups ORDER BY tgroupid ASC");

        $_statusListContainer = [];
        if (!isset($_templateGroupContainer['tgroupid']) || empty($_templateGroupContainer['tgroupid']))
        {
            $this->Setup->SetStatus(false);
        } else {
            $_statusListContainer = $this->TemplateManager->ImportAll(false);
            if (!$_statusListContainer)
            {
                $this->Setup->SetStatus(false);
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
    protected function _RunStep6()
    {
        $this->View->SetCurrentStep(6);

        $this->Load->Library('Language:LanguageManager', [], true, false, 'base');

        $_statusListContainer = $this->LanguageManager->ImportAll(false);
        if (!$_statusListContainer)
        {
            $this->Setup->SetStatus(false);
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
    protected function _RunStep7()
    {
        $this->View->SetCurrentStep(7);

        $_appList = array_merge(array('core'), SWIFT_App::ListApps());
        $this->Setup->RebuildAllCaches($_appList);
        $this->Setup->RunFinalSteps();

        $this->View->Header();
        $this->View->DisplaySetupConfirmation();
        $this->View->Footer();

        return true;
    }

    /**
     * Step 8: This is a placeholder
     *
     * @return bool
     */
    protected function _RunStep8()
    {
        return false;
    }
}
