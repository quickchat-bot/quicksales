<?php
/**
 * ###############################################
 *
 * SWIFT Framework
 * _______________________________________________
 *
 * @author         Varun Shoor
 *
 * @package        SWIFT
 * @copyright      Copyright (c) 2001-2012, QuickSupport
 * @license        http://www.kayako.com/license
 * @link           http://www.kayako.com
 *
 * ###############################################
 */

namespace Parser\Models\Ban;
use SWIFT_Exception;

/**
 * The Parser Ban Exception Handler
 *
 * @author Varun Shoor
 */
class SWIFT_Ban_Exception extends SWIFT_Exception
{
    /**
     * Constructor
     *
     * @author Varun Shoor
     *
     * @param string $_errorMessage The Error Message
     * @param int    $_errorCode    The Error Code
     *
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct($_errorMessage, $_errorCode = 0)
    {
        parent::__construct($_errorMessage, $_errorCode);
    }
}

?>
