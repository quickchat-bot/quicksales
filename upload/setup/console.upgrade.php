#!/usr/bin/php -q
<?php
/**
 * ###############################################
 *
 * SWIFT Framework
 * _______________________________________________
 *
 * @author		Varun Shoor
 *
 * @package		SWIFT
 * @copyright	Copyright (c) 2001-2012, QuickSupport
 * @license		http://www.kayako.com/license
 * @link		http://www.kayako.com
 *
 * ###############################################
 */

// Interface Declarations
define('SWIFT_INTERFACE', 'setup');
define('SWIFT_INTERFACEFILE', __FILE__);

if (defined("SWIFT_CUSTOMPATH"))
{
	chdir(SWIFT_CUSTOMPATH);
} else {
	chdir('./../__swift/');
}

define('SETUP_CONSOLE', '1');

$_SERVER['PATH_INFO'] = '/Core/Upgrade/Console';

require_once ('./swift.php');
?>