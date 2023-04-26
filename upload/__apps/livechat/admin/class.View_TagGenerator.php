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

namespace LiveChat\Admin;

use Base\Library\Help\SWIFT_Help;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;
use Base\Models\Department\SWIFT_Department;
use LiveChat\Admin\Controller_TagGenerator;
use SWIFT;
use SWIFT_View;

/**
 * The Tag Generator View Management Class
 *
 * @author Varun Shoor
 *
 * @property Controller_TagGenerator $Controller
 */
class View_TagGenerator extends SWIFT_View
{
    /**
     * Render the Tag Generator Form
     *
     * @author Varun Shoor
     * @param string $_tagType The Tag Type
     * @param string $_tagCode The Tag Code
     * @return bool "true" on Success, "false" otherwise
     */
    public function RenderGenerateTag($_tagType, $_tagCode)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->UserInterface->Start(get_short_class($this), '/LiveChat/TagGenerator/Generate/' . $_tagType, SWIFT_UserInterface::MODE_INSERT, false, true);

        $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('taggenerator'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        /*
         * ###############################################
         * BEGIN CHATTAG TAB
         * ###############################################
         */
        $_ChatTagTabObject = $this->UserInterface->AddTab(sprintf($this->Language->Get('tabchatsextended'), $this->Controller->_GetTagLabel($_tagType)), 'icon_taggenerator.gif', 'tagchats', true);

        $_ChatTagTabObject->Title($this->Language->Get('tagcode'), 'icon_doublearrows.gif');
        $_ChatTagTabObject->TextArea('tagcode', '', '', $_tagCode, '30', '5');

        $_ChatTagTabObject->RowHTML('<tr><td>' . $_tagCode . '</td></tr>');

        /*
         * ###############################################
         * END CHAT TAB
         * ###############################################
         */

        $this->UserInterface->End();

        return true;
    }

    /**
     * Render the Tag Generator Form
     *
     * @author Varun Shoor
     * @param string $_tagType The Tag Type
     * @return bool "true" on Success, "false" otherwise
     */
    public function Render($_tagType)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->Cache->Queue('skillscache');
        $this->Cache->Queue('templategroupcache');
        $this->Cache->LoadQueue();

        $_chatSkillCache = $this->Cache->Get('skillscache');
        $_templateGroupCache = $this->Cache->Get('templategroupcache');

        $this->UserInterface->Start(get_short_class($this), '/LiveChat/TagGenerator/Generate/' . $_tagType, SWIFT_UserInterface::MODE_INSERT, false, true);

        $this->UserInterface->Toolbar->AddButton($this->Language->Get('generate'), 'fa-check-circle');
        $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('taggenerator'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        /*
         * ###############################################
         * BEGIN CHATTAG TAB
         * ###############################################
         */
        $this->UserInterface->Hidden('chatprompttype', $_POST['chatprompttype']);

        $_ChatTagTabObject = $this->UserInterface->AddTab(sprintf($this->Language->Get('tabchatsextended'), $this->Controller->_GetTagLabel($_tagType)), 'icon_taggenerator.gif', 'tagchats', true);

        if ($_tagType == Controller_TagGenerator::TAG_TEXTLINK) {
            $_textContents = $this->Language->Get('defaulttextcontents');
            if ($_POST['chatprompttype'] == 'call') {
                $_textContents = $this->Language->Get('defaulttextcontentscall');
            }
            $_ChatTagTabObject->Text('textcontents', $this->Language->Get('textcontents'), $this->Language->Get('desc_textcontents'), $_textContents);
        }

        // Filteration Options are limited to: TEXTLINK, HTMLBUTTON, SITEBADGE (EXCLUDES MONITORING)
        if ($_tagType == Controller_TagGenerator::TAG_TEXTLINK || $_tagType == Controller_TagGenerator::TAG_HTMLBUTTON || $_tagType == Controller_TagGenerator::TAG_SITEBADGE) {
            $_ChatTagTabObject->Title($this->Language->Get('filteroptions'), 'icon_doublearrows.gif');

            $_departmentMapContainer = SWIFT_Department::GetDepartmentMap(APP_LIVECHAT);
            $_optionsContainer = array();
            if (_is_array($_departmentMapContainer)) {
                foreach ($_departmentMapContainer as $_key => $_val) {
                    $_optionsContainer[] = array('title' => $_val['title'], 'value' => (int)($_val['departmentid']));

                    if (_is_array($_val['subdepartments'])) {
                        foreach ($_val['subdepartments'] as $_subKey => $_subVal) {
                            $_optionsContainer[] = array('title' => ' |- ' . $_subVal['title'], 'value' => (int)($_subVal['departmentid']));
                        }
                    }
                }
            }

            if (count($_optionsContainer)) {
                $_ChatTagTabObject->SelectMultiple('filterbydepartment', $this->Language->Get('filterbydepartment'), $this->Language->Get('desc_filterbydepartment'), $_optionsContainer);
            }
        }

        // Filteration Options are limited to: HTMLBUTTON, SITEBADGE (EXCLUDES MONITORING & TEXTLINK)
        if ($_tagType == Controller_TagGenerator::TAG_HTMLBUTTON || $_tagType == Controller_TagGenerator::TAG_SITEBADGE) {
            $_optionsContainer = array();
            if (_is_array($_chatSkillCache)) {
                foreach ($_chatSkillCache as $_key => $_val) {
                    $_optionsContainer[] = array('title' => $_val['title'], 'value' => (int)($_val['chatskillid']));
                }
            }

            if (count($_optionsContainer)) {
                $_ChatTagTabObject->SelectMultiple('routetochatskill', $this->Language->Get('routetochatskill'), $this->Language->Get('desc_routetochatskill'), $_optionsContainer);
            }
        }

        $_ChatTagTabObject->Title($this->Language->Get('generaloptions'), 'icon_doublearrows.gif');

        if (substr(strtolower(SWIFT::Get('swiftpath')), 0, strlen('https')) == 'https') {
            $_useSecureLinks = true;
        } else {
            $_useSecureLinks = false;
        }
        $_ChatTagTabObject->YesNo('usesecurelinks', $this->Language->Get('usesecurelinks'), $this->Language->Get('desc_usesecurelinks'), $_useSecureLinks);

        // Template Group, Skip User Details Restricted to: Text Link, HTML Button, Site Badge (Excludes: Monitoring)
        if ($_tagType == Controller_TagGenerator::TAG_TEXTLINK || $_tagType == Controller_TagGenerator::TAG_HTMLBUTTON || $_tagType == Controller_TagGenerator::TAG_SITEBADGE) {
            $_optionsContainer = array();
//            $_optionsContainer[] = array('title' => $this->Language->Get('usedefault'), 'value' => '0');
            if (_is_array($_templateGroupCache)) {
                $_index = 0;

                foreach ($_templateGroupCache as $_key => $_val) {
                    $_selected = false;
                    if ($_val['isdefault'] == '1') {
                        $_selected = true;
                    }
                    $_optionsContainer[] = array('title' => $_val['title'], 'value' => (int)($_val['tgroupid']), 'selected' => $_selected);

                    $_index++;
                }
            }

            $_ChatTagTabObject->Select('templategroupid', $this->Language->Get('tagtemplategroup'), $this->Language->Get('desc_tagtemplategroup'), $_optionsContainer);

            if ($_POST['chatprompttype'] == 'chat') {
                $_ChatTagTabObject->YesNo('skipuserdetails', $this->Language->Get('skipuserdetails'), $this->Language->Get('desc_skipuserdetails'), false);
            } else {
                $_ChatTagTabObject->Hidden('skipuserdetails', '0');
            }
        }

        if ($_tagType == Controller_TagGenerator::TAG_HTMLBUTTON) {
            $_ChatTagTabObject->YesNo('nojavascript', $this->Language->Get('nojavascript'), $this->Language->Get('desc_nojavascript'), false);
        }

        // ALERTS & VARIABLES Options are limited to: MONITORING, HTMLBUTTON, SITEBADGE (EXCLUDES TEXTLINK)
        if ($_tagType == Controller_TagGenerator::TAG_MONITORING || $_tagType == Controller_TagGenerator::TAG_HTMLBUTTON || $_tagType == Controller_TagGenerator::TAG_SITEBADGE) {
            $_SWIFT_UserInterfaceToolbarObject = new SWIFT_UserInterfaceToolbar($this->UserInterface);
            $_SWIFT_UserInterfaceToolbarObject->AddButton($this->Language->Get('insertvariable'), 'fa-plus-circle', "newTagGeneratorVariable();", SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, '', '', false);
            $_SWIFT_UserInterfaceToolbarObject->AddButton($this->Language->Get('insertalert'), 'fa-plus-circle', "newTagGeneratorAlert();", SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, '', '', false);

            $_ChatTagTabObject->AppendHTML($_SWIFT_UserInterfaceToolbarObject->Render(true) . '<tr class="' . $_ChatTagTabObject->GetClass() . '"><td align="left" colspan="2" class="smalltext"><div id="taggeneratorcontainer"></div></td></tr>');
        }

        /*
         * ###############################################
         * END CHAT TAB
         * ###############################################
         */

        // We restrict customizations to HTML Button and Site Badge
        if ($_tagType == Controller_TagGenerator::TAG_HTMLBUTTON || $_tagType == Controller_TagGenerator::TAG_SITEBADGE) {
            /*
             * ###############################################
             * BEGIN CUSTOMIZE TAB
             * ###############################################
             */

            $_CustomizeTabObject = $this->UserInterface->AddTab($this->Language->Get('tabcustomize'), 'icon_form.gif', 'customize', false);

            if ($_tagType == Controller_TagGenerator::TAG_HTMLBUTTON) {
                $_CustomizeTabObject->URLAndUpload('customonline', $this->Language->Get('customonlineimage'), $this->Language->Get('desc_customonlineimage'));
                $_CustomizeTabObject->URLAndUpload('customoffline', $this->Language->Get('customofflineimage'), $this->Language->Get('desc_customofflineimage'));
                $_CustomizeTabObject->URLAndUpload('customaway', $this->Language->Get('customawayimage'), $this->Language->Get('desc_customawayimage'));

                if ($_POST['chatprompttype'] == 'chat') {
                    $_CustomizeTabObject->URLAndUpload('custombackshortly', $this->Language->Get('custombackshortlyimage'), $this->Language->Get('desc_custombackshortlyimage'));
                }
            } else if ($_tagType == Controller_TagGenerator::TAG_SITEBADGE) {
                $_radioContainer = array();
                $_radioContainer[] = array('title' => $this->Language->Get('badgewhite'), 'value' => 'white', 'checked' => true);
                $_radioContainer[] = array('title' => $this->Language->Get('badgeblack'), 'value' => 'black', 'checked' => false);
                $_CustomizeTabObject->Radio('sitebadgecolor', $this->Language->Get('sitebadgecolor'), $this->Language->Get('desc_sitebadgecolor'), $_radioContainer, false);

                /*
                 * BUG FIX - Ravi Sharma
                 *
                 * SWIFT-3323 Badge Language option under Tag Generator should be removed.
                 *
                 * Comments: None
                 */
                $_CustomizeTabObject->Hidden('badgelanguage', 'en');

                $_optionsContainer = array();
                if ($_POST['chatprompttype'] == 'chat') {
                    $_optionsContainer[] = array('title' => $this->Language->Get('badgelivechat'), 'value' => 'livechat', 'selected' => false);
                    $_optionsContainer[] = array('title' => $this->Language->Get('badgelivehelp'), 'value' => 'livehelp', 'selected' => true);
                } else {
                    $_optionsContainer[] = array('title' => $this->Language->Get('badgecallus'), 'value' => 'callus', 'selected' => false);
                    $_optionsContainer[] = array('title' => $this->Language->Get('badgecallme'), 'value' => 'callme', 'selected' => false);
                }
                $_CustomizeTabObject->Select('badgetext', $this->Language->Get('sitebadgetext'), $this->Language->Get('desc_sitebadgetext'), $_optionsContainer);

                $_CustomizeTabObject->Color('onlinecolor', $this->Language->Get('sitebadgeonlinecolor'), $this->Language->Get('desc_sitebadgeonlinecolor'), '#198c19');
                $_CustomizeTabObject->Color('offlinecolor', $this->Language->Get('sitebadgeofflinecolor'), $this->Language->Get('desc_sitebadgeofflinecolor'), '#a2a4ac');
                $_CustomizeTabObject->Color('awaycolor', $this->Language->Get('sitebadgeawaycolor'), $this->Language->Get('desc_sitebadgeawaycolor'), '#737c4a');
                if ($_POST['chatprompttype'] == 'chat') {
                    $_CustomizeTabObject->Color('backshortlycolor', $this->Language->Get('sitebadgebackshortlycolor'), $this->Language->Get('desc_sitebadgebackshortlycolor'), '#788a23');
                }
            }

            /*
             * ###############################################
             * END CUSTOMIZE TAB
             * ###############################################
             */

        }

        $this->UserInterface->End();

        return true;
    }
}
