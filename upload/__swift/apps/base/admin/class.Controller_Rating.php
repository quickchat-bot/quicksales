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

namespace Base\Admin;

use Controller_admin;
use SWIFT;
use SWIFT_Exception;
use Base\Models\Rating\SWIFT_Rating;
use SWIFT_Session;
use Base\Models\Staff\SWIFT_StaffActivityLog;
use Base\Library\UserInterface\SWIFT_UserInterface;

/**
 * The Rating Controller
 *
 * @property \Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel $UserInterface
 * @property View_Rating $View
 * @author Varun Shoor
 */
class Controller_Rating extends Controller_admin
{
    // Core Constants
    const MENU_ID = 1;
    const NAVIGATION_ID = 5;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct()
    {
        parent::__construct();

        $this->Language->Load('admin_ratings');
    }

    /**
     * Resort the ratings
     *
     * @author Varun Shoor
     * @param mixed $_ratingIDSortList The Rating ID Sort List Container Array
     * @return bool "true" on Success, "false" otherwise
     */
    public static function SortList($_ratingIDSortList)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('admin_canupdaterating') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        SWIFT_Rating::UpdateDisplayOrderList($_ratingIDSortList);

        return true;
    }

    /**
     * Delete the Ratings from Mass Action
     *
     * @author Varun Shoor
     * @param mixed $_ratingIDList The Rating ID List Container Array
     * @param bool $_byPassCSRF Whether to bypass the CSRF check
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_ratingIDList, $_byPassCSRF = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('admin_candeleterating') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        if (_is_array($_ratingIDList)) {
            $_SWIFT->Database->Query("SELECT ratingtitle FROM " . TABLE_PREFIX . "ratings WHERE ratingid IN (" . BuildIN($_ratingIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activitydeleterating'), htmlspecialchars($_SWIFT->Database->Record['ratingtitle'])), SWIFT_StaffActivityLog::ACTION_DELETE, SWIFT_StaffActivityLog::SECTION_RATINGS, SWIFT_StaffActivityLog::INTERFACE_ADMIN);
            }

            SWIFT_Rating::DeleteList($_ratingIDList);
        }

        return true;
    }

    /**
     * Delete the Given Rating ID
     *
     * @author Varun Shoor
     * @param int $_ratingID The Rating ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete($_ratingID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($_ratingID), true);

        $this->Load->Manage();

        return true;
    }

    /**
     * Displays the Rating Grid
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Manage()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UserInterface->Header($this->Language->Get('ratings') . ' > ' . $this->Language->Get('manageratings'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_canviewratings') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->RenderGrid();
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Runs the Checks for Insertion/Editing
     *
     * @author Varun Shoor
     * @param int $_mode The User Interface Mode
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    private function RunChecks($_mode)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        // BEGIN CSRF HASH CHECK

        if (!SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if (trim($_POST['ratingtitle']) == '' || trim($_POST['displayorder']) == '' || ($_mode == SWIFT_UserInterface::MODE_INSERT && trim($_POST['ratingtype']) == '')) {
            $this->UserInterface->CheckFields('ratingtitle', 'displayorder', 'ratingtype');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            return false;
        } elseif (SWIFT::Get('isdemo') == true) {
            $this->UserInterface->Error($this->Language->Get('titledemomode'), $this->Language->Get('msgdemomode'));

            return false;
        }

        if (($_mode == SWIFT_UserInterface::MODE_INSERT && $_SWIFT->Staff->GetPermission('admin_caninsertrating') == '0') || ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT->Staff->GetPermission('admin_canupdaterating') == '0')) {
            $this->UserInterface->Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));

            return false;
        }

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
        }

        if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            $_type = 'update';
        } else {
            $_type = 'insert';
        }

        $_finalText = '<b>' . $this->Language->Get('ratingtitle') . ':</b> ' . htmlspecialchars($_POST['ratingtitle']) . '<br />';
        $_finalText .= '<b>' . $this->Language->Get('displayorder') . ':</b> ' . (int)($_POST['displayorder']) . '<br />';
        $_finalText .= '<b>' . $this->Language->Get('ratingvisibility') . ':</b> ' . IIF($_POST['ratingvisibility'] == '1', $this->Language->Get('public'), $this->Language->Get('private')) . '<br />';
        $_finalText .= '<b>' . $this->Language->Get('iseditable') . ':</b> ' . IIF($_POST['iseditable'] == '1', $this->Language->Get('yes'), $this->Language->Get('no')) . '<br />';
        $_finalText .= '<b>' . $this->Language->Get('isclientonly') . ':</b> ' . IIF($_POST['isclientonly'] == '1', $this->Language->Get('yes'), $this->Language->Get('no')) . '<br />';
        $_finalText .= '<b>' . $this->Language->Get('ratingscale') . ':</b> ' . (int)($_POST['ratingscale']) . '<br />';
        $_finalText .= '<b>' . $this->Language->Get('ratingtype') . ':</b> ' . SWIFT_Rating::GetLabel($_POST['ratingtype']) . '<br />';

        SWIFT::Info(sprintf($this->Language->Get('titlerating' . $_type), htmlspecialchars($_POST['ratingtitle'])), sprintf($this->Language->Get('msgrating' . $_type), htmlspecialchars($_POST['ratingtitle'])) . '<br />' . $_finalText);

        return true;
    }

    /**
     * Insert a new Rating
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

        $this->UserInterface->Header($this->Language->Get('ratings') . ' > ' . $this->Language->Get('insert'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_caninsertrating') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render(SWIFT_UserInterface::MODE_INSERT);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Retrieve the Assigned Staff Group ID list for Insert/Edit Processing
     *
     * @author Varun Shoor
     * @return mixed "_assignedStaffGroupIDList" (ARRAY) on Success, "false" otherwise
     */
    private function _GetAssignedStaffGroupIDList()
    {
        if (!$this->GetIsClassLoaded() || !isset($_POST['staffgroupidlist']) || !_is_array($_POST['staffgroupidlist'])) {
            return array();
        }

        $_assignedStaffGroupIDList = array();
        foreach ($_POST['staffgroupidlist'] as $_key => $_val) {
            if ($_val == '1') {
                $_assignedStaffGroupIDList[] = (int)($_key);
            }
        }

        return $_assignedStaffGroupIDList;
    }

    /**
     * Retrieve the Assigned User Group ID list for Insert/Edit Processing
     *
     * @author Varun Shoor
     * @return mixed "_assignedUserGroupIDList" (ARRAY) on Success, "false" otherwise
     */
    private function _GetAssignedUserGroupIDList()
    {
        if (!$this->GetIsClassLoaded() || !isset($_POST['usergroupidlist']) || !_is_array($_POST['usergroupidlist'])) {
            return array();
        }

        $_assignedUserGroupIDList = array();
        foreach ($_POST['usergroupidlist'] as $_key => $_val) {
            if ($_val == '1') {
                $_assignedUserGroupIDList[] = (int)($_key);
            }
        }

        return $_assignedUserGroupIDList;
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
            $_ratingID = SWIFT_Rating::Create($_POST['ratingtitle'], $_POST['displayorder'], $_POST['ratingtype'], $_POST['departmentid'],
                $_POST['iseditable'], $_POST['isclientonly'], $_POST['ratingscale'], IIF($_POST['ratingvisibility'] == '1', SWIFT_PUBLIC, SWIFT_PRIVATE), $_POST['staffvisibilitycustom'],
                $_POST['uservisibilitycustom'], $this->_GetAssignedStaffGroupIDList(), $this->_GetAssignedUserGroupIDList());

            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityinsertrating'), htmlspecialchars($_POST['ratingtitle'])), SWIFT_StaffActivityLog::ACTION_INSERT, SWIFT_StaffActivityLog::SECTION_RATINGS, SWIFT_StaffActivityLog::INTERFACE_ADMIN);

            if (!$_ratingID) {
                return false;
            }

            $this->_RenderConfirmation(SWIFT_UserInterface::MODE_INSERT);

            $this->Load->Manage();

            return true;
        }

        $this->Load->Insert();

        return false;
    }

    /**
     * Edit the Rating ID
     *
     * @author Varun Shoor
     * @param int $_ratingID The Rating ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Edit($_ratingID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } elseif (empty($_ratingID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_RatingObject = new SWIFT_Rating($_ratingID);
        if (!$_SWIFT_RatingObject instanceof SWIFT_Rating || !$_SWIFT_RatingObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->UserInterface->Header($this->Language->Get('ratings') . ' > ' . $this->Language->Get('edit'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_canupdaterating') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render(SWIFT_UserInterface::MODE_EDIT, $_SWIFT_RatingObject);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Edit Submission Processor
     *
     * @author Varun Shoor
     * @param int $_ratingID The Rating ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function EditSubmit($_ratingID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } elseif (empty($_ratingID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_RatingObject = new SWIFT_Rating($_ratingID);
        if (!$_SWIFT_RatingObject instanceof SWIFT_Rating || !$_SWIFT_RatingObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if ($this->RunChecks(SWIFT_UserInterface::MODE_EDIT)) {
            $_departmentID = 0;
            if (isset($_POST['departmentid']) && is_numeric($_POST['departmentid'])) {
                $_departmentID = (int)($_POST['departmentid']);
            }

            $_updateResult = $_SWIFT_RatingObject->Update($_POST['ratingtitle'], $_POST['displayorder'], $_departmentID, $_POST['iseditable'], $_POST['isclientonly'],
                $_POST['ratingscale'], IIF($_POST['ratingvisibility'] == '1', SWIFT_PUBLIC, SWIFT_PRIVATE), $_POST['staffvisibilitycustom'], $_POST['uservisibilitycustom'],
                $this->_GetAssignedStaffGroupIDList(), $this->_GetAssignedUserGroupIDList());
            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityupdaterating'), htmlspecialchars($_POST['ratingtitle'])), SWIFT_StaffActivityLog::ACTION_UPDATE, SWIFT_StaffActivityLog::SECTION_RATINGS, SWIFT_StaffActivityLog::INTERFACE_ADMIN);

            if (!$_updateResult) {
                return false;
            }

            $this->_RenderConfirmation(SWIFT_UserInterface::MODE_EDIT);

            $this->Load->Manage();

            return true;
        }

        $this->Load->Edit($_ratingID);

        return false;
    }
}

?>
