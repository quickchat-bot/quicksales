<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/*
 * Autoloader and dependency injection initialization for Swift Mailer.
 */

if (class_exists('SwiftMailer', false)) {
    return;
}

// Load SwiftMailer utility class
require __DIR__.'/SwiftMailer.php';

if (!function_exists('_swiftmailer_init')) {
    function _swiftmailer_init()
    {
        require __DIR__.'/swiftmailer_init.php';
    }
}

// Start the autoloader and lazy-load the init script to set up dependency injection
SwiftMailer::registerAutoload('_swiftmailer_init');
