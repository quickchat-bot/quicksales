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

namespace Base\Library\Captcha;

use SWIFT;

\SWIFT_Loader::LoadInterface('Captcha:Captcha', 'base');

/**
 * The ReCaptcha Class
 *
 * @author Varun Shoor
 */
class SWIFT_CaptchaReCaptcha extends SWIFT_CaptchaManager implements SWIFT_Captcha_Interface
{
    const RECAPTCHA_VERIFICATION_URL = 'https://www.google.com/recaptcha/api/siteverify';

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
     * Destructor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function __destruct()
    {
        parent::__destruct();
    }

    /**
     * Retrieve the relevant HTML for Captcha
     *
     * @author Varun Shoor
     * @return string|bool
     * @throws SWIFT_Captcha_Exception If the Class is not Loaded
     */
    public function GetHTML($_scriptLoaded = false)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Captcha_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!parent::GetHTML()) {
            return false;
        }

        $_returnHTML = '';

        if ($_scriptLoaded == false) {
            $_returnHTML .= '<script src="https://www.google.com/recaptcha/api.js"></script>';
        }

        $_returnHTML .= '<div class="g-recaptcha" data-sitekey="' . $this->Settings->Get('security_recpublickey') . '"></div>';

        return $_returnHTML;
    }

    /**
     * Check to see if Captcha is Valid
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Captcha_Exception If the Class is not Loaded or If Invalid Captcha Response Received
     */
    public function IsValidCaptcha()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Captcha_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!parent::IsValidCaptcha()) {
            return true;
        }

        if (!isset($_POST['g-recaptcha-response'])) {
            return false;
        }

        $_ResponseObject = $this->recaptchaV2_check_response($this->Settings->Get('security_recprivatekey'), SWIFT::Get('IP'), $_POST['g-recaptcha-response']);
        if (!$_ResponseObject->is_valid) {
            return false;
        }

        return true;
    }

    /**
     * Check to see if ReCAPTCHA V2.0 response is Valid
     *
     * @author Ankit Saini
     * @return \stdClass
     * @throws SWIFT_Captcha_Exception If the Class is not Loaded or If Invalid Captcha Response Received
     */
    public function recaptchaV2_check_response($privkey, $remoteip, $response)
    {

        if ($privkey == null || $privkey == '') {
            die ("To use reCAPTCHA you must get an API key from <a href='https://www.google.com/recaptcha/admin/create'>https://www.google.com/recaptcha/admin/create</a>");
        }

        if ($remoteip == null || $remoteip == '') {
            die ("For security reasons, you must pass the remote ip to reCAPTCHA");
        }

        $data = array(
            'secret' => $privkey,
            'remoteip' => $remoteip,
            'response' => $_POST["g-recaptcha-response"]
        );

        $content = http_build_query($data);

        $options = array(
            'http' => array(
                'header' => 'Content-Type: application/x-www-form-urlencoded\r\n' .
                    'Content-Length: ' . strlen($content) . '\r\n',
                'method' => 'POST',
                'content' => $content
            )
        );

        $context = stream_context_create($options);
        $verify = file_get_contents(self::RECAPTCHA_VERIFICATION_URL, false, $context);
        $captcha_success = json_decode($verify);

        // this change from ReCaptchaResponse() to stdClass() is non-lethal
        // as $recaptcha_response is purely required for its is_valid prop in this class
        // and static analysis is unable to recognize the ReCaptchaResponse for some reason
        $recaptcha_response = new \stdClass();

        if ($captcha_success->success == true) {
            $recaptcha_response->is_valid = true;
        } else {
            $recaptcha_response->is_valid = false;
        }
        return $recaptcha_response;

    }
}

?>
