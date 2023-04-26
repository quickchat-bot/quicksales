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

namespace Tickets\Staff;

use SWIFT;
use SWIFT_Date;
use SWIFT_Exception;
use SWIFT_View;

/**
 * The Ajax View
 *
 * @author Varun Shoor
 */
class View_Ajax extends SWIFT_View
{
    /**
     * Render the Post Locks
     *
     * @author Varun Shoor
     * @param array $_ticketPostLockContainer The Ticket Post Lock Container
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RenderPostLocks($_ticketPostLockContainer)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!_is_array($_ticketPostLockContainer)) {
            return true;
        }

        $_staffCache = $this->Cache->Get('staffcache');

        $_outputHTML = '';
        foreach ($_ticketPostLockContainer as $_ticketPostLockID => $_ticketPostLock)
        {
            if ($_ticketPostLock['staffid'] == $_SWIFT->Staff->GetStaffID() || !isset($_staffCache[$_ticketPostLock['staffid']]) || trim($_ticketPostLock['contents']) == '')
            {
                continue;
            }

            $_staffName = $_staffCache[$_ticketPostLock['staffid']]['fullname'];
            $_lockLastUpdate = SWIFT_Date::ColorTime(DATENOW-$_ticketPostLock['dateline']);

            $_outputHTML .= '<div class="ticketpostlockcontainer">';
            $_outputHTML .= '<div class="ticketpostlocktitle">' . sprintf($this->Language->Get('tpostlockinfo'), htmlspecialchars($_staffName), $_lockLastUpdate) . '</div>';
            $_outputHTML .= '<div class="ticketpostlockcontents">' . nl2br($_ticketPostLock['contents']) . '</div>';
            $_outputHTML .= '</div>';
        }

        echo $_outputHTML;

        return true;
    }
}
