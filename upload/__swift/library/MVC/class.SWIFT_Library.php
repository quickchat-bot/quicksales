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
 * The Core Library Management Class
 *
 * @author Varun Shoor
 * @method GetProperty($propName = '')
 * @method int GetServerID()
 */
class SWIFT_Library extends SWIFT_Base
{
    protected $_updatePool = array();

    /**
     * Constructor
     *
     * @author Varun Shoore
     * @throws SWIFT_Exception If the Model could not be Initialized
     */
    public function __construct()
    {
        if (!$this->Initialize())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        parent::__construct();
    }
}
?>
