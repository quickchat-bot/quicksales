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
use Base\Library\Rules\SWIFT_Rules;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceGrid;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridField;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridMassAction;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;
use SWIFT_View;
use Tickets\Models\AutoClose\SWIFT_AutoCloseRule;

/**
 * The Auto Close View
 *
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @author Varun Shoor
 */
class View_AutoClose extends SWIFT_View
{
    /**
     * Render the Auto Close rule
     *
     * @author Varun Shoor
     * @param int $_mode The Render Mode
     * @param SWIFT_AutoCloseRule|null $_SWIFT_AutoCloseRuleObject The SWIFT_AutoCloseRule Object Pointer (Only for EDIT Mode)
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public function Render($_mode, SWIFT_AutoCloseRule $_SWIFT_AutoCloseRuleObject = null)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            return false;
        }

        $_ticketStatusCache = (array) $_SWIFT->Cache->Get('statuscache');

        $_criteriaPointer = SWIFT_AutoCloseRule::GetCriteriaPointer();
        SWIFT_AutoCloseRule::ExtendCustomCriteria($_criteriaPointer);
        SWIFT_Rules::CriteriaPointerToJavaScriptArray($_criteriaPointer);

        if (isset($_POST['rulecriteria']))
        {
            SWIFT_AutoCloseRule::CriteriaActionsPointerToJavaScript($_POST['rulecriteria'], array());
        }

        if ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT_AutoCloseRuleObject !== null)
        {
            $this->UserInterface->Start(get_short_class($this),'/Tickets/AutoClose/EditSubmit/'. $_SWIFT_AutoCloseRuleObject->GetAutoCloseRuleID(), SWIFT_UserInterface::MODE_EDIT, false);
        } else {
            $this->UserInterface->Start(get_short_class($this),'/Tickets/AutoClose/InsertSubmit', SWIFT_UserInterface::MODE_INSERT, false);
        }

        $_autoCloseRuleTitle = '';
        $_autoCloseRuleTargetTicketStatusID = 0;

        foreach ($_ticketStatusCache as $_ticketStatusID => $_ticketStatusContainer) {
            if ($_ticketStatusContainer['markasresolved'] == '1') {
                $_autoCloseRuleTargetTicketStatusID = $_ticketStatusID;

                break;
            }
        }

        $_autoCloseRuleInactivityThreshold = 48;
        $_autoCloseRuleClosureThreshold = 72;
        $_autoCloseRuleSendPendingNotification = 1;
        $_autoCloseRuleSendFinalNotification = 1;
        $_autoCloseRuleSuppressSurveyEmail = 0;
        $_autoCloseRuleIsEnabled = true;

        $_sortOrderContainer = $this->Database->QueryFetch("SELECT sortorder FROM " . TABLE_PREFIX . "autocloserules ORDER BY sortorder DESC");

        if (!isset($_sortOrderContainer['sortorder']) || empty($_sortOrderContainer['sortorder']))
        {
            $_autoCloseRuleSortOrder = 1;
        } else {
            $_autoCloseRuleSortOrder = (int) ($_sortOrderContainer['sortorder']) + 1;
        }

        if ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT_AutoCloseRuleObject !== null)
        {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle');
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('delete'), 'fa-trash', '/Tickets/AutoClose/Delete/' . $_SWIFT_AutoCloseRuleObject->GetAutoCloseRuleID(), SWIFT_UserInterfaceToolbar::LINK_CONFIRM, '', '', false);
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('autoclose'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

            $_autoCloseRuleTitle = $_SWIFT_AutoCloseRuleObject->GetProperty('title');
            $_autoCloseRuleTargetTicketStatusID = (int) ($_SWIFT_AutoCloseRuleObject->GetProperty('targetticketstatusid'));
            $_autoCloseRuleInactivityThreshold = (float) ($_SWIFT_AutoCloseRuleObject->GetProperty('inactivitythreshold'));
            $_autoCloseRuleClosureThreshold = (float) ($_SWIFT_AutoCloseRuleObject->GetProperty('closurethreshold'));
            $_autoCloseRuleSendPendingNotification = (int) ($_SWIFT_AutoCloseRuleObject->GetProperty('sendpendingnotification'));
            $_autoCloseRuleSendFinalNotification = (int) ($_SWIFT_AutoCloseRuleObject->GetProperty('sendfinalnotification'));
            $_autoCloseRuleSuppressSurveyEmail = (int) ($_SWIFT_AutoCloseRuleObject->GetProperty('suppresssurveyemail'));
            $_autoCloseRuleIsEnabled = (int) ($_SWIFT_AutoCloseRuleObject->GetProperty('isenabled'));
            $_autoCloseRuleSortOrder = (int) ($_SWIFT_AutoCloseRuleObject->GetProperty('sortorder'));

        } else {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('insert'), 'fa-plus-circle');
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('autoclose'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);
        }



        /*
         * ###############################################
         * BEGIN GENERAL TAB
         * ###############################################
         */
        $_GeneralTabObject = $this->UserInterface->AddTab($this->Language->Get('tabgeneral'), 'icon_form.gif', 'general', true);


        $_GeneralTabObject->Text('title', $this->Language->Get('ruletitle'), $this->Language->Get('desc_ruletitle'), $_autoCloseRuleTitle);

        $_optionContainer = array();
        $_index = 0;
        foreach ($_ticketStatusCache as $_ticketStatusID => $_ticketStatusContainer) {
            if ($_ticketStatusContainer['markasresolved'] != '1') {
                continue;
            }

            $_optionContainer[$_index]['title'] = $_ticketStatusContainer['title'];
            $_optionContainer[$_index]['value'] = $_ticketStatusID;

            if ($_ticketStatusID == $_autoCloseRuleTargetTicketStatusID) {
                $_optionContainer[$_index]['selected'] = true;
            }

            $_index++;
        }

        $_GeneralTabObject->Select('targetticketstatusid', $this->Language->Get('targetticketstatus'), $this->Language->Get('desc_targetticketstatus'), $_optionContainer);

        $_GeneralTabObject->Number('inactivitythreshold', $this->Language->Get('inactivitythreshold'), $this->Language->Get('desc_inactivitythreshold'), $_autoCloseRuleInactivityThreshold);
        $_GeneralTabObject->Number('closurethreshold', $this->Language->Get('closurethreshold'), $this->Language->Get('desc_closurethreshold'), $_autoCloseRuleClosureThreshold);

        $_GeneralTabObject->YesNo('sendpendingnotification', $this->Language->Get('sendpendingnotification'), $this->Language->Get('desc_sendpendingnotification'), $_autoCloseRuleSendPendingNotification);
        $_GeneralTabObject->YesNo('sendfinalnotification', $this->Language->Get('sendfinalnotification'), $this->Language->Get('desc_sendfinalnotification'), $_autoCloseRuleSendFinalNotification);

        $_GeneralTabObject->YesNo('suppresssurveyemail', $this->Language->Get('suppresssurveyemail'), $this->Language->Get('desc_suppresssurveyemail'), $_autoCloseRuleSuppressSurveyEmail);

        $_GeneralTabObject->YesNo('isenabled', $this->Language->Get('isenabled'), $this->Language->Get('desc_isenabled'), $_autoCloseRuleIsEnabled);

        // Sort Order
        $_GeneralTabObject->Number('sortorder', $this->Language->Get('sortorder'), $this->Language->Get('desc_sortorder'), $_autoCloseRuleSortOrder);

        $_defaultTicketStatusID = false;
        foreach ($_ticketStatusCache as $_ticketStatusID => $_ticketStatusContainer)
        {
            if ($_ticketStatusContainer['markasresolved'] == '0')
            {
                $_defaultTicketStatusID =  ($_ticketStatusID);
                break;
            }
        }

        $_appendHTML = '<tr id="tabtoolbar"><td align="left" valign="top" colspan="2" class="settabletitlerowmain2"><div class="tabtoolbarsub"><ul><li><a href="javascript:void(0);" onmouseup="javascript:this.blur(); newGlobalRuleCriteria(\'ticketstatusid\', \'' . SWIFT_Rules::OP_EQUAL . '\', \'' . (int) ($_defaultTicketStatusID) . ', \', \'1\', \'1\');"><img border="0" align="absmiddle" src="' . SWIFT::Get('themepath') . 'images/icon_insertcriteria.gif' . '" /> ' . $this->Language->Get('insertcriteria') . '</a></li></ul></div></td>';

        $_appendHTML .= '<tr class="gridrow2"><td align="left" colspan="2" class="smalltext"><div id="ruleparent"></div></td></tr>';
        $_GeneralTabObject->AppendHTML($_appendHTML);

        /*
         * ###############################################
         * END GENERAL TAB
         * ###############################################
         */

        if (!isset($_POST['rulecriteria']))
        {
            $this->UserInterface->AppendHTML('<script type="text/javascript">QueueFunction(function(){ newGlobalRuleCriteria(\'ticketstatusid\', \'' . SWIFT_Rules::OP_EQUAL . '\', \'' . $_defaultTicketStatusID . '\', \'1\', \'1\'); });</script>');
        }

        $this->UserInterface->End();

        return true;
    }

    /**
     * Render the Auto Close Rules Grid
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

        $this->Load->Library('UserInterface:UserInterfaceGrid', array('autoclosegrid'), true, false, 'base');

        if ($this->UserInterfaceGrid->GetMode() == SWIFT_UserInterfaceGrid::MODE_SEARCH)
        {
            $this->UserInterfaceGrid->SetSearchQuery('SELECT * FROM ' . TABLE_PREFIX . 'autocloserules
                WHERE (' . $this->UserInterfaceGrid->BuildSQLSearch('title') . ')',

                'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX . 'autocloserules WHERE (' . $this->UserInterfaceGrid->BuildSQLSearch('title') . ')');
        }

        $this->UserInterfaceGrid->SetQuery('SELECT * FROM ' . TABLE_PREFIX . 'autocloserules',
                'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX . 'autocloserules');

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('autocloseruleid', 'autocloseruleid', SWIFT_UserInterfaceGridField::TYPE_ID));

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('icon', '&nbsp;', SWIFT_UserInterfaceGridField::TYPE_CUSTOM, 16, SWIFT_UserInterfaceGridField::ALIGN_CENTER));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('title', $this->Language->Get('ruletitle'), SWIFT_UserInterfaceGridField::TYPE_DB, 0, SWIFT_UserInterfaceGridField::ALIGN_LEFT, SWIFT_UserInterfaceGridField::SORT_ASC), true);
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('targetticketstatusid', $this->Language->Get('targetstatus'), SWIFT_UserInterfaceGridField::TYPE_DB, 160, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('inactivitythreshold', $this->Language->Get('inactivitythreshold'), SWIFT_UserInterfaceGridField::TYPE_DB, 110, SWIFT_UserInterfaceGridField::ALIGN_CENTER));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('closurethreshold', $this->Language->Get('closurethreshold'), SWIFT_UserInterfaceGridField::TYPE_DB, 110, SWIFT_UserInterfaceGridField::ALIGN_CENTER));

        $this->UserInterfaceGrid->SetRenderCallback(array($this, 'GridRender'));
        $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('delete'), 'fa-trash',
                array('Tickets\Admin\Controller_AutoClose', 'DeleteList'), $this->Language->Get('actionconfirm')));
        $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('enable'), 'fa-check-circle',
                array('Tickets\Admin\Controller_AutoClose', 'EnableList'), $this->Language->Get('actionconfirm')));
        $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('disable'), 'fa-minus-circle',
                array('Tickets\Admin\Controller_AutoClose', 'DisableList'), $this->Language->Get('actionconfirm')));

        $this->UserInterfaceGrid->SetNewLinkViewport(SWIFT::Get('basename') . '/Tickets/AutoClose/Insert');

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

        $_ticketStatusCache = $_SWIFT->Cache->Get('statuscache');

        $_fieldContainer['title'] = '<a href="' . SWIFT::Get('basename') . '/Tickets/AutoClose/Edit/' . (int) ($_fieldContainer['autocloseruleid']) . '" viewport="1" title="' . addslashes(htmlspecialchars($_fieldContainer['title'])) . '">' . htmlspecialchars($_fieldContainer['title']) . '</a>';

        if (isset($_ticketStatusCache[$_fieldContainer['targetticketstatusid']])) {
            $_fieldContainer['targetticketstatusid'] = htmlspecialchars($_ticketStatusCache[$_fieldContainer['targetticketstatusid']]['title']);
        } else {
            $_fieldContainer['targetticketstatusid'] = $_SWIFT->Language->Get('na');
        }

        $_fieldContainer['inactivitythreshold'] = (float) ($_fieldContainer['inactivitythreshold']);
        $_fieldContainer['closurethreshold'] = (float) ($_fieldContainer['closurethreshold']);

        $_fieldContainer['icon'] = '<img src="' . SWIFT::Get('themepath') . 'images/' . IIF($_fieldContainer['isenabled'] == '0', 'icon_block.gif', 'icon_ticketstatusclosed.png') . '" align="absmiddle" border="0" />';

        return $_fieldContainer;
    }
}
