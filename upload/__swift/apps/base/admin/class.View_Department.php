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

use SWIFT;
use SWIFT_App;
use Base\Models\Department\SWIFT_Department;
use Base\Library\Help\SWIFT_Help;
use SWIFT_Hook;
use Base\Library\Language\SWIFT_LanguagePhraseLinked;
use Base\Models\Staff\SWIFT_Staff;
use Base\Library\Staff\SWIFT_StaffPermissionContainer;
use Base\Models\Staff\SWIFT_StaffSettings;
use Base\Models\User\SWIFT_UserGroupAssign;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceGrid;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridField;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridMassAction;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;
use SWIFT_View;

/**
 * The Department View
 *
 * @property \Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel $UserInterface
 * @property Controller_Department $Controller
 * @author Varun Shoor
 */
class View_Department extends SWIFT_View
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
     * Render the Department Form
     *
     * @author Varun Shoor
     * @param int $_mode The Render Mode
     * @param SWIFT_Department $_SWIFT_DepartmentObject The SWIFT_Department Object Pointer (Only for EDIT Mode)
     * @return bool "true" on Success, "false" otherwise
     */
    public function Render($_mode, SWIFT_Department $_SWIFT_DepartmentObject = null)
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $_departmentCache = $this->Cache->Get('departmentcache');
        $_staffGroupCache = $this->Cache->Get('staffgroupcache');
        $_staffCache = $this->Cache->Get('staffcache');
        $_groupAssignCache = $this->Cache->Get('groupassigncache');
        $_staffAssignCache = $this->Cache->Get('staffassigncache');

        // Calculate the URL
        if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            $this->UserInterface->Start(get_short_class($this), '/Base/Department/EditSubmit/' . $_SWIFT_DepartmentObject->GetDepartmentID(), SWIFT_UserInterface::MODE_EDIT, false);
        } else {
            $this->UserInterface->Start(get_short_class($this), '/Base/Department/InsertSubmit', SWIFT_UserInterface::MODE_INSERT, false);
        }

        $_departmentTitle = '';
        $_departmentID = $_parentDepartmentID = 0;
        $_userVisibilityCustom = false;

        $_parentOptionContainer = array();
        $_parentOptionContainer[0]['title'] = $this->Language->Get('naparentdep');
        $_parentOptionContainer[0]['value'] = 0;

        $_displayOrder = 1;
        if (_is_array($_departmentCache)) {
            $_displayOrder = count($_departmentCache);
        }

        $_departmentType = SWIFT_PUBLIC;
        $_userGroupIDList = array();
        $_parentDepartmentQuery = '';
        $_extendedMainJavaScript = '';

        if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle');
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('delete'), 'fa-trash', '/Base/Department/Delete/' . $_SWIFT_DepartmentObject->GetDepartmentID(), SWIFT_UserInterfaceToolbar::LINK_CONFIRM, '', '', false);
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('managedepartments'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

            $_userGroupIDList = SWIFT_UserGroupAssign::Retrieve($_SWIFT_DepartmentObject->GetDepartmentID(), SWIFT_UserGroupAssign::TYPE_DEPARTMENT);

            $_departmentTitle = $_SWIFT_DepartmentObject->GetProperty('title');
            $_displayOrder = $_SWIFT_DepartmentObject->GetProperty('displayorder');
            $_departmentID = (int)($_SWIFT_DepartmentObject->GetDepartmentID());
            $_parentDepartmentID = (int)($_SWIFT_DepartmentObject->GetProperty('parentdepartmentid'));
            $_userVisibilityCustom = $_SWIFT_DepartmentObject->GetProperty('uservisibilitycustom');

            $_departmentType = $_SWIFT_DepartmentObject->GetProperty('departmenttype');
            $_parentDepartmentQuery = "SELECT * FROM " . TABLE_PREFIX . "departments WHERE parentdepartmentid = '0' AND departmentapp = '" . $this->Database->Escape($_SWIFT_DepartmentObject->GetProperty('departmentapp')) . "' ORDER BY title ASC";
        } else {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('insert'), 'fa-plus-circle');
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('insertdepartments'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

            $_parentOptionContainer[0]['selected'] = true;
            $_parentDepartmentQuery = "SELECT * FROM " . TABLE_PREFIX . "departments WHERE parentdepartmentid = '0' ORDER BY title ASC";
        }

        /*
         * ###############################################
         * BEGIN GENERAL TAB
         * ###############################################
         */
        $_GeneralTabObject = $this->UserInterface->AddTab($this->Language->Get('tabgeneral'), 'icon_form.gif', 'general', true);
        $_GeneralTabObject->Text('title', $this->Language->Get('deptitle'), $this->Language->Get('desc_deptitle'), $_departmentTitle);

        if (empty($_parentDepartmentID)) {
            $_parentOptionContainer[0]['selected'] = true;
        }

        $_index = 1;
        $_extendedMainJavaScript .= '_departmentParentAppMap = new Array();';
        $this->Database->Query($_parentDepartmentQuery);
        while ($this->Database->NextRecord()) {
            if ($this->Database->Record['departmentid'] == $_departmentID) {
                continue;
            }

            $_appKey = 'app_' . $this->Database->Record['departmentapp'];
            $_parentOptionContainer[$_index]['title'] = $this->Database->Record['title'] . ' (' . $this->Language->Get($_appKey) . ')';
            $_parentOptionContainer[$_index]['value'] = $this->Database->Record['departmentid'];

            if ($this->Database->Record['departmentid'] == $_parentDepartmentID) {
                $_parentOptionContainer[$_index]['selected'] = true;
            }

            $_extendedMainJavaScript .= '_departmentParentAppMap[' . $this->Database->Record['departmentid'] . '] = \'' . $this->Database->Record['departmentapp'] . '\';';

            $_index++;
        }
        $_GeneralTabObject->Select('parentdepartmentid', $this->Language->Get('parentdepartment'), $this->Language->Get('desc_parentdepartment'), $_parentOptionContainer, 'javascript: ResetDepartmentParentApp();');

        $_GeneralTabObject->Number('displayorder', $this->Language->Get('displayorder'), $this->Language->Get('desc_displayorder'), $_displayOrder);

        $_GeneralTabObject->PublicPrivate('type', $this->Language->Get('deptype'), $this->Language->Get('desc_deptype'), IIF($_departmentType == SWIFT_PUBLIC, true, false));

        $_index = 0;
        $_appOptionContainer = array();

        foreach (array(APP_TICKETS, APP_LIVECHAT) as $_key => $_val) {
            if (SWIFT_App::IsInstalled($_val)) {
                $_appOptionContainer[$_index]['title'] = $this->Language->Get('app_' . $_val);
                $_appOptionContainer[$_index]['value'] = $_val;

                $_index++;
            }
        }

        if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            $_appKey = 'app_' . $_SWIFT_DepartmentObject->GetProperty('departmentapp');

            $_GeneralTabObject->DefaultDescriptionRow($this->Language->Get('departmentapp'), $this->Language->Get('desc_departmentapp'), '[' . $this->Language->Get($_appKey) . ']');

            $_GeneralTabObject->Hidden('departmentapp', $_SWIFT_DepartmentObject->GetProperty('departmentapp'));
        } else {
            $_GeneralTabObject->Select('departmentapp', $this->Language->Get('departmentapp'), $this->Language->Get('desc_departmentapp'), $_appOptionContainer);
        }

        /*
         * ###############################################
         * END GENERAL TAB
         * ###############################################
         */


        /*
         * ###############################################
         * BEGIN ASSIGNMENT TAB
         * ###############################################
         */

        $_AssignmentTabObject = $this->UserInterface->AddTab($this->Language->Get('tabassignments'), 'icon_vcard.gif', 'assignments');
        $_AssignmentTabObject->Title($this->Language->Get('assignedteams'), 'doublearrows.gif');
        foreach ($_staffGroupCache as $_key => $_val) {
            $_isAssigned = true;

            if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
                if (!isset($_groupAssignCache[$_val['staffgroupid']]) || !_is_array($_groupAssignCache[$_val['staffgroupid']])) {
                    $_isAssigned = false;
                } elseif (isset($_groupAssignCache[$_val['staffgroupid']])) {
                    if (in_array($_departmentID, $_groupAssignCache[$_val['staffgroupid']])) {
                        $_isAssigned = true;
                    } else {
                        $_isAssigned = false;
                    }
                }
            } else {
                $_isAssigned = true;
            }

            $_AssignmentTabObject->YesNo('assignedgroups[' . $_val['staffgroupid'] . ']', htmlspecialchars($_val['title']), '', $_isAssigned);
        }

        $_AssignmentTabObject->Title($this->Language->Get('assignedstaff'), 'doublearrows.gif');
        foreach ($_staffCache as $_key => $_val) {
            $_isAssigned = true;
            if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
                if (!isset($_staffAssignCache[$_val['staffid']]) || !_is_array($_staffAssignCache[$_val['staffid']])) {
                    $_isAssigned = false;
                } elseif (isset($_staffAssignCache[$_val['staffid']])) {
                    if (in_array($_departmentID, $_staffAssignCache[$_val['staffid']])) {
                        $_isAssigned = true;
                    } else {
                        $_isAssigned = false;
                    }
                }
            } else {
                $_isAssigned = true;
            }

            $_AssignmentTabObject->YesNo('assignedstaff[' . $_val['staffid'] . ']', text_to_html_entities($_val['fullname']), '', $_isAssigned, '', '', IIF($_val['groupassigns'] == 1, true, false));
        }

        /*
         * ###############################################
         * END ASSIGNMENT TAB
         * ###############################################
         */


        /*
         * ###############################################
         * BEGIN PERMISSION TAB (USER GROUP)
         * ###############################################
         */
        $_UserGroupTabObject = $this->UserInterface->AddTab($this->Language->Get('tabpermissionsclient'), 'icon_permissions.gif', 'permissionsclient');

        $_UserGroupTabObject->YesNo('uservisibilitycustom', $this->Language->Get('uservisibilitycustom'), $this->Language->Get('desc_uservisibilitycustom'), $_userVisibilityCustom);
        $_UserGroupTabObject->Title($this->Language->Get('usergroups'), 'doublearrows.gif');

        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "usergroups ORDER BY title ASC");
        while ($this->Database->NextRecord()) {
            $_isSelected = false;

            if ($_mode == SWIFT_UserInterface::MODE_INSERT && !isset($_POST['usergroupidlist'])) {
                $_isSelected = true;

            } elseif ($_mode == SWIFT_UserInterface::MODE_INSERT && isset($_POST['usergroupidlist'])) {
                if (isset($_POST['usergroupidlist'][$this->Database->Record['usergroupid']]) && $_POST['usergroupidlist'][$this->Database->Record['usergroupid']] == '1') {
                    $_isSelected = true;
                }

            } elseif ($_mode == SWIFT_UserInterface::MODE_EDIT && isset($_userGroupIDList[$this->Database->Record['usergroupid']])) {
                if (isset($_userGroupIDList[$this->Database->Record['usergroupid']]) && $_userGroupIDList[$this->Database->Record['usergroupid']] == '1') {
                    $_isSelected = true;
                }
            }

            $_UserGroupTabObject->YesNo('usergroupidlist[' . (int)($this->Database->Record['usergroupid']) . ']', htmlspecialchars($this->Database->Record['title']), '', $_isSelected);
        }

        /*
         * ###############################################
         * END PERMISSION TAB (USER GROUP)
         * ###############################################
         */


        /*
         * ###############################################
         * BEGIN PERMISSION TAB (STAFF)
         * ###############################################
         */

        if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            $_staffSettingContainer = SWIFT_StaffSettings::RetrieveOnDepartment($_SWIFT_DepartmentObject->GetDepartmentID());
            $_departmentPermissionContainer = SWIFT_StaffPermissionContainer::GetDepartment();


            $_StaffPermissionTabObject = $this->UserInterface->AddTab($this->Language->Get('tabpermissionsstaff'), 'icon_permissions.gif', 'permissionstaff', false, false, 0);

            $_cookiePermissionContainer = array();
            if (isset($_COOKIE['jqCookieJar_staffpermissions'])) {
                $_cookiePermissionContainer = @json_decode($_COOKIE['jqCookieJar_staffpermissions'], true);
            }

            foreach ($_staffCache as $_key => $_val) {
                $_tabHTML = '';
                $_isAssigned = false;

                if (isset($_staffAssignCache[$_val['staffid']]) && in_array($_SWIFT_DepartmentObject->GetDepartmentID(), $_staffAssignCache[$_val['staffid']])) {
                    $_isAssigned = true;
                }

                $_cookiePermission = false;
                if (isset($_cookiePermissionContainer['perm_ds_' . $_key]) && $_cookiePermissionContainer['perm_ds_' . $_key] == true) {
                    $_cookiePermission = true;
                }

                if (isset($_departmentPermissionContainer[$_SWIFT_DepartmentObject->GetProperty('departmentapp')])) {

                    $_tabHTML .= '<tr><td><table width="100%" border="0" cellspacing="1" cellpadding="4">';

                    $_tabHTML .= '<tr class="settabletitlerowmain2"><td class="settabletitlerowmain2" align="left" colspan="2">';
                    $_tabHTML .= '<span style="float: left;"><a href="javascript: void(0);" onclick="javascript: togglePermissionDiv(\'ds_' . addslashes($_key) . '\');"><img src="' . SWIFT::Get('themepath') . 'images/' . IIF($_isAssigned, 'icon_department.gif', 'icon_department_unassigned.gif') . '" align="absmiddle" border="0" /> ' . text_to_html_entities($_val['fullname']) . ' (' . htmlspecialchars($_val['username']) . ')</a></span><span style="float: right; margin-top: 3px;"><a href="javascript: void(0);" onclick="javascript: togglePermissionDiv(\'ds_' . addslashes($_key) . '\');"><img src="' . SWIFT::Get('themepath') . 'images/' . IIF($_cookiePermission, 'icon_minus', 'icon_plus') . '.gif" align="absmiddle" border="0" id="imgplus_ds_' . addslashes($_key) . '" /></a></span>';
                    $_tabHTML .= '</tr>';

                    $_tabHTML .= '</table>';

                    $_tabHTML .= '<div id="perm_ds_' . $_key . '" style="display: ' . IIF($_cookiePermission == '1', 'block', 'none') . ';">';
                    $_tabHTML .= '<table width="100%" border="0" cellspacing="1" cellpadding="4">';

                    $_StaffPermissionTabObject->RowHTML($_tabHTML);

                    foreach ($_departmentPermissionContainer[$_SWIFT_DepartmentObject->GetProperty('departmentapp')] as $_permissionKey => $_permissionValue) {
                        $_permissionResult = false;
                        if (isset($_staffSettingContainer[$_val['staffid']]) && isset($_staffSettingContainer[$_val['staffid']][$_permissionValue]) && $_staffSettingContainer[$_val['staffid']][$_permissionValue] == '1') {
                            $_permissionResult = true;
                        } elseif (!isset($_staffSettingContainer[$_val['staffid']])) {
                            $_permissionResult = true;
                        }

                        $_StaffPermissionTabObject->YesNo('perm[' . $_val['staffid'] . '][' . $_permissionValue . ']', $this->Language->Get($_permissionValue), '', $_permissionResult);
                    }

                    $_StaffPermissionTabObject->RowHTML('</table></div></td></tr>');
                }
            }
        }

        /*
         * ###############################################
         * END PERMISSION TAB (STAFF)
         * ###############################################
         */


        /*
         * ###############################################
         * BEGIN LANGUAGES TAB
         * ###############################################
         */

        $_LanguageTabObject = $this->UserInterface->AddTab($this->Language->Get('tablanguages'), 'icon_language2.gif', 'languages');
        $this->Controller->LanguagePhraseLinked->Render(SWIFT_LanguagePhraseLinked::TYPE_DEPARTMENT, $_departmentID, $_mode, $_LanguageTabObject);

        /*
         * ###############################################
         * END LANGUAGES TAB
         * ###############################################
         */

        // Begin Hook: admin_department_tabs
        unset($_hookCode);
        ($_hookCode = SWIFT_Hook::Execute('admin_department_tabs')) ? eval($_hookCode) : false;
        // End Hook

        $this->UserInterface->AppendHTML('<script type="text/javascript">QueueFunction(function() { ' . $_extendedMainJavaScript . ' }); </script>');

        $this->UserInterface->End();
    }

    /**
     * Render the Visitor Ban Grid
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function RenderGrid()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->Load->Library('UserInterface:UserInterfaceGrid', array('departmentgrid'), true, false, 'base');


        if ($this->UserInterfaceGrid->GetMode() == SWIFT_UserInterfaceGrid::MODE_SEARCH) {
            $this->UserInterfaceGrid->SetSearchQuery('SELECT * FROM ' . TABLE_PREFIX . 'departments WHERE parentdepartmentid = \'0\' AND (' . $this->UserInterfaceGrid->BuildSQLSearch('title') . ' OR ' . $this->UserInterfaceGrid->BuildSQLSearch('departmenttype') . ')', 'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX . 'departments WHERE parentdepartmentid = \'0\' AND (' . $this->UserInterfaceGrid->BuildSQLSearch('title') . ' OR ' . $this->UserInterfaceGrid->BuildSQLSearch('departmenttype') . ')');
        }

        $this->UserInterfaceGrid->SetSubQuery('SELECT * FROM ' . TABLE_PREFIX . 'departments WHERE parentdepartmentid IN (%s)', 'parentdepartmentid');

        $this->UserInterfaceGrid->SetQuery('SELECT * FROM ' . TABLE_PREFIX . 'departments WHERE parentdepartmentid = \'0\'', 'SELECT COUNT(*) FROM ' . TABLE_PREFIX . 'departments WHERE parentdepartmentid = \'0\'');

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('departmentid', 'departmentid', SWIFT_UserInterfaceGridField::TYPE_ID));

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('icon', '&nbsp;', SWIFT_UserInterfaceGridField::TYPE_CUSTOM, 16, SWIFT_UserInterfaceGridField::ALIGN_CENTER));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('title', $this->Language->Get('deptitle'), SWIFT_UserInterfaceGridField::TYPE_DB, 0, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('departmenttype', $this->Language->Get('deptype'), SWIFT_UserInterfaceGridField::TYPE_DB, 120, SWIFT_UserInterfaceGridField::ALIGN_CENTER));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('departmentapp', $this->Language->Get('depapp'), SWIFT_UserInterfaceGridField::TYPE_DB, 100, SWIFT_UserInterfaceGridField::ALIGN_CENTER));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('displayorder', $this->Language->Get('deporder'), SWIFT_UserInterfaceGridField::TYPE_DB, 60, SWIFT_UserInterfaceGridField::ALIGN_CENTER, SWIFT_UserInterfaceGridField::SORT_ASC), true);

        $this->UserInterfaceGrid->SetRenderCallback(array($this, 'GridRender'));
        $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('delete'), 'fa-trash', array('Base\Admin\Controller_Department', 'DeleteList'), $this->Language->Get('actionconfirm')));

        if ($_SWIFT->Staff->GetPermission('admin_caneditdepartment') != '0') {
            $this->UserInterfaceGrid->SetSortableCallback('displayorder', array('Base\Admin\Controller_Department', 'SortList'));
        }

        $this->UserInterfaceGrid->Render();

        $this->UserInterfaceGrid->Display();

        return true;
    }

    /**
     * The Grid Rendering Function
     *
     * @author Varun Shoor
     * @param array $_fieldContainer The Field Record Value Container
     * @return mixed "_fieldContainer" (ARRAY) on Success, "false" otherwise
     */
    public static function GridRender($_fieldContainer, $_isSubRecord = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_appTitle = 'app_' . $_fieldContainer['departmentapp'];

        $_departmentURL = '/Base/Department/Edit/' . (int)($_fieldContainer['departmentid']);

        $_fieldContainer['title'] = IIF($_isSubRecord == true, '<img src="' . SWIFT::Get('themepath') . 'images/linkdownarrow_blue.gif" border="0" align="absmiddle" /> ') . '<a href="' . SWIFT::Get('basename') . $_departmentURL . '" viewport="1" title="' . $_SWIFT->Language->Get('edit') . '">' . htmlspecialchars($_fieldContainer['title']) . '</a>';
        $_fieldContainer['icon'] = '<img src="' . SWIFT::Get('themepath') . IIF($_fieldContainer['departmenttype'] == 'private', 'images/icon_folderyellow3faded.gif', 'images/icon_folderyellow3.gif') . '" border="0" align="absmiddle" />';

        $_fieldContainer['departmentapp'] = IIF($_fieldContainer['departmentapp'] == APP_TICKETS, '<img src="' . SWIFT::Get('themepath') . 'images/icon_tickets.png" align="absmiddle" border="0" />', '<img src="' . SWIFT::Get('themepath') . 'images/icon_livesupport.gif" align="absmiddle" border="0" />') . '&nbsp;' . $_SWIFT->Language->Get($_appTitle);

        $_fieldContainer['departmenttype'] = IIF($_fieldContainer['departmenttype'] == SWIFT_PUBLIC, $_SWIFT->Language->Get('public'), $_SWIFT->Language->Get('private'));

        return $_fieldContainer;
    }

    /**
     * Render the Access Overview
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function RenderAccessOverview()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $_staffCache = $this->Cache->Get('staffcache');
        $_departmentCache = $this->Cache->Get('departmentcache');
        $_departmentMap = SWIFT_Department::GetDepartmentMap();

        $this->UserInterface->Start(get_short_class($this), '/Base/Department/AccessOverview', SWIFT_UserInterface::MODE_INSERT, false);

        $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('accessoverview'),
            SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);


        /*
         * ###############################################
         * BEGIN GENERAL TAB
         * ###############################################
         */
        $_GeneralTabObject = $this->UserInterface->AddTab($this->Language->Get('tabaccessoverview'), 'icon_lock.gif', 'general', true);
        $_GeneralTabObject->SetColumnWidth('25%');

        foreach ($_staffCache as $_staffID => $_staffContainer) {
            $_departmentListHTML = '';
            $_assignedDepartmentIDList = SWIFT_Staff::GetAssignedDepartmentsOnStaffID($_staffID, -1);

            foreach ($_departmentMap as $_departmentID => $_departmentContainer) {
                if (!in_array($_departmentID, $_assignedDepartmentIDList) || !isset($_departmentCache[$_departmentID])) {
                    continue;
                }

                $_departmentIcon = 'icon_folderyellow3.gif';
                if ($_departmentContainer['departmentapp'] == APP_LIVECHAT) {
                    $_departmentIcon = 'icon_livesupport.gif';
                } elseif ($_departmentContainer['departmentapp'] == APP_TICKETS) {
                    $_departmentIcon = 'icon_tickets.png';
                }

                $_departmentListHTML .= '<img src="' . SWIFT::Get('themepathimages') . $_departmentIcon . '" align="absmiddle" border="0" /> ' . text_to_html_entities($_departmentContainer['title']) . '<br/>';

                foreach ($_departmentContainer['subdepartments'] as $_subDepartmentID => $_subDepartmentContainer) {
                    if (!in_array($_subDepartmentID, $_assignedDepartmentIDList) || !isset($_departmentCache[$_subDepartmentID])) {
                        continue;
                    }

                    $_departmentListHTML .= '<img src="' . SWIFT::Get('themepathimages') . 'linkdownarrow_blue.gif" align="absmiddle" border="0" /> <img src="' . SWIFT::Get('themepathimages') . 'icon_folderyellow3.gif" align="absmiddle" border="0" /> ' . text_to_html_entities($_subDepartmentContainer['title']) . '<br/>';
                }

                if (count($_departmentContainer['subdepartments'])) {
                    $_departmentListHTML .= '<br />';
                }
            }

            $_GeneralTabObject->DefaultDescriptionRow(text_to_html_entities($_staffContainer['fullname'] . ' (' . $_staffContainer['username'] . ')'), htmlspecialchars($_staffContainer['email']), $_departmentListHTML);
        }

        /*
         * ###############################################
         * END GENERAL TAB
         * ###############################################
         */

        $this->UserInterface->End();

        return true;
    }
}

?>
