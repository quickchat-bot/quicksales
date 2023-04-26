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

namespace Base\Admin;

use SWIFT;
use SWIFT_App;
use Base\Models\CustomField\SWIFT_CustomFieldGroup;
use Base\Models\CustomField\SWIFT_CustomFieldGroupDepartmentLink;
use Base\Models\CustomField\SWIFT_CustomFieldGroupPermission;
use Base\Models\Department\SWIFT_Department;
use SWIFT_Exception;
use Base\Library\Help\SWIFT_Help;
use Base\Library\Language\SWIFT_LanguagePhraseLinked;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceGrid;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridField;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridMassAction;
use Base\Library\UserInterface\SWIFT_UserInterfaceTab;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;
use SWIFT_View;

/**
 * The Custom Field GroupView
 *
 * @property \Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel $UserInterface
 * @property Controller_CustomFieldGroup $Controller
 * @author Varun Shoor
 */
class View_CustomFieldGroup extends SWIFT_View
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
     * Render the Custom Field Group Form
     *
     * @author Varun Shoor
     * @param int $_mode The Render Mode
     * @param SWIFT_CustomFieldGroup $_SWIFT_CustomFieldGroupObject The SWIFT_CustomFieldGroup Object Pointer (Only for EDIT Mode)
     * @return bool "true" on Success, "false" otherwise
     */
    public function Render($_mode, SWIFT_CustomFieldGroup $_SWIFT_CustomFieldGroupObject = null)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $_customFieldGroupPermissionCache = $this->Cache->Get('cfgrouppermissioncache');

        // Calculate the URL
        if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            $this->UserInterface->Start(get_short_class($this), '/Base/CustomFieldGroup/EditSubmit/' . $_SWIFT_CustomFieldGroupObject->GetCustomFieldGroupID(), SWIFT_UserInterface::MODE_EDIT, true);
        } else {
            $this->UserInterface->Start(get_short_class($this), '/Base/CustomFieldGroup/InsertSubmit', SWIFT_UserInterface::MODE_INSERT, false);
        }

        $_groupTitle = '';
        $_customFieldGroupID = false;
        $_visibilityType = true;
        $_departmentIDList = array();
        $_POST['grouptype'] = SWIFT_CustomFieldGroup::GROUP_USER;
        // Get the last display order

        $_displayOrderContainer = $this->Database->QueryFetch("SELECT displayorder FROM " . TABLE_PREFIX . "customfieldgroups ORDER BY displayorder DESC");
        if (!isset($_displayOrderContainer['displayorder']) || empty($_displayOrderContainer['displayorder'])) {
            $_groupDisplayOrder = 1;
        } else {
            $_groupDisplayOrder = $_displayOrderContainer['displayorder'] + 1;
        }

        if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            if ((isset($_POST['_isDialog']) && $_POST['_isDialog'] == 1) || $this->UserInterface->IsAjax() == false) {
                $this->UserInterface->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle');
            }

            $this->UserInterface->Toolbar->AddButton($this->Language->Get('delete'), 'fa-trash', '/Base/CustomFieldGroup/Delete/' . $_SWIFT_CustomFieldGroupObject->GetCustomFieldGroupID(), SWIFT_UserInterfaceToolbar::LINK_CONFIRM, '', '', false);
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('customfieldgroup'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

            $_groupTitle = $_SWIFT_CustomFieldGroupObject->GetProperty('title');
            $_customFieldGroupID = (int)($_SWIFT_CustomFieldGroupObject->GetCustomFieldGroupID());
            $_POST['grouptype'] = (int)($_SWIFT_CustomFieldGroupObject->GetProperty('grouptype'));
            $_groupDisplayOrder = $_SWIFT_CustomFieldGroupObject->GetProperty('displayorder');
            $_visibilityType = (int)($_SWIFT_CustomFieldGroupObject->GetProperty('visibilitytype'));
            $_departmentIDList = SWIFT_CustomFieldGroupDepartmentLink::GetDepartmentListOnCustomFieldGroup($_SWIFT_CustomFieldGroupObject->GetCustomFieldGroupID());
        } else {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('insert'), 'fa-plus-circle');
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('customfieldgroup'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);
        }


        /*
         * ###############################################
         * BEGIN GENERAL TAB
         * ###############################################
         */
        $_GeneralTabObject = $this->UserInterface->AddTab($this->Language->Get('tabgeneral'), 'icon_form.gif', 'general', true);

        $_GeneralTabObject->Text('title', $this->Language->Get('grouptitle'), $this->Language->Get('desc_grouptitle'), $_groupTitle);

        // ======= BEGIN CUSTOM FIELD GROUP TYPE SELECTION =======
        // KACT 5/pages/418

        $_optionsContainer = array();
        $_optionsContainer[0]['title'] = $this->Language->Get('grouptypeuser');
        $_optionsContainer[0]['value'] = SWIFT_CustomFieldGroup::GROUP_USER;
        $_optionsContainer[1]['title'] = $this->Language->Get('grouptypeuserorganization');
        $_optionsContainer[1]['value'] = SWIFT_CustomFieldGroup::GROUP_USERORGANIZATION;
        $_index = 2;

        if (SWIFT_App::IsInstalled(APP_LIVECHAT)) {
            $_optionsContainer[$_index]['title'] = $this->Language->Get('grouptypelivesupportpre');
            $_optionsContainer[$_index]['value'] = SWIFT_CustomFieldGroup::GROUP_LIVECHATPRE;
            $_index++;

            $_optionsContainer[$_index]['title'] = $this->Language->Get('grouptypelivesupportpost');
            $_optionsContainer[$_index]['value'] = SWIFT_CustomFieldGroup::GROUP_LIVECHATPOST;
            $_index++;
        }

        if (SWIFT_App::IsInstalled(APP_TICKETS)) {
            $_optionsContainer[$_index]['title'] = $this->Language->Get('grouptypestaffticket');
            $_optionsContainer[$_index]['value'] = SWIFT_CustomFieldGroup::GROUP_STAFFTICKET;
            $_index++;

            $_optionsContainer[$_index]['title'] = $this->Language->Get('grouptypeuserticket');
            $_optionsContainer[$_index]['value'] = SWIFT_CustomFieldGroup::GROUP_USERTICKET;
            $_index++;

            $_optionsContainer[$_index]['title'] = $this->Language->Get('grouptypestaffuserticket');
            $_optionsContainer[$_index]['value'] = SWIFT_CustomFieldGroup::GROUP_STAFFUSERTICKET;
            $_index++;

            $_optionsContainer[$_index]['title'] = $this->Language->Get('grouptypetimetrack');
            $_optionsContainer[$_index]['value'] = SWIFT_CustomFieldGroup::GROUP_TIMETRACK;
            $_index++;
        }

        if ($_mode == SWIFT_UserInterface::MODE_INSERT) {
            $_GeneralTabObject->Select('grouptype', $this->Language->Get('grouptype'), $this->Language->Get('desc_grouptype'), $_optionsContainer, 'HandleCustomFieldGroupSwitch(this.value);');
        } else {
            $_GeneralTabObject->DefaultDescriptionRow($this->Language->Get('grouptype'), $this->Language->Get('desc_grouptype'), SWIFT_CustomFieldGroup::GetGroupLabel($_POST['grouptype']));
            $this->UserInterface->Hidden('grouptype', $_POST['grouptype']);
        }

        if ($_mode == SWIFT_UserInterface::MODE_INSERT || ($_mode == SWIFT_UserInterface::MODE_EDIT && ($_POST['grouptype'] == SWIFT_CustomFieldGroup::GROUP_USER || $_POST['grouptype'] == SWIFT_CustomFieldGroup::GROUP_USERORGANIZATION))) {
            $_GeneralTabObject->PublicPrivate('visibilitytype', $this->Language->Get('visibilitytype'), $this->Language->Get('desc_visibilitytype'), $_visibilityType);
        }

        $_GeneralTabObject->Number('displayorder', $this->Language->Get('displayorder'), $this->Language->Get('desc_displayorder'), $_groupDisplayOrder);

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

        $_PermissionsTabObject = $this->UserInterface->AddTab($this->Language->Get('tabpermissions'), 'icon_permissions.gif', 'permissions');

        $_PermissionsTabObject->Overflow(330);

        $_PermissionsTabObject->Title($this->Language->Get('cfteampermissions'), 'icon_permissions.gif');

        $_staffGroupContainer = array();

        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "staffgroup ORDER BY title ASC");
        while ($this->Database->NextRecord()) {
            $_staffGroupContainer[$this->Database->Record['staffgroupid']] = $this->Database->Record;

            $_isAssigned = false;
            if ($_mode == SWIFT_UserInterface::MODE_INSERT) {
                $_isAssigned = true;
            } elseif ($_mode == SWIFT_UserInterface::MODE_EDIT) {
                if (isset($_customFieldGroupPermissionCache[SWIFT_CustomFieldGroupPermission::TYPE_STAFFGROUP][$_SWIFT_CustomFieldGroupObject->GetCustomFieldGroupID()][$this->Database->Record['staffgroupid']]) && $_customFieldGroupPermissionCache[SWIFT_CustomFieldGroupPermission::TYPE_STAFFGROUP][$_SWIFT_CustomFieldGroupObject->GetCustomFieldGroupID()][$this->Database->Record['staffgroupid']] == SWIFT_CustomFieldGroupPermission::ACCESS_YES) {
                    $_isAssigned = true;
                } elseif (!isset($_customFieldGroupPermissionCache[SWIFT_CustomFieldGroupPermission::TYPE_STAFFGROUP][$_SWIFT_CustomFieldGroupObject->GetCustomFieldGroupID()][$this->Database->Record['staffgroupid']])) {
                    $_isAssigned = true;
                } else {
                    $_isAssigned = false;
                }
            }

            $_PermissionsTabObject->YesNo('permstaffgroupid[' . $this->Database->Record['staffgroupid'] . ']', IIF($this->Database->Record['isadmin'] == '1', '<img src="' . SWIFT::Get('themepath') . 'images/icon_admin.gif" border="0" align="absmiddle" /> ', '<img src="' . SWIFT::Get('themepath') . 'images/icon_notadmin.gif" border="0" align="absmiddle" /> ') . htmlspecialchars($this->Database->Record['title']), '', $_isAssigned);
        }

        $_PermissionsTabObject->Title($this->Language->Get('cfstaffpermissions'), 'icon_permissions.gif');

        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "staff ORDER BY fullname ASC");
        while ($this->Database->NextRecord()) {
            $_groupValue = SWIFT_CustomFieldGroupPermission::ACCESS_NOTSET;
            if ($_mode == SWIFT_UserInterface::MODE_INSERT) {
                $_isAssigned = true;
            } elseif ($_mode == SWIFT_UserInterface::MODE_EDIT) {
                if (isset($_customFieldGroupPermissionCache[SWIFT_CustomFieldGroupPermission::TYPE_STAFF][$_SWIFT_CustomFieldGroupObject->GetCustomFieldGroupID()][$this->Database->Record['staffid']]) && $_customFieldGroupPermissionCache[SWIFT_CustomFieldGroupPermission::TYPE_STAFF][$_SWIFT_CustomFieldGroupObject->GetCustomFieldGroupID()][$this->Database->Record['staffid']] == SWIFT_CustomFieldGroupPermission::ACCESS_NOTSET) {
                    $_groupValue = SWIFT_CustomFieldGroupPermission::ACCESS_NOTSET;
                } elseif (!isset($_customFieldGroupPermissionCache[SWIFT_CustomFieldGroupPermission::TYPE_STAFF][$_SWIFT_CustomFieldGroupObject->GetCustomFieldGroupID()][$this->Database->Record['staffid']])) {
                    $_groupValue = SWIFT_CustomFieldGroupPermission::ACCESS_NOTSET;
                } elseif (isset($_customFieldGroupPermissionCache[SWIFT_CustomFieldGroupPermission::TYPE_STAFF][$_SWIFT_CustomFieldGroupObject->GetCustomFieldGroupID()][$this->Database->Record['staffid']]) && $_customFieldGroupPermissionCache[SWIFT_CustomFieldGroupPermission::TYPE_STAFF][$_SWIFT_CustomFieldGroupObject->GetCustomFieldGroupID()][$this->Database->Record['staffid']] == SWIFT_CustomFieldGroupPermission::ACCESS_YES) {
                    $_groupValue = SWIFT_CustomFieldGroupPermission::ACCESS_YES;
                } else {
                    $_groupValue = SWIFT_CustomFieldGroupPermission::ACCESS_NO;
                }
            }

            $_fieldName = 'permstaffid[' . $this->Database->Record['staffid'] . ']';

            $_data = '<label for="u' . $_fieldName . '">' . '<input type="radio" name="' . $_fieldName . '" class="swiftradio" id="u' . $_fieldName . '" value="-1"' . IIF($_groupValue == SWIFT_CustomFieldGroupPermission::ACCESS_NOTSET, ' checked') . ' /> ' . $this->Language->Get('notset') . '</label>' . SWIFT_CRLF;
            $_data .= ' <label for="y' . $_fieldName . '">' . '<input type="radio" name="' . $_fieldName . '" class="swiftradio" id="y' . $_fieldName . '" value="1"' . IIF($_groupValue == SWIFT_CustomFieldGroupPermission::ACCESS_YES, ' checked') . ' /> ' . $this->Language->Get('yes') . '</label>' . SWIFT_CRLF;
            $_data .= ' <label for="n' . $_fieldName . '">' . '<input type="radio" class="swiftradio" name="' . $_fieldName . '" id="n' . $_fieldName . '" value="0"' . IIF($_groupValue == SWIFT_CustomFieldGroupPermission::ACCESS_NO, ' checked') . ' /> ' . $this->Language->Get('no') . '</label>' . SWIFT_CRLF;

            if (isset($_staffGroupContainer[$this->Database->Record['staffgroupid']])) {
                $_info = '<span class="tabletitle">' . IIF($_staffGroupContainer[$this->Database->Record['staffgroupid']]['isadmin'] == '1', '<img src="' . SWIFT::Get('themepath') . 'images/icon_admin.gif" border="0" align="absmiddle" /> ', '<img src="' . SWIFT::Get('themepath') . 'images/icon_notadmin.gif" border="0" align="absmiddle" /> ') . text_to_html_entities($this->Database->Record['fullname']) . '</span>';

            } else {
                $_info = '<s>' . text_to_html_entities($this->Database->Record['fullname']) . '</s>';
            }

            $_PermissionsTabObject->DefaultRow($_info, $_data);
        }


        /*
         * ###############################################
         * END PERMISSIONS TAB
         * ###############################################
         */


        /*
         * ###############################################
         * BEGIN DEPARTMENTS TAB
         * ###############################################
         */

        $_departmentIsDisabled = false;
        if ($_mode == SWIFT_UserInterface::MODE_INSERT) {
            $_departmentIsDisabled = true;
        } elseif ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            if ($_POST['grouptype'] == SWIFT_CustomFieldGroup::GROUP_USER || $_POST['grouptype'] == SWIFT_CustomFieldGroup::GROUP_USERORGANIZATION || $_POST['grouptype'] == SWIFT_CustomFieldGroup::GROUP_TIMETRACK || $_POST['grouptype'] == SWIFT_CustomFieldGroup::GROUP_KNOWLEDGEBASE || $_POST['grouptype'] == SWIFT_CustomFieldGroup::GROUP_NEWS || $_POST['grouptype'] == SWIFT_CustomFieldGroup::GROUP_TROUBLESHOOTER) {
                $_departmentIsDisabled = true;
            }
        }

        $_DepartmentTabObject = $this->UserInterface->AddTab($this->Language->Get('tabdepartments'), 'icon_folderyellow3.gif', 'departments', false, $_departmentIsDisabled);
        $_DepartmentTabObject->Overflow(330);

        $this->RenderDepartmentSection($_DepartmentTabObject, $_mode, APP_TICKETS, $_POST['grouptype'], $_departmentIDList);
        $this->RenderDepartmentSection($_DepartmentTabObject, $_mode, APP_LIVECHAT, $_POST['grouptype'], $_departmentIDList);


        /*
         * ###############################################
         * END DEPARTMENTS TAB
         * ###############################################
         */


        /*
         * ###############################################
         * BEGIN LANGUAGES TAB
         * ###############################################
         */

        $_LanguageTabObject = $this->UserInterface->AddTab($this->Language->Get('tablanguages'), 'icon_language2.gif', 'languages');
        $_LanguageTabObject->Overflow(330);
        $this->Controller->LanguagePhraseLinked->Render(SWIFT_LanguagePhraseLinked::TYPE_CUSTOMFIELDGROUP, $_customFieldGroupID, $_mode, $_LanguageTabObject);

        /*
         * ###############################################
         * END LANGUAGES TAB
         * ###############################################
         */

        $this->UserInterface->End();

        return true;
    }

    /**
     * Renders the Department Tab Section for the Custom Field Group
     *
     * @author Varun Shoor
     * @param SWIFT_UserInterfaceTab $_UserInterfaceTabObject The SWIFT_UserInterfaceTab Object Pointer
     * @param mixed $_mode The UI Mode (INSERT/EDIT)
     * @param string $_appName The App Name
     * @param mixed $_groupType The Default Group Type
     * @param array $_departmentIDList The Assigned Department ID List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    protected function RenderDepartmentSection(SWIFT_UserInterfaceTab $_UserInterfaceTabObject, $_mode, $_appName, $_groupType, $_departmentIDList)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } elseif (!$_UserInterfaceTabObject instanceof SWIFT_UserInterfaceTab || !$_UserInterfaceTabObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        } elseif (!SWIFT_App::IsInstalled($_appName) || ($_appName != APP_LIVECHAT && $_appName != APP_TICKETS)) {
            return false;
        }

        $_groupList[APP_LIVECHAT] = SWIFT_CustomFieldGroup::GetGroupListOnApp(APP_LIVECHAT);
        $_groupList[APP_TICKETS] = SWIFT_CustomFieldGroup::GetGroupListOnApp(APP_TICKETS);

        $_UserInterfaceTabObject->StartContainer('cfdep' . Clean($_appName), IIF(in_array($_groupType, $_groupList[$_appName]), true, false));

        $_departmentMap = SWIFT_Department::GetDepartmentMap($_appName);

        if ($_appName == APP_LIVECHAT) {
            $_UserInterfaceTabObject->Title($this->Language->Get('assigneddepartments') . ': ' . $this->Language->Get('app_livechat'), 'icon_livesupport.gif');
        } else {
            $_UserInterfaceTabObject->Title($this->Language->Get('assigneddepartments') . ': ' . $this->Language->Get('app_tickets'), 'icon_tickets.png');
        }

        foreach ($_departmentMap as $_key => $_val) {
            $_isAssigned = true;
            if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
                if (in_array($_val['departmentid'], $_departmentIDList)) {
                    $_isAssigned = true;
                } else {
                    $_isAssigned = false;
                }
            } else {
                // All departments are checked if its the insert area
                $_isAssigned = true;
            }

            foreach ($_val['subdepartmentids'] as $_subKey => $_subVal) {
                $_val['subdepartmentids'][$_subKey] = "'" . $_subVal . "'";
            }

            /*
             * BUG FIX - Varun Shoor
             *
             * SWIFT-822 Assigning custom fields to subdepartments
             *
             * Comments: None
             */

//            $_UserInterfaceTabObject->YesNo('assigned[' . $_val['departmentid'] . ']', htmlspecialchars($_val['title']), '', $_isAssigned, 'ChangeDepartmentRadioStatus(\'View_CustomFieldGroupform\', this.value, new Array(' . implode(', ', $_val['subdepartmentids']) . '));');
            $_UserInterfaceTabObject->YesNo('assigned[' . $_val['departmentid'] . ']', text_to_html_entities($_val['title']), '', $_isAssigned);

            if (_is_array($_val['subdepartments'])) {
                foreach ($_val['subdepartments'] as $_subKey => $_subVal) {
                    $_isAssigned = true;
                    if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
                        if (in_array($_subVal['departmentid'], $_departmentIDList)) {
                            $_isAssigned = true;
                        } else {
                            $_isAssigned = false;
                        }
                    } else {
                        // All departments are checked if its the insert area
                        $_isAssigned = true;
                    }

                    $_UserInterfaceTabObject->YesNo('assigned[' . $_subVal['departmentid'] . ']', '<img src="' . SWIFT::Get('themepath') . 'images/linkdownarrow_blue.gif" border="0" align="absmiddle" /> ' . htmlspecialchars($_subVal['title']), '', $_isAssigned);
                }
            }
        }

        $_UserInterfaceTabObject->EndContainer();

        return true;
    }

    /**
     * Render the Ticket File Type Grid
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function RenderGrid()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->Load->Library('UserInterface:UserInterfaceGrid', array('customfieldgroupgrid'), true, false, 'base');

        if ($this->UserInterfaceGrid->GetMode() == SWIFT_UserInterfaceGrid::MODE_SEARCH) {
            $this->UserInterfaceGrid->SetSearchQuery('SELECT * FROM ' . TABLE_PREFIX . 'customfieldgroups WHERE (' . $this->UserInterfaceGrid->BuildSQLSearch('title') . ')', 'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX . 'customfieldgroups WHERE (' . $this->UserInterfaceGrid->BuildSQLSearch('title') . ')');
        }

        $this->UserInterfaceGrid->SetQuery('SELECT * FROM ' . TABLE_PREFIX . 'customfieldgroups', 'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX . 'customfieldgroups');

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('customfieldgroupid', 'customfieldgroupid', SWIFT_UserInterfaceGridField::TYPE_ID));

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('icon', '&nbsp;', SWIFT_UserInterfaceGridField::TYPE_CUSTOM, 16, SWIFT_UserInterfaceGridField::ALIGN_CENTER));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('title', $this->Language->Get('grouptitle'), SWIFT_UserInterfaceGridField::TYPE_DB, 0, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('grouptype', $this->Language->Get('grouptype'), SWIFT_UserInterfaceGridField::TYPE_DB, 230, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('displayorder', $this->Language->Get('displayorder'), SWIFT_UserInterfaceGridField::TYPE_DB, 120, SWIFT_UserInterfaceGridField::ALIGN_CENTER, SWIFT_UserInterfaceGridField::SORT_ASC), true);

        $this->UserInterfaceGrid->SetRenderCallback(array($this, 'GridRender'));
        $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('delete'), 'fa-trash', array('Base\Admin\Controller_CustomFieldGroup', 'DeleteList'), $this->Language->Get('actionconfirm')));
        $this->UserInterfaceGrid->SetNewLinkViewport(SWIFT::Get('basename') . '/Base/CustomFieldGroup/Insert');

        if ($_SWIFT->Staff->GetPermission('admin_canupdatecfgroup') != '0') {
            $this->UserInterfaceGrid->SetSortableCallback('displayorder', array('Base\Admin\Controller_CustomFieldGroup', 'SortList'));
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

        $_fieldContainer['grouptype'] = SWIFT_CustomFieldGroup::GetGroupLabel($_fieldContainer['grouptype']);
        $_fieldContainer['icon'] = "<img src='" . SWIFT::Get('themepath') . "images/icon_customfieldgroup.gif' border='0' align='absmiddle' />";

        $_fieldContainer['title'] = "<a href=\"" . SWIFT::Get('basename') . "/Base/CustomFieldGroup/Edit/" . (int)($_fieldContainer['customfieldgroupid']) . "\" onclick=\"javascript: return UICreateWindowExtended(event, '" . SWIFT::Get('basename') . "/Base/CustomFieldGroup/Edit/" . (int)($_fieldContainer['customfieldgroupid']) . "', 'editcfgroup', '" . sprintf($_SWIFT->Language->Get('wineditcfgroup'), addslashes(htmlspecialchars($_fieldContainer['title']))) . "', '" . $_SWIFT->Language->Get('loadingwindow') . "', 700, 560, true, this);\" title='" . $_SWIFT->Language->Get('edit') . "'> " . htmlspecialchars($_fieldContainer['title']) . '</a>';

        return $_fieldContainer;
    }
}

?>
