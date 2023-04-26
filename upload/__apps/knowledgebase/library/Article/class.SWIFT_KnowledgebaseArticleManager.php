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

namespace Knowledgebase\Library\Article;

use Knowledgebase\Models\Article\SWIFT_KnowledgebaseArticle;
use Knowledgebase\Models\Article\SWIFT_KnowledgebaseArticleLink;
use Knowledgebase\Models\Category\SWIFT_KnowledgebaseCategory;
use SWIFT;
use SWIFT_DataID;
use SWIFT_DataStore;
use SWIFT_Exception;
use SWIFT_Library;
use Base\Models\Staff\SWIFT_StaffGroupLink;
use Base\Models\User\SWIFT_UserGroupAssign;

/**
 * The Knowledgebase Article Manager Library
 *
 * @author Varun Shoor
 */
class SWIFT_KnowledgebaseArticleManager extends SWIFT_Library
{
    /**
     * Retrieve the Knowledgebase Article by SEO subject
     *
     * @author Werner Garcia
     * @param string $_seoSubject The Knowledgebase Article ID
     * @return array|bool array(SWIFT_KnowledgebaseArticle, SWIFT_KnowledgebaseCategory)
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public function RetrieveBySeoSubject($_seoSubject)
    {
        $_SWIFT = SWIFT::GetInstance();
        $_SWIFT->Database->Query(sprintf("SELECT DISTINCT a.kbarticleid, l.linktypeid FROM " . TABLE_PREFIX . "kbarticles a left join " . TABLE_PREFIX . "kbarticlelinks l using (kbarticleid) WHERE a.seosubject = '%s' LIMIT 1", $_SWIFT->Database->Escape($_seoSubject)));
        if ($_SWIFT->Database->NextRecord())
        {
            $kbarticleid = $_SWIFT->Database->Record['kbarticleid'];
            $kbcategoryid = $_SWIFT->Database->Record['linktypeid'];
            return $this->RetrieveForUser($kbarticleid, $kbcategoryid);
        }

        return false;
    }

    /**
     * Retrieve the Knowledgebase Article for User
     *
     * @author Varun Shoor
     * @param int $_knowledgebaseArticleID The Knowledgebase Article ID
     * @param int|bool $_knowledgebaseCategoryID (OPTIONAL) The Knowledgebase Category ID
     * @return array|bool array(SWIFT_KnowledgebaseArticle, SWIFT_KnowledgebaseCategory)
     * @throws SWIFT_Exception If Invalid Data is Provided
     * @throws \Knowledgebase\Models\Article\SWIFT_Article_Exception
     * @throws \Knowledgebase\Models\Category\SWIFT_Category_Exception
     */
    public function RetrieveForUser($_knowledgebaseArticleID, $_knowledgebaseCategoryID = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_knowledgebaseCategoryID = ($_knowledgebaseCategoryID);
        $_knowledgebaseArticleID = ($_knowledgebaseArticleID);

        $_SWIFT_KnowledgebaseArticleObject = false;
        try
        {
            $_SWIFT_KnowledgebaseArticleObject = new SWIFT_KnowledgebaseArticle(new SWIFT_DataID($_knowledgebaseArticleID));
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {

            return false;
        }

        $_filterKnowledgebaseCategoryIDList = SWIFT_UserGroupAssign::RetrieveListOnUserGroup(SWIFT::Get('usergroupid'), SWIFT_UserGroupAssign::TYPE_KBCATEGORY);

        // Article Sanity Check
        if (!$_SWIFT_KnowledgebaseArticleObject instanceof SWIFT_KnowledgebaseArticle || !$_SWIFT_KnowledgebaseArticleObject->GetIsClassLoaded() ||
                $_SWIFT_KnowledgebaseArticleObject->GetProperty('articlestatus') != SWIFT_KnowledgebaseArticle::STATUS_PUBLISHED)
        {
            return false;
        }

        // Now we need to fetch all knowledgebase categories linked with this article

        // First attempt to fetch the linked knowledgebase category
        $_SWIFT_KnowledgebaseCategoryObject_Incoming = false;
        if (!empty($_knowledgebaseCategoryID))
        {
            try
            {
                $_SWIFT_KnowledgebaseCategoryObject_Incoming = new SWIFT_KnowledgebaseCategory(new SWIFT_DataID($_knowledgebaseCategoryID));
            } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            }
        }

        $_continueArticle = false;

        $_knowledgebaseCategoryObjectContainer = array();
        // Attempt to fetch all linked category objects
        $_knowledgebaseCategoryIDList = SWIFT_KnowledgebaseArticleLink::RetrieveLinkIDListOnArticle($_knowledgebaseArticleID, SWIFT_KnowledgebaseArticleLink::LINKTYPE_CATEGORY);

        if (count($_knowledgebaseCategoryIDList))
        {
            $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "kbcategories WHERE kbcategoryid IN (" . BuildIN($_knowledgebaseCategoryIDList) . ")");
            while ($_SWIFT->Database->NextRecord())
            {
                $_knowledgebaseCategoryObjectContainer[$_SWIFT->Database->Record['kbcategoryid']] = new SWIFT_KnowledgebaseCategory(new SWIFT_DataStore($_SWIFT->Database->Record));
            }
        }

        // Sanity check for incoming knowledgebase category id
        if ($_SWIFT_KnowledgebaseCategoryObject_Incoming instanceof SWIFT_KnowledgebaseCategory && $_SWIFT_KnowledgebaseCategoryObject_Incoming->GetIsClassLoaded() &&
                !in_array($_SWIFT_KnowledgebaseCategoryObject_Incoming->GetKnowledgebaseCategoryID(), $_knowledgebaseCategoryIDList))
        {
            $_SWIFT_KnowledgebaseCategoryObject_Incoming = false;
        }

        $_allowComments = $_SWIFT_KnowledgebaseArticleObject->GetProperty('allowcomments');
        $_allowRating = true;

        // Always show if the article belongs to the parent category
        if (in_array('0', $_knowledgebaseCategoryIDList) && empty($_knowledgebaseCategoryObjectContainer))
        {
            $_continueArticle = true;

        // So now we have a list of categories, we itterate through each and try to find one that would fit the permission requirements
        } else {
            foreach ($_knowledgebaseCategoryObjectContainer as $_knowledgebaseCategoryID => $_SWIFT_KnowledgebaseCategoryObject)
            {
                // If any of the category disables comments, then disable it for the article
                if ($_SWIFT_KnowledgebaseCategoryObject->GetProperty('allowcomments') == '0')
                {
                    $_allowComments = '0';
                }

                // If any of the category disables rating, then disable it for the article
                if ($_SWIFT_KnowledgebaseCategoryObject->GetProperty('allowrating') == '0')
                {
                    $_allowRating = '0';
                }

                // Global & Public Category types are simple to work on..
                $isVisible = $_SWIFT_KnowledgebaseCategoryObject->GetProperty('uservisibilitycustom') == '0' ||
                    ($_SWIFT_KnowledgebaseCategoryObject->GetProperty('uservisibilitycustom') == '1' && in_array($_SWIFT_KnowledgebaseCategoryObject->GetKnowledgebaseCategoryID(),
                            $_filterKnowledgebaseCategoryIDList));
                if (($_SWIFT_KnowledgebaseCategoryObject->GetProperty('categorytype') == SWIFT_KnowledgebaseCategory::TYPE_GLOBAL || $_SWIFT_KnowledgebaseCategoryObject->GetProperty('categorytype') == SWIFT_KnowledgebaseCategory::TYPE_PUBLIC) &&
                        ($isVisible))
                {
                    $_continueArticle = true;

                // Its the inherited categories that need some work
                } else if (($_SWIFT_KnowledgebaseCategoryObject->GetProperty('categorytype') == SWIFT_KnowledgebaseCategory::TYPE_INHERIT && $_SWIFT_KnowledgebaseCategoryObject->IsParentCategoryOfType(array(SWIFT_KnowledgebaseCategory::TYPE_GLOBAL, SWIFT_KnowledgebaseCategory::TYPE_PUBLIC))) &&
                    $isVisible) {
                    $_continueArticle = true;
                }
            }
        }

        if (!$_continueArticle)
        {
            return false;
        }

        return array($_SWIFT_KnowledgebaseArticleObject, $_SWIFT_KnowledgebaseCategoryObject_Incoming, $_allowComments, $_allowRating);
    }

    /**
     * Retrieve the Knowledgebase Article for Staff
     *
     * @author Varun Shoor
     * @param int $_knowledgebaseArticleID The Knowledgebase Article ID
     * @param int $_knowledgebaseCategoryID (OPTIONAL) The Knowledgebase Category ID
     * @return array|bool array(SWIFT_KnowledgebaseArticle, SWIFT_KnowledgebaseCategory)
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public function RetrieveForStaff($_knowledgebaseArticleID, $_knowledgebaseCategoryID = null)
    {
        $_SWIFT = SWIFT::GetInstance();

        try
        {
            $_SWIFT_KnowledgebaseArticleObject = new SWIFT_KnowledgebaseArticle(new SWIFT_DataID($_knowledgebaseArticleID));
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            return false;
        }

        $_filterKnowledgebaseCategoryIDList = SWIFT_StaffGroupLink::RetrieveListFromCacheOnStaffGroup(SWIFT_StaffGroupLink::TYPE_KBCATEGORY, $_SWIFT->Staff->GetProperty('staffgroupid'));

        // Article Sanity Check
        if (!$_SWIFT_KnowledgebaseArticleObject instanceof SWIFT_KnowledgebaseArticle || !$_SWIFT_KnowledgebaseArticleObject->GetIsClassLoaded() ||
                $_SWIFT_KnowledgebaseArticleObject->GetProperty('articlestatus') != SWIFT_KnowledgebaseArticle::STATUS_PUBLISHED)
        {
            return false;
        }

        // Now we need to fetch all knowledgebase categories linked with this article

        // First attempt to fetch the linked knowledgebase category
        $_SWIFT_KnowledgebaseCategoryObject_Incoming = false;
        if (!empty($_knowledgebaseCategoryID))
        {
            try
            {
                $_SWIFT_KnowledgebaseCategoryObject_Incoming = new SWIFT_KnowledgebaseCategory(new SWIFT_DataID($_knowledgebaseCategoryID));
            } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            }
        }

        $_continueArticle = false;

        $_knowledgebaseCategoryObjectContainer = array();
        // Attempt to fetch all linked category objects
        $_knowledgebaseCategoryIDList = SWIFT_KnowledgebaseArticleLink::RetrieveLinkIDListOnArticle($_knowledgebaseArticleID, SWIFT_KnowledgebaseArticleLink::LINKTYPE_CATEGORY);

        if (count($_knowledgebaseCategoryIDList))
        {
            $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "kbcategories WHERE kbcategoryid IN (" . BuildIN($_knowledgebaseCategoryIDList) . ")");
            while ($_SWIFT->Database->NextRecord())
            {
                $_knowledgebaseCategoryObjectContainer[$_SWIFT->Database->Record['kbcategoryid']] = new SWIFT_KnowledgebaseCategory(new SWIFT_DataStore($_SWIFT->Database->Record));
            }
        }

        // Sanity check for incoming knowledgebase category id
        if ($_SWIFT_KnowledgebaseCategoryObject_Incoming instanceof SWIFT_KnowledgebaseCategory && $_SWIFT_KnowledgebaseCategoryObject_Incoming->GetIsClassLoaded() &&
                !in_array($_SWIFT_KnowledgebaseCategoryObject_Incoming->GetKnowledgebaseCategoryID(), $_knowledgebaseCategoryIDList))
        {
            $_SWIFT_KnowledgebaseCategoryObject_Incoming = false;
        }

        $_allowComments = $_SWIFT_KnowledgebaseArticleObject->GetProperty('allowcomments');
        $_allowRating = true;

        // Always show if the article belongs to the parent category
        if (in_array('0', $_knowledgebaseCategoryIDList) && empty($_knowledgebaseCategoryObjectContainer))
        {
            $_continueArticle = true;

        // So now we have a list of categories, we itterate through each and try to find one that would fit the permission requirements
        } else {
            foreach ($_knowledgebaseCategoryObjectContainer as $_knowledgebaseCategoryID => $_SWIFT_KnowledgebaseCategoryObject)
            {
                // If any of the category disables comments, then disable it for the article
                if ($_SWIFT_KnowledgebaseCategoryObject->GetProperty('allowcomments') == '0')
                {
                    $_allowComments = '0';
                }

                // If any of the category disables rating, then disable it for the article
                if ($_SWIFT_KnowledgebaseCategoryObject->GetProperty('allowrating') == '0')
                {
                    $_allowRating = '0';
                }

                // Global & Private Category types are simple to work on..
                $isVisible = $_SWIFT_KnowledgebaseCategoryObject->GetProperty('staffvisibilitycustom') == '0' ||
                    ($_SWIFT_KnowledgebaseCategoryObject->GetProperty('staffvisibilitycustom') == '1' && in_array($_SWIFT_KnowledgebaseCategoryObject->GetKnowledgebaseCategoryID(),
                            $_filterKnowledgebaseCategoryIDList));
                if (($_SWIFT_KnowledgebaseCategoryObject->GetProperty('categorytype') == SWIFT_KnowledgebaseCategory::TYPE_GLOBAL || $_SWIFT_KnowledgebaseCategoryObject->GetProperty('categorytype') == SWIFT_KnowledgebaseCategory::TYPE_PRIVATE) &&
                        ($isVisible))
                {
                    $_continueArticle = true;

                // Its the inherited categories that need some work
                } else if (($_SWIFT_KnowledgebaseCategoryObject->GetProperty('categorytype') == SWIFT_KnowledgebaseCategory::TYPE_INHERIT && $_SWIFT_KnowledgebaseCategoryObject->IsParentCategoryOfType(array(SWIFT_KnowledgebaseCategory::TYPE_GLOBAL, SWIFT_KnowledgebaseCategory::TYPE_PRIVATE))) &&
                    $isVisible) {
                    $_continueArticle = true;
                }
            }
        }

        if (!$_continueArticle)
        {
            return false;
        }

        return array($_SWIFT_KnowledgebaseArticleObject, $_SWIFT_KnowledgebaseCategoryObject_Incoming, $_allowComments, $_allowRating);
    }
}
