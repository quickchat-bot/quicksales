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

namespace News\Library\Render;

use News\Models\NewsItem\SWIFT_NewsItem;
use SWIFT;
use SWIFT_Exception;
use SWIFT_Library;

/**
 * The News Render Manager
 *
 * @author Varun Shoor
 */
class SWIFT_NewsRenderManager extends SWIFT_Library
{
    /**
     * Constructor
     *
     * @author Varun Shoor
     */
    public function __construct()
    {
        parent::__construct();

        $this->Language->Load('staff_news');
    }

    /**
     * Render the News Tree
     *
     * @author Varun Shoor
     * @return mixed "_renderHTML" (STRING) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RenderCategoryTree()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_renderHTML = '<ul class="swifttree">';

        $_renderHTML .= '<li><span class="funnel"><a href="javascript: void(0);">' . $this->Language->Get('visibilitytype') . '</a></span>';
        $_renderHTML .= '<ul>';
        $_renderHTML .= '<li><span class="news"><a href="' . SWIFT::Get('basename') . '/News/Category/QuickFilter/visibility/public" viewport="1">' . htmlspecialchars($this->Language->Get('public')) . '</a></span></li>';
        $_renderHTML .= '<li><span class="news"><a href="' . SWIFT::Get('basename') . '/News/Category/QuickFilter/visibility/private" viewport="1">' . htmlspecialchars($this->Language->Get('private')) . '</a></span></li>';
        $_renderHTML .= '</ul></li>';

        $_renderHTML .= '<li><span class="funnel"><a href="javascript: void(0);">' . $this->Language->Get('ftdate') . '</a></span>';
        $_renderHTML .= '<ul>';
        $_renderHTML .= '<li><span class="date"><a href="' . SWIFT::Get('basename') . '/News/Category/QuickFilter/date/today" viewport="1">' . htmlspecialchars($this->Language->Get('ctoday')) . '</a></span></li>';
        $_renderHTML .= '<li><span class="date"><a href="' . SWIFT::Get('basename') . '/News/Category/QuickFilter/date/yesterday" viewport="1">' . htmlspecialchars($this->Language->Get('cyesterday')) . '</a></span></li>';
        $_renderHTML .= '<li><span class="date"><a href="' . SWIFT::Get('basename') . '/News/Category/QuickFilter/date/l7" viewport="1">' . htmlspecialchars($this->Language->Get('cl7days')) . '</a></span></li>';
        $_renderHTML .= '<li><span class="date"><a href="' . SWIFT::Get('basename') . '/News/Category/QuickFilter/date/l30" viewport="1">' . htmlspecialchars($this->Language->Get('cl30days')) . '</a></span></li>';
        $_renderHTML .= '<li><span class="date"><a href="' . SWIFT::Get('basename') . '/News/Category/QuickFilter/date/l180" viewport="1">' . htmlspecialchars($this->Language->Get('cl180days')) . '</a></span></li>';
        $_renderHTML .= '<li><span class="date"><a href="' . SWIFT::Get('basename') . '/News/Category/QuickFilter/date/l365" viewport="1">' . htmlspecialchars($this->Language->Get('cl365days')) . '</a></span></li>';
        $_renderHTML .= '</ul></li>';

        $_renderHTML .= '</ul>';

        return $_renderHTML;
    }

    /**
     * Render the News Tree
     *
     * @author Varun Shoor
     * @return mixed "_renderHTML" (STRING) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RenderTree()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_newsCategoryCache = (array) $this->Cache->Get('newscategorycache');

        $_renderHTML = '<ul class="swifttree">';

        $_renderHTML .= '<li><span class="funnel"><a href="javascript: void(0);">' . $this->Language->Get('newstype') . '</a></span>';
        $_renderHTML .= '<ul>';
            $_renderHTML .= '<li><span class="news"><a href="' . SWIFT::Get('basename') . '/News/NewsItem/QuickFilter/type/global" viewport="1">' . htmlspecialchars($this->Language->Get('global')) . '</a></span></li>';
            $_renderHTML .= '<li><span class="news"><a href="' . SWIFT::Get('basename') . '/News/NewsItem/QuickFilter/type/public" viewport="1">' . htmlspecialchars($this->Language->Get('public')) . '</a></span></li>';
            $_renderHTML .= '<li><span class="news"><a href="' . SWIFT::Get('basename') . '/News/NewsItem/QuickFilter/type/private" viewport="1">' . htmlspecialchars($this->Language->Get('private')) . '</a></span></li>';
        $_renderHTML .= '</ul></li>';

        $_renderHTML .= '<li><span class="funnel"><a href="javascript: void(0);" onclick="javascript: void(0);">' . $this->Language->Get('ftnewscategories') . '</a></span>';
        $_renderHTML .= '<ul>';

        /**
         * @var int $_newsCategoryID
         * @var array $_newsCategoryContainer
         */
        foreach ($_newsCategoryCache as $_newsCategoryID => $_newsCategoryContainer)
        {
            $_extendedText = '';

            // Counters
            if ($_newsCategoryContainer['newsitemcount'] > 0)
            {
                $_extendedText = ' <font color="red">(' . (int) ($_newsCategoryContainer['newsitemcount']) . ')</font>';
            }

            $title = $_newsCategoryContainer['categorytitle']?: '';
            $_renderHTML .= '<li><span class="folder"><a href="' . SWIFT::Get('basename') . '/News/NewsItem/QuickFilter/category/' .  ($_newsCategoryID) . '" viewport="1">' . htmlspecialchars(StripName($title, 12)) . '</a>' . $_extendedText . '</span></li>';
        }
        $_renderHTML .= '</ul></li>';

        $_renderHTML .= '<li><span class="funnel"><a href="javascript: void(0);">' . $this->Language->Get('ftdate') . '</a></span>';
        $_renderHTML .= '<ul>';
            $_renderHTML .= '<li><span class="date"><a href="' . SWIFT::Get('basename') . '/News/NewsItem/QuickFilter/date/today" viewport="1">' . htmlspecialchars($this->Language->Get('ctoday')) . '</a></span></li>';
            $_renderHTML .= '<li><span class="date"><a href="' . SWIFT::Get('basename') . '/News/NewsItem/QuickFilter/date/yesterday" viewport="1">' . htmlspecialchars($this->Language->Get('cyesterday')) . '</a></span></li>';
            $_renderHTML .= '<li><span class="date"><a href="' . SWIFT::Get('basename') . '/News/NewsItem/QuickFilter/date/l7" viewport="1">' . htmlspecialchars($this->Language->Get('cl7days')) . '</a></span></li>';
            $_renderHTML .= '<li><span class="date"><a href="' . SWIFT::Get('basename') . '/News/NewsItem/QuickFilter/date/l30" viewport="1">' . htmlspecialchars($this->Language->Get('cl30days')) . '</a></span></li>';
            $_renderHTML .= '<li><span class="date"><a href="' . SWIFT::Get('basename') . '/News/NewsItem/QuickFilter/date/l180" viewport="1">' . htmlspecialchars($this->Language->Get('cl180days')) . '</a></span></li>';
            $_renderHTML .= '<li><span class="date"><a href="' . SWIFT::Get('basename') . '/News/NewsItem/QuickFilter/date/l365" viewport="1">' . htmlspecialchars($this->Language->Get('cl365days')) . '</a></span></li>';
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
    public function RenderViewNewsTree()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_renderHTML = '<ul class="swifttree">';

        $_renderHTML .= '<li><span class="funnel"><a href="javascript: void(0);" onclick="javascript: void(0);">' . $this->Language->Get('ftnewscategories') . '</a></span>';
        $_renderHTML .= '<ul>';

        $_newsCategoryContainer = SWIFT_NewsItem::RetrieveCategoryCount(array(SWIFT_NewsItem::TYPE_GLOBAL, SWIFT_NewsItem::TYPE_PRIVATE), 0, $_SWIFT->Staff->GetProperty('staffgroupid'));

        foreach ($_newsCategoryContainer as $_newsCategoryID => $_newsCategoryContainer)
        {
            $_extendedText = '';

            // Counters
            if ($_newsCategoryContainer['totalitems'] > 0)
            {
                $_extendedText = ' <font color="red">(' . (int) ($_newsCategoryContainer['totalitems']) . ')</font>';
            }

            $_renderHTML .= '<li><span class="folder"><a href="' . SWIFT::Get('basename') . '/News/NewsItem/ViewAll/' . (int) ($_newsCategoryID) . '" viewport="1">' . htmlspecialchars(StripName($_newsCategoryContainer['categorytitle'], 12)) . '</a>' . $_extendedText . '</span></li>';
        }
        $_renderHTML .= '</ul></li>';


        $_renderHTML .= '</ul>';

        return $_renderHTML;
    }

    /**
     * Render the contents for the welcome tab
     *
     * @author Varun Shoor
     * @return string
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RenderWelcomeTab()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->Settings->Get('nw_enablestaffdashboard') == '0')
        {
            return '';
        }

        $_renderHTML = '<div>';
        $_renderHTML .= '<div><div style="display: block;min-height: 30px;"><div class="viewmore" onclick="javascript: loadViewportData(\'/News/NewsItem/ViewAll\');">View More</div></div><table class="hlineheaderext"><tr><th rowspan="2" nowrap>' . $this->Language->Get('news') . '</th><td>&nbsp;</td></tr><tr><td class="hlinelower">&nbsp;</td></tr></table></div>';

        $_renderHTML .= '<div class="dashboardtabdatacontainer">';

        $_newsContainer = SWIFT_NewsItem::Retrieve($this->Settings->Get('nw_maxdashboardnewslist'), 0, array(SWIFT_NewsItem::TYPE_GLOBAL, SWIFT_NewsItem::TYPE_PRIVATE), 0, $_SWIFT->Staff->GetProperty('staffgroupid'), 0);
        if (!_is_array($_newsContainer))
        {
            $_renderHTML .= '<div class="dashboardmsg">' . $this->Language->Get('noinfoinview') . '</div>';
        } else {
            $_renderHTML .= '<table cellpadding="0" cellspacing="0" border="0" class = "containercontenttable">';

            foreach ($_newsContainer as $_newsItemID => $_newsItem)
            {
                $_posted = ($_newsItem['author']) ? $this->Language->Get('nwpostedby') . ' ' . htmlspecialchars($_newsItem['author']) : $this->Language->Get('nwposted');

                $_renderHTML .= '<tr>';
                $_renderHTML .= '<td width="100%" valign="top">
                            <div class="newsavatar"><img src="' . SWIFT::Get('basename') . '/Base/StaffProfile/DisplayAvatar/' . $_newsItem['staffid'] . '/' . $_newsItem['emailhash'] . '/40'. '" align="absmiddle" border="0" /></div>
                            <div class="newstitle"><a class="newstitlelink" href="' . SWIFT::Get('basename') . '/News/NewsItem/ViewItem/' . $_newsItemID . '" viewport="1">' . $_newsItem['subject'] . '</a>
                            <div class="newsinfo">' . $_posted .' '. $this->Language->Get('on') . ' ' . htmlspecialchars($_newsItem['date']) . '</div>';

                $_renderHTML .= '</tr>';

                $_renderHTML .= '<tr><td colspan="2" class="newscontents">' . $_newsItem['contents'] . '<br /><a class="newsreadmorelink" href="' . SWIFT::Get('basename') . '/News/NewsItem/ViewItem/' . $_newsItemID . '" viewport="1" title="' . htmlspecialchars($_newsItem['subject']) . '">' . $this->Language->Get('nwreadmore') . '</a></td></tr>';

                $_renderHTML .= '<tr><td colspan="2"><hr class="newshr" /><br /></td></tr>';
            }

            $_renderHTML .= '</table>';
        }

        $_renderHTML .= '</div>';

        $_renderHTML .= '</div>';


        return $_renderHTML;
    }
}
