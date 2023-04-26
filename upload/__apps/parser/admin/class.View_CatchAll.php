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
 * @copyright      Copyright (c) 2001-2012, QuickSupport
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
use Parser\Models\CatchAll\SWIFT_CatchAllRule;
use SWIFT_Date;
use SWIFT_Exception;
use SWIFT_View;

/**
 * The Parser Catch-All Rule View
 *
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @author Varun Shoor
 */
class View_CatchAll extends SWIFT_View
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
     * Render the Parser Catch-All Form
     *
     * @author Varun Shoor
     *
     * @param int    $_mode                     The Render Mode
     * @param SWIFT_CatchAllRule $_SWIFT_CatchAllRuleObject The Parser\Models\CatchAll\SWIFT_CatchAllRule Object Pointer (Only for EDIT Mode)
     *
     * @return bool "true" on Success, "false" otherwise
     */
    public function Render($_mode, SWIFT_CatchAllRule $_SWIFT_CatchAllRuleObject = null)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $_catchAllCache = $this->Cache->Get('parsercatchallcache');

        // Calculate the URL
        if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            $this->UserInterface->Start(get_short_class($this), '/Parser/CatchAll/EditSubmit/' . $_SWIFT_CatchAllRuleObject->GetCatchAllRuleID(),
                SWIFT_UserInterface::MODE_EDIT, true);
        } else {
            $this->UserInterface->Start(get_short_class($this), '/Parser/CatchAll/InsertSubmit', SWIFT_UserInterface::MODE_INSERT, false);
        }

        $_catchAllRuleTitle = '';
        $_catchAllRuleExpression = '';
        $_catchAllSortOrder = count($_catchAllCache) + 1;
        $_catchAllEmailQueueID = false;

        if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            if ((isset($_POST['_isDialog']) && $_POST['_isDialog'] == 1) || $this->UserInterface->IsAjax() == false) {
                $this->UserInterface->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle');
            }

            $this->UserInterface->Toolbar->AddButton($this->Language->Get('delete'), 'fa-trash', '/Parser/CatchAll/Delete/' .
                $_SWIFT_CatchAllRuleObject->GetCatchAllRuleID(), SWIFT_UserInterfaceToolbar::LINK_CONFIRM, '', '', false);
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('parsercatchall'),
                SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

            $_catchAllRuleTitle = $_SWIFT_CatchAllRuleObject->GetProperty('title');
            $_catchAllRuleExpression = $_SWIFT_CatchAllRuleObject->GetProperty('ruleexpr');
            $_catchAllSortOrder = (int)($_SWIFT_CatchAllRuleObject->GetProperty('sortorder'));
            $_catchAllEmailQueueID = (int)($_SWIFT_CatchAllRuleObject->GetProperty('emailqueueid'));
        } else {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('insert'), 'fa-plus-circle');
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('parsercatchall'),
                SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);
        }


        /*
         * ###############################################
         * BEGIN GENERAL TAB
         * ###############################################
         */
        $_GeneralTabObject = $this->UserInterface->AddTab($this->Language->Get('tabgeneral'), 'icon_form.gif', 'general', true);

        $_GeneralTabObject->Text('title', $this->Language->Get('ruletitle'), $this->Language->Get('desc_ruletitle'), $_catchAllRuleTitle);

        $_GeneralTabObject->Textarea('ruleexpr', $this->Language->Get('rregexp'), $this->Language->Get('desc_rregexp'), $_catchAllRuleExpression,
            '40', '5');

        $_GeneralTabObject->Number('sortorder', $this->Language->Get('sortorder'), $this->Language->Get('desc_sortorder'), $_catchAllSortOrder);

        $_optionsContainer = array();
        $_index = 0;
        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "emailqueues ORDER BY email ASC", 4);
        while ($this->Database->NextRecord(4)) {
            if (($_mode == SWIFT_UserInterface::MODE_INSERT && !$_catchAllEmailQueueID) ||
                $_catchAllEmailQueueID == $this->Database->Record4['emailqueueid']) {
                $_optionsContainer[$_index]['selected'] = true;
                $_catchAllEmailQueueID = $this->Database->Record4['emailqueueid'];
            }

            $_optionsContainer[$_index]['title'] = $this->Database->Record4['email'];
            $_optionsContainer[$_index]['value'] = $this->Database->Record4['emailqueueid'];
            $_index++;
        }

        if (!count($_optionsContainer)) {
            $_optionsContainer[$_index]['title'] = $this->Language->Get('noemailqueueadd');
            $_optionsContainer[$_index]['value'] = '';
        }

        $_GeneralTabObject->Select('emailqueueid', $this->Language->Get('emailqueue'), $this->Language->Get('desc_emailqueue'), $_optionsContainer);

        /*
         * ###############################################
         * END GENERAL TAB
         * ###############################################
         */

        $this->UserInterface->End();

        return true;
    }

    /**
     * Render the Parser Catch-All Grid
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

        $this->Load->Library('UserInterface:UserInterfaceGrid', array('catchallgrid'), true, false, 'base');

        if ($this->UserInterfaceGrid->GetMode() == SWIFT_UserInterfaceGrid::MODE_SEARCH) {
            $this->UserInterfaceGrid->SetSearchQuery('SELECT * FROM ' . TABLE_PREFIX . 'catchallrules WHERE (' .
                $this->UserInterfaceGrid->BuildSQLSearch('title') . ') OR (' . $this->UserInterfaceGrid->BuildSQLSearch('ruleexpr') . ')',
                'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX . 'catchallrules WHERE (' .
                $this->UserInterfaceGrid->BuildSQLSearch('title') . ') OR (' . $this->UserInterfaceGrid->BuildSQLSearch('ruleexpr') . ')');
        }

        $this->UserInterfaceGrid->SetQuery('SELECT * FROM ' . TABLE_PREFIX . 'catchallrules', 'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX .
            'catchallrules');

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('catchallruleid', 'catchallruleid',
            SWIFT_UserInterfaceGridField::TYPE_ID));

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('icon', '&nbsp;', SWIFT_UserInterfaceGridField::TYPE_CUSTOM, 16,
            SWIFT_UserInterfaceGridField::ALIGN_CENTER));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('title', $this->Language->Get('title'),
            SWIFT_UserInterfaceGridField::TYPE_DB, 200, SWIFT_UserInterfaceGridField::ALIGN_LEFT, SWIFT_UserInterfaceGridField::SORT_ASC), true);
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('ruleexpr', $this->Language->Get('rregexp'),
            SWIFT_UserInterfaceGridField::TYPE_DB, 0, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('emailqueueid', $this->Language->Get('emailqueue'),
            SWIFT_UserInterfaceGridField::TYPE_DB, 180, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('dateline', $this->Language->Get('date'),
            SWIFT_UserInterfaceGridField::TYPE_DB, 200, SWIFT_UserInterfaceGridField::ALIGN_LEFT));

        $this->UserInterfaceGrid->SetRenderCallback(array($this, 'GridRender'));
        $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('delete'), 'fa-trash',
            array('Parser\Admin\Controller_CatchAll', 'DeleteList'), $this->Language->Get('actionconfirm')));

        $this->UserInterfaceGrid->SetNewLinkViewport(SWIFT::Get('basename') . '/Parser/CatchAll/Insert');

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

        $_queueCache = $_SWIFT->Cache->Get('queuecache');

        $_fieldContainer['icon'] = '<img src="' . SWIFT::Get('themepath') . 'images/icon_catchallrule.gif" align="absmiddle" border="0" />';

        $_fieldContainer['title'] = '<a href="' . SWIFT::Get('basename') . '/Parser/CatchAll/Edit/' . (int)($_fieldContainer['catchallruleid']) . '" onclick="' . "javascript: return UICreateWindowExtended(event, '" . SWIFT::Get('basename') .
            "/Parser/CatchAll/Edit/" . (int)($_fieldContainer['catchallruleid']) . "', 'editcatchall', '" . $_SWIFT->Language->Get('wineditcatchall') .
            "', '" . $_SWIFT->Language->Get('loadingwindow') . "', 680, 620, true, this);" . '" title="' . $_SWIFT->Language->Get('edit') .
            '">' . htmlspecialchars($_fieldContainer['title']) . '</a>';

        $_fieldContainer['sortorder'] = (int)($_fieldContainer['sortorder']);
        $_fieldContainer['ruleexpr'] = htmlspecialchars($_fieldContainer['ruleexpr']);
        $_fieldContainer['dateline'] = SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_fieldContainer['dateline']);

        if (isset($_queueCache['list'][$_fieldContainer['emailqueueid']])) {
            $_fieldContainer['emailqueueid'] = htmlspecialchars($_queueCache['list'][$_fieldContainer['emailqueueid']]['email']);
        } else {
            $_fieldContainer['emailqueueid'] = $_SWIFT->Language->Get('na');
        }

        return $_fieldContainer;
    }
}

?>
