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

namespace LiveChat\Staff;

use Base\Library\Help\SWIFT_Help;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceGrid;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridField;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridMassAction;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;
use Base\Models\SearchStore\SWIFT_SearchStore;
use LiveChat\Library\Canned\SWIFT_CannedManager;
use LiveChat\Models\Canned\SWIFT_CannedCategory;
use SWIFT;
use SWIFT_Exception;
use SWIFT_View;

/**
 * The Canned Category View
 *
 * @author Varun Shoor
 */
class View_CannedCategory extends SWIFT_View
{
    /**
     * Render the Canned Category Form
     *
     * @author Varun Shoor
     * @param int $_mode The Render Mode
     * @param SWIFT_CannedCategory|null $_SWIFT_CannedCategoryObject The SWIFT_CannedCategory Object Pointer (Only for EDIT Mode)
     * @param bool $_selectedCannedCategoryIDArg
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public function Render($_mode, SWIFT_CannedCategory $_SWIFT_CannedCategoryObject = null, $_selectedCannedCategoryIDArg = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        // Calculate the URL
        if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            $this->UserInterface->Start(get_short_class($this), '/LiveChat/CannedCategory/EditSubmit/' . $_SWIFT_CannedCategoryObject->GetCannedCategoryID(), SWIFT_UserInterface::MODE_EDIT, true);
        } else {
            $this->UserInterface->Start(get_short_class($this), '/LiveChat/CannedCategory/InsertSubmit', SWIFT_UserInterface::MODE_INSERT, true);
        }

        $_cannedCategoryTitle = '';
        $_cannedCategoryType = true;
        $_selectedCannedCategoryID = 0;
        $_activeCannedCategoryID = false;

        if (!empty($_selectedCannedCategoryIDArg)) {
            $_selectedCannedCategoryID = (int)($_selectedCannedCategoryIDArg);
        }

        if ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT_CannedCategoryObject !== null) {
            if (isset($_POST['_isDialog']) && $_POST['_isDialog'] == 1) {
                $this->UserInterface->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle');
            }

            $this->UserInterface->Toolbar->AddButton($this->Language->Get('delete'), 'fa-trash', '/LiveChat/CannedCategory/Delete/' . $_SWIFT_CannedCategoryObject->GetCannedCategoryID(), SWIFT_UserInterfaceToolbar::LINK_CONFIRM, '', '', false);
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('chatcanned'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

            $_cannedCategoryTitle = $_SWIFT_CannedCategoryObject->GetProperty('title');
            $_cannedCategoryType = (int)($_SWIFT_CannedCategoryObject->GetProperty('categorytype'));

            $_selectedCannedCategoryID = (int)($_SWIFT_CannedCategoryObject->GetProperty('parentcategoryid'));

            $_activeCannedCategoryID = $_SWIFT_CannedCategoryObject->GetCannedCategoryID();
        } else {
            if (isset($_POST['_isDialog']) && $_POST['_isDialog'] == 1) {
                $this->UserInterface->Toolbar->AddButton($this->Language->Get('insert'), 'fa-plus-circle');
            }

            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('chatcanned'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);
        }


        /*
         * ###############################################
         * BEGIN GENERAL TAB
         * ###############################################
         */
        $_GeneralTabObject = $this->UserInterface->AddTab($this->Language->Get('tabgeneral'), 'icon_form.gif', 'general', true);

        $_GeneralTabObject->Text('title', $this->Language->Get('cannedcategorytitle'), $this->Language->Get('desc_cannedcategorytitle'), $_cannedCategoryTitle);

        $_GeneralTabObject->Select('parentcategoryid', $this->Language->Get('parentcategory'), $this->Language->Get('desc_parentcategory'), SWIFT_CannedManager::GetCannedCategoryOptions($_selectedCannedCategoryID, $_activeCannedCategoryID));

        $_GeneralTabObject->PublicPrivate('categorytype', $this->Language->Get('categorytype'), $this->Language->Get('desc_categorytype'), (int)($_cannedCategoryType));

        /*
         * ###############################################
         * END GENERAL TAB
         * ###############################################
         */

        $this->UserInterface->End();

        return true;
    }

    /**
     * Render the Canned Tabs
     *
     * @author Varun Shoor
     * @param bool $_isResponsesTabSelected (OPTIONAL) Whether the responses tab is selected by default
     * @param int $_searchStoreID (OPTIONAL) The optional search store id
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RenderTabs($_isResponsesTabSelected = false, $_searchStoreID = null)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_SWIFT = SWIFT::GetInstance();

        /**
         * BUG FIX: Simaranjit Singh
         *
         * SWIFT-2884: Sorting Canned responses redirects to Categories page.
         *
         * Comments: None
         */
        $_ResponsesTabObject = false;
        if (!isset($_POST['_searchQuery']) && !isset($_POST['_sortBy'])) {
            $this->UserInterface->Start();

            $this->UserInterface->Toolbar->AddButton($this->Language->Get('insertcategory'), 'fa-folder-o', "InsertCannedCategoryWindow(0);", SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, '', '', false);
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('insertresponse'), 'fa-commenting-o', 'InsertCannedResponseWindow(0);', SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, '', '', false);
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('chatcanned'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

            /*
             * ###############################################
             * BEGIN CATEGORIES TAB
             * ###############################################
             */
            $_CategoriesTabObject = $this->UserInterface->AddTab($this->Language->Get('tabcategories'), 'icon_folderyellow3.gif', 'categories', IIF($_isResponsesTabSelected == false, true, false));

            $_CategoriesTabObject->RowHTML('<tr><td align="left" valign="top">' . SWIFT_CannedManager::GetCannedCategoryTree(0) . '</td></tr>');

            /*
             * ###############################################
             * BEGIN RESPONSES TAB
             * ###############################################
             */
            $_ResponsesTabObject = $this->UserInterface->AddTab($this->Language->Get('tabresponses'), 'icon_form.gif', 'responses', IIF($_isResponsesTabSelected == true, true, false), false, 0);
            $_ResponsesTabObject->LoadToolbar();
        }

        $this->Load->Library('UserInterface:UserInterfaceGrid', array('responsegrid'), true, false, 'base');

        if ($this->UserInterfaceGrid->GetMode() == SWIFT_UserInterfaceGrid::MODE_SEARCH) {
            $this->UserInterfaceGrid->SetSearchQuery('SELECT cannedresponses.*, cannedcategories.title AS categorytitle FROM ' . TABLE_PREFIX . 'cannedresponses AS cannedresponses
                LEFT JOIN ' . TABLE_PREFIX . 'cannedresponsedata AS cannedresponsedata ON (cannedresponses.cannedresponseid = cannedresponsedata.cannedresponseid)
                LEFT JOIN ' . TABLE_PREFIX . 'cannedcategories AS cannedcategories ON (cannedresponses.cannedcategoryid = cannedcategories.cannedcategoryid)
                WHERE (' . $this->UserInterfaceGrid->BuildSQLSearch('cannedresponses.title') . ' OR ' . $this->UserInterfaceGrid->BuildSQLSearch('cannedresponsedata.contents') . ')
                AND (cannedresponses.cannedcategoryid = \'0\' OR cannedcategories.categorytype = \'' . SWIFT_CannedCategory::TYPE_PUBLIC . '\' OR (cannedcategories.categorytype = \'' . SWIFT_CannedCategory::TYPE_PRIVATE . '\' AND cannedcategories.staffid = \'' . $_SWIFT->Staff->GetStaffID() . '\'))',

                'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX . 'cannedresponses AS cannedresponses
                LEFT JOIN ' . TABLE_PREFIX . 'cannedresponsedata AS cannedresponsedata ON (cannedresponses.cannedresponseid = cannedresponsedata.cannedresponseid)
                LEFT JOIN ' . TABLE_PREFIX . 'cannedcategories AS cannedcategories ON (cannedresponses.cannedcategoryid = cannedcategories.cannedcategoryid)
                WHERE (' . $this->UserInterfaceGrid->BuildSQLSearch('cannedresponses.title') . ' OR ' . $this->UserInterfaceGrid->BuildSQLSearch('cannedresponsedata.contents') . ')
                AND (cannedresponses.cannedcategoryid = \'0\' OR cannedcategories.categorytype = \'' . SWIFT_CannedCategory::TYPE_PUBLIC . '\' OR (cannedcategories.categorytype = \'' . SWIFT_CannedCategory::TYPE_PRIVATE . '\' AND cannedcategories.staffid = \'' . $_SWIFT->Staff->GetStaffID() . '\'))');
        }

        $this->UserInterfaceGrid->SetSearchStoreOptions($_searchStoreID, 'SELECT cannedresponses.*, cannedcategories.title AS categorytitle FROM ' . TABLE_PREFIX . 'cannedresponses AS cannedresponses
            LEFT JOIN ' . TABLE_PREFIX . 'cannedresponsedata AS cannedresponsedata ON (cannedresponses.cannedresponseid = cannedresponsedata.cannedresponseid)
            LEFT JOIN ' . TABLE_PREFIX . 'cannedcategories AS cannedcategories ON (cannedresponses.cannedcategoryid = cannedcategories.cannedcategoryid)
            WHERE cannedresponses.cannedresponseid IN (%s)', SWIFT_SearchStore::TYPE_CANNEDRESPONSE);

        $this->UserInterfaceGrid->SetQuery('SELECT cannedresponses.*, cannedcategories.title AS categorytitle FROM ' . TABLE_PREFIX . 'cannedresponses AS cannedresponses
            LEFT JOIN ' . TABLE_PREFIX . 'cannedresponsedata AS cannedresponsedata ON (cannedresponses.cannedresponseid = cannedresponsedata.cannedresponseid)
            LEFT JOIN ' . TABLE_PREFIX . 'cannedcategories AS cannedcategories ON (cannedresponses.cannedcategoryid = cannedcategories.cannedcategoryid)
            WHERE (cannedresponses.cannedcategoryid = \'0\' OR cannedcategories.categorytype = \'' . SWIFT_CannedCategory::TYPE_PUBLIC . '\' OR (cannedcategories.categorytype = \'' . SWIFT_CannedCategory::TYPE_PRIVATE . '\' AND cannedcategories.staffid = \'' . $_SWIFT->Staff->GetStaffID() . '\'))',

            'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX . 'cannedresponses AS cannedresponses
            LEFT JOIN ' . TABLE_PREFIX . 'cannedresponsedata AS cannedresponsedata ON (cannedresponses.cannedresponseid = cannedresponsedata.cannedresponseid)
            LEFT JOIN ' . TABLE_PREFIX . 'cannedcategories AS cannedcategories ON (cannedresponses.cannedcategoryid = cannedcategories.cannedcategoryid)
            WHERE (cannedresponses.cannedcategoryid = \'0\' OR cannedcategories.categorytype = \'' . SWIFT_CannedCategory::TYPE_PUBLIC . '\' OR (cannedcategories.categorytype = \'' . SWIFT_CannedCategory::TYPE_PRIVATE . '\' AND cannedcategories.staffid = \'' . $_SWIFT->Staff->GetStaffID() . '\'))');

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('cannedresponseid', 'cannedresponseid', SWIFT_UserInterfaceGridField::TYPE_ID));

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('icon', '&nbsp;', SWIFT_UserInterfaceGridField::TYPE_CUSTOM, 16, SWIFT_UserInterfaceGridField::ALIGN_CENTER));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('cannedresponses.title', $this->Language->Get('responsetitle'), SWIFT_UserInterfaceGridField::TYPE_DB, 0, SWIFT_UserInterfaceGridField::ALIGN_LEFT, SWIFT_UserInterfaceGridField::SORT_ASC), true);
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('cannedcategories.title', $this->Language->Get('cannedcategorytitle'), SWIFT_UserInterfaceGridField::TYPE_DB, 230, SWIFT_UserInterfaceGridField::ALIGN_LEFT));

        $this->UserInterfaceGrid->SetRenderCallback(array($this, 'GridRender'));
        $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('delete'), 'fa-trash', array('LiveChat\Staff\Controller_CannedCategory', 'DeleteResponseList'), $this->Language->Get('actionconfirm')));

        $this->UserInterfaceGrid->Render();

        /**
         * BUG FIX: Simaranjit Singh
         *
         * SWIFT-2884: Sorting Canned responses redirects to Categories page.
         *
         * Comments: None
         */
        if (!isset($_POST['_searchQuery']) && !isset($_POST['_sortBy'])) {
            $_ResponsesTabObject->RowHTML('<tr><td align="left" valign="top">' . $this->UserInterfaceGrid->GetRenderData() . '</td></tr>');

            $this->UserInterface->End();
        } else {
            $this->UserInterfaceGrid->Display();
        }

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

        $_icon = 'fa-commenting';

        $_fieldContainer['icon'] = '<i class="fa ' . $_icon . '" aria-hidden="true"></i>';

        $_fieldContainer['cannedresponses.title'] = '<a href="' . SWIFT::Get('basename') . '/LiveChat/CannedResponse/Edit/' . (int)($_fieldContainer['cannedresponseid']) . '" onclick="' . "javascript: return UICreateWindowExtended(event, '" . SWIFT::Get('basename') . "/LiveChat/CannedResponse/Edit/" . (int)($_fieldContainer['cannedresponseid']) . "', 'editcannedresopnse', '" . $_SWIFT->Language->Get('edit') . "', '" . $_SWIFT->Language->Get('loadingwindow') . "', 740, 770, true, this);" . '" title="' . $_SWIFT->Language->Get('edit') . '">' . htmlspecialchars($_fieldContainer['title']) . '</a>';

        $_categoryTitle = $_SWIFT->Language->Get('parentcategoryitem');
        if (isset($_fieldContainer['categorytitle']) && !empty($_fieldContainer['categorytitle'])) {
            $_categoryTitle = $_fieldContainer['categorytitle'];
        }

        $_fieldContainer['cannedcategories.title'] = htmlspecialchars($_categoryTitle);

        return $_fieldContainer;
    }
}
