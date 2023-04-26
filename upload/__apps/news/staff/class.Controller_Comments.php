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

namespace News\Staff;

use Base\Admin\Controller_Staff;
use Base\Library\Comment\SWIFT_CommentManager;
use News\Models\Category\SWIFT_NewsCategory;
use News\Models\NewsItem\SWIFT_NewsItem;
use SWIFT;
use Base\Models\Comment\SWIFT_Comment;
use SWIFT_Exception;
use SWIFT_Session;
use Base\Models\Staff\SWIFT_StaffActivityLog;
use Base\Models\Staff\SWIFT_StaffGroupLink;
use Base\Library\UserInterface\SWIFT_UserInterface;

/**
 * Comments Controller: News
 *
 * @property SWIFT_CommentManager $CommentManager
 * @author Varun Shoor
 */
class Controller_Comments extends \Controller_StaffBase
{
    /**
     * Constructor
     *
     * @author Varun Shoor
     */
    public function __construct()
    {
        parent::__construct(self::TYPE_STAFF);

        $this->Load->Library('Comment:CommentManager', [], true, false, 'base');
        $this->Load->Library('Render:NewsRenderManager');

        $this->Language->Load('staff_news');
        $this->Language->Load('staff_newsitems');
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
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_SWIFT_NewsItemObject = false;
        try
        {
            $_SWIFT_NewsItemObject = new SWIFT_NewsItem($_newsItemID);
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {

            return false;
        }

        $_filterStaffGroupIDList = SWIFT_StaffGroupLink::RetrieveList(SWIFT_StaffGroupLink::TYPE_NEWS, $_SWIFT_NewsItemObject->GetNewsItemID());

        if (!$_SWIFT_NewsItemObject instanceof SWIFT_NewsItem || !$_SWIFT_NewsItemObject->GetIsClassLoaded() ||
                $_SWIFT_NewsItemObject->GetProperty('newsstatus') != SWIFT_NewsItem::STATUS_PUBLISHED ||
                $_SWIFT_NewsItemObject->GetProperty('allowcomments') == '0' || ($_SWIFT_NewsItemObject->GetProperty('staffvisibilitycustom') == '1' && !in_array($_SWIFT->Staff->GetProperty('staffgroupid'), $_filterStaffGroupIDList)))
        {
            return false;
        }

        $this->CommentManager->ProcessPOSTStaff($_SWIFT->Staff, SWIFT_Comment::TYPE_NEWS, $_SWIFT_NewsItemObject->GetNewsItemID(), SWIFT::Get('basename') . '/News/NewsItem/ViewItem/' . $_SWIFT_NewsItemObject->GetNewsItemID());

        $this->Load->Controller('NewsItem', 'News')->Load->Method('ViewItem', $_newsItemID);

        return true;
    }
}
?>
