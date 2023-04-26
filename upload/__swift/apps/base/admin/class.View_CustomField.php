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

namespace Base\Admin;

use SWIFT;
use Base\Models\CustomField\SWIFT_CustomField;
use Base\Models\CustomField\SWIFT_CustomFieldGroup;
use SWIFT_Date;
use Base\Library\Help\SWIFT_Help;
use Base\Library\Language\SWIFT_LanguagePhraseLinked;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceGrid;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridField;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridMassAction;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;
use SWIFT_View;

/**
 * The Custom Field View
 *
 * @property \Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel $UserInterface
 * @property Controller_CustomField $Controller
 * @author Varun Shoor
 */
class View_CustomField extends SWIFT_View
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
     * Render the Custom Field Form (Insert, Step 1)
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function RenderInsert()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->UserInterface->Start(get_short_class($this), '/Base/CustomField/InsertStep2', SWIFT_UserInterface::MODE_INSERT, false);

        // ======= GROUP ARRAY LOADING =======
        $_groupIndex = 0;
        $_optionsContainer = array();

        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "customfieldgroups ORDER BY title ASC");
        while ($this->Database->NextRecord()) {
            $_optionsContainer[$_groupIndex]['title'] = $this->Database->Record['title'] . ' (' . SWIFT_CustomFieldGroup::GetGroupLabel($this->Database->Record['grouptype']) . ')';
            $_optionsContainer[$_groupIndex]['value'] = $this->Database->Record['customfieldgroupid'];

            $_groupIndex++;
        }

        if (!$_groupIndex) {
            // No groups added
            $_optionsContainer[0]['title'] = $this->Language->Get('nogroupadded');
            $_optionsContainer[0]['value'] = '';

            $this->UserInterface->DisplayAlert($this->Language->Get('titleunproc'), $this->Language->Get('msgunproc'));
        } else {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('buttonnext'), 'fa-chevron-circle-right ');
        }

        $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('customfield'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        /*
         * ###############################################
         * BEGIN GENERAL TAB
         * ###############################################
         */
        $_GeneralTabObject = $this->UserInterface->AddTab($this->Language->Get('tabgeneral'), 'icon_form.gif', 'general', true);

        // ======= CUSTOM FIELD GROUPS =======
        $_GeneralTabObject->Select('customfieldgroupid', $this->Language->Get('customfieldgroup'), $this->Language->Get('desc_customfieldgroup'), $_optionsContainer);

        $_fieldList = SWIFT_CustomField::GetFieldList();

        $_GeneralTabObject->Title($this->Language->Get('fieldtype'));

        $_rowIndex = 0;
        foreach ($_fieldList as $_key => $_val) {
            $_columnContainer = array();

            $_isChecked = false;
            if (($_rowIndex == 0 && !isset($_GET['fieldtype'])) || (!empty($_GET['fieldtype']) && $_GET['fieldtype'] == $_val[0])) {
                $_isChecked = true;
            }

            $_columnContainer[0]['align'] = 'left';
            $_columnContainer[0]['nowrap'] = true;
            $_columnContainer[0]['colspan'] = "2";
            $_columnContainer[0]['value'] = '<table width="100%"  border="0" cellspacing="0" cellpadding="2"><tr><td width="1%"><input name="fieldtype" type="radio" id="' . $_val[0] . '" onchange="this.blur();" value="' . $_val[0] . '"' . IIF($_isChecked, ' checked') . '></td><td width="99%"><table width="100%"  border="0" cellspacing="0" cellpadding="0"><tr><td width="1"><img src="' . SWIFT::Get('themepath') . 'images/' . $_val[3] . '" border="0" /></td><td><span class="tabletitle"><label for="' . $_val[0] . '">&nbsp;' . $_val[1] . '</label></span></td></tr></table></td></tr><tr><td>&nbsp;</td><td><span class="tabledescription">' . $_val[2] . '</span></td></tr></table>';

            $_GeneralTabObject->Row($_columnContainer);

            $_rowIndex++;
        }


        /*
         * ###############################################
         * END GENERAL TAB
         * ###############################################
         */

        $this->UserInterface->End();

        return true;
    }

    /**
     * Render the Custom Field Form
     *
     * @author Varun Shoor
     * @param int $_mode The Render Mode
     * @param SWIFT_CustomField $_SWIFT_CustomFieldObject The SWIFT_CustomField Object Pointer (Only for EDIT Mode)
     * @return bool "true" on Success, "false" otherwise
     */
    public function Render($_mode, SWIFT_CustomField $_SWIFT_CustomFieldObject = null)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        // Calculate the URL
        if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            $this->UserInterface->Start(get_short_class($this), '/Base/CustomField/EditSubmit/' . $_SWIFT_CustomFieldObject->GetCustomFieldID(), SWIFT_UserInterface::MODE_EDIT, false);
        } else {
            $this->UserInterface->Start(get_short_class($this), '/Base/CustomField/InsertSubmit', SWIFT_UserInterface::MODE_INSERT, false);
        }

        $_fieldType = SWIFT_CustomField::TYPE_TEXT;
        $_fieldTitle = '';
        $_fieldName = substr(BuildHash(), 1, 12);
        $_fieldDefaultValue = '';
        $_fieldDescription = '';

        $_fieldIsRequired = false;
        $_fieldUserEditable = false;
        $_fieldStaffEditable = true;
        $_customFieldID = 0;
        $_encryptInDB = false;
        $_fieldRegularExpressionValidation = '';

        $_customFieldOptionsContainer = array();

        for ($_ii = 1; $_ii < 9; $_ii++) {
            $_customFieldOptionsContainer[$_ii] = array('optionvalue' => '', 'isselected' => true, 'displayorder' => $_ii, 'suboptions' => array());
        }


        // Get the last display order
        $_displayOrderContainer = $this->Database->QueryFetch("SELECT displayorder FROM " . TABLE_PREFIX . "customfields ORDER BY displayorder DESC");
        if (!isset($_displayOrderContainer['displayorder']) || empty($_displayOrderContainer['displayorder'])) {
            $_fieldDisplayOrder = 1;
        } else {
            $_fieldDisplayOrder = (int)($_displayOrderContainer['displayorder']) + 1;
        }

        if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle');
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('delete'), 'fa-trash', '/Base/CustomField/Delete/' . $_SWIFT_CustomFieldObject->GetCustomFieldID(), SWIFT_UserInterfaceToolbar::LINK_CONFIRM, '', '', false);
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('customfield'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

            $_fieldTitle = $_SWIFT_CustomFieldObject->GetProperty('title');
            $_fieldName = $_SWIFT_CustomFieldObject->GetProperty('fieldname');
            $_fieldDefaultValue = $_SWIFT_CustomFieldObject->GetProperty('defaultvalue');
            $_fieldDescription = $_SWIFT_CustomFieldObject->GetProperty('description');
            $_fieldRegularExpressionValidation = $_SWIFT_CustomFieldObject->GetProperty('regexpvalidate');

            $_customFieldID = (int)($_SWIFT_CustomFieldObject->GetCustomFieldID());
            $_fieldType = (int)($_SWIFT_CustomFieldObject->GetProperty('fieldtype'));
            $_fieldIsRequired = (int)($_SWIFT_CustomFieldObject->GetProperty('isrequired'));
            $_customFieldGroupID = (int)($_SWIFT_CustomFieldObject->GetProperty('customfieldgroupid'));
            $_fieldDisplayOrder = $_SWIFT_CustomFieldObject->GetProperty('displayorder');
            $_fieldUserEditable = (int)($_SWIFT_CustomFieldObject->GetProperty('usereditable'));
            $_fieldStaffEditable = (int)($_SWIFT_CustomFieldObject->GetProperty('staffeditable'));
            $_encryptInDB = (int)($_SWIFT_CustomFieldObject->GetProperty('encryptindb'));

            $_customFieldOptionsContainer = $_SWIFT_CustomFieldObject->GetOptionsContainer();

            if ($_fieldType == SWIFT_CustomField::TYPE_DATE && is_numeric($_fieldDefaultValue)) {
                $_fieldDefaultValue = gmdate(SWIFT_Date::GetCalendarDateFormat(), $_fieldDefaultValue);
            }

        } else {
            $_fieldType = $_POST['fieldtype'];
            $_customFieldGroupID = (int)($_POST['customfieldgroupid']);

            $this->UserInterface->Toolbar->AddButton($this->Language->Get('insert'), 'fa-plus-circle');
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('back'), 'fa-chevron-circle-left', '/Base/CustomField/Insert', SWIFT_UserInterfaceToolbar::LINK_FORM);
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('customfield'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);
        }

        $_SWIFT_CustomFieldGroupObject = new SWIFT_CustomFieldGroup($_customFieldGroupID);
        if (!$_SWIFT_CustomFieldGroupObject instanceof SWIFT_CustomFieldGroup || !$_SWIFT_CustomFieldGroupObject->GetIsClassLoaded()) {
            return false;
        }

        /*
         * ###############################################
         * BEGIN GENERAL TAB
         * ###############################################
         */
        $_labelContainer = SWIFT_CustomField::GetFieldLabel($_fieldType);

        $_GeneralTabObject = $this->UserInterface->AddTab($_labelContainer[0], $_labelContainer[1], 'general', true);

        $this->UserInterface->Hidden('fieldtype', $_fieldType);
        $this->UserInterface->Hidden('customfieldgroupid', $_SWIFT_CustomFieldGroupObject->GetCustomFieldGroupID());

        $_GeneralTabObject->DefaultDescriptionRow($this->Language->Get('cfgroupd'), $this->Language->get('desc_cfgroupd'), htmlspecialchars($_SWIFT_CustomFieldGroupObject->GetProperty('title') . ' (' . SWIFT_CustomFieldGroup::GetGroupLabel($_SWIFT_CustomFieldGroupObject->GetProperty('grouptype')) . ')'));

        $_GeneralTabObject->Text('title', $this->Language->Get('fieldtitle'), $this->Language->Get('desc_fieldtitle'), $_fieldTitle);

        if ($_fieldType == SWIFT_CustomField::TYPE_CUSTOM && $_mode == SWIFT_UserInterface::MODE_INSERT) {
            // Ask for field name
            $_GeneralTabObject->Text('fieldname', $this->Language->Get('fieldname'), $this->Language->Get('desc_fieldname'), 'field_' . substr(BuildHash(), 0, 10));
        } elseif ($_fieldType == SWIFT_CustomField::TYPE_CUSTOM && $_mode == SWIFT_UserInterface::MODE_EDIT) {
            $_GeneralTabObject->DefaultDescriptionRow($this->Language->Get('fieldname'), $this->Language->Get('desc_fieldname'), $_fieldName);

            $this->UserInterface->Hidden('fieldname', $_fieldName);
        } else {
            $this->UserInterface->Hidden('fieldname', $_fieldName);
        }

        if ($_fieldType == SWIFT_CustomField::TYPE_TEXT || $_fieldType == SWIFT_CustomField::TYPE_PASSWORD) {
            $_GeneralTabObject->Text('defaultvalue', $this->Language->Get('defaultvalue'), $this->Language->Get('desc_defaultvalue'), $_fieldDefaultValue);
        } elseif ($_fieldType == SWIFT_CustomField::TYPE_TEXTAREA) {
            $_GeneralTabObject->Textarea("defaultvalue", $this->Language->Get('defaultvalue'), $this->Language->Get('desc_defaultvalue'), $_fieldDefaultValue);
        } elseif ($_fieldType == SWIFT_CustomField::TYPE_DATE) {
            $_GeneralTabObject->Date("defaultvalue", $this->Language->Get('defaultvalue'), $this->Language->Get('desc_defaultvalue'), $_fieldDefaultValue);
        } else {
            $this->UserInterface->Hidden('defaultvalue', '');
        }

        $_GeneralTabObject->Text('description', $this->Language->Get('description'), $this->Language->Get('desc_description'), $_fieldDescription);

        $_GeneralTabObject->Number('displayorder', $this->Language->Get('fielddisplayorder'), $this->Language->Get('desc_fielddisplayorder'), $_fieldDisplayOrder);


        /*
         * ==============================
         * BEGIN OPTIONS RENDERING
         * ==============================
         */

        if ($_fieldType != SWIFT_CustomField::TYPE_TEXT && $_fieldType != SWIFT_CustomField::TYPE_PASSWORD && $_fieldType != SWIFT_CustomField::TYPE_TEXTAREA && $_fieldType != SWIFT_CustomField::TYPE_CUSTOM && $_fieldType != SWIFT_CustomField::TYPE_DATE && $_fieldType != SWIFT_CustomField::TYPE_FILE) {
            $_columnContainer = array();
            $_columnContainer[0]['value'] = '<div class="tabtoolbarsub"><ul><li><a href="javascript:void(0);" onmouseup="javascript:this.blur(); CloneCustomFieldRow(' . IIF($_fieldType == SWIFT_CustomField::TYPE_CHECKBOX || $_fieldType == SWIFT_CustomField::TYPE_SELECTMULTIPLE, 'true', 'false') . ', ' . IIF($_fieldType == SWIFT_CustomField::TYPE_SELECTLINKED, 'true', 'false') . ');"><i class="fa fa-plus-circle" aria-hidden="true"></i> ' . $this->Language->Get('insert') . '</a></li></ul></div>';
            $_columnContainer[0]['align'] = 'left';
            $_columnContainer[0]['valign'] = 'middle';
            $_columnContainer[0]['colspan'] = '2';
            $_columnContainer[0]['class'] = 'settabletitlerowmain2';
            $_columnContainer[0]['nowrap'] = true;

            $_GeneralTabObject->Row($_columnContainer, '', 'tabtoolbartable');

            if ($_fieldType == SWIFT_CustomField::TYPE_SELECTLINKED) {
                $_columnContainer = [
                    [
                        'value' => $this->Language->Get('linkedselect_usage'),
                        'align' => 'left',
                        'valign' => 'middle',
                        'colspan' => '2',
                        'class' => 'settabletitlerowmain2',
                        'nowrap' => true,
                    ],
                ];
                $_GeneralTabObject->Row($_columnContainer);
            }

            $_fieldValue = '<table width="100%"  border="0" cellspacing="0" cellpadding="2" id="customfieldtable"><tr><td class="tabletitle">' . $this->Language->Get('optionvalues') . '</td><td width="150" align="left" class="tabletitle">' . $this->Language->Get('optiondisplayorder') . '</span> </td><td width="150" align="left" class="tabletitle">' . $this->Language->Get('optionisselected') . '</span> </td></tr>';

            $_index = 0;

            foreach ($_customFieldOptionsContainer as $_key => $_val) {
                $_index = $_key;

                if ($_fieldType == SWIFT_CustomField::TYPE_CHECKBOX || $_fieldType == SWIFT_CustomField::TYPE_SELECTMULTIPLE) {
                    $_selectedData = '<td align="left"><input name="fieldlist[' . $_index . '][2]" type="checkbox" value="1"' . IIF($_val['isselected'] == 1, ' checked') . '></td>';
                } else {
                    $_selectedData = '<td align="left"><input name="selectedfield" type="radio" value="' . $_index . '"' . IIF($_val['isselected'] == 1, ' checked') . '></td>';
                }

                $_extendJavaScript = '';

                if (count($_val['suboptions'])) {
                    $_extendJavaScript = 'new Array(';
                    $_entriesContainer = array();
                    for ($ii = 1; $ii <= count($_val['suboptions']); $ii++) {
                        $_entriesContainer[] = "'" . $ii . "'";
                    }

                    $_extendJavaScript .= implode(',', $_entriesContainer);

                    $_extendJavaScript .= ')';
                }

                $_subIndexCount = 0;
                if (count($_val['suboptions'])) {
                    $_subIndexCount = max(array_keys($_val['suboptions']));
                }

                $_fieldValue .= '<tr class="gridrow2" id="cfrow' . $_index . '"><td><input type="text" name="fieldlist[' . $_index . '][0]" class="swifttext" size="30" value="' . htmlspecialchars($_val['optionvalue']) . '">' . IIF($_fieldType == SWIFT_CustomField::TYPE_SELECTLINKED, ' <input type="hidden" id="cfsubcount' . $_index . '" name="cfsubcount' . $_index . '" value="' . $_subIndexCount . '" />&nbsp;&nbsp;<a href="javascript: void(0);" onmousedown="javascript: CloneCustomFieldSubRow(\'' . $_index . '\');"><i class="fa fa-plus-circle" aria-hidden="true"></i></a>') . ' <a href="javascript: void(0);" onmousedown="javascript: ClearCustomFieldRow(\'' . $_index . '\'' . IIF(!empty($_extendJavaScript), ', ' . $_extendJavaScript, ', new Array()') . ');"><i class="fa fa-minus-circle" aria-hidden="true"></i></a></td><td align="left"><input name="fieldlist[' . $_index . '][1]" type="text" size="5" class="swifttext" value="' . (int)($_val['displayorder']) . '"></td>' . $_selectedData . '</tr>';

                if (count($_val['suboptions'])) {
                    $_subIndex = 1;
                    foreach ($_val['suboptions'] as $_subKey => $_subVal) {
                        $_subIndex = $_subKey;

                        $_fieldValue .= '<tr id="cfsubrow' . $_index . '_' . $_subIndex . '"><td><img src="' . SWIFT::Get('themepath') . 'images/linkdownarrow_blue.gif" border="0" align="absmiddle" /> <input type="text" name="subfieldlist[' . $_index . '][' . $_subIndex . '][0]" class="swifttext" size="30" value="' . htmlspecialchars($_subVal['optionvalue']) . '"> <a href="javascript: void(0);" onmousedown="javascript: ClearCustomFieldSubRow(\'' . $_index . '\', \'' . $_subIndex . '\');"><i class="fa fa-minus-circle" aria-hidden="true"></i></a></td><td align="left"><img src="' . SWIFT::Get('themepath') . 'images/linkdownarrow_blue.gif" border="0" align="absmiddle" /> &nbsp; <input name="subfieldlist[' . $_index . '][' . $_subIndex . '][1]" type="text" size="5" class="swifttext" value="' . (int)($_subVal['displayorder']) . '"></td>' . '<td align="left"><img src="' . SWIFT::Get('themepath') . 'images/linkdownarrow_blue.gif" border="0" align="absmiddle" /> &nbsp; <input name="subfieldsellist[' . $_index . ']" type="radio" value="' . $_subIndex . '"' . IIF($_subVal['isselected'] == 1, ' checked') . '></td>' . '</tr>';

                        $_subIndex++;
                    }
                }

                $_index++;
            }

            $_fieldValue .= '</table>';

            $_columnContainer = array();

            $_columnContainer[0]['align'] = 'left';
            $_columnContainer[0]['nowrap'] = true;
            $_columnContainer[0]['colspan'] = '2';
            $_columnContainer[0]['value'] = '<table width="100%"  border="0" cellspacing="0" cellpadding="0"><tr><td width="100%">' . $_fieldValue . '</td></tr></table>';

            $_GeneralTabObject->Row($_columnContainer);

            $_indexCount = 0;
            if (count($_customFieldOptionsContainer)) {
                $_indexCount = max(array_keys($_customFieldOptionsContainer));
            }

            $this->UserInterface->Hidden('maxdisplayorder', $_indexCount);
        }

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

        $_OptionsTabObject->YesNo('isrequired', $this->Language->Get('isrequired'), $this->Language->Get('desc_isrequired'), $_fieldIsRequired);

        $_OptionsTabObject->YesNo('usereditable', $this->Language->Get('usereditable'), $this->Language->Get('desc_usereditable'), $_fieldUserEditable);
        $_OptionsTabObject->YesNo('staffeditable', $this->Language->Get('staffeditable'), $this->Language->Get('desc_staffeditable'), $_fieldStaffEditable);

        $_OptionsTabObject->Text('regexpvalidate', $this->Language->Get('regexpvalidate'), $this->Language->Get('desc_regexpvalidate'), $_fieldRegularExpressionValidation);

        $_OptionsTabObject->YesNo('encryptindb', $this->Language->Get('encryptindb'), $this->Language->Get('desc_encryptindb'), $_encryptInDB);

        /*
         * ###############################################
         * END OPTIONS TAB
         * ###############################################
         */


        /*
         * ###############################################
         * BEGIN LANGUAGES TAB
         * ###############################################
         */

        $_LanguageTabObject = $this->UserInterface->AddTab($this->Language->Get('tablanguages'), 'icon_language2.gif', 'languages');
        $this->Controller->LanguagePhraseLinked->Render(SWIFT_LanguagePhraseLinked::TYPE_CUSTOMFIELD, $_customFieldID, $_mode, $_LanguageTabObject);

        /*
         * ###############################################
         * END LANGUAGES TAB
         * ###############################################
         */

        $this->UserInterface->End();

        return true;
    }

    /**
     * Render the Custom Field Grid
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

        $this->Load->Library('UserInterface:UserInterfaceGrid', array('customfieldgrid'), true, false, 'base');

        if ($this->UserInterfaceGrid->GetMode() == SWIFT_UserInterfaceGrid::MODE_SEARCH) {
            $this->UserInterfaceGrid->SetSearchQuery('SELECT *, customfields.title AS fieldtitle, customfields.displayorder AS fielddisplayorder, customfieldgroups.title AS grouptitle FROM ' . TABLE_PREFIX . 'customfields AS customfields LEFT JOIN ' . TABLE_PREFIX . 'customfieldgroups AS customfieldgroups ON (customfields.customfieldgroupid = customfieldgroups.customfieldgroupid) WHERE (' . $this->UserInterfaceGrid->BuildSQLSearch('customfields.title') . ') OR (' . $this->UserInterfaceGrid->BuildSQLSearch('customfieldgroups.title') . ')', 'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX . 'customfields AS customfields LEFT JOIN ' . TABLE_PREFIX . 'customfieldgroups AS customfieldgroups ON (customfields.customfieldgroupid = customfieldgroups.customfieldgroupid) WHERE (' . $this->UserInterfaceGrid->BuildSQLSearch('customfields.title') . ') OR (' . $this->UserInterfaceGrid->BuildSQLSearch('customfieldgroups.title') . ')');
        }

        $this->UserInterfaceGrid->SetQuery('SELECT *, customfields.title AS fieldtitle, customfields.displayorder AS fielddisplayorder, customfieldgroups.title AS grouptitle FROM ' . TABLE_PREFIX . 'customfields AS customfields LEFT JOIN ' . TABLE_PREFIX . 'customfieldgroups AS customfieldgroups ON (customfields.customfieldgroupid = customfieldgroups.customfieldgroupid)', 'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX . 'customfields AS customfields LEFT JOIN ' . TABLE_PREFIX . 'customfieldgroups AS customfieldgroups ON (customfields.customfieldgroupid = customfieldgroups.customfieldgroupid)');

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('customfieldid', 'customfieldid', SWIFT_UserInterfaceGridField::TYPE_ID));

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('customfields.icon', '&nbsp;', SWIFT_UserInterfaceGridField::TYPE_CUSTOM, 16, SWIFT_UserInterfaceGridField::ALIGN_CENTER));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('customfields.title', $this->Language->Get('fieldtitle'), SWIFT_UserInterfaceGridField::TYPE_DB, 0, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('customfieldgroups.title', $this->Language->Get('grouptitle'), SWIFT_UserInterfaceGridField::TYPE_DB, 180, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('customfields.fieldtype', $this->Language->Get('fieldtype'), SWIFT_UserInterfaceGridField::TYPE_DB, 150, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('customfields.displayorder', $this->Language->Get('fielddisplayorder'), SWIFT_UserInterfaceGridField::TYPE_DB, 120, SWIFT_UserInterfaceGridField::ALIGN_CENTER, SWIFT_UserInterfaceGridField::SORT_ASC), true);

        $this->UserInterfaceGrid->SetRenderCallback(array($this, 'GridRender'));
        $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('delete'), 'fa-trash', array('Base\Admin\Controller_CustomField', 'DeleteList'), $this->Language->Get('actionconfirm')));
        $this->UserInterfaceGrid->SetNewLinkViewport(SWIFT::Get('basename') . '/Base/CustomField/Insert');

        if ($_SWIFT->Staff->GetPermission('admin_canupdatecustomfield') != '0') {
            $this->UserInterfaceGrid->SetSortableCallback('displayorder', array('Base\Admin\Controller_CustomField', 'SortList'));
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

        $_fieldContainer['customfields.title'] = "<a href=\"" . SWIFT::Get('basename') . "/Base/CustomField/Edit/" . (int)($_fieldContainer['customfieldid']) . "\" viewport=\"1\"> " . htmlspecialchars($_fieldContainer['fieldtitle']) . '</a>';

        $_fieldContainer['customfieldgroups.title'] = htmlspecialchars($_fieldContainer['grouptitle']);
        $_fieldContainer['customfields.displayorder'] = (int)($_fieldContainer['fielddisplayorder']);

        $_fieldType = $_fieldContainer['fieldtype'];

        $_fieldLabelContainer = SWIFT_CustomField::GetFieldLabel($_fieldType);
        if (isset($_fieldLabelContainer[0]) && isset($_fieldLabelContainer[1]) && !empty($_fieldLabelContainer[0]) && !empty($_fieldLabelContainer[1])) {
            $_fieldContainer['customfields.icon'] = '<img src="' . SWIFT::Get('themepath') . 'images/' . $_fieldLabelContainer[1] . '" border="0" align="absmiddle" />';
            $_fieldContainer['customfields.fieldtype'] = '<img src="' . SWIFT::Get('themepath') . 'images/' . $_fieldLabelContainer[1] . '" border="0" align="absmiddle" /> &nbsp;' . htmlspecialchars($_fieldLabelContainer[0]);
        } else {
            $_fieldContainer['customfields.icon'] = '<img src="' . SWIFT::Get('themepath') . 'images/' . 'space.gif' . '" border="0" align="absmiddle" />';
            $_fieldContainer['customfields.fieldtype'] = '<img src="' . SWIFT::Get('themepath') . 'images/' . 'space.gif' . '" border="0" align="absmiddle" /> &nbsp;' . htmlspecialchars($_SWIFT->Language->Get('na'));
        }

        return $_fieldContainer;
    }
}

?>
