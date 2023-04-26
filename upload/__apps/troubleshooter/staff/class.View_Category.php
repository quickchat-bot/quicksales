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

namespace Troubleshooter\Staff;

use SWIFT;
use Base\Library\Help\SWIFT_Help;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel;
use Base\Library\UserInterface\SWIFT_UserInterfaceGrid;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridField;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridMassAction;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;
use Troubleshooter\Models\Category\SWIFT_TroubleshooterCategory;

/**
 * The Troubleshooter Category View
 *
 * @author Varun Shoor
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @property SWIFT_UserInterfaceGrid $UserInterfaceGrid
 */
class View_Category extends \SWIFT_View
{

    /**
     * Render the Troubleshooter Category Form
     *
     * @author Varun Shoor
     * @param int $_mode The Render Mode
     * @param SWIFT_TroubleshooterCategory $_SWIFT_TroubleshooterCategoryObject The SWIFT_TroubleshooterCategory Object Pointer (Only for EDIT Mode)
     * @return bool "true" on Success, "false" otherwise
     * @throws \SWIFT_Exception
     */
    public function Render($_mode, SWIFT_TroubleshooterCategory $_SWIFT_TroubleshooterCategoryObject = null)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            return false;
        }

        // Calculate the URL
        if ($_mode == SWIFT_UserInterface::MODE_EDIT)
        {
            assert($_SWIFT_TroubleshooterCategoryObject !== null);
            $this->UserInterface->Start(get_short_class($this),'/Troubleshooter/Category/EditSubmit/' . $_SWIFT_TroubleshooterCategoryObject->GetTroubleshooterCategoryID(),
                    SWIFT_UserInterface::MODE_EDIT, true);
        } else {
            $this->UserInterface->Start(get_short_class($this),'/Troubleshooter/Category/InsertSubmit', SWIFT_UserInterface::MODE_INSERT, true);
        }

        $_categoryTitle = '';
        $_categoryType = SWIFT_TroubleshooterCategory::TYPE_GLOBAL;
        $_displayOrder = SWIFT_TroubleshooterCategory::GetLastDisplayOrder();
        $_userVisibilityCustom = $_staffVisibilityCustom = false;
        $_userGroupIDList = $_staffGroupIDList = array();

        $_categoryDescription = '';

        if ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT_TroubleshooterCategoryObject !== null)
        {
            if ((isset($_POST['_isDialog']) && $_POST['_isDialog'] == 1) || $this->UserInterface->IsAjax() == false)
            {
                $this->UserInterface->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle');
            }

            $this->UserInterface->Toolbar->AddButton($this->Language->Get('delete'), 'fa-trash', '/Troubleshooter/Category/Delete/' . $_SWIFT_TroubleshooterCategoryObject->GetTroubleshooterCategoryID(), SWIFT_UserInterfaceToolbar::LINK_CONFIRM, '', '', false);
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('troubleshootercategory'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

            $_categoryTitle = $_SWIFT_TroubleshooterCategoryObject->GetProperty('title');
            $_categoryDescription = $_SWIFT_TroubleshooterCategoryObject->GetProperty('description');

            $_categoryType = (int) ($_SWIFT_TroubleshooterCategoryObject->GetProperty('categorytype'));

            $_displayOrder = (int) ($_SWIFT_TroubleshooterCategoryObject->GetProperty('displayorder'));
            $_userVisibilityCustom = (int) ($_SWIFT_TroubleshooterCategoryObject->GetProperty('uservisibilitycustom'));
            $_staffVisibilityCustom = (int) ($_SWIFT_TroubleshooterCategoryObject->GetProperty('staffvisibilitycustom'));

            $_userGroupIDList = $_SWIFT_TroubleshooterCategoryObject->GetLinkedUserGroupIDList();
            $_staffGroupIDList = $_SWIFT_TroubleshooterCategoryObject->GetLinkedStaffGroupIDList();
        } else {
            if ((isset($_POST['_isDialog']) && $_POST['_isDialog'] == 1) || $this->UserInterface->IsAjax() == false)
            {
                $this->UserInterface->Toolbar->AddButton($this->Language->Get('insert'), 'fa-plus-circle');
            }

            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('troubleshootercategory'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);
        }


        /*
         * ###############################################
         * BEGIN GENERAL TAB
         * ###############################################
         */
        $_GeneralTabObject = $this->UserInterface->AddTab($this->Language->Get('tabgeneral'), 'icon_form.gif', 'general', true);

        $_GeneralTabObject->Text('title', $this->Language->Get('categorytitle'), $this->Language->Get('desc_categorytitle'), $_categoryTitle);

        $_radioContainer = array();
        $_index = 0;
        $_radioContainer[$_index]['title'] = $this->Language->Get('global');
        $_radioContainer[$_index]['value'] = SWIFT_TroubleshooterCategory::TYPE_GLOBAL;
        if ($_categoryType == SWIFT_TroubleshooterCategory::TYPE_GLOBAL)
        {
            $_radioContainer[$_index]['checked'] = true;
        }
        $_index++;

        $_radioContainer[$_index]['title'] = $this->Language->Get('public');
        $_radioContainer[$_index]['value'] = SWIFT_TroubleshooterCategory::TYPE_PUBLIC;
        if ($_mode == SWIFT_UserInterface::MODE_EDIT && $_categoryType == SWIFT_TroubleshooterCategory::TYPE_PUBLIC)
        {
            $_radioContainer[$_index]['checked'] = true;
        }
        $_index++;

        $_radioContainer[$_index]['title'] = $this->Language->Get('private');
        $_radioContainer[$_index]['value'] = SWIFT_TroubleshooterCategory::TYPE_PRIVATE;
        if ($_mode == SWIFT_UserInterface::MODE_EDIT && $_categoryType == SWIFT_TroubleshooterCategory::TYPE_PRIVATE)
        {
            $_radioContainer[$_index]['checked'] = true;
        }

        $_GeneralTabObject->Radio('categorytype', $this->Language->Get('categorytype'), $this->Language->Get('desc_categorytype'), $_radioContainer, true, 'HandleTroubleshooterCategoryType();');

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

        $_OptionsTabObject->Number('displayorder', $this->Language->Get('displayorder'), $this->Language->Get('desc_displayorder'),
            (string)$_displayOrder);

        $_OptionsTabObject->Title($this->Language->Get('description'), 'icon_doublearrows.gif');
        $_OptionsTabObject->TextArea('description', '', '', $_categoryDescription, 30, 15);

        /*
         * ###############################################
         * END OPTIONS TAB
         * ###############################################
         */



        /*
         * ###############################################
         * BEGIN PERMISSIONS (USERS) TAB
         * ###############################################
         */

        $_PermissionTabObject = $this->UserInterface->AddTab($this->Language->Get('tabpermissionsuser'), 'icon_settings2.gif', 'permissionsuser');
        $_PermissionTabObject->Overflow(450);

        $_PermissionTabObject->YesNo('uservisibilitycustom', $this->Language->Get('uservisibilitycustom'),
                $this->Language->Get('desc_uservisibilitycustom'), (bool)$_userVisibilityCustom);

        $_PermissionTabObject->Title($this->Language->Get('usergroups'), 'doublearrows.gif');

        $_index = 0;
        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "usergroups ORDER BY title ASC");
        while ($this->Database->NextRecord())
        {
            $_isSelected = false;

            if ($_mode == SWIFT_UserInterface::MODE_INSERT && !$_userGroupIDList)
            {
                $_isSelected = true;
            } else if (_is_array($_userGroupIDList)) {
                if (in_array($this->Database->Record['usergroupid'], $_userGroupIDList))
                {
                    $_isSelected = true;
                }
            }

            $_PermissionTabObject->YesNo('usergroupidlist[' . (int) ($this->Database->Record['usergroupid']) . ']', htmlspecialchars($this->Database->Record['title']), '', $_isSelected);

            $_index++;
        }

        /*
         * ###############################################
         * END PERMISSIONS (USERS) TAB
         * ###############################################
         */



        /*
         * ###############################################
         * BEGIN PERMISSIONS (STAFF) TAB
         * ###############################################
         */

        $_PermissionTabObject = $this->UserInterface->AddTab($this->Language->Get('tabpermissionsstaff'), 'icon_settings2.gif', 'permissionsstaff');
        $_PermissionTabObject->Overflow(450);

        $_PermissionTabObject->YesNo('staffvisibilitycustom', $this->Language->Get('staffvisibilitycustom'), $this->Language->Get('desc_staffvisibilitycustom'), (bool)$_staffVisibilityCustom);
        $_PermissionTabObject->Title($this->Language->Get('staffteams'), 'doublearrows.gif');

        $_index = 0;
        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "staffgroup ORDER BY title ASC");
        while ($this->Database->NextRecord())
        {
            $_isSelected = false;

            if ($_mode == SWIFT_UserInterface::MODE_INSERT && !$_staffGroupIDList)
            {
                $_isSelected = true;
            } else if (_is_array($_staffGroupIDList)) {
                if (in_array($this->Database->Record['staffgroupid'], $_staffGroupIDList))
                {
                    $_isSelected = true;
                }
            }

            $_PermissionTabObject->YesNo('staffgroupidlist[' . (int) ($this->Database->Record['staffgroupid']) . ']', htmlspecialchars($this->Database->Record['title']), '', $_isSelected);

            $_index++;
        }

        /*
         * ###############################################
         * END PERMISSIONS TAB
         * ###############################################
         */

        $_outputHTML = '<script type="text/javascript">';
        $_outputHTML .= 'QueueFunction(function(){';
        $_outputHTML .= 'HandleTroubleshooterCategoryType();';
        $_outputHTML .= '});</script>';
        $this->UserInterface->AppendHTML($_outputHTML);

        $this->UserInterface->End();


        return true;
    }

    /**
     * Render the Troubleshooter Category Grid
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws \SWIFT_Exception
     */
    public function RenderGrid()
    {
        if (!$this->GetIsClassLoaded())
        {
            return false;
        }

        $this->Load->Library('UserInterface:UserInterfaceGrid', array('troubleshootercategorygrid'), true, false, 'base');

        if ($this->UserInterfaceGrid->GetMode() == SWIFT_UserInterfaceGrid::MODE_SEARCH)
        {
            $this->UserInterfaceGrid->SetSearchQuery(
            'SELECT * FROM ' . TABLE_PREFIX . 'troubleshootercategories
                WHERE (' . $this->UserInterfaceGrid->BuildSQLSearch('title') . ')',

            'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX . 'troubleshootercategories
                WHERE (' . $this->UserInterfaceGrid->BuildSQLSearch('title') . ')');
        }

        $this->UserInterfaceGrid->SetQuery('SELECT * FROM ' . TABLE_PREFIX . 'troubleshootercategories',
                'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX . 'troubleshootercategories');

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('troubleshootercategoryid', 'troubleshootercategoryid', SWIFT_UserInterfaceGridField::TYPE_ID));

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('icon', '&nbsp;', SWIFT_UserInterfaceGridField::TYPE_CUSTOM, 16, SWIFT_UserInterfaceGridField::ALIGN_CENTER));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('title', $this->Language->Get('categorytitle'),SWIFT_UserInterfaceGridField::TYPE_DB, 0,
                SWIFT_UserInterfaceGridField::ALIGN_LEFT, SWIFT_UserInterfaceGridField::SORT_ASC), true);
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('categorytype', $this->Language->Get('categorytype'),SWIFT_UserInterfaceGridField::TYPE_DB, 120,
                SWIFT_UserInterfaceGridField::ALIGN_LEFT));

        $this->UserInterfaceGrid->SetRenderCallback(array($this, 'GridRender'));
        $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('delete'), 'fa-trash',
                array('\Troubleshooter\Staff\Controller_Category', 'DeleteList'), $this->Language->Get('actionconfirm')));

        $this->UserInterfaceGrid->SetNewLink("UICreateWindow('" . SWIFT::Get('basename') . "/Troubleshooter/Category/Insert', 'insertcategory', '" . $this->Language->Get('wininsertcategory') . "', '" . $this->Language->Get('loadingwindow') . "', 720, 635, true, this);");

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
     * @throws \SWIFT_Exception
     */
    public static function GridRender($_fieldContainer)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_icon = 'fa-folder';
        if ($_fieldContainer['categorytype'] == SWIFT_TroubleshooterCategory::TYPE_PRIVATE)
        {
            $_icon = 'fa-folder-o';
        }

        $_fieldContainer['icon'] = '<i class="fa '. $_icon .'" aria-hidden="true"></i>';

        $_fieldContainer['title'] = '<a href="' . SWIFT::Get('basename') . '/Troubleshooter/Category/Edit/' . (int) ($_fieldContainer['troubleshootercategoryid']) . '" onclick="' . "javascript: return UICreateWindowExtended(event, '" . SWIFT::Get('basename') . '/Troubleshooter/Category/Edit/' . (int) ($_fieldContainer['troubleshootercategoryid']) . "', 'editcategory', '" .
        sprintf($_SWIFT->Language->Get('wineditcategory'), htmlspecialchars($_fieldContainer['title'])) . "', '" .
                $_SWIFT->Language->Get('loadingwindow') . "', 720, 635, true, this);" . '" title="' . $_SWIFT->Language->Get('edit') .
                '">' . htmlspecialchars($_fieldContainer['title']) . '</a>';

        $_fieldContainer['categorytype'] = SWIFT_TroubleshooterCategory::GetCategoryTypeLabel($_fieldContainer['categorytype']);

        return $_fieldContainer;
    }

    /**
     * Render the View All Page
     *
     * @author Varun Shoor
     * @param array $_troubleshooterCategoryContainer The Troubleshooter Category Container
     * @return bool "true" on Success, "false" otherwise
     * @throws \SWIFT_Exception
     */
    public function RenderViewAll($_troubleshooterCategoryContainer)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            return false;
        }

        $this->UserInterface->Start('viewalltr', '/Troubleshooter/Category/ViewAll', SWIFT_UserInterface::MODE_INSERT, false);


        /*
         * ###############################################
         * BEGIN VIEW ALL TAB
         * ###############################################
         */
        $_ViewAllTabObject = $this->UserInterface->AddTab($this->Language->Get('tabviewall'), 'icon_troubleshooter.gif', 'viewall', true);

        $_renderHTML = '<div class="tabdatacontainer">';
        if (!_is_array($_troubleshooterCategoryContainer))
        {
            $_renderHTML .= '<div class="dashboardmsg">' . $this->Language->Get('noinfoinview') . '</div>';
        } else {
            $_renderHTML .= $this->Language->Get('troubleshooterdesc') . '<br /><br />';

            foreach ($_troubleshooterCategoryContainer as $_troubleshooterCategoryID => $_troubleshooterCategory)
            {
                $_renderHTML .= '<div class="troubleshootercategorycontainer" onclick="javascript: loadViewportData(\'/Troubleshooter/Step/ViewSteps/' . $_troubleshooterCategoryID . '\');">';
                    $_renderHTML .= '<div class="troubleshootercategory">';
                        $_renderHTML .= '<div class="troubleshootercategorytitle"><a class="bluelink" href="' . SWIFT::Get('basename') . '/Troubleshooter/Step/ViewSteps/' . $_troubleshooterCategoryID . '" viewport="1">' . htmlspecialchars($_troubleshooterCategory['title']) . '</a>' . IIF($this->Settings->Get('tr_displayviews') == '1' && $_troubleshooterCategory['views'] > 0, sprintf($this->Language->Get('trcategoryviews'), $_troubleshooterCategory['views'])) . '</div>';
                            $_renderHTML .= '<div class="troubleshootercategorydesc">' . $_troubleshooterCategory['description'] . '</div>';
                        $_renderHTML .= '</div>';
                    $_renderHTML .= '</div>';
                $_renderHTML .= '<div class="troubleshootercategoryfooter"></div>';
            }
        }
        $_renderHTML .= '</div>';

        $_ViewAllTabObject->RowHTML('<tr><td>' . $_renderHTML . '</td></tr>');

        /*
         * ###############################################
         * END VIEW ALL TAB
         * ###############################################
         */

        $this->UserInterface->End();

        return true;
    }
}
