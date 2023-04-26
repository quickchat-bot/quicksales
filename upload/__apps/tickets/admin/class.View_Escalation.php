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

namespace Tickets\Admin;

use Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel;
use SWIFT;
use SWIFT_Date;
use Base\Models\Department\SWIFT_Department;
use SWIFT_Exception;
use Base\Library\Help\SWIFT_Help;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceGrid;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridField;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridMassAction;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;
use SWIFT_View;
use Tickets\Library\Flag\SWIFT_TicketFlag;
use Tickets\Models\Escalation\SWIFT_EscalationNotification;
use Tickets\Models\Escalation\SWIFT_EscalationRule;

/**
 * The Escalation View Class
 *
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @property SWIFT_TicketFlag $TicketFlag
 * @author Varun Shoor
 */
class View_Escalation extends SWIFT_View
{
    /**
     * Render the Escalation Form
     *
     * @author Varun Shoor
     * @param int $_mode The Render Mode
     * @param SWIFT_EscalationRule|null $_SWIFT_EscalationRuleObject The SWIFT_EscalationRule Object Pointer (Only for EDIT Mode)
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public function Render($_mode, SWIFT_EscalationRule $_SWIFT_EscalationRuleObject = null)
    {
        if (!$this->GetIsClassLoaded())
        {
            return false;
        }

        // Calculate the URL
        if ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT_EscalationRuleObject !== null)
        {
            $this->UserInterface->Start(get_short_class($this),'/Tickets/Escalation/EditSubmit/' . $_SWIFT_EscalationRuleObject->GetEscalationRuleID(),
                    SWIFT_UserInterface::MODE_EDIT, false);
        } else {
            $this->UserInterface->Start(get_short_class($this),'/Tickets/Escalation/InsertSubmit', SWIFT_UserInterface::MODE_INSERT, false);
        }

        $_GeneralTabObject = $this->UserInterface->AddTab($this->Language->Get('tabgeneral'), 'icon_form.gif', 'general', true);
        $_NotificationTabObject = $this->UserInterface->AddTab($this->Language->Get('tabnotifications'), 'icon_mail.gif', 'notifications');
        $_NotificationTabObject->LoadToolbar();

        $_escalationRuleTitle = '';
        $_parentDepartmentID = 0;
        $_escalationAddTagsList = $_escalationRemoveTagsList = array();
        $_escalationRuleType = SWIFT_EscalationRule::TYPE_BOTH;

        if ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT_EscalationRuleObject !== null)
        {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle');
            $_NotificationTabObject->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle');

            $this->UserInterface->Toolbar->AddButton($this->Language->Get('delete'), 'fa-trash', '/Tickets/Escalation/Delete/' .
                    $_SWIFT_EscalationRuleObject->GetEscalationRuleID(), SWIFT_UserInterfaceToolbar::LINK_CONFIRM, '', '', false);
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('escalation'),
                    SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

            $_NotificationTabObject->Toolbar->AddButton($this->Language->Get('insertnotification'), 'icon_mailadd.gif',
                    'javascript: InsertEscalationNotification();', SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, '', '', false);
            $_NotificationTabObject->Toolbar->AddButton($this->Language->Get('delete'), 'fa-trash', '/Tickets/Escalation/Delete/' .
                    $_SWIFT_EscalationRuleObject->GetEscalationRuleID(), SWIFT_UserInterfaceToolbar::LINK_CONFIRM, '', '', false);
            $_NotificationTabObject->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('escalation'),
                    SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

            $_escalationRuleTitle = $_SWIFT_EscalationRuleObject->GetProperty('title');

            $_POST['slaplanid'] = (int) ($_SWIFT_EscalationRuleObject->GetProperty('slaplanid'));
            $_POST['staffid'] = (int) ($_SWIFT_EscalationRuleObject->GetProperty('staffid'));
            $_POST['departmentid'] = (int) ($_SWIFT_EscalationRuleObject->GetProperty('departmentid'));
            $_parentDepartmentID = (int) ($_SWIFT_EscalationRuleObject->GetProperty('departmentid'));
            $_POST['priorityid'] = (int) ($_SWIFT_EscalationRuleObject->GetProperty('priorityid'));
            $_POST['ticketstatusid'] = (int) ($_SWIFT_EscalationRuleObject->GetProperty('ticketstatusid'));
            $_POST['tickettypeid'] = (int) ($_SWIFT_EscalationRuleObject->GetProperty('tickettypeid'));
            $_POST['newslaplanid'] = (int) ($_SWIFT_EscalationRuleObject->GetProperty('newslaplanid'));
            $_POST['flagtype'] = (int) ($_SWIFT_EscalationRuleObject->GetProperty('flagtype'));
            $_escalationRuleType = (int) ($_SWIFT_EscalationRuleObject->GetProperty('ruletype'));

            $_escalationAddTagsList = json_decode($_SWIFT_EscalationRuleObject->GetProperty('addtags'));
            $_escalationRemoveTagsList = json_decode($_SWIFT_EscalationRuleObject->GetProperty('removetags'));
        } else {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('insert'), 'fa-plus-circle');
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('escalation'),
                    SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

            $_NotificationTabObject->Toolbar->AddButton($this->Language->Get('insert'), 'fa-plus-circle');
            $_NotificationTabObject->Toolbar->AddButton($this->Language->Get('insertnotification'), 'icon_mailadd.gif',
                    'javascript: InsertEscalationNotification();', SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, '', '', false);
            $_NotificationTabObject->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('escalation'),
                    SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);
        }


        /*
         * ###############################################
         * BEGIN GENERAL TAB
         * ###############################################
         */

        $_GeneralTabObject->Text('title', $this->Language->Get('ruletitle'), $this->Language->Get('desc_ruletitle'), $_escalationRuleTitle);

        // ======= RENDER SLA PLANS =======
        /**
         * BUG FIX - Mansi Wason <mansi.wason@kayako.com>
         *
         * SWIFT-4444: "Disabled SLA plan gets applied on the ticket if set in the triggered Escalation rule."
         *
         * Comments: Only enabled SLA should be visible.
         */
        $_index = 0;
        $_optionsContainer = array();
        $this->Database->Query("SELECT slaplanid, title, isenabled FROM " . TABLE_PREFIX . "slaplans ORDER BY title ASC");
        while ($this->Database->NextRecord())
        {
            $_optionsContainer[$_index]['title'] = $this->Database->Record['title'];
            $_optionsContainer[$_index]['value'] = $this->Database->Record['slaplanid'];

            if ($this->Database->Record['isenabled'] == '0') {
                $_optionsContainer[$_index]['disabled'] = true;
            }

            $_index++;
        }

        if (!count($_optionsContainer))
        {
            $_optionsContainer[$_index]['title'] = $this->Language->Get('noplanavailable');
            $_optionsContainer[$_index]['value'] = '';
        }
        $_GeneralTabObject->Select('slaplanid', $this->Language->Get('escalationplan'), $this->Language->Get('desc_escalationplan'),
                $_optionsContainer);

        $_optionsContainer = array();
        $_optionsContainer[0]['title'] = $this->Language->Get('eruletype_due');
        $_optionsContainer[0]['value'] = SWIFT_EscalationRule::TYPE_DUE;
        if ($_escalationRuleType == SWIFT_EscalationRule::TYPE_DUE)
        {
            $_optionsContainer[0]['checked'] = true;
        }

        $_optionsContainer[1]['title'] = $this->Language->Get('eruletype_resolutiondue');
        $_optionsContainer[1]['value'] = SWIFT_EscalationRule::TYPE_RESOLUTIONDUE;
        if ($_escalationRuleType == SWIFT_EscalationRule::TYPE_RESOLUTIONDUE)
        {
            $_optionsContainer[1]['checked'] = true;
        }

        $_optionsContainer[2]['title'] = $this->Language->Get('eruletype_both');
        $_optionsContainer[2]['value'] = SWIFT_EscalationRule::TYPE_BOTH;
        if ($_escalationRuleType == SWIFT_EscalationRule::TYPE_BOTH)
        {
            $_optionsContainer[2]['checked'] = true;
        }

        $_GeneralTabObject->Radio('ruletype', $this->Language->Get('eruletype'), $this->Language->Get('desc_eruletype'), $_optionsContainer, true);

        $_GeneralTabObject->Title($this->Language->Get('escalationaction'), 'doublearrows.gif');

        // ======= RENDER STAFF LIST =======
        $_optionsContainer = array();
        $_optionsContainer[0]['title'] = $this->Language->Get('nochange');
        $_optionsContainer[0]['value'] = '0';
        $_optionsContainer[0]['selected'] = true;

        $_index = 1;
        $this->Database->Query("SELECT staffid, fullname FROM " . TABLE_PREFIX . "staff ORDER BY fullname ASC");
        while ($this->Database->NextRecord())
        {
            $_optionsContainer[$_index]['title'] = $this->Database->Record['fullname'];
            $_optionsContainer[$_index]['value'] = $this->Database->Record['staffid'];

            $_index++;
        }

        $_GeneralTabObject->Select('staffid', $this->Language->Get('escalationstaff'), $this->Language->Get('desc_escalationstaff'),
                $_optionsContainer);

        // ======= RENDER DEPARTMENT LIST =======
        $_optionsContainer = array();
        $_optionsContainer[0]['title'] = $this->Language->Get('nochange');
        $_optionsContainer[0]['value'] = '0';
        $_optionsContainer[0]['selected'] = true;

        $_index = 1;

        $_departmentMapOptions =  SWIFT_Department::GetDepartmentMapOptions(false, APP_TICKETS);

        foreach ($_departmentMapOptions as $_key => $_val)
        {
            $_optionsContainer[$_index] = $_val;

            $_index++;
        }

        $_GeneralTabObject->Select('departmentid', $this->Language->Get('escalationdepartment'), $this->Language->Get('desc_escalationdepartment'),
                $_optionsContainer, 'javascript: UpdateTicketStatusDiv(this, \'ticketstatusid\', true, false);');

        // ======= RENDER TICKET TYPE LIST =======
        $_optionsContainer = array();
        $_optionsContainer[0]['title'] = $this->Language->Get('nochange');
        $_optionsContainer[0]['value'] = '0';
        $_optionsContainer[0]['selected'] = true;

        $_index = 1;
        $this->Database->Query("SELECT tickettypeid, title FROM " . TABLE_PREFIX . "tickettypes ORDER BY title ASC");
        while ($this->Database->NextRecord())
        {
            $_optionsContainer[$_index]['title'] = $this->Database->Record['title'];
            $_optionsContainer[$_index]['value'] = $this->Database->Record['tickettypeid'];

            $_index++;
        }

        $_GeneralTabObject->Select('tickettypeid', $this->Language->Get('escalationtickettype'), $this->Language->Get('desc_escalationtickettype'),
                $_optionsContainer);

        // ======= RENDER PRIORITY LIST =======
        $_optionsContainer = array();
        $_optionsContainer[0]['title'] = $this->Language->Get('nochange');
        $_optionsContainer[0]['value'] = '0';
        $_optionsContainer[0]['selected'] = true;

        $_index = 1;
        $this->Database->Query("SELECT priorityid, title FROM " . TABLE_PREFIX . "ticketpriorities ORDER BY title ASC");
        while ($this->Database->NextRecord())
        {
            $_optionsContainer[$_index]['title'] = $this->Database->Record['title'];
            $_optionsContainer[$_index]['value'] = $this->Database->Record['priorityid'];

            $_index++;
        }

        $_GeneralTabObject->Select('priorityid', $this->Language->Get('escalationpriority'), $this->Language->Get('desc_escalationpriority'),
                $_optionsContainer);

        // ======= RENDER TICKET STATUS LIST =======
        $_optionsContainer = array();
        $_optionsContainer[0]['title'] = $this->Language->Get('nochange');
        $_optionsContainer[0]['value'] = '0';
        $_optionsContainer[0]['selected'] = true;

        $_index = 1;
        $this->Database->Query("SELECT ticketstatusid, title FROM " . TABLE_PREFIX . "ticketstatus WHERE (departmentid = '0' OR departmentid = '" .
                 ($_parentDepartmentID) . "') ORDER BY title ASC");
        while ($this->Database->NextRecord())
        {
            $_optionsContainer[$_index]['title'] = $this->Database->Record['title'];
            $_optionsContainer[$_index]['value'] = $this->Database->Record['ticketstatusid'];

            $_index++;
        }

        $_GeneralTabObject->Select('ticketstatusid', $this->Language->Get('escalationticketstatus'),
                $this->Language->Get('desc_escalationticketstatus'), $_optionsContainer, '', 'ticketstatusid_container');

        // ======= RENDER SLA PLANS =======
        $_optionsContainer = array();
        $_optionsContainer[0]['title'] = $this->Language->Get('nochange');
        $_optionsContainer[0]['value'] = '0';
        $_optionsContainer[0]['selected'] = true;

        $_index = 1;
        $this->Database->Query("SELECT slaplanid, title FROM " . TABLE_PREFIX . "slaplans
                                WHERE isenabled = '1'
                                ORDER BY title ASC");
        while ($this->Database->NextRecord())
        {
            $_optionsContainer[$_index]['title'] = $this->Database->Record['title'];
            $_optionsContainer[$_index]['value'] = $this->Database->Record['slaplanid'];

            $_index++;
        }

        $_GeneralTabObject->Select('newslaplanid', $this->Language->Get('escalationslaplan'), $this->Language->Get('desc_escalationslaplan'),
                $_optionsContainer);

        // ======= RENDER FLAG TYPES =======
        $_optionsContainer = array();
        $_optionsContainer[0]['title'] = $this->Language->Get('nochange');
        $_optionsContainer[0]['value'] = '0';
        $_optionsContainer[0]['selected'] = true;

        $this->Load->Library('Flag:TicketFlag', [], true, false, 'tickets');

        $_index = 1;
        foreach ($this->TicketFlag->GetFlagList() as $_key => $_val)
        {
            $_optionsContainer[$_index]['title'] = $_val;
            $_optionsContainer[$_index]['value'] = $_key;

            $_index++;
        }

        $_GeneralTabObject->Select('flagtype', $this->Language->Get('escalationflagtype'), $this->Language->Get('desc_escalationflagtype'),
                $_optionsContainer);

        // ======= RENDER ADD TAGS =======
        $_GeneralTabObject->TextMultipleAutoComplete('addtags', $this->Language->Get('escalationaddtags'),
                $this->Language->Get('desc_escalationaddtags'), '/Base/Tags/QuickSearch', $_escalationAddTagsList,
                'fa-tags', false, true);

        // ======= RENDER REMOVE TAGS =======
        $_GeneralTabObject->TextMultipleAutoComplete('removetags', $this->Language->Get('escalationremovetags'),
                $this->Language->Get('desc_escalationremovetags'), '/Base/Tags/QuickSearch', $_escalationRemoveTagsList,
                'fa-tags', false, true);

        /*
         * ###############################################
         * END GENERAL TAB
         * ###############################################
         */





        /*
         * ###############################################
         * BEGIN NOTIFICATIONS TAB
         * ###############################################
         */

        $_notificationHTML = '';
        $_index = 0;

        if ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT_EscalationRuleObject !== null)
        {
            $_notificationContainer =  SWIFT_EscalationNotification::RetrieveOnEscalationRule($_SWIFT_EscalationRuleObject->GetEscalationRuleID());

            if (_is_array($_notificationContainer))
            {
                foreach ($_notificationContainer as $_key => $_val)
                {
                    $_rowID = 'notificationrow' . $_index;
                    $_rowMod = $_index % 2;
                    $_rowClass = 'searchrule' . $_rowMod;

                    $_resultHTML = '<table border="0" cellpadding="3" cellspacing="1" width="100%"><tr>
                        <td align="left" width="1"><a href="javascript:void(0);"
                        onClick="javascript: RemoveEscalationNotification(\'' . $_index . '\');"><i class="fa fa-trash" aria-hidden="true"></i></a></td>
                                <td align="left" width=""><select name="notifications[' . $_index . '][0]" class="swiftselect">';

                    foreach (SWIFT_EscalationNotification::GetTypeList() as $_subKey => $_subVal)
                    {
                        $_resultHTML .= '<option value="' . $_subKey . '"' . IIF($_val['notificationtype'] == $_subKey, ' selected') . '>' . $_subVal . '</option>';
                    }

                    $_resultHTML .= '</select></td></tr></table><table border="0" cellpadding="3" cellspacing="1" width="100%"><tr>
                        <td align="left" valign="top" width="130"><b>' . $this->Language->Get('notificationsubject') . '</b></td>
                            <td align="left" valign="top" width=""><input type="text" name="notifications[' . $_index . '][1]"
                                class="swifttext" style="width: 99%;" value="' . htmlspecialchars($_val['subject']) . '" /></td></tr>
                                    <tr><td align="left" valign="top" colspan="2"><textarea class="swifttext"
                                    name="notifications[' . $_index . '][2]" rows="15" style="width: 99%;">' .
                            htmlspecialchars($_val['notificationcontents']) . '</textarea></td></tr></table>';

                    $_notificationHTML .= '<div class="' . $_rowClass . '" id="' . $_rowID . '">' . $_resultHTML . '</div>';

                    $_index++;
                }
            }
        }

        $_appendHTML = '<tr class="gridrow2"><td align="left" colspan="2" class="smalltext"><div id="notificationparent">' .
        $_notificationHTML . '</div></td></tr>';
        $_NotificationTabObject->AppendHTML($_appendHTML . '<script type="text/javascript">QueueFunction(function(){ globalRuleSecondaryIndex = ' .
                 ($_index) . '; });</script>');

        /*
         * ###############################################
         * END NOTIFICATIONS TAB
         * ###############################################
         */

        $this->UserInterface->End();

        return true;
    }

    /**
     * Render the Ticket File Type Grid
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

        $this->Load->Library('UserInterface:UserInterfaceGrid', array('escalationrulegrid'), true, false, 'base');

        if ($this->UserInterfaceGrid->GetMode() == SWIFT_UserInterfaceGrid::MODE_SEARCH)
        {
            $this->UserInterfaceGrid->SetSearchQuery('SELECT escalationrules.*, slaplans.title AS plantitle FROM ' . TABLE_PREFIX .
                    'escalationrules AS escalationrules LEFT JOIN ' . TABLE_PREFIX . 'slaplans AS slaplans ON
                        (escalationrules.slaplanid = slaplans.slaplanid) WHERE (' .
                    $this->UserInterfaceGrid->BuildSQLSearch('escalationrules.title') . ' OR ' .
                    $this->UserInterfaceGrid->BuildSQLSearch('slaplans.title') . ')',
                    'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX . 'escalationrules AS escalationrules LEFT JOIN ' .
                    TABLE_PREFIX . 'slaplans AS slaplans ON (escalationrules.slaplanid = slaplans.slaplanid) WHERE (' .
                    $this->UserInterfaceGrid->BuildSQLSearch('escalationrules.title') . ' OR ' .
                    $this->UserInterfaceGrid->BuildSQLSearch('slaplans.title') . ')');
        }

        $this->UserInterfaceGrid->SetQuery('SELECT escalationrules.*, slaplans.title AS plantitle FROM ' . TABLE_PREFIX .
                'escalationrules AS escalationrules LEFT JOIN ' . TABLE_PREFIX . 'slaplans AS slaplans ON
                    (escalationrules.slaplanid = slaplans.slaplanid)', 'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX .
                'escalationrules AS escalationrules LEFT JOIN ' . TABLE_PREFIX . 'slaplans AS slaplans ON
                    (escalationrules.slaplanid = slaplans.slaplanid)');

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('escalationruleid', 'escalationruleid',
                SWIFT_UserInterfaceGridField::TYPE_ID));

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('icon', '&nbsp;', SWIFT_UserInterfaceGridField::TYPE_CUSTOM, 16,
                SWIFT_UserInterfaceGridField::ALIGN_CENTER));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('escalationrules.title', $this->Language->Get('ruletitle'),
                SWIFT_UserInterfaceGridField::TYPE_DB, 0, SWIFT_UserInterfaceGridField::ALIGN_LEFT, SWIFT_UserInterfaceGridField::SORT_ASC), true);
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('slaplans.title', $this->Language->Get('plantitle'),
                SWIFT_UserInterfaceGridField::TYPE_DB, 250, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('escalationrules.dateline', $this->Language->Get('creationdate'),
                SWIFT_UserInterfaceGridField::TYPE_DB, 200, SWIFT_UserInterfaceGridField::ALIGN_LEFT));

        $this->UserInterfaceGrid->SetRenderCallback(array($this, 'GridRender'));
        $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('delete'), 'fa-trash',
                array('Tickets\Admin\Controller_Escalation', 'DeleteList'), $this->Language->Get('actionconfirm')));

        $this->UserInterfaceGrid->SetNewLinkViewport(SWIFT::Get('basename') . '/Tickets/Escalation/Insert');

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

        $_fieldContainer['icon'] = '<img src="' . SWIFT::Get('themepath') . 'images/icon_escalation.gif" align="absmiddle" border="0" />';
        $_fieldContainer['slaplans.title'] = htmlspecialchars($_fieldContainer['plantitle']);

        $_fieldContainer['escalationrules.title'] = "<a href=\"" .  SWIFT::Get('basename') . '/Tickets/Escalation/Edit/' . (int) ($_fieldContainer['escalationruleid']) . "\" viewport=\"1\" title='" . $_SWIFT->Language->Get('edit') . "'> " . htmlspecialchars($_fieldContainer['title']) . '</a>';

        $_fieldContainer['escalationrules.dateline'] = SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_fieldContainer['dateline']);

        return $_fieldContainer;
    }
}
