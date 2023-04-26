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

namespace Tickets\Library\Macro;

use SWIFT;
use SWIFT_Exception;
use SWIFT_Library;
use Tickets\Models\Macro\SWIFT_MacroCategory;
use Tickets\Models\Macro\SWIFT_MacroReply;

/**
 * The Macro Manager. Deals with search, menu generation etc.
 *
 * @author Varun Shoor
 */
class SWIFT_MacroManager extends SWIFT_Library
{
    static protected $_macroCategoryCache = false;
    static protected $_macroRepliesCache = false;

    /**
     * Retrieve the Macro Category Options
     *
     * @author Varun Shoor
     * @param int|bool $_selectedMacroCategoryID (OPTIONAL) The Macro Category ID to be selected by default
     * @return mixed "$_optionContainer" (ARRAY) on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function GetMacroCategoryOptions($_selectedMacroCategoryID = false, $_activeMacroCategoryID = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!static::$_macroCategoryCache)
        {
            static::$_macroCategoryCache = SWIFT_MacroCategory::RetrieveMacroCategories();
        }

        $_macroCategoryCache = static::$_macroCategoryCache;

        $_optionContainer = array();

        $_optionContainer[0]['title'] = $_SWIFT->Language->Get('parentcategoryitem');
        $_optionContainer[0]['value'] = '0';
        $_optionContainer[0]['selected'] = IIF($_selectedMacroCategoryID == 0, true, false);

        $_optionContainer = static::GetSubMacroCategoryOptions($_selectedMacroCategoryID, 0, $_optionContainer, 0, $_activeMacroCategoryID);

        return $_optionContainer;
    }

    /**
     * Retrieve the macro category options in a loop
     *
     * @author Varun Shoor
     * @param int $_selectedMacroCategoryID The Selected Macro Category ID
     * @param int $_parentMacroCategoryID The Parent Macro Category ID
     * @param array $_optionContainer The Option Container
     * @param int $_indent
     * @param bool $_activeMacroCategoryID
     * @return mixed "_optionContainer" (ARRAY) on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    protected static function GetSubMacroCategoryOptions($_selectedMacroCategoryID, $_parentMacroCategoryID, $_optionContainer, $_indent = 0, $_activeMacroCategoryID = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_macroCategoryCache = static::$_macroCategoryCache;

        // will be overwritten by extract
        $_macroCategoryParentMap[$_parentMacroCategoryID] = [];

        extract($_macroCategoryCache, EXTR_OVERWRITE);

        if (isset($_macroCategoryParentMap[$_parentMacroCategoryID]) && is_array($_macroCategoryParentMap[$_parentMacroCategoryID]) && !empty($_macroCategoryParentMap[$_parentMacroCategoryID]))
        {
            $_childIndent = str_repeat('   ', $_indent);
            $_childPrefix = $_childIndent . '|- ';

            foreach ($_macroCategoryParentMap[$_parentMacroCategoryID] as $_key => $_val)
            {
                if ($_activeMacroCategoryID == $_val['macrocategoryid'])
                {
                    continue;
                }

                if ($_val['categorytype'] == SWIFT_MacroCategory::TYPE_PUBLIC || ($_val['categorytype'] == SWIFT_MacroCategory::TYPE_PRIVATE && $_val['staffid'] == $_SWIFT->Staff->GetStaffID()))
                {
                    $_optionArray = array();
                    $_optionArray['title'] = $_childPrefix . $_val['title'];
                    $_optionArray['value'] = $_val['macrocategoryid'];
                    $_optionArray['selected'] = IIF($_selectedMacroCategoryID == $_val['macrocategoryid'], true, false);

                    $_optionContainer[] = $_optionArray;

                    if (isset($_macroCategoryParentMap[$_val['macrocategoryid']]) && count($_macroCategoryParentMap[$_val['macrocategoryid']]) > 0)
                    {
                        $_optionContainer = static::GetSubMacroCategoryOptions($_selectedMacroCategoryID, $_val['macrocategoryid'], $_optionContainer, ($_indent + 1), $_activeMacroCategoryID);
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
     * @param int $_macroCategoryID The Macro Category ID
     * @return string The Rendered Item HTML
     * @throws SWIFT_Exception
     */
    protected static function GetTreeItemOptions($_macroCategoryID)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_returnHTML = '&nbsp;&nbsp;-&nbsp;&nbsp;' . '<div title="' . $_SWIFT->Language->Get('insertcategory') . '" class="macrocategorynew" onclick="javascript: InsertMacroCategoryWindow(\'' .  ($_macroCategoryID) . '\');">&nbsp;</div> <div title="' . $_SWIFT->Language->Get('insertmacro') . '" class="macroreplynew" onclick="javascript: InsertMacroReplyWindow(\'' .  ($_macroCategoryID) . '\');">&nbsp;</div> <div title="' . $_SWIFT->Language->Get('filterreplies') . '" class="macroreplysearch" onclick="javascript: loadViewportData(\'' . SWIFT::Get('basename') . '/Tickets/MacroCategory/QuickReplyFilter/category/' .  ($_macroCategoryID) . '/\');">&nbsp;</div> ';

        return $_returnHTML;
    }

    /**
     * Retrieve the Macro Category Tree HTML
     *
     * @author Varun Shoor
     * @param int|bool $_selectedMacroCategoryID (OPTIONAL) The Macro Category ID to be selected by default
     * @return mixed "_renderHTML" (STRING) on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function GetMacroCategoryTree($_selectedMacroCategoryID = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!static::$_macroCategoryCache)
        {
            static::$_macroCategoryCache = SWIFT_MacroCategory::RetrieveMacroCategories();
        }

        if (!static::$_macroRepliesCache)
        {
            static::$_macroRepliesCache = SWIFT_MacroReply::RetrieveMacroReplies(false);
        }

        $_macroCategoryCache = static::$_macroCategoryCache;
        $_macroRepliesCache = static::$_macroRepliesCache;

        // will be overwritten by extract
        $_replyParentMap[0] = [];

        extract($_macroRepliesCache, EXTR_OVERWRITE);

        $_extendedMacroCategoryTitle = '';
        if (isset($_replyParentMap[0]) && count($_replyParentMap[0]) > 0)
        {
            $_extendedMacroCategoryTitle .= ' <font color="red">(' . count($_replyParentMap[0]) . ')</font>';
        }

        $_renderHTML = '<ul class="swifttree">';

        $_renderHTML .= '<li><span class="folder">&nbsp;&nbsp;&nbsp;' . $_SWIFT->Language->Get('rootcategory') . $_extendedMacroCategoryTitle . static::GetTreeItemOptions(0) . '</span>';

        $_renderHTML = static::GetSubMacroCategoryTree($_selectedMacroCategoryID, 0, $_renderHTML);

        $_renderHTML .= '</li></ul>';

        return $_renderHTML;
    }

    /**
     * Retrieve the macro category tree HTML in a loop
     *
     * @author Varun Shoor
     * @param int $_selectedMacroCategoryID The Selected Macro Category ID
     * @param int $_parentMacroCategoryID The Parent Macro Category ID
     * @param string $_renderHTML The Rendered HTML
     * @return mixed "_renderHTML" (STRING) on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    protected static function GetSubMacroCategoryTree($_selectedMacroCategoryID, $_parentMacroCategoryID, $_renderHTML)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_macroCategoryCache = static::$_macroCategoryCache;
        $_macroRepliesCache = static::$_macroRepliesCache;

        // will be overwritten by extract
        $_macroCategoryParentMap[$_parentMacroCategoryID] = [];
        $_replyParentMap[0] = [];

        extract($_macroCategoryCache, EXTR_OVERWRITE);
        extract($_macroRepliesCache, EXTR_OVERWRITE);

        if (isset($_macroCategoryParentMap[$_parentMacroCategoryID]) && is_array($_macroCategoryParentMap[$_parentMacroCategoryID]) && !empty($_macroCategoryParentMap[$_parentMacroCategoryID]))
        {
            $_renderHTML .= '<ul>';

            foreach ($_macroCategoryParentMap[$_parentMacroCategoryID] as $_key => $_val)
            {
                if ($_val['categorytype'] == SWIFT_MacroCategory::TYPE_PUBLIC || ($_val['categorytype'] == SWIFT_MacroCategory::TYPE_PRIVATE && $_val['staffid'] == $_SWIFT->Staff->GetStaffID()))
                {
                    if ($_selectedMacroCategoryID == $_val['macrocategoryid'])
                    {
                        $_macroCategoryTitle = '<b>' . htmlspecialchars($_val['title']) . '</b>';
                    } else {
                        $_macroCategoryTitle = htmlspecialchars($_val['title']);
                    }

                    if (isset($_replyParentMap[$_val['macrocategoryid']]) && count($_replyParentMap[$_val['macrocategoryid']]) > 0)
                    {
                        $_macroCategoryTitle .= ' <font color="red">(' . count($_replyParentMap[$_val['macrocategoryid']]) . ')</font>';
                    }

                    $_renderHTML .= '<li><span class="' . IIF($_val['categorytype'] == SWIFT_MacroCategory::TYPE_PUBLIC, 'folder', 'folderfaded') . '"><a href="javascript: void(0);" onclick="javascript: EditMacroCategoryWindow(\'' . (int) ($_val['macrocategoryid']) . '\');">' . $_macroCategoryTitle . '</a>' . static::GetTreeItemOptions($_val['macrocategoryid']) . '</span>';

                    if (isset($_macroCategoryParentMap[$_val['macrocategoryid']]) && count($_macroCategoryParentMap[$_val['macrocategoryid']]) > 0)
                    {
                        $_renderHTML = static::GetSubMacroCategoryTree($_selectedMacroCategoryID, $_val['macrocategoryid'], $_renderHTML);
                    }

                    $_renderHTML .= '</li>';
                }
            }

            $_renderHTML .= '</ul>';
        }

        return $_renderHTML;
    }
}
