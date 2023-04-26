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

namespace Base\Client;

use Base\Library\Captcha\SWIFT_CaptchaManager;
use Base\Models\User\SWIFT_User;
use Base\Models\User\SWIFT_UserEmail;
use Base\Models\User\SWIFT_UserVerifyHash;
use Controller_client;
use SWIFT;
use SWIFT_DataID;
use SWIFT_Exception;

/**
 * The User Lost Password Controller
 *
 * @author Varun Shoor
 * @method Controller($_libraryName, $_arguments = [], $_initiateInstance = false, $_customAppName = '', $_appName = '')
 * @property Controller_UserLostPassword|\SWIFT_Loader $Load
 * @property \Base\Library\User\SWIFT_UserPasswordPolicy $UserPasswordPolicy
 */
class Controller_UserLostPassword extends Controller_client
{
    private $isSaas;
    private $internal_ut;

    /**
     * Constructor
     *
     * @author Varun Shoor
     */
    public function __construct()
    {
        parent::__construct();

        $this->Language->Load('users');

        $this->Load->Library('User:UserPasswordPolicy', [], true, false, 'base');

        $this->isSaas = preg_match('/.+saas.+/', strtolower(SWIFT::Get('licensepackage')));
        $this->internal_ut = $this->Settings->Get('internal_ut') ?: false;
    }

    /**
     * Render the lost password form
     *
     * @author Varun Shoor
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

        $this->UserInterface->Header();

        $this->Template->Render('lostpasswordform');

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Lost Password submission processor
     *
     * @author Varun Shoor
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

            $this->UserInterface->Error(true, $this->Language->Get('requiredfieldempty'));

            $this->Load->Index();

            return false;

            // Email validation
        }

        if (!IsEmailValid($_POST['email'])) {
            SWIFT::ErrorField('email');

            $this->UserInterface->Error(true, $this->Language->Get('reginvalidemail'));

            $this->Load->Index();

            return false;
        }

        // Check for captcha
        if (!$this->internal_ut && ($this->isSaas || $this->Settings->Get('user_enablecaptcha') == '1')) {
            $_CaptchaObject = SWIFT_CaptchaManager::GetCaptchaObject();
            if ($_CaptchaObject instanceof SWIFT_CaptchaManager && $_CaptchaObject->GetIsClassLoaded() && !$_CaptchaObject->IsValidCaptcha()) {
                SWIFT::ErrorField('captcha');

                $this->UserInterface->Error(true, $this->Language->Get('errcaptchainvalid'));

                $this->Load->Index();

                return false;
            }
        }

        // Check for email address in database...
        $_userID = SWIFT_UserEmail::RetrieveUserIDOnUserEmail($_POST['email']);
        if (!$_userID) {
            SWIFT::ErrorField('email');

            $this->UserInterface->Error(true, $this->Language->Get('lpinvalidemail'));

            $this->Load->Index();

            return false;
        }

        // If we have a valid user id, we attempt to load the user object
        $_userObjectLoaded = false;
        $_SWIFT_UserObject = null;

        try {
            $_SWIFT_UserObject = new SWIFT_User(new SWIFT_DataID($_userID));
            if ($_SWIFT_UserObject instanceof SWIFT_User && $_SWIFT_UserObject->GetIsClassLoaded()) {
                $_userObjectLoaded = true;
            }

        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {

        }

        if (!$_userObjectLoaded) {
            SWIFT::ErrorField('email');

            $this->UserInterface->Error(true, $this->Language->Get('lpinvalidemail'));

            $this->Load->Index();

            return false;
        }

        // By now we have a user confirmed.. I guess its time to generate the lost password request..
        $_SWIFT_UserObject->ForgotPassword();

        SWIFT::Info(true, sprintf($this->Language->Get('lprequestsent'), htmlspecialchars($_POST['email'])));

        $this->Load->Controller('Default', APP_CORE)->Load->Method('Index');

        return true;
    }

    /**
     * The Validation Routine
     *
     * @author Varun Shoor
     * @param string $_userVerifyHashID The User Verification Hash ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Validate($_userVerifyHashID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_userVerifyHashLoaded = false;
        $_SWIFT_UserVerifyHashObject = null;

        try {
            $_SWIFT_UserVerifyHashObject = new SWIFT_UserVerifyHash($_userVerifyHashID);
            if ($_SWIFT_UserVerifyHashObject instanceof SWIFT_UserVerifyHash && $_SWIFT_UserVerifyHashObject->GetIsClassLoaded() && $_SWIFT_UserVerifyHashObject->GetProperty('hashtype') == SWIFT_UserVerifyHash::TYPE_FORGOTPASSWORD && !$_SWIFT_UserVerifyHashObject->HasExpired()) {
                $_userVerifyHashLoaded = true;
            }
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {

        }

        // Invalid Hash?
        if (!$_userVerifyHashLoaded) {
            SWIFT::Error(true, $this->Language->Get('invalidverifyhash'));

            $this->Load->Index();

            return false;
        }

        // By now this hash should be loaded and correct.. time to load up the user.
        $_userObjectLoaded = false;
        try {
            $_SWIFT_UserObject = new SWIFT_User(new SWIFT_DataID($_SWIFT_UserVerifyHashObject->GetProperty('userid')));
            if ($_SWIFT_UserObject instanceof SWIFT_User && $_SWIFT_UserObject->GetIsClassLoaded()) {
                $_userObjectLoaded = true;
            }
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {

        }

        // User couldnt be loaded? die die
        if (!$_userObjectLoaded) {
            SWIFT::Error(true, $this->Language->Get('invalidverifyhash'));

            $this->Load->Index();

            return false;
        }

        // The user now has been loaded, we need to display the password reset form.
        $this->Template->Assign('_userVerifyHashID', $_userVerifyHashID);

        $this->UserInterface->Header();

        $this->Template->Render('lostpasswordresetform');

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * The Password Reset Submission Routine
     *
     * @author Varun Shoor
     * @param string $_userVerifyHashID The User Verification Hash ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ResetSubmit($_userVerifyHashID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_userVerifyHashLoaded = false;
        $_SWIFT_UserVerifyHashObject = null;

        try {
            $_SWIFT_UserVerifyHashObject = new SWIFT_UserVerifyHash($_userVerifyHashID);
            if ($_SWIFT_UserVerifyHashObject instanceof SWIFT_UserVerifyHash && $_SWIFT_UserVerifyHashObject->GetIsClassLoaded() && $_SWIFT_UserVerifyHashObject->GetProperty('hashtype') == SWIFT_UserVerifyHash::TYPE_FORGOTPASSWORD && !$_SWIFT_UserVerifyHashObject->HasExpired()) {
                $_userVerifyHashLoaded = true;
            }
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {

        }

        // Invalid Hash?
        if (!$_userVerifyHashLoaded) {
            SWIFT::Error(true, $this->Language->Get('invalidverifyhash'));

            $this->Load->Index();

            return false;
        }

        // By now this hash should be loaded and correct.. time to load up the user.
        $_userObjectLoaded = false;
        $_SWIFT_UserObject = null;

        try {
            $_SWIFT_UserObject = new SWIFT_User(new SWIFT_DataID($_SWIFT_UserVerifyHashObject->GetProperty('userid')));
            if ($_SWIFT_UserObject instanceof SWIFT_User && $_SWIFT_UserObject->GetIsClassLoaded()) {
                $_userObjectLoaded = true;
            }
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {

        }

        // User couldnt be loaded? die die
        if (!$_userObjectLoaded) {
            SWIFT::Error(true, $this->Language->Get('invalidverifyhash'));

            $this->Load->Index();

            return false;
        }

        // Check for incoming data...
        if (!isset($_POST['password']) || !isset($_POST['passwordrepeat']) || trim($_POST['password']) == '' || trim($_POST['passwordrepeat']) == '') {
            $this->UserInterface->CheckFields('password', 'passwordrepeat');

            $this->UserInterface->Error(true, $this->Language->Get('requiredfieldempty'));

            $this->Load->Validate($_userVerifyHashID);

            return false;

            // Check for password match
        } else if ($_POST['password'] != $_POST['passwordrepeat']) {
            SWIFT::ErrorField('password', 'passwordrepeat');

            $this->UserInterface->Error(true, $this->Language->Get('regpwnomatch'));

            $this->Load->Validate($_userVerifyHashID);

            return false;

        } else if (!empty($_POST['password']) && !$this->UserPasswordPolicy->Check($_POST['password'])) {
            SWIFT::ErrorField('password', 'passwordrepeat');

            $this->UserInterface->Error($this->Language->Get('regtitlepwpolicy'), $this->Language->Get('regmsgpwpolicy') . ' ' . $this->UserPasswordPolicy->GetPasswordPolicyString());

            $this->Load->Validate($_userVerifyHashID);

            return false;

        }

        // The user now has been loaded, we need to reset the password..

        // First delete the verification object...
        $_SWIFT_UserVerifyHashObject->Delete();

        // Now reset the password
        $_SWIFT_UserObject->ChangePassword($_POST['password']);

        // Show with confirmation..
        SWIFT::Info(true, $this->Language->Get('lpsuccess'));

        /*
         * BUG FIX - Varun Shoor
         *
         * SWIFT-710 Once the "Lost Password" is filled up by the clients, the "Lost Password" interface occurs again below the confirmation screen
         *
         */
        $this->Load->Controller('Default', 'Base')->Load->Index();

        return true;
    }
}
