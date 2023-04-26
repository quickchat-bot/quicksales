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

namespace Base\Library\User;

use Base\Models\User\SWIFT_UserGroup;
use SWIFT;
use SWIFT_Exception;
use SWIFT_Hook;
use SWIFT_Library;

/**
 * The User Rendering Manager
 *
 * @author Varun Shoor
 */
class SWIFT_UserRenderManager extends SWIFT_Library
{
    /**
     * Constructor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Render the User Tree
     *
     * @author Varun Shoor
     * @return mixed "_renderHTML" (STRING) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RenderTree()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_renderHTML = '<ul class="swifttree">';

        $_renderHTML .= '<li><span class="funnel"><a href="javascript: void(0);">' . $this->Language->Get('usergroups') . '</a></span>';
        $_renderHTML .= '<ul>';

        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "usergroups WHERE grouptype = '" . SWIFT_UserGroup::TYPE_REGISTERED . "'");
        while ($this->Database->NextRecord()) {
            $_renderHTML .= '<li><span class="usergroup"><a href="' . SWIFT::Get('basename') . '/Base/User/QuickFilter/usergroup/' . (int)($this->Database->Record['usergroupid']) . '" viewport="1">' . htmlspecialchars($this->Database->Record['title']) . '</a></span></li>';
        }

        $_renderHTML .= '</ul></li>';

        // Begin Hook: staff_user_tree
        unset($_hookCode);
        ($_hookCode = SWIFT_Hook::Execute('staff_user_tree')) ? eval($_hookCode) : false;
        // End Hook

        $_renderHTML .= '<li><span class="funnel"><a href="javascript: void(0);">' . $this->Language->Get('usdateregistered') . '</a></span>';
        $_renderHTML .= '<ul>';
        $_renderHTML .= '<li><span class="date"><a href="' . SWIFT::Get('basename') . '/Base/User/QuickFilter/date/today" viewport="1">' . htmlspecialchars($this->Language->Get('ctoday')) . '</a></span></li>';
        $_renderHTML .= '<li><span class="date"><a href="' . SWIFT::Get('basename') . '/Base/User/QuickFilter/date/yesterday" viewport="1">' . htmlspecialchars($this->Language->Get('cyesterday')) . '</a></span></li>';
        $_renderHTML .= '<li><span class="date"><a href="' . SWIFT::Get('basename') . '/Base/User/QuickFilter/date/l7" viewport="1">' . htmlspecialchars($this->Language->Get('cl7days')) . '</a></span></li>';
        $_renderHTML .= '<li><span class="date"><a href="' . SWIFT::Get('basename') . '/Base/User/QuickFilter/date/l30" viewport="1">' . htmlspecialchars($this->Language->Get('cl30days')) . '</a></span></li>';
        $_renderHTML .= '<li><span class="date"><a href="' . SWIFT::Get('basename') . '/Base/User/QuickFilter/date/l180" viewport="1">' . htmlspecialchars($this->Language->Get('cl180days')) . '</a></span></li>';
        $_renderHTML .= '<li><span class="date"><a href="' . SWIFT::Get('basename') . '/Base/User/QuickFilter/date/l365" viewport="1">' . htmlspecialchars($this->Language->Get('cl365days')) . '</a></span></li>';
        $_renderHTML .= '</ul></li>';

        $_renderHTML .= '</ul>';

        return $_renderHTML;
    }
}

?>
