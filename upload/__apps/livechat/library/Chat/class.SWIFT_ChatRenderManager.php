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

namespace LiveChat\Library\Chat;

use Base\Models\Department\SWIFT_Department;
use SWIFT;
use SWIFT_Library;

/**
 * The Chat Render Manager
 *
 * @author Varun Shoor
 */
class SWIFT_ChatRenderManager extends SWIFT_Library
{
    /**
     * Render the Chat Tree
     *
     * @author Varun Shoor
     * @return string
     */
    public static function RenderTree()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_chatCountCache = $_SWIFT->Cache->Get('chatcountcache');

        $_renderHTML = '<ul class="swifttree">';

        $_renderHTML .= '<li><span class="funnel"><a href="' . SWIFT::Get('basename') . '/LiveChat/ChatHistory/QuickFilter/department/0" viewport="1">' . $_SWIFT->Language->Get('chtdepartment') . '</a></span>';
        $_renderHTML .= '<ul>';
        $_departmentMap = SWIFT_Department::GetDepartmentMap(APP_LIVECHAT);
        $_assignedDepartmentIDList = $_SWIFT->Staff->GetAssignedDepartments(APP_LIVECHAT);

        foreach ($_departmentMap as $_key => $_val) {
            $_extendedText = '';

            if (!in_array($_key, $_assignedDepartmentIDList)) {
                continue;
            }

            // Counters
            if (isset($_chatCountCache[$_val['departmentid']]['totalitems']) && $_chatCountCache[$_val['departmentid']]['totalitems'] > 0) {
                $_extendedText = ' <font color="red">(' . (int)($_chatCountCache[$_val['departmentid']]['totalitems']) . ')</font>';
            }

            // Is it new?
            if (isset($_chatCountCache[$_val['departmentid']]['dateline']) && $_chatCountCache[$_val['departmentid']]['dateline'] > $_SWIFT->Staff->GetProperty('lastvisit')) {
                $_departmentClass = 'folderred';
            } else {
                $_departmentClass = 'folder';
            }

            $_renderHTML .= '<li><span class="' . $_departmentClass . '"><a href="' . SWIFT::Get('basename') . '/LiveChat/ChatHistory/QuickFilter/department/' . (int)($_val['departmentid']) . '" viewport="1">' . text_to_html_entities($_val['title']) . '</a>' . $_extendedText . '</span>';

            if (_is_array($_val['subdepartments'])) {
                $_renderHTML .= '<ul>';
                foreach ($_val['subdepartments'] as $_subKey => $_subVal) {
                    if (!in_array($_subKey, $_assignedDepartmentIDList)) {
                        continue;
                    }

                    $_extendedText = '';

                    // Sub Department Counters
                    if (isset($_chatCountCache[$_subVal['departmentid']]['totalitems']) && $_chatCountCache[$_subVal['departmentid']]['totalitems'] > 0) {
                        $_extendedText = ' <font color="red">(' . (int)($_chatCountCache[$_subVal['departmentid']]['totalitems']) . ')</font>';
                    }

                    // Is it new?
                    if (isset($_chatCountCache[$_subVal['departmentid']]['dateline']) && $_chatCountCache[$_subVal['departmentid']]['dateline'] > $_SWIFT->Staff->GetProperty('lastvisit')) {
                        $_departmentClass = 'folderred';
                    } else {
                        $_departmentClass = 'folder';
                    }

                    $_renderHTML .= '<li><span class="' . $_departmentClass . '"><a href="' . SWIFT::Get('basename') . '/LiveChat/ChatHistory/QuickFilter/department/' . (int)($_subVal['departmentid']) . '" viewport="1">' . text_to_html_entities($_subVal['title']) . '</a>' . $_extendedText . '</span></li>';
                }
                $_renderHTML .= '</ul>';
            }

            $_renderHTML .= '</li>';
        }
        $_renderHTML .= '</ul></li>';

        $_renderHTML .= '<li><span class="funnel"><a href="javascript: void(0);">' . $_SWIFT->Language->Get('chtdate') . '</a></span>';
        $_renderHTML .= '<ul>';
        $_renderHTML .= '<li><span class="date"><a href="' . SWIFT::Get('basename') . '/LiveChat/ChatHistory/QuickFilter/date/today" viewport="1">' . htmlspecialchars($_SWIFT->Language->Get('ctoday')) . '</a></span></li>';
        $_renderHTML .= '<li><span class="date"><a href="' . SWIFT::Get('basename') . '/LiveChat/ChatHistory/QuickFilter/date/yesterday" viewport="1">' . htmlspecialchars($_SWIFT->Language->Get('cyesterday')) . '</a></span></li>';
        $_renderHTML .= '<li><span class="date"><a href="' . SWIFT::Get('basename') . '/LiveChat/ChatHistory/QuickFilter/date/l7" viewport="1">' . htmlspecialchars($_SWIFT->Language->Get('cl7days')) . '</a></span></li>';
        $_renderHTML .= '<li><span class="date"><a href="' . SWIFT::Get('basename') . '/LiveChat/ChatHistory/QuickFilter/date/l30" viewport="1">' . htmlspecialchars($_SWIFT->Language->Get('cl30days')) . '</a></span></li>';
        $_renderHTML .= '<li><span class="date"><a href="' . SWIFT::Get('basename') . '/LiveChat/ChatHistory/QuickFilter/date/l180" viewport="1">' . htmlspecialchars($_SWIFT->Language->Get('cl180days')) . '</a></span></li>';
        $_renderHTML .= '<li><span class="date"><a href="' . SWIFT::Get('basename') . '/LiveChat/ChatHistory/QuickFilter/date/l365" viewport="1">' . htmlspecialchars($_SWIFT->Language->Get('cl365days')) . '</a></span></li>';
        $_renderHTML .= '</ul></li>';

        $_renderHTML .= '<li><span class="funnel"><a href="javascript: void(0);">' . $_SWIFT->Language->Get('chttype') . '</a></span>';
        $_renderHTML .= '<ul>';
        $_renderHTML .= '<li><span class="chat"><a href="' . SWIFT::Get('basename') . '/LiveChat/ChatHistory/QuickFilter/type/public" viewport="1">' . htmlspecialchars($_SWIFT->Language->Get('chpublic')) . '</a></span></li>';
        $_renderHTML .= '<li><span class="chat"><a href="' . SWIFT::Get('basename') . '/LiveChat/ChatHistory/QuickFilter/type/private" viewport="1">' . htmlspecialchars($_SWIFT->Language->Get('chprivate')) . '</a></span></li>';
        $_renderHTML .= '<li><span class="chat"><a href="' . SWIFT::Get('basename') . '/LiveChat/ChatHistory/QuickFilter/type/unanswered" viewport="1">' . htmlspecialchars($_SWIFT->Language->Get('chunanswered')) . '</a></span></li>';
        $_renderHTML .= '<li><span class="chat"><a href="' . SWIFT::Get('basename') . '/LiveChat/ChatHistory/QuickFilter/type/timedout" viewport="1">' . htmlspecialchars($_SWIFT->Language->Get('chtimedout')) . '</a></span></li>';
        $_renderHTML .= '</ul></li>';

        $_renderHTML .= '</ul>';

        return $_renderHTML;
    }
}
