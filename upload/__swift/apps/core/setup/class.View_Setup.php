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
 * The Setup View Management Class
 * 
 * @author Varun Shoor
 */
class View_Setup extends View_SetupBase
{
    /**
     * Constructor
     *
     * @author Varun Shoor
     */
    public function __construct()
    {
        parent::__construct();

        $this->SetType(self::TYPE_SETUP);

        $this->Template->Assign('_setupTypeString', $this->Language->Get('setup'));
        $this->Template->Assign('_setupType', 'Setup');

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
        $_setupSteps = array(0 => $this->Language->Get('setlicenseagreement'), 1 => $this->Language->Get('setsysreq'), 2 => $this->Language->Get('setcollectinfo'), 3 => $this->Language->Get('setautosetup'), 4 => $this->Language->Get('setsettings'), 5 => $this->Language->Get('settemplates'), 6 => $this->Language->Get('setlocalization'), 7 => $this->Language->Get('setdone'));

        $this->LoadSteps($_setupSteps);

        parent::Header();
    }

    /**
     * Displays the Confirmation for Setup
     * 
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function DisplaySetupConfirmation()
    {
        $_returnHTML = '
        <table width="100%" border="0" cellspacing="0" cellpadding="2">
        <tr>
        <td width="1" align="left"><img src="../__swift/themes/setup/images/doublearrowsnav.gif" border="0"></td>
        <td width="100%" align="left" class="smalltext"><b>'. $this->Language->Get('scadmincp') .'</b></td>
        </tr>
        <tr>
        <td width="100%" align="left" colspan="2" class="smalltext"><a href="'. $_POST['producturl'] .'admin/index.php">'. $_POST['producturl'] .'admin/index.php</a></td>
        </tr>
        <tr>
        <td width="100%" align="left" colspan="2" class="smalltext"><HR class="contenthr"></td>
        </tr>
        <tr>
        <td width="1" align="left"><img src="../__swift/themes/setup/images/doublearrowsnav.gif" border="0"></td>
        <td width="100%" align="left" class="smalltext"><b>'. $this->Language->Get('scstaffcp') .'</b></td>
        </tr>
        <tr>
        <td width="100%" align="left" colspan="2" class="smalltext"><a href="'. $_POST['producturl'] .'staff/index.php">'. $_POST['producturl'] .'staff/index.php</a></td>
        </tr>
        <tr>
        <td width="100%" align="left" colspan="2" class="smalltext"><HR class="contenthr"></td>
        </tr>
        <tr>
        <td width="1" align="left"><img src="../__swift/themes/setup/images/doublearrowsnav.gif" border="0"></td>
        <td width="100%" align="left" class="smalltext"><b>'. $this->Language->Get('scclientsc') .'</b></td>
        </tr>
        <tr>
        <td width="100%" align="left" colspan="2" class="smalltext"><a href="'. $_POST['producturl'] .'index.php">'. $_POST['producturl'] .'index.php</a></td>
        </tr>
        <tr>
        <td width="100%" align="left" colspan="2" class="smalltext"><HR class="contenthr"></td>
        </tr>
        </table><div class="smalltext">'. $this->Language->Get('scsetupconfirmation') .'</div><BR />';

        echo $_returnHTML;

        return true;
    }
}
?>
