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

try {
    SWIFT_Loader::LoadView('SetupBase', __DIR__);
} catch (SWIFT_Exception $e) {
}

/**
 * The Upgrade View Management Class
 *
 * @author Varun Shoor
 */
class View_Upgrade extends View_SetupBase
{
    /**
     * Constructor
     *
     * @author Varun Shoor
     */
    public function __construct()
    {
        parent::__construct();

        $this->SetType(self::TYPE_UPGRADE);

        $this->Template->Assign('_setupTypeString', $this->Language->Get('upgrade'));
        $this->Template->Assign('_setupType', 'Upgrade');

        if (isset($_POST['step']))
        {
            $this->SetCurrentStep((int) ($_POST['step']));
        }
    }

    /**
     * Display the Header
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function Header()
    {
        $_setupSteps = array(0 => $this->Language->Get('setlicenseagreement'), 1 => $this->Language->Get('setsysreq'), 2 => $this->Language->Get('upgautosetup'),
            3 => $this->Language->Get('setsettings'), 4 => $this->Language->Get('settemplates'), 5 => $this->Language->Get('setlocalization'), 6 => $this->Language->Get('setdone'));

        $this->LoadSteps($_setupSteps);

        parent::Header();
    }

    /**
     * Displays the Confirmation for Setup
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function DisplayUpgradeConfirmation()
    {
        $_returnHTML = $this->Language->Get('scupgradeconfirmation');

        echo $_returnHTML;

        return true;
    }
}
?>
