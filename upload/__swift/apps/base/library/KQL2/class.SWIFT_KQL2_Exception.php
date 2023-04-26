<?php
/**
 * =======================================
 * ###################################
 * SWIFT Framework
 *
 * @package    SWIFT
 * @author    QuickSupport Singapore Pte. Ltd.
 * @copyright    Copyright (c) 2001-QuickSupport Singapore Pte. Ltd.h Ltd.
 * @license    http://www.kayako.com/license
 * @link        http://www.kayako.com
 * @filesource
 * ###################################
 * =======================================
 */

// TODO:
//  o Use localized error messages
//  o Get start-end, token from KQL2Parser (can be the first parameter for constructor)

namespace Base\Library\KQL2;

use SWIFT_Exception;

/**
 * The KQL Exception Handling Class
 *
 * @author Andriy Lesyuk
 */
class SWIFT_KQL2_Exception extends SWIFT_Exception
{

    /**
     * Constructor
     *
     * @author Andriy Lesyuk
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
