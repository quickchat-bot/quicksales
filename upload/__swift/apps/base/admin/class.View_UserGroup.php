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

namespace Base\Admin;

use SWIFT;
use SWIFT_App;
use Base\Library\Help\SWIFT_Help;
use SWIFT_Hook;
use Base\Library\Permissions\SWIFT_PermissionsRenderer;
use Base\Models\User\SWIFT_UserGroup;
use Base\Models\User\SWIFT_UserGroupSettings;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceGrid;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridField;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridMassAction;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;
use Base\Library\User\SWIFT_UserPermissionContainer;
use SWIFT_View;

/**
 * The User Group View
 *
 * @property \Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel $UserInterface
 * @property Controller_UserGroup $Controller
 * @author Varun Shoor
 */
class View_UserGroup extends SWIFT_View
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
     * Render the User Group Form
     *
     * @author Varun Shoor
     * @param int $_mode The Render Mode
     * @param SWIFT_UserGroup $_SWIFT_UserGroupObject The SWIFT_UserGroup Object Pointer (Only for EDIT Mode)
     * @return bool "true" on Success, "false" otherwise
     */
    public function Render($_mode, SWIFT_UserGroup $_SWIFT_UserGroupObject = null)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        // Calculate the URL
        if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            $this->UserInterface->Start(get_short_class($this), '/Base/UserGroup/EditSubmit/' . $_SWIFT_UserGroupObject->GetUserGroupID(), SWIFT_UserInterface::MODE_EDIT, false);
        } else {
            $this->UserInterface->Start(get_short_class($this), '/Base/UserGroup/InsertSubmit', SWIFT_UserInterface::MODE_INSERT, false);
        }

        $_userGroupTitle = '';
        $_userGroupType = SWIFT_UserGroup::TYPE_REGISTERED;

        $_permissionValueContainer = array();

        if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle');
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('delete'), 'fa-trash', '/Base/UserGroup/Delete/' . $_SWIFT_UserGroupObject->GetUserGroupID(), SWIFT_UserInterfaceToolbar::LINK_CONFIRM, '', '', false);
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('usergroup'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

            $_userGroupTitle = $_SWIFT_UserGroupObject->GetProperty('title');
            $_userGroupType = (int)($_SWIFT_UserGroupObject->GetProperty('grouptype'));

            $_SWIFT_UserGroupSettingsObject = new SWIFT_UserGroupSettings($_SWIFT_UserGroupObject->GetUserGroupID());
            $_permissionValueContainer = $_SWIFT_UserGroupSettingsObject->GetSettings();
        } else {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('insert'), 'fa-plus-circle');
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('usergroup'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);
        }


        /*
         * ###############################################
         * BEGIN GENERAL TAB
         * ###############################################
         */
        $_GeneralTabObject = $this->UserInterface->AddTab($this->Language->Get('tabgeneral'), 'icon_form.gif', 'general', true);

        $_GeneralTabObject->Text('title', $this->Language->Get('usergrouptitle'), $this->Language->Get('desc_usergrouptitle'), $_userGroupTitle);

        $_radioContainer = array();
        $_index = 0;
        foreach (array(SWIFT_UserGroup::TYPE_REGISTERED => $this->Language->Get('ugregistered'), SWIFT_UserGroup::TYPE_GUEST => $this->Language->Get('ugguest')) as $_key => $_val) {
            $_radioContainer[$_index]['title'] = $_val;
            $_radioContainer[$_index]['value'] = $_key;

            if ($_key == $_userGroupType) {
                $_radioContainer[$_index]['checked'] = true;
            }

            $_index++;
        }

        $_GeneralTabObject->Radio('grouptype', $this->Language->Get('usergrouptype'), $this->Language->Get('desc_usergrouptype'), $_radioContainer, false);

        /*
         * ###############################################
         * END GENERAL TAB
         * ###############################################
         */


        /*
         * ###############################################
         * BEGIN PERMISSIONS TAB
         * ###############################################
         */

        $_userPermissionContainer = SWIFT_UserPermissionContainer::GetDefault();

        $_totalPermissionCount = 0;

        foreach ($_userPermissionContainer as $_key => $_val) {
            if (SWIFT_App::IsInstalled($_key)) {
                $_totalPermissionCount += count($_val);
            }
        }

        $_PermissionsTabObject = $this->UserInterface->AddTab($this->Language->Get('tabpermissions') . ' <font color=\'#8BB467\'>(' . $_totalPermissionCount . ')</font>', 'icon_permissions.gif', 'userpermissions');
        $_PermissionsTabObject->StartContainer('sguserpermissionscontainer');
        $_PermissionsTabObject->RowHTML('<tr><td>');
        $this->Controller->PermissionsRenderer->RenderPermissionsHTML($this->UserInterface, SWIFT_PermissionsRenderer::PERMISSIONS_USER, $_permissionValueContainer, $_PermissionsTabObject);
        $_PermissionsTabObject->RowHTML('</td><tr>');
        $_PermissionsTabObject->EndContainer();

        /*
         * ###############################################
         * END PERMISSIONS TAB
         * ###############################################
         */

        // Begin Hook: admin_usergroup_tabs
        unset($_hookCode);
        ($_hookCode = SWIFT_Hook::Execute('admin_usergroup_tabs')) ? eval($_hookCode) : false;
        // End Hook

        $this->UserInterface->End();

        return true;
    }

    /**
     * Render the User Group Grid
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function RenderGrid()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->Load->Library('UserInterface:UserInterfaceGrid', array('usergroupgrid'), true, false, 'base');

        if ($this->UserInterfaceGrid->GetMode() == SWIFT_UserInterfaceGrid::MODE_SEARCH) {
            $this->UserInterfaceGrid->SetSearchQuery('SELECT * FROM ' . TABLE_PREFIX . 'usergroups WHERE (' . $this->UserInterfaceGrid->BuildSQLSearch('title') . ')', 'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX . 'usergroups WHERE (' . $this->UserInterfaceGrid->BuildSQLSearch('title') . ')');
        }

        $this->UserInterfaceGrid->SetQuery('SELECT * FROM ' . TABLE_PREFIX . 'usergroups', 'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX . 'usergroups');

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('usergroupid', 'usergroupid', SWIFT_UserInterfaceGridField::TYPE_ID));

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('icon', '&nbsp;', SWIFT_UserInterfaceGridField::TYPE_CUSTOM, 16, SWIFT_UserInterfaceGridField::ALIGN_CENTER));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('title', $this->Language->Get('usergrouptitle'), SWIFT_UserInterfaceGridField::TYPE_DB, 0, SWIFT_UserInterfaceGridField::ALIGN_LEFT, SWIFT_UserInterfaceGridField::SORT_ASC), true);
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('grouptype', $this->Language->Get('usergrouptype'), SWIFT_UserInterfaceGridField::TYPE_DB, 160, SWIFT_UserInterfaceGridField::ALIGN_CENTER));

        $this->UserInterfaceGrid->SetRenderCallback(array($this, 'GridRender'));
        $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('delete'), 'fa-trash', array('Base\Admin\Controller_UserGroup', 'DeleteList'), $this->Language->Get('actionconfirm')));

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

        $_fieldContainer['icon'] = '<img src="' . SWIFT::Get('themepath') . 'images/icon_usergroup.gif" align="absmiddle" border="0" />';

        $_fieldContainer['grouptype'] = SWIFT_UserGroup::GetGroupTypeLabel($_fieldContainer['grouptype']);

        $_userGroupURL = '/Base/UserGroup/Edit/' . (int)($_fieldContainer['usergroupid']);

        $_fieldContainer['title'] = IIF($_fieldContainer['ismaster'] == 1, '<i>') . '<a href="' . SWIFT::Get('basename') . $_userGroupURL . '" viewport="1" title="' . $_SWIFT->Language->Get('edit') . '">' . htmlspecialchars($_fieldContainer['title']) . '</a>' . IIF($_fieldContainer['ismaster'] == 1, '</i>');

        return $_fieldContainer;
    }
}

?>
