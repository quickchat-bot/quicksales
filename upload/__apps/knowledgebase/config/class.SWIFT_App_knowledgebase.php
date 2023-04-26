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

namespace Knowledgebase;

use SWIFT;
use SWIFT_App;
use SWIFT_Interface;

/**
 * The Knowledgebase App Lib
 *
 * @author Varun Shoor
 */
class SWIFT_App_knowledgebase extends SWIFT_App
{
    /**
     * Initialize the App
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function Initialize()
    {
        $_SWIFT = SWIFT::GetInstance();

        parent::Initialize();

        $interface = $_SWIFT->Interface->GetInterface();
        if ($interface === SWIFT_Interface::INTERFACE_CLIENT || $interface === SWIFT_Interface::INTERFACE_STAFF || $interface === SWIFT_Interface::INTERFACE_TESTS) {
            $_SWIFT->Cache->Queue('kbcategorycache');
        }

        return true;
    }

}
