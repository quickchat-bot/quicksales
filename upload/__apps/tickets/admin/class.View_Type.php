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
use Base\Models\Department\SWIFT_Department;
use SWIFT_Exception;
use Base\Library\Help\SWIFT_Help;
use Base\Library\Language\SWIFT_LanguagePhraseLinked;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceGrid;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridField;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridMassAction;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;
use SWIFT_View;
use Tickets\Models\Type\SWIFT_TicketType;

/**
 * The Ticket Type View
 *
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @property Controller_Type $Controller
 * @author Varun Shoor
 */
class View_Type extends SWIFT_View
{
    /**
     * Render the Ticket Type Form
     *
     * @author Varun Shoor
     * @param int $_mode The Render Mode
     * @param SWIFT_TicketType $_SWIFT_TicketTypeObject The SWIFT_TicketType Object Pointer (Only for EDIT Mode)
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public function Render($_mode, SWIFT_TicketType $_SWIFT_TicketTypeObject = null)
    {
        if (!$this->GetIsClassLoaded())
        {
            return false;
        }

        // Calculate the URL
        if ($_mode == SWIFT_UserInterface::MODE_EDIT)
        {
            $this->UserInterface->Start(get_short_class($this),'/Tickets/Type/EditSubmit/'. $_SWIFT_TicketTypeObject->GetTicketTypeID(),
                    SWIFT_UserInterface::MODE_EDIT, true, true);
        } else {
            $this->UserInterface->Start(get_short_class($this),'/Tickets/Type/InsertSubmit', SWIFT_UserInterface::MODE_INSERT, false, true);
        }

        $_ticketTypeTitle = '';
        $_displayOrder = SWIFT_TicketType::GetLastDisplayOrder();
        $_ticketTypeVisibility = SWIFT_PUBLIC;
        $_departmentID = false;
        $_displayIcon = '';
        $_userVisibilityCustom = false;
        $_userGroupIDList = array();

        $_ticketTypeID = false;

        if ($_mode == SWIFT_UserInterface::MODE_EDIT)
        {
            if (isset($_POST['_isDialog']) && $_POST['_isDialog'] == 1)
            {
                $this->UserInterface->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle');
            }

            $this->UserInterface->Toolbar->AddButton($this->Language->Get('delete'), 'fa-trash', '/Tickets/Type/Delete/' .
                    $_SWIFT_TicketTypeObject->GetTicketTypeID(), SWIFT_UserInterfaceToolbar::LINK_CONFIRM, '', '', false);
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('tickettype'),
                    SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

            $_ticketTypeTitle = $_SWIFT_TicketTypeObject->GetProperty('title');
            $_ticketTypeVisibility = $_SWIFT_TicketTypeObject->GetProperty('type');
            $_displayIcon = $_SWIFT_TicketTypeObject->GetProperty('displayicon');
            $_displayOrder = (int) ($_SWIFT_TicketTypeObject->GetProperty('displayorder'));
            $_departmentID = (int) ($_SWIFT_TicketTypeObject->GetProperty('departmentid'));

            $_userVisibilityCustom = (int) ($_SWIFT_TicketTypeObject->GetProperty('uservisibilitycustom'));
            $_userGroupIDList = $_SWIFT_TicketTypeObject->GetLinkedUserGroupIDList();

            $_ticketTypeID = (int) ($_SWIFT_TicketTypeObject->GetTicketTypeID());

        } else {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('insert'), 'fa-plus-circle');
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('tickettype'),
                    SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);
        }



        /*
         * ###############################################
         * BEGIN GENERAL TAB
         * ###############################################
         */
        $_GeneralTabObject = $this->UserInterface->AddTab($this->Language->Get('tabgeneral'), 'icon_form.gif', 'general', true);

        $_GeneralTabObject->Overflow('430');


        $_GeneralTabObject->Text('title', $this->Language->Get('typetitle'), $this->Language->Get('desc_typetitle'), $_ticketTypeTitle);

        $_GeneralTabObject->URLAndUpload('displayicon', $this->Language->Get('displayicontype'), $this->Language->Get('desc_displayicontype'),
                $_displayIcon);

        $_GeneralTabObject->Number('displayorder', $this->Language->Get('displayorder'), $this->Language->Get('desc_displayorder'), $_displayOrder);

        $_optionsContainer = array();
        $_index = 1;
        $_optionsContainer[0]['title'] = $this->Language->Get('typealldep');
        $_optionsContainer[0]['value'] = 0;
        $_optionsContainer[0]['selected'] = IIF(!$_departmentID, true, false);

        $_departmentMapOptions =  SWIFT_Department::GetDepartmentMapOptions($_departmentID, APP_TICKETS);

        foreach ($_departmentMapOptions as $_key => $_val)
        {
            $_optionsContainer[$_index] = $_val;

            $_index++;
        }

        $_GeneralTabObject->Select('departmentid', $this->Language->Get('typedepartment'), $this->Language->Get('desc_typedepartment'),
                $_optionsContainer);

        $_GeneralTabObject->PublicPrivate('type', $this->Language->Get('typevisibility2'), $this->Language->Get('desc_typevisibility2'),
                IIF($_ticketTypeVisibility == SWIFT_PUBLIC, true, false));

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
        $this->Controller->LanguagePhraseLinked->Render(SWIFT_LanguagePhraseLinked::TYPE_TICKETTYPE,
                $_ticketTypeID, $_mode, $_LanguageTabObject);

        /*
         * ###############################################
         * END LANGUAGES TAB
         * ###############################################
         */

        $this->UserInterface->End();

        return true;
    }

    /**
     * Render the Ticket Type Grid
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

        $this->Load->Library('UserInterface:UserInterfaceGrid', array('tickettypegrid'), true, false, 'base');

        if ($this->UserInterfaceGrid->GetMode() == SWIFT_UserInterfaceGrid::MODE_SEARCH)
        {
            $this->UserInterfaceGrid->SetSearchQuery('SELECT * FROM ' . TABLE_PREFIX . 'tickettypes WHERE (' .
                    $this->UserInterfaceGrid->BuildSQLSearch('title') . ')', 'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX .
                    'tickettypes WHERE (' . $this->UserInterfaceGrid->BuildSQLSearch('title') . ')');
        }

        $this->UserInterfaceGrid->SetQuery('SELECT * FROM ' . TABLE_PREFIX . 'tickettypes', 'SELECT COUNT(*) AS totalitems FROM ' .
                TABLE_PREFIX . 'tickettypes');

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('tickettypeid', 'tickettypeid', SWIFT_UserInterfaceGridField::TYPE_ID));

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('icon', '&nbsp;', SWIFT_UserInterfaceGridField::TYPE_CUSTOM, 16,
                SWIFT_UserInterfaceGridField::ALIGN_CENTER));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('title', $this->Language->Get('typetitle'),
                SWIFT_UserInterfaceGridField::TYPE_DB, 0, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('type', $this->Language->Get('typevisibility'),
                SWIFT_UserInterfaceGridField::TYPE_DB, 160, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('displayorder', $this->Language->Get('order'),
                SWIFT_UserInterfaceGridField::TYPE_DB, 80, SWIFT_UserInterfaceGridField::ALIGN_CENTER, SWIFT_UserInterfaceGridField::SORT_ASC),
                true);

        $this->UserInterfaceGrid->SetRenderCallback(array($this, 'GridRender'));
        $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('delete'), 'fa-trash',
                array('Tickets\Admin\Controller_Type', 'DeleteList'), $this->Language->Get('actionconfirm')));

        $this->UserInterfaceGrid->SetNewLinkViewport(SWIFT::Get('basename') . '/Tickets/Type/Insert');

        if ($_SWIFT->Staff->GetPermission('admin_tcanupdatetype') != '0')
        {
            $this->UserInterfaceGrid->SetSortableCallback('displayorder', array('Tickets\Admin\Controller_Type', 'SortList'));
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

        $_fieldContainer['title'] = '<a href="' . SWIFT::Get('basename') . '/Tickets/Type/Edit/' . (int) ($_fieldContainer['tickettypeid']) . '" onclick="' . "javascript: return UICreateWindowExtended(event, '" . SWIFT::Get('basename') . '/Tickets/Type/Edit/' . (int) ($_fieldContainer['tickettypeid']) . "', 'edittickettype', '" . sprintf($_SWIFT->Language->Get('winedittickettype'), addslashes(htmlspecialchars($_fieldContainer['title']))) . "', '" . $_SWIFT->Language->Get('loadingwindow') . "', 800, 610, true, this);" .
                '" title="' . $_SWIFT->Language->Get('edit') . '">' . IIF($_fieldContainer['ismaster']==1, '<i>') .
                htmlspecialchars($_fieldContainer['title']) . IIF($_fieldContainer['ismaster']==1, '</i>') . '</a>';

        $_fieldContainer['type'] = IIF($_fieldContainer['type'] == SWIFT_PUBLIC, $_SWIFT->Language->Get('public'), $_SWIFT->Language->Get('private'));

        $_fieldContainer['icon'] = IIF(empty($_fieldContainer['displayicon']), '<img src="' . SWIFT::Get('themepath') .
                'images/space.gif" align="absmiddle" width="16" height="16" border="0" />', '<img src="' .
                str_replace('{$themepath}', SWIFT::Get('themepath') . 'images/', $_fieldContainer['displayicon']) .
                '" align="absmiddle" border="0" />');

        return $_fieldContainer;
    }
}
