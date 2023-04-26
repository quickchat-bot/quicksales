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

namespace Troubleshooter\Staff;

use Base\Admin\Controller_Staff;
use SWIFT;
use SWIFT_DataID;
use SWIFT_Exception;
use SWIFT_Loader;
use SWIFT_Session;
use Base\Models\Staff\SWIFT_StaffActivityLog;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel;
use Troubleshooter\Models\Category\SWIFT_TroubleshooterCategory;

/**
 * The Category Controller
 *
 * @author Varun Shoor
 * @property Controller_Category $Load
 * @property View_Category $View
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 */
class Controller_Category extends Controller_Staff
{
    // Core Constants
    const MENU_ID = 6;
    const NAVIGATION_ID = 1;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @throws SWIFT_Exception
     */
    public function __construct()
    {
        parent::__construct();

        $this->Language->Load('staff_troubleshooter');
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
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return true;
    }

    /**
     * Delete the Troubleshooter Categories from Mass Action
     *
     * @author Varun Shoor
     * @param mixed $_troubleshooterCategoryIDList The Troubleshooter Category ID List Container Array
     * @param bool $_byPassCSRF Whether to bypass the CSRF check
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function DeleteList($_troubleshooterCategoryIDList, $_byPassCSRF = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash']))
        {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('staff_trcandeletecategory') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        if (_is_array($_troubleshooterCategoryIDList)) {
            $_SWIFT->Database->Query("SELECT title FROM " . TABLE_PREFIX . "troubleshootercategories WHERE troubleshootercategoryid IN (" . BuildIN($_troubleshooterCategoryIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activitydeletetroubleshootercategory'),
                        htmlspecialchars($_SWIFT->Database->Record['title'])), SWIFT_StaffActivityLog::ACTION_DELETE,
                        SWIFT_StaffActivityLog::SECTION_TROUBLESHOOTER, SWIFT_StaffActivityLog::INTERFACE_STAFF);
            }

            SWIFT_TroubleshooterCategory::DeleteList($_troubleshooterCategoryIDList);
        }

        return true;
    }

    /**
     * Delete the Given Troubleshooter Category ID
     *
     * @author Varun Shoor
     * @param int $_troubleshooterCategoryID The Troubleshooter Category ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete($_troubleshooterCategoryID)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($_troubleshooterCategoryID), true);

        $this->Load->Manage();

        return true;
    }

    /**
     * Displays the Troubleshooter Category Grid
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Manage($_reportID = 0)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_LoadDisplayData();

        $this->UserInterface->Header($this->Language->Get('troubleshooter') . ' > ' . $this->Language->Get('categories'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('staff_trcanviewcategories') == '0')
        {
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
     * @param int $_troubleshooterCategoryID The Troubleshooter Category ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    private function RunChecks($_mode, $_troubleshooterCategoryID = 0)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!SWIFT_Session::CheckCSRFHash($_POST['csrfhash']))
        {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (trim($_POST['title']) == '')
        {
            $this->UserInterface->CheckFields('title');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            return false;
        }

        if (SWIFT::Get('isdemo') == true) {
            $this->UserInterface->Error($this->Language->Get('titledemomode'), $this->Language->Get('msgdemomode'));

            return false;
        }

        if (($_mode == SWIFT_UserInterface::MODE_INSERT && $_SWIFT->Staff->GetPermission('staff_trcaninsertcategory') == '0') ||
                ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT->Staff->GetPermission('staff_trcanupdatecategory') == '0')) {
            $this->UserInterface->Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));

            return false;
        }

        $_troubleshooterCategoryContainer = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "troubleshootercategories WHERE LCASE(title) = '" . $this->Database->Escape(mb_strtolower($_POST['title'])) . "'");
        if (isset($_troubleshooterCategoryContainer['troubleshootercategoryid']) && $_troubleshooterCategoryContainer['troubleshootercategoryid'] != $_troubleshooterCategoryID)
        {
            $this->UserInterface->Error(
                    sprintf($this->Language->Get('titletrcatmismatch'), htmlspecialchars($_troubleshooterCategoryContainer['title'])),
                    sprintf($this->Language->Get('msgtrcatmismatch'), htmlspecialchars($_troubleshooterCategoryContainer['title'])));

            return false;
        }

        return true;
    }

    /**
     * Insert a new Category
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Insert()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UserInterface->Header($this->Language->Get('troubleshooter') . ' > ' . $this->Language->Get('insertcategory'), self::MENU_ID,
                self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('staff_trcaninsertcategory') == '0')
        {
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
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_type = 'insert';
        if ($_mode == SWIFT_UserInterface::MODE_EDIT)
        {
            $_type = 'update';
        }

        $_categoryType = $this->Language->Get('private');
        if ($_POST['categorytype'] == SWIFT_TroubleshooterCategory::TYPE_GLOBAL)
        {
            $_categoryType = $this->Language->Get('global');
        } else if ($_POST['categorytype'] == SWIFT_TroubleshooterCategory::TYPE_PUBLIC) {
            $_categoryType = $this->Language->Get('public');
        } else if ($_POST['categorytype'] == SWIFT_TroubleshooterCategory::TYPE_PRIVATE) {
            $_categoryType = $this->Language->Get('private');
        }

        $_finalText = '<b>' . $this->Language->Get('categorytitle') . ':</b> ' . htmlspecialchars($_POST['title']) . '<br />';
        $_finalText .= '<b>' . $this->Language->Get('categorytype') . ':</b> ' . $_categoryType . '<br />';

        SWIFT::Info(sprintf($this->Language->Get('titletrcategory' . $_type), htmlspecialchars($_POST['title'])),
                sprintf($this->Language->Get('msgtrcategory' . $_type), htmlspecialchars($_POST['title'])) . '<br />' . $_finalText);

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

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->RunChecks(SWIFT_UserInterface::MODE_INSERT))
        {
            $_troubleshooterCategoryID = SWIFT_TroubleshooterCategory::Create($_POST['title'], $_POST['description'], $_POST['categorytype'], $_POST['displayorder'],
                    $_POST['uservisibilitycustom'], $this->_GetUserGroupIDList(), $_POST['staffvisibilitycustom'], $this->_GetStaffGroupIDList(), $_SWIFT->Staff);

            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityinserttrcategory'), htmlspecialchars($_POST['title'])),
                    SWIFT_StaffActivityLog::ACTION_INSERT, SWIFT_StaffActivityLog::SECTION_TROUBLESHOOTER, SWIFT_StaffActivityLog::INTERFACE_STAFF);

            if (!$_troubleshooterCategoryID)
            {
                // @codeCoverageIgnoreStart
                // This code will never be executed
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
                // @codeCoverageIgnoreEnd
            }

            $this->_RenderConfirmation(SWIFT_UserInterface::MODE_INSERT);

            $this->Load->Manage();

            return true;
        }

        $this->Load->Insert();

        return false;
    }

    /**
     * Edit the Troubleshooter Category
     *
     * @author Varun Shoor
     * @param int $_troubleshooterCategoryID The Troubleshooter Category ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Edit($_troubleshooterCategoryID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_troubleshooterCategoryID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_TroubleshooterCategoryObject = new SWIFT_TroubleshooterCategory(new SWIFT_DataID($_troubleshooterCategoryID));
        if (!$_SWIFT_TroubleshooterCategoryObject instanceof SWIFT_TroubleshooterCategory || !$_SWIFT_TroubleshooterCategoryObject->GetIsClassLoaded())
        {
            // @codeCoverageIgnoreStart
            // This code will never be executed
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }

        $this->UserInterface->Header($this->Language->Get('troubleshooter') . ' > ' . $this->Language->Get('editcategory'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('staff_trcanupdatecategory') == '0')
        {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render(SWIFT_UserInterface::MODE_EDIT, $_SWIFT_TroubleshooterCategoryObject);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Edit Submission Processor
     *
     * @author Varun Shoor
     * @param int $_troubleshooterCategoryID The Troubleshooter Category ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function EditSubmit($_troubleshooterCategoryID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_troubleshooterCategoryID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_TroubleshooterCategoryObject = new SWIFT_TroubleshooterCategory(new SWIFT_DataID($_troubleshooterCategoryID));
        if (!$_SWIFT_TroubleshooterCategoryObject instanceof SWIFT_TroubleshooterCategory || !$_SWIFT_TroubleshooterCategoryObject->GetIsClassLoaded())
        {
            // @codeCoverageIgnoreStart
            // This code will never be executed
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }

        if ($this->RunChecks(SWIFT_UserInterface::MODE_EDIT, $_troubleshooterCategoryID))
        {
            $_updateResult = $_SWIFT_TroubleshooterCategoryObject->Update($_POST['title'], $_POST['description'], $_POST['categorytype'], $_POST['displayorder'],
                    $_POST['uservisibilitycustom'], $this->_GetUserGroupIDList(), $_POST['staffvisibilitycustom'], $this->_GetStaffGroupIDList(), $_SWIFT->Staff);

            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityupdatetrcategory'), htmlspecialchars($_POST['title'])),
                    SWIFT_StaffActivityLog::ACTION_UPDATE, SWIFT_StaffActivityLog::SECTION_TROUBLESHOOTER, SWIFT_StaffActivityLog::INTERFACE_STAFF);

            if (!$_updateResult)
            {
                // @codeCoverageIgnoreStart
                // This code will never be executed
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
                // @codeCoverageIgnoreEnd
            }

            $this->_RenderConfirmation(SWIFT_UserInterface::MODE_EDIT);

            $this->Load->Manage();

            return true;
        }

        $this->Load->Edit($_troubleshooterCategoryID);

        return false;
    }

    /**
     * Retrieve the Assigned Staff Group ID list for Insert/Edit Processing
     *
     * @author Varun Shoor
     * @return mixed "_assignedStaffGroupIDList" (ARRAY) on Success, "false" otherwise
     */
    private function _GetStaffGroupIDList()
    {
        if (!$this->GetIsClassLoaded() || !isset($_POST['staffgroupidlist']) || !_is_array($_POST['staffgroupidlist']))
        {
            return array();
        }

        $_assignedStaffGroupIDList = array();
        foreach ($_POST['staffgroupidlist'] as $_key => $_val)
        {
            if ($_val == '1')
            {
                $_assignedStaffGroupIDList[] = (int) ($_key);
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
    private function _GetUserGroupIDList()
    {
        if (!$this->GetIsClassLoaded() || !isset($_POST['usergroupidlist']) || !_is_array($_POST['usergroupidlist']))
        {
            return array();
        }

        $_assignedUserGroupIDList = array();
        foreach ($_POST['usergroupidlist'] as $_key => $_val)
        {
            if ($_val == '1')
            {
                $_assignedUserGroupIDList[] = (int) ($_key);
            }
        }

        return $_assignedUserGroupIDList;
    }

    /**
     * The Troubleshooter Category Rendering Function
     *
     * @author Varun Shoor
     * @param int $_troubleshooterCategoryID (OPTIONAL) The Troubleshooter Category ID Preselected
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ViewAll($_troubleshooterCategoryID = 0)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_troubleshooterCategoryContainer = SWIFT_TroubleshooterCategory::Retrieve(array(SWIFT_TroubleshooterCategory::TYPE_GLOBAL, SWIFT_TroubleshooterCategory::TYPE_PRIVATE),
                $_SWIFT->Staff->GetProperty('staffgroupid'), 0);

        foreach ($_troubleshooterCategoryContainer as $_loopTroubleshooterCategoryID => $_troubleshooterCategory) {
            $_troubleshooterCategoryContainer[$_loopTroubleshooterCategoryID]['description'] = nl2br(htmlspecialchars($_troubleshooterCategory['description']));
        }

        $this->_LoadDisplayData();

        $this->UserInterface->Header($this->Language->Get('troubleshooter') . ' > ' . $this->Language->Get('categories'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('staff_trcanviewsteps') == '0')
        {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->RenderViewAll($_troubleshooterCategoryContainer);
        }

        $this->UserInterface->Footer();

        return true;
    }
}
?>
