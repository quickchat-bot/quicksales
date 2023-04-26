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

namespace Knowledgebase\Staff;

use Knowledgebase\Library\Category\SWIFT_KnowledgebaseCategoryManager;
use Knowledgebase\Models\Article\SWIFT_KnowledgebaseArticle;
use Knowledgebase\Models\Article\SWIFT_KnowledgebaseArticleLink;
use Knowledgebase\Models\Category\SWIFT_KnowledgebaseCategory;
use SWIFT;
use Base\Models\Attachment\SWIFT_Attachment;
use SWIFT_DataID;
use SWIFT_Date;
use SWIFT_Exception;
use Base\Library\Help\SWIFT_Help;
use Base\Models\SearchStore\SWIFT_SearchStore;
use Base\Models\Staff\SWIFT_StaffGroupLink;
use Tickets\Models\Ticket\SWIFT_Ticket;
use Tickets\Models\Ticket\SWIFT_TicketPost;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel;
use Base\Library\UserInterface\SWIFT_UserInterfaceGrid;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridField;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridMassAction;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;
use SWIFT_View;

/**
 * The Knowledgebase Article View
 *
 * @author Varun Shoor
 *
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @property SWIFT_UserInterfaceGrid $UserInterfaceGrid
 */
class View_Article extends SWIFT_View
{
    /**
     * Render the Knowledgebase Article Form
     *
     * @author Varun Shoor
     * @param int $_mode The Render Mode
     * @param SWIFT_KnowledgebaseArticle $_SWIFT_KnowledgebaseArticleObject The SWIFT_KnowledgebaseArticle Object Pointer (Only for EDIT
     *     Mode)
     * @param int|bool $_knowledgebaseCategoryID (OPTIONAL) The Knowledgebase Category ID
     * @param SWIFT_TicketPost|null $_SWIFT_TicketPostObject
     * @param int $_ticketID
     * @param string $_listType
     * @param int $_departmentID
     * @param int $_ticketStatusID
     * @param int $_ticketTypeID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public function Render($_mode, SWIFT_KnowledgebaseArticle $_SWIFT_KnowledgebaseArticleObject = null, $_knowledgebaseCategoryID = false, SWIFT_TicketPost $_SWIFT_TicketPostObject = null,
            $_ticketID = 0, $_listType = 'inbox', $_departmentID = -1, $_ticketStatusID = -1, $_ticketTypeID = -1)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            return false;
        }

        $_articleSubject = '';
        $_articleSeoSubject = '';
        $_articleContents = '';
        $_allowComments = true;
        $_isFeatured = false;
        $_hasAttachments = false;
        $_ticketPostID = '0';

        $_knowledgebaseCategoryIDList = array();

        if (!empty($_ticketID))
        {
            $_SWIFT_TicketObject = new SWIFT_Ticket(new SWIFT_DataID($_ticketID));
            if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded() || !$_SWIFT_TicketObject->CanAccess($_SWIFT->Staff) || ($_SWIFT_TicketPostObject !== null &&
                    $_SWIFT_TicketObject->GetTicketID() != $_SWIFT_TicketPostObject->GetProperty('ticketid')))
            {
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            }

            $_parsedContents  = $this->Emoji->decode($_SWIFT_TicketPostObject->GetProperty('contents'));
            $_articleContents = nl2br($_parsedContents);

            $_ticketPostID = $_SWIFT_TicketPostObject->GetProperty('ticketpostid');
        }

        // Calculate the URL
        if ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT_KnowledgebaseArticleObject !== null)
        {
            $this->UserInterface->Start(get_short_class($this),'/Knowledgebase/Article/EditSubmit/' . $_SWIFT_KnowledgebaseArticleObject->GetKnowledgebaseArticleID(), SWIFT_UserInterface::MODE_EDIT, false, true);
        } else {
            $this->UserInterface->Start(get_short_class($this),'/Knowledgebase/Article/InsertSubmit/0', SWIFT_UserInterface::MODE_INSERT, false, true);
        }

        if (!empty($_knowledgebaseCategoryID))
        {
            $_knowledgebaseCategoryIDList[] = $_knowledgebaseCategoryID;
        }

        if (!count($_knowledgebaseCategoryIDList) && $_mode == SWIFT_UserInterface::MODE_INSERT)
        {
            $_knowledgebaseCategoryIDList[] = '0';
        }

        $_attachmentContainer = array();
        if ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT_KnowledgebaseArticleObject !== null && $_SWIFT_KnowledgebaseArticleObject->GetProperty('hasattachments') == '1')
        {
            $_attachmentContainer = SWIFT_Attachment::Retrieve(SWIFT_Attachment::LINKTYPE_KBARTICLE, $_SWIFT_KnowledgebaseArticleObject->GetKnowledgebaseArticleID());
        }

        $_GeneralTabObject = $this->UserInterface->AddTab($this->Language->Get('tabgeneral'), 'icon_knowledgebase.png', 'generalkbinsert', true);
        $_AttachmentsTabObject = $this->UserInterface->AddTab($this->Language->Get('tabattachments'), 'icon_file.gif', 'kbattachments');
        $_AttachmentsTabObject->SetTabCounter(count($_attachmentContainer));
        $_AttachmentsTabObject->LoadToolbar();
        $_OptionsTabObject = $this->UserInterface->AddTab($this->Language->Get('taboptions'), 'icon_settings2.gif', 'kboptions');

        if ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT_KnowledgebaseArticleObject !== null)
        {
            $_articleStatus = $_SWIFT_KnowledgebaseArticleObject->GetProperty('articlestatus');
            if ($_articleStatus == SWIFT_KnowledgebaseArticle::STATUS_DRAFT ||
                    $_articleStatus == SWIFT_KnowledgebaseArticle::STATUS_PENDINGAPPROVAL)
            {
                $this->UserInterface->Toolbar->AddButton($this->Language->Get('publish'), 'fa-check-circle', '/Knowledgebase/Article/EditSubmit/' . $_SWIFT_KnowledgebaseArticleObject->GetKnowledgebaseArticleID() . '/1', SWIFT_UserInterfaceToolbar::LINK_FORM);
                $_AttachmentsTabObject->Toolbar->AddButton($this->Language->Get('publish'), 'fa-check-circle', '/Knowledgebase/Article/EditSubmit/' . $_SWIFT_KnowledgebaseArticleObject->GetKnowledgebaseArticleID() . '/1', SWIFT_UserInterfaceToolbar::LINK_FORM);

                $this->UserInterface->Toolbar->AddButton($this->Language->Get('update'), 'fa-save');
                $_AttachmentsTabObject->Toolbar->AddButton($this->Language->Get('update'), 'fa-save');
            } else {
                $this->UserInterface->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle');
                $_AttachmentsTabObject->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle');
            }

            $this->UserInterface->Toolbar->AddButton('');

            // Allow a user to unpublish (mark article as draft)
            if ($_articleStatus == SWIFT_KnowledgebaseArticle::STATUS_PUBLISHED) {
                $this->UserInterface->Toolbar->AddButton($this->Language->Get('markasdraft'), 'fa-save', '/Knowledgebase/Article/EditSubmit/' . $_SWIFT_KnowledgebaseArticleObject->GetKnowledgebaseArticleID() . '/-1', SWIFT_UserInterfaceToolbar::LINK_FORM);
                $_AttachmentsTabObject->Toolbar->AddButton($this->Language->Get('markasdraft'), 'fa-save', '/Knowledgebase/Article/EditSubmit/' . $_SWIFT_KnowledgebaseArticleObject->GetKnowledgebaseArticleID() . '/-1', SWIFT_UserInterfaceToolbar::LINK_FORM);
            }

            $this->UserInterface->Toolbar->AddButton($this->Language->Get('delete'), 'fa-trash-o', '/Knowledgebase/Article/Delete/' .
                    $_SWIFT_KnowledgebaseArticleObject->GetKnowledgebaseArticleID(), SWIFT_UserInterfaceToolbar::LINK_CONFIRM, '', '', false);
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('manageknowledgebase'),
                    SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

            $_articleSubject = $_SWIFT_KnowledgebaseArticleObject->GetProperty('subject');
            try {
                $_articleSeoSubject = $_SWIFT_KnowledgebaseArticleObject->GetProperty('seosubject');
            } catch (\Exception $ex) {
                $_articleSeoSubject = htmlspecialchars(str_replace(' ', '-', CleanURL($_articleSubject)));
            }
            try {
                $_articleContents = $_SWIFT_KnowledgebaseArticleObject->GetProperty('contents');
            } catch (\Exception $ex) {
                $_articleContents = '';
            }
            $_isFeatured = ($_SWIFT_KnowledgebaseArticleObject->GetProperty('isfeatured'));

            $_allowComments = ($_SWIFT_KnowledgebaseArticleObject->GetProperty('allowcomments'));
            $_hasAttachments = ($_SWIFT_KnowledgebaseArticleObject->GetProperty('hasattachments'));

            $_knowledgebaseCategoryIDList = SWIFT_KnowledgebaseArticleLink::RetrieveLinkIDListOnArticle($_SWIFT_KnowledgebaseArticleObject->GetKnowledgebaseArticleID(), SWIFT_KnowledgebaseArticleLink::LINKTYPE_CATEGORY);

        } else {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('publish'), 'fa-check-circle');
            $_AttachmentsTabObject->Toolbar->AddButton($this->Language->Get('publish'), 'fa-check-circle');

            $this->UserInterface->Toolbar->AddButton('');
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('saveasdraft'), 'fa-save', '/Knowledgebase/Article/InsertSubmit/1', SWIFT_UserInterfaceToolbar::LINK_FORM);
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('kbarticle'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);
        }

        $_AttachmentsTabObject->Toolbar->AddButton('');
        $_AttachmentsTabObject->Toolbar->AddButton($this->Language->Get('addfile'), 'fa-plus-circle', "AddKBFile();", SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, '', '', false);
        $_AttachmentsTabObject->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('kbarticle'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        /*
         * ###############################################
         * BEGIN GENERAL TAB
         * ###############################################
         */

        $_GeneralTabObject->SetColumnWidth('160');

        $_GeneralTabObject->Text('subject', $this->Language->Get('articletitle'), $this->Language->Get('desc_articletitle'), $_articleSubject, 'text', '90');

        $_GeneralTabObject->RowHTML('<td class="tablerow1 tablerowhighlight" align="left" valign="top" width="100"><span class="tabletitle" title="'.$this->Language->Get('desc_articleseo').'">'.$this->Language->Get('articleseosubject').' <i class="fa fa-question-circle"></i></span></td><td class="tablerow1 tablerowhighlight" align="left" valign="top"><input type="text" name="seosubject" id="seosubject" value="'.$_articleSeoSubject.'" size="90" autocomplete="OFF">
</td>');

        $_checkBoxContainer = SWIFT_KnowledgebaseCategoryManager::GetCategoryOptions($_knowledgebaseCategoryIDList, false, true);

        $_GeneralTabObject->CheckBoxContainerList('kbcategoryidlist', $this->Language->Get('articlecategories'), $this->Language->Get('desc_articlecategories'), $_checkBoxContainer, 615);

        $_GeneralTabObject->HTMLEditor('articlecontents', $_articleContents);

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

        $_OptionsTabObject->YesNo('isfeatured', $this->Language->Get('isfeatured'), $this->Language->Get('desc_isfeatured'), $_isFeatured);
        $_OptionsTabObject->YesNo('allowcomments', $this->Language->Get('allowcomments'), $this->Language->Get('desc_allowcommentsarticle'), $_allowComments);

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

        $_attachmentContainerHTML = '<tr class="tablerow1_tr"><td align="left" valign="top class="tablerow1"><div id="kbattachmentcontainer">';
        $_attachmentFileHTML = '<div class="ticketattachmentitem"><div class="ticketattachmentitemdelete" onclick="javascript: $(this).parent().remove();">&nbsp;</div><input name="kbattachments[]" type="file" size="20" class="swifttextlarge swifttextfile" /></div>';
        for ($index = 0; $index < 3; $index++) {
            $_attachmentContainerHTML .= $_attachmentFileHTML;
        }
        $_attachmentContainerHTML .= '</div></td></tr>';

        $_AttachmentsTabObject->RowHTML($_attachmentContainerHTML);


        if ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT_KnowledgebaseArticleObject !== null && $_SWIFT_KnowledgebaseArticleObject->GetProperty('hasattachments') == '1')
        {
            if (count($_attachmentContainer))
            {
                $_AttachmentsTabObject->Title($this->Language->Get('attachedfiles'), 'icon_doublearrows.gif');

                $_attachmentContainerHTML = '<tr class="tablerow1_tr"><td align="left" valign="top class="tablerow1"><div id="kbattachmentfilescontainer">';

                foreach ($_attachmentContainer as $_attachment)
                {
                    $_attachmentContainerHTML .= '<div class="ticketattachmentitem"><div class="ticketattachmentitemdelete" onclick="javascript: $(this).parent().remove();">&nbsp;</div> ' . htmlspecialchars($_attachment['filename']) . '<input type="hidden" name="_existingAttachmentIDList[]" value="' . ($_attachment['attachmentid']) . '" /></div>';
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

        $this->UserInterface->Hidden('tredir_ticketid', $_ticketID);
        $this->UserInterface->Hidden('tredir_listtype', $_listType);
        $this->UserInterface->Hidden('tredir_departmentid', $_departmentID);
        $this->UserInterface->Hidden('tredir_ticketstatusid', $_ticketStatusID);
        $this->UserInterface->Hidden('tredir_tickettypeid', $_ticketTypeID);
        $this->UserInterface->Hidden('tredir_ticketpostid', $_ticketPostID);

        $this->UserInterface->End();

        return true;
    }

    /**
     * Render the Knowledgebase Articles Grid
     *
     * @author Varun Shoor
     * @param bool $_searchStoreID (OPTIONAL) The Search Store ID
     * @param int $_knowledgebaseCategoryID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public function RenderGrid($_searchStoreID = false, $_knowledgebaseCategoryID = 0)
    {
        if (!$this->GetIsClassLoaded())
        {
            return false;
        }

        $this->Load->Library('UserInterface:UserInterfaceGrid', array('kbarticlegrid'), true, false, 'base');

        $_knowledgebaseCategoryContainer = $this->GetCategoriesOnGroup($_knowledgebaseCategoryID);

        if ($this->UserInterfaceGrid->GetMode() == SWIFT_UserInterfaceGrid::MODE_SEARCH) {
            $this->UserInterfaceGrid->SetSearchQuery(
                "SELECT kbarticles.* FROM " . TABLE_PREFIX . "kbarticles AS kbarticles
                LEFT JOIN " . TABLE_PREFIX . "kbarticledata AS kbarticledata ON (kbarticles.kbarticleid = kbarticledata.kbarticleid)
                LEFT JOIN " . TABLE_PREFIX . "kbarticlelinks AS kbarticlelinks ON (kbarticles.kbarticleid = kbarticlelinks.kbarticleid)
                WHERE (((" . $this->UserInterfaceGrid->BuildSQLSearch('kbarticles.subject') . ") OR (" . $this->UserInterfaceGrid->BuildSQLSearch('kbarticledata.contents') . ") OR
                    (" . $this->UserInterfaceGrid->BuildSQLSearch('kbarticles.author') . ") OR (" . $this->UserInterfaceGrid->BuildSQLSearch('kbarticles.email') . ")) AND (linktype = " . SWIFT_KnowledgebaseArticleLink::LINKTYPE_CATEGORY . " ) AND linktypeid IN (\"0\", " . BuildIN($_knowledgebaseCategoryContainer[1]) . "))",

                "SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "kbarticles AS kbarticles
                LEFT JOIN " . TABLE_PREFIX . "kbarticledata AS kbarticledata ON (kbarticles.kbarticleid = kbarticledata.kbarticleid)
                LEFT JOIN " . TABLE_PREFIX . "kbarticlelinks AS kbarticlelinks ON (kbarticles.kbarticleid = kbarticlelinks.kbarticleid)
                WHERE (((" . $this->UserInterfaceGrid->BuildSQLSearch('kbarticles.subject') . ") OR (" . $this->UserInterfaceGrid->BuildSQLSearch('kbarticledata.contents') . ") OR
                    (" . $this->UserInterfaceGrid->BuildSQLSearch('kbarticles.author') . ") OR (" . $this->UserInterfaceGrid->BuildSQLSearch('kbarticles.email') . ")) AND (linktype = " . SWIFT_KnowledgebaseArticleLink::LINKTYPE_CATEGORY . " ) AND linktypeid IN (\"0\", " . BuildIN($_knowledgebaseCategoryContainer[1]) . "))");
        }

        $this->UserInterfaceGrid->SetSearchStoreOptions($_searchStoreID,
                "SELECT kbarticles.* FROM " . TABLE_PREFIX . "kbarticles AS kbarticles
                    WHERE kbarticles.kbarticleid IN (%s)",
                SWIFT_SearchStore::TYPE_KBARTICLE, '/Knowledgebase/Article/Manage/-1');

        $this->UserInterfaceGrid->SetQuery('SELECT kbarticles.* FROM ' . TABLE_PREFIX . 'kbarticles AS kbarticles
        INNER JOIN ' . TABLE_PREFIX . 'kbarticledata AS kbarticledata ON (kbarticles.kbarticleid = kbarticledata.kbarticleid)
                                                LEFT JOIN ' . TABLE_PREFIX . 'kbarticlelinks AS kbarticlelinks ON (kbarticles.kbarticleid = kbarticlelinks.kbarticleid) WHERE linktype = '. SWIFT_KnowledgebaseArticleLink::LINKTYPE_CATEGORY . '
                                                 and linktypeid IN (\'0\', ' . BuildIN($_knowledgebaseCategoryContainer[1]) . ')' , 'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX . 'kbarticles');

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('kbarticleid', 'kbarticleid', SWIFT_UserInterfaceGridField::TYPE_ID));

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('icon', '&nbsp;', SWIFT_UserInterfaceGridField::TYPE_CUSTOM, 16, SWIFT_UserInterfaceGridField::ALIGN_CENTER));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('kbarticles.subject', $this->Language->Get('articletitle'),SWIFT_UserInterfaceGridField::TYPE_DB, 0,
                SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('kbarticles.author', $this->Language->Get('author'),SWIFT_UserInterfaceGridField::TYPE_DB, 160,
                SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('kbarticles.articlestatus', $this->Language->Get('articlestatus'),SWIFT_UserInterfaceGridField::TYPE_DB, 100,
                SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('kbarticles.dateline', $this->Language->Get('creationdate'),SWIFT_UserInterfaceGridField::TYPE_DB, 180,
                SWIFT_UserInterfaceGridField::ALIGN_LEFT, SWIFT_UserInterfaceGridField::SORT_DESC), true);

        /**
         * IMPROVEMENT - Mansi Wason <mansi.wason@opencart.com.vn>
         *
         * SWIFT-1116 Manage Knowledgebase page should have 'Last Update' as a field available for sorting
         */
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('kbarticles.editeddateline', $this->Language->Get('lastupdateon'),SWIFT_UserInterfaceGridField::TYPE_DB, 180,
                                                                            SWIFT_UserInterfaceGridField::ALIGN_CENTER), true);

        $this->UserInterfaceGrid->SetRenderCallback(array($this, 'GridRender'));
        $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('delete'), 'fa-trash',
                array('\Knowledgebase\Staff\Controller_Article', 'DeleteList'), $this->Language->Get('actionconfirm')));

        $this->UserInterfaceGrid->SetNewLinkViewport(SWIFT::Get('basename') . '/Knowledgebase/Article/Insert');

        $this->UserInterfaceGrid->Render();

        $this->UserInterfaceGrid->Display();

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
        $_icon = 'fa-file-text-o';
        if ($_fieldContainer['articlestatus'] == SWIFT_KnowledgebaseArticle::STATUS_DRAFT || $_fieldContainer['articlestatus'] == SWIFT_KnowledgebaseArticle::STATUS_PENDINGAPPROVAL)
        {
            $_icon = 'fa-file-text-o newsdraft';
        }

        $_fieldContainer['icon'] = '<i class="fa '. $_icon .'" aria-hidden="true"></i>';

        $_fieldContainer['kbarticles.subject'] = '<a href="' . SWIFT::Get('basename') . '/Knowledgebase/Article/Edit/' . ($_fieldContainer['kbarticleid']) . '" viewport="1" title="' . $_SWIFT->Language->Get('edit') . '">' . htmlspecialchars($_fieldContainer['subject']) . '</a>' . $_subjectSuffix;

        $_fieldContainer['kbarticles.dateline'] = SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_fieldContainer['dateline']);

        if ($_fieldContainer['creator'] == SWIFT_KnowledgebaseArticle::CREATOR_STAFF && isset($_staffCache[$_fieldContainer['creatorid']]))
        {
            $_fieldContainer['kbarticles.author'] = text_to_html_entities($_staffCache[$_fieldContainer['creatorid']]['fullname']);
        } else {
            $_fieldContainer['kbarticles.author'] = htmlspecialchars($_fieldContainer['author']);
        }
        if ($_fieldContainer['editeddateline'] != 0) {
            $_fieldContainer['kbarticles.editeddateline'] = SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_fieldContainer['editeddateline']);
        }

        $_fieldContainer['kbarticles.articlestatus'] = SWIFT_KnowledgebaseArticle::GetStatusLabel($_fieldContainer['articlestatus']);

        return $_fieldContainer;
    }

    /**
     * Render the Information Box
     *
     * @author Varun Shoor
     * @param SWIFT_KnowledgebaseArticle $_SWIFT_KnowledgebaseArticleObject The SWIFT_KnowledgebaseArticle Object
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RenderInfoBox(SWIFT_KnowledgebaseArticle $_SWIFT_KnowledgebaseArticleObject) {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_staffCache = $this->Cache->Get('staffcache');

        $_informationHTML = '';

        $_authorName = $_SWIFT_KnowledgebaseArticleObject->GetProperty('author');
        if ($_SWIFT_KnowledgebaseArticleObject->GetProperty('creator') == SWIFT_KnowledgebaseArticle::CREATOR_STAFF && isset($_staffCache[$_SWIFT_KnowledgebaseArticleObject->GetProperty('creatorid')]))
        {
            $_authorName = $_staffCache[$_SWIFT_KnowledgebaseArticleObject->GetProperty('creatorid')]['fullname'];
        }

        if (!empty($_authorName))
        {
            $_informationHTML .= '<div class="navinfoitemtext">' . '<div class="navinfoitemtitle">' . $this->Language->Get('infobauthor') . '</div><div class="navinfoitemcontent">' . htmlspecialchars(StripName($_authorName, 20)) . '</div></div>';
        }

        $_informationHTML .= '<div class="navinfoitemtext">' . '<div class="navinfoitemtitle">' . $this->Language->Get('infobcreationdate') . '</div><div class="navinfoitemcontent">' . SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_SWIFT_KnowledgebaseArticleObject->GetProperty('dateline')) . '</div></div>';

        if ($_SWIFT_KnowledgebaseArticleObject->GetProperty('isedited') == '1')
        {
            $_editedStaffName = $this->Language->Get('na');
            if (isset($_staffCache[$_SWIFT_KnowledgebaseArticleObject->GetProperty('editedstaffid')]))
            {
                $_editedStaffName = $_staffCache[$_SWIFT_KnowledgebaseArticleObject->GetProperty('editedstaffid')]['fullname'];
            }

            $_informationHTML .= '<div class="navinfoitemtext">' . '<div class="navinfoitemtitle">' . $this->Language->Get('infobeditedby') . '</div><div class="navinfoitemcontent">' . htmlspecialchars(StripName($_editedStaffName, 20)) . '</div></div>';

            $_informationHTML .= '<div class="navinfoitemtext">' . '<div class="navinfoitemtitle">' . $this->Language->Get('infobeditedon') . '</div><div class="navinfoitemcontent">' . SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_SWIFT_KnowledgebaseArticleObject->GetProperty('editeddateline')) . '</div></div>';
        }


        $_informationHTML .= '<div class="navinfoitemtext">' . '<div class="navinfoitemtitle">' . $this->Language->Get('infobrating') . '</div><div class="navinfoitemcontent"><img src="' . SWIFT::Get('themepathimages') . 'icon_star_' . str_replace('.', '_', $_SWIFT_KnowledgebaseArticleObject->GetProperty('articlerating')) . '.gif" align="absmiddle" border="0" /></div></div>';

        $this->UserInterface->AddNavigationBox($this->Language->Get('informationbox'), $_informationHTML);

        return true;
    }

    /**
     * Get Categories on the basis of user group
     *
     * @author Ashish Kataria
     * @param int $_knowledgebaseCategoryID Knowledgebase category id
     * @return array|bool on Success, "false" otherwise
     * @throws SWIFT_Exception
     * @throws \Knowledgebase\Models\Category\SWIFT_Category_Exception
     */
    public function GetCategoriesOnGroup($_knowledgebaseCategoryID = 0) {
        $_SWIFT = SWIFT::GetInstance();
        $_parentCategoryID = ($_knowledgebaseCategoryID);
        if (!empty($_parentCategoryID))
        {
            $_SWIFT_KnowledgebaseCategoryObject = false;
            if (!empty($_parentCategoryID))
            {
                try
                {
                    $_SWIFT_KnowledgebaseCategoryObject = new SWIFT_KnowledgebaseCategory(new SWIFT_DataID($_parentCategoryID));
                } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
                }
            }
            if (!$_SWIFT_KnowledgebaseCategoryObject instanceof SWIFT_KnowledgebaseCategory || !$_SWIFT_KnowledgebaseCategoryObject->GetIsClassLoaded())
            {
                return false;
            }

            if ($_SWIFT_KnowledgebaseCategoryObject->GetProperty('categorytype') == SWIFT_KnowledgebaseCategory::TYPE_PUBLIC ||
                ($_SWIFT_KnowledgebaseCategoryObject->GetProperty('categorytype') == SWIFT_KnowledgebaseCategory::TYPE_INHERIT && !$_SWIFT_KnowledgebaseCategoryObject->IsParentCategoryOfType(array(SWIFT_KnowledgebaseCategory::TYPE_GLOBAL, SWIFT_KnowledgebaseCategory::TYPE_PRIVATE)))) {
                return false;
            }
            if ($_SWIFT_KnowledgebaseCategoryObject->GetProperty('staffvisibilitycustom') == '1')
            {
                $_filterKnowledgebaseCategoryIDList = SWIFT_StaffGroupLink::RetrieveListFromCacheOnStaffGroup(SWIFT_StaffGroupLink::TYPE_KBCATEGORY, $_SWIFT->Staff->GetProperty('staffgroupid'));
                if (!in_array($_SWIFT_KnowledgebaseCategoryObject->GetKnowledgebaseCategoryID(), $_filterKnowledgebaseCategoryIDList))
                {
                    return false;
                }
            }
        }
        $_categoryContainer = SWIFT_KnowledgebaseCategory::Retrieve(array(SWIFT_KnowledgebaseCategory::TYPE_GLOBAL, SWIFT_KnowledgebaseCategory::TYPE_PRIVATE,
                                                                          SWIFT_KnowledgebaseCategory::TYPE_INHERIT), $_parentCategoryID, $_SWIFT->Staff->GetProperty('staffgroupid'), 0);
        return $_categoryContainer;
    }
}
