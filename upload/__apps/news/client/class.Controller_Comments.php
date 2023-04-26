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

use Base\Library\Comment\SWIFT_CommentManager;
use News\Models\NewsItem\SWIFT_NewsItem;
use SWIFT;
use SWIFT_App;
use Base\Models\Comment\SWIFT_Comment;
use SWIFT_Exception;
use Controller_client;
use Base\Models\User\SWIFT_UserGroupAssign;
use Base\Models\Widget\SWIFT_Widget;

/**
 * Comments Controller: News
 *
 * @property SWIFT_CommentManager $CommentManager
 * @author Varun Shoor
 */
class Controller_Comments extends Controller_client
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

            return;
        }

        $this->Load->Library('Comment:CommentManager', [], true, false, 'base');
    }

    /**
     * Submit a new Comment
     *
     * @author Varun Shoor
     * @param int $_newsItemID The News Item ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Submit($_newsItemID)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        try
        {
            $_SWIFT_NewsItemObject = new SWIFT_NewsItem($_newsItemID);
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            return false;
        }

        $_filterNewsItemIDList = SWIFT_UserGroupAssign::RetrieveListOnUserGroup(SWIFT::Get('usergroupid'), SWIFT_UserGroupAssign::TYPE_NEWS);

        if (!$_SWIFT_NewsItemObject instanceof SWIFT_NewsItem || !$_SWIFT_NewsItemObject->GetIsClassLoaded() ||
                $_SWIFT_NewsItemObject->GetProperty('newstype') == SWIFT_NewsItem::TYPE_PRIVATE || $_SWIFT_NewsItemObject->GetProperty('newsstatus') != SWIFT_NewsItem::STATUS_PUBLISHED ||
                $_SWIFT_NewsItemObject->GetProperty('allowcomments') == '0' || ($_SWIFT_NewsItemObject->GetProperty('uservisibilitycustom') == '1' && !in_array($_SWIFT_NewsItemObject->GetNewsItemID(), $_filterNewsItemIDList)))
        {
            return false;
        }

        $_commentResult = $this->CommentManager->ProcessPOSTUser(SWIFT_Comment::TYPE_NEWS, $_SWIFT_NewsItemObject->GetNewsItemID(), SWIFT::Get('basename') . '/News/NewsItem/View/' . $_SWIFT_NewsItemObject->GetNewsItemID());

        if ($_commentResult) {
            unset($_POST['fullname']); unset($_POST['email']); unset($_POST['comments']);
        }

        $this->Load->Controller('NewsItem', 'News')->Load->Method('View', $_newsItemID);

        return true;
    }
}
?>
