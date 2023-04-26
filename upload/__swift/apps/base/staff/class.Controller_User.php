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

namespace Base\Staff;

use Base\Admin\Controller_Staff;
use Base\Library\CustomField\SWIFT_CustomFieldManager;
use Base\Library\CustomField\SWIFT_CustomFieldRendererStaff;
use Base\Library\ProfileImage\SWIFT_ProfileImage;
use Base\Library\Rules\SWIFT_Rules;
use Base\Library\Tag\SWIFT_TagCloud;
use Base\Library\User\SWIFT_UserPasswordPolicy;
use Base\Library\User\SWIFT_UserRenderManager;
use Base\Library\User\SWIFT_UserSearch;
use Base\Library\User\SWIFT_UsersMerge;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceGrid;
use Base\Models\CustomField\SWIFT_CustomFieldGroup;
use Base\Models\SearchStore\SWIFT_SearchStore;
use Base\Models\Staff\SWIFT_StaffActivityLog;
use Base\Models\Tag\SWIFT_Tag;
use Base\Models\Tag\SWIFT_TagLink;
use Base\Models\User\SWIFT_User;
use Base\Models\User\SWIFT_UserEmail;
use Base\Models\User\SWIFT_UserEmailManager;
use Base\Models\User\SWIFT_UserNote;
use Base\Models\User\SWIFT_UserOrganization;
use Base\Models\User\SWIFT_UserOrganizationLink;
use Base\Models\User\SWIFT_UserOrganizationNote;
use Base\Models\User\SWIFT_UserProfileImage;
use Base\Models\User\SWIFT_UserSetting;
use SWIFT;
use SWIFT_DataID;
use Parser\Models\EmailQueue\SWIFT_EmailQueue;
use SWIFT_Exception;
use SWIFT_Hook;
use SWIFT_Image_Exception;
use SWIFT_ImageResize;
use SWIFT_Interface;
use SWIFT_Loader;
use SWIFT_Model;
use SWIFT_Session;
use Tickets\Models\Ticket\SWIFT_TicketEmail;

/**
 * The User Controller
 *
 * @author Varun Shoor
 * @property Controller_User $Load
 * @property SWIFT_TagCloud $TagCloud
 * @property SWIFT_CustomFieldManager $CustomFieldManager
 * @property SWIFT_UserPasswordPolicy $UserPasswordPolicy
 * @property SWIFT_UserRenderManager $UserRenderManager
 * @property SWIFT_CustomFieldRendererStaff $CustomFieldRendererStaff
 * @property \SWIFT_TimeZoneContainer $TimeZoneContainer
 * @property View_User $View
 * @method Model(string $_modelName, array $_arguments, bool $_initiateInstance, $_customAppName, string $appName = '')
 * @method Method($v='', $_ticketID=0, $_listType=0, $_departmentID=0, $_ticketStatusID=0, $_ticketTypeID=0, $_ticketLimitOffset=0);
 * @method Library($_libraryName, $_arguments = [], $_initiateInstance = false, $_customAppName = '', $_appName = '')
 */
class Controller_User extends Controller_staff
{
    // Core Constants
    const MENU_ID = 8;
    const NAVIGATION_ID = 1;

    private $_userOrganizationLinkPool = [];

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @throws SWIFT_Exception
     */
    public function __construct()
    {
        $_SWIFT = SWIFT::GetInstance();

        parent::__construct();

        $this->Load->Library('User:UserRenderManager', [], true, false, 'base');
        $this->Load->Library('User:UserPasswordPolicy', [], true, false, 'base');

        $this->Load->Library('Misc:TimeZoneContainer');

        $this->Load->Library('CustomField:CustomFieldRendererStaff', [], true, false, 'base');
        $this->Load->Library('CustomField:CustomFieldManager', [], true, false, 'base');

        $this->Language->Load('staff_users');
        $this->Load->Library('User:UsersMerge', [], true, false, 'base');

        if ($_SWIFT->Staff->GetPermission('staff_canviewusers') == '0' && strtolower($_SWIFT->Router->GetAction()) != 'ajaxsearch' && strtolower($_SWIFT->Router->GetAction()) != 'quickinsert' && strtolower($_SWIFT->Router->GetAction()) != 'quickinsertsubmit') {
            throw new SWIFT_Exception(SWIFT_NOPERMISSION);
        }
    }

    /**
     * Delete the Users from Mass Action
     *
     * @author Varun Shoor
     * @param mixed $_userEmailIDList The User Email ID List Container Array
     * @param bool $_byPassCSRF Whether to bypass the CSRF check
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_userEmailIDList, $_byPassCSRF = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('staff_candeleteuser') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        $_userIDList = SWIFT_UserEmail::RetrieveUserIDListOnUserEmail($_userEmailIDList);

        if (_is_array($_userIDList)) {
            $_SWIFT->Database->Query("SELECT fullname FROM " . TABLE_PREFIX . "users WHERE userid IN (" . BuildIN($_userIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activitydeleteuser'), text_to_html_entities($_SWIFT->Database->Record['fullname'])), SWIFT_StaffActivityLog::ACTION_DELETE, SWIFT_StaffActivityLog::SECTION_USERS, SWIFT_StaffActivityLog::INTERFACE_STAFF);
            }

            // Begin Hook: staff_user_delete
            unset($_hookCode);
            ($_hookCode = SWIFT_Hook::Execute('staff_user_delete')) ? eval($_hookCode) : false;
            // End Hook

            // Remove emails from searching as well
            SWIFT_Loader::LoadModel('Ticket:TicketEmail', APP_TICKETS);
            SWIFT_TicketEmail::MaskSearchableOnIDList(SWIFT_TicketEmail::RetrieveIDListOnEmailList(SWIFT_UserEmail::RetrieveListOnUserIDList($_userIDList)));

            SWIFT_User::DeleteList($_userIDList);
        }

        return true;
    }

    /**
     * Enable a List of Users from Mass Action
     *
     * @author Varun Shoor
     * @param mixed $_userEmailIDList The User Email ID List Container Array
     * @return bool "true" on Success, "false" otherwise
     */
    public static function EnableList($_userEmailIDList, $_byPassCSRF = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('staff_canupdateuser') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        $_userIDList = SWIFT_UserEmail::RetrieveUserIDListOnUserEmail($_userEmailIDList);

        if (_is_array($_userIDList)) {
            $_finalUserIDList = array();

            $_finalText = '';
            $_index = 1;

            $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "users WHERE userid IN (" . BuildIN($_userIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                if ($_SWIFT->Database->Record['isenabled'] == '0') {
                    $_finalUserIDList[] = $_SWIFT->Database->Record['userid'];
                    $_finalText .= $_index . '. ' . text_to_html_entities($_SWIFT->Database->Record['fullname']) . "<BR />\n";

                    SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activityenableuser'), text_to_html_entities($_SWIFT->Database->Record['fullname'])), SWIFT_StaffActivityLog::ACTION_UPDATE, SWIFT_StaffActivityLog::SECTION_USERS, SWIFT_StaffActivityLog::INTERFACE_STAFF);

                    $_index++;
                }
            }

            if (!count($_finalUserIDList)) {
                return false;
            }

            SWIFT::Info(sprintf($_SWIFT->Language->Get('titleenableuser'), count($_finalUserIDList)), sprintf($_SWIFT->Language->Get('msgenableuser'), $_finalText));

            // Begin Hook: staff_user_enable
            unset($_hookCode);
            ($_hookCode = SWIFT_Hook::Execute('staff_user_enable')) ? eval($_hookCode) : false;
            // End Hook

            SWIFT_User::EnableList($_finalUserIDList);
        }

        return true;
    }

    /**
     * Disable a List of Users from Mass Action
     *
     * @author Varun Shoor
     * @param mixed $_userEmailIDList The User Email ID List Container Array
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DisableList($_userEmailIDList, $_byPassCSRF = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('staff_canupdateuser') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        $_userIDList = SWIFT_UserEmail::RetrieveUserIDListOnUserEmail($_userEmailIDList);

        if (_is_array($_userIDList)) {
            $_finalUserIDList = array();

            $_finalText = '';
            $_index = 1;

            $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "users WHERE userid IN (" . BuildIN($_userIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                if ($_SWIFT->Database->Record['isenabled'] == '1') {
                    $_finalUserIDList[] = $_SWIFT->Database->Record['userid'];
                    $_finalText .= $_index . '. ' . text_to_html_entities($_SWIFT->Database->Record['fullname']) . "<BR />\n";

                    SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activitydisableuser'), text_to_html_entities($_SWIFT->Database->Record['fullname'])), SWIFT_StaffActivityLog::ACTION_UPDATE, SWIFT_StaffActivityLog::SECTION_USERS, SWIFT_StaffActivityLog::INTERFACE_STAFF);

                    $_index++;
                }
            }

            if (!count($_finalUserIDList)) {
                return false;
            }

            SWIFT::Info(sprintf($_SWIFT->Language->Get('titledisableuser'), count($_finalUserIDList)), sprintf($_SWIFT->Language->Get('msgdisableuser'), $_finalText));

            // Begin Hook: staff_user_disable
            unset($_hookCode);
            ($_hookCode = SWIFT_Hook::Execute('staff_user_disable')) ? eval($_hookCode) : false;
            // End Hook

            SWIFT_User::DisableList($_finalUserIDList);
        }

        return true;
    }

    /**
     * Delete the Given User ID
     *
     * @author Varun Shoor
     * @param int $_userID The User ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete($_userID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array(SWIFT_UserEmail::RetrieveUserEmailID($_userID)), true);

        $this->Load->Manage();

        return true;
    }

    /**
     * Merge users
     *
     * @author Rajat Garg
     *
     * @param int $_primaryUserID
     * @param array $_secondaryUserIDList
     *
     * @return bool
     */
    public static function Merge($_primaryUserID, $_secondaryUserIDList)
    {
        if (!_is_array($_secondaryUserIDList)) {
            return false;
        }
        return SWIFT_UsersMerge::MergeUsers($_primaryUserID, $_secondaryUserIDList);
    }

    /**
     * Disable the Given User ID
     *
     * @author Varun Shoor
     * @param int $_userID The User ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Disable($_userID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DisableList(array(SWIFT_UserEmail::RetrieveUserEmailID($_userID)), true);

        $this->Load->Manage();

        return true;
    }

    /**
     * Enable the Given User ID
     *
     * @author Varun Shoor
     * @param int $_userID The User ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Enable($_userID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::EnableList(array(SWIFT_UserEmail::RetrieveUserEmailID($_userID)), true);

        $this->Load->Manage();

        return true;
    }

    /**
     * Displays the User Grid
     *
     * @author Varun Shoor
     * @param int|false $_searchStoreID The Search Store ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Index($_searchStoreID = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->Load->Manage($_searchStoreID);

        return true;
    }

    /**
     * Displays the User Grid
     *
     * @author Varun Shoor
     * @param int|false $_searchStoreID The Search Store ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Manage($_searchStoreID = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_LoadTagCloud();
        $this->_LoadDisplayData();

        $this->UserInterface->Header($this->Language->Get('users') . ' > ' . $this->Language->Get('manageusers'), self::MENU_ID, self::NAVIGATION_ID, $this->TagCloud->Render());

        if ($_SWIFT->Staff->GetPermission('staff_canviewusers') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->RenderGrid($_searchStoreID);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Runs the Checks for Insertion/Editing
     *
     * @author Varun Shoor
     * @param int $_mode The User Interface Mode
     * @param int $_userID (OPTIONAL) The User ID only for edit mode
     * @param bool $_isQuickInsert (OPTIONAL) Whether the user is being inserted using the quick insert option
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    private function RunChecks($_mode, $_userID = 0, $_isQuickInsert = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        $this->Load->Model('EmailQueue:EmailQueue', [], false, false, APP_PARSER);

        $_EmailQueueList = SWIFT_EmailQueue::RetrieveEmailofAllEmailQueues();

        $_match = array_intersect($this->_GetPOSTEmailContainer(), $_EmailQueueList);

        // BEGIN CSRF HASH CHECK

        if (!SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (trim($_POST['fullname']) == '' || trim($_POST['usergroupid']) == '') {
            $this->UserInterface->CheckFields('fullname', 'usergroupid');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            return false;
        } else if (!SWIFT_UserInterface::GetMultipleInputValues('emails') || !_is_array(SWIFT_UserInterface::GetMultipleInputValues('emails')) || !$this->_CheckPOSTEmailContainer()) {
            SWIFT::ErrorField('emails');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            return false;
        } else if (count($_match) >= 1) {
            SWIFT::ErrorField('emails');

            $this->UserInterface->Error($this->Language->Get('titleinvalidemail'), $this->Language->Get('msginvalidadditionalemail'));

            return false;
        } else if (SWIFT::Get('isdemo') == true) {
            $this->UserInterface->Error($this->Language->Get('titledemomode'), $this->Language->Get('msgdemomode'));

            return false;
        }

        if (isset($_POST['phone']) && !empty($_POST['phone'])) {
            $_userPhoneCheckResult = SWIFT_User::CheckPhoneNumberExists($_POST['phone'], $_userID);
            if ($_userPhoneCheckResult) {
                SWIFT::ErrorField('phone');

                $this->UserInterface->Error($this->Language->Get('titlephoneexists'), sprintf($this->Language->Get('msgphoneexists'), htmlspecialchars($_userPhoneCheckResult[0])));

                return false;
            }
        }

        $_customFieldCheckResult = $this->CustomFieldManager->Check(SWIFT_CustomFieldManager::MODE_POST, $_mode, array(SWIFT_CustomFieldGroup::GROUP_USER), SWIFT_CustomFieldManager::CHECKMODE_STAFF);
        if ($_customFieldCheckResult[0] == false) {
            call_user_func_array(array('SWIFT', 'ErrorField'), $_customFieldCheckResult[1]);

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            return false;
        }

        if (($_mode == SWIFT_UserInterface::MODE_INSERT && $_SWIFT->Staff->GetPermission('staff_caninsertuser') == '0') || ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT->Staff->GetPermission('staff_canupdateuser') == '0')) {
            $this->UserInterface->Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));

            return false;
        }

        $_userEmailCheckResult = SWIFT_UserEmail::CheckEmailRecordExists($this->_GetPOSTEmailContainer(), $_userID);
        if ($_userEmailCheckResult) {
            SWIFT::ErrorField('emails');

            $this->UserInterface->Error($this->Language->Get('titleemailexists'), sprintf($this->Language->Get('msgemailexists'), $_userEmailCheckResult[0], $_userEmailCheckResult[1]));

            return false;
        }

        if (isset($_FILES['profileimage']) && is_uploaded_file($_FILES['profileimage']['tmp_name'])) {
            $_pathInfoContainer = pathinfo($_FILES['profileimage']['name']);
            if (isset($_pathInfoContainer['extension']) && !empty($_pathInfoContainer['extension'])) {
                $_pathInfoContainer['extension'] = strtolower($_pathInfoContainer['extension']);

                if ($_pathInfoContainer['extension'] != 'gif' && $_pathInfoContainer['extension'] != 'png' && $_pathInfoContainer['extension'] != 'jpg' && $_pathInfoContainer['extension'] != 'jpeg') {
                    SWIFT::ErrorField('profileimage');

                    $this->UserInterface->Error($this->Language->Get('titleinvalidfileext'), $this->Language->Get('msginvalidfileext'));

                    return false;
                }
            }
        }

        $newOrganizationList = SWIFT_UserInterface::GetMultipleInputValues('organization');
        $newOrganizationList = false == $newOrganizationList ? [] : $newOrganizationList;

        // If the $_userID is not greater than zero then it is a new user, so $oldOrganizationList should be an empty list
        $oldOrganizationList = $_userID > 0 ? SWIFT_UserOrganizationLink::RetrieveListOnUser($_userID) : [];
        sort($oldOrganizationList);
        sort($newOrganizationList);
        if ($newOrganizationList != $oldOrganizationList && $_SWIFT->Staff->GetPermission('staff_canupdateuserorganization') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
            return false;
        } elseif (!empty($newOrgs = array_diff($newOrganizationList, $oldOrganizationList))) {
            foreach ($newOrgs as $_org) {
                if (empty(SWIFT_UserOrganization::RetrieveOnName($_org)) && $_SWIFT->Staff->GetPermission('staff_caninsertuserorganization') == '0') {
                    $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
                    return false;
                }
            }
        }

        // Begin Hook: staff_user_runchecks
        unset($_hookCode);
        $_hookResult = null;
        ($_hookCode = SWIFT_Hook::Execute('staff_user_runchecks')) ? ($_hookResult = eval($_hookCode)) : false;
        if ($_hookResult !== null)
            return $_hookResult;
        // End Hook

        return true;
    }

    /**
     * Process the Uploaded Profile Image
     *
     * @author Varun Shoor
     * @param SWIFT_Model $_SWIFT_UserObject The SWIFT_User Object Pointer
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    protected function _ProcessUploadedProfileImage(SWIFT_Model $_SWIFT_UserObject)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!$_SWIFT_UserObject instanceof SWIFT_User || !$_SWIFT_UserObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        /**
         * BUGFIX: KAYAKOC-2521 - Profile Image exploit in Kayako Classic
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
                SWIFT::Error($this->Language->Get('userprofileimage') ?: 'Profile Picture', $this->Language->Get('wrong_image_size'));
                return false;
            }
            $profileImage = SWIFT_UserProfileImage::RetrieveOnUser($_SWIFT_UserObject->GetUserID());
            SWIFT_UserProfileImage::DeleteOnUser(array($_SWIFT_UserObject->GetUserID()));

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
                    SWIFT::Error($this->Language->Get('userprofileimage'), $this->Language->Get('wrong_profile_image'));
                }

                if ($_fileContents) {
                    SWIFT_UserProfileImage::Create($_SWIFT_UserObject->GetUserID(), $_pathInfoContainer['extension'], $_fileContents);
                } else {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Insert a new User
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Insert()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_LoadDisplayData();

        $this->UserInterface->Header($this->Language->Get('users') . ' > ' . $this->Language->Get('insertuser'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('staff_caninsertuser') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render(SWIFT_UserInterface::MODE_INSERT);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Insert a new User
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function QuickInsert()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UserInterface->Header($this->Language->Get('users') . ' > ' . $this->Language->Get('insertuser'), self::MENU_ID, self::NAVIGATION_ID);
        $this->View->Render(SWIFT_UserInterface::MODE_INSERT, null, true);
        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Render the Confirmation for InsertSubmit/EditSubmit
     *
     * @author Varun Shoor
     * @param mixed $_mode The User Interface Mode
     * @param SWIFT_User $_SWIFT_UserObject The SWIFT_User object pointer class
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    private function _RenderConfirmation($_mode, SWIFT_User $_SWIFT_UserObject)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            $_type = 'update';
        } else {
            $_type = 'insert';
        }

        $_userGroupCache = $this->Cache->Get('usergroupcache');

        $_userOrganizationName = $_SWIFT_UserObject->GetOrganizationName();

        $_finalText = '';

        if ($_userOrganizationName) {
            $_finalText .= '<b>' . $this->Language->Get('userorganization') . ':</b> <i class="fa fa-institution" aria-hidden="true"></i> ' . htmlspecialchars($_userOrganizationName) . '<br />';
        }

        $_finalText .= '<b>' . $this->Language->Get('fullname') . ':</b> ' . text_to_html_entities($_SWIFT_UserObject->GetFullName(true)) . '<br />';

        if ($_SWIFT_UserObject->GetProperty('userdesignation') != '') {
            $_finalText .= '<b>' . $this->Language->Get('userdesignation') . ':</b> ' . htmlspecialchars($_SWIFT_UserObject->GetProperty('userdesignation')) . '<br />';
        }

        $_finalEmailList = array();
        $_userEmailList = $_SWIFT_UserObject->GetEmailList();

        if (_is_array($_userEmailList)) {
            foreach ($_userEmailList as $_key => $_val) {
                $_finalEmailList[] = htmlspecialchars($_val);
            }
        }

        $_finalText .= '<b>' . $this->Language->Get('useremails') . ':</b> ' . implode(', ', $_finalEmailList) . '<br />';

        if ($_SWIFT_UserObject->GetProperty('phone') != '') {
            $_finalText .= '<b>' . $this->Language->Get('userphone') . ':</b> ' . htmlspecialchars($_SWIFT_UserObject->GetProperty('phone')) . '<br />';
        }

        if (isset($_userGroupCache[$_SWIFT_UserObject->GetProperty('usergroupid')])) {
            $_finalText .= '<b>' . $this->Language->Get('usergroup') . ':</b> ' . htmlspecialchars($_userGroupCache[$_SWIFT_UserObject->GetProperty('usergroupid')]['title']) . '<br />';
        }

        SWIFT::Info(sprintf($this->Language->Get('titleuser' . $_type), text_to_html_entities($_SWIFT_UserObject->GetProperty('fullname'))), sprintf($this->Language->Get('msguser' . $_type), text_to_html_entities($_SWIFT_UserObject->GetProperty('fullname'))) . '<br />' . $_finalText);

        return true;
    }

    /**
     * Retrieve the relevant user organization id from $_POST
     **
     * @author Varun Shoor
     * @param SWIFT_User|null $_SWIFT_UserObject the user object
     * @return mixed "_userOrganizationID" (INT) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function _GetPOSTUserOrganizationID(SWIFT_User $_SWIFT_UserObject = null)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_userOrganizationID = false; // primary organization
        $_userOrganizationList = SWIFT_UserInterface::GetMultipleInputValues('organization');

        /**
         * Feature/KAYAKOC-5038 - Create UserOrganizationLinks
         *
         * @author Werner Garcia
         */

        // delete current organizations
        if ($_SWIFT_UserObject) {
            SWIFT_UserOrganizationLink::DeleteOnUser([$_SWIFT_UserObject->GetUserID()]);
        }

        $this->_userOrganizationLinkPool = [];

        foreach ($_userOrganizationList as $orgName) {
            $orgs = SWIFT_UserOrganization::RetrieveOnName($orgName);
            $uid = false;
            if (empty($orgs)) {
                // create organization
                $_SWIFT_UserOrganizationObject = SWIFT_UserOrganization::Create($orgName, SWIFT_UserOrganization::TYPE_RESTRICTED);
                $uid = $_SWIFT_UserOrganizationObject->GetUserOrganizationID();
                if ($_userOrganizationID === false) {
                    $_userOrganizationID = $uid;
                }
            } else {
                $org = array_shift($orgs); // take the first org object
                $uid = $org['userorganizationid'];
                if ($_userOrganizationID === false) {
                    $_userOrganizationID = $uid;
                }
            }

            if ($uid) {
                if ($_SWIFT_UserObject && !SWIFT_UserOrganizationLink::LinkExists($uid,
                        $_SWIFT_UserObject->GetUserID())) {
                    SWIFT_UserOrganizationLink::Create($_SWIFT_UserObject, $uid);
                } else {
                    // save it and create the link later
                    $this->_userOrganizationLinkPool[] = $uid;
                }
            }
        }

        return $_userOrganizationID;
    }

    /**
     * Check validity of emails in  the input box
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function _CheckPOSTEmailContainer()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_emailCount = 0;

        $_postEmailValues = SWIFT_UserInterface::GetMultipleInputValues('emails');
        if (_is_array($_postEmailValues)) {
            foreach ($_postEmailValues as $_key => $_val) {
                if (!IsEmailValid($_val)) {
                    return false;
                } else {
                    $_emailCount++;
                }
            }
        }

        if (!$_emailCount) {
            return false;
        }

        return true;
    }

    /**
     * Retrieve the processed email container from POST
     *
     * @author Varun Shoor
     * @return mixed "_emailContainer" (ARRAY) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function _GetPOSTEmailContainer()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_emailContainer = array();

        $_postEmailValues = SWIFT_UserInterface::GetMultipleInputValues('emails');
        if (_is_array($_postEmailValues)) {
            foreach ($_postEmailValues as $_key => $_val) {
                if (IsEmailValid($_val)) {
                    $_emailContainer[] = $_val;
                }
            }
        }

        return $_emailContainer;
    }

    /**
     * Insert Submission Processor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function InsertSubmit()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->RunChecks(SWIFT_UserInterface::MODE_INSERT)) {

            $_languageID = $_POST['languageid'];
            if ($_languageID == 0) {
                $_languageID = $_SWIFT->TemplateGroup->GetProperty('languageid');
            }
            $_SWIFT_UserObject = SWIFT_User::Create($_POST['usergroupid'], $this->_GetPOSTUserOrganizationID(), $_POST['salutation'], $_POST['fullname'], $_POST['userdesignation'], $_POST['phone'], $_POST['isenabled'], $_POST['userrole'], $this->_GetPOSTEmailContainer(), substr(BuildHash(), 0, 12), $_languageID, $_POST['timezonephp'], $_POST['enabledst'], $_POST['slaplanid'], GetCalendarDateline($_POST['slaexpirytimeline']), GetCalendarDateline($_POST['userexpirytimeline']), true, true);

            $this->createOrganizationLinksForUser($_SWIFT_UserObject);

            /*
              * BUG FIX - Pankaj Garg
              *
              * SWIFT-1683, In case of multiple email addresses for user account, there should be an option under User account to be enabled for sending ticket updates to all the email addresses
              */
            if (isset($_POST['sendemailtoall'])) {
                SWIFT_UserSetting::Replace($_SWIFT_UserObject->GetID(), 'sendemailtoall', $_POST['sendemailtoall']);
            }

            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityinsertuser'), text_to_html_entities($_POST['fullname'])), SWIFT_StaffActivityLog::ACTION_INSERT, SWIFT_StaffActivityLog::SECTION_USERS, SWIFT_StaffActivityLog::INTERFACE_STAFF);

            if (!$_SWIFT_UserObject instanceof SWIFT_User || !$_SWIFT_UserObject->GetIsClassLoaded()) {
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            }

            // Update the Profile Image
            $this->_ProcessUploadedProfileImage($_SWIFT_UserObject);

            // Process the Tags
            if ($_SWIFT->Staff->GetPermission('staff_canupdatetags') != '0') {
                SWIFT_Tag::Process(SWIFT_TagLink::TYPE_USER, $_SWIFT_UserObject->GetUserID(), SWIFT_UserInterface::GetMultipleInputValues('tags'), $_SWIFT->Staff->GetStaffID());
            }

            // Add notes
            if (trim($_POST['usernotes']) != '') {
                SWIFT_UserNote::Create($_SWIFT_UserObject, $_POST['usernotes'], (int)($_POST['notecolor_usernotes']));
            }

            // Update Custom Field Values
            $this->CustomFieldManager->Update(SWIFT_CustomFieldManager::MODE_POST, SWIFT_UserInterface::MODE_INSERT, array(SWIFT_CustomFieldGroup::GROUP_USER), SWIFT_CustomFieldManager::CHECKMODE_STAFF, $_SWIFT_UserObject->GetUserID());

            // Begin Hook: staff_user_insert
            unset($_hookCode);
            ($_hookCode = SWIFT_Hook::Execute('staff_user_insert')) ? eval($_hookCode) : false;
            // End Hook

            $this->_RenderConfirmation(SWIFT_UserInterface::MODE_INSERT, $_SWIFT_UserObject);

            $this->Load->Manage();

            return true;
        }

        $this->Load->Insert();

        return false;
    }

    /**
     * Insert Submission Processor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function QuickInsertSubmit()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->RunChecks(SWIFT_UserInterface::MODE_INSERT, 0, true)) {
            $_SWIFT_UserObject = SWIFT_User::Create($_POST['usergroupid'], $this->_GetPOSTUserOrganizationID(), $_POST['salutation'], $_POST['fullname'], $_POST['userdesignation'], $_POST['phone'], true, $_POST['userrole'], $this->_GetPOSTEmailContainer(), substr(BuildHash(), 0, 12), $_SWIFT->TemplateGroup->GetProperty('languageid'), '', false, $_POST['slaplanid'], GetCalendarDateline($_POST['slaexpirytimeline']), 0, true, true);

            $this->createOrganizationLinksForUser($_SWIFT_UserObject);

            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityinsertuser'), text_to_html_entities($_POST['fullname'])), SWIFT_StaffActivityLog::ACTION_INSERT, SWIFT_StaffActivityLog::SECTION_USERS, SWIFT_StaffActivityLog::INTERFACE_STAFF);

            if (!$_SWIFT_UserObject instanceof SWIFT_User || !$_SWIFT_UserObject->GetIsClassLoaded()) {
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            }

            // Process the Tags
            if ($_SWIFT->Staff->GetPermission('staff_canupdatetags') != '0') {
                SWIFT_Tag::Process(SWIFT_TagLink::TYPE_USER, $_SWIFT_UserObject->GetUserID(), SWIFT_UserInterface::GetMultipleInputValues('tags'), $_SWIFT->Staff->GetStaffID());
            }

            // Update Custom Field Values
            $this->CustomFieldManager->Update(SWIFT_CustomFieldManager::MODE_POST, SWIFT_UserInterface::MODE_INSERT, array(SWIFT_CustomFieldGroup::GROUP_USER), SWIFT_CustomFieldManager::CHECKMODE_STAFF, $_SWIFT_UserObject->GetUserID());

            $_outputHTML = '<script type="text/javascript">';
            $_outputHTML .= 'if (window.$UIObject) { window.$UIObject.Queue(function(){';

            $_outputHTML .= "$('#userid').val('" . text_to_html_entities($_POST['fullname']) . "');";
            $_outputHTML .= "$('#autocomplete_userid').val('" . $_SWIFT_UserObject->GetUserID() . "');";

            $_outputHTML .= '}); }</script>';

            echo $_outputHTML;

            return true;
        }

        foreach (SWIFT::GetErrorContainer() as $_key => $_val) {
            echo '<div class="errordiv">' . $_val['message'] . '</div>';
        }

        return false;
    }

    /**
     * Edit the User ID
     *
     * @author Varun Shoor
     * @param int $_userID The User ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Edit($_userID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_userID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_UserObject = new SWIFT_User(new SWIFT_DataID($_userID));
        if (!$_SWIFT_UserObject instanceof SWIFT_User || !$_SWIFT_UserObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->View->RenderInfoBox($_SWIFT_UserObject);

        $this->_LoadDisplayData();

        if ($_SWIFT_UserObject->GetProperty('isvalidated') != '1') {
            SWIFT::Alert($this->Language->Get('titlevalidationpen'), $this->Language->Get('msgvalidatationpen'));
        }

        $this->UserInterface->Header(sprintf($this->Language->Get('edituserext'), text_to_html_entities($_SWIFT_UserObject->GetProperty('fullname'))), self::MENU_ID, self::NAVIGATION_ID);

//        if ($_SWIFT->Staff->GetPermission('staff_canupdateuser') == '0')
//        {
//            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
//        } else {
        $this->View->Render(SWIFT_UserInterface::MODE_EDIT, $_SWIFT_UserObject);
//        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Edit Submission Processor
     *
     * @author Varun Shoor
     * @param int $_userID The User ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function EditSubmit($_userID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_userID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_UserObject = new SWIFT_User(new SWIFT_DataID($_userID));
        if (!$_SWIFT_UserObject instanceof SWIFT_User || !$_SWIFT_UserObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if ($this->RunChecks(SWIFT_UserInterface::MODE_EDIT, $_SWIFT_UserObject->GetUserID())) {
            $_updateResult = $_SWIFT_UserObject->Update($_POST['usergroupid'], $this->_GetPOSTUserOrganizationID($_SWIFT_UserObject), $_POST['salutation'], $_POST['fullname'], $_POST['userdesignation'], $_POST['phone'], $_POST['isenabled'], $_POST['userrole'], $this->_GetPOSTEmailContainer(), $_POST['languageid'], $_POST['timezonephp'], $_POST['enabledst'], $_POST['slaplanid'], GetCalendarDateline($_POST['slaexpirytimeline']), GetCalendarDateline($_POST['userexpirytimeline']));

            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityupdateuser'), text_to_html_entities($_POST['fullname'])), SWIFT_StaffActivityLog::ACTION_UPDATE, SWIFT_StaffActivityLog::SECTION_USERS, SWIFT_StaffActivityLog::INTERFACE_STAFF);

            if (!$_updateResult) {
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            }

            /*
              * BUG FIX - Pankaj Garg
              *
              * SWIFT-1683, In case of multiple email addresses for user account, there should be an option under User account to be enabled for sending ticket updates to all the email addresses
              */
            if (isset($_POST['sendemailtoall'])) {
                SWIFT_UserSetting::Replace($_SWIFT_UserObject->GetID(), 'sendemailtoall', $_POST['sendemailtoall']);
            }

            // Update the Profile Image
            $this->_ProcessUploadedProfileImage($_SWIFT_UserObject);

            // Process the Tags
            if ($_SWIFT->Staff->GetPermission('staff_canupdatetags') != '0') {
                SWIFT_Tag::Process(SWIFT_TagLink::TYPE_USER, $_SWIFT_UserObject->GetUserID(), SWIFT_UserInterface::GetMultipleInputValues('tags'), $_SWIFT->Staff->GetStaffID());
            }

            // Process Organization Tags if Available
            if (SWIFT_UserInterface::GetMultipleInputValues('organizationtags')) {
                if ($_SWIFT_UserObject->GetProperty('userorganizationid')) {
                    try {
                        $_SWIFT_UserOrganizationObject = new SWIFT_UserOrganization($_SWIFT_UserObject->GetProperty('userorganizationid'));
                        if ($_SWIFT_UserOrganizationObject instanceof SWIFT_UserOrganization && $_SWIFT_UserOrganizationObject->GetIsClassLoaded() && $_SWIFT->Staff->GetPermission('staff_canupdatetags') != '0') {
                            SWIFT_Tag::Process(SWIFT_TagLink::TYPE_USERORGANIZATION, $_SWIFT_UserOrganizationObject->GetUserOrganizationID(), SWIFT_UserInterface::GetMultipleInputValues('organizationtags'), $_SWIFT->Staff->GetStaffID());
                        }
                    } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {

                    }
                }
            }

            // Update Custom Field Values
            $this->CustomFieldManager->Update(SWIFT_CustomFieldManager::MODE_POST, SWIFT_UserInterface::MODE_EDIT, array(SWIFT_CustomFieldGroup::GROUP_USER), SWIFT_CustomFieldManager::CHECKMODE_STAFF, $_SWIFT_UserObject->GetUserID());

            // Begin Hook: staff_user_update
            unset($_hookCode);
            ($_hookCode = SWIFT_Hook::Execute('staff_user_update')) ? eval($_hookCode) : false;
            // End Hook

            // Render the confirmation dialogs..
            $this->_RenderConfirmation(SWIFT_UserInterface::MODE_EDIT, $_SWIFT_UserObject);

            $this->Load->Manage();

            return true;
        }

        $this->Load->Edit($_SWIFT_UserObject->GetUserID());

        return false;
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
        }

        if (empty($_userID) || !is_numeric($_userID)) {
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
     * Change the User Password
     *
     * @author Varun Shoor
     * @param int $_userID The User ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ChangePassword($_userID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_userID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_UserObject = new SWIFT_User(new SWIFT_DataID($_userID));
        if (!$_SWIFT_UserObject instanceof SWIFT_User || !$_SWIFT_UserObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->UserInterface->Header($this->Language->Get('users') . ' > ' . $this->Language->Get('changepassword'), self::MENU_ID, self::NAVIGATION_ID);
        $this->View->RenderChangePasswordForm($_SWIFT_UserObject);
        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Change the User Password (SUBMISSION PROCESSOR)
     *
     * @author Varun Shoor
     * @param int $_userID The User ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ChangePasswordSubmit($_userID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_userID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_UserObject = new SWIFT_User(new SWIFT_DataID($_userID));
        if (!$_SWIFT_UserObject instanceof SWIFT_User || !$_SWIFT_UserObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if (trim($_POST['password']) == '' || trim($_POST['passwordagain']) == '') {
            $this->UserInterface->CheckFields('password', 'passwordagain');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            $this->Load->ChangePassword($_userID);

            return false;

        } else if ($_POST['password'] != $_POST['passwordagain']) {
            SWIFT::ErrorField('password', 'passwordagain');

            $this->UserInterface->Error($this->Language->Get('titlepwnomatch'), $this->Language->Get('msgpwnomatch'));

            $this->Load->ChangePassword($_userID);

            return false;

        } else if (!empty($_POST['password']) && !$this->UserPasswordPolicy->Check($_POST['password'])) {
            SWIFT::ErrorField('password');
            SWIFT::ErrorField('passwordagain');

            $this->UserInterface->Error($this->Language->Get('titlepwpolicy'), $this->Language->Get('msgpwpolicy') . ' ' . $this->UserPasswordPolicy->GetPasswordPolicyString());

            $this->Load->ChangePassword($_userID);

            return false;

        } else if ($_SWIFT->Staff->GetPermission('staff_canupdateuser') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));

            return false;
        }

        $_SWIFT_UserObject->UpdatePassword($_POST['password']);

        SWIFT::Info($this->Language->Get('titlepwupdated'), sprintf($this->Language->Get('msgpwupdated'), text_to_html_entities($_SWIFT_UserObject->GetProperty('fullname'))));

        $this->Load->Edit($_userID);

        return true;
    }

    /**
     * Generate the User Password and Email it
     *
     * @author Varun Shoor
     * @param int $_userID The User ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GeneratePassword($_userID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_userID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_UserObject = new SWIFT_User(new SWIFT_DataID($_userID));
        if (!$_SWIFT_UserObject instanceof SWIFT_User || !$_SWIFT_UserObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_UserObject->GeneratePassword();

        SWIFT::Info($this->Language->Get('titlepwgenerated'), sprintf($this->Language->Get('msgpwgenerated'), text_to_html_entities($_SWIFT_UserObject->GetProperty('fullname'))));

        $this->Load->Edit($_userID);

        return true;
    }

    /**
     * Add a note
     *
     * @author Varun Shoor
     * @param int $_userID The User ID
     * @param bool $_isOrganizationNote Whether this is an Organization Note
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function AddNote($_userID, $_isOrganizationNote = false)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_userID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_UserObject = new SWIFT_User(new SWIFT_DataID($_userID));
        if (!$_SWIFT_UserObject instanceof SWIFT_User || !$_SWIFT_UserObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_finalIsOrganizationNote = false;
        if ($_SWIFT_UserObject->GetProperty('userorganizationid') && $_isOrganizationNote == true) {
            try {
                $_SWIFT_UserOrganizationObject = new SWIFT_UserOrganization($_SWIFT_UserObject->GetProperty('userorganizationid'));
                if ($_SWIFT_UserOrganizationObject instanceof SWIFT_UserOrganization && $_SWIFT_UserOrganizationObject->GetIsClassLoaded()) {
                    $_finalIsOrganizationNote = true;
                }
            } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {

            }
        }

        $this->UserInterface->Header($this->Language->Get('users') . ' > ' . $this->Language->Get('addnote'), self::MENU_ID, self::NAVIGATION_ID);
        $this->View->RenderNoteForm(SWIFT_UserInterface::MODE_INSERT, $_SWIFT_UserObject, null, $_finalIsOrganizationNote);
        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Add a Note Submit Processer
     *
     * @author Varun Shoor
     * @param int $_userID The User ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function AddNoteSubmit($_userID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_userID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_UserObject = new SWIFT_User(new SWIFT_DataID($_userID));
        if (!$_SWIFT_UserObject instanceof SWIFT_User || !$_SWIFT_UserObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_isOrganizationNote = false;
        $_SWIFT_UserOrganizationObject = false;

        if ($_SWIFT_UserObject->GetProperty('userorganizationid') && isset($_POST['isorganizationnote']) && $_POST['isorganizationnote'] == '1') {
            try {
                $_SWIFT_UserOrganizationObject = new SWIFT_UserOrganization($_SWIFT_UserObject->GetProperty('userorganizationid'));
                if ($_SWIFT_UserOrganizationObject instanceof SWIFT_UserOrganization && $_SWIFT_UserOrganizationObject->GetIsClassLoaded()) {
                    $_isOrganizationNote = true;
                }
            } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {

            }
        }

        // Add notes
        if (trim($_POST['usernotes']) != '') {
            if ($_isOrganizationNote) {
                SWIFT_UserOrganizationNote::Create($_SWIFT_UserOrganizationObject, $_POST['usernotes'], (int)($_POST['notecolor_usernotes']));
            } else {
                SWIFT_UserNote::Create($_SWIFT_UserObject, $_POST['usernotes'], (int)($_POST['notecolor_usernotes']));
            }
        }

        echo $this->View->RenderUserNotes($_SWIFT_UserObject);

        return true;
    }

    /**
     * Edit a note
     *
     * @author Varun Shoor
     * @param int $_userID The User ID
     * @param int $_userNoteID The User Note ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function EditNote($_userID, $_userNoteID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_userID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if ($_SWIFT->Staff->GetPermission('staff_canupdateusernote') == '0') {
            return false;
        }

        $_SWIFT_UserObject = new SWIFT_User(new SWIFT_DataID($_userID));
        if (!$_SWIFT_UserObject instanceof SWIFT_User || !$_SWIFT_UserObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_UserNoteObject = new SWIFT_UserNote($_userNoteID);
        if (!$_SWIFT_UserNoteObject instanceof SWIFT_UserNote || !$_SWIFT_UserNoteObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->UserInterface->Header($this->Language->Get('users') . ' > ' . $this->Language->Get('editnote'), self::MENU_ID, self::NAVIGATION_ID);
        $this->View->RenderNoteForm(SWIFT_UserInterface::MODE_EDIT, $_SWIFT_UserObject, $_SWIFT_UserNoteObject);
        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Edit a note processor
     *
     * @author Varun Shoor
     * @param int $_userID The User ID
     * @param int $_userNoteID The User Note ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function EditNoteSubmit($_userID, $_userNoteID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_userID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if ($_SWIFT->Staff->GetPermission('staff_canupdateusernote') == '0') {
            return false;
        }

        $_SWIFT_UserObject = new SWIFT_User(new SWIFT_DataID($_userID));
        if (!$_SWIFT_UserObject instanceof SWIFT_User || !$_SWIFT_UserObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_UserNoteObject = new SWIFT_UserNote($_userNoteID);
        if (!$_SWIFT_UserNoteObject instanceof SWIFT_UserNote || !$_SWIFT_UserNoteObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        // Add notes
        if (trim($_POST['usernotes']) != '') {
            $_SWIFT_UserNoteObject->Update($_SWIFT->Staff->GetStaffID(), $_SWIFT->Staff->GetProperty('fullname'), $_POST['usernotes'], (int)($_POST['notecolor_usernotes']));
        }

        echo $this->View->RenderUserNotes($_SWIFT_UserObject);

        return true;
    }

    /**
     * Delete Note Processer
     *
     * @author Varun Shoor
     * @param int $_userID The User ID
     * @param int $_userNoteID The User Note ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function DeleteNote($_userID, $_userNoteID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_userID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if ($_SWIFT->Staff->GetPermission('staff_candeleteusernote') == '0') {
            return false;
        }

        $_SWIFT_UserObject = new SWIFT_User(new SWIFT_DataID($_userID));
        if (!$_SWIFT_UserObject instanceof SWIFT_User || !$_SWIFT_UserObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_UserNoteObject = new SWIFT_UserNote($_userNoteID);
        if (!$_SWIFT_UserNoteObject instanceof SWIFT_UserNote || !$_SWIFT_UserNoteObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_UserNoteObject->Delete();

        echo $this->View->RenderUserNotes($_SWIFT_UserObject);

        return true;
    }

    /**
     * Search processor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function SearchSubmit()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($_SWIFT->Staff->GetPermission('staff_canviewusers') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));

            $this->Load->Manage();

            return false;
        }

        $_gridSortContainer = SWIFT_UserInterfaceGrid::GetGridSortField('usergrid', 'useremails.email', 'asc');

        $_fieldPointer = SWIFT_UserSearch::GetFieldPointer();
        $_sqlContainer = array();

        if (_is_array($_POST['rulecriteria'])) {
            foreach ($_POST['rulecriteria'] as $_key => $_val) {
                if (!isset($_fieldPointer[$_val[0]])) {
                    continue;
                }

                // Is it date type?
                if ($_val[0] == SWIFT_UserSearch::USERSEARCH_DATEREGISTERED || $_val[0] == SWIFT_UserSearch::USERSEARCH_DATELASTUPDATE || $_val[0] == SWIFT_UserSearch::USERSEARCH_DATELASTVISIT || $_val[0] == SWIFT_UserSearch::USERSEARCH_USEREXPIRY || $_val[0] == SWIFT_UserSearch::USERSEARCH_SLAEXPIRY) {
                    if (empty($_val[2])) {
                        $_val[2] = DATENOW;
                    } else {
                        $_val[2] = GetCalendarDateline($_val[2]);
                    }
                }

                // Make sure its not a date range..
                if ($_val[0] == SWIFT_UserSearch::USERSEARCH_DATEREGISTEREDRANGE || $_val[0] == SWIFT_UserSearch::USERSEARCH_DATELASTUPDATERANGE || $_val[0] == SWIFT_UserSearch::USERSEARCH_DATELASTVISITRANGE) {
                    $_sqlContainer[] = SWIFT_Rules::BuildSQLDateRange($_fieldPointer[$_val[0]], $_val[2]);
                } else {
                    $_sqlContainer[] = SWIFT_Rules::BuildSQL($_fieldPointer[$_val[0]], $_val[1], $_val[2]);
                }
            }
        }

        if ($_POST['criteriaoptions'] == SWIFT_Rules::RULE_MATCHALL) {
            $_filterJoiner = ' AND ';
        } else {
            $_filterJoiner = ' OR ';
        }

        $_userIDList = array();
        $this->Database->QueryLimit("SELECT users.userid FROM " . TABLE_PREFIX . "useremails AS useremails
            LEFT JOIN " . TABLE_PREFIX . "users AS users ON (useremails.linktypeid = users.userid)
            LEFT JOIN " . TABLE_PREFIX . "userorganizations AS userorganizations ON (users.userorganizationid = userorganizations.userorganizationid)
            LEFT JOIN " . TABLE_PREFIX . "usergroups AS usergroups ON (users.usergroupid = usergroups.usergroupid)
            WHERE useremails.linktype = '" . SWIFT_UserEmail::LINKTYPE_USER . "'
                AND (" . implode($_filterJoiner, $_sqlContainer) . ')', 100);
        while ($this->Database->NextRecord()) {
            $_userIDList[] = $this->Database->Record['userid'];
        }

        $_userEmailIDList = array();
        $this->Database->Query("SELECT useremails.useremailid FROM " . TABLE_PREFIX . "useremails AS useremails
            WHERE useremails.linktype = '" . SWIFT_UserEmail::LINKTYPE_USER . "'
                AND useremails.linktypeid IN (" . BuildIN($_userIDList) . ")");
        while ($this->Database->NextRecord()) {
            $_userEmailIDList[] = $this->Database->Record['useremailid'];
        }

        SWIFT_SearchStore::DeleteOnType(SWIFT_SearchStore::TYPE_USERS, $_SWIFT->Staff->GetStaffID());

        $_searchStoreID = SWIFT_SearchStore::Create($_SWIFT->Session->GetSessionID(), SWIFT_SearchStore::TYPE_USERS, $_userEmailIDList, $_SWIFT->Staff->GetStaffID());
        if (!_is_array($_userEmailIDList)) {
            SWIFT::Alert($this->Language->Get('titlesearchfailed'), $this->Language->Get('msgsearchfailed'));
        }

        $this->Load->Manage($_searchStoreID);

        return true;
    }

    /**
     * Searches using Auto Complete
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function AjaxSearch()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($_POST['q']) || empty($_POST['q'])) {
            return false;
        }

        $_emailContainer = $_emailMap = array();

        $_userIDList = array();
        $this->Database->QueryLimit("SELECT useremails.linktypeid
            FROM " . TABLE_PREFIX . "useremails AS useremails
            WHERE ((" . BuildSQLSearch('useremails.email', $_POST['q'], false, false) . "))
                AND useremails.linktype = '" . SWIFT_UserEmailManager::LINKTYPE_USER . "'", 6);
        while ($this->Database->NextRecord()) {
            $_userIDList[] = $this->Database->Record['linktypeid'];
        }

        $this->Database->QueryLimit("SELECT users.userid FROM " . TABLE_PREFIX . "users AS users
            WHERE ((" . BuildSQLSearch('users.fullname', $_POST['q'], false, false) . ")
                OR (" . BuildSQLSearch('users.phone', $_POST['q'], false, false) . "))", 6);
        while ($this->Database->NextRecord()) {
            if (!in_array($this->Database->Record['userid'], $_userIDList)) {
                $_userIDList[] = $this->Database->Record['userid'];
            }
        }

        $_userOrganizationIDList = array();
        $this->Database->QueryLimit("SELECT userorganizations.userorganizationid
            FROM " . TABLE_PREFIX . "userorganizations AS userorganizations
            WHERE ((" . BuildSQLSearch('userorganizations.organizationname', $_POST['q'], false, false) . ")
                OR (" . BuildSQLSearch('userorganizations.address', $_POST['q'], false, false) . ")
                OR (" . BuildSQLSearch('userorganizations.phone', $_POST['q'], false, false) . ")
                )", 6);
        while ($this->Database->NextRecord()) {
            $_userOrganizationIDList[] = $this->Database->Record['userorganizationid'];
        }

        if (count($_userOrganizationIDList)) {
            $this->Database->QueryLimit("SELECT users.userid
                FROM " . TABLE_PREFIX . "users AS users
                WHERE users.userorganizationid IN (" . BuildIN($_userOrganizationIDList) . ")", 6);
            while ($this->Database->NextRecord()) {
                if (!in_array($this->Database->Record['userid'], $_userIDList)) {
                    $_userIDList[] = $this->Database->Record['userid'];
                }
            }
        }


        $this->Database->QueryLimit("SELECT useremails.*, users.fullname, users.phone AS userphone, users.userid, userorganizations.organizationname, userorganizations.phone AS organizationphone, userorganizations.address, userorganizations.city, userorganizations.state, userorganizations.country
            FROM " . TABLE_PREFIX . "useremails AS useremails
            LEFT JOIN " . TABLE_PREFIX . "users AS users ON (useremails.linktypeid = users.userid)
            LEFT JOIN " . TABLE_PREFIX . "userorganizations AS userorganizations ON (users.userorganizationid = userorganizations.userorganizationid)
            WHERE useremails.linktype = '" . SWIFT_UserEmailManager::LINKTYPE_USER . "' AND useremails.linktypeid IN (" . BuildIN($_userIDList) . ")", 6);
        while ($this->Database->NextRecord()) {
            if (in_array($this->Database->Record['email'], $_emailContainer)) {
                continue;
            }

            $_emailContainer[] = $this->Database->Record['email'];
            $_emailMap[$this->Database->Record['email']] = $this->Database->Record;

            if (isset($this->Database->Record['userid']) && !empty($this->Database->Record['userid'])) {
                $_emailMap['userid'] = $this->Database->Record['userid'];
            } else {
                $_emailMap['userid'] = $this->Database->Record['linktypeid'];
            }
        }

        sort($_emailContainer);

        foreach ($_emailContainer as $_emailAddress) {
            $_emailMapLink = $_emailMap[$_emailAddress];

            $_finalDisplayText = '';

            // Varun Shoor (Kayako Infotech Ltd.)
            // 2nd Floor, Midas Corporate Park
            // Jalandhar, Punjab, India
            // Organization Phone: +91 181xxx
            // User Phone: +91 xxx
            if (isset($_emailMapLink['fullname']) && !empty($_emailMapLink['fullname'])) {
                $_finalDisplayText .= text_to_html_entities($_emailMapLink['fullname']);
            }

            if (isset($_emailMapLink['organizationname']) && !empty($_emailMapLink['organizationname'])) {
                $_finalDisplayText .= ' (' . text_to_html_entities($_emailMapLink['organizationname']) . ')';
            }

            if (!empty($_finalDisplayText)) {
                $_finalDisplayText .= '<br />';
            }

            if (isset($_emailMapLink['address']) && !empty($_emailMapLink['address'])) {
                $_finalDisplayText .= preg_replace("#(\r\n|\r|\n)#s", '', nl2br(htmlspecialchars($_emailMapLink['address']))) . '<br />';
            }

            if ((isset($_emailMapLink['city']) && !empty($_emailMapLink['city'])) ||
                (isset($_emailMapLink['state']) && !empty($_emailMapLink['state'])) ||
                (isset($_emailMapLink['country']) && !empty($_emailMapLink['counrty']))) {
                $_extendedAddress = '';
                if (!empty($_emailMapLink['city']) && !empty($_emailMapLink['state'])) {
                    $_extendedAddress .= htmlspecialchars($_emailMapLink['city']) . ', ' . htmlspecialchars($_emailMapLink['state']);
                } else if (!empty($_emailMapLink['city'])) {
                    $_extendedAddress .= htmlspecialchars($_emailMapLink['city']);

                } else if (!empty($_emailMapLink['state'])) {
                    $_extendedAddress .= htmlspecialchars($_emailMapLink['state']);

                }

                if (!empty($_emailMapLink['country'])) {
                    if (empty($_extendedAddress)) {
                        $_extendedAddress .= htmlspecialchars($_emailMapLink['country']);

                    } else {
                        $_extendedAddress .= ' - ' . htmlspecialchars($_emailMapLink['country']);
                    }
                }

                if (!empty($_extendedAddress)) {
                    $_extendedAddress .= '<br />';
                }

                $_finalDisplayText .= $_extendedAddress;
            }

            if (isset($_emailMapLink['organizationphone']) && !empty($_emailMapLink['organizationphone'])) {
                $_finalDisplayText .= sprintf($this->Language->Get('phoneext'), htmlspecialchars($_emailMapLink['organizationphone'])) . '<br />';
            }

            if (isset($_emailMapLink['userphone']) && !empty($_emailMapLink['userphone'])) {
                $_finalDisplayText .= sprintf($this->Language->Get('phoneext'), htmlspecialchars($_emailMapLink['userphone'])) . '<br />';
            }

            echo str_replace('|', '', $_finalDisplayText) . '|' . (int)($_emailMapLink['userid']) . '|' . str_replace('|', '', text_to_html_entities($_emailMapLink['fullname'])) . SWIFT_CRLF;
        }

        return true;
    }

    /**
     * Quick Filter Options
     *
     * @author Varun Shoor
     * @param string $_filterType The Filter Type
     * @param string $_filterValue The Filter Value
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function QuickFilter($_filterType, $_filterValue)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_gridSortContainer = SWIFT_UserInterfaceGrid::GetGridSortField('usergrid', 'useremails.email', 'asc');

        $_searchStoreID = -1;

        $_userEmailIDList = array();

        switch ($_filterType) {
            case 'usergroup':
                {
                    /*
                     * BUG FIX - Varun Shoor
                     *
                     * SWIFT-1120 Quick filter > 'User Groups' or 'Date Registered' always returns 100 records
                     *
                     * Comments: None
                     */
                    $this->Database->Query("SELECT useremailid FROM (SELECT useremails.useremailid, useremails.linktypeid FROM " . TABLE_PREFIX . "useremails AS useremails
                    LEFT JOIN " . TABLE_PREFIX . "users AS users ON (useremails.linktypeid = users.userid)
                    LEFT JOIN " . TABLE_PREFIX . "userorganizations AS userorganizations ON (users.userorganizationid = userorganizations.userorganizationid)
                    LEFT JOIN " . TABLE_PREFIX . "usergroups AS usergroups ON (users.usergroupid = usergroups.usergroupid)
                    WHERE users.usergroupid = '" . (int)($_filterValue) . "' AND useremails.linktype = '" . SWIFT_UserEmail::LINKTYPE_USER . "'
                    ORDER BY useremails.isprimary DESC) AS data
                    GROUP BY linktypeid");
                    while ($this->Database->NextRecord()) {
                        $_userEmailIDList[] = $this->Database->Record['useremailid'];
                    }

                }
                break;

            case 'date':
                {
                    $_extendedSQL = false;

                    if ($_filterValue == 'today') {
                        $_extendedSQL = SWIFT_Rules::BuildSQLDateRange('users.dateline', SWIFT_Rules::DATERANGE_TODAY);
                    } else if ($_filterValue == 'yesterday') {
                        $_extendedSQL = SWIFT_Rules::BuildSQLDateRange('users.dateline', SWIFT_Rules::DATERANGE_YESTERDAY);
                    } else if ($_filterValue == 'l7') {
                        $_extendedSQL = SWIFT_Rules::BuildSQLDateRange('users.dateline', SWIFT_Rules::DATERANGE_LAST7DAYS);
                    } else if ($_filterValue == 'l30') {
                        $_extendedSQL = SWIFT_Rules::BuildSQLDateRange('users.dateline', SWIFT_Rules::DATERANGE_LAST30DAYS);
                    } else if ($_filterValue == 'l180') {
                        $_extendedSQL = SWIFT_Rules::BuildSQLDateRange('users.dateline', SWIFT_Rules::DATERANGE_LAST180DAYS);
                    } else if ($_filterValue == 'l365') {
                        $_extendedSQL = SWIFT_Rules::BuildSQLDateRange('users.dateline', SWIFT_Rules::DATERANGE_LAST365DAYS);
                    }


                    if (!empty($_extendedSQL)) {
                        /*
                         * BUG FIX - Varun Shoor
                         *
                         * SWIFT-1120 Quick filter > 'User Groups' or 'Date Registered' always returns 100 records
                         * SWIFT-1297 Quick filter > Date Registered does not return the result if user list is sorted by User Organization or User Group
                         *
                         * Comments: None
                         */
                        $this->Database->Query("SELECT useremailid FROM (SELECT useremails.useremailid, useremails.linktypeid FROM " . TABLE_PREFIX . "useremails AS useremails
                        LEFT JOIN " . TABLE_PREFIX . "users AS users ON (useremails.linktypeid = users.userid)
                        LEFT JOIN " . TABLE_PREFIX . "userorganizations AS userorganizations ON (users.userorganizationid = userorganizations.userorganizationid)
                        LEFT JOIN " . TABLE_PREFIX . "usergroups AS usergroups ON (users.usergroupid = usergroups.usergroupid)
                        WHERE " . $_extendedSQL . " AND useremails.linktype = '" . SWIFT_UserEmail::LINKTYPE_USER . "'
                        ORDER BY useremails.isprimary DESC) AS data
                        GROUP BY linktypeid");
                        while ($this->Database->NextRecord()) {
                            $_userEmailIDList[] = $this->Database->Record['useremailid'];
                        }
                    }

                }
                break;

            default:
                break;
        }

        if (_is_array($_userEmailIDList)) {
            $_searchStoreID = SWIFT_SearchStore::Create($_SWIFT->Session->GetSessionID(), SWIFT_SearchStore::TYPE_USERS, $_userEmailIDList, $_SWIFT->Staff->GetStaffID());
        } else {
            SWIFT::Alert($this->Language->Get('titlesearchfailed'), $this->Language->Get('msgsearchfailed'));
        }

        $this->Load->Manage($_searchStoreID);

        return true;
    }

    /**
     * Loads the Tag Cloud
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function _LoadTagCloud()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->Load->Library('Tag:TagCloud', array(SWIFT_TagLink::RetrieveCloudContainer(SWIFT_TagLink::TYPE_USER), false, 'window.$gridirs.RunIRS(\'usergrid\', \'tag:%s\');'), true, false, 'base');

        return true;
    }

    /**
     * Loads the Display Data
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function _LoadDisplayData()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UserInterface->AddNavigationBox($this->Language->Get('quickfilter'), $this->UserRenderManager->RenderTree());

        return true;
    }

    /**
     * Mark the given user as verified
     *
     * @author Varun Shoor
     * @param int $_userID The User ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function MarkAsVerified($_userID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_userID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_UserObject = new SWIFT_User(new SWIFT_DataID($_userID));
        if (!$_SWIFT_UserObject instanceof SWIFT_User || !$_SWIFT_UserObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_UserObject->MarkAsVerified();

        SWIFT::Info($this->Language->Get('titlevalidationsuccess'), sprintf($this->Language->Get('msgvalidationsuccess'), text_to_html_entities($_SWIFT_UserObject->GetProperty('fullname'))));

        $this->Load->Edit($_SWIFT_UserObject->GetUserID());

        return true;
    }

    /**
     * Update the User organization
     *
     * @author Varun Shoor
     * @param array $_userEmailIDList The User Email ID List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function MassActionPanel($_userEmailIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('staff_canupdateuser') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        /** @var array $_userIDList */
        $_userIDList = SWIFT_UserEmail::RetrieveUserIDListOnUserEmail($_userEmailIDList);
        $_userContainer = array();

        if (_is_array($_userIDList)) {
            $_SWIFT->Database->Query("SELECT userid, fullname FROM " . TABLE_PREFIX . "users WHERE userid IN (" . BuildIN($_userIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                $_userContainer[$_SWIFT->Database->Record['userid']] = $_SWIFT->Database->Record['fullname'];
            }
        }

        // Update Organization?
        $_userOrganizationList = SWIFT_UserInterface::GetMultipleInputValues('organization');
        if (_is_array($_userContainer) && _is_array($_userOrganizationList)) {
            // delete current links
            SWIFT_UserOrganizationLink::DeleteOnUser($_userIDList);

            foreach ($_userContainer as $_userID => $_fullName) {
                SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activityupdateuserorg'), text_to_html_entities($_fullName)),
                    SWIFT_StaffActivityLog::ACTION_UPDATE, SWIFT_StaffActivityLog::SECTION_USERS, SWIFT_StaffActivityLog::INTERFACE_STAFF);

                foreach ($_userOrganizationList as $orgidx => $orgName) {
                    $orgs = SWIFT_UserOrganization::RetrieveOnName($orgName);
                    if (empty($orgs)) {
                        // create organization
                        $_SWIFT_UserOrganizationObject = SWIFT_UserOrganization::Create($orgName, SWIFT_UserOrganization::TYPE_RESTRICTED);
                        $uid = $_SWIFT_UserOrganizationObject->GetUserOrganizationID();
                    } else {
                        $org = array_shift($orgs); // take the first org object
                        $uid = $org['userorganizationid'];
                    }

                    // update primary organization
                    if ($orgidx === 0) {
                        SWIFT_User::UpdateOrganizationList([$_userID], $uid);
                    }

                    if ($uid && !SWIFT_UserOrganizationLink::LinkExists($uid, $_userID)) {
                        /** @var SWIFT_User $SWIFT_UserObject */
                        $SWIFT_UserObject = SWIFT_User::GetOnID($_userID);
                        SWIFT_UserOrganizationLink::Create($SWIFT_UserObject, $uid);
                    }
                }
            }
        }

        // Update User Group?
        if (_is_array($_userContainer) && !empty($_POST['usergroupid'])) {
            foreach ($_userContainer as $_userID => $_fullName) {
                SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activityupdateusergroup'), text_to_html_entities($_fullName)),
                    SWIFT_StaffActivityLog::ACTION_UPDATE, SWIFT_StaffActivityLog::SECTION_USERS, SWIFT_StaffActivityLog::INTERFACE_STAFF);
            }

            SWIFT_User::UpdateGroupList($_userIDList, $_POST['usergroupid']);
        }

        // Merge Users
        if (!empty($_POST['primaryuser']) && _is_array($_userContainer) && count($_userContainer) >= 2) {
            $_primaryUserIDList = SWIFT_UserEmail::RetrieveUserIDListOnUserEmail(array($_POST['primaryuser']));
            $_secondaryUserIDList = SWIFT_UserEmail::RetrieveUserIDListOnUserEmail($_POST['itemid']);
            $_secondaryUserIDList = array_diff($_secondaryUserIDList, $_primaryUserIDList);
            self::Merge($_primaryUserIDList[0], $_secondaryUserIDList);
        } elseif (!empty($_POST['primaryuser']) && _is_array($_userContainer) && count($_userContainer) < 2) {
            $_SWIFT::Alert($_SWIFT->Language->Get('incorrectaction'), $_SWIFT->Language->Get('exacttwousers'));
        }

        return true;

    }

    /**
     * Clear the Profile Image
     *
     * @author Varun Shoor
     * @param int $_userID The User ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ClearProfileImage($_userID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_userID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if ($_SWIFT->Staff->GetPermission('staff_canupdateuser') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        $_SWIFT_UserObject = new SWIFT_User(new SWIFT_DataID($_userID));
        if (!$_SWIFT_UserObject instanceof SWIFT_User || !$_SWIFT_UserObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        SWIFT_UserProfileImage::DeleteOnUser(array($_userID));

        $this->Load->Method('Edit', $_userID);

        return true;
    }

    /**
     * Display User Data based on Phone Info
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function Phone()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($_POST['phonenumber'])) {
            $_POST['phonenumber'] = '';
        }

        /*
         * BUG FIX - Varun Shoor
         *
         * SWIFT-1542 Error when callerid is 0 in Kayako Desktop application
         *
         * Comments:
         */
        $_userIDList = array();
        try {
            $_userIDList = SWIFT_User::RetrieveOnPhoneNumber($_POST['phonenumber']);
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
        }
        if (!count($_userIDList)) {
            SWIFT::Alert(sprintf($this->Language->Get('titlephlookup'), htmlspecialchars($_POST['phonenumber'])), sprintf($this->Language->Get('msgphlookup'), htmlspecialchars($_POST['phonenumber'])));
            $_POST['phone'] = htmlspecialchars($_POST['phonenumber']);

            $this->Load->Insert();

            return true;
        }

        if (count($_userIDList) == 1) {
            $this->Load->Edit($_userIDList[0]);

            return true;
        }

        $_userEmailIDList = array();
        $this->Database->Query("SELECT useremails.useremailid FROM " . TABLE_PREFIX . "useremails AS useremails
            WHERE useremails.linktype = '" . SWIFT_UserEmail::LINKTYPE_USER . "'
                AND useremails.linktypeid IN (" . BuildIN($_userIDList) . ")");
        while ($this->Database->NextRecord()) {
            $_userEmailIDList[] = $this->Database->Record['useremailid'];
        }

        SWIFT_SearchStore::DeleteOnType(SWIFT_SearchStore::TYPE_USERS, $_SWIFT->Staff->GetStaffID());

        $_searchStoreID = SWIFT_SearchStore::Create($_SWIFT->Session->GetSessionID(), SWIFT_SearchStore::TYPE_USERS, $_userEmailIDList, $_SWIFT->Staff->GetStaffID());

        $this->Load->Manage($_searchStoreID);

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
        }

        SWIFT_ProfileImage::OutputOnUserID($_userID, $_emailAddressHash, $_preferredWidth);

        return true;
    }

    /**
     * Logs in as User
     *
     * @author Varun Shoor
     * @param int $_userID The User ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function LoginAsUser($_userID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_userID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_UserObject = new SWIFT_User(new SWIFT_DataID($_userID));
        if (!$_SWIFT_UserObject instanceof SWIFT_User || !$_SWIFT_UserObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if ($_SWIFT->Staff->GetPermission('staff_canviewusers') == '0' || $_SWIFT->Staff->GetPermission('staff_loginasuser') == '0') {
            /**
             * BUG FIX - Saloni Dhall
             *
             * SWIFT-3018: "Login as User" permission bug
             *
             */
            log_error_and_exit();
        }

        $_SWIFT_InterfaceObject = new SWIFT_Interface(SWIFT_Interface::INTERFACE_CLIENT);

        // First logout the staff from any active client session
        SWIFT_Session::Logout($_SWIFT_InterfaceObject, true);

        // Insert a new session
        $_userSessionID = SWIFT_Session::Insert(SWIFT_Interface::INTERFACE_CLIENT, $_SWIFT_UserObject->GetUserID());

        header('location: ' . StripTrailingSlash(SWIFT::Get('swiftpath')) . '/index.php?/Base/User/LoginFromStaff');

        return true;
    }

    /**
     * Import the user data from CSV
     *
     * @author Ravi Sharma <ravi.sharma@kayako.com>
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ImportCSV()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_LoadDisplayData();

        $this->UserInterface->Header($this->Language->Get('users') . ' > ' . $this->Language->Get('insertuser'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('staff_caninsertuser') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->RenderCSV(SWIFT_UserInterface::MODE_INSERT);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Import CSV Submission Processor
     *
     * @author Ravi Sharma <ravi.sharma@kayako.com>
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ImportCSVSubmit()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($_FILES['csvfile'])) {
            SWIFT::ErrorField('csvfile');
            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));
            $this->Load->ImportCSV();

            return false;
        }

        $_pathInfoContainer = pathinfo($_FILES['csvfile']['name']);

        if (isset($_pathInfoContainer['extension']) && !empty($_pathInfoContainer['extension'])) {
            $_pathInfoContainer['extension'] = strtolower($_pathInfoContainer['extension']);

            if ($_pathInfoContainer['extension'] != 'csv') {
                SWIFT::ErrorField('csvfile');
                $this->UserInterface->Error('Invalid CSV File Extension', 'The CSV file has an invalid extension. Allowed extension is: csv');
                $this->Load->ImportCSV();
                return false;
            }
        }

        $_csvHandle = fopen($_FILES['csvfile']['tmp_name'], "r");

        if (!$_csvHandle) {
            SWIFT::ErrorField('csvfile');
            $this->UserInterface->Error('Unable to open CSV File', 'The CSV file has an error. Please try again.');
            $this->Load->ImportCSV();
            return false;
        }

        // read csv file and get the user's email address, firstname and lastname
        $_csvContainer = array();
        $_csvIndex = 0;
        while (($_data = fgetcsv($_csvHandle)) !== FALSE) {
            $_csvContainer[$_csvIndex] = $_data;
            $_csvIndex++;
        }

        if (count($_csvContainer)) {
            $_defaultUserGroupID = $_SWIFT->TemplateGroup->GetRegisteredUserGroupID();
            $_userCount = 0;

            foreach ($_csvContainer as $_key => $_val) {
                if (!IsEmailValid($_val[0]) || empty($_val[1])) {
                    continue;
                }
                $_email = trim($_val[0]);
                $_fullName = trim($_val[1]);
                if (isset($_val[2]) && !empty($_val[2])) {
                    $_fullName .= ' ' . trim($_val[2]);
                }
                $_password = substr(BuildHash(), 0, 12);
                $_userEmailCheckResult = SWIFT_UserEmail::CheckEmailRecordExists(array($_email));
                if (_is_array($_userEmailCheckResult)) {
                    continue;
                }

                $_userOrganizationID = 0;
                if (isset($_val[3]) && !empty($_val[3])) {

                    // OPTIONAL
                    $emailDomainFilter = array();
                    if (isset($_val[7])) {
                        $emailDomainFilter[] = CleanEmail($_val[7]);
                    }

                    // OPTIONAL
                    $address = isset($_val[8]) ? $_val[8] : '';
                    $city = isset($_val[9]) ? $_val[9] : '';
                    $state = isset($_val[10]) ? $_val[10] : '';
                    $postalCode = isset($_val[11]) ? $_val[11] : '';
                    $country = isset($_val[12]) ? $_val[12] : '';
                    $phone = isset($_val[13]) ? $_val[13] : '';
                    $fax = isset($_val[14]) ? $_val[14] : '';
                    $website = isset($_val[15]) ? $_val[15] : '';

                    $_userOrganizationTitle = $_val[3];

                    // First check existing title..
                    $_userOrganizationContainer = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "userorganizations WHERE organizationname LIKE '" . $this->Database->Escape($_userOrganizationTitle) . "'");
                    if (isset($_userOrganizationContainer['userorganizationid']) && !empty($_userOrganizationContainer['userorganizationid'])) {
                        $_userOrganizationID = $_userOrganizationContainer['userorganizationid'];
                    } else {
                        // If no match.. then only create a new user organization
                        $_userOrganizationType = SWIFT_UserOrganization::TYPE_RESTRICTED;

                        $_SWIFT_UserOrganizationObject = SWIFT_UserOrganization::Create($_userOrganizationTitle, $_userOrganizationType, $emailDomainFilter, $address, $city, $state, $postalCode, $country, $phone, $fax, $website);
                        if ($_SWIFT_UserOrganizationObject instanceof SWIFT_UserOrganization && $_SWIFT_UserOrganizationObject->GetIsClassLoaded()) {
                            $_userOrganizationID = $_SWIFT_UserOrganizationObject->GetUserOrganizationID();
                        }
                    }

                }

                $_phone = '';
                if (isset($_val[4]) && !empty($_val[4])) {
                    $_phone = trim($_val[4]); // Phone Number
                }

                $_salutation = 0;
                if (isset($_val[5]) && !empty($_val[5])) {
                    $_salutationOptions = array('1' => 'mr', '2' => 'ms', '3' => 'mrs', '4' => 'dr');
                    $_salutation = array_search(trim(strtolower($_val[5])), $_salutationOptions); // Salutation
                }

                $_userDesignation = '';
                if (isset($_val[6]) && !empty($_val[6])) {
                    $_userDesignation = trim($_val[6]); // User Designation
                }

                try {
                    SWIFT_User::Create($_defaultUserGroupID, $_userOrganizationID, $_salutation, $_fullName, $_userDesignation, $_phone, true, 0, array($_email), $_password, 0, '', false, 0, 0, 0, true, true);
                    SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityinsertuser'), text_to_html_entities($_fullName)), SWIFT_StaffActivityLog::ACTION_INSERT, SWIFT_StaffActivityLog::SECTION_USERS, SWIFT_StaffActivityLog::INTERFACE_STAFF);
                    $_userCount++;
                } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {

                }
            }

            SWIFT::Info($_userCount . ' user(s) imported successfully', $_userCount . ' user(s) imported successfully');
        }

        $this->Load->ImportCSV();

        return false;
    }

    /**
     * Creates organizations from the pool list for the given user
     *
     * @author Werner Garcia
     * @param SWIFT_User $_SWIFT_UserObject
     * @throws SWIFT_Exception
     */
    protected function createOrganizationLinksForUser(SWIFT_User $_SWIFT_UserObject)
    {
        if (!empty($this->_userOrganizationLinkPool)) {
            foreach ($this->_userOrganizationLinkPool as $uid) {
                SWIFT_UserOrganizationLink::Create($_SWIFT_UserObject, $uid);
            }
            $this->_userOrganizationLinkPool = []; // clear
        }
    }
}
