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
use SWIFT_Exception;
use Base\Library\Help\SWIFT_Help;
use SWIFT_Hook;
use Base\Library\Permissions\SWIFT_PermissionsRenderer;
use Base\Models\Staff\SWIFT_StaffGroup;
use Base\Models\Staff\SWIFT_StaffGroupSettings;
use Base\Library\Staff\SWIFT_StaffPermissionContainer;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceGrid;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridField;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridMassAction;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;
use SWIFT_View;

/**
 * The Staff Group View
 *
 * @property \Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel $UserInterface
 * @property Controller_StaffGroup $Controller
 * @author Varun Shoor
 */
class View_StaffGroup extends SWIFT_View
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
     * Check to see if the department is assigned to a given Staff Group
     *
     * @author Varun Shoor
     * @param mixed $_mode The User Interface Mode
     * @param int $_staffGroupID The Staff Group ID
     * @param int $_departmentID The Department ID to check on
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function IsDepartmentAssigned($_mode, $_staffGroupID, $_departmentID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_groupAssignCache = $this->Cache->Get('groupassigncache');

        $_isAssigned = true;
        if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            if (!isset($_groupAssignCache[$_staffGroupID]) || !_is_array($_groupAssignCache[$_staffGroupID])) {
                $_isAssigned = false;
            } else {
                if (in_array($_departmentID, $_groupAssignCache[$_staffGroupID])) {
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
     * Render the Staff Group Form
     *
     * @author Varun Shoor
     * @param int $_mode The Render Mode
     * @param SWIFT_StaffGroup $_SWIFT_StaffGroupObject The SWIFT_StaffGroup Object Pointer (Only for EDIT Mode)
     * @return bool "true" on Success, "false" otherwise
     */
    public function Render($_mode, SWIFT_StaffGroup $_SWIFT_StaffGroupObject = null)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $_groupAssignCache = $this->Cache->Get('groupassigncache');

        $this->DispatchMenu(SWIFT_PermissionsRenderer::PERMISSIONS_STAFF);
        $this->DispatchMenu(SWIFT_PermissionsRenderer::PERMISSIONS_ADMIN);

        /*
         * ###############################################
         * BEGIN TAB COUNTS
         * ###############################################
         */

        $_staffPermissionContainer = SWIFT_StaffPermissionContainer::GetStaff();

        $_totalStaffCount = $_totalAdminCount = 0;

        foreach ($_staffPermissionContainer as $_key => $_val) {
            if (SWIFT_App::IsInstalled($_key)) {
                $_totalStaffCount += count($_val);
            }
        }

        $_adminPermissionContainer = SWIFT_StaffPermissionContainer::GetAdmin();

        foreach ($_adminPermissionContainer as $_key => $_val) {
            if (SWIFT_App::IsInstalled($_key)) {
                $_totalAdminCount += count($_val);
            }
        }

        /*
         * ###############################################
         * END TAB COUNTS
         * ###############################################
         */

        // Calculate the URL
        if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            $this->UserInterface->Start(get_short_class($this), '/Base/StaffGroup/EditSubmit/' . $_SWIFT_StaffGroupObject->GetStaffGroupID(), SWIFT_UserInterface::MODE_EDIT, false);
        } else {
            $this->UserInterface->Start(get_short_class($this), '/Base/StaffGroup/InsertSubmit', SWIFT_UserInterface::MODE_INSERT, false);
        }

        $_GeneralTabObject = $this->UserInterface->AddTab($this->Language->Get('tabgeneral'), 'icon_form.gif', 'general', true);
        $_DepartmentsTabObject = $this->UserInterface->AddTab($this->Language->Get('tabdepartments'), 'icon_folderyellow3.gif', 'departments');
        $_StaffPermissionsTabObject = $this->UserInterface->AddTab($this->Language->Get('tabpermissionsstaff') . ' <font color=\'#8BB467\'>(' . $_totalStaffCount . ')</font>', 'icon_permissions.gif', 'staffpermissions');
        $_AdminPermissionsTabObject = $this->UserInterface->AddTab($this->Language->Get('tabpermissionsadmin') . ' <font color=\'#8BB467\'>(' . $_totalAdminCount . ')</font>', 'icon_permissions.gif', 'adminpermissions');

        $_staffGroupTitle = '';
        $_staffGroupIsAdmin = false;

        $_staffGroupID = 0;
        $_permissionValueContainer = array();

        // Load the custom toolbars for the permission tabs
        $_StaffPermissionsTabObject->LoadToolbar('staffpermissiontoolbar');
        $_AdminPermissionsTabObject->LoadToolbar('adminpermissiontoolbar');

        if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle');
            $_StaffPermissionsTabObject->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle');
            $_AdminPermissionsTabObject->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle');

            $_deleteURL = '/Base/StaffGroup/Delete/' . $_SWIFT_StaffGroupObject->GetStaffGroupID();
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('delete'), 'fa-trash', $_deleteURL, SWIFT_UserInterfaceToolbar::LINK_CONFIRM, '', '', false);
            $_StaffPermissionsTabObject->Toolbar->AddButton($this->Language->Get('delete'), 'fa-trash', $_deleteURL, SWIFT_UserInterfaceToolbar::LINK_CONFIRM, '', '', false);
            $_AdminPermissionsTabObject->Toolbar->AddButton($this->Language->Get('delete'), 'fa-trash', $_deleteURL, SWIFT_UserInterfaceToolbar::LINK_CONFIRM, '', '', false);

            $_StaffPermissionsTabObject->Toolbar->AddButton($this->Language->Get('copyfrom') . ' <img src="' . SWIFT::Get('themepath') . 'images/menudropgray.gif" align="absmiddle" border="0" />', 'fa-copy', 'UIDropDown(\'staffpermissionmenu\', event, \'staffpermissionid\', \'staffpermissiontoolbar\');', SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, 'staffpermissionid', '', false);
            $_AdminPermissionsTabObject->Toolbar->AddButton($this->Language->Get('copyfrom') . ' <img src="' . SWIFT::Get('themepath') . 'images/menudropgray.gif" align="absmiddle" border="0" />', 'fa-copy', 'UIDropDown(\'adminpermissionmenu\', event, \'adminpermissionid\', \'adminpermissiontoolbar\');', SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, 'adminpermissionid', '', false);

            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('staffgroup'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);
            $_StaffPermissionsTabObject->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('staffgroup'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);
            $_AdminPermissionsTabObject->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('staffgroup'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

            $_staffGroupTitle = $_SWIFT_StaffGroupObject->GetProperty('title');
            $_staffGroupIsAdmin = (int)($_SWIFT_StaffGroupObject->GetProperty('isadmin'));

            $_staffGroupID = $_SWIFT_StaffGroupObject->GetStaffGroupID();

            $_SWIFT_StaffGroupSettingsObject = new SWIFT_StaffGroupSettings($_SWIFT_StaffGroupObject->GetStaffGroupID());
            $_permissionValueContainer = $_SWIFT_StaffGroupSettingsObject->GetSettings();
        } else {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('insert'), 'fa-plus-circle');
            $_StaffPermissionsTabObject->Toolbar->AddButton($this->Language->Get('insert'), 'fa-plus-circle');
            $_AdminPermissionsTabObject->Toolbar->AddButton($this->Language->Get('insert'), 'fa-plus-circle');

            $_StaffPermissionsTabObject->Toolbar->AddButton($this->Language->Get('copyfrom') . ' <img src="' . SWIFT::Get('themepath') . 'images/menudropgray.gif" align="absmiddle" border="0" />', 'fa-copy', 'UIDropDown(\'staffpermissionmenu\', event, \'staffpermissionid\', \'staffpermissiontoolbar\');', SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, 'staffpermissionid', '', false);
            $_AdminPermissionsTabObject->Toolbar->AddButton($this->Language->Get('copyfrom') . ' <img src="' . SWIFT::Get('themepath') . 'images/menudropgray.gif" align="absmiddle" border="0" />', 'fa-copy', 'UIDropDown(\'adminpermissionmenu\', event, \'adminpermissionid\', \'adminpermissiontoolbar\');', SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, 'adminpermissionid', '', false);

            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('insertstaffgroup'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);
            $_StaffPermissionsTabObject->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('staffgroup'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);
            $_AdminPermissionsTabObject->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('staffgroup'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);
        }

        if (isset($_POST['isadmin'])) {
            $_staffGroupIsAdmin = (int)($_POST['isadmin']);
        }

        /*
         * ###############################################
         * BEGIN GENERAL TAB
         * ###############################################
         */

        $_GeneralTabObject->Text('title', $this->Language->Get('grouptitle'), $this->Language->Get('desc_grouptitle'), $_staffGroupTitle);
        $_GeneralTabObject->YesNo('isadmin', $this->Language->Get('groupisadmin'), $this->Language->Get('desc_groupisadmin'), $_staffGroupIsAdmin, 'ToggleAdminPermissionsTab(this.value)');

        /*
         * ###############################################
         * END GENERAL TAB
         * ###############################################
         */


        /*
         * ###############################################
         * BEGIN DEPARTMENTS TAB
         * ###############################################
         */

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

            foreach ($_departmentMapContainer[$_val] as $_departmentKey => $_departmentVal) {
                $_isAssignedParent = $this->IsDepartmentAssigned($_mode, $_staffGroupID, $_departmentVal['departmentid']);

                $_extendedJavaScript = '';
                if (count($_departmentVal['subdepartmentids'])) {
                    $_finalImplodeDepartmentList = array();
                    foreach ($_departmentVal['subdepartmentids'] as $_key => $_val) {
                        $_finalImplodeDepartmentList[] = '\'' . (int)($_val) . '\'';
                    }

                    $_extendedJavaScript = 'ChangeDepartmentRadioStatus(\'' . 'View_StaffGroupform' . '\', this.value, new Array(' . implode(', ', $_finalImplodeDepartmentList) . '));';
                }

                $_DepartmentsTabObject->YesNo('assigned[' . $_departmentVal['departmentid'] . ']', $_departmentVal['title'], '', $_isAssignedParent, $_extendedJavaScript);

                if (_is_array($_departmentVal['subdepartments'])) {
                    foreach ($_departmentVal['subdepartments'] as $_subDepartmentKey => $_subDepartmentVal) {
                        if (!$_isAssignedParent) {
                            $_departmentSubItemContainer[] = $_subDepartmentVal['departmentid'];
                        }

                        $_isAssigned = $this->IsDepartmentAssigned($_mode, $_staffGroupID, $_subDepartmentVal['departmentid']);

                        $_DepartmentsTabObject->YesNo('assigned[' . $_subDepartmentVal['departmentid'] . ']', '<img src="' . SWIFT::Get('themepath') . 'images/linkdownarrow_blue.gif" border="0" align="absmiddle" /> ' . $_subDepartmentVal['title'], '', $_isAssigned);
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
         * BEGIN PERMISSIONS (STAFF) TAB
         * ###############################################
         */

        $_StaffPermissionsTabObject->StartContainer('sgstaffpermissionscontainer');
        $_StaffPermissionsTabObject->RowHTML('<tr><td>');
        $this->Controller->PermissionsRenderer->RenderPermissionsHTML($this->UserInterface, SWIFT_PermissionsRenderer::PERMISSIONS_STAFF, $_permissionValueContainer, $_StaffPermissionsTabObject);
        $_StaffPermissionsTabObject->RowHTML('</td></tr>');
        $_StaffPermissionsTabObject->EndContainer();

        /*
         * ###############################################
         * END PERMISSIONS (STAFF) TAB
         * ###############################################
         */


        /*
         * ###############################################
         * BEGIN PERMISSIONS (ADMIN) TAB
         * ###############################################
         */

        $_AdminPermissionsTabObject->StartContainer('sgadminpermissionscontainer');
        $_AdminPermissionsTabObject->RowHTML('<tr><td>');
        $this->Controller->PermissionsRenderer->RenderPermissionsHTML($this->UserInterface, SWIFT_PermissionsRenderer::PERMISSIONS_ADMIN, $_permissionValueContainer, $_AdminPermissionsTabObject);
        $_AdminPermissionsTabObject->RowHTML('</td></tr>');
        $_AdminPermissionsTabObject->EndContainer();

        /*
         * ###############################################
         * END PERMISSIONS (ADMIN) TAB
         * ###############################################
         */

        // Begin Hook: admin_staffteam_tabs
        unset($_hookCode);
        ($_hookCode = SWIFT_Hook::Execute('admin_staffteam_tabs')) ? eval($_hookCode) : false;
        // End Hook

        $this->UserInterface->AppendHTML('<script type="text/javascript">QueueFunction(function() { ToggleAdminPermissionsTab("' . (int)($_staffGroupIsAdmin) . '"); ChangeDepartmentRadioStatus(\'View_StaffGroupform\', \'0\', new Array(' . implode(', ', $_departmentSubItemContainer) . ')); }); </script>');

        $this->UserInterface->End();

        return true;
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

        $this->Load->Library('UserInterface:UserInterfaceGrid', array('staffgroupgrid'), true, false, 'base');

        if ($this->UserInterfaceGrid->GetMode() == SWIFT_UserInterfaceGrid::MODE_SEARCH) {
            $this->UserInterfaceGrid->SetSearchQuery('SELECT * FROM ' . TABLE_PREFIX . 'staffgroup WHERE (' . $this->UserInterfaceGrid->BuildSQLSearch('title') . ')', 'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX . 'staffgroup WHERE (' . $this->UserInterfaceGrid->BuildSQLSearch('title') . ')');
        }

        $this->UserInterfaceGrid->SetQuery('SELECT * FROM ' . TABLE_PREFIX . 'staffgroup', 'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX . 'staffgroup');

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('staffgroupid', 'staffgroupid', SWIFT_UserInterfaceGridField::TYPE_ID));

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('icon', '&nbsp;', SWIFT_UserInterfaceGridField::TYPE_CUSTOM, 16, SWIFT_UserInterfaceGridField::ALIGN_CENTER));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('title', $this->Language->Get('title'), SWIFT_UserInterfaceGridField::TYPE_DB, 0, SWIFT_UserInterfaceGridField::ALIGN_LEFT, SWIFT_UserInterfaceGridField::SORT_ASC), true);
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('isadmin', $this->Language->Get('isadmin'), SWIFT_UserInterfaceGridField::TYPE_DB, 120, SWIFT_UserInterfaceGridField::ALIGN_CENTER));

        $this->UserInterfaceGrid->SetRenderCallback(array($this, 'GridRender'));
        $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('delete'), 'fa-trash', array('Base\Admin\Controller_StaffGroup', 'DeleteList'), $this->Language->Get('actionconfirm')));

        $this->UserInterfaceGrid->SetNewLinkViewport(SWIFT::Get('basename') . '/Base/StaffGroup/Insert');
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

        $_fieldContainer['icon'] = IIF($_fieldContainer['isadmin'], '<img src="' . SWIFT::Get('themepath') . 'images/icon_admin.gif" border="0" align="absmiddle" />', '<img src="' . SWIFT::Get('themepath') . 'images/icon_notadmin.gif" border="0" align="absmiddle" />');
        $_fieldContainer['isadmin'] = IIF($_fieldContainer['isadmin'] == 1, $_SWIFT->Language->Get('yes'), $_SWIFT->Language->Get('no'));

        $_staffGroupURL = '/Base/StaffGroup/Edit/' . (int)($_fieldContainer['staffgroupid']);

        $_fieldContainer['title'] = '<a href="' . SWIFT::Get('basename') . $_staffGroupURL . '" viewport="1" title="' . $_SWIFT->Language->Get('edit') . '">' . htmlspecialchars($_fieldContainer['title']) . '</a>';

        return $_fieldContainer;
    }

    /**
     * Dispatches the XML Menu
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    protected function DispatchMenu($_type = SWIFT_PermissionsRenderer::PERMISSIONS_STAFF)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_whereExtension = '';
        if ($_type == SWIFT_PermissionsRenderer::PERMISSIONS_ADMIN) {
            $_whereExtension = ' WHERE isadmin = \'1\'';
        }

        echo '<ul class="swiftdropdown" id="' . $_type . 'permissionmenu">';

        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "staffgroup" . $_whereExtension . " ORDER BY title ASC");
        while ($this->Database->NextRecord()) {
            echo '<li class="swiftdropdownitemparent" onclick="javascript: ReplaceTeamPermissionsDiv(\'' . $_type . '\', \'' . (int)($this->Database->Record['staffgroupid']) . '\');"><div class="swiftdropdownitem"><div class="swiftdropdownitemimage"><img src="' . SWIFT::Get('themepath') . 'images/menu_settings.gif' . '" align="absmiddle" border="0" /></div><div class="swiftdropdownitemtext" onclick="javascript: void(0);">' . htmlspecialchars($this->Database->Record['title']) . '</div></div></li>';
        }

        echo '</ul>';

        return true;
    }
}

?>
