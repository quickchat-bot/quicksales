<?php
/**
 * ###############################################
 *
 * SWIFT Framework
 * _______________________________________________
 *
 * @author         Varun Shoor
 *
 * @package        SWIFT
 * @copyright      Copyright (c) 2001-2012, Kayako
 * @license        http://www.kayako.com/license
 * @link           http://www.kayako.com
 *
 * ###############################################
 */

namespace Parser\Admin;

use Base\Library\Help\SWIFT_Help;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel;
use Base\Library\UserInterface\SWIFT_UserInterfaceGrid;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridField;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridMassAction;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;
use SWIFT;
use SWIFT_Exception;
use Parser\Models\Loop\SWIFT_LoopRule;
use SWIFT_View;

/**
 * The Loop Rule View Management Class
 *
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @author Varun Shoor
 */
class View_LoopRule extends SWIFT_View
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
     * Render the Parser Loop Rule Form
     *
     * @author Varun Shoor
     *
     * @param int    $_mode                 The Render Mode
     * @param SWIFT_LoopRule $_SWIFT_LoopRuleObject The Parser\Models\Loop\SWIFT_LoopRule Object Pointer (Only for EDIT Mode)
     *
     * @return bool "true" on Success, "false" otherwise
     */
    public function Render($_mode, SWIFT_LoopRule $_SWIFT_LoopRuleObject = null)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        // Calculate the URL
        if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            $this->UserInterface->Start(get_short_class($this), '/Parser/LoopRule/EditSubmit/' . $_SWIFT_LoopRuleObject->GetLoopRuleID(),
                SWIFT_UserInterface::MODE_EDIT, true);
        } else {
            $this->UserInterface->Start(get_short_class($this), '/Parser/LoopRule/InsertSubmit', SWIFT_UserInterface::MODE_INSERT, false);
        }

        $_loopRuleTitle = '';
        $_loopRuleLength = 600;
        $_loopRuleMaxHits = 2;
        $_loopRuleRestoreAfter = 600;

        if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            if ((isset($_POST['_isDialog']) && $_POST['_isDialog'] == 1) || $this->UserInterface->IsAjax() == false) {
                $this->UserInterface->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle');
            }

            $this->UserInterface->Toolbar->AddButton($this->Language->Get('delete'), 'fa-trash', '/Parser/LoopRule/Delete/' .
                $_SWIFT_LoopRuleObject->GetLoopRuleID(), SWIFT_UserInterfaceToolbar::LINK_CONFIRM, '', '', false);
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('parserlooprule'),
                SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

            $_loopRuleTitle = $_SWIFT_LoopRuleObject->GetProperty('title');
            $_loopRuleLength = (int)($_SWIFT_LoopRuleObject->GetProperty('length'));
            $_loopRuleMaxHits = (int)($_SWIFT_LoopRuleObject->GetProperty('maxhits'));
            $_loopRuleRestoreAfter = (int)($_SWIFT_LoopRuleObject->GetProperty('restoreafter'));
        } else {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('insert'), 'fa-plus-circle');
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('parserlooprule'),
                SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);
        }


        /*
         * ###############################################
         * BEGIN GENERAL TAB
         * ###############################################
         */
        $_GeneralTabObject = $this->UserInterface->AddTab($this->Language->Get('tabgeneral'), 'icon_form.gif', 'general', true);

        $_GeneralTabObject->Text('title', $this->Language->Get('thresholdruletitle'), $this->Language->Get('desc_thresholdruletitle'),
            $_loopRuleTitle);
        $_GeneralTabObject->Number('rulelength', $this->Language->Get('pr_newloopcontrolwatchlength_title'),
            $this->Language->Get('pr_newloopcontrolwatchlength_desc'), $_loopRuleLength);
        $_GeneralTabObject->Number('maxhits', $this->Language->Get('pr_newloopcontrolmaxcontacts_title'),
            $this->Language->Get('pr_newloopcontrolmaxcontacts_desc'), $_loopRuleMaxHits);
        $_GeneralTabObject->Number('restoreafter', $this->Language->Get('pr_newloopcontrolrestoreafter_title'),
            $this->Language->Get('pr_newloopcontrolrestoreafter_desc'), $_loopRuleRestoreAfter);

        /*
         * ###############################################
         * END GENERAL TAB
         * ###############################################
         */

        $this->UserInterface->End();

        return true;
    }

    /**
     * Render the Parser Loop Rule Grid
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RenderGrid()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->Load->Library('UserInterface:UserInterfaceGrid', array('parserlooprulegrid'), true, false, 'base');

        if ($this->UserInterfaceGrid->GetMode() == SWIFT_UserInterfaceGrid::MODE_SEARCH) {
            $this->UserInterfaceGrid->SetSearchQuery('SELECT * FROM ' . TABLE_PREFIX . 'parserlooprules WHERE (' .
                $this->UserInterfaceGrid->BuildSQLSearch('title') . ')', 'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX .
                'parserlooprules WHERE (' . $this->UserInterfaceGrid->BuildSQLSearch('title') . ')');
        }

        $this->UserInterfaceGrid->SetQuery('SELECT * FROM ' . TABLE_PREFIX . 'parserlooprules', 'SELECT COUNT(*) AS totalitems FROM ' .
            TABLE_PREFIX . 'parserlooprules');

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('parserloopruleid', 'parserloopruleid',
            SWIFT_UserInterfaceGridField::TYPE_ID));

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('icon', '&nbsp;', SWIFT_UserInterfaceGridField::TYPE_CUSTOM, 16,
            SWIFT_UserInterfaceGridField::ALIGN_CENTER));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('title', $this->Language->Get('pr_threshhold_grid_title'),
            SWIFT_UserInterfaceGridField::TYPE_DB, 0, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('length', $this->Language->Get('pr_threshhold_grid_timeframe_title'),
            SWIFT_UserInterfaceGridField::TYPE_DB, 180, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('maxhits', $this->Language->Get('pr_threshhold_grid_maxhits_title'),
            SWIFT_UserInterfaceGridField::TYPE_DB, 200, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('restoreafter',
            $this->Language->Get('pr_threshhold_grid_restoreafter_title'), SWIFT_UserInterfaceGridField::TYPE_DB, 180,
            SWIFT_UserInterfaceGridField::ALIGN_LEFT, SWIFT_UserInterfaceGridField::SORT_ASC), true);

        $this->UserInterfaceGrid->SetRenderCallback(array($this, 'GridRender'));
        $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('delete'), 'fa-trash',
            array('Parser\Admin\Controller_LoopRule', 'DeleteList'), $this->Language->Get('actionconfirm')));

        $this->UserInterfaceGrid->SetNewLinkViewport(SWIFT::Get('basename') . '/Parser/LoopRule/Insert');

        $this->UserInterfaceGrid->Render();

        $this->UserInterfaceGrid->Display();

        return true;
    }

    /**
     * The Grid Rendering Function
     *
     * @author Varun Shoor
     *
     * @param array $_fieldContainer The Field Record Value Container
     *
     * @return array
     */
    public static function GridRender($_fieldContainer)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_fieldContainer['length'] = (int)($_fieldContainer['length']) . ' ' . $_SWIFT->Language->Get('seconds');

        $_fieldContainer['restoreafter'] = (int)($_fieldContainer['restoreafter']) . ' ' . $_SWIFT->Language->Get('seconds');

        $_fieldContainer['icon'] = '<img src="' . SWIFT::Get('themepath') . 'images/icon_loopcutter.gif" align="absmiddle" border="0" />';

        $_fieldContainer['title'] = IIF($_fieldContainer['ismaster'] == '1', '<em>') . '<a href="' . SWIFT::Get('basename') . '/Parser/LoopRule/Edit/' . (int)($_fieldContainer['parserloopruleid']) . '" onclick="' .
            "javascript: return UICreateWindowExtended(event, '" . SWIFT::Get('basename') . "/Parser/LoopRule/Edit/" . (int)($_fieldContainer['parserloopruleid']) .
            "', 'editloopcutterrule', '" . $_SWIFT->Language->Get('wineditloopcutterrule') . "', '" . $_SWIFT->Language->Get('loadingwindow') .
            "', 900, 800, true, this);" . '" title="' . $_SWIFT->Language->Get('edit') . '">' . htmlspecialchars($_fieldContainer['title']) .
            '</a>' . IIF($_fieldContainer['ismaster'] == '1', '</em>');

        return $_fieldContainer;
    }
}

?>
