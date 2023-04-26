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

use Base\Library\CallHomeData\SWIFT_CallHomeData;
use Base\Models\Department\SWIFT_Department;
use Base\Models\Staff\SWIFT_Staff;
use Base\Models\Staff\SWIFT_StaffLoginLog;
use Base\Models\Staff\SWIFT_StaffProfileImage;
use LiveChat\Library\Canned\SWIFT_CannedManager;
use LiveChat\Models\Chat\SWIFT_Chat;

/**
 * The Default Controller
 *
 * @property SWIFT_XML $XML
 * @method _DispatchError($_msg = '')
 * @method RebuildCache()
 * @method _DispatchConfirmation()
 * @method _LoadTemplateGroup($_templateGroupName = '')
 * @method GetInfo()
 * @method bool _ProcessNews()
 * @method bool _ProcessKnowledgebaseCategories()
 * @author Varun Shoor
 */
class Controller_Default extends Controller_winapp
{
    /**
     * Constructor
     *
     * @author Varun Shoor
     */
    public function __construct()
    {
        parent::__construct();

        $this->Load->AppLibrary(APP_LIVECHAT, 'Canned:CannedManager', [], false);
    }

    /**
     * The Index Controller
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function Index()
    {
        $this->_DispatchError($this->Language->Get('invalid_sessionid'));

        log_error_and_exit();
    }

    /**
     * Login the Staff Member to the Winapp Interface
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function Login()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!SWIFT_App::IsInstalled(APP_LIVECHAT))
        {
            throw new SWIFT_Exception('Live Chat App is not Registered');
        }

        unset($_hookCode);
        ($_hookCode = SWIFT_Hook::Execute('winapp_login')) ? eval($_hookCode) : false;

        if (!isset($_POST['username']) || !isset($_POST['password']) || empty($_POST['username']) || empty($_POST['password']))
        {
            $this->_DispatchError($this->Language->Get('invaliduser'));

            return false;
        }

        // Before Login, check to see if we can really log in this user..
        $_loginLogCheck = SWIFT_StaffLoginLog::CanStaffLogin(urldecode($_POST['username']));
        if (!$_loginLogCheck[0]) {
            // Login failed as user exhausted all attempts
            $this->_DispatchError(sprintf($this->Language->Get('loginlogerror'), round(SWIFT_StaffLoginLog::GetLoginTimeline()/60), $_loginLogCheck[1], SWIFT_StaffLoginLog::GetLoginRetries()));

            exit;
        }

        $_SWIFT_StaffObject = SWIFT_Staff::Authenticate(urldecode($_POST['username']), urldecode($_POST['password']), false);

        if ($_SWIFT_StaffObject instanceof SWIFT_Staff && $_SWIFT_StaffObject->GetIsClassLoaded()) {
            // Authentication successfull, Generate a sessionid for this user
            SWIFT_Session::InsertAndStart($_SWIFT_StaffObject->GetStaffID());

            // Log the Login Attempt First
            SWIFT_StaffLoginLog::Success($_SWIFT_StaffObject, SWIFT_StaffLoginLog::INTERFACE_WINAPP);

            /**
             * Improvement FIX: Nidhi Gupta <nidhi.gupta@opencart.com.vn>
             *
             * SWIFT-4899: Call Home report version 2 - SWIFT to send ping to backend
             */
            //Call home Data
            SWIFT_Loader::LoadLibrary('CallHomeData:CallHomeData', APP_BASE);
            $_CallHome = new SWIFT_CallHomeData();
            $_CallHome->CallHomeData();

            if (!isset($_SWIFT->Session) || !$_SWIFT->Session instanceof SWIFT_Session || !$_SWIFT->Session->GetIsClassLoaded())
            {
                $this->_DispatchError(SWIFT::Get('errorstring'));

                exit;
            }

            $this->XML->AddParentTag('kayako_livechat');
            $this->XML->AddTag('status', '1');

            // Insert the staff session id for this user
            $_staffSessionID = SWIFT_Session::Insert(SWIFT_Interface::INTERFACE_STAFF, $_SWIFT_StaffObject->GetStaffID());

            // ======= BEGIN BASIC XML DATA =======
            $this->XML->AddTag('sessionid', $_SWIFT->Session->GetSessionID());
            $this->XML->AddTag('uniqueid', SWIFT::Get('UniqueID'));
            $this->XML->AddTag('staffsessionid', $_staffSessionID);
            $this->XML->AddTag('version', SWIFT_VERSION);
            $this->XML->AddTag('product', SWIFT_PRODUCT);
            $this->XML->AddTag('companyname', $this->Settings->Get('general_companyname'));
            $this->XML->AddTag('displaytimestamps', $this->Settings->Get('livechat_timestamps'));
            $this->XML->AddTag('recordphonecalls', (int) ($this->Settings->Get('ls_recordphonecalls')));
            $this->XML->AddTag('chatqueuetype', $this->Settings->Get('ls_routingmode'));
            // ======= END BASIC XML DATA =======



            /**
             * ---------------------------------------------
             * BEGIN PERMISSIONS DATA
             * ---------------------------------------------
             */

            $this->XML->AddParentTag('permissions');
                $this->XML->AddTag('can_observe', (int) ($_SWIFT->Staff->GetPermission('ls_canobserve')));
            $this->XML->EndParentTag('permissions');


            /**
             * ---------------------------------------------
             * END PERMISSIONS DATA
             * ---------------------------------------------
             */



            /**
             * ---------------------------------------------
             * BEGIN VARIABLE DATA
             * ---------------------------------------------
             */

            $this->XML->AddParentTag('variables');
                $_uploadSize = GetPHPMaxUploadSize(); // Size in Bytes
                $this->XML->AddTag('max_upload_size', $_uploadSize);
            $this->XML->EndParentTag('variables');

            /**
             * ---------------------------------------------
             * END VARIABLE DATA
             * ---------------------------------------------
             */

            // ======= BEGIN DEPARTMENT DATA =======
            $_assignedDepartmentIDList = $_SWIFT_StaffObject->GetAssignedDepartments(APP_LIVECHAT);
            if (!_is_array($_assignedDepartmentIDList))
            {
                $_assignedDepartmentIDList = array();
            }

            $_departmentMap =  SWIFT_Department::GetDepartmentMap(APP_LIVECHAT);

            $this->XML->AddParentTag('departments');
            if (_is_array($_departmentMap))
            {
                foreach ($_departmentMap as $_key => $_val)
                {
                    $_attributeContainer = array('id' => (int) ($_val['departmentid']), 'title' => $_val['title'], 'assigned' => IIF(in_array($_val['departmentid'], $_assignedDepartmentIDList), '1', '0'));

                    if (_is_array($_val['subdepartments']))
                    {
                        $this->XML->AddParentTag('department', $_attributeContainer);

                        $_arr = (array) $_val['subdepartments'];
                        foreach ($_arr as $_subKey => $_subVal)
                        {
                            $_attributeContainer = array('id' => (int) ($_subVal['departmentid']), 'title' => $_subVal['title'], 'assigned' => IIF(in_array($_subVal['departmentid'], $_assignedDepartmentIDList), '1', '0'), 'parentdepartmentid' => (int) ($_val['departmentid']));
                            $this->XML->AddTag('department', '', $_attributeContainer);
                        }

                        $this->XML->EndParentTag('department');
                    } else {
                        $this->XML->AddTag('department', '', $_attributeContainer);
                    }

                }
            }
            $this->XML->EndTag('departments');

            // ======= END DEPARTMENT DATA =======

            // ======= BEGIN STAFF GROUP DATA =======
            $_staffGroupCache = (array) $_SWIFT->Cache->Get('staffgroupcache');

            $this->XML->AddParentTag('staffgroups');
                foreach ($_staffGroupCache as $_key => $_val)
                {
                    $this->XML->AddTag('staffgroup', $_val['title'], array('id' => (int) ($_val['staffgroupid'])));
                }
            $this->XML->EndTag('staffgroups');
            // ======= END STAFF GROUP DATA =======

            // ======= BEGIN CHAT SKILL DATA =======
            $_chatSkillCache = (array) $_SWIFT->Cache->Get('skillscache');

            $this->XML->AddParentTag('chatskills');
                foreach ($_chatSkillCache as $_key => $_val)
                {
                    $this->XML->AddTag('chatskill', $_val['title'], array('id' => (int) ($_val['chatskillid'])));
                }
            $this->XML->EndTag('chatskills');
            // ======= END CHAT SKILL DATA =======

            // ======= BEGIN STAFF PROFILE DATA =======
            $this->XML->AddParentTag('staff');
                $this->XML->AddTag('fullname', $_SWIFT_StaffObject->GetProperty('fullname'));
                $this->XML->AddTag('email', $_SWIFT_StaffObject->GetProperty('email'));
                $this->XML->AddTag('greeting', $_SWIFT_StaffObject->GetProperty('greeting'));
                $this->XML->AddTag('mobilenumber', $_SWIFT_StaffObject->GetProperty('mobilenumber'));
                $this->XML->AddTag('statusmsg', $_SWIFT_StaffObject->GetProperty('statusmessage'));
            $this->XML->EndTag('staff');
            // ======= END STAFF PROFILE DATA =======

            // ======= BEGIN AVATAR DATA =======
            SWIFT_StaffProfileImage::DispatchXML($this->XML, SWIFT_StaffProfileImage::TYPE_PRIVATE);
            // ======= END AVATAR DATA =======

            // ======= BEGIN CANNED DATA =======
            SWIFT_CannedManager::DispatchXML($this->XML);
            // ======= END CANNED DATA =======

            SWIFT_Session::FlushInactive();

            $this->XML->EndTag('kayako_livechat');
            $this->XML->EchoXMLWinapp();

            SWIFT_Chat::FlushInactive();

            return true;

        } else {
            // Log the Login Attempt First
            SWIFT_StaffLoginLog::Failure(urldecode($_POST['username']), SWIFT_StaffLoginLog::INTERFACE_WINAPP);
            if ($_SWIFT->Settings->Get('security_loginlocked') == '1') {
                $_loginLogCheck = SWIFT_StaffLoginLog::CanStaffLogin(urldecode($_POST['username']));

                if ($_loginLogCheck[0] && $_loginLogCheck[1] > 0) {
                    // Login was allowed but there were failures in the previous timeline

                    $this->_DispatchError(sprintf($this->Language->Get('loginlogwarning'), $_loginLogCheck[1], SWIFT_StaffLoginLog::GetLoginRetries()));
                } else {
                    $this->_DispatchError(sprintf($this->Language->Get('loginlogerror'), round(SWIFT_StaffLoginLog::GetLoginTimeline() / 60), $_loginLogCheck[1], SWIFT_StaffLoginLog::GetLoginRetries()));
                }
            } else {
                $this->_DispatchError($this->Language->Get('invaliduser'));
            }

            log_error_and_exit();
        }

        return false;
    }

    /**
     * Logout the Staff Member from the Winapp Interface
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function Logout()
    {
        $_SWIFT = SWIFT::GetInstance();

        $this->Load->LoadModel('Chat:Chat', APP_LIVECHAT);
        $this->Load->LoadModel('Chat:ChatQueue', APP_LIVECHAT);
        $this->Load->LoadModel('Chat:ChatChild', APP_LIVECHAT);
        $this->Load->LoadModel('Chat:ChatHits', APP_LIVECHAT);

        SWIFT_Chat::EndAllChatsByStaff($_SWIFT->Staff->GetStaffID());

        // Delete the staff session that we kept active
        if (!empty($_POST['staffsessionid']))
        {
            SWIFT_Session::EndCustomSession($_POST['staffsessionid']);
        }

        SWIFT_Session::Logout($this->Interface);

        $this->XML->AddParentTag('kayako_livechat');
            $this->XML->AddTag('status', '1');
        $this->XML->EndTag('kayako_livechat');
        $this->XML->EchoXMLWinapp();

        return true;
    }
}
