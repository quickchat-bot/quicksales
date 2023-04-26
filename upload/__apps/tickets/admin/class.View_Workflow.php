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

namespace Tickets\Admin;

use Base\Library\CustomField\SWIFT_CustomFieldRenderer;
use Base\Library\UserInterface\SWIFT_UserInterfaceClient;
use Base\Models\CustomField\SWIFT_CustomFieldGroup;
use SWIFT;
use SWIFT_Date;
use Base\Models\Department\SWIFT_Department;
use SWIFT_Exception;
use Base\Library\Help\SWIFT_Help;
use Base\Library\Rules\SWIFT_Rules;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceGrid;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridField;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridMassAction;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;
use SWIFT_View;
use Tickets\Models\Workflow\SWIFT_TicketWorkflow;
use Tickets\Models\Workflow\SWIFT_TicketWorkflowNotification;

/**
 * The Ticket Workflow View Management
 *
 * @property SWIFT_UserInterfaceClient $UserInterface
 * @property Controller_Workflow $Controller
 * @author Varun Shoor
 */
class View_Workflow extends SWIFT_View
{
    /**
     * Render the Ticket Workflow Form
     *
     * @author Varun Shoor
     * @param int $_mode The Render Mode
     * @param SWIFT_TicketWorkflow $_SWIFT_TicketWorkflowObject The SWIFT_TicketWorkflow Object Pointer (Only for EDIT Mode)
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public function Render($_mode, SWIFT_TicketWorkflow $_SWIFT_TicketWorkflowObject = null)
    {
        if (!$this->GetIsClassLoaded())
        {
            return false;
        }

        $_ticketStatusCache = (array) $this->Cache->Get('statuscache');

        $_criteriaPointer = SWIFT_TicketWorkflow::GetCriteriaPointer();

        SWIFT_TicketWorkflow::ExtendCustomCriteria($_criteriaPointer);
        SWIFT_Rules::CriteriaPointerToJavaScriptArray($_criteriaPointer);

        if (isset($_POST['rulecriteria']))
        {
            SWIFT_TicketWorkflow::CriteriaActionsPointerToJavaScript($_POST['rulecriteria'], array());
        }

        // Calculate the URL
        if ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT_TicketWorkflowObject !== null)
        {
            $this->UserInterface->Start(get_short_class($this),'/Tickets/Workflow/EditSubmit/'. $_SWIFT_TicketWorkflowObject->GetTicketWorkflowID(),
                    SWIFT_UserInterface::MODE_EDIT, false);
        } else {
            $this->UserInterface->Start(get_short_class($this),'/Tickets/Workflow/InsertSubmit', SWIFT_UserInterface::MODE_INSERT, false);
        }

        // Tabs
        $_GeneralTabObject = $this->UserInterface->AddTab($this->Language->Get('tabgeneral'), 'icon_form.gif', 'general', true);
        $_ActionsTabObject = $this->UserInterface->AddTab($this->Language->Get('tabactions'), 'icon_actions.gif', 'actions');
        $_NotificationTabObject = $this->UserInterface->AddTab($this->Language->Get('tabnotifications'), 'icon_mail.gif', 'notifications');
        $_NotificationTabObject->LoadToolbar();

        $_workflowTitle = '';
        $_workflowIsEnabled = true;
        $_workflowSortOrder = 1;
        $_workflowMatchAll = true;

        $_actionAddTagsList = $_actionRemoveTagsList = array();

        $_workflowNotes = '';
        $_workflowDepartmentID = false;
        $_staffVisibilityCustom = false;
        $_userVisibility = false;
        $_staffGroupIDList = array();

        if ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT_TicketWorkflowObject !== null)
        {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle');
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('delete'), 'fa-trash', '/Tickets/Workflow/Delete/' .
                    $_SWIFT_TicketWorkflowObject->GetTicketWorkflowID(), SWIFT_UserInterfaceToolbar::LINK_CONFIRM, '', '', false);
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('workflow'),
                    SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);
            $_NotificationTabObject->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle');
            $_NotificationTabObject->Toolbar->AddButton($this->Language->Get('insertnotification'), 'fa-bell',
                    'javascript: InsertWorkflowNotification();', SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, '', '', false);
            $_NotificationTabObject->Toolbar->AddButton($this->Language->Get('delete'), 'fa-trash', '/Tickets/Workflow/Delete/' .
                    $_SWIFT_TicketWorkflowObject->GetTicketWorkflowID(), SWIFT_UserInterfaceToolbar::LINK_CONFIRM, '', '', false);
            $_NotificationTabObject->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('workflow'),
                    SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

            $_workflowTitle = $_SWIFT_TicketWorkflowObject->GetProperty('title');
            $_workflowIsEnabled = (int) ($_SWIFT_TicketWorkflowObject->GetProperty('isenabled'));

            if ($_SWIFT_TicketWorkflowObject->GetProperty('ruletype') == SWIFT_Rules::RULE_MATCHALL)
            {
                $_workflowMatchAll = true;
            } else {
                $_workflowMatchAll = false;
            }

            $_workflowSortOrder = (int) ($_SWIFT_TicketWorkflowObject->GetProperty('sortorder'));
            $_staffVisibilityCustom = (int) ($_SWIFT_TicketWorkflowObject->GetProperty('staffvisibilitycustom'));
            $_userVisibility = (int) ($_SWIFT_TicketWorkflowObject->GetProperty('uservisibility'));
            $_staffGroupIDList = $_SWIFT_TicketWorkflowObject->GetLinkedStaffGroupIDList();

            $_workflowParsedActions = self::GetParsedActionsContainer($_SWIFT_TicketWorkflowObject->GetActions());

            if (_is_array($_workflowParsedActions))
            {
                foreach ($_workflowParsedActions as $_key => $_val)
                {
                    if (!isset($_POST[$_key]))
                    {
                        if ($_key === 'addtags') {
                            $_actionAddTagsList = $_val;
                        } else if ($_key === 'removetags') {
                            $_actionRemoveTagsList = $_val;
                        } else {
                            $_POST[$_key] = $_val;

                            if ($_key === 'departmentid') {
                                $_workflowDepartmentID = $_val;
                            }
                        }
                    }
                }
            }

        } else {
            $_sortOrderContainer = $this->Database->QueryFetch("SELECT sortorder FROM " . TABLE_PREFIX . "ticketworkflows ORDER BY sortorder DESC");
            if (isset($_sortOrderContainer['sortorder']))
            {
                $_workflowSortOrder = (int) ($_sortOrderContainer['sortorder'])+1;
            }

            $this->UserInterface->Toolbar->AddButton($this->Language->Get('insert'), 'fa-plus-circle');
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('workflow'),
                    SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);
            $_NotificationTabObject->Toolbar->AddButton($this->Language->Get('insert'), 'fa-plus-circle');
            $_NotificationTabObject->Toolbar->AddButton($this->Language->Get('insertnotification'), 'fa-bell',
                    'javascript: InsertWorkflowNotification();', SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, '', '', false);
            $_NotificationTabObject->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('workflow'),
                    SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);
        }

        /*
         * ###############################################
         * BEGIN GENERAL TAB
         * ###############################################
         */

        $_GeneralTabObject->Text('title', $this->Language->Get('workflowtitle'), $this->Language->Get('desc_workflowtitle'), $_workflowTitle);

        $_GeneralTabObject->YesNo('isenabled', $this->Language->Get('isenabled'), $this->Language->Get('desc_isenabled'), $_workflowIsEnabled);

        $_GeneralTabObject->Number('sortorder', $this->Language->Get('sortorder'), $this->Language->Get('desc_sortorder'), $_workflowSortOrder);

/*        $_optionsContainer = array();
        $_optionsContainer[0]["title"] = $this->Language->Get('smatchall');
        $_optionsContainer[0]["value"] = SWIFT_Rules::RULE_MATCHALL;
        $_optionsContainer[0]["checked"] = IIF($_workflowMatchAll == true, true, false);
        $_optionsContainer[1]["title"] = $this->Language->Get('smatchany');
        $_optionsContainer[1]["value"] =SWIFT_Rules::RULE_MATCHANY;
        $_optionsContainer[1]["checked"] = IIF(!$_workflowMatchAll, true, false);

        $_GeneralTabObject->Radio('ruleoptions', $this->Language->Get('matchtype'), $this->Language->Get('desc_matchtype'), $_optionsContainer);*/

        $_GeneralTabObject->Hidden('ruleoptions', SWIFT_Rules::RULE_MATCHEXTENDED);


        $_defaultTicketStatusID = false;

        foreach ($_ticketStatusCache as $_key => $_val)
        {
            $_defaultTicketStatusID =  ($_key);

            break;
        }

        $_SWIFT_UserInterfaceToolbarObject = new SWIFT_UserInterfaceToolbar($this->UserInterface);
        $_SWIFT_UserInterfaceToolbarObject->AddButton($this->Language->Get('insertcriteria'), 'fa-list-alt', "newGlobalRuleCriteria('ticketstatus', '". SWIFT_Rules::OP_CONTAINS ."', '', '1', '1');", SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, '', '', false);

        $_GeneralTabObject->AppendHTML($_SWIFT_UserInterfaceToolbarObject->Render(true) . '<tr class="' . $_GeneralTabObject->GetClass() . '">
             <td align="left" colspan="2" class="smalltext"><div id="ruleparent"></div></td></tr>');

        /*
         * ###############################################
         * END GENERAL TAB
         * ###############################################
         */





        /*
         * ###############################################
         * BEGIN ACTIONS TAB
         * ###############################################
         */


        // ======= RENDER DEPARTMENT LIST =======
        $_optionsContainer = array();
        $_optionsContainer[0]['title'] = $this->Language->Get('nochange');
        $_optionsContainer[0]['value'] = '0';
        if ($_mode == SWIFT_UserInterface::MODE_INSERT)
        {
            $_optionsContainer[0]['selected'] = true;
        }

        $_index = 1;
        $_departmentOptionsContainer =  SWIFT_Department::GetDepartmentMapOptions($_workflowDepartmentID, APP_TICKETS);
        foreach ($_departmentOptionsContainer as $_key => $_val)
        {
            $_optionsContainer[$_index] = $_val;

            $_index++;
        }

        $_ActionsTabObject->Select('departmentid', $this->Language->Get('actiondepartment'), $this->Language->Get('desc_actiondepartment'),
                $_optionsContainer, 'javascript: UpdateTicketStatusDiv(this, \'ticketstatusid\', true, false); UpdateTicketTypeDiv(this, \'tickettypeid\', true, false); UpdateTicketOwnerDiv(this, \'staffid\', true, false, true);');

        // ======= RENDER STAFF LIST =======
        $_optionsContainer = array();
        $_optionsContainer[0]['title'] = $this->Language->Get('nochange');
        $_optionsContainer[0]['value'] = '-2';
        if ($_mode == SWIFT_UserInterface::MODE_INSERT)
        {
            $_optionsContainer[0]['selected'] = true;
        }

        $_optionsContainer[1]['title'] = $this->Language->Get('unassigned');
        $_optionsContainer[1]['value'] = '0';

        $_optionsContainer[2]['title'] = $this->Language->Get('wfactivestaff');
        $_optionsContainer[2]['value'] = '-1';

        $_index = 3;
        $this->Database->Query("SELECT staffid, fullname FROM " . TABLE_PREFIX . "staff ORDER BY fullname ASC");
        while ($this->Database->NextRecord())
        {
            $_optionsContainer[$_index]['title'] = $this->Database->Record['fullname'];
            $_optionsContainer[$_index]['value'] = $this->Database->Record['staffid'];
            $_index++;
        }
        $_ActionsTabObject->Select('staffid', $this->Language->Get('actionstaff'), $this->Language->Get('desc_actionstaff'), $_optionsContainer, '', 'staffid_container');

        // ======= RENDER TICKET TYPE LIST =======
        $_optionsContainer = array();
        $_optionsContainer[0]['title'] = $this->Language->Get('nochange');
        $_optionsContainer[0]['value'] = '0';
        if ($_mode == SWIFT_UserInterface::MODE_INSERT)
        {
            $_optionsContainer[0]['selected'] = true;
        }

        $_index = 1;
        $this->Database->Query("SELECT tickettypeid, title FROM " . TABLE_PREFIX . "tickettypes WHERE departmentid = '0'" .
                IIF(!empty($_workflowDepartmentID), " OR departmentid = '" . (int) ($_workflowDepartmentID) . "'") . " ORDER BY displayorder ASC");
        while ($this->Database->NextRecord())
        {
            $_optionsContainer[$_index]['title'] = $this->Database->Record['title'];
            $_optionsContainer[$_index]['value'] = $this->Database->Record['tickettypeid'];

            $_index++;
        }

        $_ActionsTabObject->Select('tickettypeid', $this->Language->Get('actiontickettype'), $this->Language->Get('desc_actiontickettype'),
                $_optionsContainer, '', 'tickettypeid_container');

        // ======= RENDER PRIORITY LIST =======
        $_optionsContainer = array();
        $_optionsContainer[0]['title'] = $this->Language->Get('nochange');
        $_optionsContainer[0]['value'] = '0';
        if ($_mode == SWIFT_UserInterface::MODE_INSERT)
        {
            $_optionsContainer[0]['selected'] = true;
        }

        $_index = 1;
        $this->Database->Query("SELECT priorityid, title FROM " . TABLE_PREFIX . "ticketpriorities ORDER BY displayorder ASC");
        while ($this->Database->NextRecord())
        {
            $_optionsContainer[$_index]['title'] = $this->Database->Record['title'];
            $_optionsContainer[$_index]['value'] = $this->Database->Record['priorityid'];

            $_index++;
        }

        $_ActionsTabObject->Select('priorityid', $this->Language->Get('actionpriority'), $this->Language->Get('desc_actionpriority'),
                $_optionsContainer);

        // ======= RENDER TICKET STATUS LIST =======
        $_optionsContainer = array();
        $_optionsContainer[0]['title'] = $this->Language->Get('nochange');
        $_optionsContainer[0]['value'] = '0';
        if ($_mode == SWIFT_UserInterface::MODE_INSERT)
        {
            $_optionsContainer[0]['selected'] = true;
        }

        if (isset($_POST['departmentid']))
        {
            $_workflowDepartmentID = (int) ($_POST['departmentid']);
        } else {
            $_workflowDepartmentID = false;
        }

        $_index = 1;
        $this->Database->Query("SELECT ticketstatusid, title FROM " . TABLE_PREFIX . "ticketstatus WHERE departmentid = '0'" .
                IIF(!empty($_workflowDepartmentID), " OR departmentid = '" . (int) ($_workflowDepartmentID) . "'") . " ORDER BY displayorder ASC");
        while ($this->Database->NextRecord())
        {
            $_optionsContainer[$_index]['title'] = $this->Database->Record['title'];
            $_optionsContainer[$_index]['value'] = $this->Database->Record['ticketstatusid'];

            $_index++;
        }

        $_ActionsTabObject->Select('ticketstatusid', $this->Language->Get('actionticketstatus'), $this->Language->Get('desc_actionticketstatus'),
                $_optionsContainer, '', 'ticketstatusid_container');

        // ======= RENDER SLA PLANS =======
        $_optionsContainer = array();
        $_optionsContainer[0]['title'] = $this->Language->Get('nochange');
        $_optionsContainer[0]['value'] = '0';
        if ($_mode == SWIFT_UserInterface::MODE_INSERT)
        {
            $_optionsContainer[0]['selected'] = true;
        }

        $_index = 1;

        $this->Database->Query("SELECT slaplanid, title, isenabled FROM " . TABLE_PREFIX . "slaplans ORDER BY title ASC");
        while ($this->Database->NextRecord())
        {
            $_optionsContainer[$_index]['title'] = $this->Database->Record['title'];
            $_optionsContainer[$_index]['value'] = $this->Database->Record['slaplanid'];

            /**
             * BUG FIX - Ravi Sharma <ravi.sharma@opencart.com.vn>
             *
             * SWIFT-4430 Disabled SLA plan can be implemented over a ticket manually from 'Edit' tab.
             */
            if ($this->Database->Record['isenabled'] == '0') {
                $_optionsContainer[$_index]['disabled'] = true;
            }

            $_index++;
        }

        $_ActionsTabObject->Select('newslaplanid', $this->Language->Get('actionslaplan'), $this->Language->Get('desc_actionslaplan'),
                $_optionsContainer);

        // ======= RENDER BAYESIAN CATEGORIES =======
        $_optionsContainer = array();
        $_optionsContainer[0]['title'] = $this->Language->Get('nochange');
        $_optionsContainer[0]['value'] = '0';
        if ($_mode == SWIFT_UserInterface::MODE_INSERT)
        {
            $_optionsContainer[0]['selected'] = true;
        }

        $_index = 1;

        $this->Database->Query("SELECT bayescategoryid, category FROM " . TABLE_PREFIX . "bayescategories ORDER BY category ASC");
        while ($this->Database->NextRecord())
        {
            $_optionsContainer[$_index]['title'] = $this->Database->Record['category'];
            $_optionsContainer[$_index]['value'] = $this->Database->Record['bayescategoryid'];

            $_index++;
        }

        $_ActionsTabObject->Select('bayescategoryid', $this->Language->Get('actiontrainbayesian'), $this->Language->Get('desc_actiontrainbayesian'),
                $_optionsContainer);

        // ======= RENDER FLAG TYPES =======
        $_optionsContainer = array();
        $_optionsContainer[0]['title'] = $this->Language->Get('nochange');
        $_optionsContainer[0]['value'] = "0";
        if ($_mode == SWIFT_UserInterface::MODE_INSERT)
        {
            $_optionsContainer[0]['selected'] = true;
        }

        $_index = 1;
        foreach ($this->Controller->TicketFlag->GetFlagList() as $_key => $_val)
        {
            $_optionsContainer[$_index]['title'] = $_val;
            $_optionsContainer[$_index]['value'] = $_key;

            $_index++;
        }

        $_ActionsTabObject->Select('flagtype', $this->Language->Get('actionflagtype'), $this->Language->Get('desc_actionflagtype'),
                $_optionsContainer);

        $_ActionsTabObject->Textarea('notes', $this->Language->Get('actionnotes'), $this->Language->Get('desc_actionnotes'), $_workflowNotes,
                '50', '3');

        // ======= RENDER ADD TAGS =======
        $_ActionsTabObject->TextMultipleAutoComplete('addtags', $this->Language->Get('actionaddtags'),
                $this->Language->Get('desc_actionaddtags'), '/Base/Tags/QuickSearch', $_actionAddTagsList,
                'fa-tags', false, true);

        // ======= RENDER REMOVE TAGS =======
        $_ActionsTabObject->TextMultipleAutoComplete('removetags', $this->Language->Get('actionremovetags'),
                $this->Language->Get('desc_actionremovetags'), '/Base/Tags/QuickSearch', $_actionRemoveTagsList,
                'fa-tags', false, true);

        // ======= RENDER TRASH TICKET =======
        $_ActionsTabObject->YesNo('trashticket', $this->Language->Get('actiontrash'), $this->Language->Get('desc_actiontrash'), false);

        /**
         * New Feature - Werner Garcia <werner.garcia@crossover.com>
         *
         * KAYAKOC-4974 Ability to modify custom fields when triggering a workflow.
         *
         * These are the only custom field groups that make sense to update
         * when triggering a workflow. Livechat, News, KB, etc. need an ID
         * that is not available from a ticket
         */
        $this->Controller->CustomFieldRendererWorkflow->Render(SWIFT_CustomFieldRenderer::TYPE_FIELDS, $_mode, [
            SWIFT_CustomFieldGroup::GROUP_USER,
            SWIFT_CustomFieldGroup::GROUP_STAFFTICKET,
            SWIFT_CustomFieldGroup::GROUP_USERTICKET,
            SWIFT_CustomFieldGroup::GROUP_STAFFUSERTICKET,
        ], $_ActionsTabObject, $_SWIFT_TicketWorkflowObject);
        /*
         * ###############################################
         * END ACTIONS TAB
         * ###############################################
         */



        /*
         * ###############################################
         * BEGIN PERMISSIONS TAB
         * ###############################################
         */
        $_PermissionTabObject = $this->UserInterface->AddTab($this->Language->Get('tabpermissions'), 'icon_settings2.gif', 'permissions');

        $_PermissionTabObject->YesNo('staffvisibilitycustom', $this->Language->Get('staffvisibilitycustom'),
                $this->Language->Get('desc_staffvisibilitycustom'), $_staffVisibilityCustom);
        $_PermissionTabObject->YesNo('uservisibility', $this->Language->Get('uservisibility'),
            $this->Language->Get('desc_uservisibility'), $_userVisibility);
        $_PermissionTabObject->Title($this->Language->Get('staffgroups'), 'doublearrows.gif');

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

            $_PermissionTabObject->YesNo('staffgroupidlist[' . (int) ($this->Database->Record['staffgroupid']) . ']',
                    htmlspecialchars($this->Database->Record['title']), '', $_isSelected);

            $_index++;
        }

        /*
         * ###############################################
         * END PERMISSIONS TAB
         * ###############################################
         */


        /*
         * ###############################################
         * BEGIN NOTIFICATIONS TAB
         * ###############################################
         */

        $_notificationHTML = '';
        $_index = 0;

        if ($_mode == SWIFT_UserInterface::MODE_EDIT)
        {
            $_notificationContainer = SWIFT_TicketWorkflowNotification::RetrieveOnWorkflow($_SWIFT_TicketWorkflowObject->GetTicketWorkflowID());

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

                    foreach (SWIFT_TicketWorkflowNotification::GetTypeList() as $_subKey => $_subVal)
                    {
                        $_resultHTML .= '<option value="' . $_subKey . '"' . IIF($_val['notificationtype'] == $_subKey, ' selected') . '>' .
                                $_subVal . '</option>';
                    }

                    $_resultHTML .= '</select></td></tr></table><table border="0" cellpadding="3" cellspacing="1" width="100%"><tr>
                        <td align="left" valign="top" width="130"><b>' . $this->Language->Get('notificationsubject') . '</b></td>
                            <td align="left" valign="top" width=""><input type="text" name="notifications[' . $_index . '][1]"
                                class="swifttext" style="width: 99%;" value="' . htmlspecialchars($_val['subject']) . '" /></td></tr>
                                    <tr><td align="left" valign="top" colspan="2"><textarea class="swifttext"
                                    name="notifications[' . $_index . '][2]" rows="15"
                                        style="width: 99%;">' . htmlspecialchars($_val['notificationcontents']) . '</textarea></td></tr></table>';

                    $_notificationHTML .= '<div class="' . $_rowClass . '" id="' . $_rowID . '">' . $_resultHTML . '</div>';

                    $_index++;
                }
            }
        }

        $_appendHTML = '<tr class="gridrow2"><td align="left" colspan="2" class="smalltext"><div id="notificationparent">' . $_notificationHTML .
        '</div></td></tr>';
        $_NotificationTabObject->AppendHTML($_appendHTML . '<script type="text/javascript">QueueFunction(function(){ globalRuleSecondaryIndex = ' .
                 ($_index) . '; });</script>');

        /*
         * ###############################################
         * END NOTIFICATIONS TAB
         * ###############################################
         */


        $this->UserInterface->End();

        if ($_mode == SWIFT_UserInterface::MODE_INSERT && !isset($_POST['rulecriteria']))
        {
            echo '<script language="Javascript" type="text/javascript">QueueFunction(function(){newGlobalRuleCriteria(\'ticketstatus\', \''.
            SWIFT_Rules::OP_CONTAINS .'\', \'' . $_defaultTicketStatusID . '\', \'1\', \'1\');});</script>';
        }

        return true;
    }

    /**
     * Render the Visitor Ban Grid
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public function RenderGrid()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            return false;
        }

        $this->Load->Library('UserInterface:UserInterfaceGrid', array('ticketworkflowgrid'), true, false, 'base');

        if ($this->UserInterfaceGrid->GetMode() == SWIFT_UserInterfaceGrid::MODE_SEARCH)
        {
            $this->UserInterfaceGrid->SetSearchQuery('SELECT * FROM ' . TABLE_PREFIX . 'ticketworkflows WHERE (' .
                    $this->UserInterfaceGrid->BuildSQLSearch('title') . ')', 'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX .
                    'ticketworkflows WHERE (' . $this->UserInterfaceGrid->BuildSQLSearch('title') . ')');
        }

        $this->UserInterfaceGrid->SetQuery('SELECT * FROM ' . TABLE_PREFIX . 'ticketworkflows', 'SELECT COUNT(*) FROM ' . TABLE_PREFIX .
                'ticketworkflows');

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('ticketworkflowid', 'ticketworkflowid',
                SWIFT_UserInterfaceGridField::TYPE_ID));

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('icon', '&nbsp;', SWIFT_UserInterfaceGridField::TYPE_CUSTOM, 16,
                SWIFT_UserInterfaceGridField::ALIGN_CENTER));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('title', $this->Language->Get('workflowtitle'),
                SWIFT_UserInterfaceGridField::TYPE_DB, 0, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('sortorder', $this->Language->Get('sortorder'),
                SWIFT_UserInterfaceGridField::TYPE_DB, 120, SWIFT_UserInterfaceGridField::ALIGN_CENTER, SWIFT_UserInterfaceGridField::SORT_ASC),
                true);
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('dateline', $this->Language->Get('creationdate'),
                SWIFT_UserInterfaceGridField::TYPE_DB, 200, SWIFT_UserInterfaceGridField::ALIGN_LEFT));

        $this->UserInterfaceGrid->SetRenderCallback(array($this, 'GridRender'));
        $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('delete'), 'fa-trash',
                array('Tickets\Admin\Controller_Workflow', 'DeleteList'), $this->Language->Get('actionconfirm')));
        $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('enable'), 'fa-check-circle',
                array('Tickets\Admin\Controller_Workflow', 'EnableList'), $this->Language->Get('actionconfirm')));
        $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('disable'), 'fa-minus-circle',
                array('Tickets\Admin\Controller_Workflow', 'DisableList'), $this->Language->Get('actionconfirm')));

        $this->UserInterfaceGrid->SetNewLinkViewport(SWIFT::Get('basename') . '/Tickets/Workflow/Insert');

        if ($_SWIFT->Staff->GetPermission('admin_tcanupdateworkflow') != '0')
        {
//            $this->UserInterfaceGrid->SetSortableCallback('sortorder', array('Controller_Workflow', 'SortList'));
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
     * @return array "true" on Success, "false" otherwise
     */
    public static function GridRender($_fieldContainer)
    {
        $_workflowIcon = 'icon_workflow.gif';
        if ($_fieldContainer['isenabled'] == '0') {
            $_workflowIcon = 'icon_block.gif';
        }

        $_fieldContainer['icon'] = '<img src="' . SWIFT::Get('themepath') . 'images/' . $_workflowIcon . '" align="absmiddle" border="0" />';

        $_fieldContainer['title'] = '<a href="' . SWIFT::Get('basename') . '/Tickets/Workflow/Edit/' . (int) ($_fieldContainer['ticketworkflowid']) . '" viewport="1">' . htmlspecialchars($_fieldContainer['title']) . '</a>';

        $_fieldContainer['dateline'] = SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_fieldContainer['dateline']);

        return $_fieldContainer;
    }

    /**
     * Processes the Rule Actions and converts them into form variables
     *
     * @author Varun Shoor
     * @param array $_actionsContainer The Actions Container Array
     * @return mixed "_containerArray" (ARRAY) on Success, "false" otherwise
     */
    protected static function GetParsedActionsContainer($_actionsContainer)
    {
        if (!_is_array($_actionsContainer))
        {
            return false;
        }

        $_containerArray = array();

        foreach ($_actionsContainer as $_key => $_val)
        {
            if ($_val['name'] == SWIFT_TicketWorkflow::ACTION_DEPARTMENT) {
                $_containerArray['departmentid'] = (int) ($_val['typeid']);

            } else if ($_val['name'] == SWIFT_TicketWorkflow::ACTION_STATUS) {
                $_containerArray['ticketstatusid'] = (int) ($_val['typeid']);

            } else if ($_val['name'] == SWIFT_TicketWorkflow::ACTION_PRIORITY) {
                $_containerArray['priorityid'] = (int) ($_val['typeid']);

            } else if ($_val['name'] == SWIFT_TicketWorkflow::ACTION_OWNER) {
                $_containerArray['staffid'] = (int) ($_val['typeid']);

            } else if ($_val['name'] == SWIFT_TicketWorkflow::ACTION_FLAGTICKET) {
                $_containerArray['flagtype'] = (int) ($_val['typeid']);

            } else if ($_val['name'] == SWIFT_TicketWorkflow::ACTION_SLAPLAN) {
                $_containerArray['newslaplanid'] = (int) ($_val['typeid']);

            } else if ($_val['name'] == SWIFT_TicketWorkflow::ACTION_BAYESIAN) {
                $_containerArray['bayescategoryid'] = (int) ($_val['typeid']);

            } else if ($_val['name'] == SWIFT_TicketWorkflow::ACTION_TRASH) {
                $_containerArray['trashticket'] = (int) ($_val['typeid']);

            } else if ($_val['name'] == SWIFT_TicketWorkflow::ACTION_ADDNOTE) {
                $_containerArray['notes'] = $_val['typedata'];

            } else if ($_val['name'] == SWIFT_TicketWorkflow::ACTION_TICKETTYPE) {
                $_containerArray['tickettypeid'] = $_val['typeid'];

            } else if ($_val['name'] == SWIFT_TicketWorkflow::ACTION_ADDTAGS) {
                $_containerArray['addtags'] = json_decode($_val['typedata']);

            } else if ($_val['name'] == SWIFT_TicketWorkflow::ACTION_REMOVETAGS) {
                $_containerArray['removetags'] = json_decode($_val['typedata']);

            }
        }

        return $_containerArray;
    }

    /**
     * Processes the Rule Actions and converts them into string representation
     *
     * @author Varun Shoor
     * @param array $_action The Action to Process
     * @return mixed "Action HTML" (STRING) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ReturnRuleActionString($_action)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->Cache->Queue('departmentcache', 'statuscache', 'prioritycache', 'staffcache', 'slaplancache', 'tickettypecache');
        $this->Cache->LoadQueue();

        $_departmentCache = $this->Cache->Get('departmentcache');
        $_ticketStatusCache = $this->Cache->Get('statuscache');
        $_ticketPriorityCache = $this->Cache->Get('prioritycache');
        $_staffCache = $this->Cache->Get('staffcache');
        $_slaPlanCache = $this->Cache->Get('slaplancache');
        $_ticketTypeCache = $this->Cache->Get('tickettypecache');
        $_bayesCategoryCache = $this->Cache->Get('bayesiancategorycache');
        $_flagList = $this->Controller->TicketFlag->GetFlagList();

        if ($_action['name'] == SWIFT_TicketWorkflow::ACTION_DEPARTMENT && isset($_departmentCache[$_action['typeid']])) {
            return '&nbsp;&nbsp;<img src="' . SWIFT::Get('themepath') . 'images/linkdownarrow_blue.gif" align="absmiddle" border="0" /> ' .
            $this->Language->Get('wfadepartment') . ": " . text_to_html_entities($_departmentCache[$_action['typeid']]['title']) . '<br />';
        }

        if ($_action['name'] == SWIFT_TicketWorkflow::ACTION_STATUS && isset($_ticketStatusCache[$_action['typeid']])) {
            return '&nbsp;&nbsp;<img src="' . SWIFT::Get('themepath') . 'images/linkdownarrow_blue.gif" align="absmiddle" border="0" /> ' .
            $this->Language->Get('wfaticketstatus') . ": " . htmlspecialchars($_ticketStatusCache[$_action['typeid']]['title']).'<br />';
        }

        if ($_action['name'] == SWIFT_TicketWorkflow::ACTION_PRIORITY && isset($_ticketPriorityCache[$_action['typeid']])) {
            return '&nbsp;&nbsp;<img src="' . SWIFT::Get('themepath') . 'images/linkdownarrow_blue.gif" align="absmiddle" border="0" /> ' .
            $this->Language->Get('wfapriority') . ': ' . htmlspecialchars($_ticketPriorityCache[$_action['typeid']]['title']) . '<br />';
        }

        if ($_action['name'] == SWIFT_TicketWorkflow::ACTION_OWNER && isset($_staffCache[$_action['typeid']])) {
            return '&nbsp;&nbsp;<img src="' . SWIFT::Get('themepath') . 'images/linkdownarrow_blue.gif" align="absmiddle" border="0" /> ' .
            $this->Language->Get('wfastaff') . ': ' . text_to_html_entities($_staffCache[$_action['typeid']]['fullname']) . '<br />';
        }

        if (($_action['name'] == SWIFT_TicketWorkflow::ACTION_OWNER && $_action['typeid'] == -1) || $_action['typeid'] == 0) {
            return '&nbsp;&nbsp;<img src="' . SWIFT::Get('themepath') . 'images/linkdownarrow_blue.gif" align="absmiddle" border="0" /> ' .
            $this->Language->Get('wfastaff') . ': ' . htmlspecialchars(IIF($_action['typeid'] == -1, $this->Language->Get('wfactivestaff'),
                    $this->Language->Get('unassigned'))) . '<br />';
        }

        if ($_action['name'] == SWIFT_TicketWorkflow::ACTION_TICKETTYPE && isset($_ticketTypeCache[$_action['typeid']])) {
            return '&nbsp;&nbsp;<img src="' . SWIFT::Get('themepath') . 'images/linkdownarrow_blue.gif" align="absmiddle" border="0" /> ' .
            $this->Language->Get('wfatickettype') . ': ' . htmlspecialchars($_ticketTypeCache[$_action['typeid']]['title']) . '<br />';
        }

        if ($_action['name'] == SWIFT_TicketWorkflow::ACTION_ADDTAGS) {
            return '&nbsp;&nbsp;<img src="' . SWIFT::Get('themepath') . 'images/linkdownarrow_blue.gif" align="absmiddle" border="0" /> ' .
            $this->Language->Get('wfaaddtags') . ': ' . implode(', ', mb_unserialize($_action['typedata'])) . '<br />';
        }

        if ($_action['name'] == SWIFT_TicketWorkflow::ACTION_REMOVETAGS) {
            return '&nbsp;&nbsp;<img src="' . SWIFT::Get('themepath') . 'images/linkdownarrow_blue.gif" align="absmiddle" border="0" /> ' .
            $this->Language->Get('wfaremovetags') . ': ' . implode(', ', mb_unserialize($_action['typedata'])) . '<br />';
        }

        if ($_action['name'] == SWIFT_TicketWorkflow::ACTION_FLAGTICKET) {
            $_returnData = '&nbsp;&nbsp;<img src="' . SWIFT::Get('themepath') . 'images/linkdownarrow_blue.gif" align="absmiddle" border="0" /> ' .
            $this->Language->Get('wfaflag') . ": ";
            if (isset($_flagList[$_action['typeid']]))
            {
                $_returnData .= $_flagList[$_action['typeid']];
            } else {
                $_returnData .= $this->Language->Get('nochange');
            }

            return $_returnData . '<br />';
        }

        if ($_action['name'] == SWIFT_TicketWorkflow::ACTION_SLAPLAN && isset($_slaPlanCache[$_action['typeid']])) {
            return '&nbsp;&nbsp;<img src="' . SWIFT::Get('themepath') . 'images/linkdownarrow_blue.gif" align="absmiddle" border="0" /> ' .
            $this->Language->Get('wfaslaplan') . ": " . htmlspecialchars($_slaPlanCache[$_action['typeid']]['title']) . '<br />';
        }

        if ($_action['name'] == SWIFT_TicketWorkflow::ACTION_BAYESIAN && isset($_bayesCategoryCache[$_action['typeid']])) {
            return '&nbsp;&nbsp;<img src="' . SWIFT::Get('themepath') . 'images/linkdownarrow_blue.gif" align="absmiddle" border="0" /> ' .
            $this->Language->Get('wfabayesian') . ": " . htmlspecialchars($_bayesCategoryCache[$_action['typeid']]['category']) . '<br />';
        }

        if ($_action['name'] == SWIFT_TicketWorkflow::ACTION_TRASH && $_action['typeid'] == '1') {
            return '&nbsp;&nbsp;<img src="' . SWIFT::Get('themepath') . 'images/linkdownarrow_blue.gif" align="absmiddle" border="0" /> ' .
            $this->Language->Get('wfatrash') . ": " . $this->Language->Get('yes') . '<br />';
        }

        if ($_action['name'] == SWIFT_TicketWorkflow::ACTION_ADDNOTE) {
            return '&nbsp;&nbsp;<img src="' . SWIFT::Get('themepath') . 'images/linkdownarrow_blue.gif" align="absmiddle" border="0" /> ' .
            $this->Language->Get('wfaddnotes') . ": " . nl2br(htmlspecialchars($_action['typedata'])) . '<br />';
        }

        return false;
    }
}
