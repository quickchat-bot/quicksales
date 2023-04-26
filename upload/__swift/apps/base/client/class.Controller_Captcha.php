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

namespace Base\Client;

use Base\Library\Captcha\SWIFT_Captcha;
use Base\Library\Captcha\SWIFT_CaptchaManager;
use Controller_client;
use SWIFT_Exception;

/**
 * The Captcha Controller. It is used to dispatch the Captcha words
 *
 * @author Varun Shoor
 */
class Controller_Captcha extends Controller_client
{
    protected $_CaptchaObject = false;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @throws SWIFT_Exception If Invalid Data is Received
     */
    public function __construct()
    {
        parent::__construct();

        $this->_CaptchaObject = SWIFT_CaptchaManager::GetCaptchaObject();
        if (!$this->_CaptchaObject instanceof SWIFT_Captcha || !$this->_CaptchaObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }
    }

    /**
     * Retrieve the Word Image
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetWordImage()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_captchaResult = $this->_CaptchaObject->Generate();
        if (!$_captchaResult) {
            return false;
        }

        return true;
    }
}
