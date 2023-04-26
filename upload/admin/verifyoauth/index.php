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

// Interface Declarations
define('SWIFT_INTERFACE', 'admin');
define('SWIFT_INTERFACEFILE', __FILE__);

if (defined("SWIFT_CUSTOMPATH"))
{
    chdir(SWIFT_CUSTOMPATH);
} else {
    chdir('./../../__swift/');
}

$_SERVER['PATH_INFO'] = '/Parser/EmailQueue/VerifyOAuth';

require_once ('./swift.php');

?>