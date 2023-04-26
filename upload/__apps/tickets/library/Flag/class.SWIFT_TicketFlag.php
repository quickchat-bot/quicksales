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

namespace Tickets\Library\Flag;
use SWIFT_Exception;
use SWIFT_Library;

/**
 * The Ticket Flag Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_TicketFlag extends SWIFT_Library
{
    const FLAG_PURPLE = 1;
    const FLAG_ORANGE = 2;
    const FLAG_GREEN = 3;
    const FLAG_YELLOW = 4;
    const FLAG_RED = 5;
    const FLAG_BLUE = 6;

    private static $_flagContainer = array(self::FLAG_PURPLE => array('purpleflag', '#A5587C', 'icon_purpleflag.gif', 'menu_purpleflag.gif'),
        self::FLAG_ORANGE => array('orangeflag', '#FF8C5A', 'icon_orangeflag.gif', 'menu_orangeflag.gif'),
        self::FLAG_GREEN => array('greenflag', '#8BB467', 'icon_greenflag.gif', 'menu_greenflag.gif'),
        self::FLAG_YELLOW => array('yellowflag', '#FFC160', 'icon_yellowflag.gif', 'menu_yellowflag.gif'),
        self::FLAG_RED => array('redflag', '#CF5D60', 'icon_redflag.gif', 'menu_redflag.gif'),
        self::FLAG_BLUE => array('blueflag', '#5C83B4', 'icon_blueflag.gif', 'menu_blueflag.gif'));

    /**
     * Check to see if its a valid flag type
     *
     * @author Varun Shoor
     * @param mixed $_flagType The Flag Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidFlagType($_flagType) {
        if ($_flagType == self::FLAG_PURPLE || $_flagType == self::FLAG_ORANGE || $_flagType == self::FLAG_GREEN ||
                $_flagType == self::FLAG_YELLOW || $_flagType == self::FLAG_RED || $_flagType == self::FLAG_BLUE) {
            return true;
        }

        return false;
    }

    /**
     * Get a List of Flags
     *
     * @author Varun Shoor
     * @return bool|array "_flagContainer" (ARRAY) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetFlagList()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Flag_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_flagContainer = array();

        foreach (self::$_flagContainer as $_key => $_val)
        {
            $_flagContainer[$_key] = $this->Language->Get($_val[0]);
        }

        return $_flagContainer;
    }

    /**
     * Get a List of Flags
     *
     * @author Varun Shoor
     * @return bool|array "_flagContainer" (ARRAY) on Success, "false" otherwise
     * @throws SWIFT_Flag_Exception If the Class is not Loaded
     * @throws SWIFT_Exception
     */
    public function GetFlagContainer()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Flag_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_flagContainer = array();

        foreach (self::$_flagContainer as $_key => $_val)
        {
            $_flagContainer[$_key] = self::$_flagContainer[$_key];
            $_flagContainer[$_key][0] = $this->Language->Get($_val[0]);
        }

        return $_flagContainer;
    }
}
