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

/**
 * The Modify Controller
 * 
 * @author Varun Shoor
 */
class Controller_Modify extends SWIFT_Controller
{
    /**
     * Constructor
     *
     * @author Varun Shoor
     */
    public function __construct()
    {
        parent::__construct();

        $this->Language->Load('setup');
    }

    /**
     * The Index Function
     * 
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function Index()
    {
    }
}
?>
