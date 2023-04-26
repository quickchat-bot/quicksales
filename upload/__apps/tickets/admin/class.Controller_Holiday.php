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

namespace Tickets\Admin;

use Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel;
use Controller_admin;
use SWIFT;
use SWIFT_Exception;
use SWIFT_Session;
use Base\Models\Staff\SWIFT_StaffActivityLog;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Tickets\Models\SLA\SWIFT_SLAHoliday;

/**
 * The Holiday Controller Class
 *
 * @method Library($_libraryName, $_arguments, $_initiateInstance, $_customAppName, $_appName)
 * @property Controller_Holiday $Load
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @property View_Holiday $View
 * @author Varun Shoor
 */
class Controller_Holiday extends Controller_admin
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

        $this->Language->Load('tickets');
        $this->Language->Load('adminsla');
    }

    /**
     * Delete the SLA Holiday from Mass Action
     *
     * @author Varun Shoor
     * @param mixed $_slaHolidayIDList The SLA Holiday ID List Container Array
     * @param bool $_byPassCSRF Whether to bypass the CSRF check
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function DeleteList($_slaHolidayIDList, $_byPassCSRF = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash']))
        {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('admin_tcandeleteslaholidays') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        if (_is_array($_slaHolidayIDList)) {
            $_SWIFT->Database->Query("SELECT title FROM ". TABLE_PREFIX ."slaholidays WHERE slaholidayid IN (". BuildIN($_slaHolidayIDList) .")");
            while ($_SWIFT->Database->NextRecord()) {
                SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activityslaholidaydelete'), htmlspecialchars($_SWIFT->Database->Record['title'])), SWIFT_StaffActivityLog::ACTION_DELETE, SWIFT_StaffActivityLog::SECTION_TICKETS, SWIFT_StaffActivityLog::INTERFACE_ADMIN);
            }

            SWIFT_SLAHoliday::DeleteList($_slaHolidayIDList);
        }

        return true;
    }

    /**
     * Delete the Given SLA Holiday ID
     *
     * @author Varun Shoor
     * @param int $_slaHolidayID The SLA Holiday ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete($_slaHolidayID)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($_slaHolidayID), true);

        $this->Load->Manage();

        return true;
    }

    /**
     * Displays the SLA Holiday Grid
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Manage()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UserInterface->Header($this->Language->Get('sla') . ' > ' . $this->Language->Get('manageholidays'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_tcanviewslaholidays') == '0')
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
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function RunChecks($_mode)
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

        if (($_mode == SWIFT_UserInterface::MODE_INSERT && $_SWIFT->Staff->GetPermission('admin_tcaninsertslaholidays') == '0') || ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT->Staff->GetPermission('admin_tcanupdateslaholidays') == '0')) {
            $this->UserInterface->Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));

            return false;
        }

        return true;
    }

    /**
     * Insert a new SLA Holiday
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

        $this->UserInterface->Header($this->Language->Get('sla') . ' > ' . $this->Language->Get('insertholiday'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_tcaninsertslaholidays') == '0')
        {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render(SWIFT_UserInterface::MODE_INSERT);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Retrieve the Assigned SLA Plan ID list for Insert/Edit Processing
     *
     * @author Varun Shoor
     * @return mixed "_assignedSLAPlanIDList" (ARRAY) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    private function _GetAssignedSLAPlanIDList()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($_POST['slaplans']) || !_is_array($_POST['slaplans'])) {
            return array();
        }

        $_assignedSLAPlanIDList = array();
        foreach ($_POST['slaplans'] as $_key => $_val)
        {
            if ($_val == '1')
            {
                $_assignedSLAPlanIDList[] = (int) ($_key);
            }
        }

        return $_assignedSLAPlanIDList;
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
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->RunChecks(SWIFT_UserInterface::MODE_INSERT))
        {
            $_SLAHolidayObject = SWIFT_SLAHoliday::Create($_POST['title'], $_POST['iscustom'], $_POST['holidayday'], $_POST['holidaymonth'], $this->Input->SanitizeForXSS($_POST['flagicon']), $this->_GetAssignedSLAPlanIDList());

            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityslaholidayinsert'), htmlspecialchars($_POST['title'])), SWIFT_StaffActivityLog::ACTION_INSERT, SWIFT_StaffActivityLog::SECTION_TICKETS, SWIFT_StaffActivityLog::INTERFACE_ADMIN);

            if (!$_SLAHolidayObject instanceof SWIFT_SLAHoliday || !$_SLAHolidayObject->GetIsClassLoaded())
            {
                // @codeCoverageIgnoreStart
                // this code will never be executed
                return false;
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
     * Edit the SLA Holiday
     *
     * @author Varun Shoor
     * @param int $_slaHolidayID The SLA Holiday ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Edit($_slaHolidayID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_slaHolidayID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_SLAHolidayObject = new SWIFT_SLAHoliday($_slaHolidayID);
        if (!$_SWIFT_SLAHolidayObject instanceof SWIFT_SLAHoliday || !$_SWIFT_SLAHolidayObject->GetIsClassLoaded())
        {
            // @codeCoverageIgnoreStart
            // this code will never be executed
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }

        $this->UserInterface->Header($this->Language->Get('sla') . ' > ' . $this->Language->Get('editholiday'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_tcanupdateslaholidays') == '0')
        {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render(SWIFT_UserInterface::MODE_EDIT, $_SWIFT_SLAHolidayObject);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Edit Submission Processor
     *
     * @author Varun Shoor
     * @param int $_slaHolidayID The SLA Holiday ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function EditSubmit($_slaHolidayID)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_slaHolidayID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_SLAHolidayObject = new SWIFT_SLAHoliday($_slaHolidayID);
        if (!$_SWIFT_SLAHolidayObject instanceof SWIFT_SLAHoliday || !$_SWIFT_SLAHolidayObject->GetIsClassLoaded())
        {
            // @codeCoverageIgnoreStart
            // this code will never be executed
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }

        if ($this->RunChecks(SWIFT_UserInterface::MODE_EDIT))
        {
            $_updateResult = $_SWIFT_SLAHolidayObject->Update($_POST['title'], $_POST['iscustom'], $_POST['holidayday'], $_POST['holidaymonth'], $this->Input->SanitizeForXSS($_POST['flagicon']), $this->_GetAssignedSLAPlanIDList());

            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityslaholidayupdate'), htmlspecialchars($_POST['title'])), SWIFT_StaffActivityLog::ACTION_UPDATE, SWIFT_StaffActivityLog::SECTION_TICKETS, SWIFT_StaffActivityLog::INTERFACE_ADMIN);

            if (!$_updateResult)
            {
                // @codeCoverageIgnoreStart
                // this code will never be executed
                return false;
                // @codeCoverageIgnoreEnd
            }

            $this->_RenderConfirmation(SWIFT_UserInterface::MODE_EDIT);

            $this->Load->Manage();

            return true;
        }

        $this->Load->Edit($_slaHolidayID);

        return false;
    }

    /**
     * Render the User Interface Confirmation
     *
     * @author Varun Shoor
     * @param mixed $_mode The UI Mode
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    private function _RenderConfirmation($_mode)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_slaPlanCache = $this->Cache->Get('slaplancache');

        $_finalText = '<b>' . $this->Language->Get('holidaytitle') . ':</b> ' . '<img src="' . IIF(empty($_POST['flagicon']), SWIFT::Get('themepath') . 'images/icon_calendar.svg', str_replace('{$themepath}', SWIFT::Get('swiftpath') . SWIFT_BASEDIRECTORY . '/' . SWIFT_THEMESDIRECTORY . '/' . SWIFT_THEMEGLOBALDIRECTORY . '/flags/', $_POST['flagicon'])) . '" align="absmiddle" border="0" /> ' . htmlspecialchars($_POST['title']) . '<br />';

        $_finalText .= '<b>' . $this->Language->Get('holidaydate') . ':</b> ' . trim(strftime('%d %B', mktime(0, 0, 0, (int) ($_POST['holidaymonth']), (int) ($_POST['holidayday'])))) . '<br />';

        if ($_POST['iscustom'])
        {
            $_finalText .= '<b>' . $this->Language->Get('slaiscustom') . ':</b> ' . $this->Language->Get('custom') . '<br />';

            if (isset($_POST['slaplans']))
            {
                foreach ($_POST['slaplans'] as $_key => $_val)
                {
                    /**
                     * BUG FIX: Nidhi Gupta <nidhi.gupta@kayako.com>
                     *
                     * SWIFT-4636: Holidays are not considered while calculating the reply due time
                     *
                     * Comments: Only first SLA plan was displaying after linking.
                     */
                    $_slaPlanTitle = '';

                    if ($_val == 1) {
                        $_slaPlanTitle .= htmlspecialchars($_slaPlanCache[$_key]['title']);
                    }

                    if (!isset($_slaPlanCache[$_val]))
                    {
                        continue;
                    }

                    $_finalText .= '<img src="' . SWIFT::Get('themepath') . 'images/linkdownarrow_blue.gif" align="absmiddle" border="0" /> ' . $_slaPlanTitle . '<br />';
                }
            }
        } else {
            $_finalText .= '<b>' . $this->Language->Get('slaiscustom') . ':</b> ' . $this->Language->Get('customall') . '<br />';
        }

        if ($_mode == SWIFT_UserInterface::MODE_INSERT)
        {
            SWIFT::Info($this->Language->Get('titleinsertslaholiday'), sprintf($this->Language->Get('msginsertslaholiday'), htmlspecialchars($_POST['title'])) . '<br />' . $_finalText);
        } else if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            SWIFT::Info($this->Language->Get('titleupdateslaholiday'), sprintf($this->Language->Get('msgupdateslaholiday'), htmlspecialchars($_POST['title'])) . '<br />' . $_finalText);
        }

        return true;
    }
}
