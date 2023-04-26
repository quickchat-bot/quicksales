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
use Base\Models\Staff\SWIFT_Staff;
use Base\Models\Staff\SWIFT_StaffActivityLog;
use Base\Models\Staff\SWIFT_StaffProfileImage;
use SWIFT;
use SWIFT_App;
use SWIFT_CacheManager;
use SWIFT_DataID;
use Parser\Models\EmailQueue\SWIFT_EmailQueue;
use SWIFT_Exception;
use SWIFT_Hook;
use SWIFT_Image_Exception;
use SWIFT_ImageResize;
use SWIFT_Model;
use SWIFT_Session;

/**
 * The Preferences Management Controller
 *
 * @author Varun Shoor
 * @property Controller_Preferences $Load
 * @property View_Preferences $View
 * @method Model(string $_modelName, array $_arguments, bool $_initiateInstance, $_customAppName, string $appName = '')
 * @method Method($v='', $_ticketID=0, $_listType=0, $_departmentID=0, $_ticketStatusID=0, $_ticketTypeID=0, $_ticketLimitOffset=0);
 * @method Library($_libraryName, $_arguments = [], $_initiateInstance = false, $_customAppName = '', $_appName = '')
 */
class Controller_Preferences extends Controller_staff
{
    // Core Constants
    const MENU_ID = 1;
    const NAVIGATION_ID = 5;

    /**
     * Constructor
     *
     * @author Varun Shoor
     */
    public function __construct()
    {
        parent::__construct();

        $this->Load->Library('Staff:StaffPasswordPolicy', [], true, false, 'base');

        $this->Language->Load('staff_preferences');
    }

    /**
     * Render the Preferences Form
     *
     * @author Varun Shoor
     * @param mixed $_isChangePasswordTabSelected Whether the Change Password tab is selected
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ViewPreferences($_isChangePasswordTabSelected = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        if (is_numeric($_isChangePasswordTabSelected) || is_bool($_isChangePasswordTabSelected)) {
            $_isChangePasswordTabSelected = (int)($_isChangePasswordTabSelected);
        } else {
            $_isChangePasswordTabSelected = false;
        }

        $this->View->RenderInfoBox();

        $this->UserInterface->Header($this->Language->Get('home') . ' > ' . $this->Language->Get('preferences'), self::MENU_ID,
            self::NAVIGATION_ID);

        $this->View->Render($_isChangePasswordTabSelected);

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Runs the Checks for Profile Update
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    private function RunChecks()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        // BEGIN CSRF HASH CHECK

        if (!SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        $_staffCache = $this->Cache->Get('staffcache');
        $_staffGroupCache = $this->Cache->Get('staffgroupcache');

        if (trim($_POST['firstname']) == '' || trim($_POST['lastname']) == '' || trim($_POST['email']) == '') {
            $this->UserInterface->CheckFields('firstname', 'lastname', 'email');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            return false;
        } else if (!IsEmailValid($_POST['email'])) {
            SWIFT::ErrorField('email');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            return false;
        } else if (trim($_POST['mobilenumber']) != '' && trim(preg_replace('/[^0-9]/', '', $_POST['mobilenumber'])) != $_POST['mobilenumber']) {
            SWIFT::ErrorField('mobilenumber');

            $this->UserInterface->Error($this->Language->Get('titlemobilenumberinvalid'), $this->Language->Get('msgmobilenumberinvalid'));

            return false;
        } else if (SWIFT::Get('isdemo') == true) {
            $this->UserInterface->Error($this->Language->Get('titledemomode'), $this->Language->Get('msgdemomode'));

            return false;
        }

        if ($_SWIFT->Staff->GetPermission('staff_profile') == '0') {
            $this->UserInterface->Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));

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
        /**
         * BUG FIX : Nidhi Gupta
         *
         * SWIFT-4509 : System does not check for existing email address while updating email address from staff preferences
         *
         * Comments : Added Check for duplicate email.
         */
        // Duplicate Email Check
        if (_is_array($_staffCache)) {
            foreach ($_staffCache as $_key => $_val) {
                if (trim($_POST['email']) == $_val['email'] && ($_SWIFT->Staff->GetID() != $_val['staffid'])) {
                    SWIFT::Error($this->Language->Get('titleemailexists'), $this->Language->Get('msgemailexists'));

                    return false;
                }
            }
        }

        // no no.. things are not over yet, check for the parser email to make sure there are no loops..
        if (SWIFT_App::IsInstalled(APP_PARSER)) {
            $this->Load->Model('EmailQueue:EmailQueue', [], false, false, APP_PARSER);
            if (SWIFT_EmailQueue::EmailQueueExistsWithEmail($_POST['email'])) {
                SWIFT::Error($this->Language->Get('titleemailqueuematch'), sprintf($this->Language->Get('msgemailqueuematch'), htmlspecialchars($_POST['email'])));

                return false;
            }
        }

        // Begin Hook: staff_preferences_runchecks
        unset($_hookCode);
        $_hookResult = null;
        ($_hookCode = SWIFT_Hook::Execute('staff_preferences_runchecks')) ? ($_hookResult = eval($_hookCode)) : false;
        if ($_hookResult !== null)
            return $_hookResult;
        // End Hook

        return true;
    }


    /**
     * Render the Confirmation for InsertSubmit/EditSubmit
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    private function _RenderConfirmation()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_fullName = text_to_html_entities($_POST['firstname']) . ' ' . text_to_html_entities($_POST['lastname']);

        SWIFT::Info(sprintf($this->Language->Get('titleupdatestaff'), $_fullName, htmlspecialchars($_POST['email'])), sprintf($this->Language->Get('msgupdatestaff'), $_fullName, $_fullName, htmlspecialchars($_POST['email']), htmlspecialchars($_POST['mobilenumber'])));

        return true;
    }

    /**
     * Preferences Submission Processor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function PreferencesSubmit()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        if ($this->RunChecks()) {
            $_greeting = '';
            if (isset($_POST['greeting']) && trim($_POST['greeting']) != '') {
                $_greeting = trim($_POST['greeting']);
            }
            $_SWIFT->Staff->UpdatePreferences($_POST['firstname'], $_POST['lastname'], $_POST['email'], $_POST['mobilenumber'], $_POST['signature'],
                $_greeting, $_POST['timezonephp'], $_POST['enabledst']);

            $this->_ProcessUploadedProfileImage($_SWIFT->Staff);

            // Begin Hook: staff_preferences_submit
            unset($_hookCode);
            ($_hookCode = SWIFT_Hook::Execute('staff_preferences_submit')) ? eval($_hookCode) : false;
            // End Hook

            // Clear Cache
            SWIFT_CacheManager::EmptyCacheDirectory();

            SWIFT_StaffActivityLog::AddToLog($this->Language->Get('activityupdateprofile'),
                SWIFT_StaffActivityLog::ACTION_UPDATE, SWIFT_StaffActivityLog::SECTION_STAFF, SWIFT_StaffActivityLog::INTERFACE_STAFF);

            $this->_RenderConfirmation();

            $this->Load->ViewPreferences();

            return true;
        }

        $this->Load->ViewPreferences();

        return false;
    }

    /**
     * Change Password Submission Processor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ChangePasswordSubmit()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        if (SWIFT_Staff::GetComputedPassword($_POST['existingpassword']) != $_SWIFT->Staff->GetProperty('staffpassword')) {
            SWIFT::ErrorField('existingpassword');

            $this->UserInterface->Error($this->Language->Get('titleinvalidpw'), $this->Language->Get('msginvalidpw'));

            $this->Load->ViewPreferences(true);

            return false;
        } else if (trim($_POST['existingpassword']) == '' || trim($_POST['newpassword']) == '' || trim($_POST['newpasswordrepeat']) == '') {
            $this->UserInterface->CheckFields('existingpassword', 'newpassword', 'newpasswordrepeat');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            $this->Load->ViewPreferences(true);

            return false;
        } else if ($_POST['newpassword'] != $_POST['newpasswordrepeat']) {
            SWIFT::ErrorField('newpassword');
            SWIFT::ErrorField('newpasswordrepeat');

            $this->UserInterface->Error($this->Language->Get('titlepwnomatch'), $this->Language->Get('msgpwnomatch'));

            $this->Load->ViewPreferences(true);

            return false;
        } else if (!empty($_POST['newpassword']) && !$this->StaffPasswordPolicy->Check($_POST['newpassword'])) {
            SWIFT::ErrorField('newpassword');
            SWIFT::ErrorField('newpasswordrepeat');

            $this->UserInterface->Error($this->Language->Get('titlepwpolicy'), $this->Language->Get('msgpwpolicy') . ' ' . $this->StaffPasswordPolicy->GetPasswordPolicyString());

            $this->Load->ViewPreferences(true);

            return false;
        } else if ($_SWIFT->Staff->GetPermission('staff_changepassword') == '0') {
            $this->UserInterface->Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));

            $this->Load->ViewPreferences(true);

            return false;
        }

        SWIFT::Info($this->Language->Get('titlepwupdated'), $this->Language->Get('msgpwupdated'));

        $_SWIFT->Staff->ChangePassword($_POST['newpassword']);

        $this->Load->ViewPreferences(true);

        return true;
    }

    /**
     * Clear the Profile Image
     *
     * @author Varun Shoor
     * @param int $_staffID The Staff ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ClearProfileImage($_staffID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_staffID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        if ($_SWIFT->Staff->GetPermission('staff_profile') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));

            return false;
        }

        $_SWIFT_StaffObject = new SWIFT_Staff(new SWIFT_DataID($_staffID));
        if (!$_SWIFT_StaffObject instanceof SWIFT_Staff || !$_SWIFT_StaffObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        SWIFT_StaffProfileImage::DeleteOnStaff(array($_staffID));

        // Clear Cache
        SWIFT_CacheManager::EmptyCacheDirectory();

        $this->Load->Method('ViewPreferences');

        return true;
    }

    /**
     * Process the Uploaded Profile Image
     *
     * @author Varun Shoor
     * @param SWIFT_Model $_SWIFT_StaffObject The SWIFT_Staff Object Pointer
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    protected function _ProcessUploadedProfileImage(SWIFT_Model $_SWIFT_StaffObject)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (!$_SWIFT_StaffObject instanceof SWIFT_Staff || !$_SWIFT_StaffObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);

            return false;
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
                SWIFT::Error($this->Language->Get('staffprofileimage') ?: 'Profile Picture', $this->Language->Get('wrong_image_size'));
                return false;
            }
            $_profileImage = SWIFT_StaffProfileImage::RetrieveOnStaff($_SWIFT_StaffObject->GetStaffID());
            SWIFT_StaffProfileImage::DeleteOnStaff(array($_SWIFT_StaffObject->GetStaffID()));

            $_pathInfoContainer = pathinfo($_FILES['profileimage']['name']);
            if (isset($_pathInfoContainer['extension']) && !empty($_pathInfoContainer['extension'])) {
                $_ImageResizeObject = new SWIFT_ImageResize($_FILES['profileimage']['tmp_name']);

                $_ImageResizeObject->SetKeepProportions(true);

                $_fileContents = false;

                try {
                    $_ImageResizeObject->Resize();
                    $_fileContents = base64_encode($_ImageResizeObject->Get());
                } catch (SWIFT_Image_Exception $_SWIFT_Image_ExceptionObject) {
                    if ($_profileImage) {
                        $_fileContents = $_profileImage->GetProperty('imagedata');
                        unset($_profileImage);
                    }
                    SWIFT::Error($this->Language->Get('staffprofileimage'), $this->Language->Get('wrong_profile_image'));
                }

                if ($_fileContents) {
                    SWIFT_StaffProfileImage::Create($_SWIFT_StaffObject->GetStaffID(),
                        SWIFT_StaffProfileImage::TYPE_PUBLIC, $_pathInfoContainer['extension'], $_fileContents);
                } else {
                    return false;
                }
            }
        }

        return true;
    }
}

?>
