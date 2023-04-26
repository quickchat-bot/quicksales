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
use Base\Library\Help\SWIFT_Help;
use Base\Models\User\SWIFT_UserGroupAssign;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceGrid;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridField;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridMassAction;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;
use SWIFT_View;
use Base\Models\Widget\SWIFT_Widget;

/**
 * The Widget View
 *
 * @property \Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel $UserInterface
 * @author Varun Shoor
 */
class View_Widget extends SWIFT_View
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
     * Render the Widget Form
     *
     * @author Varun Shoor
     * @param int $_mode The Render Mode
     * @param SWIFT_Widget $_SWIFT_WidgetObject The SWIFT_Widget Object Pointer (Only for EDIT Mode)
     * @return bool "true" on Success, "false" otherwise
     */
    public function Render($_mode, SWIFT_Widget $_SWIFT_WidgetObject = null)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        // Calculate the URL
        if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            $this->UserInterface->Start(get_short_class($this), '/Base/Widget/EditSubmit/' . $_SWIFT_WidgetObject->GetWidgetID(), SWIFT_UserInterface::MODE_EDIT, false, true);
        } else {
            $this->UserInterface->Start(get_short_class($this), '/Base/Widget/InsertSubmit', SWIFT_UserInterface::MODE_INSERT, false, true);
        }

        $_widgetTitle = '';
        $_widgetLink = '';
        $_widgetDefaultIcon = '';
        //$_widgetDefaultSmallIcon = '';
        $_widgetDisplayOrder = SWIFT_Widget::GetLastDisplayOrder();
        $_widgetDisplayInNavBar = true;
        $_widgetDisplayInIndex = true;
        $_widgetIsEnabled = true;
        $_widgetVisibility = SWIFT_Widget::VISIBLE_ALL;
        $_userGroupIDList = array();
        $_userVisibilityCustom = false;

        if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle');

            $this->UserInterface->Toolbar->AddButton($this->Language->Get('delete'), 'fa-trash', '/Base/Widget/Delete/' . $_SWIFT_WidgetObject->GetWidgetID(), SWIFT_UserInterfaceToolbar::LINK_CONFIRM, '', '', false);
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('widget'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

            $_userGroupIDList = SWIFT_UserGroupAssign::Retrieve($_SWIFT_WidgetObject->GetWidgetID(), SWIFT_UserGroupAssign::TYPE_WIDGET);

            $_widgetTitle = $_SWIFT_WidgetObject->GetProperty('defaulttitle');
            $_widgetLink = $_SWIFT_WidgetObject->GetProperty('widgetlink');
            $_widgetDefaultIcon = $_SWIFT_WidgetObject->GetProperty('defaulticon');
            //$_widgetDefaultSmallIcon = $_SWIFT_WidgetObject->GetProperty('defaultsmallicon');
            $_widgetDisplayOrder = (int)($_SWIFT_WidgetObject->GetProperty('displayorder'));
            $_widgetDisplayInNavBar = (int)($_SWIFT_WidgetObject->GetProperty('displayinnavbar'));
            $_widgetDisplayInIndex = (int)($_SWIFT_WidgetObject->GetProperty('displayinindex'));
            $_widgetIsEnabled = (int)($_SWIFT_WidgetObject->GetProperty('isenabled'));
            $_widgetVisibility = (int)($_SWIFT_WidgetObject->GetProperty('widgetvisibility'));
            $_userVisibilityCustom = (int)($_SWIFT_WidgetObject->GetProperty('uservisibilitycustom'));
        } else {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('insert'), 'fa-plus-circle');
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('widget'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);
        }


        /*
         * ###############################################
         * BEGIN GENERAL TAB
         * ###############################################
         */
        $_GeneralTabObject = $this->UserInterface->AddTab($this->Language->Get('tabgeneral'), 'icon_form.gif', 'general', true);

        $_GeneralTabObject->Text('defaulttitle', $this->Language->Get('widgettitle'), $this->Language->Get('desc_widgettitle'), $_widgetTitle);
        $_GeneralTabObject->Text('widgetlink', $this->Language->Get('widgetlink'), $this->Language->Get('desc_widgetlink'), $_widgetLink);
        $_GeneralTabObject->URLAndUpload('defaulticon', $this->Language->Get('defaulticon'), $this->Language->Get('desc_defaulticon'), $_widgetDefaultIcon);
        //$_GeneralTabObject->URLAndUpload('defaultsmallicon', $this->Language->Get('defaultsmallicon'), $this->Language->Get('desc_defaultsmallicon'), $_widgetDefaultSmallIcon);
        $_GeneralTabObject->Number('displayorder', $this->Language->Get('displayorder'), $this->Language->Get('desc_displayorder'), $_widgetDisplayOrder);
        $_GeneralTabObject->YesNo('isenabled', $this->Language->Get('isenabled'), $this->Language->Get('desc_isenabled'), $_widgetIsEnabled);

        /*
         * ###############################################
         * END GENERAL TAB
         * ###############################################
         */


        /*
         * ###############################################
         * BEGIN OPTIONS TAB
         * ###############################################
         */
        $_OptionsTabObject = $this->UserInterface->AddTab($this->Language->Get('taboptions'), 'icon_settings2.gif', 'options', false);

        $_OptionsTabObject->YesNo('displayinnavbar', $this->Language->Get('displayinnavbar'), $this->Language->Get('desc_displayinnavbar'), $_widgetDisplayInNavBar);
        $_OptionsTabObject->YesNo('displayinindex', $this->Language->Get('displayinindex'), $this->Language->Get('desc_displayinindex'), $_widgetDisplayInIndex);

        $_optionsContainer = array();
        $_optionsContainer[0]['title'] = $this->Language->Get('visibilityall');
        $_optionsContainer[0]['value'] = SWIFT_Widget::VISIBLE_ALL;
        $_optionsContainer[0]['selected'] = IIF($_widgetVisibility == SWIFT_Widget::VISIBLE_ALL, true, false);

        $_optionsContainer[1]['title'] = $this->Language->Get('visibilityloggedin');
        $_optionsContainer[1]['value'] = SWIFT_Widget::VISIBLE_LOGGEDIN;
        $_optionsContainer[1]['selected'] = IIF($_widgetVisibility == SWIFT_Widget::VISIBLE_LOGGEDIN, true, false);

        $_optionsContainer[2]['title'] = $this->Language->Get('visibilityguests');
        $_optionsContainer[2]['value'] = SWIFT_Widget::VISIBLE_GUESTS;
        $_optionsContainer[2]['selected'] = IIF($_widgetVisibility == SWIFT_Widget::VISIBLE_GUESTS, true, false);

        $_OptionsTabObject->Select('widgetvisibility', $this->Language->Get('widgetvisibility'), $this->Language->Get('desc_widgetvisibility'), $_optionsContainer);

        /*
         * ###############################################
         * END OPTIONS TAB
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

        $this->UserInterface->End();

        return true;
    }

    /**
     * Render the Widget Grid
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

        $this->Load->Library('UserInterface:UserInterfaceGrid', array('widgetgrid'), true, false, 'base');

        if ($this->UserInterfaceGrid->GetMode() == SWIFT_UserInterfaceGrid::MODE_SEARCH) {
            $this->UserInterfaceGrid->SetSearchQuery('SELECT * FROM ' . TABLE_PREFIX . 'widgets WHERE (' . $this->UserInterfaceGrid->BuildSQLSearch('defaulttitle') . ')', 'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX . 'widgets WHERE (' . $this->UserInterfaceGrid->BuildSQLSearch('defaulttitle') . ')');
        }

        $this->UserInterfaceGrid->SetQuery('SELECT * FROM ' . TABLE_PREFIX . 'widgets', 'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX . 'widgets');

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('widgetid', 'widgetid', SWIFT_UserInterfaceGridField::TYPE_ID));

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('icon', '&nbsp;', SWIFT_UserInterfaceGridField::TYPE_CUSTOM, 36, SWIFT_UserInterfaceGridField::ALIGN_CENTER));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('defaulttitle', $this->Language->Get('widgettitle'), SWIFT_UserInterfaceGridField::TYPE_DB, 0, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('displayorder', $this->Language->Get('displayorder'), SWIFT_UserInterfaceGridField::TYPE_DB, 120, SWIFT_UserInterfaceGridField::ALIGN_CENTER, SWIFT_UserInterfaceGridField::SORT_ASC), true);

        $this->UserInterfaceGrid->SetRenderCallback(array($this, 'GridRender'));
        $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('delete'), 'fa-trash', array('Base\Admin\Controller_Widget', 'DeleteList'), $this->Language->Get('actionconfirm')));
        $this->UserInterfaceGrid->SetNewLinkViewport(SWIFT::Get('basename') . '/Base/Widget/Insert');

        if ($_SWIFT->Staff->GetPermission('admin_canupdatewidget') != '0') {
            $this->UserInterfaceGrid->SetSortableCallback('displayorder', array('Base\Admin\Controller_Widget', 'SortList'));
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
     * @return array The Processed Field Container Array
     */
    public static function GridRender($_fieldContainer)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_fieldContainer['icon'] = '<img src="' . SWIFT_Widget::GetIcon($_fieldContainer['defaulticon']) . '" align="absmiddle" border="0" width="36" height="36"/>';

        $_fieldContainer['defaulttitle'] = '<a class="widgetlinks" href="' . SWIFT::Get('basename') . '/Base/Widget/Edit/' . (int)($_fieldContainer['widgetid']) . '" viewport="1" title="' . $_SWIFT->Language->Get('edit') . '">' . htmlspecialchars(SWIFT_Widget::GetLabel($_fieldContainer['defaulttitle'])) . '</a>';

        return $_fieldContainer;
    }
}

?>
