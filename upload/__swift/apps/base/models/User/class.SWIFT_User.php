<?php
/**
 * ###############################################
 *
 * SWIFT Framework
 * _______________________________________________
 *
 * @author         Varun Shoor
 *
 * @package        SWIFT
 * @copyright      Copyright (c) 2001-2012, QuickSupport
 * @license        http://www.kayako.com/license
 * @link           http://www.kayako.com
 *
 * ###############################################
 */

namespace Base\Models\User;

use Base\Models\GeoIP\SWIFT_GeoIP;
use Base\Models\Tag\SWIFT_TagLink;
use Base\Models\Template\SWIFT_TemplateGroup;
use News\Models\Subscriber\SWIFT_NewsSubscriber;
use SWIFT;
use SWIFT_App;
use SWIFT_Data;
use SWIFT_DataID;
use SWIFT_Exception;
use SWIFT_Interface;
use SWIFT_LanguageEngine;
use SWIFT_Loader;
use Base\Library\LoginShare\SWIFT_LoginShareUser;
use SWIFT_Model;
use Base\Library\Notification\SWIFT_NotificationManager;
use SWIFT_Session;
use SWIFT_TemplateEngine;
use Base\Library\User\SWIFT_UserNotification;
use Base\Library\User\SWIFT_UserPasswordPolicy;
use Base\Models\User\SWIFT_UserEmail;
use Base\Models\User\SWIFT_UserNote;
use Base\Models\User\SWIFT_UserOrganizationEmail;

/**
 * The User Management Class
 *
 * @method int GetUserID()
 * @property \SWIFT_Mail $Mail
 * @author Varun Shoor
 */
class SWIFT_User extends SWIFT_Model
{
    const TABLE_NAME = 'users';
    const PRIMARY_KEY = 'userid';
    const TABLE_STRUCTURE = "userid I PRIMARY AUTO NOTNULL,
                            usergroupid I DEFAULT '0' NOTNULL,
                            userrole I2 DEFAULT '0' NOTNULL,
                            userorganizationid I DEFAULT '0' NOTNULL,
                            salutation I2 DEFAULT '0' NOTNULL,
                            fullname C(200) DEFAULT '' NOTNULL,
                            userdesignation C(255) DEFAULT '' NOTNULL,
                            phone C(25) DEFAULT '' NOTNULL,
                            userpassword C(50) DEFAULT '' NOTNULL,
                            islegacypassword I2 DEFAULT '0' NOTNULL,
                            dateline I DEFAULT '0' NOTNULL,
                            lastupdate I DEFAULT '0' NOTNULL,
                            lastvisit I DEFAULT '0' NOTNULL,
                            lastvisit2 I DEFAULT '0' NOTNULL,
                            lastactivity I DEFAULT '0' NOTNULL,
                            lastvisitip C(255) DEFAULT '' NOTNULL,
                            lastvisitip2 C(255) DEFAULT '' NOTNULL,
                            isenabled I2 DEFAULT '0' NOTNULL,
                            languageid I DEFAULT '0' NOTNULL,
                            timezonephp C(100) DEFAULT '' NOTNULL,
                            enabledst I2 DEFAULT '0' NOTNULL,
                            useremailcount I DEFAULT '0' NOTNULL,
                            slaplanid I DEFAULT '0' NOTNULL,
                            slaexpirytimeline I DEFAULT '0' NOTNULL,
                            userexpirytimeline I DEFAULT '0' NOTNULL,
                            isvalidated I DEFAULT '0' NOTNULL,
                            profileprompt I2 DEFAULT '0' NOTNULL,

                            hasgeoip I2 DEFAULT '0' NOTNULL,
                            geoiptimezone C(255) DEFAULT '' NOTNULL,
                            geoipisp C(255) DEFAULT '' NOTNULL,
                            geoiporganization C(255) DEFAULT '' NOTNULL,
                            geoipnetspeed C(255) DEFAULT '' NOTNULL,
                            geoipcountry C(10) DEFAULT '' NOTNULL,
                            geoipcountrydesc C(255) DEFAULT '' NOTNULL,
                            geoipregion C(255) DEFAULT '' NOTNULL,
                            geoipcity C(255) DEFAULT '' NOTNULL,
                            geoippostalcode C(255) DEFAULT '' NOTNULL,
                            geoiplatitude C(255) DEFAULT '' NOTNULL,
                            geoiplongitude C(255) DEFAULT '' NOTNULL,
                            geoipmetrocode C(255) DEFAULT '' NOTNULL,
                            geoipareacode C(255) DEFAULT '' NOTNULL";

    const INDEX_1 = 'usergroupid';
    const INDEX_2 = 'isenabled, dateline';
    const INDEX_3 = 'userorganizationid';
    const INDEX_4 = 'fullname, phone';
    const INDEX_5 = 'isvalidated, dateline';


    protected $_dataStore = array();

    static public $_permissionCache = array();

    // Notification Stuff
    public $Notification = false;
    public $NotificationManager = false;
    static protected $_notificationExecutionCache = array();
    private $_shutdownIndex = false;

    // Core Constants
    const ROLE_USER = 1;
    const ROLE_MANAGER = 2;

    const SALUTATION_NONE = 0;
    const SALUTATION_MR = 1;
    const SALUTATION_MISS = 2;
    const SALUTATION_MRS = 3;
    const SALUTATION_DR = 4;

    /**
     * Constructor
     *
     * @author Varun Shoor
     *
     * @param SWIFT_Data|array|int $_SWIFT_DataObject
     */
    public function __construct($_SWIFT_DataObject)
    {
        parent::__construct($_SWIFT_DataObject);

        $this->Language->Load('users_notifications', SWIFT_LanguageEngine::TYPE_FILE);

        $this->Notification = new SWIFT_UserNotification($this);
        $this->NotificationManager = new SWIFT_NotificationManager($this);

        register_shutdown_function(function(): void {
            SWIFT_User::ProcessNotificationManager($this);
        });
    }

    /**
     * Destructor
     *
     * @author Varun Shoor
     */
    public function __destruct()
    {
        if (SWIFT_INTERFACE == 'tests') {
            return;
        }

        chdir(SWIFT_BASEPATH);

        $this->ProcessNotifications();

        $this->ProcessUpdatePool();

        parent::__destruct();
    }

    /**
     * Process the Notifications
     *
     * @author Varun Shoor
     *
     * @param SWIFT_User $_SWIFT_UserObject
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function ProcessNotificationManager($_SWIFT_UserObject)
    {
        if ($_SWIFT_UserObject instanceof SWIFT_User && $_SWIFT_UserObject->GetIsClassLoaded()) {
            $_SWIFT_UserObject->ProcessNotifications();
        }
    }

    /**
     * Check to see if its a valid user role
     *
     * @author Varun Shoor
     *
     * @param int $_userRole The User Role
     *
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidUserRole($_userRole)
    {
        return ($_userRole == self::ROLE_MANAGER || $_userRole == self::ROLE_USER);
    }

    /**
     * Check to see if its a valid salutation
     *
     * @author Varun Shoor
     *
     * @param int $_userSalutation The User Salutation
     *
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidSalutation($_userSalutation)
    {
        return ($_userSalutation == self::SALUTATION_NONE || $_userSalutation == self::SALUTATION_MR || $_userSalutation == self::SALUTATION_MISS || $_userSalutation == self::SALUTATION_MRS || $_userSalutation == self::SALUTATION_DR);
    }

    /**
     * Retrieve a list of Salutations
     *
     * @author Varun Shoor
     * @return array Salutation List
     */
    public static function RetrieveSalutationList()
    {
        $_SWIFT = SWIFT::GetInstance();

        return array(
            self::SALUTATION_NONE => '', self::SALUTATION_MR => $_SWIFT->Language->Get('salutationmr'), self::SALUTATION_MISS => $_SWIFT->Language->Get('salutationmiss'),
            self::SALUTATION_MRS => $_SWIFT->Language->Get('salutationmrs'), self::SALUTATION_DR => $_SWIFT->Language->Get('salutationdr')
        );
    }

    /**
     * Check the Email Container
     *
     * @author Varun Shoor
     *
     * @param array $_emailContainer The Email Container
     *
     * @return bool "true" on Success, "false" otherwise
     */
    public static function CheckEmailContainer($_emailContainer)
    {
        if (!_is_array($_emailContainer)) {
            return false;
        }

        $_isEmailValid = false;

        foreach ($_emailContainer as $_emailAddress) {
            if (IsEmailValid($_emailAddress)) {
                $_isEmailValid = true;
            }
        }

        return $_isEmailValid;
    }

    /**
     * Retrieve the User Salutation
     *
     * @author Varun Shoor
     * @return mixed "salutation" (STRING) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetSalutation()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        switch ($this->GetProperty('salutation')) {
            case self::SALUTATION_NONE:
                return '';
                break;

            case self::SALUTATION_MR:
                return $this->Language->Get('salutationmr');
                break;

            case self::SALUTATION_MISS:
                return $this->Language->Get('salutationmiss');
                break;

            case self::SALUTATION_MRS:
                return $this->Language->Get('salutationmrs');
                break;

            case self::SALUTATION_DR:
                return $this->Language->Get('salutationdr');
                break;

            default:
                break;
        }

        return '';
    }

    /**
     * Retrieve the full name with salutation if specified
     *
     * @author Varun Shoor
     *
     * @param bool $_useSalutation Whether to use salutation
     *
     * @return string
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetFullName($_useSalutation = false)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!$_useSalutation) {
            return $this->GetProperty('fullname');
        }

        $_salutation = $this->GetSalutation();

        if (!empty($_salutation)) {
            return $_salutation . ' ' . $this->GetProperty('fullname');
        }

        return $this->GetProperty('fullname');
    }

    /**
     * Retrieve the User Organization Name
     *
     * @author Varun Shoor
     * @return string|false
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetOrganizationName()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_userOrganizationID = (int)($this->GetProperty('userorganizationid'));
        if (!empty($_userOrganizationID)) {
            try {
                $_SWIFT_UserOrganizationObject = new SWIFT_UserOrganization($_userOrganizationID);
                if ($_SWIFT_UserOrganizationObject instanceof SWIFT_UserOrganization && $_SWIFT_UserOrganizationObject->GetIsClassLoaded()) {
                    return $_SWIFT_UserOrganizationObject->GetProperty('organizationname');
                }
            } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            }
        }

        return false;
    }

    /**
     * Retrieve the User Organization Object
     *
     * @author Varun Shoor
     * @return SWIFT_UserOrganization|bool
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetOrganization()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_userOrganizationID = (int)($this->GetProperty('userorganizationid'));
        if (!empty($_userOrganizationID)) {
            try {
                $_SWIFT_UserOrganizationObject = new SWIFT_UserOrganization($_userOrganizationID);
                if ($_SWIFT_UserOrganizationObject instanceof SWIFT_UserOrganization && $_SWIFT_UserOrganizationObject->GetIsClassLoaded()) {
                    return $_SWIFT_UserOrganizationObject;
                }
            } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            }
        }

        return false;
    }

    /**
     * Retrieve the Email List for the User
     *
     * @author Varun Shoor
     * @return array
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetEmailList()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return SWIFT_UserEmail::RetrieveList($this->GetUserID());
    }

    /**
     * Create a new User Record
     *
     * @author Varun Shoor
     *
     * @param int $_userGroupID The User Group ID
     * @param int $_userOrganizationID The User Organization ID
     * @param int $_salutation The User Salutation
     * @param string $_fullName The User Full Name
     * @param string $_userDesignation The User Designation
     * @param string $_phone The Phone Number
     * @param bool $_isEnabled Whether the User is enabled
     * @param int $_userRole The User Role
     * @param array $_emailContainer The Email Container
     * @param string $_userPassword The User Password
     * @param int $_languageID The User Language ID
     * @param string $_timeZonePHP The User Timezone Offset
     * @param bool $_enableDST Whether to Enable DST
     * @param int $_slaPlanID Custom SLA Plan for this User
     * @param int $_slaExpiry The SLA Expiry Dateline
     * @param int $_userExpiry The User Expiry Dateline
     * @param bool $_sendWelcomeEmail Whether to send a welcome email to this user
     * @param int|bool $_isValidated Whether the user is validated by default..
     * @param bool $_calculateGeoIP Whether to calculate the GeoIP for this user
     *
     * @return mixed "_SWIFT_UserObject" (OBJECT) on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided or If Creation Fails
     */
    public static function Create(
        $_userGroupID, $_userOrganizationID, $_salutation, $_fullName, $_userDesignation, $_phone, $_isEnabled, $_userRole,
        $_emailContainer, $_userPassword = '', $_languageID = 0, $_timeZonePHP = '', $_enableDST = false, $_slaPlanID = 0,
        $_slaExpiry = 0, $_userExpiry = 0, $_sendWelcomeEmail = true, $_isValidated = 0, $_calculateGeoIP = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_SWIFT_UserOrganizationObject = false;

        try {
            $_SWIFT_UserOrganizationObject = new SWIFT_UserOrganization($_userOrganizationID);
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            $_SWIFT_UserOrganizationObject = false;
        }

        // Check sanity of the User Organization
        if (!$_SWIFT_UserOrganizationObject instanceof SWIFT_UserOrganization || !$_SWIFT_UserOrganizationObject->GetIsClassLoaded() || empty($_userOrganizationID)) {
            // No Organization found or specified.. check against organization email filters..
            $_userOrganizationID = SWIFT_UserOrganizationEmail::GetOrganizationFromEmailList($_emailContainer);

            try {
                $_SWIFT_UserOrganizationObject = new SWIFT_UserOrganization($_userOrganizationID);
            } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
                $_SWIFT_UserOrganizationObject = false;
            }
        }

        // No user role specified?
        if ($_userRole == 0) {
            if ($_SWIFT->Settings->Get('user_orgdefaultclassification') == 'manager') {
                $_userRole = self::ROLE_MANAGER;
            } else {
                $_userRole = self::ROLE_USER;
            }
        }

        if (!self::IsValidUserRole($_userRole) || trim($_fullName) == '' || !self::CheckEmailContainer($_emailContainer) ||
            !SWIFT_UserGroup::IsValidUserGroupID($_userGroupID) || !self::IsValidSalutation($_salutation)
        ) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        // Do we need to set a random userpassword?
        $_customPassword = false;
        if (empty($_userPassword)) {

            if ($_SWIFT->Settings->Get('security_scloginlocked') == '1') {
                $_userPassword = SWIFT_UserPasswordPolicy::GenerateUserPassword();
            } else {
                $_userPassword = substr(BuildHash(), 0, 10);
            }
            $_customPassword = true;
        }

        if (empty($_slaPlanID)) {
            $_slaExpiry = 0;
        }

        // Insert User
        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'users', array(
            'usergroupid' => $_userGroupID, 'salutation' => $_salutation,
            'userrole' => $_userRole, 'userorganizationid' => $_userOrganizationID,
            'fullname' => $_fullName,
            'userdesignation' => $_userDesignation, 'phone' => $_phone,
            'userpassword' => self::GetComputedPassword($_userPassword),
            'dateline' => DATENOW, 'isenabled' => (int)($_isEnabled), 'languageid' => $_languageID,
            'timezonephp' => $_timeZonePHP,
            'enabledst' => (int)($_enableDST), 'slaplanid' => $_slaPlanID,
            'slaexpirytimeline' => (int)($_slaExpiry),
            'userexpirytimeline' => $_userExpiry, 'isvalidated' => (int)$_isValidated
        ), 'INSERT');

        $_userID = $_SWIFT->Database->Insert_ID();
        if (!$_userID) {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        $_SWIFT_UserObject = new SWIFT_User(new SWIFT_DataID($_userID));
        if (!$_SWIFT_UserObject instanceof SWIFT_User || !$_SWIFT_UserObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        // create organizationlink if it doesn't exist only if organizationId is valid
        if ($_userOrganizationID > 0 && !SWIFT_UserOrganizationLink::LinkExists($_userOrganizationID, $_userID)) {
            $_userOrganizationLinkID = SWIFT_UserOrganizationLink::Create($_SWIFT_UserObject, $_userOrganizationID);
            if (!$_userOrganizationLinkID) {
                throw new SWIFT_Exception(SWIFT_CREATEFAILED . ': SWIFT_UserOrganizationLink');
            }
        }

        foreach ($_emailContainer as $_key => $_val) {
            if (!IsEmailValid($_val)) {
                continue;
            }

            SWIFT_UserEmail::Create($_SWIFT_UserObject, $_val, false);
        }

        if ($_sendWelcomeEmail == true && $_SWIFT->Settings->Get('user_dispatchregemail') == '0') {
            $_sendWelcomeEmail = false;
        }

        // Send Welcome Email?
        if ($_sendWelcomeEmail == true) {
            $_languageCache = $_SWIFT->Cache->Get('languagecache');

            if ($_languageID == 0) {
                foreach ($_languageCache as $id => $lang) {
                    if ($lang['isdefault'] == 1) {
                        $_languageID = $id;
                        break;
                    }
                }
            }

            if ($_languageID != '0' && isset($_languageCache[$_languageID]) && $_SWIFT->Language->GetLanguageID() != $_languageID) {
                $_languageCode = $_languageCache[$_languageID]['languagecode'];
                $_SWIFT->Language->SetLanguageID($_languageID);
                $_SWIFT->Language->SetLanguageCode($_languageCode);
            }

            /*
             * BUG FIX - Varun Shoor
             *
             * SWIFT-1547 Registration email always send Default template group URL, rather than sending the template group URL according to user group
             *
             * Comments: None
             */
            if (!empty($_userGroupID)
                && isset($_SWIFT->TemplateGroup) && $_SWIFT->TemplateGroup instanceof SWIFT_TemplateGroup && $_SWIFT->TemplateGroup->GetRegisteredUserGroupID() != $_userGroupID
            ) {
                $_templateGroupCache = $_SWIFT->Cache->Get('templategroupcache');
                $_templateGroupID = 0;
                foreach ($_templateGroupCache as $_templateGroupContainer) {
                    if ($_templateGroupContainer['regusergroupid'] == $_userGroupID) {
                        $_templateGroupID = $_templateGroupContainer['tgroupid'];

                        break;
                    }
                }

                if (!empty($_templateGroupID)) {
                    $_SWIFT->Template->SetTemplateGroupPrefix($_templateGroupID);
                }
            }

            $_SWIFT_UserObject->DispatchWelcomeEmail($_userPassword);
        }

        if ($_calculateGeoIP) {
            $_SWIFT_UserObject->UpdateGeoIP();
        }

        /**
         * BUG FIX : Saloni Dhall <saloni.dhall@kayako.com>
         *
         * SWIFT-3239 : News is not dispatched to 'Subscriber' list if concerned news article is restricted on basis of user group
         *
         * Comments : Updating subscriber information at the time of new insertion of user in DB.
         */
        if (SWIFT_App::IsInstalled(APP_NEWS)) {
            SWIFT_Loader::LoadModel('Subscriber:NewsSubscriber', APP_NEWS);

            $_SWIFT_NewsSubscriberObject = SWIFT_NewsSubscriber::RetreiveSubscriberOnUser($_userID, $_emailContainer['0']);
            if ($_SWIFT_NewsSubscriberObject && $_SWIFT_NewsSubscriberObject instanceof SWIFT_NewsSubscriber && $_SWIFT_NewsSubscriberObject->GetIsClassLoaded()) {
                $_SWIFT_NewsSubscriberObject->UpdatePool('usergroupid', $_userGroupID);
                $_SWIFT_NewsSubscriberObject->UpdatePool('email', $_emailContainer['0']);
                $_SWIFT_NewsSubscriberObject->UpdatePool('userid', $_userID);
            }
        }

        $_SWIFT_UserObject->NotificationManager->SetEvent('newuser');

        return $_SWIFT_UserObject;
    }

    /**
     * Update the User Record
     *
     * @author Varun Shoor
     *
     * @param int $_userGroupID The User Group ID
     * @param int $_userOrganizationID The User Organization ID
     * @param int $_salutation The User Salutation
     * @param string $_fullName The User Full Name
     * @param string $_userDesignation The User Designation
     * @param string $_phone The Phone Number
     * @param bool $_isEnabled Whether the User is enabled
     * @param int $_userRole The User Role
     * @param array $_emailContainer The Email Container
     * @param int $_languageID The User Language ID
     * @param string $_timeZonePHP The User Timezone Offset
     * @param bool $_enableDST Whether to Enable DST
     * @param int $_slaPlanID Custom SLA Plan for this User
     * @param int $_slaExpiry The SLA Expiry Dateline
     * @param int $_userExpiry The User Expiry Dateline
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Update(
        $_userGroupID, $_userOrganizationID, $_salutation, $_fullName, $_userDesignation, $_phone, $_isEnabled, $_userRole, $_emailContainer, $_languageID = 0,
        $_timeZonePHP = '', $_enableDST = false, $_slaPlanID = 0, $_slaExpiry = 0, $_userExpiry = 0)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_UserOrganizationObject = false;

        try {
            $_SWIFT_UserOrganizationObject = new SWIFT_UserOrganization($_userOrganizationID);
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
        }

        if (!self::IsValidUserRole($_userRole) || trim($_fullName) == '' || !self::CheckEmailContainer($_emailContainer) || !SWIFT_UserGroup::IsValidUserGroupID($_userGroupID) || !self::IsValidSalutation($_salutation)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        // Check sanity of the User Organization
        if (!$_SWIFT_UserOrganizationObject instanceof SWIFT_UserOrganization || !$_SWIFT_UserOrganizationObject->GetIsClassLoaded()) {
            $_userOrganizationID = false;
        }

        if (empty($_slaPlanID)) {
            $_slaExpiry = false;
        }

        $this->UpdatePool('userrole', $_userRole);
        $this->UpdatePool('usergroupid', $_userGroupID);
        $this->UpdatePool('userorganizationid', $_userOrganizationID);
        $this->UpdatePool('salutation', $_salutation);
        $this->UpdatePool('fullname', $_fullName);
        $this->UpdatePool('userdesignation', $_userDesignation);
        $this->UpdatePool('phone', $_phone);
        $this->UpdatePool('isenabled', (int)($_isEnabled));
        $this->UpdatePool('lastupdate', DATENOW);
        $this->UpdatePool('languageid', $_languageID);
        $this->UpdatePool('timezonephp', $_timeZonePHP);
        $this->UpdatePool('enabledst', (int)($_enableDST));
        $this->UpdatePool('slaplanid', $_slaPlanID);
        $this->UpdatePool('slaexpirytimeline', (int)($_slaExpiry));
        $this->UpdatePool('userexpirytimeline', $_userExpiry);
        $this->ProcessUpdatePool();

        $_primaryEmailList = SWIFT_UserEmail::RetrievePrimaryEmailOnUserEmail($_emailContainer);
        if (!_is_array($_primaryEmailList)) {
            $_primaryEmailList = array();
        }

        SWIFT_UserEmail::DeleteOnUser(array($this->GetUserID()));
        foreach ($_emailContainer as $_key => $_val) {
            if (!IsEmailValid($_val)) {
                continue;
            }

            SWIFT_UserEmail::Create($this, $_val, IIF(in_array($_val, $_primaryEmailList), true, false));
        }

        /**
         * BUG FIX : Saloni Dhall <saloni.dhall@kayako.com>
         *
         * SWIFT-3239 : News is not dispatched to 'Subscriber' list if concerned news article is restricted on basis of user group
         *
         * Comments : Updating subscriber information in database.
         */
        if (SWIFT_App::IsInstalled(APP_NEWS)) {
            SWIFT_Loader::LoadModel('Subscriber:NewsSubscriber', APP_NEWS);

            $_SWIFT_NewsSubscriberObject = SWIFT_NewsSubscriber::RetreiveSubscriberOnUser($this->GetUserID());
            if ($_SWIFT_NewsSubscriberObject && $_SWIFT_NewsSubscriberObject instanceof SWIFT_NewsSubscriber && $_SWIFT_NewsSubscriberObject->GetIsClassLoaded()) {
                $_SWIFT_NewsSubscriberObject->UpdatePool('usergroupid', $_userGroupID);
                $_SWIFT_NewsSubscriberObject->UpdatePool('email', $_emailContainer['0']);
            }
        }

        return true;
    }

    /**
     * Update the User Record from LoginShare
     *
     * @author Varun Shoor
     *
     * @param int $_userGroupID The User Group ID
     * @param string $_fullName The User Full Name
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function UpdateLoginShare($_userGroupID, $_fullName)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (trim($_fullName) == '' || !SWIFT_UserGroup::IsValidUserGroupID($_userGroupID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->UpdatePool('usergroupid', $_userGroupID);
        $this->UpdatePool('fullname', $_fullName);
        $this->UpdatePool('lastupdate', DATENOW);
        $this->ProcessUpdatePool();

        return true;
    }


    /**
     * Update the user designation
     *
     * @author Jamie Edwards
     *
     * @param string $_userDesignation The User Designation
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function UpdateUserDesignation($_userDesignation)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UpdatePool('userdesignation', $_userDesignation);
        $this->UpdatePool('lastupdate', DATENOW);
        $this->ProcessUpdatePool();

        return true;
    }


    /**
     * Update the user's phone number
     *
     * @author Jamie Edwards
     *
     * @param string $_phone The user's phone number
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function UpdateUserPhoneNumber($_phone)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UpdatePool('phone', $_phone);
        $this->UpdatePool('lastupdate', DATENOW);
        $this->ProcessUpdatePool();

        return true;
    }


    /**
     * Update the user preferences
     *
     * @author Varun Shoor
     *
     * @param int $_salutation The User Salutation
     * @param string $_fullName The User Full Name
     * @param string $_userDesignation The User Designation
     * @param string $_phone The Phone Number
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function UpdateProfile($_salutation, $_fullName, $_userDesignation, $_phone)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (trim($_fullName) == '' || !self::IsValidSalutation($_salutation)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }


        $this->UpdatePool('salutation', $_salutation);
        $this->UpdatePool('fullname', $_fullName);
        $this->UpdatePool('userdesignation', $_userDesignation);
        $this->UpdatePool('phone', $_phone);
        $this->ProcessUpdatePool();

        return true;
    }

    /**
     * Update the user preferences
     *
     * @author Varun Shoor
     *
     * @param int $_languageID The User Language ID
     * @param string $_timeZonePHP The User Timezone Offset
     * @param bool $_enableDST Whether to Enable DST
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function UpdatePreferences($_languageID, $_timeZonePHP, $_enableDST)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UpdatePool('languageid', $_languageID);
        $this->UpdatePool('timezonephp', $_timeZonePHP);
        $this->UpdatePool('enabledst', (int)($_enableDST));
        $this->ProcessUpdatePool();

        return true;
    }

    /**
     * Update the user language
     *
     * @author Varun Shoor
     *
     * @param int $_languageID The User Language ID
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function UpdateLanguage($_languageID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UpdatePool('languageid', $_languageID);
        $this->ProcessUpdatePool();

        return true;
    }

    /**
     * Delete the Given Users
     *
     * @author Varun Shoor
     *
     * @param array $_userIDList The User ID List
     *
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_userIDList)
    {
        if (!_is_array($_userIDList)) {
            return false;
        }

        // Kill the Sessions
        SWIFT_Session::KillSessionListOnType(array(SWIFT_Interface::INTERFACE_CLIENT), $_userIDList);

        parent::DeleteList($_userIDList);

        SWIFT_UserProfileImage::DeleteOnUser($_userIDList);

        SWIFT_UserEmail::DeleteOnUser($_userIDList);

        SWIFT_UserNote::DeleteOnUser($_userIDList);

        SWIFT_TagLink::DeleteOnLinkList(SWIFT_TagLink::TYPE_USER, $_userIDList);

        SWIFT_UserSetting::DeleteOnUser($_userIDList);

        return true;
    }

    /**
     * Enable the currently loaded user
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Enable()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::EnableList(array($this->GetUserID()));

        return true;
    }

    /**
     * Disable the currently loaded user
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Disable()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DisableList(array($this->GetUserID()));

        return true;
    }

    /**
     * Disable the User List
     *
     * @author Varun Shoor
     *
     * @param array $_userIDList The User ID List
     *
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DisableList($_userIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_userIDList)) {
            return false;
        }

        $_finalUserIDList = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "users WHERE userid IN (" . BuildIN($_userIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_finalUserIDList[] = $_SWIFT->Database->Record['userid'];
        }

        if (!count($_finalUserIDList)) {
            return false;
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'users', array('isenabled' => '0'), 'UPDATE', "userid IN (" . BuildIN($_finalUserIDList) . ")");

        return true;
    }

    /**
     * Enable the Given User List
     *
     * @author Varun Shoor
     *
     * @param array $_userIDList The User ID List
     *
     * @return bool "true" on Success, "false" otherwise
     */
    public static function EnableList($_userIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_userIDList)) {
            return false;
        }

        $_finalUserIDList = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "users WHERE userid IN (" . BuildIN($_userIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_finalUserIDList[] = $_SWIFT->Database->Record['userid'];
        }

        if (!count($_finalUserIDList)) {
            return false;
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'users', array('isenabled' => '1'), 'UPDATE', "userid IN (" . BuildIN($_finalUserIDList) . ")");

        return true;
    }

    /**
     * Delete users based on User Organization ID list
     *
     * @author Varun Shoor
     *
     * @param array $_userOrganizationIDList The User Organization ID List Container
     *
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteOnUserOrganization($_userOrganizationIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_userOrganizationIDList)) {
            return false;
        }

        $_userIDList = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "users WHERE userorganizationid IN (" . BuildIN($_userOrganizationIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_userIDList[] = $_SWIFT->Database->Record['userid'];
        }

        if (!count($_userIDList)) {
            return false;
        }

        self::DeleteList($_userIDList);

        return true;
    }

    /**
     * Removes the Association with User Organizations based on the given id list
     *
     * @author Varun Shoor
     *
     * @param array $_userOrganizationIDList The User Organization ID List
     *
     * @return bool "true" on Success, "false" otherwise
     */
    public static function RemoveGlobalUserOrganizationAssociation($_userOrganizationIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_userOrganizationIDList)) {
            return false;
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'users', array('userorganizationid' => '0'), 'UPDATE', "userorganizationid IN (" . BuildIN($_userOrganizationIDList) . ")");

        return true;
    }

    /**
     * Retrieve the computed password
     *
     * @author Varun Shoor
     *
     * @param string $_userPassword The User Password
     *
     * @return string The Computed Password
     */
    public static function GetComputedPassword($_userPassword)
    {
        /*
         * ###############################################
         * TODO: Add Installation Hash + Key License # as salt
         * ###############################################
         */

        return sha1($_userPassword);
    }

    /**
     * Change the password for the user
     *
     * @author Varun Shoor
     *
     * @param string $_newPassword The new user password
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function ChangePassword($_newPassword)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_newPassword)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->UpdatePool('userpassword', self::GetComputedPassword($_newPassword));
        $this->UpdatePool('lastupdate', DATENOW);
        $this->ProcessUpdatePool();

        return true;
    }

    /**
     * Updates the last visit timeline for this user
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function UpdateLastVisit()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UpdatePool('lastvisit', $this->GetProperty('lastvisit2'));
        $this->UpdatePool('lastvisit2', DATENOW);

        $this->UpdatePool('lastvisitip', $this->GetProperty('lastvisitip2'));
        $this->UpdatePool('lastvisitip2', SWIFT::Get('IP'));

        $this->ProcessUpdatePool();

        return true;
    }

    /**
     * Loads the User Data into $_SWIFT Variable
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Class is not Loaded or If Data Provided is Invalid
     */
    public function LoadIntoSWIFTNamespace()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if ($_SWIFT->User instanceof SWIFT_User && $_SWIFT->User->GetIsClassLoaded()) {
            return true;
        }

        $_userContainer = $this->GetDataStore();
        $_SWIFT->Template->Assign('_user', $_userContainer);

        $_userGroupSettingCache = $_SWIFT->Cache->Get('usergroupsettingcache');

        $_userGroupID = $_userContainer['usergroupid'];

        $_permissionContainer = array();
        if (_is_array($_userContainer) && isset($_userGroupSettingCache[$_userGroupID]) && is_array($_userGroupSettingCache[$_userGroupID])) {
            $_permissionContainer = $_userGroupSettingCache[$_userGroupID];
        }

        self::$_permissionCache = $_permissionContainer;

        $_SWIFT->User = $this;

        // If the staff user has chosen a timezone to override the default, use it here.
        if ($this->GetProperty('timezonephp') != '' && $this->GetProperty('timezonephp') != '0') // '' is "use default"
        {
            SWIFT::Set('timezone', $this->GetProperty('timezonephp'));
            SWIFT::Set('daylightsavings', ($this->GetProperty('enabledst') != '0') ? true : false);

            // Set the override timezone in PHP.
            if (!date_default_timezone_set(SWIFT::Get('timezone'))) {
                date_default_timezone_set('GMT');
            }
        }

        // User group doesnt exist? revert to master registered group..
        $_registeredUserGroupID = (int)($this->GetProperty('usergroupid'));
        $_userGroupCache = $this->Cache->Get('usergroupcache');

        if (!isset($_userGroupCache[$_registeredUserGroupID]) || (isset($_userGroupCache[$_registeredUserGroupID]) &&
                $_userGroupCache[$_registeredUserGroupID]['grouptype'] != SWIFT_UserGroup::TYPE_REGISTERED)
        ) {
            foreach ($_userGroupCache as $_userGroup) {
                if ($_userGroup['grouptype'] == SWIFT_UserGroup::TYPE_REGISTERED && $_userGroup['ismaster'] == '1') {
                    $_registeredUserGroupID = $_userGroup['usergroupid'];

                    break;
                }
            }
        }

        SWIFT::Set('usergroupid', $_registeredUserGroupID);

        // Language Logic
        $_SWIFT->Cookie->Parse('client');
        $_cookieLanguageID = $_SWIFT->Cookie->GetVariable('client', 'languageid');

        $_languageCache = $this->Cache->Get('languagecache');
        if (isset($_languageCache[$this->GetProperty('languageid')]) && $_SWIFT->Language->GetLanguageID() != $this->GetProperty('languageid') && (empty($_cookieLanguageID) || $_cookieLanguageID == $this->GetProperty('languageid'))) {

            $_languageCode = $_languageCache[$this->GetProperty('languageid')]['languagecode'];

            $_SWIFT->Language->SetLanguageID($this->GetProperty('languageid'));

            $_SWIFT->Language->SetLanguageCode($_languageCode);
            $_SWIFT->Language->LoadLanguageTable();
        }

        return true;
    }

    /**
     * Authenticate the User
     *
     * @author Varun Shoor
     *
     * @param string $_email The User Email Address
     * @param string $_userPassword The User Password
     * @param bool $_computeHash (OPTIONAL) Compute the hash for the given password
     *
     * @return SWIFT_User|bool "_SWIFT_UserObject" (OBJECT) on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function Authenticate($_email, $_userPassword, $_computeHash = true)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_finalPassword = $_userPassword;
        if ($_computeHash == true) {
            $_finalPassword = self::GetComputedPassword($_userPassword);
        }

        // LoginShare Logic
        if (SWIFT_LoginShareUser::IsActive() && isset($_SWIFT->TemplateGroup) && $_SWIFT->TemplateGroup->GetProperty('useloginshare') == '1') {
            $_SWIFT_LoginShareUserObject = new SWIFT_LoginShareUser();
            if (!$_SWIFT_LoginShareUserObject->GetIsClassLoaded()) {
                throw new SWIFT_Exception(SWIFT_INVALIDDATA . 'LOGINSHARE');
            }

            return $_SWIFT_LoginShareUserObject->Authenticate($_email, $_userPassword, SWIFT::Get('IP'));
        }

        // Fallback to normal authentication if not enabled

        // First retrieve the user email address
        $_userID = SWIFT_UserEmail::RetrieveUserIDOnUserEmail($_email);
        if (!$_userID) {
            // No user found with that email?
            SWIFT::Set('errorstring', $_SWIFT->Language->Get('invaliduseracc'));

            return false;
        }

        // Now that we have the user id.. we need to get the user.
        $_SWIFT_UserObject = new SWIFT_User(new SWIFT_DataID($_userID));
        if (!$_SWIFT_UserObject instanceof SWIFT_User || !$_SWIFT_UserObject->GetIsClassLoaded()) {
            // How did this happen?
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        // Is user disabled?
        if ($_SWIFT_UserObject->GetProperty('isenabled') == '0' || $_SWIFT_UserObject->GetProperty('isvalidated') == '0') {
            SWIFT::Set('errorstring', $_SWIFT->Language->Get('invaliduserdisabled'));

            return false;
            // Has user expired?
        } else if ($_SWIFT_UserObject->GetProperty('userexpirytimeline') != '0' && $_SWIFT_UserObject->GetProperty('userexpirytimeline') < DATENOW) {
            SWIFT::Set('errorstring', $_SWIFT->Language->Get('invaliduserexpired'));

            return false;
        }

        // Legacy password support
        if ($_SWIFT_UserObject->GetProperty('islegacypassword') == '1' && $_computeHash == true) {
            $_finalPassword = md5($_userPassword);
        }

        // Authenticate now...
        if ($_SWIFT_UserObject->GetProperty('userpassword') != $_finalPassword || empty($_finalPassword)) {
            SWIFT::Set('errorstring', $_SWIFT->Language->Get('invaliduser'));

            return false;
        }

        // User should be authenticated by now..
        $_SWIFT_UserObject->LoadIntoSWIFTNameSpace();

        $_SWIFT_UserObject->UpdateLastVisit();

        return $_SWIFT_UserObject;
    }

    /**
     * Clanup Unverified Accounts & Old Password Hashes
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public static function CleanUp()
    {
        $_SWIFT = SWIFT::GetInstance();

        // We only move ahead with clearing of unverified users if
        if ($_SWIFT->Settings->Get('user_adelunveri') == '1') {
            $_userIDList = array();
            $_dateThreshold = DATENOW - ($_SWIFT->Settings->Get('user_delcleardays') * 86400);

            $_SWIFT->Database->Query("SELECT userid FROM " . TABLE_PREFIX . "users WHERE isvalidated = '0' AND dateline < '" . (int)($_dateThreshold) . "'");
            while ($_SWIFT->Database->NextRecord()) {
                $_userIDList[] = $_SWIFT->Database->Record['userid'];
            }

            // Delete the unverified users.. if any
            if (count($_userIDList)) {
                self::DeleteList($_userIDList);
            }
        }

        SWIFT_UserVerifyHash::CleanUp();

        return true;
    }

    /**
     * Retrieve the default validation setting
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public static function GetDefaultValidationSetting()
    {
        $_SWIFT = SWIFT::GetInstance();

        // Manual verification?
        if ($_SWIFT->Settings->Get('u_enablesveri') == '1') {
            return false;
        }

        return true;
    }

    /**
     * Dispatch the welcome email when a user is inserted by staff. !! THIS IS ONLY CALLED FROM STAFF CP !!
     *
     * @author Varun Shoor
     *
     * @param string $_userPassword The User Password
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function DispatchWelcomeEmail($_userPassword)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->Load->Library('Mail:Mail');

        // Load the phrases from the database..
        $this->Language->Queue('default', SWIFT_LanguageEngine::TYPE_DB);
        $this->Language->Queue('users', SWIFT_LanguageEngine::TYPE_DB);
        $this->Language->LoadQueue(SWIFT_LanguageEngine::TYPE_DB);

        $_emailList = SWIFT_UserEmail::RetrieveList($this->GetUserID());

        // No user email?
        if (!_is_array($_emailList)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_emailContents = sprintf($this->Language->Get('registerwelcomeemail'), SWIFT::Get('companyname'), SWIFT::Get('swiftpath'), $_emailList[0], $_userPassword);

        $this->Template->Assign('_contentsText', $_emailContents);
        $this->Template->Assign('_contentsHTML', nl2br($_emailContents));

        $_textEmailContents = $this->Template->Get('email_text', SWIFT_TemplateEngine::TYPE_DB);
        $_htmlEmailContents = $this->Template->Get('email_html', SWIFT_TemplateEngine::TYPE_DB);

        $this->Mail->SetFromField($this->Settings->Get('general_returnemail'), SWIFT::Get('companyname'));

        foreach ($_emailList as $_key => $_val) {
            if ($_key == 0) {
                $this->Mail->SetToField($_val);
            } else {
                $this->Mail->AddCC($_val);
            }
        }

        $this->Mail->SetSubjectField(sprintf($this->Language->Get('registeremailsubject'), SWIFT::Get('companyname')));

        $this->Mail->SetDataText($_textEmailContents);
        $this->Mail->SetDataHTML($_htmlEmailContents);

        $this->Mail->SendMail();

        return true;
    }

    /**
     * Attempt to validate the user after verifying identity
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function MarkAsVerified()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UpdatePool('isvalidated', '1');
        $this->UpdatePool('lastupdate', DATENOW);
        $this->ProcessUpdatePool();

        if ($this->Settings->Get('user_dispatchregemail') == '0') {
            $this->DispatchRegistrationEmail();
        }

        // Empty the verification hashes..
        SWIFT_UserVerifyHash::DeleteOnUser(array($this->GetUserID()));

        return true;
    }

    /**
     * Dispatch the email once user verifies himself
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function DispatchRegistrationEmail()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->Load->Library('Mail:Mail');
        $this->Language->Queue('default', SWIFT_LanguageEngine::TYPE_DB);
        $this->Language->Queue('users', SWIFT_LanguageEngine::TYPE_DB);
        $this->Language->LoadQueue(SWIFT_LanguageEngine::TYPE_DB);

        $_emailList = SWIFT_UserEmail::RetrieveList($this->GetUserID());

        // No user email?
        if (!_is_array($_emailList)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_emailContents = sprintf($this->Language->Get('registersuccessemail'), SWIFT::Get('companyname'), SWIFT::Get('swiftpath'), $_emailList[0]);

        $this->Template->Assign('_contentsText', $_emailContents);
        $this->Template->Assign('_contentsHTML', nl2br($_emailContents));

        $_textEmailContents = $this->Template->Get('email_text', SWIFT_TemplateEngine::TYPE_DB);
        $_htmlEmailContents = $this->Template->Get('email_html', SWIFT_TemplateEngine::TYPE_DB);

        $this->Mail->SetFromField($this->Settings->Get('general_returnemail'), SWIFT::Get('companyname'));

        foreach ($_emailList as $_key => $_val) {
            if ($_key == 0) {
                $this->Mail->SetToField($_val);
            } else {
                $this->Mail->AddCC($_val);
            }
        }

        $this->Mail->SetSubjectField(sprintf($this->Language->Get('registeremailsubject'), SWIFT::Get('companyname')));

        $this->Mail->SetDataText($_textEmailContents);
        $this->Mail->SetDataHTML($_htmlEmailContents);

        $this->Mail->SendMail();

        return true;
    }

    /**
     * Validate the user's identity
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Hash Creation Fails
     */
    public function CreateVerifyAttempt()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_userVerifyHashID = SWIFT_UserVerifyHash::Create(SWIFT_UserVerifyHash::TYPE_USER, $this->GetUserID());
        if (!$_userVerifyHashID) {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        $this->DispatchValidationEmail($_userVerifyHashID);

        return true;
    }

    /**
     * Dispatch the validation email to verify email address of user
     *
     * @author Varun Shoor
     *
     * @param string $_userVerifyHashID The Unique Hash ID
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    protected function DispatchValidationEmail($_userVerifyHashID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_userVerifyHashID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->Load->Library('Mail:Mail');
        $_emailList = SWIFT_UserEmail::RetrieveList($this->GetUserID());

        // No user email?
        if (!_is_array($_emailList)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_validationLink = SWIFT::Get('basename') . '/Base/UserRegistration/Validate/' . $_userVerifyHashID;

        $_emailValidationContents = sprintf($this->Language->Get('registervalidateemail'), SWIFT::Get('companyname'), SWIFT::Get('swiftpath'), $_validationLink);

        $this->Template->Assign('_contentsText', $_emailValidationContents);
        $this->Template->Assign('_contentsHTML', nl2br($_emailValidationContents));

        $_textEmailContents = $this->Template->Get('email_text');
        $_htmlEmailContents = $this->Template->Get('email_html');

        $this->Mail->SetFromField($this->Settings->Get('general_returnemail'), SWIFT::Get('companyname'));

        foreach ($_emailList as $_key => $_val) {
            if ($_key == 0) {
                $this->Mail->SetToField($_val);
            } else {
                $this->Mail->AddCC($_val);
            }
        }

        $this->Mail->SetSubjectField(sprintf($this->Language->Get('registeremailvalidationsubject'), SWIFT::Get('companyname')));

        $this->Mail->SetDataText($_textEmailContents);
        $this->Mail->SetDataHTML($_htmlEmailContents);

        $this->Mail->SendMail();

        return true;
    }

    /**
     * Run the Forgot Password Routines
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If User Verification Hash Creation Fails
     */
    public function ForgotPassword()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_userVerifyHashID = SWIFT_UserVerifyHash::Create(SWIFT_UserVerifyHash::TYPE_FORGOTPASSWORD, $this->GetUserID());
        if (!$_userVerifyHashID) {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        $this->DispatchForgotPasswordEmail($_userVerifyHashID);

        return true;
    }

    /**
     * Dispatches the Forgot Password Email
     *
     * @author Varun Shoor
     *
     * @param string $_userVerifyHashID The Forgot Password Hash
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function DispatchForgotPasswordEmail($_userVerifyHashID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_userVerifyHashID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->Load->Library('Mail:Mail');
        $_emailList = SWIFT_UserEmail::RetrieveList($this->GetUserID());

        // No user email?
        if (!_is_array($_emailList)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        /*
         * BUG FIX - Sandeep Verma
         *
         * SWIFT-1875 Password reset email always contain default template group URL in body even password reset request is made after accessing specific template group.
         *
         */
        $_validationLink = SWIFT::Get('basename') . $this->Template->GetTemplateGroupPrefix() . '/Base/UserLostPassword/Validate/' . $_userVerifyHashID;

        $_emailValidationContents = sprintf($this->Language->Get('forgotpasswordemail'), SWIFT::Get('companyname'), SWIFT::Get('swiftpath') . 'index.php?' . $this->Template->GetTemplateGroupPrefix(), $_validationLink);

        $this->Template->Assign('_contentsText', $_emailValidationContents);
        $this->Template->Assign('_contentsHTML', nl2br($_emailValidationContents));

        $_textEmailContents = $this->Template->Get('email_text');
        $_htmlEmailContents = $this->Template->Get('email_html');

        $this->Mail->SetFromField($this->Settings->Get('general_returnemail'), SWIFT::Get('companyname'));

        foreach ($_emailList as $_key => $_val) {
            if ($_key == 0) {
                $this->Mail->SetToField($_val);
            } else {
                $this->Mail->AddCC($_val);
            }
        }

        $this->Mail->SetSubjectField(sprintf($this->Language->Get('lostpasswordemailsubject'), SWIFT::Get('companyname')));

        $this->Mail->SetDataText($_textEmailContents);
        $this->Mail->SetDataHTML($_htmlEmailContents);

        $this->Mail->SendMail();

        return true;
    }

    /**
     * Retrieve a list of users based on the user organization
     *
     * @author Varun Shoor
     *
     * @param int $_userOrganizationID The User Organization ID
     *
     * @return array
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function GetUserIDListOnOrganization($_userOrganizationID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_userOrganizationID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_userIDList = array();

        $_SWIFT->Database->Query("SELECT userid FROM " . TABLE_PREFIX . "users WHERE userorganizationid = '" . $_userOrganizationID . "'");
        while ($_SWIFT->Database->NextRecord()) {
            $_userIDList[] = (int)($_SWIFT->Database->Record['userid']);
        }

        return $_userIDList;
    }

    /**
     * Update the user's organization
     *
     * @author Varun Shoor
     *
     * @param int $_userOrganizationID The User Organization ID
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function UpdateOrganization($_userOrganizationID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UpdatePool('userorganizationid', $_userOrganizationID);
        $this->ProcessUpdatePool();

        return true;
    }

    /**
     * Update the organization for given users
     *
     * @author Varun Shoor
     *
     * @param array $_userIDList The User ID List
     * @param int $_userOrganizationID The User Organization ID
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function UpdateOrganizationList($_userIDList, $_userOrganizationID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_userIDList)) {
            return false;
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'users', array('userorganizationid' => $_userOrganizationID), 'UPDATE', "userid IN (" . BuildIN($_userIDList) . ")");

        return true;
    }

    /**
     * Update the user group for given users
     *
     * @author Varun Shoor
     *
     * @param array $_userIDList The User ID List
     * @param int $_userGroupID The User Group ID
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function UpdateGroupList($_userIDList, $_userGroupID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_userIDList)) {
            return false;
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'users', array('usergroupid' => $_userGroupID), 'UPDATE',
            "userid IN (" . BuildIN($_userIDList) . ")");

        return true;
    }

    /**
     * Updates the last activity timeline for this user
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function UpdateLastActivity()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UpdatePool('lastactivity', DATENOW);

        $this->ProcessUpdatePool();

        return true;
    }

    /**
     * Update the User Password
     *
     * @author Varun Shoor
     *
     * @param string $_newPassword The New User Password
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function UpdatePassword($_newPassword)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UpdatePool('userpassword', self::GetComputedPassword($_newPassword));
        /*
         * BUG FIX - Simaranjit Singh
         *
         * SWIFT-3060 Imported users are not able to login when staff updates their password.
         *
         * Comments: None
         */
        if ($this->GetProperty('islegacypassword') == '1') {
            $this->UpdatePool('islegacypassword', '0'); // Set this value 0 because new password is based upon SHA1
        }
        $this->ProcessUpdatePool();

        return true;
    }

    /**
     * Generate a New Password & Email
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GeneratePassword()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->Load->Library('Mail:Mail');
        $_emailList = SWIFT_UserEmail::RetrieveList($this->GetUserID());

        /**
         * BUG FIX - Ravi Sharma <ravi.sharma@kayako.com>
         *
         * SWIFT-1425 "Generate & Email" password reset on user accounts always sends in English.
         */
        $_templateGroupCache = $_SWIFT->Cache->Get('templategroupcache');
        $_templateGroupID = 0;
        $_languageID = 0;

        foreach ($_templateGroupCache as $_templateGroupContainer) {
            if ($_templateGroupContainer['regusergroupid'] == $this->GetProperty('usergroupid')) {
                $_templateGroupID = $_templateGroupContainer['tgroupid'];
                $_languageID = $_templateGroupContainer['languageid'];
                break;
            }
        }

        if (!empty($_templateGroupID)) {
            $_SWIFT->Template->SetTemplateGroupPrefix($_templateGroupID);

            $_languageCache = $_SWIFT->Cache->Get('languagecache');

            if ($_languageID != '0' && isset($_languageCache[$_languageID]) && $_SWIFT->Language->GetLanguageID() != $_languageID) {
                $_languageCode = $_languageCache[$_languageID]['languagecode'];
                $_SWIFT->Language->SetLanguageID($_languageID);
                $_SWIFT->Language->SetLanguageCode($_languageCode);
            }
        }

        // No user email?
        if (!_is_array($_emailList)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_userPassword = substr(BuildHash(), 0, 8);

        $this->UpdatePassword($_userPassword);

        $this->Template->Assign('_userPassword', $_userPassword);

        $this->Language->Queue('default', SWIFT_LanguageEngine::TYPE_DB);
        $this->Language->Queue('users', SWIFT_LanguageEngine::TYPE_DB);
        $this->Language->LoadQueue(SWIFT_LanguageEngine::TYPE_DB);

        $_textEmailContents = $this->Template->Get('email_generatepassword_text', SWIFT_TemplateEngine::TYPE_DB);
        $_htmlEmailContents = $this->Template->Get('email_generatepassword_html', SWIFT_TemplateEngine::TYPE_DB);

        $this->Mail->SetFromField($this->Settings->Get('general_returnemail'), SWIFT::Get('companyname'));

        foreach ($_emailList as $_key => $_val) {
            if ($_key == 0) {
                $this->Mail->SetToField($_val);
            } else {
                $this->Mail->AddCC($_val);
            }
        }

        $this->Mail->SetSubjectField(sprintf($this->Language->Get('generatepasswordemailsubject'), SWIFT::Get('companyname')));

        $this->Mail->SetDataText($_textEmailContents);
        $this->Mail->SetDataHTML($_htmlEmailContents);

        $this->Mail->SendMail();

        return true;
    }

    /**
     * Update the GeoIP details for this user
     *
     * @author Varun Shoor
     *
     * @param string|bool $_ipAddress (OPTIONAL) The IP Address to use, if empty, uses the SWIFT::IP
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function UpdateGeoIP($_ipAddress = false)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_finalIPAddress = SWIFT::Get('IP');
        if (!empty($_ipAddress)) {
            $_finalIPAddress = $_ipAddress;
        }

        $_geoIPContainer = SWIFT_GeoIP::GetIPDetails($_finalIPAddress, array(
            SWIFT_GeoIP::GEOIP_ISP, SWIFT_GeoIP::GEOIP_ORGANIZATION,
            SWIFT_GeoIP::GEOIP_NETSPEED, SWIFT_GeoIP::GEOIP_CITY
        ));

        if (isset($_geoIPContainer[SWIFT_GeoIP::GEOIP_ISP])) {
            $this->UpdatePool('geoipisp', $_geoIPContainer[SWIFT_GeoIP::GEOIP_ISP]);
        }

        if (isset($_geoIPContainer[SWIFT_GeoIP::GEOIP_ORGANIZATION])) {
            $this->UpdatePool('geoiporganization', $_geoIPContainer[SWIFT_GeoIP::GEOIP_ORGANIZATION]);
        }

        if (isset($_geoIPContainer[SWIFT_GeoIP::GEOIP_NETSPEED])) {
            $this->UpdatePool('geoipnetspeed', $_geoIPContainer[SWIFT_GeoIP::GEOIP_NETSPEED]);
        }

        if (isset($_geoIPContainer[SWIFT_GeoIP::GEOIP_CITY])) {
            $this->UpdatePool('geoipcountry', $_geoIPContainer[SWIFT_GeoIP::GEOIP_CITY]['country']);
            $this->UpdatePool('geoipregion', $_geoIPContainer[SWIFT_GeoIP::GEOIP_CITY]['region']);
            $this->UpdatePool('geoipcity', $_geoIPContainer[SWIFT_GeoIP::GEOIP_CITY]['city']);
            $this->UpdatePool('geoippostalcode', $_geoIPContainer[SWIFT_GeoIP::GEOIP_CITY]['postalcode']);
            $this->UpdatePool('geoiplatitude', $_geoIPContainer[SWIFT_GeoIP::GEOIP_CITY]['latitude']);
            $this->UpdatePool('geoiplongitude', $_geoIPContainer[SWIFT_GeoIP::GEOIP_CITY]['longitude']);
            $this->UpdatePool('geoipmetrocode', $_geoIPContainer[SWIFT_GeoIP::GEOIP_CITY]['metrocode']);
            $this->UpdatePool('geoipareacode', $_geoIPContainer[SWIFT_GeoIP::GEOIP_CITY]['areacode']);
        }

        $this->UpdatePool('hasgeoip', '1');
        $this->ProcessUpdatePool();

        return true;
    }

    /**
     * Retrieve a user object based on email list
     *
     * @author Varun Shoor
     *
     * @param array $_emailList The Email List
     *
     * @return mixed "SWIFT_User" (OBJECT) on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveOnEmailList($_emailList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!count($_emailList)) {
            return false;
        }

        $_userIDList = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "useremails WHERE linktype = '" . SWIFT_UserEmail::LINKTYPE_USER . "' AND email IN (" . BuildIN($_emailList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            if (!in_array($_SWIFT->Database->Record['linktypeid'], $_userIDList)) {
                $_userIDList[] = (int)($_SWIFT->Database->Record['linktypeid']);
            }
        }

        if (!count($_userIDList)) {
            return false;
        }

        foreach ($_userIDList as $_userID) {
            try {
                $_SWIFT_UserObject = new SWIFT_User(new SWIFT_DataID($_userID));

                return $_SWIFT_UserObject;
            } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
                continue;
            }
        }

        return false;
    }

    /**
     * Retrieve User List Based on Phone Number
     *
     * @author Varun Shoor
     *
     * @param string $_phoneNumber The Phone Number
     *
     * @return array User ID List
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveOnPhoneNumber($_phoneNumber)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_phoneNumber)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_userIDList = array();

        $_phoneSQLExtended = '';
        $_phoneSQLContainer = array();

        /*
         * BUG FIX: Parminder Singh
         *
         * SWIFT-644: Phone call tab - user search not working properly
         *
         * Comments: None
         */
        $_phoneDigitCount = strlen($_phoneNumber);
        if ($_phoneDigitCount > 5) {
            $_phoneDigitCount = 5;
        }

        for ($index = 0; $index < $_phoneDigitCount; $index++) {
            $_phoneSQLContainer[] = "phone LIKE '%" . $_SWIFT->Database->Escape(substr($_phoneNumber, $index)) . "'";
        }

        if (count($_phoneSQLContainer)) {
            $_phoneSQLExtended = " (" . implode(' OR ', $_phoneSQLContainer) . ") ";
        }

        $_SWIFT->Database->Query("SELECT userid FROM " . TABLE_PREFIX . "users WHERE " . $_phoneSQLExtended);
        while ($_SWIFT->Database->NextRecord()) {
            $_userIDList[] = $_SWIFT->Database->Record['userid'];
        }

        return $_userIDList;
    }

    /**
     * Process the Notification Rules
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ProcessNotifications()
    {
        chdir(SWIFT_BASEPATH);
        $_userID = $this->GetUserID();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (isset(self::$_notificationExecutionCache[$_userID]) && self::$_notificationExecutionCache[$_userID] == '1') {
            return true;
        }

        $this->NotificationManager->Trigger();

        self::$_notificationExecutionCache[$_userID] = 1;

        return true;
    }

    /**
     * Dispatch a Notification via Email on this User
     *
     * @author Varun Shoor
     *
     * @param int $_notificationType The Notification Type
     * @param array $_customEmailList The Custom Email List
     * @param string $_emailPrefix (OPTIONAL) The Custom Email Prefix
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function DispatchNotification($_notificationType, $_customEmailList, $_emailPrefix = '')
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_finalEmailPrefix = '';
        if (!empty($_emailPrefix)) {
            $_finalEmailPrefix = $_emailPrefix . ' - ';
        }

        $this->Notification->Dispatch($_notificationType, $_customEmailList, '', $this->GetProperty('fullname'), true, $_emailPrefix);

        return true;
    }

    /**
     * Dispatch a Notification via Pool (DESKTOP APP) on this User
     *
     * @author Varun Shoor
     *
     * @param int $_notificationType The Notification Type
     * @param array $_customStaffIDList The Custom Staff ID List
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function DispatchNotificationPool($_notificationType, $_customStaffIDList)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return true;
    }

    /**
     * Mark Profile Prompt as Done
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function MarkProfilePrompt()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UpdatePool('profileprompt', '1');
        $this->ProcessUpdatePool();

        return true;
    }

    /**
     * Retrieve the Permission for this User Group
     *
     * @author Varun Shoor
     *
     * @param string $_permission The Permission String
     *
     * @return int|bool "int" on Success, "true" otherwise
     */
    public static function GetPermission($_permission)
    {
        if (isset(self::$_permissionCache[$_permission])) {
            return self::$_permissionCache[$_permission];
        }

        return true;
    }

    /**
     * Get or Create a User ID based on given info
     *
     * @author Varun Shoor
     *
     * @param string $_fullName The User Full Name
     * @param string $_email the User Email
     * @param int $_userGroupID The User Group ID
     * @param string $_companyName
     * @param string $_phone
     * @param string $_website
     * @param string $_country
     * @param int|bool $_languageID (OPTIONAL) The Language ID
     * @param bool $_checkGeoIP (OPTIONAL) Check GeoIP for User
     *
     * @return SWIFT_User
     */
    public static function GetOrCreateUserID(
        $_fullName, $_email, $_userGroupID, $_companyName = '', $_phone = '', $_website = '', $_country = '', $_languageID = false, $_checkGeoIP = false)
    {
        // User processing.. no user specified?
        $_userIDFromEmail = SWIFT_UserEmail::RetrieveUserIDOnUserEmail($_email);

        $_SWIFT_UserObject = false;

        $_userID = false;
        if (!empty($_userIDFromEmail)) {
            $_userID = $_userIDFromEmail;
            $_SWIFT_UserObject = new SWIFT_User(new SWIFT_DataID($_userID));
        } else {
            $_SWIFT_UserObject = SWIFT_User::Create($_userGroupID, 0, SWIFT_User::SALUTATION_NONE, $_fullName, '', $_phone, true,
                0, array($_email), '', $_languageID, '', false, 0, 0, 0, true, true, $_checkGeoIP);

            $_userID = $_SWIFT_UserObject->GetUserID();
        }

        $_SWIFT_UserOrganizationObject = $_SWIFT_UserObject->GetOrganization();
        if ((!$_SWIFT_UserOrganizationObject instanceof SWIFT_UserOrganization || !$_SWIFT_UserOrganizationObject->GetIsClassLoaded()) && !empty($_companyName)) {
            $Organization = SWIFT_UserOrganization::Create($_companyName, SWIFT_UserOrganization::TYPE_RESTRICTED, array(), '', '', '', '', $_country, $_phone, '', $_website, 0, 0);
            $_SWIFT_UserObject->UpdateOrganization($Organization->GetID());
        }

        return $_SWIFT_UserObject;
    }

    /**
     * Retrieve the template group id for the user
     *
     * @author Varun Shoor
     * @return int Template Group ID
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetTemplateGroupID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_templateGroupCache = $this->Cache->Get('templategroupcache');

        $_userGroupID = $this->GetProperty('usergroupid');
        $_userTemplateGroupID = 0;

        foreach ($_templateGroupCache as $_templateGroupContainer) {
            if ($_templateGroupContainer['regusergroupid'] == $_userGroupID) {
                $_userTemplateGroupID = $_templateGroupContainer['tgroupid'];

                break;
            }
        }

        return $_userTemplateGroupID;
    }

    /**
     * Check to see if a given phone number already linked with other users.. if it does, return the relevant user id
     *
     * @author Parminder Singh
     *
     * @param string $_phoneNumber The Phone Number
     * @param int|bool $_currentUserID (OPTIONAL) The Current User ID to ignore
     *
     * @return array|bool User ID List
     */
    public static function CheckPhoneNumberExists($_phoneNumber, $_currentUserID = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_phoneNumber)) {
            return false;
        }

        $_SWIFT->Database->Query("SELECT userid, phone FROM " . TABLE_PREFIX . "users
            WHERE phone LIKE '" . $_SWIFT->Database->Escape($_phoneNumber) . "'");
        while ($_SWIFT->Database->NextRecord()) {
            if (!empty($_currentUserID) && $_SWIFT->Database->Record['userid'] == $_currentUserID) {
                // Belongs to the current user ignore...
            } else {
                return array($_SWIFT->Database->Record['phone'], $_SWIFT->Database->Record['userid']);
            }
        }

        return false;
    }
}
