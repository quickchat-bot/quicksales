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

namespace News\Client;

use Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel;
use News\Models\NewsItem\SWIFT_NewsItem;
use SWIFT;
use SWIFT_App;
use SWIFT_Exception;
use Controller_client;
use Base\Models\Widget\SWIFT_Widget;

/**
 * The News List Controller
 *
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @author Varun Shoor
 */
class Controller_List extends Controller_client
{

    /**
     * Constructor
     *
     * @author Varun Shoor
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
        if (!SWIFT_App::IsInstalled(APP_NEWS) || !SWIFT_Widget::IsWidgetVisible(APP_NEWS))
        {
            $this->UserInterface->Error(true, $this->Language->Get('nopermission'));
            $this->Load->Controller('Default', 'Core')->Load->Index();
            $this->stopRendering(true);
            return;
        }

        $this->Language->Load('news');

       $this->_ProcessNewsCategories();
    }

    /**
     * The News Rendering Function
     *
     * @author Varun Shoor
     * @param int $_newsCategoryID (OPTIONAL) The News Category ID
     * @param int $_newsOffset (OPTIONAL) The News Offset
     * @return bool "true" on Success,
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Index($_newsCategoryID = 0, $_newsOffset = 0)
    {

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_totalNewsItems = SWIFT_NewsItem::RetrieveCount(array(SWIFT_NewsItem::TYPE_GLOBAL, SWIFT_NewsItem::TYPE_PUBLIC), SWIFT::Get('usergroupid'), 0, $_newsCategoryID);

        $_showOlderPosts = $_showNewerPosts = false;

        $_olderOffset = $_newerOffset = 0;

        if ($_newsOffset > 0)
        {
            $_showNewerPosts = true;

            $_newerOffset = $_newsOffset - $this->Settings->Get('nw_pageno');
        }

        $_newsActiveCount = $_totalNewsItems - ($_newsOffset + $this->Settings->Get('nw_pageno'));

        if ($_newsActiveCount > 0)
        {
            $_showOlderPosts = true;

            $_olderOffset = $_newsOffset + $this->Settings->Get('nw_pageno');
        }

        $this->Template->Assign('_showNewerPosts', $_showNewerPosts);
        $this->Template->Assign('_showOlderPosts', $_showOlderPosts);

        $this->Template->Assign('_olderOffset', $_olderOffset);
        $this->Template->Assign('_newerOffset', $_newerOffset);

        $_newsContainer = SWIFT_NewsItem::Retrieve($this->Settings->Get('nw_pageno'), $_newsOffset, array(SWIFT_NewsItem::TYPE_GLOBAL, SWIFT_NewsItem::TYPE_PUBLIC), SWIFT::Get('usergroupid'), 0, $_newsCategoryID);

        $this->Template->Assign('_newsContainer', $_newsContainer);
        $this->Template->Assign('_newsCount', count($_newsContainer));
        $this->Template->Assign('_newsCategoryID', ($_newsCategoryID));

        $this->Template->Assign('_pageTitle', htmlspecialchars($this->Language->Get('news')));

        $this->UserInterface->Header('news');
        $this->Template->Render('newslist');
        $this->UserInterface->Footer();

        return true;
    }
}
?>
