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
use Knowledgebase\Models\Category\SWIFT_KnowledgebaseCategory;
use SWIFT;
use SWIFT_App;
use Base\Library\Comment\SWIFT_CommentManager;
use SWIFT_DataID;
use SWIFT_Exception;
use Base\Models\User\SWIFT_UserGroupAssign;
use Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel;
use Base\Models\Widget\SWIFT_Widget;

/**
 * The Knowledgebase List Controller
 *
 * @author Varun Shoor
 *
 * @property SWIFT_KnowledgebaseArticleManager $KnowledgebaseArticleManager
 * @property SWIFT_CommentManager $CommentManager
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 */
class Controller_List extends Controller_client
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

        $this->Language->Load('knowledgebase');

        $this->_ProcessKnowledgebaseCategories();
    }

    /**
     * The Knowledgebase Rendering Function
     *
     * @author Varun Shoor
     * @param int $_parentCategoryID (OPTIONAL) The Knowledgebase Category ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Index($_parentCategoryID = 0)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        /**
         * ---------------------------------------------
         * WARNING: When modifying this code, make sure you modify the code in staff end too
         * ---------------------------------------------
         */

        $_parentCategoryID = ($_parentCategoryID);
        $_parentCategoryList = array();

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

            if ($_SWIFT_KnowledgebaseCategoryObject->GetProperty('categorytype') == SWIFT_KnowledgebaseCategory::TYPE_PRIVATE ||
                    ($_SWIFT_KnowledgebaseCategoryObject->GetProperty('categorytype') == SWIFT_KnowledgebaseCategory::TYPE_INHERIT && !$_SWIFT_KnowledgebaseCategoryObject->IsParentCategoryOfType(array(SWIFT_KnowledgebaseCategory::TYPE_GLOBAL, SWIFT_KnowledgebaseCategory::TYPE_PUBLIC)))) {
                return false;
            }

            $_parentCategoryList = SWIFT_KnowledgebaseCategory::RetrieveParentCategoryList($_parentCategoryID, array(SWIFT_KnowledgebaseCategory::TYPE_GLOBAL, SWIFT_KnowledgebaseCategory::TYPE_PUBLIC, SWIFT_KnowledgebaseCategory::TYPE_INHERIT), SWIFT::Get('usergroupid'));

            $this->Template->Assign('_parentCategoryList', $_parentCategoryList);

            if ($_SWIFT_KnowledgebaseCategoryObject->GetProperty('uservisibilitycustom') == '1')
            {
                $_filterKnowledgebaseCategoryIDList = SWIFT_UserGroupAssign::RetrieveListOnUserGroup(SWIFT::Get('usergroupid'), SWIFT_UserGroupAssign::TYPE_KBCATEGORY);

                if (!in_array($_SWIFT_KnowledgebaseCategoryObject->GetKnowledgebaseCategoryID(), $_filterKnowledgebaseCategoryIDList))
                {
                    return false;
                }
            }
        }

        $_knowledgebaseMainCategoryContainer = SWIFT_KnowledgebaseCategory::Retrieve(array(SWIFT_KnowledgebaseCategory::TYPE_GLOBAL, SWIFT_KnowledgebaseCategory::TYPE_PUBLIC,
                SWIFT_KnowledgebaseCategory::TYPE_INHERIT), $_parentCategoryID, 0, SWIFT::Get('usergroupid'));

        $_knowledgebaseCategoryContainer = $_knowledgebaseMainCategoryContainer[0];
        $_dispatchFilterKnowledgebaseCategoryIDList = $_knowledgebaseMainCategoryContainer[1];

        $_tdWidth = round(100/$this->Settings->Get('kb_categorycolumns'));

        $_index = 1;
        foreach ($_knowledgebaseCategoryContainer as $_knowledgebaseCategoryID => $_knowledgebaseCategory)
        {
            $_knowledgebaseCategoryContainer[$_knowledgebaseCategoryID]['tdwidth'] = $_tdWidth;

            // @codeCoverageIgnoreStart
            // This code will never be executed because title is always set
            if (!isset($_knowledgebaseCategory['title']))
            {
                $_knowledgebaseCategoryContainer[$_knowledgebaseCategoryID]['title'] = false;
            }
            // @codeCoverageIgnoreEnd

            if ($_index > $this->Settings->Get('kb_categorycolumns'))
            {
                $_index = 1;
                $_knowledgebaseCategoryContainer[$_knowledgebaseCategoryID]['jumptorow'] = true;
            } else {
                $_knowledgebaseCategoryContainer[$_knowledgebaseCategoryID]['jumptorow'] = false;
            }

            $_index++;
        }

        if ($_index <= $this->Settings->Get('kb_categorycolumns'))
        {
            $_difference = $this->Settings->Get('kb_categorycolumns') - ($_index - 1);
            for ($ii = 0; $ii < $_difference; $ii++) {
                $_knowledgebaseCategoryContainer[] = array('title' => false);
            }
        }

        $_knowledgebaseArticleContainer = SWIFT_KnowledgebaseArticle::Retrieve(array($_parentCategoryID));

        $this->Template->Assign('_knowledgebaseCategoryListContainer', $_knowledgebaseCategoryContainer);
        $this->Template->Assign('_knowledgebaseCategoryCount', count($_knowledgebaseCategoryContainer));
        $this->Template->Assign('_knowledgebaseCategoryID', ($_parentCategoryID));

        $this->Template->Assign('_knowledgebaseArticleContainer', $_knowledgebaseArticleContainer);
        $this->Template->Assign('_knowledgebaseArticleCount', count($_knowledgebaseArticleContainer));

        $_knowledgebaseArticleContainer_Popular = $_knowledgebaseArticleContainer_Recent = array();
        if (empty($_parentCategoryID) && $this->Settings->Get('kb_enpopulararticles') == '1')
        {
            $_knowledgebaseArticleContainer_Popular = SWIFT_KnowledgebaseArticle::RetrieveFilter(SWIFT_KnowledgebaseArticle::FILTER_POPULAR, $_dispatchFilterKnowledgebaseCategoryIDList, 0, SWIFT::Get('usergroupid'));
        }

        if (empty($_parentCategoryID) && $this->Settings->Get('kb_enlatestarticles') == '1')
        {
            $_knowledgebaseArticleContainer_Recent = SWIFT_KnowledgebaseArticle::RetrieveFilter(SWIFT_KnowledgebaseArticle::FILTER_RECENT, $_dispatchFilterKnowledgebaseCategoryIDList, 0, SWIFT::Get('usergroupid'));
        }

        $_showEmptyViewWarning = $_hasNoCategories = false;
        if (count($_knowledgebaseCategoryContainer) == 0 || (isset($_knowledgebaseCategoryContainer[0]['title']) && isset($_knowledgebaseCategoryContainer[1]['title']) && isset($_knowledgebaseCategoryContainer[2]['title']) &&
                $_knowledgebaseCategoryContainer[0]['title'] == false && $_knowledgebaseCategoryContainer[1]['title'] == false && $_knowledgebaseCategoryContainer[2]['title'] == false))
        {
            $_hasNoCategories = true;
        }

        if ($_hasNoCategories && count($_knowledgebaseArticleContainer) == 0)
        {
            $_showEmptyViewWarning = true;
        }

        $this->Template->Assign('_showEmptyViewWarning', $_showEmptyViewWarning);
        $this->Template->Assign('_hasNoCategories', $_hasNoCategories);

        $this->Template->Assign('_knowledgebaseArticleContainer_Popular', $_knowledgebaseArticleContainer_Popular);
        $this->Template->Assign('_knowledgebaseArticleContainer_Recent', $_knowledgebaseArticleContainer_Recent);

        $this->Template->Assign('_pageTitle', $this->Language->Get('knowledgebase'));

        $this->UserInterface->Header('knowledgebase');
        $this->Template->Render('knowledgebaselist');
        $this->UserInterface->Footer();

        return true;
    }
}
