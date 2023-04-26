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

use Base\Library\CustomField\SWIFT_CustomFieldManager;
use Base\Library\CustomField\SWIFT_CustomFieldRenderer;
use Base\Library\CustomField\SWIFT_CustomFieldRendererClient;
use Base\Library\User\SWIFT_UserPasswordPolicy;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Models\CustomField\SWIFT_CustomFieldGroup;
use Base\Models\Template\SWIFT_Template;
use Base\Models\User\SWIFT_User;
use Base\Models\User\SWIFT_UserEmail;
use Base\Models\User\SWIFT_UserOrganization;
use Base\Models\User\SWIFT_UserOrganizationLink;
use Base\Models\User\SWIFT_UserProfileImage;
use Base\Models\User\SWIFT_UserSetting;
use Controller_client;
use News\Models\Subscriber\SWIFT_NewsSubscriber;
use SWIFT;
use SWIFT_App;
use Parser\Models\EmailQueue\SWIFT_EmailQueue;
use SWIFT_CountryContainer;
use SWIFT_Exception;
use SWIFT_Hook;
use SWIFT_Image_Exception;
use SWIFT_ImageResize;
use SWIFT_Loader;
use SWIFT_Session;
use SWIFT_TimeZoneContainer;

/**
 * The User Account Controller
 *
 * @method Model(string $_modelName, array $_arguments, bool $_initiateInstance, $_customAppName, string $appName = '')
 * @method Method($v='', $_ticketID=0, $_listType=0, $_departmentID=0, $_ticketStatusID=0, $_ticketTypeID=0, $_ticketLimitOffset=0);
 * @method Controller($_libraryName, $_arguments = [], $_initiateInstance = false, $_customAppName = '', $_appName = '')
 * @method Library($_libraryName, $_arguments = [], $_initiateInstance = false, $_customAppName = '', $_appName = '')
 * @property Controller_UserAccount $Load
 * @property SWIFT_CustomFieldRendererClient $CustomFieldRendererClient
 * @property SWIFT_CustomFieldManager $CustomFieldManager
 * @property SWIFT_TimeZoneContainer $TimeZoneContainer
 * @property SWIFT_CountryContainer $CountryContainer
 * @property SWIFT_UserPasswordPolicy $UserPasswordPolicy
 * @author Varun Shoor
 */
class Controller_UserAccount extends Controller_client
{
    /**
     * Constructor
     *
     * @author Varun Shoor
     * @throws SWIFT_Exception
     */
    public function __construct()
    {
        parent::__construct();

        $_SWIFT = SWIFT::GetInstance();

        $this->Load->Library('Misc:CountryContainer');

        $this->Load->Library('CustomField:CustomFieldRendererClient', [], true, false, 'base');
        $this->Load->Library('CustomField:CustomFieldManager', [], true, false, 'base');
        $this->Load->Library('User:UserPasswordPolicy', [], true, false, 'base');

        /*
         * BUG FIX - Rahul Bhattacharya, Nidhi Gupta
         *
         * SWIFT-1490 Logged in Customer Cannot Subscribe To News
         * SWIFT-4319 Uncaught exception Unable to locate file in ./__swift/models/Subscriber/class.SWIFT_NewsSubscriber.php
         */
        if (SWIFT_App::IsInstalled(APP_NEWS)) {
            SWIFT_Loader::LoadModel('Subscriber:NewsSubscriber', APP_NEWS);
        }
        $this->Language->Load('users');

        $_SWIFT = SWIFT::GetInstance();
        if (!$_SWIFT->Session->IsLoggedIn() || !$_SWIFT->User instanceof SWIFT_User || !$_SWIFT->User->GetIsClassLoaded()) {
            $this->UserInterface->Error(true, $this->Language->Get('logintocontinue'));

            $this->Load->Controller('Default', 'Base')->Load->Index();

            exit;
        }
    }

    /**
     * Display the Profile Form
     *
     * @author Varun Shoor
     * @param bool $_profilePrompt The Profile Prompt
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Profile($_profilePrompt = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($_profilePrompt) {
            $_SWIFT->User->MarkProfilePrompt();

            SWIFT::Info(true, $_SWIFT->Language->Get('userfirstprompt'));
        }

        // Process all emails for this user
        $_emailList = SWIFT_UserEmail::RetrieveList($_SWIFT->User->GetUserID());
        if (!_is_array($_emailList)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_userEmailContainer = array();
        foreach ($_emailList as $_key => $_val) {
            $_userEmailContainer[] = array('email' => htmlspecialchars($_val));
        }

        $this->Template->Assign('_userEmailContainer', $_userEmailContainer);

        // Set the profile image
        $_userAvatarImage = SWIFT::Get('themepathimages') . 'icon_defaultavatar.gif';
        if (SWIFT_UserProfileImage::UserHasProfileImage($_SWIFT->User->GetUserID())) {
            $_userAvatarImage = SWIFT::Get('basename') . '/Base/User/GetProfileImage/' . $_SWIFT->User->GetUserID();
        }
        $this->Template->Assign('_userAvatarImage', $_userAvatarImage);

        // Set the variables
        if (!isset($_POST['salutation'])) {
            $this->Template->Assign('_userSalutation', (int)($_SWIFT->User->GetProperty('salutation')));
        } else {
            $this->Template->Assign('_userSalutation', (int)($_POST['salutation']));
        }

        if (!isset($_POST['fullname'])) {
            $this->Template->Assign('_userFullName', text_to_html_entities($_SWIFT->User->GetProperty('fullname'), true));
        } else {
            $this->Template->Assign('_userFullName', text_to_html_entities($_POST['fullname'], true));
        }

        if (!isset($_POST['userdesignation'])) {
            $this->Template->Assign('_userDesignation', htmlspecialchars($_SWIFT->User->GetProperty('userdesignation')));
        } else {
            $this->Template->Assign('_userDesignation', htmlspecialchars($_POST['userdesignation']));
        }

        if (!isset($_POST['phone'])) {
            $this->Template->Assign('_userPhone', htmlspecialchars($_SWIFT->User->GetProperty('phone')));
        } else {
            $this->Template->Assign('_userPhone', htmlspecialchars($_POST['phone']));
        }
        /*
         * BUG FIX - Pankaj Garg
         *
         * SWIFT-1683, In case of multiple email addresses for user account, there should be an option under User account to be enabled for sending ticket updates to all the email addresses
         *
         */
        if (!isset($_POST['sendemailtoall'])) {
            $_userSettingContainer = SWIFT_UserSetting::RetrieveOnUser($_SWIFT->User);
            $_sendMailToAll = 1;
            if (isset($_userSettingContainer['sendemailtoall'])) {
                $_sendMailToAll = $_userSettingContainer['sendemailtoall'];
            }
            $this->Template->Assign('_sendEmailToAll', $_sendMailToAll);
        } else {
            $this->Template->Assign('_sendEmailToAll', (int)($_POST['sendemailtoall']));
        }

        // Organization Update Settings
        $_SWIFT_UserOrganizationObject = false;
        if ($_SWIFT->User->GetProperty('userorganizationid') != '0') {
            try {
                $_SWIFT_UserOrganizationObject = new SWIFT_UserOrganization($_SWIFT->User->GetProperty('userorganizationid'));
            } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
                $_userOrganizationID = false;

            }
        }

        $_hasOrganization = false;
        if ($_SWIFT_UserOrganizationObject instanceof SWIFT_UserOrganization && $_SWIFT_UserOrganizationObject->GetIsClassLoaded()) {
            $_hasOrganization = true;
            $orgs = SWIFT_UserOrganizationLink::RetrieveListOnUser($_SWIFT->User->GetUserID());
            // primary organization always goes first
            $orgName = htmlspecialchars($_SWIFT_UserOrganizationObject->GetProperty('organizationname'));
            if (count($orgs) > 1) {
                $orgName = '<b>'.$orgName.'</b>';
                foreach ($orgs as $org) {
                    if ($org === $_SWIFT_UserOrganizationObject->GetProperty('organizationname')) {
                        continue;
                    }
                    $orgName .= '<br/>' . htmlspecialchars($org);
                }
            }
            $this->Template->Assign('_userOrganizationName', $orgName);
        }

        if ($this->Settings->Get('user_orgselection') == 'dontallow' || $_hasOrganization == true) {
            $this->Template->Assign('_canUpdateOrganization', false);
        } else {
            $this->Template->Assign('_canUpdateOrganization', true);
        }

        $_extendedProfileHTML = '';
        // Begin Hook: client_user_profile
        unset($_hookCode);
        $_hookResult = null;
        ($_hookCode = SWIFT_Hook::Execute('client_user_profile')) ? ($_hookResult = eval($_hookCode)) : false;
        if ($_hookResult !== null) {
            return $_hookResult;
        }
        // End Hook
        $this->Template->Assign('_extendedProfileHTML', $_extendedProfileHTML);

        // Custom Fields
        $this->CustomFieldRendererClient->Render(SWIFT_CustomFieldRenderer::TYPE_FIELDS, SWIFT_UserInterface::MODE_EDIT, array(SWIFT_CustomFieldGroup::GROUP_USER), $_SWIFT->User->GetUserID());

        // Render the form
        $this->UserInterface->Header();

        $this->Template->Render('profileform');

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Process the Uploaded Profile Image
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    protected function _ProcessUploadedProfileImage()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        /**
         * BUNBTX: KAYAKOC-2521 - Profile Image exploit in QuickSupport Classic
         *
         * When an image with wrong format is uploaded, it should not
         * be used. Instead, the original content should be used or nothing if
         * it doesn't exist.
         *
         * @author Werner Garcia <werner.garcia@crossover.com>
         */

        // Add the Profile Image
        if (isset($_FILES['profileimage']) && is_uploaded_file($_FILES['profileimage']['tmp_name'])) {
            $maxFileSize = defined('MAXIMUM_UPLOAD_SIZE') ? MAXIMUM_UPLOAD_SIZE : 5242880;
            if ($_FILES['profileimage']['size'] > $maxFileSize) {
                SWIFT::Error(true, $this->Language->Get('wrong_image_size'));
                return false;
            }
            $profileImage = SWIFT_UserProfileImage::RetrieveOnUser($_SWIFT->User->GetUserID());
            SWIFT_UserProfileImage::DeleteOnUser(array($_SWIFT->User->GetUserID()));

            $_pathInfoContainer = pathinfo($_FILES['profileimage']['name']);
            if (isset($_pathInfoContainer['extension']) && !empty($_pathInfoContainer['extension'])) {
                $_ImageResizeObject = new SWIFT_ImageResize($_FILES['profileimage']['tmp_name']);

                $_ImageResizeObject->SetKeepProportions(true);

                $_fileContents = false;

                try {
                    $_ImageResizeObject->Resize();
                    $_fileContents = base64_encode($_ImageResizeObject->Get());
                } catch (SWIFT_Image_Exception $_SWIFT_Image_ExceptionObject) {
                    if ($profileImage) {
                        $_fileContents = $profileImage->GetProperty('imagedata');
                        unset($profileImage);
                    }
                    SWIFT::Error(true, $this->Language->Get('wrong_profile_image'));
                }

                if ($_fileContents) {
                    SWIFT_UserProfileImage::Create($_SWIFT->User->GetUserID(), $_pathInfoContainer['extension'], $_fileContents);
                } else {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Profile submission processor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function ProfileSubmit()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!in_array('profileform (Default)', SWIFT_Template::GetUpgradeRevertList()) && (!isset($_POST['_csrfhash']) || !SWIFT_Session::CheckCSRFHash($_POST['_csrfhash']))) {
            $this->UserInterface->Error(true, $this->Language->Get('msgcsrfhash'));

            $this->Load->Profile();

            return false;
        }

        if (!isset($_POST['fullname']) || !isset($_POST['salutation']) || !isset($_POST['userdesignation']) || !isset($_POST['phone'])) {

            $this->Load->Profile();

            return false;
        }

        // We need to check for data
        if (trim($_POST['fullname']) == '') {
            $this->UserInterface->CheckFields('fullname');

            $this->UserInterface->Error(true, $this->Language->Get('requiredfieldempty'));

            $this->Load->Profile();

            return false;
        }

        // No email provided?
        if ((!isset($_POST['emaillist']) && !isset($_POST['newemaillist'])) || ((!isset($_POST['emaillist']) || !_is_array($_POST['emaillist'])) && (!isset($_POST['newemaillist']) || !_is_array($_POST['newemaillist'])))) {
            $this->UserInterface->Error(true, $this->Language->Get('prnoemailprovided'));

            $this->Load->Profile();

            return false;
        }

        // Phone number check
        if (isset($_POST['phone']) && !empty($_POST['phone']) && SWIFT_User::CheckPhoneNumberExists($_POST['phone'], $_SWIFT->User->GetUserID())) {
            $this->UserInterface->Error(true, sprintf($this->Language->Get('prphoneregistered'), htmlspecialchars($_POST['phone'])));
            $this->Load->Profile();

            return false;
        }

        // Custom Field Check
        $_customFieldCheckResult = $this->CustomFieldManager->Check(SWIFT_CustomFieldManager::MODE_POST, SWIFT_UserInterface::MODE_EDIT, array(SWIFT_CustomFieldGroup::GROUP_USER), SWIFT_CustomFieldManager::CHECKMODE_CLIENT);
        if ($_customFieldCheckResult[0] == false) {
            call_user_func_array(array('SWIFT', 'ErrorField'), $_customFieldCheckResult[1]);

            $this->UserInterface->Error(true, $this->Language->Get('requiredfieldempty'));

            $this->Load->Profile();

            return false;
        }

        // Begin Hook: client_user_profilechecks
        unset($_hookCode);
        $_hookResult = null;
        ($_hookCode = SWIFT_Hook::Execute('client_user_profilechecks')) ? ($_hookResult = eval($_hookCode)) : false;
        if ($_hookResult !== null) {
            return $_hookResult;
        }
        // End Hook

        // Do we have a profile image?
        if (isset($_FILES['profileimage']) && is_uploaded_file($_FILES['profileimage']['tmp_name'])) {
            $_pathInfoContainer = pathinfo($_FILES['profileimage']['name']);
            if (isset($_pathInfoContainer['extension']) && !empty($_pathInfoContainer['extension'])) {
                $_pathInfoContainer['extension'] = strtolower($_pathInfoContainer['extension']);

                if (mb_strtolower($_pathInfoContainer['extension']) != 'gif' && mb_strtolower($_pathInfoContainer['extension']) != 'png' && mb_strtolower($_pathInfoContainer['extension']) != 'jpg' && mb_strtolower($_pathInfoContainer['extension']) != 'jpeg') {
                    SWIFT::ErrorField('profileimage');

                    $this->UserInterface->Error(true, $this->Language->Get('errorinvalidfileext'));

                    $this->Load->Profile();

                    return false;
                }
            }
        }

        // Check for new emails
        $_finalNewEmailList = array();
        if (isset($_POST['newemaillist']) && _is_array($_POST['newemaillist'])) {
            /**
             * BUG FIX : Mansi Wason <mansi.wason@opencart.com.vn>
             *
             * SWIFT-5195 : Better handling of email address for a user account
             *
             * Comments : Prevent customers to add email address same as an email queue.
             **/

            $this->Load->Model('EmailQueue:EmailQueue', [], false, false, APP_PARSER);

            $_EmailQueueList = SWIFT_EmailQueue::RetrieveEmailofAllEmailQueues();

            foreach ($_POST['newemaillist'] as $_key => $_val) {
                $_val = strtolower(trim($_val));

                // Is email valid?
                if (!IsEmailValid($_val)) {
                    $this->UserInterface->Error(true, $this->Language->Get('reginvalidemail'));

                    $this->Load->Profile();

                    return false;
                }

                // It isnt registered to someone else now is it?
                if (SWIFT_UserEmail::CheckEmailRecordExists(array($_val))) {
                    /*
                     * BUG FIX - Simaranjit Singh
                     *
                     * SWIFT-3624 Security issue
                     *
                     * Comment: None
                     */
                    $this->UserInterface->Error(true, $this->Language->Get('regemailregisteredsmall'));

                    $this->Load->Profile();

                    return false;
                }

                if (in_array($_val, $_EmailQueueList)) {

                    $this->UserInterface->Error(true, $this->Language->Get('reginvalidemailaddress'));

                    $this->Load->Profile();

                    return false;
                }

                $_finalNewEmailList[] = $_val;
            }
        }

        // Check for user emails that we need to delete
        $_userEmailList = SWIFT_UserEmail::RetrieveList($_SWIFT->User->GetUserID(), true);
        if (!_is_array($_userEmailList)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        // Do we need to completely remove existing email records?
        $_incomingExistingEmailList = array();
        if (isset($_POST['emaillist']) && _is_array($_POST['emaillist'])) {
            $_incomingExistingEmailList = $_POST['emaillist'];
        }

        $_deleteUserEmailIDList = array();
        foreach ($_userEmailList as $_key => $_val) {
            if (!in_array($_val, $_incomingExistingEmailList)) {
                $_deleteUserEmailIDList[] = $_key;
            }
        }

        // Do we need to delete any existing email records?
        if (count($_deleteUserEmailIDList)) {
            SWIFT_UserEmail::DeleteList($_deleteUserEmailIDList);
        }

        // Any new records to add?
        if (count($_finalNewEmailList)) {
            foreach ($_finalNewEmailList as $_key => $_val) {
                SWIFT_UserEmail::Create($_SWIFT->User, $_val);
            }
        }

        // Update Custom Field Values
        $this->CustomFieldManager->Update(SWIFT_CustomFieldManager::MODE_POST, SWIFT_UserInterface::MODE_EDIT, array(SWIFT_CustomFieldGroup::GROUP_USER), SWIFT_CustomFieldManager::CHECKMODE_CLIENT, $_SWIFT->User->GetUserID());

        // Time to update the profile
        /**
         * BUG FIX - Verem Dugeri <verem.dugeri@crossover.com>
         *
         * KAYAKO - 3095 - HTML tag injection
         *
         * Comments - Encode HTML Entities
         */
        $_salutation = htmlspecialchars($_POST['salutation']);
        $_fullName = text_to_html_entities($_POST['fullname'], true);
        $_userDesignation = htmlspecialchars($_POST['userdesignation']);
        $_phone = htmlspecialchars($_POST['phone']);

        $_SWIFT->User->UpdateProfile((int)$_salutation, $_fullName, $_userDesignation, $_phone);

        /*
         * BUG FIX - Pankaj Garg
         *
         * SWIFT-1683, In case of multiple email addresses for user account, there should be an option under User account to be enabled for sending ticket updates to all the email addresses
         */
        if (isset($_POST['sendemailtoall'])) {
            SWIFT_UserSetting::Replace($_SWIFT->User->GetID(), 'sendemailtoall', $_POST['sendemailtoall']);
        }

        // Any profile image?
        $this->_ProcessUploadedProfileImage();

        // Organization name specified and allowed to update?
        if ($this->Settings->Get('user_orgselection') != 'dontallow' && isset($_POST['userorganization']) && $_SWIFT->User->GetProperty('userorganizationid') == '0' && trim($_POST['userorganization']) != '') {
            $_userOrganizationID = false;

            // Always create a new organization
            if ($this->Settings->Get('user_orgselection') == 'createnew') {
                $_SWIFT_UserOrganizationObject = SWIFT_UserOrganization::Create($_POST['userorganization'], SWIFT_UserOrganization::TYPE_RESTRICTED, array());
                $_userOrganizationID = $_SWIFT_UserOrganizationObject->GetUserOrganizationID();

                // Create new or merge with an existing organization
            } else if ($this->Settings->Get('user_orgselection') == 'createmerge') {
                $_userOrganizationContainer = SWIFT_UserOrganization::RetrieveOnName($_POST['userorganization']);

                // Loop over possible organization names and check permissions
                foreach ($_userOrganizationContainer as $_userOrganization) {
                    if ($_userOrganization['organizationtype'] == SWIFT_UserOrganization::TYPE_RESTRICTED) {
                        $_userOrganizationID = $_userOrganization['userorganizationid'];

                        break;
                    }

                    if ($_userOrganization['organizationtype'] == SWIFT_UserOrganization::TYPE_SHARED && $this->Settings->Get('user_orgrestrictautoadd') == '1') {
                        $_userOrganizationID = $_userOrganization['userorganizationid'];

                        break;
                    }
                }

                if (!empty($_userOrganizationID)) {
                    $_SWIFT_UserOrganizationObject = false;

                    try {
                        $_SWIFT_UserOrganizationObject = new SWIFT_UserOrganization($_userOrganizationID);
                    } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
                        $_userOrganizationID = false;

                    }

                    // Shared organization restriction is enabled? If yes, we cannot allow this user to be part of this organization
                    if ($_SWIFT_UserOrganizationObject instanceof SWIFT_UserOrganization && $_SWIFT_UserOrganizationObject->GetIsClassLoaded() && $this->Settings->Get('user_orgrestrictautoadd') == '0' && $_SWIFT_UserOrganizationObject->GetProperty('organizationtype') == SWIFT_UserOrganization::TYPE_SHARED) {
                        $_userOrganizationID = false;
                    }

                }

                // We always create a new organization if all above fails
                if (empty($_userOrganizationID)) {
                    $_SWIFT_UserOrganizationObject = SWIFT_UserOrganization::Create($_POST['userorganization'], SWIFT_UserOrganization::TYPE_RESTRICTED, array());
                    $_userOrganizationID = $_SWIFT_UserOrganizationObject->GetUserOrganizationID();
                }
            }

            if (!empty($_userOrganizationID)) {
                $_SWIFT->User->UpdateOrganization($_userOrganizationID);
                if (!SWIFT_UserOrganizationLink::LinkExists($_userOrganizationID, $_SWIFT->User->GetUserID())) {
                    SWIFT_UserOrganizationLink::Create($_SWIFT->User, $_userOrganizationID);
                }
            }
        }

        // Begin Hook: client_user_profilesubmit
        unset($_hookCode);
        $_hookResult = null;
        ($_hookCode = SWIFT_Hook::Execute('client_user_profilesubmit')) ? ($_hookResult = eval($_hookCode)) : false;
        if ($_hookResult !== null) {
            return $_hookResult;
        }
        // End Hook

        // Display confirmation
        SWIFT::Info(true, $this->Language->Get('prprofileupdate'));

        $_userContainer = $_SWIFT->User->GetDataStore();
        $_SWIFT->Template->Assign('_user', $_userContainer);

        $this->Load->Profile();

        return true;
    }

    /**
     * Render the Preferences Form
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Preferences($_isSubmitted = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($_isSubmitted) {
            SWIFT::Info(true, $this->Language->Get('prupdated'));
        }

        // Begin timezone processing
        $_timeZoneContainer = array();
        $_userTimeZonePHP = $_SWIFT->User->GetProperty('timezonephp');
        $this->Load->Library('Misc:TimeZoneContainer');

        $_index = 0;
        foreach ($this->TimeZoneContainer->GetTimeZoneList() as $_key => $_val) {
            $_timeZoneContainer[$_index]['title'] = $_val['title'];
            $_timeZoneContainer[$_index]['value'] = htmlspecialchars($_val['value']);

            if ($_val['value'] == $_userTimeZonePHP) {
                $_timeZoneContainer[$_index]['selected'] = true;
            } else {
                $_timeZoneContainer[$_index]['selected'] = false;
            }

            $_index++;
        }

        $this->Template->Assign('_timeZoneContainer', $_timeZoneContainer);

        // Begin language processing
        $_languageContainer = array();
        $_userLanguageID = $_SWIFT->User->GetProperty('languageid');

        $_index = 0;
        $_languageCache = $this->Cache->Get('languagecache');
        foreach ($_languageCache as $_key => $_val) {
            if ($_val['isenabled'] != '1') {
                continue;
            }

            $_languageContainer[$_index]['title'] = $_val['title'];
            $_languageContainer[$_index]['languageid'] = (int)($_val['languageid']);

            if ($_val['languageid'] == $_userLanguageID) {
                $_languageContainer[$_index]['selected'] = true;
            } else {
                $_languageContainer[$_index]['selected'] = false;
            }

            $_index++;
        }

        $this->Template->Assign('_userLanguageContainer', $_languageContainer);

        // Assign other variables
        $this->Template->Assign('_enableDST', (int)($_SWIFT->User->GetProperty('enabledst')));

        /*
         * BUG FIX - Bishwanath Jha, Nidhi Gupta
         *
         * SWIFT-1490 Option to subscribe to news under user preferences (when logged in)
         * SWIFT-4319 Uncaught exception Unable to locate file in ./__swift/models/Subscriber/class.SWIFT_NewsSubscriber.php
         */
        if (SWIFT_App::IsInstalled(APP_NEWS)) {
            $_isSubscribed = SWIFT_NewsSubscriber::IsSubscribedOnUserID($_SWIFT->User->GetID());

            if (!$_isSubscribed) {
                foreach ($_SWIFT->User->GetEmailList() as $_email) {
                    if (SWIFT_NewsSubscriber::IsSubscribed($_email)) {
                        $_isSubscribed = true;
                        break;
                    }
                }
            }

            $this->Template->Assign('_newsSubscription', $_isSubscribed);

            // If User has permission to new subscribe ?
            if (SWIFT_User::GetPermission('perm_cansubscribenews') != '0') {
                $this->Template->Assign('_newsSubscriptionEnabled', true);
            } else {
                $this->Template->Assign('_newsSubscriptionEnabled', false);
            }
        }

        // Render the form
        $this->UserInterface->Header();

        $this->Template->Render('preferencesform');

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Process the preferences form
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function PreferencesSubmit()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!in_array('preferencesform (Default)', SWIFT_Template::GetUpgradeRevertList()) && (!isset($_POST['_csrfhash']) || !SWIFT_Session::CheckCSRFHash($_POST['_csrfhash']))) {
            $this->UserInterface->Error(true, $this->Language->Get('msgcsrfhash'));

            $this->Load->Preferences();

            return false;
        }

        if (!isset($_POST['languageid']) || !isset($_POST['timezone']) || !isset($_POST['enabledst'])) {
            $this->Load->Preferences();

            return false;
        }

        $_SWIFT->Cookie->Parse('client');
        $_SWIFT->Cookie->AddVariable('client', 'languageid', $_POST['languageid']);
        $_SWIFT->Cookie->Rebuild('client', true);

        $_SWIFT->User->UpdatePreferences($_POST['languageid'], $_POST['timezone'], $_POST['enabledst']);

        /*
         * BUG FIX - Bishwanath Jha, Nidhi Gupta
         *
         * SWIFT-1490 Option to subscribe to news under user preferences (when logged in)
         * SWIFT-4319 Uncaught exception Unable to locate file in ./__swift/models/Subscriber/class.SWIFT_NewsSubscriber.php
         */
        if (SWIFT_App::IsInstalled(APP_NEWS)) {
            if (isset($_POST['newssubscription']) && $_POST['newssubscription'] == 1 && SWIFT_User::GetPermission('perm_cansubscribenews') != '0') {
                SWIFT_NewsSubscriber::Subscribe($_SWIFT->User->GetID(), $_SWIFT->User->GetEmailList());
            } else if (isset($_POST['newssubscription']) && $_POST['newssubscription'] == 0) {
                SWIFT_NewsSubscriber::UnSubscribe($_SWIFT->User->GetID(), $_SWIFT->User->GetEmailList());
            }
        }

        header('location: ' . SWIFT::Get('basename') . '/Base/UserAccount/Preferences/1');

        return true;
    }

    /**
     * My Organization Profile
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function MyOrganization()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->Settings->Get('user_orgprofileupdate') == 'managersonly' && $_SWIFT->User->GetProperty('userrole') != SWIFT_User::ROLE_MANAGER) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        // Organization Update Settings
        $_SWIFT_UserOrganizationObject = false;
        if ($_SWIFT->User->GetProperty('userorganizationid') != '0') {
            try {
                $_SWIFT_UserOrganizationObject = new SWIFT_UserOrganization($_SWIFT->User->GetProperty('userorganizationid'));
            } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {

            }
        }

        if (!$_SWIFT_UserOrganizationObject instanceof SWIFT_UserOrganization || !$_SWIFT_UserOrganizationObject->GetIsClassLoaded()) {
            $this->UserInterface->Error(true, $this->Language->Get('invalidorganization'));

            $this->Load->Profile();

            return false;
        }

        if ($_SWIFT->User->GetProperty('userrole') != SWIFT_User::ROLE_MANAGER && $_SWIFT->Settings->Get('user_orgprofileupdate') == 'managersonly') {
            $this->UserInterface->Error(true, $this->Language->Get('nopermission'));

            $this->Load->Profile();

            return false;
        }

        $this->Template->Assign('_userOrganizationName', htmlspecialchars($_SWIFT_UserOrganizationObject->GetProperty('organizationname')));
        $this->Template->Assign('_userOrganizationAddress', htmlspecialchars($_SWIFT_UserOrganizationObject->GetProperty('address')));
        $this->Template->Assign('_userOrganizationCity', htmlspecialchars($_SWIFT_UserOrganizationObject->GetProperty('city')));
        $this->Template->Assign('_userOrganizationState', htmlspecialchars($_SWIFT_UserOrganizationObject->GetProperty('state')));
        $this->Template->Assign('_userOrganizationZIP', htmlspecialchars($_SWIFT_UserOrganizationObject->GetProperty('postalcode')));
        $this->Template->Assign('_userOrganizationPhone', htmlspecialchars($_SWIFT_UserOrganizationObject->GetProperty('phone')));
        $this->Template->Assign('_userOrganizationFax', htmlspecialchars($_SWIFT_UserOrganizationObject->GetProperty('fax')));
        $this->Template->Assign('_userOrganizationWebsite', htmlspecialchars($_SWIFT_UserOrganizationObject->GetProperty('website')));

        $_countryList = $this->CountryContainer->GetList();

        $_userOrganizationCountry = $_SWIFT_UserOrganizationObject->GetProperty('country');

        $_optionsContainer = array();
        $_index = 0;
        foreach ($_countryList as $_val) {
            $_optionsContainer[$_index]['title'] = htmlspecialchars($_val);
            $_optionsContainer[$_index]['value'] = htmlspecialchars(trim($_val));

            if ($_userOrganizationCountry == trim($_val) || (!$_userOrganizationCountry && $_index == 0)) {
                $_optionsContainer[$_index]['selected'] = true;
            }

            $_index++;
        }

        $this->Template->Assign('_countryContainer', $_optionsContainer);

        // Custom Fields
        $this->CustomFieldRendererClient->Render(SWIFT_CustomFieldRenderer::TYPE_FIELDS, SWIFT_UserInterface::MODE_EDIT, array(SWIFT_CustomFieldGroup::GROUP_USERORGANIZATION), $_SWIFT_UserOrganizationObject->GetUserOrganizationID());

        // Render the form
        $this->UserInterface->Header();

        $this->Template->Render('myorganizationform');

        $this->UserInterface->Footer();


        return true;
    }

    /**
     * The Organization Profile Submission
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function MyOrganizationSubmit()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->Settings->Get('user_orgprofileupdate') == 'managersonly' && $_SWIFT->User->GetProperty('userrole') != SWIFT_User::ROLE_MANAGER) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if (!in_array('myorganizationform (Default)', SWIFT_Template::GetUpgradeRevertList()) && (!isset($_POST['_csrfhash']) || !SWIFT_Session::CheckCSRFHash($_POST['_csrfhash']))) {
            $this->UserInterface->Error(true, $this->Language->Get('msgcsrfhash'));

            $this->Load->MyOrganization();

            return false;
        }

        if (!isset($_POST['address']) && !isset($_POST['city']) && !isset($_POST['state']) && !isset($_POST['postalcode']) && !isset($_POST['country']) && !isset($_POST['phone']) && !isset($_POST['fax']) && !isset($_POST['website'])) {

            $this->Load->MyOrganization();

            return false;
        }

        // Organization Update Settings
        $_SWIFT_UserOrganizationObject = false;
        if ($_SWIFT->User->GetProperty('userorganizationid') != '0') {
            try {
                $_SWIFT_UserOrganizationObject = new SWIFT_UserOrganization($_SWIFT->User->GetProperty('userorganizationid'));
            } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {

            }
        }

        if (!$_SWIFT_UserOrganizationObject instanceof SWIFT_UserOrganization || !$_SWIFT_UserOrganizationObject->GetIsClassLoaded()) {
            $this->UserInterface->Error(true, $this->Language->Get('invalidorganization'));

            $this->Load->Profile();

            return false;
        }

        if ($_SWIFT->User->GetProperty('userrole') != SWIFT_User::ROLE_MANAGER && $_SWIFT->Settings->Get('user_orgprofileupdate') == 'managersonly') {
            $this->UserInterface->Error(true, $this->Language->Get('nopermission'));

            $this->Load->Profile();

            return false;
        }

        // Custom Field Check
        $_customFieldCheckResult = $this->CustomFieldManager->Check(SWIFT_CustomFieldManager::MODE_POST, SWIFT_UserInterface::MODE_EDIT, array(SWIFT_CustomFieldGroup::GROUP_USERORGANIZATION), SWIFT_CustomFieldManager::CHECKMODE_CLIENT);
        if ($_customFieldCheckResult[0] == false) {
            call_user_func_array(array('SWIFT', 'ErrorField'), $_customFieldCheckResult[1]);

            $this->UserInterface->Error(true, $this->Language->Get('requiredfieldempty'));

            $this->Load->Profile();

            return false;
        }

        $_SWIFT_UserOrganizationObject->UpdateProfile($_POST['address'], $_POST['city'], $_POST['state'], $_POST['postalcode'], $_POST['country'],
            $_POST['phone'], $_POST['fax'], $_POST['website']);

        // Update Custom Field Values
        $this->CustomFieldManager->Update(SWIFT_CustomFieldManager::MODE_POST, SWIFT_UserInterface::MODE_EDIT, array(SWIFT_CustomFieldGroup::GROUP_USERORGANIZATION), SWIFT_CustomFieldManager::CHECKMODE_CLIENT, $_SWIFT_UserOrganizationObject->GetUserOrganizationID());

        SWIFT::Info(true, $this->Language->Get('orgupdated'));

        $this->Load->MyOrganization();

        return true;
    }

    /**
     * Render the Change Password Form
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ChangePassword()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        // Render the form
        $this->UserInterface->Header();

        $this->Template->Render('changepasswordform');

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Process the change password form
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function ChangePasswordSubmit()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!in_array('changepasswordform (Default)', SWIFT_Template::GetUpgradeRevertList()) && (!isset($_POST['_csrfhash']) || !SWIFT_Session::CheckCSRFHash($_POST['_csrfhash']))) {
            $this->UserInterface->Error(true, $this->Language->Get('msgcsrfhash'));

            $this->Load->ChangePassword();

            return false;
        }

        if (!isset($_POST['existingpassword']) || trim($_POST['existingpassword']) == '' || trim($_POST['newpassword']) == '' || trim($_POST['newpasswordrepeat']) == '') {
            $this->UserInterface->CheckFields('existingpassword', 'newpassword', 'newpasswordrepeat');

            $this->UserInterface->Error(true, $this->Language->Get('requiredfieldempty'));

            $this->Load->ChangePassword();

            return false;
        }

        /*
         * BUG FIX - Simaranjit Singh
         *
         * SWIFT-3059 Imported users are not able to update their password
         *
         * Comments: None
         */
        if ($_SWIFT->User->GetProperty('islegacypassword') == '0' && SWIFT_User::GetComputedPassword($_POST['existingpassword']) != $_SWIFT->User->GetProperty('userpassword')) {
            $this->UserInterface->Error(true, $this->Language->Get('existingpwdoesnotmatch'));

            $this->Load->ChangePassword();

            return false;
        }

        if ($_SWIFT->User->GetProperty('islegacypassword') == '1' && md5($_POST['existingpassword']) != $_SWIFT->User->GetProperty('userpassword')) {
            $this->UserInterface->Error(true, $this->Language->Get('newpwdoesnotmatch'));

            $this->Load->ChangePassword();

            return false;
        }

        if ($_POST['newpassword'] != $_POST['newpasswordrepeat']) {
            SWIFT::ErrorField('newpassword', 'newpasswordrepeat');
            $this->UserInterface->Error(true, $this->Language->Get('newpwdoesnotmatch'));

            $this->Load->ChangePassword();

            return false;
        }

        if (!empty($_POST['newpassword']) && !$this->UserPasswordPolicy->Check($_POST['newpassword'])) {
            SWIFT::ErrorField('newpassword', 'newpasswordrepeat');

            $this->UserInterface->Error($this->Language->Get('regtitlepwpolicy'), $this->Language->Get('regmsgpwpolicy') . ' ' . $this->UserPasswordPolicy->GetPasswordPolicyString());

            $this->Load->ChangePassword();

            return false;
        }

        $_SWIFT->User->UpdatePassword($_POST['newpassword']);

        SWIFT::Info(true, $this->Language->Get('newpwupdated'));

        $this->Load->ChangePassword();

        return true;
    }
}
