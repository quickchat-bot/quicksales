<?php
/**
 * ###############################################
 *
 * SWIFT Framework
 * _______________________________________________
 *
 * @author        Werner Garcia
 *
 * @package        SWIFT
 * @copyright    Copyright (c) 2001-2012, QuickSupport
 * @license        http://www.opencart.com.vn/license
 * @link        http://www.opencart.com.vn
 *
 * ###############################################
 */

namespace Base\Staff;

use Base\Library\Captcha\SWIFT_CaptchaManager;
use Base\Models\Staff\SWIFT_Staff;
use Base\Models\User\SWIFT_UserVerifyHash;
use Controller_staff;
use SWIFT;
use SWIFT_DataID;
use SWIFT_DataStore;
use SWIFT_Exception;
use SWIFT_Loader;
use SWIFT_Session;

/**
 * The Staff Lost Password Controller
 *
 * @property SWIFT_Loader|Controller_StaffLostPassword $Load
 * @author Werner Garcia
 */
class Controller_StaffLostPassword extends Controller_staff
{
    private $isSaas;
    private $internal_ut;

    /**
     * Constructor
     *
     * @author Werner Garcia
     * @throws SWIFT_Exception
     */
    public function __construct()
    {
        parent::__construct();

        $this->Load->Library('Staff:StaffPasswordPolicy', [], true, false, 'base');

        $this->Language->Load('staff');

        $this->isSaas = preg_match('/.+saas.+/i', SWIFT::Get('licensepackage'));
        $this->internal_ut = $this->Settings->Get('internal_ut') ?: false;

        if (!SWIFT_Session::Start($this->Interface)) {
            // Failed to load session
            if (!SWIFT_Session::InsertAndStart(''))
            {
                echo 'Failed to load session';
                exit;
            }
        }
    }

    /**
     * Render the lost password form
     *
     * @author Werner Garcia
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Index()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (isset($_POST['email'])) {
            $this->Template->Assign('_userEmail', htmlspecialchars($_POST['email']));
        } else {
            $this->Template->Assign('_userEmail', '');
        }

        $this->Template->Assign('_canCaptcha', false);
        if (!$this->internal_ut && ($this->isSaas || $this->Settings->Get('user_enablecaptcha') == '1')) {
            $_CaptchaObject = SWIFT_CaptchaManager::GetCaptchaObject();
            if ($_CaptchaObject instanceof SWIFT_CaptchaManager && $_CaptchaObject->GetIsClassLoaded()) {
                $_captchaHTML = $_CaptchaObject->GetHTML();
                if ($_captchaHTML) {
                    $isRecaptcha = false;
                    if(SWIFT::GetInstance()->Settings->Get('security_captchatype') == SWIFT_CaptchaManager::TYPE_RECAPTCHA) {
                        $isRecaptcha = true;
                    }
                    $this->Template->Assign('_isRecaptcha', $isRecaptcha);
                    $this->Template->Assign('_canCaptcha', true);
                    $this->Template->Assign('_captchaHTML', $_captchaHTML);
                }
            }
        }

        $this->UserInterface->ProcessDialogs();

        $this->Template->Render('resetpasswordform');

        return true;
    }

    /**
     * Lost Password submission processor
     *
     * @author Werner Garcia
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Submit()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        // Check for empty fields..
        if (!isset($_POST['email']) || trim($_POST['email']) == '') {
            $this->UserInterface->CheckFields('email');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            $this->Load->Index();

            return false;
        }

        // Email validation
        if (!IsEmailValid($_POST['email'])) {
            SWIFT::ErrorField('email');

            $this->UserInterface->Error($this->Language->Get('titleinvalidemail'), $this->Language->Get('msgfieldinvalid'));

            $this->Load->Index();

            return false;
        }

        // Check for captcha
        if (!$this->internal_ut && ($this->isSaas || $this->Settings->Get('user_enablecaptcha') == '1')) {
            $_CaptchaObject = SWIFT_CaptchaManager::GetCaptchaObject();
            if ($_CaptchaObject instanceof SWIFT_CaptchaManager && $_CaptchaObject->GetIsClassLoaded() && !$_CaptchaObject->IsValidCaptcha()) {
                SWIFT::ErrorField('captcha');

                $this->UserInterface->Error($this->Language->Get('errcaptchainvalidtitle'), $this->Language->Get('errcaptchainvalid'));

                $this->Load->Index();

                return false;
            }
        }

        // If we have a valid staff id, we attempt to load the staff object
        $_staffObjectLoaded = false;

        // Check for email address in database...
        $_staffContainer = SWIFT_Staff::RetrieveOnEmail($_POST['email']);
        if (_is_array($_staffContainer)) {
            $_SWIFT_StaffObject = new SWIFT_Staff(new SWIFT_DataStore($_staffContainer));
            if ($_SWIFT_StaffObject instanceof SWIFT_Staff && $_SWIFT_StaffObject->GetIsClassLoaded()) {
                $_staffObjectLoaded = true;
            }
        }

        if (!$_staffObjectLoaded) {
            SWIFT::ErrorField('email');

            $this->UserInterface->Error($this->Language->Get('titleinvalidemail'),  $this->Language->Get('lpinvalidemail'));

            $this->Load->Index();

            return false;
        }

        // By now we have a staff confirmed.. I guess its time to generate the lost password request..
        if (isset($_SWIFT_StaffObject)) {
            $_SWIFT_StaffObject->ForgotPassword();
        }

        $_SWIFT->Cache->Update('info_container', [
            [
                'title' => $this->Language->Get('lostpasswordtitle'),
                'message' => sprintf($this->Language->Get('lprequestsent'), htmlspecialchars($_POST['email'])),
            ],
        ]);

        $this->Load->Controller('Default', APP_CORE)->Load->Method('Index');

        return true;
    }

    /**
     * The Validation Routine
     *
     * @author Werner Garcia
     * @param string $_staffVerifyHashID The Staff Verification Hash ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Validate($_staffVerifyHashID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_staffVerifyHashLoaded = false;

        try {
            $_SWIFT_UserVerifyHashObject = new SWIFT_UserVerifyHash($_staffVerifyHashID);
            if ($_SWIFT_UserVerifyHashObject instanceof SWIFT_UserVerifyHash && $_SWIFT_UserVerifyHashObject->GetIsClassLoaded() && $_SWIFT_UserVerifyHashObject->GetProperty('hashtype') == SWIFT_UserVerifyHash::TYPE_FORGOTPASSWORD && !$_SWIFT_UserVerifyHashObject->HasExpired()) {
                $_staffVerifyHashLoaded = true;
            }
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {

        }

        // Invalid Hash?
        if (!$_staffVerifyHashLoaded) {
            $this->UserInterface->Error($this->Language->Get('titlefieldinvalid'), $this->Language->Get('notifycsrfhash'));

            $this->Load->Index();

            return false;
        }

        // By now this hash should be loaded and correct.. time to load up the staff.
        $_staffObjectLoaded = false;
        try {
            if (isset($_SWIFT_UserVerifyHashObject)) {
                $_SWIFT_StaffObject = new SWIFT_Staff(new SWIFT_DataID($_SWIFT_UserVerifyHashObject->GetProperty('userid')));
                if ($_SWIFT_StaffObject instanceof SWIFT_Staff && $_SWIFT_StaffObject->GetIsClassLoaded()) {
                    $_staffObjectLoaded = true;
                }
            }
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {

        }

        // Staff couldnt be loaded? die die
        if (!$_staffObjectLoaded) {
            $this->UserInterface->Error($this->Language->Get('titlecsrfhash'), sprintf('[%s] %s', strtoupper($this->Language->Get('staff')), $this->Language->Get('msgsearchfailed')));

            $this->Load->Index();

            return false;
        }

        $this->UserInterface->ProcessDialogs();

        // The staff now has been loaded, we need to display the password reset form.
        $this->Template->Assign('_userVerifyHashID', $_staffVerifyHashID);
        $this->Template->Render('lostpasswordresetform');

        return true;
    }

    /**
     * The Password Reset Submission Routine
     *
     * @author Werner Garcia
     * @param string $_staffVerifyHashID The Staff Verification Hash ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ResetSubmit($_staffVerifyHashID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_staffVerifyHashLoaded = false;

        try {
            $_SWIFT_UserVerifyHashObject = new SWIFT_UserVerifyHash($_staffVerifyHashID);
            if ($_SWIFT_UserVerifyHashObject instanceof SWIFT_UserVerifyHash && $_SWIFT_UserVerifyHashObject->GetIsClassLoaded() && $_SWIFT_UserVerifyHashObject->GetProperty('hashtype') == SWIFT_UserVerifyHash::TYPE_FORGOTPASSWORD && !$_SWIFT_UserVerifyHashObject->HasExpired()) {
                $_staffVerifyHashLoaded = true;
            }
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {

        }

        // By now this hash should be loaded and correct.. time to load up the staff.
        $_staffObjectLoaded = false;

        // Invalid Hash?
        if ($_staffVerifyHashLoaded) {
            try {
                if (isset($_SWIFT_UserVerifyHashObject)) {
                    $_SWIFT_StaffObject = new SWIFT_Staff(new SWIFT_DataID($_SWIFT_UserVerifyHashObject->GetProperty('userid')));
                    if ($_SWIFT_StaffObject instanceof SWIFT_Staff && $_SWIFT_StaffObject->GetIsClassLoaded()) {
                        $_staffObjectLoaded = true;
                    }
                }
            } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {

            }
        }

        // Staff couldnt be loaded? die die
        if (!$_staffObjectLoaded) {
            $this->UserInterface->Error($this->Language->Get('titlefieldinvalid'), $this->Language->Get('notifycsrfhash'));

            $this->Load->Index();

            return false;
        }

        // Check for incoming data...
        if (!isset($_POST['password']) || !isset($_POST['passwordrepeat']) || trim($_POST['password']) == '' || trim($_POST['passwordrepeat']) == '') {
            $this->UserInterface->CheckFields('password', 'passwordrepeat');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            $this->Load->Validate($_staffVerifyHashID);

            return false;
        }

        // Check for password match
        if ($_POST['password'] != $_POST['passwordrepeat']) {
            SWIFT::ErrorField('password', 'passwordrepeat');

            $this->UserInterface->Error($this->Language->Get('titlefieldinvalid'), $this->Language->Get('passworddontmatch'));

            $this->Load->Validate($_staffVerifyHashID);

            return false;

        }

        if (!empty($_POST['password']) && !$this->StaffPasswordPolicy->Check($_POST['password'])) {
            SWIFT::ErrorField('password', 'passwordrepeat');

            $this->UserInterface->Error($this->Language->Get('titlepwpolicy'), $this->Language->Get('msgpwpolicy') . '<br/>' . $this->StaffPasswordPolicy->GetPasswordPolicyString());

            $this->Load->Validate($_staffVerifyHashID);

            return false;

        }

        // The user now has been loaded, we need to reset the password..

        // First delete the verification object...
        if (isset($_SWIFT_UserVerifyHashObject)) {
            $_SWIFT_UserVerifyHashObject->Delete();
        }

        // Now reset the password
        if (isset($_SWIFT_StaffObject)) {
            $_SWIFT_StaffObject->ChangePassword($_POST['password']);
        }

        \SWIFT::GetInstance()->Cache->Update('info_container', [
            [
                'title' => $this->Language->Get('success'),
                'message' => $this->Language->Get('lpsuccess'),
            ],
        ]);

        $this->Load->Controller('Default', APP_CORE)->Load->Method('Index');

        return true;
    }
}
