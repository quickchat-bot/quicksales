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

namespace Base\Staff;

use Base\Library\Help\SWIFT_Help;
use Base\Library\Rules\SWIFT_Rules;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceGrid;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridField;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridMassAction;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;
use Base\Models\Notification\SWIFT_NotificationAction;
use Base\Models\Notification\SWIFT_NotificationRule;
use SWIFT;
use SWIFT_View;

/**
 * The Notification View
 *
 * @author Varun Shoor
 */
class View_Notification extends SWIFT_View
{
    /**
     * Render the Notification Form
     *
     * @author Varun Shoor
     * @param int $_mode The Render Mode
     * @param SWIFT_NotificationRule $_SWIFT_NotificationRuleObject The SWIFT_NotificationRule Object Pointer (Only for EDIT Mode)
     * @return bool "true" on Success, "false" otherwise
     */
    public function Render($_mode, SWIFT_NotificationRule $_SWIFT_NotificationRuleObject = null)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $_staffCache = $this->Cache->Get('staffcache');

        $_ruleType = false;
        if ($_mode == SWIFT_UserInterface::MODE_INSERT) {
            $_ruleType = $_POST['ruletype'];
        } else {
            $_ruleType = $_SWIFT_NotificationRuleObject->GetProperty('ruletype');
        }

        $_actionsContainer = array();
        if ($_SWIFT_NotificationRuleObject instanceof SWIFT_NotificationRule) {
            $_actionsContainer = SWIFT_NotificationAction::RetrieveOnNotificationRule($_SWIFT_NotificationRuleObject->GetNotificationRuleID());
        }

        $_criteriaPointer = SWIFT_NotificationRule::GetCriteriaPointer($_ruleType);

        SWIFT_NotificationRule::ExtendCustomCriteria($_ruleType, $_criteriaPointer);
        SWIFT_Rules::CriteriaPointerToJavaScriptArray($_criteriaPointer);

        if (isset($_POST['rulecriteria'])) {
            SWIFT_NotificationRule::CriteriaActionsPointerToJavaScript($_POST['rulecriteria'], false);
        }

        // Calculate the URL
        if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            $this->UserInterface->Start(get_short_class($this), '/Base/Notification/EditSubmit/' . $_SWIFT_NotificationRuleObject->GetNotificationRuleID(),
                SWIFT_UserInterface::MODE_EDIT, false);
        } else {
            $this->UserInterface->Start(get_short_class($this), '/Base/Notification/InsertSubmit', SWIFT_UserInterface::MODE_INSERT, false);
        }

        // Tabs
        $_GeneralTabObject = $this->UserInterface->AddTab($this->Language->Get('tabgeneral'), 'icon_form.gif', 'general', true);

        $_ruleTitle = '';
        $_emailPrefix = '';
        $_ruleIsEnabled = true;

        if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle');
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('delete'), 'fa-trash', '/Base/Notification/Delete/' .
                $_SWIFT_NotificationRuleObject->GetNotificationRuleID(), SWIFT_UserInterfaceToolbar::LINK_CONFIRM, '', '', false);
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('notifications'),
                SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

            $_ruleTitle = $_SWIFT_NotificationRuleObject->GetProperty('title');
            $_emailPrefix = $_SWIFT_NotificationRuleObject->GetProperty('emailprefix');
            $_ruleIsEnabled = (int)($_SWIFT_NotificationRuleObject->GetProperty('isenabled'));

        } else {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('insert'), 'fa-plus-circle');
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('notifications'),
                SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        }

        $_fieldName = '';
        $_fieldValue = '';

        if ($_ruleType == SWIFT_NotificationRule::TYPE_TICKET) {
            $_fieldValue = 'newticket';
            $_fieldName = 'ticketevent';
        } else if ($_ruleType == SWIFT_NotificationRule::TYPE_USER) {
            $_fieldValue = 'newuser';
            $_fieldName = 'userevent';
        }

        /*
         * ###############################################
         * BEGIN GENERAL TAB
         * ###############################################
         */

        $_GeneralTabObject->Text('title', $this->Language->Get('ruletitle'), $this->Language->Get('desc_ruletitle'), $_ruleTitle);

        $_GeneralTabObject->YesNo('isenabled', $this->Language->Get('isenabled'), $this->Language->Get('desc_isenabled'), $_ruleIsEnabled);

        $_SWIFT_UserInterfaceToolbarObject = new SWIFT_UserInterfaceToolbar($this->UserInterface);
        $_SWIFT_UserInterfaceToolbarObject->AddButton($this->Language->Get('insertcriteria'), 'icon_insertcriteria.gif',
            "newGlobalRuleCriteria('" . $_fieldName . "', '" . SWIFT_Rules::OP_EQUAL . "', '" . $_fieldValue . "', '1', '1');", SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, '', '', false);

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
        $_EmailTabObject = $this->UserInterface->AddTab($this->Language->Get('tabemail'), 'icon_email.gif', 'email');

        $_EmailTabObject->Text('emailprefix', $this->Language->Get('emailprefix'), $this->Language->Get('desc_emailprefix'), $_emailPrefix);

        $_EmailTabObject->Title($this->Language->Get('na_email'), 'icon_doublearrows.gif');

        if ($_ruleType == SWIFT_NotificationRule::TYPE_TICKET) {
            $_checkedContainer = array();
            $_checkedContainer[SWIFT_NotificationAction::ACTION_EMAILSTAFF] = false;
            $_checkedContainer[SWIFT_NotificationAction::ACTION_EMAILSTAFFGROUP] = false;
            $_checkedContainer[SWIFT_NotificationAction::ACTION_EMAILDEPARTMENT] = false;
            $_checkedContainer[SWIFT_NotificationAction::ACTION_EMAILUSER] = false;

            foreach (array(SWIFT_NotificationAction::ACTION_EMAILSTAFF, SWIFT_NotificationAction::ACTION_EMAILSTAFFGROUP, SWIFT_NotificationAction::ACTION_EMAILDEPARTMENT, SWIFT_NotificationAction::ACTION_EMAILUSER) as $_actionType) {
                if (isset($_actionsContainer[$_actionType])) {
                    $_checkedContainer[$_actionType] = true;
                }
            }

            $_checkboxContainer = array();
            $_checkboxContainer[] = array('title' => $this->Language->Get('na_staff'), 'checked' => $_checkedContainer[SWIFT_NotificationAction::ACTION_EMAILSTAFF], 'value' => SWIFT_NotificationAction::ACTION_EMAILSTAFF, 'icon' => SWIFT::Get('themepathimages') . 'icon_staffuser.gif');
            $_checkboxContainer[] = array('title' => $this->Language->Get('na_staffgroup'), 'checked' => $_checkedContainer[SWIFT_NotificationAction::ACTION_EMAILSTAFFGROUP], 'value' => SWIFT_NotificationAction::ACTION_EMAILSTAFFGROUP, 'icon' => SWIFT::Get('themepathimages') . 'icon_staffuser.gif');
            $_checkboxContainer[] = array('title' => $this->Language->Get('na_department'), 'checked' => $_checkedContainer[SWIFT_NotificationAction::ACTION_EMAILDEPARTMENT], 'value' => SWIFT_NotificationAction::ACTION_EMAILDEPARTMENT, 'icon' => SWIFT::Get('themepathimages') . 'icon_folderyellow3.gif');
            $_checkboxContainer[] = array('title' => $this->Language->Get('na_user'), 'checked' => $_checkedContainer[SWIFT_NotificationAction::ACTION_EMAILUSER], 'value' => SWIFT_NotificationAction::ACTION_EMAILUSER, 'icon' => SWIFT::Get('themepathimages') . 'icon_staffuser.gif');

            $_EmailTabObject->CheckBoxContainerList('emaildispatchlist', $this->Language->Get('nactionemaildispatch'),
                $this->Language->Get('desc_nactionemaildispatch'), $_checkboxContainer);
        }

        $_checkboxContainer = array();
        foreach ($_staffCache as $_staffID => $_staffContainer) {
            $_isChecked = false;
            if (isset($_actionsContainer[SWIFT_NotificationAction::ACTION_EMAILSTAFFCUSTOM]) && in_array($_staffID, $_actionsContainer[SWIFT_NotificationAction::ACTION_EMAILSTAFFCUSTOM])) {
                $_isChecked = true;
            }

            $_checkboxContainer[] = array('title' => $_staffContainer['fullname'], 'checked' => $_isChecked, 'value' => $_staffID, 'icon' => SWIFT::Get('themepathimages') . 'icon_staffuser.gif');
        }

        $_EmailTabObject->CheckBoxContainerList('emaildispatchliststaff', $this->Language->Get('nadispatchemailstaff'),
            $this->Language->Get(''), $_checkboxContainer);

        $_ticketEmailContainer = array();
        if (isset($_actionsContainer[SWIFT_NotificationAction::ACTION_EMAILCUSTOM])) {
            $_ticketEmailContainer = $_actionsContainer[SWIFT_NotificationAction::ACTION_EMAILCUSTOM];
        }

        $_EmailTabObject->TextMultipleAutoComplete('emaildispatchcustom', $this->Language->Get('nadispatchemail'), '', '/Tickets/Ajax/SearchEmail', $_ticketEmailContainer, 'fa-envelope-o', false, false, 2, false, false, false, '', array('containemail' => true));

        /*
         * ###############################################
         * END EMAIL TAB
         * ###############################################
         */


        /*
         * ###############################################
         * BEGIN NOTIFY APP TAB
         * ###############################################
         */
        /*
        $_NotifyAppTabObject = $this->UserInterface->AddTab($this->Language->Get('tabnotifyapp'), 'icon_notification.png', 'notifyapp');

        $_NotifyAppTabObject->Title($this->Language->Get('na_pool'), 'icon_doublearrows.gif');

        if ($_ruleType == SWIFT_NotificationRule::TYPE_TICKET)
        {
            $_checkedContainer = array();
            $_checkedContainer[SWIFT_NotificationAction::ACTION_POOLSTAFF] = false;
            $_checkedContainer[SWIFT_NotificationAction::ACTION_POOLSTAFFGROUP] = false;
            $_checkedContainer[SWIFT_NotificationAction::ACTION_POOLDEPARTMENT] = false;

            foreach (array(SWIFT_NotificationAction::ACTION_POOLSTAFF, SWIFT_NotificationAction::ACTION_POOLSTAFFGROUP, SWIFT_NotificationAction::ACTION_POOLDEPARTMENT) as $_actionType)
            {
                if (isset($_actionsContainer[$_actionType]))
                {
                    $_checkedContainer[$_actionType] = true;
                }
            }

            $_checkboxContainer = array();
            $_checkboxContainer[] = array('title' => $this->Language->Get('na_staff'), 'checked' => $_checkedContainer[SWIFT_NotificationAction::ACTION_POOLSTAFF], 'value' => SWIFT_NotificationAction::ACTION_POOLSTAFF, 'icon' => SWIFT::Get('themepathimages') . 'icon_staffuser.gif');
            $_checkboxContainer[] = array('title' => $this->Language->Get('na_staffgroup'), 'checked' => $_checkedContainer[SWIFT_NotificationAction::ACTION_POOLSTAFFGROUP], 'value' => SWIFT_NotificationAction::ACTION_POOLSTAFFGROUP, 'icon' => SWIFT::Get('themepathimages') . 'icon_staffuser.gif');
            $_checkboxContainer[] = array('title' => $this->Language->Get('na_department'), 'checked' => $_checkedContainer[SWIFT_NotificationAction::ACTION_POOLDEPARTMENT], 'value' => SWIFT_NotificationAction::ACTION_POOLDEPARTMENT, 'icon' => SWIFT::Get('themepathimages') . 'icon_folderyellow3.gif');

            $_NotifyAppTabObject->CheckBoxContainerList('pooldispatchlist', $this->Language->Get('nactionpooldispatch'),
                    $this->Language->Get('desc_nactionpooldispatch'), $_checkboxContainer);
        }

        $_checkboxContainer = array();
        foreach ($_staffCache as $_staffID => $_staffContainer)
        {
            $_isChecked = false;
            if (isset($_actionsContainer[SWIFT_NotificationAction::ACTION_POOLCUSTOM]) && in_array($_staffID, $_actionsContainer[SWIFT_NotificationAction::ACTION_POOLCUSTOM]))
            {
                $_isChecked = true;
            }

            $_checkboxContainer[] = array('title' => $_staffContainer['fullname'], 'checked' => $_isChecked, 'value' => $_staffID, 'icon' => SWIFT::Get('themepathimages') . 'icon_staffuser.gif');
        }
        $_NotifyAppTabObject->CheckBoxContainerList('poolcustomdispatchlist', $this->Language->Get('nactionpoolcustomdispatch'),
                $this->Language->Get('desc_nactionpoolcustomdispatch'), $_checkboxContainer);
        */
        /*
         * ###############################################
         * END NOTIFY APP TAB
         * ###############################################
         */


        $this->UserInterface->Hidden('ruletype', $_ruleType);
        $this->UserInterface->End();


        if ($_mode == SWIFT_UserInterface::MODE_INSERT && !isset($_POST['rulecriteria'])) {
            echo '<script language="Javascript" type="text/javascript">QueueFunction(function(){newGlobalRuleCriteria(\'' . $_fieldName . '\', \'' .
                SWIFT_Rules::OP_EQUAL . '\', \'' . $_fieldValue . '\', \'1\', \'1\');});</script>';
        }

        return true;
    }

    /**
     * Render the Notification Insertion Dialog
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function RenderDialog()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->UserInterface->Start(get_short_class($this), '/Base/Notification/InsertStep2', SWIFT_UserInterface::MODE_INSERT, false);

        $this->UserInterface->Toolbar->AddButton($this->Language->Get('next'), 'fa-chevron-circle-right ');
        $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('notifications'),
            SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);


        /*
         * ###############################################
         * BEGIN GENERAL TAB
         * ###############################################
         */
        $_GeneralTabObject = $this->UserInterface->AddTab($this->Language->Get('tabgeneral'), 'icon_notification.png', 'general', true);

        $_GeneralTabObject->Description($this->Language->Get('notificationruledesc'));

        $_radioContainer = array();
        $_notificationRuleTypeList = SWIFT_NotificationRule::RetrieveRuleTypeList();
        $_index = 0;

        foreach ($_notificationRuleTypeList as $_ruleType) {
            $_radioContainer[$_index]['title'] = SWIFT_NotificationRule::RetrieveTypeLabel($_ruleType);
            $_radioContainer[$_index]['value'] = $_ruleType;

            if ($_index == 0) {
                $_radioContainer[$_index]['checked'] = true;
            }

            $_index++;
        }

        $_GeneralTabObject->Radio('ruletype', $this->Language->Get('ruletype'), $this->Language->Get('desc_ruletype'), $_radioContainer);

        /*
         * ###############################################
         * END GENERAL TAB
         * ###############################################
         */

        $this->UserInterface->End();

        return true;
    }

    /**
     * Render the Notification Rule Grid
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function RenderGrid()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->Load->Library('UserInterface:UserInterfaceGrid', array('notificationrulegrid'), true, false, 'base');

        if ($this->UserInterfaceGrid->GetMode() == SWIFT_UserInterfaceGrid::MODE_SEARCH) {
            $this->UserInterfaceGrid->SetSearchQuery('SELECT * FROM ' . TABLE_PREFIX . 'notificationrules
                WHERE (' . $this->UserInterfaceGrid->BuildSQLSearch('title') . ')',

                'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX . 'notificationrules
                WHERE (' . $this->UserInterfaceGrid->BuildSQLSearch('title') . ')');
        }

        $this->UserInterfaceGrid->SetQuery('SELECT * FROM ' . TABLE_PREFIX . 'notificationrules',
            'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX . 'notificationrules');

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('notificationruleid', 'notificationruleid',
            SWIFT_UserInterfaceGridField::TYPE_ID));

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('icon', '&nbsp;', SWIFT_UserInterfaceGridField::TYPE_CUSTOM, 16,
            SWIFT_UserInterfaceGridField::ALIGN_CENTER));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('title', $this->Language->Get('ruletitle'),
            SWIFT_UserInterfaceGridField::TYPE_DB, 0, SWIFT_UserInterfaceGridField::ALIGN_LEFT, SWIFT_UserInterfaceGridField::SORT_ASC), true);
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('ruletype', $this->Language->Get('ruletype'),
            SWIFT_UserInterfaceGridField::TYPE_DB, 150, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('staffid', $this->Language->Get('creator'),
            SWIFT_UserInterfaceGridField::TYPE_DB, 220, SWIFT_UserInterfaceGridField::ALIGN_LEFT));

        $this->UserInterfaceGrid->SetRenderCallback(array($this, 'GridRender'));
        $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('delete'), 'fa-trash',
            array('Base\Staff\Controller_Notification', 'DeleteList'), $this->Language->Get('actionconfirm')));
        $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('enable'), 'fa-check-circle',
            array('Base\Staff\Controller_Notification', 'EnableList'), $this->Language->Get('actionconfirm')));
        $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('disable'), 'fa-times-circle',
            array('Base\Staff\Controller_Notification', 'DisableList'), $this->Language->Get('actionconfirm')));

        $this->UserInterfaceGrid->SetNewLink("UICreateWindow('" . SWIFT::Get('basename') . "/Base/Notification/Insert', 'insertnotification', '" .
            $this->Language->Get('wininsertnotification') . "', '" . $this->Language->Get('loadingwindow') . "', 500, 340, true, this);");

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

        $_staffCache = $_SWIFT->Cache->Get('staffcache');

        $_icon = 'fa-bell';
        if ($_fieldContainer['isenabled'] == '0') {
            $_icon = 'fa-bell-slash';
        }

        $_fieldContainer['icon'] = '<i class="fa ' . $_icon . '" aria-hidden="true"></i>';

        $_fieldContainer['title'] = '<a href="' . SWIFT::Get('basename') . '/Base/Notification/Edit/' . (int)($_fieldContainer['notificationruleid']) . '" viewport="1" title="' . $_SWIFT->Language->Get('edit') . '">' . htmlspecialchars($_fieldContainer['title']) . '</a>';

        $_fieldContainer['ruletype'] = SWIFT_NotificationRule::RetrieveTypeLabel($_fieldContainer['ruletype']);

        $_staffName = $_SWIFT->Language->Get('na');
        if (isset($_staffCache[$_fieldContainer['staffid']])) {
            $_staffName = text_to_html_entities($_staffCache[$_fieldContainer['staffid']]['fullname']);
        }
        $_fieldContainer['staffid'] = $_staffName;

        return $_fieldContainer;
    }
}

?>
