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

namespace Knowledgebase\Client;

use Controller_client;
use Knowledgebase\Library\Article\SWIFT_KnowledgebaseArticleManager;
use Knowledgebase\Models\Article\SWIFT_KnowledgebaseArticle;
use Knowledgebase\Models\Article\SWIFT_KnowledgebaseArticleLink;
use Knowledgebase\Models\Category\SWIFT_KnowledgebaseCategory;
use SWIFT;
use SWIFT_App;
use Base\Models\Attachment\SWIFT_Attachment;
use Base\Models\Comment\SWIFT_Comment;
use Base\Library\Comment\SWIFT_CommentManager;
use SWIFT_DataID;
use SWIFT_Date;
use SWIFT_Exception;
use SWIFT_MIME_Exception;
use SWIFT_MIMEList;
use Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel;
use Base\Models\Widget\SWIFT_Widget;

/**
 * The Knowledgebase Article Controller
 *
 * @author Varun Shoor
 *
 * @property SWIFT_KnowledgebaseArticleManager $KnowledgebaseArticleManager
 * @property SWIFT_CommentManager $CommentManager
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 */
class Controller_Article extends Controller_client
{
    /**
     * Constructor
     *
     * @author Varun Shoor
     * @throws \SWIFT_Exception
     */
    public function __construct()
    {
        parent::__construct();

        $this->Load->Library('Article:KnowledgebaseArticleManager');
        $this->Load->Library('Comment:CommentManager', [], true, false, 'base');

        $this->Language->Load('knowledgebase');

        $this->_ProcessKnowledgebaseCategories();
    }

    /**
     * The Knowledgebase Article Viewing Function
     *
     * @author Varun Shoor
     * @param string|int $_seoSubjectOrId The SEO Subject or numeric ID
     * @param int $_knowledgebaseCategoryID (OPTIONAL) The Knowledgebase Category ID
     * @param string $_seoSubject (OPTIONAL) The SEO Subject
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function View($_seoSubjectOrId, $_knowledgebaseCategoryID = 0, $_seoSubject = '')
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_seoSubjectOrId)) {
			$this->Load->Controller('List', 'Knowledgebase')->Load->Index();

            return false;
        }

        if (!SWIFT_App::IsInstalled(APP_KNOWLEDGEBASE) || !SWIFT_Widget::IsWidgetVisible(APP_KNOWLEDGEBASE, 'knowledgebase'))
        {
            $this->UserInterface->Error(true, $this->Language->Get('kbnopermission'));
            $this->Load->Controller('Default', 'Core')->Load->Index();

            return false;
        }

        if (is_numeric($_seoSubjectOrId)){
            $_knowledgebaseArticleObjectContainer = $this->KnowledgebaseArticleManager->RetrieveForUser($_seoSubjectOrId, $_knowledgebaseCategoryID);
        } else {
            $_knowledgebaseArticleObjectContainer = $this->KnowledgebaseArticleManager->RetrieveBySeoSubject($_seoSubjectOrId);
        }
        if (!$_knowledgebaseArticleObjectContainer)
        {
            $this->UserInterface->Error(true, $this->Language->Get('kbnopermission'));

            $this->Load->Controller('List', 'Knowledgebase')->Load->Index();

            return false;
        }

        $_SWIFT_KnowledgebaseArticleObject = $_knowledgebaseArticleObjectContainer[0];
        $_SWIFT_KnowledgebaseCategoryObject_Incoming = $_knowledgebaseArticleObjectContainer[1];
        $_allowComments = $_knowledgebaseArticleObjectContainer[2];
        $_allowRating = $_knowledgebaseArticleObjectContainer[3];
        if (!$_SWIFT_KnowledgebaseArticleObject instanceof SWIFT_KnowledgebaseArticle || !$_SWIFT_KnowledgebaseArticleObject->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        // fetch id and category from subject
        $_knowledgebaseArticleID = $_SWIFT_KnowledgebaseArticleObject->GetKnowledgebaseArticleID();
        if ($_knowledgebaseCategoryID === 0) {
            $_knowledgebaseCategoryIDList = SWIFT_KnowledgebaseArticleLink::RetrieveLinkIDListOnArticle($_knowledgebaseArticleID, SWIFT_KnowledgebaseArticleLink::LINKTYPE_CATEGORY);
            $_knowledgebaseCategoryID = $_knowledgebaseCategoryIDList[0];
        }

        // Retrieve KB category object when category ID is not provided
        if (!empty($_knowledgebaseCategoryID) && !$_SWIFT_KnowledgebaseCategoryObject_Incoming) {
            $_SWIFT_KnowledgebaseCategoryObject_Incoming = new SWIFT_KnowledgebaseCategory(new SWIFT_DataID($_knowledgebaseCategoryID));
        }

        $_categoryContainer = false;
        if ($_SWIFT_KnowledgebaseCategoryObject_Incoming instanceof SWIFT_KnowledgebaseCategory && $_SWIFT_KnowledgebaseCategoryObject_Incoming->GetIsClassLoaded())
        {
            $_categoryContainer = $_SWIFT_KnowledgebaseCategoryObject_Incoming->GetDataStore();
            $_categoryContainer['title'] = htmlspecialchars($_categoryContainer['title']);
        }

        if (!empty($_knowledgebaseCategoryID) && empty($_categoryContainer)) {
            $this->UserInterface->Error(true, $this->Language->Get('kbnopermission'));

            $this->Load->Controller('List', 'Knowledgebase')->Load->Index();

            return false;

        }
        $_parentCategoryList = SWIFT_KnowledgebaseCategory::RetrieveParentCategoryList($_knowledgebaseCategoryID, array(SWIFT_KnowledgebaseCategory::TYPE_GLOBAL, SWIFT_KnowledgebaseCategory::TYPE_PUBLIC, SWIFT_KnowledgebaseCategory::TYPE_INHERIT), SWIFT::Get('usergroupid'));
        $_articleContainer = $_SWIFT_KnowledgebaseArticleObject->RetrieveStore();

        $this->Template->Assign('_parentCategoryList', $_parentCategoryList);

        $_articleContainer['allowcomments'] = $_allowComments;
        $_articleContainer['allowrating'] = $_allowRating;

        $_articleContainer['staffid'] = '0';
        if ($_articleContainer['creator'] == SWIFT_KnowledgebaseArticle::CREATOR_STAFF)
        {
            $_articleContainer['staffid'] = $_articleContainer['creatorid'];
        }

        $this->_ProcessArticleRating($_SWIFT_KnowledgebaseArticleObject);

        /**
         * BUG FIX - Ravi Sharma <ravi.sharma@kayako.com>
         *
         * SWIFT-4823 Knowledge base article author and edit date fields don't update
         */
        $_staffCache                      = $this->Cache->Get('staffcache');
        $_articleContainer['editedstaff'] = '';
        if ($_articleContainer['isedited'] == '1' && isset($_staffCache[$_articleContainer['editedstaffid']])) {
            $_articleContainer['editedstaff'] = $_staffCache[$_articleContainer['editedstaffid']]['fullname'];
            $_articleContainer['date']        = SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_articleContainer['editeddateline']);
        }

        $this->Template->Assign('_knowledgebaseArticle', $_articleContainer);

        $this->CommentManager->LoadSupportCenter('Knowledgebase', SWIFT_Comment::TYPE_KNOWLEDGEBASE, $_knowledgebaseArticleID);

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
                    $_fileExtension = mb_strtolower(substr($_attachment['filename'], (strrpos($_attachment['filename'], '.')+1)));

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
                $_attachmentContainer[$_attachmentID]['link'] = SWIFT::Get('basename') . '/Knowledgebase/Article/GetAttachment/' . $_SWIFT_KnowledgebaseArticleObject->GetKnowledgebaseArticleID() . '/' . $_attachment['attachmentid'];
                $_attachmentContainer[$_attachmentID]['name'] = htmlspecialchars($_attachment['filename']);
                $_attachmentContainer[$_attachmentID]['size'] = FormattedSize($_attachment['filesize']);
            }
        }

        $_SWIFT_KnowledgebaseArticleObject->IncrementViews();

        $this->Template->Assign('_attachmentContainer', $_attachmentContainer);
        $this->Template->Assign('_pageTitle', htmlspecialchars($_SWIFT_KnowledgebaseArticleObject->GetProperty('subject')));

        $this->UserInterface->Header('knowledgebase');
        $this->Template->Render('knowledgebasearticle');
        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Process the article rating properties
     *
     * @author Varun Shoor
     * @param SWIFT_KnowledgebaseArticle $_SWIFT_KnowledgebaseArticleObject The Knowledgebase Article Object
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function _ProcessArticleRating(SWIFT_KnowledgebaseArticle $_SWIFT_KnowledgebaseArticleObject)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->Cookie->Parse('articleratings');
        $_hasNotRated = true;

        if ($this->Cookie->GetVariable('articleratings', $_SWIFT_KnowledgebaseArticleObject->GetKnowledgebaseArticleID()) == true)
        {
            $_hasNotRated = false;
        }

        $_rating = $_SWIFT_KnowledgebaseArticleObject->GetProperty('articlerating');
        $this->Template->Assign('_ratingTitle', sprintf($this->Language->Get('ratingstars'), $_rating));
        $this->Template->Assign('_hasNotRated', $_hasNotRated);
        $this->Template->Assign('_articleRating', str_replace('.', '_', $_rating));

        return true;
    }

    /**
     * The Knowledgebase Article Rating Function
     *
     * @author Varun Shoor
     * @param int $_knowledgebaseArticleID The Knowledgebase Article ID
     * @param bool $_isHelpful (OPTIONAL) Whether the article was helpful
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Rate($_knowledgebaseArticleID, $_isHelpful = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!SWIFT_App::IsInstalled(APP_KNOWLEDGEBASE) || !SWIFT_Widget::IsWidgetVisible(APP_KNOWLEDGEBASE, 'knowledgebase'))
        {
            $this->UserInterface->Error(true, $this->Language->Get('kbnopermission'));
            $this->Load->Controller('Default', 'Core')->Load->Index();

            return false;
        }

        $_knowledgebaseArticleObjectContainer = $this->KnowledgebaseArticleManager->RetrieveForUser($_knowledgebaseArticleID);
        if (!$_knowledgebaseArticleObjectContainer)
        {
            return false;
        }

        $_SWIFT_KnowledgebaseArticleObject = $_knowledgebaseArticleObjectContainer[0];
        if (!$_SWIFT_KnowledgebaseArticleObject instanceof SWIFT_KnowledgebaseArticle || !$_SWIFT_KnowledgebaseArticleObject->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->Cookie->Parse('articleratings');
        $_hasNotRated = true;

        if ($this->Cookie->GetVariable('articleratings', $_SWIFT_KnowledgebaseArticleObject->GetKnowledgebaseArticleID()) == true)
        {
            $_hasNotRated = false;
        }

        if ($_hasNotRated) {
            if ($_isHelpful)
            {
                $_SWIFT_KnowledgebaseArticleObject->MarkAsHelpful();
            } else {
                $_SWIFT_KnowledgebaseArticleObject->MarkAsNotHelpful();
            }

            $this->Cookie->AddVariable('articleratings', $_SWIFT_KnowledgebaseArticleObject->GetKnowledgebaseArticleID(), '1');
            $this->Cookie->Rebuild('articleratings', true);
        }

        $this->_ProcessArticleRating($_SWIFT_KnowledgebaseArticleObject);
        $this->Template->Assign('_hasNotRated', false);
        $this->Template->Assign('_knowledgebaseArticle', $_SWIFT_KnowledgebaseArticleObject->RetrieveStore());

        $this->Template->Render('knowledgebaserating');

        return true;
    }

    /**
     * Dispatch the Attachment
     *
     * @author Varun Shoor
     * @param int $_knowledgebaseArticleID The Knowledgebase Article ID
     * @param int $_attachmentID The Attachment ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetAttachment($_knowledgebaseArticleID, $_attachmentID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!SWIFT_App::IsInstalled(APP_KNOWLEDGEBASE) || !SWIFT_Widget::IsWidgetVisible(APP_KNOWLEDGEBASE, 'knowledgebase'))
        {
            $this->UserInterface->Error(true, $this->Language->Get('kbnopermission'));
            $this->Load->Controller('Default', 'Core')->Load->Index();

            return false;
        }

        $_knowledgebaseArticleObjectContainer = $this->KnowledgebaseArticleManager->RetrieveForUser($_knowledgebaseArticleID);
        if (!$_knowledgebaseArticleObjectContainer)
        {
            return false;
        }

        $_SWIFT_KnowledgebaseArticleObject = $_knowledgebaseArticleObjectContainer[0];
        if (!$_SWIFT_KnowledgebaseArticleObject instanceof SWIFT_KnowledgebaseArticle || !$_SWIFT_KnowledgebaseArticleObject->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_AttachmentObject = new SWIFT_Attachment($_attachmentID);
        // Did the object load up?
        if (!$_SWIFT_AttachmentObject instanceof SWIFT_Attachment || !$_SWIFT_AttachmentObject->GetIsClassLoaded()) {
            // @codeCoverageIgnoreStart
            // This code will never be executed
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }

        if ($_SWIFT_AttachmentObject->GetProperty('linktype') != SWIFT_Attachment::LINKTYPE_KBARTICLE || $_SWIFT_AttachmentObject->GetProperty('linktypeid') != $_SWIFT_KnowledgebaseArticleObject->GetKnowledgebaseArticleID()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_AttachmentObject->Dispatch();

        return true;
    }

    /**
     * Run IRS Search
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function IRS()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!SWIFT_App::IsInstalled(APP_KNOWLEDGEBASE) || !SWIFT_Widget::IsWidgetVisible(APP_KNOWLEDGEBASE, 'knowledgebase'))
        {
            return false;
        }

        $this->Language->Load('tickets');

        $_knowledgebaseArticleContainer = array();

        if (isset($_POST['contents']) && str_word_count($_POST['contents']) <= $this->Settings->Get('t_maxwcnt'))
        {
            $_knowledgebaseArticleContainer = SWIFT_KnowledgebaseArticle::RetrieveFullText($_POST['contents'], SWIFT::Get('usergroupid'));
        }

        $_irsResults = false;
        if (count($_knowledgebaseArticleContainer))
        {
            $_irsResults = true;
        }

        $this->Template->Assign('_irsResults', $_irsResults);
        $this->Template->Assign('_knowledgebaseArticleContainer', $_knowledgebaseArticleContainer);

        $this->Template->Render('irssuggestions');

        return true;
    }
}
