#!/usr/bin/php -q
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
define('SWIFT_INTERFACE', 'console');
define('SWIFT_INTERFACEFILE', __FILE__);

/*
 * BUG FIX - Simaranjit Singh and Ravinder Singh
 *
 * SWIFT-2881 On accessing the /console from web, it is displaying full path
 *
 * Comments: None
 */
if (function_exists('php_sapi_name'))
{
    if ((php_sapi_name() != 'cli' && php_sapi_name() != 'cgi-fcgi') || !empty($_SERVER['REMOTE_ADDR']))
    {
        log_error_and_exit();
    }
}

if (defined("SWIFT_CUSTOMPATH"))
{
    chdir(SWIFT_CUSTOMPATH);
} else {
    chdir(dirname(__FILE__) . '/../__swift/');
}

require_once ('./swift.php');

?>
