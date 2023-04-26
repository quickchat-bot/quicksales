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

namespace News\Staff;

use Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel;
use Base\Models\SearchStore\SWIFT_SearchStore;
use News\Models\Category\SWIFT_NewsCategory;
use SWIFT;
use SWIFT_Exception;
use Base\Library\Help\SWIFT_Help;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceGrid;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridField;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridMassAction;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;
use SWIFT_View;

/**
 * The News Category View
 *
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @author Varun Shoor
 */
class View_Category extends SWIFT_View
{
    /**
     * Render the News Category Form
     *
     * @author Varun Shoor
     * @param int $_mode The Render Mode
     * @param SWIFT_NewsCategory $_SWIFT_NewsCategoryObject The SWIFT_NewsCategory Object Pointer (Only for EDIT Mode)
     * @return bool "true" on Success, "false" otherwise
     */
    public function Render($_mode, SWIFT_NewsCategory $_SWIFT_NewsCategoryObject = null)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            return false;
        }

        // Calculate the URL
        if ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT_NewsCategoryObject !== null)
        {
            $this->UserInterface->Start(get_short_class($this),'/News/Category/EditSubmit/' . $_SWIFT_NewsCategoryObject->GetNewsCategoryID(),
                    SWIFT_UserInterface::MODE_EDIT, true);
        } else {
            $this->UserInterface->Start(get_short_class($this),'/News/Category/InsertSubmit', SWIFT_UserInterface::MODE_INSERT, true);
        }

        $_categoryTitle = '';
        $_visibilityType = SWIFT_PUBLIC;

        if ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT_NewsCategoryObject !== null)
        {
            if ((isset($_POST['_isDialog']) && $_POST['_isDialog'] == 1) || $this->UserInterface->IsAjax() == false)
            {
                $this->UserInterface->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle');
            }

            $this->UserInterface->Toolbar->AddButton($this->Language->Get('delete'), 'fa-trash', '/News/Category/Delete/' .
                    $_SWIFT_NewsCategoryObject->GetNewsCategoryID(), SWIFT_UserInterfaceToolbar::LINK_CONFIRM, '', '', false);
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('newscategory'),
                    SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

            $_categoryTitle = $_SWIFT_NewsCategoryObject->GetProperty('categorytitle');
            $_visibilityType = $_SWIFT_NewsCategoryObject->GetProperty('visibilitytype');
        } else {
            if ((isset($_POST['_isDialog']) && $_POST['_isDialog'] == 1) || $this->UserInterface->IsAjax() == false)
            {
                $this->UserInterface->Toolbar->AddButton($this->Language->Get('insert'), 'fa-plus-circle');
            }

            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('newscategory'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);
        }


        /*
         * ###############################################
         * BEGIN GENERAL TAB
         * ###############################################
         */

        $_GeneralTabObject = $this->UserInterface->AddTab($this->Language->Get('tabgeneral'), 'icon_form.gif', 'general', true);

        $_GeneralTabObject->Text('categorytitle', $this->Language->Get('categorytitle'), $this->Language->Get('desc_categorytitle'), $_categoryTitle);

        $_GeneralTabObject->PublicPrivate('visibilitytype', $this->Language->Get('visibilitytype'), $this->Language->Get('desc_visibilitytype'), IIF($_visibilityType==SWIFT_PUBLIC, true, false));

        /*
         * ###############################################
         * END GENERAL TAB
         * ###############################################
         */

        $this->UserInterface->End();

        return true;
    }

    /**
     * Render the News Category Grid
     * @author Varun Shoor
     * @param int $_searchStoreID (OPTIONAL) The Search Store ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public function RenderGrid($_searchStoreID = null)
    {
        if (!$this->GetIsClassLoaded())
        {
            return false;
        }

        $this->Load->Library('UserInterface:UserInterfaceGrid', array('newscategorygrid'), true, false, 'base');

        if ($this->UserInterfaceGrid->GetMode() == SWIFT_UserInterfaceGrid::MODE_SEARCH)
        {
            $this->UserInterfaceGrid->SetSearchQuery(
            'SELECT * FROM ' . TABLE_PREFIX . 'newscategories
                WHERE (' . $this->UserInterfaceGrid->BuildSQLSearch('categorytitle') . ')',

            'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX . 'newscategories
                WHERE (' . $this->UserInterfaceGrid->BuildSQLSearch('categorytitle') . ')');
        }

        $this->UserInterfaceGrid->SetSearchStoreOptions($_searchStoreID,
            "SELECT newscategories.* FROM " . TABLE_PREFIX . "newscategories AS newscategories
                    WHERE newscategories.newscategoryid IN (%s)",
            SWIFT_SearchStore::TYPE_NEWSCATEGORIES, '/News/Category/Manage/-1');

        $this->UserInterfaceGrid->SetQuery('SELECT * FROM ' . TABLE_PREFIX . 'newscategories',
                'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX . 'newscategories');

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('newscategoryid', 'newscategoryid', SWIFT_UserInterfaceGridField::TYPE_ID));

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('icon', '&nbsp;', SWIFT_UserInterfaceGridField::TYPE_CUSTOM, 16, SWIFT_UserInterfaceGridField::ALIGN_CENTER));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('categorytitle', $this->Language->Get('categorytitle'),SWIFT_UserInterfaceGridField::TYPE_DB, 0, SWIFT_UserInterfaceGridField::ALIGN_LEFT, SWIFT_UserInterfaceGridField::SORT_ASC), true);
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('visibilitytype', $this->Language->Get('visibilitytype'),SWIFT_UserInterfaceGridField::TYPE_DB, 120, SWIFT_UserInterfaceGridField::ALIGN_LEFT));

        $this->UserInterfaceGrid->SetRenderCallback(array($this, 'GridRender'));
        $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('delete'), 'fa-trash', array('\News\Staff\Controller_Category', 'DeleteList'), $this->Language->Get('actionconfirm')));

        $this->UserInterfaceGrid->SetNewLink("UICreateWindow('" . SWIFT::Get('basename') . "/News/Category/Insert', 'insertcategory', '" . $this->Language->Get('wininsertcategory') . "', '" . $this->Language->Get('loadingwindow') . "', 550, 350, true, this);");

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

        $_icon = 'fa-folder';
        if ($_fieldContainer['visibilitytype'] == SWIFT_PRIVATE)
        {
            $_icon = 'fa-folder-o';
        }

        $_fieldContainer['icon'] = '<i class="fa '. $_icon .'" aria-hidden="true"></i>';

        $_fieldContainer['categorytitle'] = '<a href="' . SWIFT::Get('basename') . '/News/Category/Edit/' . (int) ($_fieldContainer['newscategoryid']) . '" onclick="' . "javascript: return UICreateWindowExtended(event, '" . SWIFT::Get('basename') . '/News/Category/Edit/' . (int) ($_fieldContainer['newscategoryid']) . "', 'editcategory', '" .
        sprintf($_SWIFT->Language->Get('wineditcategory'), htmlspecialchars($_fieldContainer['categorytitle'])) . "', '" .
                $_SWIFT->Language->Get('loadingwindow') . "', 550, 350, true, this);" . '" title="' . $_SWIFT->Language->Get('edit') .
                '">' . htmlspecialchars($_fieldContainer['categorytitle']) . '</a>';

        $_fieldContainer['visibilitytype'] = IIF($_fieldContainer['visibilitytype'] == SWIFT_PUBLIC, $_SWIFT->Language->Get('public'), $_SWIFT->Language->Get('private'));

        return $_fieldContainer;
    }
}
?>
