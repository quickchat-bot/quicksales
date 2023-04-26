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
use Base\Library\KQL\SWIFT_KQLSchema;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel;
use Base\Library\UserInterface\SWIFT_UserInterfaceGrid;
use Base\Models\SearchStore\SWIFT_SearchStore;
use Base\Models\Staff\SWIFT_StaffActivityLog;

/**
 * The Reports Controller
 *
 * @author Varun Shoor
 *
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @property Controller_Report $Load
 * @property View_Report $View
 */
class Controller_Report extends Controller_staff
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
     * Delete the Reports from Mass Action
     *
     * @author Varun Shoor
     * @param mixed $_reportIDList The Report ID List Container Array
     * @param bool $_byPassCSRF Whether to bypass the CSRF check
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_reportIDList, $_byPassCSRF = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash']))
        {
            SWIFT::Notify(SWIFT::NOTIFICATION_ERROR, $_SWIFT->Language->Get('notifycsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('staff_rcandeletereport') == '0') {
            SWIFT::Notify(SWIFT::NOTIFICATION_ERROR, $_SWIFT->Language->Get('notifynoperm'));

            return false;
        }

        if (_is_array($_reportIDList)) {
            $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "reports WHERE reportid IN (" . BuildIN($_reportIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                /*
                 * BUG FIX - Varun Shoor
                 *
                 * SWIFT-1996 Not deletable private reports
                 *
                 */
                if ($_SWIFT->Database->Record['visibilitytype'] == SWIFT_Report::VISIBLE_PRIVATE && $_SWIFT->Database->Record['creatorstaffid'] != $_SWIFT->Staff->GetStaffID()) {
                    throw new SWIFT_Exception('You dont have permission to delete this report');
                }

                SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activitydeletereport'), htmlspecialchars($_SWIFT->Database->Record['title'])),
                        SWIFT_StaffActivityLog::ACTION_DELETE, SWIFT_StaffActivityLog::SECTION_REPORTS, SWIFT_StaffActivityLog::INTERFACE_STAFF);
            }

            SWIFT_Report::DeleteList($_reportIDList);
        }

        return true;
    }

    /**
     * Delete the Report
     *
     * @author Varun Shoor
     * @param int $_reportID The Report ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete($_reportID)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($_reportID), true);

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

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_searchStoreID = -1;

        $_reportIDList = array();

        $_filterType = strtolower($_filterType);

        $_gridSortContainer = SWIFT_UserInterfaceGrid::GetGridSortField('reportgrid', 'reports.title', 'asc');

        switch ($_filterType)
        {
            case 'myreports': {
                $this->Database->Query("SELECT reportid FROM " . TABLE_PREFIX . "reports WHERE creatorstaffid = '" . (int) ($_SWIFT->Staff->GetStaffID()) . "'");
                while ($this->Database->NextRecord()) {
                    $_reportIDList[] = $this->Database->Record['reportid'];
                }

            }
            break;

            case 'recent': {
                $_baseReportIDList = array();
                $this->Database->Query("SELECT DISTINCT(reportid) FROM " . TABLE_PREFIX . "reportusagelogs WHERE staffid = '" . (int) ($_SWIFT->Staff->GetStaffID()) . "' ORDER BY dateline DESC");
                while ($this->Database->NextRecord()) {
                    $_baseReportIDList[] = $this->Database->Record['reportid'];
                }

                $this->Database->Query("SELECT reports.reportid FROM " . TABLE_PREFIX . "reports AS reports WHERE reports.reportid IN (" . BuildIN($_baseReportIDList) . ")
                    AND (reports.visibilitytype = '" . SWIFT_Report::VISIBLE_PUBLIC . "'
                        OR (reports.visibilitytype = '" . SWIFT_Report::VISIBLE_PRIVATE . "' AND reports.creatorstaffid = '" . (int) ($_SWIFT->Staff->GetStaffID()) . "')
                    )");
                while ($this->Database->NextRecord()) {
                    $_reportIDList[] = $this->Database->Record['reportid'];
                }

            }
            break;

            case 'category': {
                // First retrieve the category
                $_reportCategory = $this->Database->QueryFetch("SELECT reportcategories.*, staffgroup.staffgroupid AS staffgroupid FROM " . TABLE_PREFIX . "reportcategories AS reportcategories
                    LEFT JOIN " . TABLE_PREFIX . "staff AS staff ON (reportcategories.staffid = staff.staffid)
                    LEFT JOIN " . TABLE_PREFIX . "staffgroup AS staffgroup ON (staff.staffgroupid = staffgroup.staffgroupid)
                    WHERE reportcategories.reportcategoryid = '" . (int) ($_filterValue) . "'");

                if ($_reportCategory['visibilitytype'] == SWIFT_ReportCategory::VISIBLE_PUBLIC
                        || ($_reportCategory['visibilitytype'] == SWIFT_ReportCategory::VISIBLE_TEAM && $_reportCategory['staffgroupid'] == $_SWIFT->Staff->GetProperty('staffgroupid'))
                        || ($_reportCategory['visibilitytype'] == SWIFT_ReportCategory::VISIBLE_PRIVATE && $_reportCategory['staffid'] == $_SWIFT->Staff->GetStaffID())) {

                    $this->Database->Query("SELECT reports.reportid AS reportid FROM " . TABLE_PREFIX . "reports AS reports WHERE reports.reportcategoryid = '" . (int) ($_reportCategory['reportcategoryid']) . "'
                        AND (reports.visibilitytype = '" . SWIFT_Report::VISIBLE_PUBLIC . "'
                            OR (reports.visibilitytype = '" . SWIFT_Report::VISIBLE_PRIVATE . "' AND reports.creatorstaffid = '" . (int) ($_SWIFT->Staff->GetStaffID()) . "')
                        )");
                    while ($this->Database->NextRecord()) {
                        $_reportIDList[] = $this->Database->Record['reportid'];
                    }
                }

            }
            break;

            default:
                break;
        }

        $_searchStoreID = SWIFT_SearchStore::Create($_SWIFT->Session->GetSessionID(), SWIFT_SearchStore::TYPE_REPORTS, $_reportIDList, $_SWIFT->Staff->GetStaffID());

        if (!_is_array($_reportIDList))
        {
            SWIFT::Notify(SWIFT::NOTIFICATION_ALERT, $_SWIFT->Language->Get('notifysearchfailed'));
        }

        $this->Load->Manage($_searchStoreID);

        return true;
    }

    /**
     * Displays the Report Grid
     *
     * @author Varun Shoor
     * @param int|bool $_searchStoreID (OPTIONAL) The Search Store ID
     * @param string $param2
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Manage($_searchStoreID = false, $param2 = '')
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_LoadDisplayData();

        $this->UserInterface->Header($this->Language->Get('reports') . ' > ' . $this->Language->Get('manage'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('staff_rcanviewreports') == '0')
        {
            SWIFT::Notify(SWIFT::NOTIFICATION_ERROR, $_SWIFT->Language->Get('notifynoperm'));
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
     * @param SWIFT_Report|null $_SWIFT_ReportObject (OPTIONAL) The Report Object Pointer
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    private function RunChecks($_mode, SWIFT_Report $_SWIFT_ReportObject = null)
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
        }

        if (SWIFT::Get('isdemo') == true) {
            SWIFT::Notify(SWIFT::NOTIFICATION_ERROR, $_SWIFT->Language->Get('notifydemomode'));

            return false;
        }

        if (empty(trim($_POST['kql']))){

            SWIFT::Notify(SWIFT::NOTIFICATION_ERROR, $_SWIFT->Language->Get('notifykqlfieldempty'));

            return false;
        }

        if (($_mode == SWIFT_UserInterface::MODE_INSERT && $_SWIFT->Staff->GetPermission('staff_rcaninsertreport') == '0') ||
                ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT->Staff->GetPermission('staff_rcanupdatereport') == '0')) {
            SWIFT::Notify(SWIFT::NOTIFICATION_ERROR, $_SWIFT->Language->Get('notifynoperm'));

            return false;
        }

        return true;
    }

    /**
     * Render the New Report Dialog
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function InsertDialog()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_LoadDisplayData();

        $this->UserInterface->Header($this->Language->Get('reports') . ' > ' . $this->Language->Get('insert'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('staff_rcaninsertreport') == '0')
        {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->RenderDialog();
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Insert a new Report
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

        if (!isset($_POST['title']) || trim($_POST['title']) == '' || !isset($_POST['basetable']) || trim($_POST['basetable']) == '') {
            $this->UserInterface->CheckFields('title');

            SWIFT::Notify(SWIFT::NOTIFICATION_ERROR, $_SWIFT->Language->Get('notifyfieldempty'));

            $this->Load->InsertDialog();

            return false;
        }


        if (!isset($_POST['reportcategoryid']) || empty($_POST['reportcategoryid']))
        {
            $this->UserInterface->CheckFields('reportcategoryid');

            SWIFT::Notify(SWIFT::NOTIFICATION_ERROR, $_SWIFT->Language->Get('notifyfieldempty') . ': ' . $this->Language->Get('reportcategory'));

            $this->Load->InsertDialog();

            return false;
        }

        $_reportCategoryCache = $this->Cache->Get('reportcategorycache');

        $_baseTableName = $_POST['basetable'];

        $_schemaContainer = SWIFT_KQLSchema::GetCombinedSchema();

        $_baseTableTitle = $_baseTableName;
        if (isset($_schemaContainer[$_baseTableName][SWIFT_KQLSchema::SCHEMA_TABLELABEL])) {
            $_baseTableTitle = SWIFT_KQLSchema::GetLabel($_schemaContainer[$_baseTableName][SWIFT_KQLSchema::SCHEMA_TABLELABEL]);
        }

        $this->View->RenderInfoBox($_reportCategoryCache[$_POST['reportcategoryid']]['title'], $_baseTableTitle, SWIFT_Report::GetVisibilityLabel($_reportCategoryCache[$_POST['reportcategoryid']]['visibilitytype']));

        $this->_LoadDisplayData();

        $this->UserInterface->Header($this->Language->Get('reports') . ' > ' . $this->Language->Get('insert'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('staff_rcaninsertreport') == '0')
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

        SWIFT::Notify(SWIFT::NOTIFICATION_INFO, sprintf($this->Language->Get('notifyreport' . $_type), htmlspecialchars($_POST['title'])));

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
        $_reportCategoryCache = $this->Cache->Get('reportcategorycache');

        if ($this->RunChecks(SWIFT_UserInterface::MODE_INSERT))
        {
            $_reportID = SWIFT_Report::Create($_POST['reportcategoryid'], $_POST['title'], $_POST['basetable'], $_POST['kql'], $_SWIFT->Staff, $_reportCategoryCache[$_POST['reportcategoryid']]['visibilitytype']);

            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityinsertreport'), htmlspecialchars($_POST['title'])), SWIFT_StaffActivityLog::ACTION_INSERT,
                    SWIFT_StaffActivityLog::SECTION_REPORTS, SWIFT_StaffActivityLog::INTERFACE_STAFF);

            if (!$_reportID)
            {
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            }

            $this->_RenderConfirmation(SWIFT_UserInterface::MODE_INSERT);

            $this->Load->Generate($_reportID);

            return true;
        }

        $this->Load->Insert();

        return false;
    }

    /**
     * Edit the Report ID
     *
     * @author Varun Shoor
     * @param int $_reportID The Report ID
     * @param bool $_isSchedulesTabSelected (OPTIONAL) Is Schedules Tab Selected
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Edit($_reportID, $_isSchedulesTabSelected = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_reportID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_ReportObject = new SWIFT_Report(new SWIFT_DataID($_reportID));
        if (!$_SWIFT_ReportObject instanceof SWIFT_Report || !$_SWIFT_ReportObject->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_reportCategoryCache = $this->Cache->Get('reportcategorycache');

        $_baseTableName = $_SWIFT_ReportObject->GetProperty('basetablename');

        $_schemaContainer = SWIFT_KQLSchema::GetCombinedSchema();

        $_baseTableTitle = $_baseTableName;
        if (isset($_schemaContainer[$_baseTableName][SWIFT_KQLSchema::SCHEMA_TABLELABEL])) {
            $_baseTableTitle = SWIFT_KQLSchema::GetLabel($_schemaContainer[$_baseTableName][SWIFT_KQLSchema::SCHEMA_TABLELABEL]);
        }

        $this->View->RenderInfoBox($_reportCategoryCache[$_SWIFT_ReportObject->GetProperty('reportcategoryid')]['title'], $_baseTableTitle, SWIFT_Report::GetVisibilityLabel($_SWIFT_ReportObject->GetProperty('visibilitytype')));

        $this->_LoadDisplayData();

        $this->UserInterface->Header($this->Language->Get('reports') . ' > ' . $this->Language->Get('edit'), self::MENU_ID, self::NAVIGATION_ID);

        /*
         * BUG FIX - Varun Shoor
         *
         * SWIFT-1893 Private reports can be visible to other staff users
         */
        if ($_SWIFT->Staff->GetPermission('staff_rcanviewreports') == '0' || ($_SWIFT_ReportObject->Get('visibilitytype') == SWIFT_Report::VISIBLE_PRIVATE && $_SWIFT_ReportObject->Get('creatorstaffid') != $_SWIFT->Staff->GetStaffID()))
        {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $_rsCount = $this->Database->QueryFetch("SELECT COUNT(*) AS cant FROM " . TABLE_PREFIX . "reportschedules where reportid = " . $_reportID . " and staffid <> " . $_SWIFT->Staff->GetStaffID());
            $_isScheduled = false;
            if (isset($_rsCount['cant']) && $_rsCount['cant'] > 0) {
                $_isScheduled = true;
            }
            $this->View->Render(SWIFT_UserInterface::MODE_EDIT, $_SWIFT_ReportObject, $_isSchedulesTabSelected, $_isScheduled);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Edit Submission Processor
     *
     * @author Varun Shoor
     * @param int $_reportID The Report ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function EditSubmit($_reportID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_reportID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if (trim($_POST['kql']) == '') {
            $this->UserInterface->CheckFields('kql');

            SWIFT::Notify(SWIFT::NOTIFICATION_ERROR, $_SWIFT->Language->Get('notifykqlfieldempty'));

            $this->Load->Edit($_reportID);

            return false;
        }

        $_SWIFT_ReportObject = new SWIFT_Report(new SWIFT_DataID($_reportID));
        if (!$_SWIFT_ReportObject instanceof SWIFT_Report || !$_SWIFT_ReportObject->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        /*
         * BUG FIX - Ravi Sharma
         *
         * SWIFT-3244 Report update permission issue
         */
        if ($_SWIFT->Staff->GetPermission('staff_rcanupdatereport') == '0' || ($_SWIFT_ReportObject->Get('visibilitytype') == SWIFT_Report::VISIBLE_PRIVATE && $_SWIFT_ReportObject->Get('creatorstaffid') != $_SWIFT->Staff->GetStaffID()))
        {
            SWIFT::Notify(SWIFT::NOTIFICATION_ERROR, $_SWIFT->Language->Get('notifynoperm'));

            $this->Load->Edit($_reportID);

            return false;
        }

        if ($this->RunChecks(SWIFT_UserInterface::MODE_EDIT, $_SWIFT_ReportObject))
        {
            $_updateResult = $_SWIFT_ReportObject->Update($_SWIFT_ReportObject->GetProperty('title'), $_POST['kql'], $_POST['chartsenabled'], $_SWIFT->Staff);

            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityupdatereport'), htmlspecialchars($_SWIFT_ReportObject->GetProperty('title'))), SWIFT_StaffActivityLog::ACTION_UPDATE,
                    SWIFT_StaffActivityLog::SECTION_REPORTS, SWIFT_StaffActivityLog::INTERFACE_STAFF);

            if (!$_updateResult)
            {
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            }

            $this->_RenderConfirmation(SWIFT_UserInterface::MODE_EDIT);

            $this->Load->Edit($_reportID);

            return true;
        }

        $this->Load->Edit($_reportID);

        return false;
    }

    /**
     * Render the Properties Dialog
     *
     * @author Varun Shoor
     * @param int $_reportID The Report ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function PropertiesDialog($_reportID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_reportID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_ReportObject = new SWIFT_Report(new SWIFT_DataID($_reportID));
        if (!$_SWIFT_ReportObject instanceof SWIFT_Report || !$_SWIFT_ReportObject->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->_LoadDisplayData();

        $this->UserInterface->Header($this->Language->Get('reports') . ' > ' . $this->Language->Get('properties'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('staff_rcanupdatereport') == '0')
        {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->RenderPropertiesDialog($_SWIFT_ReportObject);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Properties Submission Processor
     *
     * @author Varun Shoor
     * @param int $_reportID The Report ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function PropertiesSubmit($_reportID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_reportID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_ReportObject = new SWIFT_Report(new SWIFT_DataID($_reportID));
        if (!$_SWIFT_ReportObject instanceof SWIFT_Report || !$_SWIFT_ReportObject->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if (trim($_POST['basetable']) == '' || trim($_POST['title']) == '' || trim($_POST['visibilitytype']) == '' || trim($_POST['reportcategoryid']) == '') {
            $this->UserInterface->CheckFields('title', 'basetable', 'reportcategoryid');

            SWIFT::Notify(SWIFT::NOTIFICATION_ERROR, $_SWIFT->Language->Get('notifyfieldempty'));

            $this->Load->PropertiesDialog($_reportID);

            return false;
        }

        $_updateResult = $_SWIFT_ReportObject->UpdateProperties($_POST['title'], $_POST['reportcategoryid'], $_POST['basetable'], $_POST['visibilitytype'], $_SWIFT->Staff);

        SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityupdatereport'), htmlspecialchars($_SWIFT_ReportObject->GetProperty('title'))), SWIFT_StaffActivityLog::ACTION_UPDATE,
                SWIFT_StaffActivityLog::SECTION_REPORTS, SWIFT_StaffActivityLog::INTERFACE_STAFF);

        if (!$_updateResult)
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->_RenderConfirmation(SWIFT_UserInterface::MODE_EDIT);

        $this->Load->Edit($_reportID);

        return true;
    }

    /**
     * Generate the Report
     *
     * @author Varun Shoor
     * @param int $_reportID The Report ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Generate($_reportID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_reportID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_ReportObject = new SWIFT_Report(new SWIFT_DataID($_reportID));
        if (!$_SWIFT_ReportObject instanceof SWIFT_Report || !$_SWIFT_ReportObject->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_ReportRenderObject = false;

        $_start = GetMicroTime();
        try {
            $_SWIFT_ReportRenderObject = SWIFT_ReportRender::Process($_SWIFT_ReportObject);
        } catch (Exception $_ExceptionObject) {
            SWIFT::Notify(SWIFT::NOTIFICATION_ERROR, $_ExceptionObject->getMessage());

            $this->Load->Edit($_reportID);

            return false;
        }

        $_end = GetMicroTime();

        $_timeTaken = $_end - $_start;

        $_SWIFT_ReportObject->UpdateExecution($_SWIFT->Staff, $_timeTaken);

        if ($_SWIFT_ReportRenderObject->GetRecordCount() == 0) {
            SWIFT::Notify(SWIFT::NOTIFICATION_ALERT, $this->Language->Get('noreportresultfound'));

            $this->Load->Edit($_reportID);

            return false;
        }

        $_chartNotifications = $_SWIFT_ReportRenderObject->GetChartNotifications();

        if (count($_chartNotifications) > 0) {
            foreach ($_chartNotifications as $_chartNotification) {
                SWIFT::Notify(SWIFT::NOTIFICATION_ALERT, $_chartNotification);
            }
        }

        $_reportCategoryCache = $this->Cache->Get('reportcategorycache');

        $_baseTableName = $_SWIFT_ReportObject->GetProperty('basetablename');

        $_schemaContainer = SWIFT_KQLSchema::GetCombinedSchema();

        $_baseTableTitle = $_baseTableName;
        if (isset($_schemaContainer[$_baseTableName][SWIFT_KQLSchema::SCHEMA_TABLELABEL])) {
            $_baseTableTitle = SWIFT_KQLSchema::GetLabel($_schemaContainer[$_baseTableName][SWIFT_KQLSchema::SCHEMA_TABLELABEL]);
        }

        SWIFT_ReportUsageLog::Create($_SWIFT_ReportObject, $_SWIFT->Staff, ($_end-$_start));

        $this->View->RenderInfoBox($_reportCategoryCache[$_SWIFT_ReportObject->GetProperty('reportcategoryid')]['title'], $_baseTableTitle, SWIFT_Report::GetVisibilityLabel($_SWIFT_ReportObject->GetProperty('visibilitytype')));

        $this->_LoadDisplayData();

        $this->UserInterface->Header($this->Language->Get('reports') . ' > ' . htmlspecialchars($_SWIFT_ReportObject->GetProperty('title')), self::MENU_ID, self::NAVIGATION_ID);

        /*
         * BUG FIX - Varun Shoor
         *
         * SWIFT-1893 Private reports can be visible to other staff users
         *
         */
        if ($_SWIFT->Staff->GetPermission('staff_rcanviewreports') == '0' || ($_SWIFT_ReportObject->GetProperty('visibilitytype') == SWIFT_Report::VISIBLE_PRIVATE && $_SWIFT_ReportObject->GetProperty('creatorstaffid') != $_SWIFT->Staff->GetStaffID()))
        {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Generate($_SWIFT_ReportObject, $_SWIFT_ReportRenderObject);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Export the Report into Excel/PDF/HTML
     *
     * @author Andriy Lesyuk
     * @param int $_reportID The Report ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Export($_reportID, $_exportFormat = SWIFT_ReportExport::EXPORT_EXCEL)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_reportID) || !SWIFT_ReportBase::IsValidExportFormat($_exportFormat)) {
            SWIFT::Notify(SWIFT::NOTIFICATION_ALERT, $this->Language->Get('invalidexportformat'));

            $this->Load->Edit($_reportID);

            return false;
        }

        $_SWIFT_ReportObject = new SWIFT_Report(new SWIFT_DataID($_reportID));
        if (!$_SWIFT_ReportObject instanceof SWIFT_Report || !$_SWIFT_ReportObject->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_ReportExportObject = false;

        if (($_exportFormat != SWIFT_ReportExport::EXPORT_CSV) &&
            ($_exportFormat != SWIFT_ReportExport::EXPORT_HTML)) {

            /**
             * ---------------------------------------------
             * Export in Excel, PDF etc.
             * ---------------------------------------------
             */

            try {
                $_SWIFT_ReportExportObject = SWIFT_ReportExport::Process($_SWIFT_ReportObject, $_exportFormat);
            } catch (Exception $_ExceptionObject) {
                SWIFT::Notify(SWIFT::NOTIFICATION_ERROR, $_ExceptionObject->getMessage());

                $this->Load->Edit($_reportID);

                return false;
            }

            if ($_SWIFT_ReportExportObject->GetRecordCount() == 0) {
                SWIFT::Notify(SWIFT::NOTIFICATION_ALERT, $this->Language->Get('noreportresultfound'));

                $this->Load->Edit($_reportID);

                return false;
            }

            $_SWIFT_ReportExportObject->DispatchFile($_exportFormat);

        } else {

            /**
             * ---------------------------------------------
             * Export in CSV/HTML
             * ---------------------------------------------
             */

            try {
                $_SWIFT_ReportExportObject = SWIFT_ReportRender::Process($_SWIFT_ReportObject, ($_exportFormat == SWIFT_ReportExport::EXPORT_CSV));
            } catch (Exception $_ExceptionObject) {
                SWIFT::Notify(SWIFT::NOTIFICATION_ERROR, $_ExceptionObject->getMessage());

                $this->Load->Edit($_reportID);

                return false;
            }

            if ($_SWIFT_ReportExportObject->GetRecordCount() == 0) {
                SWIFT::Notify(SWIFT::NOTIFICATION_ALERT, $this->Language->Get('noreportresultfound'));

                $this->Load->Edit($_reportID);

                return false;
            }

            /**
             * ---------------------------------------------
             * Dispatch to Browser
             * ---------------------------------------------
             */

            $_exportFormatMap = $_SWIFT_ReportExportObject->GetExportFormatMap();

            if (isset($_exportFormatMap[$_exportFormat])) {

                if (isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/MSIE/', $_SERVER['HTTP_USER_AGENT'])) {
                    // IE Bug in download name workaround
                    @ini_set('zlib.output_compression', 'Off');
                }

                $_fileName = $_SWIFT_ReportExportObject->GetFilename();

                header('Content-Type: ' . $_exportFormatMap[$_exportFormat][1]);
                header('Content-Disposition: attachment; filename="' . $_fileName . '.' . $_exportFormatMap[$_exportFormat][2] . '"');
                header("Content-Transfer-Encoding: binary");
                header('Cache-Control: max-age=0');

            } else {
                SWIFT::Notify(SWIFT::NOTIFICATION_ALERT, SWIFT_INVALIDDATA);

                $this->Load->Edit($_reportID);

                return false;
            }

            /**
             * ---------------------------------------------
             * Generate Content
             * ---------------------------------------------
             */

            if ($_exportFormat == SWIFT_ReportExport::EXPORT_CSV) {
                $_SWIFT_ReportExportObject->DispatchCSV();
            } else {
                $_SWIFT_ReportExportObject->DispatchHTML();
            }

        }

        return true;
    }

    /**
     * Generate Submission
     *
     * @author Varun Shoor
     * @param int $_reportID The Report ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GenerateSubmit($_reportID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_reportID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_ReportObject = new SWIFT_Report(new SWIFT_DataID($_reportID));
        if (!$_SWIFT_ReportObject instanceof SWIFT_Report || !$_SWIFT_ReportObject->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        /*
        * BUG FIX - Ravi Sharma
        *
        * SWIFT-3244 Report update permission issue
        *
        * Comments: Don't update the report in case staff do not have update permissions
        */
        if (isset($_POST['kql']) && strcmp($_SWIFT_ReportObject->Get('kql'), $_POST['kql']) != '0' && $_SWIFT->Staff->GetPermission('staff_rcanupdatereport') != '0') {
            if (empty(trim($_POST['kql']))) {

                SWIFT::Notify(SWIFT::NOTIFICATION_ERROR, $_SWIFT->Language->Get('notifykqlfieldempty'));

                $this->Load->Edit($_reportID);

                return false;
            }
            $_SWIFT_ReportObject->Update($_SWIFT_ReportObject->Get('title'), $_POST['kql'], $_POST['chartsenabled'], $_SWIFT->Staff);
        } else if (strcmp($_SWIFT_ReportObject->Get('kql'), $_POST['kql']) != '0') {
            SWIFT::Notify(SWIFT::NOTIFICATION_ERROR, $this->Language->Get('unmodifiedreport'));
        }

        $this->Load->Generate($_reportID);

        return true;
    }


    /**
     * Print the Report
     *
     * @author Varun Shoor
     * @param int $_reportID The Report ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function PrintReport($_reportID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_reportID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_ReportObject = new SWIFT_Report(new SWIFT_DataID($_reportID));
        if (!$_SWIFT_ReportObject instanceof SWIFT_Report || !$_SWIFT_ReportObject->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_ReportRenderObject = SWIFT_ReportRender::Process($_SWIFT_ReportObject);
        if ($_SWIFT_ReportRenderObject->GetRecordCount() == 0) {
            SWIFT::Notify(SWIFT::NOTIFICATION_ALERT, $this->Language->Get('noreportresultfound'));

            $this->Load->Edit($_reportID);

            return false;
        }

        $this->Template->Assign('_reportContents', $_SWIFT_ReportRenderObject->GetOutput());
        $this->Template->Assign('_reportTitle', htmlspecialchars($_SWIFT_ReportObject->GetProperty('title')));
        $this->Template->Assign('_reportDate', SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME));
        $this->Template->Assign('_headerImageCP', SWIFT::Get('swiftpath') . $this->Template->RetrieveHeaderImagePath(SWIFT_TemplateEngine::HEADERIMAGE_CONTROLPANEL));

        $this->Template->Render('printreport');

        return true;
    }
}
