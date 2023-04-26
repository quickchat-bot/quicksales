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

namespace Base\Client;

use Base\Library\ProfileImage\SWIFT_ProfileImage;
use Base\Models\Template\SWIFT_Template;
use Base\Models\User\SWIFT_User;
use Base\Models\User\SWIFT_UserConsent;
use Base\Models\User\SWIFT_UserLoginLog;
use Base\Models\User\SWIFT_UserProfileImage;
use Controller_client;
use SWIFT;
use SWIFT_App;
use SWIFT_DataID;
use SWIFT_Exception;
use SWIFT_Hook;
use SWIFT_Session;

/**
 * The User Controller
 *
 * @author Varun Shoor
 */
class Controller_User extends Controller_client
{
    /**
     * Constructor
     *
     * @author Varun Shoor
     */
    public function __construct()
    {
        parent::__construct();

        $this->Language->Load('users');
    }

    /**
     * Login the user
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Login()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        /*
         * BUG FIX - Ravi Sharma <ravi.sharma@kayako.com>
         *
         * SWIFT-3743 A Cross Site Request Forgery error on accessing /?/Base/User/Login if user is already logged in.
         */
        if (($_SWIFT->Session->IsLoggedIn() && $_SWIFT->User instanceof SWIFT_User && $_SWIFT->User->GetIsClassLoaded())) {
            $this->Load->Controller('Default')->Load->Index();

            return false;
        }

        if (isset($_POST['scemail']) && $_POST['scemail'] === $this->Language->Get('loginenteremail')) {
            $_POST['scemail'] = '';
        }

        /**
         * BUGFIX - Verem Dugeri <verem.dugeri@crossover.com>
         *
         * KAYAKO-4896 - Remove vulnerable tags from user input.
         *
         * Comments  - None.
         */

        $_scEmail = isset($_POST['scemail']) ? strip_javascript($_POST['scemail']) : null;

        if (isset($_scEmail) && $_scEmail != $this->Language->Get('loginenteremail')) {
            $this->Template->Assign('_userLoginEmail', $_scEmail);
        }

        /**
         * BUG FIX - Ravi Sharma <ravi.sharma@kayako.com>
         *
         * SWIFT-4248 Security issue (medium)
         *
         * Comments: System should automate the login if user is using remember me.
         */
        $_cookieLoginEmail = $this->Cookie->Get('scloginemail', true);
        $_cookieLoginPassword = $this->Cookie->Get('scloginpassword', true);
        $_cookieHashCheck = $this->Cookie->Get('schashcheck');
        $_skipCSRF = false;

        if (!empty($_cookieLoginEmail) && !empty($_cookieLoginPassword) && $_cookieHashCheck == sha1(SWIFT::Get('InstallationHash')) && empty($_scEmail)) {
            $_scEmail = $_POST['scemail'] = $_cookieLoginEmail;
            $_POST['scpassword'] = $_cookieLoginPassword;
            $_POST['rememberme'] = true;
            $_skipCSRF = true;
        }

        /*
         * BUG FIX - Ravi Sharma
         *
         * SWIFT-3445 CSRF in Login Functionality
         *
         * Comment: None
         */
        // BEGIN CSRF HASH CHECK
        if (!$_skipCSRF && !in_array('header (Default)', SWIFT_Template::GetUpgradeRevertList()) && (!isset($_POST['_csrfhash']) || !SWIFT_Session::CheckCSRFHash($_POST['_csrfhash']))) {
            $this->UserInterface->Error(true, $this->Language->Get('msgcsrfhash'));

            $this->Load->Controller('Default')->Load->Index();

            return false;
        }

        // END CSRF HASH CHECK

        // Check for sanity of data
        if (empty($_scEmail) || $_scEmail != $_POST['scemail'] || !isset($_POST['scpassword']) || empty($_POST['scpassword'])) {
            $this->UserInterface->Error(true, $this->Language->Get('invaliduser'));

            $this->Load->Controller('Default')->Load->Index();

            return false;
        }

        /**
         * Feature FIX : Saloni Dhall <saloni.dhall@kayako.com>
         *
         * SWIFT-4561 : Login failure lockout attempts lockout in the support center for end-users
         *
         */
        // Before Login, check to see if we can really log in this user..
        $_loginLogCheck = SWIFT_UserLoginLog::CanUserLogin($_scEmail);
        if (!$_loginLogCheck[0]) {
            if (SWIFT_UserLoginLog::GetLoginTimeline() < 60) {
                $_errorString = sprintf($this->Language->Get('loginlogerrorsecs'),
                    ceil(SWIFT_UserLoginLog::GetLoginTimeline()),
                    $_loginLogCheck[1],
                    SWIFT_UserLoginLog::GetLoginRetries());
            } else {
                $_errorString = sprintf($this->Language->Get('loginlogerror'),
                    ceil((float) SWIFT_UserLoginLog::GetLoginTimeline() / 60.0),
                    $_loginLogCheck[1],
                    SWIFT_UserLoginLog::GetLoginRetries());
            }

            $this->UserInterface->Error(true, $_errorString);

            $this->Load->Controller('Default')->Load->Index();

            return false;
        }

        /**
         * BUGFIX - Verem Dugeri <verem.dugeri@crossover.com>
         *
         * KAYAKO-4896 - Strip all tags from user input
         *
         * Comments - None
         */

        $_authenticationResult = SWIFT_User::Authenticate($_scEmail, $_POST['scpassword'], true);

        // Authentication failed?
        if (!$_authenticationResult) {
            $this->Cookie->Delete('scloginemail');
            $this->Cookie->Delete('scloginpassword');

            $_errorString = SWIFT::Get('errorstring');

            if ($_SWIFT->Settings->Get('security_scloginlocked') == '1' && strcmp($_errorString, $_SWIFT->Language->Get('invaliduser')) == 0) {

                // Log the Login Attempt First
                SWIFT_UserLoginLog::Failure(array($_scEmail), SWIFT_UserLoginLog::INTERFACE_CLIENT);

                $_loginLogCheck = SWIFT_UserLoginLog::CanUserLogin($_scEmail);
                if ($_loginLogCheck[0] && $_loginLogCheck[1] > 0) {
                    $_errorStringText = $this->Language->Get('loginlogwarning');

                    $_errorString = sprintf($_errorStringText, $_loginLogCheck[1], SWIFT_UserLoginLog::GetLoginRetries());
                } else {
                    if (SWIFT_UserLoginLog::GetLoginTimeline()  < 60) {
                        $_errorString = sprintf($this->Language->Get('loginlogerrorsecs'),
                            ceil(SWIFT_UserLoginLog::GetLoginTimeline()),
                            $_loginLogCheck[1],
                            SWIFT_UserLoginLog::GetLoginRetries());
                    } else {
                        $_errorString = sprintf($this->Language->Get('loginlogerror'),
                            ceil((float) SWIFT_UserLoginLog::GetLoginTimeline() / 60.0),
                            $_loginLogCheck[1],
                            SWIFT_UserLoginLog::GetLoginRetries());
                    }
                }
            }

            $this->UserInterface->Error(true, $_errorString);

            $this->Load->Controller('Default')->Load->Index();

            return false;
        }

        // Check for sanity of loaded object
        if (!$_SWIFT->User instanceof SWIFT_User || !$_SWIFT->User->GetIsClassLoaded()) {
            $this->UserInterface->Error(true, $this->Language->Get('invaliduser'));

            $this->Load->Controller('Default')->Load->Index();

            return false;
        }

        // Check for template group restriction..
        if ($_SWIFT->TemplateGroup->GetProperty('restrictgroups') == '1' && $_SWIFT->TemplateGroup->GetRegisteredUserGroupID() != $_SWIFT->User->GetProperty('usergroupid')) {
            /*
             * BUG FIX - Ravi Sharma
             *
             * SWIFT-3635 User is redirected to an invalid URL in case 'Restrict Users Group' setting is enabled under Template Group setting
             *
             * Comment: Due to redirecting twice.
             */
            $_SWIFT->User = false;

            $this->UserInterface->Error(true, $this->Language->Get('invalidusertgroupres'));

            $this->Load->Controller('Default')->Load->Index();

            return false;
        }

        // So by now we have the user object, we need to update the session record..
        $_SWIFT->Session->Update($_SWIFT->User->GetUserID());

        if (SWIFT_App::IsInstalled(APP_LIVECHAT)) {
            $this->Cookie->Parse('livechatdetails');
            $this->Cookie->AddVariable('livechatdetails', 'fullname', $_SWIFT->User->GetFullName());
            $this->Cookie->AddVariable('livechatdetails', 'email', $_scEmail);
            $this->Cookie->Rebuild('livechatdetails', true);
        }

        // Log the Login Attempt First
        SWIFT_UserLoginLog::Success($_SWIFT->User, SWIFT_UserLoginLog::INTERFACE_CLIENT);

        // Did the user check remember me?
        if (isset($_POST['rememberme']) && $_POST['rememberme'] == 1) {
            $this->Cookie->Set('schashcheck', sha1(SWIFT::Get('InstallationHash')), true, false);
            $this->Cookie->Set('scloginemail', $_scEmail, true, true);
            $this->Cookie->Set('scloginpassword', $_POST['scpassword'], true, true);
        }

        $_cookieConsent = $this->Cookie->Get('cookieconsent');
        $_cookieConsentUrl = $this->Cookie->Get('cookieconsenturl');
        if ((!empty($_cookieConsent) && $_cookieConsent == 'dismiss') && !empty($_cookieConsentUrl)) {
            if ($_userConsent = SWIFT_UserConsent::RetrieveConsent($_SWIFT->User->GetUserID(), SWIFT_UserConsent::CONSENT_COOKIE)) {
                (new SWIFT_UserConsent($_userConsent[SWIFT_UserConsent::PRIMARY_KEY]))
                    ->update(SWIFT_UserConsent::CHANNEL_WEB, SWIFT_UserConsent::SOURCE_POP_UP, $_cookieConsentUrl);
            } else {
                SWIFT_UserConsent::Create(
                    $_SWIFT->User->GetUserID(),
                    SWIFT_UserConsent::CONSENT_COOKIE,
                    SWIFT_UserConsent::CHANNEL_WEB,
                    SWIFT_UserConsent::SOURCE_POP_UP,
                    $_cookieConsentUrl
                );
            }
        }

        /**
         * Begin Hook: client_userlogin
         */

        unset($_hookCode);
        ($_hookCode = SWIFT_Hook::Execute('client_userlogin')) ? eval($_hookCode) : false;

        /**
         * End Hook
         */

        $_templateGroupCache = $this->Cache->Get('templategroupcache');
        $_templateGroupString = '';

        // If we dont have a custom template group to load and theres one set in cookie.. attempt to use it..
        if ($this->Cookie->GetVariable('client', 'templategroupid')) {
            $_templateGroupID = (int)($this->Cookie->GetVariable('client', 'templategroupid'));
            $_templateGroupString = '/' . $_templateGroupCache[$_templateGroupID]['title'];
        }

        if ($_SWIFT->TemplateGroup->GetRegisteredUserGroupID() != $_SWIFT->User->GetProperty('usergroupid')) {
            foreach ($_templateGroupCache as $_key => $_val) {
                if ($_val["regusergroupid"] == $_SWIFT->User->GetProperty('usergroupid')) {
                    $_templateGroupString = '/' . $_val["title"];
                    break;
                }
            }
        }

        $_redirectAction = false;
        if (isset($_POST['_redirectAction']) && !empty($_POST['_redirectAction'])) {
            $_redirectAction = SWIFT::Get('basename') . $_templateGroupString . $_POST['_redirectAction'];
        }

        if (!empty($_redirectAction) && !strstr($_redirectAction, '/UserLostPassword/') && !strstr($_redirectAction, '/UserRegistration/')
            && !strstr($_redirectAction, '/User/Login') && !strstr($_redirectAction, '/Backend/Order') && !strstr($_redirectAction, '/Backend/Trial')) {
            header("location: " . $_redirectAction);

            return true;
        }

        /**
         * BUG FIX - Ravi Sharma <ravi.sharma@kayako.com>
         *
         * SWIFT-4465 Language selected under user's profile should be indicated at the support center language drop down option after login.
         *
         * Comments: Set the language cookies if user selection is not default.
         */
        if ($_SWIFT->User->GetProperty('languageid') != '0') {
            $_SWIFT->Cookie->Parse('client');
            $_SWIFT->Cookie->AddVariable('client', 'languageid', $_SWIFT->User->GetProperty('languageid'));
            $_SWIFT->Cookie->Rebuild('client', true);
        }

        if ($_SWIFT->User->GetProperty('profileprompt') == '0') {
            // Redirect..
            header("location: " . SWIFT::Get('basename') . $_templateGroupString . '/Base/UserAccount/Profile/1');
        } else {
            // Redirect..
            header("location: " . SWIFT::Get('basename') . $_templateGroupString);
        }

        return true;
    }

    /**
     * @author Arotimi Busayo
     */
    public function UpdateProcessingConsentAJAX()
    {
        $_SWIFT = SWIFT::GetInstance();
        $_processingConsentURL = $this->Cookie->Get('prconsenturl');
        if ($_SWIFT->User instanceof SWIFT_User && $_SWIFT->User->GetIsClassLoaded() && !empty($_processingConsentURL)) {
            SWIFT_UserConsent::Create(
                $_SWIFT->User->GetUserID(),
                SWIFT_UserConsent::CONSENT_REGISTRATION,
                SWIFT_UserConsent::CHANNEL_WEB,
                SWIFT_UserConsent::SOURCE_NEW_REGISTRATION,
                $_processingConsentURL
            );
        }
    }

    /**
     * @author Arotimi Busayo
     */
    public function UpdateCookieConsentAJAX()
    {
        $_SWIFT = SWIFT::GetInstance();
        if ($_SWIFT->User instanceof SWIFT_User && $_SWIFT->User->GetIsClassLoaded()) {
            $_cookieConsent = $this->Cookie->Get('cookieconsent');
            $_cookieConsentUrl = $this->Cookie->Get('cookieconsenturl');
            if ((!empty($_cookieConsent) && $_cookieConsent == 'dismiss') && !empty($_cookieConsentUrl)) {
                if ($_userConsent = SWIFT_UserConsent::RetrieveConsent($_SWIFT->User->GetUserID(), SWIFT_UserConsent::CONSENT_COOKIE)) {
                    (new SWIFT_UserConsent($_userConsent[SWIFT_UserConsent::PRIMARY_KEY]))
                        ->update(SWIFT_UserConsent::CHANNEL_WEB, SWIFT_UserConsent::SOURCE_POP_UP, $_cookieConsentUrl);
                } else {
                    SWIFT_UserConsent::Create(
                        $_SWIFT->User->GetUserID(),
                        SWIFT_UserConsent::CONSENT_COOKIE,
                        SWIFT_UserConsent::CHANNEL_WEB,
                        SWIFT_UserConsent::SOURCE_POP_UP,
                        $_cookieConsentUrl
                    );
                }
            }
        }
    }

    /**
     * Log the user out
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Logout()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        // At the time of logout request, the entry must gets deleted from swsessions table.
        if ($_SWIFT->User instanceof SWIFT_User && $_SWIFT->User->GetIsClassLoaded()) {
            $_userID = $_SWIFT->User->GetUserID();
        } else {
            $_userID = 0;
        }

        $_SWIFT->Session->Update($_userID);

        // Destroying cookies...
        $this->Cookie->Delete('scloginemail');
        $this->Cookie->Delete('scloginpassword');

        if (SWIFT_App::IsInstalled(APP_LIVECHAT)) {
            $this->Cookie->Delete('livechatdetails');
        }

        // Redirect..
        header("location: " . SWIFT::Get('basename'));

        return true;
    }

    /**
     * Retrieve the Profile Image
     *
     * @author Varun Shoor
     * @param int $_userID The User ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetProfileImage($_userID = 0)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } elseif (empty($_userID) || !is_numeric($_userID)) {
            return false;
        }

        /**
         * BUG FIX - Ravi Sharma <ravi.sharma@kayako.com>
         *
         * SWIFT-4364 Security Issue.
         *
         * Comments: Suppressing the exceptions with try statement.
         */
        try {
            $_SWIFT_UserObject = new SWIFT_User(new SWIFT_DataID($_userID));
            $_SWIFT_UserProfileImageObject = SWIFT_UserProfileImage::RetrieveOnUser($_SWIFT_UserObject->GetUserID());
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            return false;
        }

        if (!$_SWIFT_UserProfileImageObject instanceof SWIFT_UserProfileImage || !$_SWIFT_UserProfileImageObject->GetIsClassLoaded()) {
            return false;
        }

        $_SWIFT_UserProfileImageObject->Output();

        return true;
    }

    /**
     * Display the Avatar
     *
     * @author Varun Shoor
     * @param int $_userID The User ID
     * @param string $_emailAddressHash (OPTIONAL) The Email Address Hash
     * @param int $_preferredWidth (OPTIONAL) The Preferred Width
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function DisplayAvatar($_userID, $_emailAddressHash = '', $_preferredWidth = 60)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        SWIFT_ProfileImage::OutputOnUserID($_userID, $_emailAddressHash, $_preferredWidth);

        return true;
    }

    /**
     * Login from Staff CP
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function LoginFromStaff()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } elseif (!$_SWIFT->User instanceof SWIFT_User || !$_SWIFT->User->GetIsClassLoaded()) {
            throw new SWIFT_Exception('Unable to login as user. Invalid user object detected.');
        }

        $this->UserInterface->Info(true, sprintf($this->Language->Get('loggedinfromstaff'), text_to_html_entities($_SWIFT->User->GetProperty('fullname'))));

        $this->Load->Controller('Default')->Load->Index();

        return true;
    }
}
