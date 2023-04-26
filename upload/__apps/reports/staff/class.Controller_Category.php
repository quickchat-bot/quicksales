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

use Base\Admin\Controller_Staff;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel;
use Base\Models\Staff\SWIFT_StaffActivityLog;

/**
 * The Reports Category Controller
 *
 * @author Varun Shoor
 *
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @property Controller_Category $Load
 * @property View_Category $View
 */
class Controller_Category extends Controller_staff
{
    // Core Constants
    const MENU_ID = 9;
    const NAVIGATION_ID = 1;

    /**
     * Constructor
     *
     * @author Varun Shoor
     */
    public function __construct()
    {
        parent::__construct();

        $this->Language->Load('reports_main');
        $this->Language->Load('reports_categories');
    }

    /**
     * Delete the Report Categories from Mass Action
     *
     * @author Varun Shoor
     * @param mixed $_reportCategoryIDList The Report Category ID List Container Array
     * @param bool $_byPassCSRF Whether to bypass the CSRF check
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function DeleteList($_reportCategoryIDList, $_byPassCSRF = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash']))
        {
            SWIFT::Notify(SWIFT::NOTIFICATION_ERROR, $_SWIFT->Language->Get('notifycsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('staff_rcandeletecategory') == '0') {
            SWIFT::Notify(SWIFT::NOTIFICATION_ERROR, $_SWIFT->Language->Get('notifynoperm'));

            return false;
        }

        if (_is_array($_reportCategoryIDList)) {
            $_SWIFT->Database->Query("SELECT reportcategories.*, staffgroup.staffgroupid AS staffgroupid FROM " . TABLE_PREFIX . "reportcategories AS reportcategories
                LEFT JOIN " . TABLE_PREFIX . "staff AS staff ON (reportcategories.staffid = staff.staffid)
                LEFT JOIN " . TABLE_PREFIX . "staffgroup AS staffgroup ON (staff.staffgroupid = staffgroup.staffgroupid)
                WHERE reportcategories.reportcategoryid IN (" . BuildIN($_reportCategoryIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                if (($_SWIFT->Database->Record['visibilitytype'] == SWIFT_ReportCategory::VISIBLE_PRIVATE && $_SWIFT->Database->Record['staffid'] != $_SWIFT->Staff->GetStaffID())
                        || ($_SWIFT->Database->Record['visibilitytype'] == SWIFT_ReportCategory::VISIBLE_TEAM && $_SWIFT->Database->Record['staffgroupid'] != $_SWIFT->Staff->GetProperty('staffgroupid'))) {
                    throw new SWIFT_Exception('You dont have permission to delete this report');
                }

                SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activitydeletereportcategory'), htmlspecialchars($_SWIFT->Database->Record['title'])),
                        SWIFT_StaffActivityLog::ACTION_DELETE, SWIFT_StaffActivityLog::SECTION_REPORTS, SWIFT_StaffActivityLog::INTERFACE_STAFF);
            }

            SWIFT_ReportCategory::DeleteList($_reportCategoryIDList);
        }

        return true;
    }

    /**
     * Delete the Report Category ID
     *
     * @author Varun Shoor
     * @param int $_reportCategoryID The Report Category ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete($_reportCategoryID)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($_reportCategoryID), true);

        $this->Load->Manage(false, false);

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
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UserInterface->AddNavigationBox($this->Language->Get('quickfilter'), SWIFT_ReportTreeRender::Render());

        return true;
    }

    /**
     * Displays the Report Categories
     *
     * @author Varun Shoor
     * @param int|bool $_searchStoreID (OPTIONAL) The Search Store ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Manage($_searchStoreID = false, $_param2 = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_LoadDisplayData();

        $this->UserInterface->Header($this->Language->Get('reports') . ' > ' . $this->Language->Get('categories'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('staff_rcanviewcategories') == '0')
        {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            // $this->View->RenderGrid($_searchStoreID); PHPStan recommended to remove the parameter
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
     * @param SWIFT_ReportCategory|null $_SWIFT_ReportCategoryObject (OPTIONAL) The Report Category Object Pointer
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    private function RunChecks($_mode, SWIFT_ReportCategory $_SWIFT_ReportCategoryObject = null)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!SWIFT_Session::CheckCSRFHash($_POST['csrfhash']))
        {
            SWIFT::Notify(SWIFT::NOTIFICATION_ERROR, $_SWIFT->Language->Get('notifycsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (trim($_POST['title']) == '' || trim($_POST['visibilitytype']) == '')
        {
            $this->UserInterface->CheckFields('title');

            SWIFT::Notify(SWIFT::NOTIFICATION_ERROR, $_SWIFT->Language->Get('notifyfieldempty'));

            return false;
        } else if (SWIFT::Get('isdemo') == true) {
            SWIFT::Notify(SWIFT::NOTIFICATION_ERROR, $_SWIFT->Language->Get('notifydemomode'));

            return false;
        }

        if (($_mode == SWIFT_UserInterface::MODE_INSERT && $_SWIFT->Staff->GetPermission('staff_rcaninsertcategory') == '0') ||
                ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT->Staff->GetPermission('staff_rcanupdatecategory') == '0')) {
            SWIFT::Notify(SWIFT::NOTIFICATION_ERROR, $_SWIFT->Language->Get('notifynoperm'));

            return false;
        }

        return true;
    }

    /**
     * Insert a new Report Category
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

        $this->_LoadDisplayData();

        $this->UserInterface->Header($this->Language->Get('reports') . ' > ' . $this->Language->Get('categories') . ' > ' . $this->Language->Get('insert'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('staff_rcaninsertcategory') == '0')
        {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render(SWIFT_UserInterface::MODE_INSERT, null);
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

        SWIFT::Notify(SWIFT::NOTIFICATION_INFO, sprintf($this->Language->Get('notifyreportcategory' . $_type), htmlspecialchars($_POST['title'])));

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
            $_reportCategoryID = SWIFT_ReportCategory::Create($_POST['title'], $_POST['visibilitytype'], $_SWIFT->Staff);

            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityinsertreportcategory'), htmlspecialchars($_POST['title'])), SWIFT_StaffActivityLog::ACTION_INSERT,
                    SWIFT_StaffActivityLog::SECTION_REPORTS, SWIFT_StaffActivityLog::INTERFACE_STAFF);

            if (!$_reportCategoryID)
            {
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            }

            $this->_RenderConfirmation(SWIFT_UserInterface::MODE_INSERT);

            $this->Load->Manage(false, false);

            return true;
        }

        $this->Load->Insert();

        return false;
    }

    /**
     * Edit the Report Category ID
     *
     * @author Varun Shoor
     * @param int $_reportCategoryID The Report Category ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Edit($_reportCategoryID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_reportCategoryID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_ReportCategoryObject = new SWIFT_ReportCategory(new SWIFT_DataID($_reportCategoryID));
        if (!$_SWIFT_ReportCategoryObject instanceof SWIFT_ReportCategory || !$_SWIFT_ReportCategoryObject->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->_LoadDisplayData();

        $this->UserInterface->Header($this->Language->Get('reports') . ' > ' . $this->Language->Get('categories') . ' > ' . $this->Language->Get('edit'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('staff_rcanupdatecategory') == '0')
        {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render(SWIFT_UserInterface::MODE_EDIT, $_SWIFT_ReportCategoryObject);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Edit Submission Processor
     *
     * @author Varun Shoor
     * @param int $_reportCategoryID The Report Category ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function EditSubmit($_reportCategoryID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_reportCategoryID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_ReportCategoryObject = new SWIFT_ReportCategory(new SWIFT_DataID($_reportCategoryID));
        if (!$_SWIFT_ReportCategoryObject instanceof SWIFT_ReportCategory || !$_SWIFT_ReportCategoryObject->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if ($this->RunChecks(SWIFT_UserInterface::MODE_EDIT, $_SWIFT_ReportCategoryObject))
        {
            $_updateResult = $_SWIFT_ReportCategoryObject->Update($_POST['title'], $_POST['visibilitytype'], $_SWIFT->Staff);

            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityupdatereportcategory'), htmlspecialchars($_POST['title'])), SWIFT_StaffActivityLog::ACTION_UPDATE,
                    SWIFT_StaffActivityLog::SECTION_REPORTS, SWIFT_StaffActivityLog::INTERFACE_STAFF);

            if (!$_updateResult)
            {
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            }

            $this->_RenderConfirmation(SWIFT_UserInterface::MODE_EDIT);

            $this->Load->Manage(false, false);

            return true;
        }

        $this->Load->Edit($_reportCategoryID);

        return false;
    }

}
