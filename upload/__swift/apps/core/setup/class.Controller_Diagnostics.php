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

require_once 'class.View_Diagnostics.php';

/**
 * The Diagnostics Controller
 *
 * @method void _RunStep4()
 * @property SWIFT_Setup $Setup
 * @property View_Diagnostics $View
 * @property SWIFT_SetupDiagnostics $SetupDiagnostics
 * @author Varun Shoor
 */
class Controller_Diagnostics extends SWIFT_Controller
{
    /**
     * Constructor
     *
     * @author Varun Shoor
     */
    public function __construct()
    {
        parent::__construct();

        $this->Language->Load('setup');

        $this->Load->Library('Setup:SetupDiagnostics');

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

            // Display Diagnostics Results
            case 4:
                $this->_RunStep4();
            break;

            default:
            break;
        }

        return true;
    }

    /**
     * Get the Database Version
     *
     * @author Varun Shoor
     */
    protected function _GetVersion(): string
    {
        $_versionContainer = $this->Database->QueryFetch("SELECT * FROM ". TABLE_PREFIX ."settings WHERE section = 'core' AND `vkey` = 'version'");

        if (!isset($_versionContainer['data']) || empty($_versionContainer['data']))
        {
            $this->Setup->SetStatus(false);

            return '';
        }

        return $_versionContainer['data'];
    }

    /**
     * Runs the Step 1: System Checks
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    protected function _RunStep1()
    {
        $this->View->SetCurrentStep(1);
        $this->View->Header();

        $this->Setup->Message($this->Language->Get('stproduct'), SWIFT_PRODUCT);
        $this->Setup->Message($this->Language->Get('stversion'), SWIFT_VERSION);
        $this->Setup->Message($this->Language->Get('stcurrentversion'), $this->_GetVersion());
        $this->Setup->Message($this->Language->Get('stbuilddate'), SWIFT::Get('builddate'));
        $this->Setup->Message($this->Language->Get('stbuildtype'), SWIFT::Get('buildtype'));
        $this->Setup->Message($this->Language->Get('stsourcetype'), SWIFT::Get('sourcetype'));

        $this->Setup->Status($this->Language->Get('stverifyingconnection'), $this->Database->IsConnected(), $this->Database->FetchLastError());

        if ($this->Database->IsConnected())
        {
            $this->Setup->RunSystemChecks();
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
    protected function _RunStep2()
    {
        $this->View->SetCurrentStep(2);
        $this->View->Header();

        $this->View->DisplayInput($this->Language->Get('scaction'), "action", '<select name="action" class="swiftselect"><option value="dbstructure">'. $this->Language->Get('sccheckdbstruct') .'</option></select>', 'select', true);

        $this->View->Footer();

        return true;
    }

    /**
     * Run the Step 3: Diagnostics Result
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    protected function _RunStep3()
    {
        $this->View->SetCurrentStep(3);
        $this->View->Header();

        if ($_POST['action'] == 'dbstructure')
        {
            $this->View->DatabaseDiagnosticsResult();
        }

        $this->View->Footer();

        return true;
    }
}
?>
