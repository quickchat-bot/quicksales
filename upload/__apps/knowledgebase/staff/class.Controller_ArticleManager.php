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

use Controller_StaffBase;
use Knowledgebase\Library\Render\SWIFT_KnowledgebaseRenderManager;
use Knowledgebase\Models\Article\SWIFT_KnowledgebaseArticle;
use Knowledgebase\Models\Article\SWIFT_KnowledgebaseArticleLink;
use Knowledgebase\Models\Category\SWIFT_KnowledgebaseCategory;
use SWIFT;
use Base\Models\Attachment\SWIFT_Attachment;
use Base\Library\Attachment\SWIFT_AttachmentRenderer;
use SWIFT_DataID;
use SWIFT_DataStore;
use SWIFT_Exception;
use SWIFT_Interface;
use SWIFT_Loader;
use Base\Library\Rules\SWIFT_Rules;
use Base\Models\SearchStore\SWIFT_SearchStore;
use SWIFT_Session;
use Base\Models\Staff\SWIFT_Staff;
use Base\Models\Staff\SWIFT_StaffActivityLog;
use Base\Models\Staff\SWIFT_StaffGroupLink;
use Tickets\Models\Ticket\SWIFT_TicketPost;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel;
use Base\Library\UserInterface\SWIFT_UserInterfaceGrid;

/**
 * The Knowledgebase Article Manager Controller
 *
 * @author Varun Shoor
 *
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @property SWIFT_KnowledgebaseRenderManager $KnowledgebaseRenderManager
 */
class Controller_ArticleManager extends Controller_StaffBase
{
    // Core Constants
    const MENU_ID = 4;
    const NAVIGATION_ID = 1;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @throws SWIFT_Exception
     */
    public function __construct()
    {
        parent::__construct(self::TYPE_STAFF);

        $this->Load->Library('Render:KnowledgebaseRenderManager');

        $this->Language->Load('staff_knowledgebase');
    }

    /**
     * Search Knowledgebase
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetLookup()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($_POST['q']) || empty($_POST['q']))
        {
            return false;
        }

        $_filterStaffKBCategoryIDList = SWIFT_StaffGroupLink::RetrieveListFromCacheOnStaffGroup(SWIFT_StaffGroupLink::TYPE_KBCATEGORY, $_SWIFT->Staff->GetProperty('staffgroupid'));

        $_categoryTypeList = array(SWIFT_KnowledgebaseCategory::TYPE_GLOBAL, SWIFT_KnowledgebaseCategory::TYPE_PRIVATE, SWIFT_KnowledgebaseCategory::TYPE_INHERIT);

        $_knowledgebaseCategoryObjectContainer = $_finalKnowledgebaseCategoryIDList = $_inheritedKnowledgebaseCategoryIDList = array();

        // First get the knowledgebase categories
        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "kbcategories AS kbcategories
            WHERE kbcategories.categorytype IN (" . BuildIN($_categoryTypeList) . ")
                AND (kbcategories.staffvisibilitycustom = '0' OR (kbcategories.staffvisibilitycustom = '1' AND kbcategories.kbcategoryid IN (" . BuildIN($_filterStaffKBCategoryIDList) . ")))
            ");
        while ($this->Database->NextRecord())
        {
            $_knowledgebaseCategoryObjectContainer[$this->Database->Record['kbcategoryid']] = new SWIFT_KnowledgebaseCategory(new SWIFT_DataStore($this->Database->Record));

            if ($this->Database->Record['categorytype'] == SWIFT_KnowledgebaseCategory::TYPE_INHERIT)
            {
                $_inheritedKnowledgebaseCategoryIDList[] = $this->Database->Record['kbcategoryid'];
            } else {
                $_finalKnowledgebaseCategoryIDList[] = $_SWIFT->Database->Record['kbcategoryid'];
            }
        }

        foreach ($_inheritedKnowledgebaseCategoryIDList as $_inheritedKnowledgebaseCategoryID)
        {
            if (!isset($_knowledgebaseCategoryObjectContainer[$_inheritedKnowledgebaseCategoryID]))
            {
                // @codeCoverageIgnoreStart
                // this code will never be executed
                continue;
                // @codeCoverageIgnoreEnd
            }

            $_SWIFT_KnowledgebaseCategoryObject = $_knowledgebaseCategoryObjectContainer[$_inheritedKnowledgebaseCategoryID];

            if ($_SWIFT_KnowledgebaseCategoryObject->IsParentCategoryOfType($_categoryTypeList))
            {
                $_finalKnowledgebaseCategoryIDList[] = $_inheritedKnowledgebaseCategoryID;
            }
        }

        $_finalKnowledgebaseCategoryIDList[] = 0;

        // Now search!
        $this->Database->QueryLimit("SELECT kbarticles.kbarticleid, kbarticles.subject FROM " . TABLE_PREFIX . "kbarticles AS kbarticles
                LEFT JOIN " . TABLE_PREFIX . "kbarticlelinks AS kbarticlelinks ON (kbarticles.kbarticleid = kbarticlelinks.kbarticleid)
                LEFT JOIN " . TABLE_PREFIX . "kbarticledata AS kbarticledata ON (kbarticledata.kbarticleid = kbarticlelinks.kbarticleid)
                WHERE kbarticlelinks.linktype = '" . SWIFT_KnowledgebaseArticleLink::LINKTYPE_CATEGORY . "' AND kbarticlelinks.linktypeid IN (" . BuildIN($_finalKnowledgebaseCategoryIDList) . ")
                    AND kbarticles.articlestatus = '" . SWIFT_KnowledgebaseArticle::STATUS_PUBLISHED . "'
                    AND ((" . BuildSQLSearch('kbarticles.subject', $_POST['q']) . ")
                        OR (" . BuildSQLSearch('kbarticledata.contents', $_POST['q']) . "))
            ORDER BY kbarticles.subject ASC", 6);
        while ($this->Database->NextRecord())
        {
            $_displayHTML = '<b><img src="' . SWIFT::Get('themepathimages') . 'icon_kbarticle.png' . '" align="absmiddle" border="0" /> ' . $this->Database->Record['subject']. '</b><br />';
            echo str_replace('|', '', $_displayHTML) . '|' . $this->Database->Record['kbarticleid'] . SWIFT_CRLF;
        }

        return true;
    }

    /**
     * Retrieve the menu
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetMenu()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_categoryTypeList = array(SWIFT_KnowledgebaseCategory::TYPE_GLOBAL, SWIFT_KnowledgebaseCategory::TYPE_PRIVATE, SWIFT_KnowledgebaseCategory::TYPE_INHERIT);

        $_knowledgebaseCategoryContainer = SWIFT_KnowledgebaseCategory::RetrieveTree($_categoryTypeList, $_SWIFT->Staff->GetProperty('staffgroupid'), 0);


        echo $this->RenderMenu($_knowledgebaseCategoryContainer);

        return true;
    }

    /**
     * Render the Menu
     *
     * @author Varun Shoor
     * @param array $_knowledgebaseCategoryContainer The Knowledgebase Category Container
     * @return string
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function RenderMenu($_knowledgebaseCategoryContainer)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!_is_array($_knowledgebaseCategoryContainer)) {
            return '';
        }

        $_returnHTML = '<ul>';

        $_itemCount = 0;

        if (isset($_knowledgebaseCategoryContainer['subcategories']))
        {
            foreach ($_knowledgebaseCategoryContainer['subcategories'] as $_knowledgebaseCategoryID => $_knowledgebaseCategory)
            {
                $_itemCount++;

                $_returnHTML .= '<li><a href="#"><img src="' . SWIFT::Get('themepathimages') . 'icon_folderyellow3.gif" align="absmiddle" border="0" /> ' . htmlspecialchars($_knowledgebaseCategory['title']) . '</a>';
                    $_returnHTML .= $this->RenderMenu($_knowledgebaseCategory);
                $_returnHTML .= '</li>';
            }
        }

        if (isset($_knowledgebaseCategoryContainer['articles']))
        {
            foreach ($_knowledgebaseCategoryContainer['articles'] as $_knowledgebaseArticleID => $_knowledgebaseArticle)
            {
                $_returnHTML .= '<li><a href="#k_' . $_knowledgebaseArticleID . '">' . htmlspecialchars($_knowledgebaseArticle['subject']) . '</a></li>';

                $_itemCount++;
            }
        }

        if (empty($_itemCount))
        {
            $_returnHTML .= '<li><a href="#k_0">' . $this->Language->Get('noitemstodisplay') . '</a></li>';
        }

        $_returnHTML .= '</ul>';

        return $_returnHTML;
    }

    /**
     * Attempt to retrieve the knowledgebase article id data and dispatch it as JSON
     *
     * @author Varun Shoor
     * @param int $_knowledgebaseArticleID The Knowledgebase Article ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Get($_knowledgebaseArticleID, $_activeTab)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_knowledgebaseArticleID = ($_knowledgebaseArticleID);

        if (empty($_knowledgebaseArticleID))
        {
            echo json_encode(array('contents' => '', 'contentstext' => '', 'attachments' => ''));

            return false;
        }

        $_SWIFT_KnowledgebaseArticleObject = new SWIFT_KnowledgebaseArticle(new SWIFT_DataID($_knowledgebaseArticleID));
        if (!$_SWIFT_KnowledgebaseArticleObject instanceof SWIFT_KnowledgebaseArticle || !$_SWIFT_KnowledgebaseArticleObject->GetIsClassLoaded())
        {
            // @codeCoverageIgnoreStart
            // This code will never be executed
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }

        $_finalDataStore = $_SWIFT_KnowledgebaseArticleObject->GetDataStore();

        $_finalDataStore['contentstext'] = html_entity_decode($_finalDataStore['contentstext'], ENT_QUOTES, 'UTF-8');

        $_finalDataStore['attachments'] = SWIFT_AttachmentRenderer::RenderCheckbox(SWIFT_Attachment::LINKTYPE_KBARTICLE, array($_knowledgebaseArticleID), $_activeTab . 'attachmentslist');

        echo json_encode($_finalDataStore);

        return true;
    }
}
