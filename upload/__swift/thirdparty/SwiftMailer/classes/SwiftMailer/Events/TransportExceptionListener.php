<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Listens for Exceptions thrown from within the Transport system.
 *
 * @author Chris Corbyn
 */
interface SwiftMailer_Events_TransportExceptionListener extends SwiftMailer_Events_EventListener
{
    /**
     * Invoked as a TransportException is thrown in the Transport system.
     *
     * @param SwiftMailer_Events_TransportExceptionEvent $evt
     */
    public function exceptionThrown(SwiftMailer_Events_TransportExceptionEvent $evt);
}
