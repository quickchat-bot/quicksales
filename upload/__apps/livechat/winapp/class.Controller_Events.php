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

namespace LiveChat\Winapp;

use Controller_winapp;
use SWIFT;
use SWIFT_Exception;

/**
 * The Winapp Event Dispatching Controller
 *
 * @author Varun Shoor
 */
class Controller_Events extends Controller_winapp
{
    public $ChatEventWinapp;

    /**
     * Constructor
     *
     * @author Varun Shoor
     */
    public function __construct()
    {
        parent::__construct();

        $this->Language->Load('livesupport');

        $this->Load->Library('Chat:ChatEventWinapp', [], true, false, APP_LIVECHAT);
    }

    /**
     * The Main Dispatcher Function
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function Index()
    {
        HeaderNoCache();

        $this->_DispatchPacket();

        return true;
    }

    /**
     * Dispatches the Winapp Packet
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function _DispatchPacket()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_winappGUIDList = array();
        if (isset($_POST['xml']) && !empty($_POST['xml'])) {
            $_winappGUIDList = $this->ChatEventWinapp->ProcessIncomingWinappEvents($_POST['xml']);
        }

        $this->ChatEventWinapp->PrepareWinappPacket($_winappGUIDList);

        return true;
    }

    /**
     * The Main Dispatcher Function
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Stream()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_SWIFT = SWIFT::GetInstance();

        HeaderNoCache();

        @ini_set('zlib.output_compression', 0);
        @ini_set('implicit_flush', 1);
        for ($i = 0; $i < ob_get_level(); $i++) {
            ob_end_flush();
        }
        ob_implicit_flush(1);

        for ($_ii = 0; $_ii < Controller_winapp::POLL_ITTERATIONS; $_ii++) {
            ob_start();
            $this->_DispatchPacket();

            $_contents = ob_get_contents();
            ob_end_clean();

            $_contentsHash = md5($_contents);
            echo sprintf(Controller_winapp::POLL_HEADER, $_contentsHash) . SWIFT_CRLF;
            echo $_contents . SWIFT_CRLF;
            echo Controller_winapp::POLL_FOOTER . SWIFT_CRLF;
            echo sprintf(Controller_winapp::POLL_FOOTER, $_contentsHash) . SWIFT_CRLF;

            // Attempt to update the Session Activity...
            $_mod = $_ii % 8;

            if ($_mod == 0) {
                $_SWIFT->Session->UpdateActivityCombined();
            }

            sleep(Controller_winapp::POLL_SLEEPTIME);
        }

        return true;
    }
}
