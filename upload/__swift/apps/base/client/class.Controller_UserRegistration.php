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

namespace Base\Client;

use Base\Library\Captcha\SWIFT_CaptchaManager;
use Base\Library\CustomField\SWIFT_CustomFieldManager;
use Base\Library\CustomField\SWIFT_CustomFieldRenderer;
use Base\Library\CustomField\SWIFT_CustomFieldRendererClient;
use Base\Library\User\SWIFT_UserPasswordPolicy;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Models\CustomField\SWIFT_CustomFieldGroup;
use Base\Models\Language\SWIFT_Language;
use Base\Models\Template\SWIFT_TemplateGroup;
use Base\Models\User\SWIFT_User;
use Base\Models\User\SWIFT_UserConsent;
use Base\Models\User\SWIFT_UserEmail;
use Base\Models\User\SWIFT_UserOrganizationLink;
use Base\Models\User\SWIFT_UserVerifyHash;
use Base\Models\Widget\SWIFT_Widget;
use Controller_client;
use SWIFT;
use SWIFT_App;
use SWIFT_DataID;
use Parser\Models\EmailQueue\SWIFT_EmailQueue;
use SWIFT_Exception;
use SWIFT_Hook;

/**
 * The User Registration Controller
 *
 * @method Model(string $_modelName, array $_arguments, bool $_initiateInstance, $_customAppName, string $appName = '')
 * @method Method($v='', $_ticketID=0, $_listType=0, $_departmentID=0, $_ticketStatusID=0, $_ticketTypeID=0, $_ticketLimitOffset=0);
 * @method Controller($_libraryName, $_arguments = [], $_initiateInstance = false, $_customAppName = '', $_appName = '')
 * @method Library($_libraryName, $_arguments = [], $_initiateInstance = false, $_customAppName = '', $_appName = '')
 * @property Controller_UserRegistration $Load
 * @property SWIFT_CustomFieldRendererClient $CustomFieldRendererClient
 * @property SWIFT_CustomFieldManager $CustomFieldManager
 * @property SWIFT_UserPasswordPolicy $UserPasswordPolicy
 * @author Varun Shoor
 */
class Controller_UserRegistration extends Controller_client
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

        /*
         * BUG FIX - Parminder Singh
         *
         * SWIFT-2528: Widget particular pages shows up using direct URIs irrespective of whether the widget's visibility is restricted.
         *
         * Comments: None
         */
        if (!SWIFT_App::IsInstalled(APP_BASE) || !SWIFT_Widget::IsWidgetVisible(APP_BASE, 'register')) {
            $this->UserInterface->Error(true, $this->Language->Get('nopermission'));
            $this->Load->Controller('Default', 'Core')->Load->Index();

            log_error_and_exit();
        }

        $this->Load->Library('User:UserPasswordPolicy', [], true, false, 'base');
        $this->Load->Library('CustomField:CustomFieldRendererClient', [], true, false, 'base');
        $this->Load->Library('CustomField:CustomFieldManager', [], true, false, 'base');

        $this->Language->Load('users');

        $this->isSaas = preg_match('/.+saas.+/', strtolower(SWIFT::Get('licensepackage')));
        $this->internal_ut = $this->Settings->Get('internal_ut') ?: false;
    }

    /**
     * Register a new user
     *
     * @author Varun Shoor
     * @return bool "true" on Success
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Register()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (isset($_POST['fullname'])) {
            $this->Template->Assign('_userFullName', text_to_html_entities($_POST['fullname'], 1));
        } else {
            $this->Template->Assign('_userFullName', '');
        }

        if (isset($_POST['regemail'])) {
            $this->Template->Assign('_userEmail', htmlspecialchars($_POST['regemail']));
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

        $_registerExtendedForms = '';

        // Begin Hook: client_user_register
        unset($_hookCode);
        $_hookResult = null;
        ($_hookCode = SWIFT_Hook::Execute('client_user_register')) ? ($_hookResult = eval($_hookCode)) : false;
        if ($_hookResult !== null)
            return $_hookResult;
        // End Hook

        $this->Template->Assign('_registerExtendedForms', $_registerExtendedForms);

        // Custom Fields
        $this->CustomFieldRendererClient->Render(SWIFT_CustomFieldRenderer::TYPE_FIELDS, SWIFT_UserInterface::MODE_INSERT, array(SWIFT_CustomFieldGroup::GROUP_USER));

        $this->UserInterface->Header('register');

        $this->Template->Render('registerform');

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Register submission processor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RegisterSubmit()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }
        /**
         * BUG FIX : Mansi Wason <mansi.wason@kayako.com>
         *
         * SWIFT-5195 : Better handling of email address for a user account
         *
         * Comments : Prevent customers to register the email address same as the email queue.
         **/
        $_EmailQueueList = [];

        if (SWIFT_App::IsInstalled(APP_PARSER)) {
            $this->Load->Model('EmailQueue:EmailQueue', [], false, false, APP_PARSER);
            $_EmailQueueList = SWIFT_EmailQueue::RetrieveEmailofAllEmailQueues();
        }

        // Check for empty fields..
        if (!isset($_POST['fullname']) || !isset($_POST['regemail']) || !isset($_POST['regpassword']) || !isset($_POST['passwordrepeat']) || trim($_POST['fullname']) == '' || trim($_POST['regemail']) == '' || trim($_POST['regpassword']) == '' || trim($_POST['passwordrepeat']) == '') {
            $this->UserInterface->CheckFields('fullname', 'regemail', 'regpassword', 'passwordrepeat');

            $this->UserInterface->Error(true, $this->Language->Get('requiredfieldempty'));

            $this->Load->Register();

            return false;

            // Check for password match
        }

        if ($_POST['regpassword'] != $_POST['passwordrepeat']) {
            SWIFT::ErrorField('regpassword', 'passwordrepeat');

            $this->UserInterface->Error(true, $this->Language->Get('regpwnomatch'));

            $this->Load->Register();

            return false;

            // Email validation
        }

        if (!empty($_POST['regpassword']) && !$this->UserPasswordPolicy->Check($_POST['regpassword'])) {
            SWIFT::ErrorField('regpassword', 'passwordrepeat');

            $this->UserInterface->Error($this->Language->Get('regtitlepwpolicy'), $this->Language->Get('regmsgpwpolicy') . ' ' . $this->UserPasswordPolicy->GetPasswordPolicyString());

            $this->Load->Register();

            return false;

        }

        if (!isset($_POST['registrationconsent'])) {

            $this->UserInterface->Error(true, $this->Language->Get('regpolicyareement'));

            $this->Load->Register();

            return false;

        }

        if (in_array($_POST['regpassword'], $_EmailQueueList)) {

            $this->UserInterface->Error(true, $this->Language->Get('reginvalidemailaddress'));

            $this->Load->Register();

            return false;

        }

        if (!IsEmailValid($_POST['regemail'])) {
            SWIFT::ErrorField('regemail');

            $this->UserInterface->Error(true, $this->Language->Get('reginvalidemail'));

            $this->Load->Register();

            return false;

            // Existing user?
        }

        if (SWIFT_UserEmail::CheckEmailRecordExists(array(mb_strtolower($_POST['regemail'])))) {
            SWIFT::ErrorField('regemail');

            $this->UserInterface->Error(true, $this->Language->Get('regemailregistered'));

            $this->Load->Register();

            return false;

        }

        // Custom Field Check
        $_customFieldCheckResult = $this->CustomFieldManager->Check(SWIFT_CustomFieldManager::MODE_POST, SWIFT_UserInterface::MODE_INSERT, array(SWIFT_CustomFieldGroup::GROUP_USER), SWIFT_CustomFieldManager::CHECKMODE_CLIENT);
        if ($_customFieldCheckResult[0] == false) {
            call_user_func_array(array('SWIFT', 'ErrorField'), $_customFieldCheckResult[1]);

            $this->UserInterface->Error(true, $this->Language->Get('requiredfieldempty'));

            $this->Load->Register();

            return false;
        }

        // Check for captcha
        if (!$this->internal_ut && ($this->isSaas || $this->Settings->Get('user_enablecaptcha') == '1')) {
            $_CaptchaObject = SWIFT_CaptchaManager::GetCaptchaObject();
            if ($_CaptchaObject instanceof SWIFT_CaptchaManager && $_CaptchaObject->GetIsClassLoaded() && !$_CaptchaObject->IsValidCaptcha()) {
                SWIFT::ErrorField('captcha');

                $this->UserInterface->Error(true, $this->Language->Get('errcaptchainvalid'));

                $this->Load->Register();

                return false;
            }
        }

        // Begin Hook: client_user_registerchecks
        unset($_hookCode);
        $_hookResult = null;
        ($_hookCode = SWIFT_Hook::Execute('client_user_registerchecks')) ? ($_hookResult = eval($_hookCode)) : false;
        if ($_hookResult !== null)
            return $_hookResult;
        // End Hook

        // Everything ok! good to go..
        $_validationRequired = false;
        $_isValidated = true;
        if ($this->Settings->Get('user_enableemailverification') == '1') {
            $_validationRequired = true;
            $_isValidated = false;
        }

        if (!$_SWIFT->TemplateGroup || !$_SWIFT->TemplateGroup instanceof SWIFT_TemplateGroup || !$_SWIFT->TemplateGroup->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        /**
         * BUG FIX - Verem Dugeri <verem.dugeri@crossover.com>
         *
         * KAYAKO-3095 - HTML Tag injection
         *
         * Comments: Encode all HTML characters
         */

        $_fullName = text_to_html_entities($_POST['fullname'], 1);

        /**
         * BUG FIX - Ravi Sharma <ravi.sharma@kayako.com>
         *
         * SWIFT-2863 Registration email is not sent if "User Email Verification" option is disabled.
         */
        $_SWIFT_UserObject = SWIFT_User::Create($_SWIFT->TemplateGroup->GetRegisteredUserGroupID(), 0,
            SWIFT_User::SALUTATION_NONE, $_fullName, '', '', true, 0, array($_POST['regemail']), $_POST['regpassword'],
            SWIFT_Language::GetDefaultLanguageId(), '', true, 0, 0, 0, true, $_isValidated, true);
        if (!$_SWIFT_UserObject instanceof SWIFT_User || !$_SWIFT_UserObject->GetIsClassLoaded()) {
            $this->UserInterface->Error(true, $this->Language->Get('creationfailed'));

            $this->Load->Register();

            return false;
        }

        $this->createOrganizationLinksForUser($_SWIFT_UserObject);

        // Save User Registration Consent
        SWIFT_UserConsent::Create(
            $_SWIFT_UserObject->GetProperty('userid'),
            SWIFT_UserConsent::CONSENT_REGISTRATION,
            SWIFT_UserConsent::CHANNEL_WEB,
            SWIFT_UserConsent::SOURCE_NEW_REGISTRATION, $this->Router->GetCurrentURL());

        // Update Custom Field Values
        $this->CustomFieldManager->Update(SWIFT_CustomFieldManager::MODE_POST, SWIFT_UserInterface::MODE_INSERT, array(SWIFT_CustomFieldGroup::GROUP_USER), SWIFT_CustomFieldManager::CHECKMODE_CLIENT, $_SWIFT_UserObject->GetUserID());

        // Begin Hook: client_user_registersubmit
        unset($_hookCode);
        $_hookResult = null;
        ($_hookCode = SWIFT_Hook::Execute('client_user_registersubmit')) ? ($_hookResult = eval($_hookCode)) : false;
        if ($_hookResult !== null)
            return $_hookResult;
        // End Hook

        // So we now have a registered user.. if hes validated, display success message.. else display the validation pending message
        if ($_isValidated) {
            return $this->_RegisterSuccess($_SWIFT_UserObject);

            // Create a verification hash and email it to user..
        } else {
            $_SWIFT_UserObject->CreateVerifyAttempt();
        }

        $this->Template->Assign('_userFullName', text_to_html_entities($_POST['fullname'], 1));

        $this->Template->Assign('_userEmail', htmlspecialchars($_POST['regemail']));

        $this->UserInterface->Header('register');

        $this->Template->Render('registervalidationpending');

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Display the registration successful confirmation
     *
     * @author Varun Shoor
     * @param SWIFT_User $_SWIFT_UserObject The User Object
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    protected function _RegisterSuccess(SWIFT_User $_SWIFT_UserObject)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (!$_SWIFT_UserObject instanceof SWIFT_User || !$_SWIFT_UserObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $this->Template->Assign('_userFullName', text_to_html_entities($_SWIFT_UserObject->GetProperty('fullname')));

        $this->Template->Assign('_userEmail', htmlspecialchars(implode(', ', SWIFT_UserEmail::RetrieveList($_SWIFT_UserObject->GetUserID()))));

        $this->UserInterface->Header('register');

        $this->Template->Render('registersuccess');

        $this->UserInterface->Footer();

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
        }

        $_userVerifyHashLoaded = false;
        $_SWIFT_UserVerifyHashObject = null;

        try {
            $_SWIFT_UserVerifyHashObject = new SWIFT_UserVerifyHash($_userVerifyHashID);
            if ($_SWIFT_UserVerifyHashObject instanceof SWIFT_UserVerifyHash && $_SWIFT_UserVerifyHashObject->GetIsClassLoaded() && $_SWIFT_UserVerifyHashObject->GetProperty('hashtype') == SWIFT_UserVerifyHash::TYPE_USER && !$_SWIFT_UserVerifyHashObject->HasExpired()) {
                $_userVerifyHashLoaded = true;
            }
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {

        }

        // Invalid Hash?
        if (!$_userVerifyHashLoaded) {
            SWIFT::Error(true, $this->Language->Get('invalidverifyhash'));

            $this->Load->Register();

            return false;
        }

        // By now this hash should be loaded and correct.. time to load up the user.
        $_userVerified = false;
        $_SWIFT_UserObject = null;

        try {
            $_SWIFT_UserObject = new SWIFT_User(new SWIFT_DataID($_SWIFT_UserVerifyHashObject->GetProperty('userid')));
            if ($_SWIFT_UserObject instanceof SWIFT_User && $_SWIFT_UserObject->GetIsClassLoaded()) {
                $_SWIFT_UserObject->MarkAsVerified();
                $_userVerified = true;
            }
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {

        }

        // User couldnt be loaded? die die
        if (!$_userVerified) {
            SWIFT::Error(true, $this->Language->Get('invalidverifyhash'));

            $this->Load->Register();

            return false;
        }

        // The user now has been verified, so we just need to display the confirmation.
        $this->_RegisterSuccess($_SWIFT_UserObject);

        return true;
    }

    /**
     * Creates organizations for the given user
     *
     * @param SWIFT_User $_SWIFT_UserObject
     * @return bool
     * @throws SWIFT_Exception
     * @author Werner Garcia
     */
    protected function createOrganizationLinksForUser(SWIFT_User $_SWIFT_UserObject)
    {
        if (!$_SWIFT_UserObject->GetOrganization() || !$_SWIFT_UserObject->GetOrganization()->GetUserOrganizationID()) {
            return false;
        }

        $uid = $_SWIFT_UserObject->GetOrganization()->GetUserOrganizationID();
        if (!SWIFT_UserOrganizationLink::LinkExists($uid, $_SWIFT_UserObject->GetUserID())) {
            SWIFT_UserOrganizationLink::Create($_SWIFT_UserObject, $uid);
        }

        return true;
    }
}
