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

namespace Tickets;

use SWIFT;
use SWIFT_App;
use SWIFT_Interface;

/**
 * The Tickets App Lib
 *
 * @author Varun Shoor
 */
class SWIFT_App_tickets extends SWIFT_App
{
    /**
     * Initialize the App
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws \SWIFT_Exception
     */
    public function Initialize()
    {
        $_SWIFT = SWIFT::GetInstance();

        parent::Initialize();

        $intName = $_SWIFT->Interface->GetName()?:SWIFT_INTERFACE;

        if ($intName === 'tests' || $intName === 'staff')
        {
            $_SWIFT->Cache->Queue('ticketviewdepartmentlinkcache');
        }

        return true;
    }
}
