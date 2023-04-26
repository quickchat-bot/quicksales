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
use SWIFT;
use SWIFT_App;
use Base\Models\Comment\SWIFT_Comment;
use Base\Library\Comment\SWIFT_CommentManager;
use SWIFT_Exception;
use Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel;
use Base\Models\Widget\SWIFT_Widget;

/**
 * Comments Controller: Knowledgebase Article
 *
 * @author Varun Shoor
 *
 * @property SWIFT_KnowledgebaseArticleManager $KnowledgebaseArticleManager
 * @property SWIFT_CommentManager $CommentManager
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 */
class Controller_Comments extends Controller_client
{
    /**
     * Constructor
     *
     * @author Varun Shoor
     * @throws SWIFT_Exception
     */
    public function __construct()
    {
        parent::__construct();

        /*
        * BUG FIX - Parminder Singh
        *
        * SWIFT-2528: Widget particular pages shows up using direct URIs irrespective of whether the widget's visibility is restricted.
        *
        * Comments: None
        */
        if (!SWIFT_App::IsInstalled(APP_KNOWLEDGEBASE) || !SWIFT_Widget::IsWidgetVisible(APP_KNOWLEDGEBASE, 'knowledgebase'))
        {
            $this->UserInterface->Error(true, $this->Language->Get('nopermission'));
            $this->Load->Controller('Default', 'Core')->Load->Index();

            return;
        }

        $this->Load->Library('Comment:CommentManager', [], true, false, 'base');
        $this->Load->Library('Article:KnowledgebaseArticleManager');
    }

    /**
     * Submit a new Comment
     *
     * @author Varun Shoor
     * @param int $_knowledgebaseArticleID The Knowledgebase Article ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Submit($_knowledgebaseArticleID)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
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

        try {
            $parentURL = SWIFT::Get('basename') . '/Knowledgebase/Article/View/' . $_SWIFT_KnowledgebaseArticleObject->GetProperty('seosubject');
        } catch(\Exception $ex) {
            $parentURL = SWIFT::Get('basename') . '/Knowledgebase/Article/View/' . $_SWIFT_KnowledgebaseArticleObject->GetKnowledgebaseArticleID();
        }

        $_commentResult = $this->CommentManager->ProcessPOSTUser(SWIFT_Comment::TYPE_KNOWLEDGEBASE, $_SWIFT_KnowledgebaseArticleObject->GetKnowledgebaseArticleID(), $parentURL);

        if ($_commentResult) {
            unset($_POST['fullname']); unset($_POST['email']); unset($_POST['comments']);
        }

        $_knowledgebaseCategoryIDList = SWIFT_KnowledgebaseArticleLink::RetrieveLinkIDListOnArticle($_knowledgebaseArticleID, SWIFT_KnowledgebaseArticleLink::LINKTYPE_CATEGORY);

        $_knowledgebaseCategoryID = $_knowledgebaseCategoryIDList[0];

        $this->Load->Controller('Article', 'Knowledgebase')->Load->Method('View', $_knowledgebaseArticleID, $_knowledgebaseCategoryID);

        return true;
    }
}
