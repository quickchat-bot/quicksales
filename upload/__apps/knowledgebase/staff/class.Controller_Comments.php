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
use Knowledgebase\Library\Article\SWIFT_KnowledgebaseArticleManager;
use Knowledgebase\Models\Article\SWIFT_KnowledgebaseArticle;
use SWIFT;
use Base\Models\Comment\SWIFT_Comment;
use Base\Library\Comment\SWIFT_CommentManager;
use SWIFT_Exception;
use SWIFT_Loader;

/**
 * Comments Controller: Knowledgebase
 *
 * @author Varun Shoor
 *
 * @method Controller($_libraryName, $_arguments = [], $_initiateInstance = false, $_customAppName = '', $_appName = '')
 * @method Library($_libraryName, $_arguments = [], $_initiateInstance = false, $_customAppName = '', $_appName = '')
 * @property SWIFT_CommentManager $CommentManager
 * @property SWIFT_KnowledgebaseArticleManager $KnowledgebaseArticleManager
 * @property Controller_Comments $Load
 */
class Controller_Comments extends Controller_StaffBase
{
    /**
     * Constructor
     *
     * @author Varun Shoor
     * @throws SWIFT_Exception
     */
    public function __construct()
    {
        parent::__construct(self::TYPE_STAFF);

        $this->Load->Library('Comment:CommentManager', [], true, false, 'base');
        $this->Load->Library('Render:KnowledgebaseRenderManager');
        $this->Load->Library('Article:KnowledgebaseArticleManager');

        $this->Language->Load('staff_knowledgebase');
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
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_knowledgebaseArticleObjectContainer = $this->KnowledgebaseArticleManager->RetrieveForStaff($_knowledgebaseArticleID);
        if (!$_knowledgebaseArticleObjectContainer)
        {
            return false;
        }

        $_SWIFT_KnowledgebaseArticleObject = $_knowledgebaseArticleObjectContainer[0];
        if (!$_SWIFT_KnowledgebaseArticleObject instanceof SWIFT_KnowledgebaseArticle || !$_SWIFT_KnowledgebaseArticleObject->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->CommentManager->ProcessPOSTStaff($_SWIFT->Staff, SWIFT_Comment::TYPE_KNOWLEDGEBASE, $_SWIFT_KnowledgebaseArticleObject->GetKnowledgebaseArticleID(), SWIFT::Get('basename') . '/Knowledgebase/ViewKnowledgebase/Article/' . $_SWIFT_KnowledgebaseArticleObject->GetKnowledgebaseArticleID());

        $this->Load->Controller('ViewKnowledgebase', 'Knowledgebase')->Load->Method('Article', $_knowledgebaseArticleID);

        return true;
    }
}
