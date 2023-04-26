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

/**
 * The Base Admin Controller
 * 
 * @author Varun Shoor
 */
class Controller_admin extends Controller_StaffBase
{
    /**
     * Constructor
     *
     * @author Varun Shoore
     * @throws SWIFT_Exception
     */
    public function __construct()
    {
        parent::__construct(self::TYPE_ADMIN);
    }

    public function GetInfo() {
        return true;
    }

    public function RebuildCache() {
        return true;
    }
}
