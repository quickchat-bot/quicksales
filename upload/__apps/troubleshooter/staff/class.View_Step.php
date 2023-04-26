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

namespace Troubleshooter\Staff;

use Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel;
use SWIFT;
use SWIFT_App;
use Base\Models\Attachment\SWIFT_Attachment;
use Base\Models\Comment\SWIFT_Comment;
use SWIFT_Date;
use Base\Models\Department\SWIFT_Department;
use SWIFT_Exception;
use Base\Models\SearchStore\SWIFT_SearchStore;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceGrid;
use Base\Library\Help\SWIFT_Help;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridField;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridMassAction;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;
use Troubleshooter\Library\Step\SWIFT_TroubleshooterStepManager;
use Troubleshooter\Models\Category\SWIFT_TroubleshooterCategory;
use Troubleshooter\Models\Link\SWIFT_TroubleshooterLink;
use Troubleshooter\Models\Step\SWIFT_TroubleshooterStep;

/**
 * The Troubleshooter Step View
 *
 * @author Varun Shoor
 *
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @property SWIFT_UserInterfaceGrid $UserInterfaceGrid
 * @property Controller_Step $Controller
 */
class View_Step extends \SWIFT_View
{

    /**
     * Render the Troubleshooter Step Form
     *
     * @author Varun Shoor
     * @param int $_mode The Render Mode
     * @param SWIFT_TroubleshooterStep $_SWIFT_TroubleshooterStepObject The SWIFT_TroubleshooterStep Object Pointer
     *     (Only for EDIT Mode)
     * @param bool|int $_troubleshooterCategoryID (OPTIONAL) The Troubleshooter Category ID
     * @param bool|int $_incomingTroubleshooterStepID (OPTIONAL) The preselected troubleshooter step id
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public function Render($_mode, SWIFT_TroubleshooterStep $_SWIFT_TroubleshooterStepObject = null, $_troubleshooterCategoryID = false, $_incomingTroubleshooterStepID = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            return false;
        }

        $_departmentCache = (array) $this->Cache->Get('departmentcache');
        $_ticketTypeCache = (array) $this->Cache->Get('tickettypecache');
        $_priorityCache = (array) $this->Cache->Get('prioritycache');

        $_stepSubject = '';
        $_stepContents = '';
        $_allowComments = true;
        $_hasAttachments = false;
        $_stepRedirectDepartmentID = 0;
        $_activeTroubleshooterStepID = 0;
        $_stepTicketSubject = '';
        $_stepRedirectTickets = false;
        $_stepTicketTypeID = 0;
        $_stepTicketPriorityID = 0;
        $_displayOrder = SWIFT_TroubleshooterStep::GetLastDisplayOrder();

        $_parentTroubleshooterStepIDList = array(0);
        if (!empty($_incomingTroubleshooterStepID))
        {
            $_parentTroubleshooterStepIDList = array($_incomingTroubleshooterStepID);
        }

        // Calculate the URL
        if ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT_TroubleshooterStepObject !== null)
        {
            $this->UserInterface->Start('trstepform', '/Troubleshooter/Step/EditSubmit/' . $_SWIFT_TroubleshooterStepObject->GetTroubleshooterStepID(),
                    SWIFT_UserInterface::MODE_EDIT, false, true);
        } else {
            $this->UserInterface->Start('trstepform', '/Troubleshooter/Step/InsertSubmit/0', SWIFT_UserInterface::MODE_INSERT, false, true);
        }

        $_attachmentContainer = array();
        if ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT_TroubleshooterStepObject !== null && $_SWIFT_TroubleshooterStepObject->GetProperty('hasattachments') == '1')
        {
            $_attachmentContainer = SWIFT_Attachment::Retrieve(SWIFT_Attachment::LINKTYPE_TROUBLESHOOTERSTEP, $_SWIFT_TroubleshooterStepObject->GetTroubleshooterStepID());
        }

        $_GeneralTabObject = $this->UserInterface->AddTab($this->Language->Get('tabgeneral'), 'icon_troubleshooter.gif', 'generaltrinsert', true);
        $_AttachmentsTabObject = $this->UserInterface->AddTab($this->Language->Get('tabattachments'), 'icon_file.gif', 'trattachments');
        $_AttachmentsTabObject->SetTabCounter(count($_attachmentContainer));
        $_AttachmentsTabObject->LoadToolbar();
        $_OptionsTabObject = $this->UserInterface->AddTab($this->Language->Get('taboptions'), 'icon_settings2.gif', 'troptions');

        if ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT_TroubleshooterStepObject !== null)
        {
            if ($_SWIFT_TroubleshooterStepObject->GetProperty('stepstatus') == SWIFT_TroubleshooterStep::STATUS_DRAFT)
            {
                $this->UserInterface->Toolbar->AddButton($this->Language->Get('publish'), 'fa-check-circle', '/Troubleshooter/Step/EditSubmit/' . $_SWIFT_TroubleshooterStepObject->GetTroubleshooterStepID() . '/1', SWIFT_UserInterfaceToolbar::LINK_FORM);
                $_AttachmentsTabObject->Toolbar->AddButton($this->Language->Get('publish'), 'fa-check-circle', '/Troubleshooter/Step/EditSubmit/' . $_SWIFT_TroubleshooterStepObject->GetTroubleshooterStepID() . '/1', SWIFT_UserInterfaceToolbar::LINK_FORM);

                $this->UserInterface->Toolbar->AddButton($this->Language->Get('update'), 'fa-repeat');
                $_AttachmentsTabObject->Toolbar->AddButton($this->Language->Get('update'), 'fa-repeat');
            } else {
                $this->UserInterface->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle');
                $_AttachmentsTabObject->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle');
            }

            $this->UserInterface->Toolbar->AddButton('');

            // Allow a user to unpublish (mark step as draft)
            if ($_SWIFT_TroubleshooterStepObject->GetProperty('stepstatus') == SWIFT_TroubleshooterStep::STATUS_PUBLISHED) {
                $this->UserInterface->Toolbar->AddButton($this->Language->Get('markasdraft'), 'fa-repeat', '/Troubleshooter/Step/EditSubmit/' . $_SWIFT_TroubleshooterStepObject->GetTroubleshooterStepID() . '/-1', SWIFT_UserInterfaceToolbar::LINK_FORM);
                $_AttachmentsTabObject->Toolbar->AddButton($this->Language->Get('markasdraft'), 'fa-repeat', '/Troubleshooter/Step/EditSubmit/' . $_SWIFT_TroubleshooterStepObject->GetTroubleshooterStepID() . '/-1', SWIFT_UserInterfaceToolbar::LINK_FORM);
            }

            $this->UserInterface->Toolbar->AddButton($this->Language->Get('delete'), 'fa-trash', '/Troubleshooter/Step/Delete/' .
                    $_SWIFT_TroubleshooterStepObject->GetTroubleshooterStepID(), SWIFT_UserInterfaceToolbar::LINK_CONFIRM, '', '', false);
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('troubleshooterstep'),
                    SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

            $_activeTroubleshooterStepID = $_SWIFT_TroubleshooterStepObject->GetTroubleshooterStepID();
            $_troubleshooterCategoryID = $_SWIFT_TroubleshooterStepObject->GetProperty('troubleshootercategoryid');

            $_stepSubject = $_SWIFT_TroubleshooterStepObject->GetProperty('subject');
            $_stepContents = $_SWIFT_TroubleshooterStepObject->GetProperty('contents');

            $_displayOrder = (int) ($_SWIFT_TroubleshooterStepObject->GetProperty('displayorder'));
            $_allowComments = (int) ($_SWIFT_TroubleshooterStepObject->GetProperty('allowcomments'));
            $_hasAttachments = (int) ($_SWIFT_TroubleshooterStepObject->GetProperty('hasattachments'));

            $_parentTroubleshooterStepIDList = SWIFT_TroubleshooterLink::RetrieveOnChild(array($_SWIFT_TroubleshooterStepObject->GetTroubleshooterStepID()));

            $_stepRedirectDepartmentID = (int) ($_SWIFT_TroubleshooterStepObject->GetProperty('redirectdepartmentid'));
            $_stepRedirectTickets = (int) ($_SWIFT_TroubleshooterStepObject->GetProperty('redirecttickets'));
            $_stepTicketSubject = $_SWIFT_TroubleshooterStepObject->GetProperty('ticketsubject');
            $_stepTicketTypeID = (int) ($_SWIFT_TroubleshooterStepObject->GetProperty('tickettypeid'));
            $_stepTicketPriorityID = (int) ($_SWIFT_TroubleshooterStepObject->GetProperty('priorityid'));

        } else {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('publish'), 'fa-check-circle');
            $_AttachmentsTabObject->Toolbar->AddButton($this->Language->Get('publish'), 'fa-check-circle');

            $this->UserInterface->Toolbar->AddButton('');
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('saveasdraft'), 'fa-repeat', '/Troubleshooter/Step/InsertSubmit/1', SWIFT_UserInterfaceToolbar::LINK_FORM);
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('troubleshooterstep'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);
        }

        $_AttachmentsTabObject->Toolbar->AddButton('');
        $_AttachmentsTabObject->Toolbar->AddButton($this->Language->Get('addfile'), 'fa-plus-circle',
                "AddTRFile();", SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, '', '', false);
        $_AttachmentsTabObject->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('troubleshooterstep'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        /*
         * ###############################################
         * BEGIN GENERAL TAB
         * ###############################################
         */

        $_GeneralTabObject->SetColumnWidth('100');

        $_GeneralTabObject->Text('subject', $this->Language->Get('steptitle'), $this->Language->Get('desc_steptitle'), $_stepSubject, 'text', 90);

        $_checkBoxContainer = SWIFT_TroubleshooterStepManager::GetCategoryOptions($_troubleshooterCategoryID, $_parentTroubleshooterStepIDList, $_activeTroubleshooterStepID, true);

        $_GeneralTabObject->CheckBoxContainerList('parentstepidlist', $this->Language->Get('parentsteps'), $this->Language->Get('desc_parentsteps'), $_checkBoxContainer, 615);

        $_GeneralTabObject->HTMLEditor('stepcontents', $_stepContents);

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

        $_OptionsTabObject->Title($this->Language->Get('generalsettings'), 'icon_doublearrows.gif');

        $_OptionsTabObject->Number('displayorder', $this->Language->Get('displayorder'), $this->Language->Get('desc_displayorder'), (string) ($_displayOrder));

        $_OptionsTabObject->YesNo('allowcomments', $this->Language->Get('allowcomments'), $this->Language->Get('desc_allowcommentsstep'), (bool)$_allowComments);

        if (SWIFT_App::IsInstalled(APP_TICKETS)) {
            $_OptionsTabObject->Title($this->Language->Get('ticketredirectionsettings'), 'icon_doublearrows.gif');

            $_OptionsTabObject->YesNo('redirecttickets', $this->Language->Get('redirecttickets'), $this->Language->Get('desc_redirecttickets'), (bool)$_stepRedirectTickets);

            $_optionsContainer = array();
            $_optionsContainer[0]['title'] = $this->Language->Get('noticketrediractive');
            $_optionsContainer[0]['value'] = '0';
            if ($_stepRedirectDepartmentID == 0 || !isset($_departmentCache[$_stepRedirectDepartmentID])) {
                $_optionsContainer[0]['selected'] = true;
            }

            $_index = 1;
            $_departmentMapContainer =  SWIFT_Department::GetDepartmentMap(APP_TICKETS);
            foreach ($_departmentMapContainer as $_departmentID => $_departmentContainer) {
                $_optionsContainer[$_index]['title'] = $_departmentContainer['title'];
                $_optionsContainer[$_index]['value'] = $_departmentID;
                if ($_departmentID == $_stepRedirectDepartmentID) {
                    $_optionsContainer[$_index]['selected'] = true;
                }

                $_index++;

                $subdepartments = (array) $_departmentContainer['subdepartments'];
                foreach ($subdepartments as $_subDepartmentID => $_subDepartmentContainer) {
                    $_optionsContainer[$_index]['title'] = $_subDepartmentContainer['title'];
                    $_optionsContainer[$_index]['value'] = $_subDepartmentID;
                    if ($_subDepartmentID == $_stepRedirectDepartmentID) {
                        $_optionsContainer[$_index]['selected'] = true;
                    }

                    $_index++;
                }
            }

            $_OptionsTabObject->Select('redirectdepartmentid', $this->Language->Get('redirectdepartment'), $this->Language->Get('desc_redirectdepartment'), $_optionsContainer,
                    'javascript: UpdateTicketTypeDiv(this, \'tickettypeid\', true, true);');


            // Type
            $_optionsContainer = array();
            $_index = 1;
            $_optionsContainer[0]['title'] = $this->Language->Get('nochange');
            $_optionsContainer[0]['value'] = '0';
            if ($_stepTicketTypeID == false)
            {
                $_optionsContainer[0]['selected'] = true;
            }

            if (_is_array($_ticketTypeCache))
            {
                foreach ($_ticketTypeCache as $_key => $_val)
                {
                    if ($_val['departmentid'] == '0' || $_val['departmentid'] == $_stepRedirectDepartmentID)
                    {
                        $_optionsContainer[$_index]['title'] = $_val['title'];
                        $_optionsContainer[$_index]['value'] = $_val['tickettypeid'];

                        if ($_stepTicketTypeID == $_val['tickettypeid'])
                        {
                            $_optionsContainer[$_index]['selected'] = true;
                        }

                        $_index++;
                    }
                }
            }
            $_OptionsTabObject->Select('tickettypeid', $this->Language->Get('tickettype'), $this->Language->Get('desc_tickettype'),
                    $_optionsContainer, '', 'tickettypeid_container');

            // Priority
            $_optionsContainer = array();
            $_index = 1;
            $_optionsContainer[0]['title'] = $this->Language->Get('nochange');
            $_optionsContainer[0]['value'] = '0';
            if ($_stepTicketPriorityID == false)
            {
                $_optionsContainer[0]['selected'] = true;
            }

            if (_is_array($_priorityCache))
            {
                foreach ($_priorityCache as $_key => $_val)
                {
                    if ($_val['type'] != SWIFT_PUBLIC)
                    {
                        continue;
                    }

                    $_optionsContainer[$_index]['title'] = $_val['title'];
                    $_optionsContainer[$_index]['value'] = $_val['priorityid'];

                    if (($_stepTicketPriorityID == false && $_index == 0) || $_stepTicketPriorityID == $_val['priorityid'])
                    {
                        $_optionsContainer[$_index]['selected'] = true;
                    }

                    $_index++;
                }
            }

            $_OptionsTabObject->Select('ticketpriorityid', $this->Language->Get('ticketpriority'), $this->Language->Get('desc_ticketpriority'),
                    $_optionsContainer);

            $_OptionsTabObject->Text('ticketsubject', $this->Language->Get('ticketsubject'), $this->Language->Get('desc_ticketsubject'), $_stepTicketSubject);
        }

        /*
         * ###############################################
         * END OPTIONS TAB
         * ###############################################
         */

        /*
         * ###############################################
         * BEGIN ATTACHMENTS TAB
         * ###############################################
         */

        $_attachmentContainerHTML = '<tr class="tablerow1_tr"><td align="left" valign="top class="tablerow1"><div id="trattachmentcontainer">';
        $_attachmentFileHTML = '<div class="ticketattachmentitem"><div class="ticketattachmentitemdelete" onclick="javascript: $(this).parent().remove();">&nbsp;</div><input name="trattachments[]" type="file" size="20" class="swifttextlarge swifttextfile" /></div>';
        for ($index = 0; $index < 3; $index++) {
            $_attachmentContainerHTML .= $_attachmentFileHTML;
        }
        $_attachmentContainerHTML .= '</div></td></tr>';

        $_AttachmentsTabObject->RowHTML($_attachmentContainerHTML);


        if ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT_TroubleshooterStepObject !== null && $_SWIFT_TroubleshooterStepObject->GetProperty('hasattachments') == '1')
        {
            if (count($_attachmentContainer))
            {
                $_AttachmentsTabObject->Title($this->Language->Get('attachedfiles'), 'icon_doublearrows.gif');

                $_attachmentContainerHTML = '<tr class="tablerow1_tr"><td align="left" valign="top class="tablerow1"><div id="trattachmentfilescontainer">';

                foreach ($_attachmentContainer as $_attachment)
                {
                    $_attachmentContainerHTML .= '<div class="ticketattachmentitem"><div class="ticketattachmentitemdelete" onclick="javascript: $(this).parent().remove();">&nbsp;</div> ' . htmlspecialchars($_attachment['filename']) . '<input type="hidden" name="_existingAttachmentIDList[]" value="' . (int) ($_attachment['attachmentid']) . '" /></div>';
                }

                $_attachmentContainerHTML .= '</div></td></tr>';
                $_AttachmentsTabObject->RowHTML($_attachmentContainerHTML);
            }
        }

        /*
         * ###############################################
         * END ATTACHMENTS TAB
         * ###############################################
         */

        $this->UserInterface->Hidden('troubleshootercategoryid', (string) ($_troubleshooterCategoryID));

        $this->UserInterface->End();

        return true;
    }

    /**
     * Render the New Step Dialog
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public function RenderNewStepDialog()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            return false;
        }

        $_optionsContainer = array();
        $_index = 0;
        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "troubleshootercategories ORDER BY displayorder ASC");
        while ($this->Database->NextRecord()) {
            $_optionsContainer[$_index]['title'] = $this->Database->Record['title'] . ' (' . SWIFT_TroubleshooterCategory::GetCategoryTypeLabel($this->Database->Record['categorytype']) . ')';
            $_optionsContainer[$_index]['value'] = $this->Database->Record['troubleshootercategoryid'];

            $_index++;
        }

        $_isDialog = true;
        if (count($_optionsContainer))
        {
            $_isDialog = false;
        }

        $this->UserInterface->Start('newstepdialog', '/Troubleshooter/Step/Insert', SWIFT_UserInterface::MODE_INSERT, $_isDialog);

        if (count($_optionsContainer))
        {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('next'), 'fa-chevron-circle-right ');
        }

        $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('troubleshooterstep'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        $this->UserInterface->SetDialogOptions(false);

        /*
         * ###############################################
         * BEGIN GENERAL TAB
         * ###############################################
         */
        $_GeneralTabObject = $this->UserInterface->AddTab($this->Language->Get('tabgeneral'), 'icon_troubleshooter.gif', 'generalstepdialog', true);

        if (!count($_optionsContainer))
        {
            $_optionsContainer[$_index]['title'] = $this->Language->Get('na');
            $_optionsContainer[$_index]['value'] = '0';
            $_optionsContainer[$_index]['selected'] = true;
        }

        $_GeneralTabObject->Select('troubleshootercategoryid', $this->Language->Get('trcategory'), $this->Language->Get('desc_trcategory'), $_optionsContainer, '', '', '', false, "width:80%");

        /*
         * ###############################################
         * END GENERAL TAB
         * ###############################################
         */

        $this->UserInterface->End();

        return true;
    }

    /**
     * Render the Step Tabs
     *
     * @author Varun Shoor
     * @param bool $_isListTabSelected (OPTIONAL) Whether the grid tab is selected by default
     * @param bool|int $_searchStoreID (OPTIONAL) The optional search store id
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RenderTabs($_isListTabSelected = false, $_searchStoreID = false)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_SWIFT = SWIFT::GetInstance();

        if (!isset($_POST['_searchQuery']))
        {
            $this->UserInterface->Start();

            $this->UserInterface->Toolbar->AddButton($this->Language->Get('insertstep'), 'fa-th-list', 'UICreateWindow(\'/Troubleshooter/Step/InsertDialog/\', \'newstep\', \'' . $this->Language->Get('insertstep') . '\', \'Loading..\', 400, 280, true);', SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, '', '', false);
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('Troubleshootermanage'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

            /*
             * ###############################################
             * BEGIN TREE TAB
             * ###############################################
             */
            $_TreeTabObject = $this->UserInterface->AddTab($this->Language->Get('tabtree'), 'icon_troubleshooter.gif', 'tree', (bool)IIF($_isListTabSelected == false, true, false));

            $_TreeTabObject->RowHTML('<tr><td align="left" valign="top">' . SWIFT_TroubleshooterStepManager::GetCategoryTree() . '</td></tr>');

            /*
             * ###############################################
             * BEGIN LIST TAB
             * ###############################################
             */
            $_ListTabObject = $this->UserInterface->AddTab($this->Language->Get('tablist'), 'icon_form.gif', 'list', (bool)IIF($_isListTabSelected == true, true, false), false, 0);
            $_ListTabObject->LoadToolbar();
        }

        $this->Load->Library('UserInterface:UserInterfaceGrid', array('trstepgrid'), true, false, 'base');

        if ($this->UserInterfaceGrid->GetMode() == SWIFT_UserInterfaceGrid::MODE_SEARCH)
        {
            $this->UserInterfaceGrid->SetSearchQuery('SELECT troubleshootersteps.*, troubleshootercategories.title AS categorytitle FROM ' . TABLE_PREFIX . 'troubleshootersteps AS troubleshootersteps
                LEFT JOIN ' . TABLE_PREFIX . 'troubleshooterdata AS troubleshooterdata ON (troubleshootersteps.troubleshooterstepid = troubleshooterdata.troubleshooterstepid)
                LEFT JOIN ' . TABLE_PREFIX . 'troubleshootercategories AS troubleshootercategories ON (troubleshootersteps.troubleshootercategoryid = troubleshootercategories.troubleshootercategoryid)
                WHERE (' . $this->UserInterfaceGrid->BuildSQLSearch('troubleshootersteps.subject') . ') OR (' . $this->UserInterfaceGrid->BuildSQLSearch('troubleshooterdata.contents') . ')',

                'SELECT COUNT(*) FROM ' . TABLE_PREFIX . 'troubleshootersteps AS troubleshootersteps
                LEFT JOIN ' . TABLE_PREFIX . 'troubleshooterdata AS troubleshooterdata ON (troubleshootersteps.troubleshooterstepid = troubleshooterdata.troubleshooterstepid)
                LEFT JOIN ' . TABLE_PREFIX . 'troubleshootercategories AS troubleshootercategories ON (troubleshootersteps.troubleshootercategoryid = troubleshootercategories.troubleshootercategoryid)
                WHERE (' . $this->UserInterfaceGrid->BuildSQLSearch('troubleshootersteps.subject') . ') OR (' . $this->UserInterfaceGrid->BuildSQLSearch('troubleshooterdata.contents') . ')');
        }

        $this->UserInterfaceGrid->SetSearchStoreOptions((int)$_searchStoreID, 'SELECT troubleshootersteps.*, troubleshootercategories.title AS categorytitle FROM ' . TABLE_PREFIX . 'troubleshootersteps AS troubleshootersteps
            LEFT JOIN ' . TABLE_PREFIX . 'troubleshooterdata AS troubleshooterdata ON (troubleshootersteps.troubleshooterstepid = troubleshooterdata.troubleshooterstepid)
            LEFT JOIN ' . TABLE_PREFIX . 'troubleshootercategories AS troubleshootercategories ON (troubleshootersteps.troubleshootercategoryid = troubleshootercategories.troubleshootercategoryid)
            WHERE troubleshootersteps.troubleshooterstepid IN (%s)', SWIFT_SearchStore::TYPE_TROUBLESHOOTER, '/Troubleshooter/Step/Manage/-1/1');

        $this->UserInterfaceGrid->SetQuery('SELECT troubleshootersteps.*, troubleshootercategories.title AS categorytitle FROM ' . TABLE_PREFIX . 'troubleshootersteps AS troubleshootersteps
            LEFT JOIN ' . TABLE_PREFIX . 'troubleshooterdata AS troubleshooterdata ON (troubleshootersteps.troubleshooterstepid = troubleshooterdata.troubleshooterstepid)
            LEFT JOIN ' . TABLE_PREFIX . 'troubleshootercategories AS troubleshootercategories ON (troubleshootersteps.troubleshootercategoryid = troubleshootercategories.troubleshootercategoryid)',

            'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX . 'troubleshootersteps AS troubleshootersteps
            LEFT JOIN ' . TABLE_PREFIX . 'troubleshooterdata AS troubleshooterdata ON (troubleshootersteps.troubleshooterstepid = troubleshooterdata.troubleshooterstepid)
            LEFT JOIN ' . TABLE_PREFIX . 'troubleshootercategories AS troubleshootercategories ON (troubleshootersteps.troubleshootercategoryid = troubleshootercategories.troubleshootercategoryid)');

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('troubleshooterstepid', 'troubleshooterstepid', SWIFT_UserInterfaceGridField::TYPE_ID));

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('icon', '&nbsp;', SWIFT_UserInterfaceGridField::TYPE_CUSTOM, 16, SWIFT_UserInterfaceGridField::ALIGN_CENTER));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('troubleshootersteps.subject', $this->Language->Get('steptitle'), SWIFT_UserInterfaceGridField::TYPE_DB, 0, SWIFT_UserInterfaceGridField::ALIGN_LEFT, SWIFT_UserInterfaceGridField::SORT_ASC), true);
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('troubleshotercategories.title', $this->Language->Get('categorytitle'), SWIFT_UserInterfaceGridField::TYPE_DB, 200, SWIFT_UserInterfaceGridField::ALIGN_CENTER));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('troubleshootersteps.staffid', $this->Language->Get('author'), SWIFT_UserInterfaceGridField::TYPE_DB, 160, SWIFT_UserInterfaceGridField::ALIGN_CENTER));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('troubleshootersteps.stepstatus', $this->Language->Get('stepstatus'), SWIFT_UserInterfaceGridField::TYPE_DB, 70, SWIFT_UserInterfaceGridField::ALIGN_CENTER));

        $this->UserInterfaceGrid->SetRenderCallback(array($this, 'GridRender'));
        $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('delete'), 'fa-trash', array('\Troubleshooter\Staff\Controller_Step', 'DeleteList'), $this->Language->Get('actionconfirm')));

        $this->UserInterfaceGrid->Render();


        if (!isset($_POST['_searchQuery']) && isset($_ListTabObject))
        {
            $_ListTabObject->RowHTML('<tr><td align="left" valign="top">' . $this->UserInterfaceGrid->GetRenderData() . '</td></tr>');

            $this->UserInterface->End();
        } else {
            $this->UserInterfaceGrid->Display();
        }

        return true;
    }

    /**
     * The Grid Rendering Function
     *
     * @author Varun Shoor
     * @param array $_fieldContainer The Field Record Value Container
     * @return array The Processed Field Container Array
     * @throws SWIFT_Exception
     */
    public static function GridRender($_fieldContainer)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_staffCache = $_SWIFT->Cache->Get('staffcache');

        $_subjectSuffix = '';
        $_icon = 'fa-file-text';
        if ($_fieldContainer['stepstatus'] == SWIFT_TroubleshooterStep::STATUS_DRAFT)
        {
            $_icon = 'fa-file-text-o';
        }

        $_fieldContainer['troubleshotercategories.title'] = htmlspecialchars($_fieldContainer['categorytitle']);

        $_fieldContainer['icon'] = '<i class="fa '. $_icon .'" aria-hidden="true"></i>';

        $_fieldContainer['troubleshootersteps.subject'] = '<a href="' . SWIFT::Get('basename') . '/Troubleshooter/Step/Edit/' . (int) ($_fieldContainer['troubleshooterstepid']) . '" viewport="1" title="' . $_SWIFT->Language->Get('edit') . '">' . htmlspecialchars($_fieldContainer['subject']) . '</a>' . $_subjectSuffix;

        if (isset($_staffCache[$_fieldContainer['staffid']]))
        {
            $_fieldContainer['troubleshootersteps.staffid'] = text_to_html_entities($_staffCache[$_fieldContainer['staffid']]['fullname']);
        } else {
            $_fieldContainer['troubleshootersteps.staffid'] = htmlspecialchars($_fieldContainer['staffname']);
        }

        $_fieldContainer['troubleshootersteps.stepstatus'] = SWIFT_TroubleshooterStep::GetStatusLabel($_fieldContainer['stepstatus']);

        return $_fieldContainer;
    }

    /**
     * Render the Information Box
     *
     * @author Varun Shoor
     * @param SWIFT_TroubleshooterStep $_SWIFT_TroubleshooterStepObject The SWIFT_TroubleshooterStep Object
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RenderInfoBox(SWIFT_TroubleshooterStep $_SWIFT_TroubleshooterStepObject) {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_staffCache = $this->Cache->Get('staffcache');

        $_informationHTML = '';

        $_authorName = $_SWIFT_TroubleshooterStepObject->GetProperty('staffname');
        if (isset($_staffCache[$_SWIFT_TroubleshooterStepObject->GetProperty('staffid')]))
        {
            $_authorName = $_staffCache[$_SWIFT_TroubleshooterStepObject->GetProperty('staffid')]['fullname'];
        }

        if (!empty($_authorName))
        {
            $_informationHTML .= '<div class="navinfoitemtext">' .
                    '<div class="navinfoitemtitle">' . $this->Language->Get('infobauthor') . '</div><div class="navinfoitemcontent">' . htmlspecialchars(StripName($_authorName, 20)) . '</div></div>';
        }

        $_informationHTML .= '<div class="navinfoitemtext">' .
                '<div class="navinfoitemtitle">' . $this->Language->Get('infobcreationdate') . '</div><div class="navinfoitemcontent">' . SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_SWIFT_TroubleshooterStepObject->GetProperty('dateline')) . '</div></div>';

        if ($_SWIFT_TroubleshooterStepObject->GetProperty('edited') == '1')
        {
            $_editedStaffName = $this->Language->Get('na');
            if (isset($_staffCache[$_SWIFT_TroubleshooterStepObject->GetProperty('editedstaffid')]))
            {
                $_editedStaffName = $_staffCache[$_SWIFT_TroubleshooterStepObject->GetProperty('editedstaffid')]['fullname'];
            }

            $_informationHTML .= '<div class="navinfoitemtext">' .
                    '<div class="navinfoitemtitle">' . $this->Language->Get('infobeditedby') . '</div><div class="navinfoitemcontent">' . htmlspecialchars(StripName($_editedStaffName, 20)) . '</div></div>';

            $_informationHTML .= '<div class="navinfoitemtext">' .
                    '<div class="navinfoitemtitle">' . $this->Language->Get('infobeditedon') . '</div><div class="navinfoitemcontent">' . SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_SWIFT_TroubleshooterStepObject->GetProperty('editeddateline')) . '</div></div>';
        }

        $this->UserInterface->AddNavigationBox($this->Language->Get('informationbox'), $_informationHTML);

        return true;
    }

    /**
     * Render the Troubleshooter Tree
     *
     * @author Varun Shoor
     * @return mixed "_renderHTML" (STRING) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RenderQuickFilterTree()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_renderHTML = '<ul class="swifttree">';

        $_renderHTML .= '<li><span class="funnel"><a href="javascript: void(0);" onclick="javascript: void(0);">' . $this->Language->Get('ftcategories') . '</a></span>';
        $_renderHTML .= '<ul>';

        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "troubleshootercategories ORDER BY displayorder ASC");
        while ($this->Database->NextRecord())
        {
            $_extendedText = '';

            $_renderHTML .= '<li><span class="folder"><a href="' . SWIFT::Get('basename') . '/Troubleshooter/Step/QuickFilter/category/' . (int) ($this->Database->Record['troubleshootercategoryid']) . '" viewport="1">' . htmlspecialchars(StripName($this->Database->Record['title'], 16)) . '</a>' . $_extendedText . '</span></li>';
        }
        $_renderHTML .= '</ul></li>';

        $_renderHTML .= '<li><span class="funnel"><a href="javascript: void(0);">' . $this->Language->Get('ftdate') . '</a></span>';
        $_renderHTML .= '<ul>';
            $_renderHTML .= '<li><span class="date"><a href="' . SWIFT::Get('basename') . '/Troubleshooter/Step/QuickFilter/date/today" viewport="1">' . htmlspecialchars($this->Language->Get('ctoday')) . '</a></span></li>';
            $_renderHTML .= '<li><span class="date"><a href="' . SWIFT::Get('basename') . '/Troubleshooter/Step/QuickFilter/date/yesterday" viewport="1">' . htmlspecialchars($this->Language->Get('cyesterday')) . '</a></span></li>';
            $_renderHTML .= '<li><span class="date"><a href="' . SWIFT::Get('basename') . '/Troubleshooter/Step/QuickFilter/date/l7" viewport="1">' . htmlspecialchars($this->Language->Get('cl7days')) . '</a></span></li>';
            $_renderHTML .= '<li><span class="date"><a href="' . SWIFT::Get('basename') . '/Troubleshooter/Step/QuickFilter/date/l30" viewport="1">' . htmlspecialchars($this->Language->Get('cl30days')) . '</a></span></li>';
            $_renderHTML .= '<li><span class="date"><a href="' . SWIFT::Get('basename') . '/Troubleshooter/Step/QuickFilter/date/l180" viewport="1">' . htmlspecialchars($this->Language->Get('cl180days')) . '</a></span></li>';
            $_renderHTML .= '<li><span class="date"><a href="' . SWIFT::Get('basename') . '/Troubleshooter/Step/QuickFilter/date/l365" viewport="1">' . htmlspecialchars($this->Language->Get('cl365days')) . '</a></span></li>';
        $_renderHTML .= '</ul></li>';

        $_renderHTML .= '</ul>';

        return $_renderHTML;
    }

    /**
     * Render the View Steps Page
     *
     * @author Varun Shoor
     * @param array $_dataContainer The Data Container
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public function RenderViewSteps($_dataContainer)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            return false;
        }

        // these will be overwritten by extract
        $_troubleshooterStepContainer = $_attachmentContainer = [];
        $_troubleshooterCategoryID = $_troubleshooterStepSubject = $_troubleshooterStepContents = $_troubleshooterStepHasAttachments = $_troubleshooterStepCount = $_showBackButton = $_troubleshooterStepCount = $_troubleshooterStepHistory = $_troubleshooterStepAllowComments = $_activeTroubleshooterStepID = 0;
        extract($_dataContainer, EXTR_OVERWRITE);

        $_troubleshooterStepContents = StripScriptTags($_troubleshooterStepContents);

        $this->UserInterface->Start('viewalltrsteps', '/Troubleshooter/Step/ViewSteps/' . $_troubleshooterCategoryID, SWIFT_UserInterface::MODE_INSERT, false);

        /*
         * ###############################################
         * BEGIN VIEW ALL TAB
         * ###############################################
         */
        $_tabTitle = $this->Language->Get('tabviewall');
        if (!empty($_extendedTitle))
        {
            $_tabTitle = $_extendedTitle;
        }
        $_ViewAllTabObject = $this->UserInterface->AddTab($_tabTitle, 'icon_troubleshooter.gif', 'viewsteps', true);

        $_renderHTML = '<div class="tabdatacontainer">';

        $_renderHTML .= '<table width="100%" cellpadding="0" cellspacing="0" border="0">';

        $_renderHTML .= '<tr><td valign="top"><div class="trsteptitle"><span class="trsteptitlemain">' . $_troubleshooterStepSubject . '</span></div></td></tr>';

        $_renderHTML .= '<tr><td colspan="2" class="trstepcontents">' . $_troubleshooterStepContents . '</td></tr>';

        $_renderHTML .= '<tr><td colspan="2">';

        if ($_troubleshooterStepHasAttachments == '1' && !empty($_attachmentContainer))
        {
            $_renderHTML .= '<br /><br /><div><table class="hlineheader hlinegray"><tr><th rowspan="2" nowrap>' . $this->Language->Get('trattachments') . '</th><td>&nbsp;</td></tr><tr><td class="hlinelower">&nbsp;</td></tr></table></div>';
            $_renderHTML .= '<div class="trattachments">';

            foreach ($_attachmentContainer as $_attachmentID => $_attachment)
            {
                $_renderHTML .= '<div class="trattachmentitem" onclick="javascript: PopupSmallWindow(\'' . $_attachment['link'] . '\');" style="background-image: URL(\'' . SWIFT::Get('themepathimages') . $_attachment['icon'] . '\');">&nbsp;' . $_attachment['name'] . ' (' . $_attachment['size'] . ')</div>';
            }
            $_renderHTML .= '</div>';
        }

        if ($_troubleshooterStepCount > 0 && !empty($_troubleshooterStepContainer))
        {
            $_renderHTML .= '<br /><div><table class="hlineheader hlinegray"><tr><th rowspan="2" nowrap>' . $this->Language->Get('trnextsteps') . '</th><td>&nbsp;</td></tr><tr><td class="hlinelower">&nbsp;</td></tr></table></div>';

            $_renderHTML .= '<table width="100%" cellpadding="4" cellspacing="1" border="0">';

            foreach ($_troubleshooterStepContainer as $_troubleshooterStepID => $_troubleshooterStep) {
                $_renderHTML .= '<tr>';
                $_renderHTML .= '<td align="left" width="16" valign="middle" class="troubleshooterstepradio"><input id="trstep' . $_troubleshooterStep['troubleshooterstepid'] . '" type="radio" name="nexttroubleshooterstepid" value="' . $_troubleshooterStep['troubleshooterstepid'] . '" /></td>';
                $_renderHTML .= '<td align="left" valign="middle" class="troubleshooterstepsubject"><label for="trstep' . $_troubleshooterStep['troubleshooterstepid'] . '">' . $_troubleshooterStep['subject'] . '</label></td>';
                $_renderHTML .= '</tr>';
            }
            $_renderHTML .= '</table>';
        }

        $_renderHTML .= '<br />';
        $_renderHTML .= '<div class="subcontent">' . IIF($_troubleshooterStepCount > 0, '<input class="rebuttonwide2" value="' . $this->Language->Get('trnext') . '" type="button" onclick="javascript: ajaxFormSubmit(\'viewalltrstepsform\');" name="actiontype" />&nbsp;&nbsp;&nbsp;') . IIF($_showBackButton == true, '<input class="rebuttonwide2" value="' . $this->Language->Get('trback') . '" onclick="javascript: $(\'#trisback\').val(\'1\'); ajaxFormSubmit(\'viewalltrstepsform\');" type="button" name="actiontype" />');
        $_renderHTML .= '</div><br />';
        $_renderHTML .= '<input type="hidden" name="troubleshooterstephistory" value="' . $_troubleshooterStepHistory .'" /><input type="hidden" name="isback" id="trisback" value="0" />';

        $_renderHTML .= '</td></tr></table>';

        if ($_troubleshooterStepAllowComments == '1')
        {
            $_renderHTML .= '<hr class="trstephr" />';
            $_renderHTML .= $this->Controller->CommentManager->LoadStaffCP('Troubleshooter', SWIFT_Comment::TYPE_TROUBLESHOOTER, $_activeTroubleshooterStepID);
        }

        $_renderHTML .= '</div>';

        $_ViewAllTabObject->RowHTML('<tr><td>' . $_renderHTML . '</td></tr>');

        /*
         * ###############################################
         * END VIEW ALL TAB
         * ###############################################
         */

        $this->UserInterface->End();

        return true;
    }
}
