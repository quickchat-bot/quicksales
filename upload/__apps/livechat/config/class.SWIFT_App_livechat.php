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

namespace LiveChat;

use SWIFT;
use SWIFT_App;
use SWIFT_Exception;
use SWIFT_Interface;

/**
 * The Live Chat App Lib
 *
 * @author Varun Shoor
 */
class SWIFT_App_livechat extends SWIFT_App
{
    /**
     * Initialize the App
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Initialize()
    {
        $_SWIFT = SWIFT::GetInstance();

        parent::Initialize();

        if ($_SWIFT->Interface->GetInterface() == SWIFT_Interface::INTERFACE_VISITOR) {
            $_SWIFT->Cache->Queue('visitorrulecache');

        } else if ($_SWIFT->Interface->GetInterface() == SWIFT_Interface::INTERFACE_WINAPP || $_SWIFT->Interface->GetInterface() == SWIFT_Interface::INTERFACE_MOBILE
            || $_SWIFT->Interface->GetInterface() == SWIFT_Interface::INTERFACE_STAFFAPI || $_SWIFT->Interface->GetInterface() == SWIFT_Interface::INTERFACE_ADMIN) {
            $_SWIFT->Cache->Queue('skillscache', 'visitorgroupcache');
        }

        return true;
    }

}
