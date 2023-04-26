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
 * @copyright    Copyright (c) 2001-2012, Kayako
 * @license        http://www.kayako.com/license
 * @link        http://www.kayako.com
 *
 * ###############################################
 */

namespace Knowledgebase\Staff;

use Base\Library\Comment\SWIFT_CommentManager;
use Controller_StaffBase;
use Knowledgebase\Library\Article\SWIFT_KnowledgebaseArticleManager;
use Knowledgebase\Library\Render\SWIFT_KnowledgebaseRenderManager;
use Knowledgebase\Models\Article\SWIFT_KnowledgebaseArticle;
use SWIFT;
use SWIFT_App;
use Base\Models\Attachment\SWIFT_Attachment;
use SWIFT_Exception;
use SWIFT_Loader;
use Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel;
use News\Models\NewsItem\SWIFT_NewsItem;

/**
 * The Knowledgebase Article Controller
 *
 * @author Varun Shoor
 *
 * @method Library($_libraryName, $_arguments = [], $_initiateInstance = false, $_customAppName = '', $_appName = '')
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @property SWIFT_KnowledgebaseRenderManager $KnowledgebaseRenderManager
 * @property SWIFT_KnowledgebaseArticleManager $KnowledgebaseArticleManager
 * @property View_ViewKnowledgebase $View
 * @property Controller_ViewKnowledgebase $Load
 * @property SWIFT_CommentManager $CommentManager
 */
class Controller_ViewKnowledgebase extends Controller_StaffBase
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
        $this->Load->Library('Article:KnowledgebaseArticleManager');
        $this->Load->Library('Comment:CommentManager', [], true, false, 'base');

        if (SWIFT_App::IsInstalled(APP_TICKETS))
        {
            SWIFT_Loader::LoadModel('Ticket:Ticket', APP_TICKETS);
            SWIFT_Loader::LoadModel('Ticket:TicketPost', APP_TICKETS);
        }

        $this->Language->Load('staff_knowledgebase');
    }

    /**
     * Loads the Display Data
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function _LoadDisplayDataForViewArticle()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UserInterface->AddNavigationBox($this->Language->Get('quickfilter'), $this->KnowledgebaseRenderManager->RenderViewKnowledgebaseTree());

        return true;
    }

    /**
     * View All Knowledgebase Articles
     *
     * @author Varun Shoor
     * @param int $_knowledgebaseCategoryID (OPTIONAL) The filter by knowledgebase category id
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function Index($_knowledgebaseCategoryID = 0)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_knowledgebaseCategoryID = ($_knowledgebaseCategoryID);

        $this->_LoadDisplayDataForViewArticle();

        $this->UserInterface->Header($this->Language->Get('knowledgebase') . ' > ' . $this->Language->Get('view'), self::MENU_ID, 0);

        if ($_SWIFT->Staff->GetPermission('staff_kbcanviewarticles') == '0')
        {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->RenderViewAll($_knowledgebaseCategoryID);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * The Knowledgebase Article Viewing Function
     *
     * @author Varun Shoor
     * @param int $_knowledgebaseArticleID The Knowledgebase Article ID
     * @param int $_knowledgebaseCategoryID (OPTIONAL) The Knowledgebase Category ID
     * @param string $_seoSubject (OPTIONAL) The SEO Subject
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Article($_knowledgebaseArticleID, $_knowledgebaseCategoryID = 0, $_seoSubject = '')
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_knowledgebaseArticleObjectContainer = $this->KnowledgebaseArticleManager->RetrieveForStaff($_knowledgebaseArticleID, $_knowledgebaseCategoryID);
        if (!$_knowledgebaseArticleObjectContainer)
        {
            return false;
        }

        $_SWIFT_KnowledgebaseArticleObject = $_knowledgebaseArticleObjectContainer[0];

        $this->View->RenderInfoBox($_SWIFT_KnowledgebaseArticleObject);
        $this->_LoadDisplayDataForViewArticle();

        $_SWIFT_KnowledgebaseArticleObject->IncrementViews();

        $this->UserInterface->Header($this->Language->Get('knowledgebase') . ' > ' . htmlspecialchars($_SWIFT_KnowledgebaseArticleObject->GetProperty('subject')), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('staff_kbcanviewarticles') == '0')
        {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->RenderViewArticle($_knowledgebaseArticleObjectContainer);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Process the article rating properties
     *
     * @author Varun Shoor
     * @param SWIFT_KnowledgebaseArticle $_SWIFT_KnowledgebaseArticleObject The Knowledgebase Article Object
     * @return bool|array "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function _ProcessArticleRating(SWIFT_KnowledgebaseArticle $_SWIFT_KnowledgebaseArticleObject)
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

        $_articleRating = str_replace('.', '_', $_SWIFT_KnowledgebaseArticleObject->GetProperty('articlerating'));

        return array('_hasNotRated' => $_hasNotRated, '_articleRating' => $_articleRating);
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

        if ($_isHelpful)
        {
            $_SWIFT_KnowledgebaseArticleObject->MarkAsHelpful();
        } else {
            $_SWIFT_KnowledgebaseArticleObject->MarkAsNotHelpful();
        }

        $this->Cookie->Parse('articleratings');
        $this->Cookie->AddVariable('articleratings', $_SWIFT_KnowledgebaseArticleObject->GetKnowledgebaseArticleID(), '1');
        $this->Cookie->Rebuild('articleratings', true);

        $_variableContainer = $this->_ProcessArticleRating($_SWIFT_KnowledgebaseArticleObject);
        $_articleRating = 0;
        extract($_variableContainer, EXTR_OVERWRITE);

        echo $this->View->GetRating($_SWIFT_KnowledgebaseArticleObject->RetrieveStore(), false, $_articleRating);

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
}
