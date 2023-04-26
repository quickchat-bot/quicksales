<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Redundantly and rotationally uses several Transports when sending.
 *
 * @author Chris Corbyn
 */
class SwiftMailer_Transport_LoadBalancedTransport implements SwiftMailer_Transport
{
    /**
     * Transports which are deemed useless.
     *
     * @var SwiftMailer_Transport[]
     */
    private $_deadTransports = array();

    /**
     * The Transports which are used in rotation.
     *
     * @var SwiftMailer_Transport[]
     */
    protected $_transports = array();

    /**
     * The Transport used in the last successful send operation.
     *
     * @var SwiftMailer_Transport
     */
    protected $_lastUsedTransport = null;

    // needed as __construct is called from elsewhere explicitly
    public function __construct()
    {
    }

    /**
     * Set $transports to delegate to.
     *
     * @param SwiftMailer_Transport[] $transports
     */
    public function setTransports(array $transports)
    {
        $this->_transports = $transports;
        $this->_deadTransports = array();
    }

    /**
     * Get $transports to delegate to.
     *
     * @return SwiftMailer_Transport[]
     */
    public function getTransports()
    {
        return array_merge($this->_transports, $this->_deadTransports);
    }

    /**
     * Get the Transport used in the last successful send operation.
     *
     * @return SwiftMailer_Transport
     */
    public function getLastUsedTransport()
    {
        return $this->_lastUsedTransport;
    }

    /**
     * Test if this Transport mechanism has started.
     *
     * @return bool
     */
    public function isStarted()
    {
        return count($this->_transports) > 0;
    }

    /**
     * Start this Transport mechanism.
     */
    public function start()
    {
        $this->_transports = array_merge($this->_transports, $this->_deadTransports);
    }

    /**
     * Stop this Transport mechanism.
     */
    public function stop()
    {
        foreach ($this->_transports as $transport) {
            $transport->stop();
        }
    }

    /**
     * Send the given Message.
     *
     * Recipient/sender data will be retrieved from the Message API.
     * The return value is the number of recipients who were accepted for delivery.
     *
     * @param SwiftMailer_Mime_Message $message
     * @param string[]           $failedRecipients An array of failures by-reference
     *
     * @return int
     */
    public function send(SwiftMailer_Mime_Message $message, &$failedRecipients = null)
    {
        $maxTransports = count($this->_transports);
        $sent = 0;
        $this->_lastUsedTransport = null;

        for ($i = 0; $i < $maxTransports
            && $transport = $this->_getNextTransport(); ++$i) {
            try {
                if (!$transport->isStarted()) {
                    $transport->start();
                }
                if ($sent = $transport->send($message, $failedRecipients)) {
                    $this->_lastUsedTransport = $transport;
                    break;
                }
            } catch (SwiftMailer_TransportException $e) {
                $this->_killCurrentTransport();
            }
        }

        if (count($this->_transports) == 0) {
            throw new SwiftMailer_TransportException(
                'All Transports in LoadBalancedTransport failed, or no Transports available'
                );
        }

        return $sent;
    }

    /**
     * Register a plugin.
     *
     * @param SwiftMailer_Events_EventListener $plugin
     */
    public function registerPlugin(SwiftMailer_Events_EventListener $plugin)
    {
        foreach ($this->_transports as $transport) {
            $transport->registerPlugin($plugin);
        }
    }

    /**
     * Rotates the transport list around and returns the first instance.
     *
     * @return SwiftMailer_Transport
     */
    protected function _getNextTransport()
    {
        if ($next = array_shift($this->_transports)) {
            $this->_transports[] = $next;
        }

        return $next;
    }

    /**
     * Tag the currently used (top of stack) transport as dead/useless.
     */
    protected function _killCurrentTransport()
    {
        if ($transport = array_pop($this->_transports)) {
            try {
                $transport->stop();
            } catch (Exception $e) {
            }
            $this->_deadTransports[] = $transport;
        }
    }
}
