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

namespace Base\Staff;

use Base\Admin\Controller_Staff;
use Base\Library\CustomField\SWIFT_CustomFieldManager;
use Base\Library\CustomField\SWIFT_CustomFieldRendererStaff;
use Base\Library\Tag\SWIFT_TagCloud;
use Base\Library\User\SWIFT_UserRenderManager;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Models\CustomField\SWIFT_CustomFieldGroup;
use Base\Models\Staff\SWIFT_StaffActivityLog;
use Base\Models\Tag\SWIFT_Tag;
use Base\Models\Tag\SWIFT_TagLink;
use Base\Models\User\SWIFT_UserOrganization;
use Base\Models\User\SWIFT_UserOrganizationEmail;
use Base\Models\User\SWIFT_UserOrganizationNote;
use SWIFT;
use SWIFT_Exception;
use SWIFT_Hook;
use SWIFT_Session;

/**
 * The User Organization Controller
 *
 * @author Varun Shoor
 * @property SWIFT_UserRenderManager $UserRenderManager
 * @property SWIFT_CustomFieldManager $CustomFieldManager
 * @property SWIFT_TagCloud $TagCloud
 * @property View_UserOrganization $View
 * @property SWIFT_CustomFieldRendererStaff $CustomFieldRendererStaff
 * @property \SWIFT_CountryContainer $CountryContainer
 */
class Controller_UserOrganization extends Controller_staff
{
    // Core Constants
    const MENU_ID = 8;
    const NAVIGATION_ID = 1;

    /**
     * Constructor
     *
     * @author Varun Shoor
     */
    public function __construct()
    {
        $_SWIFT = SWIFT::GetInstance();

        parent::__construct();

        $this->Load->Library('User:UserRenderManager', [], true, false, 'base');

        $this->Load->Library('Misc:CountryContainer');

        $this->Load->Library('CustomField:CustomFieldRendererStaff', [], true, false, 'base');
        $this->Load->Library('CustomField:CustomFieldManager', [], true, false, 'base');

        $this->Language->Load('staff_users');

        if ($_SWIFT->Staff->GetPermission('cu_entab') == '0') {
            $this->UserInterface->Header($this->Language->Get('users') . ' > ' . $this->Language->Get('manageorganizations'), self::MENU_ID, self::NAVIGATION_ID);
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
            $this->UserInterface->Footer();

            log_error_and_exit();
        }
    }

    /**
     * Delete the User Organizations from Mass Action
     *
     * @author Varun Shoor
     * @param mixed $_userOrganizationIDList The User Organization ID List Container Array
     * @param bool $_byPassCSRF Whether to bypass the CSRF check
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_userOrganizationIDList, $_byPassCSRF = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('staff_candeleteuserorganization') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        if (_is_array($_userOrganizationIDList)) {
            $_SWIFT->Database->Query("SELECT organizationname FROM " . TABLE_PREFIX . "userorganizations WHERE userorganizationid IN (" . BuildIN($_userOrganizationIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activitydeleteuserorganization'), htmlspecialchars($_SWIFT->Database->Record['organizationname'])), SWIFT_StaffActivityLog::ACTION_DELETE, SWIFT_StaffActivityLog::SECTION_USERS, SWIFT_StaffActivityLog::INTERFACE_STAFF);
            }

            // Begin Hook: staff_userorganization_delete
            unset($_hookCode);
            ($_hookCode = SWIFT_Hook::Execute('staff_userorganization_delete')) ? eval($_hookCode) : false;
            // End Hook

            SWIFT_UserOrganization::DeleteList($_userOrganizationIDList);
        }

        return true;
    }

    /**
     * Delete the Given User Organization ID
     *
     * @author Varun Shoor
     * @param int $_userOrganizationID The User Organization ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete($_userOrganizationID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        self::DeleteList(array($_userOrganizationID), true);

        $this->Load->Manage();

        return true;
    }

    /**
     * Load the Tag Cloud
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function _LoadTagCloud()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $this->Load->Library('Tag:TagCloud', array(SWIFT_TagLink::RetrieveCloudContainer(SWIFT_TagLink::TYPE_USERORGANIZATION), false, 'window.$gridirs.RunIRS(\'userorganizationgrid\', \'tag:%s\');'), true, false, 'base');

        return true;
    }

    /**
     * Load the Display
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function _LoadDisplayData()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $this->UserInterface->AddNavigationBox($this->Language->Get('quickfilter'), $this->UserRenderManager->RenderTree());
        $this->Load->Library('Tag:TagCloud', array(SWIFT_TagLink::RetrieveCloudContainer(SWIFT_TagLink::TYPE_USERORGANIZATION), false, 'window.$gridirs.RunIRS(\'userorganizationgrid\', \'tag:%s\');'), true, false, 'base');

        return true;
    }

    /**
     * Displays the User Organization Grid
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

            return false;
        }

        $this->_LoadTagCloud();
        $this->_LoadDisplayData();

        $this->UserInterface->Header($this->Language->Get('users') . ' > ' . $this->Language->Get('manageorganizations'), self::MENU_ID, self::NAVIGATION_ID, $this->TagCloud->Render());

        if ($_SWIFT->Staff->GetPermission('staff_canviewuserorganizations') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->RenderGrid($_searchStoreID);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Searches using Auto Complete
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function QuickSearchNames() {
        return $this->QuickSearch(true);
    }

    /**
     * Searches using Auto Complete
     *
     * @author Varun Shoor
     * @param bool $_namesOnly
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function QuickSearch($_namesOnly = false)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($_POST['q']) || empty($_POST['q'])) {
            return false;
        }

        $_userOrganizationsContainer = array();

        $this->Database->QueryLimit("SELECT userorganizations.* FROM " . TABLE_PREFIX . "userorganizations AS userorganizations
            WHERE ((" . BuildSQLSearch('userorganizations.address', $_POST['q']) . ")
                OR (" . BuildSQLSearch('userorganizations.city', $_POST['q']) . ")
                OR (" . BuildSQLSearch('userorganizations.state', $_POST['q']) . ")
                OR (" . BuildSQLSearch('userorganizations.country', $_POST['q']) . ")
                OR (" . BuildSQLSearch('userorganizations.phone', $_POST['q']) . ")
                OR (" . BuildSQLSearch('userorganizations.organizationname', $_POST['q']) . "))", 6);
        while ($this->Database->NextRecord()) {
            $_userOrganizationsContainer[$this->Database->Record['organizationname'] . ':' . $this->Database->Record['userorganizationid']] = $this->Database->Record;
        }

        ksort($_userOrganizationsContainer);

        foreach ($_userOrganizationsContainer as $_userOrganizationContainer) {
            $_finalDisplayText = '';

            // QuickSupport Infotech Ltd.
            // 2nd Floor, Midas Corporate Park
            // Jalandhar, Punjab, India
            // Organization Phone: +91 181xxx
            $_finalDisplayText .= htmlspecialchars($_userOrganizationContainer['organizationname']) . '<br />';

            if (isset($_userOrganizationContainer['address']) && !empty($_userOrganizationContainer['address'])) {
                $_finalDisplayText .= preg_replace("#(\r\n|\r|\n)#s", '', nl2br(htmlspecialchars($_userOrganizationContainer['address']))) . '<br />';
            }

            if ((isset($_userOrganizationContainer['city']) && !empty($_userOrganizationContainer['city'])) ||
                (isset($_userOrganizationContainer['state']) && !empty($_userOrganizationContainer['state'])) ||
                (isset($_userOrganizationContainer['country']) && !empty($_userOrganizationContainer['counrty']))) {
                $_extendedAddress = '';
                if (!empty($_userOrganizationContainer['city']) && !empty($_userOrganizationContainer['state'])) {
                    $_extendedAddress .= htmlspecialchars($_userOrganizationContainer['city']) . ', ' . htmlspecialchars($_userOrganizationContainer['state']);
                } else if (!empty($_userOrganizationContainer['city'])) {
                    $_extendedAddress .= htmlspecialchars($_userOrganizationContainer['city']);

                } else if (!empty($_userOrganizationContainer['state'])) {
                    $_extendedAddress .= htmlspecialchars($_userOrganizationContainer['state']);

                }

                if (!empty($_userOrganizationContainer['country'])) {
                    if (empty($_extendedAddress)) {
                        $_extendedAddress .= htmlspecialchars($_userOrganizationContainer['country']);

                    } else {
                        $_extendedAddress .= ' - ' . htmlspecialchars($_userOrganizationContainer['country']);
                    }
                }

                if (!empty($_extendedAddress)) {
                    $_extendedAddress .= '<br />';
                }

                $_finalDisplayText .= $_extendedAddress;
            }

            if (isset($_userOrganizationContainer['phone']) && !empty($_userOrganizationContainer['phone'])) {
                $_finalDisplayText .= sprintf($this->Language->Get('phoneext'), htmlspecialchars($_userOrganizationContainer['phone'])) . '<br />';
            }

            if ($_namesOnly) {
                echo str_replace('|', '', $_finalDisplayText) . '|' .  str_replace('|', '', $_userOrganizationContainer['organizationname']) . SWIFT_CRLF;
            } else {
                echo str_replace('|', '', $_finalDisplayText) . '|' . (int)($_userOrganizationContainer['userorganizationid']) . '|' . str_replace('|', '', $_userOrganizationContainer['organizationname']) . SWIFT_CRLF;
            }
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

            return false;
        }

        $_emailContainer = array();

        $_postEmailValues = SWIFT_UserInterface::GetMultipleInputValues('emails');
        if (_is_array($_postEmailValues)) {
            foreach ($_postEmailValues as $_key => $_val) {
                if (strpos($_val, '.')) {
                    $_emailContainer[] = $_val;
                }
            }
        }

        return $_emailContainer;
    }

    /**
     * Runs the Checks for Insertion/Editing
     *
     * @author Varun Shoor
     * @param int $_mode The User Interface Mode
     * @param int $_userOrganizationID (OPTIONAL) The User Organization ID to ignore for email check
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    private function RunChecks($_mode, $_userOrganizationID = 0)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        if (trim($_POST['organizationname']) == '' || trim($_POST['organizationtype']) == '' || !SWIFT_UserOrganization::IsValidOrganizationType($_POST['organizationtype'])) {
            $this->UserInterface->CheckFields('organizationname', 'organizationtype');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            return false;
        } else if (SWIFT::Get('isdemo') == true) {
            $this->UserInterface->Error($this->Language->Get('titledemomode'), $this->Language->Get('msgdemomode'));

            return false;
        }

        if (($_mode == SWIFT_UserInterface::MODE_INSERT && $_SWIFT->Staff->GetPermission('staff_caninsertuserorganization') == '0') || ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT->Staff->GetPermission('staff_canupdateuserorganization') == '0')) {
            $this->UserInterface->Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));

            return false;
        }

        $_userOrganizationEmailCheckResult = SWIFT_UserOrganizationEmail::CheckEmailRecordExists($this->_GetPOSTEmailContainer(), $_userOrganizationID);
        if ($_userOrganizationEmailCheckResult) {
            SWIFT::ErrorField('emails');

            $this->UserInterface->Error($this->Language->Get('titleemailexistsorg'), sprintf($this->Language->Get('msgemailexistsorg'), $_userOrganizationEmailCheckResult[0], $_userOrganizationEmailCheckResult[1]));

            return false;
        }

        $_customFieldCheckResult = $this->CustomFieldManager->Check(SWIFT_CustomFieldManager::MODE_POST, $_mode, array(SWIFT_CustomFieldGroup::GROUP_USERORGANIZATION), SWIFT_CustomFieldManager::CHECKMODE_STAFF);
        if ($_customFieldCheckResult[0] == false) {
            call_user_func_array(array('SWIFT', 'ErrorField'), $_customFieldCheckResult[1]);

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            return false;
        }

        // Begin Hook: staff_userorganization_runchecks
        unset($_hookCode);
        $_hookResult = null;
        ($_hookCode = SWIFT_Hook::Execute('staff_userorganization_runchecks')) ? ($_hookResult = eval($_hookCode)) : false;
        if ($_hookResult !== null)
            return $_hookResult;
        // End Hook

        /*        $_userOrganizationContainer = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "userorganizations WHERE organizationname LIKE '" . $this->Database->Escape(trim($_POST['organizationname'])) . "'");
                if (isset($_userOrganizationContainer['userorganizationid']) && !empty($_userOrganizationContainer['userorganizationid']) && $_userOrganizationContainer['userorganizationid'] != $_userOrganizationID)
                {
                    SWIFT::ErrorField('organizationname');

                    $this->UserInterface->Error($this->Language->Get('titleorgnameexists'), sprintf($this->Language->Get('msgorgnameexists'), htmlspecialchars($_POST['organizationname'])));

                    return false;
                }*/

        return true;
    }

    /**
     * Insert a new User Organization
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

            return false;
        }

        $this->_LoadDisplayData();

        $this->UserInterface->Header($this->Language->Get('users') . ' > ' . $this->Language->Get('insertorganization'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('staff_caninsertuserorganization') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render(SWIFT_UserInterface::MODE_INSERT);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Render the Confirmation for InsertSubmit/EditSubmit
     *
     * @author Varun Shoor
     * @param mixed $_mode The User Interface Mode
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    private function _RenderConfirmation($_mode)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            $_type = 'update';
        } else {
            $_type = 'insert';
        }

        $_finalText = '<b>' . $this->Language->Get('organizationname') . ':</b> <i class="fa fa-institution" aria-hidden="true"></i> ' . htmlspecialchars($_POST['organizationname']) . '<br />';
        $_finalText .= '<b>' . $this->Language->Get('organizationtype') . ':</b> ' . htmlspecialchars(SWIFT_UserOrganization::GetOrganizationTypeLabel($_POST['organizationtype'])) . '<br />';

        $_userEmailList = $this->_GetPOSTEmailContainer();
        $_finalEmailList = array();

        if (_is_array($_userEmailList)) {
            foreach ($_userEmailList as $_key => $_val) {
                $_finalEmailList[] = htmlspecialchars($_val);
            }

            $_finalText .= '<b>' . $this->Language->Get('userorganizationemails') . ':</b> ' . implode(', ', $_finalEmailList) . '<br />';
        }

        SWIFT::Info(sprintf($this->Language->Get('titleorganization' . $_type), htmlspecialchars($_POST['organizationname'])), sprintf($this->Language->Get('msgorganization' . $_type), htmlspecialchars($_POST['organizationname'])) . '<br />' . $_finalText);

        return true;
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

            return false;
        }

        if ($this->RunChecks(SWIFT_UserInterface::MODE_INSERT)) {
            $_SWIFT_UserOrganizationObject = SWIFT_UserOrganization::Create(trim($_POST['organizationname']), $_POST['organizationtype'], $this->_GetPOSTEmailContainer(), $_POST['address'], $_POST['city'], $_POST['state'], $_POST['postalcode'], $_POST['country'], $_POST['phone'], $_POST['fax'], $_POST['website'], $_POST['slaplanid'], GetCalendarDateline($_POST['slaexpirytimeline']));

            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityinsertuserorganization'), htmlspecialchars($_POST['organizationname'])), SWIFT_StaffActivityLog::ACTION_INSERT, SWIFT_StaffActivityLog::SECTION_USERS, SWIFT_StaffActivityLog::INTERFACE_STAFF);

            if (!$_SWIFT_UserOrganizationObject instanceof SWIFT_UserOrganization || !$_SWIFT_UserOrganizationObject->GetIsClassLoaded()) {
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);

                return false;
            }

            // Process the Tags
            if ($_SWIFT->Staff->GetPermission('staff_canupdatetags') != '0') {
                SWIFT_Tag::Process(SWIFT_TagLink::TYPE_USERORGANIZATION, $_SWIFT_UserOrganizationObject->GetUserOrganizationID(), SWIFT_UserInterface::GetMultipleInputValues('tags'), $_SWIFT->Staff->GetStaffID());
            }

            // Add notes
            if (trim($_POST['userorganizationnotes']) != '') {
                SWIFT_UserOrganizationNote::Create($_SWIFT_UserOrganizationObject, $_POST['userorganizationnotes'], (int)($_POST['notecolor_userorganizationnotes']));
            }

            // Update Custom Field Values
            $this->CustomFieldManager->Update(SWIFT_CustomFieldManager::MODE_POST, SWIFT_UserInterface::MODE_INSERT, array(SWIFT_CustomFieldGroup::GROUP_USERORGANIZATION), SWIFT_CustomFieldManager::CHECKMODE_STAFF, $_SWIFT_UserOrganizationObject->GetUserOrganizationID());

            // Begin Hook: staff_userorganization_insert
            unset($_hookCode);
            ($_hookCode = SWIFT_Hook::Execute('staff_userorganization_insert')) ? eval($_hookCode) : false;
            // End Hook

            $this->_RenderConfirmation(SWIFT_UserInterface::MODE_INSERT);

            $this->Load->Manage();

            return true;
        }

        $this->Load->Insert();

        return false;
    }

    /**
     * Edit the User Organization ID
     *
     * @author Varun Shoor
     * @param int $_userOrganizationID The User Organization ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Edit($_userOrganizationID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_userOrganizationID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $_SWIFT_UserOrganizationObject = new SWIFT_UserOrganization($_userOrganizationID);
        if (!$_SWIFT_UserOrganizationObject instanceof SWIFT_UserOrganization || !$_SWIFT_UserOrganizationObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $this->_LoadDisplayData();

        $this->UserInterface->Header(sprintf($this->Language->Get('editorganizationext'), htmlspecialchars($_SWIFT_UserOrganizationObject->GetProperty('organizationname'))), self::MENU_ID, self::NAVIGATION_ID);

        /**
         * ---------------------------------------------
         * SWIFT-219: Staff Permissions Issue [VARUN]
         * ---------------------------------------------
         */
//        if ($_SWIFT->Staff->GetPermission('staff_canupdateuserorganization') == '0')
//        {
//            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
//        } else {
        $this->View->Render(SWIFT_UserInterface::MODE_EDIT, $_SWIFT_UserOrganizationObject);
//        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Edit Submission Processor
     *
     * @author Varun Shoor
     * @param int $_userOrganizationID The User Organization ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function EditSubmit($_userOrganizationID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_userOrganizationID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $_SWIFT_UserOrganizationObject = new SWIFT_UserOrganization($_userOrganizationID);
        if (!$_SWIFT_UserOrganizationObject instanceof SWIFT_UserOrganization || !$_SWIFT_UserOrganizationObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        if ($this->RunChecks(SWIFT_UserInterface::MODE_EDIT, $_SWIFT_UserOrganizationObject->GetUserOrganizationID())) {
            $_updateResult = $_SWIFT_UserOrganizationObject->Update(trim($_POST['organizationname']), $_POST['organizationtype'], $this->_GetPOSTEmailContainer(), $_POST['address'], $_POST['city'], $_POST['state'], $_POST['postalcode'], $_POST['country'], $_POST['phone'], $_POST['fax'], $_POST['website'], $_POST['slaplanid'], GetCalendarDateline($_POST['slaexpirytimeline']));

            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityupdateuserorganization'), htmlspecialchars($_POST['organizationname'])), SWIFT_StaffActivityLog::ACTION_UPDATE, SWIFT_StaffActivityLog::SECTION_USERS, SWIFT_StaffActivityLog::INTERFACE_STAFF);

            if (!$_updateResult) {
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);

                return false;
            }

            // Process the Tags
            if ($_SWIFT->Staff->GetPermission('staff_canupdatetags') != '0') {
                SWIFT_Tag::Process(SWIFT_TagLink::TYPE_USERORGANIZATION, $_SWIFT_UserOrganizationObject->GetUserOrganizationID(), SWIFT_UserInterface::GetMultipleInputValues('tags'), $_SWIFT->Staff->GetStaffID());
            }

            // Update Custom Field Values
            $this->CustomFieldManager->Update(SWIFT_CustomFieldManager::MODE_POST, SWIFT_UserInterface::MODE_EDIT, array(SWIFT_CustomFieldGroup::GROUP_USERORGANIZATION), SWIFT_CustomFieldManager::CHECKMODE_STAFF, $_SWIFT_UserOrganizationObject->GetUserOrganizationID());

            // Begin Hook: staff_userorganization_update
            unset($_hookCode);
            ($_hookCode = SWIFT_Hook::Execute('staff_userorganization_update')) ? eval($_hookCode) : false;
            // End Hook

            $this->_RenderConfirmation(SWIFT_UserInterface::MODE_EDIT);

            $this->Load->Manage();

            return true;
        }

        $this->Load->Edit($_userOrganizationID);

        return false;
    }

    /**
     * Add a note
     *
     * @author Varun Shoor
     * @param int $_userOrganizationID The User Organization ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function AddNote($_userOrganizationID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_userOrganizationID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $_SWIFT_UserOrganizationObject = new SWIFT_UserOrganization($_userOrganizationID);
        if (!$_SWIFT_UserOrganizationObject instanceof SWIFT_UserOrganization || !$_SWIFT_UserOrganizationObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $this->UserInterface->Header($this->Language->Get('users') . ' > ' . $this->Language->Get('addnote'), self::MENU_ID, self::NAVIGATION_ID);
        $this->View->RenderNoteForm(SWIFT_UserInterface::MODE_INSERT, $_SWIFT_UserOrganizationObject);
        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Add a Note Submit Processer
     *
     * @author Varun Shoor
     * @param int $_userOrganizationID The User Organization ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function AddNoteSubmit($_userOrganizationID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_userOrganizationID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $_SWIFT_UserOrganizationObject = new SWIFT_UserOrganization($_userOrganizationID);
        if (!$_SWIFT_UserOrganizationObject instanceof SWIFT_UserOrganization || !$_SWIFT_UserOrganizationObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        // Add notes
        if (trim($_POST['userorganizationnotes']) != '') {
            SWIFT_UserOrganizationNote::Create($_SWIFT_UserOrganizationObject, $_POST['userorganizationnotes'], (int)($_POST['notecolor_userorganizationnotes']));
        }

        echo $this->View->RenderUserNotes($_SWIFT_UserOrganizationObject);

        return true;
    }

    /**
     * Edit a note
     *
     * @author Varun Shoor
     * @param int $_userOrganizationID The User Organization ID
     * @param int $_userNoteID The User Note ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function EditNote($_userOrganizationID, $_userNoteID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_userOrganizationID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);

            return false;
        } else if ($_SWIFT->Staff->GetPermission('staff_canupdateusernote') == '0') {
            return false;
        }

        $_SWIFT_UserOrganizationObject = new SWIFT_UserOrganization($_userOrganizationID);
        if (!$_SWIFT_UserOrganizationObject instanceof SWIFT_UserOrganization || !$_SWIFT_UserOrganizationObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $_SWIFT_UserOrganizationNoteObject = new SWIFT_UserOrganizationNote($_userNoteID);
        if (!$_SWIFT_UserOrganizationNoteObject instanceof SWIFT_UserOrganizationNote || !$_SWIFT_UserOrganizationNoteObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $this->UserInterface->Header($this->Language->Get('users') . ' > ' . $this->Language->Get('editnote'), self::MENU_ID, self::NAVIGATION_ID);
        $this->View->RenderNoteForm(SWIFT_UserInterface::MODE_EDIT, $_SWIFT_UserOrganizationObject, $_SWIFT_UserOrganizationNoteObject);
        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Edit a note processor
     *
     * @author Varun Shoor
     * @param int $_userOrganizationID The User Organization ID
     * @param int $_userNoteID The User Note ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function EditNoteSubmit($_userOrganizationID, $_userNoteID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_userOrganizationID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);

            return false;
        } else if ($_SWIFT->Staff->GetPermission('staff_canupdateusernote') == '0') {
            return false;
        }

        $_SWIFT_UserOrganizationObject = new SWIFT_UserOrganization($_userOrganizationID);
        if (!$_SWIFT_UserOrganizationObject instanceof SWIFT_UserOrganization || !$_SWIFT_UserOrganizationObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $_SWIFT_UserOrganizationNoteObject = new SWIFT_UserOrganizationNote($_userNoteID);
        if (!$_SWIFT_UserOrganizationNoteObject instanceof SWIFT_UserOrganizationNote || !$_SWIFT_UserOrganizationNoteObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        // Add notes
        if (trim($_POST['userorganizationnotes']) != '') {
            $_SWIFT_UserOrganizationNoteObject->Update($_SWIFT->Staff->GetStaffID(), $_SWIFT->Staff->GetProperty('fullname'), $_POST['userorganizationnotes'], (int)($_POST['notecolor_userorganizationnotes']));
        }

        echo $this->View->RenderUserNotes($_SWIFT_UserOrganizationObject);

        return true;
    }

    /**
     * Delete Note Processer
     *
     * @author Varun Shoor
     * @param int $_userOrganizationID The User Organization ID
     * @param int $_userNoteID The User Note ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function DeleteNote($_userOrganizationID, $_userNoteID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_userOrganizationID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);

            return false;
        } else if ($_SWIFT->Staff->GetPermission('staff_candeleteusernote') == '0') {
            return false;
        }

        $_SWIFT_UserOrganizationObject = new SWIFT_UserOrganization($_userOrganizationID);
        if (!$_SWIFT_UserOrganizationObject instanceof SWIFT_UserOrganization || !$_SWIFT_UserOrganizationObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $_SWIFT_UserOrganizationNoteObject = new SWIFT_UserOrganizationNote($_userNoteID);
        if (!$_SWIFT_UserOrganizationNoteObject instanceof SWIFT_UserOrganizationNote || !$_SWIFT_UserOrganizationNoteObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $_SWIFT_UserOrganizationNoteObject->Delete();

        echo $this->View->RenderUserNotes($_SWIFT_UserOrganizationObject);

        return true;
    }

    /**
     * Render the Merge Organization Dialog
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function _MergeOrganizationDialog()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $this->UserInterface->Header($this->Language->Get('users') . ' > ' . $this->Language->Get('mergeorganization'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('staff_canupdateuserorganization') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->RenderMergeOrganization();
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * The Organization List
     *
     * @author Varun Shoor
     * @param array $_userOrganizationIDList The User Organization ID List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function MergeOrganizationList($_userOrganizationIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_userOrganizationIDList) || !isset($_POST['primaryorganizationid']) || empty($_POST['primaryorganizationid'])) {
            return false;
        }


        // BEGIN CSRF HASH CHECK

        if (!SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('staff_canupdateuserorganization') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        $_userOrganizationContainer = array();
        $_primaryOrganizationName = '';
        if (_is_array($_userOrganizationIDList)) {
            $_SWIFT->Database->Query("SELECT userorganizationid, organizationname FROM " . TABLE_PREFIX . "userorganizations
                WHERE userorganizationid IN (" . BuildIN($_userOrganizationIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                $_userOrganizationContainer[$_SWIFT->Database->Record['userorganizationid']] = $_SWIFT->Database->Record['organizationname'];

                if ($_POST['primaryorganizationid'] == $_SWIFT->Database->Record['userorganizationid']) {
                    $_primaryOrganizationName = $_SWIFT->Database->Record['organizationname'];
                }
            }
        }

        // Update Organization?
        if (_is_array($_userOrganizationContainer)) {
            foreach ($_userOrganizationContainer as $_userOrganizationID => $_organizationName) {
                SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activitymergeuserorg'), htmlspecialchars($_organizationName), htmlspecialchars($_primaryOrganizationName)),
                    SWIFT_StaffActivityLog::ACTION_UPDATE, SWIFT_StaffActivityLog::SECTION_USERS, SWIFT_StaffActivityLog::INTERFACE_STAFF);
            }

            SWIFT_UserOrganization::MergeList($_POST['primaryorganizationid'], $_userOrganizationIDList);
        }

        return true;
    }
}
