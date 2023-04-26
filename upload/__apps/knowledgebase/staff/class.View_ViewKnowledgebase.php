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

namespace Knowledgebase\Staff;

use Base\Library\Comment\SWIFT_CommentManager;
use Base\Library\HTML\SWIFT_HTMLPurifier;
use Knowledgebase\Models\Article\SWIFT_KnowledgebaseArticle;
use Knowledgebase\Models\Category\SWIFT_KnowledgebaseCategory;
use SWIFT;
use Base\Models\Attachment\SWIFT_Attachment;
use Base\Models\Comment\SWIFT_Comment;
use SWIFT_DataID;
use SWIFT_Date;
use SWIFT_Exception;
use Base\Library\Help\SWIFT_Help;
use SWIFT_MIME_Exception;
use SWIFT_MIMEList;
use Base\Models\Staff\SWIFT_StaffGroupLink;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel;
use Base\Library\UserInterface\SWIFT_UserInterfaceGrid;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;
use SWIFT_View;

/**
 * The Knowledgebase Article View
 *
 * @author Varun Shoor
 *
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @property SWIFT_UserInterfaceGrid $UserInterfaceGrid
 * @property Controller_ViewKnowledgebase $Controller
 */
class View_ViewKnowledgebase extends SWIFT_View
{
    /**
     * Render the View All Page
     *
     * @author Varun Shoor
     * @param int $_knowledgebaseCategoryID (OPTIONAL) The filter by knowledgebase category id
     * @param int $_offset
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     * @throws \Knowledgebase\Models\Category\SWIFT_Category_Exception
     * @throws \Base\Library\Staff\SWIFT_Staff_Exception
     */
    public function RenderViewAll($_knowledgebaseCategoryID = 0, $_offset = 0)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->UserInterface->Start('viewallkb', '/Knowledgebase/ViewKnowledgebase', SWIFT_UserInterface::MODE_INSERT, false);

        $_parentCategoryID = ($_knowledgebaseCategoryID);

        if (!empty($_parentCategoryID)) {
            $_SWIFT_KnowledgebaseCategoryObject = false;
            if (!empty($_parentCategoryID)) {
                try {
                    $_SWIFT_KnowledgebaseCategoryObject = new SWIFT_KnowledgebaseCategory(new SWIFT_DataID($_parentCategoryID));
                } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
                }
            }

            if (!$_SWIFT_KnowledgebaseCategoryObject instanceof SWIFT_KnowledgebaseCategory || !$_SWIFT_KnowledgebaseCategoryObject->GetIsClassLoaded()) {
                return false;
            }

            if ($_SWIFT_KnowledgebaseCategoryObject->GetProperty('categorytype') == SWIFT_KnowledgebaseCategory::TYPE_PUBLIC ||
                    ($_SWIFT_KnowledgebaseCategoryObject->GetProperty('categorytype') == SWIFT_KnowledgebaseCategory::TYPE_INHERIT && !$_SWIFT_KnowledgebaseCategoryObject->IsParentCategoryOfType(array(SWIFT_KnowledgebaseCategory::TYPE_GLOBAL, SWIFT_KnowledgebaseCategory::TYPE_PRIVATE)))) {
                return false;
            }

            if ($_SWIFT_KnowledgebaseCategoryObject->GetProperty('staffvisibilitycustom') == '1') {
                $_filterKnowledgebaseCategoryIDList = SWIFT_StaffGroupLink::RetrieveListFromCacheOnStaffGroup(SWIFT_StaffGroupLink::TYPE_KBCATEGORY, $_SWIFT->Staff->GetProperty('staffgroupid'));

                if (!in_array($_SWIFT_KnowledgebaseCategoryObject->GetKnowledgebaseCategoryID(), $_filterKnowledgebaseCategoryIDList)) {
                    return false;
                }
            }
        }

        $_knowledgebaseMainCategoryContainer = SWIFT_KnowledgebaseCategory::Retrieve(array(SWIFT_KnowledgebaseCategory::TYPE_GLOBAL, SWIFT_KnowledgebaseCategory::TYPE_PRIVATE,
                SWIFT_KnowledgebaseCategory::TYPE_INHERIT), $_parentCategoryID, $_SWIFT->Staff->GetProperty('staffgroupid'), 0);

        $_knowledgebaseCategoryContainer = $_knowledgebaseMainCategoryContainer[0];
        $_dispatchFilterKnowledgebaseCategoryIDList = $_knowledgebaseMainCategoryContainer[1];

        $_tdWidth = round(100/$this->Settings->Get('kb_categorycolumns'));

        $_index = 1;
        foreach ($_knowledgebaseCategoryContainer as $_knowledgebaseCategoryID => $_knowledgebaseCategory) {
            $_knowledgebaseCategoryContainer[$_knowledgebaseCategoryID]['tdwidth'] = $_tdWidth;

            if (!isset($_knowledgebaseCategory['title'])) {
                // @codeCoverageIgnoreStart
                // title is always set in Retrieve
                $_knowledgebaseCategoryContainer[$_knowledgebaseCategoryID]['title'] = false;
            }
            // @codeCoverageIgnoreEnd

            if ($_index > $this->Settings->Get('kb_categorycolumns')) {
                $_index = 1;
                $_knowledgebaseCategoryContainer[$_knowledgebaseCategoryID]['jumptorow'] = true;
            } else {
                // @codeCoverageIgnoreStart
                $_knowledgebaseCategoryContainer[$_knowledgebaseCategoryID]['jumptorow'] = false;
                // @codeCoverageIgnoreEnd
            }

            $_index++;
        }

        if ($_index <= $this->Settings->Get('kb_categorycolumns')) {
            $_difference = $this->Settings->Get('kb_categorycolumns') - ($_index - 1);
            for ($ii = 0; $ii < $_difference; $ii++) {
                $_knowledgebaseCategoryContainer[] = array('title' => false);
            }
        }

        $_knowledgebaseArticleContainer = SWIFT_KnowledgebaseArticle::Retrieve(array($_parentCategoryID));

        $_knowledgebaseCategoryCount = count($_knowledgebaseCategoryContainer);
        $_knowledgebaseArticleCount = count($_knowledgebaseArticleContainer);

        $_knowledgebaseArticleContainer_Popular = $_knowledgebaseArticleContainer_Recent = array();
        if (empty($_parentCategoryID) && $this->Settings->Get('kb_enpopulararticles') == '1') {
            $_knowledgebaseArticleContainer_Popular = SWIFT_KnowledgebaseArticle::RetrieveFilter(SWIFT_KnowledgebaseArticle::FILTER_POPULAR, $_dispatchFilterKnowledgebaseCategoryIDList, $_SWIFT->Staff->GetProperty('staffgroupid'), 0);
        }

        if (empty($_parentCategoryID) && $this->Settings->Get('kb_enlatestarticles') == '1') {
            $_knowledgebaseArticleContainer_Recent = SWIFT_KnowledgebaseArticle::RetrieveFilter(SWIFT_KnowledgebaseArticle::FILTER_RECENT, $_dispatchFilterKnowledgebaseCategoryIDList, $_SWIFT->Staff->GetProperty('staffgroupid'), 0);
        }

        $_showEmptyViewWarning = $_hasNoCategories = false;
        if (count($_knowledgebaseCategoryContainer) == 0 || (isset($_knowledgebaseCategoryContainer[0]['title']) && isset($_knowledgebaseCategoryContainer[1]['title']) && isset($_knowledgebaseCategoryContainer[2]['title']) &&
                $_knowledgebaseCategoryContainer[0]['title'] == false && $_knowledgebaseCategoryContainer[1]['title'] == false && $_knowledgebaseCategoryContainer[2]['title'] == false)) {
            $_hasNoCategories = true;
        }

        if ($_hasNoCategories && count($_knowledgebaseArticleContainer) == 0) {
            $_showEmptyViewWarning = true;
        }

        /*
         * ###############################################
         * BEGIN VIEW ALL TAB
         * ###############################################
         */
        $_ViewAllTabObject = $this->UserInterface->AddTab($this->Language->Get('tabviewall'), 'icon_knowledgebase.png', 'viewall', true);

        $_renderHTML = '<div class="tabdatacontainer">';
        if ($_showEmptyViewWarning) {
            $_renderHTML .= '<div class="dashboardmsg">' . $this->Language->Get('noinfoinview') . '</div>';
        } else {
            /*
            * BUG FIX - Bishwanath Jha <bishwanath.jha@kayako.com>
            *
            * SWIFT-3576: KB Listing - Top Margin.
            *
            * Comments: In case, It has no Category then Do Not render blank Table
            */
            if ($_hasNoCategories === false)
            {
                $_renderHTML .= '<table cellpadding="0" cellspacing="0" width="100%" border="0">';
                $_renderHTML .= '<tr>';

                foreach ($_knowledgebaseCategoryContainer as $_kbCategoryID => $_knowledgebaseCategory) {
                    if (isset($_knowledgebaseCategory['jumptorow']) && $_knowledgebaseCategory['jumptorow'] == true) {
                        $_renderHTML .= '</tr><tr>';
                    }

                    if ($_knowledgebaseCategory['title'] == false) {
                        // @codeCoverageIgnoreStart
                        // title is always set in Retrieve
                        $_renderHTML .= '<td>&nbsp;</td>';
                        // @codeCoverageIgnoreEnd
                    } else {

                        $_renderHTML .= '<td width="' . $_knowledgebaseCategory['tdwidth'] . '%" align="left" valign="top">';

                        /*
                        * BUG FIX - Anjali Sharma
                        *
                        *  SWIFT-3820 Knowledgebase Category name is not rendering correctly at Knowledgebase/List page.
                        */
                        $_renderHTML .= '<div class="kbcategorytitlecontainer"><div class="kbcategorytitle"><a href="' . SWIFT::Get('basename') . '/Knowledgebase/ViewKnowledgebase/Index/' . $_knowledgebaseCategory['kbcategoryid'] . '" viewport="1">' . IIF(!mb_strstr($_knowledgebaseCategory['title'], ' '), wordwrapWithZeroWidthSpace(htmlspecialchars($_knowledgebaseCategory['title'])), htmlspecialchars($_knowledgebaseCategory['title'])) . '</a> <span class="kbcategorycount">' . IIF($_knowledgebaseCategory['totalarticles'] > 0, ' (' . $_knowledgebaseCategory['totalarticles'] . ')') . '</span></div>';

                        foreach ($_knowledgebaseCategory['articles'] as $_knowledgebaseArticle) {
                            // @codeCoverageIgnoreStart
                            $_renderHTML .= '<div class="kbarticlecategorylistitem"><a href="' . SWIFT::Get('basename') . '/Knowledgebase/ViewKnowledgebase/Article/' . $_knowledgebaseArticle['kbarticleid'] . '/' . $_knowledgebaseCategory['kbcategoryid'] . '" viewport="1">' . $_knowledgebaseArticle['subject'] . '<br/><span class="smalltext">(' . $_knowledgebaseArticle['seosubject'] . ')</span></a></div>';
                            // @codeCoverageIgnoreEnd
                        }

                        $_renderHTML .= '</div></td>';
                    }
                }

                $_renderHTML .= '</tr></table>';
                $_renderHTML .= '<br />';
            }

            if ($_knowledgebaseArticleCount > 0) {
                $purifier = new SWIFT_HTMLPurifier();
                $kbPreviewLimit = $this->Settings->get('kb_climit');
                foreach ($_knowledgebaseArticleContainer as $_knowledgebaseArticle) {
                    $_renderHTML .= '<div class="kbarticlecontainer' . IIF($_knowledgebaseArticle['isfeatured'] == '1', ' kbarticlefeatured') . '">';
                    $_renderHTML .= '<div class="kbarticle"><a href="' . SWIFT::Get('basename') . '/Knowledgebase/ViewKnowledgebase/Article/' . $_knowledgebaseArticle['kbarticleid'] . '/' . ($_parentCategoryID) . '" viewport="1">' . $_knowledgebaseArticle['subject'] . ' <span class="smalltext">(' . $_knowledgebaseArticle['seosubject'] . ')</span></a></div>';
                    $_renderHTML .= '<div class="kbarticletext">' .  $purifier->Purify(substr($_knowledgebaseArticle['contents'], 0, $kbPreviewLimit)). '</div>';
                    $_renderHTML .= '</div>';
                }
            }

            if ($_parentCategoryID == 0 && $_showEmptyViewWarning == false) {
                $_renderHTML .= '<table cellpadding="0" cellspacing="0" width="100%" border="0">';
                $_renderHTML .= '<tr>';

                if ($this->Settings->Get('kb_enpopulararticles') == '1') {
                    $_renderHTML .= '<td width="50%" align="left" valign="top"><div class="kbrightstrip">';
                    $_renderHTML .= '<table class="hlineheader hlinegray"><tr><th rowspan="2" nowrap>' . $this->Language->Get('mostpopular') . '</th><td>&nbsp;</td></tr><tr><td class="hlinelower">&nbsp;</td></tr></table>';
                    $_renderHTML .= '</div>';
                    $_renderHTML .= '<ol class="kbarticlelistol">';

                    foreach ($_knowledgebaseArticleContainer_Popular as $_knowledgebaseArticle) {
                        // @codeCoverageIgnoreStart
                        $_renderHTML .= '<li class="kbarticlelist">';
                        $_renderHTML .= '<div class="kbarticlelistitem"><a href="' . SWIFT::Get('basename') . '/Knowledgebase/ViewKnowledgebase/Article/' . $_knowledgebaseArticle['kbarticleid'] . '/' . ($_parentCategoryID) . '" viewport="1">' . $_knowledgebaseArticle['subject'] . '<br/><span class="smalltext">(' . $_knowledgebaseArticle['seosubject'] . ')</span></a></div>';
                        $_renderHTML .= '</li>';
                        // @codeCoverageIgnoreEnd
                    }

                    $_renderHTML .= '</ol>';
                    $_renderHTML .= '</td>';
                }

                if ($this->Settings->Get('kb_enlatestarticles') == '1') {
                    $_renderHTML .= '<td width="' . IIF($this->Settings->Get('kb_enpopulararticles') == '1', '50%', '100%') . '" align="left" valign="top">';
                    $_renderHTML .= '<div class="kbrightstrip"><table class="hlineheader hlinegray"><tr><th rowspan="2" nowrap>' . $this->Language->Get('recentarticles') . '</th><td>&nbsp;</td></tr><tr><td class="hlinelower">&nbsp;</td></tr></table></div>';
                    $_renderHTML .= '<ol class="kbarticlelistol">';

                    foreach ($_knowledgebaseArticleContainer_Recent as $_knowledgebaseArticle) {
                        // @codeCoverageIgnoreStart
                        $_renderHTML .= '<li class="kbarticlelist">';
                        $_renderHTML .= '<div class="kbarticlelistitem"><a href="' . SWIFT::Get('basename') . '/Knowledgebase/ViewKnowledgebase/Article/' . $_knowledgebaseArticle['kbarticleid'] . '/' . ($_parentCategoryID) . '" viewport="1">' . $_knowledgebaseArticle['subject'] . '<br/><span class="smalltext">(' . $_knowledgebaseArticle['seosubject'] . ')</span></a></div>';
                        $_renderHTML .= '</li>';
                        // @codeCoverageIgnoreEnd
                    }

                    $_renderHTML .= '</ol>';
                    $_renderHTML .= '</td>';
                }

                $_renderHTML .= '</tr>';
                $_renderHTML .= '</table>';
            }

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

    /**
     * Render the View Article Page
     *
     * @author Varun Shoor
     * @param array $_knowledgebaseArticleObjectContainer
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     * @throws \Knowledgebase\Models\Article\SWIFT_Article_Exception
     * @throws \Knowledgebase\Models\Category\SWIFT_Category_Exception
     */
    public function RenderViewArticle($_knowledgebaseArticleObjectContainer)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            return false;
        }

        $_SWIFT_KnowledgebaseArticleObject = $_knowledgebaseArticleObjectContainer[0];
        $_SWIFT_KnowledgebaseCategoryObject_Incoming = $_knowledgebaseArticleObjectContainer[1];

        $_knowledgebaseCategoryID = 0;
        if ($_SWIFT_KnowledgebaseCategoryObject_Incoming instanceof SWIFT_KnowledgebaseCategory && $_SWIFT_KnowledgebaseCategoryObject_Incoming->GetIsClassLoaded())
        {
            $_knowledgebaseCategoryID = $_SWIFT_KnowledgebaseCategoryObject_Incoming->GetKnowledgebaseCategoryID();
        }

        if (!$_SWIFT_KnowledgebaseArticleObject instanceof SWIFT_KnowledgebaseArticle || !$_SWIFT_KnowledgebaseArticleObject->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->UserInterface->Start('viewarticle', '/Knowledgebase/Comments/Submit/' . $_SWIFT_KnowledgebaseArticleObject->GetKnowledgebaseArticleID(), SWIFT_UserInterface::MODE_INSERT, false);

        $this->UserInterface->Toolbar->AddButton($this->Language->Get('back'), 'fa-chevron-circle-left', '/Knowledgebase/ViewKnowledgebase/Index/' . ($_knowledgebaseCategoryID), SWIFT_UserInterfaceToolbar::LINK_VIEWPORT);

        if ($_SWIFT->Staff->GetPermission('staff_kbcanmanagearticles') != '0' && $_SWIFT->Staff->GetPermission('staff_kbcanupdatearticle') != '0')
        {
            $this->UserInterface->Toolbar->AddButton('');
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('edit'), 'fa-pencil', '/Knowledgebase/Article/Edit/' . ($_SWIFT_KnowledgebaseArticleObject->GetKnowledgebaseArticleID()), SWIFT_UserInterfaceToolbar::LINK_VIEWPORT);
        }

        $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('knowledgebaseview'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        /*
         * ###############################################
         * BEGIN VIEW ARTICLE TAB
         * ###############################################
         */
        $_ViewArticleTabObject = $this->UserInterface->AddTab($this->Language->Get('tabarticle'), 'icon_knowledgebase.png', 'viewarticle', true);

        $_allowComments = $_knowledgebaseArticleObjectContainer[2];
        $_allowRating = $_knowledgebaseArticleObjectContainer[3];

        $_articleContainer = $_SWIFT_KnowledgebaseArticleObject->RetrieveStore();

        $_categoryContainer = false;
        if ($_SWIFT_KnowledgebaseCategoryObject_Incoming instanceof SWIFT_KnowledgebaseCategory && $_SWIFT_KnowledgebaseCategoryObject_Incoming->GetIsClassLoaded())
        {
            $_categoryContainer = $_SWIFT_KnowledgebaseCategoryObject_Incoming->GetDataStore();
            $_categoryContainer['title'] = htmlspecialchars($_categoryContainer['title']);
        }

        $_articleContainer['allowcomments'] = $_allowComments;
        $_articleContainer['allowrating'] = $_allowRating;
        $_articleContainer['staffid'] = '0';
        if ($_articleContainer['creator'] == SWIFT_KnowledgebaseArticle::CREATOR_STAFF)
        {
            $_articleContainer['staffid'] = $_articleContainer['creatorid'];
        }

        $_hasNotRated = false;
        $_articleRating = '0';

        $_variableContainer = $this->Controller->_ProcessArticleRating($_SWIFT_KnowledgebaseArticleObject);
        extract($_variableContainer);

        // Attachment Logic
        $_attachmentContainer = array();
        if ($_SWIFT_KnowledgebaseArticleObject->GetProperty('hasattachments') == '1')
        {
            $_attachmentContainer = SWIFT_Attachment::Retrieve(SWIFT_Attachment::LINKTYPE_KBARTICLE, $_SWIFT_KnowledgebaseArticleObject->GetKnowledgebaseArticleID());

            foreach ($_attachmentContainer as $_attachmentID => $_attachment)
            {
                $_mimeDataContainer = array();
                try
                {
                    $_fileExtension = mb_strtolower(mb_substr($_attachment['filename'], (mb_strrpos($_attachment['filename'], '.')+1)));

                    $_MIMEListObject = new SWIFT_MIMEList();
                    $_mimeDataContainer = $_MIMEListObject->Get($_fileExtension);
                } catch (SWIFT_MIME_Exception $_SWIFT_MIME_ExceptionObject) {
                    // Do nothing
                }

                $_attachmentIcon = 'icon_file.gif';
                if (isset($_mimeDataContainer[1]))
                {
                    $_attachmentIcon = $_mimeDataContainer[1];
                }

                $_attachmentContainer[$_attachmentID] = array();
                $_attachmentContainer[$_attachmentID]['icon'] = $_attachmentIcon;
                $_attachmentContainer[$_attachmentID]['link'] = SWIFT::Get('basename') . '/Knowledgebase/ViewKnowledgebase/GetAttachment/' . $_SWIFT_KnowledgebaseArticleObject->GetKnowledgebaseArticleID() . '/' . $_attachment['attachmentid'];
                $_attachmentContainer[$_attachmentID]['name'] = htmlspecialchars($_attachment['filename']);
                $_attachmentContainer[$_attachmentID]['size'] = FormattedSize($_attachment['filesize']);
            }
        }


        $_knowledgebaseArticleID = $_SWIFT_KnowledgebaseArticleObject->GetKnowledgebaseArticleID();

        $_renderHTML = '<div class="tabdatacontainer">';

        $_renderHTML .= '<table width="100%" cellpadding="0" cellspacing="0" border="0">';
        $_renderHTML .= '<tr>';
        $_renderHTML .= '<td valign="top">';
        $_renderHTML .= '<div class="kbavatar"><img src="' . SWIFT::Get('basename') . '/Base/StaffProfile/DisplayAvatar/' . $_articleContainer['staffid'] . '/' . $_articleContainer['emailhash'] . '/60'. '" align="absmiddle" border="0" /></div>';
        $_renderHTML .= '<div class="kbtitle"><span class="kbtitlemain">' . $_articleContainer['subject'] . '</span>';
        $_renderHTML .= ' <span class="kbseosubject">(' . $_articleContainer['seosubject'] . ')</span></div>';
        $_renderHTML .= '<div class="kbinfo">' . $this->Language->Get('postedby') . ' ' . $_articleContainer['author'] . ' ' . $this->Language->Get('on') . ' ' . $_articleContainer['date'] . '</div>';
        $_renderHTML .= '</td>';
        $_renderHTML .= '</tr>';
        $_renderHTML .= '<tr><td colspan="2" class="kbcontents">';
        $_renderHTML .= StripScriptTags($_articleContainer['contents']);
        $_renderHTML .= '</td></tr>';
        $_renderHTML .= '<tr>';
        $_renderHTML .= '<td colspan="2">';

        if ($_articleContainer['hasattachments'] == '1')
        {
            $_renderHTML .= '<br /><br />';
            $_renderHTML .= '<div><table class="hlineheader hlinegray"><tr><th rowspan="2" nowrap>' . $this->Language->Get('attachments') . '</th><td>&nbsp;</td></tr><tr><td class="hlinelower">&nbsp;</td></tr></table></div>';
            $_renderHTML .= '<div class="kbattachments">';

            foreach ($_attachmentContainer as $_kbAttachment)
            {
                $_renderHTML .= '<div class="kbattachmentitem" onclick="javascript: PopupSmallWindow(\'' . $_kbAttachment['link'] . '\');" style="background-image: URL(\'' . SWIFT::Get('themepathimages') . $_kbAttachment['icon'] . '\');">&nbsp;' . $_kbAttachment['name'] . ' (' . $_kbAttachment['size'] . ')</div>';
            }
            $_renderHTML .= '</div>';
        }


        if ($_articleContainer['allowrating'] == '1')
        {
            $_renderHTML .= '<div id="kbratingcontainer">';
            $_renderHTML .= $this->GetRating($_articleContainer, $_hasNotRated, $_articleRating);
            $_renderHTML .= '</div>';
        }

        $_renderHTML .= '<hr class="kbhr" /></td>';
        $_renderHTML .= '</tr>';
        $_renderHTML .= '</table>';

        if ($_articleContainer['allowcomments'] == '1')
        {
            $_renderHTML .= $this->Controller->CommentManager->LoadStaffCP('Knowledgebase', SWIFT_Comment::TYPE_KNOWLEDGEBASE, $_articleContainer['kbarticleid']);
        }

        $_renderHTML .= '</div>';


        $_ViewArticleTabObject->RowHTML('<tr><td>' . $_renderHTML . '</td></tr>');

        /*
         * ###############################################
         * END VIEW ITEM TAB
         * ###############################################
         */

        $this->UserInterface->End();

        return true;
    }

    /**
     * Retrieve the parsed rating
     *
     * @author Varun Shoor
     * @param array $_articleContainer The Article Container
     * @param bool $_hasNotRated Whether the staff has rated the kb article
     * @param float $_articleRating
     * @return string
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetRating($_articleContainer, $_hasNotRated, $_articleRating)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_renderHTML = '<div class="kbrating">';
        $_renderHTML .= '<div class="kbratingstars"><img src="' . SWIFT::Get('themepathimages') . 'icon_star_' . $_articleRating . '.gif" align="absmiddle" border="0" title="' . sprintf($this->Language->Get('ratingstars'), $_articleContainer['articlerating']) . '" /><span> (' . $_articleContainer['ratinghits'] . ' ' . $this->Language->Get('votes') . ')</span></div>';
        if ($_hasNotRated == true)
        {
            $_renderHTML .= '<div class="kbratinghelpful" onclick="javascript: ArticleHelpful(\'' . $_articleContainer['kbarticleid'] . '\');"><i class="fa fa-thumbs-o-up" aria-hidden="true"></i> ' . $this->Language->Get('articlehelpful') . '</div><div class="kbratingnothelpful" onclick="javascript: ArticleNotHelpful(\'' . $_articleContainer['kbarticleid'] . '\');"><i class="fa fa-thumbs-o-down" aria-hidden="true"></i> ' . $this->Language->Get('articlenothelpful') . '</div>';
        }
        $_renderHTML .= '</div>';

        return $_renderHTML;
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
            $_informationHTML .= '<div class="navinfoitemtext">' .
                    '<div class="navinfoitemtitle">' . $this->Language->Get('infobauthor') . '</div><div class="navinfoitemcontent">' . htmlspecialchars(StripName($_authorName, 20)) . '</div></div>';
        }

        $_informationHTML .= '<div class="navinfoitemtext">' .
                '<div class="navinfoitemtitle">' . $this->Language->Get('infobcreationdate') . '</div><div class="navinfoitemcontent">' . SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_SWIFT_KnowledgebaseArticleObject->GetProperty('dateline')) . '</div></div>';

        if ($_SWIFT_KnowledgebaseArticleObject->GetProperty('isedited') == '1')
        {
            $_editedStaffName = $this->Language->Get('na');
            if (isset($_staffCache[$_SWIFT_KnowledgebaseArticleObject->GetProperty('editedstaffid')]))
            {
                $_editedStaffName = $_staffCache[$_SWIFT_KnowledgebaseArticleObject->GetProperty('editedstaffid')]['fullname'];
            }

            $_informationHTML .= '<div class="navinfoitemtext">' .
                    '<div class="navinfoitemtitle">' . $this->Language->Get('infobeditedby') . '</div><div class="navinfoitemcontent">' . htmlspecialchars(StripName($_editedStaffName, 20)) . '</div></div>';

            $_informationHTML .= '<div class="navinfoitemtext">' .
                    '<div class="navinfoitemtitle">' . $this->Language->Get('infobeditedon') . '</div><div class="navinfoitemcontent">' . SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_SWIFT_KnowledgebaseArticleObject->GetProperty('editeddateline')) . '</div></div>';
        }


        $this->UserInterface->AddNavigationBox($this->Language->Get('informationbox'), $_informationHTML);

        return true;
    }
}
