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

namespace Troubleshooter\Library\Step;

use SWIFT;
use SWIFT_Exception;
use Troubleshooter\Models\Category\SWIFT_TroubleshooterCategory;
use Troubleshooter\Models\Step\SWIFT_TroubleshooterStep;

/**
 * The Troubleshooter Step Manager Class
 *
 * @author Varun Shoor
 */
class SWIFT_TroubleshooterStepManager extends \SWIFT_Library
{
    static protected $_troubleshooterStepCache = false;
    static protected $_troubleshooterStepHits = array();

    /**
     * Retrieve the Troubleshooter Step Options
     *
     * @author Varun Shoor
     * @param int $_troubleshooterCategoryID The Troubleshooter Category ID
     * @param array|bool $_selectedTroubleshooterStepIDList (OPTIONAL) The Troubleshooter Step IDs to be selected by default
     * @param int|bool $_activeTroubleshooterStepID (OPTIONAL) The Active Troubleshooter Step ID (during Edit)
     * @param bool $_isCheckbox (OPTIONAL) Whether this is a checkbox container
     * @return mixed "$_optionContainer" (ARRAY) on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function GetCategoryOptions($_troubleshooterCategoryID, $_selectedTroubleshooterStepIDList = false, $_activeTroubleshooterStepID = false, $_isCheckbox = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!self::$_troubleshooterStepCache)
        {
            self::$_troubleshooterStepCache = SWIFT_TroubleshooterStep::RetrieveSteps($_troubleshooterCategoryID);
        }

        $_troubleshooterStepCache = self::$_troubleshooterStepCache;

        $_optionContainer = array();

        $_optionContainer[0]['title'] = $_SWIFT->Language->Get('parentcategoryitem');
        $_optionContainer[0]['value'] = '0';

        $_isSelected = false;
        if (_is_array($_selectedTroubleshooterStepIDList) && in_array(0, (array) $_selectedTroubleshooterStepIDList))
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

        self::$_troubleshooterStepHits = array();

        self::GetSubCategoryOptions($_troubleshooterCategoryID, $_selectedTroubleshooterStepIDList, 0, $_optionContainer, 0, $_activeTroubleshooterStepID, $_isCheckbox);

        return $_optionContainer;
    }

    /**
     * Retrieve the troubleshooter step options in a loop
     *
     * @author Varun Shoor
     * @param int $_troubleshooterCategoryID The Troubleshooter Category ID
     * @param array|bool $_selectedTroubleshooterStepIDList The Troubleshooter Step IDs to be selected by default
     * @param int $_parentTroubleshooterStepID The Parent Troubleshooter Step ID
     * @param array $_optionContainer The Option Container
     * @param int $_indent The Indent Counter
     * @param int|bool $_activeTroubleshooterStepID (OPTIONAL) The Active Troubleshooter Step ID (during Edit)
     * @param bool $_isCheckbox
     * @return mixed "_optionContainer" (ARRAY) on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    protected static function GetSubCategoryOptions($_troubleshooterCategoryID, $_selectedTroubleshooterStepIDList, $_parentTroubleshooterStepID, &$_optionContainer, $_indent = 0,
            $_activeTroubleshooterStepID = false, $_isCheckbox = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_troubleshooterStepCache = (array) self::$_troubleshooterStepCache;

        $_troubleshooterParentMap = array();

        // will be overwritten by extract
        $_troubleshooterParentMap[$_parentTroubleshooterStepID] = [];

        extract($_troubleshooterStepCache, EXTR_OVERWRITE);

        if (is_array($_troubleshooterParentMap[$_parentTroubleshooterStepID]) && !empty($_troubleshooterParentMap[$_parentTroubleshooterStepID]))
        {
            $_childIndent = str_repeat('   ', $_indent);

            $_childPrefix = $_childIndent . '|- ';

            foreach ($_troubleshooterParentMap[$_parentTroubleshooterStepID] as $_val)
            {
                // Dont show the currently active node
                if ($_activeTroubleshooterStepID == $_val['troubleshooterstepid'])
                {
                    continue;
                }

                if ($_parentTroubleshooterStepID == 0)
                {
                    self::$_troubleshooterStepHits = array();
                }

                // To prevent recursion
                if (!isset(self::$_troubleshooterStepHits[$_val['troubleshooterstepid']]))
                {
                    self::$_troubleshooterStepHits[$_val['troubleshooterstepid']] = 0;
                }

                self::$_troubleshooterStepHits[$_val['troubleshooterstepid']]++;

                $_optionArray = array();
                $_optionArray['title'] = $_childPrefix . $_val['subject'] . IIF(self::$_troubleshooterStepHits[$_val['troubleshooterstepid']] == 4, $_SWIFT->Language->Get('recursionsuffix'));
                $_optionArray['value'] = $_val['troubleshooterstepid'];

                $_isSelected = false;
                if (_is_array($_selectedTroubleshooterStepIDList) && in_array($_val['troubleshooterstepid'], (array) $_selectedTroubleshooterStepIDList))
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

                if (isset($_troubleshooterParentMap[$_val['troubleshooterstepid']]) && count($_troubleshooterParentMap[$_val['troubleshooterstepid']]) > 0
                        && self::$_troubleshooterStepHits[$_val['troubleshooterstepid']] <= 3)
                {
                    self::GetSubCategoryOptions($_troubleshooterCategoryID, $_selectedTroubleshooterStepIDList, $_val['troubleshooterstepid'], $_optionContainer, ($_indent + 1),
                            $_activeTroubleshooterStepID, $_isCheckbox);
                }
            }
        }

        return $_optionContainer;
    }

    /**
     * Render the options for the given category id
     *
     * @author Varun Shoor
     * @param int $_troubleshooterCategoryID The Troubleshooter Category ID
     * @param int $_troubleshooterStepID (OPTIONAL) The Troubleshooter Step ID
     * @return string The Rendered Item HTML
     * @throws SWIFT_Exception
     */
    protected static function GetTreeItemOptions($_troubleshooterCategoryID, $_troubleshooterStepID = 0)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_returnHTML = '&nbsp;&nbsp;-&nbsp;&nbsp;' . '<div title="' . $_SWIFT->Language->Get('insertstep') . '" class="kbstepnew" onclick="javascript: loadViewportData(\'/Troubleshooter/Step/Insert/' . $_troubleshooterStepID . '/' . $_troubleshooterCategoryID . '\');">&nbsp;</div>';

        return $_returnHTML;
    }

    /**
     * Retrieve the Category Tree HTML
     *
     * @author Varun Shoor
     * @param bool $_selectedTroubleshooterCategoryID (OPTIONAL) The Troubleshooter Category ID to be selected by default
     * @return mixed "_renderHTML" (STRING) on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function GetCategoryTree($_selectedTroubleshooterCategoryID = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_troubleshooterCategoryContainer = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "troubleshootercategories ORDER BY displayorder ASC");
        while ($_SWIFT->Database->NextRecord()) {
            $_troubleshooterCategoryContainer[$_SWIFT->Database->Record['troubleshootercategoryid']] = $_SWIFT->Database->Record;
        }

        $_renderHTML = '<ul class="swifttree">';
        foreach ($_troubleshooterCategoryContainer as $_troubleshooterCategory) {
            $_extendedCategoryTitle = ' (' . SWIFT_TroubleshooterCategory::GetCategoryTypeLabel($_troubleshooterCategory['categorytype']) . ')';

            $_renderHTML .= '<li><span class="folder">&nbsp;&nbsp;&nbsp;' . htmlspecialchars($_troubleshooterCategory['title']) . $_extendedCategoryTitle . self::GetTreeItemOptions($_troubleshooterCategory['troubleshootercategoryid']) . '</span>';

            self::$_troubleshooterStepCache = SWIFT_TroubleshooterStep::RetrieveSteps($_troubleshooterCategory['troubleshootercategoryid']);

            self::GetSubTroubleshooterTree($_SWIFT->Database->Record['troubleshootercategoryid'], 0, $_renderHTML);

            $_renderHTML .= '</li>';
        }

        $_renderHTML .= '</li></ul>';

        return $_renderHTML;
    }

    /**
     * Retrieve the troubleshooter step options in a loop
     *
     * @author Varun Shoor
     * @param int $_troubleshooterCategoryID The Troubleshooter Category ID
     * @param int $_parentTroubleshooterStepID The Parent Troubleshooter Step ID
     * @param string $_renderHTML The Render HTML
     * @return mixed "_optionContainer" (ARRAY) on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    protected static function GetSubTroubleshooterTree($_troubleshooterCategoryID, $_parentTroubleshooterStepID, &$_renderHTML)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_troubleshooterStepCache = (array) self::$_troubleshooterStepCache;

        $_troubleshooterParentMap = array();

        // will be overwritten by extract
        $_troubleshooterParentMap[$_parentTroubleshooterStepID] = [];

        extract($_troubleshooterStepCache, EXTR_OVERWRITE);

        if (is_array($_troubleshooterParentMap[$_parentTroubleshooterStepID]) && !empty($_troubleshooterParentMap[$_parentTroubleshooterStepID]))
        {
            $_renderHTML .= '<ul>';

            foreach ($_troubleshooterParentMap[$_parentTroubleshooterStepID] as $_val)
            {
                if ($_parentTroubleshooterStepID == 0)
                {
                    self::$_troubleshooterStepHits = array();
                }

                // To prevent recursion
                if (!isset(self::$_troubleshooterStepHits[$_val['troubleshooterstepid']]))
                {
                    self::$_troubleshooterStepHits[$_val['troubleshooterstepid']] = 0;
                }

                self::$_troubleshooterStepHits[$_val['troubleshooterstepid']]++;

                $_stepTitle =  htmlspecialchars($_val['subject']) . IIF(self::$_troubleshooterStepHits[$_val['troubleshooterstepid']] == 4, $_SWIFT->Language->Get('recursionsuffix'));

                $_renderHTML .= '<li><span class="file"><a href="' . SWIFT::Get('basename') . '/Troubleshooter/Step/Edit/' . (int) ($_val['troubleshooterstepid']) . '" viewport="1">' . $_stepTitle . '</a>' . self::GetTreeItemOptions($_val['troubleshootercategoryid'], $_val['troubleshooterstepid']) . '</span>';


                if (isset($_troubleshooterParentMap[$_val['troubleshooterstepid']]) && count($_troubleshooterParentMap[$_val['troubleshooterstepid']]) > 0
                        && self::$_troubleshooterStepHits[$_val['troubleshooterstepid']] <= 3)
                {
                    self::GetSubTroubleshooterTree($_troubleshooterCategoryID, $_val['troubleshooterstepid'], $_renderHTML);
                }

                $_renderHTML .= '</li>';
            }

            $_renderHTML .= '</ul>';
        }

        return true;
    }
}
