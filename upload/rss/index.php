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
 * @license        http://www.kayako.com/license
 * @link        http://www.kayako.com
 *
 * ###############################################
 */

// Interface Declarations
define('SWIFT_INTERFACE', 'rss');
define('SWIFT_INTERFACEFILE', __FILE__);

if (defined("SWIFT_CUSTOMPATH"))
{
    chdir(SWIFT_CUSTOMPATH);
} else {
    chdir(dirname(__FILE__) . '/../__swift/');
}

require_once ('./swift.php');

?>