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

require_once 'class.Controller_Setup.php';
/**
 * The Setup Base View Class
 *
 * @author Varun Shoor
 *
 * @property Controller_Setup $Controller
 */
class View_SetupBase extends SWIFT_View
{
    private $_currentStep = 0;
    private $_finalSteps = array();
    private $_setupType = 0;
    private $_reasonFailure = '';

    public $_executeApp;
    public $_executePage;

    // Core Constants
    const TYPE_SETUP = 'Setup';
    const TYPE_UPGRADE = 'Upgrade';
    const TYPE_DIAGNOSTICS = 'Diagnostics';

    /**
     * Constructor
     *
     * @author Varun Shoor
     */
    public function __construct()
    {
        parent::__construct();

        if (isset($_POST['isajax']) && $_POST['isajax'] == '1')
        {
            $this->Template->Assign('_isAJAX', true);
        } else {
            $this->Template->Assign('_isAJAX', false);
        }
    }

    /**
     * Checks to see if the given value is a valid setup type
     *
     * @author Varun Shoor
     * @param string $_setupType The Setup Type
     * @return bool "true" on Success, "false" otherwise
     */
    protected static function IsValidSetupType($_setupType): bool
    {
        if ($_setupType == self::TYPE_SETUP || $_setupType == self::TYPE_UPGRADE || $_setupType == self::TYPE_DIAGNOSTICS)
        {
            return true;
        }

        return false;
    }

    /**
     * Sets the current setup type
     *
     * @author Varun Shoor
     * @param string $_setupType The Setup Type
     * @return bool "true" on Success, "false" otherwise
     */
    protected function SetType($_setupType)
    {
        if (!self::IsValidSetupType($_setupType))
        {
            return false;
        }

        $this->_setupType = $_setupType;

        return true;
    }

    /**
     * Retrieve the currently set setup type
     *
     * @author Varun Shoor
     * @return mixed "_setupType" (INT) on Success, "false" otherwise
     */
    protected function GetType()
    {
        if (!$this->GetIsClassLoaded())
        {
            return false;
        }

        return $this->_setupType;
    }

    /**
     * Display the license agreement template
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function DisplayLicenseAgreement()
    {
        try
        {
            $this->Template->Render('license');
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            trigger_error($_SWIFT_ExceptionObject, E_USER_ERROR);

            return false;
        }

        return true;
    }

    /**
     * Display the header template
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function Header()
    {
        try
        {
            $this->Template->Render('header');
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            trigger_error($_SWIFT_ExceptionObject, E_USER_ERROR);

            return false;
        }

        return true;
    }

    /**
     * Display the Footer Template
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function Footer()
    {
        $this->DisplayFooterButtons();

        try
        {
            $this->Template->Render('footer');
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            trigger_error($_SWIFT_ExceptionObject, E_USER_ERROR);

            return false;
        }

        return true;
    }

    /**
     * Sets the current setup step
     *
     * @author Varun Shoor
     * @param int $_currentStep The Current Setup Step
     * @return bool "true" on Success, "false" otherwise
     */
    public function SetCurrentStep($_currentStep)
    {
        $_currentStep =  ($_currentStep);

        $this->_currentStep = $_currentStep;

        return true;
    }

    /**
     * Get the current step
     *
     * @author Varun Shoor
     * @return mixed "_currentStep" (INT) on Success, "false" otherwise
     */
    public function GetCurrentStep()
    {
        return $this->_currentStep;
    }

    /**
     * Sets the final steps
     *
     * @author Varun Shoor
     * @param array $_finalSteps The Final Processed Steps
     * @return bool "true" on Success, "false" otherwise
     */
    protected function SetSteps($_finalSteps)
    {
        if (!_is_array($_finalSteps))
        {
            return false;
        }

        $this->_finalSteps = $_finalSteps;

        return true;
    }

    /**
     * Retrieve the final processed steps
     *
     * @author Varun Shoor
     * @return mixed "_finalSteps" (ARRAY) on Success, "false" otherwise
     */
    protected function GetSteps()
    {
        if (!$this->GetIsClassLoaded())
        {
            return false;
        }

        return $this->_finalSteps;
    }

    /**
     * Loads the setup steps into the template and local class scope
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadSteps($_setupSteps)
    {
        $_arguments = $this->Router->GetArguments();

        $_defaultStep = $this->GetCurrentStep();

        $_finalSetupSteps = array();
        foreach ($_setupSteps as $_key => $_val)
        {
            $_finalSetupSteps[$_key]['name'] = $_val;

            if ($_key == $_defaultStep)
            {
                if ($_key == (count($_setupSteps)-1))
                {
                    $_finalSetupSteps[$_key]['type'] = 'doneactive';
                } else {
                    $_finalSetupSteps[$_key]['type'] = 'active';
                }

                if ($_key == 0)
                {
                    $this->Template->Assign('_setupStep', ($_defaultStep+1) . '. &nbsp;' . $this->Language->Get('eula'));
                } else {
                    $this->Template->Assign('_setupStep', ($_defaultStep+1) . '. &nbsp;' . $_val);
                }
            } else if ($_key < $_defaultStep) {
                $_finalSetupSteps[$_key]['type'] = 'done';
            } else {
                $_finalSetupSteps[$_key]['type'] = 'notdone';
            }
        }

        $this->SetSteps($_finalSetupSteps);

        $this->Template->Assign('_setupSteps', $_finalSetupSteps);

        return true;
    }

    /**
     * Display the Status Message
     *
     * @author Varun Shoor
     * @param string $_statusText The Status Text
     * @param bool $_result The Result of Check
     * @param string $_reasonFailure (OPTIONAL) The Reason for Failure
     * @return bool "true" on Success, "false" otherwise
     */
    public function DisplayStatus($_statusText, $_result = true, $_reasonFailure = '')
    {
        if (!$_result)
        {
            echo '<table width="98%"  border="0" cellspacing="0" cellpadding="4"><tr><td width="60%"><span class="smalltext">'. $_statusText .' </span></td><td width="40%" align="right"><span class="statusfailed">FAILED</span></td></tr>';
            if (!empty($_reasonFailure))
            {
                echo '<tr><td width="100%" colspan="2" class="smalltext"><font color="red">Reason: '.$_reasonFailure .'</font></td></tr>';

                $this->_reasonFailure = $_reasonFailure;
            }

            echo '</table>';

        } else {
            echo '<table width="98%"  border="0" cellspacing="0" cellpadding="4"><tr><td width="60%"><span class="smalltext">' .$_statusText .'</span></td><td width="40%" align="right"><span class="statusok">OK</span></td></tr></table>';
        }

        return true;
    }

    /**
     * Displays the message to the user
     *
     * @author Varun Shoor
     * @param string $_messageText The Message Text
     * @param string $_messageValue (OPTIONAL) The Message Value
     * @param string $_reasonText (OPTIONAL) The Reason Text
     * @return bool "true" on Success, "false" otherwise
     */
    public function DisplayMessage($_messageText, $_messageValue = '', $_reasonText = '')
    {
        echo '<table width="98%"  border="0" cellspacing="0" cellpadding="4"><tr><td width="" align="left" class="smalltext">'. IIF(empty($_messageValue), '<b>') . $_messageText . IIF(empty($_messageValue), '</b>') . '</td>'. IIF(!empty($_messageValue), '<td width="26%" align="right" class="smalltext"><b>'.$_messageValue.'</b>') . '</td></tr>';

        if ($_reasonText != '')
        {
            echo '<tr><td colspan="2" width="100%" class="smalltext"><font color="green">'. $_reasonText .'</font></td></tr>';
        }
        echo '</table>';

        return true;
    }

    /**
     * Displays an input field
     *
     * @author Varun Shoor
     * @param string $_fieldTitle The Field Title
     * @param string $_fieldName The Field Name
     * @param string $_defaultValue The Default Value
     * @param string $_fieldType The Field Type
     * @param bool $_displayDefaultValue Whether field data should be displayed directly
     * @return bool "true" on Success, "false" otherwise
     */
    public function DisplayInput($_fieldTitle, $_fieldName, $_defaultValue = '', $_fieldType = 'text', $_displayDefaultValue = false)
    {
        echo '<table width="98%"  border="0" cellspacing="1" cellpadding="3"><tr><td width="200" class="row1"><b>&nbsp;'. $_fieldTitle .':</b></span></td><td align="left" class="smalltext"><b>';
        if ($_displayDefaultValue == false)
        {
            echo '<input type="'. $_fieldType .'" name="'. $_fieldName .'" id="' . $_fieldName . '" value="'. $_defaultValue .'" class="swifttext">';
        } else {
            echo $_defaultValue;
        }
        echo '</b></td></tr></table>';

        return true;
    }

    /**
     * Displays an Error
     *
     * @author Varun Shoor
     * @param string $_errorMessage The Error Message
     * @return bool "true" on Success, "false" otherwise
     */
    public function DisplayError($_errorMessage)
    {
        echo '<div class="error">'. $_errorMessage . '</div>';

        return true;
    }

    /**
     * Display the footer buttons
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    private function DisplayFooterButtons()
    {
        $_resultHTML = '';

        if ($this->Controller->_GetSetupObject()->GetStatus() == true)
        {
            $_resultHTML .= '<br /><hr class="contenthr" />';
        } else {
            if ($this->GetCurrentStep() == '1' && $this->Controller->_GetSetupObject()->GetStatus() == false && ($this->GetType() == self::TYPE_SETUP || $this->GetType() == self::TYPE_UPGRADE)) {
                $_resultHTML .= '<table><tr><td class="smalltext"><font color="red"><b>'. $this->Language->Get('scsyscheckfailed') .'</b></font></td></tr></table>';
            } else {
                $_resultHTML .= '<br /><HR class="contenthr"><div class="error">'. '<b>' . $this->Language->Get('errorprefix') . '</b>' . htmlspecialchars($this->_reasonFailure) .'</div>';
            }

        }

        $_coreFieldHTML = '';
        if ($this->GetCurrentStep() != 2 && isset($_POST['username'], $_POST['firstname'], $_POST['lastname'], $_POST['email'], $_POST['password'], $_POST['password2'], $_POST['producturl'], $_POST['companyname']))
        {
            $_coreFieldHTML = '<input type="hidden" name="username" value="' . htmlspecialchars($_POST['username']) . '" /><input type="hidden" name="firstname" value="' . htmlspecialchars($_POST['firstname']) . '" /><input type="hidden" name="lastname" value="' . htmlspecialchars($_POST['lastname']) . '" /><input type="hidden" name="email" value="' . htmlspecialchars($_POST['email']) . '" /><input type="hidden" name="password" value="' . htmlspecialchars($_POST['password']) . '" /><input type="hidden" name="password2" value="' . htmlspecialchars($_POST['password2']) . '" /><input type="hidden" name="producturl" value="' . htmlspecialchars($_POST['producturl']) . '" /><input type="hidden" name="companyname" value="' . htmlspecialchars($_POST['companyname']) . '" />';
        }

        $_resultHTML .= $_coreFieldHTML;

        // ======= FIRST STEP: Display the I Agree button =======
        if ($this->GetCurrentStep() == '0')
        {
            $_resultHTML .= '<input class="rebutton2 rebutton2default" type="submit" name="submitbutton" value="'. $this->Language->Get('iagree') .'" /><input type="hidden" name="step" value="1" />';
        // ======= SECOND STEP: For the core setup and if there are existing tables=======
        } else if ($this->GetCurrentStep() == '1' && $this->Controller->_GetSetupObject()->GetStatus() == true && ($this->GetType() == self::TYPE_SETUP || $this->GetType() == self::TYPE_UPGRADE)) {
            $_resultHTML .= IIF(!$this->Controller->_GetSetupObject()->_databaseEmpty, '<input type="hidden" id="docleardb" name="docleardb" value="0" /><input class="rebutton2 rebuttonred2 todisablebutton" type="button" onclick="javascript: HandleClearDatabase();" name="submitbutton" value="'. $this->Language->Get('sccleardb') .'" /> ') . '<input class="rebutton2 rebutton2default todisablebutton" type="submit" name="submitbutton" value="'. IIF($this->Controller->_GetSetupObject()->_databaseEmpty, $this->Language->Get('scnext'), $this->Language->Get('sccontinue')) .'" /><input type="hidden" name="step" value="'. IIF($this->Controller->_GetSetupObject()->_databaseEmpty, '2', '1') .'" />';
        // ======= SECOND STEP: System Requirement Check Failed =======
        } else if ($this->GetCurrentStep() == '1' && $this->Controller->_GetSetupObject()->GetStatus() == false && ($this->GetType() == self::TYPE_SETUP || $this->GetType() == self::TYPE_UPGRADE)) {
        // The system requirements step has failed; don't allow them to continue and display a message explaining what happened.

        // ======= THIRD STEP: Request for information from the user =======
        } else if ($this->GetCurrentStep() == '2' && $this->GetType() == self::TYPE_SETUP) {
            $_resultHTML .= '<input class="rebutton2 rebutton2default todisablebutton" type="submit" name="submitbutton" value="'. $this->Language->Get('scstartsetup') .'" /><input type="hidden" name="step" value="3" />';

        // ======= FOURTH STEP: Move onto Settings =======
        } else if ($this->GetCurrentStep() == '3' && $this->GetType() == self::TYPE_SETUP && $this->Controller->_GetSetupObject()->GetStatus() == true) {
            // Fun! Fun! All auto setup, should display just the submit button where JS is disabled, if JS is enabled, should disable the submit button and move over to next page automatically.

            if (trim($this->_executeApp) == '')
            {
                $_resultHTML .= '<input class="rebutton2 rebuttonred2" type="submit" name="submitbutton" id="autosetupbutton" value="'. $this->Language->Get('scsettingsbut') .'" /><input type="hidden" name="step" value="4" />';
            } else if ($this->Controller->_GetSetupObject()->GetStatus() == true) {
                $_resultHTML .= '<input class="rebutton2 rebuttongreen2" type="submit" name="submitbutton" id="autosetupbutton" value="'. $this->Language->Get('scnext') .'" /><input type="hidden" name="step" value="3" /><input type="hidden" name="setupapp" value="'. Clean($this->_executeApp) .'" /><input type="hidden" name="page" value="'. (int) ($this->_executePage) .'" />';
            }

        // ======= FIFTH STEP: Move onto Templates =======
        } else if ($this->GetCurrentStep() == '4' && $this->GetType() == self::TYPE_SETUP && $this->Controller->_GetSetupObject()->GetStatus() == true) {
            $_resultHTML .= '<input class="rebutton2 rebuttonred2" type="submit" name="submitbutton" id="autosetupbutton" value="'. $this->Language->Get('sctemplates') .'" /><input type="hidden" name="step" value="5" />';

        // ======= SIXTH STEP: Move onto Languages =======
        } else if ($this->GetCurrentStep() == '5' && $this->GetType() == self::TYPE_SETUP && $this->Controller->_GetSetupObject()->GetStatus() == true) {
            $_resultHTML .= '<input class="rebutton2 rebuttonred2" type="submit" name="submitbutton" id="autosetupbutton" value="'. $this->Language->Get('sclanguages') .'" /><input type="hidden" name="step" value="6" />';
        // ======= SEVENTH STEP: Move onto the finish part =======
        } else if ($this->GetCurrentStep() == "6" && $this->GetType() == self::TYPE_SETUP && $this->Controller->_GetSetupObject()->GetStatus() == true) {
            $_resultHTML .= '<input class="rebutton2 rebuttonred2" type="submit" id="autosetupbutton" name="submitbutton" value="'. $this->Language->Get('scfinish') .'" /><input type="hidden" name="step" value="7" />';
        // ======= SECOND STEP: Valid only for upgrade, impex and modify =======
        } else if ($this->GetCurrentStep() == '1' && $this->Controller->_GetSetupObject()->GetStatus() == true && ($this->GetType() == self::TYPE_DIAGNOSTICS)) {
            $_resultHTML .= '<input class="rebutton2 rebutton2default" type="submit" name="submitbutton" value="'. $this->Language->Get('scnext') .'" /><input type="hidden" name="step" value="2" />';
        // ======= THIRD STEP: Valid only for upgrade, impex and modify =======
        } else if ($this->GetCurrentStep() == '2' && $this->Controller->_GetSetupObject()->GetStatus() == true && $this->GetType() == self::TYPE_DIAGNOSTICS) {
            $_resultHTML .= '<input class="rebutton2 rebutton2default ' . IIF($this->GetType() == self::TYPE_DIAGNOSTICS, 'todisablebutton') . '" type="submit" name="submitbutton" value="'. $this->Language->Get('scnext') .'" /><input type="hidden" name="step" value="3" />';


        // ======= FOURTH STEP: Move onto Settings (UPGRADE) =======
        } else if ($this->GetCurrentStep() == '2' && $this->GetType() == self::TYPE_UPGRADE && $this->Controller->_GetSetupObject()->GetStatus() == true) {
            // Fun! Fun! All auto setup, should display just the submit button where JS is disabled, if JS is enabled, should disable the submit button and move over to next page automatically.
            if (trim($this->_executeApp) == '')
            {
                $_resultHTML .= '<input class="rebutton2 rebuttonred2" type="submit" name="submitbutton" id="autosetupbutton" value="'. $this->Language->Get('scsettingsbut') .'" /><input type="hidden" name="step" value="3" />';
            } else if ($this->Controller->_GetSetupObject()->GetStatus() == true) {
                $_resultHTML .= '<input class="rebutton2 rebuttongreen2" type="submit" name="submitbutton" id="autosetupbutton" value="'. $this->Language->Get('scnext') .'" /><input type="hidden" name="step" value="2" /><input type="hidden" name="setupapp" value="'. Clean($this->_executeApp) .'" /><input type="hidden" name="page" value="'. (int) ($this->_executePage) .'" />';
            }

        // ======= FIFTH STEP: Move onto Templates (UPGRADE) =======
        } else if ($this->GetCurrentStep() == '3' && $this->GetType() == self::TYPE_UPGRADE && $this->Controller->_GetSetupObject()->GetStatus() == true) {
            $_resultHTML .= '<input class="rebutton2 rebuttonred2" type="submit" name="submitbutton" id="autosetupbutton" value="'. $this->Language->Get('sctemplates') .'" /><input type="hidden" name="step" value="4" />';

        // ======= SIXTH STEP: Move onto Languages (UPGRADE) =======
        } else if ($this->GetCurrentStep() == '4' && $this->GetType() == self::TYPE_UPGRADE && $this->Controller->_GetSetupObject()->GetStatus() == true) {
            $_resultHTML .= '<input class="rebutton2 rebuttonred2" type="submit" name="submitbutton" id="autosetupbutton" value="'. $this->Language->Get('sclanguages') .'" /><input type="hidden" name="step" value="5" />';
        // ======= SEVENTH STEP: Move onto the finish part (UPGRADE) =======
        } else if ($this->GetCurrentStep() == "5" && $this->GetType() == self::TYPE_UPGRADE && $this->Controller->_GetSetupObject()->GetStatus() == true) {
            $_resultHTML .= '<input class="rebutton2 rebuttonred2" type="submit" id="autosetupbutton" name="submitbutton" value="'. $this->Language->Get('scfinish') .'" /><input type="hidden" name="step" value="6" />';
        }

        $this->Template->Assign('_footerButtonHTML', $_resultHTML);

        return true;
    }
}
?>
