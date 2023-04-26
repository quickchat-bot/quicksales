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

namespace Base\Library\Captcha;

use Base\Library\Captcha\SWIFT_Captcha;
use SWIFT;
use Base\Library\Captcha\SWIFT_CaptchaReCaptcha;
use SWIFT_Exception;
use SWIFT_Library;

/**
 * The Captcha Manager Class
 *
 * @author Varun Shoor
 */
abstract class SWIFT_CaptchaManager extends SWIFT_Library
{
    // Core Constants
    const TYPE_LOCAL = 'local';
    const TYPE_RECAPTCHA = 'recaptcha';

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Check to see if its a valid captcha type
     *
     * @author Varun Shoor
     * @param string $_captchaType The Captcha Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidCaptchaType($_captchaType)
    {
        if ($_captchaType == self::TYPE_LOCAL || $_captchaType == self::TYPE_RECAPTCHA) {
            return true;
        }

        return false;
    }

    /**
     * Can run Captcha
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public static function CanCaptcha()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_reCaptchaPublicKey = $_SWIFT->Settings->Get('security_recpublickey');
        $_reCaptchaPrivateKey = $_SWIFT->Settings->Get('security_recpublickey');

        if ($_SWIFT->Settings->Get('security_captchatype') == self::TYPE_LOCAL && function_exists('imagecreate') && function_exists('imagettftext')) {
            return true;
        } elseif ($_SWIFT->Settings->Get('security_captchatype') == self::TYPE_RECAPTCHA && !empty($_reCaptchaPublicKey) && !empty($_reCaptchaPrivateKey)) {
            return true;
        }

        return false;
    }

    /**
     * Retrieve the relevant Captcha Object based on settings
     *
     * @author Varun Shoor
     * @return mixed SWIFT_CaptchaManager derived Object on Success, "false" otherwise
     */
    public static function GetCaptchaObject()
    {
        $_SWIFT = SWIFT::GetInstance();

        if ($_SWIFT->Settings->Get('security_captchatype') == self::TYPE_LOCAL) {
            return new SWIFT_Captcha();
        } elseif ($_SWIFT->Settings->Get('security_captchatype') == self::TYPE_RECAPTCHA) {
            return new SWIFT_CaptchaReCaptcha();
        }

        return false;
    }

    /**
     * Retrieve the relevant HTML for Captcha
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetHTML()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            // Dont return any HTML if Captcha settings are invalid
        } elseif (!self::CanCaptcha()) {
            return false;
        }

        return true;
    }

    /**
     * Check to see if Captcha is Valid
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function IsValidCaptcha()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            // Always return true if Captcha settings are invalid
        } elseif (!self::CanCaptcha()) {
            return true;
        }

        return true;
    }
}

?>
