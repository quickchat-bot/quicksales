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

use Base\Models\Department\SWIFT_Department;
use Base\Models\Staff\SWIFT_Staff;
use Base\Models\Staff\SWIFT_StaffLoginLog;
use Base\Models\Staff\SWIFT_StaffProfileImage;
use Tickets\Library\StaffAPI\SWIFT_TicketStaffAPIManager;

/**
 * The Default Staf API Controller
 *
 * @property SWIFT_XML $XML
 * @method _DispatchError($_msg = '')
 * @method _DispatchConfirmation()
 * @method _LoadTemplateGroup($_templateGroupName = '')
 * @method bool _ProcessNews()
 * @method bool _ProcessKnowledgebaseCategories()
 * @author Varun Shoor
 */
class Controller_Default extends Controller_staffapi
{
    /**
     * The Index Controller
     *
     * @author Varun Shoor
     */
    public function Index()
    {
        $this->_DispatchError($this->Language->Get('invalid_sessionid'));

        log_error_and_exit();
    }

    /**
     * Login the Staff Member to the Staff API Interface
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function Login()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!SWIFT_App::IsInstalled(APP_TICKETS))
        {
            throw new SWIFT_Exception('Tickets App is not Registered');
        }

        if (!isset($_POST['username']) || !isset($_POST['password']) || empty($_POST['username']) || empty($_POST['password']))
        {
            $this->_DispatchError($this->Language->Get('invaliduser'));

            return false;
        }

        // Before Login, check to see if we can really log in this user..
        $_loginLogCheck = SWIFT_StaffLoginLog::CanStaffLogin(urldecode($_POST['username']));
        if (!$_loginLogCheck[0]) {
            // Login failed as user exhausted all attempts
            $_errorString = sprintf($this->Language->Get('loginlogerror'),
                ceil(SWIFT_StaffLoginLog::GetLoginTimeline()),
                $_loginLogCheck[1],
                SWIFT_StaffLoginLog::GetLoginRetries());

            $this->_DispatchError($_errorString);

            log_error_and_exit();
        }

        /*
         * BUG FIX - Jamie Edwards
         *
         * SWIFT-2359 PHP automatically urldecodes POST data, so urldecode is not needed for StaffAPI->Login->Password
         *
         */
        $_SWIFT_StaffObject = SWIFT_Staff::Authenticate($_POST['username'], $_POST['password'], false);

        if ($_SWIFT_StaffObject instanceof SWIFT_Staff && $_SWIFT_StaffObject->GetIsClassLoaded()) {
            // Authentication successfull, Generate a sessionid for this user
            SWIFT_Session::InsertAndStart($_SWIFT_StaffObject->GetStaffID());

            // Log the Login Attempt First
            SWIFT_StaffLoginLog::Success($_SWIFT_StaffObject, SWIFT_StaffLoginLog::INTERFACE_WINAPP);

            if (!isset($_SWIFT->Session) || !$_SWIFT->Session instanceof SWIFT_Session || !$_SWIFT->Session->GetIsClassLoaded())
            {
                $this->_DispatchError(SWIFT::Get('errorstring'));

                log_error_and_exit();
            }

            $this->XML->AddParentTag('kayako_staffapi');

            // ======= BEGIN BASIC XML DATA =======
            $this->XML->AddTag('status', '1');
            $this->XML->AddTag('error', '');
            $this->XML->AddTag('version', SWIFT_VERSION);
            $this->XML->AddTag('sessionid', $_SWIFT->Session->GetSessionID());
            $this->XML->AddTag('sessiontimeout', $_SWIFT->Settings->Get('security_sessioninactivity'));
            $this->XML->AddTag('staffid', $_SWIFT_StaffObject->GetStaffID());
            // ======= END BASIC XML DATA =======

            SWIFT_Session::FlushInactive();

            $this->XML->EndTag('kayako_staffapi');
            $this->XML->EchoXMLStaffAPI();

            return true;

        } else {
            // Log the Login Attempt First
            SWIFT_StaffLoginLog::Failure(urldecode($_POST['username']), SWIFT_StaffLoginLog::INTERFACE_STAFFAPI);
            if ($_SWIFT->Settings->Get('security_loginlocked') == '1') {
                $_loginLogCheck = SWIFT_StaffLoginLog::CanStaffLogin(urldecode($_POST['username']));

                if ($_loginLogCheck[0] && $_loginLogCheck[1] > 0) {
                    // Login was allowed but there were failures in the previous timeline

                    $this->_DispatchError(sprintf($this->Language->Get('loginlogwarning'), $_loginLogCheck[1], SWIFT_StaffLoginLog::GetLoginRetries()));
                } else {
                    $this->_DispatchError(sprintf($this->Language->Get('loginlogerror'),
                        ceil(SWIFT_StaffLoginLog::GetLoginTimeline() / 60),
                        $_loginLogCheck[1],
                        SWIFT_StaffLoginLog::GetLoginRetries()));
                }
            } else {
                $this->_DispatchError($this->Language->Get('invaliduser'));
            }

            log_error_and_exit();
        }

        return false;
    }

    /**
     * Logout the Staff Member from the StaffAPI Interface
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function Logout()
    {
        $_SWIFT = SWIFT::GetInstance();

        SWIFT_Session::Logout($this->Interface);

        $this->_DispatchConfirmation();

        return true;
    }

    /**
     * Get the Desk Info
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetInfo()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())     {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_StaffObject = $_SWIFT->Staff;

        if (!$_SWIFT_StaffObject instanceof SWIFT_Staff || !$_SWIFT_StaffObject->GetIsClassLoaded()) {
            // Failed to load session
            $this->_DispatchError($this->Language->Get('invalid_sessionid'));

            return false;
        }

        $_ticketCountCache = (array) $_SWIFT->Cache->Get('ticketcountcache');
        $_departmentCache = (array) $_SWIFT->Cache->Get('departmentcache');
        $_ticketStatusCache = (array) $_SWIFT->Cache->Get('statuscache');

        $this->XML->AddParentTag('kayako_staffapi');

        $this->XML->AddTag('status', '1');
        $this->XML->AddTag('error', '');

        // ======= BEGIN BASIC XML DATA =======
        $this->XML->AddTag('uniqueid', SWIFT::Get('UniqueID'));
        $this->XML->AddTag('version', SWIFT_VERSION);
        $this->XML->AddTag('product', SWIFT_PRODUCT);
        $this->XML->AddTag('companyname', $this->Settings->Get('general_companyname'));
        // ======= END BASIC XML DATA =======

        // ======= BEGIN DEPARTMENT DATA =======
        $_assignedDepartmentIDList = $_SWIFT_StaffObject->GetAssignedDepartments(APP_TICKETS);
        if (!_is_array($_assignedDepartmentIDList))
        {
            $_assignedDepartmentIDList = array();
        }

        $_staffAssignedDepartmentIDList = $_SWIFT->Staff->GetAssignedDepartments(APP_TICKETS);

        $_departmentMap =  SWIFT_Department::GetDepartmentMap(APP_TICKETS);

        /**
         * ---------------------------------------------
         * Ticket Counts
         * ---------------------------------------------
         */

        $_inboxCount = 0;
        foreach ($_departmentMap as $_departmentID => $_departmentContainer) {
            if (!in_array($_departmentID, $_staffAssignedDepartmentIDList)) {
                continue;
            }

            if (isset($_ticketCountCache['departments'][$_departmentID]))
            {
                $_inboxCount += $_ticketCountCache['departments'][$_departmentID]['totalunresolveditems'];
            }

            if (_is_array($_departmentContainer['subdepartments'])) {
                $_arr = (array) $_departmentContainer['subdepartments'];
                foreach ($_arr as $_subDepartmentID => $_subDepartmentContainer) {
                    if (!in_array($_subDepartmentID, $_staffAssignedDepartmentIDList)) {
                        continue;
                    }

                    if (isset($_ticketCountCache['departments'][$_subDepartmentID]))
                    {
                        $_inboxCount += $_ticketCountCache['departments'][$_subDepartmentID]['totalunresolveditems'];
                    }
                }
            }
        }

        $_myTicketsCount = 0;
        if (isset($_ticketCountCache['ownerstaff'][$_SWIFT->Staff->GetStaffID()])) {
            $_myTicketsCount = $_ticketCountCache['ownerstaff'][$_SWIFT->Staff->GetStaffID()]['totalunresolveditems'];
        }

        $_unassignedCount = 0;


        $_trashCount = 0;
        if (isset($_ticketCountCache['departments'][0]['totalitems']))
        {
            $_trashCount = $_ticketCountCache['departments'][0]['totalitems'];
        }

        $_unassignedCount = 0;
        if (isset($_ticketCountCache['unassigned'][$_SWIFT->Staff->GetStaffID()])) {
            $_unassignedCount = $_ticketCountCache['unassigned'][$_SWIFT->Staff->GetStaffID()]['totalunresolveditems'];
        }

        $this->XML->AddTag('ticketsummary', '', array('inbox' => $_inboxCount, 'mytickets' => $_myTicketsCount,
            'unassigned' => $_unassignedCount, 'trash' => $_trashCount));

        if (_is_array($_departmentMap))
        {
            foreach ($_departmentMap as $_key => $_val)
            {
                /*
                 * BUG FIX - Varun Shoor
                 *
                 * SWIFT-2064 StaffAPI not abiding by staff permissions for the department list
                 *
                 * Comments: Added the staff assigned department check
                 */
                if (!in_array($_key, $_staffAssignedDepartmentIDList)) {
                    continue;
                }

                $_isNew = false;
                if (isset($_ticketCountCache['departments'][$_val['departmentid']]) && $_ticketCountCache['departments'][$_val['departmentid']]['lastactivity'] > $_SWIFT->Staff->GetProperty('lastvisit'))
                {
                    $_isNew = true;
                }

                $_ticketCount = 0;
                if (isset($_ticketCountCache['departments'][$_val['departmentid']]))
                {
                    $_ticketCount = $_ticketCountCache['departments'][$_val['departmentid']]['totalunresolveditems'];
                }

                $_attributeContainer = array('departmentid' => $_val['departmentid'], 'statusid' => '0', 'new' => (int) ($_isNew), 'ticketcount' => (int) ($_ticketCount));
                $this->XML->AddTag('ticketcount', '', $_attributeContainer);

                foreach ($_ticketStatusCache as $_ticketStatusID => $_ticketStatus) {
                    if (!empty($_ticketStatus['departmentid']) && $_ticketStatus['departmentid'] != $_val['departmentid']) {
                        continue;
                    }

                    $_isStatusNew = false;
                    if (isset($_ticketCountCache['departments'][$_val['departmentid']]['ticketstatus'][$_ticketStatusID]) && $_ticketCountCache['departments'][$_val['departmentid']]['ticketstatus'][$_ticketStatusID]['lastactivity'] > $_SWIFT->Staff->GetProperty('lastvisit'))
                    {
                        $_isStatusNew = true;
                    }


                    $_ticketStatusCount = 0;
                    if (isset($_ticketCountCache['departments'][$_val['departmentid']]['ticketstatus'][$_ticketStatusID]))
                    {
                        $_ticketStatusCount = $_ticketCountCache['departments'][$_val['departmentid']]['ticketstatus'][$_ticketStatusID]['totalitems'];
                    }

                    $_attributeContainer = array('departmentid' => $_val['departmentid'], 'statusid' => $_ticketStatusID, 'new' => (int) ($_isStatusNew), 'ticketcount' => (int) ($_ticketStatusCount));
                    $this->XML->AddTag('ticketcount', '', $_attributeContainer);
                }

                if (_is_array($_val['subdepartments']))
                {
                    $_arr = (array) $_val['subdepartments'];
                    foreach ($_arr as $_subKey => $_subVal)
                    {
                        /*
                         * BUG FIX - Varun Shoor
                         *
                         * SWIFT-2064 StaffAPI not abiding by staff permissions for the department list
                         *
                         * Comments: Added the staff assigned department check
                         */
                        if (!in_array($_subKey, $_staffAssignedDepartmentIDList)) {
                            continue;
                        }

                        $_isNew = false;
                        if (isset($_ticketCountCache['departments'][$_subVal['departmentid']]) && $_ticketCountCache['departments'][$_subVal['departmentid']]['lastactivity'] > $_SWIFT->Staff->GetProperty('lastvisit'))
                        {
                            $_isNew = true;
                        }

                        $_ticketCount = 0;
                        if (isset($_ticketCountCache['departments'][$_subVal['departmentid']]))
                        {
                            $_ticketCount = $_ticketCountCache['departments'][$_subVal['departmentid']]['totalunresolveditems'];
                        }

                        $_attributeContainer = array('departmentid' => (int) ($_subVal['departmentid']), 'statusid' => '0', 'new' => (int) ($_isNew), 'ticketcount' => (int) ($_ticketCount));
                        $this->XML->AddTag('ticketcount', '', $_attributeContainer);

                        foreach ($_ticketStatusCache as $_ticketStatusID => $_ticketStatus) {
                            if (!empty($_ticketStatus['departmentid']) && $_ticketStatus['departmentid'] != $_subVal['departmentid']) {
                                continue;
                            }

                            $_isStatusNew = false;
                            if (isset($_ticketCountCache['departments'][$_subVal['departmentid']]['ticketstatus'][$_ticketStatusID]) && $_ticketCountCache['departments'][$_subVal['departmentid']]['ticketstatus'][$_ticketStatusID]['lastactivity'] > $_SWIFT->Staff->GetProperty('lastvisit'))
                            {
                                $_isStatusNew = true;
                            }


                            $_ticketStatusCount = 0;
                            if (isset($_ticketCountCache['departments'][$_subVal['departmentid']]['ticketstatus'][$_ticketStatusID]))
                            {
                                $_ticketStatusCount = $_ticketCountCache['departments'][$_subVal['departmentid']]['ticketstatus'][$_ticketStatusID]['totalitems'];
                            }

                            $_attributeContainer = array('departmentid' => $_subVal['departmentid'], 'statusid' => $_ticketStatusID, 'new' => (int) ($_isStatusNew), 'ticketcount' => (int) ($_ticketStatusCount));
                            $this->XML->AddTag('ticketcount', '', $_attributeContainer);
                        }
                    }
                }
            }
        }

        if (_is_array($_departmentMap))
        {
            foreach ($_departmentMap as $_key => $_val)
            {

                /*
                 * BUG FIX - Varun Shoor
                 *
                 * SWIFT-2064 StaffAPI not abiding by staff permissions for the department list
                 *
                 * Comments: Added the staff assigned department check
                 */
                if (!in_array($_key, $_staffAssignedDepartmentIDList)) {
                    continue;
                }

                $_attributeContainer = array('id' => (int) ($_val['departmentid']), 'title' => $_val['title'], 'assigned' => IIF(in_array($_val['departmentid'], $_assignedDepartmentIDList), '1', '0'),
                    'parentdepartmentid' => '0');

                if (_is_array($_val['subdepartments']))
                {
                    $this->XML->AddParentTag('department', $_attributeContainer);

                    $_arr = (array) $_val['subdepartments'];
                    foreach ($_arr as $_subKey => $_subVal)
                    {
                        /*
                         * BUG FIX - Varun Shoor
                         *
                         * SWIFT-2064 StaffAPI not abiding by staff permissions for the department list
                         *
                         * Comments: Added the staff assigned department check
                         */
                        if (!in_array($_subKey, $_staffAssignedDepartmentIDList)) {
                            continue;
                        }

                        $_attributeContainer = array('id' => (int) ($_subVal['departmentid']), 'title' => $_subVal['title'], 'assigned' => IIF(in_array($_subVal['departmentid'], $_assignedDepartmentIDList), '1', '0'),
                            'parentdepartmentid' => (int) ($_val['departmentid']));
                        $this->XML->AddTag('department', '', $_attributeContainer);
                    }

                    $this->XML->EndParentTag('department');
                } else {
                    $this->XML->AddTag('department', '', $_attributeContainer);
                }

            }
        }

        // ======= END DEPARTMENT DATA =======

        // ======= BEGIN STAFF GROUP DATA =======
        $_staffGroupCache = (array) $_SWIFT->Cache->Get('staffgroupcache');

        foreach ($_staffGroupCache as $_staffGroup)
        {
            $this->XML->AddTag('staffgroup', '', array('id' => (int) ($_staffGroup['staffgroupid']), 'title' => $_staffGroup['title']));
        }
        // ======= END STAFF GROUP DATA =======


        // ======= BEGIN AVATAR DATA =======

        if (isset($_POST['wantavatars']) && $_POST['wantavatars'] == '1') {
            SWIFT_StaffProfileImage::DispatchXML($this->XML, SWIFT_StaffProfileImage::TYPE_PUBLIC);
        }

        // ======= END AVATAR DATA =======

        if (SWIFT_App::IsInstalled(APP_TICKETS))
        {
            $_wantMacros = true;
            if (isset($_POST['wantmacros']) && $_POST['wantmacros'] == '0') {
                $_wantMacros = false;
            }

            SWIFT_Loader::LoadLibrary('StaffAPI:TicketStaffAPIManager', APP_TICKETS);
            SWIFT_TicketStaffAPIManager::DispatchLogin($this->XML, $_wantMacros);
        }

        SWIFT_Session::FlushInactive();

        $this->XML->EndTag('kayako_staffapi');
        $this->XML->EchoXMLStaffAPI();

        return true;
    }
}
