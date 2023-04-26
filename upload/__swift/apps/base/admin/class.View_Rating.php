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

namespace Base\Admin;

use SWIFT;
use Base\Library\Help\SWIFT_Help;
use Base\Models\Rating\SWIFT_Rating;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceGrid;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridField;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridMassAction;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;
use SWIFT_View;

/**
 * The Rating View
 *
 * @property \Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel $UserInterface
 * @author Varun Shoor
 */
class View_Rating extends SWIFT_View
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
     * Render the Rating Form
     *
     * @author Varun Shoor
     * @param int $_mode The Render Mode
     * @param SWIFT_Rating $_SWIFT_RatingObject The SWIFT_Rating Object Pointer (Only for EDIT Mode)
     * @return bool "true" on Success, "false" otherwise
     */
    public function Render($_mode, SWIFT_Rating $_SWIFT_RatingObject = null)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        // Calculate the URL
        if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            $this->UserInterface->Start(get_short_class($this), '/Base/Rating/EditSubmit/' . $_SWIFT_RatingObject->GetRatingID(), SWIFT_UserInterface::MODE_EDIT, true);
        } else {
            $this->UserInterface->Start(get_short_class($this), '/Base/Rating/InsertSubmit', SWIFT_UserInterface::MODE_INSERT, false);
        }

        $_ratingTitle = '';
        $_departmentID = false;
        $_staffVisibilityCustom = false;
        $_staffGroupIDList = false;
        $_isEditable = true;
        $_ratingType = SWIFT_Rating::TYPE_TICKETPOST;
        $_displayOrder = SWIFT_Rating::GetLastDisplayOrder();
        $_userVisibilityCustom = false;
        $_userGroupIDList = false;
        $_isClientOnly = false;
        $_ratingVisibility = SWIFT_PUBLIC;
        $_ratingScale = 5;

        if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            if ((isset($_POST['_isDialog']) && $_POST['_isDialog'] == 1) || $this->UserInterface->IsAjax() == false) {
                $this->UserInterface->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle');
            }

            $this->UserInterface->Toolbar->AddButton($this->Language->Get('delete'), 'fa-trash', '/Base/Rating/Delete/' . $_SWIFT_RatingObject->GetRatingID(), SWIFT_UserInterfaceToolbar::LINK_CONFIRM, '', '', false);
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('ratings'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

            $_ratingTitle = $_SWIFT_RatingObject->GetProperty('ratingtitle');
            $_displayOrder = (int)($_SWIFT_RatingObject->GetProperty('displayorder'));
            $_departmentID = (int)($_SWIFT_RatingObject->GetProperty('departmentid'));
            $_isEditable = (int)($_SWIFT_RatingObject->GetProperty('iseditable'));
            $_isClientOnly = (int)($_SWIFT_RatingObject->GetProperty('isclientonly'));
            $_ratingType = (int)($_SWIFT_RatingObject->GetProperty('ratingtype'));
            $_ratingScale = (int)($_SWIFT_RatingObject->GetProperty('ratingscale'));
            $_ratingVisibility = $_SWIFT_RatingObject->GetProperty('ratingvisibility');
            $_staffVisibilityCustom = (int)($_SWIFT_RatingObject->GetProperty('staffvisibilitycustom'));
            $_userVisibilityCustom = (int)($_SWIFT_RatingObject->GetProperty('uservisibilitycustom'));

            $_staffGroupIDList = $_SWIFT_RatingObject->GetLinkedStaffGroupIDList();
            $_userGroupIDList = $_SWIFT_RatingObject->GetLinkedUserGroupIDList();

        } else {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('insert'), 'fa-plus-circle');
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('ratings'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);
        }


        /*
         * ###############################################
         * BEGIN GENERAL TAB
         * ###############################################
         */
        $_GeneralTabObject = $this->UserInterface->AddTab($this->Language->Get('tabgeneral'), 'icon_form.gif', 'general', true);

        $_GeneralTabObject->Text('ratingtitle', $this->Language->Get('ratingtitle'), $this->Language->Get('desc_ratingtitle'), $_ratingTitle);

        $_GeneralTabObject->Number('displayorder', $this->Language->Get('displayorder'), $this->Language->Get('desc_displayorder'), $_displayOrder);

        $_GeneralTabObject->PublicPrivate('ratingvisibility', $this->Language->Get('ratingvisibility'), $this->Language->Get('desc_ratingvisibility'), $_ratingVisibility);

        $_GeneralTabObject->YesNo('iseditable', $this->Language->Get('iseditable'), $this->Language->Get('desc_iseditable'), $_isEditable);

        $_GeneralTabObject->YesNo('isclientonly', $this->Language->Get('isclientonly'), $this->Language->Get('desc_isclientonly'), $_isClientOnly);

        $_optionsContainer = array();
        $_index = 0;
        for ($_ii = 1; $_ii <= 10; $_ii++) {
            $_optionsContainer[$_index]['title'] = $_ii;
            $_optionsContainer[$_index]['value'] = $_ii;
            $_optionsContainer[$_index]['selected'] = IIF($_ratingScale == $_ii, true, false);

            $_index++;
        }
        $_GeneralTabObject->Select('ratingscale', $this->Language->Get('ratingscale'), $this->Language->Get('desc_ratingscale'), $_optionsContainer);

        if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            $_GeneralTabObject->DefaultDescriptionRow($this->Language->Get('ratingtype'), '', SWIFT_Rating::GetLabel($_ratingType));
            $this->UserInterface->Hidden('ratingtype', $_ratingType);
        } else {
            $_optionsContainer = array();
            $_index = 0;

            foreach (SWIFT_Rating::RetrieveAvailableTypes() as $_key => $_val) {
                $_optionsContainer[$_index]['title'] = $_val;
                $_optionsContainer[$_index]['value'] = $_key;
                $_optionsContainer[$_index]['selected'] = IIF($_ratingType == $_key, true, false);

                $_index++;
            }

            $_GeneralTabObject->Select('ratingtype', $this->Language->Get('ratingtype'), $this->Language->Get('desc_ratingtype'), $_optionsContainer, 'javascript: ToggleRatingTypeValues();');
        }

        if ($_mode == SWIFT_UserInterface::MODE_INSERT || ($_mode == SWIFT_UserInterface::MODE_EDIT && ($_ratingType == SWIFT_Rating::TYPE_TICKET || $_ratingType == SWIFT_Rating::TYPE_TICKETPOST))) {
            $_optionsContainer = array();
            $_index = 1;
            $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "departments ORDER BY displayorder ASC");
            $_optionsContainer[0]['title'] = $this->Language->Get('ratingalldep');
            $_optionsContainer[0]['value'] = 0;
            $_optionsContainer[0]['selected'] = IIF(!$_departmentID, true, false);

            while ($this->Database->NextRecord()) {
                if ($this->Database->Record['departmentapp'] == APP_TICKETS) {
                    $_optionsContainer[$_index]['title'] = $this->Database->Record['title'];
                    $_optionsContainer[$_index]['value'] = $this->Database->Record['departmentid'];

                    if ($_departmentID == $this->Database->Record['departmentid']) {
                        $_optionsContainer[$_index]['selected'] = true;
                    }

                    $_index++;
                }
            }

            $_GeneralTabObject->Select('departmentid', $this->Language->Get('ratingdep'), $this->Language->Get('desc_ratingdep'), $_optionsContainer);
        }

        /*
         * ###############################################
         * END GENERAL TAB
         * ###############################################
         */


        /*
         * ###############################################
         * BEGIN PERMISSIONS (STAFF) TAB
         * ###############################################
         */
        $_StaffPermissionTabObject = $this->UserInterface->AddTab($this->Language->Get('tabpermissionsstaff'), 'icon_settings2.gif', 'staffpermissions');

        $_StaffPermissionTabObject->Overflow(500);

        $_StaffPermissionTabObject->YesNo('staffvisibilitycustom', $this->Language->Get('bstaffvisibilitycustom'), $this->Language->Get('desc_bstaffvisibilitycustom'), $_staffVisibilityCustom);
        $_StaffPermissionTabObject->Title($this->Language->Get('staffgroups'), 'doublearrows.gif');

        $_index = 0;
        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "staffgroup ORDER BY title ASC");
        while ($this->Database->NextRecord()) {
            $_isSelected = false;

            if ($_mode == SWIFT_UserInterface::MODE_INSERT && !$_staffGroupIDList) {
                $_isSelected = true;
            } elseif (_is_array($_staffGroupIDList)) {
                if (in_array($this->Database->Record['staffgroupid'], $_staffGroupIDList)) {
                    $_isSelected = true;
                }
            }

            $_StaffPermissionTabObject->YesNo('staffgroupidlist[' . (int)($this->Database->Record['staffgroupid']) . ']', htmlspecialchars($this->Database->Record['title']), '', $_isSelected);

            $_index++;
        }

        /*
         * ###############################################
         * END PERMISSIONS (STAFF) TAB
         * ###############################################
         */


        /*
         * ###############################################
         * BEGIN PERMISSIONS (USER) TAB
         * ###############################################
         */
        $_UserPermissionTabObject = $this->UserInterface->AddTab($this->Language->Get('tabpermissionsuser'), 'icon_settings2.gif', 'userpermissions');

        $_UserPermissionTabObject->Overflow(500);

        $_UserPermissionTabObject->YesNo('uservisibilitycustom', $this->Language->Get('buservisibilitycustom'), $this->Language->Get('desc_buservisibilitycustom'), $_userVisibilityCustom);
        $_UserPermissionTabObject->Title($this->Language->Get('usergroups'), 'doublearrows.gif');

        $_index = 0;
        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "usergroups ORDER BY title ASC");
        while ($this->Database->NextRecord()) {
            $_isSelected = false;

            if ($_mode == SWIFT_UserInterface::MODE_INSERT && !$_userGroupIDList) {
                $_isSelected = true;
            } elseif (_is_array($_userGroupIDList)) {
                if (in_array($this->Database->Record['usergroupid'], $_userGroupIDList)) {
                    $_isSelected = true;
                }
            }

            $_UserPermissionTabObject->YesNo('usergroupidlist[' . (int)($this->Database->Record['usergroupid']) . ']', htmlspecialchars($this->Database->Record['title']), '', $_isSelected);

            $_index++;
        }

        /*
         * ###############################################
         * END PERMISSIONS (USER) TAB
         * ###############################################
         */

        if ($_mode == SWIFT_UserInterface::MODE_INSERT) {
            $_GeneralTabObject->PrependHTML('<script type="text/javascript">QueueFunction(function(){ ToggleRatingTypeValues(); });</script>');
        }

        $this->UserInterface->End();

        return true;
    }

    /**
     * Render the Rating Grid
     *
     * @author Varun Shoor
     * @author Abdulrahman Suleiman <abdulrahman.suleiman@crossover.com>
     *
     * @return bool "true" on Success, "false" otherwise
     */
    public function RenderGrid()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->Load->Library('UserInterface:UserInterfaceGrid', array('ratinggrid'), true, false, 'base');

        if ($this->UserInterfaceGrid->GetMode() == SWIFT_UserInterfaceGrid::MODE_SEARCH) {
            $this->UserInterfaceGrid->SetSearchQuery('SELECT * FROM ' . TABLE_PREFIX . 'ratings WHERE (' . $this->UserInterfaceGrid->BuildSQLSearch('ratingtitle') . ')', 'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX . 'ratings WHERE (' . $this->UserInterfaceGrid->BuildSQLSearch('ratingtitle') . ')');
        }

        $sortRatingTypeMappings = [];
        foreach(SWIFT_Rating::RATING_TYPES as $type){
            $sortRatingTypeMappings[$type] = SWIFT_Rating::GetLabel($type);
        }

        $sortRatingTypeMappingsQuery = SWIFT_UserInterfaceGrid::GetSortFieldMappingsQuery($sortRatingTypeMappings, 'ratingtype', 'ratingtypelabel');

        $this->UserInterfaceGrid->SetSortFieldMapping('ratingtype', 'ratingtypelabel');
        $this->UserInterfaceGrid->SetQuery('SELECT *, ' . $sortRatingTypeMappingsQuery . ' FROM ' . TABLE_PREFIX . 'ratings', 'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX . 'ratings');

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('ratingid', 'ratingid', SWIFT_UserInterfaceGridField::TYPE_ID));

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('icon', '&nbsp;', SWIFT_UserInterfaceGridField::TYPE_CUSTOM, 16, SWIFT_UserInterfaceGridField::ALIGN_CENTER));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('ratingtitle', $this->Language->Get('ratingtitle'), SWIFT_UserInterfaceGridField::TYPE_DB, 0, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('ratingtype', $this->Language->Get('ratingtype'), SWIFT_UserInterfaceGridField::TYPE_DB, 140, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('ratingvisibility', $this->Language->Get('ratingvis'), SWIFT_UserInterfaceGridField::TYPE_DB, 120, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('displayorder', $this->Language->Get('order'), SWIFT_UserInterfaceGridField::TYPE_DB, 80, SWIFT_UserInterfaceGridField::ALIGN_CENTER, SWIFT_UserInterfaceGridField::SORT_ASC), true);

        $this->UserInterfaceGrid->SetRenderCallback(array($this, 'GridRender'));
        $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('delete'), 'fa-trash', array('Base\Admin\Controller_Rating', 'DeleteList'), $this->Language->Get('actionconfirm')));

        $this->UserInterfaceGrid->SetNewLinkViewport(SWIFT::Get('basename') . '/Base/Rating/Insert');

        if ($_SWIFT->Staff->GetPermission('admin_canupdaterating') != '0') {
            $this->UserInterfaceGrid->SetSortableCallback('displayorder', array('Base\Admin\Controller_Rating', 'SortList'));
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
     * @return array
     */
    public static function GridRender($_fieldContainer)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_fieldContainer['ratingtitle'] = '<a href="' . SWIFT::Get('basename') . '/Base/Rating/Edit/' . (int)($_fieldContainer['ratingid']) . '" onclick="' . "javascript: return UICreateWindowExtended(event, '" . SWIFT::Get('basename') . '/Base/Rating/Edit/' . (int)($_fieldContainer['ratingid']) . "', 'editrating', '" . sprintf($_SWIFT->Language->Get('wineditrating'), htmlspecialchars($_fieldContainer['ratingtitle'])) . "', '" . $_SWIFT->Language->Get('loadingwindow') . "', 750, 810, true, this);" . '" title="' . $_SWIFT->Language->Get('edit') . '">' . htmlspecialchars($_fieldContainer['ratingtitle']) . '</a>';

        $_fieldContainer['ratingtype'] = SWIFT_Rating::GetLabel($_fieldContainer['ratingtype']);

        if ($_fieldContainer['ratingvisibility'] == SWIFT_PUBLIC) {
            $_fieldContainer['ratingvisibility'] = $_SWIFT->Language->Get('public');
        } else {
            $_fieldContainer['ratingvisibility'] = $_SWIFT->Language->Get('private');
        }

        $_fieldContainer['icon'] = '<img src="' . SWIFT::Get('themepath') . 'images/icon_rating.gif" border="0" align="absmiddle" />';

        return $_fieldContainer;
    }
}

?>
