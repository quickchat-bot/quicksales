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

use Base\Library\Language\SWIFT_LanguageManager;
use Base\Library\Template\SWIFT_TemplateManager;

// TODO: Remove extra properties required by __swift/apps/core classes after adding namespaces

/**
 * The Setup Controller
 *
 * @property SWIFT_SettingsManager $SettingsManager
 * @property SWIFT_TemplateManager $TemplateManager
 * @property SWIFT_LanguageManager $LanguageManager
 * @property SWIFT_Setup $Setup
 * @author Varun Shoor
 */
class Controller_Setup extends Controller_console
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
    }

    /**
     * The Default Setup Method
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Index()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        SWIFT_ReportSetup::Install();

        return true;
    }

    // TODO: Remove extra methods required by __swift/apps/core classes after adding namespaces

    protected function _GetSetupObject(){
        return false;
    }

    protected function _RunStep1(){
        return false;
    }

    protected function _RunStep2($_param1 = ''){
        return false;
    }

    protected function _RunStep3(){
        return false;
    }

    protected function _RunStep4(){
        return false;
    }

    protected function _RunStep5(){
        return false;
    }

    protected function _RunStep6(){
        return false;
    }

    protected function _RunStep7(){
        return false;
    }

    protected function _RunStep8(){
        return false;
    }

    public function Console(){
        return false;
    }

    public function ShowProgress($_title = '', $_msg = ''){
        return false;
    }

    public function StepProcessor(){
        return false;
    }
}
