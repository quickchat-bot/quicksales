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

namespace LiveChat\Admin;

use Base\Library\Help\SWIFT_Help;
use Base\Library\Rules\SWIFT_Rules;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceGrid;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridField;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridMassAction;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;
use SWIFT;
use LiveChat\Models\Skill\SWIFT_ChatSkill;
use SWIFT_View;
use LiveChat\Models\Visitor\SWIFT_Visitor;
use LiveChat\Models\Group\SWIFT_VisitorGroup;
use LiveChat\Models\Rule\SWIFT_VisitorRule;

/**
 * The Rule View Management Class
 *
 * @author Varun Shoor
 */
class View_Rule extends SWIFT_View
{
    /**
     * Render the Visitor Rule Form
     *
     * @author Varun Shoor
     * @param int $_mode The Render Mode
     * @param SWIFT_VisitorRule $_SWIFT_VisitorRuleObject The SWIFT_VisitorRule Object Pointer (Only for EDIT Mode)
     * @return bool "true" on Success, "false" otherwise
     */
    public function Render($_mode, SWIFT_VisitorRule $_SWIFT_VisitorRuleObject = null)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->DispatchMenu();

        $_criteriaPointer = SWIFT_VisitorRule::GetCriteriaPointer();

        SWIFT_Visitor::DispatchJSVariable();
        SWIFT_ChatSkill::DispatchJSVariable();
        SWIFT_VisitorGroup::DispatchJSVariable();
        SWIFT_VisitorRule::ExtendCustomCriteria($_criteriaPointer);
        SWIFT_Rules::CriteriaPointerToJavaScriptArray($_criteriaPointer);

        // Calculate the URL
        if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            $this->UserInterface->Start(get_short_class($this), '/LiveChat/Rule/EditSubmit/' . $_SWIFT_VisitorRuleObject->GetVisitorRuleID(), SWIFT_UserInterface::MODE_EDIT, false);
        } else {
            $this->UserInterface->Start(get_short_class($this), '/LiveChat/Rule/InsertSubmit', SWIFT_UserInterface::MODE_INSERT, false);
        }

        $_GeneralTabObject = $this->UserInterface->AddTab($this->Language->Get('tabgeneral'), 'icon_form.gif', 'general', true);
        $_ActionsTabObject = $this->UserInterface->AddTab($this->Language->Get('tabactions'), 'icon_actions.gif', 'actions');
        $_ActionsTabObject->LoadToolbar('toolbarRuleAction');

        $_ruleMatchAll = true;
        $_ruleSortOrder = 1;
        $_ruleStopProcessing = true;
        $_ruleType = false;
        $_ruleTitle = '';
        if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle', '/LiveChat/Rule/Edit/' . $_SWIFT_VisitorRuleObject->GetVisitorRuleID());
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('delete'), 'fa-trash', '/LiveChat/Rule/Delete/' . $_SWIFT_VisitorRuleObject->GetVisitorRuleID(), SWIFT_UserInterfaceToolbar::LINK_CONFIRM, '', '', false);
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('chatrule'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

            $_ActionsTabObject->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle', '/LiveChat/Rule/Edit/' . $_SWIFT_VisitorRuleObject->GetVisitorRuleID());
            $_ActionsTabObject->Toolbar->AddButton($this->Language->Get('insertaction') . ' <img src="' . SWIFT::Get('themepath') . 'images/menudropgray.gif" align="absmiddle" border="0" />', 'fa-list-alt', 'UIDropDown(\'visitoractionmenu\', event, \'visitorRuleActionID\', \'toolbarRuleAction\');', SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, 'visitorRuleActionID', '', false);
            $_ActionsTabObject->Toolbar->AddButton($this->Language->Get('delete'), 'fa-trash', '/LiveChat/Rule/Delete/' . $_SWIFT_VisitorRuleObject->GetVisitorRuleID(), SWIFT_UserInterfaceToolbar::LINK_CONFIRM, '', '', false);
            $_ActionsTabObject->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('chatrule'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

            if ($_SWIFT_VisitorRuleObject->GetProperty('matchtype') == SWIFT_Rules::RULE_MATCHALL) {
                $_ruleMatchAll = true;
            } else {
                $_ruleMatchAll = false;
            }

            $_ruleSortOrder = (int)($_SWIFT_VisitorRuleObject->GetProperty('sortorder'));
            $_ruleType = (int)($_SWIFT_VisitorRuleObject->GetProperty('ruletype'));

            $_ruleStopProcessing = (int)($_SWIFT_VisitorRuleObject->GetProperty('stopprocessing'));

            $_ruleTitle = $_SWIFT_VisitorRuleObject->GetProperty('title');
        } else {
            $_sortOrderContainer = $this->Database->QueryFetch("SELECT sortorder FROM " . TABLE_PREFIX . "visitorrules ORDER BY sortorder DESC");
            if (isset($_sortOrderContainer['sortorder'])) {
                $_ruleSortOrder = (int)($_sortOrderContainer['sortorder']) + 1;
            }

            if (isset($_POST['title'])) {
                $_ruleTitle = $_POST['title'];
            }

            $this->UserInterface->Toolbar->AddButton($this->Language->Get('insert'), 'fa-plus-circle');
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('chatrule'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

            $_ActionsTabObject->Toolbar->AddButton($this->Language->Get('insert'), 'fa-plus-circle');
            $_ActionsTabObject->Toolbar->AddButton($this->Language->Get('insertaction') . ' <img src="' . SWIFT::Get('themepath') . 'images/menudropgray.gif" align="absmiddle" border="0" />', 'fa-plus-circle', 'UIDropDown(\'visitoractionmenu\', event, \'visitorRuleActionID\', \'toolbarRuleAction\');', SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, 'visitorRuleActionID', '', false);
            $_ActionsTabObject->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('chatrule'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);
        }

        $_GeneralTabObject->Text('title', $this->Language->Get('ruletitle'), $this->Language->Get('desc_ruletitle'), $_ruleTitle);

        $_optionsContainer = array();
        $_index = 0;

        /**
         * $_optionsContainer[0]['title'] = $this->Language->Get('ruletype_'.SWIFT_VisitorRule::RULETYPE_CHATESTABLISHED);
         * $_optionsContainer[0]['value'] = SWIFT_VisitorRule::RULETYPE_CHATESTABLISHED;
         * if ($_ruleType == SWIFT_VisitorRule::RULETYPE_CHATESTABLISHED)
         * {
         *     $_optionsContainer[0]['selected'] = true;
         * }
         */

        $_optionsContainer[$_index]['title'] = $this->Language->Get('ruletype_' . SWIFT_VisitorRule::RULETYPE_VISITORENTERSPAGE);
        $_optionsContainer[$_index]['value'] = SWIFT_VisitorRule::RULETYPE_VISITORENTERSPAGE;

        if ($_mode == SWIFT_UserInterface::MODE_INSERT && !isset($_POST['ruletype'])) {
            $_optionsContainer[$_index]['selected'] = true;
        } else if ($_ruleType == SWIFT_VisitorRule::RULETYPE_VISITORENTERSPAGE) {
            $_optionsContainer[$_index]['selected'] = true;
        }
        $_index++;

        $_optionsContainer[$_index]['title'] = $this->Language->Get('ruletype_' . SWIFT_VisitorRule::RULETYPE_VISITORENTERSSITE);
        $_optionsContainer[$_index]['value'] = SWIFT_VisitorRule::RULETYPE_VISITORENTERSSITE;
        if ($_ruleType == SWIFT_VisitorRule::RULETYPE_VISITORENTERSSITE) {
            $_optionsContainer[$_index]['selected'] = true;
        }
        $_index++;

        /**
         * $_optionsContainer[3]['title'] = $this->Language->Get('ruletype_'.SWIFT_VisitorRule::RULETYPE_CHATQUEUED);
         * $_optionsContainer[3]['value'] = SWIFT_VisitorRule::RULETYPE_CHATQUEUED;
         * if ($_ruleType == SWIFT_VisitorRule::RULETYPE_CHATQUEUED)
         * {
         *     $_optionsContainer[3]['selected'] = true;
         * }
         */

        $_GeneralTabObject->Select('ruletype', $this->Language->Get('ruletype'), $this->Language->Get('desc_ruletype'), $_optionsContainer);

        $_GeneralTabObject->YesNo('stopprocessing', $this->Language->Get('rulestop'), $this->Language->Get('desc_rulestop'), $_ruleStopProcessing);

        $_GeneralTabObject->Number('sortorder', $this->Language->Get('sortorder'), $this->Language->Get('desc_sortorder'), $_ruleSortOrder);

        $_optionsContainer = array();
        $_optionsContainer[0]["title"] = $this->Language->Get('smatchall');
        $_optionsContainer[0]["value"] = 'all';
        $_optionsContainer[0]["checked"] = IIF($_ruleMatchAll == true, true, false);
        $_optionsContainer[1]["title"] = $this->Language->Get('smatchany');
        $_optionsContainer[1]["value"] = 'any';
        $_optionsContainer[1]["checked"] = IIF(!$_ruleMatchAll, true, false);

        $_GeneralTabObject->Radio('ruleoptions', $this->Language->Get('matchtype'), $this->Language->Get('desc_matchtype'), $_optionsContainer);

        $_SWIFT_UserInterfaceToolbarObject = new SWIFT_UserInterfaceToolbar($this->UserInterface);
        $_SWIFT_UserInterfaceToolbarObject->AddButton($this->Language->Get('insertcriteria'), 'fa-plus-circle', "newGlobalRuleCriteria('currentpagetitle', '" . SWIFT_Rules::OP_CONTAINS . "', '');", SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, '', '', false);

        $_GeneralTabObject->AppendHTML($_SWIFT_UserInterfaceToolbarObject->Render(true) . '<tr class="' . $_GeneralTabObject->GetClass() . '"><td align="left" colspan="2" class="smalltext"><div id="ruleparent"></div></td></tr>');

        $_ActionsTabObject->AppendHTML('<tr class="' . $_ActionsTabObject->GetClass() . '"><td align="left" colspan="2" class="smalltext"><div id="visitorActionParent"></div></td></tr>');

        $this->UserInterface->End();

        if ($_mode == SWIFT_UserInterface::MODE_INSERT && isset($_POST['rulecriteria'])) {
            if (isset($_POST['ruleaction'])) {
                $_ruleActions = $_POST['ruleaction'];
            } else {
                $_ruleActions = array();
            }

            SWIFT_VisitorRule::CriteriaActionsPointerToJavaScript($_POST['rulecriteria'], $_ruleActions);
        } else if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            SWIFT_VisitorRule::CriteriaActionsPointerToJavaScript($_SWIFT_VisitorRuleObject->GetProperty('_criteria'), $_SWIFT_VisitorRuleObject->GetProperty('_actions'));
        }

        if ($_mode == SWIFT_UserInterface::MODE_INSERT && !isset($_POST['rulecriteria'])) {
            echo '<script language="Javascript" type="text/javascript">QueueFunction(function(){newGlobalRuleCriteria(\'currentpagetitle\', \'' . SWIFT_Rules::OP_CONTAINS . '\', \'\');});</script>';
        }
    }

    /**
     * Render the Visitor Ban Grid
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function RenderGrid()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->Load->Library('UserInterface:UserInterfaceGrid', array('visitorrulegrid'), true, false, 'base');

        if ($this->UserInterfaceGrid->GetMode() == SWIFT_UserInterfaceGrid::MODE_SEARCH) {
            $this->UserInterfaceGrid->SetSearchQuery('SELECT * FROM ' . TABLE_PREFIX . 'visitorrules WHERE (' . $this->UserInterfaceGrid->BuildSQLSearch('title') . ')', 'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX . 'visitorrules WHERE (' . $this->UserInterfaceGrid->BuildSQLSearch('title') . ')');
        }

        $this->UserInterfaceGrid->SetQuery('SELECT * FROM ' . TABLE_PREFIX . 'visitorrules', 'SELECT COUNT(*) FROM ' . TABLE_PREFIX . 'visitorrules');

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('visitorruleid', 'visitorruleid', SWIFT_UserInterfaceGridField::TYPE_ID));

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('icon', '&nbsp;', SWIFT_UserInterfaceGridField::TYPE_CUSTOM, 16, SWIFT_UserInterfaceGridField::ALIGN_CENTER));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('title', $this->Language->Get('ruletitle'), SWIFT_UserInterfaceGridField::TYPE_DB, 0, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('sortorder', $this->Language->Get('sortorder'), SWIFT_UserInterfaceGridField::TYPE_DB, 150, SWIFT_UserInterfaceGridField::ALIGN_CENTER, SWIFT_UserInterfaceGridField::SORT_ASC), true);
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('matchtype', $this->Language->Get('smatchtype'), SWIFT_UserInterfaceGridField::TYPE_DB, 180, SWIFT_UserInterfaceGridField::ALIGN_LEFT));

        $this->UserInterfaceGrid->SetRenderCallback(array($this, 'GridRender'));
        $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('delete'), 'fa-trash', array('LiveChat\Admin\Controller_Rule', 'DeleteList'), $this->Language->Get('actionconfirm')));
        $this->UserInterfaceGrid->SetNewLinkViewport(SWIFT::Get('basename') . '/LiveChat/Rule/Insert');

        $this->UserInterfaceGrid->Render();

        $this->UserInterfaceGrid->Display();

        return true;
    }

    /**
     * The Grid Rendering Function
     *
     * @author Varun Shoor
     * @param array $_fieldContainer The Field Record Value Container
     * @return array
     */
    public static function GridRender($_fieldContainer)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_fieldContainer['icon'] = '<img src="' . SWIFT::Get('themepath') . 'images/icon_visitorrule.gif" align="absmiddle" border="0" />';
        $_fieldContainer['title'] = '<a href="' . SWIFT::Get('basename') . "/LiveChat/Rule/Edit/" . (int)($_fieldContainer['visitorruleid']) . '" viewport="1">' . htmlspecialchars($_fieldContainer['title']) . '</a>';

        if ($_fieldContainer['matchtype'] == SWIFT_VisitorRule::RULE_MATCHALL) {
            $_fieldContainer['matchtype'] = $_SWIFT->Language->Get('smatchall');
        } else {
            $_fieldContainer['matchtype'] = $_SWIFT->Language->Get('smatchany');
        }

        return $_fieldContainer;
    }

    /**
     * Dispatches the XML Menu
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function DispatchMenu()
    {
        $_SWIFT = SWIFT::GetInstance();

        echo '<ul class="swiftdropdown" id="visitoractionmenu">';

        echo '<li class="swiftdropdownitemparent" onclick="javascript: globalActionVariables(\'\', \'\');"><div class="swiftdropdownitem"><div class="swiftdropdownitemimage"><img src="' . SWIFT::Get('themepath') . 'images/menu_newaction.gif' . '" align="absmiddle" border="0" /></div><div class="swiftdropdownitemtext" onclick="javascript: void(0);">' . $this->Language->Get('actionvariables') . '</div></div></li>

        <li class="swiftdropdownitemparent" onclick="javascript: globalActionVisitorExperience(\'engage\');"><div class="swiftdropdownitem"><div class="swiftdropdownitemimage"><img src="' . SWIFT::Get('themepath') . 'images/menu_newaction.gif' . '" align="absmiddle" border="0" /></div><div class="swiftdropdownitemtext" onclick="javascript: void(0);">' . $this->Language->Get('actionvisitorexperience') . '</div></div></li>

        <li class="swiftdropdownitemparent" onclick="javascript: globalActionStaffAlerts(\'\', \'\');"><div class="swiftdropdownitem"><div class="swiftdropdownitemimage"><img src="' . SWIFT::Get('themepath') . 'images/menu_newaction.gif' . '" align="absmiddle" border="0" /></div><div class="swiftdropdownitemtext" onclick="javascript: void(0);">' . $this->Language->Get('actionstaffalerts') . '</div></div></li>

        <li class="swiftdropdownitemparent" onclick="javascript: globalActionSetDepartment(\'\');"><div class="swiftdropdownitem"><div class="swiftdropdownitemimage"><img src="' . SWIFT::Get('themepath') . 'images/menu_newaction.gif' . '" align="absmiddle" border="0" /></div><div class="swiftdropdownitemtext" onclick="javascript: void(0);">' . $this->Language->Get('actionsetdepartment') . '</div></div></li>

        <li class="swiftdropdownitemparent" onclick="javascript: globalActionSetSkill(\'\');"><div class="swiftdropdownitem"><div class="swiftdropdownitemimage"><img src="' . SWIFT::Get('themepath') . 'images/menu_newaction.gif' . '" align="absmiddle" border="0" /></div><div class="swiftdropdownitemtext" onclick="javascript: void(0);">' . $this->Language->Get('actionsetskill') . '</div></div></li>

        <li class="swiftdropdownitemparent" onclick="javascript: globalActionSetGroup(\'\');"><div class="swiftdropdownitem"><div class="swiftdropdownitemimage"><img src="' . SWIFT::Get('themepath') . 'images/menu_newaction.gif' . '" align="absmiddle" border="0" /></div><div class="swiftdropdownitemtext" onclick="javascript: void(0);">' . $this->Language->Get('actionsetgroup') . '</div></div></li>

        <li class="swiftdropdownitemparent" onclick="javascript: globalActionSetColor(\'\', \'\');"><div class="swiftdropdownitem"><div class="swiftdropdownitemimage"><img src="' . SWIFT::Get('themepath') . 'images/menu_newaction.gif' . '" align="absmiddle" border="0" /></div><div class="swiftdropdownitemtext" onclick="javascript: void(0);">' . $this->Language->Get('actionsetcolor') . '</div></div></li>

        <li class="swiftdropdownitemparent" onclick="javascript: globalActionBanVisitor(\'\');"><div class="swiftdropdownitem"><div class="swiftdropdownitemimage"><img src="' . SWIFT::Get('themepath') . 'images/menu_newaction.gif' . '" align="absmiddle" border="0" /></div><div class="swiftdropdownitemtext" onclick="javascript: void(0);">' . $this->Language->Get('actionbanvisitor') . '</div></div></li>';

        echo '</ul>';

        return true;
    }
}
