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

/**
 * The Staff API Controller
 *
 * @author Varun Shoor
 */
class Controller_staffapi extends SWIFT_Controller
{
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
            $this->_DispatchError($this->Language->Get('invalid_sessionid'), -2);

            return;
        }
    }

    /**
     * Dispatch an Error
     *
     * @author Varun Shoor
     * @param string $_errorString The Error String to Dispatch
     * @param int $_statusCode (OPTIONAL) The Status Code
     * @return bool "true" on Success, "false" otherwise
     */
    protected function _DispatchError($_errorString, $_statusCode = -1)
    {
        $this->XML->AddParentTag('kayako_staffapi');
            $this->XML->AddTag('status', $_statusCode);
            $this->XML->AddTag('error', $_errorString);
        $this->XML->EndTag('kayako_staffapi');
        $this->XML->EchoXMLStaffAPI();

        return false;
    }

    /**
     * Dispatch a Confirmation
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    protected function _DispatchConfirmation()
    {
        $this->XML->AddParentTag('kayako_staffapi');
            $this->XML->AddTag('status', '1');
        $this->XML->EndTag('kayako_staffapi');
        $this->XML->EchoXMLStaffAPI();

        return true;
    }

    public function GetInfo() {
        return true;
    }

    public function RebuildCache() {
        return true;
    }
}
