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

namespace Knowledgebase\Library\Search;

use Knowledgebase\Models\Article\SWIFT_KnowledgebaseArticle;
use SWIFT;
use SWIFT_Library;

/**
 * The Knowledgebase Search Manager Class
 *
 * @author Varun Shoor
 */
class SWIFT_KnowledgebaseSearch extends SWIFT_Library {
    /**
     * Search for given query under the provided user group
     *
     * @author Varun Shoor
     * @param string $_searchQuery The Search Query
     * @param int $_userGroupID The User Group ID
     * @return array The Search Results Container
     * @throws \SWIFT_Exception
     */
    public static function Search($_searchQuery, $_userGroupID) {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_searchQuery) || empty($_userGroupID)) {
            return array();
        }

        $_searchResultContainer = array();

        $_knowledgebaseArticleContainer = SWIFT_KnowledgebaseArticle::RetrieveFullText($_searchQuery, SWIFT::Get('usergroupid'));
        if (_is_array($_knowledgebaseArticleContainer)) {
            foreach ($_knowledgebaseArticleContainer as $_knowledgebaseArticle) {
                $_searchResultContainer[$_knowledgebaseArticle['kbarticleid']] = $_knowledgebaseArticle;
                $_searchResultContainer[$_knowledgebaseArticle['kbarticleid']]['cssprefix'] = 'kbsearch';
                $_urlId = empty($_knowledgebaseArticle['seosubject']) ? $_knowledgebaseArticle['kbarticleid'] : $_knowledgebaseArticle['seosubject'];
                $_searchResultContainer[$_knowledgebaseArticle['kbarticleid']]['url'] = SWIFT::Get('basename') . $_SWIFT->Template->GetTemplateGroupPrefix() . '/Knowledgebase/Article/View/' . $_urlId;
            }
        }

        return $_searchResultContainer;
    }
}
