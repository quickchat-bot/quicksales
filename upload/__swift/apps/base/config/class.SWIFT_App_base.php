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

namespace Base;

use SWIFT;
use SWIFT_App;
use SWIFT_Exception;
use SWIFT_Interface;

/**
 * The Base App Lib
 *
 * @author Varun Shoor
 */
class SWIFT_App_base extends SWIFT_App
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

        $_interfaceType = $_SWIFT->Interface->GetInterface();

        parent::Initialize();

        if ($_interfaceType == SWIFT_Interface::INTERFACE_CLIENT || $_interfaceType == SWIFT_Interface::INTERFACE_VISITOR || $_interfaceType == SWIFT_Interface::INTERFACE_RSS || $_interfaceType == SWIFT_Interface::INTERFACE_CHAT || $_interfaceType == SWIFT_Interface::INTERFACE_ADMIN || $_interfaceType == SWIFT_Interface::INTERFACE_STAFF) {
            $_SWIFT->Cache->Queue('visitorbancache');
            $_SWIFT->Cache->Queue('widgetcache');
            $_SWIFT->Cache->Queue('chatcountcache');
        }

        return true;
    }

}

?>
