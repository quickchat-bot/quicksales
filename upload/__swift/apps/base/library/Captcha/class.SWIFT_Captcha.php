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

namespace Base\Library\Captcha;

use SWIFT;
use SWIFT_Exception;
use SWIFT_Session;

\SWIFT_Loader::LoadInterface('Captcha:Captcha', 'base');

/**
 * The Captcha Generation Library
 *
 * @author Varun Shoor
 */
class SWIFT_Captcha extends SWIFT_CaptchaManager implements SWIFT_Captcha_Interface
{
    private $_captchaColors = array(array(33, 80, 132), array(143, 101, 34), array(49, 49, 49), array(36, 86, 26), array(188, 22, 22), array(85, 183, 166), array(171, 170, 64));

    // Core Constants
    const FONT_COUNT = 3;
    const FONT_FILENAME = 'captcha%d.ttf';

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
     * Retrieve the relevant HTML for Captcha
     *
     * @author Varun Shoor
     * @return string|bool
     * @throws SWIFT_Captcha_Exception If the Class is not Loaded
     */
    public function GetHTML()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Captcha_Exception(SWIFT_CLASSNOTLOADED);
        } elseif (!parent::GetHTML()) {
            return false;
        }

        $_captchaWord = self::GenerateWord();
        if(!$this->Session)
            SWIFT_Session::Start(SWIFT::GetInstance()->Interface);

        $this->Session->SetCaptcha($_captchaWord);

        return '<div class="captchaholder"><img src="' . SWIFT::Get('basename') . '/Base/Captcha/GetWordImage' . '" align="middle" border="0" /><input type="text" name="captcha" class="swifttextlarge" value="" /></div>';
    }

    /**
     * Generate and return the captcha word
     *
     * @author Varun Shoor
     * @return string
     */
    protected static function GenerateWord()
    {
        return preg_replace('/[oli0]/i', '', strtolower(substr(BuildHash(), 0, 8)));
    }

    /**
     * Check to see if Captcha is Valid
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Captcha_Exception If the Class is not Loaded
     */
    public function IsValidCaptcha()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Captcha_Exception(SWIFT_CLASSNOTLOADED);
        } elseif (!parent::IsValidCaptcha()) {
            return true;
        }

        if (!isset($_POST['captcha'])) {
            return false;
        }

        if ($this->Session->GetProperty('captcha') != strtolower($_POST['captcha']) || empty($_POST['captcha'])) {
            // We have to reset the captcha word..
            $_captchaWord = self::GenerateWord();
            $this->Session->SetCaptcha($_captchaWord);

            return false;
        }

        // We have to reset the captcha word.. so that if user refreshes (due to some error), he gets access denied.
        $_captchaWord = self::GenerateWord();
        $this->Session->SetCaptcha($_captchaWord);

        return true;
    }

    /**
     * Check to see if Captcha can be generated
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function CanCapthca()
    {
        if (function_exists("imagecreate") && function_exists("imagettftext")) {
            return true;
        }

        return false;
    }

    /**
     * Generate the Captcha
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public function Generate()
    {
        $_captchaWord = $this->Session->GetProperty('captcha');
        if (empty($_captchaWord)) {
            return false;
        }

        $_fontPath = './' . SWIFT_BASEDIRECTORY . '/' . SWIFT_COREAPPSDIRECTORY . '/' . APP_BASE . '/' . SWIFT_LIBRARYDIRECTORY . '/Captcha/' . self::FONT_FILENAME;

        $_imageHandle = imagecreate(215, 40);

        if (!$_imageHandle) {
            echo self::CantGenerateImage(true);

            return false;
        }

        $_backgroundColor = imageColorAllocate($_imageHandle, 255, 255, 255);
        $_captchaColors = count($this->_captchaColors);

        $_coordinateX = 0;
        for ($_ii = 0; $_ii < mb_strlen($_captchaWord); $_ii++) {
            $_index = mt_rand(0, (count($this->_captchaColors) - 1));
            $_angle = mt_rand(-8, 8);
            $_randomCoordinateX = mt_rand(0, 12);
            $_coordinateX = $_coordinateX + 15 + $_randomCoordinateX;
            $_foregroundColor = imageColorAllocate($_imageHandle, $this->_captchaColors[$_index][0], $this->_captchaColors[$_index][1], $this->_captchaColors[$_index][2]);

            $_fontNumber = mt_rand(1, self::FONT_COUNT);

            if (function_exists('imagettftext')) {
                call_user_func('imagettftext', $_imageHandle, 15, $_angle, $_coordinateX, 30, $_foregroundColor,
                    realpath(sprintf($_fontPath, $_fontNumber)), substr($_captchaWord, $_ii, 1));
            } else {
                echo 'GD not installed';
                return false;
            }
        }

        HeaderNoCache();

        if (function_exists('imagejpeg')) {
            header("Content-type: image/jpeg");
            call_user_func('imagejpeg', $_imageHandle);
        } else {
            echo 'GD not installed';
            return false;
        }
        return true;
    }

    /**
     * Data to send if image cannot be generated
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public static function CantGenerateImage($_sendHTTP = false)
    {
        $_imageData = base64_decode("R0lGODlhhQAPAPAAAAAAAP///yH5BAAAAAAALAAAAACFAA8AAALXjIFoy+0BnktK2otbzLwf7YGQtS1l+JyZirbj5G7sB7tiN9tcriUmVBlRbpJI0NerDA9BkVHZBEZ9JyTvhz0SsaQq90Xz0pwl7RhcLv+u4DBDNWOLs85v2/2V1d9noMfKF7hV08cCWFgzdyeFeBeVonQGl4JRRXEUiXZpKCl4OCl1WbnXOEiUA6o22iaWSioY6GqHSsn6+qJqhwurF3vby2XZCbuVO2aceVyrCOx3nGuJZEI1PI261Mf4Rq3d/To3FM36qFNufo5OnL7O3r6a7R4v344tUQAAOw==");
        if ($_sendHTTP) {
            HeaderNoCache();
            header("Content-type: image/gif");
        }

        return $_imageData;
    }
}

?>
