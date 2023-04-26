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

namespace Base\Staff;

use Base\Library\UserInterface\SWIFT_UserInterfaceGrid;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridField;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridMassAction;
use Base\Models\Comment\SWIFT_Comment;
use Base\Staff\Controller_Comment;
use SWIFT;
use SWIFT_Date;
use SWIFT_Exception;
use SWIFT_View;

/**
 * The Comment View
 *
 * @author Varun Shoor
 *
 * @property Controller_Comment $Controller
 */
class View_Comment extends SWIFT_View
{
    /**
     * Render the Comments Grid
     *
     * @author Varun Shoor
     * @param mixed $_commentStatus The Comment Status
     * @return bool "true" on Success, "false" otherwise
     */
    public function RenderGrid($_commentStatus)
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->Load->Library('UserInterface:UserInterfaceGrid', array('comments'), true, false, 'base');

        if ($this->UserInterfaceGrid->GetMode() == SWIFT_UserInterfaceGrid::MODE_SEARCH) {
            $_commentsWhereClause = ' WHERE (
                    (' . $this->UserInterfaceGrid->BuildSQLSearch('comments.fullname') . ') OR
                    (' . $this->UserInterfaceGrid->BuildSQLSearch('comments.email') . ') OR
                    (' . $this->UserInterfaceGrid->BuildSQLSearch('comments.fullname') . ') OR
                    (' . $this->UserInterfaceGrid->BuildSQLSearch('commentdata.contents') . ')) AND comments.commentstatus = \'' . (int)($_commentStatus) . '\'';

            $this->UserInterfaceGrid->SetSearchQuery('SELECT comments.*, commentdata.contents FROM ' . TABLE_PREFIX . 'comments AS comments
                LEFT JOIN ' . TABLE_PREFIX . 'commentdata AS commentdata ON (comments.commentid = commentdata.commentid)' . $_commentsWhereClause,

                'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX . 'comments AS comments ' . $_commentsWhereClause);
        }

        $this->UserInterfaceGrid->SetQuery('SELECT comments.*, commentdata.contents FROM ' . TABLE_PREFIX . 'comments AS comments
                LEFT JOIN ' . TABLE_PREFIX . 'commentdata AS commentdata ON (comments.commentid = commentdata.commentid)
                WHERE comments.commentstatus = \'' . (int)($_commentStatus) . '\'',
            'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX . 'comments AS comments
                WHERE comments.commentstatus = \'' . (int)($_commentStatus) . '\'');

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('commentid', 'commentid',
            SWIFT_UserInterfaceGridField::TYPE_ID));

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('comments.fullname', $this->Language->Get('author'),
            SWIFT_UserInterfaceGridField::TYPE_DB, 300, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('contents', $this->Language->Get('comments'),
            SWIFT_UserInterfaceGridField::TYPE_CUSTOM, 0, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('comments.dateline', $this->Language->Get('date'),
            SWIFT_UserInterfaceGridField::TYPE_DB, 180, SWIFT_UserInterfaceGridField::ALIGN_LEFT, SWIFT_UserInterfaceGridField::SORT_DESC), true);

        $this->UserInterfaceGrid->SetExtendedArguments((int)($_commentStatus));
        $this->UserInterfaceGrid->SetRenderCallback(array($this, 'GridRender'));

        if ($_commentStatus == SWIFT_Comment::STATUS_PENDING) {
            $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('approve'), 'fa-check-circle',
                array('Base\Staff\Controller_Comment', 'ApproveList'), $this->Language->Get('actionconfirm')));
            $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('spam'), 'fa-exclamation-triangle',
                array('Base\Staff\Controller_Comment', 'SpamList'), $this->Language->Get('actionconfirm')));

        } else if ($_commentStatus == SWIFT_Comment::STATUS_SPAM) {
            $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('notspam'), 'fa-envelope-open',
                array('Base\Staff\Controller_Comment', 'NotSpamList'), $this->Language->Get('actionconfirm')));

        }

        $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('delete'), 'fa-trash',
            array('Base\Staff\Controller_Comment', 'DeleteList'), $this->Language->Get('actionconfirm')));

        $this->UserInterfaceGrid->Render();

        $this->Controller->_LoadDisplayData($_commentStatus);

        $this->UserInterface->Header($this->Language->Get('comments') . ' > ' . $this->Language->Get('manage'), Controller_Comment::MENU_ID,
            Controller_Comment::NAVIGATION_ID);

        $this->UserInterfaceGrid->Display();

        return true;
    }

    /**
     * The Grid Rendering Function
     *
     * @author Varun Shoor
     * @param array $_fieldContainer The Field Record Value Container
     * @return array The Processed Field Container Array
     */
    public static function GridRender($_fieldContainer)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_profileImageURL = '';
        if ($_fieldContainer['creatortype'] == SWIFT_Comment::CREATOR_STAFF) {
            $_profileImageURL = SWIFT::Get('basename') . '/Base/StaffProfile/DisplayAvatar/' . $_fieldContainer['creatorid'] . '/' . md5($_fieldContainer['email']) . '/40';
        } else {
            $_profileImageURL = SWIFT::Get('basename') . '/Base/User/DisplayAvatar/' . $_fieldContainer['creatorid'] . '/' . md5($_fieldContainer['email']) . '/40';
        }

        $_fieldContainer['comments.fullname'] = '<div><div style="float: left;"><img src="' . $_profileImageURL . '" align="absmiddle" border="0" /></div>
            <div style="padding-left: 50px;"><div class="tabletitle">' . text_to_html_entities($_fieldContainer['fullname']) . '</div><div class="smalltext"><a href="mailto: ' . htmlspecialchars($_fieldContainer['email']) . '">' . htmlspecialchars($_fieldContainer['email']) . '</a></div></div>
        </div>';

        $_fieldContainer['contents'] = '<div class="commentstext">' . nl2br(htmlspecialchars($_fieldContainer['contents'])) . '</div>' . IIF(!empty($_fieldContainer['parenturl']), '<div class="smalltext" style="padding-top: 6px;">' . $_SWIFT->Language->Get('inresponseto') . ' <a href="' . $_fieldContainer['parenturl'] . '" target="_blank">' . $_fieldContainer['parenturl'] . ' <img src="' . SWIFT::Get('themepathimages') . 'icon_newwindow_gray.png" align="absmiddle" border="0" /> </a></div>');

        $_fieldContainer['comments.dateline'] = SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_fieldContainer['dateline']);

        return $_fieldContainer;
    }

    /**
     * Render the Comment Tree
     *
     * @author Varun Shoor
     * @return mixed "_renderHTML" (STRING) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RenderTree($_activeStatus = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_commentCache = $this->Cache->Get('commentscache');

        $_countPending = $_countApproved = $_countSpam = 0;
        if (isset($_commentCache[SWIFT_Comment::STATUS_PENDING])) {
            $_countPending = number_format((int)($_commentCache[SWIFT_Comment::STATUS_PENDING]), 2);
        }

        if (isset($_commentCache[SWIFT_Comment::STATUS_APPROVED])) {
            $_countApproved = number_format((int)($_commentCache[SWIFT_Comment::STATUS_APPROVED]), 2);
        }

        if (isset($_commentCache[SWIFT_Comment::STATUS_SPAM])) {
            $_countSpam = number_format((int)($_commentCache[SWIFT_Comment::STATUS_SPAM]), 2);
        }

        $_renderHTML = '<ul class="swifttree">';

        $_renderHTML .= '<li><span class="funnel"><a href="javascript: void(0);">' . $this->Language->Get('commentstatus') . '</a></span>';
        $_renderHTML .= '<ul>';
        $_renderHTML .= '<li><span class="folder' . IIF($_activeStatus == SWIFT_Comment::STATUS_PENDING, ' boldtext') . '"><a href="' . SWIFT::Get('basename') . '/Base/Comment/Manage/' . SWIFT_Comment::STATUS_PENDING . '" viewport="1">' . htmlspecialchars($this->Language->Get('commentpending')) . '</a> ' . IIF(!empty($_countPending), '<font color="red">(' . (int)($_countPending) . ')</font>') . '</span></li>';
        $_renderHTML .= '<li><span class="folder' . IIF($_activeStatus == SWIFT_Comment::STATUS_APPROVED, ' boldtext') . '"><a href="' . SWIFT::Get('basename') . '/Base/Comment/Manage/' . SWIFT_Comment::STATUS_APPROVED . '" viewport="1">' . htmlspecialchars($this->Language->Get('commentapproved')) . '</a> ' . IIF(!empty($_countApproved), '<font color="darkgreen">(' . (int)($_countApproved) . ')</font>') . '</span></li>';
        $_renderHTML .= '<li><span class="folder' . IIF($_activeStatus == SWIFT_Comment::STATUS_SPAM, ' boldtext') . '"><a href="' . SWIFT::Get('basename') . '/Base/Comment/Manage/' . SWIFT_Comment::STATUS_SPAM . '" viewport="1">' . htmlspecialchars($this->Language->Get('commentspam')) . '</a> ' . IIF(!empty($_countSpam), '<font color="red">(' . (int)($_countSpam) . ')</font>') . '</span></li>';
        $_renderHTML .= '</ul></li>';

        $_renderHTML .= '</ul>';

        return $_renderHTML;
    }
}

?>
