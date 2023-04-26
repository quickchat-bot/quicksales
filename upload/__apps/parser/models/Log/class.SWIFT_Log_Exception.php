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
 * @copyright      Copyright (c) 2001-2012, Kayako
 * @license        http://www.kayako.com/license
 * @link           http://www.kayako.com
 *
 * ###############################################
 */

namespace Parser\Models\Log;
use SWIFT_Exception;

/**
 * The Parser Log Exception Handler
 *
 * @author Varun Shoor
 */
class SWIFT_Log_Exception extends SWIFT_Exception
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
