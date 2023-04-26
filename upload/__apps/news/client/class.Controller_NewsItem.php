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
 * @license        http://www.opencart.com.vn/license
 * @link        http://www.opencart.com.vn
 *
 * ###############################################
 */

namespace News\Client;

use Base\Library\Comment\SWIFT_CommentManager;
use Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel;
use News\Models\NewsItem\SWIFT_NewsItem;
use SWIFT;
use SWIFT_App;
use Base\Models\Comment\SWIFT_Comment;
use SWIFT_Exception;
use Controller_client;
use Base\Models\User\SWIFT_UserGroupAssign;
use Base\Models\Widget\SWIFT_Widget;

/**
 * The News Item Controller
 *
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @property SWIFT_CommentManager $CommentManager
 * @author Varun Shoor
 */
class Controller_NewsItem extends Controller_client
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

        $this->Load->Library('Comment:CommentManager', [], true, false, 'base');

        $this->Language->Load('news');

        $this->_ProcessNewsCategories();
    }

    /**
     * The News Rendering Function
     *
     * @author Varun Shoor
     * @param int $_newsItemID The News Item
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function View($_newsItemID = 0)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_newsItemID)) {
            $this->Load->Controller('List', 'News')->Load->Index();

            return false;
        }

        try
        {
            $_SWIFT_NewsItemObject = new SWIFT_NewsItem($_newsItemID);
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            /*
             * BUG FIX - Varun Shoor
             *
             * SWIFT-1201 News view shows blank page if news does not exist.
             *
             * Comments: None
             */
            $this->UserInterface->Error(true, $this->Language->Get('newsnotfound'));
            $this->Load->Controller('List', 'News')->Load->Index();

            return false;
        }

        $_filterNewsItemIDList = SWIFT_UserGroupAssign::RetrieveListOnUserGroup(SWIFT::Get('usergroupid'), SWIFT_UserGroupAssign::TYPE_NEWS);

        if (!$_SWIFT_NewsItemObject instanceof SWIFT_NewsItem || !$_SWIFT_NewsItemObject->GetIsClassLoaded() ||
                $_SWIFT_NewsItemObject->GetProperty('newstype') == SWIFT_NewsItem::TYPE_PRIVATE || $_SWIFT_NewsItemObject->GetProperty('newsstatus') != SWIFT_NewsItem::STATUS_PUBLISHED ||
                ($_SWIFT_NewsItemObject->GetProperty('uservisibilitycustom') == '1' && !in_array($_SWIFT_NewsItemObject->GetNewsItemID(), $_filterNewsItemIDList)))
        {
            return false;
        }

        $start = $_SWIFT_NewsItemObject->GetProperty('start');
        if ($start > DATENOW && $start != '0') // news item has not started
        {
            $this->UserInterface->Error(true, $this->Language->Get('newsnotfound'));
            $this->Load->Controller('List', 'News')->Load->Index();

            return false;
        }

        $store = $_SWIFT_NewsItemObject->RetrieveStore();

        $_subjectSuffix = '';
        $expiry = $_SWIFT_NewsItemObject->GetProperty('expiry');
        if ($expiry < DATENOW && $expiry != '0')
        {
            $_subjectSuffix .= ' ' . ($_SWIFT->Language->Get('newsexpired')?:'[Expired]');
            $store['subject'] .= ' ' . $_subjectSuffix;
        }
        $this->Template->Assign('_newsItem', $store);

        $this->CommentManager->LoadSupportCenter('News', SWIFT_Comment::TYPE_NEWS, $_newsItemID);

        $this->Template->Assign('_pageTitle', htmlspecialchars($_SWIFT_NewsItemObject->GetProperty('subject') . ' ' . $_subjectSuffix));

        $this->UserInterface->Header('news');
        $this->Template->Render('newsitem');
        $this->UserInterface->Footer();

        return true;
    }
}
