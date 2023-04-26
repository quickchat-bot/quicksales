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

namespace News;

use SWIFT;
use SWIFT_App;
use SWIFT_Exception;
use SWIFT_Interface;

/**
 * The News App Lib
 *
 * @author Varun Shoor
 */
class SWIFT_App_news extends SWIFT_App
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

        if ($_SWIFT->Interface->GetInterface() == SWIFT_Interface::INTERFACE_CLIENT || $_SWIFT->Interface->GetInterface() == SWIFT_Interface::INTERFACE_STAFF || $_SWIFT->Interface->GetInterface() == SWIFT_Interface::INTERFACE_TESTS)
        {
            $_SWIFT->Cache->Queue('newscategorycache');
        }

        return true;
    }

}
?>