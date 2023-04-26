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

namespace Knowledgebase\Library\Render;

use Knowledgebase\Models\Category\SWIFT_KnowledgebaseCategory;
use SWIFT;
use SWIFT_Exception;
use SWIFT_Library;
use Base\Models\Staff\SWIFT_StaffGroupLink;

/**
 * The Knowledgebase Render Manager
 *
 * @author Varun Shoor
 */
class SWIFT_KnowledgebaseRenderManager extends SWIFT_Library
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

        $this->Language->Load('staff_knowledgebase');
    }


    /**
     * Render the Knowledgebase Tree
     *
     * @author Varun Shoor
     * @return mixed "_renderHTML" (STRING) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RenderTree()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_renderHTML = '<ul class="swifttree">';

        $_renderHTML .= '<li><span class="funnel"><a href="javascript: void(0);" onclick="javascript:void(0);">' . $this->Language->Get('ftcategories') . '</a></span>';
        $_renderHTML .= '<ul>';

        /**
         * BUG FIX - Mansi Wason <mansi.wason@kayako.com>
         *
         * SWIFT-1326 "Knowledgebase category restrictions to staff teams do not take effect".
         *
         * Comment - Restricted Categories must not be visible in Quick Filter.
         */
        $_StaffKnowledgebaseCategoryIDList = SWIFT_StaffGroupLink::RetrieveListFromCacheOnStaffGroup(SWIFT_StaffGroupLink::TYPE_KBCATEGORY, $_SWIFT->Staff->GetProperty('staffgroupid'));
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . SWIFT_KnowledgebaseCategory::TABLE_NAME . "
                                    WHERE parentkbcategoryid = '0' AND categorytype IN ('" . SWIFT_KnowledgebaseCategory::TYPE_GLOBAL . "', '" . SWIFT_KnowledgebaseCategory::TYPE_PRIVATE . "', '" . SWIFT_KnowledgebaseCategory::TYPE_INHERIT . "', '" . SWIFT_KnowledgebaseCategory::TYPE_PUBLIC . "')
                                      AND (staffvisibilitycustom = '0' OR (staffvisibilitycustom = '1' AND kbcategoryid IN (" . BuildIN($_StaffKnowledgebaseCategoryIDList) . "))) ORDER BY displayorder ASC");
        while ($this->Database->NextRecord())
        {
            $_extendedText = '';

            $_renderHTML .= '<li><span class="folder"><a href="' . SWIFT::Get('basename') . '/Knowledgebase/Article/QuickFilter/category/' . ($this->Database->Record['kbcategoryid']) . '" viewport="1">' . htmlspecialchars(StripName($this->Database->Record['title'], 16)) . '</a>' . $_extendedText . '</span></li>';
        }
        $_renderHTML .= '</ul></li>';

        $_renderHTML .= '<li><span class="funnel"><a href="javascript: void(0);">' . $this->Language->Get('ftdate') . '</a></span>';
        $_renderHTML .= '<ul>';
            $_renderHTML .= '<li><span class="date"><a href="' . SWIFT::Get('basename') . '/Knowledgebase/Article/QuickFilter/date/today" viewport="1">' . htmlspecialchars($this->Language->Get('ctoday')) . '</a></span></li>';
            $_renderHTML .= '<li><span class="date"><a href="' . SWIFT::Get('basename') . '/Knowledgebase/Article/QuickFilter/date/yesterday" viewport="1">' . htmlspecialchars($this->Language->Get('cyesterday')) . '</a></span></li>';
            $_renderHTML .= '<li><span class="date"><a href="' . SWIFT::Get('basename') . '/Knowledgebase/Article/QuickFilter/date/l7" viewport="1">' . htmlspecialchars($this->Language->Get('cl7days')) . '</a></span></li>';
            $_renderHTML .= '<li><span class="date"><a href="' . SWIFT::Get('basename') . '/Knowledgebase/Article/QuickFilter/date/l30" viewport="1">' . htmlspecialchars($this->Language->Get('cl30days')) . '</a></span></li>';
            $_renderHTML .= '<li><span class="date"><a href="' . SWIFT::Get('basename') . '/Knowledgebase/Article/QuickFilter/date/l180" viewport="1">' . htmlspecialchars($this->Language->Get('cl180days')) . '</a></span></li>';
            $_renderHTML .= '<li><span class="date"><a href="' . SWIFT::Get('basename') . '/Knowledgebase/Article/QuickFilter/date/l365" viewport="1">' . htmlspecialchars($this->Language->Get('cl365days')) . '</a></span></li>';
        $_renderHTML .= '</ul></li>';

        $_renderHTML .= '</ul>';

        return $_renderHTML;
    }

    /**
     * Render the View News Tree
     *
     * @author Varun Shoor
     * @return mixed "_renderHTML" (STRING) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RenderViewKnowledgebaseTree()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_filterStaffKnowledgebaseCategoryIDList = SWIFT_StaffGroupLink::RetrieveListFromCacheOnStaffGroup(SWIFT_StaffGroupLink::TYPE_KBCATEGORY, $_SWIFT->Staff->GetProperty('staffgroupid'));

        $_renderHTML = '<ul class="swifttree">';

        $_renderHTML .= '<li><span class="funnel"><a href="javascript: void(0);" onclick="javascript:void(0);">' . $this->Language->Get('ftcategories') . '</a></span>';
        $_renderHTML .= '<ul>';

        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "kbcategories
            WHERE parentkbcategoryid = '0' AND categorytype IN ('" . SWIFT_KnowledgebaseCategory::TYPE_GLOBAL . "', '" . SWIFT_KnowledgebaseCategory::TYPE_PRIVATE . "')
                AND (staffvisibilitycustom = '0' OR (staffvisibilitycustom = '1' AND kbcategoryid IN (" . BuildIN($_filterStaffKnowledgebaseCategoryIDList) . ")))");
        while ($this->Database->NextRecord())
        {
            $_extendedText = '';

            $_renderHTML .= '<li><span class="folder"><a href="' . SWIFT::Get('basename') . '/Knowledgebase/ViewKnowledgebase/Index/' . ($this->Database->Record['kbcategoryid']) . '" viewport="1">' . htmlspecialchars(StripName($this->Database->Record['title'], 16)) . '</a>' . $_extendedText . '</span></li>';
        }
        $_renderHTML .= '</ul></li>';

        $_renderHTML .= '</ul>';

        return $_renderHTML;
    }
}
