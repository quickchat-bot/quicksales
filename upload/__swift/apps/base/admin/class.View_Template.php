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
use SWIFT_Date;
use SWIFT_Exception;
use Base\Library\Help\SWIFT_Help;
use Base\Models\Template\SWIFT_Template;
use Base\Library\Template\SWIFT_Template_Exception;
use Base\Models\Template\SWIFT_TemplateCategory;
use Base\Models\Template\SWIFT_TemplateHistory;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;
use SWIFT_View;

/**
 * The Template View
 *
 * @property \Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel $UserInterface
 * @author Varun Shoor
 */
class View_Template extends SWIFT_View
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
     * Render the Template Edit Form
     *
     * @author Varun Shoor
     * @param SWIFT_Template $_SWIFT_TemplateObject The SWIFT_Template Object Pointer
     * @param int $_templateHistoryID (OPTIONAL) The Custom History Item to Load
     * @param string $_diffInlineHTML (OPTIONAL) The Inline Diff Comparison HTML
     * @param bool $_isSearch (OPTIONAL) Whether this is a search call
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or if Invalid Data is Provided
     */
    public function RenderTemplate(SWIFT_Template $_SWIFT_TemplateObject, $_templateHistoryID = null, $_diffInlineHTML = '', $_isSearch = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } elseif (!$_SWIFT_TemplateObject instanceof SWIFT_Template || !$_SWIFT_TemplateObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_templateGroupCache = $this->Cache->Get('templategroupcache');
        $_staffCache = $this->Cache->Get('staffcache');

        if (!isset($_templateGroupCache[$_SWIFT_TemplateObject->GetProperty('tgroupid')])) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_isDiffTabActive = false;
        if (!empty($_diffInlineHTML)) {
            $_isDiffTabActive = true;
        }

        $this->UserInterface->Start(get_short_class($this), '/Base/Template/EditSubmit/' . $_SWIFT_TemplateObject->GetTemplateID(), SWIFT_UserInterface::MODE_EDIT, false);

        if ($_isSearch) {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('back'), 'fa-chevron-circle-left', '/Base/TemplateSearch/RunSearch', SWIFT_UserInterfaceToolbar::LINK_VIEWPORT);
        } else {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('back'), 'fa-chevron-circle-left', '/Base/Template/Manage/' . $_SWIFT_TemplateObject->GetProperty('tgroupid') . '/' . $_SWIFT_TemplateObject->GetProperty('tcategoryid'), SWIFT_UserInterfaceToolbar::LINK_VIEWPORT);
        }

        if ($_SWIFT->Staff->GetPermission('admin_tmpcanupdatetemplate') != '0') {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('save'), 'fa-save');
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('saveandreload'), 'fa-repeat', '/Base/Template/EditSubmit/' . $_SWIFT_TemplateObject->GetTemplateID() . '/1', SWIFT_UserInterfaceToolbar::LINK_FORM);
        }

        if ($_SWIFT->Staff->GetPermission('admin_tmpcanrestoretemplates') != '0') {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('restore'), 'fa-rotate-left', '/Base/Template/Restore/' . $_SWIFT_TemplateObject->GetTemplateID(), SWIFT_UserInterfaceToolbar::LINK_CONFIRM, '', '', false);
        }

//        $this->UserInterface->Toolbar->AddButton($this->Language->Get('preview'), 'icon_preview.gif', 'PreviewTemplate(\'' . $_SWIFT_TemplateObject->GetTemplateID() . '\');', SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT);
//        $this->UserInterface->Toolbar->AddButton($this->Language->Get('copyclipboard'), 'icon_copytoclipboard.gif', 'CopyTemplateToClipboard();', SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT);

        if ($_SWIFT_TemplateObject->GetProperty('iscustom') == '1') {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('delete'), 'fa-trash', '/Base/Template/Delete/' . $_SWIFT_TemplateObject->GetTemplateID(), SWIFT_UserInterfaceToolbar::LINK_CONFIRM, '', '', false);
        }

        $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('template'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        /**
         * Process the Active Template Data
         */
        $_activeTemplateIsCurrent = true;
        $_templateModified = $_SWIFT_TemplateObject->GetProperty('modified');
        $_activeTemplateDateline = $_SWIFT_TemplateObject->GetProperty('dateline');
        $_activeTemplateVersion = $_SWIFT_TemplateObject->GetProperty('templateversion');
        $_activeTemplateContents = $_SWIFT_TemplateObject->GetProperty('contents');

        if (!empty($_templateHistoryID)) {
            try {
                $_SWIFT_TemplateHistoryObject = new SWIFT_TemplateHistory($_templateHistoryID);
                if ($_SWIFT_TemplateHistoryObject instanceof SWIFT_TemplateHistory && $_SWIFT_TemplateHistoryObject->GetIsClassLoaded() && $_SWIFT_TemplateHistoryObject->GetProperty('templateid') == $_SWIFT_TemplateObject->GetTemplateID()) {
                    $_activeTemplateDateline = $_SWIFT_TemplateHistoryObject->GetProperty('dateline');
                    $_activeTemplateVersion = $_SWIFT_TemplateHistoryObject->GetProperty('templateversion');
                    $_activeTemplateContents = $_SWIFT_TemplateHistoryObject->GetProperty('contents');
                    $_activeTemplateIsCurrent = false;
                }
            } catch (SWIFT_Template_Exception $_SWIFT_Template_ExceptionObject) {

            }
        }

        $_modifiedContainer = SWIFT_Template::GetModifiedHTML($_templateModified);
        if (!$_modifiedContainer) {
            $_modifiedContainer = ['', ''];
        }

        $_modifiedStatus = $_modifiedContainer[0];
        $_modifiedText = $_modifiedContainer[1];
        $_activeTemplateStatus = '<img src="' . SWIFT::Get('themepath') . $_modifiedStatus . '" align="absmiddle" border="0" /> ' . $_modifiedText;


        /*
         * ###############################################
         * BEGIN TEMPLATE TAB
         * ###############################################
         */

        $_TemplateTabObject = $this->UserInterface->AddTab(sprintf($this->Language->Get('tabedittemplate'), htmlspecialchars($_SWIFT_TemplateObject->GetProperty('name')), htmlspecialchars($_templateGroupCache[$_SWIFT_TemplateObject->GetProperty('tgroupid')]['title'])), 'icon_template.gif', 'general', IIF(!$_isDiffTabActive, true, false));

        $_TemplateTabObject->RowHTML($this->UserInterface->GetAlert($this->Language->Get('templateeditingguideline'), $this->Language->Get('desc_templateeditingguideline')));

        $_TemplateTabObject->DefaultDescriptionRow($this->Language->Get('templatestatus'), $this->Language->Get('desc_templatestatus'), $_activeTemplateStatus);

        $_TemplateTabObject->DefaultDescriptionRow($this->Language->Get('dateadded'), $this->Language->Get('desc_dateadded'), SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_activeTemplateDateline));

        $_TemplateTabObject->DefaultDescriptionRow($this->Language->Get('templateversion'), $this->Language->Get('desc_templateversion'), '<b>' . $_activeTemplateVersion . '</b>' . IIF($_activeTemplateIsCurrent, ' (' . $this->Language->Get('current') . ')', ' <font color="red"><b><i>(' . $this->Language->Get('notcurrenttemp') . ')</i></b></font>'));

        $_TemplateTabObject->YesNo('saveasnewversion', $this->Language->Get('saveasnewversion'), '', true);
        $_TemplateTabObject->Text('changelognotes', $this->Language->Get('changelognotes'), $this->Language->Get('desc_changelognotes'), '');

        $_TemplateTabObject->Title($this->Language->Get('templatedata'), 'icon_doublearrows.gif');

        $_TemplateTabObject->TextArea('templatecontents', '', '', $_activeTemplateContents, 100, 32);

        /*
         * ###############################################
         * END TEMPLATE TAB
         * ###############################################
         */

        /*
         * ###############################################
         * BEGIN HISTORY TAB
         * ###############################################
         */

        $_HistoryTabObject = $this->UserInterface->AddTab($this->Language->Get('tabhistory'), 'icon_history.gif', 'history', false);
        $_HistoryTabObject->LoadToolbar();
        $_HistoryTabObject->Toolbar->AddButton($this->Language->Get('back'), 'fa-chevron-circle-left', '/Base/Template/Manage/' . $_SWIFT_TemplateObject->GetProperty('tgroupid') . '/' . $_SWIFT_TemplateObject->GetProperty('tcategoryid'), SWIFT_UserInterfaceToolbar::LINK_VIEWPORT);
        $_HistoryTabObject->Toolbar->AddButton($this->Language->Get('compare'), 'fa-random', '/Base/Template/Diff/' . $_SWIFT_TemplateObject->GetTemplateID(), SWIFT_UserInterfaceToolbar::LINK_FORM);
        $_HistoryTabObject->Toolbar->AddButton($this->Language->Get('exportdiff'), 'fa-sign-out', 'ExportTemplateDiff(\'' . $_SWIFT_TemplateObject->GetTemplateID() . '\');', SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, '', '', false);
        $_HistoryTabObject->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('template'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        $_templateHistoryContainer = SWIFT_TemplateHistory::RetrieveListOnTemplate($_SWIFT_TemplateObject->GetTemplateID());

        if (!count($_templateHistoryContainer)) {
            $_HistoryTabObject->Alert($this->Language->Get('titlenohistory'), sprintf($this->Language->Get('msgnohistory'), htmlspecialchars($_SWIFT_TemplateObject->GetProperty('name'))));
        } else {
            $_compareTemplateHistoryID1 = $_compareTemplateHistoryID2 = false;

            if (isset($_POST['comparetemplatehistoryid1'])) {
                $_compareTemplateHistoryID1 = (int)($_POST['comparetemplatehistoryid1']);
            }

            if (isset($_POST['comparetemplatehistoryid2'])) {
                $_compareTemplateHistoryID2 = (int)($_POST['comparetemplatehistoryid2']);
            }

            $_columnContainer = array();
            $_columnContainer[0]['width'] = '16';
            $_columnContainer[0]["value"] = '&nbsp;';
            $_columnContainer[0]['align'] = 'center';

            $_columnContainer[1]['width'] = '16';
            $_columnContainer[1]['value'] = '&nbsp;';
            $_columnContainer[1]['align'] = 'center';

            $_columnContainer[2]['align'] = 'left';
            $_columnContainer[2]['nowrap'] = true;
            $_columnContainer[2]['value'] = $this->Language->Get('historydescription');
            $_HistoryTabObject->Row($_columnContainer, 'gridtabletitlerow');

            $_columnContainer = array();
            $_columnContainer[0]['width'] = '16';
            $_columnContainer[0]['value'] = '<input type="radio" name="comparetemplatehistoryid1" value="0"' . IIF(empty($_compareTemplateHistoryID1), ' checked') . ' />';
            $_columnContainer[0]['align'] = "center";

            $_columnContainer[1]['width'] = '16';
            $_columnContainer[1]['value'] = '&nbsp;';
            $_columnContainer[1]['align'] = 'center';

            $_columnContainer[2]['align'] = 'left';
            $_columnContainer[2]['value'] = '<a href="' . SWIFT::Get('basename') . '/Base/Template/Edit/' . (int)($_SWIFT_TemplateObject->GetTemplateID()) . '" viewport="1">' . sprintf($this->Language->Get('historyitemcurrent'), htmlspecialchars($_SWIFT_TemplateObject->GetProperty('templateversion')), SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_SWIFT_TemplateObject->GetProperty('dateline'))) . '</a>';
            $_HistoryTabObject->Row($_columnContainer);

            $_isItemSelected = false;
            foreach ($_templateHistoryContainer as $_key => $_val) {
                $_historyCreator = $this->Language->Get('system');
                if (!empty($_val['staffid']) && isset($_staffCache[$_val['staffid']])) {
                    $_historyCreator = text_to_html_entities($_staffCache[$_val['staffid']]['fullname']);
                }

                $_columnContainer = array();
                $_columnContainer[0]['width'] = '16';
                $_columnContainer[0]['value'] = '<input type="radio" name="comparetemplatehistoryid1" value="' . (int)($_val['templatehistoryid']) . '"' . IIF($_compareTemplateHistoryID1 == $_val['templatehistoryid'], ' checked') . ' />';
                $_columnContainer[0]['align'] = 'center';

                $_columnContainer[1]['width'] = '16';
                $_columnContainer[1]['value'] = '<input type="radio" name="comparetemplatehistoryid2" value="' . (int)($_val['templatehistoryid']) . '"' . IIF(($_compareTemplateHistoryID2 == $_val['templatehistoryid']) || (empty($_compareTemplateHistoryID2) && !$_isItemSelected), ' checked') . ' />';
                $_columnContainer[1]['align'] = 'center';

                $_columnContainer[2]['align'] = 'left';
                $_columnContainer[2]['value'] = '<a href="' . SWIFT::Get('basename') . '/Base/Template/Edit/' . (int)($_val['templateid']) . '/' . (int)($_val['templatehistoryid']) . '" viewport="1">' . sprintf($this->Language->Get('historyitemlist'), htmlspecialchars($_val['templateversion']), $_historyCreator, SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_val['dateline']), htmlspecialchars(IIF(empty($_val['changelognotes']), $this->Language->Get('none'), $_val['changelognotes']))) . '</a>';

                $_HistoryTabObject->Row($_columnContainer);

                $_isItemSelected = true;
            }
        }


        /*
         * ###############################################
         * END HISTORY TAB
         * ###############################################
         */


        /*
         * ###############################################
         * BEGIN DIFF TAB
         * ###############################################
         */

        if ($_isDiffTabActive) {
            $_DiffTabObject = $this->UserInterface->AddTab($this->Language->Get('tabcomparison'), 'icon_diff.gif', 'diff', $_isDiffTabActive);
            $_DiffTabObject->LoadToolbar();
            $_DiffTabObject->Toolbar->AddButton($this->Language->Get('back'), 'fa-chevron-circle-left', '/Base/Template/Manage/' . $_SWIFT_TemplateObject->GetProperty('tgroupid') . '/' . $_SWIFT_TemplateObject->GetProperty('tcategoryid'), SWIFT_UserInterfaceToolbar::LINK_VIEWPORT);
            $_DiffTabObject->Toolbar->AddButton($this->Language->Get('exportdiff'), 'fa-sign-out', 'ExportTemplateDiff(\'' . $_SWIFT_TemplateObject->GetTemplateID() . '\');', SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, '', '', false);
            $_DiffTabObject->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('template'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

            $_DiffTabObject->PrependHTML('<div style="width: 100%;">' . $_diffInlineHTML . '</div>');
        }

        /*
         * ###############################################
         * END DIFF TAB
         * ###############################################
         */

        $this->UserInterface->End();

        return true;
    }

    /**
     * Render the Template Insert Form
     *
     * @author Varun Shoor
     * @param int $_templateGroupID The Template Group ID
     * @param SWIFT_TemplateCategory $_SWIFT_TemplateCategoryObject The SWIFT_TemplateCategory Object Pointer
     * @return bool "true" on Success, "false" otherwise
     */
    public function RenderInsert($_templateGroupID, SWIFT_TemplateCategory $_SWIFT_TemplateCategoryObject)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $_templateGroupCache = $this->Cache->Get('templategroupcache');

        $this->UserInterface->Start(get_short_class($this), '/Base/Template/InsertSubmit', SWIFT_UserInterface::MODE_INSERT, false);

        $this->UserInterface->Toolbar->AddButton($this->Language->Get('back'), 'fa-chevron-circle-left', '/Base/Template/Manage/' . $_templateGroupID . '/' . $_SWIFT_TemplateCategoryObject->GetTemplateCategoryID(), SWIFT_UserInterfaceToolbar::LINK_VIEWPORT);
        $this->UserInterface->Toolbar->AddButton($this->Language->Get('save'), 'fa-save');
        $this->UserInterface->Toolbar->AddButton($this->Language->Get('saveandreload'), 'fa-repeat', '/Base/Template/InsertSubmit/1', SWIFT_UserInterfaceToolbar::LINK_FORM);
        $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('template'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        $this->UserInterface->Hidden('templatecategoryid', $_SWIFT_TemplateCategoryObject->GetTemplateCategoryID());
        $this->UserInterface->Hidden('templategroupid', $_templateGroupID);

        /*
         * ###############################################
         * BEGIN TEMPLATE TAB
         * ###############################################
         */
        $_TemplateTabObject = $this->UserInterface->AddTab($this->Language->Get('inserttemplate'), 'icon_template.gif', 'general', true);

        $_TemplateTabObject->Text('name', $this->Language->Get('inserttemplatename'), $this->Language->Get('desc_inserttemplatename'), '');
        $_TemplateTabObject->DefaultDescriptionRow($this->Language->Get('inserttemplatetgroup'), '', htmlspecialchars($_templateGroupCache[$_templateGroupID]['title']));

        $_TemplateTabObject->DefaultDescriptionRow($this->Language->Get('inserttemplatetcategory'), '', htmlspecialchars($_SWIFT_TemplateCategoryObject->GetLabel()));

        $_TemplateTabObject->Title($this->Language->Get('templatedata'), 'icon_doublearrows.gif');

        $_templateContents = '';
        if (isset($_POST['contents'])) {
            $_templateContents = $_POST['contents'];
        }

        $_TemplateTabObject->TextArea('templatecontents', '', '', $_templateContents, 100, 32);

        /*
         * ###############################################
         * END TEMPLATE TAB
         * ###############################################
         */

        $this->UserInterface->End();

        return true;
    }

    /**
     * Render the Template Manage Section
     *
     * @author Varun Shoor
     * @param int $_templateGroupID (OPTIONAL) The Template Group ID
     * @param int $_templateCategoryID (OPTIONAL) The Expanded Template Category ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public function RenderManage($_templateGroupID, $_templateCategoryID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $_templateGroupID = $_templateGroupID;
        $_masterTemplateGroupID = false;

        $_templateGroupCache = $this->Cache->Get('templategroupcache');
        if (isset($_GET['templategroupid']) && isset($_templateGroupCache[$_GET['templategroupcache']])) {
            $_templateGroupID = $_templateGroupCache[$_GET['templategroupcache']];
        }

        foreach ($_templateGroupCache as $_key => $_val) {
            if ($_val['ismaster'] == '1') {
                $_masterTemplateGroupID = $_key;

                break;
            }
        }

        if (empty($_templateGroupID)) {
            $_templateGroupID = $_masterTemplateGroupID;
        }

        if (empty($_templateGroupID) || !isset($_templateGroupCache[$_templateGroupID]) && !empty($_masterTemplateGroupID)) {
            $_templateGroupID = $_masterTemplateGroupID;
        } elseif (empty($_templateGroupID) || !isset($_templateGroupCache[$_templateGroupID])) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->UserInterface->Start(get_short_class($this), '/Base/Template/Manage', SWIFT_UserInterface::MODE_INSERT, false);

        $this->UserInterface->Toolbar->AddButton($this->Language->Get('changegroup'), 'fa-check-circle');
        $this->UserInterface->Toolbar->AddButton($this->Language->Get('expandcontract'), 'fa-arrows-h', 'ExpandContractTemplates();', SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, '', '', false);
        $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('template'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        $_templateContainer = array();

        // Build an array of all templates
        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "templates WHERE tgroupid = '" . $_templateGroupID . "'");
        while ($this->Database->NextRecord()) {
            $_templateContainer[$this->Database->Record['tcategoryid']][] = $this->Database->Record;
        }

        $_POST['tgroupid'] = $_templateGroupID;

        /*
         * ###############################################
         * BEGIN TEMPLATES TAB
         * ###############################################
         */
        $_TemplatesTabObject = $this->UserInterface->AddTab(sprintf($this->Language->Get('templatetitle'), '<b>' . $_templateGroupCache[$_templateGroupID]['title'] . '</b>'), 'icon_template.gif', 'general', true);

        $_optionsContainer = array();
        $_index = 0;
        foreach ($_templateGroupCache as $_key => $_val) {
            $_optionsContainer[$_index]['title'] = $_val['title'] . ' (' . $_val['companyname'] . ')';
            $_optionsContainer[$_index]['value'] = $_val['tgroupid'];

            if ($_templateGroupID == $_val['tgroupid']) {
                $_optionsContainer[$_index]['selected'] = true;
            }
            $_index++;
        }

        $_TemplatesTabObject->Select('templategroupid', $this->Language->Get('diagtgroup'), $this->Language->Get(''), $_optionsContainer);
        $_TemplatesTabObject->Title($this->Language->Get('templates'), 'icon_doublearrows.gif');

        // Build the template category list
        $_templateCategoryIDList = array();
        $_categoryHTML = '';
        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "templatecategories WHERE tgroupid = '" . $_templateGroupID . "'");
        while ($this->Database->NextRecord()) {
            if (!SWIFT_App::IsInstalled($this->Database->Record['app'])) {
                continue;
            }

            if ($this->Language->Get($this->Database->Record['name'])) {
                $_templateName = $this->Language->Get($this->Database->Record['name']);
            } else {
                $_templateName = $this->Database->Record['name'];
            }

            $_templateCategoryIDList[] = $this->Database->Record['tcategoryid'];

            $_categoryHTML .= '<table width="100%" border="0" cellpadding="3" cellspacing="0" class="gridcontentborder"><tr class="' . $_TemplatesTabObject->GetClass() . '"><td width="16" align="center" valign="middle" class="gridtabletitlerow">' . '<img src="' . SWIFT::Get('themepath') . 'images/' . IIF(!empty($this->Database->Record['icon']), htmlspecialchars($this->Database->Record['icon']), 'icon_template.gif') . '" align="absmiddle" border="0" />' . '</td>';
            $_categoryHTML .= '<td align="left" valign="middle" nowrap>' . '<a href="javascript: void(0);" onclick="javascript: $(\'#category' . $this->Database->Record['tcategoryid'] . '\').toggle();"><b>' . $_templateName . '</b></a>' . '</td>';
            $_categoryHTML .= '<td align="center" valign="middle" width="150" nowrap>' . '<a href="javascript: void(0);" onclick="javascript: $(\'#category' . $this->Database->Record['tcategoryid'] . '\').show();"><img src="' . SWIFT::Get('themepath') . 'images/icon_expand.gif" border="0" align="absmiddle" />&nbsp;' . $this->Language->Get('expand') . '</a>' . '</td>';
            $_categoryHTML .= '</tr></table>';

            if ($_templateCategoryID == $this->Database->Record['tcategoryid']) {
                $_displayStyle = 'block';
            } else {
                $_displayStyle = 'none';
            }

            if (isset($_templateContainer[$this->Database->Record['tcategoryid']]) && _is_array($_templateContainer[$this->Database->Record['tcategoryid']])) {
                $_renderHTML = '<div id="category' . $this->Database->Record['tcategoryid'] . '" style="DISPLAY: ' . $_displayStyle . ';">';
                $_renderHTML .= '<table width="100%" border="0" cellspacing="0" cellpadding="0"><tr><td align="left" valign="middle" class="settabletitlerowmain2" id="tabtoolbar">';
                $_renderHTML .= '<div class="tabtoolbarsub"><ul>' . IIF($_SWIFT->Staff->GetPermission('admin_tmpcaninserttemplate') != '0', '<li><a href="' . SWIFT::Get('basename') . '/Base/Template/Insert/' . (int)($this->Database->Record['tgroupid']) . '/' . (int)($this->Database->Record['tcategoryid']) . '" viewport="1"><img border="0" align="absmiddle" src="' . SWIFT::Get('themepath') . 'images/icon_template.gif' . '" /> ' . $this->Language->Get('inserttemplate') . '</a></li>') . IIF($_SWIFT->Staff->GetPermission('admin_tmpcanrestoretemplates') != '0', '<li><a href="javascript:void(0);" onmouseup="javascript:this.blur(); doConfirm(\'' . $this->Language->Get('restoreconfirmaskcat') . '\', _baseName + \'/Base/Template/RestoreCategory/' . (int)($this->Database->Record['tgroupid']) . '/' . (int)($this->Database->Record['tcategoryid']) . '\');"><img border="0" align="absmiddle" src="' . SWIFT::Get('themepath') . 'images/icon_restore.gif' . '" /> ' . $this->Language->Get('restore') . '</a></li>') . '</ul></div>';
                $_renderHTML .= '</table>';

                $_renderHTML .= '<table width="100%" border="0" cellpadding="1" cellspacing="0" class="gridcontentborder"><tr><td>';

                $_templateClass = 'gridrow2';
                $_renderHTML .= '<table width="100%" border="0" cellspacing="0" cellpadding="4">';

                foreach ($_templateContainer[$this->Database->Record['tcategoryid']] as $_key => $_val) {
                    if ($_templateClass == 'gridrow2') {
                        $_templateClass = 'gridrow1';
                    } else {
                        $_templateClass = 'gridrow2';
                    }

                    $_modifiedContainer = SWIFT_Template::GetModifiedHTML($_val['modified']);
                    if (!$_modifiedContainer) {
                        continue;
                    }

                    $_modifiedStatus = $_modifiedContainer[0];
                    $_modifiedText = $_modifiedContainer[1];

                    $_renderHTML .= '<tr class="' . $_templateClass . '"><td width="10"><img src="' . SWIFT::Get('themepath') . $_modifiedStatus . '" border="0" align="absmiddle" /></td><td>' . IIF($_val['iscustom'] == '1', '<i>') . '<a href="' . SWIFT::Get('basename') . '/Base/Template/Edit/' . $_val['templateid'] . '" viewport="1">' . htmlspecialchars($_val['name']) . '</a>' . IIF($_val['iscustom'] == '1', '</i>') . '</td><td align="right" width="250">' . $_modifiedText . '</td></tr>';
                }

                $_renderHTML .= '</table>';

                $_renderHTML .= '</td></tr></table></div>';

                $_categoryHTML .= $_renderHTML;
            }
        }

        $_TemplatesTabObject->RowHTML('<tr class="gridrow1"><td colspan="2" style="padding: 0px;">' . $_categoryHTML . '</td></tr>');

        /*
         * ###############################################
         * END TEMPLATES TAB
         * ###############################################
         */

        $this->UserInterface->End();

        return true;
    }
}

?>
