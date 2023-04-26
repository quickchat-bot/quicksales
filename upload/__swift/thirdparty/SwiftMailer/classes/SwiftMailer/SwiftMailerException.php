<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Base Exception class.
 *
 * @author Chris Corbyn
 */
class SwiftMailer_SwiftMailerException extends Exception
{
    /**
     * Create a new SwiftMailerException with $message.
     *
     * @param string    $message
     * @param int       $code
     * @param Exception $previous
     */
    public function __construct($message, $code = 0, Exception $previous = null)
    {
        /**
         * BUG FIX - Ravi Sharma <ravi.sharma@kayako.com>
         *
         * SWIFT-4831 SMTP errors should be logged under error logs for notification emails when there is issue with SMTP server.
         */
        SWIFT_ErrorLog::Create(SWIFT_ErrorLog::TYPE_MAILERROR, $message);

        parent::__construct($message, $code, $previous);
    }
}
