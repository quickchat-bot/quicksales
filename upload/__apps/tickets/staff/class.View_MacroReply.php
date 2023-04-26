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

namespace Tickets\Staff;

use Base\Library\HTML\SWIFT_HTML;
use Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel;
use SWIFT;
use SWIFT_DataID;
use Base\Models\Department\SWIFT_Department;
use SWIFT_Exception;
use Base\Library\Help\SWIFT_Help;
use Tickets\Library\Macro\SWIFT_MacroManager;
use Tickets\Models\Macro\SWIFT_MacroReply;
use Base\Models\Staff\SWIFT_StaffGroupLink;
use Tickets\Models\Ticket\SWIFT_Ticket;
use Tickets\Models\Ticket\SWIFT_TicketPost;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;
use SWIFT_View;

/**
 * The Macro Reply View
 *
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @author Varun Shoor
 */
class View_MacroReply extends SWIFT_View
{
    /**
     * Render the Macro Reply Form
     *
     * @author Varun Shoor
     * @param int $_mode The Render Mode
     * @param SWIFT_MacroReply $_SWIFT_MacroReplyObject The SWIFT_MacroReply Object Pointer (Only for EDIT Mode)
     * @param bool $_selectedMacroCategoryIDArg
     * @param SWIFT_TicketPost|null $_SWIFT_TicketPostObject
     * @param int $_ticketID
     * @param string $_listType
     * @param string $_departmentID
     * @param string $_ticketStatusID
     * @param string $_ticketTypeID
     * @param bool $_addKBArticle
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public function Render($_mode, SWIFT_MacroReply $_SWIFT_MacroReplyObject = null, $_selectedMacroCategoryIDArg = false, SWIFT_TicketPost $_SWIFT_TicketPostObject = null, $_ticketID = 0, $_listType = 'inbox',
            $_departmentID = '-1', $_ticketStatusID = '-1', $_ticketTypeID = '-1', $_addKBArticle = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            return false;
        }

        $_staffCache = (array) $this->Cache->Get('staffcache');
        $_departmentCache = (array) $this->Cache->Get('departmentcache');
        $_ticketStatusCache = (array) $this->Cache->Get('statuscache');
        $_ticketPriorityCache = (array) $this->Cache->Get('prioritycache');
        $_ticketTypeCache = (array) $this->Cache->Get('tickettypecache');
        $_macroDepartmentID = $_macroTicketStatusID = $_macroTicketPriorityID = $_macroTicketTypeID = -1;
        $_macroAddTagsList = array();
        $_macroOwnerStaffID = -1;
        $_staffGroupTicketStatusIDList = SWIFT_StaffGroupLink::RetrieveListFromCacheOnStaffGroup(SWIFT_StaffGroupLink::TYPE_TICKETSTATUS, $_SWIFT->Staff->GetProperty('staffgroupid'));

        $_isDialog = true;
        $_ticketPostID = '0';
        if (($_SWIFT_TicketPostObject instanceof SWIFT_TicketPost && $_SWIFT_TicketPostObject->GetIsClassLoaded()) || isset($_POST['tredir_ticketid']))
        {
            $_isDialog = false;
        }

        // Calculate the URL
        if ($_mode == SWIFT_UserInterface::MODE_EDIT)
        {
            $this->UserInterface->Start(get_short_class($this),'/Tickets/MacroReply/EditSubmit/' . $_SWIFT_MacroReplyObject->GetMacroReplyID(), SWIFT_UserInterface::MODE_EDIT, true);
        } else {
            $this->UserInterface->Start(get_short_class($this),'/Tickets/MacroReply/InsertSubmit', SWIFT_UserInterface::MODE_INSERT, $_isDialog);
        }

        $_macroReplyTitle = '';
        $_selectedMacroCategoryID = 0;

        if (!empty($_selectedMacroCategoryIDArg))
        {
            $_selectedMacroCategoryID = (int) ($_selectedMacroCategoryIDArg);
        }

        $_macroReplyContents = '';
        if (!empty($_ticketID) && $_isDialog == false)
        {
            $_SWIFT_TicketObject = new SWIFT_Ticket(new SWIFT_DataID($_ticketID));
            if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded() || !$_SWIFT_TicketObject->CanAccess($_SWIFT->Staff) ||
                    $_SWIFT_TicketObject->GetTicketID() != $_SWIFT_TicketPostObject->GetProperty('ticketid'))
            {
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            }

            $_macroReplyContents = $_SWIFT_TicketPostObject->GetProperty('contents');

            $_ticketPostID = $_SWIFT_TicketPostObject->GetProperty('ticketpostid');
        }

        if ($_mode == SWIFT_UserInterface::MODE_EDIT)
        {
            if (isset($_POST['_isDialog']) && $_POST['_isDialog'] == 1)
            {
                $this->UserInterface->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle');
            }

            $this->UserInterface->Toolbar->AddButton($this->Language->Get('delete'), 'fa-trash', '/Tickets/MacroCategory/DeleteReply/' . $_SWIFT_MacroReplyObject->GetMacroReplyID(), SWIFT_UserInterfaceToolbar::LINK_CONFIRM, '', '', false);
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('ticketmacro'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

            $_macroReplyTitle    = $_SWIFT_MacroReplyObject->GetProperty('subject');
            $_macroReplyContents = $_SWIFT_MacroReplyObject->GetProperty('contents');

            $_selectedMacroCategoryID = (int) ($_SWIFT_MacroReplyObject->GetProperty('macrocategoryid'));
            $_macroDepartmentID = (int) ($_SWIFT_MacroReplyObject->GetProperty('departmentid'));
            $_macroOwnerStaffID = (int) ($_SWIFT_MacroReplyObject->GetProperty('ownerstaffid'));
            $_macroTicketStatusID = (int) ($_SWIFT_MacroReplyObject->GetProperty('ticketstatusid'));
            $_macroTicketTypeID = (int) ($_SWIFT_MacroReplyObject->GetProperty('tickettypeid'));
            $_macroTicketPriorityID = (int) ($_SWIFT_MacroReplyObject->GetProperty('priorityid'));

            $_macroAddTagsString = $_SWIFT_MacroReplyObject->GetProperty('tagcontents');
            if (!empty($_macroAddTagsString))
            {
                $_macroAddTagsList = mb_unserialize($_macroAddTagsString);
            }

        } else {
            if ((isset($_POST['_isDialog']) && $_POST['_isDialog'] == 1) || $_isDialog == false)
            {
                $this->UserInterface->Toolbar->AddButton($this->Language->Get('insert'), 'fa-plus-circle');
            }

            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('ticketmacro'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);
        }


        /*
         * ###############################################
         * BEGIN GENERAL TAB
         * ###############################################
         */
        $_GeneralTabObject = $this->UserInterface->AddTab($this->Language->Get('tabgeneral'), 'icon_form.gif', 'general', true);

        $_GeneralTabObject->Text('title', $this->Language->Get('macroreplytitle'), $this->Language->Get('desc_macroreplytitle'), $_macroReplyTitle);

        $_GeneralTabObject->Select('macrocategoryid', $this->Language->Get('parentcategoryreply'), $this->Language->Get('desc_parentcategoryreply'), SWIFT_MacroManager::GetMacroCategoryOptions($_selectedMacroCategoryID));

        $_GeneralTabObject->Title($this->Language->Get('macroreplycontents'), 'icon_doublearrows.gif');

        /**
         * BUG FIX - Ravi Sharma <ravi.sharma@opencart.com.vn>
         *
         * SWIFT-4986 If WYSIWYG editor is disabled, <br> tags are added in Macros and KB articles.
         */
        if ($_SWIFT->Settings->Get('t_tinymceeditor') != '0') {
            if (!SWIFT_HTML::DetectHTMLContent($_macroReplyContents)) {
                $_macroReplyContents = nl2br($_macroReplyContents);
            }
            $_GeneralTabObject->HTMLEditor('replycontents', $_macroReplyContents);
        } else {
            $_GeneralTabObject->TextArea('replycontents', '', '', $_macroReplyContents, 30, 10);
        }

        /*
         * ###############################################
         * END GENERAL TAB
         * ###############################################
         */



        /*
         * ###############################################
         * BEGIN PROPERTIES TAB
         * ###############################################
         */
        $_PropertiesTabObject = $this->UserInterface->AddTab($this->Language->Get('tabproperties'), 'icon_settings2.gif', 'properties', false);


        $_PropertiesTabObject->Title($this->Language->Get('ticketfields'), 'doublearrows.gif');

        // Departments
        $_optionsContainer = array();
        $_index = 0;

        $_optionsContainer = SWIFT_Department::GetDepartmentMapOptions($_macroDepartmentID, APP_TICKETS);
        array_unshift($_optionsContainer, array('title' => $this->Language->Get('nochange'), 'value' => '-1', 'selected' => IIF($_macroDepartmentID === 0, true, false)));

        $_PropertiesTabObject->Select('departmentid', $this->Language->Get('macrodepartment'), $this->Language->Get('desc_macrodepartment'),
                $_optionsContainer, 'javascript: UpdateTicketStatusDiv(this, \'ticketstatusid\', true, false); UpdateTicketTypeDiv(this, \'tickettypeid\', true, false); UpdateTicketOwnerDiv(this, \'ownerstaffid\', true, false);');

        // Owner
        $_optionsContainer = array();
        $_optionsContainer[0]['title'] = $this->Language->Get('nochange');
        $_optionsContainer[0]['value'] = '-1';
        if ($_macroOwnerStaffID == -1)
        {
            $_optionsContainer[0]['selected'] = true;
        }

        $_optionsContainer[1]['title'] = $this->Language->Get('sactivestaff');
        $_optionsContainer[1]['value'] = '-2';
        if ($_macroOwnerStaffID == -2)
        {
            $_optionsContainer[1]['selected'] = true;
        }

        $_optionsContainer[2]['title'] = $this->Language->Get('unassigned');
        $_optionsContainer[2]['value'] = '0';
        if ($_macroOwnerStaffID == 0)
        {
            $_optionsContainer[2]['selected'] = true;
        }

        $_index = 3;

        if (_is_array($_staffCache))
        {
            foreach ($_staffCache as $_staffID => $_staffContainer)
            {
                $_optionsContainer[$_index]['title'] = $_staffContainer['fullname'];
                $_optionsContainer[$_index]['value'] = $_staffContainer['staffid'];

                if ($_macroOwnerStaffID == false || $_macroOwnerStaffID == $_staffContainer['staffid'])
                {
                    $_optionsContainer[$_index]['selected'] = true;
                }

                $_index++;
            }
        }

        $_PropertiesTabObject->Select('ownerstaffid', $this->Language->Get('macroownerstaff'), $this->Language->Get('desc_macroownerstaff'),
                $_optionsContainer, '', 'ownerstaffid_container');

        // Type
        $_optionsContainer = array();
        $_index = 1;
        $_optionsContainer[0]['title'] = $this->Language->Get('nochange');
        $_optionsContainer[0]['value'] = '-1';
        if ($_macroTicketTypeID == -1)
        {
            $_optionsContainer[0]['selected'] = true;
        }

        if (_is_array($_ticketTypeCache))
        {
            foreach ($_ticketTypeCache as $_tID => $_ticketTypeContainer)
            {
                if ($_ticketTypeContainer['departmentid'] == '0' || $_ticketTypeContainer['departmentid'] == $_macroDepartmentID)
                {
                    $_optionsContainer[$_index]['title'] = $_ticketTypeContainer['title'];
                    $_optionsContainer[$_index]['value'] = $_ticketTypeContainer['tickettypeid'];

                    if ($_macroTicketTypeID == false || $_macroTicketTypeID == $_ticketTypeContainer['tickettypeid'])
                    {
                        $_optionsContainer[$_index]['selected'] = true;
                    }

                    $_index++;
                }
            }
        }

        $_PropertiesTabObject->Select('tickettypeid', $this->Language->Get('macrotickettype'), $this->Language->Get('desc_macrotickettype'),
                $_optionsContainer, '', 'tickettypeid_container');

        // Status
        $_optionsContainer = array();
        $_index = 1;
        $_optionsContainer[0]['title'] = $this->Language->Get('nochange');
        $_optionsContainer[0]['value'] = '-1';
        if ($_macroTicketStatusID == -1)
        {
            $_optionsContainer[0]['selected'] = true;
        }

        if (_is_array($_ticketStatusCache))
        {
            foreach ($_ticketStatusCache as $_tID => $_ticketStatusContainer)
            {
                if ($_ticketStatusContainer['staffvisibilitycustom'] == '1' && !in_array($_tID, $_staffGroupTicketStatusIDList))
                {
                    continue;
                }

                if ($_ticketStatusContainer['departmentid'] == '0' || $_ticketStatusContainer['departmentid'] == $_macroDepartmentID)
                {
                    $_optionsContainer[$_index]['title'] = $_ticketStatusContainer['title'];
                    $_optionsContainer[$_index]['value'] = $_ticketStatusContainer['ticketstatusid'];

                    if ($_macroTicketStatusID == false || $_macroTicketStatusID == $_ticketStatusContainer['ticketstatusid'])
                    {
                        $_optionsContainer[$_index]['selected'] = true;
                    }

                    $_index++;
                }
            }
        }
        $_PropertiesTabObject->Select('ticketstatusid', $this->Language->Get('macroticketstatus'), $this->Language->Get('desc_macroticketstatus'),
                $_optionsContainer, '', 'ticketstatusid_container');

        // Priority
        $_optionsContainer = array();
        $_index = 1;
        $_optionsContainer[0]['title'] = $this->Language->Get('nochange');
        $_optionsContainer[0]['value'] = '-1';
        if ($_macroTicketPriorityID == -1)
        {
            $_optionsContainer[0]['selected'] = true;
        }

        if (_is_array($_ticketPriorityCache))
        {
            foreach ($_ticketPriorityCache as $_ticketPriorityID => $_ticketPriorityContainer)
            {
                $_optionsContainer[$_index]['title'] = $_ticketPriorityContainer['title'];
                $_optionsContainer[$_index]['value'] = $_ticketPriorityContainer['priorityid'];

                if ($_macroTicketPriorityID == false || $_macroTicketPriorityID == $_ticketPriorityContainer['priorityid'])
                {
                    $_optionsContainer[$_index]['selected'] = true;
                }

                $_index++;
            }
        }

        $_PropertiesTabObject->Select('ticketpriorityid', $this->Language->Get('macroticketpriority'), $this->Language->Get('desc_macroticketpriority'),
                $_optionsContainer);

        $_PropertiesTabObject->TextMultipleAutoComplete('addtags', $this->Language->Get('macroaddtags'),
                $this->Language->Get('desc_macroaddtags'), '/Base/Tags/QuickSearch', $_macroAddTagsList,
                'fa-tags', false, true);

        /*
         * ###############################################
         * END PROPERTIES TAB
         * ###############################################
         */

        $this->UserInterface->Hidden('tredir_ticketid', $_ticketID);
        $this->UserInterface->Hidden('tredir_listtype', $_listType);
        $this->UserInterface->Hidden('tredir_departmentid', $_departmentID);
        $this->UserInterface->Hidden('tredir_ticketstatusid', $_ticketStatusID);
        $this->UserInterface->Hidden('tredir_tickettypeid', $_ticketTypeID);
        $this->UserInterface->Hidden('tredir_ticketpostid', $_ticketPostID);
        $this->UserInterface->Hidden('tredir_addkb', (int) ($_addKBArticle));

        $this->UserInterface->End();

        return true;
    }
}
