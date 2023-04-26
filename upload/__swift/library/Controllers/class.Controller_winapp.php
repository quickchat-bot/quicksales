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
 * @copyright    Copyright (c) 2001-2012, Kayako
 * @license        http://www.kayako.com/license
 * @link        http://www.kayako.com
 *
 * ###############################################
 */

/**
 * The Winapp Main Controller
 *
 * @author Varun Shoor
 */
class Controller_winapp extends SWIFT_Controller
{
    // Core Constants
    const POLL_HEADER = '------ BEGIN PACKET (%s) ------';
    const POLL_FOOTER = '------ END PACKET (%s) ------';
    const POLL_ITTERATIONS = 60;
    const POLL_SLEEPTIME = 20;

    /** @var SWIFT_XML */
    public $XML;

    /**
     * Constructor
     *
     * @author Varun Shoore
     */
    public function __construct()
    {
        parent::__construct();

        $_SWIFT = SWIFT::GetInstance();

        $this->Load->Library('XML:XML');

        // We dont check the session when attempging login...
        if (($_SWIFT->Router->GetController() == 'Default' && $_SWIFT->Router->GetAction() == 'Login'))
        {
            return;
        }

        if (!SWIFT_Session::Start($this->Interface)) {
            // Failed to load session
            $this->_DispatchError($this->Language->Get('invalid_sessionid'));

            log_error_and_exit();
        }
    }

    /**
     * Destructor
     *
     * @author Varun Shoore
     */
    public function __destruct()
    {
        parent::__destruct();
    }

    /**
     * Dispatch an Error to the Winapp
     *
     * @author Varun Shoor
     * @param string $_errorString The Error String to Dispatch
     * @return bool "true" on Success, "false" otherwise
     */
    protected function _DispatchError($_errorString)
    {
        $this->XML->AddParentTag('kayako_livechat');
            $this->XML->AddTag('status', '-1');
            $this->XML->AddTag('error', $_errorString);
        $this->XML->EndTag('kayako_livechat');
        $this->XML->EchoXMLWinapp();

        log_error_and_exit();
    }

    /**
     * Dispatch a Confirmation
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    protected function _DispatchConfirmation()
    {
        $this->XML->AddParentTag('kayako_livechat');
            $this->XML->AddTag('status', '1');
        $this->XML->EndTag('kayako_livechat');
        $this->XML->EchoXMLWinapp();

        log_error_and_exit();
    }
}
