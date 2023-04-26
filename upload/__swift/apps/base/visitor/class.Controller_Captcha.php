<?php
namespace Base\Visitor;

use Base\Library\Captcha\SWIFT_Captcha;
use Base\Library\Captcha\SWIFT_CaptchaManager;
use Controller_visitor;
use SWIFT_Exception;
use SWIFT_Session;

/**
 * Captcha Controller. Renders / Loads Captcha Words
 *
 * @author Abdulrahman Suleiman <abdulrahman.suleiman@crossover.com>
 */
class Controller_Captcha extends Controller_visitor
{
    protected $_CaptchaObject = false;

    /**
     * @author Abdulrahman Suleiman <abdulrahman.suleiman@crossover.com>
     *
     * @throws SWIFT_Exception
     */
    public function __construct()
    {
        parent::__construct();

        SWIFT_Session::Start($this->Interface);

        $this->_CaptchaObject = SWIFT_CaptchaManager::GetCaptchaObject();
        if (!$this->_CaptchaObject instanceof SWIFT_Captcha || !$this->_CaptchaObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }
    }

    /**
     * @author Abdulrahman Suleiman <abdulrahman.suleiman@crossover.com>
     *
     * @return bool
     * @throws SWIFT_Exception
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
