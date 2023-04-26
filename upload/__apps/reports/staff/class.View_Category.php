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

use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel;
use Base\Library\UserInterface\SWIFT_UserInterfaceGrid;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridField;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridMassAction;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;

/**
 * The Reports Category View
 *
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @author Varun Shoor
 */
class View_Category extends SWIFT_View
{
    /**
     * Render the Report Category Form
     *
     * @author Varun Shoor
     * @param int $_mode The Render Mode
     * @param SWIFT_ReportCategory $_SWIFT_ReportCategoryObject The SWIFT_ReportCategory Object Pointer (Only for EDIT
     *     Mode)
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public function Render($_mode, SWIFT_ReportCategory $_SWIFT_ReportCategoryObject = null)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            return false;
        }

        // Calculate the URL
        if ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT_ReportCategoryObject !== null)
        {
            $this->UserInterface->Start(get_short_class($this),'/Reports/Category/EditSubmit/' . $_SWIFT_ReportCategoryObject->GetReportCategoryID(), SWIFT_UserInterface::MODE_EDIT, true);
        } else {
            $this->UserInterface->Start(get_short_class($this),'/Reports/Category/InsertSubmit', SWIFT_UserInterface::MODE_INSERT, true);
        }

        $_categoryTitle = '';
        $_visibilityType = SWIFT_ReportCategory::VISIBLE_PUBLIC;

        if ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT_ReportCategoryObject !== null)
        {
            if ((isset($_POST['_isDialog']) && $_POST['_isDialog'] == 1) || $this->UserInterface->IsAjax() == false)
            {
                $this->UserInterface->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle');
            }

            $this->UserInterface->Toolbar->AddButton($this->Language->Get('delete'), 'fa-trash', '/Reports/Category/Delete/' . $_SWIFT_ReportCategoryObject->GetReportCategoryID(), SWIFT_UserInterfaceToolbar::LINK_CONFIRM, '', '', false);

            $_categoryTitle = $_SWIFT_ReportCategoryObject->GetProperty('title');
            $_visibilityType = $_SWIFT_ReportCategoryObject->GetProperty('visibilitytype');
        } else {
            if (isset($_POST['_isDialog']) && $_POST['_isDialog'] == 1)
            {
                $this->UserInterface->Toolbar->AddButton($this->Language->Get('insert'), 'fa-plus-circle');
            }

        }

        $_bottomLeftPanel = '<div id="rcperm"><i class="fa fa-lock" aria-hidden="true"></i><span id="rcperm_text">' . SWIFT_ReportCategory::GetVisibilityLabel($_visibilityType) . '</span> <img src="' . SWIFT::Get('themepathimages') . 'menudrop_grey.svg" border="0" /></div>';


        $this->UserInterface->SetDialogBottomLeftPanel($_bottomLeftPanel);


        /*
         * ###############################################
         * BEGIN GENERAL TAB
         * ###############################################
         */

        $_GeneralTabObject = $this->UserInterface->AddTab($this->Language->Get('tabgeneral'), 'icon_form.gif', 'general', true);

        $_GeneralTabObject->Text('title', $this->Language->Get('categorytitle'), $this->Language->Get('desc_categorytitle'), $_categoryTitle);

        $_GeneralTabObject->Hidden('visibilitytype', $_visibilityType);

        /*
         * ###############################################
         * END GENERAL TAB
         * ###############################################
         */

        $this->UserInterface->AppendHTML('<ul class="swiftpopup" id="popup_rcperm">
        <li onclick="$(\'#View_Category_visibilitytype\').val(\'' . SWIFT_ReportCategory::VISIBLE_PUBLIC . '\'); $(\'#rcperm_text\').html(\'' . SWIFT_ReportCategory::GetVisibilityLabel(SWIFT_ReportCategory::VISIBLE_PUBLIC) . '\'); SWIFT_PopupDestroyAll(\'rcperm\');"><a href="javascript: void(0);">' . $this->Language->Get('visible_public') . '</a></li>
        <li onclick="$(\'#View_Category_visibilitytype\').val(\'' . SWIFT_ReportCategory::VISIBLE_TEAM . '\'); $(\'#rcperm_text\').html(\'' . SWIFT_ReportCategory::GetVisibilityLabel(SWIFT_ReportCategory::VISIBLE_TEAM) . '\'); SWIFT_PopupDestroyAll(\'rcperm\');"><a href="javascript: void(0);">' . $this->Language->Get('visible_team') . '</a></li>
        <li class="separator"></li>
        <li onclick="$(\'#View_Category_visibilitytype\').val(\'' . SWIFT_ReportCategory::VISIBLE_PRIVATE . '\'); $(\'#rcperm_text\').html(\'' . SWIFT_ReportCategory::GetVisibilityLabel(SWIFT_ReportCategory::VISIBLE_PRIVATE) . '\'); SWIFT_PopupDestroyAll(\'rcperm\');"><a href="javascript: void(0);">' . $this->Language->Get('visible_private') . '</a></li>
        </ul><script type="text/javascript">QueueFunction(function(){ $("#rcperm").SWIFT_Popup({align: "left", isdialog: true, width: 100}); });</script>');

        $this->UserInterface->End();

        return true;
    }

    /**
     * Render the Reports Category Grid
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function RenderGrid()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            return false;
        }

        $this->Load->Library('UserInterface:UserInterfaceGrid', array('reportcategorygrid'), true, false, 'base');

        if ($this->UserInterfaceGrid->GetMode() == SWIFT_UserInterfaceGrid::MODE_SEARCH)
        {
            $this->UserInterfaceGrid->SetSearchQuery(
            "SELECT reportcategories.* FROM " . TABLE_PREFIX . "reportcategories AS reportcategories
                LEFT JOIN " . TABLE_PREFIX . "staff AS staff ON (reportcategories.staffid = staff.staffid)
                LEFT JOIN " . TABLE_PREFIX . "staffgroup AS staffgroup ON (staff.staffgroupid = staffgroup.staffgroupid)
                WHERE (" . $this->UserInterfaceGrid->BuildSQLSearch('reportcategories.title') . ")
                    AND (reportcategories.visibilitytype = '" . SWIFT_ReportCategory::VISIBLE_PUBLIC . "'
                        OR (reportcategories.visibilitytype = '" . SWIFT_ReportCategory::VISIBLE_PRIVATE . "' AND reportcategories.staffid = '" . (int) ($_SWIFT->Staff->GetStaffID()) . "')
                        OR (reportcategories.visibilitytype = '" . SWIFT_ReportCategory::VISIBLE_TEAM . "' AND staffgroup.staffgroupid = '" . (int) ($_SWIFT->Staff->GetProperty('staffgroupid')) . "')
                        )",

            "SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "reportcategories AS reportcategories
                LEFT JOIN " . TABLE_PREFIX . "staff AS staff ON (reportcategories.staffid = staff.staffid)
                LEFT JOIN " . TABLE_PREFIX . "staffgroup AS staffgroup ON (staff.staffgroupid = staffgroup.staffgroupid)
                WHERE (" . $this->UserInterfaceGrid->BuildSQLSearch('reportcategories.title') . ")
                    AND (reportcategories.visibilitytype = '" . SWIFT_ReportCategory::VISIBLE_PUBLIC . "'
                        OR (reportcategories.visibilitytype = '" . SWIFT_ReportCategory::VISIBLE_PRIVATE . "' AND reportcategories.staffid = '" . (int) ($_SWIFT->Staff->GetStaffID()) . "')
                        OR (reportcategories.visibilitytype = '" . SWIFT_ReportCategory::VISIBLE_TEAM . "' AND staffgroup.staffgroupid = '" . (int) ($_SWIFT->Staff->GetProperty('staffgroupid')) . "')
                        )");
        }

        $this->UserInterfaceGrid->SetQuery("SELECT reportcategories.* FROM " . TABLE_PREFIX . "reportcategories AS reportcategories
                LEFT JOIN " . TABLE_PREFIX . "staff AS staff ON (reportcategories.staffid = staff.staffid)
                LEFT JOIN " . TABLE_PREFIX . "staffgroup AS staffgroup ON (staff.staffgroupid = staffgroup.staffgroupid)
                WHERE (reportcategories.visibilitytype = '" . SWIFT_ReportCategory::VISIBLE_PUBLIC . "'
                        OR (reportcategories.visibilitytype = '" . SWIFT_ReportCategory::VISIBLE_PRIVATE . "' AND reportcategories.staffid = '" . (int) ($_SWIFT->Staff->GetStaffID()) . "')
                        OR (reportcategories.visibilitytype = '" . SWIFT_ReportCategory::VISIBLE_TEAM . "' AND staffgroup.staffgroupid = '" . (int) ($_SWIFT->Staff->GetProperty('staffgroupid')) . "')
                        )",
            "SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "reportcategories AS reportcategories
                LEFT JOIN " . TABLE_PREFIX . "staff AS staff ON (reportcategories.staffid = staff.staffid)
                LEFT JOIN " . TABLE_PREFIX . "staffgroup AS staffgroup ON (staff.staffgroupid = staffgroup.staffgroupid)
                WHERE (reportcategories.visibilitytype = '" . SWIFT_ReportCategory::VISIBLE_PUBLIC . "'
                        OR (reportcategories.visibilitytype = '" . SWIFT_ReportCategory::VISIBLE_PRIVATE . "' AND reportcategories.staffid = '" . (int) ($_SWIFT->Staff->GetStaffID()) . "')
                        OR (reportcategories.visibilitytype = '" . SWIFT_ReportCategory::VISIBLE_TEAM . "' AND staffgroup.staffgroupid = '" . (int) ($_SWIFT->Staff->GetProperty('staffgroupid')) . "')
                        )");

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('reportcategoryid', 'reportcategoryid', SWIFT_UserInterfaceGridField::TYPE_ID));

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('icon', '&nbsp;', SWIFT_UserInterfaceGridField::TYPE_CUSTOM, 16, SWIFT_UserInterfaceGridField::ALIGN_CENTER));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('reportcategories.title', $this->Language->Get('categorytitle'), SWIFT_UserInterfaceGridField::TYPE_DB, 0, SWIFT_UserInterfaceGridField::ALIGN_LEFT, SWIFT_UserInterfaceGridField::SORT_ASC), true);
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('reportcategories.visibilitytype', $this->Language->Get('visibilitytype'), SWIFT_UserInterfaceGridField::TYPE_DB, 120, SWIFT_UserInterfaceGridField::ALIGN_LEFT));

        $this->UserInterfaceGrid->SetRenderCallback(array($this, 'GridRender'));
        $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('delete'), 'fa-trash',
                array('Controller_Category', 'DeleteList'), $this->Language->Get('actionconfirm')));

        if ($_SWIFT->Staff->GetPermission('staff_rcaninsertcategory') != '0')
        {
            $this->UserInterfaceGrid->SetNewLink("UICreateWindow('" . SWIFT::Get('basename') . "/Reports/Category/Insert', 'insertcategory', '" . $this->Language->Get('wininsertcategory') . "', '" . $this->Language->Get('loadingwindow') . "', 400, 175, true, this);");
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

        $_icon = 'fa-folder-o';
        if ($_fieldContainer['visibilitytype'] == SWIFT_ReportCategory::VISIBLE_PRIVATE)
        {
            $_icon = 'fa-folder-o private-fa-icon';
        }

        $_fieldContainer['icon'] = '<i class="fa ' . $_icon . '" aria-hidden="true"></i>';

        $_fieldContainer['reportcategories.title'] = '<a href="' . SWIFT::Get('basename') . '/Reports/Category/Edit/' . (int) ($_fieldContainer['reportcategoryid']) . '" onclick="' . "javascript: return UICreateWindowExtended(event, '" . SWIFT::Get('basename') . '/Reports/Category/Edit/' . (int) ($_fieldContainer['reportcategoryid']) . "', 'editcategory', '" .
            sprintf($_SWIFT->Language->Get('wineditcategory'), htmlspecialchars(addslashes($_fieldContainer['title']))) . "', '" .
                $_SWIFT->Language->Get('loadingwindow') . "', 400, 225, true, this);" . '" title="' . $_SWIFT->Language->Get('edit') .
                '">' . htmlspecialchars($_fieldContainer['title']) . '</a>';

        $_fieldContainer['reportcategories.visibilitytype'] = SWIFT_ReportCategory::GetVisibilityLabel($_fieldContainer['visibilitytype']);

        return $_fieldContainer;
    }

}
?>
