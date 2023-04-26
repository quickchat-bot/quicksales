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

namespace Knowledgebase\Library\Category;

use Knowledgebase\Models\Category\SWIFT_KnowledgebaseCategory;
use SWIFT;
use SWIFT_Exception;
use SWIFT_Library;
use Base\Library\Staff\SWIFT_Staff_Exception;

/**
 * The Knowledgebase Category Manager Class
 *
 * @author Varun Shoor
 */
class SWIFT_KnowledgebaseCategoryManager extends SWIFT_Library
{
    static protected $_knowledgebaseCategoryCache = false;

    /**
     * Retrieve the Knowledgebase Category Options
     *
     * @author Varun Shoor
     * @param bool|array $_selectedKnowledgebaseCategoryIDList
     * @param bool $_activeKnowledgebaseCategoryID (OPTIONAL) The Active Knowledgebase Category ID (during Edit)
     * @param bool $_isCheckbox (OPTIONAL) Whether this is a checkbox container
     * @return mixed "$_optionContainer" (ARRAY) on Success, "false" otherwise
     * @throws SWIFT_Exception
     * @throws SWIFT_Staff_Exception
     */
    public static function GetCategoryOptions($_selectedKnowledgebaseCategoryIDList = false, $_activeKnowledgebaseCategoryID = false, $_isCheckbox = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!static::$_knowledgebaseCategoryCache)
        {
            static::$_knowledgebaseCategoryCache = SWIFT_KnowledgebaseCategory::RetrieveCategories();
        }

        $_knowledgebaseCategoryCache = static::$_knowledgebaseCategoryCache;

        $_optionContainer = array();

        $_optionContainer[0]['title'] = $_SWIFT->Language->Get('parentcategoryitem');
        $_optionContainer[0]['value'] = '0';

        $_isSelected = false;
        if (_is_array($_selectedKnowledgebaseCategoryIDList) && in_array(0, $_selectedKnowledgebaseCategoryIDList))
        {
            $_isSelected = true;
        }

        if ($_isCheckbox)
        {
            $_optionContainer[0]['checked'] = $_isSelected;
            $_optionContainer[0]['icon'] = SWIFT::Get('themepathimages'). 'icon_folderyellow3.gif';
        } else {
            $_optionContainer[0]['selected'] = $_isSelected;
        }

        static::GetSubCategoryOptions($_selectedKnowledgebaseCategoryIDList, 0, $_optionContainer, 0, $_activeKnowledgebaseCategoryID, $_isCheckbox);

        return $_optionContainer;
    }

    /**
     * Retrieve the knowledgebase category options in a loop
     *
     * @author Varun Shoor
     * @param array $_selectedKnowledgebaseCategoryIDList The Selected Knowledgebase Category ID List
     * @param int $_parentKnowledgebaseCategoryID The Parent Knowledgebase Category ID
     * @param array $_optionContainer The Option Container
     * @param int $_indent The Indent Counter
     * @param bool|int $_activeKnowledgebaseCategoryID
     * @param bool $_isCheckbox
     * @return mixed "_optionContainer" (ARRAY) on Success, "false" otherwise
     */
    protected static function GetSubCategoryOptions($_selectedKnowledgebaseCategoryIDList, $_parentKnowledgebaseCategoryID, &$_optionContainer, $_indent = 0,
            $_activeKnowledgebaseCategoryID = false, $_isCheckbox = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_knowledgebaseCategoryCache = static::$_knowledgebaseCategoryCache;

        $_knowledgebaseParentMap = array();

        extract($_knowledgebaseCategoryCache);

        $_childCount = 0;

        if (isset($_knowledgebaseParentMap[$_parentKnowledgebaseCategoryID]))
        {
            $_childCount = count($_knowledgebaseParentMap[$_parentKnowledgebaseCategoryID]);

            if ($_childCount > 0)
            {
                $_childIndent = str_repeat('   ', $_indent);

                $_childPrefix = $_childIndent . '|- ';

                foreach ($_knowledgebaseParentMap[$_parentKnowledgebaseCategoryID] as $_val)
                {
                    if ($_activeKnowledgebaseCategoryID == $_val['kbcategoryid'])
                    {
                        continue;
                    }

                    $_optionArray = array();
                    $_optionArray['title'] = $_childPrefix . $_val['title'];
                    $_optionArray['value'] = $_val['kbcategoryid'];


                    $_isSelected = false;
                    if (_is_array($_selectedKnowledgebaseCategoryIDList) && in_array($_val['kbcategoryid'], $_selectedKnowledgebaseCategoryIDList))
                    {
                        $_isSelected = true;
                    }

                    if ($_isCheckbox)
                    {
                        $_optionArray['checked'] = $_isSelected;
                        $_optionArray['icon'] = SWIFT::Get('themepathimages'). 'icon_folderyellow3.gif';
                    } else {
                        $_optionArray['selected'] = $_isSelected;
                    }

                    $_optionContainer[] = $_optionArray;

                    if (isset($_knowledgebaseParentMap[$_val['kbcategoryid']]) && count($_knowledgebaseParentMap[$_val['kbcategoryid']]) > 0)
                    {
                        static::GetSubCategoryOptions($_selectedKnowledgebaseCategoryIDList, $_val['kbcategoryid'], $_optionContainer, ($_indent + 1), $_activeKnowledgebaseCategoryID, $_isCheckbox);
                    }
                }
            }
        }

        return $_optionContainer;
    }

    /**
     * Render the options for the given category id
     *
     * @author Varun Shoor
     * @param int $_knowledgebaseCategoryID The Knowledgebase Category ID
     * @return string The Rendered Item HTML
     * @throws SWIFT_Exception
     */
    protected static function GetTreeItemOptions($_knowledgebaseCategoryID)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_returnHTML = '&nbsp;&nbsp;-&nbsp;&nbsp;' . '<div title="' . $_SWIFT->Language->Get('insertcategory') . '" class="kbcategorynew" onclick="javascript:InsertKnowledgebaseCategoryWindow(\'' . ($_knowledgebaseCategoryID) . '\');">&nbsp;</div> <div title="' . $_SWIFT->Language->Get('insertarticle') . '" class="kbarticlenew" onclick="javascript: loadViewportData(\'/Knowledgebase/Article/Insert/' . ($_knowledgebaseCategoryID) . '\');">&nbsp;</div> <div title="' . $_SWIFT->Language->Get('filterarticles') . '" class="kbarticlesearch" onclick="javascript:loadViewportData(\'' . SWIFT::Get('basename') . '/Knowledgebase/Article/QuickFilter/category/' . ($_knowledgebaseCategoryID) . '/\');">&nbsp;</div> ';

        return $_returnHTML;
    }

    /**
     * Retrieve the Category Tree HTML
     *
     * @author Varun Shoor
     * @param bool $_selectedKnowledgebaseCategoryID (OPTIONAL) The Knowledgebase Category ID to be selected by default
     * @return mixed "_renderHTML" (STRING) on Success, "false" otherwise
     * @throws SWIFT_Exception
     * @throws SWIFT_Staff_Exception
     */
    public static function GetCategoryTree($_selectedKnowledgebaseCategoryID = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!static::$_knowledgebaseCategoryCache)
        {
            static::$_knowledgebaseCategoryCache = SWIFT_KnowledgebaseCategory::RetrieveCategories();
        }

        $_knowledgebaseCategoryCache = static::$_knowledgebaseCategoryCache;

        $_extendedKnowledgebaseCategoryTitle = '';

        $_renderHTML = '<ul class="swifttree">';

        $_renderHTML .= '<li><span class="folder">' . $_SWIFT->Language->Get('rootcategory') . $_extendedKnowledgebaseCategoryTitle . static::GetTreeItemOptions(0) . '</span>';

        static::GetSubKnowledgebaseCategoryTree($_selectedKnowledgebaseCategoryID, 0, $_renderHTML);

        $_renderHTML .= '</li></ul>';

        return $_renderHTML;
    }

    /**
     * Retrieve the knowledgebase category tree HTML in a loop
     *
     * @author Varun Shoor
     * @param int $_selectedKnowledgebaseCategoryID The Selected Knowledgebase Category ID
     * @param int $_parentKnowledgebaseCategoryID The Parent Knowledgebase Category ID
     * @param string $_renderHTML The Rendered HTML
     * @return mixed "_renderHTML" (STRING) on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    protected static function GetSubKnowledgebaseCategoryTree($_selectedKnowledgebaseCategoryID, $_parentKnowledgebaseCategoryID, &$_renderHTML)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_knowledgebaseCategoryCache = static::$_knowledgebaseCategoryCache;

        $_knowledgebaseParentMap[$_parentKnowledgebaseCategoryID] = [];
        extract($_knowledgebaseCategoryCache, EXTR_OVERWRITE);

        $_childCount = 0;

        if (!empty($_knowledgebaseParentMap[$_parentKnowledgebaseCategoryID])) {
            $_childCount = count($_knowledgebaseParentMap[$_parentKnowledgebaseCategoryID]);

            if ($_childCount > 0) {
                $_renderHTML .= '<ul>';

                foreach ($_knowledgebaseParentMap[$_parentKnowledgebaseCategoryID] as $_key => $_val) {
                    $_knowledgebaseCategoryTitle = htmlspecialchars($_val['title']);
                    if ($_selectedKnowledgebaseCategoryID == $_val['kbcategoryid']) {
                        $_knowledgebaseCategoryTitle = '<b>' . htmlspecialchars($_val['title']) . '</b>';
                    }

                    $_renderHTML .= '<li><span class="folder"><a href="javascript: void(0);" onclick="javascript:EditKnowledgebaseCategoryWindow(\'' . ($_val['kbcategoryid']) . '\');">' . $_knowledgebaseCategoryTitle . '</a>' . static::GetTreeItemOptions($_val['kbcategoryid']) . '</span>';

                    if (isset($_knowledgebaseParentMap[$_val['kbcategoryid']]) && count($_knowledgebaseParentMap[$_val['kbcategoryid']]) > 0) {
                        static::GetSubKnowledgebaseCategoryTree($_selectedKnowledgebaseCategoryID,
                            $_val['kbcategoryid'], $_renderHTML);
                    }

                    $_renderHTML .= '</li>';
                }

                $_renderHTML .= '</ul>';
            }
        }

        return $_renderHTML;
    }
}
