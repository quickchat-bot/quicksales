<?php
/**
 * ###############################################
 *
 * SWIFT Framework
 * _______________________________________________
 *
 * @author         Varun Shoor
 *
 * @package        SWIFT
 * @copyright      Copyright (c) 2001-2012, QuickSupport
 * @license        http://www.kayako.com/license
 * @link           http://www.kayako.com
 *
 * ###############################################
 */

namespace Parser\Admin;

use Base\Library\Help\SWIFT_Help;
use Base\Library\Rules\SWIFT_Rules;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel;
use Base\Library\UserInterface\SWIFT_UserInterfaceGrid;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridField;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridMassAction;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;
use Base\Models\Department\SWIFT_Department;
use SWIFT;
use SWIFT_Date;
use SWIFT_Exception;
use Parser\Models\Rule\SWIFT_ParserRule;
use SWIFT_View;

/**
 * The Parser Rule View
 *
 * @property \Tickets\Library\Flag\SWIFT_TicketFlag $TicketFlag
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @author Varun Shoor
 */
class View_Rule extends SWIFT_View
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
     * Render the Parser Rule Form
     *
     * @author Varun Shoor
     * @author Abdulrahman Suleiman <abdulrahman.suleiman@crossover.com>
     *
     * @param int    $_mode                   The Render Mode
     * @param SWIFT_ParserRule $_SWIFT_ParserRuleObject The Parser\Models\Rule\SWIFT_ParserRule Object Pointer (Only for EDIT Mode)
     *
     * @return bool "true" on Success, "false" otherwise
     */
    public function Render($_mode, SWIFT_ParserRule $_SWIFT_ParserRuleObject = null)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->Cache->Queue('departmentcache', 'staffcache', 'statuscache', 'prioritycache', 'slaplancache', 'tickettypecache');
        $this->Cache->LoadQueue();

        $_departmentCache = $this->Cache->Get('departmentcache');
        $_staffCache = $this->Cache->Get('staffcache');
        $_statusCache = $this->Cache->Get('statuscache');
        $_priorityCache = $this->Cache->Get('prioritycache');
        $_slaPlanCache = $this->Cache->Get('slaplancache');
        $_ticketTypeCache = $this->Cache->Get('tickettypecache');

        $_criteriaPointer = SWIFT_ParserRule::GetCriteriaPointer();
        SWIFT_ParserRule::ExtendCustomCriteria($_criteriaPointer);
        SWIFT_Rules::CriteriaPointerToJavaScriptArray($_criteriaPointer);

        if (isset($_POST['rulecriteria'])) {
            SWIFT_ParserRule::CriteriaActionsPointerToJavaScript($_POST['rulecriteria'], array());
        }

        // Calculate the URL
        if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            $this->UserInterface->Start(get_short_class($this), '/Parser/Rule/EditSubmit/' . $_SWIFT_ParserRuleObject->GetParserRuleID(),
                SWIFT_UserInterface::MODE_EDIT, false);
        } else {
            $this->UserInterface->Start(get_short_class($this), '/Parser/Rule/InsertSubmit', SWIFT_UserInterface::MODE_INSERT, false);
        }

        $_parserRuleTitle = '';
        $_parserRuleIsEnabled = true;
        $_parserRuleStopProcessing = true;
        $_parserRuleSortOrder = 1;
        $_parserRuleMatchType = SWIFT_Rules::RULE_MATCHALL;
        $_parserRuleType = SWIFT_ParserRule::TYPE_PREPARSE;

        $_ruleAddTagsList = $_ruleRemoveTagsList = array();

        $_sortOrderContainer = $this->Database->QueryFetch("SELECT sortorder FROM " . TABLE_PREFIX . "parserrules ORDER BY sortorder DESC");
        if (isset($_sortOrderContainer['sortorder']) && !empty($_sortOrderContainer['sortorder'])) {
            $_parserRuleSortOrder = (int)($_sortOrderContainer['sortorder']) + 1;
        }

        if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle');

            $this->UserInterface->Toolbar->AddButton($this->Language->Get('delete'), 'fa-trash', '/Parser/Rule/Delete/' .
                $_SWIFT_ParserRuleObject->GetParserRuleID(), SWIFT_UserInterfaceToolbar::LINK_CONFIRM, '', '', false);
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('parserrule'),
                SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

            $_parserRuleTitle = $_SWIFT_ParserRuleObject->GetProperty('title');
            $_parserRuleIsEnabled = (int)($_SWIFT_ParserRuleObject->GetProperty('isenabled'));
            $_parserRuleStopProcessing = (int)($_SWIFT_ParserRuleObject->GetProperty('stopprocessing'));
            $_parserRuleSortOrder = (int)($_SWIFT_ParserRuleObject->GetProperty('sortorder'));
            $_parserRuleMatchType = $_SWIFT_ParserRuleObject->GetProperty('matchtype');
            $_parserRuleType = (int)($_SWIFT_ParserRuleObject->GetProperty('ruletype'));
        } else {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('insert'), 'fa-plus-circle');
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('parserrule'),
                SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);
        }


        /*
         * ###############################################
         * BEGIN GENERAL TAB
         * ###############################################
         */
        $_GeneralTabObject = $this->UserInterface->AddTab($this->Language->Get('tabgeneral'), 'icon_form.gif', 'general', true);

        $_GeneralTabObject->Text('title', $this->Language->Get('ptitle'), $this->Language->Get('desc_ptitle'), $_parserRuleTitle);

        $_GeneralTabObject->YesNo('isenabled', $this->Language->Get('isenabled'), $this->Language->Get('desc_isenabled'), $_parserRuleIsEnabled);
        $_GeneralTabObject->YesNo('stopprocessing', $this->Language->Get('pstop'), $this->Language->Get('desc_pstop'), $_parserRuleStopProcessing);

        $_GeneralTabObject->Number('sortorder', $this->Language->Get('sortorder'), $this->Language->Get('desc_sortorder'), $_parserRuleSortOrder);

        /*
        $_optionsContainer = array();
        $_index = 0;
        foreach (array(SWIFT_Rules::RULE_MATCHALL, SWIFT_Rules::RULE_MATCHANY) as $_key => $_val)
        {
            $_optionsContainer[$_index]['title'] = $this->Language->Get('smatch' . IIF($_val == SWIFT_Rules::RULE_MATCHALL, 'all', 'any'));
            $_optionsContainer[$_index]['value'] = $_val;
            $_optionsContainer[$_index]['checked'] = IIF($_parserRuleMatchType == $_val, true, false);

            $_index++;
        }

        $_GeneralTabObject->Radio('ruleoptions', $this->Language->Get('matchtype'), $this->Language->Get('desc_matchtype'), $_optionsContainer);
        */

        $_GeneralTabObject->Hidden('ruleoptions', SWIFT_Rules::RULE_MATCHEXTENDED);

        $_appendHTML = '<tr id="tabtoolbar"><td align="left" valign="top" colspan="2" class="settabletitlerowmain2"><div class="tabtoolbarsub"><ul>
            <li><a href="javascript:void(0);" onmouseup="javascript:this.blur();
            newGlobalRuleCriteria(\'' . SWIFT_ParserRule::PARSER_SUBJECT . '\', \'' . SWIFT_Rules::OP_CONTAINS . '\', \'\', \'1\', \'1\');"><img border="0"
                align="absmiddle" src="' . SWIFT::Get('themepath') . 'images/icon_insertcriteria.gif' . '" /> ' .
            $this->Language->Get('insertcriteria') . '</a></li></ul></div></td>';

        $_appendHTML .= '<tr class="gridrow2"><td align="left" colspan="2" class="smalltext"><div id="ruleparent"></div></td></tr>';
        $_GeneralTabObject->AppendHTML($_appendHTML);

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

        $_parserRuleType = $_POST['ruletype'] ?? $_parserRuleType;
        $_ActionsTabObject = $this->UserInterface->AddTab($this->Language->Get('tabactions'), 'icon_actions.gif', 'actions');
        $_ActionsTabObject->Title('<b>' . $this->Language->Get('iruletype') . '</b>&nbsp;<label for="preparse"><input type="radio" id="preparse"
            name="ruletype" onClick="this.blur(); if (this.checked) {SwitchRuleType(1);}" style="PADDING: 0px;" value="1"' .
            IIF($_parserRuleType == SWIFT_ParserRule::TYPE_PREPARSE, " checked") . '>&nbsp;' . $this->Language->Get('ipreparse') .
            '</label>&nbsp;&nbsp;&nbsp;<label for="postparse"><input type="radio" onClick="this.blur(); if (this.checked) {
                    SwitchRuleType(2);}" id="postparse" name="ruletype" style="PADDING: 0px;" value="2"' .
            IIF($_parserRuleType == SWIFT_ParserRule::TYPE_POSTPARSE, " checked") . '>&nbsp;' . $this->Language->Get('ipostparse') . '</label>',
            'icon_doublearrows.gif');

        $_preParseDisplay = $_postParseDisplay = false;
        if ($_parserRuleType == SWIFT_ParserRule::TYPE_PREPARSE) {
            $_preParseDisplay = true;
        } else if ($_parserRuleType == SWIFT_ParserRule::TYPE_POSTPARSE) {
            $_postParseDisplay = true;
        }

        // ======= PRE PARSE ACTIONS =======
        $_ActionsTabObject->StartContainer('rulepreparse', $_preParseDisplay);
        $_ActionsTabObject->Textarea('replycontents', $this->Language->Get('preply'), $this->Language->Get('desc_preply'), $_POST['replycontents'] ?? '',
            '60', '8');
        $_ActionsTabObject->Text('forwardemail', $this->Language->Get('pcforward'), $this->Language->Get('desc_pcforward'), $_POST['forwardemail'] ?? '');
        $_ActionsTabObject->YesNo(SWIFT_ParserRule::PARSERACTION_IGNORE, $this->Language->Get('paignore'), $this->Language->Get('desc_paignore'));
        $_ActionsTabObject->YesNo(SWIFT_ParserRule::PARSERACTION_NOAUTORESPONDER, $this->Language->Get('panoautoresp'),
            $this->Language->Get('desc_panoautoresp'));
        $_ActionsTabObject->YesNo(SWIFT_ParserRule::PARSERACTION_NOALERTRULES, $this->Language->Get('panoalerts'),
            $this->Language->Get('desc_panoalerts'));
        $_ActionsTabObject->YesNo(SWIFT_ParserRule::PARSERACTION_NOTICKET, $this->Language->Get('panoticket'),
            $this->Language->Get('desc_panoticket'));
        $_ActionsTabObject->EndContainer();

        // ======= POST PARSE ACTIONS =======
        $_ActionsTabObject->StartContainer('rulepostparse', $_postParseDisplay);
        $_optionsContainer = array();
        $_optionsContainer[0]['title'] = $this->Language->Get('pnochange');
        $_optionsContainer[0]['value'] = '0';
        $_optionsContainer[0]['selected'] = true;

        $_departmentMapOptions = SWIFT_Department::GetDepartmentMapOptions($_POST['departmentid'] ?? '0', APP_TICKETS);

        $_index = 1;
        foreach ($_departmentMapOptions as $_key => $_val) {
            $_optionsContainer[$_index] = $_val;

            $_index++;
        }

        $_ActionsTabObject->Select('departmentid', $this->Language->Get('pcdepartment'), $this->Language->Get('desc_pcdepartment'),
            $_optionsContainer, 'javascript: UpdateTicketStatusDiv(this, \'ticketstatusid\', true, false);
                    UpdateTicketTypeDiv(this, \'tickettypeid\', true, false);');

        $_optionsContainer = array();
        $_optionsContainer[0]['title'] = $this->Language->Get('pnochange');
        $_optionsContainer[0]['value'] = '0';
        $_optionsContainer[0]['selected'] = true;

        if (_is_array($_staffCache)) {
            $_index = 1;
            foreach ($_staffCache as $_key => $_val) {
                $_optionsContainer[$_index]['title'] = text_to_html_entities($_val['fullname']);
                $_optionsContainer[$_index]['value'] = (int)($_val['staffid']);

                $_index++;
            }
        }

        $_ActionsTabObject->Select('staffid', $this->Language->Get('pcstaff'), $this->Language->Get('desc_pcstaff'),
            $_optionsContainer);

        $_optionsContainer = array();
        $_optionsContainer[0]['title'] = $this->Language->Get('pnochange');
        $_optionsContainer[0]['value'] = '0';
        $_optionsContainer[0]['selected'] = true;

        if (_is_array($_ticketTypeCache)) {
            $_index = 1;
            foreach ($_ticketTypeCache as $_key => $_val) {
                if ($_val['departmentid'] == '0' || $_val['departmentid'] == $_POST['departmentid']) {
                    $_optionsContainer[$_index]['title'] = htmlspecialchars($_val['title']);
                    $_optionsContainer[$_index]['value'] = (int)($_val['tickettypeid']);

                    $_index++;
                }
            }
        }

        $_ActionsTabObject->Select('tickettypeid', $this->Language->Get('pctickettype'), $this->Language->Get('desc_pctickettype'),
            $_optionsContainer, '', 'tickettypeid_container');

        $_optionsContainer = array();
        $_optionsContainer[0]['title'] = $this->Language->Get('pnochange');
        $_optionsContainer[0]['value'] = '0';
        $_optionsContainer[0]['selected'] = true;

        if (_is_array($_statusCache)) {
            $_index = 1;
            foreach ($_statusCache as $_key => $_val) {
                if ($_val['departmentid'] == '0' || $_val['departmentid'] == $_POST['departmentid']) {
                    $_optionsContainer[$_index]['title'] = htmlspecialchars($_val['title']);
                    $_optionsContainer[$_index]['value'] = (int)($_val['ticketstatusid']);

                    $_index++;
                }
            }
        }

        $_ActionsTabObject->Select('ticketstatusid', $this->Language->Get('pcstatus'), $this->Language->Get('desc_pcstatus'), $_optionsContainer,
            '', 'ticketstatusid_container');

        $_optionsContainer = array();
        $_optionsContainer[0]['title'] = $this->Language->Get('pnochange');
        $_optionsContainer[0]['value'] = '0';
        $_optionsContainer[0]['selected'] = true;

        if (_is_array($_priorityCache)) {
            $_index = 1;
            foreach ($_priorityCache as $_key => $_val) {
                $_optionsContainer[$_index]['title'] = $_val['title'];
                $_optionsContainer[$_index]['value'] = $_val['priorityid'];

                $_index++;
            }
        }

        $_ActionsTabObject->Select('ticketpriorityid', $this->Language->Get('pcpriority'), $this->Language->Get('desc_pcpriority'),
            $_optionsContainer);

        $_optionsContainer = array();
        $_optionsContainer[0]['title'] = $this->Language->Get('pnochange');
        $_optionsContainer[0]['value'] = '0';
        $_optionsContainer[0]['selected'] = true;

        $_index = 1;
        if (_is_array($_slaPlanCache)) {
            foreach ($_slaPlanCache as $_key => $_val) {
                $_optionsContainer[$_index]['title'] = $_val['title'];
                $_optionsContainer[$_index]['value'] = $_val['slaplanid'];

                /**
                 * BUG FIX - Ravi Sharma <ravi.sharma@kayako.com>
                 *
                 * SWIFT-4430 Disabled SLA plan can be implemented over a ticket manually from 'Edit' tab.
                 */
                if ($_val['isenabled'] == '0') {
                    $_optionsContainer[$_index]['disabled'] = true;
                }

                $_index++;
            }
        }

        $_ActionsTabObject->Select('slaplanid', $this->Language->Get('pcslaplan'), $this->Language->Get('desc_pcslaplan'), $_optionsContainer);

        $_optionsContainer = array();
        $_optionsContainer[0]['title'] = $this->Language->Get('pnochange');
        $_optionsContainer[0]['value'] = '0';
        $_optionsContainer[0]['selected'] = true;

        $this->Load->Library('Flag:TicketFlag', [], true, false,APP_TICKETS);

        $_index = 1;
        foreach ($this->TicketFlag->GetFlagList() as $_key => $_val) {
            $_optionsContainer[$_index]['title'] = $_val;
            $_optionsContainer[$_index]['value'] = $_key;

            $_index++;
        }

        $_ActionsTabObject->Select('flagtype', $this->Language->Get('pcflag'), $this->Language->Get('desc_pcflag'), $_optionsContainer);

        $_ActionsTabObject->Textarea('notes', $this->Language->Get('paddnotes'), $this->Language->Get('desc_paddnotes'), $_POST['notes'] ?? '', '50', '3');

        // ======= RENDER ADD TAGS =======
        if (isset($_POST['addtags']) && _is_array($_POST['addtags'])) {
            $_ruleAddTagsList = $_POST['addtags'];
        }

        $_ActionsTabObject->TextMultipleAutoComplete('addtags', $this->Language->Get('pcaddtags'),
            $this->Language->Get('desc_pcaddtags'), '/Base/Tags/QuickSearch', $_ruleAddTagsList,
            'fa-tags', false, true);

        // ======= RENDER REMOVE TAGS =======
        if (isset($_POST['removetags']) && _is_array($_POST['removetags'])) {
            $_ruleRemoveTagsList = $_POST['removetags'];
        }

        $_ActionsTabObject->TextMultipleAutoComplete('removetags', $this->Language->Get('pcremovetags'),
            $this->Language->Get('desc_pcremovetags'), '/Base/Tags/QuickSearch', $_ruleRemoveTagsList,
            'fa-tags', false, true);

        $_ActionsTabObject->YesNo('movetotrash', $this->Language->Get('pcmovetotrash'), $this->Language->Get('desc_pcmovetotrash'), false);

        $_ActionsTabObject->YesNo('private', $this->Language->Get('pcprivate'), $this->Language->Get('desc_pcprivate'), false);

        $_ActionsTabObject->EndContainer();

        /*
         * ###############################################
         * END ACTIONS TAB
         * ###############################################
         */

        if (!isset($_POST['rulecriteria'])) {
            $_GeneralTabObject->PrependHTML('<script type="text/javascript">QueueFunction(function(){ newGlobalRuleCriteria(\'' .
                SWIFT_ParserRule::PARSER_SUBJECT . '\', \'' . SWIFT_Rules::OP_CONTAINS . '\', \'\', \'1\', \'1\'); });</script>');
        }

        $this->UserInterface->End();

        return true;
    }

    /**
     * Render the Parser Rules Grid
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RenderGrid()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->Load->Library('UserInterface:UserInterfaceGrid', array('parserrulegrid'), true, false, 'base');

        if ($this->UserInterfaceGrid->GetMode() == SWIFT_UserInterfaceGrid::MODE_SEARCH) {
            $this->UserInterfaceGrid->SetSearchQuery('SELECT * FROM ' . TABLE_PREFIX . 'parserrules WHERE (' .
                $this->UserInterfaceGrid->BuildSQLSearch('title') . ')', 'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX .
                'parserrules WHERE (' . $this->UserInterfaceGrid->BuildSQLSearch('title') . ')');
        }

        $this->UserInterfaceGrid->SetQuery('SELECT * FROM ' . TABLE_PREFIX . 'parserrules', 'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX .
            'parserrules');

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('parserruleid', 'parserruleid', SWIFT_UserInterfaceGridField::TYPE_ID));

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('icon', '&nbsp;', SWIFT_UserInterfaceGridField::TYPE_CUSTOM, 16,
            SWIFT_UserInterfaceGridField::ALIGN_CENTER));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('title', $this->Language->Get('ruletitle'),
            SWIFT_UserInterfaceGridField::TYPE_DB, 0, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('sortorder', $this->Language->Get('sortorder'),
            SWIFT_UserInterfaceGridField::TYPE_DB, 120, SWIFT_UserInterfaceGridField::ALIGN_CENTER, SWIFT_UserInterfaceGridField::SORT_ASC),
            true);
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('ruletype', $this->Language->Get('ruletype'),
            SWIFT_UserInterfaceGridField::TYPE_DB, 180, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('dateline', $this->Language->Get('creationdate'),
            SWIFT_UserInterfaceGridField::TYPE_DB, 200, SWIFT_UserInterfaceGridField::ALIGN_LEFT));

        $this->UserInterfaceGrid->SetRenderCallback(array($this, 'GridRender'));
        $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('delete'), 'fa-trash',
            array('Parser\Admin\Controller_Rule', 'DeleteList'), $this->Language->Get('actionconfirm')));
        $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('enable'), 'fa-check-circle',
            array('Parser\Admin\Controller_Rule', 'EnableList'), $this->Language->Get('actionconfirm')));
        $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('disable'), 'fa-minus-circle',
            array('Parser\Admin\Controller_Rule', 'DisableList'), $this->Language->Get('actionconfirm')));

        $this->UserInterfaceGrid->SetNewLinkViewport(SWIFT::Get('basename') . '/Parser/Rule/Insert');

        $this->UserInterfaceGrid->Render();

        $this->UserInterfaceGrid->Display();

        return true;
    }

    /**
     * The Grid Rendering Function
     *
     * @author Varun Shoor
     *
     * @param array $_fieldContainer The Field Record Value Container
     *
     * @return array
     */
    public static function GridRender($_fieldContainer)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_parserRuleIcon = 'fa-envelope-square';
        if ($_fieldContainer['isenabled'] == '0') {
            $_parserRuleIcon = 'fa-minus-circle';
        }

        $_fieldContainer['icon'] = '<i class="fa ' . $_parserRuleIcon . ' " aria-hidden="true"></i>';

        $_fieldContainer['title'] = '<a href="' . SWIFT::Get('basename') . '/Parser/Rule/Edit/' . (int)($_fieldContainer['parserruleid']) . '" viewport="1">' . htmlspecialchars($_fieldContainer['title']) . '</a>';

        $_fieldContainer['dateline'] = SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_fieldContainer['dateline']);
        $_fieldContainer['ruletype'] = SWIFT_ParserRule::GetRuleTypeLabel($_fieldContainer['ruletype']);

        return $_fieldContainer;
    }

    /**
     * Renders the Rule Action HTML String
     *
     * @author Varun Shoor
     *
     * @param array $_actionArray The Action Array
     *
     * @return mixed Rendered HTML on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RenderRuleAction($_actionArray)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->Cache->Queue('departmentcache', 'staffcache', 'statuscache', 'prioritycache', 'slaplancache', 'tickettypecache');
        $_departmentCache = $this->Cache->Get('departmentcache');
        $_staffCache = $this->Cache->Get('staffcache');
        $_statusCache = $this->Cache->Get('statuscache');
        $_priorityCache = $this->Cache->Get('prioritycache');
        $_slaPlanCache = $this->Cache->Get('slaplancache');
        $_ticketTypeCache = $this->Cache->Get('tickettypecache');

        $this->Load->Library('Flag:TicketFlag', [], true, false, APP_TICKETS);
        $_flagContainer = $this->TicketFlag->GetFlagList();

        if ($_actionArray['name'] == SWIFT_ParserRule::PARSERACTION_REPLY) {
            return '&nbsp;&nbsp;<img src="' . SWIFT::Get('themepath') . 'images/linkdownarrow_blue.gif" align="absmiddle" border="0" /> ' .
                $this->Language->Get('preply') . ': ' . nl2br(htmlspecialchars($_actionArray['typedata'])) . '<br />';
        } else if ($_actionArray['name'] == SWIFT_ParserRule::PARSERACTION_FORWARD) {
            return '&nbsp;&nbsp;<img src="' . SWIFT::Get('themepath') . 'images/linkdownarrow_blue.gif" align="absmiddle" border="0" /> ' .
                $this->Language->Get('pcforward') . ': ' . htmlspecialchars($_actionArray['typechar']) . '<br />';
        } else if ($_actionArray['name'] == SWIFT_ParserRule::PARSERACTION_IGNORE) {
            return '&nbsp;&nbsp;<img src="' . SWIFT::Get('themepath') . 'images/linkdownarrow_blue.gif" align="absmiddle" border="0" /> ' .
                $this->Language->Get('paignore') . ': ' . $this->Language->Get('yes') . '<br />';
        } else if ($_actionArray['name'] == SWIFT_ParserRule::PARSERACTION_NOAUTORESPONDER) {
            return '&nbsp;&nbsp;<img src="' . SWIFT::Get('themepath') . 'images/linkdownarrow_blue.gif" align="absmiddle" border="0" /> ' .
                $this->Language->Get('panoautoresp') . ': ' . $this->Language->Get('yes') . '<br />';
        } else if ($_actionArray['name'] == SWIFT_ParserRule::PARSERACTION_NOALERTRULES) {
            return '&nbsp;&nbsp;<img src="' . SWIFT::Get('themepath') . 'images/linkdownarrow_blue.gif" align="absmiddle" border="0" /> ' .
                $this->Language->Get('panoalerts') . ': ' . $this->Language->Get('yes') . '<br />';
        } else if ($_actionArray['name'] == SWIFT_ParserRule::PARSERACTION_NOTICKET) {
            return '&nbsp;&nbsp;<img src="' . SWIFT::Get('themepath') . 'images/linkdownarrow_blue.gif" align="absmiddle" border="0" /> ' .
                $this->Language->Get('panoticket') . ': ' . $this->Language->Get('yes') . '<br />';
        } else if ($_actionArray['name'] == SWIFT_ParserRule::PARSERACTION_DEPARTMENT && isset($_departmentCache[$_actionArray['typeid']])) {
            return '&nbsp;&nbsp;<img src="' . SWIFT::Get('themepath') . 'images/linkdownarrow_blue.gif" align="absmiddle" border="0" /> ' .
                $this->Language->Get('pcdepartment') . ': ' . text_to_html_entities($_departmentCache[$_actionArray['typeid']]['title']) . '<br />';
        } else if ($_actionArray['name'] == SWIFT_ParserRule::PARSERACTION_STATUS && isset($_statusCache[$_actionArray['typeid']])) {
            return '&nbsp;&nbsp;<img src="' . SWIFT::Get('themepath') . 'images/linkdownarrow_blue.gif" align="absmiddle" border="0" /> ' .
                $this->Language->Get('pcstatus') . ': ' . htmlspecialchars($_statusCache[$_actionArray['typeid']]['title']) . '<br />';
        } else if ($_actionArray['name'] == SWIFT_ParserRule::PARSERACTION_TICKETTYPE && isset($_ticketTypeCache[$_actionArray['typeid']])) {
            return '&nbsp;&nbsp;<img src="' . SWIFT::Get('themepath') . 'images/linkdownarrow_blue.gif" align="absmiddle" border="0" /> ' .
                $this->Language->Get('pctickettype') . ': ' . htmlspecialchars($_ticketTypeCache[$_actionArray['typeid']]['title']) . '<br />';
        } else if ($_actionArray['name'] == SWIFT_ParserRule::PARSERACTION_PRIORITY && isset($_priorityCache[$_actionArray['typeid']])) {
            return '&nbsp;&nbsp;<img src="' . SWIFT::Get('themepath') . 'images/linkdownarrow_blue.gif" align="absmiddle" border="0" /> ' .
                $this->Language->Get('pcpriority') . ': ' . htmlspecialchars($_priorityCache[$_actionArray['typeid']]['title']) . '<br />';
        } else if ($_actionArray['name'] == SWIFT_ParserRule::PARSERACTION_OWNER && isset($_staffCache[$_actionArray['typeid']])) {
            return '&nbsp;&nbsp;<img src="' . SWIFT::Get('themepath') . 'images/linkdownarrow_blue.gif" align="absmiddle" border="0" /> ' .
                $this->Language->Get('pcstaff') . ': ' . text_to_html_entities($_staffCache[$_actionArray['typeid']]['fullname']) . '<br />';
        } else if ($_actionArray['name'] == SWIFT_ParserRule::PARSERACTION_FLAGTICKET && isset($_flagContainer[$_actionArray['typeid']])) {
            return '&nbsp;&nbsp;<img src="' . SWIFT::Get('themepath') . 'images/linkdownarrow_blue.gif" align="absmiddle" border="0" /> ' .
                $this->Language->Get('pcflag') . ': ' . $_flagContainer[$_actionArray['typeid']] . '<br />';
        } else if ($_actionArray['name'] == SWIFT_ParserRule::PARSERACTION_MOVETOTRASH && $_actionArray['typeid'] == '1') {
            return '&nbsp;&nbsp;<img src="' . SWIFT::Get('themepath') . 'images/linkdownarrow_blue.gif" align="absmiddle" border="0" /> ' .
                $this->Language->Get('pcmovetotrash') . '<br />';
        } else if ($_actionArray['name'] == SWIFT_ParserRule::PARSERACTION_SLAPLAN && isset($_slaPlanCache[$_actionArray['typeid']])) {
            return '&nbsp;&nbsp;<img src="' . SWIFT::Get('themepath') . 'images/linkdownarrow_blue.gif" align="absmiddle" border="0" /> ' .
                $this->Language->Get('pcslaplan') . ': ' . htmlspecialchars($_slaPlanCache[$_actionArray['typeid']]['title']) . '<br />';
        } else if ($_actionArray['name'] == SWIFT_ParserRule::PARSERACTION_ADDNOTE) {
            return '&nbsp;&nbsp;<img src="' . SWIFT::Get('themepath') . 'images/linkdownarrow_blue.gif" align="absmiddle" border="0" /> ' .
                $this->Language->Get('paddnotes') . ': ' . nl2br(htmlspecialchars($_actionArray['typedata'])) . '<br />';
        } else if ($_actionArray['name'] == SWIFT_ParserRule::PARSERACTION_ADDTAGS) {
            return '&nbsp;&nbsp;<img src="' . SWIFT::Get('themepath') . 'images/linkdownarrow_blue.gif" align="absmiddle" border="0" /> ' .
                $this->Language->Get('pcaddtags') . ': ' . implode(', ', json_decode($_actionArray['typedata'])) . '<br />';
        } else if ($_actionArray['name'] == SWIFT_ParserRule::PARSERACTION_REMOVETAGS) {
            return '&nbsp;&nbsp;<img src="' . SWIFT::Get('themepath') . 'images/linkdownarrow_blue.gif" align="absmiddle" border="0" /> ' .
                $this->Language->Get('pcremovetags') . ': ' . implode(', ', json_decode($_actionArray['typedata'])) . '<br />';
        }

        return false;

    }
}
