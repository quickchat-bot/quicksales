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

namespace Parser\Models\Breakline;
use SWIFT_Exception;

/**
 * The Breakline Exception Class
 *
 * @author Varun Shoor
 */
class SWIFT_Breakline_Exception extends SWIFT_Exception
{
    /**
     * Constructor
     *
     * @author Varun Shoor
     *
     * @param string $_errorMessage The Error Message
     * @param int    $_errorCode    The Error Code
     */
    public function __construct($_errorMessage, $_errorCode = 0)
    {
        parent::__construct($_errorMessage, $_errorCode);
    }
}

?>
