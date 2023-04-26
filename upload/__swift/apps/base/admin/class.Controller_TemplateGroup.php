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

namespace Base\Admin;

use Controller_admin;
use SWIFT;
use SWIFT_App;
use SWIFT_Exception;
use SWIFT_Session;
use Base\Models\Staff\SWIFT_StaffActivityLog;
use Base\Models\Template\SWIFT_TemplateGroup;
use Base\Models\User\SWIFT_UserGroup;
use Base\Library\UserInterface\SWIFT_UserInterface;

/**
 * The Template Group Controller
 *
 * @property \Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel $UserInterface
 * @property View_TemplateGroup $View
 * @author Varun Shoor
 */
class Controller_TemplateGroup extends Controller_admin
{
    // Core Constants
    const MENU_ID = 1;
    const NAVIGATION_ID = 0;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct()
    {
        parent::__construct();

        $this->Language->Load('templates');
    }

    /**
     * Delete the Template Groups from Mass Action
     *
     * @author Varun Shoor
     * @param mixed $_templateGroupIDList The Template Group ID List Container Array
     * @param bool $_byPassCSRF Whether to bypass the CSRF check
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_templateGroupIDList, $_byPassCSRF = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('admin_tmpcandeletegroup') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        if (_is_array($_templateGroupIDList)) {
            $_SWIFT->Database->Query("SELECT title FROM " . TABLE_PREFIX . "templategroups WHERE tgroupid IN (" . BuildIN($_templateGroupIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activitydeletetemplategroup'), htmlspecialchars($_SWIFT->Database->Record['title'])), SWIFT_StaffActivityLog::ACTION_DELETE, SWIFT_StaffActivityLog::SECTION_TEMPLATES, SWIFT_StaffActivityLog::INTERFACE_ADMIN);
            }

            SWIFT_TemplateGroup::DeleteList($_templateGroupIDList);
        }

        return true;
    }

    /**
     * Delete the Given Template Group ID
     *
     * @author Varun Shoor
     * @param int $_templateGroupID The Template Group ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete($_templateGroupID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($_templateGroupID), true);

        $this->Load->Manage();

        return true;
    }

    /**
     * Restore the Template Groups from Mass Action
     *
     * @author Varun Shoor
     * @param mixed $_templateGroupIDList The Template Group ID List Container Array
     * @param bool $_byPassCSRF Whether to bypass the CSRF check
     * @return bool "true" on Success, "false" otherwise
     */
    public static function RestoreList($_templateGroupIDList, $_byPassCSRF = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('admin_tmpcanrestoretemplates') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        if (_is_array($_templateGroupIDList)) {
            $_SWIFT->Database->Query("SELECT title FROM " . TABLE_PREFIX . "templategroups WHERE tgroupid IN (" . BuildIN($_templateGroupIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activityrestoretemplategroup'), htmlspecialchars($_SWIFT->Database->Record['title'])), SWIFT_StaffActivityLog::ACTION_DELETE, SWIFT_StaffActivityLog::SECTION_TEMPLATES, SWIFT_StaffActivityLog::INTERFACE_ADMIN);
            }

            SWIFT_TemplateGroup::RestoreList($_templateGroupIDList, $_SWIFT->Staff->GetStaffID());
        }

        return true;
    }

    /**
     * Restore the Given Template Group ID
     *
     * @author Varun Shoor
     * @param int $_templateGroupID The Template Group ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Restore($_templateGroupID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::RestoreList(array($_templateGroupID), true);

        $this->Load->Manage();

        return true;
    }

    /**
     * Displays the Template Group Grid
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

        $this->UserInterface->Header($this->Language->Get('templates') . ' > ' . $this->Language->Get('managegroups'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_tmpcanviewgroups') == '0') {
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
     * @param int $_templateGroupID The Template Group ID
     * @param bool $_passwordWasEnabled password enabled settings
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    private function RunChecks($_mode, $_templateGroupID = 0, $_passwordWasEnabled = true)
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

        if (trim($_POST['title']) == '' || trim($_POST['companyname']) == '' || trim($_POST['languageid']) == '') {
            $this->UserInterface->CheckFields('title', 'companyname', 'languageid');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            return false;
        } elseif (
            (trim($_POST['groupusername']) !== '' || $_POST['enablepassword'] === 1)
            && ($_mode === SWIFT_UserInterface::MODE_INSERT || !$_passwordWasEnabled)
            && trim($_POST['password']) === '') {
            SWIFT::ErrorField('password');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            return false;
        } elseif ($_POST['enablepassword'] == 1 && trim($_POST['groupusername']) == '') {
            SWIFT::ErrorField('groupusername');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            return false;
        } elseif (trim($_POST["password"]) != '' && $_POST['password'] != $_POST['passwordconfirm']) {
            $this->UserInterface->Error($this->Language->Get('titlepwnomatch'), $this->Language->Get('msgpwnomatch'));

            $_POST["password"] = '';
            $_POST["passwordconfirm"] = '';
            SWIFT::ErrorField('password', 'passwordconfirm');

            return false;
        } elseif (!preg_match('/^[a-z][a-z0-9_]+$/i', $_POST['title'])) {
            SWIFT::ErrorField('title');
            $this->UserInterface->Error($this->Language->Get('titleinvalidgrouptitle'), $this->Language->Get('msginvalidgrouptitle'));

            return false;
        } elseif (SWIFT::Get('isdemo') == true) {
            $this->UserInterface->Error($this->Language->Get('titledemomode'), $this->Language->Get('msgdemomode'));

            return false;
        } elseif ($_POST['isenabled'] == '0' && $_POST['isdefault'] == '1') {
            $this->UserInterface->Error($this->Language->Get('titleisenabledprob'), $this->Language->Get('msgisenabledprob'));

            return false;
        }

        $_templateGroup = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "templategroups WHERE title = '" . $this->Database->Escape($_POST['title']) . "'");
        if (isset($_templateGroup['tgroupid']) && !empty($_templateGroup['tgroupid']) && $_templateGroup['tgroupid'] != $_templateGroupID) {
            $this->UserInterface->Error($this->Language->Get('titlegrouptitleexists'), $this->Language->Get('msggrouptitleexists'));

            return false;
        }

        // Trying to set Is Default to No and we have only one template group?
        $_totalCountContainer = $this->Database->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "templategroups");
        if (isset($_totalCountContainer['totalitems']) && $_totalCountContainer['totalitems'] == 1 && $_POST['isdefault'] == '0' && $_mode == SWIFT_UserInterface::MODE_EDIT) {
            SWIFT::ErrorField('isdefault');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            return false;
        }

        if (($_mode == SWIFT_UserInterface::MODE_INSERT && $_SWIFT->Staff->GetPermission('admin_tmpcaninsertgroup') == '0') || ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT->Staff->GetPermission('admin_tmpcanupdategroup') == '0')) {
            $this->UserInterface->Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));

            return false;
        }

        return true;
    }

    /**
     * Insert a new Template Group
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

        $this->UserInterface->Header($this->Language->Get('templates') . ' > ' . $this->Language->Get('insertemplategroup'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_tmpcaninsertgroup') == '0') {
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
     * @param SWIFT_TemplateGroup $_SWIFT_TemplateGroupObject The Template Group Object
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    private function _RenderConfirmation($_mode, SWIFT_TemplateGroup $_SWIFT_TemplateGroupObject)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }
        if (!$_SWIFT_TemplateGroupObject instanceof SWIFT_TemplateGroup || !$_SWIFT_TemplateGroupObject->GetIsClassLoaded()) {
            return false;
        }

        $_languageCache = $this->Cache->Get('languagecache');
        $_departmentCache = $this->Cache->Get('departmentcache');
        $_ticketStatusCache = $this->Cache->Get('statuscache');
        $_ticketPriorityCache = $this->Cache->Get('prioritycache');

        if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            $_type = 'update';
        } else {
            $_type = 'insert';
        }

        $_finalText = '<b>' . $this->Language->Get('tgrouptitle') . ':</b> ' . htmlspecialchars($_POST['title']) . '<br />';
        $_finalText .= '<b>' . $this->Language->Get('companyname') . ':</b> ' . htmlspecialchars($_POST['companyname']) . '<br />';
        $_finalText .= '<b>' . $this->Language->Get('isdefault') . ':</b> ' . htmlspecialchars(IIF($_POST['isdefault'] == 1, $this->Language->Get('yes'), $this->Language->Get('no'))) . '<br />';

//        $_finalText .= '<b>' . $this->Language->Get('loginshare') . ':</b> ' . htmlspecialchars($_SWIFT['loginsharecache'][$_templategroup['loginshareid']]['title']).'<br />';

        $_finalText .= '<b>' . $this->Language->Get('isenabled') . ':</b> ' . htmlspecialchars(IIF($_POST['isenabled'] == 1, $this->Language->Get('yes'), $this->Language->Get('no'))) . '<br />';

        if (isset($_languageCache[$_POST['languageid']])) {
            $_finalText .= '<b>' . $this->Language->Get('defaultlanguage') . ':</b> ' . htmlspecialchars($_languageCache[$_POST['languageid']]['title']) . '<br />';
        }

        if (SWIFT_App::IsInstalled(APP_TICKETS)) {
            if (isset($_departmentCache[$_POST['departmentid']])) {
                $_finalText .= '<b>' . $this->Language->Get('ticketdep') . ':</b> ' . text_to_html_entities($_departmentCache[$_POST['departmentid']]['title']) . '<br />';
            }

            if (isset($_ticketStatusCache[$_POST['ticketstatusid']])) {
                $_finalText .= '<b>' . $this->Language->Get('ticketstatus') . ':</b> ' . htmlspecialchars($_ticketStatusCache[$_POST['ticketstatusid']]['title']) . '<br />';
            }

            if (isset($_ticketPriorityCache[$_POST['priorityid']])) {
                $_finalText .= '<b>' . $this->Language->Get('ticketpriority') . ':</b> ' . htmlspecialchars($_ticketPriorityCache[$_POST['priorityid']]['title']) . '<br />';
            }
        }

        $_SWIFT_UserGroupObject_Guest = new SWIFT_UserGroup($_POST['guestusergroupid']);
        $_SWIFT_UserGroupObject_Registered = new SWIFT_UserGroup($_POST['regusergroupid']);
        if (!$_SWIFT_UserGroupObject_Guest instanceof SWIFT_UserGroup || !$_SWIFT_UserGroupObject_Guest->GetIsClassLoaded() || !$_SWIFT_UserGroupObject_Registered instanceof SWIFT_UserGroup || !$_SWIFT_UserGroupObject_Registered->GetIsClassLoaded()) {
            return false;
        }

        $_finalText .= '<b>' . $this->Language->Get('guestusergroup') . ':</b> ' . htmlspecialchars($_SWIFT_UserGroupObject_Guest->GetProperty('title')) . '<br />';
        $_finalText .= '<b>' . $this->Language->Get('regusergroup') . ':</b> ' . htmlspecialchars($_SWIFT_UserGroupObject_Registered->GetProperty('title')) . '<br />';

        $_finalText .= '<b>' . $this->Language->Get('restrictgroups') . ':</b> ' . htmlspecialchars(IIF($_POST['restrictgroups'] == 1, $this->Language->Get('yes'), $this->Language->Get('no'))) . '<br />';

        SWIFT::Info(sprintf($this->Language->Get('titletgroup' . $_type), htmlspecialchars($_POST['title'])), sprintf($this->Language->Get('msgtgroup' . $_type), htmlspecialchars($_POST['title'])) . '<br />' . $_finalText);

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
        }

        if ($this->RunChecks(SWIFT_UserInterface::MODE_INSERT)) {
            if (isset($_POST['useloginshare'])) {
                $_useLoginShare = (int)($_POST['useloginshare']);
            } else {
                $_useLoginShare = false;
            }

            $_ticketTypeID = 0;
            if (isset($_POST['tickettypeid']) && !empty($_POST['tickettypeid'])) {
                $_ticketTypeID = $_POST['tickettypeid'];
            }

            $_defaultLiveChatDepartmentID = 0;
            if (isset($_POST['departmentid_livechat']) && !empty($_POST['departmentid_livechat'])) {
                $_defaultLiveChatDepartmentID = $_POST['departmentid_livechat'];
            }

            $_SWIFT_TemplateGroupObject = SWIFT_TemplateGroup::Create($_POST['title'], '', $_POST['companyname'], $_POST['enablepassword'],
                $_POST['groupusername'], $_POST['password'], $_POST['languageid'], $_POST['isdefault'], $_POST['restrictgroups'],
                $_POST['guestusergroupid'], $_POST['regusergroupid'], $_useLoginShare, $_POST['fromtgroupid'], $_POST['departmentid'],
                $_POST['ticketstatusid'], $_POST['priorityid'], $_ticketTypeID, $_POST['prompt_tickettype'],
                $_POST['prompt_ticketpriority'], $_defaultLiveChatDepartmentID, $_POST['isenabled'], false);

            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityinserttemplategroup'), htmlspecialchars($_POST['title'])), SWIFT_StaffActivityLog::ACTION_INSERT, SWIFT_StaffActivityLog::SECTION_TEMPLATES, SWIFT_StaffActivityLog::INTERFACE_ADMIN);

            if (!$_SWIFT_TemplateGroupObject instanceof SWIFT_TemplateGroup || !$_SWIFT_TemplateGroupObject->GetIsClassLoaded()) {
                throw new SWIFT_Exception(SWIFT_CREATEFAILED);
            }

            $this->_RenderConfirmation(SWIFT_UserInterface::MODE_INSERT, $_SWIFT_TemplateGroupObject);

            $this->Load->Manage();

            return true;
        }

        $this->Load->Insert();

        return false;
    }

    /**
     * Edit the Template Group ID
     *
     * @author Varun Shoor
     * @param int $_templateGroupID The Template Group ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Edit($_templateGroupID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } elseif (empty($_templateGroupID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_TemplateGroupObject = new SWIFT_TemplateGroup($_templateGroupID);
        if (!$_SWIFT_TemplateGroupObject instanceof SWIFT_TemplateGroup || !$_SWIFT_TemplateGroupObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->UserInterface->Header($this->Language->Get('templates') . ' > ' . $this->Language->Get('edittemplategroup'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_tmpcanupdategroup') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render(SWIFT_UserInterface::MODE_EDIT, $_SWIFT_TemplateGroupObject);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Edit Submission Processor
     *
     * @author Varun Shoor
     * @param int $_templateGroupID The Template Group ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function EditSubmit($_templateGroupID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } elseif (empty($_templateGroupID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_TemplateGroupObject = new SWIFT_TemplateGroup($_templateGroupID);
        if (!$_SWIFT_TemplateGroupObject instanceof SWIFT_TemplateGroup || !$_SWIFT_TemplateGroupObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if ($this->RunChecks(SWIFT_UserInterface::MODE_EDIT, $_SWIFT_TemplateGroupObject->GetTemplateGroupID(), $_SWIFT_TemplateGroupObject->GetProperty('enablepassword'))) {
            $_useLoginShare = false;
            if (isset($_POST['useloginshare'])) {
                $_useLoginShare = (int)($_POST['useloginshare']);
            }

            $_ticketTypeID = 0;
            if (isset($_POST['tickettypeid']) && !empty($_POST['tickettypeid'])) {
                $_ticketTypeID = $_POST['tickettypeid'];
            }

            $_defaultLiveChatDepartmentID = 0;
            if (isset($_POST['departmentid_livechat']) && !empty($_POST['departmentid_livechat'])) {
                $_defaultLiveChatDepartmentID = $_POST['departmentid_livechat'];
            }

            $_updateResult = $_SWIFT_TemplateGroupObject->Update($_POST['title'], '', $_POST['companyname'], $_POST['enablepassword'],
                $_POST['groupusername'], $_POST['password'], $_POST['languageid'], $_POST['isdefault'], $_POST['restrictgroups'],
                $_POST['guestusergroupid'], $_POST['regusergroupid'], $_useLoginShare, $_POST['departmentid'], $_POST['ticketstatusid'],
                $_POST['priorityid'], $_ticketTypeID, $_POST['prompt_tickettype'], $_POST['prompt_ticketpriority'],
                $_defaultLiveChatDepartmentID, $_POST['isenabled']);

            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityupdatetemplategroup'), htmlspecialchars($_POST['title'])), SWIFT_StaffActivityLog::ACTION_UPDATE, SWIFT_StaffActivityLog::SECTION_TEMPLATES, SWIFT_StaffActivityLog::INTERFACE_ADMIN);

            if (!$_updateResult) {
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            }

            $this->_RenderConfirmation(SWIFT_UserInterface::MODE_EDIT, $_SWIFT_TemplateGroupObject);

            $this->Load->Manage();

            return true;
        }

        $this->Load->Edit($_templateGroupID);

        return false;
    }
}

?>
