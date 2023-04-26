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

namespace LiveChat\Winapp;

use Controller_winapp;
use SWIFT_Exception;

/**
 * The Chat <> Ticket Management Controller
 *
 * @author Varun Shoor
 */
class Controller_Ticket extends Controller_winapp
{
    /**
     *
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Create()
    {
        /*
         * ###############################################
         * TODO: Finish this function
         * ###############################################
         */
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return true;
    }
}
