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
use Base\Library\Language\SWIFT_LanguagePhraseLinked;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceGrid;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridField;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridMassAction;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;
use SWIFT_View;
use Tickets\Models\Priority\SWIFT_TicketPriority;

/**
 * The Ticket Priority View
 *
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @property Controller_Priority $Controller
 * @author Varun Shoor
 */
class View_Priority extends SWIFT_View
{
    /**
     * Render the Ticket Priority Form
     *
     * @author Varun Shoor
     * @param int $_mode The Render Mode
     * @param SWIFT_TicketPriority $_SWIFT_TicketPriorityObject The SWIFT_TicketPriority Object Pointer (Only for EDIT Mode)
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public function Render($_mode, SWIFT_TicketPriority $_SWIFT_TicketPriorityObject = null)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            return false;
        }

        // Calculate the URL
        if ($_mode == SWIFT_UserInterface::MODE_EDIT)
        {
            $this->UserInterface->Start(get_short_class($this),'/Tickets/Priority/EditSubmit/'. $_SWIFT_TicketPriorityObject->GetTicketPriorityID(),
                    SWIFT_UserInterface::MODE_EDIT, true, true);
        } else {
            $this->UserInterface->Start(get_short_class($this),'/Tickets/Priority/InsertSubmit', SWIFT_UserInterface::MODE_INSERT, false, true);
        }

        $_priorityTitle = '';
        $_foregroundColor = '';
        $_backgroundColor = '';
        $_displayOrder = SWIFT_TicketPriority::GetLastDisplayOrder();
        $_priorityType = true;
        $_userVisibilityCustom = false;
        $_userGroupIDList = array();

        $_ticketPriorityID = false;

        if ($_mode == SWIFT_UserInterface::MODE_EDIT)
        {
            if ((isset($_POST['_isDialog']) && $_POST['_isDialog'] == 1) || $this->UserInterface->IsAjax() == false)
            {
                $this->UserInterface->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle');
            }

            $this->UserInterface->Toolbar->AddButton($this->Language->Get('delete'), 'fa-trash', '/Tickets/Priority/Delete/' .
                    $_SWIFT_TicketPriorityObject->GetTicketPriorityID(), SWIFT_UserInterfaceToolbar::LINK_CONFIRM, '', '', false);
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('ticketpriority'),
                    SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

            $_priorityTitle = $_SWIFT_TicketPriorityObject->GetProperty('title');
            $_foregroundColor = $_SWIFT_TicketPriorityObject->GetProperty('frcolorcode');
            $_backgroundColor = $_SWIFT_TicketPriorityObject->GetProperty('bgcolorcode');
            $_displayOrder = (int) ($_SWIFT_TicketPriorityObject->GetProperty('displayorder'));
            $_priorityType = IIF($_SWIFT_TicketPriorityObject->GetProperty('type') == SWIFT_PUBLIC, true, false);

            $_userVisibilityCustom = (int) ($_SWIFT_TicketPriorityObject->GetProperty('uservisibilitycustom'));
            $_userGroupIDList = $_SWIFT_TicketPriorityObject->GetLinkedUserGroupIDList();

            $_ticketPriorityID = (int) ($_SWIFT_TicketPriorityObject->GetTicketPriorityID());

        } else {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('insert'), 'fa-plus-circle');
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('ticketpriority'),
                    SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);
        }



        /*
         * ###############################################
         * BEGIN GENERAL TAB
         * ###############################################
         */
        $_GeneralTabObject = $this->UserInterface->AddTab($this->Language->Get('tabgeneral'), 'icon_form.gif', 'general', true);

        $_GeneralTabObject->Text('title', $this->Language->Get('prioritytitle'), $this->Language->Get('desc_prioritytitle'), $_priorityTitle);

        $_GeneralTabObject->Number('displayorder', $this->Language->Get('displayorder'), $this->Language->Get('desc_displayorder'), $_displayOrder);

        $_GeneralTabObject->PublicPrivate('type', $this->Language->Get('prioritytype'), $this->Language->Get('desc_prioritytype'), $_priorityType);

        $_GeneralTabObject->Color('frcolorcode', $this->Language->Get('forecolor'), $this->Language->Get('desc_forecolor'), $_foregroundColor);
        $_GeneralTabObject->Color('bgcolorcode', $this->Language->Get('bgcolor'), $this->Language->Get('desc_bgcolor'), $_backgroundColor);

        /*
         * ###############################################
         * END GENERAL TAB
         * ###############################################
         */


        /*
         * ###############################################
         * BEGIN PERMISSIONS TAB
         * ###############################################
         */
        $_PermissionTabObject = $this->UserInterface->AddTab($this->Language->Get('tabpermissions'), 'icon_settings2.gif', 'permissions');

        $_PermissionTabObject->Overflow('430');

        $_PermissionTabObject->YesNo('uservisibilitycustom', $this->Language->Get('uservisibilitycustom'),
                $this->Language->Get('desc_uservisibilitycustom'), $_userVisibilityCustom);
        $_PermissionTabObject->Title($this->Language->Get('usergroups'), 'doublearrows.gif');

        $_index = 0;
        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "usergroups ORDER BY title ASC");
        while ($this->Database->NextRecord())
        {
            $_isSelected = false;

            if ($_mode == SWIFT_UserInterface::MODE_INSERT && !$_userGroupIDList)
            {
                $_isSelected = true;
            } else if (_is_array($_userGroupIDList)) {
                if (in_array($this->Database->Record['usergroupid'], $_userGroupIDList))
                {
                    $_isSelected = true;
                }
            }

            $_PermissionTabObject->YesNo('usergroupidlist[' . (int) ($this->Database->Record['usergroupid']) . ']',
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
         * BEGIN LANGUAGES TAB
         * ###############################################
         */

        $_LanguageTabObject = $this->UserInterface->AddTab($this->Language->Get('tablanguages'), 'icon_language2.gif', 'languages');
        $_LanguageTabObject->Overflow('430');
        $this->Controller->LanguagePhraseLinked->Render(SWIFT_LanguagePhraseLinked::TYPE_TICKETPRIORITY,
                $_ticketPriorityID, $_mode, $_LanguageTabObject);

        /*
         * ###############################################
         * END LANGUAGES TAB
         * ###############################################
         */

        $this->UserInterface->End();

        return true;
    }

    /**
     * Render the Ticket Priority Grid
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

        $this->Load->Library('UserInterface:UserInterfaceGrid', array('ticketprioritygrid'), true, false, 'base');

        if ($this->UserInterfaceGrid->GetMode() == SWIFT_UserInterfaceGrid::MODE_SEARCH)
        {
            $this->UserInterfaceGrid->SetSearchQuery('SELECT * FROM ' . TABLE_PREFIX . 'ticketpriorities WHERE (' .
                    $this->UserInterfaceGrid->BuildSQLSearch('title') . ')', 'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX .
                    'ticketpriorities WHERE (' . $this->UserInterfaceGrid->BuildSQLSearch('title') . ')');
        }

        $this->UserInterfaceGrid->SetQuery('SELECT * FROM ' . TABLE_PREFIX . 'ticketpriorities', 'SELECT COUNT(*) AS totalitems FROM ' .
                TABLE_PREFIX . 'ticketpriorities');

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('priorityid', 'priorityid', SWIFT_UserInterfaceGridField::TYPE_ID));

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('title', $this->Language->Get('title'),
                SWIFT_UserInterfaceGridField::TYPE_DB, 0, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('type', $this->Language->Get('prioritytype'),
                SWIFT_UserInterfaceGridField::TYPE_DB, 160, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('displayorder', $this->Language->Get('order'),
                SWIFT_UserInterfaceGridField::TYPE_DB, 80, SWIFT_UserInterfaceGridField::ALIGN_CENTER, SWIFT_UserInterfaceGridField::SORT_ASC), true);

        $this->UserInterfaceGrid->SetRenderCallback(array($this, 'GridRender'));
        $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('delete'), 'fa-trash',
                array('Tickets\Admin\Controller_Priority', 'DeleteList'), $this->Language->Get('actionconfirm')));

        $this->UserInterfaceGrid->SetNewLinkViewport(SWIFT::Get('basename') . '/Tickets/Priority/Insert');

        if ($_SWIFT->Staff->GetPermission('admin_tcanupdatepriority') != '0')
        {
            $this->UserInterfaceGrid->SetSortableCallback('displayorder', array('Tickets\Admin\Controller_Priority', 'SortList'));
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
     * @throws SWIFT_Exception
     */
    public static function GridRender($_fieldContainer)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_fieldContainer['title'] = '<div style="cursor: pointer;" onclick="' . "javascript: UICreateWindow('" . SWIFT::Get('basename') . '/Tickets/Priority/Edit/' . (int) ($_fieldContainer['priorityid']) . "', 'editticketpriority', '" . sprintf($_SWIFT->Language->Get('wineditticketpriority'), htmlspecialchars($_fieldContainer['title'])) . "', '" . $_SWIFT->Language->Get('loadingwindow') . "', 800, 630, true, this);" . '" title="' . $_SWIFT->Language->Get('edit') . '">' .
                IIF(!empty($_fieldContainer['bgcolorcode']), '<table border="0" cellpadding="0" cellspacing="0"><tr><td bgcolor="' .
                        $_fieldContainer['bgcolorcode'] . '">') . '<font color="' . $_fieldContainer["frcolorcode"] . '">' .
                IIF($_fieldContainer['ismaster']==1, '<i>') . htmlspecialchars($_fieldContainer['title']) .
                IIF($_fieldContainer['ismaster']==1, '</i>') . '</font>' . IIF(!empty($_fieldContainer['bgcolorcode']), '</td></tr></table>') .
                '</div>';

        $_fieldContainer['type'] = IIF($_fieldContainer['type'] == SWIFT_PUBLIC, $_SWIFT->Language->Get('public'),
                $_SWIFT->Language->Get('private'));

        return $_fieldContainer;
    }
}
