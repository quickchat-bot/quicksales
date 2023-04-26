<?php
/**
 * ###############################################
 *
 * SWIFT Framework
 * _______________________________________________
 *
 * @author         Varun Shoor
 *
 * @package        SWIFT
 * @copyright      Copyright (c) 2001-2012, QuickSupport
 * @license        http://www.opencart.com.vn/license
 * @link           http://www.opencart.com.vn
 *
 * ###############################################
 */

/**
 * Cookie Handling Class, good to combine multiple variables into serialized array
 *
 * @author Varun Shoor
 */
class SWIFT_Cookie extends SWIFT_Library
{
    private $_cookiesCache = array();

    public $_cookieDomain = null;

    // Core Constants
    const COOKIE_PREFIX = 'SWIFT_';

    /**
     * Constructor
     *
     * @author Varun Shoore
     */
    public function __construct()
    {
        parent::__construct();
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
     * @author Rahul Bhattacharya
     *
     * @param string $_cookieDomain
     */
    public function SetDomain($_cookieDomain)
    {
        $this->_cookieDomain = $_cookieDomain;
    }

    /**
     * @author Rahul Bhattacharya
     *
     * @return string
     */
    public function GetDomain()
    {
        return $this->_cookieDomain;
    }

    /**
     * Encrypt a value and return data
     *
     * @author Varun Shoor
     *
     * @param string $_cookieValue The Cookie Value
     *
     * @return mixed "Encrypted Data" (STRING) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function Encrypt($_cookieValue)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

//        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
//        $iv      = mcrypt_create_iv($iv_size, MCRYPT_RAND);
//
//        $_crypted = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, substr(SWIFT::Get('InstallationHash'), 0, 32), $_cookieValue, MCRYPT_MODE_ECB, $iv);

        $iv_size = openssl_cipher_iv_length('aes-256-ecb');
        $iv = openssl_random_pseudo_bytes($iv_size);
        $_crypted = openssl_encrypt($_cookieValue, 'aes-256-ecb', substr(SWIFT::Get('InstallationHash'), 0, 32), OPENSSL_RAW_DATA, $iv);

        return trim(base64_encode($_crypted));
    }

    /**
     * Decrypt the value and return data
     *
     * @author Varun Shoor
     *
     * @param string $_cookieValue The Cookie Value
     *
     * @return string
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function Decrypt($_cookieValue)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_cookieValue = base64_decode($_cookieValue);
//        $iv_size      = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
//        $iv           = mcrypt_create_iv($iv_size, MCRYPT_RAND);
//
//        $_decrypted = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, substr(SWIFT::Get('InstallationHash'), 0, 32), $_cookieValue, MCRYPT_MODE_ECB, $iv);

        $iv_size = openssl_cipher_iv_length('aes-256-ecb');
        $iv = openssl_random_pseudo_bytes($iv_size);
        $_decrypted = openssl_decrypt($_cookieValue, 'aes-256-ecb', substr(SWIFT::Get('InstallationHash'), 0, 32), OPENSSL_RAW_DATA, $iv);

        return trim($_decrypted);
    }

    /**
     * Get a cookie string
     *
     * @author Varun Shoor
     *
     * @param string $_cookieName   The Cookie Name
     * @param bool   $_decryptValue (OPTIONAL) Whether to decrypt the cookie data
     *
     * @return string The Cookie Value
     */
    public function Get($_cookieName, $_decryptValue = false)
    {
        $_cookieName      = Clean($_cookieName);
        $_finalCookieName = self::COOKIE_PREFIX . htmlspecialchars($_cookieName);

        if (isset($_COOKIE[$_finalCookieName])) {
            $_cookieData = $_COOKIE[$_finalCookieName];

            if ($_decryptValue == true) {
                return $this->Decrypt($_cookieData);
            }

            return $_cookieData;
        }

        return '';
    }

    /**
     * Set the Cookie String
     *
     * @author Varun Shoor
     * @author Rahul Bhattacharya
     *
     * @param string $_cookieName   The Cookie Name
     * @param string $_cookieValue  The Cookie Value
     * @param bool   $_isPermanent  (OPTIONAL) Whether the cookie is permanent or not
     * @param bool   $_encryptValue (OPTIONAL) Whether to encrypt the cookie data
     * @param bool   $_secure       (OPTIONAL) Whether the cookie should only be transmitted over a secure HTTPS connection
     * @param bool   $_httpOnly     (OPTIONAL) Whether the cookie has to be accessible by scripting languages, such as JavaScript
     *
     * @return bool "true" on Success, "false" otherwise
     */
    public function Set($_cookieName, $_cookieValue, $_isPermanent = false, $_encryptValue = false, $_secure = false, $_httpOnly = true)
    {
        $_SWIFT = SWIFT::GetInstance();

        if ($_encryptValue == true) {
            $_cookieValue = $this->Encrypt($_cookieValue);
        }

        /**
         * BUG FIX - Ravi Sharma <ravi.sharma@opencart.com.vn>
         *
         * SWIFT-4790 Cookies should use secure attribute with HttpOnly flag to prevent session hijacking attacks.
         */
        if (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
            $_secure = true;
        }

        $_cookieExpiry = false;
        if ($_isPermanent) {
            $_cookieExpiry = DATENOW + 60 * 60 * 24 * 7; // 1 week from now
        }

        $_cookiePath = IIF($_SWIFT->Settings->Get('security_cookiepath') == '', '/', $_SWIFT->Settings->Get('security_cookiepath'));

        $_cookieDomain = $this->GetDomain();
        if (empty($_cookieDomain)) {
            $_cookieDomain = IIF($_SWIFT->Settings->Get('security_cookiedomain') == '', '', $_SWIFT->Settings->Get('security_cookiedomain'));
        }

        SetCookie(self::COOKIE_PREFIX . $_cookieName, $_cookieValue, $_cookieExpiry, $_cookiePath, $_cookieDomain, $_secure, $_httpOnly);

        return true;
    }

    /**
     * Empty the given cookie
     *
     * @author Varun Shoor
     *
     * @param string $_cookieName The Cookie Name
     *
     * @return bool "true" on Success, "false" otherwise
     */
    public function Delete($_cookieName)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_finalCookieName = self::COOKIE_PREFIX . $_cookieName;
        SetCookie($_finalCookieName, '', 0, IIF($_SWIFT->Settings->Get('security_cookiepath') == '', '/', $_SWIFT->Settings->Get('security_cookiepath')), IIF($_SWIFT->Settings->Get('security_cookiedomain') == '', '', $_SWIFT->Settings->Get('security_cookiedomain')));

        unset($_COOKIE[$_finalCookieName]);

        unset($this->_cookiesCache[$_finalCookieName]);

        return true;
    }

    /**
     * Parse the cookie name into an unserialized array
     *
     * @author Varun Shoor
     *
     * @param string $_cookieName The Cookie Name
     *
     * @return bool "true" on Success, "false" otherwise
     */
    public function Parse($_cookieName)
    {
        $_cookieName = Clean($_cookieName);

        if (!isset($_COOKIE[self::COOKIE_PREFIX . $_cookieName])) {
            $this->_cookiesCache[self::COOKIE_PREFIX . $_cookieName] = array();

            return false;
        }

        if (!isset($this->_cookiesCache[self::COOKIE_PREFIX . $_cookieName]) && isset($_COOKIE[self::COOKIE_PREFIX . $_cookieName])) {
            $this->_cookiesCache[self::COOKIE_PREFIX . $_cookieName] = @json_decode(html_entity_decode($_COOKIE[self::COOKIE_PREFIX . $_cookieName]), true);
        } else if (!isset($this->_cookiesCache[self::COOKIE_PREFIX . $_cookieName]) && !isset($_COOKIE[self::COOKIE_PREFIX . $_cookieName])) {
            $this->_cookiesCache[self::COOKIE_PREFIX . $_cookieName] = array();
        }

        if (is_array($this->_cookiesCache[self::COOKIE_PREFIX . $_cookieName])) {
            return true;
        }

        $this->_cookiesCache[self::COOKIE_PREFIX . $_cookieName] = array();

        return false;
    }

    /**
     * Add a variable into the cookies cache
     *
     * @author Varun Shoor
     *
     * @param string $_cookieName    The Cookie Name
     * @param string $_variableName  The Variable Name
     * @param string $_variableValue The Variable Value
     *
     * @return bool "true" on Success, "false" otherwise
     */
    public function AddVariable($_cookieName, $_variableName, $_variableValue)
    {
        $this->_cookiesCache[self::COOKIE_PREFIX . $_cookieName][$_variableName] = $_variableValue;

        return true;
    }

    /**
     * Get the variable value from a given cookie
     *
     * @author Varun Shoor
     *
     * @param string $_cookieName   The Cookie Name
     * @param string $_variableName The Variable Name
     *
     * @return mixed "Cookie Variable Value" on Success, "void" otherwise
     */
    public function GetVariable($_cookieName, $_variableName)
    {
        if (!isset($this->_cookiesCache[self::COOKIE_PREFIX . $_cookieName]) || !isset($this->_cookiesCache[self::COOKIE_PREFIX . $_cookieName][$_variableName])) {
            return;
        }

        return $this->_cookiesCache[self::COOKIE_PREFIX . $_cookieName][$_variableName];
    }

    /**
     * Rebuild the Cache and set the cookie
     *
     * @author Varun Shoor
     *
     * @param string $_cookieName  The Cookie Name
     * @param bool   $_isPermanent Whether the Cookie is Permanent
     *
     * @return bool "true" on Success, "false" otherwise
     */
    public function Rebuild($_cookieName, $_isPermanent = false)
    {
        if (!isset($this->_cookiesCache[self::COOKIE_PREFIX . $_cookieName])) {
            return false;
        }

        $this->Set($_cookieName, json_encode($this->_cookiesCache[self::COOKIE_PREFIX . $_cookieName]), $_isPermanent);

        return true;
    }
}

?>
