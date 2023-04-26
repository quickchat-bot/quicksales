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

namespace Base\Library\Rating;

use SWIFT_Exception;

/**
 * The Rating Exception Handling Class
 *
 * @author Varun Shoor
 */
class SWIFT_Rating_Exception extends SWIFT_Exception
{
    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param string $_errorMessage The Error Message
     * @param int $_errorCode The Error Code
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct($_errorMessage, $_errorCode = 0)
    {
        parent::__construct($_errorMessage, $_errorCode);
    }
}

?>
