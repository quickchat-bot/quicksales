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

namespace Tickets\Admin;

use Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel;
use SWIFT;
use SWIFT_Exception;
use Base\Library\Help\SWIFT_Help;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceGrid;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridField;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridMassAction;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;
use SWIFT_View;
use Tickets\Models\Bayesian\SWIFT_BayesianCategory;

/**
 * The Bayesian Category View
 *
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @author Varun Shoor
 */
class View_BayesianCategory extends SWIFT_View
{
    /**
     * Render the Bayesian Category Form
     *
     * @author Varun Shoor
     * @param int $_mode The Render Mode
     * @param SWIFT_BayesianCategory $_SWIFT_BayesianCategoryObject The SWIFT_BayesianCategory Object Pointer (Only for EDIT Mode)
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public function Render($_mode, SWIFT_BayesianCategory $_SWIFT_BayesianCategoryObject = null)
    {
        if (!$this->GetIsClassLoaded())
        {
            return false;
        }

        // Calculate the URL
        if ($_mode == SWIFT_UserInterface::MODE_EDIT)
        {
            $this->UserInterface->Start(get_short_class($this),'/Tickets/BayesianCategory/EditSubmit/'. $_SWIFT_BayesianCategoryObject->GetBayesianCategoryID(), SWIFT_UserInterface::MODE_EDIT, true);
        } else {
            $this->UserInterface->Start(get_short_class($this),'/Tickets/BayesianCategory/InsertSubmit', SWIFT_UserInterface::MODE_INSERT, false);
        }

        $_categoryTitle = '';
        $_categoryWeight = 1;
        $_wordCount = 0;
        $_categoryIsMaster = false;

        if ($_mode == SWIFT_UserInterface::MODE_EDIT)
        {
            if ((isset($_POST['_isDialog']) && $_POST['_isDialog'] == 1) || $this->UserInterface->IsAjax() == false)
            {
                $this->UserInterface->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle');
            }

            $this->UserInterface->Toolbar->AddButton($this->Language->Get('delete'), 'fa-trash', '/Tickets/BayesianCategory/Delete/' . $_SWIFT_BayesianCategoryObject->GetBayesianCategoryID(), SWIFT_UserInterfaceToolbar::LINK_CONFIRM, '', '', false);
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('bayes'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

            $_categoryTitle = $_SWIFT_BayesianCategoryObject->GetProperty('category');
            $_categoryWeight = (int) ($_SWIFT_BayesianCategoryObject->GetProperty('categoryweight'));
            $_wordCount = (int) ($_SWIFT_BayesianCategoryObject->GetProperty('wordcount'));
            $_categoryIsMaster = (int) ($_SWIFT_BayesianCategoryObject->GetProperty('ismaster'));
        } else {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('insert'), 'fa-plus-circle');
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('bayes'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);
        }

        /*
         * ###############################################
         * BEGIN GENERAL TAB
         * ###############################################
         */
        $_GeneralTabObject = $this->UserInterface->AddTab($this->Language->Get('tabgeneral'), 'icon_form.gif', 'general', true);

        if ($_categoryIsMaster) {
            $_GeneralTabObject->DefaultDescriptionRow($this->Language->Get('bayescattitle'), $this->Language->Get('desc_bayescattitle'), $_categoryTitle);
            $_GeneralTabObject->Hidden('category', $_categoryTitle);
        } else {
            $_GeneralTabObject->Text('category', $this->Language->Get('bayescattitle'), $this->Language->Get('desc_bayescattitle'), $_categoryTitle);
        }
        $_GeneralTabObject->Number('categoryweight', $this->Language->Get('categoryweight'), $this->Language->Get('desc_categoryweight'), ($_categoryWeight));

        /*
         * ###############################################
         * END GENERAL TAB
         * ###############################################
         */

        $this->UserInterface->End();

        return true;
    }

    /**
     * Render the Ticket Bayesian Grid
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public function RenderGrid()
    {
        if (!$this->GetIsClassLoaded())
        {
            return false;
        }

        $this->Load->Library('UserInterface:UserInterfaceGrid', array('bayesiancategorygrid'), true, false, 'base');

        if ($this->UserInterfaceGrid->GetMode() == SWIFT_UserInterfaceGrid::MODE_SEARCH)
        {
            $this->UserInterfaceGrid->SetSearchQuery('SELECT * FROM ' . TABLE_PREFIX . 'bayescategories WHERE (' . $this->UserInterfaceGrid->BuildSQLSearch('category') . ')', 'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX . 'bayescategories WHERE (' . $this->UserInterfaceGrid->BuildSQLSearch('category') . ')');
        }

        $this->UserInterfaceGrid->SetQuery('SELECT * FROM ' . TABLE_PREFIX . 'bayescategories', 'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX . 'bayescategories');

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('bayescategoryid', 'bayescategoryid', SWIFT_UserInterfaceGridField::TYPE_ID));

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('icon', '&nbsp;', SWIFT_UserInterfaceGridField::TYPE_CUSTOM, 16, SWIFT_UserInterfaceGridField::ALIGN_CENTER));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('category', $this->Language->Get('bayescattitle'), SWIFT_UserInterfaceGridField::TYPE_DB, 0, SWIFT_UserInterfaceGridField::ALIGN_LEFT, SWIFT_UserInterfaceGridField::SORT_ASC), true);
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('ismaster', $this->Language->Get('ismaster'), SWIFT_UserInterfaceGridField::TYPE_DB, 140, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('wordcount', $this->Language->Get('wordcount'), SWIFT_UserInterfaceGridField::TYPE_DB, 130, SWIFT_UserInterfaceGridField::ALIGN_CENTER));

        $this->UserInterfaceGrid->SetRenderCallback(array($this, 'GridRender'));
        $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('delete'), 'fa-trash', array('Tickets\Admin\Controller_BayesianCategory', 'DeleteList'), $this->Language->Get('actionconfirm')));

        $this->UserInterfaceGrid->SetNewLinkViewport(SWIFT::Get('basename') . '/Tickets/BayesianCategory/Insert');

        $this->UserInterfaceGrid->Render();

        $this->UserInterfaceGrid->Display();

        return true;
    }

    /**
     * The Grid Rendering Function
     *
     * @author Varun Shoor
     * @param array $_fieldContainer The Field Record Value Container
     * @return array "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function GridRender($_fieldContainer)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_fieldContainer['category'] = '<a href="' . SWIFT::Get('basename') . '/Tickets/BayesianCategory/Edit/' . (int) ($_fieldContainer['bayescategoryid']) . '" onclick="' . "javascript: return UICreateWindowExtended(event, '" . SWIFT::Get('basename') . "/Tickets/BayesianCategory/Edit/" . (int) ($_fieldContainer['bayescategoryid']) . "', 'editbayescategory', '". sprintf($_SWIFT->Language->Get('winbayeseditcategory'), htmlspecialchars($_fieldContainer['category'])) . "', '" . $_SWIFT->Language->Get('loadingwindow') . "', 680, 390, true, this);" . '" title="' . $_SWIFT->Language->Get('edit') . '">' . htmlspecialchars($_fieldContainer['category']).'</a>';

        if ($_fieldContainer['ismaster'] == '0')
        {
            $_fieldContainer['icon'] = '<img src="' . SWIFT::Get('themepath') . 'images/icon_funnelblue.gif" border="0" align="absmiddle" />';
            $_fieldContainer['ismaster'] = $_SWIFT->Language->Get('no');
        } else {
            $_fieldContainer['icon'] = '<img src="' . SWIFT::Get('themepath') . 'images/icon_funnel.gif" border="0" align="absmiddle" />';
            $_fieldContainer['ismaster'] = $_SWIFT->Language->Get('yes');
        }

        $_fieldContainer['wordcount'] = number_format($_fieldContainer['wordcount'], 0);
        $_fieldContainer['categoryweight'] = (float) ($_fieldContainer['categoryweight']);

        return $_fieldContainer;
    }
}
