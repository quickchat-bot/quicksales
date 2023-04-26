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
use Tickets\Models\Status\SWIFT_TicketStatus;

/**
 * The Ticket Status View Class
 *
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @property Controller_Status $Controller
 * @author Varun Shoor
 */
class View_Status extends SWIFT_View
{
    /**
     * Render the Ticket Status Form
     *
     * @author Varun Shoor
     * @param int $_mode The Render Mode
     * @param SWIFT_TicketStatus|null $_SWIFT_TicketStatusObject The SWIFT_TicketStatus Object Pointer (Only for EDIT Mode)
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public function Render($_mode, SWIFT_TicketStatus $_SWIFT_TicketStatusObject = null)
    {
        if (!$this->GetIsClassLoaded())
        {
            return false;
        }

        // Calculate the URL
        if ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT_TicketStatusObject !== null)
        {
            $this->UserInterface->Start(get_short_class($this),'/Tickets/Status/EditSubmit/'. $_SWIFT_TicketStatusObject->GetTicketStatusID(),
                    SWIFT_UserInterface::MODE_EDIT, true, true);
        } else {
            $this->UserInterface->Start(get_short_class($this),'/Tickets/Status/InsertSubmit', SWIFT_UserInterface::MODE_INSERT, false, true);
        }

        $_ticketStatusID = false;

        $_statusTitle = '';
        $_displayIcon = '';
        $_statusColor = '';
        $_statusBackgroundColor = '#36a148';
        $_displayOrder = SWIFT_TicketStatus::GetLastDisplayOrder();
        $_departmentID = false;
        $_markAsResolved = false;
        $_displayCount = false;
        $_statusType = true;
        $_resetDueTime = false;
        $_dispatchNotification = false;
        $_triggerSurvey = false;
        $_staffVisibilityCustom = false;
        $_staffGroupIDList = array();

        if ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT_TicketStatusObject !== null)
        {
            if ((isset($_POST['_isDialog']) && $_POST['_isDialog'] == 1) || $this->UserInterface->IsAjax() == false)
            {
                $this->UserInterface->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle');
            }

            $this->UserInterface->Toolbar->AddButton($this->Language->Get('delete'), 'fa-trash', '/Tickets/Status/Delete/' .
                    $_SWIFT_TicketStatusObject->GetTicketStatusID(), SWIFT_UserInterfaceToolbar::LINK_CONFIRM, '', '', false);
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('ticketstatus'),
                    SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

            $_statusTitle = $_SWIFT_TicketStatusObject->GetProperty('title');
            $_displayIcon = $_SWIFT_TicketStatusObject->GetProperty('displayicon');
            $_statusColor =  $_SWIFT_TicketStatusObject->GetProperty('statuscolor');
            $_statusBackgroundColor = $_SWIFT_TicketStatusObject->GetProperty('statusbgcolor');
            $_displayOrder = (int) ($_SWIFT_TicketStatusObject->GetProperty('displayorder'));
            $_departmentID = (int) ($_SWIFT_TicketStatusObject->GetProperty('departmentid'));
            $_markAsResolved = (int) ($_SWIFT_TicketStatusObject->GetProperty('markasresolved'));
            $_displayCount = (int) ($_SWIFT_TicketStatusObject->GetProperty('displaycount'));
            $_statusType = IIF($_SWIFT_TicketStatusObject->GetProperty('statustype') == SWIFT_PUBLIC, true, false);
            $_resetDueTime = (int) ($_SWIFT_TicketStatusObject->GetProperty('resetduetime'));
            $_dispatchNotification = (int) ($_SWIFT_TicketStatusObject->GetProperty('dispatchnotification'));
            $_triggerSurvey = (int) ($_SWIFT_TicketStatusObject->GetProperty('triggersurvey'));

            $_staffVisibilityCustom = (int) ($_SWIFT_TicketStatusObject->GetProperty('staffvisibilitycustom'));
            $_staffGroupIDList = $_SWIFT_TicketStatusObject->GetLinkedStaffGroupIDList();

            $_ticketStatusID = (int) ($_SWIFT_TicketStatusObject->GetTicketStatusID());

        } else {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('insert'), 'fa-plus-circle');
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('ticketstatus'),
                    SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);
        }



        /*
         * ###############################################
         * BEGIN GENERAL TAB
         * ###############################################
         */
        $_GeneralTabObject = $this->UserInterface->AddTab($this->Language->Get('tabgeneral'), 'icon_form.gif', 'general', true);

        $_GeneralTabObject->Text('title', $this->Language->Get('statustitle'), $this->Language->Get('desc_statustitle'), $_statusTitle);

        $_GeneralTabObject->YesNo('markasresolved', $this->Language->Get('markasresolved'), $this->Language->Get('desc_markasresolved'),
                $_markAsResolved);

        $_GeneralTabObject->URLAndUpload('displayicon', $this->Language->Get('displayiconstatus'), $this->Language->Get('desc_displayiconstatus'),
                $_displayIcon);

        $_GeneralTabObject->Color('statuscolor', $this->Language->Get('statuscolor'), $this->Language->Get('desc_statuscolor'), $_statusColor);

        $_GeneralTabObject->Color('statusbgcolor', $this->Language->Get('statusbgcolor'), $this->Language->Get('desc_statusbgcolor'),
                $_statusBackgroundColor);

        $_GeneralTabObject->Number('displayorder', $this->Language->Get('displayorder'), $this->Language->Get('desc_displayorder'), $_displayOrder);

        /*
         * ###############################################
         * END GENERAL TAB
         * ###############################################
         */



        /*
         * ###############################################
         * BEGIN OPTIONS TAB
         * ###############################################
         */

        $_OptionsTabObject = $this->UserInterface->AddTab($this->Language->Get('taboptions'), 'icon_settings2.gif', 'options');


        $_optionsContainer = array();
        $_index = 1;
        $_optionsContainer[0]['title'] = $this->Language->Get('statusalldep');
        $_optionsContainer[0]['value'] = 0;
        $_optionsContainer[0]['selected'] = IIF(!$_departmentID, true, false);

        $_departmentMapOptions =  SWIFT_Department::GetDepartmentMapOptions($_departmentID, APP_TICKETS);

        foreach ($_departmentMapOptions as $_key => $_val)
        {
            $_optionsContainer[$_index] = $_val;

            $_index++;
        }

        $_OptionsTabObject->Select('departmentid', $this->Language->Get('statusdep'), $this->Language->Get('desc_statusdep'), $_optionsContainer);

        $_OptionsTabObject->YesNo('displaycount', $this->Language->Get('displaycount'), $this->Language->Get('desc_displaycount'), $_displayCount);

        $_OptionsTabObject->PublicPrivate('type', $this->Language->Get('statustype2'), $this->Language->Get('desc_statustype2'), $_statusType);

        $_OptionsTabObject->YesNo('resetduetime', $this->Language->Get('resetduetime'), $this->Language->Get('desc_resetduetime'), $_resetDueTime);

        $_OptionsTabObject->YesNo('triggersurvey', $this->Language->Get('triggersurvey'), $this->Language->Get('desc_triggersurvey'), $_triggerSurvey);

        $_OptionsTabObject->Hidden('dispatchnotification', false);
//        $_OptionsTabObject->YesNo('dispatchnotification', $this->Language->Get('dispatchnotification'),
//            $this->Language->Get('desc_dispatchnotification'), $_dispatchNotification);


        /*
         * ###############################################
         * END OPTIONS TAB
         * ###############################################
         */



        /*
         * ###############################################
         * BEGIN PERMISSIONS TAB
         * ###############################################
         */
        $_PermissionTabObject = $this->UserInterface->AddTab($this->Language->Get('tabpermissions'), 'icon_settings2.gif', 'permissions');

        $_PermissionTabObject->Overflow('440');

        $_PermissionTabObject->YesNo('staffvisibilitycustom', $this->Language->Get('staffvisibilitycustom'),
                $this->Language->Get('desc_staffvisibilitycustom'), $_staffVisibilityCustom);
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
         * BEGIN LANGUAGES TAB
         * ###############################################
         */

        $_LanguageTabObject = $this->UserInterface->AddTab($this->Language->Get('tablanguages'), 'icon_language2.gif', 'languages');
        $_LanguageTabObject->Overflow('440');
        $this->Controller->LanguagePhraseLinked->Render(SWIFT_LanguagePhraseLinked::TYPE_TICKETSTATUS,
                $_ticketStatusID, $_mode, $_LanguageTabObject);

        /*
         * ###############################################
         * END LANGUAGES TAB
         * ###############################################
         */

        $this->UserInterface->End();

        return true;
    }

    /**
     * Render the Ticket Status Grid
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

        $this->Load->Library('UserInterface:UserInterfaceGrid', array('ticketstatusgrid'), true, false, 'base');

        if ($this->UserInterfaceGrid->GetMode() == SWIFT_UserInterfaceGrid::MODE_SEARCH)
        {
            $this->UserInterfaceGrid->SetSearchQuery('SELECT * FROM ' . TABLE_PREFIX . 'ticketstatus WHERE (' .
                    $this->UserInterfaceGrid->BuildSQLSearch('title') . ')', 'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX .
                    'ticketstatus WHERE (' . $this->UserInterfaceGrid->BuildSQLSearch('title') . ')');
        }

        $this->UserInterfaceGrid->SetQuery('SELECT * FROM ' . TABLE_PREFIX . 'ticketstatus', 'SELECT COUNT(*) AS totalitems FROM ' .
                TABLE_PREFIX . 'ticketstatus');

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('ticketstatusid', 'ticketstatusid',
                SWIFT_UserInterfaceGridField::TYPE_ID));

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('icon', '&nbsp;', SWIFT_UserInterfaceGridField::TYPE_CUSTOM, 16,
                SWIFT_UserInterfaceGridField::ALIGN_CENTER));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('title', $this->Language->Get('statustitle'),
                SWIFT_UserInterfaceGridField::TYPE_DB, 0, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('statustype', $this->Language->Get('statustype2'),
                SWIFT_UserInterfaceGridField::TYPE_DB, 160, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('displayorder', $this->Language->Get('order'),
                SWIFT_UserInterfaceGridField::TYPE_DB, 80, SWIFT_UserInterfaceGridField::ALIGN_CENTER, SWIFT_UserInterfaceGridField::SORT_ASC),
                true);

        $this->UserInterfaceGrid->SetRenderCallback(array($this, 'GridRender'));
        $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('delete'), 'fa-trash',
                array('Tickets\Admin\Controller_Status', 'DeleteList'), $this->Language->Get('actionconfirm')));

        $this->UserInterfaceGrid->SetNewLinkViewport(SWIFT::Get('basename') . '/Tickets/Status/Insert');

        if ($_SWIFT->Staff->GetPermission('admin_tcanupdatestatus') != '0')
        {
            $this->UserInterfaceGrid->SetSortableCallback('displayorder', array('Tickets\Admin\Controller_Status', 'SortList'));
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

        $_fieldContainer['title'] = '<a href="' . SWIFT::Get('basename') . '/Tickets/Status/Edit/' . (int) ($_fieldContainer['ticketstatusid']) . '" onclick="' . "javascript: return UICreateWindowExtended(event, '" . SWIFT::Get('basename') . '/Tickets/Status/Edit/' . (int) ($_fieldContainer['ticketstatusid']) . "', 'editticketstatus', '" . addslashes(sprintf($_SWIFT->Language->Get('wineditticketstatus'),
                htmlspecialchars($_fieldContainer['title']))) . "', '" . $_SWIFT->Language->Get('loadingwindow') . "', 800, 710, true, this);" .
                '" title="' . $_SWIFT->Language->Get('edit') . '">' .
                IIF($_fieldContainer['ismaster']==1, '<i>') . htmlspecialchars($_fieldContainer['title']) .
                IIF($_fieldContainer['ismaster']==1, '</i>') . '</a>';

        $_fieldContainer['statustype'] = IIF($_fieldContainer['statustype'] == SWIFT_PUBLIC, $_SWIFT->Language->Get('public'), $_SWIFT->Language->Get('private'));

        $_fieldContainer['icon'] = IIF(empty($_fieldContainer['displayicon']), '<img src="' . SWIFT::Get('themepath') .
                'images/space.gif" align="absmiddle" width="16" height="16" border="0" />', '<img src="' .
                str_replace('{$themepath}', SWIFT::Get('themepath') . 'images/', $_fieldContainer['displayicon']) .
                '" align="absmiddle" border="0" />');

        return $_fieldContainer;
    }
}
