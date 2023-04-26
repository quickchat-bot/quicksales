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

namespace LiveChat\Staff;

use Base\Library\Help\SWIFT_Help;
use Base\Library\Rules\SWIFT_Rules;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;
use LiveChat\Models\Chat\SWIFT_ChatSearch;
use SWIFT;
use SWIFT_View;

/**
 * The Chat Search View
 *
 * @author Varun Shoor
 */
class View_Search extends SWIFT_View
{
    /**
     * Render the Chat Search Form
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function Render()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->UserInterface->Start(get_short_class($this), '/LiveChat/ChatHistory/SearchSubmit', SWIFT_UserInterface::MODE_INSERT, false);

        $_criteriaPointer = SWIFT_ChatSearch::GetCriteriaPointer();
        SWIFT_ChatSearch::ExtendCustomCriteria($_criteriaPointer);
        SWIFT_Rules::CriteriaPointerToJavaScriptArray($_criteriaPointer);

        $this->UserInterface->Toolbar->AddButton($this->Language->Get('search'), 'fa-search');
        $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('livechatsearch'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        /*
         * ###############################################
         * BEGIN GENERAL TAB
         * ###############################################
         */
        $_GeneralTabObject = $this->UserInterface->AddTab($this->Language->Get('tabsearch'), 'icon_spacer.gif', 'search', true);

        $_optionsContainer = array();
        $_optionsContainer[0]['title'] = $this->Language->Get('smatchall');
        $_optionsContainer[0]['value'] = SWIFT_Rules::RULE_MATCHALL;
        $_optionsContainer[0]['checked'] = true;

        $_optionsContainer[1]['title'] = $this->Language->Get('smatchany');
        $_optionsContainer[1]['value'] = SWIFT_Rules::RULE_MATCHANY;

        $_GeneralTabObject->Radio('criteriaoptions', $this->Language->Get('matchtype'), $this->Language->Get('desc_matchtype'), $_optionsContainer);

        $_appendHTML = '<tr id="tabtoolbar"><td align="left" valign="top" colspan="2" class="settabletitlerowmain2"><div class="tabtoolbarsub"><ul><li><a href="javascript:void(0);" onmouseup="javascript:this.blur(); newGlobalRuleCriteria(\'userfullname\', \'' . SWIFT_Rules::OP_CONTAINS . '\', \'\');"><i class="fa fa-list-ul" aria-hidden="true"></i> ' . $this->Language->Get('insertcriteria') . '</a></li></ul></div></td>';

        $_appendHTML .= '<tr class="gridrow2"><td align="left" colspan="2" class="smalltext"><div id="ruleparent"></div></td></tr>';

        $_javascriptAppendHTML = '';
        if (!isset($_POST['rulecriteria'])) {
            $_javascriptAppendHTML = '<script type="text/javascript">QueueFunction(function(){ newGlobalRuleCriteria(\'userfullname\', \'' . SWIFT_Rules::OP_CONTAINS . '\', \'\'); });</script>';
        }

        $_GeneralTabObject->AppendHTML($_appendHTML . $_javascriptAppendHTML);


        /*
         * ###############################################
         * END GENERAL TAB
         * ###############################################
         */

        $this->UserInterface->End();

        return true;
    }
}
