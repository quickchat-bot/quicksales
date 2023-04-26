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

namespace Knowledgebase\Library\Rss;

use Knowledgebase\Models\Article\SWIFT_KnowledgebaseArticle;
use Knowledgebase\Models\Category\SWIFT_KnowledgebaseCategory;
use SWIFT;
use SWIFT_DataID;
use SWIFT_Exception;
use SWIFT_Library;
use Base\Models\User\SWIFT_UserGroupAssign;
use SWIFT_XML;

/**
 * The Knowledgebase RSS Manager
 *
 * @author Varun Shoor
 *
 * @property SWIFT_XML $XML
 */
class SWIFT_KnowledgebaseRSSManager extends SWIFT_Library
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

        $this->Load->Library('XML:XML');

        $this->Language->Load('knowledgebase');
    }

    /**
     * Dispatch the RSS feed to the user
     *
     * @author Varun Shoor
     * @param int $_parentCategoryID The Knowledgebase Category ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Dispatch($_parentCategoryID = 0)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_parentCategoryID = ($_parentCategoryID);


        if (!empty($_parentCategoryID))
        {
            $_SWIFT_KnowledgebaseCategoryObject = false;
            try
            {
                $_SWIFT_KnowledgebaseCategoryObject = new SWIFT_KnowledgebaseCategory(new SWIFT_DataID($_parentCategoryID));
            } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            }

            if (!$_SWIFT_KnowledgebaseCategoryObject instanceof SWIFT_KnowledgebaseCategory || !$_SWIFT_KnowledgebaseCategoryObject->GetIsClassLoaded())
            {
                return false;
            }

            if ($_SWIFT_KnowledgebaseCategoryObject->GetProperty('categorytype') == SWIFT_KnowledgebaseCategory::TYPE_PRIVATE ||
                    ($_SWIFT_KnowledgebaseCategoryObject->GetProperty('categorytype') == SWIFT_KnowledgebaseCategory::TYPE_INHERIT && !$_SWIFT_KnowledgebaseCategoryObject->IsParentCategoryOfType(array(SWIFT_KnowledgebaseCategory::TYPE_GLOBAL, SWIFT_KnowledgebaseCategory::TYPE_PUBLIC)))) {
                return false;
            }

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
        $_knowledgebaseArticleContainer = SWIFT_KnowledgebaseArticle::Retrieve(array($_parentCategoryID));

        @header("Content-Type: text/xml");

        $this->XML->AddParentTag('rss', array('xmlns:content' => 'http://purl.org/rss/1.0/modules/content/', 'xmlns:dc' => 'http://purl.org/dc/elements/1.1/', 'version' => '2.0'));
            $this->XML->AddParentTag('channel');

                $this->XML->AddTag('title', SWIFT::Get('companyname'));
                $this->XML->AddTag('link', SWIFT::Get('swiftpath'));
                $this->XML->AddTag('description', '');
                $this->XML->AddTag('generator', 'Kayako ' . SWIFT_PRODUCT . ' v' . SWIFT_VERSION);

                foreach ($_knowledgebaseArticleContainer as $_knowledgebaseArticle)
                {
                    $this->XML->AddParentTag('item');

                    $this->XML->AddTag('title', $_knowledgebaseArticle['subject']);
                    $this->XML->AddTag('link', SWIFT::Get('swiftpath') . 'index.php?' . $this->Template->GetTemplateGroupPrefix() . '/Knowledgebase/Article/View/' . $_knowledgebaseArticle['seosubject']);
                    $this->XML->AddTag('guid', md5($_knowledgebaseArticle['kbarticleid']), array('isPermaLink' => 'false'));
                    $this->XML->AddTag('pubDate', date('D, d M Y H:i:s O', $_knowledgebaseArticle['dateline']));
                    $this->XML->AddTag('dc:creator', $_knowledgebaseArticle['author']);
                    $this->XML->AddTag('description', StripName(strip_tags_attributes($_knowledgebaseArticle['contents']), $this->Settings->Get('kb_rssclimit')));
                    $this->XML->AddTag('content:encoded', $_knowledgebaseArticle['contents']);

                    $this->XML->EndParentTag('item');
                }

            $this->XML->EndParentTag('channel');
        $this->XML->EndParentTag('rss');

        $this->XML->EchoXML();

        return true;
    }
}
