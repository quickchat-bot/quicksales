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

use SWIFT;
use SWIFT_App;
use LiveChat\Models\Skill\SWIFT_ChatSkill;
use Base\Models\Department\SWIFT_Department;
use SWIFT_Exception;
use Base\Library\Help\SWIFT_Help;
use SWIFT_Hook;
use Base\Models\Staff\SWIFT_Staff;
use Base\Library\Staff\SWIFT_StaffPermissionContainer;
use Base\Models\Staff\SWIFT_StaffProfileImage;
use Base\Models\Staff\SWIFT_StaffSettings;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceGrid;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridField;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridMassAction;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;
use SWIFT_View;

/**
 * The Staff View
 *
 * @property \Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel $UserInterface
 * @property \SWIFT_TimeZone $TimeZone
 * @property Controller_Staff $Controller
 * @author Varun Shoor
 */
class View_Staff extends SWIFT_View
{
    /**
     * Constructor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Render the Staff Form
     *
     * @author Varun Shoor
     * @param int $_mode The Render Mode
     * @param SWIFT_Staff $_SWIFT_StaffObject The SWIFT_Staff Object Pointer (Only for EDIT Mode)
     * @return bool "true" on Success, "false" otherwise
     */
    public function Render($_mode, SWIFT_Staff $_SWIFT_StaffObject = null)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $_staffGroupCache = $this->Cache->Get('staffgroupcache');
        $_departmentCache = $_SWIFT->Cache->Get('departmentcache');

        // Calculate the URL
        if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            $this->UserInterface->Start(get_short_class($this), '/Base/Staff/EditSubmit/' . $_SWIFT_StaffObject->GetStaffID(), SWIFT_UserInterface::MODE_EDIT, false, true);
        } else {
            $this->UserInterface->Start(get_short_class($this), '/Base/Staff/InsertSubmit', SWIFT_UserInterface::MODE_INSERT, false, true);
        }

        $_staffFirstName = '';
        $_staffLastName = '';
        $_staffDesignation = '';
        $_staffUserName = '';
        $_staffEmail = '';
        $_staffGroupID = false;
        $_staffIsEnabled = true;
        $_staffMobileNumber = '';
        $_staffSignature = '';
        $_staffAvatarImage = SWIFT::Get('themepath') . 'images/icon_defaultavatar.gif';
        $_staffGroupAssigns = true;
        $_staffID = false;
        $_staffSettingContainer = array();
        $_staffAssignedDepartments = array();
        $_staffGreeting = '';
        $_ipRestriction = '';
        $_profileImageBottom = '';
        $_timeZonePHP = '';
        $_enableDST = false;

        if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle');

            $this->UserInterface->Toolbar->AddButton($this->Language->Get('delete'), 'fa-trash', '/Base/Staff/Delete/' . $_SWIFT_StaffObject->GetStaffID(), SWIFT_UserInterfaceToolbar::LINK_CONFIRM, '', '', false);
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('staff'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

            $_staffFirstName = $_SWIFT_StaffObject->GetProperty('firstname');
            $_staffLastName = $_SWIFT_StaffObject->GetProperty('lastname');
            $_staffDesignation = $_SWIFT_StaffObject->GetProperty('designation');
            $_staffUserName = $_SWIFT_StaffObject->GetProperty('username');
            $_staffEmail = $_SWIFT_StaffObject->GetProperty('email');
            $_staffMobileNumber = $_SWIFT_StaffObject->GetProperty('mobilenumber');
            $_staffSignature = $_SWIFT_StaffObject->GetProperty('signature');
            $_staffGreeting = $_SWIFT_StaffObject->GetProperty('greeting');
            $_ipRestriction = $_SWIFT_StaffObject->GetProperty('iprestriction');
            $_timeZonePHP = $_SWIFT_StaffObject->GetProperty('timezonephp');
            $_enableDST = (int)($_SWIFT_StaffObject->GetProperty('enabledst'));

            $_staffGroupID = (int)($_SWIFT_StaffObject->GetProperty('staffgroupid'));
            $_staffIsEnabled = (int)($_SWIFT_StaffObject->GetProperty('isenabled'));

            $_staffGroupAssigns = (int)($_SWIFT_StaffObject->GetProperty('groupassigns'));

            $_staffID = $_SWIFT_StaffObject->GetStaffID();
            $_staffSettingContainer = SWIFT_StaffSettings::RetrieveOnStaff($_staffID);

            $_staffAssignedDepartments = $_SWIFT_StaffObject->GetAssignedDepartments(-1);

            if (SWIFT_StaffProfileImage::StaffHasProfileImage($_staffID)) {
                $_staffAvatarImage = SWIFT::Get('basename') . '/Base/Staff/GetProfileImage/' . $_staffID;
                $_profileImageBottom = '<a class="clearimagebutton" href="javascript: void(0);" onclick="javascript: loadViewportData(\'/Base/Staff/ClearProfileImage/' . $_staffID . '\');">' . $this->Language->Get('clear') . '</a>';
            }
        } else {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('insert'), 'fa-plus-circle');
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('insertstaff'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);
        }


        /*
         * ###############################################
         * BEGIN GENERAL TAB
         * ###############################################
         */
        $_GeneralTabObject = $this->UserInterface->AddTab($this->Language->Get('tabgeneral'), 'icon_form.gif', 'general', true);

        $_GeneralTabObject->RowHTML('<tr><td colspan="2" align="left" valign="top"><table width="100%" border="0" cellspacing="0" cellpadding="0">');

        $_GeneralTabObject->Text('firstname', $this->Language->Get('stafffirstname'), $this->Language->Get('desc_stafffirstname'), $_staffFirstName);
        $_GeneralTabObject->Text('lastname', $this->Language->Get('stafflastname'), $this->Language->Get('desc_stafflastname'), $_staffLastName);
        $_GeneralTabObject->Text('designation', $this->Language->Get('staffdesignation'), $this->Language->Get('desc_staffdesignation'), $_staffDesignation);

        $_GeneralTabObject->RowHTML('</table></td></tr>');

        $_GeneralTabObject->Title($this->Language->Get('generaloptions'), 'icon_doublearrows.gif');

        $_GeneralTabObject->Text('username', $this->Language->Get('staffusername'), $this->Language->Get('desc_staffusername'), $_staffUserName, 'text', 20);

        $_GeneralTabObject->Password('password', $this->Language->Get('staffpassword'), IIF($_mode == SWIFT_UserInterface::MODE_INSERT, $this->Language->Get('desc_staffpassword'), $this->Language->Get('desc_staffpasswordedit')) . '<BR /> ' . $this->Controller->StaffPasswordPolicy->GetPasswordPolicyString());
        $_GeneralTabObject->Password("passwordconfirm", $this->Language->Get('staffpasswordconfirm'), $this->Language->Get('desc_staffpasswordconfirm'));
        $_GeneralTabObject->Text('email', $this->Language->Get('staffemail'), $this->Language->Get('desc_staffemail'), $_staffEmail);

        $_index = 0;
        $_optionsContainer = array();

        foreach ($_staffGroupCache as $_key => $_val) {
            $_optionsContainer[$_index]['title'] = $_val['title'];
            $_optionsContainer[$_index]['value'] = (int)($_key);

            if (($_mode == SWIFT_UserInterface::MODE_INSERT && $_index == 0) || ($_staffGroupID == $_key)) {
                $_optionsContainer[$_index]['selected'] = true;
            } else {
                $_optionsContainer[$_index]['selected'] = false;
            }

            $_index++;
        }

        $_GeneralTabObject->Select('staffgroupid', $this->Language->Get('staffgroup'), $this->Language->Get('desc_staffgroup'), $_optionsContainer);

        $_GeneralTabObject->YesNo('isenabled', $this->Language->Get('isenabled'), $this->Language->Get('desc_isenabled'), $_staffIsEnabled);

        /*
         * ###############################################
         * END GENERAL TAB
         * ###############################################
         */


        /*
         * ###############################################
         * BEGIN PROFILE TAB
         * ###############################################
         */

        $_ProfileTabObject = $this->UserInterface->AddTab($this->Language->Get('tabprofile'), 'icon_settings2.gif', 'profile', false);

        $_ProfileTabObject->Title($this->Language->Get('personalizeoptions'), 'icon_doublearrows.gif');

        $_ProfileTabObject->RowHTML('<tr><td align="left" valign="top" width=""></td><td align="left" valign="top" width="105"><img style="margin: 8px;border-radius:3px;box-shadow: 0px 0px 3px 2px rgba(0,0,0,0.06), 0 1px 3px 0 rgba(44,48,56,0.09);" src="' . $_staffAvatarImage . '" align="absmiddle" border="0" />' . $_profileImageBottom . '</td></tr>');

        $_ProfileTabObject->File('profileimage', $this->Language->Get('staffprofileimage'), $this->Language->Get('desc_staffprofileimage'), 0);

        $_ProfileTabObject->Title($this->Language->Get('generaloptions'), 'icon_doublearrows.gif');

        $_ProfileTabObject->Text('mobilenumber', $this->Language->Get('staffmobilenumber'), $this->Language->Get('desc_staffmobilenumber'), $_staffMobileNumber, 'text', 20);

        $_ProfileTabObject->TextArea('iprestriction', $this->Language->Get('iprestriction'), $this->Language->Get('desc_iprestriction'), $_ipRestriction, 40, 2);

        $_ProfileTabObject->TextArea('signature', $this->Language->Get('staffsignature'), $this->Language->Get('desc_staffsignature'), $_staffSignature, 60, 4);

        $_optionsContainer = array();
        $_index = 1;
        $_optionsContainer[0]['title'] = $this->Language->Get('tzusedefault');
        $_optionsContainer[0]['value'] = '';
        if ($_timeZonePHP == '') {
            $_optionsContainer[0]['selected'] = true;
        }

        $this->Load->Library('Time:TimeZone');
        $_timeZoneContainer = $this->TimeZone->Get();
        foreach ($_timeZoneContainer as $_timeZone) {
            $_optionsContainer[$_index]['title'] = $_timeZone['title'];
            $_optionsContainer[$_index]['value'] = $_timeZone['value'];

            if ($_timeZonePHP == $_timeZone['value']) {
                $_optionsContainer[$_index]['selected'] = true;
            }

            $_index++;
        }

        $_ProfileTabObject->Select('timezonephp', $this->Language->Get('stafftimezone'), $this->Language->Get('desc_stafftimezone'), $_optionsContainer);

        $_ProfileTabObject->YesNo('enabledst', $this->Language->Get('staffenabledst'), $this->Language->Get('desc_staffenabledst'), $_enableDST);

        if (SWIFT_App::IsInstalled(APP_LIVECHAT)) {
            $_ProfileTabObject->Title($this->Language->Get('livechatoptions'), 'icon_doublearrows.gif');

            $_ProfileTabObject->Text('greeting', $this->Language->Get('lcgreeting'), $this->Language->Get('desc_lcgreeting'), $_staffGreeting, 'text', 60);
        }

        /*
         * ###############################################
         * END PROFILE TAB
         * ###############################################
         */


        /*
         * ###############################################
         * BEGIN DEPARTMENTS TAB
         * ###############################################
         */

        $_DepartmentsTabObject = $this->UserInterface->AddTab($this->Language->Get('tabdepartments'), 'icon_folderyellow3.gif', 'departments');

        $_departmentIDListJS = array();
        foreach ($_departmentCache as $_key => $_val) {
            $_departmentIDListJS[] = "'" . $_val['departmentid'] . "'";
        }

        $_extendedMainJavaScript = '';
        if (count($_departmentIDListJS)) {
            $_extendedMainJavaScript = "StatusDepartmentSelect(new Array(" . implode(', ', $_departmentIDListJS) . "));";
        }

        $_DepartmentsTabObject->YesNo('groupassigns', $this->Language->Get('usegroupdep'), $this->Language->Get('desc_usegroupdep'), $_staffGroupAssigns, $_extendedMainJavaScript);

        $_departmentMapContainer = array();

        $_departmentSubItemContainer = array();

        foreach (array(APP_TICKETS, APP_LIVECHAT) as $_key => $_val) {
            if (!SWIFT_App::IsInstalled($_val)) {
                continue;
            }

            $_departmentMapContainer[$_val] = SWIFT_Department::GetDepartmentMap($_val);

            $_icon = $_text = '';

            if ($_val == APP_TICKETS) {
                $_icon = 'icon_tickets.png';
                $_text = $this->Language->Get('app_tickets');

            } elseif ($_val == APP_LIVECHAT) {
                $_icon = 'icon_livesupport.gif';
                $_text = $this->Language->Get('app_livechat');

            }

            $_DepartmentsTabObject->Title(IIF(!empty($_icon), '<img src="' . SWIFT::Get('themepath') . 'images/' . $_icon . '" align="absmiddle" border="0" /> ') . $_SWIFT->Language->Get('assigneddepartments') . ': ' . $_text);

            foreach ($_departmentMapContainer[$_val] as $_departmentVal) {
                $_isAssignedParent = $this->IsDepartmentAssigned($_mode, $_staffID, $_departmentVal['departmentid']);

                $_extendedJavaScript = '';
                if (count($_departmentVal['subdepartmentids'])) {
                    $_finalImplodeDepartmentList = array();
                    foreach ($_departmentVal['subdepartmentids'] as $_key => $_val) {
                        $_finalImplodeDepartmentList[] = '\'' . (int)($_val) . '\'';
                    }

                    $_extendedJavaScript = 'ChangeDepartmentRadioStatus(\'' . 'View_Staffform' . '\', this.value, new Array(' . implode(', ', $_finalImplodeDepartmentList) . '));';
                }

                $_DepartmentsTabObject->YesNo('assigned[' . $_departmentVal['departmentid'] . ']', text_to_html_entities($_departmentVal['title']), '', $_isAssignedParent, $_extendedJavaScript);

                if (_is_array($_departmentVal['subdepartments'])) {
                    foreach ($_departmentVal['subdepartments'] as $_subDepartmentVal) {
                        if (!$_isAssignedParent) {
                            $_departmentSubItemContainer[] = $_subDepartmentVal['departmentid'];
                        }

                        $_isAssigned = $this->IsDepartmentAssigned($_mode, $_staffID, $_subDepartmentVal['departmentid']);

                        $_DepartmentsTabObject->YesNo('assigned[' . $_subDepartmentVal['departmentid'] . ']', '<img src="' . SWIFT::Get('themepath') . 'images/linkdownarrow_blue.gif" border="0" align="absmiddle" /> ' . text_to_html_entities($_subDepartmentVal['title']), '', $_isAssigned);
                    }
                }
            }
        }

        /*
         * ###############################################
         * END DEPARTMENTS TAB
         * ###############################################
         */


        /*
         * ###############################################
         * BEGIN PERMISSIONS TAB
         * ###############################################
         */

        $_PermissionsTabObject = $this->UserInterface->AddTab($this->Language->Get('permissions'), 'icon_permissions.gif', 'permissions', false, false, 0);

        $_departmentPermissionContainer = SWIFT_StaffPermissionContainer::GetDepartment();

        $_cookiePermissionContainer = array();
        if (isset($_COOKIE['jqCookieJar_staffpermissions'])) {
            $_cookiePermissionContainer = @json_decode($_COOKIE['jqCookieJar_staffpermissions'], true);
        }

        foreach ($_departmentCache as $_key => $_val) {
            $_tabHTML = '';
            $_isAssigned = false;

            if (in_array($_key, $_staffAssignedDepartments)) {
                $_isAssigned = true;
            }

            $_cookiePermission = false;
            if (isset($_cookiePermissionContainer['perm_d_' . $_key]) && $_cookiePermissionContainer['perm_d_' . $_key] == true) {
                $_cookiePermission = true;
            }

            if (isset($_departmentPermissionContainer[$_val['departmentapp']])) {
                $_appName = $this->Language->Get('app_' . $_val['departmentapp']);
                if (!$_appName) {
                    $_appName = $_val['departmentapp'];
                }

                $_tabHTML .= '<tr><td><table width="100%" border="0" cellspacing="1" cellpadding="4">';

                $_tabHTML .= '<tr class="settabletitlerowmain2"><td class="settabletitlerowmain2" align="left" colspan="2">';
                $_tabHTML .= '<span style="float: left;"><a href="javascript: void(0);" onclick="javascript: togglePermissionDiv(\'d_' . addslashes($_key) . '\');"><img src="' . SWIFT::Get('themepath') . 'images/' . IIF($_isAssigned, 'icon_department.gif', 'icon_department_unassigned.gif') . '" align="absmiddle" border="0" /> ' . $_appName . ': ' . text_to_html_entities($_val['title']) . IIF(!$_isAssigned, ' <i>' . $this->Language->Get('depnotassigned') . '</i>') . '</a></span><span style="float: right; margin-top: 3px;"><a href="javascript: void(0);" onclick="javascript: togglePermissionDiv(\'d_' . addslashes($_key) . '\');"><img src="' . SWIFT::Get('themepath') . 'images/' . IIF($_cookiePermission, 'icon_minus', 'icon_plus') . '.gif" align="absmiddle" border="0" id="imgplus_d_' . addslashes($_key) . '" /></a></span>';
                $_tabHTML .= '</td></tr>';

                $_tabHTML .= '</table>';

                $_tabHTML .= '<div id="perm_d_' . $_key . '" style="display: ' . IIF($_cookiePermission == '1', 'block', 'none') . ';">';
                $_tabHTML .= '<table width="100%" border="0" cellspacing="1" cellpadding="4">';

                $_PermissionsTabObject->RowHTML($_tabHTML);

                foreach ($_departmentPermissionContainer[$_val['departmentapp']] as $_permissionKey => $_permissionValue) {
                    $_permissionResult = true;
                    if (isset($_staffSettingContainer[$_val['departmentid']]) && isset($_staffSettingContainer[$_val['departmentid']][$_permissionValue]) && $_staffSettingContainer[$_val['departmentid']][$_permissionValue] == '1') {
                        $_permissionResult = true;
                    } elseif (isset($_staffSettingContainer[$_val['departmentid']]) && isset($_staffSettingContainer[$_val['departmentid']][$_permissionValue]) && $_staffSettingContainer[$_val['departmentid']][$_permissionValue] == '0') {
                        $_permissionResult = false;
                    } elseif (!isset($_staffSettingContainer[$_val['departmentid']])) {
                        $_permissionResult = true;
                    }

                    $_PermissionsTabObject->YesNo('perm[' . $_val['departmentid'] . '][' . $_permissionValue . ']', $this->Language->Get($_permissionValue), '', $_permissionResult);
                }

                $_PermissionsTabObject->RowHTML('</table></div></td></tr>');
            }
        }

        /*
         * ###############################################
         * END PERMISSIONS TAB
         * ###############################################
         */


        /*
         * ###############################################
         * BEGIN SKILLS TAB
         * ###############################################
         */

        if (SWIFT_App::IsInstalled(APP_LIVECHAT)) {
            $_SkillsTabObject = $this->UserInterface->AddTab($this->Language->Get('tabskills'), 'icon_chatskill.gif', 'staffskills', false);

            $_chatSkillIDList = array();

            $this->Load->LoadModel('Skill:ChatSkill', APP_LIVECHAT);

            if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
                $_chatSkillIDList = SWIFT_ChatSkill::RetrieveSkillListOnStaff($_SWIFT_StaffObject->GetStaffID());
            }

            $_chatSkillContainer = SWIFT_ChatSkill::RetrieveSkills();
            foreach ($_chatSkillContainer as $_key => $_val) {
                $_skillResult = false;
                if ($_mode == SWIFT_UserInterface::MODE_INSERT) {
                    $_skillResult = true;
                } else {
                    if (in_array($_key, $_chatSkillIDList)) {
                        $_skillResult = true;
                    }
                }

                $_SkillsTabObject->YesNo('skills[' . $_key . ']', '<img src="' . SWIFT::Get('themepathimages') . 'icon_chatskill.gif' . '" align="absmiddle" border="0" /> ' . htmlspecialchars($_val['title']), '', $_skillResult);
            }

            if (!count($_chatSkillContainer)) {
                $_rowContainer = array();
                $_rowContainer[0]['value'] = '<i>' . $this->Language->Get('noskillstodisplay') . '</i>';
                $_rowContainer[0]['align'] = 'left';
                $_SkillsTabObject->Row($_rowContainer, 'gridrow2');
            }
        }

        /*
         * ###############################################
         * END SKILLS TAB
         * ###############################################
         */

        // Begin Hook: admin_staff_tabs
        unset($_hookCode);
        ($_hookCode = SWIFT_Hook::Execute('admin_staff_tabs')) ? eval($_hookCode) : false;
        // End Hook

        $this->UserInterface->AppendHTML('<script type="text/javascript">QueueFunction(function() { ' . $_extendedMainJavaScript . ' ChangeDepartmentRadioStatus(\'View_StaffGroupform\', \'0\', new Array(' . implode(', ', $_departmentSubItemContainer) . ')); }); </script>');

        $this->UserInterface->End();

        return true;
    }

    /**
     * Check to see if the department is assigned to a given Staff
     *
     * @author Varun Shoor
     * @param mixed $_mode The User Interface Mode
     * @param int $_staffID The Staff ID
     * @param int $_departmentID The Department ID to check on
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function IsDepartmentAssigned($_mode, $_staffID, $_departmentID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_staffAssignCache = $this->Cache->Get('staffassigncache');

        $_isAssigned = true;
        if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            if (!isset($_staffAssignCache[$_staffID]) || !_is_array($_staffAssignCache[$_staffID])) {
                $_isAssigned = false;
            } else {
                if (in_array($_departmentID, $_staffAssignCache[$_staffID])) {
                    $_isAssigned = true;
                } else {
                    $_isAssigned = false;
                }
            }
        } else {
            $_isAssigned = true;
        }

        return $_isAssigned;
    }

    /**
     * Render the Ticket File Type Grid
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function RenderGrid()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->Load->Library('UserInterface:UserInterfaceGrid', array('staffgrid'), true, false, 'base');

        if ($this->UserInterfaceGrid->GetMode() == SWIFT_UserInterfaceGrid::MODE_SEARCH) {
            $this->UserInterfaceGrid->SetSearchQuery('SELECT * FROM ' . TABLE_PREFIX . 'staff AS staff LEFT JOIN ' . TABLE_PREFIX . 'staffgroup AS staffgroup ON (staff.staffgroupid = staffgroup.staffgroupid) WHERE (' . $this->UserInterfaceGrid->BuildSQLSearch('staff.fullname') . ') OR (' . $this->UserInterfaceGrid->BuildSQLSearch('staff.email') . ') OR (' . $this->UserInterfaceGrid->BuildSQLSearch('staff.username') . ') OR (' . $this->UserInterfaceGrid->BuildSQLSearch('staffgroup.title') . ')', 'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX . 'staff AS staff LEFT JOIN ' . TABLE_PREFIX . 'staffgroup AS staffgroup ON (staff.staffgroupid = staffgroup.staffgroupid) WHERE (' . $this->UserInterfaceGrid->BuildSQLSearch('staff.fullname') . ') OR (' . $this->UserInterfaceGrid->BuildSQLSearch('staff.email') . ') OR (' . $this->UserInterfaceGrid->BuildSQLSearch('staff.username') . ') OR (' . $this->UserInterfaceGrid->BuildSQLSearch('staffgroup.title') . ')');
        }

        $this->UserInterfaceGrid->SetQuery('SELECT * FROM ' . TABLE_PREFIX . 'staff AS staff LEFT JOIN ' . TABLE_PREFIX . 'staffgroup AS staffgroup ON (staff.staffgroupid = staffgroup.staffgroupid)', 'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX . 'staff AS staff LEFT JOIN ' . TABLE_PREFIX . 'staffgroup AS staffgroup ON (staff.staffgroupid = staffgroup.staffgroupid)');

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('staffid', 'staffid', SWIFT_UserInterfaceGridField::TYPE_ID));

        /*
         * BUG FIX - Ravi Sharma
         *
         * SWIFT-3353 Sort Staff list according to enabled/disabled staff.
         *
         * Comments: None
         */
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('staff.isenabled', '&nbsp;', SWIFT_UserInterfaceGridField::TYPE_DB, 16, SWIFT_UserInterfaceGridField::ALIGN_CENTER), true);
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('staff.fullname', $this->Language->Get('fullname'), SWIFT_UserInterfaceGridField::TYPE_DB, 0, SWIFT_UserInterfaceGridField::ALIGN_LEFT, SWIFT_UserInterfaceGridField::SORT_ASC), true);
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('staff.username', $this->Language->Get('username'), SWIFT_UserInterfaceGridField::TYPE_DB, 140, SWIFT_UserInterfaceGridField::ALIGN_CENTER));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('staffgroup.title', $this->Language->Get('staffgroup'), SWIFT_UserInterfaceGridField::TYPE_DB, 200, SWIFT_UserInterfaceGridField::ALIGN_CENTER));

        $this->UserInterfaceGrid->SetRenderCallback(array($this, 'GridRender'));
        $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('delete'), 'fa-trash', array('Base\Admin\Controller_Staff', 'DeleteList'), $this->Language->Get('actionconfirm')));
        $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('enable'), 'fa-check-circle', array('Base\Admin\Controller_Staff', 'EnableList'), $this->Language->Get('actionconfirm')));
        $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('disable'), 'fa-minus-circle', array('Base\Admin\Controller_Staff', 'DisableList'), $this->Language->Get('actionconfirm')));

        $this->UserInterfaceGrid->SetNewLinkViewport(SWIFT::Get('basename') . '/Base/Staff/Insert');

        $this->UserInterfaceGrid->Render();

        $this->UserInterfaceGrid->Display();

        return true;
    }

    /**
     * The Grid Rendering Function
     *
     * @author Varun Shoor
     * @param array $_fieldContainer The Field Record Value Container
     * @return array The Processed Field Container Array
     */
    public static function GridRender($_fieldContainer)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_staffGroupCache = $_SWIFT->Cache->Get('staffgroupcache');

        $_staffURL = '/Base/Staff/Edit/' . (int)($_fieldContainer['staffid']);

        $_fieldContainer['staff.fullname'] = '<a href="' . SWIFT::Get('basename') . $_staffURL . '" viewport="1" title="' . $_SWIFT->Language->Get('edit') . '">' . text_to_html_entities($_fieldContainer['fullname']) . '</a>';

        $_statusImage = 'icon_bulboff.gif';
        if ($_fieldContainer['isenabled'] == '0') {
            $_statusImage = 'icon_block.gif';
        } elseif (in_array($_fieldContainer['staffid'], Controller_Staff::$_activeSessionList)) {
            $_statusImage = 'icon_bulbon.gif';
        }

        $_fieldContainer['staff.isenabled'] = '<img src="' . SWIFT::Get('themepath') . 'images/' . $_statusImage . '" border="0" align="absmiddle" />';

        $_fieldContainer['staff.username'] = htmlspecialchars($_fieldContainer['username']);
        $_fieldContainer['staff.staffid'] = $_fieldContainer['staffid'];

        $_isAdmin = false;
        $_staffGroupTitle = $_SWIFT->Language->Get('na');

        if (isset($_staffGroupCache[$_fieldContainer['staffgroupid']])) {
            $_isAdmin = (int)($_staffGroupCache[$_fieldContainer['staffgroupid']]['isadmin']);
            $_staffGroupTitle = htmlspecialchars($_staffGroupCache[$_fieldContainer['staffgroupid']]['title']);
        }

        $_fieldContainer['staffgroup.title'] = '<img src="' . SWIFT::Get('themepath') . 'images/' . IIF($_isAdmin, 'icon_admin.gif', 'icon_notadmin.gif') . '" border="0" align="absmiddle" />&nbsp;' . $_staffGroupTitle;

        return $_fieldContainer;
    }
}
