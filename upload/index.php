<?php
/***
 * =======================================
 * ###################################
 * SWIFT Framework
 *
 * @package    SWIFT
 * @author    QuickSupport Singapore Pte. Ltd.
 * @copyright    Copyright (c) 2001-2009, QuickSupport Singapore Pte. Ltd.
 * @license    http://www.kayako.com/license
 * @link        http://www.kayako.com
 * @filesource
 * ###################################
 * =======================================
 */

// Interface Declarations
define('SWIFT_INTERFACE', 'client');
define('SWIFT_INTERFACEFILE', __FILE__);

if (defined("SWIFT_CUSTOMPATH"))
{
    chdir(SWIFT_CUSTOMPATH);
} else {
    chdir('./__swift/');
}

require_once ('./swift.php');